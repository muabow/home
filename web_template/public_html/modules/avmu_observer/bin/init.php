<?php
	$env_path = str_replace(basename(__FILE__), "", realpath(__FILE__));
	echo ${env_path}
	shell_exec("{$env_path}avmu_observer_monitor.sh > /dev/null 2>/dev/null &");
?>

