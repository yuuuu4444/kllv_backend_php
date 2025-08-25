<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once __DIR__ . '/../../common/env_init.php';

//  Session 安全性設定
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
// ini_set('session.cookie_secure', 1); 

session_set_cookie_params(['samesite' => 'Strict']);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 守衛檢查：如果 Session 中沒有登入資訊，則拒絕存取
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    http_response_code(401); // 401 Unauthorized (未授權)
    // 在輸出 JSON 後立刻停止腳本，確保不會執行到後面的程式碼
    echo json_encode(['status' => 'error', 'message' => '未登入或憑證無效'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 從 Session 中取得當前登入者的 user_id
$loggedInUserId = $_SESSION['user_id'];

// 設定 HTTP Header
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER["REQUEST_METHOD"] !== "POST")  {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "僅支援 POST 方法"]);
    exit;
}

// 接收前端傳來的 JSON 資料
$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['user_id']) || empty($data['fullname']) || empty($data['password']) || empty($data['email']) || empty($data['phone_number'])) { 
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'所有欄位皆為必填']);
    exit;
}

$response = [];
$stmt_role = null;
$stmt_check = null;
$stmt_insert = null;

try {
     // 確保只有主帳號 (role_type=0) 才能新增成員
    $stmt_role = $mysqli->prepare("SELECT role_type, household_no FROM users WHERE user_id = ?");

    if ($stmt_role === false) throw new Exception("資料庫準備失敗 (權限查詢)", 500);

    $stmt_role->bind_param('s', $loggedInUserId);
    $stmt_role->execute();
    $stmt_role->store_result();

    if ($stmt_role->num_rows === 0) throw new Exception("找不到主帳號資料", 404);

    $stmt_role->bind_result($role_type, $household_no);
    $stmt_role->fetch();
    
    if ($role_type !== 0) {
        throw new Exception("權限不足，只有主帳號可以新增成員", 403);
    }

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

    $response =[
        "status" => "success", 
        "message" => "家庭成員新增成功",
        "data" => [
            "user_id"      => $data['user_id'],
            "fullname"     => $data['fullname'],
            "email"        => $data['email'],
            "phone_number" => $data['phone_number']
        ]
    ];

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response = ["status" => "error", "message" => $e->getMessage()];
} finally {
    // 確保所有可能建立的 statement 都會被關閉
    if ($stmt_role !== null) $stmt_role->close();
    if ($stmt_check !== null) $stmt_check->close();
    if ($stmt_insert !== null) $stmt_insert->close();
    if (isset($mysqli)) $mysqli->close();
}

echo json_encode($response);
?>