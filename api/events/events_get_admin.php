<?php
require_once __DIR__ . '/../../common/env_init.php';

$sql = "SELECT 
            e.*,
            ec.category_name
        FROM events AS e
        LEFT JOIN events_categories AS ec ON e.category_no = ec.category_no
        ORDER BY e.start_date DESC";

$result = $mysqli->query($sql);
$data = [];
if ($result) {
    $data = $result->fetch_all(MYSQLI_ASSOC);
}

// 在json_encode之前將所有欄位的值都強制轉為字串
foreach ($data as &$row) {
    foreach ($row as $key => &$value) { // 第二層也用&引用
        // 只轉換非null的值避免null變成空字串
        if ($value !== null) {
            $value = (string)$value;
        }
    }
}

echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
?>