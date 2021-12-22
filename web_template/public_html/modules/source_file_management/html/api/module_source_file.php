<?php
	include_once '/opt/interm/public_html/api/api_websocket.php';

	$app->post(
		"/source_file/setPlayInfo",
		function() use($app) {
			// API 응답은 정상 케이스 이므로 200 ok 처리
			$app->setResponseMessage("ok");
			$app->setResponseCode(200);

			// #1 - 출력장치 유무 판단
			$json_dev_info = json_decode(file_get_contents("/opt/interm/conf/config-device-info.json"));
			if( $json_dev_info->port->Audio->out == 0 ) {
				$app->setResponseResult("invalid device type");
				return $app->getResponseData();
			}
		
			// #2 - post 데이터 처리 및 action 지정
			$inputData = $app->getPostContent();
			$cmd_id = 0x00;
			$data	= null;
			
			if( !isset($inputData->action) ) {
				$app->setResponseResult("invalid data format");
				return $app->getResponseData();
			}
			
			switch( $inputData->action ) {
				case "play" :
					if( isset($inputData->is_dir) && $inputData->is_dir == true ) {
						// 전체 재생
						$cmd_id = 0x24;
						$data	= "";
	
					} else {
						// 한곡 재생
						if( !isset($inputData->fileName) || !isset($inputData->count) ) {
							$app->setResponseResult("invalid data format");
							return $app->getResponseData();
						}
	
						$cmd_id = 0x23;
						$data["source_name"]	   = $inputData->fileName;
						$data["source_loop_count"] = $inputData->count;
							
						$data = json_encode($data);
					}
	
					$ws_snd_handler = new WebsocketHandler("127.0.0.1", "snd_interface");
					$ws_snd_handler->send(0x01, "audio_player_control");
					
					while( true ) {
						$result = $ws_snd_handler->recv();
	
						if( $result["cmd_id"] == 0x01 ) {
							break;
						}
					}
					break;
				
				case "stop" :
					$cmd_id = 0x12;
					$data	= null;
					break;
				
				default :
					$app->setResponseResult("invalid action type");
					return $app->getResponseData();

				break;
			}

			$ws_handler = new WebsocketHandler("127.0.0.1", "audio_player_control");

			while( true ) {
				$rc = $ws_handler->send($cmd_id, $data);
				usleep(10000);

				if( $rc == 1 ) break;
			}

			$app->setResponseResult(null);
			return $app->getResponseData();
		}
	);

	$app->get(
		"/source_file/getFileList",
		function() use($app) {
			// API 응답은 정상 케이스 이므로 200 ok 처리
			$app->setResponseMessage("ok");
			$app->setResponseCode(200);

			// #1 - 출력장치 유무 판단
			$json_dev_info = json_decode(file_get_contents("/opt/interm/conf/config-device-info.json"));
			if( $json_dev_info->port->Audio->out == 0 ) {
				$app->setResponseResult("invalid device type");
				return $app->getResponseData();
			}
		
			// #2 - request / response 데이터 처리
			$ws_handler = new WebsocketHandler("127.0.0.1", "audio_player_control");
			$ws_handler->send(0x22, null);

			while( true ) {
		        $res_src_list = $ws_handler->recv();

		        if( $res_src_list["cmd_id"] == 0x22 ) {
		            break;
				}
			}
			
			$json_src_list = json_decode($res_src_list["data"]);
			$arr_src_list  = explode("|", $json_src_list->data->source_name_list);

			$app->setResponseResult(array("files" => $arr_src_list));
			return $app->getResponseData();
		}
	);

    $app->post(
        "/source_file/setVolume",
        function() use($app) {
			// API 응답은 정상 케이스 이므로 200 ok 처리
			$app->setResponseMessage("ok");
			$app->setResponseCode(200);

			// #1 - 출력장치 유무 판단
			$json_dev_info = json_decode(file_get_contents("/opt/interm/conf/config-device-info.json"));
			if( $json_dev_info->port->Audio->out == 0 ) {
				$app->setResponseResult("invalid device type");
				return $app->getResponseData();
			}

			// #2 - snd_interface 를 통한 volume 제어
            $inputData = $app->getPostContent();
			$ws_snd_handler = new WebsocketHandler("127.0.0.1", "snd_interface");
			
			$arr_data = array();
			$arr_data["audio_volume"] = $inputData->volume;
			$data = json_encode($arr_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
			
			$ws_snd_handler->send(0x10, $data);
			while( true ) {
				$result = $ws_snd_handler->recv();
				
		        if( $result["cmd_id"] == 0x10 ) {
					break;
				}
			}

            $app->setResponseResult(null);
            return $app->getResponseData();
        }
    );
?>
