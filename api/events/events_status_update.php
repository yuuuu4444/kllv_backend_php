<?php
require_once __DIR__ . '/../../common/env_init.php';

// 後台Session檢查
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

// 驗證必要的欄位是否存在
$event_no = filter_var($input['event_no'] ?? null, FILTER_VALIDATE_INT);
$status = filter_var($input['status'] ?? null, FILTER_VALIDATE_INT);

// is_null()檢查status是否真的是0而不是空字串
if (!$event_no || is_null($status)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => '缺少必要的參數 (event_no 或 status)']);
    exit;
}

$sql = "UPDATE events SET status = ? WHERE event_no = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $status, $event_no); // i for integer

// 執行並回傳結果
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => '活動狀態更新成功']);
    } else {
        echo json_encode(['status' => 'info', 'message' => '狀態未變更或找不到該活動']);
    }
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => '資料庫更新失敗']);
}
?>