<?php
	function setEthStatus($_networkData) {
		$networkData = $_networkData;

		// check architecture
		$arrEthList = array("eth0", "eth1");

		$fp  = popen("lscpu | grep Architecture | grep arm | wc -l", "r");
		$arc = fread($fp, 1024);
		pclose($fp);

		if( $arc == 0 ) {
			// bonding unsupport
			$networkData->network_bonding->view = "disabled";
			$networkData->network_bonding->use  = "disabled";

			$arrEthType = array("network_primary", "network_secondary");
			foreach( $arrEthType as $type ) {
				$networkData->$type->view = "disabled";
				$networkData->$type->use  = "disabled";
			}

			$fp  = popen("ifconfig -s -a | grep BMRU | awk '{print $1}'", "r");
			$nameEths = fread($fp, 1024);
			pclose($fp);

			$arrEth = preg_split("/[\s,]+/", $nameEths);
			array_pop($arrEth);

			foreach( $arrEth as $index => $eth ) {
				$default_eth = "eth{$index}";
				$ethType = $arrEthType[$index];

				$arrEthList[$index] = $eth;
				$networkData->$ethType->view = "enabled";
				$networkData->$ethType->use  = "enabled";
			}
		}

		return $networkData;
	}

	function getEthList() {
		// check architecture
		$arrEthList = array("eth0", "eth1");

		$fp  = popen("lscpu | grep Architecture | grep arm | wc -l", "r");
		$arc = fread($fp, 1024);
		pclose($fp);

		if( $arc == 0 ) {
			$fp  = popen("ifconfig -s -a | grep BMRU | awk '{print $1}'", "r");
			$nameEths = fread($fp, 1024);
			pclose($fp);

			$arrEth = preg_split("/[\s,]+/", $nameEths);
			array_pop($arrEth);

			foreach( $arrEth as $index => $eth ) {
				$default_eth = "eth{$index}";
				$ethType = $arrEthType[$index];

				$arrEthList[$index] = $eth;
			}
		}

		return $arrEthList;
	}


	echo "\nNetwork_setup module init script start \n";

	$load_envData = file_get_contents(dirname(__FILE__) . "/../conf/network_stat.json");
	$networkData  = json_decode($load_envData);

	$networkData = setEthStatus($networkData);
	$arrEthList	 = getEthList();

	$syncFlag = false;

	// MAC 주소 갱신
	//
	if( file_exists("/sys/class/net/bond0/address") ) {
		$mac_address = trim(file_get_contents("/sys/class/net/bond0/address"));
		if( $mac_address != $networkData->network_bonding->mac_address ) {
			echo " - Sync Mac address parameter [bonding] \n";
			echo " : [" . $networkData->network_bonding->mac_address . "] -> [" . $mac_address . "] \n";
			$networkData->network_bonding->mac_address = $mac_address;
			$syncFlag = true;
		}
	}

	if( file_exists("/sys/class/net/" . $arrEthList[0] . "/address") ) {
		$mac_address = trim(file_get_contents("/sys/class/net/" . $arrEthList[0] . "/address"));
		if( $mac_address != $networkData->network_primary->mac_address ) {
			echo " - Sync Mac address parameter [primary] \n";
			echo " : [" . $networkData->network_primary->mac_address . "] -> [" . $mac_address . "] \n";
			$networkData->network_primary->mac_address = $mac_address;
			$syncFlag = true;
		}
	}

	if( file_exists("/sys/class/net/" . $arrEthList[1] . "/address") ) {
		$mac_address = trim(file_get_contents("/sys/class/net/" . $arrEthList[1] . "/address"));
		if( $mac_address != $networkData->network_secondary->mac_address ) {
			echo " - Sync Mac address parameter.. \n";
			echo " : [" . $networkData->network_secondary->mac_address . "] -> [" . $mac_address . "] \n";
			$networkData->network_secondary->mac_address = $mac_address;
			$syncFlag = true;
		}
	}

	/* network 설정 시 적용 및 재시작
	echo " - Sync network parameter.. \n";

	$interface = '
	source-directory /etc/network/interfaces.d

	auto lo
	iface lo inet loopback';

	if( $networkData->network_bonding->use == "enabled" ) {
		$interface .= '
		auto bond0
		iface bond0 inet static
			address ' . $networkData->network_bonding->ip_address . '
			netmask ' . $networkData->network_bonding->subnetmask . '
			gateway ' . $networkData->network_bonding->gateway .'
			bond_mode broadcast
			bond_miimon 100
			slaves ' . $arrEthList[0] . ' ' . $arrEthList[1] . ';
	}

	if( $networkData->network_primary->use == "enabled" ) {
		$interface .= '
		auto ' . $arrEthList[0] . '
		iface ' . $arrEthList[0] . ' inet ';

		if( $networkData->network_primary->dhcp == "on" ) {
			$interface .= 'manual';

		} else {
			$interface .= 'static
			address ' . $networkData->network_primary->ip_address . '
			netmask ' . $networkData->network_primary->subnetmask . '
			gateway ' . $networkData->network_primary->gateway . '
			dns-nameservers ' . $networkData->network_primary->dns_server_1 . ' ' . $networkData->network_primary->dns_server_2;
		}
	}

	if( $networkData->network_secondary->use == "enabled" ) {
		$interface .= '
		auto ' . $arrEthList[1] . '
		iface ' . $arrEthList[1] . ' inet ';

		if( $networkData->network_secondary->dhcp == "on" ) {
			$interface .= 'manual';

		} else {
			$interface .= 'static
			address ' . $networkData->network_secondary->ip_address . '
			netmask ' . $networkData->network_secondary->subnetmask . '
			gateway ' . $networkData->network_secondary->gateway . '
			dns-nameservers ' . $networkData->network_secondary->dns_server_1 . ' ' . $networkData->network_secondary->dns_server_2;
		}
	}

	pclose(popen('echo "' . $interface . '" > /etc/network/interfaces 2>&1', "r"));
	pclose(popen('/sbin/ip addr flush dev ' . $arrEthList[0] . ' 2>&1', "r"));
	pclose(popen('/sbin/ip addr flush dev ' . $arrEthList[1] . ' 2>&1', "r"));
	pclose(popen('/etc/init.d/networking restart  2>&1', "r"));
	*/

	pclose(popen('echo -n "" > /etc/resolv.conf', "r"));
	if( $networkData->network_primary->use == "enabled"
		&& $networkData->network_primary->dhcp == "off" ) {
		pclose(popen('echo "nameserver ' . $networkData->network_primary->dns_server_1 . '" >> /etc/resolv.conf', "r"));
		pclose(popen('echo "nameserver ' . $networkData->network_primary->dns_server_2 . '" >> /etc/resolv.conf', "r"));
//		pclose(popen('route add default gw ' . $networkData->network_primary->gateway .' dev ' . $arrEthList[0] . ' metric 0', "r"));

//		pclose(popen('route add -net 224.0.0.0 netmask 224.0.0.0 ' . $arrEthList[0], "r"));
	}

	if( $networkData->network_secondary->use == "enabled"
		&& $networkData->network_secondary->dhcp == "off" ) {
		pclose(popen('echo "nameserver ' . $networkData->network_secondary->dns_server_1 . '" >> /etc/resolv.conf', "r"));
		pclose(popen('echo "nameserver ' . $networkData->network_secondary->dns_server_2 . '" >> /etc/resolv.conf', "r"));
//		pclose(popen('route add default gw ' . $networkData->network_secondary->gateway .' dev ' . $arrEthList[1] . ' metric 1', "r"));
//		pclose(popen('route add default gw ' . $networkData->network_primary->gateway .' dev ' . $arrEthList[0] . ' metric 0', "r"));

//		pclose(popen('route add -net 224.0.0.0 netmask 224.0.0.0 ' . $arrEthList[1], "r"));
	}

	if( $networkData->network_bonding->use == "enabled" ) {
		// pclose(popen('/sbin/ifenslave bond0 ' . $arrEthList[0] . ' ' . $arrEthList[1] . ' 2>&1', "r"));
	}

	if( $networkData->network_primary->use == "enabled"
		&& $networkData->network_primary->dhcp == "on" ) {
		echo " : Get DHCP network information [primary] \n";
		$handle = popen("ifconfig " . $arrEthList[0] . " | grep \"inet addr\" | awk '{print $2 \" \" $4}'", "r");
		$read = fread($handle, 1024);
		pclose($handle);
		$arrAddress = explode(" ", $read);

		$arrIpAddr = explode(":", $arrAddress[0]);
		$networkData->network_primary->ip_address = trim($arrIpAddr[1]);

		$arrSubnet = explode(":", $arrAddress[1]);
		$networkData->network_primary->subnetmask = trim($arrSubnet[1]);
		echo " : - IP Address : [" . $networkData->network_primary->ip_address . "] \n";
		echo " : - Subnetmask : [" . $networkData->network_primary->subnetmask . "] \n";

		$handle = popen("route -n | grep " . $arrEthList[0] . " | grep UG | awk '{print $2}'", "r");
		$read = fread($handle, 1024);
		pclose($handle);

		$networkData->network_primary->gateway = "";

		if( $read ) {
			$networkData->network_primary->gateway = trim($read);
		}
		echo " : - Gateway    : [" . $networkData->network_primary->gateway . "] \n";

		$handle = popen("cat /etc/resolv.conf | grep nameserver | awk '{print $2}'", "r");
		$read = fread($handle, 1024);
		pclose($handle);

		$networkData->network_primary->dns_server_1 = "";
		$networkData->network_primary->dns_server_2 = "";

		if( $read ) {
			$arrDns = explode("", $read);

			$networkData->network_primary->dns_server_1 = trim($arrDns[0]);
			$networkData->network_primary->dns_server_2 = trim($arrDns[1]);
		}
		echo " : - DNS_1      : [" . $networkData->network_primary->dns_server_1 . "] \n";
		echo " : - DNS_2      : [" . $networkData->network_primary->dns_server_2 . "] \n";
		$syncFlag = true;
	}


	if( $networkData->network_secondary->use == "enabled"
		&& $networkData->network_secondary->dhcp == "on" ) {
		echo " : Get DHCP network information [secondary] \n";
		$handle = popen("ifconfig " . $arrEthList[1] . " | grep \"inet addr\" | awk '{print $2 \" \" $4}'", "r");
		$read = fread($handle, 1024);
		pclose($handle);
		$arrAddress = explode(" ", $read);

		$arrIpAddr = explode(":", $arrAddress[0]);
		$networkData->network_secondary->ip_address = trim($arrIpAddr[1]);

		$arrSubnet = explode(":", $arrAddress[1]);
		$networkData->network_secondary->subnetmask = trim($arrSubnet[1]);

		echo " : - IP Address : [" . $networkData->network_secondary->ip_address . "] \n";
		echo " : - Subnetmask : [" . $networkData->network_secondary->subnetmask . "] \n";

		$handle = popen("route -n | grep " . $arrEthList[1] . " | grep UG | awk '{print $2}'", "r");
		$read = fread($handle, 1024);
		pclose($handle);

		$networkData->network_secondary->gateway = "";

		if( $read ) {
			$networkData->network_secondary->gateway = trim($read);
		}
		echo " : - Gateway    : [" . $networkData->network_secondary->gateway . "] \n";

		$handle = popen("cat /etc/resolv.conf | grep nameserver | awk '{print $2}'", "r");
		$read = fread($handle, 1024);
		pclose($handle);

		$networkData->network_secondary->dns_server_1 = "";
		$networkData->network_secondary->dns_server_2 = "";

		if( $read ) {
			$arrDns = explode("", $read);

			$networkData->network_secondary->dns_server_1 = trim($arrDns[0]);
			$networkData->network_secondary->dns_server_2 = trim($arrDns[1]);
		}
		echo " : - DNS_1      : [" . $networkData->network_secondary->dns_server_1 . "] \n";
		echo " : - DNS_2      : [" . $networkData->network_secondary->dns_server_2 . "] \n";
		$syncFlag = true;
	}


	if( $syncFlag == true ) {
		echo " - Write network config file.. \n";
		file_put_contents(dirname(__FILE__) . "/../conf/network_stat.json", json_encode($networkData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
	}

	shell_exec('/usr/bin/php /opt/interm/public_html/modules/network_setup/bin/avahi_info.php > /dev/null 2>/dev/null');
	
	echo "Network_setup module init script end \n";

	pclose(popen(dirname(__FILE__)."/network_info_receiver &", "r"));
	
	shell_exec("/usr/bin/php /opt/interm/public_html/modules/network_setup/bin/sync_device_info.php > /dev/null 2>/dev/null &");
	
	return ;
?>
