<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once __DIR__ . '/../../common/env_init.php';

    header('Content-Type: application/json; charset=utf-8');
    session_name('ADMINSESSID');
    session_start();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status'=>'error','message'=>'Only POST allowed']);
        exit;
    }

    /* 把請求內容打到 PHP error log，方便確認實際收到什麼 */
    $raw = file_get_contents('php://input');
    error_log('[reply_save] RAW BODY: ' . $raw);
    error_log('[reply_save] POST: ' . print_r($_POST, true));

    $input = json_decode($raw, true);
    if (!is_array($input) || empty($input)) {
        // 如果不是 JSON，就退回吃表單（multipart/x-www-form-urlencoded 或 FormData）
        $input = $_POST;
    }

    $username = isset($input['username']) ? trim($input['username']) : '';
    $password = isset($input['password']) ? (string)$input['password'] : '';

    if ($username === '' || $password === '') {
        http_response_code(400);
        echo json_encode(['status'=>'error','message'=>'username/password required']);
        exit;
    }

    $sql = "SELECT admin_id, fullname, password, is_active
            FROM admin
            WHERE admin_id = ?
            LIMIT 1";
            
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        http_response_code(401);
        echo json_encode(['status'=>'error','message'=>'帳號不存在或未啟用']);
        $stmt->close();
        exit;
    }
    $stmt->bind_result($admin_id, $fullname, $hashOrPlain, $is_active);
    $stmt->fetch();
    $stmt->close();

    if ((int)$is_active !== 1) {
        http_response_code(401);
        echo json_encode(['status'=>'error','message'=>'帳號不存在或未啟用']); exit;
    }

    $passOK = password_get_info($hashOrPlain)['algo'] ? password_verify($password, $hashOrPlain) : ($password === $hashOrPlain);

    if (!$passOK) {
        http_response_code(401);
        echo json_encode(['status'=>'error','message'=>'密碼錯誤']); exit;
    }

    // 登入成功 → 寫 Session
    $_SESSION['admin_id'] = $admin_id;
    $_SESSION['admin_name'] = $fullname;

    echo json_encode([
        'status'=>'success',
        'data'=>['admin'=>['admin_id'=>$admin_id, 'fullname'=>$fullname]]
    ]);
?>