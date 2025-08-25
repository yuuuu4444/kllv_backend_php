<?php
	error_reporting(E_ALL);
	ini_set("display_errors",1);

	require_once __DIR__ . '/../../common/env_init.php';

	// Session 安全性設定
	// ini_set('session.cookie_httponly', 1);
	ini_set('session.use_only_cookies', 1);
	ini_set('session.cookie_secure', 1);

	session_set_cookie_params([
			'samesite' => 'Strict'
	]);

	session_start();

	if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {

			$sql = "SELECT
								user_id,
								fullname,
								nickname,
								profile_image,
								phone_number,
								email,
								id_number,
								birth_date,
								gender,
								household_no,
								role_type,
								is_active
							FROM users
							WHERE user_id = ?";

			$stmt = $mysqli->prepare($sql);
			$stmt->bind_param('s', $_SESSION['user_id']);
			$stmt->execute();
			$stmt->bind_result(
					$db_user_id,
					$db_fullname,
					$db_nickname,
					$db_profile_image,
					$db_phone_number,
					$db_email,
					$db_id_number,
					$db_birth_date,
					$db_gender,
					$db_household_no,
					$db_role_type,
					$db_is_active
			);

			if ($stmt->fetch()) {
					echo json_encode([
							'status' => 'success',
							'data' => [
									'user' => [
											'user_id'       => $db_user_id,
											'fullname'      => $db_fullname,
											'nickname'      => $db_nickname,
											'profile_image' => $db_profile_image,
											'phone_number'  => $db_phone_number,
											'email'         => $db_email,
											'id_number'     => $db_id_number,
											'birth_date'    => $db_birth_date,
											'gender'        => $db_gender,
											'household_no'  => $db_household_no,
											'role_type'     => $db_role_type,
											'is_active'     => $db_is_active
									]
							]
					], JSON_UNESCAPED_UNICODE);
			} else {
					http_response_code(404);
					echo json_encode([
						'status'=>'error',
						'message'=>'找不到使用者'
					], JSON_UNESCAPED_UNICODE);
			}

			$stmt->close();
	} else {
			http_response_code(401);
			echo json_encode([
					'status' => 'error',
					'message' => '未登入'
			], JSON_UNESCAPED_UNICODE);
	}
?>