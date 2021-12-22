<?php
	$pathApiMnvSvrInfoModule = 'http://' . $_SERVER["HTTP_HOST"] . '/modules/system_mng_svr_info/html/common/system_process.php';

	$app->get(
		"/mng_svr_info/getSvrInfo",
		function() use($app) {
			$postData["type"] 			= "system";
			$postData["act"] 			= "get_svr_list";

			global $pathApiMnvSvrInfoModule;
			$result = PostIntSync($pathApiMnvSvrInfoModule, $postData);

			$arrRow = str_replace("\n\t\t\t\t\t\t\t", "", $result);
			$arrRow = str_replace("\n|\t", "", $arrRow);
			$arrRow = explode(" ", $arrRow);

			$chkNum = array_search('checked', $arrRow);
			$chkNum = preg_replace("/[^0-9]/","",$arrRow[$chkNum - 1]);

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
				$msg = "";
				$temp = explode(" ", trim($arrList[$idx]));

				$chkValue = 0;
				if( $idx == $chkNum ) $chkValue = 1;
				array_unshift($temp, $chkValue);

				$temp[count($temp) - 2] = $temp[count($temp) - 2] . ' ' . $temp[count($temp) - 1];
				unset($temp[count($temp) - 1]);

				for( $inIdx = 4 ; $inIdx < count($temp) - 1 ; $inIdx++ ) {
					$msg .= $temp[$inIdx];
					if( $inIdx < count($temp) - 2) {
						$msg .= ' ';
					}
				}

				$temp[4] = $msg;
				$temp[5] = $temp[count($temp) - 1];
				array_splice($temp, 6);
				$keyList[$idx] = $temp;
			}

			$app->setResponseMessage("ok");
			$app->setResponseResult($keyList);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	$app->post(
		"/mng_svr_info/setSvrInfo",
		function() use($app) {
			$inputData = $app->getPostContent();

			$postData["type"] 			= "system";
			$postData["act"] 			= "set_svr_list";

			if( !isset($inputData->api_server_id) || $inputData->api_server_id == "" ) {

				return ResponseErrMsg($app);
			}

			$postData["svr_id"]		= $inputData->api_server_id;

			global $pathApiMnvSvrInfoModule;

			$result = PostIntSync($pathApiMnvSvrInfoModule, $postData);

			$result = ($result != "" ? true : false);

			$app->setResponseMessage("ok");
			$app->setResponseResult($result);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	$app->post(
		"/mng_svr_info/unsetSvrInfo",
		function() use($app) {
			$inputData = $app->getPostContent();

			$postData["type"] 			= "system";
			$postData["act"] 			= "remove_svr_list";

			$postData["svr_id"]		= $inputData->api_server_id;

			global $pathApiMnvSvrInfoModule;

			$result = PostIntSync($pathApiMnvSvrInfoModule, $postData);
			//$result = ($result != "" ? true : false);

			$app->setResponseMessage("ok");
			$app->setResponseResult($result);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);
?>
