<?php
session_start();
include '../../lib/db_config.php';
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true) {
    if (isset($_GET)) {
	$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
	$sql = "DELETE FROM `users` WHERE `id`=$id LIMIT 1;";
	$link = mysqli_connect(DB_HOST,DB_USER,DB_PASS);
	mysqli_select_db($link, DB_NAME);
	$username_sql = "SELECT `user_id` FROM `users` WHERE `id`=$id LIMIT 1;";
	$username_res = mysqli_query($link, $username_sql);
	$username_row = mysqli_fetch_array($username_res);
	$username = $username_row['user_id'];
	mysqli_query($link, $sql);
	mysqli_close($link);
	$_SESSION['good_notice'] = "$username DELETED!!! Live Long and Prosper.";
	header('location:'.BASE_PATH.'manage_users');
    } else {
	$_SESSION['error_notice'] = "A required field was not filled in";
	header('location:'.BASE_PATH."manage_users");
    }
} else {
    $_SESSION['error_notice'] = "You do not have permission to add users. This even thas been logged, and the admin has been notified.";
    header('location:'.BASE_PATH);
    exit();
}
?>
