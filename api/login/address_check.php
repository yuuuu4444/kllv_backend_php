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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status'=>'error','message'=>'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_GET['address'])) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'缺少 address 參數'], JSON_UNESCAPED_UNICODE);
    exit;
}

$address = trim($_GET['address']);

$sql = "SELECT household_no FROM users_households WHERE address = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('s', $address);
$stmt->execute();
$stmt->bind_result($household_no);

$exists = false;
if ($stmt->fetch()) {
    $exists = true;
}

echo json_encode(['status'=>'success','data'=>['exists'=>$exists]], JSON_UNESCAPED_UNICODE);
?>