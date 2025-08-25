<?php
// 錯誤回報
error_reporting(E_ALL);
ini_set("display_errors", 1);

// 引入資料庫連線等共用檔案
require_once __DIR__ . '/../../common/env_init.php';

//  Session 安全性設定
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
// 在 localhost 開發時，連線是 http 而非 https，部署到正式的 https 伺服器時取消注解
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
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "僅支援 GET 方法"]);
    exit;
}

$response = [];
$stmt = null;

try {
    $sql = "SELECT 
            u.user_id, 
            u.fullname, 
            u.nickname, 
            u.profile_image, 
            u.phone_number, 
            u.email, 
            u.id_number, 
            u.birth_date, 
            u.gender, 
            u.household_no, 
            u.role_type,
            h.address 
        FROM users u
        LEFT JOIN users_households h ON u.household_no = h.household_no
        WHERE u.user_id = ?";

    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) throw new Exception("資料庫查詢準備失敗: " . $mysqli->error, 500);

    $stmt->bind_param('s', $loggedInUserId);
    $stmt->execute();
    
    // 將查詢結果從資料庫傳輸到 PHP
    $stmt->store_result();
    
    // 檢查是否有找到資料
    if ($stmt->num_rows === 0) {
        throw new Exception("在資料庫中找不到指定的使用者 ID: " . $loggedInUserId, 404);
    }
    
    // 根據 SELECT 的欄位順序，建立對應的 PHP 變數並綁定
    $stmt->bind_result(
        $user_id, 
        $fullname, 
        $nickname, 
        $profile_image, 
        $phone_number, 
        $email, 
        $id_number, 
        $birth_date, 
        $gender, 
        $household_no, 
        $role_type,
        $address
    );
    
    // 將綁定的變數填入值
    $stmt->fetch();
    
    // 手動將變數組合成一個關聯陣列
    $user = [
        'user_id'       => $user_id,
        'fullname'      => $fullname,
        'nickname'      => $nickname,
        'profile_image' => $profile_image,
        'phone_number'  => $phone_number,
        'email'         => $email,
        'id_number'     => $id_number,
        'birth_date'    => $birth_date,
        'gender'        => $gender,
        'household_no'  => $household_no,
        'role_type'     => $role_type,
        'address'       => $address
    ];

    $response = ["status" => "success", "data" => $user];

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response = ["status" => "error", "message" => $e->getMessage()];
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($mysqli)) $mysqli->close();
}

echo json_encode($response);
?>