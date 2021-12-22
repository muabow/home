<?php
	include_once "common_define.php";
	include_once "common_script.php";

	$registerFunc = new Api_register\Func\ApiRegisterFunc();

	if( $_POST["type"] == "open_api" && isset($_POST["act"]) ) {
		$act = $_POST["act"];

		if( $act == "set_user" ) {
			$serverAddr = $_POST["serverAddr"];
			$apiKey 	= $_POST["apiKey"];
			$apiSecret 	= $_POST["apiSecret"];

			echo $registerFunc->setUserList($serverAddr, $apiKey, $apiSecret);

			return ;
			
		} else if( $act == "get_user" ) {

			echo $registerFunc->getUserList();

			return ;
			
		} else if( $act == "remove_user" ) {
			$secretKey = $_POST["secretKey"];
			$rc     = false;

			if( $registerFunc->removeUserList($secretKey) ) {
				$rc = $registerFunc->getUserList();
			}

			echo $rc;

			return ;
			
		} else if( $act == "check_server" ) {
			if( (isset($_POST["serverAddr"]) != true) || 
				(isset($_POST["apiKey"]) != true) || 
				(isset($_POST["apiSecret"]) != true)) {
				echo "Invalid Params";
				return;
			} 
			
			if( !file_exists("/tmp/api_ignore_svr_check") ) { 
				$postRet = $registerFunc->getServerVersion($_POST["serverAddr"], $_POST["apiKey"], $_POST["apiSecret"]);
				if($postRet->message == "error" && $postRet->code == 401 ) {
					echo "Unauthorized";
					return ;
				}

				if( $postRet->message != "ok" ) {
					echo "Can not broadcast";
					return;
				}
				
				if( (isset($postRet->result->version) != true) || ($postRet->result->version == "") ) {
					echo "Unknown version";
					return;
				}
				
				$serverVersion = $postRet->result->version;
				$ret = $registerFunc->checkIsCompatibleVersion($serverVersion);
				if( $ret == true ) {
					echo "ok";		
							
				} else {
					echo "fail";
				}
			
			} else {
				echo "ok";
			}
		
		} else if( $act == "set_master_key" ) {
			$server_addr = $_POST["server_addr"];
			$api_key 	 = $_POST["api_key"];
			$secret_key  = $_POST["secret_key"];

			if( $registerFunc->setUserList($server_addr, $api_key, $secret_key, true) ) {
				echo "The master key has been registered : [{$api_key}/{$secret_key}]";

			} else {
				echo "The same master key is registered : [{$api_key}/{$secret_key}]";
			}

		} else if( $act == "unset_master_key" ) {
			$server_addr = $_POST["server_addr"];

			if( $registerFunc->unset_master_key($server_addr) ) {
				echo "The master key has been removed : [{$server_addr}]";

			} else {
				echo "The same master key is not found : [{$server_addr}]";
			}
		}
	}
	
	include_once 'api_register_process_etc.php';
?>
