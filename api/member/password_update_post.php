<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../common/env_init.php';

//  Session 安全性設定
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
// ini_set('session.cookie_secure', 1); 

session_set_cookie_params(['samesite' => 'Strict']);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 守衛檢查：如果 Session 中沒有登入資訊，則拒絕存取
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    http_response_code(401); // 401 Unauthorized (未授權)
    // 在輸出 JSON 後立刻停止腳本，確保不會執行到後面的程式碼
    echo json_encode(['status' => 'error', 'message' => '未登入或憑證無效'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 從 Session 中取得當前登入者的 user_id
$loggedInUserId = $_SESSION['user_id'];


// 設定 HTTP Header
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "僅支援 POST 方法"]);
    exit;
}

// 接收並驗證前端傳來的資料
$data = json_decode(file_get_contents("php://input"), true);
if (empty($data['old_password']) || empty($data['new_password'])) { 
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'新舊密碼皆為必填']);
    exit;
}
$oldPassword = $data['old_password'];
$newPassword = $data['new_password'];

if ($oldPassword === $newPassword) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'新密碼不可與舊密碼相同']);
    exit;
}


$response = [];
$stmt_get = null;
$stmt_update = null;

try {
    $stmt_get = $mysqli->prepare("SELECT password FROM users WHERE user_id = ?");
    if ($stmt_get === false) throw new Exception("資料庫準備失敗 (查詢密碼)", 500);
    $stmt_get->bind_param('s', $loggedInUserId);
    $stmt_get->execute();
    $stmt_get->store_result();
    if ($stmt_get->num_rows === 0) throw new Exception("找不到使用者資料", 404);
    $stmt_get->bind_result($db_password);
    $stmt_get->fetch();
    
    // 比對密碼 (不加密直接用 === 比對)
    // 建議 password_verify()
    if ($oldPassword !== $db_password) {
        throw new Exception("舊密碼輸入錯誤", 401); 
    }

    // 更新為新密碼
    $stmt_update = $mysqli->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    if ($stmt_update === false) throw new Exception("資料庫準備失敗 (更新密碼)", 500);
    
    // 如有加密則綁定加密後的新密碼
    $stmt_update->bind_param('ss', $newPassword, $loggedInUserId);
    
    if ($stmt_update->execute() && $mysqli->affected_rows > 0) {
        $response = ["status" => "success", "message" => "密碼已成功更新"];
    } else {
        throw new Exception("更新密碼時發生錯誤", 500);
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response = ["status" => "error", "message" => $e->getMessage()];
} finally {
    if ($stmt_get !== null) {
        $stmt_get->close();
    }
    if ($stmt_update !== null) {
        $stmt_update->close();
    }
    if (isset($mysqli)) {
        $mysqli->close();
    }
}

echo json_encode($response);
?>