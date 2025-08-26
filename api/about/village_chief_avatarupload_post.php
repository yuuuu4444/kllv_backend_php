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


header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "僅支援 POST 方法"]);
    exit;
}


try {
    // 接收上傳的圖片檔案 
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "沒有收到圖片檔案或上傳失敗"]);
        exit;
    }

    $file = $_FILES['file']; // Element Plus 預設的檔案 key 是 'file'

    // 檔案驗證 (大小, 類型)
    $allowed_mime_types = ['image/jpeg', 'image/png'];
    if (!in_array($file['type'], $allowed_mime_types)) {
        throw new Exception("不支援的圖片格式，僅限 JPG 和 PNG", 400);
    }

    // 設定儲存路徑和檔名
    $upload_dir_absolute = __DIR__ . '/../../uploads/villagechief/';
    $upload_dir_relative = '/uploads/villagechief/';

    // 將檔名固定，但副檔名來自使用者上傳的檔案
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = 'villagechief.' . $file_extension;
    
    $destination_absolute = $upload_dir_absolute . $new_filename;
    $public_url = $upload_dir_relative . $new_filename;

    // 檢查並建立資料夾 (一個好的保險措施)
    if (!is_dir($upload_dir_absolute)) {
        if (!mkdir($upload_dir_absolute, 0775, true)) { // 使用 0775 權限通常更安全
            throw new Exception("無法建立上傳資料夾", 500);
        }
    }

    // 移動並覆蓋檔案
    if (!move_uploaded_file($file['tmp_name'], $destination_absolute)) {
        throw new Exception("儲存圖片檔案失敗，請檢查資料夾權限", 500);
    }

    // 圖片移動成功後，立刻更新資料庫
    $sql = "UPDATE village_chief 
                SET profile_image = ? 
                WHERE chief_id = (SELECT chief_id FROM (SELECT MIN(chief_id) AS chief_id FROM village_chief) AS t)";
    
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        throw new Exception("資料庫準備失敗: " . $mysqli->error, 500);
    }
    
    $stmt->bind_param('s', $public_url);

    if (!$stmt->execute()) {
        throw new Exception("更新圖片路徑到資料庫失敗: " . $stmt->error, 500);
    }
    
    $response = [
        "status" => "success",
        "message" => "圖片上傳並更新成功",
        "data" => ["url" => $public_url]
    ];

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