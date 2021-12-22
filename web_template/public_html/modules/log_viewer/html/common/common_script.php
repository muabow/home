<?php
	namespace Log_viewer\Func {

		use Log_viewer;
		use Common;

		class LogViewerFunc {
			/* variables */
			private $arrLineUnits = Log_viewer\Def\LOG_DISPLAY_LINE_UNIT;
			private $logStatData;

			private $envData;

			/* constructor */
			// ajax에서 1000ms마다 호출하기 때문에 constructor 지양
			function __construct() {
				$load_envData  				= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . Common\Def\PATH_SYS_ENV_JSON);
				$envData   					= json_decode($load_envData);

				$this->envData				= $envData;
			}

			function setLogStatData() {
				$load_logStat		= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/modules/log_viewer/conf/log_stat.json");
				$statData			= json_decode($load_logStat);

				$this->logStatData = $statData;

				return ;
			}

			function setLogStatInfo($_name, $_value) {
				// ajax 호출, script 파일 기준 상대 경로
				$load_logStat		= file_get_contents("../../conf/log_stat.json");
				$statData			= json_decode($load_logStat);

				$statData->$_name   = $_value;

				file_put_contents("../../conf/log_stat.json", json_encode($statData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
				return ;
			}

			function getLogStatInfo($_name) {
				return $this->logStatData->$_name;
			}

			function getLogUpdateStat() {
				if( $this->getLogStatInfo("update") == Log_viewer\Def\OPTION_VALUE_DISABLED ) {
					echo " disabled";
				}
				return ;
			}

			function getStandSupportAPIStat($_name) {
				$stat = false;

				if( isset($this->envData->mode) ) {
					if( $this->envData->mode->set == "STAND ALONE" && $this->envData->mode->stand_support_api == false ) {
						if( $_name == "api" ) {
							$stat = true;
						}
					}
				}

				return $stat;
			}

			function checkUserAuth($_userName, $_auth) {
				$envAuth  = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/user_auth_list.json");
				$userList = json_decode($envAuth, true);

				if( !array_key_exists($_userName, $userList) ) return false;

				if( $userList[$_userName] <= $userList[$_auth] ) {
					return true;

				} else {
					return false;
				}
			}

			function makeLogFileList() {
				$envData			= $this->envData;

				$categoryModuleName = "";
				$categoryName 		= "";
				$logFileList  		= "";
				$categoryStat 		= false;

				foreach( $envData->module->list as $module_list ) {
					switch( $module_list->type ) {
						case Common\Def\JSON_MODULE_CATEGORY :

							if( !$this->checkUserAuth($_SESSION['username'], $module_list->auth) ) break;

							// STAND ALONE 모드 중 API 를 제공하지 않는 경우 출력 해제
							if( $this->getStandSupportAPIStat($module_list->name) ) {
								$categoryModuleName = $module_list->name;
								break;
							}

							$module_list_name = strtoupper($module_list->name);
							$category_name	  = constant("Common\Lang\STR_MENU_SETUP_" . $module_list_name);
							if( is_null($category_name) ) {
								$category_name = $module_list->type . " / " . $module_list->name;
							}

							if( $categoryName != "" ) {
								$logFileList .= '</optgroup>';
							}

							$categoryName       = $category_name;
							$categoryModuleName = $module_list->name;
							$categoryStat = false;

						break;

						case Common\Def\JSON_MODULE_MENU :
							if( !$this->checkUserAuth($_SESSION['username'], $module_list->auth) ) break;

							// STAND ALONE 모드 중 API 를 제공하지 않는 경우 출력 해제
							if( $this->getStandSupportAPIStat($categoryModuleName) ) break;

							// mode를 사용하고 mode가 설정된 모듈만 출력하도록 제한
							if( isset($envData->mode->set) && isset($module_list->mode) ) {
								if( $envData->mode->set != $module_list->mode ) break;
							}

							$module_path		   = $module_list->path;

							// Module의 log file 존재 확인
							$module_contents = $module_path . "/log/" . $module_list->name . '.log';
							if( !file_exists($module_contents) ) {
								break;
							}

							// log 명세
							$module_list_name = ucfirst($module_list->name);
							$module_name	  = constant($module_list_name . "\Lang\STR_MENU_NAME");

							if( $categoryStat == false ) {
								$logFileList .= '<optgroup label="' . $categoryName . '">';
								$categoryStat = true;
							}

							$optModule = $categoryModuleName . '/'. $module_list->name;

							// selected 조건 명세
							$optSelect = "";
							if( $this->logStatData->module == $optModule ) {
								$optSelect = "selected";
							}

							$logFileList .= '<option value="' . $optModule . '" ' . $optSelect . '> ' . $module_name . '</option>';

						break;
					}

					next($envData->module);
				}

				if( $logFileList != "" ) {
					$logFileList .= '</optgroup>';
				}

				// common 부분, template에서 생성하는 system.log 또는 임의로 생성된 log 파일들 view 생성
				$arrStat = false;
				foreach (glob("../log/*.log") as $filename) {
					if( $arrStat == false ) {
						$logFileList .= '<optgroup label="' . Log_viewer\Lang\STR_LOG_COMMON . '">';
						$arrStat = true;
					}

					$filename = str_replace(".log", "", basename($filename));
					if( $filename == "system" ) {
						$desc = Log_viewer\Lang\STR_LOG_COMMON_LOG;

					} else {
						$desc = $filename;
					}

					$optModule = "common/". $filename;
					$optSelect = "";

					// selected 조건 명세
					if( $this->logStatData->module == $optModule ) {
						$optSelect = "selected";
					}

					$logFileList .= '<option value="' .  $optModule . '" ' . $optSelect . '>' . $desc . '</option>';
				}

				if( $arrStat == true ) {
					$logFileList .= '</optgroup>';
				}

				return $logFileList;
			}

			function makeLogDisplayLine() {
				$lineList = '<optgroup label="' . Log_viewer\Lang\STR_LOG_DISPLAY_UNIT . '">';
				$arrLineUnit = $this->arrLineUnits;

				foreach( $arrLineUnit as $line ) {
					$optSelect = "";
					if( $this->logStatData->line == $line ) {
						$optSelect = "selected";
					}
					if( $line == 10240 ) {
						$lineName = Log_viewer\Lang\STR_LOG_FULL;
					} else {
						$lineName = $line . ' ' . Log_viewer\Lang\STR_LOG_DISPLAY_LINE;
					}
					$lineList .= '<option value="' . $line . '" ' . $optSelect . '>' . $lineName . '</option>';
				}
				$lineList .= '</optgroup>';

				return $lineList;
			}

			function makeLogUpdateMode() {
				if( $this->logStatData->update == Log_viewer\Def\OPTION_VALUE_ENABLED ) {
					$optEnabled  = "selected";
					$optDisabled = "";

				} else {
					$optEnabled  = "";
					$optDisabled = "selected";
				}

				$lineList = '<optgroup label="' . Log_viewer\Lang\STR_OPTION_AUTO_UPDATE . '">';
				$lineList .= '<option value="' . Log_viewer\Def\OPTION_VALUE_ENABLED .  '" ' . $optEnabled .  '>' . Log_viewer\Lang\STR_OPTION_ENABLED  . '</option>';
				$lineList .= '<option value="' . Log_viewer\Def\OPTION_VALUE_DISABLED . '" ' . $optDisabled . '>' . Log_viewer\Lang\STR_OPTION_DISABLED . '</option>';
				$lineList .= '</optgroup>';

				return $lineList;
			}

			function makeLogUpdateTime() {
				$lineList = '<optgroup label="' . Log_viewer\Lang\STR_OPTION_UPDATE_PERIOD . '">';

				for($idx = 1 ; $idx <= Log_viewer\Def\OPTION_MAX_UPDATE_TIME ; $idx++ ) {
					$optSelect = "";
					if( $this->logStatData->time == ($idx * 1000) ) {
						$optSelect = "selected";
					}

					if( $idx == 1 ) $sec = Log_viewer\Lang\STR_OPTION_SEC;
					else 			$sec = Log_viewer\Lang\STR_OPTION_SECS;

					$lineList .= '<option value="' . ($idx * 1000) . '" ' . $optSelect . '>' . $idx . " " . $sec . '</option>';
				}

				$lineList .= '</optgroup>';

				return $lineList;
			}

			function makeLogScrollMode() {
				if( $this->logStatData->scroll == Log_viewer\Def\OPTION_VALUE_AUTO ) {
					$optEnabled  = "selected";
					$optDisabled = "";

				} else {
					$optEnabled  = "";
					$optDisabled = "selected";
				}

				$lineList = '<optgroup label="' . Log_viewer\Lang\STR_OPTION_SCROLL_MODE . '">';
				$lineList .= '<option value="' . Log_viewer\Def\OPTION_VALUE_AUTO .   '" ' . $optEnabled .  '>' . Log_viewer\Lang\STR_OPTION_AUTO . '</option>';
				$lineList .= '<option value="' . Log_viewer\Def\OPTION_VALUE_MANUAL . '" ' . $optDisabled . '>' . Log_viewer\Lang\STR_OPTION_MANUAL . '</option>';
				$lineList .= '</optgroup>';


				return $lineList;
			}

			function getLogContents($_logType, $_line, $_currentIdx) {
				$curIdx = $this->getCurrentIndex($_logType);
				$logMsg = "";

				// 1. 최초 실행 시
				if( $_currentIdx == -1 ) {
					$logMsg = $this->getLogMessage($_logType, $_line, $curIdx);

				// 2. 업데이트 시
				} else {
					if( $_currentIdx != $curIdx ) {
						$logLine = $curIdx - $_currentIdx;
						$logMsg  = $this->getLogMessage($_logType, $logLine, $curIdx);
					}
				}

				return '{"index":"' . $curIdx . '", "log":"' . $logMsg . '"}';
			}


			function getCurrentIndex($_logType) {
				$arrLogType   = explode("/", $_logType);
				$categoryName = $arrLogType[0];
				$moduleName   = $arrLogType[1];
				$logMessage	  = "";

				if( $categoryName == Log_viewer\Def\TYPE_MODULE_COMMON ) {
					$logPath = $_SERVER['DOCUMENT_ROOT'] . "/../log/". $moduleName . ".log";
				} else {
					$logPath = $_SERVER['DOCUMENT_ROOT'] . "/modules/" . $moduleName . "/log/" . $moduleName . ".log";
				}

				if( !($handle = fopen($logPath, "rb")) ) return ;

				fseek($handle, -4, SEEK_END);
				$logIndex = fread($handle, 4);
				$header = unpack("iindex/", $logIndex);
				$curIndex = $header['index'];

				fclose($handle);

				return $curIndex;
			}

			function getLogMessage($_logType, $_line, $_curIdx) {
				$arrLogType   = explode("/", $_logType);
				$categoryName = $arrLogType[0];
				$moduleName   = $arrLogType[1];
				$logMessage	  = "";
				$lineCnt	  = $_line;

				if( $categoryName == Log_viewer\Def\TYPE_MODULE_COMMON ) {
					$logPath = $_SERVER['DOCUMENT_ROOT'] . "/../log/". $moduleName . ".log";
				} else {
					$logPath = $_SERVER['DOCUMENT_ROOT'] . "/modules/" . $moduleName . "/log/" . $moduleName . ".log";
				}

				if( !($handle = fopen($logPath, "rb")) ) return ;

				// 전체 로그
				if( $_line == 0 ) {
					$_line = Common\Def\SIZE_LOG_LINE;
				}
				
				// 읽을 line이 0 미만일 경우 
				if( $lineCnt < 0 ) {
					$lineCnt += Common\Def\SIZE_LOG_LINE - 1;
				}

				$curIndex = $_curIdx;
				$lineIdx = $curIndex - $lineCnt;


				if( $lineIdx < 0 ) {
					$preIdx = Common\Def\SIZE_LOG_LINE + $lineIdx;

					fseek($handle, Common\Def\SIZE_LOG_BYTE * ($preIdx), SEEK_SET);
					for( $idx = $preIdx ; $idx < Common\Def\SIZE_LOG_LINE ; $idx++ ) {
						$logMsg = fread($handle, Common\Def\SIZE_LOG_BYTE);

						if( trim($logMsg) != null ) {
							$logMessage .= trim($logMsg,  "\x00..\x1F") . "<br />";
						}
					}

					fseek($handle, 0, SEEK_SET);
					for( $idx = 0 ; $idx < $curIndex ; $idx++ ) {
						$logMsg = fread($handle, Common\Def\SIZE_LOG_BYTE);

						if( trim($logMsg) != null ) {
							$logMessage .= trim($logMsg,  "\x00..\x1F") . "<br />";

						} else {
							break;
						}
					}

				} else {
					fseek($handle, Common\Def\SIZE_LOG_BYTE * ($lineIdx), SEEK_SET);

					for( $idx = $lineIdx ; $idx < $curIndex ; $idx++ ) {
						$logMsg = fread($handle, Common\Def\SIZE_LOG_BYTE);

						if( trim($logMsg) != null ) {
							$logMessage .= trim($logMsg,  "\x00..\x1F") . "<br />";

						} else {
							break;
						}
					}

				}

				fclose($handle);
				return $logMessage;
			}

		} // end of LogViewerFunc()

		include_once "common_script_etc.php";
	}
?>
