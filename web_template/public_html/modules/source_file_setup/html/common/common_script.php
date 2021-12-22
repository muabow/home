<?php
	// PHP 함수 등을 작성합니다.
	namespace Source_file_setup\Func {
		use Source_file_setup;
		use SQLite3;

		const ERR_DB_LOCK		= 5;
		const TIME_LOCK_PERIOD	= 20000;	// msec

		const PATH_LAST_ERR_MSG	= "/tmp/err_sqlite3_source_file_setup";

		function func_sql_select($_file_path, $_table_name, $_key) {
			if( !($sql_handler = new SQLite3($_file_path)) ) {
				shell_exec("echo " . $sql_handler->lastErrorMsg() . " > " . PATH_LAST_ERR_MSG);
			}

			while( true ) {
				if( !($result = $sql_handler->query("select * from {$_table_name} where key='{$_key}';")) ) {
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

		function func_sql_update($_file_path, $_table_name, $_key, $_value) {
			if( !($sql_handler = new SQLite3($_file_path)) ) {
				shell_exec("echo " . $sql_handler->lastErrorMsg() . " > " . PATH_LAST_ERR_MSG);
			}

			while( true ) {
				if( !($result = $sql_handler->query("update {$_table_name} set value='{$_value}' where key='{$_key}';")) ) {
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

		function func_sql_query($_file_path, $_table_name, $_query) {
			if( !($sql_handler = new SQLite3($_file_path)) ) {
				shell_exec("echo " . $sql_handler->lastErrorMsg() . " > " . PATH_LAST_ERR_MSG);
			}

			while( true ) {
				if( !($result = $sql_handler->query("update " . $_table_name . " set " . $_query)) ) {
					shell_exec("echo " . $sql_handler->lastErrorMsg() . " > " . PATH_LAST_ERR_MSG);

					if( $sql_handler->lastErrorCode() == ERR_DB_LOCK ) {
						usleep(TIME_LOCK_PERIOD);
						continue;
					}
				}
				break;
			}
			$sql_handler->close();

			return $result;
		}


		class SetupHandler {
			private $env_data;

			function __construct() {
				$this->load_env_data();

				return ;
			}

			function load_env_data() {
				$config_db_path = $_SERVER['DOCUMENT_ROOT'] . "/modules/source_file_setup/conf/source_file_io.db";

				$load_envData = array();
				$arr_result = Source_file_setup\Func\func_sql_select($config_db_path, "source_file_server", "module_use");
				$load_envData["server"] = $arr_result["value"];

				$arr_result = Source_file_setup\Func\func_sql_select($config_db_path, "source_file_client", "module_use");
				$load_envData["client"] = $arr_result["value"];

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

			// ajax function, for AOE-N300
			function update_module_status($_table, $_key, $_value) {
				func_sql_update("../../conf/source_file_io.db", $_table, $_key, $_value);

				return ;
			}
			
			function update_module_info($_type, $_query) {
				func_sql_query("../../conf/source_file_io.db", $_type, $_query);

				return ;
			}
		}

		class ServerHandler {
			private $env_conf;

			function __construct() {
				$this->load_env_data();

				return ;
			}

			function load_env_data() {
				$this->env_conf = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/env.json"));
				$config_db_path = $_SERVER['DOCUMENT_ROOT'] . "/modules/source_file_setup/conf/source_file_io.db";

				return ;
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
