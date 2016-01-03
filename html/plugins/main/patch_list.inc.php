<?php
/*
 * Fail-safe check. Ensures that they go through the main page (and are authenticated to use this page
 */
    if (!isset($index_check) || $index_check != "active") { exit(); }
    include 'inc/supressed_patches.inc.php';
    if (!isset($supressed)) { $supressed = array(); }
    $link = mysql_connect(DB_HOST,DB_USER,DB_PASS);
    $package_count = 0;
    $npm_count = 0;
    $base_path = BASE_PATH;
    mysql_select_db(DB_NAME,$link);
    $server_name = filter_var($_GET['server'],FILTER_SANITIZE_MAGIC_QUOTES);
    $distro_sql1 = "SELECT s.server_alias AS server_alias, d.upgrade_command AS upgrade_command FROM servers s, distro d WHERE server_name = '$server_name' AND d.id = s.distro_id;";
    $distro_res1 = mysql_query($distro_sql1);
    $distro_row1 = mysql_fetch_array($distro_res1);
    $server_alias = $distro_row1['server_alias'];
    $apt_cmd = $distro_row1['upgrade_command'];
    if (isset($_GET['orderby'])) {
	switch($_GET['orderby']) {
	    case 'urgency': $orderfield = 'urgency'; break;
	    default: $orderfield = 'package_name'; break;
	}
    } else {
	$orderfield = 'package_name';
    }
    if (isset($_GET['order'])) {
	switch ($_GET['order']) {
	    case 'desc': $orderscheme = 'DESC'; break;
	    default: $orderscheme = 'ASC'; break;
	}
    } else {
	$orderscheme = 'ASC';
    }
    if ($orderfield == 'package_name' && $orderscheme == 'ASC') {
	$toggle_sort_name = "patches/server/$server_name?orderby=package_name&order=desc";
    } else {
	$toggle_sort_name = "patches/server/$server_name?orderby=package_name&order=asc";
    }
    if ($orderfield == 'urgency' && $orderscheme == 'ASC') {
	$toggle_sort_urg = "patches/server/$server_name?orderby=urgency&order=desc";
    } else {
	$toggle_sort_urg = "patches/server/$server_name?orderby=urgency&order=asc";
    }
    $order = "ORDER BY $orderfield $orderscheme";
    $sql1 = "SELECT * FROM patches WHERE server_name='$server_name' $order;";
    $res1 = mysql_query($sql1);
    $table = "";
    while ($row1 = mysql_fetch_assoc($res1)) {
	$package_name = $row1['package_name'];
	$bug_url = $row1['bug_url'];
	$package_name_orig = $package_name;
	if (in_array($package_name,$supressed)) {
	    $package_name .= " <strong>(SUPRESSED)</strong>";
	} else {
	    if (stristr($bug_url, 'snyk.io') == FALSE) { $apt_cmd .= " $package_name"; }
	    else { $npm_count++; }
	    $package_count++;
	}
	$current = $row1['current'];
	$new = $row1['new'];
	$urgency = $row1['urgency'];
	$bug_url = $row1['bug_url'];
	if ($bug_url != '') {
	    if (stristr($bug_url,'snyk.io')) {
		$url_array = explode("/",$bug_url);
		$bug = end($url_array);
		$url = "<td><a href='$bug_url' style='color:black'>Snyk ID #$bug</a></td>";
	    } else if (stristr($bug_url,'debian')) {
		$url_array = explode("/",$bug_url);
		$cve = end($url_array);
		$url = "<td><a href='$bug_url' style='color:black'>Debian $cve</a></td>";
	    } else{
		$url_array = explode("/",$bug_url);
		$bug = end($url_array);
		$url = "<td><a href='$bug_url' style='color:black'>Launchpad Bug #$bug</a></td>";
	    }
        }
	if (in_array($urgency,array('high','emergency'))) {
	    $urgency = "<td style='color:red'><a href='http://www.ubuntuupdates.org/package/core/precise/main/updates/$package_name_orig' style='color:red' target='_blank'>$urgency</a></td>";
	} elseif ($urgency == "medium") {
	    $urgency = "<td style='color:#FF8C00'><a href='http://www.ubuntuupdates.org/package/core/precise/main/updates/$package_name_orig' style='color:#FF8C00' target='_blank'>medium</a></td>";
	} elseif ($urgency == "low") {
	    $urgency = "<td><a href='http://www.ubuntuupdates.org/package/core/precise/main/updates/$package_name_orig' style='color:black' target='_blank'>$urgency</a></td>";
	} else {
	    $urgency = "<td>$urgency</td>";
	}
	if ($bug_url != '' && stristr($bug_url,'snyk.io')) {
	    $urgency = "<td>medium (nodejs)</td>";
	}
	$table .= "<tr><td><a href='${base_path}search/exact/$package_name_orig' style='color:green'>$package_name</a></td><td>$current</td><td>$new</td>$urgency " . (isset($url) ? $url : "") . "</tr>";
    }
    if ($package_count - $npm_count == 0) {
	$apt_cmd = "";
    } else {
	$apt_cmd = "<code>$apt_cmd</code>";
    }
?>
    <h1 class="page-header">List Packages to Install</h1>
    <h2 class="sub-header"><?php echo $server_alias;?>(<a href="<?php echo BASE_PATH;?>packages/server/<?php echo $server_name;?>">List all installed packages</a>)</h2><br />
    <p>(<a href="<?php echo BASE_PATH;?>plugins/main/install_all.inc.php?id=<?php echo $id;?>" style="color:green;">Install all patches not suppressed</a> | <a href="<?php echo BASE_PATH;?>plugins/main/install_all.inc.php?reboot=1&id=<?php echo $id;?>" style="color:red;">Install all patches not suppressed and reboot</a>)</p>
    <div class="container">
	<div class="table-responsive">
	    <table class="table table-striped">
		<thead>
		    <tr>
			<th><a href="<?php echo $base_path?><?php echo $toggle_sort_name?>"><img width=21px height=21px src="<?php echo $base_path?>img/sort.png"></a>&nbsp;Package Name</th>
			<th>Current Version</th>
			<th>New Version</th>
			<th><a href="<?php echo $base_path?><?php echo $toggle_sort_urg?>"><img width=21px height=21px src="<?php echo $base_path?>img/sort.png"></a>&nbsp;Urgency Level</th>
			<th>Bug Report Name/Page</th>
		    </tr>
		</thead>
		<tbody>
<?php echo $table;?>
		</tbody>
	    </table>
	</div>
<?php echo $apt_cmd;?>
    </div>
</div>
</div>
