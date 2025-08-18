<?php
  // 錯誤回報設定
  error_reporting(E_ALL);
  ini_set("display_errors",1);
  
  // 設定回傳 Content-Type 為 JSON
  header('Content-Type: application/json; charset=utf-8');

  // 引入環境初始化檔案，連線資料庫
  require_once __DIR__ . '/../../common/env_init.php';

  // 僅允許 POST　方法，否則回傳 405 Method Not Allowed
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      http_response_code(405);
      echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
      exit();
  }

  // 取得前端送來的 JSON 輸入
  $input = json_decode(file_get_contents("php://input"), true);

  // 檢查必填欄位
  $requiredFields = ['title','category_no','content','status','published_at'];
  foreach ($requiredFields as $field) {
      if (!isset($input[$field])) {
          http_response_code(400);
          echo json_encode(['status'=>'error','message'=>"Missing field: $field"]);
          exit();
      }
  }

  // 準備 SQL 語句
  $sql = "INSERT INTO kllv_db.news 
          (title, category_no, image, content, published_at, status) 
          VALUES (?, ?, ?, ?, ?, ?)";

  // 預備 SQL 語句，綁定參數
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param(
      "sisssi",
      $input['title'],
      $input['category_no'],
      $input['image'],
      $input['content'],       // HTML
      $input['published_at'],  // 前端送的時間
      $input['status']
  );

  // 執行
  if ($stmt->execute()) {
      $news_no = $stmt->insert_id; // 取得新插入的 news_no
      echo json_encode([
          'status' => 'success',
          'message' => 'News created successfully',
          'news_no' => $news_no
      ], JSON_UNESCAPED_UNICODE);
  } else {
      http_response_code(500);
      echo json_encode(['status'=>'error','message'=>'Database insert failed']);
  }

  // 關閉資料庫連線
  $stmt->close();
  $mysqli->close();
?>
