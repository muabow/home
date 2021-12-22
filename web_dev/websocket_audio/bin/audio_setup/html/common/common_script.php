<?php
	// PHP 함수 등을 작성합니다.
	namespace Audio_setup\Func {
		use Audio_setup;
		use SQLite3;

		const ERR_DB_LOCK		= 5;
		const TIME_LOCK_PERIOD	= 20000;	// msec

		function selectDatabase($_filePath, $_tableName) {
			if( !($db = new SQLite3($_filePath)) ) {
				shell_exec("echo " . $db->lastErrorMsg() . " > /tmp/sqllite3_error");
			}

			while( true ) {
				if( !($query = $db->query("select * from " . $_tableName))  ) {
					shell_exec("echo " . $db->lastErrorMsg() . " > /tmp/sqllite3_error");
					if( $db->lastErrorCode() == ERR_DB_LOCK ) { // db lock
						usleep(TIME_LOCK_PERIOD);
						continue;
					}
				}
				break;
			}

			$arrRow = array();
			while( ($row = $query->fetchArray(SQLITE3_ASSOC)) ) {
				$arrRow[] = $row;
			}
			$db->close();

			return $arrRow[0];
		}

		function updateDatabase($_filePath, $_tableName, $_setData) {
			if( !($db = new SQLite3($_filePath)) ) {
				shell_exec("echo " . $db->lastErrorMsg() . " > /tmp/sqllite3_error");
			}

			while( true ) {
				if( !($query = $db->query("update " . $_tableName . " set " . $_setData))  ) {
					shell_exec("echo " . $db->lastErrorMsg() . " > /tmp/sqllite3_error");
					if( $db->lastErrorCode() == ERR_DB_LOCK ) { // db lock
						usleep(TIME_LOCK_PERIOD);
						continue;
					}
				}
				break;
			}

			$db->close();

			return $query;
		}
		
		class AudioFunc {
			
			function setChangeMode($_statData) { // for ajax
				$statData = json_decode($_statData);
				
				$setList  = "";
				$setList .= "tabStat = '{$statData->tabStat}'";

				updateDatabase("../../conf/audio_stat.db", "audio_info", $setList);

				$setList  = "";
				$setList  .= "use           = '{$statData->audioServer}' ";

				updateDatabase("../../conf/audio_stat.db", "audio_server", $setList);
				$setList  = "";
				$setList  .= "use           = '{$statData->audioClient}' ";
				
				updateDatabase("../../conf/audio_stat.db", "audio_client", $setList);

				return ;

			}
		}

		class AudioServerFunc {
			private $envData;

			function __construct() {
				$this->envData = $this->loadEnvData($_SERVER['DOCUMENT_ROOT'] . "/" . "modules/audio_setup/conf/audio_stat.db");

				return ;
			}

			function loadEnvData($_filePath) {
				$db_audioInfo   = Audio_setup\Func\selectDatabase($_filePath, "audio_info");
				$db_audioServer = Audio_setup\Func\selectDatabase($_filePath, "audio_server");

				$load_envData = array();
				$load_envData["tabStat"] = $db_audioInfo["tabStat"];
				$load_envData["audio_server"] = array(
														"use" 		 => $db_audioServer["use"],
														"stat" 		 => $db_audioServer["stat"],
														"actStat" 	 => $db_audioServer["actStat"],
														"protocol" 	 => $db_audioServer["protocol"],
														"castType" 	 => $db_audioServer["castType"],
														"encode" 	 => $db_audioServer["encode"],
														"pcm"	 	 => array(
																			"sampleRate" => $db_audioServer["pcm_sampleRate"],
																			"channels"   => $db_audioServer["pcm_channels"],
																			"chunkSize"  => $db_audioServer["pcm_chunkSize"]
																		),
														"mp3"	 	 => array(
																			"sampleRate" => $db_audioServer["mp3_sampleRate"],
																			"bitRate"    => $db_audioServer["mp3_bitRate"],
																			"chunkSize"  => $db_audioServer["mp3_chunkSize"]
																		),
														"deviceInfo" => array(
																			"deviceName" => $db_audioServer["deviceName"],
																			"queueCnt"   => $db_audioServer["queueCnt"],
																			"clientCnt"  => $db_audioServer["clientCnt"],
																			"bufferRate" => $db_audioServer["bufferRate"],
																			"playMode"   => $db_audioServer["playMode"],
																			"fileName"   => $db_audioServer["fileName"],
																		),
														"operType" 	 => $db_audioServer["operType"],
														"default"	 => array(
																			"ipAddr"     => $db_audioServer["default_ipAddr"],
																			"port"       => $db_audioServer["default_port"]
																		),
														"change"	 => array(
																			"ipAddr"     => $db_audioServer["change_ipAddr"],
																			"port"       => $db_audioServer["change_port"]
																		)
														);
				$encData = json_encode($load_envData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

				return json_decode($encData);
			}

			function getEnableTabStat($_type) {
				if( $this->envData->$_type->use == "disabled" ) {
					return false;
				}

				return true;
			}

			function getTabStat($_stat) {
				if( $this->envData->tabStat == $_stat ) {
					return "tabs_list_click";

				} else {
					return "";
				}
			}

			function setTabStat($_stat) {
				$setList = "tabStat = " . $_stat;

				updateDatabase($_SERVER['DOCUMENT_ROOT'] . "/" . "modules/audio_setup/conf/audio_stat.db", "audio_info", $setList);

				return ;
			}

			function getTabContentStat($_stat) {
				if( $this->envData->tabStat == $_stat ) {
					return "";

				} else {
					return 'style="display: none;"';
				}
			}

			function getEnableStat($_key, $_stat) {
				if( $this->envData->audio_server->$_key == $_stat ) {
					return "checked";
				}

				return "";
			}

			function getSelectStat($_attr, $_value, $_stat) {
				if( $this->envData->audio_server->$_attr->$_value == $_stat ) {
					return "selected";
				}

				return "";
			}

			function getActStat($_stat) {
				if( $this->envData->audio_server->actStat == $_stat ) {
					return "";

				} else {
					return 'style="display: none;"';
				}
			}
			
			function getChannelsStat() {
				if( Audio_setup\Def\NUM_CHANNELS == 2) {
					return "";

				} else {
					return 'style="display: none;"';
				}
			}

			function getOperStat() {
				if( $this->envData->audio_server->operType == "default" ) {
					return ' disabled style="color: #808080; background: #ffffff;"';

				} else {
					return 'style="color: #000000"';
				}
			}

			function getEncodeStat() {
				if( $this->envData->audio_server->encode == "pcm" ) {
					return 'style="display: none;"';

				} else {
					return "";
				}

			}

			function getPcmStat() {
				if( $this->envData->audio_server->encode == "mp3" ) {
					return 'style="display: none;"';

				} else {
					return "";
				}

			}

			function getSetupStat($_stat) {
				if( $this->envData->audio_server->stat == $_stat ) {
					return "";

				} else {
					return 'style="display: none;"';
				}
			}

			function getCastStat() {
				/*if( $this->envData->audio_server->castType == "unicast" ) {
					return 'style="display: none;"';
				}*/

				return "";
			}
			
			function getOperValueMyIp($_type) {
				
				return $_SERVER['SERVER_NAME'];

			}

			function getOperValue($_type) {
				$operType = $this->envData->audio_server->operType;
				/*if( $_type == "ipAddr" && $this->envData->audio_server->castType == "unicast" ) {
					return $_SERVER['SERVER_NAME'];
				}*/

				return $this->envData->audio_server->$operType->$_type;
			}

			function getProtocolValue() {
				$value = $this->envData->audio_server->protocol;

				return $value == "tcp" ? "TCP/IP" : "RTSP";
			}

			function getCastTypeValue() {
				$value = $this->envData->audio_server->castType;

				return $value == "unicast" ? "Unicast" : "Multicast";
			}

			function getEncodeValue() {
				$value = $this->envData->audio_server->encode;

				return $value == "pcm" ? "PCM" : "MP3";
			}

			function getEnvData() { // for ajax

				return $this->loadEnvData("../../conf/audio_stat.db");
			}

			function setEnvData($_statData) { // for ajax
				$statData = json_decode($_statData);
				$setList = "tabStat = '{$statData->tabStat}'";

				updateDatabase("../../conf/audio_stat.db", "audio_info", $setList);

				$statData = $statData->audio_server;
				$setList  = "";
				$setList .= "stat           = '{$statData->stat}', ";
				if( isset($statData->protocol) && $statData->protocol != "" ) {
					$setList .= "protocol       = '{$statData->protocol}', ";

				} if( isset($statData->castType) && $statData->castType != "" ) {
					$setList .= "castType       = '{$statData->castType}', ";

				} if( isset($statData->encode) && $statData->encode != "" ) {
					$setList .= "encode         = '{$statData->encode}', ";

				} if( isset($statData->pcm->sampleRate) && $statData->pcm->sampleRate != "" ) {
					$setList .= "pcm_sampleRate = '{$statData->pcm->sampleRate}', ";

				} if( isset($statData->pcm->channels) && $statData->pcm->channels != "" ) {
					$setList .= "pcm_channels   = '{$statData->pcm->channels}', ";

				} if( isset($statData->pcm->chunkSize) && $statData->pcm->chunkSize != "" ) {
					$setList .= "pcm_chunkSize  = '{$statData->pcm->chunkSize}', ";

				} if( isset($statData->mp3->sampleRate) && $statData->mp3->sampleRate != "" ) {
					$setList .= "mp3_sampleRate = '{$statData->mp3->sampleRate}', ";

				} if( isset($statData->mp3->bitRate) && $statData->mp3->bitRate != "" ) {
					$setList .= "mp3_bitRate    = '{$statData->mp3->bitRate}', ";

				} if( isset($statData->mp3->chunkSize) && $statData->mp3->chunkSize != "" ) {
					$setList .= "mp3_chunkSize  = '{$statData->mp3->chunkSize}', ";

				} if( isset($statData->deviceInfo->deviceName) && $statData->deviceInfo->deviceName != "" ) {
					$setList .= "deviceName     = '{$statData->deviceInfo->deviceName}', ";

				} if( isset($statData->deviceInfo->queueCnt) && $statData->deviceInfo->queueCnt != "" ) {
					$setList .= "queueCnt       = '{$statData->deviceInfo->queueCnt}', ";

				} if( isset($statData->deviceInfo->clientCnt) && $statData->deviceInfo->clientCnt != "" ) {
					$setList .= "clientCnt      = '{$statData->deviceInfo->clientCnt}', ";

				} if( isset($statData->deviceInfo->bufferRate) && $statData->deviceInfo->bufferRate != "" ) {
					$setList .= "bufferRate     = '{$statData->deviceInfo->bufferRate}', ";

				} if( isset($statData->deviceInfo->playMode) && $statData->deviceInfo->playMode != "" ) {
					$setList .= "playMode       = '{$statData->deviceInfo->playMode}', ";

				} if( isset($statData->deviceInfo->fileName) && $statData->deviceInfo->fileName != "" ) {
					$setList .= "fileName       = '{$statData->deviceInfo->fileName}', ";

				} if( isset($statData->operType) && $statData->operType != "" ) {
					$setList .= "operType       = '{$statData->operType}', ";

				} if( isset($statData->default->ipAddr) && $statData->default->ipAddr != "" ) {
					$setList .= "default_ipAddr = '{$statData->default->ipAddr}', ";

				} if( isset($statData->default->port) && $statData->default->port != "" ) {
					$setList .= "default_port   = '{$statData->default->port}', ";

				} if( isset($statData->change->ipAddr) && $statData->change->ipAddr != "" ) {
					$setList .= "change_ipAddr  = '{$statData->change->ipAddr}', ";

				} if( isset($statData->change->port) && $statData->change->port != "" ) {
					$setList .= "change_port    = '{$statData->change->port}', ";

				}
				$setList .= "actStat        = '{$statData->actStat}'";

				updateDatabase("../../conf/audio_stat.db", "audio_server", $setList);

				return ;
			}
		}
	

		class AudioClientFunc {
			private $envData;

			function __construct() {
				$this->envData = $this->loadEnvData($_SERVER['DOCUMENT_ROOT'] . "/" . "modules/audio_setup/conf/audio_stat.db");

				return ;
			}

			function loadEnvData($_filePath) {
				$db_audioClient = Audio_setup\Func\selectDatabase($_filePath, "audio_client");

				$load_envData = array();
				$load_envData["audio_client"] = array(
														"use" 		 => $db_audioClient["use"],
														"stat" 		 => $db_audioClient["stat"],
														"actStat" 	 => $db_audioClient["actStat"],
														"operType" 	 => $db_audioClient["operType"],
														"protocol" 	 => $db_audioClient["protocol"],
														"castType" 	 => $db_audioClient["castType"],
														"buffer"	 	 => array(
																			"sec"    => $db_audioClient["buffer_sec"],
																			"msec"   => $db_audioClient["buffer_msec"]
																		),
														"volume" 	 => $db_audioClient["volume"],
														"redundancy" => $db_audioClient["redundancy"],
														"default" => array(
																			"unicast"     => array(
																						"ipAddr"   => $db_audioClient["default_unicast_ipAddr"],
																						"port"     => $db_audioClient["default_unicast_port"]
																						),
																			"unicast_rep" => array(
																						"ipAddr"   => $db_audioClient["default_unicast_rep_ipAddr"],
																						"port"     => $db_audioClient["default_unicast_rep_port"]
																						),
																			"multicast"   => array(
																						"ipAddr"   => $db_audioClient["default_multicast_ipAddr"],
																						"port"     => $db_audioClient["default_multicast_port"]
																						),
																		),
														"change" => array(
																			"unicast"     => array(
																						"ipAddr"   => $db_audioClient["change_unicast_ipAddr"],
																						"port"     => $db_audioClient["change_unicast_port"]
																						),
																			"unicast_rep" => array(
																						"ipAddr"   => $db_audioClient["change_unicast_rep_ipAddr"],
																						"port"     => $db_audioClient["change_unicast_rep_port"]
																						),
																			"multicast"   => array(
																						"ipAddr"   => $db_audioClient["change_multicast_ipAddr"],
																						"port"     => $db_audioClient["change_multicast_port"]
																						),
																		),

														);
				$encData = json_encode($load_envData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

				return json_decode($encData);
			}

			function getEnableTabStat($_type) {
				if( $this->envData->$_type->use == "disabled" ) {
					return false;
				}

				return true;
			}


			function getActStat($_stat) {
				if( $this->envData->audio_client->actStat == $_stat ) {
					return "";

				} else {
					return 'style="display: none;"';
				}
			}

			function getEnableStat($_key, $_stat) {
				if( $this->envData->audio_client->$_key == $_stat ) {
					return "checked";
				}

				return "";
			}

			function getSetupStat($_stat) {
				if( $this->envData->audio_client->stat == $_stat ) {
					return "";

				} else {
					return 'style="display: none;"';
				}
			}

			function getProtocolValue() {
				$value = $this->envData->audio_client->protocol;

				return $value == "tcp" ? "TCP/IP" : "RTSP";
			}

			function getCastTypeValue() {
				$value = $this->envData->audio_client->castType;

				return $value == "unicast" ? "Unicast" : "Multicast";
			}

			function getBufferStat($_type, $_value) {
				$buffer = $this->envData->audio_client->buffer;

				if( $_type == "sec" ) {
					return $buffer->sec  == $_value ? "selected" : "";

				} else {
					return $buffer->msec == $_value ? "selected" : "";
				}
			}
			function getBufferStatMobile($_type) {
				$buffer = $this->envData->audio_client->buffer;

				if( $_type == "sec" ) {
					return $buffer->sec ;

				} else {
					return $buffer->msec;
				}
			}
			
			function getVolumeMobile() {
				$volume = $this->envData->audio_client->volume;

					return $volume;

			}

			function getOperValue($_rep, $_type) {
				$path = $this->envData->audio_client;
				$changeType =$path->operType;
				$castType = $path->castType;

				if( $_rep == "master" ) {
					if( $_castType == "unicast" ) {
						$value = $path->$changeType->$castType->$_type;

					} else {
						$value = $path->$changeType->$castType->$_type;
					}

				} else {
					$value = $path->$changeType->unicast_rep->$_type;
				}

				return $value;
			}

			function getRedundancyStat($_stat) {
				if( $_stat == "setup" ) {
					if( $this->envData->audio_client->castType == "unicast" ) {

						return "";
					}

				} else {
					if( $this->envData->audio_client->castType == "unicast" ) {
						if(  $this->envData->audio_client->redundancy == "slave" ) {
							return "";
						}
					}
				}

				return 'style="display: none"';
			}

			function getOperStat() {
				if( $this->envData->audio_client->operType == "default" ) {
					return ' disabled style="color: #808080; background: #ffffff;"';

				} else {
					return 'style="color: #000000"';
				}
			}

			function getEnvData() { // for ajax

				return $this->loadEnvData("../../conf/audio_stat.db");
			}
			
			function getOperVolume() {
				$value = $this->envData->audio_client->volume;

				return $value;
			}

			function setEnvData($_statData) { // for ajax
				$statData = json_decode($_statData);
				$statData = $statData->audio_client;

				$setList  = "";
				$setList .= "stat        = '{$statData->stat}', ";
				if( isset($statData->operType) && $statData->operType != "" ) {
				$setList .= "operType    = '{$statData->operType}', ";

				} if( isset($statData->protocol) && $statData->protocol != "" ) {
				$setList .= "protocol    = '{$statData->protocol}', ";

				} if( isset($statData->castType) && $statData->castType != "" ) {
				$setList .= "castType    = '{$statData->castType}', ";

				} if( isset($statData->buffer->sec) && $statData->buffer->sec != "" ) {
				$setList .= "buffer_sec  = '{$statData->buffer->sec}', ";

				} if( isset($statData->buffer->msec) && $statData->buffer->msec != "" ) {
				$setList .= "buffer_msec = '{$statData->buffer->msec}', ";

				} if( isset($statData->volume) && $statData->volume != "" ) {
				$setList .= "volume      = '{$statData->volume}', ";

				} if( isset($statData->redundancy) && $statData->redundancy != "" ) {
				$setList .= "redundancy  = '{$statData->redundancy}', ";

				} if( isset($statData->default->unicast->ipAddr) && $statData->default->unicast->ipAddr != "" ) {
				$setList .= "default_unicast_ipAddr     = '{$statData->default->unicast->ipAddr}', ";

				} if( isset($statData->default->unicast->port) && $statData->default->unicast->port != "" ) {
				$setList .= "default_unicast_port       = '{$statData->default->unicast->port}', ";

				} if( isset($statData->default->unicast_rep->ipAddr) && $statData->default->unicast_rep->ipAddr != "" ) {
				$setList .= "default_unicast_rep_ipAddr = '{$statData->default->unicast_rep->ipAddr}', ";

				} if( isset($statData->default->unicast_rep->port) && $statData->default->unicast_rep->port != "" ) {
				$setList .= "default_unicast_rep_port   = '{$statData->default->unicast_rep->port}', ";

				} if( isset($statData->default->multicast->ipAddr) && $statData->default->multicast->ipAddr != "" ) {
				$setList .= "default_multicast_ipAddr   = '{$statData->default->multicast->ipAddr}', ";

				} if( isset($statData->default->multicast->port) && $statData->default->multicast->port != "" ) {
				$setList .= "default_multicast_port     = '{$statData->default->multicast->port}', ";

				} if( isset($statData->change->unicast->ipAddr) && $statData->change->unicast->ipAddr != "" ) {
				$setList .= "change_unicast_ipAddr      = '{$statData->change->unicast->ipAddr}', ";

				} if( isset($statData->change->unicast->port) && $statData->change->unicast->port != "" ) {
				$setList .= "change_unicast_port        = '{$statData->change->unicast->port}', ";

				} if( isset($statData->change->unicast_rep->ipAddr) && $statData->change->unicast_rep->ipAddr != "" ) {
				$setList .= "change_unicast_rep_ipAddr  = '{$statData->change->unicast_rep->ipAddr}', ";

				} if( isset($statData->change->unicast_rep->port) && $statData->change->unicast_rep->port != "" ) {
				$setList .= "change_unicast_rep_port    = '{$statData->change->unicast_rep->port}', ";

				} if( isset($statData->change->multicast->ipAddr) && $statData->change->multicast->ipAddr != "" ) {
				$setList .= "change_multicast_ipAddr    = '{$statData->change->multicast->ipAddr}', ";

				} if( isset($statData->change->multicast->port) && $statData->change->multicast->port != "" ) {
				$setList .= "change_multicast_port      = '{$statData->change->multicast->port}', ";

				}

				$setList .= "actStat     = '{$statData->actStat}'";


				updateDatabase("../../conf/audio_stat.db", "audio_client", $setList);

				return ;
			}
		}
	}
?>
