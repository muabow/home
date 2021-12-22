<?php

	function GetDeviceStatus() {
		//cpu stat
		$cpu_result = shell_exec("cat /proc/cpuinfo | grep model\ name");
		$stat['cpu_model'] = strstr($cpu_result, "\n", true);
		$stat['cpu_model'] = str_replace("model name\t: ", "", $stat['cpu_model']);

		//memory stat
		$stat['mem_percent'] = round(shell_exec("free | grep Mem | awk '{print $3/$2 * 100.0}'"), 2);
		$mem_result = shell_exec("cat /proc/meminfo | grep MemTotal");

		$stat['mem_total'] = round(preg_replace("#[^0-9]+(?:\.[0-9]*)?#", "", $mem_result) / 1024 / 1024, 3);
		$mem_result = shell_exec("cat /proc/meminfo | grep MemFree");

		$stat['mem_free'] = round(preg_replace("#[^0-9]+(?:\.[0-9]*)?#", "", $mem_result) / 1024 / 1024, 3);
		$stat['mem_used'] = $stat['mem_total'] - $stat['mem_free'];

		//hdd stat
		$stat['hdd_free']    = round(disk_free_space("/") / 1024 / 1024 / 1024, 2);
		$stat['hdd_total']   = round(disk_total_space("/") / 1024 / 1024/ 1024, 2);
		$stat['hdd_used']    = $stat['hdd_total'] - $stat['hdd_free'];
		$stat['hdd_percent'] = round(sprintf('%.2f',($stat['hdd_used'] / $stat['hdd_total']) * 100), 2);

		return $stat;
	}

	function GetApiRegStatus($_ipAddr) {
		/* API key matching check */
		$load_hashTable	= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/device_key_list.json");
		$hashTable		= json_decode($load_hashTable, true);
		$flagRemote		= false;

		foreach( $hashTable as $key => $keyInfo ) {
			if( is_array($keyInfo) ) {
				foreach( $keyInfo as $secretKey => $secretInfo ) {
					if( $_ipAddr == $secretInfo["server_addr"] ) {
						$flagRemote = true;

						break;
					}
				}
			}

			if( $flagRemote == true ) break;
		}

		return $flagRemote;
	}

	function GetApiKeyStatus($_ipAddr) {
		/* API key matching check */
		$load_hashTable		= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/device_key_list.json");
		$hashTable			= json_decode($load_hashTable, true);
		$flagRemote			= false;
		$arrKey				= Array();
		$arrKey["key_list"]	= array();

		foreach( $hashTable as $key => $keyInfo ) {
			if( is_array($keyInfo) ) {
				foreach( $keyInfo as $secretKey => $secretInfo ) {
					if( $_ipAddr == $secretInfo["server_addr"] ) {
						$flagRemote = true;
						$arrKey["key_list"][$key] = $secretKey;
						
						$arrKey["id_key"] 		= $key;
						$arrKey["secret_key"] 	= $secretKey;
					}
				}
			}
		}

		if( !$flagRemote ) {
			$arrKey["id_key"] 		= null;
			$arrKey["secret_key"] 	= null;
		}

		return $arrKey;
	}


	function GetUsedMngSvrInfo() {
		$confPath = $_SERVER['DOCUMENT_ROOT'] . "/../conf/config-manager-server.db";
		$db = new SQLite3($confPath);

		$query   = "select * from mng_svr_info where mng_svr_used='1'; ";
		$results = $db->query($query);
		$row = $results->fetchArray(1);

		$db->close();

		/* API key matching check */
		$load_hashTable	= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/device_key_list.json");
		$hashTable		= json_decode($load_hashTable, true);
		$flagRemote		= false;

		foreach( $hashTable as $key => $keyInfo ) {
			if( is_array($keyInfo) ) {
				foreach( $keyInfo as $secretKey => $secretInfo ) {
					if( $row["mng_svr_ip"] == $secretInfo["server_addr"] ) {
						$flagRemote = true;

						break;
					}
				}
			}

			if( $flagRemote == true ) break;
		}

		$svrInfo = null;
		if( $flagRemote ) {
			$svrInfo["mng_svr_ip"] 		= $row["mng_svr_ip"];
			$svrInfo["mng_svr_port"]	= $row["mng_svr_port"];
			$svrInfo["api_key"]			= $key;
			$svrInfo["api_secret"]		= $secretKey;
		}

		return $svrInfo;
	}
	
	// 외부 타 장비 동기 GET function
	function GetEtcSync($_url, $_device_id, $_secret_key) {
		$device_id  = $svrInfo["api_key"];
		$secret_key = $svrInfo["api_secret"];

		$headers = array(
							'Content-type: application/json',
							'X-Interm-Device-ID: ' . $_device_id,
							'X-Interm-Device-Secret: ' . $_secret_key
						);

		$curlsession = curl_init();
		curl_setopt($curlsession, CURLOPT_URL, $_url);
		curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlsession, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($curlsession);

		curl_close($curlsession);
		
		return $result;
	}

		// Method Type: GET, Search All
	// curl -i -X GET http://192.168.47.179/api/status
	$app->get(
		"/status",
		function() use($app) {
			$stat = GetDeviceStatus();

			$app->setResponseMessage("ok");
			$app->setResponseResult(
									array(
										"cpu_model"		=> $stat['cpu_model'],
										"mem_total"		=> $stat['mem_total'],
										"mem_percent"	=> $stat['mem_percent'],
										"mem_used"		=> $stat['mem_used'],
										"mem_free"		=> $stat['mem_free'],
										"hdd_total"		=> $stat['hdd_total'],
										"hdd_free"		=> $stat['hdd_free'],
										"hdd_used"		=> $stat['hdd_used'],
										"hdd_percent"	=> $stat['hdd_percent']
									 	)
									);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	// Method Type: GET, Search by name
	// ex) curl -i -X GET http://192.168.47.179/api/status/cpu_model
	$app->get(
		"/status/{name}",
		function() use($app) {
			$stat   = GetDeviceStatus();
			$arrUri = $app->getRequestURI();

			$app->setResponseMessage("ok");
			$app->setResponseResult(
									array($arrUri['name']	=> $stat[$arrUri['name']])
									);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	// Method Type: POST, JSON data 처리
	// ex) curl -i -X POST -H "Content-Type: application/json" -d '{"id":"2","name":"Astro Boy"}' http://192.168.47.179/api/post
	$app->post(
		"/post",
		function() use($app) {
			$inputData = $app->getPostContent();

			$app->setResponseMessage("ok");
			$app->setResponseResult(
									array(	"id"	=> $inputData->id,
											"name"	=> $inputData->name
								 		)
									);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	// Method Type: POST, JSON data 처리 (by name)
	// ex) curl -i -X POST -H "Content-Type: application/json" -d '{"id":"2","name":"Astro Boy"}' http://192.168.47.179/api/post/test
	$app->post(
		"/post/{name}",
		function() use($app) {
			$arrUri    = $app->getRequestURI();
			$inputData = $app->getPostContent();

			$app->setResponseMessage("ok");
			$app->setResponseResult(
									array($arrUri['name']	=> $inputData->id)
									);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	// common
	$app->post(
		"/common/getHostInfo",
		function() use($app) {
			$header_info = $app->getHeaderContent();
			$inputData   = $app->getPostContent();

			$app->setResponseMessage("ok");
			$app->setResponseCode(200);

			$is_super_request = false;
			if( $header_info["x-interm-device-id"] == "1" && file_exists("/opt/interm/key_data/ljh.txt") ) {
				$m_key = trim(file_get_contents("/opt/interm/key_data/ljh.txt"));

				if( $header_info["x-interm-device-secret"] == $m_key ) {
					$is_super_request = true;
				}
			}

			// post parameter check
			if( (!isset($inputData->mng_server_ip)      || $inputData->mng_server_ip == "") ) {
				$app->setResponseResult(
								array(
									"status"		=> "fail",
									"message"		=> "invaild parameter"
									)
								);

				return $app->getResponseData();
			}

			// super key 로 접근 시 regist server check 우회
			if( !$is_super_request ) {
				if( !GetApiRegStatus($inputData->mng_server_ip) ) {
					$app->setResponseResult(
									array(
										"status"		=> "fail",
										"message"		=> "unregistered server [{$inputData->mng_server_ip}]"
										)
									);

					return $app->getResponseData();
				}
			}
			
			// check svr version
			$confPath = $_SERVER['DOCUMENT_ROOT'] . "/../conf/config-manager-server.db";
			$db = new SQLite3($confPath);

			$query   = "select mng_svr_version from mng_svr_info where mng_svr_ip='{$inputData->mng_server_ip}' order by mng_svr_version desc, mng_svr_date desc;";
			$results = $db->query($query);
			$row = $results->fetchArray(1);
			$db->close();
			
			$svrVersion = $row["mng_svr_version"];
			$svrSecVersion = "";
			if($svrVersion != "") {
				$svrSecVersion = explode(".", $svrVersion)[1];
			}

			$envJson  	= file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/../conf/env.json");
			$envJson  		= json_decode($envJson, true);
			
			$devVersion = $envJson["device"]["version"];
			$devSecVersion = explode(".", $devVersion)[1];
			
			if($svrSecVersion != $devSecVersion) {
				$app->setResponseMessage("fail");
				$app->setResponseCode(200);
				$app->setResponseResult(
								array(
									"status"		=> "fail",
									"message"		=> "not match version.[Svr : {$svrVersion}, Dev : {$devVersion}]"
								 	)
								);

				return $app->getResponseData();
			}

			$machineId = trim(shell_exec('cat /etc/machine-id 2>/dev/null'));
			$hostName  = trim(shell_exec('cat /etc/hostname 2>/dev/null'));

			$load_envData  	= file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/modules/network_setup/conf/network_stat.json");
			$envData  		= json_decode($load_envData);

			$app->setResponseResult(
									array(
										"host"			=> $hostName,
										"device_info"	=> array(
																"device_id"		=> $machineId,
																"hostname"		=> $envData->hostname,
																"location"		=> $envData->location,
																"bonding"		=> array(
																						"use"			=> $envData->network_bonding->use,
																						"mac_address"	=> $envData->network_bonding->mac_address,
																						"ip_address"	=> $envData->network_bonding->ip_address
																						),
																"primary"		=> array(
																						"use"			=> $envData->network_primary->use,
																						"mac_address"	=> $envData->network_primary->mac_address,
																						"ip_address"	=> $envData->network_primary->ip_address
																						),
																"secondary"		=> array(
																						"use"			=> $envData->network_secondary->use,
																						"mac_address"	=> $envData->network_secondary->mac_address,
																						"ip_address"	=> $envData->network_secondary->ip_address
																						),
															 	)
															)
									);

			return $app->getResponseData();
		}
	);
	
	// only use controller
	$app->post(
		"/common/updateDeviceInfo",
		function() use($app) {
			$inputData = $app->getPostContent();
			if($inputData != null) {
				$devMID 	= $inputData->deviceId;
				
				$db_control = new Common\Func\db_control();
				$link 		= $db_control->Connect();
				
				$query 		= "SELECT * FROM tbl_network_device WHERE f_device_no = '{$devMID}'";
				$result 	= $db_control->SelectQuery($query, $link);
				
				if($result != null) {
					$row = $result->fetch_assoc();
				
					$recvHostName = $inputData->detail->hostname;
					$recvLocation = $inputData->detail->location;
					$recvVersion  = $inputData->detail->version;
					
					if(($row["f_device_name"] != $recvHostName) || 
					   ($row["f_device_loc"] != $recvLocation)  ||
					   ($row["f_reserved_varchar_value1"] != $recvVersion)) {
						$query 	= "UPDATE tbl_network_device SET f_device_name = '{$recvHostName}', f_device_loc = '{$recvLocation}', "
						 		 ."f_reserved_varchar_value1 = '{$recvVersion}' WHERE f_device_no = '{$devMID}'";
						
						$result = $db_control->Query($query, $link);
					}
				}
				
				$db_control->Close($link);
			}

			$app->setResponseMessage("ok");
			$app->setResponseCode(200);
			
			return $app->getResponseData();
		}
	);
	
	$app->post(
		"/common/updateServerInfo",
		function() use($app) {
			$ret 		 = array();
			$ret["stat"] = "ok";

			$inputData = $app->getPostContent();
			if($inputData == null) {
				$ret["stat"] = "fail";
				goto pass;
			}
			
			$envData  	= file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/../conf/env.json");
			$envDataObj = json_decode($envData);
			
			$svrMsgFunc = new Common\Func\CommonSvrMngFunc();

			$svrInfo 	= $svrMsgFunc->getSvrInfoByMID($inputData->device_id);
			if($svrInfo == null) {
				$ret["stat"] = "fail";
				goto pass;
			}	
			
			$version 			= $envDataObj->device->version;
			$splitVersion	 	= explode('.', $version);
			$compatibleVersion 	= (explode('.', $inputData->device_version)[1]);
	
			$enable = 0;
			if($splitVersion[1] == $compatibleVersion) {
				$enable = 1;	
			}
			
			$extend = 0;
			if(isset($inputData->device_extend) == true) {
				$extend = 1;	
			}
			
			$updateData = array();
			if((isset($svrInfo["mng_svr_version"]) == true) && ($svrInfo["mng_svr_version"] != $inputData->device_version)) {
				$updateData["mng_svr_version"] =  $inputData->device_version;
			}
			
			if((isset($svrInfo["mng_svr_enabled"]) == true) && ($svrInfo["mng_svr_enabled"] != $enable)) {
				$updateData["mng_svr_enabled"] = $enable;
			}
			
			if((isset($svrInfo["mng_svr_extend"]) == true) && ($svrInfo["mng_svr_extend"] != $extend)) {
				$updateData["mng_svr_extend"] = $extend;
			}
	
			
			if(count($updateData) > 0) {
				$svrMsgFunc->updateSvrInfoByMId($inputData->device_id, $updateData);
			}
			
		pass:
			$app->setResponseMessage("ok");
			$app->setResponseResult($ret);
			$app->setResponseCode(200);
			
			return $app->getResponseData();
		}
	);
	$app->post(
		"/common/setMngServerInfo",
		function() use($app) {
			$header_info = $app->getHeaderContent();
			$inputData   = $app->getPostContent();

			$app->setResponseMessage("ok");
			$app->setResponseCode(200);

			$is_super_request = false;
			if( $header_info["x-interm-device-id"] == "1" && file_exists("/opt/interm/key_data/ljh.txt") ) {
				$m_key = trim(file_get_contents("/opt/interm/key_data/ljh.txt"));

				if( $header_info["x-interm-device-secret"] == $m_key ) {
					$is_super_request = true;
				}
			}

			// post parameter check
			if( (!isset($inputData->mng_server_id)      || $inputData->mng_server_id == "")
				&& (!isset($inputData->mng_server_ip)   || $inputData->mng_server_ip == "")
				&& (!isset($inputData->mng_server_port) || $inputData->mng_server_port == "")
				&& (!isset($inputData->mng_server_name) || $inputData->mng_server_name == "") ) {

				$app->setResponseResult(
								array(
									"status"		=> "fail",
									"message"		=> "invaild parameter"
									)
								);

				return $app->getResponseData();
			}
			
			// controller version check
			$arrKey = GetApiKeyStatus($inputData->mng_server_ip);

			$svrVersion = "";
			$isUsed     = "1";
			$isExtend   = "0";
			$result     = null;
			$svrSecVersion = "";
			$devSecVersion = "";
			
			if(isset($inputData->mng_server_version) && $inputData->mng_server_version != "") {
				$svrVersion    = $inputData->mng_server_version;
				$svrSecVersion = explode(".", $svrVersion)[1];
				$isExtend = "1";

			} else {
				$deviceInfoUri = "http://" . $inputData->mng_server_ip . "/api/getDeviceInfo";
				$result = GetEtcSync($deviceInfoUri, $arrKey["id_key"], $arrKey["secret_key"]);
				$result = json_decode($result, true);
				
				if(isset($result["result"]["version"])) {
					$svrVersion = $result["result"]["version"];
					$svrSecVersion = explode(".", $svrVersion)[1];
				}
			}
			
			$envData = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/env.json");
			$envData = json_decode($envData, true);
			$version = $envData["device"]["version"];
			$devSecVersion = explode(".", $version)[1];
			
			if($svrSecVersion != '') {
				if ($svrSecVersion != $devSecVersion) {
					$isUsed = "0";
				}
			} else {
				$isUsed = "0";
			}

			$confPath = $_SERVER['DOCUMENT_ROOT'] . "/../conf/config-manager-server.db";
			$queryType  = "insert";

			// DB file check
			if( !file_exists($confPath) ) {
				$app->setResponseResult(
								array(
									"status"		=> "fail",
									"message"		=> "db file not found"
									)
								);
			} else {
				$db = new SQLite3($confPath);

				// super key 로 접근 시 regist server check 우회
				if( !$is_super_request ) {
					if( !GetApiRegStatus($inputData->mng_server_ip) ) {
						$app->setResponseResult(
										array(
											"status"		=> "fail",
											"message"		=> "unregistered server [{$inputData->mng_server_ip}]"
											)
										);

						return $app->getResponseData();
					}
				}

				/* case: remove				*/
				if( isset($inputData->act_type) && $inputData->act_type == "remove" ) {
					$query   = "delete from mng_svr_info where mng_svr_id='{$inputData->mng_server_id}';";
					$results = $db->query($query);

					if( $results ) {
						$app->setResponseResult(
									array(
										"status"		=> "ok",
										"message"		=> "remove server information : {$inputData->mng_server_id}"
									 	)
									);

					} else {
						$app->setResponseResult(
									array(
										"status"		=> "fail",
										"message"		=> $db->lastErrorMsg()
									 	)
									);
					}
					$db->close();

					return $app->getResponseData();
				}

				/* case: insert, update		*/
				// #1. DB에 row가 있는지 확인
				$query   = "select * from mng_svr_info;";
				$results = $db->query($query);

				if( !$results ) {
					$app->setResponseResult(
								array(
									"status"		=> "fail",
									"message"		=> $db->lastErrorMsg()
								 	)
								);

					return $app->getResponseData();
				}

				$arrRows = array();
				while( $row = $results->fetchArray(1) ) {
					array_push($arrRows, $row);
				}

				$rowCnt = count($arrRows);
				
				if($isUsed == "1") {
					$query = "update mng_svr_info set mng_svr_used = '0';";
					$db->query($query);
				}

				$date  = time();

				// #2. row가 0이면 입력 처리 (insert)
				if( $rowCnt == 0 ) {
					$query = "insert into mng_svr_info
											(mng_svr_used, mng_svr_id, mng_svr_ip, mng_svr_port, mng_svr_name, mng_svr_date, mng_svr_version, mng_svr_enabled, mng_svr_extend)
											values('" . $isUsed . "', '{$inputData->mng_server_id}', '{$inputData->mng_server_ip}', {$inputData->mng_server_port}, '{$inputData->mng_server_name}', '{$date}', '{$svrVersion}', '{$isUsed}', '{$isExtend}'); ";
				} else {
					// #3. 해당 row가 있는지 확인
					$query   = "select * from mng_svr_info where mng_svr_id='{$inputData->mng_server_id}'; ";
					$results = $db->query($query);

					$arrRows = array();
					while( $row = $results->fetchArray(1) ) {
						array_push($arrRows, $row);
					}

					$rowCnt = count($arrRows);
					if( $rowCnt == 0 ) {
						// #4. 해당하는 row가 없다면 입력 처리 (insert)
						$query = "insert into mng_svr_info
											(mng_svr_used, mng_svr_id, mng_svr_ip, mng_svr_port, mng_svr_name, mng_svr_date, mng_svr_version, mng_svr_enabled, mng_svr_extend)
											values('" . $isUsed . "', '{$inputData->mng_server_id}', '{$inputData->mng_server_ip}', {$inputData->mng_server_port}, '{$inputData->mng_server_name}', '{$date}', '{$svrVersion}', '{$isUsed}', '{$isExtend}'); ";

					} else {
						// #5. row가 0이 아니면 갱신 처리 (update)
						$query = "update mng_svr_info set
											mng_svr_used = '" . $isUsed . "',
											mng_svr_ip   = '{$inputData->mng_server_ip}',
											mng_svr_port = {$inputData->mng_server_port},
											mng_svr_name = '{$inputData->mng_server_name}',
											mng_svr_date = '{$date}',
											mng_svr_version = '{$svrVersion}',
											mng_svr_enabled = '{$isUsed}',
											mng_svr_extend = '{$isExtend}'
								  where mng_svr_id = '{$inputData->mng_server_id}'; ";

						$queryType = "update";
					}
				}
				$results = $db->query($query);

				$pathKeyModule = 'http://' . $_SERVER["HTTP_HOST"] . '/modules/system_mng_svr_info/html/common/system_process.php';

				if( $results ) {
					$app->setResponseResult(
								array(
									"status"		=> "ok",
									"message"		=> "{$queryType} server information",
									"api_key"		=> array(
																"id_key"		=> $arrKey["id_key"],
																"secret_key"	=> $arrKey["secret_key"],
																"key_list"		=> $arrKey["key_list"]
															)
								 	)
								);

					$postData["type"] 			= "system";
					$postData["act"] 			= "log";
					$postData["results"] 		= "success";
					$postData["key"] 			= $inputData->mng_server_id;
					$postData["ip"] 			= $inputData->mng_server_ip;

					PostIntAsync($pathKeyModule, $postData);
				} else {
					$app->setResponseResult(
								array(
									"status"		=> "fail",
									"message"		=> $db->lastErrorMsg()
								 	)
								);
					$postData["type"] 			= "system";
					$postData["act"] 			= "log";
					$postData["results"] 		= "fail";

					PostIntAsync($pathKeyModule, $postData);
				}

				$db->close();
			}

			return $app->getResponseData();
		}
	);


?>
