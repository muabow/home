<?php
	include "/opt/interm/public_html/common/common_define.php";

	const DEBUG_PRINT_MSG		= false;

	const NUM_INTERFACE_PORT	= 25004;
	const NUM_MSGQUEUE_KEY		= 25004;

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
				if( !($logFp = @fopen($this->env_logPath . "/" . $this->env_logName, "r+b")) ) {
					// 파일 생성 가능 체크 (가능: dummy 파일 생성 / 불가능 : false)
					if( !($logFp = @fopen($this->env_logPath . "/" . $this->env_logName, "w+b")) ) {

						return null;

					} else {
						if( flock($logFp, LOCK_EX) ) {
							fseek($logFp, Common\Def\SIZE_LOG_BYTE * Common\Def\SIZE_LOG_LINE, SEEK_CUR);
							fwrite($logFp, $this->packInt32Le(0));

							flock($logFp, LOCK_UN);
						}
					}
				}

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

	cli_set_process_title("log_write_interface");

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

	$host = "127.0.0.1";
	$port = NUM_INTERFACE_PORT;

	set_time_limit(0);

	// message queue clear
	msg_remove_queue(msg_get_queue(NUM_MSGQUEUE_KEY));

	$pid = pcntl_fork();
	if( $pid == -1 ) {
		die('could not fork');

	} else if( $pid ) {
		$msgKey  = msg_get_queue(NUM_MSGQUEUE_KEY);
		$msgType = NUM_MSGQUEUE_KEY;

		$ServSockFd = socket_create(AF_INET, SOCK_DGRAM, 0) or die("Could not create socket\n");

		socket_set_option($ServSockFd, SOL_SOCKET, SO_REUSEADDR, 1);
		socket_bind($ServSockFd, $host, $port) or die("Could not bind to socket\n");

		while( true ) {
			$read = socket_recvfrom($ServSockFd, $recvData, 4096, 0, $host, $port) or die("Could not read input\n");

			$moduleName = trim(substr($recvData, 0,  32));
			$logLevel 	= trim(substr($recvData, 32, 6));
			$text 		= trim(substr($recvData, 38));

			if( DEBUG_PRINT_MSG ) {
				printf("log_write_interface : [%s->%s] %s\n", $moduleName, $logLevel, $text);
			}

			// delimeter "&|"
			$sendData = $moduleName ."&|" . $logLevel . "&|" .  $text;
			msg_send($msgKey, $msgType, $sendData);
		}

		socket_close($ServSockFd);

		pcntl_wait($status);

	} else {
		cli_set_process_title("log_write_process");

		$want  = 0;
		$size  = 4096;
		$unser = true;
		$flags = 0;
		$error = null;
		$type  = NUM_MSGQUEUE_KEY;

		$msgKey = msg_get_queue(NUM_MSGQUEUE_KEY);

		while( true ) {
			msg_receive($msgKey, $want, $type, $size, $recvData, $unser, $flags, $error);

			$arrRecvData = explode("&|", $recvData);
			$moduleName = $arrRecvData[0];
			$logLevel 	= $arrRecvData[1];
			$text 		= $arrRecvData[2];

			$postData["type"] 			= "exec";
			$postData["moduleName"]		= $moduleName;
			$postData["text"]			= $text;

			while( true ) {
				$text = PostIntSync("http://{$host}/common/common_log.php", $postData);
				if( strpos($text, "<html><body>") !== false ) {
					if( DEBUG_PRINT_MSG ) {
						printf("waiting web service.. [%s]\n", $text);
					}
					sleep(1);
					continue;

				} else if( strpos($text, "<html><head>") !== false ) {
					if( DEBUG_PRINT_MSG ) {
						printf("waiting web service.. [%s]\n", $text);
					}
					sleep(1);
					continue;

				} else if( strpos($text, "<!DOCTYPE") !== false ) {
					if( DEBUG_PRINT_MSG ) {
						printf("waiting web service.. [%s]\n", $text);
					}
					sleep(1);
					continue;

				} else {
					break;
				}
			}

			if( DEBUG_PRINT_MSG ) {
				printf("log_write_process : [%s->%s] %s\n", $moduleName, $logLevel, $text);
			}

			switch( $logLevel ) {
				case "fatal"  :
				case "error"  :
				case "warn"   :
				case "debug"  :
				case "info"   :
				case "pinfo"  :
					$logger = new CommonLogFunc($moduleName);

					if( $logLevel == "pinfo") {
						$logger->setLogInfo(true);
						$logLevel = "info";
					}

					$logger->{$logLevel}($text);
					unset($logger);

					break;

				case "clear"  :
				case "remove" :
					$logger = new CommonLogFunc($moduleName);
					$logger->{$logLevel . "Log"}();

					break;

				default:
					printf("unkown log type : [%s]\n", $logLevel);
					break;
			}
		}
	}
?>
