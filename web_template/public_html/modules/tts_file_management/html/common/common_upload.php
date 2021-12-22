<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_define.php";
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_script.php";

	require_once "common_define.php";
	require_once "common_script.php";

	$code = 0;
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
	$json_info = json_decode(file_get_contents($conf_path), true);

	foreach( $_FILES as $key => $value ) {
		$uploadfile = $_SERVER["DOCUMENT_ROOT"] . "/" . TTS_file_management\Def\PATH_HOME . "/" . TTS_file_management\Def\PATH_SRCFILE_STORAGE . $_FILES[$key]['name'];
		$result 	= move_uploaded_file($_FILES[$key]['tmp_name'], $uploadfile);
		$file_name  = $_FILES[$key]['name'];
		
		$is_exist  = false;
		$chime_idx = 0;
		foreach( $json_info["chime_list"] as $index => $chime_info ) {
			if( $chime_info["name"] == $file_name ) {
				$is_exist = true;
				break;
			}

			$chime_idx++;
		}

		$exec_conv = "/opt/interm/public_html/modules/tts_file_management/bin/audio_convert.sh \"{$uploadfile}\" 2>/dev/null";
		$fp = popen($exec_conv, "r");
		$duration = fread($fp, 1024);
		pclose($fp);
		
		$duration = round($duration);
		$duration = ($duration > 10 ? 10 : $duration);

		$arr_chime_info["name"] 	= $file_name;
		$arr_chime_info["duration"] = $duration;
		if( !$is_exist ) {
			$chime_idx = count($json_info["chime_list"]);
		}
		$json_info["chime_list"][$chime_idx] = $arr_chime_info;
	}

	file_put_contents($conf_path, json_encode($json_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

	$chime_handle = new TTS_file_management\Func\ChimeFileHandler();
	echo $chime_handle->get_file_list();

	return ;
?>
