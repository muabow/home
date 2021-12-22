<?php
	$_SERVER["DOCUMENT_ROOT"] = "/opt/interm/public_html";

	include "/opt/interm/public_html/common/common_define.php";

	class CommonLogFunc {
		const TIMEZONE_TYPE		= 0;
		const TIMEZONE_GMT		= 1;
		const TIMEZONE_NAME		= 2;
		const TIMEZONE_PATH		= 3;

		//* variables */
		private $env_logPath;
		private $env_logName;
		private $env_setInfoFlag;

		/* constructor */
		function __construct($_moduleName) {
			// 모듈명 지정안하면 사용할 수 없음
			if( is_null($_moduleName) ) {
				echo "input module name";

				return ;
			}

			$env_pathModule = "/opt/interm/public_html/modules/" . $_moduleName;

			if( !file_exists($env_pathModule) ) {
				$env_pathModule = "/opt/interm";
			}

			$this->env_logPath		= $env_pathModule . "/log";
			$this->env_logName		= $_moduleName .".log";
			$this->env_setInfoFlag	= false;

			if( ($logFp = $this->openFile()) == null ) return ;
			else fclose($logFp);
		}

		/* functions */
		function packInt32Be($_idx) {
			return pack('C4', ($_idx >> 24) & 0xFF, ($_idx >> 16) & 0xFF, ($_idx >>  8) & 0xFF, ($_idx >>  0) & 0xFF);
		}

		function packInt32Le($_idx) {
			return pack('C4', ($_idx >>  0) & 0xFF, ($_idx >>  8) & 0xFF, ($_idx >> 16) & 0xFF, ($_idx >> 24) & 0xFF);
		}

		function openFile() {
			// 파일 용량 정상 체크 (비정상 시 삭제)
			if( file_exists($this->env_logPath . "/" . $this->env_logName) ) {
				$fileSize  = filesize($this->env_logPath . "/" . $this->env_logName);
				$existSize = Common\Def\SIZE_LOG_BYTE * Common\Def\SIZE_LOG_LINE + 4;

				if( $existSize != $fileSize ) {
					unlink($this->env_logPath . "/" . $this->env_logName);
				}
			}

			// 파일 유무 체크 (유: 정상 진행 / 무: 파일 생성)
			if( !($logFp = fopen($this->env_logPath . "/" . $this->env_logName, "r+b")) ) {
				// 파일 생성 가능 체크 (가능: dummy 파일 생성 / 불가능 : false)
				if( !($logFp = fopen($this->env_logPath . "/" . $this->env_logName, "w+b")) ) {

					return null;

				} else {
					if( flock($logFp, LOCK_EX) ) {
						fseek($logFp, Common\Def\SIZE_LOG_BYTE * Common\Def\SIZE_LOG_LINE, SEEK_CUR);
						fwrite($logFp, $this->packInt32Le(0));

						flock($logFp, LOCK_UN);
					}
				}
			}

			chown($this->env_logPath . "/" . $this->env_logName, "interm");
			chgrp($this->env_logPath . "/" . $this->env_logName, "interm");

			return $logFp;
		}

		function clearLog() {
			unlink($this->env_logPath . "/" . $this->env_logName);

			if( ($logFp = $this->openFile()) == null ) return ;
			else fclose($logFp);

			return ;
		}

		function removeLog() {
			unlink($this->env_logPath . "/" . $this->env_logName);

			return ;
		}

		function writeLog($_logLevel, $_message) {
			// Step 1. 로그 메시지 세팅
			$maxLength = Common\Def\SIZE_LOG_BYTE - 23; // log format 길이(약 23byte)

			$message = $_logLevel . $_message;
			if( strlen($message) >  $maxLength ) {
				$message = substr($message, 0, $maxLength);
			}

			if( file_exists("/opt/interm/public_html/modules/time_setup/conf/time_stat.json") ) {
				$load_envData 	= file_get_contents("/opt/interm/public_html/modules/time_setup/conf/time_stat.json");
				$timeData 		= json_decode($load_envData);
				$timezone_info 	= $this->setTimeZoneInfo();

				date_default_timezone_set($timezone_info[$timeData->timezone_idx][self::TIMEZONE_PATH]);

				$date = getdate();
				$logFormat = "["
					. $date["year"]  . "/"
					. sprintf("%02d", $date["mon"])    . "/"
					. sprintf("%02d", $date["mday"])   . " "
					. sprintf("%02d", $date["hours"])  . ":"
					. sprintf("%02d", $date["minutes"]). ":"
					. sprintf("%02d", $date["seconds"])
					. "] " . $message . "\n";

			} else {
				$logFormat = "[" . date("Y/m/d H:i:s", time()) . "] " . $message . "\n";
			}


			// Step 2. File 유/무 확인(없을 시 생성) 및 저장
			if( ($logFp = $this->openFile()) == null ) return ;

			if( flock($logFp, LOCK_EX) ) {
				fseek($logFp, -4, SEEK_END);
				$logIndex = fread($logFp, 4);
				$header = unpack("iindex/", $logIndex);
				$curIndex = $header['index'];
				fseek($logFp, Common\Def\SIZE_LOG_BYTE * ($curIndex), SEEK_SET);

				fwrite($logFp, str_pad($logFormat, Common\Def\SIZE_LOG_BYTE, "\0", STR_PAD_RIGHT));

				$curIndex++;
				if( $curIndex == Common\Def\SIZE_LOG_LINE ) $curIndex = 0;

				fseek($logFp, -4, SEEK_END);
				fwrite($logFp, $this->packInt32Le($curIndex));

				flock($logFp, LOCK_UN);
			}

			fclose($logFp);

			return ;
		}

		// info level의 [INFO] level을 보기 위해선 stat을 true로 변경해야 함
		function setLogInfo($_stat) {
			$this->env_setInfoFlag = $_stat;

			return ;
		}

		function fatal($_message) { $this->writeLog(Common\Def\LOG_LEVEL_FATAL, $_message);	}
		function error($_message) { $this->writeLog(Common\Def\LOG_LEVEL_ERROR, $_message);	}
		function warn ($_message) { $this->writeLog(Common\Def\LOG_LEVEL_WARN,  $_message);	}
		function debug($_message) { $this->writeLog(Common\Def\LOG_LEVEL_DEBUG, $_message);	}

		// info level은 기본으로 log에 level을 출력하지 않음(false)
		function info ($_message) {
			if( $this->env_setInfoFlag == true ) {
				$this->writeLog(Common\Def\LOG_LEVEL_INFO, $_message);

			} else {
				$this->writeLog("", $_message);
			}
		}

		function setTimeZoneInfo() { // time_setup module 참고
			return json_decode(file_get_contents("/opt/interm/conf/config-timezone-info.json"));
		}
	} // end of CommonLogFunc()

	function execute($_cmd) {
		$rc = exec($_cmd . "; echo $?");

		if( $rc != 0 ) return false;
		return true;
	}

	function PostIntSync($_url, $_postData) {
		$curlsession = curl_init();
		curl_setopt($curlsession, CURLOPT_URL, $_url);
		curl_setopt($curlsession, CURLOPT_POST, 1);
		curl_setopt($curlsession, CURLOPT_POSTFIELDS, $_postData);
		curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlsession, CURLOPT_FRESH_CONNECT, true);

		$result = curl_exec($curlsession);

		curl_close($curlsession);

		return $result;
	}

	function get_log_message($_text) {
		$postData["type"] 			= "exec";
		$postData["moduleName"]		= "time_setup";
		$postData["text"]			= $_text;

		$text = PostIntSync("http://127.0.0.1/common/common_log.php", $postData);
		return $text;
	}

	// time_sync_daemon
	$timeLog = new CommonLogFunc("time_setup");

	const TIMEZONE_TYPE		= 0;
	const TIMEZONE_GMT		= 1;
	const TIMEZONE_NAME		= 2;
	const TIMEZONE_PATH		= 3;

	const TIME_SLEEP_PERIOD	= 1;	// sec

	$configPath 	= "/opt/interm/public_html/modules/time_setup/conf/time_stat.json";
	$timeData 	= json_decode(file_get_contents($configPath));
   	$timezoneInfo 	= $timeLog->setTimeZoneInfo();
	
	// sync time_zone list
	$selRegion = explode(',', preg_replace('/\s+/', '', $timeData->timezone));
		
	if((is_array($selRegion) == true) && ($selRegion[0] != "")) {
		$isFind = false;
		
		foreach($timezoneInfo as $tzIdx => $tzData) {
			$region = explode(',', preg_replace('/\s+/', '', $tzData[2]));
			$regCnt = count($region);
			
			for($idx = 0; $idx < $regCnt; $idx++) {
				if($region[$idx] == $selRegion[0]) {
					$isFind = true;
					
					if(($tzData[2] != $timeData->timezone) || ($tzIdx != $timeData->timezone_idx)) {
						$timeData->timezone 	= $tzData[2];
						$timeData->timezone_idx = $tzIdx;
						
						file_put_contents($configPath, json_encode($timeData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));	
					}
					
					break;
				}
			}
			
			if($isFind == true) {
				break;
			}
		}
	}
	
	if( $timeData->timeserver->enable == "on" ) {
		echo "Start SNTP sync \n";
		echo " - Sync to [{$timeData->timeserver->url}] \n";

		$str_exec = "";
		if( !execute("timedatectl set-ntp 0") ) {
			$str_exec = "error : timedatectl failed\n";
		}

		if( !execute("sntp -t 1 -s " . $timeData->timeserver->url) ) {
			$str_exec = "error : sntp faild [" . $timeData->timeserver->url . "]\n";

		} else {
			if( file_exists("/dev/rtc") ) {
				if( !execute("sudo hwclock -w") ) {
					$str_exec = "error : hwclock failed\n";
				}
			}
		}

		if( $str_exec == "" ) {
			echo "SNTP sync completed \n";

		} else {
			echo $str_exec;
			echo "SNTP sync failed \n";
		}
	}

	echo "Start time sync daemon\n";
	$is_auto = false;

	while( true ) {
		$timeData = json_decode(file_get_contents($configPath));

		if( $timeData->timeserver->enable != "on" ) {
			if( $is_auto == true ) {
				$is_auto = false;
				echo "disabled automatic time update\n";
			}

			sleep(TIME_SLEEP_PERIOD);
			continue;
		}
		if( $is_auto == false ) {
			echo "enabled automatic time update\n";
			$is_auto = true;
		}
		
		date_default_timezone_set($timezoneInfo[$timeData->timezone_idx][TIMEZONE_PATH]);

		$date = new DateTime();
		$currentTime	= $date->format('Y-m-d H:i:s');
		$syncTime		= $timeData->timeserver->nextSync;

		$cTime = new DateTime($currentTime);
		$sTime = new DateTime($syncTime);

		if( !($sTime <= $cTime) ) {
			sleep(TIME_SLEEP_PERIOD);
			continue;
		}

		// step. next sync time 갱신
		$dt = new DateTime();
		$dt->setTimezone(new DateTimeZone(date_default_timezone_get()));
		$dt->modify('+' . $timeData->timeserver->period . ' hour');

		$sync  = str_replace("-", ".",   $timeData->timeserver->nextSync);
		$sync  = str_replace(" ", " / ", $sync);

		$syncTime = $dt->format('Y-m-d H:i:s');
		$timeData->timeserver->nextSync = $syncTime;

		file_put_contents($configPath, json_encode($timeData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

		echo "Start SNTP sync \n";
		echo " - Sync to [{$timeData->timeserver->url}] \n";

		$str_exec = "";
		if( !execute("timedatectl set-ntp 0") ) {
			$str_exec = "error : timedatectl failed\n";
		}

		if( !execute("sntp -t 1 -s " . $timeData->timeserver->url) ) {
			$str_exec = "error : sntp faild [" . $timeData->timeserver->url . "]\n";

		} else {
			if( file_exists("/dev/rtc") ) {
				if( !execute("sudo hwclock -w") ) {
					$str_exec = "error : hwclock failed\n";
				}
			}
		}

		if( $str_exec == "" ) {
			$timeLog->info(get_log_message($sync . ", " . $timeData->timeserver->url . "{LOG_DATE_SYNC_TAIL}"));
			echo "SNTP sync completed \n";

		} else {
			$timeLog->info(get_log_message($timeData->timeserver->url . "{LOG_DATE_SYNC_FAIL}"));
			echo $str_exec;
			echo "SNTP sync failed \n";
		}

		sleep(TIME_SLEEP_PERIOD);
	}


?>
