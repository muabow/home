<?php
	// PHP 함수 등을 작성합니다.
	namespace Audio_setup\Func {
		use Audio_setup;
		use SQLite3;

		const ERR_DB_LOCK		= 5;
		const TIME_LOCK_PERIOD	= 20000;	// msec

		const PATH_LAST_ERR_MSG	= "/tmp/err_sqlite3_audio_setup";

		function func_sql_select($_file_path, $_table_name) {
			if( !($sql_handler = new SQLite3($_file_path)) ) {
				shell_exec("echo " . $sql_handler->lastErrorMsg() . " > " . PATH_LAST_ERR_MSG);
			}

			while( true ) {
				if( !($result = $sql_handler->query("select * from " . $_table_name)) ) {
					shell_exec("echo " . $sql_handler->lastErrorMsg() . " > " . PATH_LAST_ERR_MSG);

					if( $sql_handler->lastErrorCode() == ERR_DB_LOCK ) {
						usleep(TIME_LOCK_PERIOD);
						continue;
					}
				}
				break;
			}

			$arrRow = array();
			while( ($row = $result->fetchArray(SQLITE3_ASSOC)) ) {
				$arrRow[] = $row;
			}
			$sql_handler->close();

			return $arrRow[0];
		}

		function func_sql_update($_file_path, $_table_name, $_query) {
			if( !($sql_handler = new SQLite3($_file_path)) ) {
				shell_exec("echo " . $sql_handler->lastErrorMsg() . " > " . PATH_LAST_ERR_MSG);
			}

			while( true ) {
				if( !($result = $sql_handler->query("update " . $_table_name . " set " . $_query)) ) {
					shell_exec("echo " . $sql_handler->lastErrorMsg() . " > " . PATH_LAST_ERR_MSG);

					if( $sql_handler->lastErrorCode() == ERR_DB_LOCK ) { // db lock
						usleep(TIME_LOCK_PERIOD);
						continue;
					}
				}
				break;
			}
			$sql_handler->close();

			return $result;
		}

		class AudioSetupHandler {
			private $env_data;

			function __construct() {
				$this->load_env_data();

				return ;
			}

			function load_env_data() {
				$config_db_path = $_SERVER['DOCUMENT_ROOT'] . "/modules/audio_setup/conf/audio_io.db";

				$db_audioServer = Audio_setup\Func\func_sql_select($config_db_path, "audio_server");
				$db_audioClient = Audio_setup\Func\func_sql_select($config_db_path, "audio_client");

				$load_envData = array();
				$load_envData["audio_server"] = $db_audioServer["module_use"];
				$load_envData["audio_client"] = $db_audioClient["module_use"];

				$json_data = json_encode($load_envData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

				$this->env_data = json_decode($json_data);

				return ;
			}

			function is_enable_tab($_type) {
				if( $this->env_data->$_type == "enabled" ) {
					return true;
				}

				return false;
			}

			function get_env_data() {
				$config_db_path = $_SERVER['DOCUMENT_ROOT'] . "/modules/audio_setup/conf/audio_io.db";

				$db_audioServer = Audio_setup\Func\func_sql_select($config_db_path, "audio_server");
				$db_audioClient = Audio_setup\Func\func_sql_select($config_db_path, "audio_client");

				$load_envData = array();
				$load_envData["audio_server"] = $db_audioServer;
				$load_envData["audio_client"] = $db_audioClient;

				$json_data = json_encode($load_envData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);


				return $json_data;
			}

			// ajax function, for AOE-N300
			function update_module_status($_type, $_query) {
				func_sql_update("../../conf/audio_io.db", $_type, $_query);

				return ;
			}
		}

		class AudioServerHandler {
			private $env_data;
			private $env_conf;

			function __construct() {
				$this->load_env_data();

				return ;
			}

			function load_env_data() {
				$this->env_conf = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/env.json"));

				$config_db_path = $_SERVER['DOCUMENT_ROOT'] . "/modules/audio_setup/conf/audio_io.db";
				$db_audioServer = Audio_setup\Func\func_sql_select($config_db_path, "audio_server");

				$this->env_data = json_decode(json_encode($db_audioServer));

				return ;
			}

			function is_enable_channels() {
				if( defined('Audio_setup\Def\IS_ENABLE_AUDIO_STEREO') ) {
					if( Audio_setup\Def\IS_ENABLE_AUDIO_STEREO == false ) {
						return false;

					} else {
					 	return true;
					}
				}

				if( $this->env_data->audio_pcm_channels == 1 ) {
					return false;

				} else {
					return false;
				}
			}

			function set_enable_channels() {
				if( $this->is_enable_channels() ) {
					return "";

				} else {
					return "disabled";
				}
			}

			function is_enable_multicast($_is_echo = false) {
				$rc_true	= true;
				$rc_false	= false;

				if( $_is_echo ) {
					$rc_true	= 1;
					$rc_false	= 0;
				}

				if( !isset($this->env_conf->mode) || !isset($this->env_conf->mode->set) ) {
					return $rc_false;
				}

				if( $this->env_conf->mode->set == "STAND ALONE" ) {
					return $rc_true;

				} else {
					return $rc_false;
				}
			}

			function set_enable_multicast() {
				if( $this->is_enable_multicast() ) {
					return "";

				} else {
					return 'style="display: none;"';
				}
			}
		}

		include_once "common_script_etc.php";
	}
?>
