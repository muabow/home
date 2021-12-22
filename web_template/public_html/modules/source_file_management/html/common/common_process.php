<?php
	include_once "common_define.php";
	include_once "common_script.php";

	if( isset($_POST["act"]) ) {
		$source_handler = new Source_file_management\Func\SourceFileHandler();

		switch( $_POST["act"] ) {
			case "reload" :
				echo $source_handler->get_size_upload_available();

				return ;
				break;
			case "reload_ext" :
				echo $source_handler->get_size_upload_available_ext();

				return ;
				break;
				
			case "change_module_status" :
				$source_handler->update_module_status("audio_player",     'is_run=0');
				$source_handler->update_module_status("audio_player",     'is_play=0');
				$source_handler->update_module_status("audio_player",     'is_pause=0');
				$source_handler->update_module_status("source_info_list", 'is_playlist=0');

				shell_exec("sudo killall audio_player");
				break;

			case "preview" :
				$file_path = $_POST["src"];
				$ext_config_path = "/opt/interm/conf/config-external-storage.json";
				$json_env_info   = json_decode(file_get_contents($ext_config_path));
				
				shell_exec("ln -s '{$json_env_info->mnt_info->target_dir}{$json_env_info->mnt_info->sub_dir_list->source_file}/{$file_path}' /opt/interm/public_html/modules/source_file_management/html/data/.");
			
				echo $file_path;
			break;

			case "preview_clear" :
				shell_exec("find /opt/interm/public_html/modules/source_file_management/html/data/ -type l -maxdepth 1 -delete");
				break;

			default :
				break;
		}
	}

	include_once 'common_process_etc.php';
?>