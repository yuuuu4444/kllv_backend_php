<?php
// Session 安全性設定
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
session_set_cookie_params([
    'samesite' => 'Strict'
]);

session_start();
header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo json_encode([
        'status' => 'success',
        'data' => [
            'user' => [
                'user_id' => $_SESSION['user_id']
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => '未登入'
    ], JSON_UNESCAPED_UNICODE);
}
?>