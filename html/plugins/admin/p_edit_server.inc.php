<?php
session_start();
include '../../lib/db_config.php';
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true) {
    if (isset($_POST)) {
	$distro_ver_id = filter_input(INPUT_POST, 'distro_ver_id', FILTER_SANITIZE_NUMBER_INT);
	$distro_array = explode("-",$distro_ver_id);
	$distro = $distro_array[0];
	$distro_ver = $distro_array[1];
	$server_name = filter_input(INPUT_POST, 'server_name', FILTER_SANITIZE_SPECIAL_CHARS);
	$server_alias = filter_input(INPUT_POST, 'server_alias', FILTER_SANITIZE_SPECIAL_CHARS);
	$server_group = filter_input(INPUT_POST, 'server_group', FILTER_SANITIZE_SPECIAL_CHARS);
	$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
	$trusted = filter_input(INPUT_POST, 'trusted', FILTER_SANITIZE_SPECIAL_CHARS);
	$sql_array = array();
	if (isset($server_name) && !empty($server_name) && isset($id) && !empty($id) && is_numeric($id)){
	    $sql_array[] = "`server_name`='$server_name'";
	    if (is_numeric($distro) && is_numeric($distro_ver)){
		$sql_array[] = "`distro_id`=$distro";
		$sql_array[] = "`distro_version`=$distro_ver";
	    }
	    if (isset($server_alias) && !empty($server_alias)){
		$sql_array[] = "`server_alias`='$server_alias'";
	    }
	    if (isset($server_group) && !empty($server_group)){
		$sql_array[] = "`server_group`='$server_group'";
	    }
	    if (isset($server_ip) && !empty($server_ip)){
		$sql_array = "`server_ip`='$server_ip'";
	    }
	    if (isset($trusted) && !empty($trusted)){
		$sql_array[] = "`trusted`=1";
	    } else {
		$sql_array[] = "`trusted`=0";
	    }
	    $replacement_parts = implode(", ", $sql_array);
	    $sql = "UPDATE `servers` SET $replacement_parts WHERE `id`='$id';";
	    $link = mysqli_connect(DB_HOST,DB_USER,DB_PASS);
	    mysqli_select_db($link, DB_NAME);
	    $old_server_name_sql = "SELECT `server_name` FROM `servers` WHERE `id`=$id LIMIT 1;";
	    $old_server_name_res = mysqli_query($link, $old_server_name_sql) or die(mysqli_error());
	    $old_server_name_row = mysqli_fetch_array($old_server_name_res) or die(mysqli_error());
	    $old_server_name = $old_server_name_row['server_name'];
	    $patch_update_sql = "UPDATE `patches` SET `server_name`='$server_name' WHERE `server_name`='$old_server_name'; UPDATE `patch_allpackages` SET `server_name`='$server_name' WHERE `server_name`='$old_server_name';";
	    mysqli_query($link, $patch_update_sql);
	    mysqli_query($link, $sql);
	    mysqli_close($link);
	    $_SESSION['good_notice'] = "$server_name modified! I got no joke for this one. Sad day.";
	    sleep(1);
	    header('location:'.BASE_PATH."edit_server?id=$id");
	} else {
	    $_SESSION['error_notice'] = "A required field was not filled in";
	}
    } else {
	header('location:'.BASE_PATH."edit_user?id=$id");
	exit();
    }
} else {
    $_SESSION['error_notice'] = "You do not have permission to add users. This even thas been logged, and the admin has been notified.";
    header('location:'.BASE_PATH);
    exit();
}
?>
