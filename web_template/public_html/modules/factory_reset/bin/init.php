<?php
	$monitor_script = "factoryReset_monitor.sh";
	$env_path = str_replace(basename(__FILE__), "", realpath(__FILE__));
	echo ${env_path}
	shell_exec("{$env_path}{$monitor_script} > /dev/null 2>/dev/null &");
?>

