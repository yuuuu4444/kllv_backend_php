<?php
    // require_once __DIR__ . '/../../common/cors.php';
    // require_once __DIR__ . '/../../common/conn.php';
    require_once __DIR__ . '/../../common/env_init.php';

    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $repair_no = isset($input['repair_no']) ? intval($input['repair_no']) : 0;

    if (!$repair_no) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'repair_no required']);
        exit;
    }

    $sql = "SELECT r.*, CONCAT('RR', LPAD(repair_no, 5, '0')) AS repair_code, c.category_name
            FROM repair AS r
            JOIN repair_categories AS c
                ON r.category_no = c.category_no
            WHERE r.repair_no = ?
            LIMIT 1";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $repair_no);
    $stmt->execute();
    // $result = $stmt->get_result();
    $stmt->bind_result($repair_no, $location, $category_no, $description, $reporter_id, $reported_at, $reply_content, $resolved_at, $status, $repair_code, $category_name);
    // $detail = $result->fetch_assoc();
    if ($stmt->fetch()) {
        $stmt->close();

        $imgs = [];
        $sqiImg = "SELECT image_no, image_path FROM repair_images WHERE repair_no = ? ORDER BY image_no ASC";
        $stmtImg = $mysqli->prepare($sqiImg);
        $stmtImg->bind_param("i", $repair_no);
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