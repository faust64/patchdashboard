<?php

include '../lib/db_config.php';
$client_key = filter_input(INPUT_SERVER, 'HTTP_X_CLIENT_KEY');
$client_host = filter_input(INPUT_SERVER, 'HTTP_X_CLIENT_HOST');
$client_os = filter_input(INPUT_SERVER, 'HTTP_X_CLIENT_OS');
$client_os_ver = filter_input(INPUT_SERVER, 'HTTP_X_CLIENT_OS_VER');
if (isset($client_key) && !empty($client_key)) {
    $sql = "SELECT * FROM `servers` WHERE `client_key`='$client_key' and `trusted`= 1;";
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
    mysqli_select_db($link, DB_NAME);
    $company = YOUR_COMPANY;
    $company_sql = "SELECT * FROM `company` WHERE `display_name`='$company' LIMIT 1;";
    $company_res = mysqli_query($link, $company_sql);
    $company_row = mysqli_fetch_array($company_res);
    $key_to_check_array = explode(" ",$company_row['install_key']);
    $key_to_check = $key_to_check_array[0];
    $res = mysqli_query($link, $sql);
    if (mysqli_num_rows($res) == 0) {
        $sql_check = "SELECT * FROM `servers` WHERE `client_key`='$client_key';";
        $check_res = mysqli_query($link, $sql_check);
        if (mysqli_num_rows($check_res) == 0) {
            $server_ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
            $os_versql = "SELECT `id` FROM `distro` WHERE `distro_name` LIKE '$client_os' LIMIT 1;";
	    $os_version = mysqli_query($link, $os_versql);
	    mysqli_data_seek($os_version, 0);
	    $os_version = mysqli_fetch_row($os_version)[0];
            $os_dissql = "SELECT `id` FROM `distro_version` WHERE `distro_id`='$os_version' AND `version_num` LIKE '$client_os_ver%' LIMIT 1;";
	    $os_distro = mysqli_query($link, $os_dissql);
	    mysqli_data_seek($os_distro, 0);
	    $os_distro = mysqli_fetch_row($os_distro)[0];
            if (empty($client_host)) {$client_host = 'UNKNOWN SERVER';}
            if (empty($client_os)) {$os_id = 0;}
            if (empty($client_os_ver)) {$client_os_ver = 0;}
            $sql2 = "INSERT INTO `servers`(`server_name`,`server_alias`,`distro_id`,`distro_version`,`server_ip`,`client_key`) VALUES('$client_host','$client_host','$os_version','$os_distro','$server_ip','$client_key');";
            mysqli_query($link, $sql2);
        }
        $out = "allowed='FALSE'
key_to_check='FALSE'
check_patches='FALSE'";
    } else {
        $time_sql = "SELECT * FROM `servers` WHERE `last_checked` < NOW() - INTERVAL 20 MINUTE AND `client_key`='$client_key' LIMIT 1;";
        $time_res = mysqli_query($link, $time_sql);
        if (mysqli_num_rows($time_res) == 1) {
            $CHECK_PATCHES = "TRUE";
            mysqli_query($link, "UPDATE `servers` SET `last_checked` = NOW() WHERE `client_key` = '$client_key' LIMIT 1;");
            #echo "UPDATE `servers` SET `last_checked` = NOW() WHERE `client_key` = '$client_key' LIMIT 1;";
        } else {
            $CHECK_PATCHES = "FALSE";
        }
        $sql2 = "UPDATE `servers` SET `last_seen` = NOW() WHERE `client_key`='$client_key';";
        #echo $sql2;
        mysqli_query($link, $sql2);
        $out = "allowed='TRUE'
key_to_check='$key_to_check'
check_patches='$CHECK_PATCHES'";
    }
} else {
    $out = "allowed='FALSE'
key_to_check='FALSE'
check_patches='FALSE'";
}
echo $out;
mysqli_close($link);
