<?php
  // 錯誤回報設定
  error_reporting(E_ALL);
  ini_set("display_errors",1);
  
  // 設定回傳 Content-Type 為 JSON
  header('Content-Type: application/json; charset=utf-8');

  // 引入環境初始化檔案，連線資料庫
  require_once __DIR__ . '/../../common/env_init.php';

  // 僅允許 GET　方法，否則回傳 405 Method Not Allowed
  if ($_SERVER["REQUEST_METHOD"] !== "GET") {
      http_response_code(405);
      echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
      exit();
  }

  // SQL 語句
  $sql = "SELECT 
            h.household_no AS household_no
          FROM kllv_db.users_households h
          JOIN kllv_db.users u ON h.creator_id = u.user_id
          ORDER BY household_no";
  // 預備 SQL 語句，執行，綁定查詢結果
  $stmt = $mysqli->prepare($sql);
  $stmt->execute();
  $stmt->bind_result($household_no);

  // 取得查詢結果
  $data = [];
  while ($stmt->fetch()) {
  $data[] = [
      'household_no' => $household_no
  ];
  }

  // 輸出 JSON
  echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);

  // 關閉資料庫連線
  $stmt->close();
  $mysqli->close();
?>