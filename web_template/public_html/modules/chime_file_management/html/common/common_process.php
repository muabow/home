<?php
	include_once "common_define.php";
	include_once "common_script.php";

	if( isset($_POST["act"]) ) {
		$chime_handler = new Chime_file_management\Func\ChimeFileHandler();

		switch( $_POST["act"] ) {
			case "reload" :
				echo $chime_handler->get_size_upload_available();
				break;

			case "remove_file" :
				echo $chime_handler->remove_file($_POST["source_name"]);
				break;

			case "sort_file_list" :
				echo $chime_handler->sort_file($_POST["source_name"]);
				break;
	
			case "get_file_list" :
				echo $chime_handler->get_file_list();
				break;

			case "reload_ext" :
				echo $chime_handler->get_size_upload_available_ext();
				break;
			
			case "preview" :
				$file_path = $_POST["src"];
				$ext_config_path = "/opt/interm/conf/config-external-storage.json";
				$json_env_info   = json_decode(file_get_contents($ext_config_path));
				
				shell_exec("ln -s '{$json_env_info->mnt_info->target_dir}{$json_env_info->mnt_info->sub_dir_list->chime_file}/{$file_path}' /opt/interm/public_html/modules/chime_file_management/html/data/.");
			
				echo $file_path;
			break;

			case "preview_clear" :
				shell_exec("find /opt/interm/public_html/modules/chime_file_management/html/data/ -type l -maxdepth 1 -delete");
				break;


			default :
				break;
		}
	}

	include_once 'common_process_etc.php';
?>