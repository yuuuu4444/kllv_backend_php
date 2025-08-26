<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../common/env_init.php';


// 設定與 login_post.php 完全一致的 Session 名稱
session_name('ADMINSESSID');

// Session 安全性設定
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
// ini_set('session.cookie_secure', 1); 


// 啟動 Session
session_start();

// 檢查 Session 中是否有 admin_id，以此作為登入憑證
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401); // 未授權
    echo json_encode(['status' => 'error', 'message' => '後台管理員未登入或憑證無效'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 從 Session 中取得當前登入的管理員 ID 
$loggedInAdminId = $_SESSION['admin_id'];


// 設定 HTTP Header
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    throw new Exception("僅支援 GET 方法", 405);
}

$response = [];
$stmt = null;

try {
    $sql = "SELECT 
                chief_id, 
                fullname, 
                phone_number, 
                email, 
                address, 
                profile_image, 
                introduction 
            FROM village_chief LIMIT 1";
    
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        throw new Exception("資料庫查詢準備失敗: " . $mysqli->error, 500);
    }

    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result(
            $chief_id, 
            $fullname, 
            $phone_number, 
            $email, 
            $address, 
            $profile_image, 
            $introduction
        );
        $stmt->fetch();
        $chief_data = [
            'chief_id'      => $chief_id,
            'fullname'      => $fullname,
            'phone_number'  => $phone_number,
            'email'         => $email,
            'address'       => $address,
            'profile_image' => $profile_image,
            'introduction'  => $introduction
        ];
        $response = ['status' => 'success', 'data' => [$chief_data]]; 
    } else {
        $response = ['status' => 'success', 'data' => []];
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response = ["status" => "error", "message" => $e->getMessage()];
} finally {
    if ($stmt !== null) {
        $stmt->close();
    }
    if (isset($mysqli)) {
        $mysqli->close();
    }
}

echo json_encode($response);
?>