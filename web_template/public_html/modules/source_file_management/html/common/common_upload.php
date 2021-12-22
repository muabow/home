<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_define.php";
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_script.php";

	require_once "common_define.php";
	require_once "common_script.php";

	$ext_config_path = "/opt/interm/conf/config-external-storage.json";
	$json_env_info   = json_decode(file_get_contents($ext_config_path));
	$str_sd_disk_dir = "{$json_env_info->ext_info->target_dir}/{$json_env_info->ext_info->dev_name}";
	
	$code = 0;
	$upload_path = $_SERVER["DOCUMENT_ROOT"] . "/" . Source_file_management\Def\PATH_HOME . "/" . Source_file_management\Def\PATH_SRCFILE_STORAGE;

	if( isset($_POST["storage"]) ) {
		if( $_POST["storage"] == "upload_external" && file_exists($str_sd_disk_dir) ) {
			$upload_path = "{$json_env_info->mnt_info->target_dir}{$json_env_info->mnt_info->sub_dir_list->source_file}";
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

	foreach( $_FILES as $key => $value ) {
		$uploadfile = $upload_path . "/" . $_FILES[$key]['name'];
		$result 	= move_uploaded_file($_FILES[$key]['tmp_name'], $uploadfile);

		shell_exec("sync");
		
		$code = 0;
		$msg  = $uploadfile;
	}

	echo '{"code":' . $code . ', "msg":"' . $msg . '"}';

	return ;
?>