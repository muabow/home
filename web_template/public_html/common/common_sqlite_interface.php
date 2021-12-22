<?php
	function query_interface($_path, $_query) {
		$host	= "127.0.0.1";
		$port	= 25003;

		$sendMsg["path"]  = $_path;
		$sendMsg["query"] = $_query;
		$sendMsg = json_encode($sendMsg);

		try {
			if( !($socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) ) {
				throw new Exception("socket_create error", 1);
			}

			if( !@socket_connect($socket, $host, $port) ) {
				throw new Exception("socket_connect error", 1);
			}

		} catch(Exception $_e) {

			return 0;
		}

		socket_write($socket, $sendMsg, strlen($sendMsg)) or die("Could not send data to server\n");
		
		if( strpos(strtolower($_query), 'multi_select') > -1 ) {
			
			$returnVal = null;
			$result = null;
			while(true) {
				if( ($length = socket_recv($socket, $result, 8, MSG_WAITALL)) < 0 ) {
				} else if( $length == 0 ) {
					break;
				}
				$rc = substr($result, 4, 4);
				$outLength = unpack("l", $rc)[1];
				printLog("SQL_INTERFACE", "length : " . $outLength);
				$length = socket_recv($socket, $result, $outLength, MSG_WAITALL);
				if($length == 0) {
					break;
				}
				if($returnVal == null) {
					$returnVal = array(json_decode($result));
				} else {
					array_push($returnVal, json_decode($result));
				} 
			}
			$result = json_encode($returnVal);
		} else {
			$result = socket_read($socket, 4096) or die("Could not read server response\n");
		}
		socket_close($socket);

		return $result;
	}
?>