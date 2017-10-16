<?php
include '../lib/db_config.php';
$client_key = filter_input(INPUT_SERVER, 'HTTP_X_CLIENT_KEY');
$client_check_sql = "SELECT `server_name` FROM `servers` WHERE `client_key` = '$client_key' AND `trusted`=1 LIMIT 1;";
$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
mysqli_select_db($link, DB_NAME);
$client_check_res = mysqli_query($link, $client_check_sql);
if (mysqli_num_rows($client_check_res) == 1) {
    $row = mysqli_fetch_array($client_check_res);
    $server_name = $row['server_name'];
    $data = file_get_contents("php://input");
    mysqli_query($link, "DELETE FROM `patch_allpackages` WHERE `server_name`='$server_name';");
    $package_array = explode("\n", $data);
    foreach ($package_array as $val) {
	$tmp_array = explode(":::", $val);
	if (count($tmp_array) > 1) {
	    $package_name = $tmp_array[0];
	    $package_version = $tmp_array[1];
	    $sql = "INSERT INTO patch_allpackages(server_name,package_name,package_version) VALUES('$server_name','$package_name','$package_version');";
	    mysqli_query($link, $sql);
	}
    }
}
mysqli_close($link);
