<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_define.php";
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_script.php";

	include_once "common_define.php";
	include_once "common_script.php";

	$srcFileMngFunc = new Source_file_management\Func\SrcFileMngFunc();
	$sysFunc    = new Common\Func\CommonSystemFunc();
	//$sysLogFunc = new Common\Func\CommonLogFunc("source_file_management");
	
	function SendData($_data) {
		$serverIpAddr = gethostbyname('127.0.0.1');
		$data = $_data;

		if( !$_data || $_data == "" || $_data == null ) $data = "";
		$dataLength   = strlen($data);

		$msg_packet .= pack("C", 0xEF);
		$msg_packet .= pack("v", $dataLength);
		$msg_packet .= pack("C", 2);
		if( $dataLength > 0 ) {
			$msg_packet .= $data;
		} 

		if( !($socketFd = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) ) {
			return '{"status": "false", "message":"socket_create() failed"}';
		}

		if( !($result = socket_connect($socketFd, $serverIpAddr, Source_file_management\Def\NUM_SERVER_PORT)) ) {
			socket_close($socketFd);
			return '{"status": "error", "message":"socket_connect() failed"}';
		}

		$linger = array ('l_linger' => 0, 'l_onoff' => 1);
		socket_set_option($socketFd, SOL_SOCKET, SO_LINGER, $linger);

		if( !socket_write($socketFd, $msg_packet, strlen($msg_packet)) ) {
			socket_close($socketFd);
			return '{"status": "false", "message":"socket_write() failed"}';
		}
		if( !($recvMsg = socket_read($socketFd, 1024)) ) {
			socket_close($socketFd);
			return '{"status": "false", "message":"socket_read() failed"}';
		}
		socket_close($socketFd);

		$output = substr($recvMsg, 5, strlen($recvMsg) - 5);

		return '{"status": "success", "message":' . $output . '}';
	}
	
	
	$act = $_POST["act"];
	$playerData = $srcFileMngFunc->getEnvData();

	if ( "stop" == $act ) {
		// 정지
		$srcFileMngFunc->stopPlayer("audio_player");
		//echo '{"$act": "stop", "message":"stop!!!!"}';
		return ;
		
	}
	else if( "play" == $act ) {
		if( isset($_POST["type"]) && isset($_POST["storage"]) ) {
			$type = $_POST["type"];
			$storage = $_POST["storage"];
			$volume = $_POST["volume"];
			$repeat = 0;

			$playerPath = $_SERVER['DOCUMENT_ROOT'] . '/' . Source_file_management\Def\PATH_BIN . '/' . Source_file_management\Def\BIN_NAME_AUDIOPLAYER;
			
			$srcFileMngFunc->stopAudioClient();
			$srcFileMngFunc->stopPlayer("audio_player");
			
			
					
			if("dir" == $type ) {
				$srcFileMngFunc->writePlayDBSettings($act, $type,NULL, $repeat, $volume);
				$srcFileMngFunc->playPlayerForDir($playerPath, $storage);

			} else {
				if( isset($_POST["srcfile"]) && isset($_POST["repeatCount"]) ) {
					$srcfiles = $_POST["srcfile"];
					$repeat = $_POST["repeatCount"];
					
					if(null == $repeat) {
						$repeat = 0;
					}
					$srcFileMngFunc->writePlayDBSettings($act, $type, $_POST["srcfile"], $repeat, $volume);
					$srcFileMngFunc->playPlayerForFile($playerPath, $storage, $srcfiles, $repeat);
				}
			}
		}
	}
	else if( "delete" == $act ) {
		if( isset($_POST["type"]) && isset($_POST["storage"]) && isset($_POST["stopPlay"]) ) {
			$type = $_POST["type"];
			$storage = $_POST["storage"];
			$stopPlay = $_POST["stopPlay"];
			$volume = $_POST["volume"];	
			
			
			if("yes" == $stopPlay) {
				$srcFileMngFunc->stopPlayer("audio_player");
				$srcFileMngFunc->writePlayDBSettings("stop", NULL, NULL, 0, $volume);
			}
			
			if("dir" == $type ) {
				$srcFileMngFunc->deleteAllFile($storage);
			} else {
				if( isset($_POST["srcfile"]) ) {
					$delfiles = json_decode($_POST["srcfile"]);
					$srcFileMngFunc->deleteFile($storage, $delfiles);
				}
			}
		}
	}
	else if( "reload" == $act ) {		
		// reload List		
		if( isset($_POST["storage"]) ) {
			$storage = $_POST["storage"];
			
			$files =  $srcFileMngFunc->readFilesFromDir($storage);
			echo json_encode($files);
		}
	}
	else if("getAvailableMemSize" == $act) {
		//act : getAvailableMemSize
		if( isset($_POST["storage"]) ) {
			$storage = $_POST["storage"];
		
			echo $srcFileMngFunc->getDirMemSize($storage);
		}
	}
	else if("checkRunAudioPlayer" == $act) {
		echo $srcFileMngFunc->checkIsAlive("audio_player");
		
		return ;		
	}
	
	else if("setVolume" == $act) {
			$playerData->audio_player->volume = $_POST["volume"];

			$srcFileMngFunc->setVolumeData(json_encode($playerData));
			
			$envJson = '{
				    "PLUGIN": "plugin-audio-client",
				    "FUNCTION": "setVolumePlayer",
				    "ARGUMENTS": {
				        "volume": ' . $_POST["volume"] . '
				    	}
					}';

			echo SendData($envJson);

		return ;
	}
	else {
		//setPlaySettings
		if( isset($_POST["state"]) ) {
	
			$state = $_POST["state"];
			$volume = $_POST["volume"];	
			
			if("stop" == $state) {
				$srcFileMngFunc->writePlayDBSettings($state, NULL, NULL, 0, $volume);
			}
			else {
				if( isset($_POST["ftype"]) && isset($_POST["fname"]) && isset($_POST["replay"]) ) {
					$srcFileMngFunc->writePlayDBSettings($state, $_POST["ftype"], $_POST["fname"], $_POST["replay"], $_POST["volume"]);
				}	
			}
		}
	}
	
	include_once 'source_file_process_etc.php';
?>