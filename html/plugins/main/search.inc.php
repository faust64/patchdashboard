<?php
/*
 * Fail-safe check. Ensures that they go through the main page (and are authenticated to use this page
 */
    if (!isset($index_check) || $index_check != "active") { exit(); }
    include 'inc/supressed_patches.inc.php';
    $link = mysql_connect(DB_HOST,DB_USER,DB_PASS);
    mysql_select_db(DB_NAME,$link);
    $search = filter_var($_GET['package'],FILTER_SANITIZE_MAGIC_QUOTES);
    $emptytab = "<tr><td colspan=3><div align='center'><i>no matching record</i></div></td></tr>";
    if (isset($_GET['exact']) && $_GET['exact'] == "true") {
	$sql1 = "SELECT package_name, package_version, server_name FROM patch_allpackages WHERE package_name = '$search';";
	$sql2 = "SELECT s.server_name AS server_name, s.server_group AS server_group, COUNT(p.package_name) FROM servers s LEFT JOIN patches p ON s.server_name = p.server_name WHERE (server_name = '$search' OR server_alias = '$search' or server_group = '$search') AND p.package_name NOT IN (SELECT package_name FROM supressed) GROUP BY s.server_name;";
    } else {
	$sql1 = "SELECT package_name, package_version, server_name FROM patch_allpackages WHERE package_name LIKE '%$search%';";
	$sql2 = "SELECT s.server_name AS server_name, s.server_group AS server_group, COUNT(p.package_name) FROM servers s LEFT JOIN patches p ON s.server_name = p.server_name WHERE (s.server_name LIKE '%$search%' OR s.server_alias LIKE '%$search%' OR s.server_group LIKE '%$search%') AND p.package_name NOT IN (SELECT package_name FROM supressed) GROUP BY s.server_name;";
    }
    $res1 = mysql_query($sql1);
    $res2 = mysql_query($sql2);
    $countpkg = $counthost = 0;
    $base_path = BASE_PATH;
    $tablepkg = $tablehost = "";
    while ($row1 = mysql_fetch_assoc($res1)) {
	$countpkg++;
	$package_name = $row1['package_name'];
	$package_version = $row1['package_version'];
	$server_name = $row1['server_name'];
	$tablepkg .= "<tr><td><a href='${base_path}patches/server/$server_name' style='color:black'>$server_name</a></td><td><a href='${base_path}search/exact/$package_name' style='color:green'>$package_name</a></td><td>$package_version</td></tr>";
    }
    while ($row2 = mysql_fetch_assoc($res2)) {
	$counthost++;
	$server_name = $row2['server_name'];
	$server_group = $row2['server_group'];
	$patches_count = $row2['patches_count'];
	$tablehost .= "<tr><td><a href='${base_path}patches/server/$server_name' style='color:black'>$server_name</a><td><td><a href='${base_path}search/exact/$server_group' style='color:green'>$server_group</a></td><td><a href='${base_path}patches/server/$server_name' style='color:black'>$patches_count</td></tr>";
    }
?>
    <h1 class="page-header">Search</h1>
    <h3 class="sub-header">Results for search "<?php echo $search;?>" (Found <a href='#packagesearch'><?php echo $countpkg;?> packages</a> & <a href='#hostsearch'><?php echo $counthost;?> hosts</a> matching)</h3>
    <div class="container">
	<div class="table-responsive">
	    <table id='packagesearch' class="table table-striped">
		<thead>
		    <tr>
			<th>Server Name</th>
			<th>Package Name</th>
			<th>Package Version</th>
		    </tr>
		</thead>
		<tbody>
<?php
    if ($countpkg > 0) {
	echo $tablepkg;
    } else {
	echo $emptytab;
    }
?>
		</tbody>
	    </table>
	    <table id='hostsearch' class="table table-striped">
		<thead>
		    <tr>
		    <th>Server Name</th>
		    <th>Server Group</th>
		    <th>Patches count</th>
		    </tr>
		</thead>
		<tbody>
<?php
    if ($counthost > 0) {
	echo $tablehost;
    } else {
	echo $emptytab;
    }
?>
		</tbody>
	    </table>
	</div>
    </div>
