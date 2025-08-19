<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once __DIR__ . '/../../common/env_init.php';

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "不支援的請求方法"]);
    exit;
}


// 手動建立的假資料
$loggedInUserId = 'user_account_001';
$safe_loggedInUserId = $mysqli->real_escape_string($loggedInUserId);

// 取得登入者的戶號 (household_no)
$sql_get_household = "SELECT household_no 
                      FROM users 
                      WHERE user_id = '{$safe_loggedInUserId}'";
$result_household = $mysqli->query($sql_get_household);

if (!$result_household || $result_household->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "找不到登入者資料或戶號"]);
    exit;
}

$user = $result_household->fetch_assoc();
$household_no = $user['household_no'];

if (empty($household_no)) {
    // 如果使用者沒有戶號，就回傳一個空陣列
    echo json_encode(["status" => "success", "data" => []]);
    exit;
}

$safe_household_no = $mysqli->real_escape_string($household_no);

// 查詢同一戶號下的所有其他成員
$sql_get_members = "SELECT  user_id , 
                            fullname , 
                            email, 
                            phone_number
                    FROM users 
                    WHERE household_no = '{$safe_household_no}' AND user_id != '{$safe_loggedInUserId}' AND is_deleted = 0
";

$result_members = $mysqli->query($sql_get_members);

if ($result_members) {
    $familyMembers = [];
    // 將查詢結果逐筆存入陣列
    while ($row = $result_members->fetch_assoc()) {
        $familyMembers[] = $row;
    }
    
    echo json_encode([
        "status" => "success",
        "data" => $familyMembers
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "查詢家庭成員失敗: " . $mysqli->error
    ]);
}

$mysqli->close();
?>