<?php
// 錯誤回報設定
error_reporting(E_ALL);
ini_set("display_errors", 1);

// 設定回傳 Content-Type 為 JSON
header('Content-Type: application/json; charset=utf-8');

// 引入環境初始化檔案，連線資料庫
require_once __DIR__ . '/../../common/env_init.php';

// 僅允許 POST 方法
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit();
}

// 取得前端送來的 JSON 輸入
$input = json_decode(file_get_contents("php://input"), true);

// 檢查必要欄位
$requiredFields = ['user_id', 'nickname', 'id_number', 'birth_date', 'gender'];
foreach ($requiredFields as $field) {
    if (!isset($input[$field])) {
        http_response_code(400);
        echo json_encode(['status'=>'error','message'=>"Missing field: $field"]);
        exit();
    }
}

$user_id = $input['user_id'];
error_log("取得的 user_id: " . $user_id);
$nickname = $input['nickname'];
$id_number = $input['id_number'];
$birth_date = $input['birth_date'];
$gender = $input['gender'];

// 格式檢查
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth_date) || !strtotime($birth_date)) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'birth_date 格式需為 YYYY-MM-DD']);
    exit();
}
if (!in_array($gender, ['M','F','N'])) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'gender 欄位錯誤（僅支援 M/F/N）']);
    exit();
}

// 查詢此 user_id 是否存在並且是子帳號
$user_stmt = $mysqli->prepare("SELECT email, fullname, role_type FROM users WHERE user_id = ?");
$user_stmt->bind_param('s', $user_id);
$user_stmt->execute();
$user_stmt->store_result();

if ($user_stmt->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status'=>'error','message'=>'查無此 user_id']);
    $user_stmt->close();
    exit();
}

$user_stmt->bind_result($email, $fullname, $role_type);
$user_stmt->fetch();

if ($role_type != 1) {
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'此帳號不是子帳號（role_type 必須為 1）']);
    $user_stmt->close();
    exit();
}
$user_stmt->close();

// 執行 UPDATE 將資料寫入子帳號，並更新 is_active 為 1
$update_stmt = $mysqli->prepare("UPDATE users SET nickname = ?, id_number = ?, birth_date = ?, gender = ?, is_active = 1 WHERE user_id = ?");
$update_stmt->bind_param('sssss', $nickname, $id_number, $birth_date, $gender, $user_id);

if (!$update_stmt->execute()) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'更新子帳號失敗: ' . $update_stmt->error]);
    $update_stmt->close();
    $mysqli->close();
    exit();
}
$update_stmt->close();

// 成功回傳 email, fullname, user_id
echo json_encode([
    'status'=>'success',
    'message'=>'子帳號資料已送出',
    'user_id'=>$user_id,
    'email'=>$email,
    'fullname'=>$fullname
], JSON_UNESCAPED_UNICODE);

  $mysqli->close();
?>
