<?php
	function query_multi_select($_dbPath, $_query) {
		if( !file_exists($_dbPath) ) {
			return -1;
		}

		$dbConn = new SQLite3($_dbPath);
		$dbConn->exec('PRAGMA journal_mode = wal;');

		try {
			if( ($result = $dbConn->query($_query)) == false ) {
				$dbConn->close();
				throw new Exception("query error", 1);
			}

		} catch(Exception $_e) {
			return -1;
		}

		$arrResult = array();

		while( $row = $result->fetchArray(1) ) {
			$arrResult[] = $row;
		}

		$dbConn->close();
		return $arrResult;
	}
	
	function query_select($_dbPath, $_query) {
		if( !file_exists($_dbPath) ) {
			return -1;
		}

		$dbConn = new SQLite3($_dbPath);
		$dbConn->exec('PRAGMA journal_mode = wal;');

		try {
			if( ($result = $dbConn->query($_query)) == false ) {
				$dbConn->close();
				throw new Exception("query error", 1);
			}

		} catch(Exception $_e) {
			return -1;
		}

		$arrResult = array();

		while( $row = $result->fetchArray(1) ) {
			$arrResult[] = $row;
		}

		$dbConn->close();
		return json_encode($arrResult);
	}

	function query_others($_dbPath, $_query) {
		if( !file_exists($_dbPath) ) {
			return -1;
		}

		$dbConn = new SQLite3($_dbPath);
		$dbConn->exec('PRAGMA journal_mode = wal;');

		try {
			if( ($dbConn->query($_query)) == false ) {
				$dbConn->close();
				throw new Exception("query error", 1);
			}

		} catch(Exception $_e) {
			return -1;
		}

		$dbConn->close();
		return 1;
	}

	cli_set_process_title("sqlite_interface");


	// socket env
	$host = "127.0.0.1";
	$port = 25003;

	set_time_limit(0);

	$ServSockFd = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");

	socket_set_option($ServSockFd, SOL_SOCKET, SO_REUSEADDR, 1);

	socket_bind($ServSockFd, $host, $port) or die("Could not bind to socket\n");
	socket_listen($ServSockFd, 512) or die("Could not set up socket listener\n");

	while( true ) {
		$clntSockFd = socket_accept($ServSockFd) or die("Could not accept incoming connection\n");

		$recvData = socket_read($clntSockFd, 4096) or die("Could not read input\n");
		$jsonData = json_decode($recvData);

		if( strpos(strtolower($jsonData->query), 'select') === 0 ) {
			//shell_exec('echo "select query : ' . $jsonData->query . '" >> /tmp/sqlite_interface.log');
			$output = query_select($jsonData->path, $jsonData->query);
		} else if( strpos(strtolower($jsonData->query), 'multi_select') === 0 ) {
			//shell_exec('echo "multi select query : ' . $jsonData->query . '" >> /tmp/sqlite_interface.log');
			// echo "multi select \n";
			$jsonData->query = str_replace("multi_select", "select", $jsonData->query);
			$output = query_multi_select($jsonData->path, $jsonData->query);
			
			foreach($output as $key => $value) {
				$tmpValue = json_encode($value);
				$out = pack("l", 0x00);
				$out .= pack("l", strlen($tmpValue));
				$out .= $tmpValue;
				socket_write($clntSockFd, $out, strlen($out)) or die("Could not write output\n");
			}
			socket_close($clntSockFd);
			
			continue;
		} else {
			//shell_exec('echo "others query : ' . $jsonData->query . '" >> /tmp/sqlite_interface.log');
			// echo "others \n";
			$output = query_others($jsonData->path, $jsonData->query);
		}
		
		socket_write($clntSockFd, $output, strlen($output)) or die("Could not write output\n");
		socket_close($clntSockFd);
	}

	socket_close($ServSockFd);
?>
