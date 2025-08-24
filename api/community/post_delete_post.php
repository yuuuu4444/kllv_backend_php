<?php
    require_once __DIR__ . '/../../common/env_init.php';

    header('Content-Type: application/json; charset=utf-8');

    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_set_cookie_params(['samesite' => 'Strict']);
    if (session_status() === PHP_SESSION_NONE) session_start();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status'=>'error','message'=>'Only POST allowed']);
        exit;
    }

    $current_user_id = $_SESSION['user_id'] ?? '';
    if ($current_user_id === '') {
        http_response_code(401);
        echo json_encode(['status'=>'error','message'=>'unauthorized']);
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

    $post_no = isset($input['post_no']) ? intval($input['post_no']) : 0;
    if ($post_no <= 0) {
        http_response_code(422);
        echo json_encode(['status'=>'error','message'=>'post_no required']);
        exit;
    }

    $chk = $mysqli->prepare("SELECT author_id, is_deleted FROM community_posts WHERE post_no = ? LIMIT 1");
    $chk->bind_param('i', $post_no);
    $chk->execute();
    $chk->store_result();

    if ($chk->num_rows === 0) {
        $chk->close();
        http_response_code(404);
        echo json_encode(['status'=>'error','message'=>'post not found']);
        exit;
    }

    $chk->bind_result($author_id, $is_deleted);
    $chk->fetch();
    $chk->close();

    if ($author_id !== $current_user_id) {
        http_response_code(403);
        echo json_encode(['status'=>'error','message'=>'forbidden']);
        exit;
    }

    $sql = "UPDATE community_posts
            SET is_deleted = 1
            WHERE post_no = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $post_no);if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>'update failed']);
        exit;
    }
    $stmt->close();

    echo json_encode(['status'=>'success','data'=>['post_no'=>$post_no,'is_deleted'=>1]]);
?>