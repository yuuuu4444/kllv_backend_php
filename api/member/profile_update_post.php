<?php
error_reporting(E_ALL);
ini_set("display_errors",1);
require_once __DIR__ . '/../../common/env_init.php';


// 用 POST 來模擬 PATCH
// 因為有些環境不支援 PATCH，且 form-data 只能用 POST
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "不支援的請求方法"]);
    exit;
}



// 手動建立的假資料
$loggedInUserId = 'user_account_001'; 

// 接收前端傳來的 JSON 資料
$data = json_decode(file_get_contents("php://input"), true);

// 資料驗證
if (empty($data)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "沒有收到更新資料"]);
    exit;
}

// 核心邏輯：動態建立 UPDATE SQL
$sql_parts = [];
$allowed_fields = ['nickname', 'phone_number', 'gender', 'profile_image'];

foreach ($data as $key => $value) {
    if (in_array($key, $allowed_fields)) {
        //使用 query 需要手動凈化每個值
        $escaped_value = $mysqli->real_escape_string($value);
        $sql_parts[] = "`{$key}` = '{$escaped_value}'";
    }
}

if (empty($sql_parts)) {
    echo json_encode(["status" => "success", "message" => "沒有需要更新的欄位", "data" => null]);
    exit;
}

// 組合最終的 SQL 語句
$safe_user_id = $mysqli->real_escape_string($loggedInUserId);
$sql = "UPDATE `users` 
        SET " . implode(', ', $sql_parts) . " WHERE `user_id` = '{$safe_user_id}'";

// 執行更新 (使用 query)
if ($mysqli->query($sql) === TRUE) {
    // 成功回應
    // 為了讓前端的狀態能完全同步，成功後我們應該回傳更新後的完整使用者資料
    $sql_select = "
        SELECT 
            u.user_id, u.fullname, u.nickname, u.profile_image, 
            u.phone_number, u.email, u.id_number, u.birth_date, 
            u.gender, u.household_no, h.address 
        FROM users u
        LEFT JOIN users_households h ON u.household_no = h.household_no
        WHERE u.user_id = '{$safe_user_id}'
    ";
    
    $result = $mysqli->query($sql_select);
    $updatedUser = $result->fetch_assoc(); // 直接獲取更新後的資料

    echo json_encode([
        "status" => "success", 
        "message" => "個人資料更新成功",
        "data" => $updatedUser
    ]);

} else {
    // 失敗回應
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "資料庫更新失敗: " . $mysqli->error]);
}

$mysqli->close();

?>
