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

	$data = json_decode(file_get_contents("php://input"), true);
	$user_id = trim($data['user_id'] ?? '');
	$password = $data['password'] ?? '';

	if (!$user_id || !$password) {
			http_response_code(400);
			echo json_encode(['status'=>'error','message'=>'帳號密碼必填'], JSON_UNESCAPED_UNICODE);
			exit;
	}

	$sql = "SELECT user_id, password, is_active
					FROM kllv_db.users
					WHERE user_id = ?";
	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param('s', $user_id);
	$stmt->execute();
	$stmt->bind_result($db_user_id, $db_password, $db_is_active);

	if ($stmt->fetch()) {

			if (!$db_is_active) {
					http_response_code(403);
					echo json_encode(['status'=>'error','message'=>'帳號尚未啟用'], JSON_UNESCAPED_UNICODE);
					exit;
			}

			if ($password !== $db_password) {
					http_response_code(401);
					echo json_encode(['status'=>'error','message'=>'帳號或密碼錯誤'], JSON_UNESCAPED_UNICODE);
					exit;
			}

			$_SESSION['user_id'] = $db_user_id;
			$_SESSION['logged_in'] = true;

			echo json_encode([
					'status' => 'success',
					'message' => '登入成功'
			], JSON_UNESCAPED_UNICODE);

	} else {
			http_response_code(401);
			echo json_encode(['status'=>'error','message'=>'帳號或密碼錯誤'], JSON_UNESCAPED_UNICODE);
	}

	$stmt->close();
?>
