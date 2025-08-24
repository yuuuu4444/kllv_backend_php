<?php
    require_once __DIR__ . '/../../common/env_init.php';

    $sql = "SELECT r.report_no, r.category_no, r.reporter_id, r.reported_at, r.status, c.category_name, cc.content
            FROM community_comments_reports AS r
            JOIN community_posts_reports_categories AS c
                ON r.category_no = c.category_no
            JOIN community_comments AS cc
                ON r.comment_no = cc.comments_no
            ORDER BY r.report_no DESC";
    $stmt = $mysqli->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($report_no, $category_no, $reporter_id, $reported_at, $status, $category_name, $content);

    $data = [];
    while ($stmt->fetch()) {
        $data[] = [
            'report_no' => $report_no,
            'category_no' => $category_no,
            'reporter_id' => $reporter_id,
            'reported_at' => $reported_at,
            'status' => $status,
            'category_name' => $category_name,
            'content' => $content,
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
?>