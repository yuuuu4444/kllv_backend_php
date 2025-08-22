<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once __DIR__ . '/../../common/env_init.php';

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER["REQUEST_METHOD"] !== "GET")  {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "僅支援 GET 方法"]);
    exit;
}

// 手動建立的假資料 (未來從 Session 或 Token 取得)
$loggedInUserId = 'user_account_001';

try {
    // 取得登入者的戶號
    $stmt_household = $mysqli->prepare("SELECT household_no FROM users WHERE user_id = ?");
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
    echo json_encode(["status" => "success", "data" => $familyMembers]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
} finally {
    if (isset($stmt_household)) $stmt_household->close();
    if (isset($stmt_members)) $stmt_members->close();
    if (isset($mysqli)) $mysqli->close();
}
?>