<?php
error_reporting(E_ALL);
ini_set("display_errors",1);

// Session 安全性設定
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
session_set_cookie_params([
    'samesite' => 'Strict'
]);

session_start();

require_once __DIR__ . '/../../common/env_init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status'=>'error','message'=>'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 取得POST資料
$data = json_decode(file_get_contents("php://input"), true);
// 參數檢查略...（建議照前面詳細版本做欄位驗證）

$user_id = trim($data['user_id'] ?? '');
// 其他欄位...

// 1. 檢查帳號重複
$stmt = $mysqli->prepare("SELECT user_id FROM users WHERE user_id = ?");
$stmt->bind_param('s', $user_id);
$stmt->execute();
$stmt->bind_result($tmp_id);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['status'=>'error','message'=>'帳號已存在'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. 寫入 users 或 users_households...
// ...（以下略）

echo json_encode(['status'=>'success','message'=>'註冊成功'], JSON_UNESCAPED_UNICODE);
?>