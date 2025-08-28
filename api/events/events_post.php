<?php
require_once __DIR__ . '/../../common/env_init.php';

// 檢查是否為POST
if ($_SERVER["REQUEST_METHOD"] == "POST"){

    $input = json_decode(file_get_contents("php://input"), true);

    $title          = trim($input['title'] ?? '');
    $category_no    = filter_var($input['category'] ?? null, FILTER_VALIDATE_INT);
    $location       = trim($input['location'] ?? '');
    $image          = trim($input['image'] ?? '');
    $description    = trim($input['description'] ?? '');
    $fee_per_person = filter_var($input['fee_per_person'] ?? null, FILTER_VALIDATE_INT);
    $p_limit        = filter_var($input['p_limit'] ?? null, FILTER_VALIDATE_INT);
    $daterange      = $input['daterange'] ?? []; 
    $start_date     = $daterange[0] ?? '';
    $end_date       = $daterange[1] ?? '';
    $reg_deadline   = trim($input['reg_deadline'] ?? '');
    
    if (empty($title) || empty($category_no) || empty($location) || empty($daterange) || empty($reg_deadline)) {
        http_response_code(422);
        echo json_encode(['status'=>'error','message'=>'缺少必要的表單欄位']);
        exit;
    }

    $sql = "INSERT INTO events
            (title, category_no, location, image, description, fee_per_person, p_limit, start_date, end_date, reg_deadline, created_at, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 0)";
    
    $stmt = $mysqli->prepare($sql);
    
    $stmt->bind_param("sisssiisss", 
        $title, 
        $category_no, 
        $location, 
        $image, 
        $description, 
        $fee_per_person, 
        $p_limit,
        $start_date,
        $end_date,
        $reg_deadline
    );
    
    $stmt->execute();

    echo json_encode([
        'status'   => 'success',
        'message'  => '新活動建立成功',
        'event_id' => $mysqli->insert_id
    ]);

} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => '只允許 POST 請求']);
}
?>