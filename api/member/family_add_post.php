<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once __DIR__ . '/../../common/env_init.php';

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER["REQUEST_METHOD"] !== "POST")  {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "僅支援 POST 方法"]);
    exit;
}

// 手動建立的假資料 (未來從 Session 或 Token 取得)
$loggedInUserId = 'user_account_001';
$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['user_id']) || empty($data['fullname']) || empty($data['password']) || empty($data['email']) || empty($data['phone_number'])) { 
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'所有欄位皆為必填']);
    exit;
}
$stmt_household = null;
$stmt_check = null;
$stmt_insert = null;

try {
    // 取得主帳號戶號
    $stmt_household = $mysqli->prepare("SELECT household_no FROM users WHERE user_id = ?");
    if ($stmt_household === false) throw new Exception("資料庫準備失敗 (戶號查詢)", 500);
    $stmt_household->bind_param('s', $loggedInUserId);
    $stmt_household->execute();

    $stmt_household->store_result();

    // 判斷 num_rows
    if ($stmt_household->num_rows === 0) {
        throw new Exception("找不到主帳號資料", 404);
    }
    
    $stmt_household->bind_result($household_no);
    $stmt_household->fetch();
    
    // 確認戶號有效
    if (empty($household_no)) {
        throw new Exception("主帳號沒有設定戶籍資料，無法新增家庭成員", 400);
    }

    // 檢查帳號和Email是否重複
    $stmt_check = $mysqli->prepare("SELECT user_id FROM users WHERE user_id = ? OR email = ?");
    if ($stmt_check === false) throw new Exception("資料庫準備失敗 (重複檢查)", 500);
    $stmt_check->bind_param('ss', $data['user_id'], $data['email']);
    $stmt_check->execute();
    

    $stmt_check->store_result();
    
    // 判斷 num_rows > 0 代表已存在
    if ($stmt_check->num_rows > 0) {
        throw new Exception("帳號或電子信箱已被註冊", 409); 
    }

    // 密碼加密
    // $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

    // 直接用明碼
    $plain_password = $data['password'];


    // 執行新增
    $sql_insert = "INSERT INTO users (user_id, fullname, password, email, phone_number, household_no, role_type, is_active, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, 1, 0, NOW())";
    $stmt_insert = $mysqli->prepare($sql_insert);
    if ($stmt_insert === false) throw new Exception("資料庫準備失敗 (新增)", 500);
    
    $stmt_insert->bind_param('sssssi', 
        $data['user_id'], 
        $data['fullname'], 
        // $hashed_password, 
        $plain_password,
        $data['email'], 
        $data['phone_number'], 
        $household_no
    );

    if (!$stmt_insert->execute()) {
        throw new Exception("資料庫新增失敗: " . $stmt_insert->error, 500);
    }

    echo json_encode([
        "status" => "success", 
        "message" => "家庭成員新增成功",
        "data" => [
            "user_id"      => $data['user_id'],
            "fullname"     => $data['fullname'],
            "email"        => $data['email'],
            "phone_number" => $data['phone_number']
        ]
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
} finally {
    // 確保所有可能建立的 statement 都會被關閉
    if (isset($stmt_household)) $stmt_household->close();
    if (isset($stmt_check)) $stmt_check->close();
    if (isset($stmt_insert)) $stmt_insert->close();
    if (isset($mysqli)) $mysqli->close();
}
?>