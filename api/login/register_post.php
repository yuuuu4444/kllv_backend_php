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
  $requiredFields = [
    'user_id', 
    'fullname',
    'nickname',
    'password',
    'phone_number',
    'email',
    'id_number',
    'birth_date',
    'gender',
    'household_no',
    'role_type'
  ];
  foreach ($requiredFields as $field) {
      if (!isset($input[$field])) {
          http_response_code(400);
          echo json_encode(['status'=>'error','message'=>"Missing field: $field"]);
          exit();
      }
  }

  // 檢查 user_id 是否重複
  $check_stmt = $mysqli->prepare("SELECT user_id FROM users WHERE user_id = ?");
  $check_stmt->bind_param('s', $input['user_id']);
  $check_stmt->execute();
  $check_stmt->store_result();
  if ($check_stmt->num_rows > 0) {
      http_response_code(400);
      echo json_encode(['status'=>'error','message'=>'帳號已存在']);
      $check_stmt->close();
      exit();
  }
  $check_stmt->close();

  // 檢查 email 是否重複
  $email_stmt = $mysqli->prepare("SELECT email FROM users WHERE email = ?");
  $email_stmt->bind_param('s', $input['email']);
  $email_stmt->execute();
  $email_stmt->store_result();
  if ($email_stmt->num_rows > 0) {
      http_response_code(400);
      echo json_encode(['status'=>'error','message'=>'此信箱已被註冊']);
      $email_stmt->close();
      exit();
  }
  $email_stmt->close();

  // 新增 users
  $stmt = $mysqli->prepare("INSERT INTO users (user_id, fullname, nickname, password, phone_number, email, id_number, birth_date, gender, household_no, role_type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
  $stmt->bind_param('sssssssssis', $input['user_id'], $input['fullname'], $input['nickname'], $input['password'], $input['phone_number'], $input['email'], $input['id_number'], $input['birth_date'], $input['gender'], $input['household_no'], $input['role_type']);
  if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'新增 users 失敗: ' . $stmt->error]);
    exit();
  }

  // 更新 users_households 的 creator_id
  $household_no = intval($input['household_no']);
  $stmt2 = $mysqli->prepare("UPDATE users_households SET creator_id = ? WHERE household_no = ?");
  $stmt2->bind_param('si', $input['user_id'], $household_no);
  $stmt2->execute();

  // 回傳結果
  echo json_encode(['status'=>'success','message'=>'註冊成功','user_id'=>$input['user_id']], JSON_UNESCAPED_UNICODE);

  // 關閉資料庫連線
  $stmt->close();
  $stmt2->close();
  $mysqli->close();
?>