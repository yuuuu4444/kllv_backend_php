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
if ($_SERVER["REQUEST_METHOD"] !== "POST") { 
    throw new Exception("僅支援 POST 方法", 405);
}

$data = json_decode(file_get_contents("php://input"), true);
if (empty($data) || empty($data['chief_id'])) {
    throw new Exception("缺少更新資料或里長 ID", 400);
}

$response = [];
$stmt = null;

try {
    $chiefId = (int)$data['chief_id'];

    $sql_parts = [];
    $params = [];
    $types = '';
    
    $field_map = [
        'fullname'     => 'fullname',
        'introduction' => 'introduction',
        'phone_number' => 'phone_number',
        'email'        => 'email',
        'address'      => 'address'
    ];

    foreach ($field_map as $front_key => $db_column) {
        if (isset($data[$front_key])) {
            $sql_parts[] = "`{$db_column}` = ?";
            $params[] = $data[$front_key];
            $types .= 's'; // 假設都是字串
        }
    }

    if (empty($sql_parts)) {
        throw new Exception("沒有需要更新的欄位", 400);
    }

    // 將 chiefId 加入到參數列表的最後，用於 WHERE 條件
    $params[] = $chiefId;
    $types .= 'i';

    $sql = "UPDATE village_chief SET " . implode(', ', $sql_parts) . " WHERE chief_id = ?";
    
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) throw new Exception("資料庫更新準備失敗: " . $mysqli->error, 500);
    
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        if ($mysqli->affected_rows > 0) {
            $response = ['status' => 'success', 'message' => '里長資料更新成功'];
        } else {
            $response = ['status' => 'success', 'message' => '資料無變化'];
        }
    } else {
        throw new Exception("資料庫更新失敗: " . $stmt->error, 500);
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response = ["status" => "error", "message" => $e->getMessage()];
} finally {
    if ($stmt !== null) $stmt->close();
    if (isset($mysqli)) $mysqli->close();
}

echo json_encode($response);
?>