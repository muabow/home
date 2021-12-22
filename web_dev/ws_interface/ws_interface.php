<?php
	set_time_limit(0);
	ob_implicit_flush();

	include_once "ws_if_func.php";

	class WebsocketInterface {
		// debug message print flag
		const DEBUG_PRINT_MSG		= false;
		const TRACE_PRINT_MSG		= false;
		const PATH_DEBUG_PRINT		= "/tmp/debug_wsif";
		const STR_URI_WSCIF			= "wsc_inteface";

		// use the default configuration file when there is no configuration file input.
		const PORT_INSIDE_CONNECT	= 2100;
		const PORT_OUTSIDE_CONNECT	= 2200;
		const SOCKET_BACK_LOG		= 512;

		// maximum amount of clients that can be connected at one time
		const WS_MAX_CLIENTS 		= 1024; // 100 -> 1024, Increase connectivity capacity

		// maximum amount of clients that can be connected at one time on the same IP v4 address
		const WS_MAX_CLIENTS_PER_IP = 100;  // 15 -> 100, for internal process

		// amount of seconds a client has to send data to the server, before a ping request is sent to the client,
		// if the client has not completed the opening handshake, the ping request is skipped and the client connection is closed
		const WS_TIMEOUT_RECV 		= 10;

		// amount of seconds a client has to reply to a ping request, before the client connection is closed
		const WS_TIMEOUT_PONG 		= 5;

		// the maximum length, in bytes, of a frame's payload data (a message consists of 1 or more frames), this is also internally limited to 2,147,479,538
		const WS_MAX_FRAME_PAYLOAD_RECV		= 1000000; // 100000(0.09Mb) -> 1000000(0.9Mb)

		// the maximum length, in bytes, of a message's payload data, this is also internally limited to 2,147,483,647
		const WS_MAX_MESSAGE_PAYLOAD_RECV	= 5000000; // 500000(0.47Mb) -> 5000000(4.76Mb)

		const WS_FIN	= 128;
		const WS_MASK	= 128;

		const WS_OPCODE_CONTINUATION				= 0;
		const WS_OPCODE_TEXT						= 1;
		const WS_OPCODE_BINARY						= 2;
		const WS_OPCODE_CLOSE 						= 8;
		const WS_OPCODE_PING 						= 9;
		const WS_OPCODE_PONG 						= 10;

		const WS_PAYLOAD_LENGTH_16					= 126;
		const WS_PAYLOAD_LENGTH_63 					= 127;

		const WS_READY_STATE_CONNECTING				= 0;
		const WS_READY_STATE_OPEN 					= 1;
		const WS_READY_STATE_CLOSING 				= 2;
		const WS_READY_STATE_CLOSED 				= 3;

		const WS_STATUS_NORMAL_CLOSE 				= 1000;
		const WS_STATUS_GONE_AWAY 					= 1001;
		const WS_STATUS_PROTOCOL_ERROR		 		= 1002;
		const WS_STATUS_UNSUPPORTED_MESSAGE_TYPE 	= 1003;
		const WS_STATUS_MESSAGE_TOO_BIG 			= 1004;

		const WS_STATUS_TIMEOUT 					= 3000;

		const WS_DATA_TYPE_PS						= 0;
		const WS_DATA_TYPE_WS						= 1;

		const EVENT_TYPE_WS							= 0;	// ws_session
		const EVENT_TYPE_PS							= 1;	// ps_socket/ps_session

		const DATA_TYPE_PS_SOCKET					= 0;
		const DATA_TYPE_PS_SESSION					= 1;
		const DATA_TYPE_WS_SOCKET					= 2;
		const DATA_TYPE_WS_SESSION					= 3;

		const WS_DFLT_CONFIG_PATH					= "/opt/interm/conf/ws_router.xml";

		/*
		 $this->arr_client_info[ integer ClientID ] = array(
		 0 => resource  Socket,                            // client socket
		 1 => string    MessageBuffer,                     // a blank string when there's no incoming frames
		 2 => integer   ReadyState,                        // between 0 and 3
		 3 => integer   LastRecvTime,                      // set to time() when the client is added
		 4 => int/false PingSentTime,                      // false when the server is not waiting for a pong
		 5 => int/false CloseStatus,                       // close status that ws_on_close_handler() will be called with
		 6 => integer   IPv4,                              // client's IP stored as a signed long, retrieved from ip2long()
		 7 => int/false FramePayloadDataLength,            // length of a frame's payload data, reset to false when all frame data has been read (cannot reset to 0, to allow reading of mask key)
		 8 => integer   FrameBytesRead,                    // amount of bytes read for a frame, reset to 0 when all frame data has been read
		 9 => string    FrameBuffer,                       // joined onto end as a frame's data comes in, reset to blank string when all frame data has been read
		 10 => integer  MessageOpcode,                     // stored by the first frame for fragmented messages, default value is 0
		 11 => integer  MessageBufferLength,               // the payload data length of MessageBuffer
		 12 => string	URI,							   // URI name are stored, For example, /test
		 13 => bool		process type,					   // process type,  0: ws, 1: process(socket/session)
		 14 => integer  session type, 					   // session type, -1: ws, 0: Continuous socket(session), 0>: Packet socket(csocket)
		 15 => object	extend type,
		 16 => bool		websocket session interface flag   // normal websocket: 0, websocket session interface: 1
		 )

		 $fds_reads[ integer ClientID ] = resource Socket  // this one-dimensional array is used for socket_select()
		 // $fds_reads[ 0 ] is the socket listening for incoming client connections
		 // $fds_reads[ 1 ] is the socket listening for incoming binary data

		 $cnt_connected_client               = integer ClientCount  // amount of clients currently connected
		 $arr_cnt_connected_ip[ integer IP ] = integer ClientCount  // amount of clients connected per IP v4 address
		*/

		public $is_debug_print			= false;
		public $is_data_print			= false;

		public $arr_client_info			= array();
		public $arr_event_list			= array();

		public $arr_cnt_connected_ip	= array();
		public $cnt_connected_client 	= 0;

		public $fds_reads		= array();

		public $cnt_route_list  = 0;
		public $arr_route_list	= array();

		public $arr_if_info		= array("host"		   => 'localhost',
										"inside_port"  => self::PORT_INSIDE_CONNECT,
										"outside_port" => self::PORT_OUTSIDE_CONNECT,
										"back_log"	   => self::SOCKET_BACK_LOG);


		function __construct() {
			$this->log(sprintf("WebsocketInterface::Start the websocket interface \n"));

			$this->init_interface_info();
			$this->log(sprintf("WebsocketInterface::construct init_interface_info() success \n"));

			if( !$this->init_socket_server(0, $this->arr_if_info["outside_port"]) ) {
				$this->log(sprintf("WebsocketInterface::construct init_socket_server() failed \n"));

				return false;
			}
			$this->log(sprintf("WebsocketInterface::construct init_socket_server() outside_port success \n"));

			if( !$this->init_socket_server(1, $this->arr_if_info["inside_port"]) ) {
				$this->log(sprintf("WebsocketInterface::construct init_socket_server() failed \n"));

				return false;
			}
			$this->log(sprintf("WebsocketInterface::construct init_socket_server() inside_port success \n"));

			return ;
		}

		function init_interface_info() {
			$default_config_path = self::WS_DFLT_CONFIG_PATH;

			$options = getopt("f:dv");
			if( isset($options["f"]) ) {
				$default_config_path = $options["f"];
			}
			if( isset($options["d"]) ) {
				$this->is_debug_print = true;
			}
			if( isset($options["v"]) ) {
				$this->is_data_print = true;
			}

			if( !file_exists($default_config_path) ) {
				$this->log(sprintf("WebsocketInterface::init_interface_info() config file not found : [%s]\n", $default_config_path));
				$this->log(sprintf("WebsocketInterface::init_interface_info() use default interface parameter\n"));

			} else {
				$this->log(sprintf("WebsocketInterface::init_interface_info() config file loaded : [%s]\n", $default_config_path));

				$xmlData = simplexml_load_string(file_get_contents($default_config_path));
				$this->arr_if_info["outside_port"] = intval($xmlData->server->http_port);
				$this->arr_if_info["inside_port"]  = intval($xmlData->server->inner_port);
				$this->arr_if_info["back_log"]     = intval($xmlData->server->back_log);

				$this->arr_route_list = json_decode(json_encode($xmlData->routing_key), true);
			}

			$this->log(sprintf("WebsocketInterface::init_interface_info() interface information \n"));
			$this->log(sprintf(" # [%-20s] => [%4d]\n", "outside_port", $this->arr_if_info["outside_port"]));
			$this->log(sprintf(" # [%-20s] => [%4d]\n", "inside_port", 	$this->arr_if_info["inside_port"]));
			$this->log(sprintf(" # [%-20s] => [%4d]\n", "back_log",   	$this->arr_if_info["back_log"]));

			$this->log(sprintf("WebsocketInterface::init_interface_info() route_table information \n"));
			$this->cnt_route_list = count($this->arr_route_list);

			if( $this->cnt_route_list > 0 ) {
				foreach( $this->arr_route_list as $uri => $port ) {
					$this->log(sprintf(" # [%-20s] => [%4d] \n", $uri, $port));
				}

			} else {
				$this->log(sprintf(" # [%-20s] \n", "Data does not exist."));
			}

			return ;
		}

		function init_socket_server($_index, $_port) {
			if( isset($this->fds_reads[$_index]) ) {
				$this->log(sprintf("WebsocketInterface::init_socket_server() already set socket fd \n"));
				return false;
			}

			if( !$this->fds_reads[$_index] = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) ) {
				$this->log(sprintf("WebsocketInterface::init_socket_server() socket_create failed : idx[%02d]/[%02d] %s\n",
					$_index, socket_last_error(), socket_strerror(socket_last_error())));
				return false;
			}


			if( !socket_set_option($this->fds_reads[$_index], SOL_SOCKET, SO_REUSEADDR, 1) ) {
				$this->log(sprintf("WebsocketInterface::init_socket_server() socket_set_option failed : idx[%02d]/[%02d] %s\n",
					$_index, socket_last_error(), socket_strerror(socket_last_error())));
				socket_close($this->fds_reads[$_index]);
				return false;
			}

			if( !socket_set_option($this->fds_reads[$_index], SOL_SOCKET, TCP_NODELAY, 1) ) {
				$this->log(sprintf("WebsocketInterface::init_socket_server() socket_set_option failed : idx[%02d]/[%02d] %s\n",
					$_index, socket_last_error(), socket_strerror(socket_last_error())));
				socket_close($this->fds_reads[$_index]);
				return false;
			}

			if( !socket_bind($this->fds_reads[$_index], $this->arr_if_info["host"], $_port) ) {
				$this->log(sprintf("WebsocketInterface::init_socket_server() socket_bind failed : idx[%02d]/[%02d] %s\n",
					$_index, socket_last_error(),  socket_strerror(socket_last_error())));
				socket_close($this->fds_reads[$_index]);
				return false;
			}


			if( !socket_listen($this->fds_reads[$_index], $this->arr_if_info["back_log"]) ) {
				$this->log(sprintf("WebsocketInterface::init_socket_server() socket_listen failed : idx[%02d]/[%02d] %s\n",
					$_index, socket_last_error(),  socket_strerror(socket_last_error())));
				socket_close($this->fds_reads[$_index]);
				return false;
			}


			return true;
		}

		function run() {
			$this->log(sprintf("WebsocketInferface::run() initialize completed. start interface server. \n"));

			$fds_writes  = array();
			$fds_except  = array();

			$time_next_idle_check = time() + 1;
			while( isset($this->fds_reads[0]) ) {
				$fds_changed = $this->fds_reads;

				if( ($rc = socket_select($fds_changed, $fds_writes, $fds_except, 1)) < 0 ) {
					$this->log(sprintf("WebsocketInferface::run() socket_select failed : [%02d] %s \n",
								socket_last_error(), socket_strerror(socket_last_error())));

					return false;

				} else if( $rc > 0 ) {
					foreach( $fds_changed as $clientID => $socket ) {
						switch( $clientID ) {
							case 0 :
							case 1 :
								$this->on_connection_handler($clientID);
								break ;

							default :
								$this->on_event_handler($clientID, $socket);
								break;
						}
					}
				}

				if( time() >= $time_next_idle_check ) {
					$this->check_idle_clients();
					$time_next_idle_check = time() + 1;
				}
			} // end of while

			return true;
		}

		function stop() {
			// check if server is not running
			if( !isset($this->fds_reads[0]) ) {
				return false;
			}

			// close all client connections
			foreach( $this->arr_client_info as $clientID => $client ) {
				// if the client's opening handshake is complete, tell the client the server is 'going away'
				if( $client[2] != self::WS_READY_STATE_CONNECTING ) {
					$this->send_client_close($clientID, self::WS_STATUS_GONE_AWAY);
				}
				socket_close($client[0]);
			}

			// close the socket which listens for incoming clients
			socket_close($this->fds_reads[0]);
			socket_close($this->fds_reads[1]);

			// reset variables
			$this->fds_reads			= array();
			$this->arr_client_info		= array();
			$this->arr_cnt_connected_ip	= array();
			$this->arr_route_list		= array();
			$this->cnt_connected_client	= 0;

			return true;
		}

		// sub method::run()
		function on_connection_handler($_clientID) {
			if( ($client = socket_accept($this->fds_reads[$_clientID])) !== false ) {
				$clientIP = '';
				$result   = socket_getpeername($client, $clientIP);
				$clientIP = ip2long($clientIP);

				if( ($result !== false)	&& ($this->cnt_connected_client < self::WS_MAX_CLIENTS)	&& (!isset($this->arr_cnt_connected_ip[$clientIP])
					|| $this->arr_cnt_connected_ip[$clientIP] < self::WS_MAX_CLIENTS_PER_IP) ) {

					socket_set_option($this->fds_reads[$_clientID], SOL_SOCKET, TCP_NODELAY, 1);
					$this->add_client($client, $clientIP, $_clientID);

				} else {
					socket_close($client);
				}
			}

			return ;
		}

		// sub method::run()
		function on_event_handler($_clientID, $_socket) {
			$client_ps_type = $this->get_client_ps_type($_clientID);

			switch( $client_ps_type ) {
				case self::EVENT_TYPE_WS :
					$this->_recv_client_ws_data($_clientID, $_socket);
					break;

				case self::EVENT_TYPE_PS :
					$this->_recv_client_ps_data($_clientID, $_socket);
					break;
			}

			return ;
		}

		// sub method::on_event_handler(), add websocket client session
		function _recv_client_ws_data($_clientID, $_socket) {
			$buffer = '';
			$bytes  = @socket_recv($_socket, $buffer, 4096, 0);

			if( $bytes === false ) {
				// error on recv, remove client socket (will check to send close frame)
				$this->log("socket_recv() client_ws_data failed [{$_clientID}] : [" . socket_last_error() . "] ". socket_strerror(socket_last_error()) . "\n");

				$this->send_client_close($_clientID, self::WS_STATUS_PROTOCOL_ERROR);

			} else if( $bytes > 0 ) {
				// process handshake or frame(s)
				if( !$this->process_client($_clientID, $buffer, $bytes) ) {
					$this->send_client_close($_clientID, self::WS_STATUS_PROTOCOL_ERROR);
				}

			} else {
				// 0 bytes received from client, meaning the client closed the TCP connection
				$this->remove_client($_clientID);
			}

			return ;
		}

		function _recv_client_ps_data($_clientID, $_socket) {
			$wsif_parser = new WSIF_DataParser();

			$size_common_header = $wsif_parser->get_size_common_header();

			if( ($bytes = @socket_recv($_socket, $header_data, $size_common_header, MSG_WAITALL)) === false ) {
				$this->log("socket_recv() header_data failed [{$_clientID}] : [" . socket_last_error() . "] ". socket_strerror(socket_last_error()) . "\n");
				$this->send_client_close($_clientID, self::WS_STATUS_PROTOCOL_ERROR);

				return ;

			} else if( $bytes == 0 ) {
				$this->log("(header_data) disconnected by peer, remove client : [{$_clientID}]\n");
				$this->remove_client($_clientID);

				return ;
			}

			if( strpos($header_data, "POST") !== false || strpos($header_data, "GET") !== false ) {
				$this->log(sprintf("\033[41merror header data : type [ps], client [%d/%s]\033[0m\n", $_clientID, $header_data));
				$this->remove_client($_clientID);

				return ;
			}

			if( !$wsif_parser->parse_data(self::WS_DATA_TYPE_PS, $header_data) ) {
				$uri_name = $this->get_client_uri($_clientID);
				$this->log(sprintf("\033[41mparse invalid header data : type [ps], client [%d/%s]\033[0m\n", $_clientID, $uri_name));

				if( $uri_name != self::STR_URI_WSCIF ) {
					$this->remove_client($_clientID);
				}

				return ;
			}

			$size_common_data = $wsif_parser->get_size_common_data();

			if( $size_common_data == 16777248 ) { // overflow
				$this->log(sprintf("\033[41msocket_recv() invalid header, data length : [%d]\033[0m\n", $size_common_data));
				var_dump($wsif_parser->common_header);
				
				$this->remove_client($_clientID);
				return ;
			}

			if( ($bytes = socket_recv($_socket, $common_data, $size_common_data, MSG_WAITALL)) === false ) {
				$this->log("socket_recv() common_data failed [{$_clientID}] : [" . socket_last_error() . "] ". socket_strerror(socket_last_error()) . "\n");
				$this->send_client_close($_clientID, self::WS_STATUS_PROTOCOL_ERROR);

				return ;

			} else if( $bytes == 0 ) {
				$this->log("(common_data) disconnected by peer, remove client : [{$_clientID}]\n");
				$this->remove_client($_clientID);

				return ;
			}

			if( $bytes != $size_common_data ) {
				$this->log(sprintf("\033[41msocket_recv() not matched data size : [%d/%d]\033[0m\n", $bytes, $size_common_data));
				return ;
			}

			// extend flag on: ps_session
			$is_regist   = false;

			if( $wsif_parser->get_header("is_extend") ) {
				if( ($arr_ext_header = $wsif_parser->parse_ext_data($common_data)) == false ) {
					$this->log(sprintf("\033[41mparse_ext_data() error : [%s/%s]\033[0m\n", $this->get_client_ip_addr($_clientID), $this->get_client_uri($_clientID)));
					$bytes = @socket_recv($_socket, $dump_data, 10240, 0);
					$this->log(sprintf("flush socket data : %d\n", $bytes));

					return ;
				}

				foreach( $arr_ext_header as $header ) {
					switch( $header["tag"] ) {
						case 0x00 :
							$wsif_parser->set_header("uri", $header["value"]);
							break;

						case 0x01 :	// uri_regist
							$route_uri = $wsif_parser->get_header("uri");
							$this->set_client_route_key($_clientID, $route_uri);
							$this->set_client_route_wscif($_clientID, false);

							$is_regist = true;
							break;

						case 0x02 : // wscif_regist
							$route_uri = $wsif_parser->get_header("uri");
							$this->set_client_route_key($_clientID, $route_uri);
							$this->set_client_route_wscif($_clientID, true);

							$is_regist = true;
							break;

						case 0x10 :
							$route_uri = $this->get_client_uri($_clientID);
							$wsif_parser->set_header("uri",    $route_uri);
							$wsif_parser->set_header("data",   $header["value"]);
							$wsif_parser->set_header("length", $header["length"]);
							break;
					}
				}

			} else {
				if( in_array($wsif_parser->get_header("ps_type"), $this->arr_route_list) ) {
					$route_uri = array_keys($this->arr_route_list, $wsif_parser->get_header("ps_type"))[0];

				} else {
					$this->remove_client($_clientID);
					$this->log("Not found client ID : [{$_clientID}], route port : [{$wsif_parser->get_header("ps_type")}]\n");

					return ;
				}

				$wsif_parser->set_header("uri",      $route_uri);
				$wsif_parser->set_header("data",     $common_data);
			}

			$wsif_parser->set_header("clientID", $_clientID);
			$this->set_client_session_type($_clientID, $wsif_parser->get_header("ps_type"));
			$this->set_client_extend_type($_clientID,  $wsif_parser->get_header("is_extend"));

			if( $is_regist ) {
				$str_route_wscif = "";
				if( $this->get_client_route_wscif($_clientID) ) {
					$str_route_wscif = ", route to wscif";
				}
				$this->log("Register the client URI : [{$_clientID}]/{$route_uri}{$str_route_wscif}\n");

				if(    $wsif_parser->get_header("length") == 0
					|| strcmp($wsif_parser->get_header("data"), '') == 0 ) {
					return ;
				}
			}

			$header = $wsif_parser->get_header_info();

			// send to process socket
			$data = $wsif_parser->get_ws_data(self::DATA_TYPE_PS_SOCKET);
			$this->send_ws_data(self::DATA_TYPE_PS_SOCKET, $header, $data);

			// send to process session
			$data = $wsif_parser->get_ws_data(self::DATA_TYPE_PS_SESSION);
			$this->send_ws_data(self::DATA_TYPE_PS_SESSION, $header, $data);

			// send to websocket socket/session
			$arr_data = array($wsif_parser->get_ws_data(self::DATA_TYPE_WS_SOCKET),
					          $wsif_parser->get_ws_data(self::DATA_TYPE_WS_SESSION));
			$this->send_ws_data(self::DATA_TYPE_WS_SESSION, $header, $arr_data);

			if( !$wsif_parser->get_header("is_extend") ) {
				$this->remove_client($_clientID);
			}

			return ;
		}


		// system functions
		// client timeout functions
		function check_idle_clients() {
			$time = time();

			foreach( $this->arr_client_info as $clientID => $client ) {
				if( $client[13] == 1 ) { // type : process socket
					continue;
				}

				if( $client[2] != self::WS_READY_STATE_CLOSED ) {
					// client ready state is not closed
					if( $client[4] !== false ) {
						// ping request has already been sent to client, pending a pong reply
						if( $time >= $client[4] + self::WS_TIMEOUT_PONG ) {
							// client didn't respond to the server's ping request in self::WS_TIMEOUT_PONG seconds
							$this->send_client_close($clientID, self::WS_STATUS_TIMEOUT);
							$this->remove_client($clientID);
						}

					} else if( $time >= $client[3] + self::WS_TIMEOUT_RECV ) {
						// last data was received >= self::WS_TIMEOUT_RECV seconds ago
						if( $client[2] != self::WS_READY_STATE_CONNECTING ) {
							// client ready state is open or closing
							$this->arr_client_info[$clientID][4] = time();
							$this->send_client_message($clientID, self::WS_OPCODE_PING, '');

						} else {
							// client ready state is connecting
							$this->remove_client($clientID);
						}
					}
				}
			}

			return ;
		}

		// client existence functions
		function add_client($_socket, $_clientIP, $_clientID) {
			// increase amount of clients connected
			$this->cnt_connected_client++;

			// increase amount of clients connected on this client's IP
			if( isset($this->arr_cnt_connected_ip[$_clientIP]) ) {
				$this->arr_cnt_connected_ip[$_clientIP]++;

			} else {
				$this->arr_cnt_connected_ip[$_clientIP] = 1;
			}

			// fetch next client ID
			$clientID = $this->get_next_clientID();

			// store initial client data
			$this->arr_client_info[$clientID] = array(
														$_socket,							// 0 : Socket
														'',									// 1 : MessageBuffer
														self::WS_READY_STATE_CONNECTING,	// 2 : ReadyState
														time(),								// 3 : LastRecvTime
														false,								// 4 : PingSendTime
														0, 									// 5 : CloseStatus
														$_clientIP,							// 6 : IPv4
														false,								// 7 : FramePayloadDataLength
														0, 									// 8 : FrameBytesRead
														'', 								// 9 : FraemBuffer
														0, 									// 10: MessageOpcode
														0, 									// 11: MessageBufferLength
														'', 								// 12: URI
														$_clientID, 						// 13: process type
														-1,									// 14: session type
														new WSIF_ExtendInfo(),				// 15: extend type
														false								// 16: websocket session interface flag
													);

			// store socket - used for socket_select()
			$this->fds_reads[$clientID] = $_socket;

			return ;
		}

		function remove_client($_clientID) {
			// fetch close status (which could be false), and call ws_on_close_handler
			$closeStatus = $this->arr_client_info[$_clientID][5];

			if( array_key_exists('close', $this->arr_event_list) ) {
				foreach ($this->arr_event_list['close'] as $func) {
					$func($_clientID, $closeStatus);
				}
			}

			// close socket
			$socket = $this->get_client_socket($_clientID);
			socket_close($socket);

			// decrease amount of clients connected on this client's IP
			$clientIP = $this->arr_client_info[$_clientID][6];
			if ($this->arr_cnt_connected_ip[$clientIP] > 1) {
				$this->arr_cnt_connected_ip[$clientIP]--;

			} else {
				unset($this->arr_cnt_connected_ip[$clientIP]);
			}

			// decrease amount of clients connected
			$this->cnt_connected_client--;

			// remove socket and client data from arrays
			unset($this->fds_reads[$_clientID], $this->arr_client_info[$_clientID]);

			return ;
		}

		// client read functions
		function process_client($_clientID, &$_buffer, $_bufferLength) {
			if( $this->arr_client_info[$_clientID][2] == self::WS_READY_STATE_OPEN ) {
				// handshake completed
				$result = $this->build_client_frame($_clientID, $_buffer, $_bufferLength);

			} else if( $this->arr_client_info[$_clientID][2] == self::WS_READY_STATE_CONNECTING ) {
				// handshake not completed
				$result = $this->process_client_handshake($_clientID, $_buffer);

				if( $result ) {
					$this->arr_client_info[$_clientID][2] = self::WS_READY_STATE_OPEN;

					if( array_key_exists('open', $this->arr_event_list) ) {
						foreach( $this->arr_event_list['open'] as $func ) {
							$func($_clientID);
						}
					}
				}

			} else {
				// ready state is set to closed
				$result = false;
			}

			return $result;
		}

		function build_client_frame($_clientID, &$_buffer, $_bufferLength) {
			// increase number of bytes read for the frame, and join buffer onto end of the frame buffer
			$this->arr_client_info[$_clientID][8] += $_bufferLength;
			$this->arr_client_info[$_clientID][9] .= $_buffer;

			// check if the length of the frame's payload data has been fetched, if not then attempt to fetch it from the frame buffer
			if( $this->arr_client_info[$_clientID][7] !== false || $this->check_size_client_frame($_clientID) == true ) {
				// work out the header length of the frame
				$headerLength = ($this->arr_client_info[$_clientID][7] <= 125 ? 0 : ($this->arr_client_info[$_clientID][7] <= 65535 ? 2 : 8)) + 6;

				// check if all bytes have been received for the frame
				$frameLength = $this->arr_client_info[$_clientID][7] + $headerLength;
				if( $this->arr_client_info[$_clientID][8] >= $frameLength ) {
					// check if too many bytes have been read for the frame (they are part of the next frame)
					$nextFrameBytesLength = $this->arr_client_info[$_clientID][8] - $frameLength;

					if( $nextFrameBytesLength > 0 ) {
						$this->arr_client_info[$_clientID][8] -= $nextFrameBytesLength;
						$nextFrameBytes = substr($this->arr_client_info[$_clientID][9], $frameLength);
						$this->arr_client_info[$_clientID][9] = substr($this->arr_client_info[$_clientID][9], 0, $frameLength);
					}

					// process the frame
					$result = $this->process_client_frame($_clientID);

					// check if the client wasn't removed, then reset frame data
					if( isset($this->arr_client_info[$_clientID]) ) {
						$this->arr_client_info[$_clientID][7] = false;
						$this->arr_client_info[$_clientID][8] = 0;
						$this->arr_client_info[$_clientID][9] = '';
					}

					// if there's no extra bytes for the next frame, or processing the frame failed, return the result of processing the frame
					if( $nextFrameBytesLength <= 0 || !$result ) {
						return $result;
					}

					// build the next frame with the extra bytes
					return $this->build_client_frame($_clientID, $nextFrameBytes, $nextFrameBytesLength);
				}
			}

			return true;
		}

		function check_size_client_frame($_clientID) {
			// check if at least 2 bytes have been stored in the frame buffer
			if( $this->arr_client_info[$_clientID][8] > 1 ) {
				// fetch payload length in byte 2, max will be 127
				$payloadLength = ord(substr($this->arr_client_info[$_clientID][9], 1, 1)) & 127;

				if( $payloadLength <= 125 ) {
					// actual payload length is <= 125
					$this->arr_client_info[$_clientID][7] = $payloadLength;

				} else if( $payloadLength == 126 ) {
					// actual payload length is <= 65,535
					if( substr($this->arr_client_info[$_clientID][9], 3, 1) !== false ) {
						// at least another 2 bytes are set
						$payloadLengthExtended = substr($this->arr_client_info[$_clientID][9], 2, 2);
						$array = unpack('na', $payloadLengthExtended);
						$this->arr_client_info[$_clientID][7] = $array['a'];
					}

				} else {
					// actual payload length is > 65,535
					if( substr($this->arr_client_info[$_clientID][9], 9, 1) !== false ) {
						// at least another 8 bytes are set
						$payloadLengthExtended = substr($this->arr_client_info[$_clientID][9], 2, 8);

						// check if the frame's payload data length exceeds 2,147,483,647 (31 bits)
						// the maximum integer in PHP is "usually" this number. More info: http://php.net/manual/en/language.types.integer.php
						$payloadLengthExtended32_1 = substr($payloadLengthExtended, 0, 4);
						$array = unpack('Na', $payloadLengthExtended32_1);

						if( $array['a'] != 0 || ord(substr($payloadLengthExtended, 4, 1)) & 128 ) {
							$this->send_client_close($_clientID, self::WS_STATUS_MESSAGE_TOO_BIG);

							return false;
						}

						// fetch length as 32 bit unsigned integer, not as 64 bit
						$payloadLengthExtended32_2 = substr($payloadLengthExtended, 4, 4);
						$array = unpack('Na', $payloadLengthExtended32_2);

						// check if the payload data length exceeds 2,147,479,538 (2,147,483,647 - 14 - 4095)
						// 14 for header size, 4095 for last recv() next frame bytes
						if( $array['a'] > 2147479538 ) {
							$this->send_client_close($_clientID, self::WS_STATUS_MESSAGE_TOO_BIG);

							return false;
						}

						// store frame payload data length
						$this->arr_client_info[$_clientID][7] = $array['a'];
					}
				}

				// check if the frame's payload data length has now been stored
				if( $this->arr_client_info[$_clientID][7] !== false ) {

					// check if the frame's payload data length exceeds self::WS_MAX_FRAME_PAYLOAD_RECV
					if( $this->arr_client_info[$_clientID][7] > self::WS_MAX_FRAME_PAYLOAD_RECV ) {
						$this->arr_client_info[$_clientID][7] = false;
						$this->send_client_close($_clientID, self::WS_STATUS_MESSAGE_TOO_BIG);

						return false;
					}

					// check if the message's payload data length exceeds 2,147,483,647 or self::WS_MAX_MESSAGE_PAYLOAD_RECV
					// doesn't apply for control frames, where the payload data is not internally stored
					$controlFrame = (ord(substr($this->arr_client_info[$_clientID][9], 0, 1)) & 8) == 8;
					if( !$controlFrame ) {
						$newMessagePayloadLength = $this->arr_client_info[$_clientID][11] + $this->arr_client_info[$_clientID][7];

						if( $newMessagePayloadLength > self::WS_MAX_MESSAGE_PAYLOAD_RECV || $newMessagePayloadLength > 2147483647 ) {
							$this->send_client_close($_clientID, self::WS_STATUS_MESSAGE_TOO_BIG);

							return false;
						}
					}

					return true;
				}
			}

			return false;
		}

		function process_client_frame($_clientID) {
			// store the time that data was last received from the client
			$this->arr_client_info[$_clientID][3] = time();

			// fetch frame buffer
			$buffer = &$this->arr_client_info[$_clientID][9];

			// check at least 6 bytes are set (first 2 bytes and 4 bytes for the mask key)
			if( substr($buffer, 5, 1) === false ) {
				return false;
			}

			// fetch first 2 bytes of header
			$octet0 = ord(substr($buffer, 0, 1));
			$octet1 = ord(substr($buffer, 1, 1));

			$fin    = $octet0 & self::WS_FIN;
			$opcode = $octet0 & 15;

			$mask = $octet1 & self::WS_MASK;

			if( !$mask ) {
				return false;
			}
			// close socket, as no mask bit was sent from the client

			// fetch byte position where the mask key starts
			$seek = $this->arr_client_info[$_clientID][7] <= 125 ? 2 : ($this->arr_client_info[$_clientID][7] <= 65535 ? 4 : 10);

			// read mask key
			$maskKey = substr($buffer, $seek, 4);

			$array   = unpack('Na', $maskKey);
			$maskKey = $array['a'];
			$maskKey = array($maskKey>>24, ($maskKey>>16) & 255, ($maskKey>>8) & 255, $maskKey & 255);

			$seek += 4;

			// decode payload data
			if( substr($buffer, $seek, 1) !== false ) {
				$data = str_split(substr($buffer, $seek));

				foreach( $data as $key => $byte ) {
					$data[$key] = chr(ord($byte) ^ ($maskKey[$key % 4]));
				}
				$data = implode('', $data);

			} else {
				$data = '';
			}

			// check if this is not a continuation frame and if there is already data in the message buffer
			if( $opcode != self::WS_OPCODE_CONTINUATION && $this->arr_client_info[$_clientID][11] > 0 ) {
				// clear the message buffer
				$this->arr_client_info[$_clientID][11] = 0;
				$this->arr_client_info[$_clientID][1] = '';
			}

			// check if the frame is marked as the final frame in the message
			if( $fin == self::WS_FIN ) {
				// check if this is the first frame in the message
				if( $opcode != self::WS_OPCODE_CONTINUATION ) {
					// process the message
					return $this->process_client_message($_clientID, $opcode, $data, $this->arr_client_info[$_clientID][7]);

				} else {
					// increase message payload data length
					$this->arr_client_info[$_clientID][11] += $this->arr_client_info[$_clientID][7];

					// push frame payload data onto message buffer
					$this->arr_client_info[$_clientID][1] .= $data;

					// process the message
					$result = $this->process_client_message($_clientID, $this->arr_client_info[$_clientID][10], $this->arr_client_info[$_clientID][1], $this->arr_client_info[$_clientID][11]);

					// check if the client wasn't removed, then reset message buffer and message opcode
					if( isset($this->arr_client_info[$_clientID]) ) {
						$this->arr_client_info[$_clientID][1] = '';
						$this->arr_client_info[$_clientID][10] = 0;
						$this->arr_client_info[$_clientID][11] = 0;
					}

					return $result;
				}

			} else {
				// check if the frame is a control frame, control frames cannot be fragmented
				if( $opcode & 8 ) {
					return false;
				}

				// increase message payload data length
				$this->arr_client_info[$_clientID][11] += $this->arr_client_info[$_clientID][7];

				// push frame payload data onto message buffer
				$this->arr_client_info[$_clientID][1] .= $data;

				// if this is the first frame in the message, store the opcode
				if( $opcode != self::WS_OPCODE_CONTINUATION ) {
					$this->arr_client_info[$_clientID][10] = $opcode;
				}
			}

			return true;
		}

		function process_client_message($_clientID, $_opcode, &$_data, $_dataLength) {
			// check opcodes
			if( $_opcode == self::WS_OPCODE_PING ) {
				// received ping message
				return $this->send_client_message($_clientID, self::WS_OPCODE_PONG, $_data);

			} else if( $_opcode == self::WS_OPCODE_PONG ) {
				// received pong message (it's valid if the server did not send a ping request for this pong message)
				if( $this->arr_client_info[$_clientID][4] !== false ) {
					$this->arr_client_info[$_clientID][4] = false;
				}

			} else if( $_opcode == self::WS_OPCODE_CLOSE ) {
				// received close message
				if( substr($_data, 1, 1) !== false ) {
					$array  = unpack('na', substr($_data, 0, 2));
					$status = $array['a'];

				} else {
					$status = false;
				}

				if( $this->arr_client_info[$_clientID][2] == self::WS_READY_STATE_CLOSING ) {
					// the server already sent a close frame to the client, this is the client's close frame reply
					// (no need to send another close frame to the client)
					$this->arr_client_info[$_clientID][2] = self::WS_READY_STATE_CLOSED;

				} else {
					// the server has not already sent a close frame to the client, send one now
					$this->send_client_close($_clientID, self::WS_STATUS_NORMAL_CLOSE);
				}

				$this->remove_client($_clientID);

			} else if( $_opcode == self::WS_OPCODE_TEXT || $_opcode == self::WS_OPCODE_BINARY ) {
				if (array_key_exists('message', $this->arr_event_list)) {
					foreach ($this->arr_event_list['message'] as $func) {
						$func($_clientID, $_data, $_dataLength, $_opcode == self::WS_OPCODE_BINARY);
					}
				}

			} else {
				// unknown opcode
				return false;
			}

			return true;
		}

		function process_client_handshake($_clientID, &$_buffer) {
			// fetch headers and request line
			$sep = strpos($_buffer, "\r\n\r\n");

			if (!$sep) {
				return false;
			}

			$headers 	  =  explode("\r\n", substr($_buffer, 0, $sep));
			$headersCount = sizeof($headers);
			// includes request line
			if ($headersCount < 1) {
				return false;
			}

			// fetch request and check it has at least 3 parts (space tokens)
			$request = &$headers[0];
			$requestParts     = explode(' ', $request);
			$requestPartsSize = sizeof($requestParts);

			if( $requestPartsSize < 3 ) {
				return false;
			}

			// check request method is GET
			if (strtoupper($requestParts[0]) != 'GET') {
				return false;
			}

			// check request HTTP version is at least 1.1
			$httpPart  = &$requestParts[$requestPartsSize - 1];
			$httpParts = explode('/', $httpPart);

			if( !isset($httpParts[1]) || (float)$httpParts[1] < 1.1 ) {
				return false;
			}

			// URI
			$this->arr_client_info[$_clientID][12] = $requestParts[1];

			// store headers into a keyed array: array[headerKey] = headerValue
			$headersKeyed = array();
			for( $i = 1 ; $i < $headersCount ; $i++ ) {
				$parts = explode(':', $headers[$i]);

				if( !isset($parts[1]) ) {
					return false;
				}

				$headersKeyed[trim($parts[0])] = trim($parts[1]);
			}

			// check Host header was received
			if( !isset($headersKeyed['Host']) ) {
				return false;
			}

			// check Sec-WebSocket-Key header was received and decoded value length is 16
			if( !isset($headersKeyed['Sec-WebSocket-Key']) ) {
				return false;
			}

			$key = $headersKeyed['Sec-WebSocket-Key'];
			if( strlen(base64_decode($key)) != 16 ) {
				return false;
			}

			// check Sec-WebSocket-Version header was received and value is 7
			// should really be != 7, but Firefox 7 beta users send 8
			if( !isset($headersKeyed['Sec-WebSocket-Version']) || (int)$headersKeyed['Sec-WebSocket-Version'] < 7 ) {
				return false;
			}

			// work out hash to use in Sec-WebSocket-Accept reply header
			$hash = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));

			// build headers
			$headers = array('HTTP/1.1 101 Switching Protocols', 'Upgrade: websocket', 'Connection: Upgrade', 'Sec-WebSocket-Accept: ' . $hash);
			$headers = implode("\r\n", $headers) . "\r\n\r\n";

			// send headers back to client
			$socket = $this->get_client_socket($_clientID);

			$left = strlen($headers);
			do {
				$sent = @socket_send($socket, $headers, $left, 0);
				if( $sent === false ) {
					return false;
				}

				$left -= $sent;
				if( $sent > 0 ) {
					$headers = substr($headers, $sent);
				}
			} while ($left > 0);

			return true;
		}

		// client write functions
		function send_client_message($_clientID, $_opcode, $_message) {
			// check if client ready state is already closing or closed
			if( $this->arr_client_info[$_clientID][2] == self::WS_READY_STATE_CLOSING || $this->arr_client_info[$_clientID][2] == self::WS_READY_STATE_CLOSED ) {
				return true;
			}
			// fetch message length
			$messageLength = strlen($_message);

			// set max payload length per frame
			$bufferSize = 4096;

			// work out amount of frames to send, based on $bufferSize
			$frameCount = ceil($messageLength / $bufferSize);
			if ($frameCount == 0) {
				$frameCount = 1;
			}

			// set last frame variables
			$maxFrame = $frameCount - 1;
			$lastFrameBufferLength = ($messageLength % $bufferSize) != 0 ? ($messageLength % $bufferSize) : ($messageLength != 0 ? $bufferSize : 0);

			// loop around all frames to send
			for( $i = 0 ; $i < $frameCount ; $i++ ) {
				// fetch fin, opcode and buffer length for frame
				$fin = $i != $maxFrame ? 0 : self::WS_FIN;
				$_opcode = $i != 0 ? self::WS_OPCODE_CONTINUATION : $_opcode;

				$bufferLength = $i != $maxFrame ? $bufferSize : $lastFrameBufferLength;

				// set payload length variables for frame
				if( $bufferLength <= 125 ) {
					$payloadLength					= $bufferLength;
					$payloadLengthExtended			= '';
					$payloadLengthExtendedLength	= 0;

				} else if( $bufferLength <= 65535 ) {
					$payloadLength 					= self::WS_PAYLOAD_LENGTH_16;
					$payloadLengthExtended 			= pack('n', $bufferLength);
					$payloadLengthExtendedLength 	= 2;

				} else {
					$payloadLength 					= self::WS_PAYLOAD_LENGTH_63;
					$payloadLengthExtended 			= pack('xxxxN', $bufferLength);
					// pack 32 bit int, should really be 64 bit int
					$payloadLengthExtendedLength 	= 8;
				}

				// set frame bytes
				$buffer = pack('n', (($fin | $_opcode)<<8) | $payloadLength) . $payloadLengthExtended . substr($_message, $i * $bufferSize, $bufferLength);

				// send frame
				$socket = $this->get_client_socket($_clientID);
				$left   = 2 + $payloadLengthExtendedLength + $bufferLength;

				do {
					$sent = @socket_send($socket, $buffer, $left, 0);
					if( $sent === false ) {
						return false;
					}

					$left -= $sent;
					if( $sent > 0 ) {
						$buffer = substr($buffer, $sent);
					}
				} while ($left > 0);
			}

			return true;
		}

		function send_client_close( $_clientID, $_status = false ) {
			// check if client ready state is already closing or closed
			if( $this->arr_client_info[$_clientID][2] == self::WS_READY_STATE_CLOSING || $this->arr_client_info[$_clientID][2] == self::WS_READY_STATE_CLOSED ) {
				return true;
			}

			// store close status
			$this->arr_client_info[$_clientID][5] = $_status;

			// send close frame to client
			$_status = $_status !== false ? pack('n', $_status) : '';
			$this->send_client_message($_clientID, self::WS_OPCODE_CLOSE, $_status);

			// set client ready state to closing
			$this->arr_client_info[$_clientID][2] = self::WS_READY_STATE_CLOSING;

			return ;
		}

		// client non-internal functions
		function ws_close($_clientID) {
			return $this->send_client_close($_clientID, self::WS_STATUS_NORMAL_CLOSE);
		}

		function ws_send($_clientID, $_message, $_binary = false) {
			return $this->send_client_message($_clientID, $_binary ? self::WS_OPCODE_BINARY : self::WS_OPCODE_TEXT, $_message);
		}

		function log($_message) {
			$is_file_exist = file_exists(self::PATH_DEBUG_PRINT);

			if( self::DEBUG_PRINT_MSG || $this->is_debug_print || $is_file_exist ) {
				echo date('[Y-m-d H:i:s] ') . $_message;

				if( $is_file_exist ) {
					$message = str_replace("[33m", "", $_message);
					$message = str_replace("[36m", "", $_message);
					$message = str_replace("[0m",  "", $message);

					global $wsif_logger;
					$wsif_logger->info(preg_replace("/[^0-9a-zA-Z |:\[\],._\-{}\/<>]/", "", $message));
				}
			}
			return ;
		}

		function bind($_type, $_func) {
			$this->log(sprintf("WebsocketInferface::bind() set event [%-8s] on [%-20s] \n", $_type, $_func));

			if( !isset($this->arr_event_list[$_type]) ) {
				$this->arr_event_list[$_type] = array();
			}

			$this->arr_event_list[$_type][] = $_func;

			return ;
		}

		function unbind($_type = '' ) {
			$this->log(sprintf("WebsocketInferface::bind() unset event [%-8s] \n", $_type));

			if( $_type ) {
				unset($this->arr_event_list[$_type]);

			} else {
				$this->arr_event_list = array();
			}

			return ;
		}

		function send_ws_data($_dst_type, $_header, $_data) {
			switch( $_dst_type ) {
				case self::DATA_TYPE_PS_SOCKET  :
					$this->_send_type_ps_socket($_header, $_data);
					break;

				case self::DATA_TYPE_PS_SESSION :
					$this->_send_type_ps_session($_header, $_data);
					break;

				case self::DATA_TYPE_WS_SESSION :
					$this->_send_type_ws_session($_header, $_data);
					break;
			}

			return ;
		}

		// sub method::send_ws_data, for ps_socket
		function _send_type_ps_socket($_header, $_data) {
			$is_route = $this->get_ws_route_case($_header["src_type"], self::DATA_TYPE_PS_SOCKET, $_header["route_case"]);
			if( !$is_route ) return true;

			$data   = $_data;
			$length = strlen($_data);

			$route_uri = $_header["uri"];
			$is_binary = $_header["is_binary"];

			// for debug print message
			$type           = $_header["cmd_id"];
			$str_is_binary  = $is_binary == true ? "Binary" : "String";
			$str_route_case = $this->get_ws_route_case_str($_header["route_case"]);
			$str_ws_src	    = $this->get_ws_data_type_str($_header["src_type"]);
			$str_ws_dst     = $this->get_ws_data_type_str(self::DATA_TYPE_PS_SOCKET);

			if( ($process_port = $this->get_client_route_port($route_uri)) == 0 ) {
				if( self::TRACE_PRINT_MSG ) {
					$this->log(sprintf("WebsocketInterface::_send_type_ps_socket() URI [%s] not found\n", $route_uri));
				}
				return false;
			}

			if( $process_port == $_header["ps_type"] ) {
				$this->log(sprintf("WebsocketInterface::_send_type_ps_socket() URI [%s] can not send to self \n", $route_uri));
				return false;
			}

			if( !($ps_socketFd = socket_create(AF_INET, SOCK_STREAM, 0)) ) {
				$this->log(sprintf("WebsocketInterface::_send_type_ps_socket() URI [%s] socket_create failed : %s\n",
						$route_uri, socket_strerror(socket_last_error())));
				return false;
			}

			if( @socket_connect($ps_socketFd, 'localhost', $process_port) === false ) {
				if( self::TRACE_PRINT_MSG ) {
					$this->log(sprintf("WebsocketInterface::_send_type_ps_socket() URI [%s] socket_connect failed : %s\n",
							$route_uri, socket_strerror(socket_last_error())));
				}
				return false;
			}

			socket_set_nonblock($ps_socketFd);

			if( !socket_write($ps_socketFd, $data, $length) ) {
				$this->log(sprintf("WebsocketInterface::_send_type_ps_socket() URI [%s] socket_write failed : %s\n",
						$route_uri, socket_strerror(socket_last_error())));

				socket_set_block($ps_socketFd);
				socket_close($ps_socketFd);

				return false;
			}

			socket_set_block($ps_socketFd);
			socket_close($ps_socketFd);

			if( $this->is_data_print ) {
				$this->log(sprintf("%s%s -> %s | %-6s | %-7s | %-24s | %-8s | %-12s | %-12s | %-s\033[0m\n",
					$this->get_ws_data_type_color($_header["src_type"]),
					$str_ws_src, $str_ws_dst,
					$str_is_binary,
					$process_port,
					"uri: "    . $route_uri,
					"type: "   . $type,
					"case: "   . $str_route_case,
					"length: " . $length,
					$data));
			}
			return true;
		}

		// sub method::send_ws_data, for ps_session
		function _send_type_ps_session($_header, $_data) {
			$is_route = $this->get_ws_route_case($_header["src_type"], self::DATA_TYPE_PS_SESSION, $_header["route_case"]);
			$is_src_ps_socket = $_header["src_type"] == self::DATA_TYPE_PS_SOCKET ? true : false;

			if( !$is_route ) return true;

			$data   = $_data;
			$length = strlen($_data);

			$clientID  = $_header["clientID"];
			$route_uri = $_header["uri"];
			$is_binary = $_header["is_binary"];

			// for debug print message
			$type           = $_header["cmd_id"];
			$str_route_case = $this->get_ws_route_case_str($_header["route_case"]);
			$str_is_binary  = $is_binary == true ? "Binary" : "String";
			$str_ws_src	    = $this->get_ws_data_type_str($_header["src_type"]);
			$str_ws_dst     = $this->get_ws_data_type_str(self::DATA_TYPE_PS_SESSION);

			foreach( $this->fds_reads as $id => $client ) {
				$client_uri     = $this->get_client_uri($id);
				$client_ps_type = $this->get_client_ps_type($id);
				$is_route_wscif = $this->get_client_route_wscif($id);

				if( $is_src_ps_socket && !$is_route_wscif ) {
					continue;
				}

				// send to session process
				if( $clientID != $id && $route_uri == $client_uri && $client_ps_type == self::EVENT_TYPE_PS ) {
					$ps_sessionFd = $this->get_client_socket($id);
					socket_set_nonblock($ps_sessionFd);

					if( !($rc = socket_write($ps_sessionFd, $data, $length)) ) {
						$this->ws_close($id);

						$this->log(sprintf("WebsocketInterface::_send_type_ps_session() type [%s] URI [%s] socket_connect failed : %s\n",
								$client_ps_type, $route_uri, socket_strerror(socket_last_error())));

					} else {
						if( $this->is_data_print ) {
							$this->log(sprintf("%s%s -> %s | %-6s | %-7s | %-24s | %-8s | %-12s | %-12s | %-s\033[0m\n",
								$this->get_ws_data_type_color($_header["src_type"]),
								$str_ws_src, $str_ws_dst,
								$str_is_binary,
								"session",
								"uri: "    . $client_uri,
								"type: "   . $type,
								"case: "   . $str_route_case,
								"length: " . $length,
								$data));
						}
					}
					socket_set_block($ps_sessionFd);
				}
			}

			return true;
		}

		// sub method::send_ws_data, for websocket socket/session
		function _send_type_ws_session($_header, $_data) {
			$is_route = $this->get_ws_route_case($_header["src_type"], self::DATA_TYPE_WS_SESSION, $_header["route_case"]);
			if( !$is_route ) return true;

			$data   = $_data;

			$clientID  = $_header["clientID"];
			$route_uri = $_header["uri"];
			$is_binary = $_header["is_binary"];

			// for debug print message
			$length = array();
			$length[0] = strlen($_data[0]);
			$length[1] = strlen($_data[1]);

			$type           = $_header["cmd_id"];
			$str_route_case = $this->get_ws_route_case_str($_header["route_case"]);
			$str_is_binary  = $is_binary == true ? "Binary" : "String";
			$str_ws_src     = $this->get_ws_data_type_str($_header["src_type"]);

			foreach( $this->fds_reads as $id => $client ) {
				$client_uri     = $this->get_client_uri($id);
				$client_ps_type = $this->get_client_ps_type($id);
				$client_extend  = $this->get_client_extend_type($id);

				$idx = ($client_extend == true ? 1 : 0);

				// send to session process
				if( $clientID != $id && $route_uri == $client_uri && $client_ps_type == self::EVENT_TYPE_WS ) {
					$this->ws_send($id, $data[$idx], $client_extend);

					if( $this->is_data_print ) {
						$this->log(sprintf("%s%s -> %s | %-6s | %-7s | %-24s | %-8s | %-12s | %-12s | %-s\033[0m\n",
							$this->get_ws_data_type_color($_header["src_type"]),
							$str_ws_src, $this->get_ws_data_type_str(($client_extend ? self::DATA_TYPE_WS_SESSION : self::DATA_TYPE_WS_SOCKET)),
							$str_is_binary,
							"session",
							"uri: "    . $client_uri,
							"type: "   . $type,
							"case: "   . $str_route_case,
							"length: " . $length[$idx],
							$data[$idx]));
					}
				}
			}

			return true;
		}


		// client data functions
		function set_client_route_key($_clientID, $_uri) {
			$this->arr_client_info[$_clientID][12] = "/" . $_uri;

			return ;
		}

		function set_client_session_type($_clientID, $_value) {
			$this->arr_client_info[$_clientID][14] = $_value;

			return ;
		}

		function set_client_extend_type($_clientID, $_is_extend) {
			if( !$this->arr_client_info[$_clientID][15]->is_set ) {
				$this->arr_client_info[$_clientID][15]->is_set    = true;
				$this->arr_client_info[$_clientID][15]->is_extend = $_is_extend;
			}

			return ;
		}

		function get_next_clientID() {
			$i = 1;

			// starts at 1 because 0 is the listen socket
			while( isset($this->fds_reads[$i]) ) {
				$i++;
			}

			return $i;
		}

		function get_client_socket($_clientID) {

			return $this->arr_client_info[$_clientID][0];
		}

		function get_client_ip_addr($_clientID) {
			if( !isset($this->arr_client_info[$_clientID][6]) ) {
				return null;

			} else {
				return long2ip($this->arr_client_info[$_clientID][6]);
			}
		}

		function get_client_uri($_clientID) {
			if( !isset($this->arr_client_info[$_clientID][12]) ) {
				return null;

			} else {
				return substr($this->arr_client_info[$_clientID][12], 1);
			}
		}

		function get_client_route_key($_clientID) {
			if( !isset($this->arr_client_info[$_clientID][12]) ) {
				return null;

			} else {
				return $this->arr_client_info[$_clientID][12];
			}
		}

		function get_client_ps_type($_clientID) {
			if( !isset($this->arr_client_info[$_clientID][13]) ) {
				return null;

			} else {
				return $this->arr_client_info[$_clientID][13];
			}
		}

		function get_client_session_type($_clientID) {
			if( !isset($this->arr_client_info[$_clientID][14]) ) {
				return null;

			} else {
				return $this->arr_client_info[$_clientID][14];
			}
		}

		function get_client_extend_type($_clientID) {
			if( !isset($this->arr_client_info[$_clientID][15]) ) {
				return false;
			}
			return $this->arr_client_info[$_clientID][15]->is_extend;
		}

		function get_client_route_port($_uri) {
			if( $this->cnt_route_list == 0 ) return 0;
			if( !isset($this->arr_route_list[$_uri]) ) return 0;

			return intval($this->arr_route_list[$_uri]);
		}

		function set_client_route_wscif($_clientID, $_type) {
			$this->arr_client_info[$_clientID][16] = $_type; // false: normal, true: wscif

			return ;
		}

		function get_client_route_wscif($_clientID) {
			if( !isset($this->arr_client_info[$_clientID][16]) ) {
				return null;

			} else {
				return $this->arr_client_info[$_clientID][16];
			}
		}

		function get_ws_route_case_str($_case) {
			switch( $_case ) {
				case 0 : return "to each"; break;
				case 1 : return "to ws ";  break;
				case 2 : return "to ps";   break;
				case 3 : return "to all "; break;
				default: return "unknown"; break;
			}
		}

		function get_ws_data_type_str($_case) {
			switch( $_case ) {
				case self::DATA_TYPE_WS_SESSION : return "ws+"; break;
				case self::DATA_TYPE_WS_SOCKET  : return "ws-"; break;
				case self::DATA_TYPE_PS_SESSION : return "ps+"; break;
				case self::DATA_TYPE_PS_SOCKET  : return "ps-"; break;
			}
		}

		function get_ws_data_type_color($_case) {
			switch( $_case ) {
				case self::DATA_TYPE_WS_SESSION : return "\033[32m"; break;
				case self::DATA_TYPE_WS_SOCKET  : return "\033[33m"; break;
				case self::DATA_TYPE_PS_SESSION : return "\033[35m"; break;
				case self::DATA_TYPE_PS_SOCKET  : return "\033[36m"; break;
			}
		}

		function get_ws_route_case($_src, $_dest, $_case) {
			$rc = true;

			if( $_src == self::DATA_TYPE_WS_SOCKET ) {
				if( $_dest == self::DATA_TYPE_WS_SOCKET || $_dest == self::DATA_TYPE_WS_SESSION ) {
					$rc = false;
				}
			}
			else if( $_src == self::DATA_TYPE_PS_SOCKET ) {
				if( $_dest == self::DATA_TYPE_PS_SOCKET ) {
					$rc = false;
				}
			}
			else if( $_src == self::DATA_TYPE_WS_SESSION ) {
				// all case allowed
			}
			else if( $_src == self::DATA_TYPE_PS_SESSION ) {
				// all case allowed
			}

			return $rc;
		}
	} // End of class::WebsocketInterface

	class CommonLogFunc {
		const TIMEZONE_TYPE				= 0;
		const TIMEZONE_GMT				= 1;
		const TIMEZONE_NAME				= 2;
		const TIMEZONE_PATH				= 3;

		const SIZE_LOG_BYTE             = 1024;	// bytes = 1K bytes
		// const SIZE_LOG_LINE             = 10240;	// 1K x Line = 10M bytes
		const SIZE_LOG_LINE             = 10;	// 1K x Line = 10M bytes

		const LOG_LEVEL_FATAL           = "[FATAL] ";
		const LOG_LEVEL_ERROR           = "[ERROR] ";
		const LOG_LEVEL_WARN            = "[WARN]  ";
		const LOG_LEVEL_INFO            = "[INFO]  ";
		const LOG_LEVEL_DEBUG           = "[DEBUG] ";


		//* variables */
		private $env_logPath;
		private $env_logName;
		private $env_setInfoFlag;

		/* constructor */
		function __construct($_moduleName) {
			// 모듈명 지정안하면 사용할 수 없음
			if( is_null($_moduleName) ) {
				echo "input module name\n";

				return ;
			}

			$env_pathModule = "/opt/interm/public_html/modules/" . $_moduleName;

			if( !file_exists($env_pathModule) ) {
				$env_pathModule = "/opt/interm/public_html/..";
			}

			$this->env_logPath		= $env_pathModule . "/log";
			$this->env_logName		= $_moduleName .".log";
			$this->env_setInfoFlag	= false;

			/*
				if( ($logFp = $this->openFile()) == null ) return ;
				else fclose($logFp);
			*/
		}

		/* functions */
		function packInt32Be($_idx) {
			return pack('C4', ($_idx >> 24) & 0xFF, ($_idx >> 16) & 0xFF, ($_idx >>  8) & 0xFF, ($_idx >>  0) & 0xFF);
		}

		function packInt32Le($_idx) {
		   return pack('C4', ($_idx >>  0) & 0xFF, ($_idx >>  8) & 0xFF, ($_idx >> 16) & 0xFF, ($_idx >> 24) & 0xFF);
		}

		function openFile() {
			// 파일 용량 정상 체크 (비정상 시 삭제)
			if( file_exists($this->env_logPath . "/" . $this->env_logName) ) {
				$fileSize  = filesize($this->env_logPath . "/" . $this->env_logName);
				$existSize = self::SIZE_LOG_BYTE * self::SIZE_LOG_LINE + 4;

				if( $existSize != $fileSize ) {
					unlink($this->env_logPath . "/" . $this->env_logName);
				}
			}

			// 파일 유무 체크 (유: 정상 진행 / 무: 파일 생성)
			if( !($logFp = fopen($this->env_logPath . "/" . $this->env_logName, "r+b")) ) {
				// 파일 생성 가능 체크 (가능: dummy 파일 생성 / 불가능 : false)
				if( !($logFp = fopen($this->env_logPath . "/" . $this->env_logName, "w+b")) ) {

					return null;

				} else {
					if( flock($logFp, LOCK_EX) ) {
						fseek($logFp, self::SIZE_LOG_BYTE * self::SIZE_LOG_LINE, SEEK_CUR);
						fwrite($logFp, $this->packInt32Le(0));

						flock($logFp, LOCK_UN);
					}
				}
			}

			return $logFp;
		}

		function clearLog() {
			unlink($this->env_logPath . "/" . $this->env_logName);

			if( ($logFp = $this->openFile()) == null ) return ;
			else fclose($logFp);

			return ;
		}

		function removeLog() {
			unlink($this->env_logPath . "/" . $this->env_logName);

			return ;
		}

		function writeLog($_logLevel, $_message) {
			// Step 1. 로그 메시지 세팅
			$maxLength = self::SIZE_LOG_BYTE - 23; // log format 길이(약 23byte)

			$message = $_logLevel . $_message;
			if( strlen($message) >  $maxLength ) {
				$message = substr($message, 0, $maxLength);
			}

			if( file_exists("/opt/interm/public_html/modules/time_setup/conf/time_stat.json") ) {
				$load_envData 	= file_get_contents("/opt/interm/public_html/modules/time_setup/conf/time_stat.json");
				$timeData 		= json_decode($load_envData);
				$timezone_info 	= $this->setTimeZoneInfo();

				date_default_timezone_set($timezone_info[$timeData->timezone_idx][self::TIMEZONE_PATH]);

				$date = getdate();
				$logFormat = "["
					. $date["year"]  . "/"
					. sprintf("%02d", $date["mon"])    . "/"
					. sprintf("%02d", $date["mday"])   . " "
					. sprintf("%02d", $date["hours"])  . ":"
					. sprintf("%02d", $date["minutes"]). ":"
					. sprintf("%02d", $date["seconds"])
					. "] " . $message . "\n";

			} else {
				$logFormat = "[" . date("Y/m/d H:i:s", time()) . "] " . $message . "\n";
			}


			// Step 2. File 유/무 확인(없을 시 생성) 및 저장
			if( ($logFp = $this->openFile()) == null ) return ;

			if( flock($logFp, LOCK_EX) ) {
				fseek($logFp, -4, SEEK_END);
				$logIndex = fread($logFp, 4);
				$header = unpack("iindex/", $logIndex);
				$curIndex = $header['index'];
				fseek($logFp, self::SIZE_LOG_BYTE * ($curIndex), SEEK_SET);

				fwrite($logFp, str_pad($logFormat, self::SIZE_LOG_BYTE, "\0", STR_PAD_RIGHT));

				$curIndex++;
				if( $curIndex == self::SIZE_LOG_LINE ) $curIndex = 0;

				fseek($logFp, -4, SEEK_END);
				fwrite($logFp, $this->packInt32Le($curIndex));

				flock($logFp, LOCK_UN);
			}

			fclose($logFp);

			return ;
		}

		// info level의 [INFO] level을 보기 위해선 stat을 true로 변경해야 함
		function setLogInfo($_stat) {
			$this->env_setInfoFlag = $_stat;

			return ;
		}

		function fatal($_message) { $this->writeLog(self::LOG_LEVEL_FATAL, $_message);	}
		function error($_message) { $this->writeLog(self::LOG_LEVEL_ERROR, $_message);	}
		function warn ($_message) { $this->writeLog(self::LOG_LEVEL_WARN,  $_message);	}
		function debug($_message) { $this->writeLog(self::LOG_LEVEL_DEBUG, $_message);	}

		// info level은 기본으로 log에 level을 출력하지 않음(false)
		function info ($_message) {
			if( $this->env_setInfoFlag == true ) {
				$this->writeLog(self::LOG_LEVEL_INFO, $_message);

			} else {
				$this->writeLog("", $_message);
			}
		}

		function setTimeZoneInfo() { // time_setup module 참고
			return $timezone_info = array(
					array("MHT+12"		, "GMT-12:00"	, "Eniwetok Kwajalein"							, "Pacific/Kwajalein"),
					array("SST+11"		, "GMT-11:00"	, "MidwayIsland ,Samoa"							, "US/Samoa"),
					array("HST+10"		, "GMT-10:00"	, "Hawaii"										, "US/Hawaii"),
					array("AKST+9"		, "GMT-09:00"	, "Alaska"										, "US/Alaska"),
					array("PST+8"		, "GMT-08:00"	, "Pacific Time(US & Canada), Tijuana"			, "US/Pacific"),
					array("MST+7"		, "GMT-07:00"	, "Arizona"										, "US/Arizona"),
					array("MST+7"		, "GMT-07:00"	, "Mountain Time(US & Canada)"					, "US/Mountain"),
					array("CST+6"		, "GMT-06:00"	, "Central Time(US & Canada)"					, "US/Central"),
					array("CDT+6"		, "GMT-06:00"	, "Mexico City"									, "America/Mexico_City"),
					array("CST+6"		, "GMT-06:00"	, "Tegucigalpa"									, "America/Tegucigalpa"),
					array("CST+6"		, "GMT-06:00"	, "Saskatchewan"								, "Canada/Saskatchewan"),
					array("COT+5"		, "GMT-05:00"	, "Bogota, Lima, Quito"							, "America/Lima"),
					array("EST+5"		, "GMT-05:00"	, "Eastern Time(US &amp; Canada)"				, "US/Eastern"),
					array("EST+5"		, "GMT-05:00"	, "Indiana(East)"								, "US/East-Indiana"),
					array("AST+4"		, "GMT-04:00"	, "Atlantic Time(Canada)"						, "Canada/Atlantic"),
					array("VET+4"		, "GMT-04:00"	, "Caracas"										, "America/Caracas"),
					array("VET+4"		, "GMT-04:00"	, "La Paz"										, "America/La_Paz"),
					array("CLT+4"		, "GMT-04:00"	, "Santiago"									, "America/Santiago"),
					array("NST+0330"	, "GMT-03:30"	, "Newfoundland"								, "Canada/Newfoundland"),
					array("ESAST+3"		, "GMT-03:00"	, "Brasilia"									, "Brazil/East"),
					array("ART+3"		, "GMT-03:00"	, "Buenos Aires"								, "America/Argentina/Buenos_Aires"),
					array("MAST+3"		, "GMT-03:00"	, "Georgetown"									, "America/Guyana"),
					array("MAST+2"		, "GMT-02:00"	, "Mid-Atlantic"								, "America/New_York"),
					array("AZOST+1"		, "GMT-01:00"	, "Azores"										, "Atlantic/Azores"),
					array("AZOST+1"		, "GMT-01:00"	, "Cape Verde Is."								, "Atlantic/Cape_Verde"),
					array("GMT0"		, "GMT"		 	, "Greenwich Mean Time : Dublin, Lisbon, London", "Europe/London"),
					array("CEST-1"		, "GMT+01:00"	, "Amsterdam, Berlin, Rome, Stockholm, Vienna"	, "Europe/Amsterdam"),
					array("CEST-1"		, "GMT+01:00"	, "Belgrade, Bratislava, Budapest, Ljubljana"	, "Europe/Belgrade"),
					array("CEST-1"		, "GMT+01:00"	, "Brussels, Copenhagen, Madrid, Paris"			, "Europe/Brussels"),
					array("CEST-1"		, "GMT+01:00"	, "Vilnius"										, "Europe/Vilnius"),
					array("CEST-1"		, "GMT+01:00"	, "Sarajevo, Skopje, Warsaw, Zagreb"			, "Europe/Sarajevo"),
					array("CEST-1"		, "GMT+01:00"	, "Sofija"										, "Europe/Sofia"),
					array("EEST-2"		, "GMT+02:00"	, "Athens, Istanbul"							, "Europe/Athens"),
					array("EEST-2"		, "GMT+02:00"	, "Minsk"										, "Europe/Minsk"),
					array("EEST-2"		, "GMT+02:00"	, "Bucharest"									, "Europe/Bucharest"),
					array("EEST-2"		, "GMT+02:00"	, "Cairo"										, "Egypt"),
					array("CAT-2"		, "GMT+02:00"	, "Harare, Pretoria"							, "Africa/Harare"),
					array("EEST-2"		, "GMT+02:00"	, "Helsinki, Riga, Tallinn"						, "Europe/Helsinki"),
					array("IST-2"		, "GMT+02:00"	, "Israel"										, "Asia/Tel_Aviv"),
					array("ADT-3"		, "GMT+03:00"	, "Baghdad, Kuwait, Riyadh"						, "Asia/Baghdad"),
					array("MSD-3"		, "GMT+03:00"	, "Moscow, St. Petersburg, Volgograd"			, "Europe/Moscow"),
					array("EAT-3"		, "GMT+03:00"	, "Nairobi"										, "Africa/Nairobi"),
					array("IRST-0330"	, "GMT+03:30"	, "Tehran"										, "Asia/Tehran"),
					array("GST-4"		, "GMT+04:00"	, "Abu Dhabi, Muscat"							, "Asia/Dubai"),
					array("GEST-4"		, "GMT+04:00"	, "Tbilisi"										, "Asia/Tbilisi"),
					array("GEST-4"		, "GMT+04:00"	, "Baku"										, "Asia/Baku"),
					array("AFT-0430"	, "GMT+04:30"	, "Kabul"										, "Asia/Kabul"),
					array("PKT-5"		, "GMT+05:00"	, "Yekaterinburg"								, "Asia/Yekaterinburg"),
					array("PKT-5"		, "GMT+05:00"	, "Islamabad, Karachi, Tashkent"				, "Asia/Karachi"),
					array("IST-0530"	, "GMT+05:30"	, "Bombay, Calcutta, Madras, New Delhi"			, "Asia/Calcutta"),
					array("ALMST-6"		, "GMT+06:00"	, "Almaty, Dhaka"								, "Asia/Almaty"),
					array("LKT-6"		, "GMT+06:00"	, "Colombo"										, "Asia/Colombo"),
					array("ICT-7"		, "GMT+07:00"	, "Bangkok, Hanoi, Jakarta"						, "Asia/Bangkok"),
					array("HKT-8"		, "GMT+08:00"	, "Beijing, Chongqing, Hong Kong, Urumqi"		, "Asia/Hong_Kong"),
					array("WST-8"		, "GMT+08:00"	, "Perth"										, "Australia/Perth"),
					array("SGT-8"		, "GMT+08:00"	, "Singapore"									, "Asia/Singapore"),
					array("CST-8"		, "GMT+08:00"	, "Taipei"										, "Asia/Taipei"),
					array("JST-9"		, "GMT+09:00"	, "Osaka, Sapporo, Tokyo"						, "Asia/Tokyo"),
					array("KST-9"		, "GMT+09:00"	, "Seoul"										, "Asia/Seoul"),
					array("YAKST-9"		, "GMT+09:00"	, "Yakutsk"										, "Asia/Yakutsk"),
					array("CST-0930"	, "GMT+09:30"	, "Adelaide"									, "Australia/Adelaide"),
					array("CST-0930"	, "GMT+09:30"	, "Darwin"										, "Australia/Darwin"),
					array("EST-10"		, "GMT+10:00"	, "Brisbane"									, "Australia/Brisbane"),
					array("EST-10"		, "GMT+10:00"	, "Canberra, Melbourne, Sydney"					, "Australia/Canberra"),
					array("PGT-10"		, "GMT+10:00"	, "Guam, Port Moresby"							, "Pacific/Port_Moresby"),
					array("EST-10"		, "GMT+10:00"	, "Hobart"										, "Australia/Hobart"),
					array("VLAST-10"	, "GMT+10:00"	, "Vladivostok"									, "Asia/Vladivostok"),
					array("MAGST-11"	, "GMT+11:00"	, "Magadan"										, "Asia/Magadan"),
					array("MAGST-11"	, "GMT+11:00"	, "Solomon Is."									, "Pacific/Guadalcanal"),
					array("MAGST-11"	, "GMT+11:00"	, "New Caledonia"								, "Pacific/Noumea"),
					array("NZST-12"		, "GMT+12:00"	, "Auckland, Wellington"						, "Pacific/Auckland")
				);
		}
	} // end of CommonLogFunc()

	/* Websocket Event Handler, from web browser side */
	// when a client sends data to the server
	function ws_on_message_handler($_clientID, $_data, $_length) {
		global $wsif;

		// check if message length is 0, client close
		if( $_length == 0 ) {
			$wsif->ws_close($_clientID);
			return ;
		}

		// check if uri is null, invalid format client
		if( ($route_uri = $wsif->get_client_uri($_clientID)) == null ) {
			$wsif->ws_close($_clientID);
			return ;
		}

		$wsif_parser = new WSIF_DataParser();

		if( !$wsif_parser->parse_data($wsif::WS_DATA_TYPE_WS, $_data) ) {
			$uri_name = $wsif->get_client_uri($_clientID);
			$wsif->log(sprintf("parse invalid header data : type [ws], client [%d/%s]\n", $_clientID, $uri_name));

			if( $uri_name != $wsif::STR_URI_WSCIF ) {
				$wsif->ws_close($_clientID);
			}

			return ;
		}

		$wsif_parser->set_header("uri",      $route_uri);
		$wsif_parser->set_header("clientID", $_clientID);

		$header = $wsif_parser->get_header_info();
		$wsif->set_client_extend_type($_clientID, $header["is_extend"]);

		// send to ws_router.xml defined process
		$data = $wsif_parser->get_ws_data($wsif::DATA_TYPE_PS_SOCKET);
		$wsif->send_ws_data($wsif::DATA_TYPE_PS_SOCKET,  $header, $data);

		// send to process session
		$data = $wsif_parser->get_ws_data($wsif::DATA_TYPE_PS_SESSION);
		$wsif->send_ws_data($wsif::DATA_TYPE_PS_SESSION, $header, $data);

		// send to websocket socket/session
		$arr_data = array($wsif_parser->get_ws_data($wsif::DATA_TYPE_WS_SOCKET),
				          $wsif_parser->get_ws_data($wsif::DATA_TYPE_WS_SESSION));
		$wsif->send_ws_data($wsif::DATA_TYPE_WS_SESSION, $header, $arr_data);

		return ;
	}

	// when a client connects
	function ws_on_open_handler($_clientID) {
		global $wsif;

		$ip	 = $wsif->get_client_ip_addr($_clientID);
		$uri = $wsif->get_client_uri($_clientID);

		$wsif->log(sprintf("[%s/%s][%d] has connected.\n", $ip, $uri, $_clientID));

		return ;
	}

	function ws_on_close_handler($_clientID, $_status) {
		global $wsif;

		// disable print socket data
		if( !($wsif->get_client_session_type($_clientID) > 0) ) {
			$ip	 = $wsif->get_client_ip_addr($_clientID);
			$uri = $wsif->get_client_uri($_clientID);

			$wsif->log(sprintf("[%s/%s][%d] has disconnected.\n", $ip, $uri, $_clientID));
		}

		return ;
	}

	$wsif_logger = new CommonLogFunc("wsif");
	cli_set_process_title("ws_router");

	$wsif = new WebsocketInterface();

	$wsif->bind('message', 'ws_on_message_handler');
	$wsif->bind('open',	   'ws_on_open_handler');
	$wsif->bind('close',   'ws_on_close_handler');

	$wsif->run();
	$wsif->stop();
?>
