<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_define.php";
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_script.php";

	include_once "common_define.php";
	include_once "common_script.php";

	$load_envData  				= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/env.json");
	$envData   					= json_decode($load_envData);
	$env_langSet  	   			= $envData->info->language_set;

	include_once "../" . $envData->language_pack->$env_langSet->path;

	$modeLogFunc = new Common\Func\CommonLogFunc("mode_management");

	if( $_POST["type"] == "mode_management" && isset($_POST["act"]) ) {
		$act = $_POST["act"];

		$systemFunc = new Mode_management\Func\ModeFunc();

		if( $act == "get_mode_list" ) {
			echo $systemFunc->getModeList();

			return ;

		} else if( $act == "set_mode_list" ) {
			$rc = $systemFunc->setModeList($_POST["mode_name"]);

			if( $rc == 1 ) {
				$modeLogFunc->info(Mode_management\Lang\STR_MODE_CONFIRM_SUCCESS . " : [{$_POST["mode_name"]}]");
			}

			echo $rc;

			return ;
		}
	}
	
	include_once 'mode_process_etc.php';
?>