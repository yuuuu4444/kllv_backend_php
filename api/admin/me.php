<?php
    // 使用者重整頁面時用的
    require_once __DIR__ . '/../../common/env_init.php';

    header('Content-Type: application/json; charset=utf-8');
    session_name('ADMINSESSID');
    session_start();

    if (!isset($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode(['status'=>'error','message'=>'not logged in']); exit;
    }

    echo json_encode([
        'status'=>'success',
        'data'=>['admin'=>[
            'admin_id' => $_SESSION['admin_id'],
            'fullname' => $_SESSION['admin_name'],
        ]]
    ]);
?>