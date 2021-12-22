<?php
	namespace TTS_file_management\Func {
		include_once "{$_SERVER['DOCUMENT_ROOT']}/common/common_define.php";
		include_once "{$_SERVER['DOCUMENT_ROOT']}/common/common_script.php";

		use TTS_file_management;

		const PATH_CHIME_INFO = "/opt/interm/public_html/modules/chime_file_management/conf/chime_info.json";
		const PATH_TTS_INFO   = "/opt/interm/public_html/modules/tts_file_management/conf/tts_info.json";
		const PATH_DFLT_INFO  = "/opt/interm/conf/default.json";

		class TTS_Handler {
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

			function read_file_info($_dir) {
				if( $fp = opendir($_dir) ) {
					$files = Array();
					$in_files = Array();
		
					while( $fileName = readdir($fp) ) {
						if( $fileName[0] != '.' ) {
							if( is_dir($_dir . "/" . $fileName) ) {
								$in_files = read_file_info($_dir . "/" . $fileName);
		
								if( is_array($in_files) ) {
									$files = array_merge ($files , $in_files);
								}
		
							} else {
								array_push($files, $_dir . "/" . $fileName);
							}
						}
					}
					closedir($fp);
		
					return $files;
				}
			}

			function load_chime_list() {
				$json_info = json_decode(file_get_contents(PATH_CHIME_INFO));
				
				$str_option_list = "";

				foreach( $json_info->chime_list as $key ) {
					$d_time = sprintf("%02d:%02d", $key->duration / 60, $key->duration % 60);
					$file_name = substr($key->name, 0, strrpos($key->name, ".")); 
					
					$str_option_list .= "<option value=\"{$file_name}\"> [{$d_time}] {$file_name} &nbsp; &nbsp; </option>";
				}
		
				return $str_option_list;
			}

			function load_support_language() {
				$json_info = json_decode(file_get_contents(PATH_DFLT_INFO));
				
				$str_option_list = "";

				foreach( $json_info->tts_support_language as $item ) {
					foreach( $item as $lang_type => $lang_info ) {
						if( $lang_info->is_enable == true ) {
							$str_option_list .= "<option value=\"{$lang_type}\"> {$lang_info->name} </option>";
						}
					}
				}

				echo $str_option_list;
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
