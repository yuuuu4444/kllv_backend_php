<?php
error_reporting(E_ALL);
ini_set("display_errors",1);

require_once __DIR__ . '/../../common/env_init.php';

// Session 安全性設定
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);

session_set_cookie_params([
    'samesite' => 'Strict'
]);

session_start();

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