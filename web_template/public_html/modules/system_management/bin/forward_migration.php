<?php
	$moduleName = "system_management";
	$backupVersion = "0.0.0.0";
	$currentVersion = "0.0.0.0";



	# Check Version Info
	if ($argc > 1) { $backupVersion = $argv[1]; }
	if ($argc > 2) { $currentVersion = $argv[2]; }

	# Operation order : backward_migration.php --> upgrade --> forward_migration.php

	# ------------------------------------------------------------------------------------
	# version	:	description
	# ------------------------------------------------------------------------------------
	# 4.7		:	add table column(mng_svr_info -> mng_svr_version, mng_svr_enabled, mng_svr_extend)
	# ------------------------------------------------------------------------------------
	
	$db_path = "/opt/interm/conf/config-manager-server.db";
	
	$db = new SQLite3($db_path);
	
	$query = "select sql from sqlite_master where name = 'mng_svr_info' and sql like '%" . "mng_svr_extend" . "%';";
	$result = $db->query($query);
	
	$row = $result->fetchArray(1);
	
	if( gettype($row) != "array" ) {
		
		$query = "alter table mng_svr_info add column mng_svr_version TEXTNOT;";
		$result = $db->query($query);
		
		$query = "alter table mng_svr_info add column mng_svr_enabled INTEGERNOT;";
		$result = $db->query($query);
		
		$query = "alter table mng_svr_info add column mng_svr_extend INTEGER;";
		$result = $db->query($query);
		
	} else {
		echo "exist\n";
	}
	
	$db->close();

	echo "# Forward Migration - $moduleName\n";



	### TODO : Migration Code
	echo "TODO : Migration Code\n";
	echo "$backupVersion => $currentVersion\n";



	echo "# Forward Migration - $moduleName : Done\n";
	echo "\n";
?>

