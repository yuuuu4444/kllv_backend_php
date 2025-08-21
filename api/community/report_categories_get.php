<?php
    error_reporting(E_ALL);
    ini_set("display_errors",1);

    require_once __DIR__ . '/../../common/env_init.php';

    $sql = "SELECT * FROM community_posts_reports_categories
            ORDER BY category_no";

    $result = $mysqli->query($sql);

    if (!$result) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $mysqli->error]);
        exit;
    }

    $data = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $data]);
?>