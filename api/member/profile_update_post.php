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
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "僅支援 PATCH 方法"]);
    exit;
}


// 接收前端傳來的 JSON 資料
$data = json_decode(file_get_contents("php://input"), true);

if (empty($data)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "沒有收到更新資料"]);
    exit;
}

$response = [];
$stmt_update = null;
$stmt_select = null;

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


    $sql_select = "SELECT 
                    u.user_id, 
                    u.fullname, 
                    u.nickname, 
                    u.profile_image, 
                    u.phone_number, 
                    u.email, 
                    u.id_number, 
                    u.birth_date, 
                    u.gender, u.household_no, 
                    u.role_type, 
                    h.address 
                    FROM users u 
                    LEFT JOIN users_households h ON u.household_no = h.household_no
                    WHERE u.user_id = ?";
    
    $stmt_select = $mysqli->prepare($sql_select);
    $stmt_select->bind_param('s', $loggedInUserId);
    $stmt_select->execute();
    
    $stmt_select->store_result();
    $stmt_select->bind_result(
        $user_id, 
        $fullname,
        $nickname, 
        $profile_image, 
        $phone_number, 
        $email, 
        $id_number, 
        $birth_date, 
        $gender, 
        $household_no, 
        $role_type,
        $address
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
        'role_type'     => $role_type,
        'address' => $address
    ];
    
    $response = ["status" => "success", "message" => "個人資料更新成功", "data" => $updatedUser];

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response = ["status" => "error", "message" => $e->getMessage()];
} 
finally {
    // 檢查並關閉所有可能已建立的 statement 物件
    if (isset($stmt_update)) $stmt_update->close();
    if (isset($stmt_select)) $stmt_select->close();
    // 最後關閉資料庫連線
    if (isset($mysqli)) $mysqli->close();
}

echo json_encode($response);
?>