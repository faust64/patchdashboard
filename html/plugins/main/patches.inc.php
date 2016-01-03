<?php
/*
 * Fail-safe check. Ensures that they go through the main page (and are authenticated to use this page
 */
    if (!isset($index_check) || $index_check != "active") { exit(); }
    $supressed = array("nadda");
    $supressed_list = "";
    foreach($supressed as $val) { $supressed_list .= " '$val'"; }
    $supressed_list = str_replace("' '","', '",$supressed_list);
    $link = mysql_connect(DB_HOST,DB_USER,DB_PASS);
    mysql_select_db(DB_NAME,$link);
    $nsupressed_sql = "SELECT COUNT(DISTINCT(`server_name`)) AS total_needing_patched FROM `patches` WHERE `package_name` NOT IN (SELECT `package_name` FROM `supressed`) AND package_name !='';";
    $nsupressed_res = mysql_query($nsupressed_sql);
    $nsupressed_row = mysql_fetch_array($nsupressed_res);
    $nsupressed_total = $nsupressed_row['total_needing_patched'];
    if (isset($_GET['orderby'])) {
	switch($_GET['orderby']) {
	    case 'total': $orderfield = 'total'; break;
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
	$toggle_sort_name = "patches?orderby=server_name&order=desc";
    } else {
	$toggle_sort_name = "patches?orderby=server_name&order=asc";
    }
    if ($orderfield == 'total' && $orderscheme == 'ASC') {
	$toggle_sort_count = "patches?orderby=total&order=desc";
    } else {
	$toggle_sort_count = "patches?orderby=total&order=asc";
    }
    $order = "ORDER BY $orderfield $orderscheme";
    $sql1 = "SELECT s.server_name AS server_name, s.server_alias AS server_alias, d.icon_path AS icon_path, COUNT(p.package_name) AS total from distro d, servers s LEFT JOIN patches p ON s.server_name = p.server_name WHERE s.trusted = 1 AND s.distro_id = d.id AND p.package_name NOT IN (SELECT package_name FROM supressed) GROUP BY s.server_name $order;";
    $res1 = mysql_query($sql1);
    $table = "";
    $total_count = 0;
    $server_count = 0;
    $base_path=BASE_PATH;
    while ($row1 = mysql_fetch_assoc($res1)) {
	$server_count++;
	$server_name = $row1['server_name'];
	$server_alias = $row1['server_alias'];
	$count = $row1['total'];
	$dist_img = $row1['icon_path'];
	$total_count = $total_count + $count;
	$table .= "<tr><td><a href='{$base_path}patches/server/$server_name'><img src='$dist_img' height='32' width='32' border='0'>&nbsp;$server_alias</a></td><td>$count</td></tr>";
    }
    mysql_close($link);
    $percent_needing_upgrade = round((($nsupressed_total / $server_count)*100));
    $percent_good_to_go = 100 - $percent_needing_upgrade;
    if ($percent_good_to_go < 0) { $percent_good_to_go = 0; }
?>
    <div class="col-sm-9 col-md-9">
	<h1 class="page-header">Patch List</h1>
	<div class="chart">
	    <div class="percentage" data-percent="<?php echo $percent_good_to_go;?>"><span><?php echo $percent_good_to_go;?></span>%</div>
	    <div class="label" style="color:#0000FF">Percent of servers not needing upgrades/patches</div>
	</div>
	<div class="table-responsive">
	    <table class="table table-striped">
		<thead>
		    <tr>
			<th><a href="<?php echo $base_path?><?php echo $toggle_sort_name?>"><img width=21px height=21px src="<?php echo $base_path?>img/sort.png"></a>&nbsp;Server Name (<?php echo $server_count;?> servers)</th>
			<th><a href="<?php echo $base_path?><?php echo $toggle_sort_count?>"><img width=21px height=21px src="<?php echo $base_path?>img/sort.png"></a>&nbsp;Patch Count (<?php echo $total_count;?> total patches available)</th>
		    </tr>
		</thead>
		<tbody>
<?php echo $table;?>
		</tbody>
	    </table>
	</div>
    </div>
