<?php
    require_once __DIR__ . '/../../common/cors.php';
    require_once __DIR__ . '/../../common/conn.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST"){

        $location    = trim($_POST['location'] ?? '');
        $category_no = filter_input(INPUT_POST, 'category_no', FILTER_VALIDATE_INT) ?: 0;
        $description = trim($_POST['description'] ?? '');
        $reporter    = trim($_POST['reporter_id'] ?? '');
        $reported_at = trim($_POST['reported_at'] ?? date('Y-m-d'));
        $status      = filter_input(INPUT_POST, 'status', FILTER_VALIDATE_INT);
    
        if ($status === null) {
            $status = 0; // 預設為 0
        }
    
        if ($location === '' || $category_no === 0 || $description === '' || $reporter === '') {
            http_response_code(422);
            echo json_encode(['status'=>'error','message'=>'缺少必要欄位']);
            exit;
        }
    
        $chk = $mysqli->prepare("SELECT category_name 
                                FROM kllv_db.repair_categories 
                                WHERE category_no = ? 
                                LIMIT 1");
        $chk->bind_param("i", $category_no);
        $chk->execute();
        $cat = $chk->get_result()->fetch_assoc();
        $chk->close();
    
        if (!$cat) {
        
            $all = $mysqli->query("SELECT category_no, category_name FROM kllv_db.repair_categories ORDER BY category_no")->fetch_all(MYSQLI_ASSOC);
    
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => '無效的分類（category 不存在）',
                'request_category' => $category_no,
                'categories' => $all
            ]);
            exit;
        }
        $category_name = $cat['category_name'];
    
        
        $sql = "INSERT INTO kllv_db.repair
                (location, category_no, description, reporter_id, reported_at, status)
                VALUES (?,?,?,?,?,?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sisssi", $location, $category_no, $description, $reporter, $reported_at, $status);
        $stmt->execute();
    
        $repair_no   = $mysqli->insert_id;
        // $repair_code = 'RR' . str_pad((string)$repair_no, 5, '0', STR_PAD_LEFT);
    
        echo json_encode([
            'status'  => 'success',
            'message' => '建立成功',
            'data' => [
                'repair_no'     => $repair_no,
                // 'repair_code'   => $repair_code,
                'location'      => $location,
                'category'      => $category_no,
                'category_name' => $category_name,
                'description'   => $description,
                'reporter_id'   => $reporter,
                'reported_at'   => $reported_at,
                'status'        => $status,
            ]
        ]);
    } else {
        http_response_code(405); // Method Not Allowed
        echo json_encode(['status' => 'error', 'message' => '只允許 POST 請求']);
    }
?>