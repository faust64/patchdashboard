<?php
/*
 * Fail-safe check. Ensures that they go through the main page (and are authenticated to use this page
 */
    if (!isset($index_check) || $index_check != "active") { exit(); }
    $link = mysqli_connect(DB_HOST,DB_USER,DB_PASS);
    mysqli_select_db($link, DB_NAME);
    if (isset($_GET['orderby'])) {
	switch($_GET['orderby']) {
	    case 'group': $orderfield = 'admin'; break;
	    case 'alerts': $orderfield = 'receive_alerts'; break;
	    case 'login': $orderfield = 'last_seen'; break;
	    case 'email': $orderfield = 'email'; break;
	    default: $orderfield = 'user_id'; break;
	}
    } else {
	$orderfield = 'user_id';
    }
    if (isset($_GET['order'])) {
	switch ($_GET['order']) {
	    case 'desc': $orderscheme = 'DESC'; break;
	    default: $orderscheme = 'ASC'; break;
	}
    } else {
	$orderscheme = 'ASC';
    }
    if ($orderfield == 'user_id' && $orderscheme == 'ASC') {
	$toggle_sort_name = "manage_users?orderby=name&order=desc";
    } else {
	$toggle_sort_name = "manage_users?orderby=name&order=asc";
    }
    if ($orderfield == 'admin' && $orderscheme == 'ASC') {
	$toggle_sort_grp = "manage_users?orderby=group&order=desc";
    } else {
	$toggle_sort_grp = "manage_users?orderby=group&order=asc";
    }
    if ($orderfield == 'receive_alerts' && $orderscheme == 'ASC') {
	$toggle_sort_alert = "manage_users?orderby=alerts&order=desc";
    } else {
	$toggle_sort_alert = "manage_users?orderby=alerts&order=asc";
    }
    if ($orderfield == 'last_seen' && $orderscheme == 'ASC') {
	$toggle_sort_login = "manage_users?orderby=login&order=desc";
    } else {
	$toggle_sort_login = "manage_users?orderby=login&order=asc";
    }
    if ($orderfield == 'email' && $orderscheme == 'ASC') {
	$toggle_sort_email = "manage_users?orderby=email&order=desc";
    } else {
	$toggle_sort_email = "manage_users?orderby=email&order=asc";
    }
    $order = "ORDER BY $orderfield $orderscheme";
    $sql = "SELECT * FROM users $order;";
    $res = mysqli_query($link, $sql);
    $base_path = BASE_PATH;
    $table = "";
    while ($row = mysqli_fetch_assoc($res)) {
	$id = $row['id'];
	$username = $row['user_id'];
	$active = $row['active'];
	$email = $row['email'];
	if ($row['admin'] == 1) { $group = "Admin"; }
	else { $group = "User"; }
	$last_seen = $row['last_seen'] == "0000-00-00 00:00:00" ? "Never" : $row['last_seen'];
	$alerts = $row['receive_alerts'] == 1 ? "Yes": "No";
        if ($active == 1) {
	    $active_action = "<a href='".BASE_PATH."plugins/admin/deactivate_user.inc.php?id=$id' style='color:red;'>Deactivate</a>";
        } else {
	    $active_action = "<a href='".BASE_PATH."plugins/admin/activate_user.inc.php?id=$id' style='color:green;'>Reactivate</a>";
        }
	$table .="<tr><td>$username</td><td>$email</td><td>$group</td><td>$last_seen</td><td>$alerts</td><td><a href='".BASE_PATH."edit_user?id=$id'>Edit</a> | $active_action | <a href='".BASE_PATH."plugins/admin/delete_user.inc.php?id=$id'>Delete</a></td></tr>";
    }
?>
    <h1 class="page-header">List Users</h1>
    <div class="container">
	<div class="table-responsive">
	    <table class="table table-striped">
		<thead>
		    <tr>
			<th><a href="<?php echo $base_path?><?php echo $toggle_sort_name?>"><img width=21px height=21px src="<?php echo $base_path?>img/sort.png"></a>&nbsp;Username</th>
			<th><a href="<?php echo $base_path?><?php echo $toggle_sort_email?>"><img width=21px height=21px src="<?php echo $base_path?>img/sort.png"></a>&nbsp;E-mail</th>
			<th><a href="<?php echo $base_path?><?php echo $toggle_sort_grp?>"><img width=21px height=21px src="<?php echo $base_path?>img/sort.png"></a>&nbsp;Group</th>
			<th><a href="<?php echo $base_path?><?php echo $toggle_sort_login?>"><img width=21px height=21px src="<?php echo $base_path?>img/sort.png"></a>&nbsp;Last Login</th>
			<th><a href="<?php echo $base_path?><?php echo $toggle_sort_alert?>"><img width=21px height=21px src="<?php echo $base_path?>img/sort.png"></a>&nbsp;Notified</th>
			<th>Actions</th>
		    </tr>
		</thead>
		<tbody>
<?php echo $table;?>
		</tbody>
	    </table>
	</div>
    </div>
