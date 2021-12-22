<?php
namespace Source_file_management\Func {
	include_once "{$_SERVER['DOCUMENT_ROOT']}/common/common_define.php";
	include_once "{$_SERVER['DOCUMENT_ROOT']}/common/common_script.php";
	
	use Source_file_management;
	use SQLite3;

		const ERR_DB_LOCK		= 5;
		const TIME_LOCK_PERIOD	= 20000;	// msec

		const PATH_LAST_ERR_MSG	= "/tmp/err_sqlite3_source_file_management";

		function func_sql_update($_file_path, $_table_name, $_query) {
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


		class SourceFileHandler {
			private $ext_config_path = "/opt/interm/conf/config-external-storage.json";
			
			private $is_ext_use = false;
			private $str_sd_disk_dir = "";
			
			function __construct() {
				if( file_exists($this->ext_config_path) ) {
					$json_env_info = json_decode(file_get_contents($this->ext_config_path));
					if( $json_env_info->is_ext_use ) {
						$this->is_ext_use = true;
						$this->str_sd_disk_dir = "{$json_env_info->ext_info->target_dir}/{$json_env_info->ext_info->dev_name}";
					}
				}

   				return ;
			}

			function get_size_upload_available() {
				global $commonInfoFunc;

				return round($commonInfoFunc->get_memory_available() / 1024 / 1024);
			}

			function get_size_upload_available_ext() {
				if( !$this->is_ext_use ) {
					return -1;
				}

				if( !file_exists($this->str_sd_disk_dir) ) {
					return -1;
				}
				
				$exec_output = shell_exec("df {$this->str_sd_disk_dir} | grep -v Filesystem | awk {'print $3,$4'}");
				$arr_size = explode(" ", $exec_output);

				return round(($arr_size[1] - $arr_size[0]) / 1024);
			}


			function update_module_status($_type, $_query) {
				func_sql_update("../../conf/source_file_management.db", $_type, $_query);

				return ;
			}

			function is_exist_ext_storage() {
				if( !$this->is_ext_use ) {
					return false;
				}

				return file_exists($this->str_sd_disk_dir);
			}
		}

		include_once "common_script_etc.php";
	}
?>
