<?php
	namespace Mode_management\Func {
		use Mode_management;

		class ModeFunc {
			private $envData;

			function __construct() {
				$load_envData	= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/env.json");
				$this->envData 	= json_decode($load_envData);

				return ;
			}

			// ex) postIntSync("http://192.168.1.99/modules/audio_setup/html/common/audio_process.php", $data)
			function postIntSync($_url, $_postData) {
				$curlsession = curl_init();
				curl_setopt($curlsession, CURLOPT_URL, $_url);
				curl_setopt($curlsession, CURLOPT_POST, 1);
				curl_setopt($curlsession, CURLOPT_POSTFIELDS, $_postData);
				curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, 1);

				$result = curl_exec($curlsession);

				curl_close($curlsession);

				return $result;
			}

			function getCurrentMode() {

				return $this->envData->mode->set;
			}

			function getSupportMode() {
				$stat = false;

				if( isset($this->envData->mode) ) {
					$stat = true;
				}

				return $stat;
			}

			function getModeList() {
				$modeList = "";
				$idx 	  = 0;

				$envMode  = $this->envData->mode;

				foreach( $envMode->list as $mode ) {
					$checked = "";
					if( $mode == $envMode->set ) $checked = "checked";

					$upStrMode   = strtoupper(str_replace(" ", "_", $mode));
					$descSupport = constant("Mode_management\Lang\STR_MODE_SUPPORT_" . $upStrMode);

					$modeList .= '
								<div class="divTableRow">
										<div class="divTableCell_left">
											<input type="radio" name="radio_mode_select" value="' . $idx . '" ' . $checked . ' />
										</div>
										<div class="divTableCell" id="div_mode_name_' . $idx . '">
										' . $mode . '
										</div>
										<div class="divTableCell" id="div_mode_support_' . $idx . '" style="text-align: left;">
										' . $descSupport . '
										</div>
									</div>
							';
					$idx++;
				}

				return $modeList;
			}

			function setModeList($_modeName) {
				if( $this->envData->mode->set == $_modeName ) {
					return "0";
				}

				$this->envData->mode->set = $_modeName;
				if( !file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/env.json", json_encode($this->envData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)) ) {
					return "-1";
				}

				// # STAND ALONE MODE
				// module: audio_setup
				$url = "http://127.0.0.1/modules/audio_setup/html/common/audio_process.php";
				$postData = null;
				$postData["type"] 			= "audio";
				$postData["act"] 			= "change_module_status";
				$postData["mode"]			= $_modeName;
				$this->postIntSync($url, $postData);

				// module: source_file_management
				$url = "http://127.0.0.1/modules/source_file_management/html/common/common_process.php";
				$postData = null;
				$postData["act"] 			= "change_module_status";
				$postData["mode"]			= $_modeName;
				$this->postIntSync($url, $postData);

				// module: log_viewer
				$url = "http://127.0.0.1/modules/log_viewer/html/common/log_process.php";
				$postData = null;
				$postData["type"] 			= "log_view";
				$postData["act"] 			= "default";
				$this->postIntSync($url, $postData);


				// 이하 AOE-N300 대상
				// module: serial_232_setup
				$url = "http://127.0.0.1/modules/serial_232_setup/html/common/common_process.php";
				$postData = null;
				$postData["type"] 			= "serial_232";
				$postData["act"] 			= "change_module_status";
				$postData["mode"]			= $_modeName;
				$this->postIntSync($url, $postData);

				// module: serial_422_setup
				$url = "http://127.0.0.1/modules/serial_422_setup/html/common/common_process.php";
				$postData = null;
				$postData["type"] 			= "serial_422";
				$postData["act"] 			= "change_module_status";
				$postData["mode"]			= $_modeName;
				$this->postIntSync($url, $postData);

				// module: contact_setup
				$url = "http://127.0.0.1/modules/contact_setup/html/common/common_process.php";
				$postData = null;
				$postData["type"] 			= "contact";
				$postData["act"] 			= "change_module_status";
				$postData["mode"]			= $_modeName;
				$this->postIntSync($url, $postData);


				// # INPUT CONNECTING MODE
				// module: device_mode_input
				shell_exec("sudo php {$_SERVER['DOCUMENT_ROOT']}/modules/device_mode_input/bin/device_mode_handler.php");


				// # OUTPUT CONNECTING MODE
				// module: device_mode_output
				shell_exec("sudo php {$_SERVER['DOCUMENT_ROOT']}/modules/device_mode_output/bin/device_mode_handler.php");

				// module: source_file_setup
				$url = "http://127.0.0.1/modules/source_file_setup/html/common/common_process.php";
				$postData = null;
				$postData["act"] 			= "change_module_status";
				$postData["mode"]			= $_modeName;
				$this->postIntSync($url, $postData);


				return "1";
			}
		}

		include_once "common_script_etc.php";
	}
?>
