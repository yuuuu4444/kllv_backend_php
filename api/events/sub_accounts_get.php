<?php
require_once __DIR__ . '/../../common/env_init.php';

// --- 登入檢查 ---
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => '請先登入']);
    exit;
}
// --- 登入檢查結束 ---

// 檢查請求方法
if ($_SERVER["REQUEST_METHOD"] !== "GET")  {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "僅支援 GET 方法"]);
    exit;
}

$loggedInUserId = $_SESSION['user_id'];

try {
    // 1.取得登入者的戶號
    $stmt_household = $mysqli->prepare("SELECT household_no FROM users WHERE user_id = ?");
    $stmt_household->bind_param('s', $loggedInUserId);
    $stmt_household->execute();
    $stmt_household->bind_result($household_no);
    $stmt_household->fetch();
    $stmt_household->close(); // 查完立刻關閉，好習慣

    if (empty($household_no)) {
        echo json_encode(['status' => 'success', 'data' => []]);
        exit;
    }

    // 2.查詢同一戶號下的「所有」成員
    $sql_members = "SELECT 
                        u.user_id, u.fullname, u.id_number, u.birth_date, u.phone_number, u.email,
                        uh.address
                    FROM users AS u
                    LEFT JOIN users_households AS uh ON u.household_no = uh.household_no
                    WHERE u.household_no = ? AND u.is_deleted = 0 AND u.is_active = 1
                    ORDER BY u.role_type ASC, u.created_at ASC"; // 讓主帳號(role_type=0)排在最前面

    $stmt_members = $mysqli->prepare($sql_members);
    $stmt_members->bind_param('i', $household_no);
    $stmt_members->execute();
    $stmt_members->bind_result($user_id, $fullname, $id_number, $birth_date, $phone_number, $email, $address);
    
    $familyMembers = [];
    while ($stmt_members->fetch()) {
        $familyMembers[] = [
            'user_id' => $user_id,
            'fullname' => $fullname,
            'id_number' => $id_number,
            'birth_date' => $birth_date,
            'phone_number' => $phone_number,
            'email' => $email,
            'address' => $address
        ];
    }

    echo json_encode(["status" => "success", "data" => $familyMembers]);

} catch (Exception $e) {
    // 捕捉任何潛在錯誤
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "伺服器內部錯誤: " . $e->getMessage()]);
}
?>