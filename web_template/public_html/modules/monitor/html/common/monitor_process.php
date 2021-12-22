<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_define.php";

	include_once "common_define.php";
	include_once "common_script.php";

	$monitorFunc = new Monitor\Func\MonitorFunc();

	if( $_POST["type"] == "monitor" && isset($_POST["act"]) ) {
		$act = $_POST["act"];

		if( $act == "set_device" ) {
			$ipaddr   = $_POST["ipaddr"];
			$hostname = $_POST["hostname"];
			$location = $_POST["location"];

			echo $monitorFunc->setDeviceList($ipaddr, $hostname, $location);

			return ;
		}
		else if( $act == "get_device" ) {

			echo $monitorFunc->getDeviceList();

			return ;
		}
		else if( $act == "remove_device" ) {
			$ipaddr = $_POST["ipaddr"];
			$rc     = false;

			if( $monitorFunc->removeDeviceList($ipaddr) ) {
				$rc = $monitorFunc->getDeviceList();
			}

			echo $rc;

			return ;
		}
		else if( $act == "get_status" ) {
			$ipaddr = $_POST["ipaddr"];
			$audio  = "off";
			$stat   = "off";

			$xmlUrl = 'http://' . $ipaddr . '/get_proc_alive.php';

			if( ($xml = simplexml_load_file($xmlUrl)) ) {
				$stat  = "on";
				$audio = $xml->client->audio;
			}

			echo '{"ipaddr":"' . $ipaddr . '", "status":"' . $stat . '", "audio":"' . $audio . '"}';
		}
		else if( $act == "check_status") {
			$ipList = $_POST["ip_list"];

			$arrIpList = explode(",", $ipList);
			$waitTimeout = 1;

			$sockFd = array();

			foreach( $arrIpList as $address ) {
				if( !$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) ) {
					continue;
				}
				socket_set_nonblock($sock);

				@socket_connect($sock, $address, 80); // 80 port 고정
				$sockFd[$address] = $sock;
			}

			sleep($waitTimeout);

			$w = $sockFd;
			$r = $e = NULL;
			$count = socket_select($r, $w, $e, 0);

			$aliveIpList = "";

			foreach ($w as $sock) {
				$address = array_search($sock, $sockFd);
				$aliveIpList .= '"' . $address . '",';

				@socket_close($sock);
			}
			echo '{"count":"' . $count. '", "ipList":[' . chop($aliveIpList, ",") . ']}';

			return ;
		}
	}

	include_once 'monitor_process_etc.php';
?>
