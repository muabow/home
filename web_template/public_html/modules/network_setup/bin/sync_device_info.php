<?php
	$_SERVER['DOCUMENT_ROOT'] = "/opt/interm/public_html";
	
	include_once "/opt/interm/public_html/common/common_script.php";
	
	function getServerInfoByMID($_mid) {
		global $svrList;
		
		$svrCnt = count($svrList);
		for($idx = 0; $idx < $svrCnt; $idx++) {
			if($svrList[$idx]["mng_svr_id"] == $_mid) {
				return $svrList[$idx]; 
			}
		}
		
		return null;
	}
	
	// 수신받은 서버 정보를 현재 장비에 저장되어 있는 정보와 비교하여 틀릴 경우 업데이트 하는 함수. 
	function UpdateServerInfo($_serverData) {
		// 수신된 데이터에 version, f_device_no 정보 유무 체크
		if((isset($_serverData->version) == false) || (isset($_serverData->f_device_no) == false)) {
			return;
		}
		
		global $svrMngFunc;
				
		$svrInfo = $svrMngFunc->getSvrInfoByMID($_serverData->f_device_no);
		if($svrInfo == null) {
			return;
		}
		
		global $envObj;

		$version 			= $envObj->device->version;
		$splitVersion	 	= explode('.', $version);
		$compatibleVersion 	= explode('.', $_serverData->version)[1];

		$enable = "0";
		if($splitVersion[1] == $compatibleVersion) {
			$enable = "1";	
		}
		
		$extend = "0";
		if(isset($_serverData->extend) == true) {
			$extend = "1";	
		}
		
		$updateData = array();
				
		if((array_key_exists("mng_svr_version", $svrInfo) == true) && ($svrInfo["mng_svr_version"] != $_serverData->version)) {
			$updateData["mng_svr_version"] = $_serverData->version;
		}
		
		if((array_key_exists("mng_svr_enabled", $svrInfo) == true) && ($svrInfo["mng_svr_enabled"] != $enable)) {
			$updateData["mng_svr_enabled"] = $enable;
		}
		
		if((array_key_exists("mng_svr_extend", $svrInfo) == true) && ($svrInfo["mng_svr_extend"] != $extend)) {
			$updateData["mng_svr_extend"] = $extend;
		}

		if(count($updateData) > 0) {
			$svrMngFunc->updateSvrInfoByMId($_serverData->f_device_no, $updateData);
		}
	}
	
	// CURL 전송(POST/GET) 
	function SendData($_uri, $_svrInfo, $_data) {
		$ret = true;
			
		if(($_svrInfo["api_key"] == null) || ($_svrInfo["api_secret"] == null)) {
			return $ret;
		}
		
		$url = $_svrInfo["mng_svr_ip"] . $_uri;
		
		$headers = array(
			'Content-type: application/json',
			'X-Interm-Device-ID: ' . $_svrInfo["api_key"],
			'X-Interm-Device-Secret: ' . $_svrInfo["api_secret"]
		);
		
		$curlsession = curl_init();
		curl_setopt ($curlsession, CURLOPT_URL, $url);
		curl_setopt ($curlsession, CURLOPT_FAILONERROR, true);
		curl_setopt ($curlsession, CURLOPT_TIMEOUT, 5);
		curl_setopt ($curlsession, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($curlsession, CURLOPT_HTTPHEADER, $headers);
		
		if($_data != null) {
			curl_setopt ($curlsession, CURLOPT_POST, 1);
			curl_setopt ($curlsession, CURLOPT_POSTFIELDS, json_encode($_data));	
		}
				
		$curlResp = curl_exec($curlsession);
		$errno	  = curl_errno($curlsession);
		
		if($errno == 0) {
			$respData = utf8_decode($curlResp);
			$respData = str_replace('?', '', $respData);
			
			$respDataObj = json_decode($respData);
			if(($respDataObj == null) || ($respDataObj->message != "ok")) {
				
			} else {
				return $respDataObj;
			}	
			
		} else {
			$ret = false;
		}
		
		curl_close($curlsession);
		
		return $ret;
	}
	 
	define("MAX_TRYCOUNT", 20);
	
	$envData = file_get_contents("/opt/interm/conf/env.json");
	$netData = file_get_contents("/opt/interm/public_html/modules/network_setup/conf/network_stat.json");
	
	$envObj  = json_decode($envData);
	$netObj  = json_decode($netData);
	
	$mid = trim(shell_exec('cat /etc/machine-id 2>/dev/null'));
	
	$svrMngFunc = new Common\Func\CommonSvrMngFunc();
	
	$dbHandler	= $svrMngFunc->getDBHandler();
	if($dbHandler == null) {
		return;
	}
	
	$svrList 	= $svrMngFunc->getSvrList();
	$svrKeyInfo = $svrMngFunc->getSvrKeyInfo();
	$svrCnt 	= count($svrKeyInfo);
	
	if($svrCnt == 0) {
		return;
	}
	
	$postData = array();
	$postData["deviceId"] = $mid;
	
	$postData["detail"] = array();
	$postData["detail"]["hostname"]	= $netObj->hostname;
	$postData["detail"]["location"]	= $netObj->location;
	$postData["detail"]["version"]	= $envObj->device->version;

	for($idx = 0; $idx < $svrCnt; $idx++) {
		$svrInfo = $svrKeyInfo[$idx];
		
		for($tryCnt = 0; $tryCnt < MAX_TRYCOUNT; $tryCnt++) {
			$ret = SendData("/api/common/updateDeviceInfo", $svrInfo, $postData);
			if($ret == false) {
				sleep(1);
				continue;
			}
			
			$ret = SendData("/api/getDeviceInfo", $svrInfo, null);
			if($ret != false) {
				UpdateServerInfo($ret->result);
			}
			
			break;
		}
	}

	
?>
