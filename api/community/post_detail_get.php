<?php
    require_once __DIR__ . '/../../common/env_init.php';

    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $post_no = isset($input['post_no']) ? intval($input['post_no']) : 0;

    if (!$post_no) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'post_no required']);
        exit;
    }

    $sql = "SELECT p.*, c.category_name, u.fullname AS author_name
            FROM community_posts AS p
            JOIN community_posts_categories AS c
                ON p.category_no = c.category_no
            JOIN users AS u
                ON u.user_id = p.author_id
            WHERE p.post_no = ? AND p.is_deleted = 0
            LIMIT 1";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $post_no);
    $stmt->execute();
    $stmt->bind_result($post_no, $title, $category_no, $image, $content, $author_id, $is_deleted, $posted_at, $updated_at, $category_name, $author_name);

    if ($stmt->fetch()) {
        $stmt->close();

        $imgs = [];
        $sqiImg = "SELECT image_no, image_path FROM community_posts_images WHERE post_no = ? ORDER BY image_no ASC";
        $stmtImg = $mysqli->prepare($sqiImg);
        $stmtImg->bind_param("i", $post_no);
        $stmtImg->execute();
        $stmtImg->bind_result($image_no, $image_path);
        while ($stmtImg->fetch()) {
            $imgs[] = [
                'image_no' => $image_no, 
                'image_path' => $image_path,
            ];
        }
        $stmtImg->close();

        $detail = [
            'post_no' => $post_no,
            'title' => $title,
            'category_no' => $category_no,
            'image' => $image,
            'content' => $content,
            'author_id' => $author_id,
            'author_name' => $author_name,
            'is_deleted' => $is_deleted,
            'posted_at' => $posted_at,
            'updated_at' => $updated_at,
            'category_name' => $category_name,
            'images'        => $imgs,
        ];
        echo json_encode(['status' => 'success', 'data' => $detail], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        $stmt->close();
        echo json_encode(['status' => 'error', 'message' => 'not found']);
        exit;
    }
?>