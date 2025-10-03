<!-- Handle Auto Logout -->
<?php
	require_once 'classes.php';

	$db = new db_class();

	if (isset($_SESSION['user_id'], $_SESSION['session_token'])) {
		$userId = $_SESSION['user_id'];
		$sessionToken = $_SESSION['session_token'];

		$stmt = $db->getConnection()->prepare(
			"UPDATE user_sessions SET is_active = 0 WHERE user_id = ? AND session_token = ?"
		);
		$stmt->bind_param("is", $userId, $sessionToken);
		$stmt->execute();
		$stmt->close();
	}

	session_unset();
	session_destroy();

	header("Location: ../index.php");
	exit();
