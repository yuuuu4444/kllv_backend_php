<?php
    require_once __DIR__ . '/../../common/env_init.php';

    $sql = "SELECT r.*, CONCAT('RR', LPAD(repair_no, 5, '0')) AS repair_code, c.category_name
            FROM repair AS r
            JOIN repair_categories AS c
                ON r.category_no = c.category_no
            ORDER BY repair_no DESC";

    $stmt = $mysqli->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($repair_no, $location, $category_no, $description, $reporter_id, $reported_at, $reply_content, $resolved_at, $status, $repair_code, $category_name);
    $data = [];
    while ($stmt->fetch()) {
        $data[] = [
            'repair_no' => $repair_no,
            'location' => $location,
            'category_no' => $category_no,
            'description' => $description,
            'reporter_id' => $reporter_id,
            'reported_at' => $reported_at,
            'reply_content' => $reply_content,
            'resolved_at' => $resolved_at,
            'status' => $status,
            'repair_code' => $repair_code,
            'category_name' => $category_name,
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
?>