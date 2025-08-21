<?php
error_reporting(E_ALL);
ini_set("display_errors",1);

require_once __DIR__ . '/../../common/env_init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status'=>'error','message'=>'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 取得POST資料
$data = json_decode(file_get_contents("php://input"), true);
$user_id = trim($data['user_id'] ?? '');
$password = $data['password'] ?? '';

if (!$user_id || !$password) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'帳號密碼必填'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 查詢用戶
$sql = "SELECT password, is_active, is_deleted FROM users WHERE user_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('s', $user_id);
$stmt->execute();
$stmt->bind_result($db_password, $is_active, $is_deleted);

if ($stmt->fetch()) {
    if ($is_deleted) {
        http_response_code(403);
        echo json_encode(['status'=>'error','message'=>'帳號已停用'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (!password_verify($password, $db_password)) {
        http_response_code(401);
        echo json_encode(['status'=>'error','message'=>'帳號或密碼錯誤'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (!$is_active) {
        http_response_code(403);
        echo json_encode(['status'=>'error','message'=>'帳號尚未啟用'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    // 成功登入回傳token...
    echo json_encode(['status'=>'success','message'=>'登入成功','data'=>['user_id'=>$user_id]], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(401);
    echo json_encode(['status'=>'error','message'=>'帳號或密碼錯誤'], JSON_UNESCAPED_UNICODE);
}
?>