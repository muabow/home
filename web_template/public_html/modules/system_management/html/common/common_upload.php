<?php
	include_once "common_define.php";
	
	if( isset($_POST['filePath']) ) {
		// upgrade
		$filePath = $_POST['filePath'];

		// 1. 업로드파일 압축 해제 (/tmp -> 재부팅 시 알아서 삭제)
		// 2. 스크립트 실행

		//common_logWrite(UPGRADE_SYSTEM_STR." - ".UPGRADE_SUCCESS_MSG);
		//common_restartConfig(SET_UPGRADE . " " . $uploadfile);
		echo $filePath;

	} else {
		// file upload
		$code = 0;
		$fileElementName = 'file';

		if(empty($_FILES[$fileElementName]['tmp_name']) || $_FILES[$fileElementName]['tmp_name'] == 'none') {
			$code = -1;
			$msg  = "No file was uploaded";

		} else {
			// $env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));
			//$uploadfile     = "/tmp/" . $_FILES[$fileElementName]['name'];
			$uploadfile     = "/tmp/" . System_management\Def\UPGRADE_FILE_NAME;
			move_uploaded_file($_FILES[$fileElementName]['tmp_name'], $uploadfile);

			$code = 0;
			$msg  = $uploadfile;

		}

		echo '{"code":' . $code . ', "msg":"' . $msg . '"}';
	}

	return ;
?>