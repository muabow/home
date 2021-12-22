<?php
	$env_path = str_replace(basename(__FILE__), "", realpath(__FILE__));

	shell_exec("/usr/bin/php {$env_path}time_sync_daemon.php > /dev/null 2>/dev/null &");
?>
