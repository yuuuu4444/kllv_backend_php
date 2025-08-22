<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../common/env_init.php';

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    throw new Exception("僅支援 GET 方法", 405);
}


$response = [];
$stmt = null;

try {
    $sql = "SELECT reason_no, reason_name 
              FROM events_regs_cancel_reasons 
              ORDER BY reason_no ASC";
    
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        throw new Exception("資料庫查詢準備失敗: " . $mysqli->error, 500);
    }

    $stmt->execute();

    $stmt->store_result();
    $stmt->bind_result($reason_no, $reason_name);
    
    $reasons = [];
    while ($stmt->fetch()) {
        $reasons[] = [
            'reason_no' => $reason_no,
            'reason_name' => $reason_name
        ];
    }
    $response = ['status' => 'success', 'data' => $reasons];

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