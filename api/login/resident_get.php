<?php
// 錯誤回報設定
  error_reporting(E_ALL);
  ini_set("display_errors",1);

  // 設定回傳 Content-Type 為 JSON
  header('Content-Type: application/json; charset=utf-8');

  // 引入環境初始化檔案，連線資料庫
  require_once __DIR__ . '/../../common/env_init.php';

  // 僅允許 GET 方法，否則回傳 405 Method Not Allowed
  if ($_SERVER["REQUEST_METHOD"] !== "GET") {
      http_response_code(405);
      echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
      exit();
  }

  $user_id = $_GET['user_id'] ?? null;
  if (!$user_id) {
      http_response_code(400);
      echo json_encode(['status'=>'error','message'=>'Missing user_id']);
      exit();
  }

  // 準備 SQL 語句
  $sql = "SELECT email, fullname FROM users WHERE user_id = ?";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('s', $user_id);
  $stmt->execute();
  $stmt->bind_result($email, $fullname);

  $data = [];
  if ($stmt->fetch()) {
      $data = [
          'email' => $email,
          'fullname' => $fullname
      ];
      echo json_encode(['status'=>'success', 'data'=>$data], JSON_UNESCAPED_UNICODE);
  } else {
      echo json_encode(['status'=>'error', 'message'=>'查無資料']);
  }

  $stmt->close();
  $mysqli->close();
?>
