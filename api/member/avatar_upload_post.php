<?php
error_reporting(E_ALL);
ini_set("display_errors",1);
require_once __DIR__ . '/../../common/env_init.php';


header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST')  {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "僅支援 POST 方法"]);
    exit;
}


// 手動建立的假資料
$loggedInUserId = 'user_account_001';

// 檢查是否有檔案被上傳
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "沒有收到檔案或上傳失敗"]);
    exit;
}

$file = $_FILES['avatar'];

// 檔案驗證
$allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($file['type'], $allowed_mime_types)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "不支援的檔案格式"]);
    exit;
}

// 準備儲存路徑和檔名
// 定義上傳資料夾的“絕對路徑”
$upload_dir_absolute = __DIR__ . '/../../uploads/avatars/';
// 定義上傳資料夾的“相對路徑”
$upload_dir_relative = '/uploads/avatars/';

// 建立一個唯一的新檔名，避免覆蓋
$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$new_filename = $loggedInUserId . '_' . time() . '.' . $file_extension;

$destination_absolute = $upload_dir_absolute . $new_filename;
$public_url = $upload_dir_relative . $new_filename;

// 移動檔案
if (move_uploaded_file($file['tmp_name'], $destination_absolute)) {
    // 移動成功，回傳新的公開 URL
    echo json_encode([
        "status" => "success",
        "message" => "大頭貼上傳成功",
        "data" => [
            "profile_image_url" => $public_url 
        ]
    ]);
} else {
    // 移動失敗
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "儲存檔案失敗"]);
}
?>