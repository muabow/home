<?php
	$app->get(
			"/getDeviceInfo",
			function() use($app) {
				$arrUri = $app->getRequestURI();
				$machineId = trim(shell_exec('cat /etc/machine-id 2>/dev/null'));

				// env info
				$load_envData  	= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/env.json");
				$envData  		= json_decode($load_envData);
				
				//device info
				$load_deviceInfo = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/config-device-info.json");
				$deviceInfo 	 = json_decode($load_deviceInfo);
								
				$app->setResponseMessage("ok");
				$app->setResponseResult(
						array(
								"f_device_no"					=> $machineId,
								"device_name"					=> $envData->device->name,
								"type"							=> $envData->device->device_type,
								"version"						=> $envData->device->version,
								"port"							=> $deviceInfo->port,
								"support"						=> $deviceInfo->support )
					);
				$app->setResponseCode(200);

				return $app->getResponseData();
			}
	);

?>