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

    $report_no = isset($input['report_no']) ? intval($input['report_no']) : 0;
    $status = isset($input['status']) ? intval($input['status']) : null;

    if ($report_no <= 0) {
        http_response_code(422);
        echo json_encode(['status'=>'error','message'=>'report_no required']);
        exit;
    }
    if (!in_array($status, [0,2,3], true)) {
        http_response_code(422);
        echo json_encode(['status'=>'error','message'=>'invalid status']);
        exit;
    }

    $chk = $mysqli->prepare('SELECT comment_no FROM community_comments_reports WHERE report_no = ? LIMIT 1');
    $chk->bind_param('i', $report_no);
    $chk->execute();
    $chk->store_result();

    if ($chk->num_rows === 0) {
        $chk->close();
        http_response_code(404);
        echo json_encode(['status'=>'error','message'=>'report not found']);
        exit;
    }

    $chk->bind_result($comment_no);
    $chk->fetch();
    $chk->close();

    try {
        $mysqli->begin_transaction();

        $stmt = $mysqli->prepare('UPDATE community_comments_reports SET status = ? WHERE report_no = ?');
        $stmt->bind_param('ii', $status, $report_no);
        if (!$stmt->execute()) {
            throw new Exception('update report status failed');
        }
        $stmt->close();

        if ($status === 2) {
            $is_deleted = 1;
        } elseif ($status === 3) {
            $is_deleted = 0;
        } else {
            $is_deleted = 0;
        }

        $stmt2 = $mysqli->prepare('UPDATE community_comments SET is_deleted = ? WHERE comments_no = ?');
        $stmt2->bind_param('ii', $is_deleted, $comment_no);
        if (!$stmt2->execute()) {
            throw new Exception('update comment visibility failed');
        }
        $stmt2->close();

        $mysqli->commit();

        echo json_encode([
            'status' => 'success',
            'data' => [
            'report_no' => $report_no,
            'comments_no'   => $comment_no,
            'status'    => $status,
            'is_deleted' => $is_deleted,
            ],
        ]);
    } catch (Throwable $e) {
        $mysqli->rollback();
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }
?>