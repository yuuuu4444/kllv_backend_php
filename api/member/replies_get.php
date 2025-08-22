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
            c.comments_no,
            c.post_no,
            c.commented_at,
            p.title,
            p.is_deleted
        FROM community_comments c
        LEFT JOIN community_posts p ON c.post_no = p.post_no
        WHERE c.author_id = ? AND c.is_deleted = 0
        ORDER BY c.commented_at DESC";
    
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        throw new Exception("資料庫查詢準備失敗: " . $mysqli->error, 500);
    }

    $stmt->bind_param('s', $loggedInUserId);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result(
        $comments_no,
        $post_no,
        $commented_at,
        $title,
        $is_deleted
    );
    
    $replies = [];
    while ($stmt->fetch()) {
        $replies[] = [
            'comments_no' => $comments_no,
            'post_no'     => $post_no,
            // 將完整的 datetime 格式化為前端需要的 Y-m-d 日期格式
            'commented_at'=> date('Y-m-d', strtotime($commented_at)),
            'title'       => $title,
            // 將 is_deleted 欄位的 0/1 (整數) 轉換為前端更容易處理的 true/false (布林值)
            'is_deleted'  => (bool)$is_deleted
        ];
    }
    
    // 將最終處理好的資料陣列放入回應中
    $response = ['status' => 'success', 'data' => $replies];

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