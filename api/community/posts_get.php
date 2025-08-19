<?php
    require_once __DIR__ . '/../../common/env_init.php';

    $sql = "SELECT p.*, c.category_name
            FROM community_posts AS p
            JOIN community_posts_categories AS c
                ON p.category_no = c.category_no
            ORDER BY post_no DESC";
    
    $result = $mysqli->query($sql);

    if (!$result) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $mysqli->error]);
        exit;
    }

    $data = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $data]);
?>