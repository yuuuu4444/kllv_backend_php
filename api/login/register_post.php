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

  // 準備 SQL 語句
  $sql = "INSERT INTO kllv_db.users (user_id, fullname, nickname, password, phone_number, email, id_number, birth_date, gender, household_no, role_type, created_at)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

  // 預備 SQL 語句，綁定參數
  $stmt = $mysqli->prepare($sql);
  $created_at = date('Y-m-d H:i:s');
  $stmt->bind_param(
      "sssssssssiis",  // 12個參數：s=string, i=integer
      $input['user_id'],
      $input['fullname'],
      $input['nickname'],
      $input['password'],       
      $input['phone_number'],
      $input['email'],
      $input['id_number'],
      $input['birth_date'],
      $input['gender'],
      $input['household_no'],
      $input['role_type'],
      $input['created_at']
  );

  // 執行
  if ($stmt->execute()) {
      echo json_encode([
        "status" => "success", 
        "message" => "新增成功",
        "data" => [
            "user_id"      => $input['user_id'],
            "fullname"     => $input['fullname'],
            "nickname"     => $input['nickname'],
            "password"     => $input['password'],
            "phone_number" => $input['phone_number'],
            "email"        => $input['email'],
            "id_number"    => $input['id_number'],
            "birth_date"   => $input['birth_date'],
            "gender"       => $input['gender'],
            "household_no" => $input['household_no'],
            "role_type"    => $input['role_type'],
            "created_at"   => $created_at
        ]
    ], JSON_UNESCAPED_UNICODE);
  } else {
      http_response_code(500);
      echo json_encode(['status'=>'error','message'=>'Database insert failed']);
  }

  // 關閉資料庫連線
  $stmt->close();
  $mysqli->close();
?>