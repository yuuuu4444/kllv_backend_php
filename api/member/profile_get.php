<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once __DIR__ . '/../../common/env_init.php';

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "僅支援 GET 方法"]);
    exit;
}

// 假設的登入者ID (未來從 Session 或 Token 取得)
$loggedInUserId = 'user_account_001';

try {
    $sql = "SELECT 
            u.user_id, u.fullname, u.nickname, u.profile_image, 
            u.phone_number, u.email, u.id_number, u.birth_date, 
            u.gender, u.household_no, h.address 
        FROM users u
        LEFT JOIN users_households h ON u.household_no = h.household_no
        WHERE u.user_id = ?";

    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) throw new Exception("資料庫查詢準備失敗: " . $mysqli->error, 500);

    $stmt->bind_param('s', $loggedInUserId);
    $stmt->execute();
    
    // 將查詢結果從資料庫傳輸到 PHP
    $stmt->store_result();
    
    // 檢查是否有找到資料
    if ($stmt->num_rows === 0) {
        throw new Exception("在資料庫中找不到指定的使用者 ID: " . $loggedInUserId, 404);
    }
    
    // 根據 SELECT 的欄位順序，建立對應的 PHP 變數並綁定
    $stmt->bind_result(
        $user_id, $fullname, $nickname, $profile_image, 
        $phone_number, $email, $id_number, $birth_date, 
        $gender, $household_no, $address
    );
    
    // 將綁定的變數填入值
    $stmt->fetch();
    
    // 手動將變數組合成一個關聯陣列
    $user = [
        'user_id'       => $user_id,
        'fullname'      => $fullname,
        'nickname'      => $nickname,
        'profile_image' => $profile_image,
        'phone_number'  => $phone_number,
        'email'         => $email,
        'id_number'     => $id_number,
        'birth_date'    => $birth_date,
        'gender'        => $gender,
        'household_no'  => $household_no,
        'address'       => $address
    ];

    echo json_encode(["status" => "success", "data" => $user]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($mysqli)) $mysqli->close();
}
?>