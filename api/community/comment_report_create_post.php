<?php
    require_once __DIR__ . '/../../common/env_init.php';

    header('Content-Type: application/json; charset=utf-8');

    /* 開 session（讓 PHP 讀到登入者） */
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

    $comment_no = isset($input['comment_no']) ? intval($input['comment_no']) : 0;
    $category_no = isset($input['category_no']) ? intval($input['category_no']) : 0;
    $current_user_id = $_SESSION['user_id'] ?? '';

    if ($current_user_id === '') {
        http_response_code(401);
        echo json_encode(['status'=>'error','message'=>'unauthorized']);
        exit;
    }
    if ($comment_no <= 0 || $category_no <= 0) {
        http_response_code(422);
        echo json_encode(['status'=>'error','message'=>'comment_no 與 category_no 為必填']);
        exit;
    }

    try {
        $mysqli->begin_transaction();

        $stmt = $mysqli->prepare("SELECT comments_no, author_id FROM community_comments WHERE comments_no = ? LIMIT 1");
        $stmt->bind_param('i', $comment_no);
        $stmt->execute();
        $stmt->bind_result($c_no, $author_id);
        if ($stmt->fetch()) {
            $comment = ['comment_no' => $c_no, 'author_id' => $author_id];
        } else {
            $comment = null;
        }
        $stmt->close();

        if (!$comment) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => '找不到留言']);
            exit;
        }
        if ($comment['author_id'] === $current_user_id) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => '不可檢舉自己的留言']);
            exit;
        }

        $sql = "SELECT report_no 
                FROM community_comments_reports
                WHERE comment_no = ? AND reporter_id = ? AND status IN (0) 
                LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("is", $comment_no, $current_user_id);
        $stmt->execute();
        $stmt->bind_result($existing_report_no);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => '您已檢舉過此留言，待審中'], JSON_UNESCAPED_UNICODE);
            $stmt->close();
            exit;
        }
        $stmt->close();

        $sql = "INSERT INTO community_comments_reports(comment_no, category_no, reporter_id, reported_at, status)
                VALUES(?, ?, ?, NOW(), 0)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('iis', $comment_no, $category_no, $current_user_id);
        $stmt->execute();
        $report_no = $mysqli->insert_id;
        $stmt->close();

        $mysqli->commit();

        echo json_encode([
            'status'  => 'success',
            'message' => '檢舉已送出',
            'data'    => [
                'report_no'   => $report_no,
                'comment_no'  => $comment_no,
                'category_no' => $category_no,
                'reporter_id' => $current_user_id,
                'status'      => 0,
            ],
        ], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        $mysqli->rollback();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
?>