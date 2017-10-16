<?php
session_start();
include '../../lib/db_config.php';
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == "true") {
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    $reboot = filter_input(INPUT_GET, 'reboot', FILTER_SANITIZE_NUMBER_INT);
    if (isset($id) && !empty($id) && is_numeric($id)) {
        $sql = "SELECT * FROM `servers` WHERE `id`=$id LIMIT 1;";
        $link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
        mysqli_select_db($link, DB_NAME);
        if (isset($reboot) && !empty($reboot) && $reboot == 1) {
            $reboot_sql = "UPDATE `servers` SET `reboot_cmd_sent`=1 WHERE `id`=$id LIMIT 1;";
            mysqli_query($link, $reboot_sql);
            $message_injection = "Server also set to reboot after installing patches.";
        } else {
            $message_injection = "";
        }
        $res = mysqli_query($link, $sql);
        $row1 = mysqli_fetch_array($res);
        $server_name = $row1['server_name'];
        $suppression_sql = "SELECT * FROM `supressed` WHERE `server_name` IN (0,'$server_name');";
        $suppression_res = mysqli_query($link, $sql);
        $suppression_array = array();
        while ($suppression_row = mysqli_fetch_assoc($suppression_res)) {
	    if (isset($suppression_row['package_name'])) {
		$suppression_array[] = "'" . $suppression_row['package_name'] . "'";
	    }
        }
        $sql3 = "UPDATE `patches` SET `to_upgrade`=1 WHERE `to_upgrade`=0 AND `server_name`='$server_name' AND `upgraded`=0";
	if (count($suppression_array) > 0) {
            $suppression_list = implode(", ", $suppression_array);
            $sql3 .= " AND `package_name` NOT IN ($suppression_list);";
	}
        mysqli_query($link, $sql3);
        $_SESSION['good_notice'] = "All non-suppressed packages set to upgrade on <strong>$server_name</strong>. $message_injection Bionic machine closer than I thought.";
        header('location:' . BASE_PATH . "patches/server/$server_name");
        exit();
    }
    mysqli_close($link);
} else {
    session_unset();
    header('location:' . BASE_PATH);
    exit();
}
