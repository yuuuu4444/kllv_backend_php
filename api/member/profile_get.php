<?php

error_reporting(E_ALL);
ini_set("display_errors",1);
require_once __DIR__ . '/../../common/env_init.php';


header('Content-Type: application/json; charset=utf-8');
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "不支援的請求方法"]);
    exit;
}


// 手動建立的假資料
$loggedInUserId = 'user_account_001'; 

// 準備 SQL 查詢 (使用 query) 
$safe_user_id = $mysqli->real_escape_string($loggedInUserId);


$sql = "SELECT 
        u.user_id, u.fullname, u.nickname, u.profile_image, 
        u.phone_number, u.email, u.id_number, u.birth_date, 
        u.gender, u.household_no, h.address 
        
        FROM users u
        LEFT JOIN users_households h ON u.household_no = h.household_no
        WHERE u.user_id = '{$safe_user_id}'
";

// 執行查詢 
$result = $mysqli->query($sql);

// 檢查查詢結果並處理
if ($result) {
    if ($result->num_rows > 0) {
        // fetch_assoc() 直接回傳一個我們需要的關聯陣列
        $user = $result->fetch_assoc();

        // 成功回應
        echo json_encode([
            "status" => "success",
            "data" => $user
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // 查詢成功，但沒有找到符合條件的資料
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "在資料庫中找不到指定的使用者 ID: " . $loggedInUserId
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    // SQL 語法錯誤或其他資料庫錯誤
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "資料庫查詢失敗: " . $mysqli->error
    ], JSON_UNESCAPED_UNICODE);
}

// 關閉資料庫連線
$mysqli->close();

?>