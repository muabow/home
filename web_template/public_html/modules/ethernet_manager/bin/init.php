<?php
	$daemon = "ethernetManager.sh";
	$env_path = str_replace(basename(__FILE__), "", realpath(__FILE__));
	echo ${env_path}
	shell_exec("{$env_path}{$daemon} > /dev/null 2>/dev/null &");
?>

