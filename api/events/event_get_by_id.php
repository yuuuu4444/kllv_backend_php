<?php
require_once __DIR__ . '/../../common/env_init.php';

// 獲取event_no
$event_no = filter_input(INPUT_GET, 'event_no', FILTER_VALIDATE_INT);

if (!$event_no) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '未提供有效的活動編號']);
    exit;
}

$sql = "SELECT 
            e.event_no, e.title, e.location, e.category_no, e.image, 
            e.description, e.fee_per_person, e.p_limit, e.start_date, 
            e.end_date, e.reg_deadline, e.created_at, e.status,
            ec.category_name
        FROM events AS e
        LEFT JOIN events_categories AS ec ON e.category_no = ec.category_no
        WHERE e.event_no = ? 
        LIMIT 1";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $event_no);
$stmt->execute();

$stmt->bind_result(
    $db_event_no, $db_title, $db_location, $db_category_no, $db_image,
    $db_description, $db_fee_per_person, $db_p_limit, $db_start_date,
    $db_end_date, $db_reg_deadline, $db_created_at, $db_status,
    $db_category_name
);

$data = null;
if ($stmt->fetch()) {
    $data = [
        'event_no'        => $db_event_no,
        'title'           => $db_title,
        'location'        => $db_location,
        'category_no'     => $db_category_no,
        'image'           => $db_image,
        'description'     => $db_description,
        'fee_per_person'  => $db_fee_per_person,
        'p_limit'         => $db_p_limit,
        'start_date'      => $db_start_date,
        'end_date'        => $db_end_date,
        'reg_deadline'    => $db_reg_deadline,
        'created_at'      => $db_created_at,
        'status'          => $db_status,
        'category_name'   => $db_category_name
    ];
}

if ($data) {
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => '找不到該活動']);
}
?>