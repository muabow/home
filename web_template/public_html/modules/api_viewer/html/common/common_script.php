<?php
	namespace Api_viewer\Func {

		use Api_viewer;
		use Common;

		class ApiViewerFunc {
				private	$hashTable;

			function __construct() {
				$load_hashTable		= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/header_key.json");
				$this->hashTable	= json_decode($load_hashTable, true);
				
				include_once $_SERVER['DOCUMENT_ROOT'] . "/" . "common/common_define.php";
				
				$envData   			= json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . Common\Def\PATH_SYS_ENV_JSON));
				$env_langSet  		= $envData->info->language_set;
				$env_language_name 	= $envData->language_pack->$env_langSet->path;
				$env_pathModule 	= str_replace(basename(__FILE__), "..", realpath(__FILE__));
				
				include_once $env_pathModule . "/" . $envData->language_pack->$env_langSet->path;

				return ;
			}

			function getStdDate() {
				$headerFunc	= new Common\Func\CommonHeaderFunc();

				return $headerFunc->getStdDate();
			}

			function getUserList() { // for ajax
				$load_hashTable		= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/header_key.json");
				$this->hashTable	= json_decode($load_hashTable, true);

				return $this->makeUserList();
			}

			function setUserList($_userMail, $_userContact, $_userCompany) { // for ajax
				include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_define.php";
				include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_script.php";

				$headerFunc	= new Common\Func\CommonHeaderFunc();

				$headerId  = $headerFunc->makeHeaderId($_userCompany);
				$secretKey = $headerFunc->makeHeaderSecretKey($_userMail);

				$arrUserInfo = array();
				$arrUserInfo['companyName'] = $_userCompany;
				$arrUserInfo['userName']    = $_userMail;
				$arrUserInfo['contact']		= $_userContact;

				$headerFunc->setHashTable($headerId, $secretKey, $arrUserInfo);

				return true;
			}


			function removeUserList($_secretKey) {
				$filePath = $_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/header_key.json";

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

			function makeUserList() {
				$userList	= "";
				$idx = 1;

				foreach($this->hashTable as $key => $secretKey) {
					if( $key == "stdDate" || $key == "maxCount" ) continue;

					foreach( $secretKey as $secretKeyId => $userInfo ) {
						if( !isset($userInfo["master_key"]) )  continue;

						$userList .= '<div class="divTableRow">
								<div class="divTableCell divTableCell_checkbox divTableCell_master">
									-
								</div>
								<div class="divTableCell divTableCell_number divTableCell_master">
									M
								</div>
								<div class="divTableCell divTableCell_master">
									' . $key . '
								</div>
								<div class="divTableCell divTableCell_master">
									<span id="span_open_api_table_secretKey_' . '0' . '">' . $secretKeyId . '</span>
								</div>
								<div class="divTableCell divTableCell_master">
									' . $userInfo['day_count'] . ' / ' . $this->hashTable['maxCount'] . '
								</div>
								<div class="divTableCell divTableCell_master">
									' . $userInfo['cum_count'] . '
								</div>
								<div class="divTableCell divTableCell_master">
									' . $userInfo['userName'] . '
								</div>
								<div class="divTableCell divTableCell_master">
									' . $userInfo['contact'] . '
								</div>
								<div class="divTableCell divTableCell_master">
									' . $userInfo['companyName'] . '
								</div>
								<div class="divTableCopy divTableCopy_master">
									<div class="copy_button">' . Api_viewer\Lang\STR_BODY_COPY . '</div>
								</div>
							</div>
						';
					}
				}

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
									' . $key . '
								</div>
								<div class="divTableCell">
									<span id="span_open_api_table_secretKey_' . $idx . '">' . $secretKeyId . '</span>
								</div>
								<div class="divTableCell">
									' . $userInfo['day_count'] . ' / ' . $this->hashTable['maxCount'] . '
								</div>
								<div class="divTableCell">
									' . $userInfo['cum_count'] . '
								</div>
								<div class="divTableCell">
									' . $userInfo['userName'] . '
								</div>
								<div class="divTableCell">
									' . $userInfo['contact'] . '
								</div>
								<div class="divTableCell">
									' . $userInfo['companyName'] . '
								</div>
								<div class="divTableCopy">
									<div class="copy_button">' . Api_viewer\Lang\STR_BODY_COPY . '</div>
								</div>
							</div>
						';
						$idx++;
					}
				}

				return $userList;
			}
		}
		
		include_once "common_script_etc.php";
	}
?>
