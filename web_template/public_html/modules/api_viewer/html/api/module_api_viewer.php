<?php
	$pathApiViewerModule = 'http://' . $_SERVER["HTTP_HOST"] . '/modules/api_viewer/html/common/api_viewer_process.php';

	$app->get(
		"/api_viewer/getKeyInfo",
		function() use($app) {
			$postData["type"] 			= "open_api";
			$postData["act"] 			= "get_user";

			global $pathApiViewerModule;
			$result = PostIntSync($pathApiViewerModule, $postData);

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
		"/api_viewer/setKeyInfo",
		function() use($app) {
			$inputData = $app->getPostContent();

			$postData["type"] 			= "open_api";
			$postData["act"] 			= "set_user";

			$postData["userMail"]		= "admin@inter-m.com";
			$postData["userContact"]	= "000-0000-0000";
			$postData["userCompany"]	= "Inter-M";

			if( isset($inputData->api_user_mail) || $inputData->api_user_mail != "" ) {
				$postData["userMail"]		= $inputData->api_user_mail;
			}
			if( isset($inputData->api_user_contact) || $inputData->api_user_contact != "" ) {
				$postData["userContact"]	= $inputData->api_user_contact;
			}
			if( isset($inputData->api_user_company) || $inputData->api_user_company != "" ) {
				$postData["userCompany"]	= $inputData->api_user_company;
			}

			global $pathApiViewerModule;

			$result = PostIntSync($pathApiViewerModule, $postData);

			$result = ($result == 1 ? true : false);

			$app->setResponseMessage("ok");
			$app->setResponseResult($result);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	$app->post(
		"/api_viewer/unsetKeyInfo",
		function() use($app) {
			$inputData = $app->getPostContent();

			$postData["type"] 			= "open_api";
			$postData["act"] 			= "remove_user";

			if( !isset($inputData->api_secret) || $inputData->api_secret == "" ) {

				return ResponseErrMsg($app);
			}

			$postData["secretKey"]		= $inputData->api_secret;

			global $pathApiViewerModule;

			$result = PostIntSync($pathApiViewerModule, $postData);
			$result = ($result != "" ? true : false);

			$app->setResponseMessage("ok");
			$app->setResponseResult($result);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);
?>
