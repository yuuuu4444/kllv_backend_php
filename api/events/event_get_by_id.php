<?php
require_once __DIR__ . '/../../common/env_init.php';

// 獲取event_no
$event_no = filter_input(INPUT_GET, 'event_no', FILTER_VALIDATE_INT);

if (!$event_no) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '未提供有效的活動編號']);
    exit;
}

// 撈一筆資料並撈出所有欄位
$sql = "SELECT e.*, ec.category_name
        FROM events AS e
        LEFT JOIN events_categories AS ec ON e.category_no = ec.category_no
        WHERE e.event_no = ? 
        LIMIT 1";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $event_no);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc(); // 只取一筆fetch_assoc()

if ($data) {
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => '找不到該活動']);
}
?>