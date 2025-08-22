<?php
require_once __DIR__ . '/../../common/env_init.php';

// 接收reg_no
$reg_no = filter_input(INPUT_GET, 'reg_no', FILTER_VALIDATE_INT);

if (!$reg_no) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '未提供有效的訂單編號 (reg_no)']);
    exit;
}

$sql = "SELECT 
            u.fullname,
            u.phone_number,
            u.id_number,
            u.birth_date,
            erp.econtact_name,
            erp.econtact_phone
        FROM events_regs_plist AS erp
        LEFT JOIN users AS u ON erp.participant_id = u.user_id
        WHERE erp.reg_no = ? -- 直接用 reg_no 篩選
        ORDER BY u.role_type ASC, erp.plist_no ASC"; // 讓主帳號排在最前面

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $reg_no);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
if ($result) {
    $data = $result->fetch_all(MYSQLI_ASSOC);
}

echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
?>