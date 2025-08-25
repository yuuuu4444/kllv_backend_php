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
if ($_SERVER["REQUEST_METHOD"] !== "POST")  {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "僅支援 POST 方法"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "沒有收到更新資料"]);
    exit;
}
$memberIdToDelete = $data['user_id'];

$response = [];
$stmt_role = null;
$stmt_member = null;
$stmt_delete = null;


try {
    if ($loggedInUserId === $memberIdToDelete) {
        throw new Exception("無法刪除自己", 400);
    }

    //是否主帳號並取得戶號
    $stmt_role = $mysqli->prepare("SELECT role_type, household_no FROM users WHERE user_id = ?");
    if ($stmt_role === false) throw new Exception("資料庫準備失敗 (權限查詢)", 500);
    $stmt_role->bind_param('s', $loggedInUserId);
    $stmt_role->execute();
    $stmt_role->store_result();
    if ($stmt_role->num_rows === 0) throw new Exception("找不到主帳號資料", 404);
    $stmt_role->bind_result($role_type, $my_household_no);
    $stmt_role->fetch();
    
    // 主帳號才能刪除
    if ($role_type !== 0) {
        throw new Exception("權限不足，只有主帳號可以刪除成員", 403);
    }

    // 被刪除者的戶號
    $stmt_member = $mysqli->prepare("SELECT household_no FROM users WHERE user_id = ?");
    $stmt_member->bind_param('s', $memberIdToDelete);
    $stmt_member->execute();
    $stmt_member->store_result();
    if ($stmt_member->num_rows === 0) throw new Exception("找不到要刪除的成員", 404);
    $stmt_member->bind_result($member_household_no);
    $stmt_member->fetch();

    // 比對戶號是否一致
    if (empty($my_household_no) || $my_household_no !== $member_household_no) {
        throw new Exception("權限不足，無法刪除此成員 (非同戶籍)", 403);
    }

    
    // 執行刪除
    // $stmt_delete = $mysqli->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt_delete = $mysqli->prepare("UPDATE users SET is_deleted = 1, is_active = 0  WHERE user_id = ?");
    $stmt_delete->bind_param('s', $memberIdToDelete);

    if ($stmt_delete->execute()) {
        if ($stmt_delete->affected_rows > 0) {
            $response = ["status" => "success", "message" => "成員刪除成功"];
        } else {
            throw new Exception("刪除操作已執行，但找不到對應的成員", 404);
        }
    } else {
        throw new Exception("資料庫刪除失敗: " . $stmt_delete->error, 500);
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response = ["status" => "error", "message" => $e->getMessage()];
}finally {
    if ($stmt_role !== null) $stmt_role->close();
    if ($stmt_member !== null) $stmt_member->close();
    if ($stmt_delete !== null) $stmt_delete->close();
    if (isset($mysqli)) $mysqli->close();
}

echo json_encode($response);
?>