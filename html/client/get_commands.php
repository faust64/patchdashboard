<?php
include '../lib/db_config.php';
$client_key = filter_input(INPUT_SERVER, 'HTTP_X_CLIENT_KEY');
if (isset($client_key) && !empty($client_key)) {
    $sql = "SELECT * FROM `servers` WHERE `client_key`='$client_key' and `trusted`= 1;";
    $link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
    mysql_select_db(DB_NAME, $link);
    $res = mysql_query($sql);
    if (mysql_num_rows($res) == 0) {
        $sql_check = "SELECT * FROM `servers` WHERE `client_key`='$client_key';";
        $check_res = mysql_query($sql_check);
        if (mysql_num_rows($check_res) == 0) {
            $server_ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
            $sql2 = "INSERT INTO `servers`(`server_name`,`distro_id`,`distro_version`,`server_ip`,`client_key`) VALUES('UNKNOWN SERVER',0,0,'$server_ip',$client_key');";
            mysql_query($sql2);
        }
    } else {
        $row1 = mysql_fetch_array($res);
        $server_name = $row1['server_name'];
        $id = $row1['id'];
        $to_reboot = $row1['reboot_cmd_sent'];
        $sql2 = "UPDATE `servers` SET `last_seen` = NOW() WHERE `client_key`='$client_key';";
        mysql_query($sql2);
        $sql3 = "SELECT `package_name` FROM `patches` WHERE `to_upgrade`=1 and `upgraded`=0 AND `server_name`='$server_name';";
        $res3 = mysql_query($sql3);
        $suppression_array = array();
        $package_array = array();
        if (mysql_num_rows($res3) > 0){
            $suppression_sql = "SELECT * FROM `supressed` WHERE `server_name` IN (0,'$server_name');";
            $suppression_res = mysql_query($sql);
            while ($suppression_row = mysql_fetch_assoc($suppression_res)){
                $suppression_array[] = $suppression_row['package_name'];
            }
            while ($row3 = mysql_fetch_assoc($res3)){
                $package_name = $row3['package_name'];
                if (!in_array($package_name, $supressed_array)){
                    $package_array[] = $package_name;
                }
            }
            foreach ($package_array as $val){
                mysql_query("UPDATE `patches` SET `to_upgrade` = 0, `upgraded` = 1 WHERE `server_name` = '$server_name' AND `package_name` = '$val' LIMIT 1;");
            }
            $package_string = implode(" ", $package_array);
        }
        //CMD GOES HERE
        $company = YOUR_COMPANY;
        $company_sql = "SELECT * FROM `company` WHERE `display_name`='$company' LIMIT 1;";
        $company_res = mysql_query($company_sql);
        $company_row = mysql_fetch_array($company_res);
        $key_to_check=$company_row['install_key'];
        $cmd_sql = "SELECT d.upgrade_command as cmd from servers s left join distro d on s.distro_id=d.id where s.server_name='$server_name' LIMIT 1;";
        $cmd_res = mysql_query($cmd_sql);
        $cmd_row = mysql_fetch_array($cmd_res);
        $cmd = $cmd_row['cmd'];
        // If it needs to be rebooted, lets add it on to the end of the rest of the cmd.
        if ($to_reboot == 1){
            $reboot_cmd_sent_sql = "UPDATE `servers` SET `reboot_cmd_sent`=0 WHERE `id`=$id LIMIT 1;";
            mysql_query($reboot_cmd_sent_sql);
            $add_after = "/sbin/reboot";
        }
        else{
            $add_after = "";
        }
        echo "$key_to_check\n$cmd $package_string;$add_after";
    }
}
mysql_close($link);