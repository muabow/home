<?php
	// PHP 함수 등을 작성합니다.
	namespace Network_setup\Func {
		use Network_setup;

		class NetworkFunc {
			private $envData;

			function __construct() {
				$load_envData  	= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/modules/network_setup/conf/network_stat.json");
				$this->envData 	= json_decode($load_envData);

				return ;
			}

			function getEnableTabStat($_type) {
				if( $this->envData->$_type->use == "disabled" ) {
					return false;
				}

				return true;
			}

			function getEnableTab($_type) {
				if( $_SESSION['username'] == "dev" ) return true;

				if( $this->envData->$_type->use == "disabled" ) {
					return false;
				}

				return true;
			}

			function getTabStat($_stat) {
				if( $this->envData->tabStat == $_stat ) {
					return "tabs_list_click";

				} else {
					return "";
				}
			}

			function getTabViewStat($_stat) {
				$stat = "network_" . $_stat;

				if( $this->envData->$stat->view == "enabled" ) {
					return "";

				} else {
					return 'style="display: none;"';
				}
			}

			function getTabContentStat($_stat) {
				if( $this->envData->tabStat == $_stat ) {
					return "";

				} else {
					return 'style="display: none;"';
				}
			}

			function getNetworkInfo($_type, $_parameter) {
				if( $_type == "common" ) {
					echo $this->envData->$_parameter;
				} else {
					echo $this->envData->$_type->$_parameter;
				}

				return ;
			}

			function getNetworkInputDisabled($_type) {
				if( $this->envData->$_type->use == "enabled" ) {
					if( $this->envData->$_type->dhcp == "on" ) {
						echo ' disabled style="color: #808080; background: #ebebe4;"';
					} else {
						echo 'style="color: #000000"';
					}

				} else {
						echo ' disabled style="color: #808080; background: #ebebe4;"';
				}
				return ;
			}

			function getNetworkDhcpStat($_type, $_stat) {
				$result = "";

				if( $this->envData->$_type->dhcp == $_stat ) {
					$result = "checked";
				}

				echo $result;

				return ;
			}

			function getNetworkEnableStat($_type, $_stat) {
				$result = "";

				if( $this->envData->$_type->use == $_stat ) {
					$result = "checked";
				}

				echo $result;

				return ;
			}

			function getNetworkDhcp($_type) {
				return $this->envData->$_type->dhcp;
			}

			function getNetworkStatColor($_type) {
				if( $this->envData->$_type->dhcp == "on" ) {
					return "green";

				} else {
					return "red";
				}
			}

			function getEnvData() {

				return $this->envData;
			}

			function getNetworkStat() { // for ajax
				$load_envData  				= file_get_contents("../../conf/network_stat.json");
				$envData 		  			= json_decode($load_envData);

				return $envData;
			}

			function setNetworkStat($_statData) { // for ajax

				file_put_contents("../../conf/network_stat.json", json_encode($_statData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

				return ;
			}
		}

		include_once "common_script_etc.php";
	}
?>
