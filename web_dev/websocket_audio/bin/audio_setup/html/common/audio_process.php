<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_define.php";
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_script.php";

	include_once "common_define.php";
	include_once "common_script.php";
	
	$load_envData  				= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/env.json");
	$envData   					= json_decode($load_envData);
	$env_langSet  	   			= $envData->info->language_set;
	
	include_once "../" . $envData->language_pack->$env_langSet->path;
	
	$audioLogFunc = new Common\Func\CommonLogFunc("audio_setup");
	$logMsg;
	
	function GetDevHostName() {
		$load_envData  		= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/modules/network_setup/conf/network_stat.json");
		$envData   			= json_decode($load_envData);
		
		return $envData->hostname;
	}

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

		if( !($result = socket_connect($socketFd, $serverIpAddr, Audio_setup\Def\NUM_SERVER_PORT)) ) {
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

	if( $_POST["type"] == "audio" && isset($_POST["act"]) ) {
		$serverFunc = new Audio_setup\Func\AudioFunc();
		$act = $_POST["act"];
		
		if( $act == "changeMode" ) {
			
			if($_POST["modeName"] == "NC-S01"){
				$audioData->tabStat = "server";
				$audioData->audioServer = "enabled";
				$audioData->audioClient = "disabled";
				
			} else if($_POST["modeName"] == "NC-S02"){
				$audioData->tabStat = "client";
				$audioData->audioServer = "disabled";
				$audioData->audioClient = "enabled";
				
			} else {
				$audioData->tabStat = "server";
				$audioData->audioServer = "enabled";
				$audioData->audioClient = "enabled";
				
			}
				$serverFunc->setChangeMode(json_encode($audioData));
				
				return ;
			}
		}

	if( $_POST["type"] == "audio_server" && isset($_POST["act"]) ) {
		$serverFunc = new Audio_setup\Func\AudioServerFunc();

		$act = $_POST["act"];
		$serverData = $serverFunc->getEnvData();

		if( $serverData->audio_server->use == "disabled" ) {
			echo '{"status": "false", "message":"audio_server does not work"}';
			return ;
		}

		if( $act == "set_tab" ) {
			$serverData->tabStat = $_POST["tab_stat"];

			$serverFunc->setEnvData(json_encode($serverData));

			echo $serverData->tabStat;
			return ;

		}
		else if( $act == "set_stat" ) {
				if( isset($_POST["protocol"]) 		) $serverData->audio_server->protocol			= $_POST["protocol"];
				if( isset($_POST["castType"]) 		) $serverData->audio_server->castType			= $_POST["castType"];
				if( isset($_POST["encodeType"])		) $serverData->audio_server->encode				= $_POST["encodeType"];
				if( isset($_POST["pcm_sampleRate"]) ) $serverData->audio_server->pcm->sampleRate	= $_POST["pcm_sampleRate"];
				if( isset($_POST["pcm_channels"]	) ) {
					if (Audio_setup\Def\NUM_CHANNELS == 2) 
						$serverData->audio_server->pcm->channels	= $_POST["pcm_channels"]; 
					else 
						$serverData->audio_server->pcm->channels = 1;
					}
				
				if( isset($_POST["mp3_sampleRate"]) ) $serverData->audio_server->mp3->sampleRate	= $_POST["mp3_sampleRate"];
				if( isset($_POST["mp3_bitRate"]) 	) $serverData->audio_server->mp3->bitRate		= $_POST["mp3_bitRate"];
				if( isset($_POST["operType"]) 		) $serverData->audio_server->operType			= $_POST["operType"];
				if( isset($_POST["operType"]		) && $_POST["operType"] == "change" ) {
					if( isset($_POST["ipAddr"]) 	) $serverData->audio_server->change->ipAddr		= $_POST["ipAddr"];
					if( isset($_POST["port"]) 		) $serverData->audio_server->change->port		= $_POST["port"];
				}
				if( isset($_POST["stat"]) 		) $serverData->audio_server->stat				= $_POST["stat"];

				$serverFunc->setEnvData(json_encode($serverData));

				if( $serverData->audio_server->operType == "change" ) {
					$port   = $serverData->audio_server->change->port;
					$ipAddr =$serverData->audio_server->change->ipAddr;
				} else {
					$port = $serverData->audio_server->default->port;
					$ipAddr =$serverData->audio_server->default->ipAddr;
				}

				// Send to plug-in
				$envJson = '{
				    "PLUGIN": "plugin-audio-server",
				    "FUNCTION": "setInitAudioServer",
				    "ARGUMENTS": {
				        "queueCnt": ' . $serverData->audio_server->deviceInfo->queueCnt . ',
				        "bufferRate": ' . $serverData->audio_server->deviceInfo->bufferRate . ',
				        "chunkSize": ' . $serverData->audio_server->pcm->chunkSize . ',
				        "sampleRate": ' . $serverData->audio_server->pcm->sampleRate . ',
				        "channels": ' . $serverData->audio_server->pcm->channels . ',
				        "mp3_mode": ' . ($serverData->audio_server->encode == "mp3" ? 1 : 0) . ',
				        "mp3_chunkSize": ' . $serverData->audio_server->mp3->chunkSize . ',
				        "mp3_bitRate": ' . $serverData->audio_server->mp3->bitRate . ',
				        "mp3_sampleRate": ' . $serverData->audio_server->mp3->sampleRate . ',
				        "typeProtocol": ' . ($serverData->audio_server->protocol == "tcp" ? 1 : 0) . ',
				        "castType": "' . $serverData->audio_server->castType  . '",
				        "port": ' . $port . ',
				        "clientCnt": ' . $serverData->audio_server->deviceInfo->clientCnt . ',
				        "ipAddr": "' . $ipAddr . '",
				        "typePlayMode": ' . ($serverData->audio_server->deviceInfo->playMode == "file" ? 1 : 0). ',
				        "filename": "' . $serverData->audio_server->deviceInfo->fileName . '",
				        "deviceName": "' . $serverData->audio_server->deviceInfo->deviceName . '"
				    }
				}';

				echo SendData($envJson);

				return ;
		}
		else if( $act == "get_stat" ) {
			echo '{"protocol":"' .		$serverData->audio_server->protocol
					. '", "castType":"' .	$serverData->audio_server->castType
					. '", "encode":"' . 	$serverData->audio_server->encode
					. '", "pcm_sampleRate":"' . $serverData->audio_server->pcm->sampleRate
					. '", "pcm_channels":"' . $serverData->audio_server->pcm->channels
					. '", "mp3_sampleRate":"' . $serverData->audio_server->mp3->sampleRate
					. '", "mp3_bitRate":"' . $serverData->audio_server->mp3->bitRate
					. '", "operType":"' . $serverData->audio_server->operType
					. '", "default_ipAddr":"' . $serverData->audio_server->default->ipAddr
					. '", "default_port":"' . $serverData->audio_server->default->port
					. '", "change_ipAddr":"' . $serverData->audio_server->change->ipAddr
					. '", "change_port":"' . $serverData->audio_server->change->port
					. '"}';

			return ;
		}
		else if( $act == "init" ) {
				if( isset($_POST["queueCnt"]) )			$serverData->audio_server->deviceInfo->queueCnt		= $_POST["queueCnt"];
				if( isset($_POST["bufferRate"]) )		$serverData->audio_server->deviceInfo->bufferRate	= $_POST["bufferRate"];
				if( isset($_POST["chunkSize"]) )		$serverData->audio_server->pcm->chunkSize			= $_POST["chunkSize"];
				if( isset($_POST["sampleRate"]) )		$serverData->audio_server->pcm->sampleRate			= $_POST["sampleRate"];
				if( isset($_POST["channels"]) )			
				if( isset($_POST["pcm_channels"]	) ) {
					if (Audio_setup\Def\NUM_CHANNELS == 2) 
						$serverData->audio_server->pcm->channels	= $_POST["pcm_channels"]; 
					else 
						$serverData->audio_server->pcm->channels = 1;
					}
					
				if( isset($_POST["mp3_mode"]) )			$serverData->audio_server->encode 					= ($_POST["mp3_mode"] == 1 ? "mp3" : "pcm");
				if( isset($_POST["mp3_chunkSize"]) )	$serverData->audio_server->mp3->chunkSize			= $_POST["mp3_chunkSize"];
				if( isset($_POST["mp3_bitRate"]) )		$serverData->audio_server->mp3->bitRate				= $_POST["mp3_bitRate"];
				if( isset($_POST["mp3_sampleRate"]) )	$serverData->audio_server->mp3->sampleRate			= $_POST["mp3_sampleRate"];
				if( isset($_POST["typeProtocol"]) ) 	$serverData->audio_server->protocol 				= ($_POST["typeProtocol"] == 1 ? "tcp" : "udp");
				if( isset($_POST["port"]) )				$serverData->audio_server->change->port				= $_POST["port"];
				if( isset($_POST["clientCnt"]) )		$serverData->audio_server->deviceInfo->clientCnt	= $_POST["clientCnt"];
				if( isset($_POST["ipAddr"]) )			$serverData->audio_server->change->ipAddr			= $_POST["ipAddr"];
				if( isset($_POST["typePlayMode"]) )		$serverData->audio_server->deviceInfo->playMode		= ($_POST["typePlayMode"] == 1 ? "file" : "pcm");
				if( isset($_POST["filename"]) )			$serverData->audio_server->deviceInfo->fileName		= $_POST["filename"];
				if( isset($_POST["deviceName"]) )		$serverData->audio_server->deviceInfo->deviceName	= $_POST["deviceName"];

				if( isset($_POST["operType"]) 		) 	$serverData->audio_server->operType					= $_POST["operType"];
				if( isset($_POST["stat"]) 		) 		$serverData->audio_server->stat						= $_POST["stat"];

				if( isset($_POST["castType"]) 				) $serverData->audio_server->castType			= $_POST["castType"];

				$serverFunc->setEnvData(json_encode($serverData));

				if( $serverData->audio_server->operType == "change" ) {
					$port   = $serverData->audio_server->change->port;
					$ipAddr =$serverData->audio_server->change->ipAddr;
				} else {
					$port = $serverData->audio_server->default->port;
					$ipAddr =$serverData->audio_server->default->ipAddr;
				}

				$envJson = '{
				    "PLUGIN": "plugin-audio-server",
				    "FUNCTION": "setInitAudioServer",
				    "ARGUMENTS": {
				        "queueCnt": ' . $serverData->audio_server->deviceInfo->queueCnt . ',
				        "bufferRate": ' . $serverData->audio_server->deviceInfo->bufferRate . ',
				        "chunkSize": ' . $serverData->audio_server->pcm->chunkSize . ',
				        "sampleRate": ' . $serverData->audio_server->pcm->sampleRate . ',
				        "channels": ' . $serverData->audio_server->pcm->channels . ',
				        "mp3_mode": ' . ($serverData->audio_server->encode == "mp3" ? 1 : 0) . ',
				        "mp3_chunkSize": ' . $serverData->audio_server->mp3->chunkSize . ',
				        "mp3_bitRate": ' . $serverData->audio_server->mp3->bitRate . ',
				        "mp3_sampleRate": ' . $serverData->audio_server->mp3->sampleRate . ',
				        "typeProtocol": ' . ($serverData->audio_server->protocol == "tcp" ? 1 : 0) . ',
				        "castType": "' . $serverData->audio_server->castType  . '",
				        "port": ' . $port . ',
				        "clientCnt": ' . $serverData->audio_server->deviceInfo->clientCnt . ',
				        "ipAddr": "' . $ipAddr . '",
				        "typePlayMode": ' . ($serverData->audio_server->deviceInfo->playMode == "file" ? 1 : 0). ',
				        "filename": "' . $serverData->audio_server->deviceInfo->fileName . '",
				        "deviceName": "' . $serverData->audio_server->deviceInfo->deviceName . '"
				    }
				}';

				echo SendData($envJson);

				return ;
		}
		else if( $act == "run" ) {
			if( isset($_POST["stat"]) ) {
				$serverData->audio_server->stat	= $_POST["stat"];
			}
			$serverData->audio_server->actStat = "run";

			$serverFunc->setEnvData(json_encode($serverData));

			$envJson = '{
				    "PLUGIN": "plugin-audio-server",
				    "FUNCTION": "setRunAudioServer"
				}';

			echo SendData($envJson);
			
			$logMsg = Audio_setup\Lang\STR_JS_START_AUDIO_SERVER;
			$audioLogFunc->info($logMsg);
			
			return ;
		}

		else if( $act == "stop" ) {
			if( isset($_POST["stat"]) ) {
				$serverData->audio_server->stat	= $_POST["stat"];
			}
			$serverData->audio_server->actStat = "stop";

			$serverFunc->setEnvData(json_encode($serverData));

			$envJson = '{
				    "PLUGIN": "plugin-audio-server",
				    "FUNCTION": "setStopAudioServer"
			}';

			echo SendData($envJson);
			
			$logMsg = Audio_setup\Lang\STR_SERVER_OP_STOP;
			$audioLogFunc->info($logMsg);
			
			return ;

		}
		else if( $act == "initrun" ) {
			$serverFunc->getEnvData();
			
			if( ($serverData->audio_server->encode 						!= ($_POST["mp3_mode"] == 1 ? "mp3" : "pcm")) ||
					($serverData->audio_server->change->port			!= $_POST["port"]) ||
					($serverData->audio_server->change->ipAddr			!= $_POST["ipAddr"]) ||
					($serverData->audio_server->actStat != "run")) {

				if( isset($_POST["queueCnt"]) )			$serverData->audio_server->deviceInfo->queueCnt		= $_POST["queueCnt"];
				if( isset($_POST["bufferRate"]) )		$serverData->audio_server->deviceInfo->bufferRate	= $_POST["bufferRate"];
				if( isset($_POST["chunkSize"]) )		$serverData->audio_server->pcm->chunkSize			= $_POST["chunkSize"];
				if( isset($_POST["sampleRate"]) )		$serverData->audio_server->pcm->sampleRate			= $_POST["sampleRate"];
				if( isset($_POST["channels"]) ) {
					if (Audio_setup\Def\NUM_CHANNELS == 2) 
						$serverData->audio_server->pcm->channels	= $_POST["pcm_channels"]; 
					else 
						$serverData->audio_server->pcm->channels = 1;
					}
				
				if( isset($_POST["mp3_mode"]) )			$serverData->audio_server->encode 					= ($_POST["mp3_mode"] == 1 ? "mp3" : "pcm");
				if( isset($_POST["mp3_chunkSize"]) )	$serverData->audio_server->mp3->chunkSize			= $_POST["mp3_chunkSize"];
				if( isset($_POST["mp3_bitRate"]) )		$serverData->audio_server->mp3->bitRate				= $_POST["mp3_bitRate"];
				if( isset($_POST["mp3_sampleRate"]) )	$serverData->audio_server->mp3->sampleRate			= $_POST["mp3_sampleRate"];
				if( isset($_POST["typeProtocol"]) ) 	$serverData->audio_server->protocol 				= ($_POST["typeProtocol"] == 1 ? "tcp" : "udp");
				if( isset($_POST["port"]) )				$serverData->audio_server->change->port				= $_POST["port"];
				if( isset($_POST["clientCnt"]) )		$serverData->audio_server->deviceInfo->clientCnt	= $_POST["clientCnt"];
				if( isset($_POST["ipAddr"]) )			$serverData->audio_server->change->ipAddr			= $_POST["ipAddr"];
				if( isset($_POST["typePlayMode"]) )		$serverData->audio_server->deviceInfo->playMode		= ($_POST["typePlayMode"] == 1 ? "file" : "pcm");
				if( isset($_POST["filename"]) )			$serverData->audio_server->deviceInfo->fileName		= $_POST["filename"];
				if( isset($_POST["deviceName"]) )		$serverData->audio_server->deviceInfo->deviceName	= $_POST["deviceName"];

				if( isset($_POST["operType"]) 		) 	$serverData->audio_server->operType					= $_POST["operType"];
				if( isset($_POST["stat"]) 		) 		$serverData->audio_server->stat						= $_POST["stat"];
				if( isset($_POST["castType"]) 	) 		$serverData->audio_server->castType			= $_POST["castType"];

				$serverData->audio_server->actStat = "run"; 

				$serverFunc->setEnvData(json_encode($serverData));

				if( $serverData->audio_server->operType == "change" ) {
					$port   = $serverData->audio_server->change->port;
					$ipAddr =$serverData->audio_server->change->ipAddr;
				} else {
					$port = $serverData->audio_server->default->port;
					$ipAddr =$serverData->audio_server->default->ipAddr;
				}

				$envJson = '{
					"PLUGIN": "plugin-audio-server",
						"FUNCTION": "setInitRunAudioServer",
						"ARGUMENTS": {
							"queueCnt": ' . $serverData->audio_server->deviceInfo->queueCnt . ',
							"bufferRate": ' . $serverData->audio_server->deviceInfo->bufferRate . ',
							"chunkSize": ' . $serverData->audio_server->pcm->chunkSize . ',
							"sampleRate": ' . $serverData->audio_server->pcm->sampleRate . ',
							"channels": ' . $serverData->audio_server->pcm->channels . ',
							"mp3_mode": ' . ($serverData->audio_server->encode == "mp3" ? 1 : 0) . ',
							"mp3_chunkSize": ' . $serverData->audio_server->mp3->chunkSize . ',
							"mp3_bitRate": ' . $serverData->audio_server->mp3->bitRate . ',
							"mp3_sampleRate": ' . $serverData->audio_server->mp3->sampleRate . ',
							"typeProtocol": ' . ($serverData->audio_server->protocol == "tcp" ? 1 : 0) . ',
							"castType": "' . $serverData->audio_server->castType  . '",
							"port": ' . $port . ',
							"clientCnt": ' . $serverData->audio_server->deviceInfo->clientCnt . ',
							"ipAddr": "' . $ipAddr . '",
							"typePlayMode": ' . ($serverData->audio_server->deviceInfo->playMode == "file" ? 1 : 0). ',
							"filename": "' . $serverData->audio_server->deviceInfo->fileName . '",
							"deviceName": "' . $serverData->audio_server->deviceInfo->deviceName . '"
						}
				}';

				echo SendData($envJson);
			}
			$logMsg = Audio_setup\Lang\STR_JS_START_AUDIO_SERVER;
			$audioLogFunc->info($logMsg);

			return ;
		}
		else if(   $act == "getAliveStatus"
				|| $act == "getServerInfo"
				|| $act == "getClientList" ) {
			$envJson = '{
				    "PLUGIN": "plugin-audio-server",
				    "FUNCTION": "' . $act . '"
				}';

			echo SendData($envJson);

			return ;
		}
		else if ( $act == "setStackIdx" ) {
			$threadIdx = 0;
			if( isset($_POST["threadIdx"]) ) {
				$threadIdx = $_POST["threadIdx"];
			}

			$queueIdx = 0;
			if( isset($_POST["queueIdx"]) ) {
				$queueIdx = $_POST["queueIdx"];
			}

			$envJson = '{
				    "PLUGIN": "plugin-audio-server",
				    "FUNCTION": "' . $act . '",
				    "ARGUMENTS": {
				        "threadIdx": ' . $threadIdx . ',
				        "queueIdx": ' . $queueIdx . '
				    }
				}';

			echo SendData($envJson);

			return ;
		}
		else if ( $act == "setPlayMode" ) {
			$index = 0;
			if( isset($_POST["index"]) ) {
				$index = $_POST["index"];
			}
			$fileName = "";
			if( isset($_POST["fileName"]) ) {
				$fileName = $_POST["fileName"];
			}

			$envJson = '{
				    "PLUGIN": "plugin-audio-server",
				    "FUNCTION": "' . $act . '",
				    "ARGUMENTS": {
				        "index": ' . $index . ',
				        "fileName": "' . $fileName . '"
				    }
				}';

			echo SendData($envJson);

			return ;
		}
	}

	else if( $_POST["type"] == "audio_client" && isset($_POST["act"]) ) {
		$clientFunc = new Audio_setup\Func\AudioClientFunc();

		$act = $_POST["act"];
		$clientData = $clientFunc->getEnvData();

		if( $clientData->audio_client->use == "disabled" ) {
			echo '{"status": "false", "message":"audio_client does not work"}';
			return ;
		}

		if( $act == "get_volume" ) {
			echo $clientData->audio_client->volume;

			return ;
		}
		else if( $act == "setVolume" ) {
			$clientData->audio_client->volume = $_POST["volume"];

			$clientFunc->setEnvData(json_encode($clientData));

			$envJson = '{
				    "PLUGIN": "plugin-audio-client",
				    "FUNCTION": "setVolume",
				    "ARGUMENTS": {
				        "volume": ' . $_POST["volume"] . '
				    	}
					}';

			echo SendData($envJson);
			
			$logMsg = Audio_setup\Lang\STR_CLIENT_INFO_VOLUME.' '. $_POST["volume"].Audio_setup\Lang\STR_CLIENT_INFO_VOLUME_COMPLETE;
			$audioLogFunc->info($logMsg);

			return ;
		}
		else if( $act == "get_stat" ) {
			echo '{"protocol":"' .		$clientData->audio_client->protocol
					. '", "operType":"' .	$clientData->audio_client->operType
					. '", "castType":"' .	$clientData->audio_client->castType
					. '", "buffer_sec":"' .	$clientData->audio_client->buffer->sec
					. '", "buffer_msec":"' .	$clientData->audio_client->buffer->msec
					. '", "volume":"' .	$clientData->audio_client->volume
					. '", "redundancy":"' .	$clientData->audio_client->redundancy
					. '", "default_unicast_ipAddr":"' .	$clientData->audio_client->default->unicast->ipAddr
					. '", "default_unicast_port":"' .	$clientData->audio_client->default->unicast->port
					. '", "default_unicast_rep_ipAddr":"' .	$clientData->audio_client->default->unicast_rep->ipAddr
					. '", "default_unicast_rep_port":"' .	$clientData->audio_client->default->unicast_rep->port
					. '", "default_multicast_ipAddr":"' .	$clientData->audio_client->default->multicast->ipAddr
					. '", "default_multicast_port":"' .	$clientData->audio_client->default->multicast->port
					. '", "change_unicast_ipAddr":"' .	$clientData->audio_client->change->unicast->ipAddr
					. '", "change_unicast_port":"' .	$clientData->audio_client->change->unicast->port
					. '", "change_unicast_rep_ipAddr":"' .	$clientData->audio_client->change->unicast_rep->ipAddr
					. '", "change_unicast_rep_port":"' .	$clientData->audio_client->change->unicast_rep->port
					. '", "change_multicast_ipAddr":"' .	$clientData->audio_client->change->multicast->ipAddr
					. '", "change_multicast_port":"' .	$clientData->audio_client->change->multicast->port
					. '"}';

			return ;
		}
		else if( $act == "set_stat" ) {
				if( isset($_POST["protocol"]) 				) $clientData->audio_client->protocol			= $_POST["protocol"];
				if( isset($_POST["castType"]) 				) $clientData->audio_client->castType			= $_POST["castType"];
				if( isset($_POST["buffer_sec"])				) $clientData->audio_client->buffer->sec		= $_POST["buffer_sec"];
				if( isset($_POST["buffer_msec"]) 			) $clientData->audio_client->buffer->msec		= $_POST["buffer_msec"];
				if( isset($_POST["redundancy"])				) $clientData->audio_client->redundancy			= $_POST["redundancy"];
				if( isset($_POST["operType"]) 				) $clientData->audio_client->operType			= $_POST["operType"];

				if( isset($_POST["operType"]) && $_POST["operType"] == "change" ) {
					if( $_POST["castType"] == "unicast" ) {
						if( isset($_POST["client_ipAddr_master"])	) $clientData->audio_client->change->unicast->ipAddr		= $_POST["client_ipAddr_master"];
						if( isset($_POST["client_port_master"]) 	) $clientData->audio_client->change->unicast->port			= $_POST["client_port_master"];
						if( isset($_POST["client_ipAddr_slave"]) 	) $clientData->audio_client->change->unicast_rep->ipAddr	= $_POST["client_ipAddr_slave"];
						if( isset($_POST["client_port_slave"]) 		) $clientData->audio_client->change->unicast_rep->port		= $_POST["client_port_slave"];

					} else {
						if( isset($_POST["client_ipAddr_master"])	) $clientData->audio_client->change->multicast->ipAddr		= $_POST["client_ipAddr_master"];
						if( isset($_POST["client_port_master"]) 	) $clientData->audio_client->change->multicast->port			= $_POST["client_port_master"];
					}
				}
				if( isset($_POST["playVolume"]) ) $clientData->audio_client->volume = $_POST["volume"];
				if( isset($_POST["stat"]) 		) $clientData->audio_client->stat	= $_POST["stat"];
				
				$clientFunc->setEnvData(json_encode($clientData));

				// Send to plug-in
				$envJson = '{
				    "PLUGIN": "plugin-audio-client",
				    "FUNCTION": "setInitAudioClient",
				    "ARGUMENTS": {
				        "delay": ' .  $clientData->audio_client->buffer->sec . ',
				        "delayMs": ' . $clientData->audio_client->buffer->msec . ',
				        "typeProtocol": ' . ($clientData->audio_client->protocol == "tcp" ? 1 : 0) . ',
				        "serverCnt": ' . ($clientData->audio_client->redundancy == "master" ? 1 : 2)	 . ',
				        "port1": ' . $clientData->audio_client->change->unicast->port  . ',
				        "port2": ' . $clientData->audio_client->change->unicast_rep->port . ',
				        "mPort": ' . $clientData->audio_client->change->multicast->port . ',
				        "playVolume": ' . $clientData->audio_client->volume . ',
				        "castType": "' . $clientData->audio_client->castType  . '",
				        "ipAddr1": "' . $clientData->audio_client->change->unicast->ipAddr . '",
				        "ipAddr2": "' . $clientData->audio_client->change->unicast_rep->ipAddr . '",
				        "mIpAddr": "' . $clientData->audio_client->change->multicast->ipAddr . '",
				        "hostname": "hostName:' . GetDevHostName() . '"
				    }
				}';

				echo SendData($envJson);

				return ;
		}
		else if( $act == "init" ) {
				if( isset($_POST["protocol"]) 				) $clientData->audio_client->protocol			= $_POST["protocol"];
				if( isset($_POST["castType"]) 				) $clientData->audio_client->castType			= $_POST["castType"];
				if( isset($_POST["buffer_sec"])				) $clientData->audio_client->buffer->sec		= $_POST["buffer_sec"];
				if( isset($_POST["buffer_msec"]) 			) $clientData->audio_client->buffer->msec		= $_POST["buffer_msec"];
				if( isset($_POST["redundancy"])				) $clientData->audio_client->redundancy			= $_POST["redundancy"];
				if( isset($_POST["operType"]) 				) $clientData->audio_client->operType			= $_POST["operType"];

				if( isset($_POST["operType"]) && $_POST["operType"] == "change" ) {
					if( $_POST["castType"] == "unicast" ) {
						if( isset($_POST["client_ipAddr_master"])	) $clientData->audio_client->change->unicast->ipAddr		= $_POST["client_ipAddr_master"];
						if( isset($_POST["client_port_master"]) 	) $clientData->audio_client->change->unicast->port			= $_POST["client_port_master"];
						if( isset($_POST["client_ipAddr_slave"]) 	) $clientData->audio_client->change->unicast_rep->ipAddr	= $_POST["client_ipAddr_slave"];
						if( isset($_POST["client_port_slave"]) 		) $clientData->audio_client->change->unicast_rep->port		= $_POST["client_port_slave"];

					} else {
						if( isset($_POST["client_ipAddr_master"])	) $clientData->audio_client->change->multicast->ipAddr		= $_POST["client_ipAddr_master"];
						if( isset($_POST["client_port_master"]) 	) $clientData->audio_client->change->multicast->port			= $_POST["client_port_master"];
					}
				}
				if( isset($_POST["playVolume"]) ) $clientData->audio_client->volume = $_POST["volume"];
				if( isset($_POST["stat"]) 		) $clientData->audio_client->stat	= $_POST["stat"];

				$clientFunc->setEnvData(json_encode($clientData));

				// Send to plug-in
				$envJson = '{
				    "PLUGIN": "plugin-audio-client",
				    "FUNCTION": "setInitAudioClient",
				    "ARGUMENTS": {
				        "delay": ' .  $clientData->audio_client->buffer->sec . ',
				        "delayMs": ' . $clientData->audio_client->buffer->msec . ',
				        "typeProtocol": ' . ($clientData->audio_client->protocol == "tcp" ? 1 : 0) . ',
				        "serverCnt": ' . ($clientData->audio_client->redundancy == "master" ? 1 : 2)	 . ',
				        "port1": ' . $clientData->audio_client->change->unicast->port  . ',
				        "port2": ' . $clientData->audio_client->change->unicast_rep->port . ',
				        "mPort": ' . $clientData->audio_client->change->multicast->port . ',
				        "playVolume": ' . $clientData->audio_client->volume . ',
				        "castType": "' . $clientData->audio_client->castType  . '",
				        "ipAddr1": "' . $clientData->audio_client->change->unicast->ipAddr . '",
				        "ipAddr2": "' . $clientData->audio_client->change->unicast_rep->ipAddr . '",
				        "mIpAddr": "' . $clientData->audio_client->change->multicast->ipAddr . '",
				        "hostname": "hostName:' . GetDevHostName()  . '"
				    }
				}';

				echo SendData($envJson);

				return ;
		}
		else if( $act == "run" ) {
				
			$execmd = "pkill " . audio_player;
			pclose(popen('echo "' . $execmd . '" > /tmp/web_fifo', "r"));
			
			if( isset($_POST["stat"]) ) {
				$clientData->audio_client->stat	= $_POST["stat"];
			}
			$clientData->audio_client->actStat = "run";

			$clientFunc->setEnvData(json_encode($clientData));

			$envJson = '{
				    "PLUGIN": "plugin-audio-client",
				    "FUNCTION": "setRunAudioClient"
				}';

			echo SendData($envJson);
			
			$logMsg = Audio_setup\Lang\STR_JS_START_AUDIO_CLIENT;
			$audioLogFunc->info($logMsg);

			return ;
		}
		else if( $act == "stop" ) {
			if( isset($_POST["stat"]) ) {
				$clientData->audio_client->stat	= $_POST["stat"];
			}
			$clientData->audio_client->actStat = "stop";

			$clientFunc->setEnvData(json_encode($clientData));
			
			if( $_POST["con"] == "yes" ) {
				$envJson = '{
					    "PLUGIN": "plugin-audio-client",
						"FUNCTION": "setStopAudioClient"
				}';
				echo SendData($envJson);
			}
			
			$logMsg = Audio_setup\Lang\STR_CLIENT_OP_STOP;
			$audioLogFunc->info($logMsg);

			return ;
		}
		else if( $act == "initrun" ) {
			$execmd = "pkill " . audio_player;
			pclose(popen('echo "' . $execmd . '" > /tmp/web_fifo', "r"));
			
			$clientFunc->getEnvData();

			if( ($clientData->audio_client->castType					!= $_POST["castType"]) ||
				($clientData->audio_client->change->unicast->ipAddr		!= $_POST["client_ipAddr_master"]) ||
				($clientData->audio_client->change->unicast->port		!= $_POST["client_port_master"]) ||
				($clientData->audio_client->actStat != "run")
			  ) {
				if( isset($_POST["protocol"]) 				) $clientData->audio_client->protocol			= $_POST["protocol"];
				if( isset($_POST["castType"]) 				) $clientData->audio_client->castType			= $_POST["castType"];
				if( isset($_POST["buffer_sec"])				) $clientData->audio_client->buffer->sec		= $_POST["buffer_sec"];
				if( isset($_POST["buffer_msec"]) 			) $clientData->audio_client->buffer->msec		= $_POST["buffer_msec"];
				if( isset($_POST["redundancy"])				) $clientData->audio_client->redundancy			= $_POST["redundancy"];
				if( isset($_POST["operType"]) 				) $clientData->audio_client->operType			= $_POST["operType"];

				if( isset($_POST["operType"]) && $_POST["operType"] == "change" ) {
					if( $_POST["castType"] == "unicast" ) {
						if( isset($_POST["client_ipAddr_master"])	) $clientData->audio_client->change->unicast->ipAddr		= $_POST["client_ipAddr_master"];
						if( isset($_POST["client_port_master"]) 	) $clientData->audio_client->change->unicast->port			= $_POST["client_port_master"];
						if( isset($_POST["client_ipAddr_slave"]) 	) $clientData->audio_client->change->unicast_rep->ipAddr	= $_POST["client_ipAddr_slave"];
						if( isset($_POST["client_port_slave"]) 		) $clientData->audio_client->change->unicast_rep->port		= $_POST["client_port_slave"];

					} else {
						if( isset($_POST["client_ipAddr_master"])	) $clientData->audio_client->change->multicast->ipAddr		= $_POST["client_ipAddr_master"];
						if( isset($_POST["client_port_master"]) 	) $clientData->audio_client->change->multicast->port		= $_POST["client_port_master"];
					}
				}
				if( isset($_POST["playVolume"]) ) $clientData->audio_client->volume = $_POST["volume"];
				if( isset($_POST["stat"]) 		) $clientData->audio_client->stat	= $_POST["stat"];
				$clientData->audio_client->actStat = "run";

				$clientFunc->setEnvData(json_encode($clientData));

				// Send to plug-in
				$envJson = '{
					"PLUGIN": "plugin-audio-client",
						"FUNCTION": "setInitRunAudioClient",
						"ARGUMENTS": {
							"delay": ' .  $clientData->audio_client->buffer->sec . ',
							"delayMs": ' . $clientData->audio_client->buffer->msec . ',
							"typeProtocol": ' . ($clientData->audio_client->protocol == "tcp" ? 1 : 0) . ',
							"serverCnt": ' . ($clientData->audio_client->redundancy == "master" ? 1 : 2)	 . ',
							"port1": ' . $clientData->audio_client->change->unicast->port  . ',
							"port2": ' . $clientData->audio_client->change->unicast_rep->port . ',
							"mPort": ' . $clientData->audio_client->change->multicast->port . ',
							"playVolume": ' . $clientData->audio_client->volume . ',
							"castType": "' . $clientData->audio_client->castType  . '",
							"ipAddr1": "' . $clientData->audio_client->change->unicast->ipAddr . '",
							"ipAddr2": "' . $clientData->audio_client->change->unicast_rep->ipAddr . '",
							"mIpAddr": "' . $clientData->audio_client->change->multicast->ipAddr . '",
							"hostname": "hostName:' . GetDevHostName()  . '"
						}
				}';

				echo SendData($envJson);
			}
			$logMsg = Audio_setup\Lang\STR_JS_START_AUDIO_CLIENT;
			$audioLogFunc->info($logMsg);

			return ;
		}
		else if( $act == "dbupdate" ) {
				if( isset($_POST["protocol"]) 				) $clientData->audio_client->protocol			= $_POST["protocol"];
				if( isset($_POST["castType"]) 				) $clientData->audio_client->castType			= $_POST["castType"];
				if( isset($_POST["buffer_sec"])				) $clientData->audio_client->buffer->sec		= $_POST["buffer_sec"];
				if( isset($_POST["buffer_msec"]) 			) $clientData->audio_client->buffer->msec		= $_POST["buffer_msec"];
				if( isset($_POST["redundancy"])				) $clientData->audio_client->redundancy			= $_POST["redundancy"];
				if( isset($_POST["operType"]) 				) $clientData->audio_client->operType			= $_POST["operType"];

				if( isset($_POST["operType"]) && $_POST["operType"] == "change" ) {
					if( $_POST["castType"] == "unicast" ) {
						if( isset($_POST["client_ipAddr_master"])	) $clientData->audio_client->change->unicast->ipAddr		= $_POST["client_ipAddr_master"];
						if( isset($_POST["client_port_master"]) 	) $clientData->audio_client->change->unicast->port			= $_POST["client_port_master"];
						if( isset($_POST["client_ipAddr_slave"]) 	) $clientData->audio_client->change->unicast_rep->ipAddr	= $_POST["client_ipAddr_slave"];
						if( isset($_POST["client_port_slave"]) 		) $clientData->audio_client->change->unicast_rep->port		= $_POST["client_port_slave"];

					} else {
						if( isset($_POST["client_ipAddr_master"])	) $clientData->audio_client->change->multicast->ipAddr		= $_POST["client_ipAddr_master"];
						if( isset($_POST["client_port_master"]) 	) $clientData->audio_client->change->multicast->port			= $_POST["client_port_master"];
					}
				}
				if( isset($_POST["playVolume"]) ) $clientData->audio_client->volume = $_POST["volume"];
				if( isset($_POST["stat"]) 		) $clientData->audio_client->stat	= $_POST["stat"];
				$clientData->audio_client->actStat = "run";

				$clientFunc->setEnvData(json_encode($clientData));

				return ;
		}
		else if(   $act == "getAliveStatus"
				|| $act == "getClientInfo"
				|| $act == "getVolume" ) {
			$envJson = '{
				    "PLUGIN": "plugin-audio-client",
				    "FUNCTION": "' . $act . '"
				}';

			echo SendData($envJson);

			return ;
		}
	}
?>
