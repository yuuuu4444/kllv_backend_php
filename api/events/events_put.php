<?php
    require_once __DIR__ . '/../../common/env_init.php';

    if ($_SERVER["REQUEST_METHOD"] == "PUT"){

        $event_no = filter_input(INPUT_GET, 'event_no', FILTER_VALIDATE_INT);

        // 用來解析x-www-form-urlencoded格式
        parse_str(file_get_contents("php://input"), $put_vars);

        if (!$event_no) {
            http_response_code(400);
            echo json_encode(['status'=>'error','message'=>'未提供有效的 event_no']);
            exit;
        }

        // 從解析出來的 $put_vars 陣列中取值 (使用 ?? null 來避免 Undefined key 警告)
        $title          = trim($put_vars['title'] ?? null);
        $category_no    = filter_var($put_vars['category'] ?? null, FILTER_VALIDATE_INT);
        $location       = trim($put_vars['location'] ?? null);
        $image          = trim($put_vars['image'] ?? null);
        $description    = trim($put_vars['description'] ?? null);
        $fee_per_person = filter_var($put_vars['fee_per_person'] ?? null, FILTER_VALIDATE_INT);
        $p_limit        = filter_var($put_vars['p_limit'] ?? null, FILTER_VALIDATE_INT);
        $daterange      = $put_vars['daterange'] ?? null;
        $start_date     = is_array($daterange) ? ($daterange[0] ?? null) : null;
        $end_date       = is_array($daterange) ? ($daterange[1] ?? null) : null;
        $reg_deadline   = trim($put_vars['reg_deadline'] ?? null);

        // 檢查必填欄位
        if (empty($title) || empty($category_no) || empty($location)) {
            http_response_code(422);
            echo json_encode(['status'=>'error','message'=>'缺少必要的表單欄位']);
            exit;
        }

        $sql = "UPDATE events SET
                    title = ?, category_no = ?, location = ?, image = ?, description = ?, 
                    fee_per_person = ?, p_limit = ?, start_date = ?, end_date = ?, reg_deadline = ?
                WHERE event_no = ?";
        
        $stmt = $mysqli->prepare($sql);
        
        $stmt->bind_param("sisssiisssi", 
            $title, $category_no, $location, $image, $description, 
            $fee_per_person, $p_limit, $start_date, $end_date, $reg_deadline,
            $event_no
        );
        
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'status'   => 'success',
                'message'  => '活動 ' . $event_no . ' 更新成功',
            ]);
        } else {
            echo json_encode([
                'status'   => 'info',
                'message'  => '沒有任何變更，或找不到對應的活動',
            ]);
        }

    } else {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => '只允許 PUT 請求']);
    }
?>