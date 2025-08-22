<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once __DIR__ . '/../../common/env_init.php';

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER["REQUEST_METHOD"] !== "PATCH") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "僅支援 PATCH 方法"]);
    exit;
}

// 手動建立的假資料 (未來從 Session 或 Token 取得)
$loggedInUserId = 'user_account_001'; 

// 接收前端傳來的 JSON 資料
$data = json_decode(file_get_contents("php://input"), true);

if (empty($data)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "沒有收到更新資料"]);
    exit;
}


try {
    $sql_parts = [];
    $params = [];
    $types = '';
    $allowed_fields = ['nickname', 'phone_number', 'gender', 'profile_image'];

    foreach ($data as $key => $value) {
        if (in_array($key, $allowed_fields)) {
            $sql_parts[] = "`{$key}` = ?";
            $params[] = $value;
            $types .= 's';
        }
    }

    if (empty($sql_parts)) {
        echo json_encode(["status" => "success", "message" => "沒有需要更新的欄位", "data" => null]);
        exit;
    }

    $params[] = $loggedInUserId;
    $types .= 's';
    $sql = "UPDATE `users` SET " . implode(', ', $sql_parts) . " WHERE `user_id` = ?";
    
    $stmt_update = $mysqli->prepare($sql);
    if ($stmt_update === false) throw new Exception("資料庫更新準備失敗: " . $mysqli->error, 500);
    
    $stmt_update->bind_param($types, ...$params);
    if (!$stmt_update->execute()) throw new Exception("資料庫更新失敗: " . $stmt_update->error, 500);
    $stmt_update->close();


    $sql_select = "SELECT u.user_id, u.fullname, u.nickname, u.profile_image, u.phone_number, u.email, u.id_number, u.birth_date, u.gender, u.household_no, h.address 
                    FROM users u 
                    LEFT JOIN users_households h ON u.household_no = h.household_no
                    WHERE u.user_id = ?";
    
    $stmt_select = $mysqli->prepare($sql_select);
    $stmt_select->bind_param('s', $loggedInUserId);
    $stmt_select->execute();
    
    $stmt_select->store_result();
    $stmt_select->bind_result(
        $user_id, $fullname, $nickname, $profile_image, $phone_number, $email, 
        $id_number, $birth_date, $gender, $household_no, $address
    );
    $stmt_select->fetch();
    
    $updatedUser = [
        'user_id' => $user_id, 
        'fullname' => $fullname, 
        'nickname' => $nickname,
        'profile_image' => $profile_image, 
        'phone_number' => $phone_number,
        'email' => $email, 
        'id_number' => $id_number, 
        'birth_date' => $birth_date,
        'gender' => $gender, 
        'household_no' => $household_no, 
        'address' => $address
    ];
    
    echo json_encode(["status" => "success", "message" => "個人資料更新成功", "data" => $updatedUser]);

} catch (Exception $e) { /* ... */ } 
finally {
    // 檢查並關閉所有可能已建立的 statement 物件
    if (isset($stmt_update)) $stmt_update->close();
    if (isset($stmt_select)) $stmt_select->close();
    // 最後關閉資料庫連線
    if (isset($mysqli)) $mysqli->close();
}
?>