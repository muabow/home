<?php
	$env_path = str_replace(basename(__FILE__), "", realpath(__FILE__));

	shell_exec("{$env_path}audio_player_monitor.sh > /dev/null 2>/dev/null &");
?>

