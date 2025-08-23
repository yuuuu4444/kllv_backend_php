<?php
  error_reporting(E_ALL);
  ini_set("display_errors",1);

  require_once __DIR__ . '/../../common/env_init.php';

  // Session 安全性設定
  ini_set('session.cookie_httponly', 1);
  ini_set('session.use_only_cookies', 1);
  ini_set('session.cookie_secure', 1);

  session_set_cookie_params([
      'samesite' => 'Strict'
  ]);

  if (session_status() === PHP_SESSION_NONE) {
      session_start();
  }

  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      echo json_encode(['status'=>'error','message'=>'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
      exit;
  }

  $_SESSION = [];

  if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
          $params["path"], $params["domain"],
          $params["secure"], $params["httponly"]
      );
  }

  session_destroy();

  echo json_encode([
      'status' => 'success',
      'message' => '登出成功'
  ], JSON_UNESCAPED_UNICODE);
?>