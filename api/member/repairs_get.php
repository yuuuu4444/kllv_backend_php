<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../common/env_init.php';

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        throw new Exception("僅支援 GET 方法", 405);
}

// 手動建立的假資料 (未來從 Session 或 Token 取得)
$loggedInUserId = 'user_account_001'; 
$response = [];
$stmt = null;

try {
    $sql = "SELECT 
            r.repair_no,
            r.reported_at,
            rc.category_name,
            r.status
            FROM repair r
            LEFT JOIN repair_categories rc ON r.category_no = rc.category_no
            WHERE r.reporter_id = ?
            ORDER BY r.reported_at DESC";
    
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        throw new Exception("資料庫查詢準備失敗: " . $mysqli->error, 500);
    }
    

    $stmt->bind_param('s', $loggedInUserId);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result(
        $repair_no,
        $reported_at,
        $category_name,
        $status
    );
    
    $repairs = [];
    while ($stmt->fetch()) {
        $repairs[] = [
            'repair_no'     => $repair_no,
            'formatted_repair_no'     => 'RR' . str_pad($repair_no, 5, '0', STR_PAD_LEFT), // report_no 在資料庫中是數字，前端假資料是字串 'RR00001'
            'reported_at'   => date('Y-m-d', strtotime($reported_at)), // 將完整的 date 格式化為 Y-m-d
            'category_name' => $category_name,
            'status'        => $status
        ];
    }
    
    // 將最終處理好的資料陣列放入回應中
    $response = ['status' => 'success', 'data' => $repairs];

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