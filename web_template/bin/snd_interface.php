<?php
	include_once "/opt/interm/public_html/api/api_websocket.php";

	class InterfaceHandler {
		const	TIME_SLEEP_RECONNECT	= 1;		// 1s
		const	TIME_SLEEP_CHECK_LOOP	= 10000;	// 10ms

		public 	$is_debug_print			= false;
		public 	$ws_handler 			= null;

		public	$num_playback_list		= 0;
		public	$num_capture_list		= 0;
		public	$num_volume_list		= 0;
		public	$arr_playback_list		= array();
		public	$arr_capture_list		= array();
		public	$arr_volume_list		= array();

		private $is_control_stop_list	= true;

		function __construct() {
			$options = getopt("v");
			if( isset($options["v"]) ) {
				$this->is_debug_print = true;
			}

			$this->ws_handler = new WebsocketHandler("127.0.0.1", "snd_interface", $this->is_debug_print);
			$this->ws_handler->set_route_to(WebsocketHandler::WS_ROUTE_TO_ALL);
			
			$json_info = json_decode(file_get_contents("/opt/interm/conf/config-common-info.json"))->snd_interface_info;
			if( isset($json_info->is_control_stop_list) ) {
				$this->is_control_stop_list = $json_info->is_control_stop_list;
			}
			$this->log(sprintf("constructor() control stop list status : %d\n", $this->is_control_stop_list));

			$this->log(sprintf("constructor() create instance\n"));

			return ;
		}

		function log($_message) {
			if( $this->is_debug_print ) {
				echo date('[Y-m-d H:i:s] ') . "SndInterface::" . $_message;
			}
			return ;
		}

		function set_playback_info($_module_info) {
			$this->num_playback_list++;
			$this->arr_playback_list[$_module_info->module]["uri"]    = $_module_info->uri;
			$this->arr_playback_list[$_module_info->module]["cmd_id"] = hexdec($_module_info->cmd_id);

			$this->log(sprintf("set_playback_info() [%s] set [%s] cmd_id [0x%02x] \n", 
								$_module_info->module, 
								$this->arr_playback_list[$_module_info->module]["uri"], 
								$this->arr_playback_list[$_module_info->module]["cmd_id"]));

			return ;
		}

		function set_capture_info($_module_info) {
			$this->num_capture_list++;
			$this->arr_capture_list[$_module_info->module]["uri"]    = $_module_info->uri;
			$this->arr_capture_list[$_module_info->module]["cmd_id"] = hexdec($_module_info->cmd_id);

			$this->log(sprintf("set_capture_info() [%s] set [%s] cmd_id [0x%02x] \n", 
								$_module_info->module, 
								$this->arr_capture_list[$_module_info->module]["uri"], 
								$this->arr_capture_list[$_module_info->module]["cmd_id"]));

			return ;

		}

		function set_volume_info($_module_info) {
			$this->num_volume_list++;
			$this->arr_volume_list[$_module_info->module]["uri"]    = $_module_info->uri;
			$this->arr_volume_list[$_module_info->module]["cmd_id"] = hexdec($_module_info->cmd_id);

			$this->log(sprintf("set_volume_info() [%s] set [%s] cmd_id [0x%02x] \n", 
								$_module_info->module, 
								$this->arr_volume_list[$_module_info->module]["uri"], 
								$this->arr_volume_list[$_module_info->module]["cmd_id"]));

			return ;

		}

		function run() {
			$this->log(sprintf("run() receive websocket data \n"));

			while( true ) {
				$ws_data = $this->ws_handler->recv();

				if( $this->ws_handler->is_term() ) {
					$this->log(sprintf("run() disconnected by peer, try reconnect [snd_interface]\n"));
					sleep(self::TIME_SLEEP_RECONNECT);

					$this->ws_handler = new WebsocketHandler("127.0.0.1", "snd_interface", $this->is_debug_print);
					$this->ws_handler->set_route_to(WebsocketHandler::WS_ROUTE_TO_ALL);

					continue;
				}

				if( $ws_data == false ) {
					continue;
				}

				$this->execute($ws_data);
			}

			return ;
		}

		function execute($_ws_data) {
			$this->log(sprintf("execute() cmd_id: [0x%02x], length: [%d], data: [%s] \n", $_ws_data["cmd_id"], $_ws_data["length"], $_ws_data["data"]));

			$cmd_id = $_ws_data["cmd_id"];
			$data   = $_ws_data["data"];

			switch( $cmd_id ) {
				case 0x01 :
					$this->func_snd_playback_handle($cmd_id, $data);
					break;

				case 0x02 :
					$this->func_snd_capture_handle($cmd_id, $data);
					break;

				case 0x10 :
					$this->func_snd_control_volume($cmd_id, $data);
					break;

				default :
					break;
			}

			return ;
		}

		function func_snd_playback_handle($_cmd_id, $_data) {
			$src_uri  = $_data;
			$ret_data = "{\"type\":{$_cmd_id}, \"data\":\"\"}";

			if( $this->is_control_stop_list == false ) {
				$this->log(sprintf("func_snd_playback_handle() control stop, termed.\n"));
				$this->ws_handler->send($_cmd_id, $ret_data);

				return ;
			}

			if( $this->num_playback_list == 0 ) {
				$this->log(sprintf("func_snd_playback_handle() target is null, termed.\n"));
				$this->ws_handler->send($_cmd_id, $ret_data);

				return ;
			}

			// #1 - driver를 아무도 사용하지 않을 때는 driver handle 동작을 하지 않음
			$current_use_pid = intval(shell_exec("fuser -fv /dev/snd/pcmC0D0p 2>/dev/null"));
			if( $current_use_pid == 0 ) {
				$this->log(sprintf("func_snd_playback_handle() playback driver unused, termed.\n"));
				$this->ws_handler->send($_cmd_id, $ret_data);

				return ;
			}

			// #2 - 같은 프로세스가 재시작 될때는 driver handle 동작을 하지 않음
			$current_module = trim(shell_exec("ps -p {$current_use_pid} -o comm="));
			$this->log(sprintf("func_snd_playback_handle() current in use on plyback driver : [%s]\n", $current_module));

			$is_current_use = false;
			foreach( $this->arr_playback_list as $module_name => $module_info ) {
				if( $module_info["uri"] == $src_uri && $module_name == $current_module ) {
					$is_current_use = true;

					break;
				}
			}

			if( $is_current_use ) {
				$this->log(sprintf("func_snd_playback_handle() current in use on plyback process matched, termed.\n"));
				$this->ws_handler->send($_cmd_id, $ret_data);

				return ;
			}


			// #3 - 해당 프로세스를 제외한 다른 프로세스의 driver handle 동작 시도
			foreach( $this->arr_playback_list as $module_name => $module_info ) {
				if( $module_info["uri"]== $src_uri ) {
					continue;
				}

				$target_uri = $module_info["uri"];
				$target_act = $module_info["cmd_id"];

				if( !isset($this->arr_playback_list[$module_name]["ws_info"]) 
					|| $this->arr_playback_list[$module_name]["ws_info"]->is_term() ) {
					$this->log(sprintf("func_snd_playback_handle() uri:[%s], create new websocket session.\n", $target_uri));
					$this->arr_playback_list[$module_name]["ws_info"] = new WebsocketHandler("127.0.0.1", $target_uri, $this->is_debug_print);
					$this->arr_playback_list[$module_name]["ws_info"]->set_route_to(WebsocketHandler::WS_ROUTE_TO_NATIVE_ONLY);
				}

				$this->arr_playback_list[$module_name]["ws_info"]->send($target_act, $ret_data);
			}

			// #4 - playback handler freed 상태까지 대기 후 허용 응답 
			$num_loop_cnt = 0;
			while( true ) {
				$current_use_pid = intval(shell_exec("fuser -fv /dev/snd/pcmC0D0p 2>/dev/null"));

				if( $current_use_pid == 0 ) {
					$this->log(sprintf("func_snd_playback_handle() loop:[%d], playback handler freed, termed.\n", $num_loop_cnt));
					break;
				}			
				
				$num_loop_cnt++;
				usleep(self::TIME_SLEEP_CHECK_LOOP);
			}

			$this->ws_handler->send($_cmd_id, $ret_data);

			return ;
		}

		function func_snd_capture_handle($_cmd_id, $_data) {
			$src_uri  = $_data;
			$ret_data = "{\"type\":{$_cmd_id}, \"data\":\"\"}";

			if( $this->is_control_stop_list == false ) {
				$this->log(sprintf("func_snd_capture_handle() control stop, termed.\n"));
				$this->ws_handler->send($_cmd_id, $ret_data);

				return ;
			}

			if( $this->num_capture_list == 0 ) {
				$this->log(sprintf("func_snd_capture_handle() target is null, termed.\n"));
				$this->ws_handler->send($_cmd_id, $ret_data);

				return ;
			}

			// #1 - driver를 아무도 사용하지 않을 때는 driver handle 동작을 하지 않음
			$current_use_pid = intval(shell_exec("fuser -fv /dev/snd/pcmC0D0c 2>/dev/null"));
			if( $current_use_pid == 0 ) {
				$this->log(sprintf("func_snd_capture_handle() playback driver unused, termed.\n"));
				$this->ws_handler->send($_cmd_id, $ret_data);

				return ;
			}

			// #2 - 같은 프로세스가 재시작 될때는 driver handle 동작을 하지 않음
			$current_module = trim(shell_exec("ps -p {$current_use_pid} -o comm="));
			$this->log(sprintf("func_snd_capture_handle() current in use on plyback driver : [%s]\n", $current_module));

			$is_current_use = false;
			foreach( $this->arr_capture_list as $module_name => $module_info ) {
				if( $module_info["uri"] == $src_uri && $module_name == $current_module ) {
					$is_current_use = true;

					break;
				}
			}

			if( $is_current_use ) {
				$this->log(sprintf("func_snd_capture_handle() current in use on plyback process matched, termed.\n"));
				$this->ws_handler->send($_cmd_id, $ret_data);

				return ;
			}


			// #3 - 해당 프로세스를 제외한 다른 프로세스의 driver handle 동작 시도
			foreach( $this->arr_capture_list as $module_name => $module_info ) {
				if( $module_info["uri"]== $src_uri ) {
					continue;
				}

				$target_uri = $module_info["uri"];
				$target_act = $module_info["cmd_id"];

				if( !isset($this->arr_capture_list[$module_name]["ws_info"]) 
					|| $this->arr_capture_list[$module_name]["ws_info"]->is_term() ) {
					$this->log(sprintf("func_snd_capture_handle() uri:[%s], create new websocket session.\n", $target_uri));
					$this->arr_capture_list[$module_name]["ws_info"] = new WebsocketHandler("127.0.0.1", $target_uri, $this->is_debug_print);
					$this->arr_capture_list[$module_name]["ws_info"]->set_route_to(WebsocketHandler::WS_ROUTE_TO_NATIVE_ONLY);
				}

				$this->arr_capture_list[$module_name]["ws_info"]->send($target_act, $ret_data);
			}

			// #4 - playback handler freed 상태까지 대기 후 허용 응답 
			$num_loop_cnt = 0;
			while( true ) {
				$current_use_pid = intval(shell_exec("fuser -fv /dev/snd/pcmC0D0c 2>/dev/null"));

				if( $current_use_pid == 0 ) {
					$this->log(sprintf("func_snd_capture_handle() loop:[%d], playback handler freed, termed.\n", $num_loop_cnt));
					break;
				}			
				
				$num_loop_cnt++;
				usleep(self::TIME_SLEEP_CHECK_LOOP);
			}

			$this->ws_handler->send($_cmd_id, $ret_data);

			return ;
		}

		function func_snd_control_volume($_cmd_id, $_data) {
			$ret_data = "{\"type\":{$_cmd_id}, \"data\":\"\"}";

			if( $this->num_volume_list == 0 ) {
				$this->log(sprintf("func_snd_control_volume() target is null, termed.\n"));
				$this->ws_handler->send($_cmd_id, $ret_data);

				return ;
			}

			foreach( $this->arr_volume_list as $module_name => $module_info ) {
				$target_uri = $module_info["uri"];
				$target_act = $module_info["cmd_id"];

				if( !isset($this->arr_volume_list[$module_name]["ws_info"]) 
					|| $this->arr_volume_list[$module_name]["ws_info"]->is_term() ) {
					$this->log(sprintf("func_snd_capture_handle() uri:[%s], create new websocket session.\n", $target_uri));
					$this->arr_volume_list[$module_name]["ws_info"] = new WebsocketHandler("127.0.0.1", $target_uri, $this->is_debug_print);
					$this->arr_volume_list[$module_name]["ws_info"]->set_route_to(WebsocketHandler::WS_ROUTE_TO_NATIVE_ONLY);
				}

				$this->arr_volume_list[$module_name]["ws_info"]->send($target_act, $_data);
			}


			$this->ws_handler->send($_cmd_id, $ret_data);

			return ;
		}
	}

	cli_set_process_title("snd_interface");

	$sndif_handler = new InterfaceHandler();

	$json_info = json_decode(file_get_contents("/opt/interm/conf/config-common-info.json"))->snd_interface_info;

	foreach( $json_info as $interface_info => $module_list ) {
		foreach( $module_list as $module_info ) {
			switch( $interface_info ) {
				case "playback_stop_list"  : $sndif_handler->set_playback_info($module_info);	break; 
				case "capture_stop_list"   : $sndif_handler->set_capture_info($module_info);	break;
				case "control_volume_list" : $sndif_handler->set_volume_info($module_info);		break;
				default : break;
			}
		}
	}
	$sndif_handler->log(sprintf("set_playback_info() count : %d\n", $sndif_handler->num_playback_list));
	$sndif_handler->log(sprintf("set_capture_info()  count : %d\n", $sndif_handler->num_capture_list));
	$sndif_handler->log(sprintf("set_volume_info()   count : %d\n", $sndif_handler->num_volume_list));


	$sndif_handler->run();
?>
