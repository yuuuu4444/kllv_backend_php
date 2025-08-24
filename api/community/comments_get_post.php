<?php
    require_once __DIR__ . '/../../common/env_init.php';

    header('Content-Type: application/json; charset=utf-8');

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
    if ($post_no <= 0) {
        http_response_code(422);
        echo json_encode(['status'=>'error','message'=>'post_no required']);
        exit;
    }

    /* 取總數 */
    $sqlCnt = "SELECT COUNT(*) FROM community_comments WHERE post_no = ? AND is_deleted = 0";
    $stCnt = $mysqli->prepare($sqlCnt);
    $stCnt->bind_param('i', $post_no);
    $stCnt->execute();
    $stCnt->bind_result($total);
    $stCnt->fetch();
    $stCnt->close();

    /* 取清單 */
    $sql = "SELECT c.*, u.fullname AS author_name, u.profile_image
            FROM community_comments AS c
            LEFT JOIN users AS u 
                ON u.user_id = c.author_id
            WHERE c.post_no = ? AND c.is_deleted = 0
            ORDER BY c.commented_at ASC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $post_no);
    $stmt->execute();
    $stmt->bind_result($comments_no, $post_no, $content, $author_id, $is_deleted, $commented_at, $author_name, $profile_image);

    $data = [];
    while ($stmt->fetch()) {
    $data[] = [
            'comments_no' => $comments_no,
            'post_no' => $post_no,
            'content' => $content,
            'author_id' => $author_id,
            'author_name' => $author_name,
            'profile_image' => $profile_image,
            'commented_at' => $commented_at,
            'is_deleted' => $is_deleted,
        ];
    }
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'data'   => ['items' => $data, 'total' => $total]
    ], JSON_UNESCAPED_UNICODE);
?>