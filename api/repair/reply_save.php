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

    $repair_no = isset($input['repair_no']) ? intval($input['repair_no']) : 0;
    $reply_content = isset($input['reply_content']) ? trim($input['reply_content']) : '';
    $status = isset($input['status']) ? intval($input['status']) : null;
    
    if ($repair_no <= 0) {
        http_response_code(422);
        echo json_encode(['status'=>'error','message'=>'repair_no required']);
        exit;
    }
    if (!in_array($status, [0,2,3], true)) {
        http_response_code(422);
        echo json_encode(['status'=>'error','message'=>'invalid status']);
        exit;
    }
    if ($status === 2 && $reply_content === '') {
        http_response_code(422);
        echo json_encode(['status'=>'error','message'=>'reply_content required for status=2']);
        exit;
    }

    // 檢查是否又相對應的repair_no
    $chk = $mysqli->prepare('SELECT repair_no FROM repair WHERE repair_no = ? LIMIT 1');
    $chk->bind_param('i', $repair_no);
    $chk->execute();
    $chk->store_result(); // 儲存結果
    if ($chk->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['status'=>'error','message'=>'repair not found']);
        exit;
    }
    $chk->close();

    if ($status === 2) {
        $sql = 'UPDATE repair 
                SET reply_content = ?, status = 2, resolved_at = NOW() 
                WHERE repair_no = ?';

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('si', $reply_content, $repair_no);
    } elseif ($status === 3) {
        $sql = 'UPDATE repair
                SET status = 3, resolved_at = NOW()
                WHERE repair_no = ?';
        
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $repair_no);
    } else {
        $sql = 'UPDATE repair 
                SET status = ? 
                WHERE repair_no = ?';

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ii', $status, $repair_no);
    }

    $stmt->execute();

    echo json_encode(['status'=>'success']);
?>