<?php
	$env_path = str_replace(basename(__FILE__), "", realpath(__FILE__));

	shell_exec("{$env_path}log_system_check.sh &");
?>

