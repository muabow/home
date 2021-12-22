<?php
	include_once "common_define.php";
	include_once "common_script.php";

	if( isset($_POST["act"]) ) {
        $env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__)) . "/../";
		
		$ext_config_path = "/opt/interm/conf/config-external-storage.json";
		$json_env_info   = json_decode(file_get_contents($ext_config_path));
		$str_sd_disk_dir = "{$json_env_info->ext_info->target_dir}/{$json_env_info->ext_info->dev_name}";

        switch( $_POST["act"] ) {
			case "create" :
                $type_language = $_POST["language"];
                $type_gender   = $_POST["gender"];
                $text_speak    = $_POST["text"];
                $text_option   = $_POST["option"];

                $text_speak  = str_replace('"' , '\"', $text_speak);
                $text_option = str_replace('"' , '\"', $text_option);

                shell_exec("sudo rm -rf /tmp/tts_output.wav");
                shell_exec("sudo rm -rf {$env_pathModule}/data/tts_*");

                shell_exec("sudo LD_LIBRARY_PATH=\"/opt/interm/usr/lib\" {$env_pathModule}/../bin/tts_bin -l {$type_language} -g {$type_gender} -m \"{$text_speak}\" -o \"{$text_option}\"");
                
                if( !file_exists("/tmp/tts_output.wav") ) {
                    echo "";
                    exit ;
                }

                $current_time = time();
                $path_output = "data/tts_output_{$current_time}.wav";
                
				shell_exec("cp /tmp/tts_output.wav {$env_pathModule}/{$path_output}");
                
                echo "modules/tts_file_management/html/{$path_output}";
                break;

			case "save" :
				$opt_type_language = $_POST["language"];
                $opt_type_gender   = $_POST["gender"];
                $opt_text_speak    = "<vtml_pause time=\"500\"/>" . $_POST["text"] . "<vtml_pause time=\"500\"/>";
                $opt_text_option   = $_POST["option"];

                $opt_text_speak  = str_replace('"' , '\"', $opt_text_speak);
                $opt_text_option = str_replace('"' , '\"', $opt_text_option);

                shell_exec("sudo rm -rf /tmp/tts_output.wav");
                shell_exec("sudo rm -rf {$env_pathModule}/data/tts_*");

                shell_exec("sudo LD_LIBRARY_PATH=\"/opt/interm/usr/lib\" {$env_pathModule}/../bin/tts_bin -l {$opt_type_language} -g {$opt_type_gender} -m \"{$opt_text_speak}\" -o \"{$opt_text_option}\"");
                
                if( !file_exists("/tmp/tts_output.wav") ) {
                    echo "";
                    exit ;
                }

                $current_time = time();
                $path_output = "data/tts_output_{$current_time}.wav";
                
				shell_exec("cp /tmp/tts_output.wav {$env_pathModule}/{$path_output}");
				$result_path = "modules/tts_file_management/html/{$path_output}";
				
				$result_duration = shell_exec("avprobe -show_format \"{$env_pathModule}/{$path_output}\" -v quiet | sed -n 's/duration=//p'");
				$time_msec = ($result_duration - (int)$result_duration) * 100;
				$time_duration = sprintf("%02d:%02d.%02d", (int)$result_duration / 60, (int)$result_duration % 60, $time_msec);

                $text_filepath  = substr($result_path, 37);
                $text_title     = $_POST["title"];
                $type_language  = $_POST["language"];
                $type_gender    = $_POST["gender"];
                $text_speak     = $_POST["text"];
                $text_option    = $_POST["option"];
				$text_duration  = $time_duration;
                $chime_begin    = $_POST["chime_begin"];
				$chime_end      = $_POST["chime_end"];
				$ext_storage    = $_POST["storage"];

                $text_decode_option = json_decode($text_option);

                $arr_tts_info = array();
                $arr_tts_info["file_path"] = $text_filepath;
                $arr_tts_info["tts_info"]["title"]      = $text_title;
                $arr_tts_info["tts_info"]["text"]       = $text_speak;
                $arr_tts_info["tts_info"]["language"]   = $type_language;
                $arr_tts_info["tts_info"]["gender"]     = $type_gender;
                $arr_tts_info["tts_info"]["option"]     = $text_decode_option;
                $arr_tts_info["tts_info"]["duration"]   = $text_duration;
                $arr_tts_info["chime_info"]["begin"]    = $chime_begin;
				$arr_tts_info["chime_info"]["end"]      = $chime_end;
				
				$target_path = "{$env_pathModule}/data/audiofiles";
				if( $ext_storage == "upload_external" && file_exists($str_sd_disk_dir) ) {
					$target_path = "{$json_env_info->mnt_info->target_dir}{$json_env_info->mnt_info->sub_dir_list->tts_file}";
					$arr_tts_info["ext_storage"] = true;
				}

                shell_exec("cp {$env_pathModule}/data/{$text_filepath} {$target_path}/.");
				shell_exec("sudo {$env_pathModule}../bin/tts_convert.sh {$target_path}/{$text_filepath} {$target_path}");

                $conf_path  = "{$env_pathModule}/../conf/tts_info.json";
				$json_info = json_decode(file_get_contents($conf_path), true);
				
                $json_info["tts_list"][] = $arr_tts_info;
                
                file_put_contents($conf_path, json_encode($json_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
				
				// save
				echo $result_path;
				
				// copy to source_file_management module 기능으로 사용 안함
				// shell_exec("cp {$env_pathModule}/data/{$text_filepath} \"/opt/interm/public_html/modules/source_file_management/html/data/audiofiles/TTS_{$text_title}.wav\"");
				break;

			case "load" :
				$conf_path = "{$env_pathModule}/../conf/tts_info.json";
				$json_info = json_decode(file_get_contents($conf_path));
				
				$json_env_info = json_decode(file_get_contents($ext_config_path));
				$tts_path = "{$json_env_info->mnt_info->target_dir}{$json_env_info->mnt_info->sub_dir_list->tts_file}";

				$obj_json_info = "";
				// 파일 목록 작성 및 없는 파일 삭제
				foreach( $json_info->tts_list as $index => $tts_unit ) {
					if( file_exists($str_sd_disk_dir) && isset($tts_unit->ext_storage) ) {
						if( !file_exists("{$tts_path}/{$tts_unit->file_path}") ) {
							unset($json_info->tts_list[$index]);
							file_put_contents($conf_path, json_encode($obj_json_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
							
							continue;
						}
					}

					if( !file_exists($str_sd_disk_dir) && isset($tts_unit->ext_storage) ) {
						continue;
					}

					$obj_json_info->tts_list[] = $tts_unit;
				}

				// 추가된 파일 확인은 제공하지 않음, TTS 옵션 정보가 없기 때문
				
				echo json_encode($obj_json_info);

				break;

			case "remove" :
				$conf_path = "{$env_pathModule}/../conf/tts_info.json";
				$json_info = json_decode(file_get_contents($conf_path), true);
				
				$str_source_list = $_POST["source_name"];

				$arr_file_list = explode("|", $str_source_list);
				$num_file_list = count($arr_file_list);

				for( $idx = 0 ; $idx < $num_file_list ; $idx++ ) {
					$filename = $arr_file_list[$idx];

					foreach( $json_info["tts_list"] as $index => $tts_unit ) {
						if( $tts_unit["file_path"] == $filename ) {
							$tts_path = str_replace(basename(__FILE__), "", realpath(__FILE__)) . "/../data/audiofiles";
							
							if( isset($json_info["tts_list"][$index]["ext_storage"]) ) {
								$json_env_info = json_decode(file_get_contents($ext_config_path));
								$tts_path = "{$json_env_info->mnt_info->target_dir}{$json_env_info->mnt_info->sub_dir_list->tts_file}";
							}

							unset($json_info["tts_list"][$index]);

							$filename = substr($filename, 1);
							@unlink("{$tts_path}/{$filename}");
							
							$none_ext_name = substr($filename, 0, strrpos($filename, "."));
							$target_src = "{$tts_path}/*_{$none_ext_name}_*.pcm";
							$target_src = str_replace(array(']', '['),  array('\\]', '\\['), $target_src);

							array_map('unlink', glob($target_src));
							
							shell_exec("sync");

							break;
						}
					}
				}

				$json_info["tts_list"] = array_values($json_info["tts_list"]);
				file_put_contents($conf_path, json_encode($json_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

				break;

			case "sort" :
				$conf_path = "{$env_pathModule}/../conf/tts_info.json";
				$json_info = json_decode(file_get_contents($conf_path), true);

				$str_source_list = $_POST["source_name"];

				$arr_file_list = explode("|", $str_source_list);
				$num_file_list = count($arr_file_list);
				
				$arr_tts_list = array();

				for( $idx = 0 ; $idx < $num_file_list ; $idx++ ) {
					$filename = $arr_file_list[$idx];

					foreach( $json_info["tts_list"] as $index => $tts_unit ) {
						if( $tts_unit["file_path"] == $filename ) {
							$arr_tts_list[] = $tts_unit;
							break;
						}
					}
				}
				$json_info["tts_list"] = array_values($arr_tts_list);

				file_put_contents($conf_path, json_encode($json_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
				
				break;
			
			case "reload" :
				$tts_handle = new TTS_file_management\Func\TTS_Handler();

				echo $tts_handle->get_size_upload_available();
				break;
				
			case "reload_ext" :
				$tts_handle = new TTS_file_management\Func\TTS_Handler();

				echo $tts_handle->get_size_upload_available_ext();
				break;

			case "preview" :
				$file_path = $_POST["src"];
				$ext_config_path = "/opt/interm/conf/config-external-storage.json";
				$json_env_info   = json_decode(file_get_contents($ext_config_path));
				
				shell_exec("ln -s '{$json_env_info->mnt_info->target_dir}{$json_env_info->mnt_info->sub_dir_list->tts_file}/{$file_path}' /opt/interm/public_html/modules/tts_file_management/html/data/.");
			
				echo $file_path;
				break;

			case "preview_clear" :
				shell_exec("find /opt/interm/public_html/modules/tts_file_management/html/data/ -type l -maxdepth 1 -delete");
				break;


			case "copy" :
				$conf_path = "{$env_pathModule}/../conf/tts_info.json";
				$json_info = json_decode(file_get_contents($conf_path), true);
				
				$str_source_list = $_POST["source_name"];

				$arr_file_list = explode("|", $str_source_list);
				$num_file_list = count($arr_file_list);

				for( $idx = 0 ; $idx < $num_file_list ; $idx++ ) {
					$filename = $arr_file_list[$idx];
					
					foreach( $json_info["tts_list"] as $tts_info ) {
						if( $tts_info["file_path"] == $filename ) {
							// copy to source_file_management module, sample_rate 변경 후 복사
							$text_title = $tts_info["tts_info"]["title"];
							shell_exec("avconv -y -i \"{$env_pathModule}/data/audiofiles/{$filename}\" $time -ar 44100 -ac 1 -f wav -acodec pcm_s16le \"/opt/interm/public_html/modules/source_file_management/html/data/audiofiles/TTS_{$text_title}.wav\"");
						}
					}
				}

				break;

            default :
		        break;
        }

    }

	include_once 'common_process_etc.php';
?>