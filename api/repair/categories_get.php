<?php
    require_once __DIR__ . '/../../common/cors.php';
    require_once __DIR__ . '/../../common/conn.php';

    $sql = "SELECT * FROM repair_categories
            ORDER BY category_no";
    $stmt = $mysqli->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $categories]);
?>