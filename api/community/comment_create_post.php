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
    $content = isset($input['content']) ? trim($input['content']) : '';
    $author_id = $_SESSION['user_id'] ?? '';

    if ($author_id === '') {
        http_response_code(401);
        echo json_encode(['status'=>'error','message'=>'unauthorized']);
        exit;
    }
    if ($post_no <= 0) {
        http_response_code(422);
        echo json_encode(['status'=>'error','message'=>'post_no required']);
        exit;
    }
    if ($content === '') {
        http_response_code(422);
        echo json_encode(['status'=>'error','message'=>'留言內容不可空白']);
        exit;
    }

    $chkPost = "SELECT is_deleted FROM community_posts WHERE post_no = ? LIMIT 1";
    $StmtPost = $mysqli->prepare($chkPost);
    $StmtPost->bind_param('i', $post_no);
    $StmtPost->execute();
    $StmtPost->bind_result($post_deleted);
    $found = $StmtPost->fetch();
    $StmtPost->close();

    if (!$found) {
        http_response_code(404);
        echo json_encode(['status'=>'error','message'=>'貼文不存在']);
        exit;
    }
    if (intval($post_deleted) === 1) {
        http_response_code(409);
        echo json_encode(['status'=>'error','message'=>'此貼文已下架，無法留言']);
        exit;
    }

    $sql = "INSERT INTO community_comments(post_no, author_id, content, is_deleted, commented_at)
            VALUES(?, ?, ?, 0, NOW())";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("iss", $post_no, $author_id, $content);
    $ok = $stmt->execute();

    if (!$ok) {
        error_log('[comment_create] DB ERROR: ' . $stmt->error);
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>'留言建立失敗']);
        exit;
    }

    $comments_no = $stmt->insert_id;
    $stmt->close();

    $userSql = "SELECT fullname, profile_image FROM users WHERE user_id = ? LIMIT 1";
    $userStmt = $mysqli->prepare($userSql);
    $userStmt->bind_param('s', $author_id);
    $userStmt->execute();
    $userStmt->bind_result($fullname, $profile_image);
    $userStmt->fetch();
    $userStmt->close();

    echo json_encode([
        'status' => 'success',
        'data' => [
            'comment_no' => intval($comments_no),
            'post_no' => $post_no,
            'author_id' => $author_id,
            'author_name' => $fullname,
            'profile_image' => $profile_image,
            'content' => $content,
            'is_deleted' => 0
        ]
    ]);
?>