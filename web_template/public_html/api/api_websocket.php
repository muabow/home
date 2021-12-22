<?php
	/*
	 * [Structure Info]
	 * 1. ws_data
	 * 	  {
	 * 		char	cmd_id;			// 1  , header start
	 * 		char	route_case;		// 2
	 * 		char	is_binary;		// 3
	 * 		char	is_extend;		// 4
	 * 		int		length;			// 8  , header end
	 * 		char	*data;
	 *    }
	 *
	 * 2. ws_ext_data
	 * 	  {
	 * 		int		tag;			// 1  , header start
	 * 		int		length;			// 2
	 * 		char	*value;			// 3
	 * 	  }
	 *
	 * [How to use]
	 * - create instance
	 * 	 ex) $wsHandler = new WebsocketHandler("127.0.0.1", "audio_client", true);
	 *
	 * - send string
	 *   ex) $wsHandler->send($cmd_id, '{"test" : "value"}');
	 *
	 * - send binary
	 *   ex) $wsHandler->send_binary($cmd_id, $binaryArr, count($binaryArr));
	 *
	 * - recv data
	 *   ex) $recv_ws_data = $wsHandler->recv();
	*/

	class WebsocketHandler {
		const	DFLT_WS_IF_ADDR			= "127.0.0.1";
		const	DFLT_WS_IF_PORT			= 2100;
		//const	DFLT_WS_RETRY_CNT		= 10;

		const   WS_ROUTE_TO_EACH_OTHER  = 0;
		const 	WS_ROUTE_TO_WEB_ONLY 	= 1;
		const 	WS_ROUTE_TO_NATIVE_ONLY	= 2;
		const 	WS_ROUTE_TO_ALL 		= 3;

		const 	WS_HEADER_SIZE			= 8;
		const 	WS_EXT_HEADER_SIZE		= 8;

		const	WS_RECV_TRY_CNT			= 10;

		private $is_p_term;
		private $is_p_run;
		private $is_noti_fail;
		private $is_debug;

		private $socket_fd;
		private $port;
		private $ip_addr;
		private $ws_route_to;
		private $uri;

		private $ws_data;
		private $do_chk_conn;
		private $conn_timeout;

		function __construct($_ip_addr, $_uri, $_is_debug = false, $_conn_timeout = 1) {
			// init parameter
			{
				$this->init_data();

				$this->is_debug 		= $_is_debug;
				$this->ws_route_to 		= self::WS_ROUTE_TO_NATIVE_ONLY;
			}

			// set argument values
			{
				$this->uri 	= $_uri;

				if(self::DFLT_WS_IF_ADDR !== $_ip_addr) {
					$this->dbg_printf("WebsocketHandler::WebsocketHandler() changed default addr : [%s] -> [%s]\n", self::DFLT_WS_IF_ADDR, $_ip_addr);
				}

				$this->ip_addr = $_ip_addr;
				$this->port    = 80;
				
				$this->conn_timeout  = $_conn_timeout;
				$this->do_chk_conn   = true;
				
				if($this->ip_addr == "127.0.0.1") {
					$this->port 		= self::DFLT_WS_IF_PORT;
					$this->do_chk_conn	= false;	
				} 
				
				$this->dbg_printf("WebsocketHandler::WebsocketHandler() address : [%s] \n", $this->ip_addr);
				$this->dbg_printf("WebsocketHandler::WebsocketHandler() port    : [%d] \n", $this->port);
			}

			$this->init();
		}

		function __destruct() {
			$this->term();
		}

		public function init() {
			$this->init_data();

			$this->socket_fd = -1;

			$this->is_p_term = false;
			$this->is_p_run  = false;

			if(!$this->init_connect()) {
				$this->dbg_printf("WebsocketHandler::init() init failed, URI : [%s] \n", $this->uri);

				return false;
			}

			if(!$this->init_socket_option()) {
				$this->dbg_printf("WebsocketHandler::init() setSockopt failed\n");

				return false;
			}

			if(!$this->send(0, "uri_regist")) {
				$this->dbg_printf("WebsocketHandler::init() URI registration failed\n");
				return false;
			}
			$this->is_p_run = true;

			$this->dbg_printf("WebsocketHandler::init() init success, URI : [%s] \n", $this->uri);

			return true;
		}

		public function term() {
			if($this->is_p_term) {
				$this->dbg_printf("WebsocketHandler::term() already termed\n");

				return ;
			}

			$this->is_p_term = true;
			$this->is_p_run  = false;

			if($this->socket_fd != -1) {
				socket_close($this->socket_fd);
				$this->socket_fd = -1;
			}

			if($this->ws_data["data"] != null) {
				$this->ws_data["data"] = null;
			}

			$this->dbg_printf("WebsocketHandler::term() termed\n");

			return ;
		}

		private function init_data() {
			if(isset($this->ws_data) == false) {
				$this->ws_data = array();
			}

			$this->ws_data["cmd_id"] 	 = null;
			$this->ws_data["route_case"] = null;
			$this->ws_data["is_binary"]  = null;
			$this->ws_data["is_extend"]  = null;
			$this->ws_data["length"] 	 = null;

			$is_exist = array_key_exists("data", $this->ws_data);
			if(($is_exist == true) && ($this->ws_data["data"] != null)) {
				unset($this->ws_data["data"]);
				$this->ws_data["data"] = null;
			}
			$this->ws_data["data"] = array();
		}

		private function free_data() {
			if(isset($this->ws_data) == true) {
				if(isset($this->ws_data["data"]) == true) {
					unset($this->ws_data["data"]);
					$this->ws_data["data"] = null;
				}

				unset($this->ws_data);
				$this->ws_data = null;
			}
		}

		private function init_connect() {
			if($this->do_chk_conn == true) {
				$ret = shell_exec("wget --timeout={$this->conn_timeout} --tries=1 --spider {$this->ip_addr}:{$this->port} 2>&1 | grep -c 'connected'");
				if($ret == 0) {
					$this->dbg_printf("WebsocketHandler::init_connect() : fail to check connection. ({$this->ip_addr}:{$this->port})\n");
					return false;
				}
			}
			
			if(($this->socket_fd = socket_create(AF_INET, SOCK_STREAM, 0)) < 0) {
				$this->dbg_printf("WebsocketHandler::init_connect() socket() failed : %s\n", socket_strerror(socket_last_error()));

				return false;
			}
			
			if(socket_connect($this->socket_fd, $this->ip_addr, $this->port) != true) {
				$this->dbg_printf("WebsocketHandler::init_connect() connect() failed : %s\n", socket_strerror(socket_last_error()));

				return false;
			}

			return true;
		}

		private function init_socket_option() {
			$linger = array("l_onoff" => true, "l_linger" => 0);

			if(socket_set_option($this->socket_fd, SOL_SOCKET, SO_LINGER, $linger) != true) {
				$this->dbg_printf("WebsocketHandler::init_socket_option() SO_LINGER failed : %s\n", socket_strerror(socket_last_error()));
			}

			/* TCP_NODELAY : permission denied
			if(socket_set_option($this->socket_fd, SOL_SOCKET, TCP_NODELAY, TRUE) != true) {
				$this->dbg_printf("WebsocketHandler::init_socket_option() TCP_NODELAY failed : %s\n", socket_strerror(socket_last_error()));
			}
			*/

			$timeout = array("sec" => 2, "usec" => 0);
			if(socket_set_option($this->socket_fd, SOL_SOCKET, SO_RCVTIMEO, $timeout) != true) {
				$this->dbg_printf("WebsocketHandler::init_socket_option() SO_RCVTIMEO failed : %s\n", socket_strerror(socket_last_error()));
			}

			return true;
		}

		public function recv() {
			$is_success	= true;

			$fd_writes = $fd_excepts = array();

			while(!$this->is_p_term) {
				$fd_reads = array($this->socket_fd);
				if(($rc = @socket_select($fd_reads, $fd_writes, $fd_excepts, 0, 200000)) < 0) {
					// error
					$this->dbg_printf("WebsocketHandler::recv() select failed : %s\n", socket_strerror(socket_last_error()));

					$is_success = false;
					$this->term();

				} else if($rc == 0) {
					// timeout
					$is_success = false;
					break;

				} else {
					$ret;
					$recv_data = null;

					if(($ret = socket_recv($this->socket_fd, $recv_data, self::WS_HEADER_SIZE, MSG_WAITALL)) < 0) {
						$this->dbg_printf("WebsocketHandler::recv() recv header failed : %s\n", socket_strerror(socket_last_error()));

					} else if($ret == 0) {
						$this->dbg_printf("WebsocketHandler::recv() connection disconnected\n");

						$is_success = false;
						$this->term();


					} else {
						$this->free_data();

						$this->ws_data = unpack("C1cmd_id/C1route_case/C1is_binary/C1is_extend/I1length", $recv_data); // total 8Bytes.
						$this->dbg_printf("WebsocketHandler::recv() recv header - cmd : %d, route - %d, bin - %d, ext - %d, len - %d \n"
											, $this->ws_data["cmd_id"], $this->ws_data["route_case"], $this->ws_data["is_binary"]
											, $this->ws_data["is_extend"], $this->ws_data["length"]);

						if($this->ws_data["length"] == 0) {
							$this->ws_data["data"] = null;

						} else {
							if(($readRet = socket_recv($this->socket_fd, $this->ws_data["data"], $this->ws_data["length"], MSG_WAITALL)) < 0) {
								$this->dbg_printf("WebsocketHandler::recv() recv body failed : %s\n", socket_strerror(socket_last_error()));

								$is_success = false;
								$this->term();

							} else if ($readRet == 0) {
								$this->dbg_printf("WebsocketHandler::recv() connection disconnected\n");

								$is_success = false;
								$this->term();
							}
						}

						break;
					}
				}
			}

			if($is_success == false) {
				return $is_success;
			}

			return $this->ws_data;
		}

		public function send($_cmd_id, $_data) {
			$is_success = true;
			$is_regist  = (($_data === "uri_regist") ? true : false);

			if(!$is_regist && !$this->is_p_run) {
				$this->init();

				if(!$this->is_noti_fail) {
					$this->dbg_printf("WebsocketHandler::send() send failed, not running\n");
					$this->is_noti_fail;
				}

				return false;
			}

			$this->init_data();
			$this->ws_data["cmd_id"] 		= $_cmd_id;
			$this->ws_data["route_case"] 	= $this->ws_route_to;
			$this->ws_data["is_binary"] 	= false;
			$this->ws_data["is_extend"] 	= true;
			$this->ws_data["length"] 		= 0;

			$length_regist = 0;

			if($is_regist) {
				$ext_regist 			= array();
				$ext_regist["tag"] 		= 0;
				$ext_regist["length"] 	= strlen($this->uri);
				$ext_regist["value"] 	= $this->uri;

				$length_regist = self::WS_EXT_HEADER_SIZE + $ext_regist["length"];
			}

			if($is_regist) {
				$ext_data["tag"] 	= 0x01;
				$ext_data["length"] = 0;

			} else {
				$ext_data["tag"] 	= 0x10;
				$ext_data["length"] = strlen($_data);
			}

			$length_data 			 = self::WS_EXT_HEADER_SIZE + $ext_data["length"];
			$this->ws_data["length"] = $length_regist + $length_data;

			if($ext_data["length"] > 0) {
				$ext_data["value"] = $_data;
			}

			if($is_regist) {
				array_push($this->ws_data["data"], $ext_regist);
			}
			array_push($this->ws_data["data"], $ext_data);

			// pack Header, Data;
			$pack_header = pack("ccccl"	, $this->ws_data["cmd_id"], $this->ws_data["route_case"]
										, $this->ws_data["is_binary"], $this->ws_data["is_extend"], $this->ws_data["length"]);

			$idx;
			$pack_data = null;
			$data_cnt  = count($this->ws_data["data"]);

			for($idx = 0; $idx < $data_cnt; $idx++) {
				$temp_ext	= $this->ws_data["data"][$idx];
				$pack_data .= pack("ll", $temp_ext["tag"], $temp_ext["length"]);

				if(isset($temp_ext["value"]) == true) {
					$pack_data .= pack("a*", $temp_ext["value"]);
				}
			}
			// send
			try {
				$ps_type 		= 0;
				$pack_ps_type 	= pack("l", $ps_type);
				$send_size 		= strlen($pack_ps_type);

				$send_ret = socket_send($this->socket_fd, $pack_ps_type, $send_size, 0);
				if($send_ret == false) {
					$this->dbg_printf("WebsocketHandler::send() send process type failed : %s\n", socket_strerror(socket_last_error()));

					throw "error";

				} else if(($send_ret == 0) || ($send_ret != $send_size)) {
					$this->dbg_printf("WebsocketHandler::send() send invalid size(%d) : %s\n", $sendRet, socket_strerror(socket_last_error()));

					throw "error";
				}

				$send_size = strlen($pack_header);
				$send_ret  = socket_send($this->socket_fd, $pack_header, $send_size, 0);
				if($send_ret == false) {
					$this->dbg_printf("WebsocketHandler::send() send process type failed : %s\n", socket_strerror(socket_last_error()));

					throw "error";

				} else if(($send_ret == 0) || ($send_ret != $send_size)) {
					$this->dbg_printf("WebsocketHandler::send() send invalid size(%d) : %s\n", $send_ret, socket_strerror(socket_last_error()));

					throw "error";
				}

				$send_size = strlen($pack_data);
				if($send_size > 0) {
					$send_ret  = socket_send($this->socket_fd, $pack_data, $send_size, 0);
					if($send_ret == false) {
						$this->dbg_printf("WebsocketHandler::send() send process type failed : %s\n", socket_strerror(socket_last_error()));

						throw "error";

					} else if(($send_ret == 0) || ($send_ret != $send_size)) {
						$this->dbg_printf("WebsocketHandler::send() send invalid size(%d) : %s\n", $send_ret, socket_strerror(socket_last_error()));

						throw "error";
					}
				}

			} catch(Exception $e) {
				$is_success = false;
			}

			return $is_success;
		}

		public function send_binary($_cmd_id, $_data, $_length) {
			$is_success = true;

			if(!$this->is_p_run) {
				$this->init();

				if(!$this->is_noti_fail) {
					$this->dbg_printf("WebsocketHandler::send() send failed, not running\n");
					$this->is_noti_fail = true;
				}

				return false;
			}

			$this->init_data();
			$this->ws_data["cmd_id"] 		= $_cmd_id;
			$this->ws_data["route_case"] 	= $this->ws_route_to;
			$this->ws_data["is_binary"] 	= true;
			$this->ws_data["is_extend"] 	= true;
			$this->ws_data["length"] 		= 0;

			$ext_data["tag"] 	= 0x10;
			$ext_data["length"] = $_length;

			$length_data = self::WS_EXT_HEADER_SIZE + $ext_data["length"];

			$this->ws_data["length"] = $length_data;

			if($ext_data["length"] > 0) {
				$ext_data["value"] = $_data;
			}

			// pack Header, Data;
			$pack_header = pack("ccccl"	, $this->ws_data["cmd_id"], $this->ws_data["route_case"]
										, $this->ws_data["is_binary"], $this->ws_data["is_extend"], $this->ws_data["length"]);

			$pack_data   = pack("ll", $ext_data["tag"], $ext_data["length"]);
			for($idx = 0; $idx < $_length; $idx++) {
				$pack_data .= pack("c", $ext_data["value"][$idx]);
			}

			// send
			try {
				$ps_type 		= 0;
				$pack_ps_type 	= pack("l", $ps_type);
				$send_size		= strlen($pack_ps_type);

				$send_ret = socket_send($this->socket_fd, $pack_ps_type, $send_size, 0);
				if($send_ret == false) {
					$this->dbg_printf("WebsocketHandler::send() send process type failed : %s\n", socket_strerror(socket_last_error()));

					throw "error";

				} else if(($send_ret == 0) || ($send_ret != $send_size)) {
					$this->dbg_printf("WebsocketHandler::send() send invalid size(%d) : %s\n", $send_ret, socket_strerror(socket_last_error()));

					throw "error";
				}

				$send_size = strlen($pack_header);
				$send_ret = socket_send($this->socket_fd, $pack_header, $send_size, 0);
				if($send_ret == false) {
					$this->dbg_printf("WebsocketHandler::send() send process type failed : %s\n", socket_strerror(socket_last_error()));

					throw "error";

				} else if(($send_ret == 0) || ($send_ret != $send_size)) {
					$this->dbg_printf("WebsocketHandler::send() send invalid size(%d) : %s\n", $send_ret, socket_strerror(socket_last_error()));

					throw "error";
				}

				$send_size = strlen($pack_data);
				if($send_size > 0) {
					$send_ret   = socket_send($this->socket_fd, $pack_data, $send_size, 0);
					if($send_ret == false) {
						$this->dbg_printf("WebsocketHandler::send() send process type failed : %s\n", socket_strerror(socket_last_error()));

						throw "error";

					} else if(($send_ret == 0) || ($send_ret != $send_size)) {
						$this->dbg_printf("WebsocketHandler::send() send invalid size(%d) : %s\n", $send_ret, socket_strerror(socket_last_error()));

						throw "error";
					}
				}

			} catch(Exception $e) {
				$is_success = false;
			}

			$this->ws_data = null;

			return $is_success;
		}

		public function set_route_to($_type) {
			$this->ws_route_to = $_type;
		}

		public function get_uri_name() {
			return $this->uri;
		}

		public function get_ip_addr() {
			return $this->ip_addr;
		}

		public function is_run() {
			return $this->is_p_run;
		}

		public function is_term() {
			return $this->is_p_term;
		}

		public function set_debug_print() {
			$this->is_debug = true;
		}

		public function dbg_printf() {
			if($this->is_debug === false) {
				return;
			}

			$args = func_get_args();
			$fmt  = array_shift($args);

			echo vsprintf($fmt, $args);
		}
	}


?>
