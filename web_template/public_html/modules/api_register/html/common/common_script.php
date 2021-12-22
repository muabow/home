<?php
	namespace Api_register\Func {

		use Api_register;
		use Common;

		class ApiRegisterFunc {
				private	$hashTable;

				const HEADER_MAX_DAY_CALL_COUNT = 1000000;		// max count (json 파일 최초 생성 시 default 값으로 사용)

			function __construct() {
				$load_hashTable		= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/device_key_list.json");
				$this->hashTable	= json_decode($load_hashTable, true);

				return ;
			}

			function getStdDate() {
				$headerFunc	= new Common\Func\CommonHeaderFunc("device_key_list.json");

				return $headerFunc->getStdDate();
			}

			function getUserList() { // for ajax
				$load_hashTable		= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/device_key_list.json");
				$this->hashTable	= json_decode($load_hashTable, true);

				return $this->makeUserList();
			}

			function setUserList($_serverAddr, $_apiKey, $_apiSecret, $_is_master_key = false) { // for ajax
				include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_define.php";
				include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_script.php";

				if( !isset($this->hashTable[$_apiKey]) ) {
					$this->hashTable[$_apiKey] = array();
				}
				
				// master_key 설정 시 동일한 key 정보가 존재하지만 master_key 가 아닐 때 master_key 로 승격
				if( $_is_master_key && isset($this->hashTable[$_apiKey][$_apiSecret]) && !isset($this->hashTable[$_apiKey][$_apiSecret]["master_key"]) ) {
					$this->hashTable[$_apiKey][$_apiSecret]["master_key"] = true;
					file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/device_key_list.json", json_encode($this->hashTable, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
					
					return true;

				} else if( isset($this->hashTable[$_apiKey][$_apiSecret]) ) {
					// 동일한 key 정보가 존재할 때 same key 처리
					return false; 
				}

				$this->hashTable["maxCount"] = self::HEADER_MAX_DAY_CALL_COUNT;
				
				if( $_is_master_key ) {
					foreach($this->hashTable as $key => $secretKey) {
						if( $key == "stdDate" || $key == "maxCount" ) continue;
	
						foreach( $secretKey as $secretKeyId => $userInfo ) {
							if( $userInfo["server_addr"] == $_serverAddr && isset($userInfo["master_key"]) ) {
								if( $_apiKey != $key || $_apiSecret != $secretKeyId ) {
									unset($this->hashTable[$key][$secretKeyId]);
									if( count($this->hashTable[$key]) == 0 ) unset($this->hashTable[$key]);

									break;
								}
							}
						}
					}

					$this->hashTable[$_apiKey][$_apiSecret] = [ "server_addr"	=> $_serverAddr,
																"day_count"		=> 0,
																"cum_count"		=> 0,
																"master_key"	=> true
															  ];

				} else {
					$this->hashTable[$_apiKey][$_apiSecret] = [ "server_addr"	=> $_serverAddr,
																"day_count"		=> 0,
																"cum_count"		=> 0
															  ];
				}

				file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/device_key_list.json", json_encode($this->hashTable, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

				return true;
			}


			function removeUserList($_secretKey) {
				$filePath = $_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/device_key_list.json";

				foreach($this->hashTable as $key => $secretKey) {
					if( $key == "stdDate" || $key == "maxCount" ) continue;

					foreach( $secretKey as $secretKeyId => $userInfo ) {
						if( $secretKeyId == $_secretKey ) {
							unset($this->hashTable[$key][$secretKeyId]);

							if( count($this->hashTable[$key]) == 0 ) unset($this->hashTable[$key]);

							file_put_contents($filePath, json_encode($this->hashTable, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

							return true;
						}
					}
				}

				return false;
			}

			function unset_master_key($_ip_addr) {
				$filePath = $_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/device_key_list.json";

				foreach($this->hashTable as $key => $secretKey) {
					if( $key == "stdDate" || $key == "maxCount" ) continue;

					foreach( $secretKey as $secretKeyId => $userInfo ) {
						if( $userInfo["server_addr"] == $_ip_addr && isset($userInfo["master_key"]) ) {
							unset($this->hashTable[$key][$secretKeyId]);

							if( count($this->hashTable[$key]) == 0 ) unset($this->hashTable[$key]);

							file_put_contents($filePath, json_encode($this->hashTable, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

							return true;
						}
					}
				}

				return false;
			}

			function makeUserList() {
				$userList = "";

				$idx = 1;
				foreach($this->hashTable as $key => $secretKey) {
					if( $key == "stdDate" || $key == "maxCount" ) continue;

					foreach( $secretKey as $secretKeyId => $userInfo ) {
						if( !isset($userInfo["master_key"]) )  continue;
						
						$userList .= '<div class="divTableRow">
								<div class="divTableCell divTableCell_checkbox divTableCell_master">
									<input type="checkbox" id="input_open_api_check_user_M' . $idx . '" style="padding-top: 15px;"/>
								</div>
								<div class="divTableCell divTableCell_number divTableCell_master">
									M
								</div>
								<div class="divTableCell divTableCell_master">
									<span id="span_open_api_table_key_M' . $idx . '">' . $key . '</span>
								</div>
								<div class="divTableCell divTableCell_master">
									<span id="span_open_api_table_secretKey_M' . $idx . '">' . $secretKeyId . '</span>
								</div>
								<div class="divTableCell divTableCell_master">
									<span id="span_open_api_table_ip_M'. $idx . '">' .  $userInfo['server_addr'] . '</span>
								</div>
								<div class="divTableCell divTableCell_master">
									' . $userInfo['day_count'] . ' / ' . $this->hashTable['maxCount'] . '
								</div>
								<div class="divTableCell_right divTableRight_master">
									' . $userInfo['cum_count'] . '
								</div>
							</div>
						';

						$idx++;
					}
				}
				
				$idx = 1;
				foreach($this->hashTable as $key => $secretKey) {
					if( $key == "stdDate" || $key == "maxCount" ) continue;

					foreach( $secretKey as $secretKeyId => $userInfo ) {
						if( isset($userInfo["master_key"]) )  continue;

						$userList .= '<div class="divTableRow">
								<div class="divTableCell divTableCell_checkbox">
									<input type="checkbox" id="input_open_api_check_user_' . $idx . '" style="padding-top: 15px;"/>
								</div>
								<div class="divTableCell divTableCell_number">
									' . $idx . '
								</div>
								<div class="divTableCell">
									<span id="span_open_api_table_key_' .$idx. '">' . $key . '</span>
								</div>
								<div class="divTableCell">
									<span id="span_open_api_table_secretKey_' . $idx . '">' . $secretKeyId . '</span>
								</div>
								<div class="divTableCell">
									<span id="span_open_api_table_ip_'. $idx . '">' .  $userInfo['server_addr'] . '</span>
								</div>
								<div class="divTableCell">
									' . $userInfo['day_count'] . ' / ' . $this->hashTable['maxCount'] . '
								</div>
								<div class="divTableCell_right">
									' . $userInfo['cum_count'] . '
								</div>
							</div>
						';
						$idx++;
					}
				}
				return $userList;
			}

			function getServerVersion($_serverAddr, $_apiKey, $_secretKey) {
				$url = $_serverAddr . "/api/getDeviceInfo";
				
				$headers = array(
									'Content-type: application/json', 
									'X-Interm-Device-ID: ' . $_apiKey,
									'X-Interm-Device-Secret: ' . $_secretKey
								);
		
				$curlsession = curl_init();
				curl_setopt ($curlsession, CURLOPT_URL, $url);
				curl_setopt ($curlsession, CURLOPT_POST, 0);
				curl_setopt ($curlsession, CURLOPT_TIMEOUT, 12);
				curl_setopt ($curlsession, CURLOPT_RETURNTRANSFER, true);
				curl_setopt ($curlsession, CURLOPT_HTTPHEADER, $headers);
		
				$result = utf8_decode(curl_exec($curlsession));
				$result = str_replace('?','',$result);
				curl_close($curlsession);
				
				$result = json_decode($result);
                
				return $result;
			}
	
			function checkIsCompatibleVersion($_serverVersion) {
				$load_envData  	= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/env.json");
				$envData  		= json_decode($load_envData);
					
				$deviceVersion = $envData->device->version;
				 
				$splitDeviceVersion = explode('.', $deviceVersion);
				$splitServerVersion = explode('.', $_serverVersion);
				
				$isCompatible = true;
				
				if(count($splitDeviceVersion) != count($splitServerVersion)) {
					$isCompatible = false;
				}
				
				if($splitDeviceVersion[1] != $splitServerVersion[1]) {	// compare major version. 
					$isCompatible = false;		
				}
				
				return $isCompatible;
			}
		}
		
		include_once "common_script_etc.php";
	}
?>
