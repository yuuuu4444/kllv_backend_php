<?php
error_reporting(E_ALL);
ini_set("display_errors",1);
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
if ($_SERVER['REQUEST_METHOD'] !== 'POST')  {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "僅支援 POST 方法"]);
    exit;
}


$response = [];

try {
    //  檔案上傳的初步驗證
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("沒有收到檔案或上傳失敗", 400);
    }
    $file = $_FILES['avatar'];

    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_mime_types)) {
        throw new Exception("不支援的檔案格式", 400);
    }
    // (可選) 加上檔案大小的檢查
    // if ($file['size'] > 2097152) { // 2MB
    //     throw new Exception("檔案大小不可超過 2MB", 400);
    // }

    // 準備儲存路徑和檔名
    $upload_dir_absolute = __DIR__ . '/../../uploads/avatars/';
    $upload_dir_relative = '/uploads/avatars/';

    // 確保上傳目錄存在且可寫
    if (!is_dir($upload_dir_absolute) || !is_writable($upload_dir_absolute)) {
        throw new Exception("伺服器端上傳目錄錯誤或不可寫", 500);
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    // 使用從 Session 獲取的 $loggedInUserId 來建立檔名
    $new_filename = $loggedInUserId . '_' . time() . '.' . $file_extension;

    $destination_absolute = $upload_dir_absolute . $new_filename;
    $public_url = $upload_dir_relative . $new_filename;

    // 移動檔案
    if (move_uploaded_file($file['tmp_name'], $destination_absolute)) {
        $response = [
            "status" => "success",
            "message" => "大頭貼上傳成功",
            "data" => [
                "profile_image_url" => $public_url 
            ]
        ];
    } else {
        throw new Exception("儲存檔案失敗，請檢查伺服器權限", 500);
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response = ["status" => "error", "message" => $e->getMessage()];
}


echo json_encode($response);
?>