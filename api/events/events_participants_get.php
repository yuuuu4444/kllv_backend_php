<?php
require_once __DIR__ . '/../../common/env_init.php';

// URL獲取event_no
$event_no = filter_input(INPUT_GET, 'event_no', FILTER_VALIDATE_INT);

if (!$event_no) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '未提供有效的活動編號 (event_no)']);
    exit;
}

$sql = "SELECT 
            erp.plist_no,     -- 參與者流水號
            u.fullname,       -- 參與者姓名
            u.phone_number,
            u.id_number,
            u.birth_date,
            erp.econtact_name,  -- 緊急聯絡人
            erp.econtact_phone  -- 緊急聯絡人電話
        FROM events_regs_plist AS erp
        -- 先JOIN users表，用participant_id找出參與者基本資料
        LEFT JOIN users AS u ON erp.participant_id = u.user_id
        -- 再JOIN events_regs表，才能用event_no來篩選
        LEFT JOIN events_regs AS er ON erp.reg_no = er.reg_no
        WHERE er.event_no = ?
        ORDER BY erp.plist_no ASC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $event_no);
$stmt->execute();
$stmt->bind_result($plist_no, $fullname, $phone_number, $id_number, $birth_date, $econtact_name, $econtact_phone);

$data = [];
while ($stmt->fetch()) {
    $data[] = [
        'plist_no'       => $plist_no,
        'fullname'       => $fullname,
        'phone_number'   => $phone_number,
        'id_number'      => $id_number,
        'birth_date'     => $birth_date,
        'econtact_name'  => $econtact_name,
        'econtact_phone' => $econtact_phone
    ];
}

echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
?>