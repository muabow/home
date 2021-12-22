<?php
	$pathApiRegisterModule = 'http://' . $_SERVER["HTTP_HOST"] . '/modules/api_register/html/common/api_register_process.php';

	$app->get(
		"/api_register/getKeyInfo",
		function() use($app) {
			$postData["type"] 			= "open_api";
			$postData["act"] 			= "get_user";

			global $pathApiRegisterModule;
			$result = PostIntSync($pathApiRegisterModule, $postData);

			$result = strip_tags($result);
			$result = str_replace("\n\t\t\t\t\t\t\t", "", $result);
			$result = str_replace("\r", "", $result);
			$result = str_replace("\n", "#", $result);
			$result = str_replace(" / ", "/", $result);
			$result = preg_replace('/(\t|\n)+/', ' ', $result);

			$arrList = explode("#", $result);
			unset($arrList[count($arrList) - 1]);

			$keyList = array();
			for( $idx = 0 ; $idx < count($arrList) ; $idx++ ) {
				$temp = explode(" ", trim($arrList[$idx]));
				unset($temp[0]);
				$keyList[$idx] = $temp;
			}

			$app->setResponseMessage("ok");
			$app->setResponseResult($keyList);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	$app->post(
		"/api_register/setKeyInfo",
		function() use($app) {
			$inputData = $app->getPostContent();

			$postData["type"] 			= "open_api";
			$postData["act"] 			= "set_user";

			if( 	(!isset($inputData->api_key)	|| $inputData->api_key == "")
				||  (!isset($inputData->api_secret) || $inputData->api_secret == "")
				||  (!isset($inputData->api_server) || $inputData->api_server == "") ) {

				return ResponseErrMsg($app);
			}

			$postData["apiKey"]			= $inputData->api_key;
			$postData["apiSecret"]		= $inputData->api_secret;
			$postData["serverAddr"]		= $inputData->api_server;

			global $pathApiRegisterModule;

			$result = PostIntSync($pathApiRegisterModule, $postData);

			$result = ($result == 1 ? true : false);

			$app->setResponseMessage("ok");
			$app->setResponseResult($result);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	$app->post(
		"/api_register/unsetKeyInfo",
		function() use($app) {
			$inputData = $app->getPostContent();

			$postData["type"] 			= "open_api";
			$postData["act"] 			= "remove_user";

			if( !isset($inputData->api_secret) || $inputData->api_secret == "" ) {

				return ResponseErrMsg($app);
			}

			$postData["secretKey"]		= $inputData->api_secret;

			global $pathApiRegisterModule;

			$result = PostIntSync($pathApiRegisterModule, $postData);
			$result = ($result != "" ? true : false);

			$app->setResponseMessage("ok");
			$app->setResponseResult($result);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	$app->post(
		"/api_register/setMasterKeyInfo",
		function() use($app) {
			$inputData = $app->getPostContent();

			$postData["type"] 			= "open_api";
			$postData["act"] 			= "set_master_key";

			if( !isset($inputData->api_key) || $inputData->api_key == "" ) {

				return ResponseErrMsg($app);
			}

			if( !isset($inputData->secret_key) || $inputData->secret_key == "" ) {

				return ResponseErrMsg($app);
			}
			
			if( !isset($inputData->server_addr) || $inputData->server_addr == "" ) {

				return ResponseErrMsg($app);
			}

			$postData["server_addr"]	= $inputData->server_addr;
			$postData["api_key"]		= $inputData->api_key;
			$postData["secret_key"]		= $inputData->secret_key;
			
			global $pathApiRegisterModule;

			$result = PostIntSync($pathApiRegisterModule, $postData);
			
			$app->setResponseMessage("ok");
			$app->setResponseResult($result);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	$app->post(
		"/api_register/unsetMasterKeyInfo",
		function() use($app) {
			$inputData = $app->getPostContent();

			$postData["type"] 			= "open_api";
			$postData["act"] 			= "unset_master_key";

			if( !isset($inputData->server_addr) || $inputData->server_addr == "" ) {

				return ResponseErrMsg($app);
			}

			$postData["server_addr"]	= $inputData->server_addr;
			
			global $pathApiRegisterModule;

			$result = PostIntSync($pathApiRegisterModule, $postData);
			
			$app->setResponseMessage("ok");
			$app->setResponseResult($result);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);
?>
