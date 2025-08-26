<?php
require_once __DIR__ . '/../../common/env_init.php';

// Session檢查
session_name('ADMINSESSID');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['status'=>'error','message'=>'管理者未登入']);
    exit;
}
// 檢查結束

// 檢查請求方法是否為POST
if ($_SERVER["REQUEST_METHOD"] !== "POST"){
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => '只允許 POST 請求']);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

// 接收reg_no
$reg_no = filter_var($input['reg_no'] ?? null, FILTER_VALIDATE_INT);
$status = filter_var($input['status'] ?? null, FILTER_VALIDATE_INT);

if (!$reg_no || is_null($status)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '缺少必要的參數 (reg_no 或 status)']);
    exit;
}

// 準備SQL UPDATE，目標是events_regs表
$sql = "UPDATE events_regs SET status = ? WHERE reg_no = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $status, $reg_no);

// 執行並回傳
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => '訂單狀態更新成功']);
    } else {
        echo json_encode(['status' => 'info', 'message' => '狀態未變更或找不到該訂單']);
    }
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => '資料庫更新失敗']);
}
?>