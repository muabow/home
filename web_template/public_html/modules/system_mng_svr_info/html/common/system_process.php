<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_define.php";
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_script.php";

	include_once "common_define.php";
	include_once "common_script.php";
	
	$load_envData  				= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/env.json");
	$envData   					= json_decode($load_envData);
	$env_langSet  	   			= $envData->info->language_set;
	
	include_once "../" . $envData->language_pack->$env_langSet->path;
	
	$sysLogFunc = new Common\Func\CommonLogFunc("system_mng_svr_info");
	$logMsg;
	
	if( $_POST["type"] == "system" && isset($_POST["act"]) ) {
		$act = $_POST["act"];

		$systemFunc = new System_mng_svr_info\Func\SystemFunc();

		if( $act == "get_svr_list" ) {
			echo $systemFunc->getSvrList();

			return ;

		} else if( $act == "set_svr_list" ) {
			echo $systemFunc->setSvrList($_POST["svr_id"]);

			return ;

		} else if( $act == "remove_svr_list" ) {
			echo $systemFunc->removeSvrList($_POST["svr_id"]);

			return ;
			
		} else if( $act == "log" ) {
			$results = $_POST["results"];
			
			if($results == "success") {
			 	$key = $_POST["key"];
				$ip = $_POST["ip"];
				$logMsg = $key ."(" .$ip .") " . System_mng_svr_info\Lang\STR_SYSTEM_SERVER_SCCESS;
	
				$sysLogFunc->info($logMsg);
	
			} else {
				$logMsg = System_mng_svr_info\Lang\STR_SYSTEM_SERVER_FAIL;
	
				$sysLogFunc->info($logMsg);
	
			}
			return ;
		}
	}
	
	include_once 'system_process_etc.php';
?>