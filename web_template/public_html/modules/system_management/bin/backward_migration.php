<?php
	$moduleName = "system_management";
	$backupVersion = "0.0.0.0";
	$currentVersion = "0.0.0.0";



	# Check Version Info
	if ($argc > 1) { $backupVersion = $argv[1]; }
	if ($argc > 2) { $currentVersion = $argv[2]; }



	echo "# Backward Migration - $moduleName\n";



	### TODO : Migration Code
	echo "TODO : Migration Code\n";
	echo "$backupVersion => $currentVersion\n";



	echo "# Backward Migration - $moduleName : Done\n";
	echo "\n";
?>

