<?php
session_start();
include '../../lib/db_config.php';
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true) {
    if (isset($_GET)) {
	$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
	if (isset($id) && !empty($id)){
		$sql = "UPDATE `users`SET `active`=0 WHERE `id`=$id LIMIT 1;";
		$link = mysqli_connect(DB_HOST,DB_USER,DB_PASS);
		mysqli_select_db($link, DB_NAME);
		mysqli_query($link, $sql);
		$sql2 = "SELECT `user_id` FROM `users` WHERE `id`=$id LIMIT 1;";
		$username_res = mysqli_query($link, $sql2);
		$username_row = mysqli_fetch_row($username_res);
		$username = $username_row['user_id'];
		mysqli_close($link);
		$_SESSION['good_notice'] = "$username Deactivated!!! THEY are on holiday while I have to keep watching all these servers? It's not fair!";
		header('location:'.BASE_PATH.'manage_users');
	} else {
	    $_SESSION['error_notice'] = "A required field was not filled in";
	    header('location:'.BASE_PATH."manage_users");
        }
    } else {
	$_SESSION['warning_notice'] = "You didn't pick a user to deactivate";
	header('location:'.BASE_PATH."manage_users");
    }
} else {
    $_SESSION['error_notice'] = "You do not have permission to deactivate users. This even thas been logged, and the admin has been notified.";
    header('location:'.BASE_PATH);
    exit();
}
?>
