<?php
/*
 * Fail-safe check. Ensures that they go through the main page (and are authenticated to use this page
 */
    if (!isset($index_check) || $index_check != "active") { exit(); }
    include 'inc/supressed_patches.inc.php';
    $link = mysql_connect(DB_HOST,DB_USER,DB_PASS);
    mysql_select_db(DB_NAME,$link);
    $server_name = filter_var($_GET['server'],FILTER_SANITIZE_MAGIC_QUOTES);
    $table = "";
    if (isset($_GET['orderby'])) {
	switch($_GET['orderby']) {
	    case 'version': $orderfield = 'package_version'; break;
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
	$toggle_sort_name = "packages/server/$server_name?orderby=package_name&order=desc";
    } else {
	$toggle_sort_name = "packages/server/$server_name?orderby=package_name&order=asc";
    }
    if ($orderfield == 'package_version' && $orderscheme == 'ASC') {
	$toggle_sort_vers = "packages/server/$server_name?orderby=version&order=desc";
    } else {
	$toggle_sort_vers = "packages/server/$server_name?orderby=version&order=asc";
    }
    $order = "ORDER BY $orderfield $orderscheme";
    $sql1 = "SELECT s.server_alias AS server_alias, p.package_name AS package_name, p.package_version AS package_version FROM servers s, patch_allpackages p WHERE s.server_name = '$server_name' AND p.server_name = s.server_name $order;";
    $res1 = mysql_query($sql1);
    $base_path = BASE_PATH;
    $server_alias = false;
    while ($row1 = mysql_fetch_assoc($res1)) {
	if ($server_alias == false) { $server_alias = $row1['server_alias']; }
	$package_name = $row1['package_name'];
	$package_version = $row1['package_version'];
	$table .= "<tr><td><a href='${base_path}search/exact/$package_name' style='color:green'>$package_name</a></td><td>$package_version</td></tr>";
    }
?>
    <h1 class="page-header">Full Package List</h1>
    <h3 class="sub-header"><?php echo $server_alias;?></h3>
    <div class="container">
	<div class="table-responsive">
	    <table class="table table-striped">
		<thead>
		    <tr>
			<th><a href="<?php echo $base_path?><?php echo $toggle_sort_name?>"><img width=21px height=21px src="<?php echo $base_path?>img/sort.png"></a>&nbsp;Package Name</th>
			<th><a href="<?php echo $base_path?><?php echo $toggle_sort_vers?>"><img width=21px height=21px src="<?php echo $base_path?>img/sort.png"></a>&nbsp;Package Version</th>
		    </tr>
		</thead>
		<tbody>
<?php echo $table;?>
		</tbody>
	    </table>
	</div>
    </div>
