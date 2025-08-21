<?php
  // 錯誤回報設定
  error_reporting(E_ALL);
ini_set("display_errors",1);

  // 設定回傳 Content-Type 為 JSON
  header('Content-Type: application/json; charset=utf-8');

  // 引入環境初始化檔案，連線資料庫
  require_once __DIR__ . '/../../common/env_init.php';

  // 僅允許 POST
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      http_response_code(405);
      echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
      exit();
  }

  // 取得前端送來的 JSON 輸入
  $input = json_decode(file_get_contents("php://input"), true);

  // 檢查必填欄位
  $requiredFields = ['news_no','title','category_no','content','status','published_at'];
  foreach ($requiredFields as $field) {
      if (!isset($input[$field])) {
          http_response_code(400);
          echo json_encode(['status'=>'error','message'=>"Missing field: $field"]);
          exit();
      }
  }

  // 準備 SQL 語句
  $sql = "UPDATE kllv_db.news 
          SET title = ?, category_no = ?, image = ?, content = ?, published_at = ?, status = ? 
          WHERE news_no = ?";

  // 預備 SQL 語句，綁定參數
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param(
      "sisssii",
      $input['title'],
      $input['category_no'],
      $input['image'],
      $input['content'],       // HTML
      $input['published_at'],
      $input['status'],
      $input['news_no'],
      
  );

  // 執行
  if ($stmt->execute()) {
      echo json_encode([
          'status' => 'success',
          'message' => 'News updated successfully',
          'news_no' => $input['news_no']
      ], JSON_UNESCAPED_UNICODE);
  } else {
      http_response_code(500);
      echo json_encode(['status'=>'error','message'=>'Database update failed']);
  }

  // 關閉資料庫連線
  $stmt->close();
  $mysqli->close();
?>
