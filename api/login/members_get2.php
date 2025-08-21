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

  $household_no = $_GET['household_no'] ?? null;
  if (!$household_no) {
      http_response_code(400);
      echo json_encode(['status'=>'error','message'=>'Missing household_no']);
      exit();
  }

  // 準備 SQL 語句
  $sql = "SELECT 
            user_id,
            fullname,
            gender,
            birth_date,
            id_number,
            phone_number,
            email
          FROM kllv_db.users
          WHERE household_no = ?
          AND is_active = 1
          ORDER BY role_type";

  // 預備 SQL 語句，執行，綁定查詢結果
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param("i", $household_no);
  $stmt->execute();
  $stmt->bind_result($user_id, $fullname, $gender, $birth_date, $id_number, $phone_number, $email);

  // 取得查詢結果
  $data = [];
  while ($stmt->fetch()) {
  $data[] = [
      'user_id' => $user_id,
      'fullname' => $fullname,
      'gender' => $gender,
      'birth_date' => $birth_date,
      'id_number' => $id_number,
      'phone_number' => $phone_number,
      'email' => $email,
  ];
  }

  // 輸出 JSON
  echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);

  // 關閉資料庫連線
  $stmt->close();
  $mysqli->close();
?>