<?php
	namespace Chime_file_management\Func {
		include_once "{$_SERVER['DOCUMENT_ROOT']}/common/common_define.php";
		include_once "{$_SERVER['DOCUMENT_ROOT']}/common/common_script.php";

		use Chime_file_management;

		class ChimeFileHandler {
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

			function get_size_upload_available() {
				global $commonInfoFunc;

				return round($commonInfoFunc->get_memory_available() / 1024 / 1024);
			}
			
			function get_file_list() {
				$conf_path = str_replace(basename(__FILE__), "", realpath(__FILE__)) . "/../../conf/chime_info.json";
				$json_info = json_decode(file_get_contents($conf_path), true);

				$json_ext_info = json_decode(file_get_contents($this->ext_config_path));
				
				$ext_chime_path = "";
				$arr_list_ext_files = array();
				if( file_exists($this->str_sd_disk_dir) ) {
					$ext_chime_path = "{$json_ext_info->mnt_info->target_dir}{$json_ext_info->mnt_info->sub_dir_list->chime_file}";
					$arr_list_ext_files = $this->read_file_info($ext_chime_path);
				}

				$arr_src_list = array();
				$arr_src_list["code"] = 0; 
				$arr_src_list["remain_size"] = $this->get_size_upload_available();
				
				// JSON 에 list-up 된 파일 목록 탐색 
				foreach( $json_info["chime_list"] as $idx => $chime_info ) {
					// return value setting
					$arr_chime_info = array();
					$arr_chime_info["chime_name"]     	= $chime_info["name"];
					$arr_chime_info["audio_play_time"] 	= $chime_info["duration"];
					$arr_chime_info["ext_storage"] 		= isset($chime_info["ext_storage"]) ? true : false;

					// SD 카드 미삽입 상태이고 외부저장소 파일인 경우 list-up 하지 않음
					if( !file_exists($this->str_sd_disk_dir) && $arr_chime_info["ext_storage"] == true ) {
						unset($json_info["chime_list"][$idx]);
						continue;	
					} 
					
					// SD 카드 삽입 상태이고 외부저장소 파일인 경우
					if( file_exists($this->str_sd_disk_dir) && $arr_chime_info["ext_storage"] == true ) {
						$none_ext_name = substr($chime_info["name"], 0, $chime_info["name"] - 4);

						// 1. 원본 파일이 있는지 체크
						$is_exist_ext_file = false;
						foreach( $arr_list_ext_files as $ext_file ) {
							if( "{$ext_chime_path}/{$chime_info["name"]}" == $ext_file ) {
								$is_exist_ext_file = true;
								break;
							}
						}

						if( $is_exist_ext_file == false ) {
							unset($json_info["chime_list"][$idx]);

							$target_src = "{$ext_chime_path}/*_{$none_ext_name}_*.pcm";
							$target_src = str_replace(array(']', '['),  array('\\]', '\\['), $target_src);
							array_map('unlink', glob($target_src));
							shell_exec("sync");
							
							continue;
						}

						// 2. PCM 파일이 있는지 체크
						$num_pcm_files = count(preg_grep("/(\d)_{$none_ext_name}_(\d).pcm/i", $arr_list_ext_files));

						// 2.1. PCM 파일이 없으면 생성
						if(  $num_pcm_files != 4 ) {
							$exec_conv = "/opt/interm/public_html/modules/chime_file_management/bin/audio_convert.sh \"{$ext_chime_path}/{$chime_info["name"]}\" \"{$ext_chime_path}\" 2>/dev/null";
							pclose(popen($exec_conv, "r"));
						}
					} 

					$arr_src_list["chime_list"][] = $arr_chime_info;
				}

				// JSON 에 list-up 되지 않은 파일 탐색 및 PCM 생성
				// 외부 저장소에 파일 확인하여 목록 추가
				if( file_exists($this->str_sd_disk_dir) ) {
					foreach( $arr_list_ext_files as $ext_file ) {
						$file_info = pathinfo($ext_file);
						$file_name = substr($ext_file, strlen($file_info["dirname"]) + 1);
						
						if( !($file_info["extension"] == "wav" || $file_info["extension"] == "mp3") ) {
							continue;
						}
						
						// 1. list-up 된 원본 파일이 있는지 체크
						$is_exist_listup = false;
						foreach( $arr_src_list["chime_list"] as $listup_file ) {
							if( $file_name == $listup_file["chime_name"] ) {
								$is_exist_listup = true;
								break;
							}
						}
						if( $is_exist_listup == true ) continue;

						// 2. PCM 파일이 있는지 체크
						$none_ext_name = substr($file_name, 0, $file_name - 4);
						$num_pcm_files = count(preg_grep("/(\d)_{$none_ext_name}_(\d).pcm/i", $arr_list_ext_files));

						// 2.1. PCM 파일이 없으면 생성
						if(  $num_pcm_files != 4 ) {
							$exec_conv = "/opt/interm/public_html/modules/chime_file_management/bin/audio_convert.sh \"{$ext_chime_path}/{$file_name}\" \"{$ext_chime_path}\" 2>/dev/null";
							$fp = popen($exec_conv, "r");
							$duration = fread($fp, 1024);
							pclose($fp);
						
						} else {
							$exec_conv = "/opt/interm/public_html/modules/chime_file_management/bin/audio_info.sh \"{$ext_chime_path}/{$file_name}\" 2>/dev/null";
							$fp = popen($exec_conv, "r");
							$duration = fread($fp, 1024);
							pclose($fp);
						}
						
						$duration = round($duration);
						$duration = ($duration > 10 ? 10 : $duration);

						$arr_json_info = array();
						$arr_json_info["name"] 			= $file_name;
						$arr_json_info["duration"] 		= $duration;
						$arr_json_info["ext_storage"] 	= true;
						$json_info["chime_list"][] = $arr_json_info;

						$arr_chime_info = array();
						$arr_chime_info["chime_name"]     	= $file_name;
						$arr_chime_info["audio_play_time"] 	= $duration;
						$arr_chime_info["ext_storage"] 		= true;

						$arr_src_list["chime_list"][] = $arr_chime_info;
					}
				}
				
				$json_info["chime_list"] = array_values($json_info["chime_list"]);
				file_put_contents($conf_path, json_encode($json_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

				return json_encode($arr_src_list, JSON_HEX_APOS);
			}

			/* 사용 안함, get_file_list function 수정
			function get_file_list() {
				$conf_path = str_replace(basename(__FILE__), "", realpath(__FILE__)) . "/../../conf/chime_info.json";
				$json_info = json_decode(file_get_contents($conf_path));
				
				$json_ext_info = json_decode(file_get_contents($this->ext_config_path));
				
				$int_chime_path = str_replace(basename(__FILE__), "", realpath(__FILE__)) . "/../data/audiofiles";
				$ext_chime_path = "";

				$arr_list_ext_files = array();
				if( file_exists($this->str_sd_disk_dir) ) {
					$ext_chime_path = "{$json_ext_info->mnt_info->target_dir}{$json_ext_info->mnt_info->sub_dir_list->chime_file}";
					$arr_list_ext_files = $this->read_file_info($ext_chime_path);
				}

				$index = 0;
				$arr_src_list = array();
				$arr_src_list["code"] = 0; 
				$arr_src_list["remain_size"] = $this->get_size_upload_available();
				
				
				// 내부/외부 저장소 : JSON 파일 기반 파일 정보 확인
				$is_exist_key = false;
				foreach( $json_info->chime_list as $idx => $chime_info ) {
					// return value setting
					$arr_chime_info = array();
					$arr_chime_info["chime_name"]     	= $chime_info->name;
					$arr_chime_info["audio_play_time"] 	= $chime_info->duration;
					$arr_chime_info["ext_storage"] 		= isset($chime_info->ext_storage) ? true : false;
					
					// 외부 저장소에 위치한 파일과 JSON 정보가 일치한지 확인
					// SD 카드 제거 시 JSON 정보에서 외부 저장소 전체 삭제
					if( $arr_chime_info["ext_storage"] == true ) {
						if( file_exists($this->str_sd_disk_dir) ) {
							// SD 카드 삽입되어 있으나 파일이 없으면 JSON 정보와 관련 PCM 파일 삭제
							if( !file_exists("{$ext_chime_path}/{$arr_chime_info["chime_name"]}") ) {
								unset($json_info->chime_list[$idx]);
								
								$none_ext_name = substr($arr_chime_info["chime_name"], 0, strlen($arr_chime_info["chime_name"]) - 4);
								$target_src = "{$ext_chime_path}/*_{$none_ext_name}_*.pcm";
								$target_src = str_replace(array(']', '['),  array('\\]', '\\['), $target_src);
								array_map('unlink', glob($target_src));
								shell_exec("sync");

								continue;
							
							} else {
								// SD 카드가 삽입되어 있으나 음원은 있지만 PCM 파일의 갯수가 맞지 않다면 관련 PCM 파일 삭제
								$none_ext_name = substr($arr_chime_info["chime_name"], 0, strlen($arr_chime_info["chime_name"]) - 4);
								$num_pcm_files = count(preg_grep("/(\d)_{$none_ext_name}_(\d).pcm$/", $arr_list_ext_files));

								if( $num_pcm_files != 4 ) {
									unset($json_info->chime_list[$idx]);
									
									$target_src = "{$ext_chime_path}/*_{$none_ext_name}_*.pcm";
									$target_src = str_replace(array(']', '['),  array('\\]', '\\['), $target_src);
									array_map('unlink', glob($target_src));
									shell_exec("sync");

									continue;
								
								} else {
									// SD 카드가 삽입되어 있고 음원/PCM 모두 정상인 경우 list-up
								}
							}

						} else {
							// SD 카드 미삽입 시 JSON 정보 삭제
							unset($json_info->chime_list[$idx]);
							
							continue;
						}
					}

					$arr_src_list["chime_list"][] = $arr_chime_info;
				}

				// 내부 저장소에 파일 확인하여 목록 추가
				$arr_list_int_files = $this->read_file_info($int_chime_path);

				foreach( $arr_list_int_files as $idx => $name ) {
					$file_info = pathinfo($name);
					$file_name = substr($name, strlen($file_info["dirname"]) + 1);
					
					if( !($file_info["extension"] == "wav" || $file_info["extension"] == "mp3") ) {
						continue;
					}

					$is_exist_int_info = false;
					foreach( $json_info->chime_list as $chime_idx => $chime_info ) {
						// 이미 JSON 정보 목록에 올라가 있는 정상 목록인 경우, 아무 처리 하지 않음
						if( $chime_info->name == $file_name ) {
							$is_exist_int_info = true;
							break;
						}
					}

					if( $is_exist_int_info ) {
						continue;
					}

					// JSON 정보 목록에는 올라가 있지 않으나 음원 파일이 존재하는 경우
					// 1. 음원 파일만 있는 경우
					// 3. 음원 파일 및 PCM 파일 일부가 있는 경우
					// 2. 음원 파일 및 PCM 파일 전체가 있는 경우
					$exec_conv = "/opt/interm/public_html/modules/chime_file_management/bin/audio_convert.sh \"{$int_chime_path}/{$file_name}\" \"{$int_chime_path}\" 2>/dev/null";
					$fp = popen($exec_conv, "r");
					$duration = fread($fp, 1024);
					pclose($fp);
					
					$duration = round($duration);
					$duration = ($duration > 10 ? 10 : $duration);

					$arr_json_info = array();
					$arr_json_info["name"] 			= $file_name;
					$arr_json_info["duration"] 		= $duration;
					$ojb_json_info = json_decode(json_encode($arr_json_info));

					$num_json_idx = count($json_info->chime_list);
					$json_info->chime_list[$num_json_idx] = $ojb_json_info;
					
					$arr_chime_info = array();
					$arr_chime_info["chime_name"]     	= $file_name;
					$arr_chime_info["audio_play_time"] 	= $duration;

					$arr_src_list["chime_list"][] = $arr_chime_info;
				}

				//  외부 저장소에 파일 확인하여 목록 추가
				if( file_exists($this->str_sd_disk_dir) ) {
					$arr_list_ext_files = $this->read_file_info($ext_chime_path);

					foreach( $arr_list_ext_files as $idx => $name ) {
						$file_info = pathinfo($name);
						$file_name = substr($name, strlen($file_info["dirname"]) + 1);

						if( !($file_info["extension"] == "wav" || $file_info["extension"] == "mp3") ) {
							// 자기의 원본 파일 없으면 PCM 파일 삭제
							$pcm_file = substr($file_name, 6, strlen($file_name) - 12);

							$is_exist_src_file = false;
							foreach( $arr_list_ext_files as $ext_idx => $ext_files ) {
								$ext_info = pathinfo($ext_files);
								$ext_name = substr($ext_files, strlen($ext_info["dirname"]) + 1);
								$none_ext_name = substr($ext_name, 0, $ext_name - 4);

								if( $none_ext_name == $pcm_file ) {
									$is_exist_src_file = true;
									break;
								}
							}

							if( !$is_exist_src_file ) {
								@unlink("{$ext_chime_path}/{$file_name}");
							}

							continue;
						}

						$is_exist_ext_info = false;
						foreach( $json_info->chime_list as $chime_idx => $chime_info ) {
							// 이미 JSON 정보 목록에 올라가 있는 정상 목록인 경우, 아무 처리 하지 않음
							if( $chime_info->name == $file_name ) {
								$is_exist_ext_info = true;
								break;
							}
						}

						if( $is_exist_ext_info ) {
							continue;
						}

						// JSON 정보 목록에는 올라가 있지 않으나 음원 파일이 존재하는 경우
						// 1. 음원 파일만 있는 경우
						// 3. 음원 파일 및 PCM 파일 일부가 있는 경우
						// 2. 음원 파일 및 PCM 파일 전체가 있는 경우
						$exec_conv = "/opt/interm/public_html/modules/chime_file_management/bin/audio_convert.sh \"{$ext_chime_path}/{$file_name}\" \"{$ext_chime_path}\" 2>/dev/null";
						$fp = popen($exec_conv, "r");
						$duration = fread($fp, 1024);
						pclose($fp);
						
						$duration = round($duration);
						$duration = ($duration > 10 ? 10 : $duration);

						$arr_json_info = array();
						$arr_json_info["name"] 			= $file_name;
						$arr_json_info["duration"] 		= $duration;
						$arr_json_info["ext_storage"] 	= true;
						$ojb_json_info = json_decode(json_encode($arr_json_info));

						$num_json_idx = count($json_info->chime_list);
						$json_info->chime_list[$num_json_idx] = $ojb_json_info;
						
						$arr_chime_info = array();
						$arr_chime_info["chime_name"]     	= $file_name;
						$arr_chime_info["audio_play_time"] 	= $duration;
						$arr_chime_info["ext_storage"] 		= true;

						$arr_src_list["chime_list"][] = $arr_chime_info;
					}
				}

				file_put_contents($conf_path, json_encode($json_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

				return json_encode($arr_src_list, JSON_HEX_APOS);
			}
			*/

			function remove_file($_filename) {
				$conf_path  = str_replace(basename(__FILE__), "", realpath(__FILE__)) . "/../../conf/chime_info.json";
				$json_info = json_decode(file_get_contents($conf_path), true);

				$arr_file_list = explode("|", $_filename);
				$num_file_list = count($arr_file_list);

				for( $idx = 0 ; $idx < $num_file_list ; $idx++ ) {
					$filename = $arr_file_list[$idx];

					foreach( $json_info["chime_list"] as $index => $chime_info ) {
						if( $chime_info["name"] == $filename ) {
							$chime_path = str_replace(basename(__FILE__), "", realpath(__FILE__)) . "/../data/audiofiles";
							
							if( isset($json_info["chime_list"][$index]["ext_storage"]) ) {
								$json_env_info = json_decode(file_get_contents($this->ext_config_path));
								$chime_path = "{$json_env_info->mnt_info->target_dir}{$json_env_info->mnt_info->sub_dir_list->chime_file}";
							}
							
							unset($json_info["chime_list"][$index]);
							
							shell_exec("sync");

							@unlink("{$chime_path}/{$filename}");
							
							$none_ext_name = substr($filename, 0, strrpos($filename, "."));
							$target_src = "{$chime_path}/*_{$none_ext_name}_*.pcm";
							$target_src = str_replace(array(']', '['),  array('\\]', '\\['), $target_src);

							array_map('unlink', glob($target_src));
							
							for( $in_idx = 0 ; $in_idx < count($json_info["chime_set"]) ; $in_idx++ ) {
								if( $json_info["chime_set"][$in_idx] == $none_ext_name ) {
									$json_info["chime_set"][$in_idx] = "";
								}
							}

							break;
						}
					}
				}

				if( count($json_info["chime_list"]) == 0 ) {
					for( $idx = 0 ; $idx < count($json_info["chime_set"]) ; $idx++ ) {
						$json_info["chime_set"][$idx] = "";
					}
				}

				$json_info["chime_list"] = array_values($json_info["chime_list"]);
				$json_info["chime_set"]  = array_values($json_info["chime_set"]);

				file_put_contents($conf_path, json_encode($json_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
				
				return $this->get_file_list();
			}

			function sort_file($_filename) {
				$conf_path  = str_replace(basename(__FILE__), "", realpath(__FILE__)) . "/../../conf/chime_info.json";
				$json_info = json_decode(file_get_contents($conf_path), true);

				$arr_file_list = explode("|", $_filename);
				$num_file_list = count($arr_file_list);
				
				$arr_chime_list = array();

				for( $idx = 0 ; $idx < $num_file_list ; $idx++ ) {
					$filename = $arr_file_list[$idx];

					foreach( $json_info["chime_list"] as $index => $chime_info ) {
						if( $chime_info["name"] == $filename ) {
							$arr_chime_list[] = $chime_info;
							break;
						}
					}
				}
				$json_info["chime_list"] = array_values($arr_chime_list);
				$json_info["chime_set"]  = array_values($json_info["chime_set"]);

				file_put_contents($conf_path, json_encode($json_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
				
				return $this->get_file_list();
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
