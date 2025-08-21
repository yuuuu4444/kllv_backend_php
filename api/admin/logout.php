<?php
    require_once __DIR__ . '/../../common/env_init.php';

    header('Content-Type: application/json; charset=utf-8');
    session_name('ADMINSESSID');
    session_start();

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();

    echo json_encode(['status'=>'success']);
?>