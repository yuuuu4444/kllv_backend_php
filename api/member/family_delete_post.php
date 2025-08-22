<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../common/env_init.php';

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER["REQUEST_METHOD"] !== "POST")  {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "僅支援 POST 方法"]);
    exit;
}

$loggedInUserId = 'user_account_001';
$data = json_decode(file_get_contents("php://input"), true);
$response = [];

try {

    if (empty($data['user_id'])) {
        throw new Exception('請求中未提供 user_id', 400);
    }
    $memberIdToDelete = $data['user_id'];

    if ($loggedInUserId === $memberIdToDelete) {
        throw new Exception("無法刪除自己", 400);
    }

    // 權限驗證部分
    $stmt_my = $mysqli->prepare("SELECT household_no FROM users WHERE user_id = ?");
    $stmt_my->bind_param('s', $loggedInUserId);
    $stmt_my->execute();
    $stmt_my->store_result();
    if ($stmt_my->num_rows === 0) throw new Exception("找不到主帳號資料", 404);
    $stmt_my->bind_result($my_household_no);
    $stmt_my->fetch();
    $stmt_my->close();

    $stmt_member = $mysqli->prepare("SELECT household_no FROM users WHERE user_id = ?");
    $stmt_member->bind_param('s', $memberIdToDelete);
    $stmt_member->execute();
    $stmt_member->store_result();
    if ($stmt_member->num_rows === 0) throw new Exception("找不到要刪除的成員", 404);
    $stmt_member->bind_result($member_household_no);
    $stmt_member->fetch();
    $stmt_member->close();

    if (empty($my_household_no) || $my_household_no !== $member_household_no) {
        throw new Exception("權限不足，無法刪除此成員", 403);
    }
    
    // 執行刪除
    $stmt_delete = $mysqli->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt_delete->bind_param('s', $memberIdToDelete);

    if ($stmt_delete->execute()) {
        if ($stmt_delete->affected_rows > 0) {
            $response = ["status" => "success", "message" => "成員刪除成功"];
        } else {
            throw new Exception("刪除操作已執行，但找不到對應的成員", 404);
        }
    } else {
        throw new Exception("資料庫刪除失敗: " . $stmt_delete->error, 500);
    }
    $stmt_delete->close();

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response = ["status" => "error", "message" => $e->getMessage()];
}

echo json_encode($response);
?>