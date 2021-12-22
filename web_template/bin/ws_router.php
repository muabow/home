<?php
	set_time_limit(0);
	ob_implicit_flush();

	class PHPWebSocket {
		// debug message print flag
		const DEBUG_PRINT_MSG		= false;

		// use the default configuration file when there is no configuration file input.

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

		// internal
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

		const WS_SOCKET_BACKLOG						= 512;

		// global vars
		public $wsClients		= array();
		public $wsRead			= array();
		public $wsOnEvents		= array();
		public $wsClientIPCount	= array();
		public $routeList		= array();
		public $wsClientCount 	= 0;

		/*
		 $this->wsClients[ integer ClientID ] = array(
		 0 => resource  Socket,                            // client socket
		 1 => string    MessageBuffer,                     // a blank string when there's no incoming frames
		 2 => integer   ReadyState,                        // between 0 and 3
		 3 => integer   LastRecvTime,                      // set to time() when the client is added
		 4 => int/false PingSentTime,                      // false when the server is not waiting for a pong
		 5 => int/false CloseStatus,                       // close status that wsOnClose() will be called with
		 6 => integer   IPv4,                              // client's IP stored as a signed long, retrieved from ip2long()
		 7 => int/false FramePayloadDataLength,            // length of a frame's payload data, reset to false when all frame data has been read (cannot reset to 0, to allow reading of mask key)
		 8 => integer   FrameBytesRead,                    // amount of bytes read for a frame, reset to 0 when all frame data has been read
		 9 => string    FrameBuffer,                       // joined onto end as a frame's data comes in, reset to blank string when all frame data has been read
		 10 => integer  MessageOpcode,                     // stored by the first frame for fragmented messages, default value is 0
		 11 => integer  MessageBufferLength,               // the payload data length of MessageBuffer
		 12 => string	URI								   // URI, e.g. /test
		 13 => bool		Type							   //  0: ws, 1: ps
		 14 => integer  process type ID					   // -1: ws, 0: Continuous socket, 0>: Packet socket
		 )

		 $wsRead[ integer ClientID ] = resource Socket         // this one-dimensional array is used for socket_select()
		 // $wsRead[ 0 ] is the socket listening for incoming client connections
		 // $wsRead[ 1 ] is the socket listening for incoming binary data

		 $wsClientCount = integer ClientCount                  // amount of clients currently connected

		 $wsClientIPCount[ integer IP ] = integer ClientCount  // amount of clients connected per IP v4 address
		 */

		// server state functions
		function wsStartServer($_configPath) {
			$host		= "localhost";
			$xmlData 	= simplexml_load_string(file_get_contents($_configPath));
			$port_http  = intval($xmlData->server->http_port);
			$port_inner = intval($xmlData->server->inner_port);

			$this->routeList = json_decode(json_encode($xmlData->routing_key), true);

			if( isset($this->wsRead[0]) ) {
				return false;
			}

			if( isset($this->wsRead[1]) ) {
				return false;
			}

			if( !$this->wsRead[0] = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) ) {
				return false;
			}

			if( !$this->wsRead[1] = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) ) {
				return false;
			}

			if( !socket_set_option($this->wsRead[0], SOL_SOCKET, SO_REUSEADDR, 1) ) {
				socket_close($this->wsRead[0]);
				return false;
			}

			if( !socket_set_option($this->wsRead[1], SOL_SOCKET, SO_REUSEADDR, 1) ) {
				socket_close($this->wsRead[1]);
				return false;
			}

			if( !socket_bind($this->wsRead[0], $host, $port_http) ) {
				socket_close($this->wsRead[0]);
				return false;
			}

			if( !socket_bind($this->wsRead[1], $host, $port_inner) ) {
				socket_close($this->wsRead[1]);
				return false;
			}

			if( !socket_listen($this->wsRead[0], self::WS_SOCKET_BACKLOG) ) {
				socket_close($this->wsRead[0]);
				return false;
			}

			if( !socket_listen($this->wsRead[1], self::WS_SOCKET_BACKLOG) ) {
				socket_close($this->wsRead[1]);
				return false;
			}

			$write  = array();
			$except = array();

			$nextPingCheck = time() + 1;
			while( isset($this->wsRead[0]) ) {
				$changed = $this->wsRead;
				$result  = socket_select($changed, $write, $except, 1);

				if( $result === false ) {
					socket_close($this->wsRead[0]);
					return false;

				} else if( $result > 0 ) {
					foreach( $changed as $clientID => $socket ) {
						if( $clientID > 1 ) {
							// client socket changed
							if( $this->wsClients[$clientID][13] == 0 ) {
								$buffer = '';
								$bytes  = @socket_recv($socket, $buffer, 4096, 0);

								if( $bytes === false ) {
									// error on recv, remove client socket (will check to send close frame)
									$this->wsSendClientClose($clientID, self::WS_STATUS_PROTOCOL_ERROR);

								} else if( $bytes > 0 ) {
									// process handshake or frame(s)
									if( !$this->wsProcessClient($clientID, $buffer, $bytes) ) {
										$this->wsSendClientClose($clientID, self::WS_STATUS_PROTOCOL_ERROR);
									}

								} else {
									// 0 bytes received from client, meaning the client closed the TCP connection
									$this->wsRemoveClient($clientID);
								}

							} else {
								$buffer = '';
								$bytes  = @socket_recv($socket, $buffer, 4, 0);

								if( $bytes === false ) {
									// error on recv, remove client socket (will check to send close frame)
									$this->wsSendClientClose($clientID, self::WS_STATUS_PROTOCOL_ERROR);

								} else if( $bytes > 0 ) {
									$psType = unpack("l", substr($buffer, 0, 4))[1];
									$isBinary = false;

									// Continuous socket communication
									if( $psType == 0 ) {
										$buffer = '';
										$bytes  = @socket_recv($socket, $buffer, 72, 0);

										$cmd  = $psType;
										$rsvd = 0;

										if( ($rc = substr($buffer, 0, 64)) )	$uri      = trim($rc);
										if( ($rc = substr($buffer, 64, 4)) )	$isBinary = (unpack("l",  $rc)[1] == 1 ? true : false);
										if( ($rc = substr($buffer, 68, 4)) )	$bodyLen  = unpack("l",  $rc)[1];

										$routeKey = "/" . $uri;

									} else {
										// recv header packet
										$buffer = '';
										$bytes  = @socket_recv($socket, $buffer, 8, 0);
										if( ($rc = substr($buffer, 0, 1)) )	$cmd     = unpack("c",  $rc)[1];
										if( ($rc = substr($buffer, 1, 3)) )	$rsvd    = unpack("c",  $rc)[1];
										if( ($rc = substr($buffer, 4, 4)) )	$bodyLen = unpack("l",  $rc)[1];

										$routeKey = "/" . array_keys($this->routeList, $psType)[0];
									}

									$this->wsClients[$clientID][14] = $psType;

									// recv body packet
									$data = "";
									$remainSize = $bodyLen;
									$recvSize   = 0;

									while( $bodyLen != $recvSize ) {
										$buffer = '';
										$rc  = @socket_recv($socket, $buffer, $remainSize, 0);

										$data     .= $buffer;
										$recvSize += $rc;

										if( $recvSize < $bodyLen ) {
											$remainSize = $bodyLen - $recvSize;
										}
									}

									// Register the client URI
									if( $bodyLen == 0 && $this->wsClients[$clientID][12] == "" ) {
										if( Self::DEBUG_PRINT_MSG ) $this->log("Register the client URI : [{$clientID}] {$routeKey}");
										$this->wsClients[$clientID][12] = $routeKey;
										continue;
									}

									foreach( $this->wsRead as $id => $client ) {
										if( isset($this->wsClients[$id][12]) && $this->wsClients[$id][13] == 0 ) {
											$uri = $this->wsClients[$id][12];

											if( $routeKey == $uri ) {
												if( Self::DEBUG_PRINT_MSG ) printf("to ws | %s | %-7s | %-20s | %-2s | %s | %-4s | %-s\n", ($isBinary == true ? "B" : "W"), ($psType == 0 ? "session" : $psType), substr($routeKey, 1), $cmd, $rsvd, $bodyLen, $data);
												$this->wsSend($id, $data, $isBinary);
											}
										}
									}

								} else {
									// 0 bytes received from client, meaning the client closed the TCP connection
									$this->wsRemoveClient($clientID);
								}
							}

						} else {
							// ws connector
							// $clientID = 0: ws, websocket connector
							// $clientID = 1: ps, process to web browser
							// listen socket changed
							$client = socket_accept($this->wsRead[$clientID]);

							if( $client !== false ) {
								// fetch client IP as integer
								$clientIP = '';
								$result   = socket_getpeername($client, $clientIP);
								$clientIP = ip2long($clientIP);

								if( $result !== false && $this->wsClientCount < self::WS_MAX_CLIENTS && (!isset($this->wsClientIPCount[$clientIP]) || $this->wsClientIPCount[$clientIP] < self::WS_MAX_CLIENTS_PER_IP) ) {
									$this->wsAddClient($client, $clientIP, $clientID);

								} else {
									socket_close($client);
								}
							}
						}
					} // end of foreach
				}

				if( time() >= $nextPingCheck ) {
					$this->wsCheckIdleClients();
					$nextPingCheck = time() + 1;
				}
			} // end of while

			return true;
			// returned when wsStopServer() is called
		}

		function wsStopServer() {
			// check if server is not running
			if( !isset($this->wsRead[0]) ) {
				return false;
			}

			// close all client connections
			foreach( $this->wsClients as $clientID => $client ) {
				// if the client's opening handshake is complete, tell the client the server is 'going away'
				if( $client[2] != self::WS_READY_STATE_CONNECTING ) {
					$this->wsSendClientClose($clientID, self::WS_STATUS_GONE_AWAY);
				}
				socket_close($client[0]);
			}

			// close the socket which listens for incoming clients
			socket_close($this->wsRead[0]);

			// reset variables
			$this->wsRead			= array();
			$this->wsClients		= array();
			$this->wsClientIPCount	= array();
			$this->routeList		= array();
			$this->wsClientCount	= 0;

			return true;
		}

		// client timeout functions
		function wsCheckIdleClients() {
			$time = time();

			foreach( $this->wsClients as $clientID => $client ) {
				if( $client[13] == 1 ) { // type : process socket
					continue;
				}

				if( $client[2] != self::WS_READY_STATE_CLOSED ) {
					// client ready state is not closed
					if( $client[4] !== false ) {
						// ping request has already been sent to client, pending a pong reply
						if( $time >= $client[4] + self::WS_TIMEOUT_PONG ) {
							// client didn't respond to the server's ping request in self::WS_TIMEOUT_PONG seconds
							$this->wsSendClientClose($clientID, self::WS_STATUS_TIMEOUT);
							$this->wsRemoveClient($clientID);
						}

					} else if( $time >= $client[3] + self::WS_TIMEOUT_RECV ) {
						// last data was received >= self::WS_TIMEOUT_RECV seconds ago
						if( $client[2] != self::WS_READY_STATE_CONNECTING ) {
							// client ready state is open or closing
							$this->wsClients[$clientID][4] = time();
							$this->wsSendClientMessage($clientID, self::WS_OPCODE_PING, '');

						} else {
							// client ready state is connecting
							$this->wsRemoveClient($clientID);
						}
					}
				}
			}

			return ;
		}

		// client existence functions
		function wsAddClient($_socket, $_clientIP, $_clientID) {
			// increase amount of clients connected
			$this->wsClientCount++;

			// increase amount of clients connected on this client's IP
			if( isset($this->wsClientIPCount[$_clientIP]) ) {
				$this->wsClientIPCount[$_clientIP]++;

			} else {
				$this->wsClientIPCount[$_clientIP] = 1;
			}

			// fetch next client ID
			$clientID = $this->wsGetNextClientID();

			// store initial client data
			$this->wsClients[$clientID] = array($_socket, '', self::WS_READY_STATE_CONNECTING, time(), false, 0, $_clientIP, false, 0, '', 0, 0, '', $_clientID, -1);

			// store socket - used for socket_select()
			$this->wsRead[$clientID] = $_socket;

			return ;
		}

		function wsRemoveClient($_clientID) {
			// fetch close status (which could be false), and call wsOnClose
			$closeStatus = $this->wsClients[$_clientID][5];

			if( array_key_exists('close', $this->wsOnEvents) ) {
				foreach ($this->wsOnEvents['close'] as $func) {
					$func($_clientID, $closeStatus);
				}
			}

			// close socket
			$socket = $this->wsClients[$_clientID][0];
			socket_close($socket);

			// decrease amount of clients connected on this client's IP
			$clientIP = $this->wsClients[$_clientID][6];
			if ($this->wsClientIPCount[$clientIP] > 1) {
				$this->wsClientIPCount[$clientIP]--;

			} else {
				unset($this->wsClientIPCount[$clientIP]);
			}

			// decrease amount of clients connected
			$this->wsClientCount--;

			// remove socket and client data from arrays
			unset($this->wsRead[$_clientID], $this->wsClients[$_clientID]);

			return ;
		}

		// client data functions
		function wsGetNextClientID() {
			$i = 1;

			// starts at 1 because 0 is the listen socket
			while( isset($this->wsRead[$i]) ) {
				$i++;
			}

			return $i;
		}

		function wsGetClientSocket($_clientID) {

			return $this->wsClients[$_clientID][0];
		}

		// client read functions
		function wsProcessClient($_clientID, &$_buffer, $_bufferLength) {
			if( $this->wsClients[$_clientID][2] == self::WS_READY_STATE_OPEN ) {
				// handshake completed
				$result = $this->wsBuildClientFrame($_clientID, $_buffer, $_bufferLength);

			} else if( $this->wsClients[$_clientID][2] == self::WS_READY_STATE_CONNECTING ) {
				// handshake not completed
				$result = $this->wsProcessClientHandshake($_clientID, $_buffer);

				if( $result ) {
					$this->wsClients[$_clientID][2] = self::WS_READY_STATE_OPEN;

					if( array_key_exists('open', $this->wsOnEvents) ) {
						foreach( $this->wsOnEvents['open'] as $func ) {
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

		function wsBuildClientFrame($_clientID, &$_buffer, $_bufferLength) {
			// increase number of bytes read for the frame, and join buffer onto end of the frame buffer
			$this->wsClients[$_clientID][8] += $_bufferLength;
			$this->wsClients[$_clientID][9] .= $_buffer;

			// check if the length of the frame's payload data has been fetched, if not then attempt to fetch it from the frame buffer
			if( $this->wsClients[$_clientID][7] !== false || $this->wsCheckSizeClientFrame($_clientID) == true ) {
				// work out the header length of the frame
				$headerLength = ($this->wsClients[$_clientID][7] <= 125 ? 0 : ($this->wsClients[$_clientID][7] <= 65535 ? 2 : 8)) + 6;

				// check if all bytes have been received for the frame
				$frameLength = $this->wsClients[$_clientID][7] + $headerLength;
				if( $this->wsClients[$_clientID][8] >= $frameLength ) {
					// check if too many bytes have been read for the frame (they are part of the next frame)
					$nextFrameBytesLength = $this->wsClients[$_clientID][8] - $frameLength;

					if( $nextFrameBytesLength > 0 ) {
						$this->wsClients[$_clientID][8] -= $nextFrameBytesLength;
						$nextFrameBytes = substr($this->wsClients[$_clientID][9], $frameLength);
						$this->wsClients[$_clientID][9] = substr($this->wsClients[$_clientID][9], 0, $frameLength);
					}

					// process the frame
					$result = $this->wsProcessClientFrame($_clientID);

					// check if the client wasn't removed, then reset frame data
					if( isset($this->wsClients[$_clientID]) ) {
						$this->wsClients[$_clientID][7] = false;
						$this->wsClients[$_clientID][8] = 0;
						$this->wsClients[$_clientID][9] = '';
					}

					// if there's no extra bytes for the next frame, or processing the frame failed, return the result of processing the frame
					if( $nextFrameBytesLength <= 0 || !$result ) {
						return $result;
					}

					// build the next frame with the extra bytes
					return $this->wsBuildClientFrame($_clientID, $nextFrameBytes, $nextFrameBytesLength);
				}
			}

			return true;
		}

		function wsCheckSizeClientFrame($_clientID) {
			// check if at least 2 bytes have been stored in the frame buffer
			if( $this->wsClients[$_clientID][8] > 1 ) {
				// fetch payload length in byte 2, max will be 127
				$payloadLength = ord(substr($this->wsClients[$_clientID][9], 1, 1)) & 127;

				if( $payloadLength <= 125 ) {
					// actual payload length is <= 125
					$this->wsClients[$_clientID][7] = $payloadLength;

				} else if( $payloadLength == 126 ) {
					// actual payload length is <= 65,535
					if( substr($this->wsClients[$_clientID][9], 3, 1) !== false ) {
						// at least another 2 bytes are set
						$payloadLengthExtended = substr($this->wsClients[$_clientID][9], 2, 2);
						$array = unpack('na', $payloadLengthExtended);
						$this->wsClients[$_clientID][7] = $array['a'];
					}

				} else {
					// actual payload length is > 65,535
					if( substr($this->wsClients[$_clientID][9], 9, 1) !== false ) {
						// at least another 8 bytes are set
						$payloadLengthExtended = substr($this->wsClients[$_clientID][9], 2, 8);

						// check if the frame's payload data length exceeds 2,147,483,647 (31 bits)
						// the maximum integer in PHP is "usually" this number. More info: http://php.net/manual/en/language.types.integer.php
						$payloadLengthExtended32_1 = substr($payloadLengthExtended, 0, 4);
						$array = unpack('Na', $payloadLengthExtended32_1);

						if( $array['a'] != 0 || ord(substr($payloadLengthExtended, 4, 1)) & 128 ) {
							$this->wsSendClientClose($_clientID, self::WS_STATUS_MESSAGE_TOO_BIG);

							return false;
						}

						// fetch length as 32 bit unsigned integer, not as 64 bit
						$payloadLengthExtended32_2 = substr($payloadLengthExtended, 4, 4);
						$array = unpack('Na', $payloadLengthExtended32_2);

						// check if the payload data length exceeds 2,147,479,538 (2,147,483,647 - 14 - 4095)
						// 14 for header size, 4095 for last recv() next frame bytes
						if( $array['a'] > 2147479538 ) {
							$this->wsSendClientClose($_clientID, self::WS_STATUS_MESSAGE_TOO_BIG);

							return false;
						}

						// store frame payload data length
						$this->wsClients[$_clientID][7] = $array['a'];
					}
				}

				// check if the frame's payload data length has now been stored
				if( $this->wsClients[$_clientID][7] !== false ) {

					// check if the frame's payload data length exceeds self::WS_MAX_FRAME_PAYLOAD_RECV
					if( $this->wsClients[$_clientID][7] > self::WS_MAX_FRAME_PAYLOAD_RECV ) {
						$this->wsClients[$_clientID][7] = false;
						$this->wsSendClientClose($_clientID, self::WS_STATUS_MESSAGE_TOO_BIG);

						return false;
					}

					// check if the message's payload data length exceeds 2,147,483,647 or self::WS_MAX_MESSAGE_PAYLOAD_RECV
					// doesn't apply for control frames, where the payload data is not internally stored
					$controlFrame = (ord(substr($this->wsClients[$_clientID][9], 0, 1)) & 8) == 8;
					if( !$controlFrame ) {
						$newMessagePayloadLength = $this->wsClients[$_clientID][11] + $this->wsClients[$_clientID][7];

						if( $newMessagePayloadLength > self::WS_MAX_MESSAGE_PAYLOAD_RECV || $newMessagePayloadLength > 2147483647 ) {
							$this->wsSendClientClose($_clientID, self::WS_STATUS_MESSAGE_TOO_BIG);

							return false;
						}
					}

					return true;
				}
			}

			return false;
		}

		function wsProcessClientFrame($_clientID) {
			// store the time that data was last received from the client
			$this->wsClients[$_clientID][3] = time();

			// fetch frame buffer
			$buffer = &$this->wsClients[$_clientID][9];

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
			$seek = $this->wsClients[$_clientID][7] <= 125 ? 2 : ($this->wsClients[$_clientID][7] <= 65535 ? 4 : 10);

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
			if( $opcode != self::WS_OPCODE_CONTINUATION && $this->wsClients[$_clientID][11] > 0 ) {
				// clear the message buffer
				$this->wsClients[$_clientID][11] = 0;
				$this->wsClients[$_clientID][1] = '';
			}

			// check if the frame is marked as the final frame in the message
			if( $fin == self::WS_FIN ) {
				// check if this is the first frame in the message
				if( $opcode != self::WS_OPCODE_CONTINUATION ) {
					// process the message
					return $this->wsProcessClientMessage($_clientID, $opcode, $data, $this->wsClients[$_clientID][7]);

				} else {
					// increase message payload data length
					$this->wsClients[$_clientID][11] += $this->wsClients[$_clientID][7];

					// push frame payload data onto message buffer
					$this->wsClients[$_clientID][1] .= $data;

					// process the message
					$result = $this->wsProcessClientMessage($_clientID, $this->wsClients[$_clientID][10], $this->wsClients[$_clientID][1], $this->wsClients[$_clientID][11]);

					// check if the client wasn't removed, then reset message buffer and message opcode
					if( isset($this->wsClients[$_clientID]) ) {
						$this->wsClients[$_clientID][1] = '';
						$this->wsClients[$_clientID][10] = 0;
						$this->wsClients[$_clientID][11] = 0;
					}

					return $result;
				}

			} else {
				// check if the frame is a control frame, control frames cannot be fragmented
				if( $opcode & 8 ) {
					return false;
				}

				// increase message payload data length
				$this->wsClients[$_clientID][11] += $this->wsClients[$_clientID][7];

				// push frame payload data onto message buffer
				$this->wsClients[$_clientID][1] .= $data;

				// if this is the first frame in the message, store the opcode
				if( $opcode != self::WS_OPCODE_CONTINUATION ) {
					$this->wsClients[$_clientID][10] = $opcode;
				}
			}

			return true;
		}

		function wsProcessClientMessage($_clientID, $_opcode, &$_data, $_dataLength) {
			// check opcodes
			if( $_opcode == self::WS_OPCODE_PING ) {
				// received ping message
				return $this->wsSendClientMessage($_clientID, self::WS_OPCODE_PONG, $_data);

			} else if( $_opcode == self::WS_OPCODE_PONG ) {
				// received pong message (it's valid if the server did not send a ping request for this pong message)
				if( $this->wsClients[$_clientID][4] !== false ) {
					$this->wsClients[$_clientID][4] = false;
				}

			} else if( $_opcode == self::WS_OPCODE_CLOSE ) {
				// received close message
				if( substr($_data, 1, 1) !== false ) {
					$array  = unpack('na', substr($_data, 0, 2));
					$status = $array['a'];

				} else {
					$status = false;
				}

				if( $this->wsClients[$_clientID][2] == self::WS_READY_STATE_CLOSING ) {
					// the server already sent a close frame to the client, this is the client's close frame reply
					// (no need to send another close frame to the client)
					$this->wsClients[$_clientID][2] = self::WS_READY_STATE_CLOSED;

				} else {
					// the server has not already sent a close frame to the client, send one now
					$this->wsSendClientClose($_clientID, self::WS_STATUS_NORMAL_CLOSE);
				}

				$this->wsRemoveClient($_clientID);

			} else if( $_opcode == self::WS_OPCODE_TEXT || $_opcode == self::WS_OPCODE_BINARY ) {
				if (array_key_exists('message', $this->wsOnEvents)) {
					foreach ($this->wsOnEvents['message'] as $func) {
						$func($_clientID, $_data, $_dataLength, $_opcode == self::WS_OPCODE_BINARY);
					}
				}

			} else {
				// unknown opcode
				return false;
			}

			return true;
		}

		function wsProcessClientHandshake($_clientID, &$_buffer) {
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
			$this->wsClients[$_clientID][12] = $requestParts[1];

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
			$socket = $this->wsClients[$_clientID][0];

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
		function wsSendClientMessage($_clientID, $_opcode, $_message) {
			// check if client ready state is already closing or closed
			if( $this->wsClients[$_clientID][2] == self::WS_READY_STATE_CLOSING || $this->wsClients[$_clientID][2] == self::WS_READY_STATE_CLOSED ) {
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
				$socket = $this->wsClients[$_clientID][0];
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

		function wsSendClientClose( $_clientID, $_status = false ) {
			// check if client ready state is already closing or closed
			if( $this->wsClients[$_clientID][2] == self::WS_READY_STATE_CLOSING || $this->wsClients[$_clientID][2] == self::WS_READY_STATE_CLOSED ) {
				return true;
			}

			// store close status
			$this->wsClients[$_clientID][5] = $_status;

			// send close frame to client
			$_status = $_status !== false ? pack('n', $_status) : '';
			$this->wsSendClientMessage($_clientID, self::WS_OPCODE_CLOSE, $_status);

			// set client ready state to closing
			$this->wsClients[$_clientID][2] = self::WS_READY_STATE_CLOSING;

			return ;
		}

		// client non-internal functions
		function wsClose($_clientID) {
			return $this->wsSendClientClose($_clientID, self::WS_STATUS_NORMAL_CLOSE);
		}

		function wsSend($_clientID, $_message, $_binary = false) {
			return $this->wsSendClientMessage($_clientID, $_binary ? self::WS_OPCODE_BINARY : self::WS_OPCODE_TEXT, $_message);
		}

		function log($_message) {
			echo date('Y-m-d H:i:s: ') . $_message . "\n";

			return ;
		}

		function bind($_type, $_func) {
			if( !isset($this->wsOnEvents[$_type]) ) {
				$this->wsOnEvents[$_type] = array();
			}

			$this->wsOnEvents[$_type][] = $_func;

			return ;
		}

		function unbind( $_type = '' ) {
			if( $_type ) {
				unset($this->wsOnEvents[$_type]);

			} else {
				$this->wsOnEvents = array();
			}

			return ;
		}

	} // end of class


	function websocket_open($_host = '', $_uri = '', $_port = 80, $_headers = '', &$_error_string = '', $_timeout = 10) {
		// Generate a key (to convince server that the update is not random)
		// The key is for the server to prove it i websocket aware. (We know it is)
		// $key = base64_encode(uniqid());
		$key = "vWZSc6tSM3ZXPcdHPzJKMA==";

		$header = "GET /" . $_uri ." HTTP/1.1\r\n" . "Host: $_host\r\n" . "pragma: no-cache\r\n" . "Upgrade: WebSocket\r\n" . "Connection: Upgrade\r\n" . "Sec-WebSocket-Key: $key\r\n" . "Sec-WebSocket-Version: 13\r\n";
		// Add extra headers
		if( !empty($_headers) ) {
			foreach( $_headers as $h ) {
				$header .= $h . "\r\n";
			}
		}
		// Add end of header marker
		$header .= "\r\n";

		// Connect to server
		$host = $_host ? $_host : "127.0.0.1";
		$port = $_port < 1 ? 80 : $_port;
		$sp = fsockopen($host, $port, $errno, $errstr, $_timeout);

		if (!$sp) {
			$_error_string = "Unable to connect to websocket server: $errstr ($errno)";
			return false;
		}
		// Set timeouts
		// stream_set_timeout($sp, $_timeout);

		//Request upgrade to websocket
		$rc = fwrite($sp, $header);
		if( !$rc ) {
			$_error_string = "Unable to send upgrade header to websocket server: $errstr ($errno)";
			return false;
		}

		// Read response into an assotiative array of headers. Fails if upgrade failes.
		$reaponse_header = fread($sp, 1024);
		// status code 101 indicates that the WebSocket handshake has completed.
		if( !strpos($reaponse_header, " 101 ") || !strpos($reaponse_header, 'Sec-WebSocket-Accept: ') ) {
			$_error_string = "Server did not accept to upgrade connection to websocket." . $reaponse_header . E_USER_ERROR;
			return false;
		}
		// The key we send is returned, concatenate with "258EAFA5-E914-47DA-95CA-
		// C5AB0DC85B11" and then base64-encoded. one can verify if one feels the need...

		return $sp;
	}

	function websocket_write($_sp, $_data, $_final = true) {
		// Assamble header: FINal 0x80 | Opcode 0x02
		$header = chr(($_final ? 0x80 : 0) | 0x02);

		// 0x02 binary
		// Mask 0x80 | payload length (0-125)
		if( strlen($_data) < 126 ) {
			$header .= chr(0x80 | strlen($_data));

		} else if( strlen($_data) < 0xFFFF ) {
			$header .= chr(0x80 | 126) . pack("n", strlen($_data));

		} else {
			$header .= chr(0x80 | 127) . pack("N", 0) . pack("N", strlen($_data));
		}
		// Add mask
		$mask    = pack("N", rand(1, 0x7FFFFFFF));
		$header .= $mask;

		// Mask application data.
		for( $i = 0 ; $i < strlen($_data) ; $i++ ) {
			$_data[$i] = chr(ord($_data[$i]) ^ ord($mask[$i % 4]));
		}

		return fwrite($_sp, $header . $data);
	}

	function websocket_read($_sp, &$_error_string = NULL) {
		$data = "";
		do {
			// Read header
			$header = fread($_sp, 2);
			if( !$header) {
				$_error_string = "Reading header from websocket failed.";
				return false;
			}

			$_opcode     = ord($header[0]) & 0x0F;
			$final       = ord($header[0]) & 0x80;
			$masked      = ord($header[1]) & 0x80;
			$payload_len = ord($header[1]) & 0x7F;

			// Get payload length extensions
			$ext_len = 0;
			if( $payload_len >= 0x7E ) {
				$ext_len = 2;

				if ($payload_len == 0x7F) {
					$ext_len = 8;
				}

				$header = fread($_sp, $ext_len);
				if( !$header ) {
					$_error_string = "Reading header extension from websocket failed.";
					return false;
				}

				// Set extented paylod length
				$payload_len = 0;
				for( $i = 0 ; $i < $ext_len ; $i++ ) {
					$payload_len += ord($header[$i])<<($ext_len - $i - 1) * 8;
				}
			}

			// Get Mask key
			if( $masked ) {
				$mask = fread($_sp, 4);

				if( !$mask ) {
					$_error_string = "Reading header mask from websocket failed.";
					return false;
				}
			}

			// Get payload
			$frame_data = '';

			do {
				$frame = fread($_sp, $payload_len);
				if( !$frame ) {
					$_error_string = "Reading from websocket failed.";
					return false;
				}

				$payload_len -= strlen($frame);
				$frame_data  .= $frame;

			} while($payload_len>0);

			// Handle ping requests (sort of) send pong and continue to read
			if( $opcode == 9 ) {
				// Assamble header: FINal 0x80 | Opcode 0x0A + Mask on 0x80 with zero payload
				fwrite($_sp, chr(0x8A) . chr(0x80) . pack("N", rand(1, 0x7FFFFFFF)));
				continue;

				// Close
			} else if($opcode == 8 ) {
				fclose($_sp);

				// 0 = continuation frame, 1 = text frame, 2 = binary frame
			} else if( $opcode < 3 ) {
				// Unmask data
				$data_len = strlen($frame_data);
				if( $masked ) {
					for( $i = 0 ; $i < $data_len ; $i++ ) {
						$data .= $frame_data[$i] ^ $mask[$i % 4];
					}

				} else {
					$data .= $frame_data;
				}

			} else {
				continue;
			}
		} while(!$final);

		return $data;
	}

	// when a client sends data to the server
	function wsOnMessage($_clientID, $_message, $_messageLength, $_binary) {
		global $Server;

		$ip = long2ip($Server->wsClients[$_clientID][6]);

		// check if message length is 0
		if( $_messageLength == 0 ) {
			$Server->wsClose($_clientID);
			return ;
		}

		$uri = substr($Server->wsClients[$_clientID][12], 1);
		$psType = "session";

		if( ($rc = substr($_message, 0, 1)) )	$cmd     = unpack("c",  $rc)[1];
		if( ($rc = substr($_message, 1, 4)) )	$rsvd    = unpack("l",  $rc)[1];
		if( ($rc = substr($_message, 4, 8)) )	$bodyLen = unpack("l",  $rc)[1];
		if( ($rc = substr($_message, 8)) )		$data    = $rc;
		if( !isset($data) ) $data = "-";

		try {
			if( !isset($Server->routeList[$uri]) ) {
				throw new Exception("wsOnMessage() URI[{$uri}] not found");
			}
			$psType = intval($Server->routeList[$uri]);

			if( !($extSockFd = socket_create(AF_INET, SOCK_STREAM, 0)) ) {
				throw new Exception("wsOnMessage() socket_create failed : [{$uri}]" . socket_strerror(socket_last_error($extSockFd)));
			}

			if( @socket_connect($extSockFd, 'localhost', $psType) === false ) {
				throw new Exception("wsOnMessage() socket_connect failed [{$uri}]: " . socket_strerror(socket_last_error($extSockFd)));
			}

			if( $Server::DEBUG_PRINT_MSG ) printf("\033[33mto ps | P | %-7s | %-20s | %-2s | %s | %-4s | %-s \033[0m\n", $psType, $uri, $cmd, 0, $bodyLen, $data);

			socket_set_nonblock($extSockFd);

			if( !socket_write($extSockFd, $_message, $_messageLength) ) {
				socket_set_block($extSockFd);
				socket_close($extSockFd);

				throw new Exception("wsOnMessage() socket_write failed [{$uri}]: " . socket_strerror(socket_last_error($extSockFd)));
			}

			socket_set_block($extSockFd);
			socket_close($extSockFd);

		} catch( Exception $_err ) {
			if( $Server::DEBUG_PRINT_MSG ) printf("\033[32;1m%s\033[0m\n", $_err->getMessage());
		}

		foreach( $Server->wsClients as $id => $client ) {
			if( isset($Server->wsClients[$id][12]) && $Server->wsClients[$id][13] == 1 ) {
				$routeKey = substr($Server->wsClients[$id][12], 1);

				if( $routeKey == $uri ) {
					if( $Server::DEBUG_PRINT_MSG ) printf("\033[33mto ps | S | %-7s | %-20s | %-2s | %s | %-4s | %-s \033[0m\n", $psType, $uri, $cmd, 0, $bodyLen, $data);

					$socketFd = $Server->wsClients[$id][0];
					socket_set_nonblock($socketFd);

					if( !($rc = socket_write($socketFd, $_message, $_messageLength)) ) {
						if( $Server::DEBUG_PRINT_MSG ) printf("\033[32;1msession socket_write error : [%s] %s\033[0m\n", $routeKey, socket_strerror(socket_last_error($socketFd)));
						$Server->wsClose($id);
					}

					socket_set_block($socketFd);
				}
			}
		}

		return ;
	}

	// when a client connects
	function wsOnOpen($_clientID) {
		global $Server;

		if( $Server::DEBUG_PRINT_MSG ) {
			$ip		= long2ip($Server->wsClients[$_clientID][6]);
			$uri	= substr($Server->wsClients[$_clientID][12], 1);

			$Server->log("$ip/$uri($_clientID) has connected.");
		}

		return ;
	}

	// when a client closes or lost connection
	function wsOnClose($_clientID, $_status) {
		global $Server;

		if( $Server::DEBUG_PRINT_MSG ) {
			$ip		= long2ip($Server->wsClients[$_clientID][6]);
			$uri	= substr($Server->wsClients[$_clientID][12], 1);

			// disable print packet socket data
			if( !($Server->wsClients[$_clientID][14] > 0) ) {
				$Server->log("$ip/$uri($_clientID) type has disconnected.");
			}
		}

		return ;
	}

	// set process name
	cli_set_process_title("ws_router");

	// default config file path
	$default_config_path = "/opt/interm/conf/ws_router.xml";

	$options = getopt("f:");

	if( isset($options["f"]) ) {
		$default_config_path = $options["f"];
	}

	if( !file_exists($default_config_path) ) {
		printf("config file not found : [%s]\n", $default_config_path);

		return ;
	}

	// start the server
	$Server = new PHPWebSocket();
	$Server->bind('message', 'wsOnMessage');
	$Server->bind('open',	 'wsOnOpen');
	$Server->bind('close',	 'wsOnClose');

	$Server->wsStartServer($default_config_path);
?>
