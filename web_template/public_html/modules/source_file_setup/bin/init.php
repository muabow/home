<?php
	$env_path = str_replace(basename(__FILE__), "", realpath(__FILE__));

	$json_dev_info = json_decode(file_get_contents("/opt/interm/conf/config-device-info.json"));

    $arr_dev_info = array();
    $arr_dev_info[] = array("source_file_server", $json_dev_info->port->Audio->in);
    $arr_dev_info[] = array("source_file_client", $json_dev_info->port->Audio->out);

    $sql_handler = new SQLite3("{$env_path}/../conf/source_file_io.db");

    foreach( $arr_dev_info as $dev_info ) {
        if( $dev_info[1] == 0 ) {
            $sql_handler->query("update {$dev_info[0]} set \"value\"=\"disabled\" where \"key\"=\"module_use\";");
        }
    }

    $sql_handler->close();

	shell_exec("{$env_path}source_file_server_monitor.sh > /dev/null 2>/dev/null &");
	shell_exec("{$env_path}source_file_client_monitor.sh > /dev/null 2>/dev/null &");
?>

