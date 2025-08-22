<?php
    require_once __DIR__ . '/../../common/env_init.php';

    // 從URL參數安全獲取event_no
    $event_no = filter_input(INPUT_GET, 'event_no', FILTER_VALIDATE_INT);

    // 檢查event_no是否有效
    if (!$event_no) {
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => '未提供有效的活動編號 (event_no)']);
        exit;
    }

    $sql = "SELECT 
                er.reg_no,
                u.fullname,
                er.p_total,
                er.fee_total,
                er.registered_at,
                erp.payment_name,
                er.status,
                e.title AS event_title -- 活動標題
            FROM events_regs AS er
            LEFT JOIN users AS u ON er.participant_id = u.user_id
            LEFT JOIN events_regs_payments AS erp ON er.payment_no = erp.payment_no
            LEFT JOIN events AS e ON er.event_no = e.event_no
            WHERE er.event_no = ?
            ORDER BY er.registered_at DESC";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $event_no);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    if ($result) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
    }

    $event_title = empty($data) ? '未知活動' : ($data[0]['event_title'] ?? '標題未找到');

    echo json_encode([
        'status' => 'success', 
        'event_title' => $event_title,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
?>