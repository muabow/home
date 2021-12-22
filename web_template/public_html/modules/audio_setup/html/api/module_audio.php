<?php
	include_once '/opt/interm/public_html/api/api_websocket.php';

	$app->post(
		"/audio/server/initrun",
		function() use($app) {
			$inputData = $app->getPostContent();

			$ws_snd_handler = new WebsocketHandler("127.0.0.1", "snd_interface");
			$ws_snd_handler->send(0x02, "audio_server_control");
			while( true ) {
		        $result = $ws_snd_handler->recv();

		        if( $result["cmd_id"] == 0x02 ) {
		            break;
		        }
			}

			$ws_handler = new WebsocketHandler("127.0.0.1", "audio_server_control");

			$arr_data = array();
			$arr_data["init_bypass"]		= $inputData->init_bypass;
			$arr_data["audio_encode_type"]	= "pcm";
			$arr_data["network_cast_type"]	= "unicast";

			// change encode type
			if( isset($inputData->mp3_mode) ) {
				if( $inputData->mp3_mode == "true" ) {
					$arr_data["audio_encode_type"] = "mp3";
				}
			}

			// change sample_rate info
			if( isset($inputData->audio_pcm_sample_rate) ) {
				$arr_data["audio_pcm_sample_rate"] = $inputData->audio_pcm_sample_rate;
			}

			// change channel info
			if( isset($inputData->audio_pcm_channels) ) {
				$arr_data["audio_pcm_channels"] = $inputData->audio_pcm_channels;
			}

			// change multicast ip address
			if( isset($inputData->ipAddr) ) {
				$arr_data["network_mcast_ip_addr"] = $inputData->ipAddr;
			}

			// change cast type
			if( isset($inputData->network_cast_type) ) {
				$arr_data["network_cast_type"] = $inputData->network_cast_type;
			}

			if( isset($inputData->mcast_port) ) {
				$arr_data["network_mcast_port"] = $inputData->mcast_port;
			}

			if( isset($inputData->ucast_port) ) {
				$arr_data["network_ucast_port"] = $inputData->ucast_port;
			}

			$data = json_encode($arr_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

			while( true ) {
				$rc = $ws_handler->send(0x10, $data);
				usleep(10000);

				if( $rc == 1 ) break;
			}

			$app->setResponseMessage("ok");
			$app->setResponseResult(null);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	$app->get(
		"/audio/server/run",
		function() use($app) {
			$ws_snd_handler = new WebsocketHandler("127.0.0.1", "snd_interface");
			$ws_snd_handler->send(0x02, "audio_server_control");
			while( true ) {
		        $result = $ws_snd_handler->recv();

		        if( $result["cmd_id"] == 0x02 ) {
		            break;
		        }
			}

			$ws_handler = new WebsocketHandler("127.0.0.1", "audio_server_control");

			while( true ) {
				$rc = $ws_handler->send(0x11, null);
				usleep(10000);

				if( $rc == 1 ) break;
			}

			$app->setResponseMessage("ok");
			$app->setResponseResult(null);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	$app->get(
		"/audio/server/stop",
		function() use($app) {
			$ws_handler = new WebsocketHandler("127.0.0.1", "audio_server_control");

			while( true ) {
				$rc = $ws_handler->send(0x12, null);
				usleep(10000);

				if( $rc == 1 ) break;
			}

			$app->setResponseMessage("ok");
			$app->setResponseResult(null);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	$app->post(
		"/audio/server/setVolume",
		function() use($app) {
			$inputData = $app->getPostContent();

			$arr_data = array();
			$arr_data["audio_volume"] = $inputData->volume;
			$data = json_encode($arr_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

			$ws_server_handler = new WebsocketHandler("127.0.0.1", "audio_server_control");
			$ws_server_handler->send(0x15, $data);

			usleep(10000);

			$app->setResponseMessage("ok");
			$app->setResponseResult(null);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);


	// Client URL process
	$app->post(
		"/audio/client/initrun",
		function() use($app) {
			$inputData = $app->getPostContent();

			$ws_snd_handler = new WebsocketHandler("127.0.0.1", "snd_interface");
			$ws_snd_handler->send(0x01, "audio_client_control");
			while( true ) {
		        $result = $ws_snd_handler->recv();

		        if( $result["cmd_id"] == 0x01 ) {
		            break;
		        }
		    }

			$ws_handler = new WebsocketHandler("127.0.0.1", "audio_client_control");

			$arr_data = array();
			$arr_data["network_cast_type"]		= $inputData->castType;
			if( $arr_data["network_cast_type"] == "unicast" ) {
				$arr_data["network_redundancy"]		= "master";
				$arr_data["network_master_ip_addr"]	= $inputData->ipAddr1;
				$arr_data["network_master_port"]	= $inputData->port1;

			} else {
				$arr_data["network_mcast_ip_addr"]	= $inputData->ipAddr1;
				$arr_data["network_mcast_port"]		= $inputData->port1;
			}

			if( isset($inputData->delayMs) ) {
				$delay_sec  = (int)($inputData->delayMs / 1000);
				$delay_msec = $inputData->delayMs - (1000 * $delay_sec);

				$arr_data["audio_play_buffer_sec"]	= $delay_sec;
				$arr_data["audio_play_buffer_msec"]	= $delay_msec;
			}

			$data = json_encode($arr_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

			while( true ) {
				$rc = $ws_handler->send(0x10, $data);
				usleep(10000);

				if( $rc == 1 ) break;
			}

			$app->setResponseMessage("ok");
			$app->setResponseResult(null);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	$app->get(
		"/audio/client/run",
		function() use($app) {
			$ws_snd_handler = new WebsocketHandler("127.0.0.1", "snd_interface");
			$ws_snd_handler->send(0x01, "audio_client_control");
			while( true ) {
		        $result = $ws_snd_handler->recv();

		        if( $result["cmd_id"] == 0x01 ) {
		            break;
		        }
			}
			
			$ws_handler = new WebsocketHandler("127.0.0.1", "audio_client_control");

			while( true ) {
				$rc = $ws_handler->send(0x11, null);
				usleep(10000);

				if( $rc == 1 ) break;
			}

			$app->setResponseMessage("ok");
			$app->setResponseResult(null);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	$app->get(
		"/audio/client/stop",
		function() use($app) {
			$ws_handler = new WebsocketHandler("127.0.0.1", "audio_client_control");

			while( true ) {
				$rc = $ws_handler->send(0x12, null);
				usleep(10000);

				if( $rc == 1 ) break;
			}

			$app->setResponseMessage("ok");
			$app->setResponseResult(null);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	$app->post(
		"/audio/client/setVolume",
		function() use($app) {
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

			$app->setResponseMessage("ok");
			$app->setResponseResult(null);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);
?>
