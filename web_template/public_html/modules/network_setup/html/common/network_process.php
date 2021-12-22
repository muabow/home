<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_define.php";
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_script.php";

	include_once "common_define.php";
	include_once "common_script.php";

	$networkFunc = new Network_setup\Func\NetworkFunc();

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

	function netmaskToPrefix($_mask) {
		$long = ip2long($_mask);
		$base = ip2long("255.255.255.255");
		$result = 32 - log(($long ^ $base) + 1, 2);

		return intval($result);
	}

	function execFunc($_cmd) {
		pclose(popen('echo "' . $_cmd . '" > /tmp/web_fifo', "r"));

		return ;
	}
	$arrEthList	 = getEthList();

	if( $_POST["type"] == "network" && isset($_POST["act"]) ) {
		$act = $_POST["act"];
		$networkData = $networkFunc->getNetworkStat();
		$networkData = setEthStatus($networkData);

		if( $act == "get_stat" ) {

			echo '{'
					. '"hostname":"' .	$networkData->hostname
					. '", "location":"' . 	$networkData->location
					. '", "primary" : {'
							. '"use":"' . 			$networkData->network_primary->use
							. '", "dhcp":"' . 		$networkData->network_primary->dhcp
							. '", "mac_address":"' .$networkData->network_primary->mac_address
							. '", "ip_address":"' . $networkData->network_primary->ip_address
							. '", "subnetmask":"' . $networkData->network_primary->subnetmask
							. '", "gateway":"' . 	$networkData->network_primary->gateway
							. '", "dns_server_1":"' . $networkData->network_primary->dns_server_1
							. '", "dns_server_2":"' . $networkData->network_primary->dns_server_2
						. '"}'
						. ', "secondary" : {'
							. '"use":"' . 			$networkData->network_secondary->use
							. '", "dhcp":"' . 		$networkData->network_secondary->dhcp
							. '", "mac_address":"' .$networkData->network_secondary->mac_address
							. '", "ip_address":"' . $networkData->network_secondary->ip_address
							. '", "subnetmask":"' . $networkData->network_secondary->subnetmask
							. '", "gateway":"' . 	$networkData->network_secondary->gateway
							. '", "dns_server_1":"' . $networkData->network_secondary->dns_server_1
							. '", "dns_server_2":"' . $networkData->network_secondary->dns_server_2
						. '"}'
						. ', "bonding" : {'
							. '"use":"' . 		$networkData->network_bonding->use
							. '", "mac_address":"' .$networkData->network_bonding->mac_address
							. '", "ip_address":"' . $networkData->network_bonding->ip_address
							. '", "subnetmask":"' . $networkData->network_bonding->subnetmask
							. '", "gateway":"' .    $networkData->network_bonding->gateway
						. '"} '
					. '}';

			return ;
		}
		else if( $act == "set_stat" ) {
			$networkData->hostname 	= $_POST["hostname"];
			$networkData->location 	= $_POST["location"];

			$networkData->network_primary->use 				= $_POST["primary_use"];
			$networkData->network_primary->dhcp 			= $_POST["primary_dhcp"];
			$networkData->network_primary->ip_address	 	= $_POST["primary_ip_address"];
			$networkData->network_primary->subnetmask	 	= $_POST["primary_subnetmask"];
			$networkData->network_primary->gateway 			= $_POST["primary_gateway"];
			$networkData->network_primary->dns_server_1 	= $_POST["primary_dns_server_1"];
			$networkData->network_primary->dns_server_2 	= $_POST["primary_dns_server_2"];

			$networkData->network_secondary->use 			= $_POST["secondary_use"];
			$networkData->network_secondary->dhcp 			= $_POST["secondary_dhcp"];
			$networkData->network_secondary->ip_address 	= $_POST["secondary_ip_address"];
			$networkData->network_secondary->subnetmask 	= $_POST["secondary_subnetmask"];
			$networkData->network_secondary->gateway 		= $_POST["secondary_gateway"];
			$networkData->network_secondary->dns_server_1 	= $_POST["secondary_dns_server_1"];
			$networkData->network_secondary->dns_server_2 	= $_POST["secondary_dns_server_2"];

			$networkData->network_bonding->use	 			= $_POST["bonding_use"];
			$networkData->network_bonding->ip_address 		= $_POST["bonding_ip_address"];
			$networkData->network_bonding->subnetmask 		= $_POST["bonding_subnetmask"];
			$networkData->network_bonding->gateway 			= $_POST["bonding_gateway"];

			$networkFunc->setNetworkStat($networkData);

			$interface  = '# interfaces(5) file used by ifup(8) and ifdown(8)' . "\n";
			$interface .= '# Include files from /etc/network/interfaces.d:' . "\n";
			$interface .= 'source-directory /etc/network/interfaces.d' . "\n";
			$interface .= '' . "\n";
			$interface .= 'auto lo' . "\n";
			$interface .= 'iface lo inet loopback' . "\n";
			$interface .= '' . "\n";

			$dhcpcd = '';
			$dhcpcd .= '# A sample configuration for dhcpcd.' . "\n";
			$dhcpcd .= '# See dhcpcd.conf(5) for details.' . "\n";
			$dhcpcd .= '' . "\n";
			$dhcpcd .= '# Inform the DHCP server of our hostname for DDNS.' . "\n";
			$dhcpcd .= 'hostname' . "\n";
			$dhcpcd .= '' . "\n";
			$dhcpcd .= '# Use the hardware address of the interface for the Client ID.' . "\n";
			$dhcpcd .= '#clientid' . "\n";
			$dhcpcd .= '# or' . "\n";
			$dhcpcd .= '# Use the same DUID + IAID as set in DHCPv6 for DHCPv4 ClientID as per RFC4361.' . "\n";
			$dhcpcd .= 'duid' . "\n";
			$dhcpcd .= '' . "\n";
			$dhcpcd .= '# Rapid commit support.' . "\n";
			$dhcpcd .= '# Safe to enable by default because it requires the equivalent option set' . "\n";
			$dhcpcd .= '# on the server to actually work.' . "\n";
			$dhcpcd .= 'option rapid_commit' . "\n";
			$dhcpcd .= '' . "\n";
			$dhcpcd .= '# A list of options to request from the DHCP server.' . "\n";
			$dhcpcd .= 'option domain_name_servers, domain_name, domain_search, host_name' . "\n";
			$dhcpcd .= 'option classless_static_routes' . "\n";
			$dhcpcd .= '# Most distributions have NTP support.' . "\n";
			$dhcpcd .= 'option ntp_servers' . "\n";
			$dhcpcd .= '# Respect the network MTU.' . "\n";
			$dhcpcd .= '# Some interface drivers reset when changing the MTU so disabled by default.' . "\n";
			$dhcpcd .= '#option interface_mtu' . "\n";
			$dhcpcd .= '' . "\n";
			$dhcpcd .= '# A ServerID is required by RFC2131.' . "\n";
			$dhcpcd .= 'require dhcp_server_identifier' . "\n";
			$dhcpcd .= '' . "\n";
			$dhcpcd .= '# A hook script is provided to lookup the hostname if not set by the DHCP' . "\n";
			$dhcpcd .= '# server, but it should not be run by default.' . "\n";
			$dhcpcd .= 'nohook lookup-hostname' . "\n";
			$dhcpcd .= '' . "\n";

			$denyinterfaces = '';
			if( $networkData->network_bonding->use == "enabled" ) {
				$interface .= 'allow-hotplug bond0' . "\n";
				$interface .= 'iface bond0 inet static' . "\n";
				$interface .= 'address ' . $networkData->network_bonding->ip_address . "\n";
				$interface .= 'netmask ' . $networkData->network_bonding->subnetmask . "\n";
				$interface .= 'gateway ' . $networkData->network_bonding->gateway . "\n";
				$interface .= 'bond-mode broadcast' . "\n";
				$interface .= 'bond-slaves eth0 eth1' . "\n";
				$interface .= 'bond-miimon 100' . "\n";
				$interface .= '' . "\n";

				// dhcpcd
				$denyinterfaces .= 'bond0 ';
			}


			if( $networkData->network_primary->use == "enabled" ) {
				$interface .= 'allow-hotplug ' . $arrEthList[0] . "\n";
				$interface .= 'iface ' . $arrEthList[0] . ' inet ';

				if( $networkData->network_primary->dhcp == "on" ) {
					$interface .= 'manual';

				} else {
					$interface .= 'static' . "\n";
					$interface .= 'address ' . $networkData->network_primary->ip_address . "\n";
					$interface .= 'netmask ' . $networkData->network_primary->subnetmask . "\n";
					$interface .= 'gateway ' . $networkData->network_primary->gateway . "\n";
					$interface .= 'dns-nameservers ' . $networkData->network_primary->dns_server_1 . ' ' . $networkData->network_primary->dns_server_2 . "\n";
				}
			}
			// dhcpcd
			if( $networkData->network_primary->dhcp != "on" ) {
				$denyinterfaces .= 'eth0 ';
			}
			$interface .= '' . "\n";

			if( $networkData->network_secondary->use == "enabled" ) {
				$interface .= 'allow-hotplug ' . $arrEthList[1] . "\n";
				$interface .= 'iface ' . $arrEthList[1] . ' inet ';

				if( $networkData->network_secondary->dhcp == "on" ) {
					$interface .= 'manual';

				} else {
					$interface .= 'static' . "\n";
					$interface .= 'address ' . $networkData->network_secondary->ip_address . "\n";
					$interface .= 'netmask ' . $networkData->network_secondary->subnetmask . "\n";
					$interface .= 'gateway ' . $networkData->network_secondary->gateway . "\n";
					$interface .= 'dns-nameservers ' . $networkData->network_secondary->dns_server_1 . ' ' . $networkData->network_secondary->dns_server_2 . "\n";
				}
			}
			// dhcpcd
			if( $networkData->network_secondary->dhcp != "on" ) {
				$denyinterfaces .= 'eth1 ';
			}
			$interface .= '' . "\n";

			if( $denyinterfaces != "" ) {
				$dhcpcd .= 'denyinterfaces ' . $denyinterfaces . "\n";
			}

			execFunc('echo \"' . $interface . '\" > /etc/network/interfaces');

			file_put_contents("/tmp/dhcpcd.conf", $dhcpcd);
			execFunc('mv -f /tmp/dhcpcd.conf /etc/.');

			if( !isset($_POST["no_reboot"]) ) execFunc('/opt/interm/bin/reboot.sh 2>&1');

			return ;

		} else if( $act == "set_tab" ) {
			$networkData->tabStat 	= $_POST["tab_stat"];

			$networkFunc->setNetworkStat($networkData);

			return ;

		} else if( $act == "set_use" ) {
			$type = "network_" . $_POST["tab"];
			$stat = $_POST["stat"];

			$networkData->$type->use = $stat;

			$networkFunc->setNetworkStat($networkData);

			return ;
		}
	}

	include_once 'network_process_etc.php';
?>
