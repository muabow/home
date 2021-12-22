<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_define.php";
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_script.php";

	require_once "common_define.php";
	require_once "common_script.php";
	
	$ext_config_path = "/opt/interm/conf/config-external-storage.json";
	$json_env_info   = json_decode(file_get_contents($ext_config_path));
	$str_sd_disk_dir = "{$json_env_info->ext_info->target_dir}/{$json_env_info->ext_info->dev_name}";

	$code = 0;
	$msg  = "success";
	
	$upload_path = $_SERVER["DOCUMENT_ROOT"] . "/" . Chime_file_management\Def\PATH_HOME . "/" . Chime_file_management\Def\PATH_SRCFILE_STORAGE;

	if( isset($_POST["storage"]) ) {
		if( $_POST["storage"] == "upload_external" && file_exists($str_sd_disk_dir) ) {
			$upload_path = "{$json_env_info->mnt_info->target_dir}{$json_env_info->mnt_info->sub_dir_list->chime_file}";
		}
	}

	foreach( $_FILES as $key => $value ) {
		if( empty($_FILES[$key]['tmp_name']) || $_FILES[$key]['tmp_name'] == 'none' ) {
			$code = -1;
			$msg  = "No file was uploaded";

			echo '{"code":' . $code . ', "msg":"' . $msg . '"}';

			exit;
			break;
		}
	}

	$conf_path = str_replace(basename(__FILE__), "", realpath(__FILE__)) . "/../../conf/chime_info.json";
	$json_info = json_decode(file_get_contents($conf_path));

	foreach( $_FILES as $key => $value ) {
		$uploadfile = $upload_path . "/" . $_FILES[$key]['name'];
		$result 	= move_uploaded_file($_FILES[$key]['tmp_name'], $uploadfile);
		
		shell_exec("sync");
		
		$file_name  = $_FILES[$key]['name'];
		
		$is_exist  = false;
		$chime_idx = 0;
		foreach( $json_info->chime_list as $index => $chime_info ) {
			if( $chime_info->name == $file_name ) {
				$is_exist = true;
				break;
			}

			$chime_idx++;
		}

		$exec_conv = "/opt/interm/public_html/modules/chime_file_management/bin/audio_convert.sh \"{$uploadfile}\" \"{$upload_path}\" 2>/dev/null";
		$fp = popen($exec_conv, "r");
		$duration = fread($fp, 1024);
		pclose($fp);
		
		$duration = round($duration);
		$duration = ($duration > 10 ? 10 : $duration);

		$arr_chime_info["name"] 	= $file_name;
		$arr_chime_info["duration"] = $duration;
		if( !$is_exist ) {
			$chime_idx = count($json_info->chime_list);
		}

		if( isset($_POST["storage"]) && $_POST["storage"] == "upload_external" ) {
			$arr_chime_info["ext_storage"] = true;
		}

		$json_info->chime_list[$chime_idx] = json_decode(json_encode($arr_chime_info));
	}

	file_put_contents($conf_path, json_encode($json_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

	echo '{"code":' . $code . ', "msg":"' . $msg . '"}';

	return ;
?>
