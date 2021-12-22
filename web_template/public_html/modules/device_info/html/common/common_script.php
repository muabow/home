<?php
	$load_envData = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/env.json");
	$envData      = json_decode($load_envData);
	$env_langSet  = $envData->info->language_set;

	if ( !file_exists($env_device_module . $envData->language_pack->$env_langSet->path) ) {
		$env_langSet = "eng";
	}
	$module_path_lang_pack = $env_device_module . $envData->language_pack->$env_langSet->path;
	include $module_path_lang_pack;

	$env_network_module = $_SERVER["DOCUMENT_ROOT"] . "/modules/network_setup/html/";
	include_once $env_network_module . "common/common_define.php";
	include_once $env_network_module . "common/common_script.php";

	$networkFunc  = new Network_setup\Func\NetworkFunc();
	$network_info = $networkFunc->getEnvData();
	
	
	$load_deviceInfo = file_get_contents($_SERVER['DOCUMENT_ROOT']."/../conf/config-device-info.json");	
	$deviceData = json_decode($load_deviceInfo);
	$port = $deviceData->port;
	
	function make_deviceInfo_table_row($_type) {	
		$_inout = check_inout($_type);
		
		if ( $_inout != "none" ) {
			echo '<div class="div_table_row">';
			echo '<div class="div_table_column div_table_background">';
				
			switch ($_type) {
				
				case 'Audio' :
					echo '<div class="div_table_cell">' . Device_info\Lang\STR_INIT_SPEC_AUDIO . '</div>';
					echo '</div>';
					
					make_colume($_type, $_inout);
					break;
				
				case 'CP' :				
					echo '<div class="div_table_cell">' . Device_info\Lang\STR_INIT_SPEC_CONTACT . '</div>';
					echo '</div>';
					 
					make_colume($_type, $_inout);
					break;
				
				case 'RM' :
					echo '<div class="div_table_cell">RM</div>';
					echo '</div>';
					
					make_colume($_type, $_inout);
					break;
					
				default:
					
					break;
			}	
			
			echo '</div>';
			echo '</div>';
		}
		
		return;
	}
	
	// in, out 에 따라 column을 만들어 주는 함수
	function make_colume($_type, $_inout) {
			
		echo '<div class="div_table_column div_table_background">';
		
		switch( $_inout ) {
			case "in" :
				echo '<div class="div_table_cell">' . Device_info\Lang\STR_INIT_SPEC_INPUT . '</div>';
				echo '</div>';
				echo '<div class="div_table_column div_table_cell_double">';

				make_chennel_div($_type, $_inout);				
				break;
				
			case "out" :
				echo '<div class="div_table_cell">' . Device_info\Lang\STR_INIT_SPEC_OUTPUT . '</div>';
				echo '</div>';
				echo '<div class="div_table_column div_table_cell_double">';
				
				make_chennel_div($_type, $_inout);				
				break;
				
			case "inout":			
				echo '<div class="div_table_cell">' . Device_info\Lang\STR_INIT_SPEC_INPUT . '</div>';
				echo '<div class="div_table_cell">' . Device_info\Lang\STR_INIT_SPEC_OUTPUT . '</div>';
				echo '</div>';
				echo '<div class="div_table_column div_table_cell_double">';
				
				make_chennel_div($_type, $_inout);									
				break;
		}				
		
		return ;
	}

// 채널 구하는 함수
	function make_chennel_div($_type, $_inout) {
		global $deviceData;
		
		if( $_inout == "inout" ) {
			echo '<div class="div_table_cell">' . $deviceData->port->$_type->in . Device_info\Lang\STR_INIT_SPEC_CHANNEL . '</div>';
			echo '<div class="div_table_cell">'.$deviceData->port->$_type->out . Device_info\Lang\STR_INIT_SPEC_CHANNEL . '</div>';
			
		} else {
			echo '<div class="div_table_cell">'.$deviceData->port->$_type->$_inout . Device_info\Lang\STR_INIT_SPEC_CHANNEL . '</div>';
		}
		
		return ;
	}
		
	// in, out 값이 있는지 확인하는 함수
	function check_inout($_type) {
		global $port;		
				
		if ( $port->$_type->in == 0 && $port->$_type->out == 0 ) { // 입력 & 출력 값 X
			return "none";
		
		} else if( $port->$_type->in != 0 && $port->$_type->out == 0 ) { // 입력만
			return "in";
			
		} else if( $port->$_type->in == 0 && $port->$_type->out != 0 ) { // 출력만
			return "out";
			
		} else { // 입출력 모두 O
			return "inout";			
		}		
		
		return "";
	}
	
	
	
	function get_network_info($_eth, $_type) {
		global $network_info;

		if( ($rc = $network_info->$_eth->$_type) == "" ) {
			return "-";

		}  else {
			return $rc;
		}
	}

	function get_view_info($_eth) {
		global $network_info;

		if( $network_info->$_eth->view == "enabled" ) {
			return true;

		} else {
			return false;
		}
	}

	function get_enable_info($_eth) {
		global $network_info;

		if( $network_info->$_eth->use == "enabled" ) {
			return '<a style="font-weight: bold; color: green;">' . Device_info\Lang\STR_INIT_NETWORK_FLAG_ENABLE . "</a>";

		} else {
			return '<a style="font-weight: bold; color: red;">' . Device_info\Lang\STR_INIT_NETWORK_FLAG_DISABLE . "</a>";
		}
	}

	function get_dhcp_info($_eth) {
		global $network_info;

		if( $_eth == "network_bonding" ) {
			return "-";
		}

		if( $network_info->$_eth->dhcp == "on" ) {
			return '<a style="font-weight: bold; color: green;">' . Device_info\Lang\STR_INIT_NETWORK_FLAG_ENABLE . "</a>";

		} else {
			return '<a style="font-weight: bold; color: red;">' . Device_info\Lang\STR_INIT_NETWORK_FLAG_DISABLE . "</a>";
		}
	}

	function find_string($_array, $_target) {
	    $is_eth = false;

	    foreach( $_array as $element ) {
	        if( !$is_eth ) {
	            if( strpos($element, $_target) !== false ) {
	                $is_eth = true;
	            }

	        } else {
	            if( strpos($element, "Link encap:") !== false ) {
	                return false;
	            }

	            if( strpos($element, "RUNNING") !== false ) {
	                return true;
	            }
	        }
	    }
	    return false;
	}

	function make_table_row($_eth, $_case) {
		if( !get_view_info($_eth) ) {
			return ;
		}

		switch( $_case ) {
			case "title" 		:
				$arr_eth_name = explode("_", $_eth);
				echo '<div class="div_table_cell div_table_background">'. ucfirst($arr_eth_name[1]) .'</div>';
				break;

			case "enable" 		:
				echo '<div class="div_table_cell">' . get_enable_info($_eth) . '</div>';
				break;

			case "dhcp" 		:
				echo '<div class="div_table_cell">' . get_dhcp_info($_eth) . '</div>';
				break;

			case "ip_address"	:
			case "mac_address"	:
			case "gateway"		:
			case "gateway"		:
			case "subnetmask"	:
			case "dns_server_1"	:
			case "dns_server_2"	:
				echo '<div class="div_table_cell">' . get_network_info($_eth, $_case) . '</div>';
				break;

			case "link"			:
				global $arr_running_status;
				global $arr_eth_name;

				echo '<div class="div_table_cell"><img src="' . Device_info\Def\PATH_WEB_IMG_CIRCLE;
				echo $arr_running_status[$arr_eth_name[$_eth]] == true ? "green" : "red";
				echo '.jpg" style="height: 20px;"></div>';
				break;
		}

		return ;
	}

	exec("sudo ifconfig", $arr_result);
	$arr_result = array_filter($arr_result);

	$arr_running_status = null;
	$arr_eth_list       = array("bond0", "eth0", "eth1");
	$arr_eth_name		= array("network_primary"=> "eth0", "network_secondary"=> "eth1", "network_bonding"=> "bond0");

	foreach( $arr_eth_list as $eth_device ) {
		$arr_running_status[$eth_device] = find_string($arr_result, $eth_device);
	}
?>