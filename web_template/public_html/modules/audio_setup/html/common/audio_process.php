<?php
	include_once '/opt/interm/public_html/api/api_websocket.php';

	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_define.php";
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_script.php";

	include_once "common_define.php";
	include_once "common_script.php";

	if( $_POST["type"] == "audio" && isset($_POST["act"]) ) {
		$setup_handler = new Audio_setup\Func\AudioSetupHandler();
		$act = $_POST["act"];

		if( $act == "change_module_status" ) {
			if($_POST["mode"] == "INPUT CONNECTING") {
				// input connecting mode, NC-S01
				// [오디오 설정, 서버], [장치 정보 설정]
				$setup_handler->update_module_status("audio_server", 'module_use="enabled"');
				$setup_handler->update_module_status("audio_client", 'module_use="disabled"');

			} else if($_POST["mode"] == "OUTPUT CONNECTING") {
				// output connecting mode, NC-600
				// [오디오 설정, 서버/클라이언트], [접점 출력 상태], [음원 파일 관리]
				$setup_handler->update_module_status("audio_server", 'module_use="enabled"');
				$setup_handler->update_module_status("audio_client", 'module_use="enabled"');

			} else {
				// stand_alone, [오디오 설정, 서버/클라이언트]
				$setup_handler->update_module_status("audio_server", 'module_use="enabled"');
				$setup_handler->update_module_status("audio_client", 'module_use="enabled"');
			}

			$setup_handler->update_module_status("audio_server", 'module_view="setup"');
			$setup_handler->update_module_status("audio_server", 'module_status="stop"');

			$setup_handler->update_module_status("audio_client", 'module_view="setup"');
			$setup_handler->update_module_status("audio_client", 'module_status="stop"');

			shell_exec("sudo killall audio_server");
			shell_exec("sudo killall audio_client");

			return ;
		}

		else if( $act == "operation_status" ) {
			$setup_data = json_decode($setup_handler->get_env_data());
			
			$arr_json_data = "";

			if( $_POST["mode"] == "server" ) {
				$num_srv_sample_rate = $_POST["sample_rate"];
				if( $num_srv_sample_rate == 0 ) {
					$num_srv_sample_rate = $setup_data->audio_server->audio_pcm_sample_rate;
				}

				$client_module_use    = $setup_data->audio_client->module_use;
				$client_module_view   = $setup_data->audio_client->module_view;
				$client_module_status = $setup_data->audio_client->module_status;
				
				if( $client_module_use == "disabled" ) {
					// 클라이언트를 사용하지 않고 있다면 시작
					$arr_json_data["result"] = "ok";

				}else if( !($client_module_view == "operation" && $client_module_status == "run") ) {
					// 클라이언트가 동작 중이 아니라면 시작
					$arr_json_data["result"] = "ok";
				
				} else {
					$ws_snd_handler = new WebsocketHandler("127.0.0.1", "audio_client");
					$ws_snd_handler->send(0x01, null);
					
					while( true ) {
						$result = $ws_snd_handler->recv();
						$arr_recv_data[$result["cmd_id"]] = json_decode($result["data"]);

						if( $result["cmd_id"] == 0x01 ) break;
					}
					
					$is_client_alive = ($arr_recv_data[1]->data->stat == "1" ? true : false);

					if( $is_client_alive ) {
						$num_client_sample_rate = $arr_recv_data[10]->data->pcm_sample_rate;

						if( $num_client_sample_rate == $num_srv_sample_rate ) {
							// 클라이언트가 동작 중이고 샘플레이트가 같다면 시작
							$arr_json_data["result"] = "ok";
						
						} else {
							// 클라이언트가 동작 중이고 샘플레이트가 다르다면 정지
							$arr_json_data["result"] = "fail";
							$arr_json_data["client_sample_rate"] = $num_client_sample_rate;
						}
					
					} else {
						$arr_json_data["result"] = "ok";
					}
				}
			}
			echo json_encode($arr_json_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

			return ;
		}
	}

	include_once 'audio_process_etc.php';
?>
