<?php
	namespace Monitor\Func {

		use Monitor;

		class MonitorFunc {
				private	$statData;

			function __construct() {
				$load_deviceList	= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/modules/monitor/conf/device_list.json");
				$statData			= json_decode($load_deviceList);
				$this->statData		= $statData;

				return ;
			}

			function getDeviceListCount() {
				return count($this->statData->device);
			}

			function setDeviceList($_ipaddr, $_hostname, $_location) {
				$filePath			= "../../conf/device_list.json";
				$load_deviceList	= file_get_contents($filePath);
				$arrData			= json_decode($load_deviceList, true);

				foreach($arrData['device'] as $device) {
					if( $device['ipaddr'] == $_ipaddr )  {
						return false;
					}
				}

				$arrData['device'][] = ['ipaddr' => $_ipaddr, 'hostname' => $_hostname, 'location' => $_location];

				file_put_contents($filePath, json_encode($arrData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

				return true;
			}

			function getDeviceList() {
				$filePath			= "../../conf/device_list.json";
				$load_deviceList	= file_get_contents($filePath);
				$statData			= json_decode($load_deviceList);
				$this->statData		= $statData;

				return $this->makeDeviceList();
			}

			function removeDeviceList($_ipaddr) {
				$filePath			= "../../conf/device_list.json";
				$load_deviceList	= file_get_contents($filePath);
				$arrData			= json_decode($load_deviceList, true);

				foreach($arrData['device'] as $idx => $device) {
					if( $device['ipaddr'] == $_ipaddr )  {

						unset($arrData['device'][$idx]);
						file_put_contents($filePath, json_encode($arrData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

						return true;
					}
				}

				return false;
			}

			function makeDeviceList() {
				$statData	= $this->statData;
				$deviceList	= "";
				$idx = 1;
				foreach($statData->device as $device) {
					$deviceList .= '<div class="divTableRow">
							<div class="divTableCell divTableCell_checkbox">
								<input type="checkbox" id="input_monitor_check_device_' . $idx . '" style="padding-top: 15px;"/>
							</div>
							<div class="divTableCell divTableCell_number">
								' . $idx . '
							</div>
							<div class="divTableCell divTableCell_ipaddr">
								<a class="div_monitor_table_list_link" href="http://' . $device->ipaddr . '" target="_new">
									<span id="span_monitor_table_ipaddr_' . $idx . '">' . $device->ipaddr . '</span>
								</a>
							</div>
							<div class="divTableCell divTableCell_hostname" title="' . $device->hostname . '">
								' . $device->hostname . '
							</div>
							<div class="divTableCell divTableCell_location" title="' . $device->location . '">
								' . $device->location . '
							</div>
							<div class="divTableCell divTableCell_device">
								<div id="div_monitor_table_device_' . $idx . '" class="circle_deact"></div>
							</div>
							<div class="divTableCell divTableCell_audio">
								<div id="div_monitor_table_audio_' . $idx . '" class="circle_deact"></div>
							</div>
							<div class="divTableCell divTableCell_levelmeter">
								<div class="outputVolume_' . $idx . '" style="clear:both;"></div>
								<div class="level_outputVolume_' . $idx . '" style="display:none;">0</div>
							</div>
						</div>
					';
					$idx++;
				}

				return $deviceList;
			}
		}
		
		include_once "common_script_etc.php";
	}
?>
