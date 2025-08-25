<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
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
if ($_SERVER["REQUEST_METHOD"] !== "GET")  {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "僅支援 GET 方法"]);
    exit;
}

$response = [];
$stmt_household = null;
$stmt_members = null;

try {
    // 取得登入者的戶號
    $stmt_household = $mysqli->prepare("SELECT household_no FROM users WHERE user_id = ?");
    
    if ($stmt_household === false) throw new Exception("資料庫準備失敗 (戶號查詢)", 500);

    $stmt_household->bind_param('s', $loggedInUserId);
    $stmt_household->execute();
    $stmt_household->store_result();
    
    if ($stmt_household->num_rows === 0) {
        throw new Exception("找不到登入者資料", 404);
    }
    
    $stmt_household->bind_result($household_no);
    $stmt_household->fetch();

    // 處理使用者沒有戶號的情況
    if (empty($household_no)) {
        echo json_encode(["status" => "success", "data" => []]);
        exit;
    }

    // 查詢同一戶號下的所有其他成員
    $sql_members = "SELECT user_id, fullname, email, phone_number
                    FROM users 
                    WHERE household_no = ? AND user_id != ? AND is_deleted = 0";
    $stmt_members = $mysqli->prepare($sql_members);
    $stmt_members->bind_param('is', $household_no, $loggedInUserId); // household_no 是數字(int), user_id 是字串(string) -> 'is'
    $stmt_members->execute();
    
    $stmt_members->store_result();
    $stmt_members->bind_result($user_id, $fullname, $email, $phone_number);
    
    $familyMembers = [];
    while ($stmt_members->fetch()) {
        $familyMembers[] = [
            'user_id' => $user_id,
            'fullname' => $fullname,
            'email' => $email,
            'phone_number' => $phone_number
        ];
    }
    $response = ["status" => "success", "data" => $familyMembers];

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response = ["status" => "error", "message" => $e->getMessage()];
} finally {
    if (isset($stmt_household)) $stmt_household->close();
    if (isset($stmt_members)) $stmt_members->close();
    if (isset($mysqli)) $mysqli->close();
}

echo json_encode($response);
?>