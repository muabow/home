<?php
	include_once '/opt/interm/public_html/api/api_websocket.php';

	$app->post(
		"/tts_file/setPlayInfo",
		function() use($app) {
			$json_dev_info = json_decode(file_get_contents("/opt/interm/conf/config-device-info.json"));
			$is_dev_output = ($json_dev_info->port->Audio->out == 0 ? false : true);
			if( !$is_dev_output ) {
				$app->setResponseMessage("error");
				$app->setResponseResult($app->getHttpStatusMessage(503));
				$app->setResponseCode(503);
				
				return $app->getResponseData();
			}
			
			$inputData = $app->getPostContent();
			
			if( !isset($inputData->action) || !isset($inputData->key) || $inputData->action != "play" ) {
				$app->setResponseMessage("ok");
				$app->setResponseResult("invalid request");
				$app->setResponseCode(200);
				
				return $app->getResponseData();
			}

			$json_tts_info = "/opt/interm/public_html/modules/tts_file_management/conf/tts_info.json";
			$json_info = json_decode(file_get_contents($json_tts_info));

			$is_exist_key = false;
			$key_index    = -1;
			foreach( $json_info->tts_list as $index => $key ) {
				if( $inputData->key == $key->file_path ) {
					$key_index = $index;
					$is_exist_key = true;
					break;
				}
			}
			
			if( !$is_exist_key ) {
				$app->setResponseMessage("ok");
				$app->setResponseResult("file not exist");
				$app->setResponseCode(200);
				
				return $app->getResponseData();
			}

			$ws_handler = new WebsocketHandler("127.0.0.1", "tts_ctrl");
			
			$json_info = array();
			$json_info["tts_idx"] = $key_index;
			
			$rc = $ws_handler->send(0x01, json_encode($json_info));

			while( true ) {
				usleep(10000);
				if( $rc == 1 ) break;
			}

			$app->setResponseMessage("ok");
			$app->setResponseResult("success");
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	$app->get(
		"/tts_file/getFileList",
		function() use($app) {
			$json_dev_info = json_decode(file_get_contents("/opt/interm/conf/config-device-info.json"));
			$is_dev_output = ($json_dev_info->port->Audio->out == 0 ? false : true);
			
			if( !$is_dev_output ) {
				$app->setResponseMessage("error");
				$app->setResponseResult($app->getHttpStatusMessage(503));
				$app->setResponseCode(503);

				return $app->getResponseData();
			}

			$json_tts_info = "/opt/interm/public_html/modules/tts_file_management/conf/tts_info.json";
			$json_info = json_decode(file_get_contents($json_tts_info));

			$arr_src_list = array();
			foreach( $json_info->tts_list as $key ) {
				$d_time = $key->tts_info->duration;
				$file_name = $key->tts_info->title;
				$file_path = $key->file_path;
				
				$tts_unit = array();
				$tts_unit["key"] 		= $file_path;
				$tts_unit["duration"]	= $d_time;
				$tts_unit["title"]		= $file_name; 

				$arr_src_list[] = $tts_unit;
			}

 			$app->setResponseMessage("ok");
			$app->setResponseResult(array("files" => $arr_src_list));
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);
?>
