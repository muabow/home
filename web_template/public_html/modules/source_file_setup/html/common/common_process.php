<?php
	include_once "common_define.php";
	include_once "common_script.php";

	if( isset($_POST["act"]) ) {
		$setup_handler = new Source_file_setup\Func\SetupHandler();

		switch( $_POST["act"] ) {
			case "change_module_status" :
				if($_POST["mode"] == "INPUT CONNECTING") {
					// input connecting mode, NC-S01
					$setup_handler->update_module_status("source_file_server", "module_use", "enabled");
					$setup_handler->update_module_status("source_file_client", "module_use", "disabled");
	
				} else if($_POST["mode"] == "OUTPUT CONNECTING") {
					// output connecting mode, NC-600
					$setup_handler->update_module_status("source_file_server", "module_use", "enabled");
					$setup_handler->update_module_status("source_file_client", "module_use", "enabled");
	
				} else {
					// stand_alone
					$setup_handler->update_module_status("source_file_server", "module_use", "disabled");
					$setup_handler->update_module_status("source_file_client", "module_use", "disabled");
				}

				$setup_handler->update_module_status("source_file_server", 'is_run',      0);
				$setup_handler->update_module_status("source_file_server", 'is_play',     0);
				$setup_handler->update_module_status("source_file_server", 'is_pause',    0);
				$setup_handler->update_module_status("source_file_server", "module_view",   "setup");
				$setup_handler->update_module_status("source_file_server", "module_status", "stop");
				
				$setup_handler->update_module_status("source_file_client", "module_view",   "setup");
				$setup_handler->update_module_status("source_file_client", "module_status", "stop");
				
				$setup_handler->update_module_info("source_info_list", 'is_playlist=0');

				shell_exec("sudo killall source_file_server");
				shell_exec("sudo killall source_file_client");
				break;

			default :
				break;
		}
	}

	include_once 'common_process_etc.php';
?>
