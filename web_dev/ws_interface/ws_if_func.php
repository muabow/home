<?php
	set_time_limit(0);
	ob_implicit_flush();

	// -----------------------------------------------------------------------------
	// * ps_socket:  socket base connector (old version, C file)
	// * ws_socket:  websocket connector   (old version, javascript file)
	// * ps_session: session base process  (new version, C++ class API)
	// * ws_session: websocket session     (new version, javascript class API)

	//  1/2) socket base : ws_router.xml route table
	//  1-1) ws_socket to ps_socket   구조, O - String 지원
	//  1-2) ws_socket to ps_session  구조, O - String 지원
	//  1-3) ws_socket to ws_socket   구조, X - 지원 안함, only each other
	//  1-4) ws_socket to ws_session  구조, X - 지원 안함, only each other

    //  2-1) ps_socket to ps_socket   구조, X - 지원 안함, only each other
    //  2-2) ps_socket to ps_session  구조, O - String 지원
	//  2-3) ps_socket to ws_socket   구조, O - String 지원
    //  2-4) ps_socket to ws_session  구조, O - String 지원

	//  3/4) session base : Each other, Web only, Native only, All
    //  3-1) ws_session to ps_socket  구조, O - String 지원
    //  3-2) ws_session to ps_session 구조, O - String/Binary 지원
	//  3-3) ws_session to ws_socket  구조, X - String 지원
    //  3-4) ws_session to ws_session 구조, O - String/Binary 지원

    //  4-1) ps_session to ps_socket  구조, O - String 지원
    //  4-2) ps_session to ps_session 구조, O - String/Binary 지원
    //  4-3) ps_session to ws_socket  구조, O - String 지원
    //  4-4) ps_session to ws_session 구조, O - String/Binary 지원


	// -----------------------------------------------------------------------------
	// # issue list
	//  + connection lose 시 간헐적 socket_select i/o broken 발생
    //  - listen/bind socket init/switching method 고도화 필요
    //  - php class API 개발 필요
    //  - wsif_parser wsif class 내장
    //  - json_parser 마무리 필요
	// -----------------------------------------------------------------------------

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
	 )

	 $fds_reads[ integer ClientID ] = resource Socket  // this one-dimensional array is used for socket_select()
	 // $fds_reads[ 0 ] is the socket listening for incoming client connections
	 // $fds_reads[ 1 ] is the socket listening for incoming binary data

	 $cnt_connected_client               = integer ClientCount  // amount of clients currently connected
	 $arr_cnt_connected_ip[ integer IP ] = integer ClientCount  // amount of clients connected per IP v4 address
	*/

	class WSIF_ExtendInfo {
		public $is_set    = false;
		public $is_extend = false;
	}

	class WSIF_HeaderInfo {
		public $common_header = array('ps_type'    => 0,    // 4
									  'cmd_id'     => 0,	// 5
									  'route_case' => 0,	// 6
									  'is_binary'  => 0,	// 7
									  'is_extend'  => 0,	// 8
									  'length'     => 0,	// 12
									  'data'       => ''
									 );

		public $client_info   = array('clientID'   => null,
		 							  'src_type'   => 0,
		 							  'uri'		   => null
		 							 );
	}

	class WSIF_DataParser {
		const WS_DATA_TYPE_PS						= 0;
		const WS_DATA_TYPE_WS						= 1;

		const DATA_TYPE_PS_SOCKET					= 0;
		const DATA_TYPE_PS_SESSION					= 1;
		const DATA_TYPE_WS_SOCKET					= 2;
		const DATA_TYPE_WS_SESSION					= 3;

		CONST SIZE_SOCKET_HEADER					= 8;
		CONST SIZE_COMMON_HEADER					= 12;
		const SIZE_EXTEND_HEADER					= 6;

		public $common_header = array('ps_type'    => 0,    // 4
									  'cmd_id'     => 0,	// 5
									  'route_case' => 0,	// 6
									  'is_binary'  => 0,	// 7
									  'is_extend'  => 0,	// 8
									  'length'     => 0,	// 12
									  'data'       => ''
									 );

		public $client_info   = array('clientID'   => null,
		 							  'src_type'   => 0,
		 							  'uri'		   => null
		 							 );


		function parse_data($_type, $_data) {
			switch( $_type ) {
				case self::WS_DATA_TYPE_PS :
					// if( ($rc = substr($_data, 0,  4)) )	$this->common_header["ps_type"]      = unpack("l",  $rc)[1];

					$arr_data = unpack("l1ps_type", $_data);
					$this->common_header["ps_type"] = $arr_data["ps_type"];

					if( $this->common_header["ps_type"] > 0 ) {
						$this->client_info['src_type'] = self::DATA_TYPE_PS_SOCKET;
						$this->_parse_ps_socket($_data);

					} else {
						$this->client_info['src_type'] = self::DATA_TYPE_PS_SESSION;
						$this->_parse_ps_session($_data);
					}

					break;

				case self::WS_DATA_TYPE_WS :
					/*
					if( ($rc = substr($_data, 0,  1)) ) $this->common_header["cmd_id"]       = unpack("c",  $rc)[1];
					if( ($rc = substr($_data, 1,  1)) ) $this->common_header["route_case"]   = unpack("c",  $rc)[1];
					if( ($rc = substr($_data, 2,  1)) ) $this->common_header["is_binary"]    = unpack("c",  $rc)[1];
					if( ($rc = substr($_data, 3,  1)) ) $this->common_header["is_extend"]    = unpack("c",  $rc)[1];
					*/

					$arr_data = unpack("c1cmd_id/c1route_case/c1is_binary/c1is_extend", $_data);
					$this->common_header["cmd_id"] 		= $arr_data["cmd_id"];
					$this->common_header["route_case"] 	= $arr_data["route_case"];
					$this->common_header["is_binary"] 	= $arr_data["is_binary"];
					$this->common_header["is_extend"]	= $arr_data["is_extend"];

					if( $this->common_header["is_extend"] ) {
						$this->client_info['src_type'] = self::DATA_TYPE_WS_SESSION;
						$this->_parse_ws_session($_data);

					} else {
						$this->client_info['src_type'] = self::DATA_TYPE_WS_SOCKET;
						$this->_parse_ws_socket($_data);
					}

					break;
			}

			// header valid check
			if( !($this->common_header['ps_type'] >= 0 && $this->common_header['ps_type'] <= 65535) ) {			// socket port range
				return false;
			}

			if( !($this->common_header['cmd_id'] >= -128 && $this->common_header['cmd_id'] <= 255) ) {			// signed char range
				return false;
			}

			if( !($this->common_header['route_case'] >= 0 && $this->common_header['route_case'] <= 3) ) {	// route case: 0,1,2,3
				return false;
			}

			if( !($this->common_header['is_binary'] == 0 || $this->common_header['is_binary'] == 1) ) {
				return false;
			}

			if( !($this->common_header['is_extend'] == 0 || $this->common_header['is_extend'] == 1) ) {
				return false;
			}

			return true;
		}

		// sub method::parse_data, for ps_socket
		function _parse_ps_socket($_data) {
			/*
			if( ($rc = substr($_data, 4,  1)) )	$this->common_header["cmd_id"]       = unpack("c",  $rc)[1];
			if( ($rc = substr($_data, 5,  1)) )	$this->common_header["route_case"]   = 0;		// only string
			if( ($rc = substr($_data, 6,  1)) )	$this->common_header["is_binary"]    = 0;		// only each other
			if( ($rc = substr($_data, 7,  1)) )	$this->common_header["is_extend"]    = 0;
			if( ($rc = substr($_data, 8,  4)) )	$this->common_header["length"]       = unpack("l",  $rc)[1];
			*/

			$arr_data = unpack("l1ps_type/c1cmd_id/c1route_case/c1is_binary/c1is_extend/l1length", $_data);
			$this->common_header["cmd_id"] 		= $arr_data["cmd_id"];
			$this->common_header["route_case"] 	= 0;	// only each other
			$this->common_header["is_binary"] 	= 0;	// only string
			$this->common_header["is_extend"]	= 0;
			$this->common_header["length"]      = $arr_data["length"];

			return ;
		}

		// sub method::parse_data, for ps_session
		function _parse_ps_session($_data) {
			/*
			if( ($rc = substr($_data, 4,  1)) ) $this->common_header["cmd_id"]       = unpack("c",  $rc)[1];
			if( ($rc = substr($_data, 5,  1)) ) $this->common_header["route_case"]   = unpack("c",  $rc)[1];
			if( ($rc = substr($_data, 6,  1)) ) $this->common_header["is_binary"]    = (unpack("c",  $rc)[1] == 1 ? true : false);
			if( ($rc = substr($_data, 7,  1)) ) $this->common_header["is_extend"]    = (unpack("c",  $rc)[1] == 1 ? true : false);
			if( ($rc = substr($_data, 8,  4)) ) $this->common_header["length"]       = unpack("l",  $rc)[1];
			*/

			$arr_data = unpack("l1ps_type/c1cmd_id/c1route_case/c1is_binary/c1is_extend/l1length", $_data);
			$this->common_header["cmd_id"] 		=  $arr_data["cmd_id"];
			$this->common_header["route_case"] 	=  $arr_data["route_case"];
			$this->common_header["is_binary"] 	= ($arr_data["is_binary"] == 1 ? true : false);
			$this->common_header["is_extend"]	= ($arr_data["is_extend"] == 1 ? true : false);
			$this->common_header["length"]      =  $arr_data["length"];

			return ;
		}

		// sub method::parse_data, for ws_socket
		function _parse_ws_socket($_data) {
			/*
			if( ($rc = substr($_data, 4,  4)) ) $this->common_header["length"]       = unpack("l",  $rc)[1];
			if( ($rc = substr($_data, 8)) ) 	$this->common_header["data"]         = $rc;
			*/

			$arr_data = unpack("c4headers/l1length", $_data);
			$rc = substr($_data, 8);

			$this->common_header["route_case"]   = 0;		// only string
			$this->common_header["is_binary"]    = 0;		// only each other
			$this->common_header["is_extend"]    = 0;
			$this->common_header["length"]       = $arr_data["length"];
			$this->common_header["data"]         = $rc;

			return ;
		}

		// sub method::parse_data, for ws_session
		function _parse_ws_session($_data) {
			/*
			if( ($rc = substr($_data, 4,  4)) ) $this->common_header["length"]       = unpack("l",  $rc)[1];
			if( ($rc = substr($_data, 8)) ) 	$this->common_header["data"]         = $rc;
		    */

			$arr_data = unpack("c4headers/l1length", $_data);
			$rc = substr($_data, 8);

			$this->common_header["is_binary"] = $this->common_header["is_binary"] == 1 ? true : false;
			$this->common_header["is_extend"] = $this->common_header["is_extend"] == 1 ? true : false;
			$this->common_header["length"]    = $arr_data["length"];
			$this->common_header["data"]      = $rc;

			return ;
		}

		function parse_ext_data($_common_data) {
			$data_length = strlen($_common_data);

			$ext_header = array();
			$offset     = 0;

			while( true ) {
				$data = array('tag' => 0, 'length' => 0, 'value' => null);

				if( ($rc = substr($_common_data, 0 + $offset,  4)) ) $data["tag"]     = unpack("l",  $rc)[1];
				if( ($rc = substr($_common_data, 4 + $offset,  4)) ) $data["length"]  = unpack("l",  $rc)[1];
				if( ($rc = substr($_common_data, 8 + $offset,  $data["length"])) ) $data["value"] = $rc;

				array_push($ext_header, $data);
				
				$offset += (8 + $data["length"]);
				if( $offset == $data_length ) break;
				if( $offset > $data_length ) return false;
			}

			return $ext_header;
		}

		function get_ws_data($_dst_type) {
			$ws_data = null;

			switch( $_dst_type ) {
				case self::DATA_TYPE_PS_SOCKET :
					$ws_data = $this->_get_data_ps_socket();
					break;

				case self::DATA_TYPE_PS_SESSION :
					$ws_data = $this->_get_data_ps_session();
					break;

				case self::DATA_TYPE_WS_SOCKET :
					$ws_data = $this->_get_data_ws_socket();
					break;

				case self::DATA_TYPE_WS_SESSION :
					$ws_data = $this->_get_data_ws_session();
					break;
			}

			return $ws_data;
		}

		// sub method::get_ws_data, for ps_socket
		function _get_data_ps_socket() {
			$ws_data = '';
			$ws_data .= pack("c", $this->common_header["cmd_id"]);
			$ws_data .= pack("c", 0);
			$ws_data .= pack("c", 0);
			$ws_data .= pack("c", 0);
			$ws_data .= pack("l", $this->common_header["length"]);
			$ws_data .= $this->common_header["data"];

			return $ws_data;
		}

		// sub method::get_ws_data, for ps_session
		function _get_data_ps_session() {
			$ws_data = '';
			$ws_data .= pack("c",   $this->common_header["cmd_id"]);
			$ws_data .= pack("c",   $this->common_header["route_case"]);
			$ws_data .= pack("c",   $this->common_header["is_binary"]);
			$ws_data .= pack("c",   $this->common_header["is_extend"]);
			$ws_data .= pack("l",   $this->common_header["length"]);
			$ws_data .= $this->common_header["data"];

			return $ws_data;
		}

		// sub method::get_ws_data, for ws_socket
		function _get_data_ws_socket() {
			$ws_data = '';
			$ws_data .= $this->common_header["data"];

			return $ws_data;
		}

		// sub method::get_ws_data, for ws_session
		function _get_data_ws_session() {
			$ws_data = '';
			$ws_data .= pack("c",   $this->common_header["cmd_id"]);
			$ws_data .= pack("c",   $this->common_header["route_case"]);
			$ws_data .= pack("c",   $this->common_header["is_binary"]);
			$ws_data .= pack("c",   $this->common_header["is_extend"]);
			$ws_data .= pack("l",   $this->common_header["length"]);
			$ws_data .= $this->common_header["data"];

			return $ws_data;
		}

		function set_header($_name, $_value) {
			if( array_key_exists($_name, $this->common_header) ) {
				$this->common_header[$_name] = $_value;

			} else if( array_key_exists($_name, $this->client_info) ) {
				$this->client_info[$_name] = $_value;
			}

			return ;
		}

		function get_header_info() {
			return array_merge($this->common_header, $this->client_info);
		}

		function get_common_header() {
			return $this->common_header;
		}

		function get_client_info() {
			return $this->client_info;
		}

		function get_header($_name) {
			if( array_key_exists($_name, $this->common_header) ) {
				return $this->common_header[$_name];
			}

			if( array_key_exists($_name, $this->client_info) ) {
				return $this->client_info[$_name];
			}

			return null;
		}

		function get_size_common_header() {
			return self::SIZE_COMMON_HEADER;
		}

		function get_size_common_data() {
			return $this->common_header["length"];
		}
	}
?>