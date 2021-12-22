<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_define.php";
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_script.php";
	
	require_once "common_define.php";
	require_once "common_script.php";
	
	$srcFileMngFunc = new Source_file_management\Func\SrcFileMngFunc();

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
		$storage = $_SERVER["DOCUMENT_ROOT"] . "/" . Source_file_management\Def\PATH_HOME . "/" . Source_file_management\Def\PATH_SRCFILE_STORAGE;
		
		// 파일 갯수 체크 (최대 업로드 가능 갯수 이하 여부 확인)
		$fileCnt = $srcFileMngFunc->getFileCount($storage);
		if( $fileCnt + count($_FILES) >= Source_file_management\Def\MAX_SRCFILE_COUNT) {
			$code = -2;
			$msg = "The number of files that can be uploaded is exceeded. ";
			
			goto retmsg;
		}
		
		//파일 용량
		$fileSize = 0;
		foreach ($_FILES as $key => $value) {
			$fileSize = $fileSize + $_FILES[$key]['size'];
		}
		
		// 저장 용량 체크
		$maxCapacity  = Source_file_management\Def\MAX_AVAILABLE_MEM_SIZE;
		$avaliMemSize = $maxCapacity - ($srcFileMngFunc->getDirMemSize($storage));
		if($avaliMemSize - $fileSize < 0)
		{
			$code = -3;
			$msg = "Avaliable capacity to save is exceeded. ";
			
			goto retmsg;
		}
		
		foreach ($_FILES as $key => $value) {
			if(empty($_FILES[$key]['tmp_name']) || $_FILES[$key]['tmp_name'] == 'none') {
				$code = -1;
				$msg  = "No file was uploaded";
				
				goto retmsg;
			}
		}
		
		foreach ($_FILES as $key => $value) {
			$uploadfile = $_SERVER["DOCUMENT_ROOT"] . "/" . Source_file_management\Def\PATH_HOME . "/" . Source_file_management\Def\PATH_SRCFILE_STORAGE . $_FILES[$key]['name'];
			$result 	= move_uploaded_file($_FILES[$key]['tmp_name'], $uploadfile);
			
			$code = 0;
			$msg  = $uploadfile;
		}

		retmsg:
		echo '{"code":' . $code . ', "msg":"' . $msg . '"}';
	}

	return ;
?>