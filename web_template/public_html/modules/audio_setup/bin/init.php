<?php
	$env_path = str_replace(basename(__FILE__), "", realpath(__FILE__));

	$json_dev_info = json_decode(file_get_contents("/opt/interm/conf/config-device-info.json"));

	$arr_dev_info = array();
	$arr_dev_info[] = array("audio_server", $json_dev_info->port->Audio->in);
	$arr_dev_info[] = array("audio_client", $json_dev_info->port->Audio->out);

	$sql_handler = new SQLite3("{$env_path}/../conf/audio_io.db");

	foreach( $arr_dev_info as $dev_info ) {
		if( $dev_info[1] == 0 ) {
			$sql_handler->query("update {$dev_info[0]} set \"module_use\"=\"disabled\";");
		}
	}

	$sql_handler->close();

	shell_exec("{$env_path}audio_server_monitor.sh > /dev/null 2>/dev/null &");
	shell_exec("{$env_path}audio_client_monitor.sh > /dev/null 2>/dev/null &");
?>
