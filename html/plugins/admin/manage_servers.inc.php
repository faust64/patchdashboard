<?php
/*
 * Fail-safe check. Ensures that they go through the main page (and are authenticated to use this page
 */
    if (!isset($index_check) || $index_check != "active") { exit(); }
    $link = mysql_connect(DB_HOST,DB_USER,DB_PASS);
    mysql_select_db(DB_NAME,$link);
    if (isset($_GET['orderby'])) {
	switch($_GET['orderby']) {
	    case 'group': $orderfield = 'server_group'; break;
	    case 'check': $orderfield = 'last_checked'; break;
	    case 'trust': $orderfield = 'trusted'; break;
	    default: $orderfield = 'server_name'; break;
	}
    } else {
	$orderfield = 'server_name';
    }
    if (isset($_GET['order'])) {
	switch ($_GET['order']) {
	    case 'desc': $orderscheme = 'DESC'; break;
	    default: $orderscheme = 'ASC'; break;
	}
    } else {
	$orderscheme = 'ASC';
    }
    if ($orderfield == 'server_name' && $orderscheme == 'ASC') {
	$toggle_sort_name = "manage_servers?orderby=server_name&order=desc";
    } else {
	$toggle_sort_name = "manage_servers?orderby=server_name&order=asc";
    }
    if ($orderfield == 'server_group' && $orderscheme == 'ASC') {
	$toggle_sort_grp = "manage_servers?orderby=group&order=desc";
    } else {
	$toggle_sort_grp = "manage_servers?orderby=group&order=asc";
    }
    if ($orderfield == 'last_checked' && $orderscheme == 'ASC') {
	$toggle_sort_chk = "manage_servers?orderby=check&order=desc";
    } else {
	$toggle_sort_chk = "manage_servers?orderby=check&order=asc";
    }
    if ($orderfield == 'trusted' && $orderscheme == 'ASC') {
	$toggle_sort_trust = "manage_servers?orderby=trust&order=desc";
    } else {
	$toggle_sort_trust = "manage_servers?orderby=trust&order=asc";
    }
    $order = "ORDER BY $orderfield $orderscheme";
    $sql = "SELECT * FROM servers $order;";
    $res = mysql_query($sql);
    $base_path = BASE_PATH;
    $table = "";
    $distro_array = array();
    $distro_map_sql = "SELECT d.distro_name as distro_name,dv.version_num as version_num, dv.id as version_id,d.id as distro_id FROM distro_version dv LEFT JOIN distro d on d.id=dv.distro_id;";
    $distro_map_res = mysql_query($distro_map_sql);
    while ($distro_map_row = mysql_fetch_assoc($distro_map_res)) {
	$distro_array[$distro_map_row['distro_id']][$distro_map_row['version_id']] = str_replace("_"," ",$distro_map_row['distro_name']." ".$distro_map_row['version_num']);
    }
    while ($row = mysql_fetch_assoc($res)) {
	$id = $row['id'];
	$server_name = $row['server_name'];
	$server_alias = $row['server_alias'];
	$server_group = $row['server_group'];
	$distro_id = $row['distro_id'];
	$server_ip = $row['server_ip'];
	$distro_version = $row['distro_version'];
	$distro_name = $distro_array[$distro_id][$distro_version];
	$client_key = $row['client_key'];
	$trusted = $row['trusted'];
	$last_seen = $row['last_seen'] == "0000-00-00 00:00:00" ? "Never" : $row['last_seen'];
        if ($trusted == 1) {
	    $active_action = "<a href='".BASE_PATH."plugins/admin/deactivate_server.inc.php?id=$id'>Deactivate/Distrust</a>";
	    $trust = "YES";
        } else {
	    $active_action = "<a href='".BASE_PATH."plugins/admin/activate_server.inc.php?id=$id'>Reactivate/Trust</a>";
	    $trust = "NO";
        }
	$table .= "<tr><td><span title=$server_name>$server_alias</span></td><td><div align='center'>$server_group</div></td><td><div align='center'>$distro_name</div></td><td><div align='center'>$server_ip</div></td><td><div align='center'>$trust</div></td><td><div align='center'>$last_seen</div></td><td><a href='".BASE_PATH."edit_server?id=$id'>Edit</a> | $active_action | <a href='".BASE_PATH."plugins/admin/delete_server.inc.php?id=$id'>Delete</a></td></tr>";
    }
?>
    <h1 class="page-header">All Servers</h1>
    <div class="container">
	<div class="table-responsive">
	    <table class="table table-striped">
		<thead>
		    <tr>
			<th><a href="<?php echo $base_path?><?php echo $toggle_sort_name?>"><img width=21px height=21px src="<?php echo $base_path?>img/sort.png"></a>&nbsp;Server Name (Alias)</th>
			<th><a href="<?php echo $base_path?><?php echo $toggle_sort_grp?>"><img width=21px height=21px src="<?php echo $base_path?>img/sort.png"></a>&nbsp;Group</th>
			<th><div align='center'>Distro</div></th>
			<th><div align='center'>Server IP</div></th>
			<th><a href="<?php echo $base_path?><?php echo $toggle_sort_trust?>"><img width=21px height=21px src="<?php echo $base_path?>img/sort.png"></a>&nbsp;Trust</th>
			<th><a href="<?php echo $base_path?><?php echo $toggle_sort_chk?>"><img width=21px height=21px src="<?php echo $base_path?>img/sort.png"></a>&nbsp;Last Check-in</th>
			<th><div align='center'>Actions</div></th>
		    </tr>
		</thead>
		<tbody>
<?php echo $table;?>
		</tbody>
	    </table>
	</div>
    </div>
