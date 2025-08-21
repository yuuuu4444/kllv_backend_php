<?php
    require_once __DIR__ . '/../../common/env_init.php';

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
    $category_no = isset($input['category_no']) ? intval($input['category_no']) : 0;
    $reporter_id = isset($input['reporter_id']) ? trim($input['reporter_id']) : '';

    if (!$post_no || !$category_no || $reporter_id === '') {
        http_response_code(400);
        echo json_encode(['status'=>'error','message'=>'post_no, category_no, reporter_id required']);
        exit;
    }

    try {

        // 檢查貼文是否存在
        $stmt = $mysqli->prepare("SELECT 1 FROM community_posts WHERE post_no=? LIMIT 1");
        $stmt->bind_param('i', $post_no);
        $stmt->execute();
        $stmt->bind_result($one);
        $existsPost = $stmt->fetch();
        $stmt->close();
        if (!$existsPost) {
            http_response_code(400);
            echo json_encode(['status'=>'error','message'=>'post_no 不存在']);
            exit;
        }

        // 檢查檢舉分類是否存在
        $stmt = $mysqli->prepare("SELECT 1 FROM community_posts_reports_categories WHERE category_no=? LIMIT 1");
        $stmt->bind_param('i', $category_no);
        $stmt->execute();
        $stmt->bind_result($one);
        $existsCat = $stmt->fetch();
        $stmt->close();
        if (!$existsCat) {
            http_response_code(400);
            echo json_encode(['status'=>'error','message'=>'category_no 不存在']);
            exit;
        }

        // 檢查檢舉人是否存在（FK: users.user_id）
        $stmt = $mysqli->prepare("SELECT 1 FROM users WHERE user_id = ? LIMIT 1");
        $stmt->bind_param('s', $reporter_id);
        $stmt->execute();
        $stmt->bind_result($one);
        $existsUser = $stmt->fetch();
        $stmt->close();
        if (!$existsUser) {
            http_response_code(400);
            echo json_encode(['status'=>'error','message'=>'reporter_id 不存在']);
            exit;
        }

        $sql = "INSERT INTO community_posts_reports(post_no, category_no, reporter_id, reported_at, status)
                VALUES (?, ?, ?, NOW(), 0)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('iis', $post_no, $category_no, $reporter_id);
        $stmt->execute();
        $report_no = $mysqli->insert_id;
        $stmt->close();

        echo json_encode([
            'status'=>'success',
            'data'=>[
                'report_no'=> $report_no,
                'post_no' => $post_no,
                'category_no' => $category_no,
                'reporter_id' => $reporter_id,
                'status' => 0,
            ]
        ], JSON_UNESCAPED_UNICODE);

    } catch (Throwable $e) {
        $mysqli->rollback();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
?>