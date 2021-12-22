<?php
	include_once "{$_SERVER['DOCUMENT_ROOT']}/common/common_define.php";
	include_once "{$_SERVER['DOCUMENT_ROOT']}/common/common_script.php";
	include_once "{$_SERVER['DOCUMENT_ROOT']}/api/api_websocket.php";

	/* GET function : 삭제 */
	function GetData($_url, $_headerStat = false, $_asyncStat = false) {

		return false;
	}

	/* POST function : 삭제 */
	function PostData($_url, $_postData, $_headerStat = false, $_asyncStat = false) {

		return false;
	}


	/* 사용 가능한 GET/POST Function List
	 - GetIntSync($_url)									// 내부 동기   GET function
	 - GetIntAsync($_url, $_msTime = 100)					// 내부 비동기 GET function (default : 100ms)
	 - GetExtSync($_url)									// 외부 동기   GET function
	 - GetExtAsync($_url, $_msTime = 100)					// 외부 비동기 GET function (default : 100ms)
	 - GetSvrSync($_uri)									// 서버 동기   GET function
	 - GetSvrAsync($_uri, $_msTime = 100)					// 서버 비동기 GET function (default : 100ms)

 	 - PostIntSync($_url, $_postData)						// 내부 동기   POST function
	 - PostIntAsync($_url, $_postData, $_msTime = 100)		// 내부 비동기 POST function (default : 100ms)
	 - PostExtSync($_url, $_postData)						// 외부 동기   POST function
	 - PostExtAsync($_url, $_postData, $_msTime = 100)		// 외부 비동기 POST function (default : 100ms)
	 - PostSvrSync($_uri, $_postData)						// 서버 동기   POST function
	 - PostSvrAsync($_uri, $_postData, $_msTime = 100)		// 서버 비동기 POST function (default : 100ms)
	*/

	// 내부 동기 GET function
	// ex) GetIntSync('http://' . $_SERVER["HTTP_HOST"] . '/modules/audio_setup/html/common/audio_process.php')
	function GetIntSync($_url) {
		$curlsession = curl_init();

		curl_setopt($curlsession, CURLOPT_URL, $_url);
		curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($curlsession);

		curl_close($curlsession);

		return $result;
	}

	// 내부 비동기 GET function (default : 100ms)
	// ex) GetIntAsync("http://192.168.1.99/modules/audio_setup/html/common/audio_process.php")
	// ex) GetIntAsync("http://192.168.1.99/modules/audio_setup/html/common/audio_process.php", 1000)
	function GetIntAsync($_url, $_msTime = 100) {
		$curlsession = curl_init();

		curl_setopt($curlsession, CURLOPT_URL, $_url);
		curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlsession, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curlsession, CURLOPT_TIMEOUT_MS, $_msTime);

		$result = curl_exec($curlsession);

		curl_close($curlsession);

		return $result;
	}

	// 외부 동기 GET function
	// * 동일한 NCS API Key/Secret이 등록되어 있는 장치에 한함.
	// ex) GetExtSync("http://192.168.1.99/api/common/getHostInfo")
	function GetExtSync($_url) {
		if( ($svrInfo = GetUsedMngSvrInfo()) == null ) {

			return null;
		}

		$device_id  = $svrInfo["api_key"];
		$secret_key = $svrInfo["api_secret"];

		$headers = array(
							'Content-type: application/json',
							'X-Interm-Device-ID: ' . $device_id,
							'X-Interm-Device-Secret: ' . $secret_key
						);

		$curlsession = curl_init();
		curl_setopt($curlsession, CURLOPT_URL, $_url);
		curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlsession, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($curlsession);

		curl_close($curlsession);

		return $result;
	}

	// 외부 비동기 GET function (default : 100ms)
	// * 동일한 NCS API Key/Secret이 등록되어 있는 장치에 한함.
	// ex) GetExtAsync("http://192.168.1.99/api/common/getHostInfo")
	// ex) GetExtAsync("http://192.168.1.99/api/common/getHostInfo", 1000)
	function GetExtAsync($_url, $_msTime = 100) {
		if( ($svrInfo = GetUsedMngSvrInfo()) == null ) {

			return null;
		}

		$device_id  = $svrInfo["api_key"];
		$secret_key = $svrInfo["api_secret"];

		$headers = array(
							'Content-type: application/json',
							'X-Interm-Device-ID: ' . $device_id,
							'X-Interm-Device-Secret: ' . $secret_key
						);

		$curlsession = curl_init();
		curl_setopt($curlsession, CURLOPT_URL, $_url);
		curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlsession, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curlsession, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curlsession, CURLOPT_TIMEOUT_MS, $_msTime);

		$result = curl_exec($curlsession);

		curl_close($curlsession);

		return $result;
	}

	// 서버 동기 GET function
	// ex) GetSvrSync("/common/getHostInfo")
	function GetSvrSync($_uri) {
		if( ($svrInfo = GetUsedMngSvrInfo()) == null ) {

			return null;
		}

		$device_id  = $svrInfo["api_key"];
		$secret_key = $svrInfo["api_secret"];

		$headers = array(
							'Content-type: application/json',
							'X-Interm-Device-ID: ' . $device_id,
							'X-Interm-Device-Secret: ' . $secret_key
						);

		$url = "http://" . $svrInfo["mng_svr_ip"] . ":" . $svrInfo["mng_svr_port"] . "/api/" . $_uri;

		$curlsession = curl_init();
		curl_setopt($curlsession, CURLOPT_URL, $url);
		curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlsession, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($curlsession);

		curl_close($curlsession);

		return $result;
	}

	// 서버 비동기 GET function (default : 100ms)
	// ex) GetSvrAsync("/common/getHostInfo")
	// ex) GetSvrAsync("/common/getHostInfo", 1000)
	function GetSvrAsync($_uri, $_msTime = 100) {
		if( ($svrInfo = GetUsedMngSvrInfo()) == null ) {

			return null;
		}

		$device_id  = $svrInfo["api_key"];
		$secret_key = $svrInfo["api_secret"];

		$headers = array(
							'Content-type: application/json',
							'X-Interm-Device-ID: ' . $device_id,
							'X-Interm-Device-Secret: ' . $secret_key
						);

		$url = "http://" . $svrInfo["mng_svr_ip"] . ":" . $svrInfo["mng_svr_port"] . "/api/" . $_uri;

		$curlsession = curl_init();
		curl_setopt($curlsession, CURLOPT_URL, $url);
		curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlsession, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curlsession, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curlsession, CURLOPT_TIMEOUT_MS, $_msTime);

		$result = curl_exec($curlsession);

		curl_close($curlsession);

		return $result;
	}
	// 내부 동기 POST function
	// ex) PostIntSync("http://192.168.1.99/modules/audio_setup/html/common/audio_process.php", $data)
	function PostIntSync($_url, $_postData) {
		$curlsession = curl_init();
		curl_setopt($curlsession, CURLOPT_URL, $_url);
		curl_setopt($curlsession, CURLOPT_POST, 1);
		curl_setopt($curlsession, CURLOPT_POSTFIELDS, $_postData);
		curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curlsession);

		curl_close($curlsession);

		return $result;
	}

	// 내부 비동기 POST function (default : 100ms)
	// ex) PostIntAsync("http://192.168.1.99/modules/audio_setup/html/common/audio_process.php", $data)
	// ex) PostIntAsync("http://192.168.1.99/modules/audio_setup/html/common/audio_process.php", $data, 1000)
	function PostIntAsync($_url, $_postData, $_msTime = 100) {
		$curlsession = curl_init();
		curl_setopt($curlsession, CURLOPT_URL, $_url);
		curl_setopt($curlsession, CURLOPT_POST, 1);
		curl_setopt($curlsession, CURLOPT_POSTFIELDS, $_postData);
		curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlsession, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curlsession, CURLOPT_TIMEOUT_MS, $_msTime);
		$result = curl_exec($curlsession);

		curl_close($curlsession);

		return $result;
	}

	// 외부 동기 POST function
	// * 동일한 NCS API Key/Secret이 등록되어 있는 장치에 한함.
	// ex) PostExtSync("http://192.168.1.99/api/common/setMngSvrInfo", $data)
	function PostExtSync($_url, $_postData) {
		if( ($svrInfo = GetUsedMngSvrInfo()) == null ) {

			return null;
		}

		$device_id  = $svrInfo["api_key"];
		$secret_key = $svrInfo["api_secret"];

		$headers = array(
							'Content-type: application/json',
							'X-Interm-Device-ID: ' . $device_id,
							'X-Interm-Device-Secret: ' . $secret_key
						);

		$curlsession = curl_init();
		curl_setopt($curlsession, CURLOPT_URL, $_url);
		curl_setopt($curlsession, CURLOPT_POST, 1);
		curl_setopt($curlsession, CURLOPT_POSTFIELDS, $_postData);
		curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlsession, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($curlsession);

		curl_close($curlsession);

		return $result;
	}

	// 외부 비동기 POST function (default : 100ms)
	// * 동일한 NCS API Key/Secret이 등록되어 있는 장치에 한함.
	// ex) PostExtAsync("http://192.168.1.99/api/common/setMngSvrInfo", $data)
	// ex) PostExtAsync("http://192.168.1.99/api/common/setMngSvrInfo", $data, 1000)
	function PostExtAsync($_url, $_postData, $_msTime = 100) {
		if( ($svrInfo = GetUsedMngSvrInfo()) == null ) {

			return null;
		}

		$device_id  = $svrInfo["api_key"];
		$secret_key = $svrInfo["api_secret"];

		$headers = array(
							'Content-type: application/json',
							'X-Interm-Device-ID: ' . $device_id,
							'X-Interm-Device-Secret: ' . $secret_key
						);

		$curlsession = curl_init();
		curl_setopt($curlsession, CURLOPT_URL, $_url);
		curl_setopt($curlsession, CURLOPT_POST, 1);
		curl_setopt($curlsession, CURLOPT_POSTFIELDS, $_postData);
		curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlsession, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curlsession, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curlsession, CURLOPT_TIMEOUT_MS, $_msTime);

		$result = curl_exec($curlsession);

		curl_close($curlsession);

		return $result;
	}

	// 서버 동기 POST function
	// ex) PostSvrSync("/common/setMngSvrInfo", $data)
	function PostSvrSync($_uri, $_postData) {
		if( ($svrInfo = GetUsedMngSvrInfo()) == null ) {

			return null;
		}

		$device_id  = $svrInfo["api_key"];
		$secret_key = $svrInfo["api_secret"];

		$headers = array(
							'Content-type: application/json',
							'X-Interm-Device-ID: ' . $device_id,
							'X-Interm-Device-Secret: ' . $secret_key
						);

		$url = "http://" . $svrInfo["mng_svr_ip"] . ":" . $svrInfo["mng_svr_port"] . "/api/" . $_uri;

		$curlsession = curl_init();
		curl_setopt($curlsession, CURLOPT_URL, $url);
		curl_setopt($curlsession, CURLOPT_POST, 1);
		curl_setopt($curlsession, CURLOPT_POSTFIELDS, $_postData);
		curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlsession, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($curlsession);

		curl_close($curlsession);

		return $result;
	}

	// 서버 비동기 POST function (default : 100ms)
	// ex) PostSvrAsync("/common/setMngSvrInfo", $data)
	// ex) PostSvrAsync("/common/setMngSvrInfo", $data, 1000)
	function PostSvrAsync($_uri, $_postData, $_msTime = 100) {
		if( ($svrInfo = GetUsedMngSvrInfo()) == null ) {

			return null;
		}

		$device_id  = $svrInfo["api_key"];
		$secret_key = $svrInfo["api_secret"];

		$headers = array(
							'Content-type: application/json',
							'X-Interm-Device-ID: ' . $device_id,
							'X-Interm-Device-Secret: ' . $secret_key
						);

		$url = "http://" . $svrInfo["mng_svr_ip"] . ":" . $svrInfo["mng_svr_port"] . "/api/" . $_uri;

		$curlsession = curl_init();
		curl_setopt($curlsession, CURLOPT_URL, $url);
		curl_setopt($curlsession, CURLOPT_POST, 1);
		curl_setopt($curlsession, CURLOPT_POSTFIELDS, $_postData);
		curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlsession, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curlsession, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curlsession, CURLOPT_TIMEOUT_MS, $_msTime);

		$result = curl_exec($curlsession);

		curl_close($curlsession);

		return $result;
	}

	function ResponseErrMsg($_app) {
		$_app->setResponseMessage("Bad Request");
		$_app->setResponseResult(array(
									"status"		=> "fail",
									"message"		=> "invalid parameter"
								 	)
								);
		$_app->setResponseCode(400);

		return $_app->getResponseData();
	}

	$app = new Common\Func\CommonRestFunc();
	/* 사용 가능한 Method List
	 - getHttpStatusMessage($_statusCode) 	// HTTP 상태 코드에 따른 description 반환
	 - getRequestURI()						// URI 정보 반환 (Array Data)
	 - getPostContent()						// POST에서 전송된 Data 반환 (JSON Data)

	 - get($_path, $_func)					// GET method처리에 사용, URI path와 처리 함수부로 구성
	 - post($_path, $_func)					// POST method 처리에 사용, URI path와 처리 함수부로 구성
	 - handle($_httpCode)					// Rest 처리의 마무리에 사용, http status code 입력 (default. 404 - not found)

	 - setResponseMessage($_message)		// 상태 메시지 설정 (ok, error)
	 - setResponseResult($_result)			// response 데이터 설정
	 - setResponseCode($_code)				// response 데이터의 반환 code 설정 (ex. 200 - ok)
	 - getResponseData()					// 설정된 response data 반환 (JSON Data)
	*/

	// for AOE-N300, 동작 모드 변경에 따른 API interface list-up
	// includes/support_modules.json, 해당 파일에 list-up 하지 않은 모듈은 by-pass하여 공용으로 사용 됨.
	$load_envData  	= file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/../conf/env.json");
	$envData   		= json_decode($load_envData);
	$arrFileList 	= array();

	// First priority included
	include_once "{$_SERVER['DOCUMENT_ROOT']}/api/includes/module_common.php";

	if( isset($envData->mode) && isset($envData->mode->set) ) {
		// common includes
		$arrModuleList = json_decode(file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/../conf/support_modules.json"), true);
		foreach( glob("{$_SERVER['DOCUMENT_ROOT']}/api/includes/*.php") as $filePath ) {
			$fileName = basename($filePath);
			$cFlag = false;

			foreach( $arrModuleList as $modeName => $list ) {
				if( in_array($fileName, $list) && $envData->mode->set != $modeName ) {
					$cFlag = true;
					break;
				}
			}
			if( $cFlag == false ) {
				if( !in_array($fileName, $arrFileList) ) {
					$arrFileList[] = $fileName;
	            	include_once $filePath;
	        	}
			}
		}

		// module includes
		foreach( $envData->module->list as $moduleList ) {
			if( $moduleList->type == "menu" ) {
				foreach( glob("{$_SERVER['DOCUMENT_ROOT']}/modules/{$moduleList->name}/html/api/*.php") as $filePath ) {
					$fileName = basename($filePath);
					$cFlag = false;
		
					foreach( $arrModuleList as $modeName => $list ) {
						if( in_array($fileName, $list) && $envData->mode->set != $modeName ) {
							$cFlag = true;
							break;
						}
					}
					if( $cFlag == false ) {
						if( !in_array($fileName, $arrFileList) ) {
							$arrFileList[] = $fileName;
			            	include_once $filePath;
			        	}
					}
				}
			}
		}

	} else {
		// common includes
	    foreach( glob("{$_SERVER['DOCUMENT_ROOT']}/api/includes/*.php") as $filePath ) {
	        $fileName = basename($filePath);

	        if( !in_array($fileName, $arrFileList) ) {
	            $arrFileList[] = $fileName;
	            include_once $filePath;
	        }
	    }

	    // module includes
	    foreach( $envData->module->list as $moduleList ) {
			if( $moduleList->type == "menu" ) {
				foreach( glob("{$_SERVER['DOCUMENT_ROOT']}/modules/{$moduleList->name}/html/api/*.php") as $filePath ) {
					$fileName = basename($filePath);
					
					if( !in_array($fileName, $arrFileList) ) {
			            $arrFileList[] = $fileName;
			            include_once $filePath;
			        }
				}
			}
		}	   
	}
	$app->handle();
?>
`
