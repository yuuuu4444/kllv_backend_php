<?php
// 錯誤回報設定
error_reporting(E_ALL);
ini_set("display_errors",1);

// 設定回傳 Content-Type 為 JSON
header('Content-Type: application/json; charset=utf-8');

// 引入環境初始化檔案，連線資料庫
require_once __DIR__ . '/../../common/env_init.php';

// 僅允許 POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit();
}

// 取得前端送來的 JSON 輸入
$input = json_decode(file_get_contents("php://input"), true);

// 檢查必填欄位
$requiredFields = ['household_no','status'];
foreach ($requiredFields as $field) {
    if (!isset($input[$field])) {
        http_response_code(400);
        echo json_encode(['status'=>'error','message'=>"Missing field: $field"]);
        exit();
    }
}

$household_no = (int)$input['household_no'];
$status = (int)$input['status'];

// status 對應 is_active
if ($status === 2) {
    $is_active = 1;
} else {
    $is_active = 0;
}

// 更新 users_households.status
$sql1 = "UPDATE kllv_db.users_households SET status = ? WHERE household_no = ?";
$stmt1 = $mysqli->prepare($sql1);
$stmt1->bind_param("ii", $status, $household_no);
$ok1 = $stmt1->execute();
$stmt1->close();

// 更新 users.is_active
$sql2 = "UPDATE kllv_db.users SET is_active = ? WHERE household_no = ?";
$stmt2 = $mysqli->prepare($sql2);
$stmt2->bind_param("ii", $is_active, $household_no);
$ok2 = $stmt2->execute();
$stmt2->close();

if ($ok1 && $ok2) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Household status and user active state updated successfully',
        'household_no' => $household_no,
        'status_value' => $status,
        'is_active' => $is_active
    ], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Database update failed']);
}

// 關閉資料庫連線
$mysqli->close();
?>
