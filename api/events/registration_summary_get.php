<?php
require_once __DIR__ . '/../../common/env_init.php';

// Session 登入檢查
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => '請先登入']);
    exit;
}

// 接收訂單編號reg_no
$reg_no = filter_input(INPUT_GET, 'reg_no', FILTER_VALIDATE_INT);

if (!$reg_no) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '未提供有效的訂單編號']);
    exit;
}

$sql = "SELECT 
            er.reg_no,
            er.p_total,
            er.fee_total,
            e.title AS event_title,
            e.location AS event_location,
            e.start_date AS event_start_date,
            e.end_date AS event_end_date
        FROM events_regs AS er
        LEFT JOIN events AS e ON er.event_no = e.event_no
        WHERE er.reg_no = ? AND er.participant_id = ? 
        LIMIT 1"; // 檢查participant_id確保使用者只能看自己的訂單

$stmt = $mysqli->prepare($sql);
$loggedInUserId = $_SESSION['user_id'];
$stmt->bind_param("is", $reg_no, $loggedInUserId); // i for reg_no, s for user_id
$stmt->execute();
$stmt->bind_result($db_reg_no, $db_p_total, $db_fee_total, $db_event_title, $db_event_location, $db_event_start_date, $db_event_end_date);

$data = null;
if ($stmt->fetch()) {
    $data = [
        'reg_no'            => $db_reg_no,
        'p_total'           => $db_p_total,
        'fee_total'         => $db_fee_total,
        'event_title'       => $db_event_title,
        'event_location'    => $db_event_location,
        'event_start_date'  => $db_event_start_date,
        'event_end_date'    => $db_event_end_date
    ];
}

if ($data) {
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => '找不到該筆訂單，或您無權查看']);
}
?>