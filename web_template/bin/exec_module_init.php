<?php
	echo "execute module init scripts start\n";

	$env_data = json_decode(file_get_contents("/opt/interm/conf/env.json"));

	$arr_exec_init = array();

	foreach( $env_data->module->list as $module_list ) {
		if( $module_list->type == "menu" ) {
			$path_init_file = "/opt/interm/public_html/modules/{$module_list->name}/bin/init.php";

			if( file_exists($path_init_file) ) {
				if( in_array($module_list->name, $arr_exec_init) ) {
					printf(" - already running module [%s]\n", $module_list->name);
					continue;
				}
				$arr_exec_init[] = $module_list->name;

				printf(" - execute module [%s]\n", $module_list->name);
				exec("php " . $path_init_file);
			}
		}
	}

	echo "execute module init scripts end\n";
?>
