<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../common/env_init.php';


header('Content-Type: application/json; charset=utf-8');
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      throw new Exception("僅支援 POST 方法", 405);
  }

$loggedInUserId = 'user_account_001';
$data = json_decode(file_get_contents("php://input"), true);
// 驗證前端是否傳來必要的參數
if (empty($data['order_no']) || !isset($data['reason_no'])) { // reason_no 可以是 0，所以用 !isset 檢查
    throw new Exception("缺少 order_no 或 reason_no 參數", 400);
}
// 將資料轉為整數，增加安全性
$orderNoToCancel = (int)$data['order_no'];
$reasonNo = (int)$data['reason_no'];


$response = [];
$stmt = null;

try {
    // 將 status 更新為 3 (代表已取消)，並記錄取消原因
    // WHERE 條件句同時驗證 reg_no 和 participant_id，確保使用者只能取消自己的報名
    $sql = "UPDATE events_regs 
            SET status = 3, cancel_reason_no = ? 
            WHERE reg_no = ? AND participant_id = ?";
            

    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        throw new Exception("資料庫更新準備失敗: " . $mysqli->error, 500);
    }
    
  
    $stmt->bind_param('iis', $reasonNo, $orderNoToCancel, $loggedInUserId);
    $is_execute_successful = $stmt->execute();

    if ($is_execute_successful) {
        if ($mysqli->affected_rows > 0) {
            $response = ['status' => 'success', 'message' => '活動取消成功'];
        } else {
            // 如果沒有任何行被影響，代表該筆報名紀錄不存在，或不屬於當前登入者
            throw new Exception("找不到報名紀錄或權限不足", 404);
        }
    } else {
        // 如果 execute() 直接返回 false，代表資料庫執行出錯
        throw new Exception("資料庫更新失敗: " . $stmt->error, 500);
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response = ["status" => "error", "message" => $e->getMessage()];
} finally {
    if ($stmt !== null) {
        $stmt->close();
    }
    if (isset($mysqli)) {
        $mysqli->close();
    }
}
echo json_encode($response);
?>