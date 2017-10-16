<?php
session_start();
if (isset($_POST) && !empty($_POST['username']) && !empty($_POST['pass'])){
    include '../lib/db_config.php';
    $username = filter_input(INPUT_POST, 'username');
    $password = hash("sha256", $_POST['pass'].PW_SALT);
    $sql = "SELECT * FROM users where `user_id`='$username' AND `password`='$password' LIMIT 1;";
    $link = mysqli_connect(DB_HOST,DB_USER,DB_PASS);
    mysqli_select_db($link, DB_NAME);
    $res = mysqli_query($link, $sql);
    if (mysqli_num_rows($res) > 0){
        while ($row = mysqli_fetch_assoc($res)){
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['is_admin'] = $row['admin'];
            $_SESSION['display_name'] = $row['display_name'];
            if ($row['active'] == 0){
		$_SESSION['logged_in'] = false;
		$_SESSION['error'] = "Your account has been disabled. Please contact your systems administrator.";
            } else {
		$time_sql = "UPDATE `users` SET `last_seen` = NOW() WHERE `user_id` = '$username' LIMIT 1;";
		mysqli_query($link, $time_sql);
		$_SESSION['logged_in'] = true;
            }
        }
    } else {
        $_SESSION['error'] = "Invalid username/password combination";
    }
} else {
    $_SESSION['error'] = "Please be sure to enter both your username AND password";
}
mysqli_close($link);
header("location:".BASE_PATH);
