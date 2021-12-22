<?php // time_setup module 종속
	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";

	const TIMEZONE_TYPE		= 0;
	const TIMEZONE_GMT		= 1;
	const TIMEZONE_NAME		= 2;
	const TIMEZONE_PATH		= 3;

	function uptime() {
        $str   = file_get_contents('/proc/uptime');
        $num   = floatval($str);
        $secs  = $num % 60;
        $num   = (int)($num / 60);
        $mins  = $num % 60;
        $num   = (int)($num / 60);
        $hours = $num % 24;
        $num   = (int)($num / 24);
        $days  = $num;

        return array(
            "days"  => $days,
            "hours" => $hours,
            "mins"  => $mins,
            "secs"  => $secs
        );
    }

	function setTimeZoneInfo() {
		return json_decode(file_get_contents("/opt/interm/conf/config-timezone-info.json"));
	}

	$upTime = uptime();
	if( file_exists($_SERVER['DOCUMENT_ROOT'] . "/modules/time_setup/conf/time_stat.json") ) {
		$load_envData 	= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/modules/time_setup/conf/time_stat.json");
		$timeData 		= json_decode($load_envData);
		$timezone_info 	= setTimeZoneInfo();

		date_default_timezone_set($timezone_info[$timeData->timezone_idx][TIMEZONE_PATH]);

		$date = getdate();
		$currentTime =    $date["year"]  . "."
						. sprintf("%02d", $date["mon"])    . "."
						. sprintf("%02d", $date["mday"])   . " / "
						. sprintf("%02d", $date["hours"])  . ":"
						. sprintf("%02d", $date["minutes"]). ":"
						. sprintf("%02d", $date["seconds"]);

	} else {
		$currentTime = date("Y.m.d / H:i:s", time());
	}

	$load_envData  	= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . Common\Def\PATH_SYS_ENV_JSON);
	$envData  		= json_decode($load_envData);

	$modeInfo = "";
	if( isset($envData->mode) ) {
		if( $envData->mode->set != "STAND ALONE" ) {
			$modeInfo = '
						<span class="span_main_menu_view_title" style="height: 2px; padding-top: 10px; padding-bottom: 5px; display: flex;">
							<hr style="border: dotted 0.5px #2f312e; flex: 1 1 0;">
						</span>
						<span class="span_main_menu_view_title">' . Time\Lang\STR_MENU_MODE_NAME . '</span>
						<span class="span_main_menu_view" id="span_menu_mode"> ' . $envData->mode->set . ' </span>
						';
		}
	}

	$mainMenu .= '<div class="div_main_menu_viewer">
						<span class="span_main_menu_view_title">' . Time\Lang\STR_MENU_SYSTEM_CONNECT_TIME . '</span>
						<span class="span_main_menu_view" id="span_menu_connectTime">' . $currentTime . ' </span>
						<span class="span_main_menu_view_title">' . Time\Lang\STR_MENU_SYSTEM_CURRENT_TIME . '</span>
						<span class="span_main_menu_view" id="span_menu_currentTime">' . $currentTime . ' </span>
						<span class="span_main_menu_view_title">' . Time\Lang\STR_MENU_SYSTEM_UPTIME_TIME . '</span>
						<span class="span_main_menu_view" id="span_menu_upTime">' . sprintf("%02d", $upTime["days"]) . ' ' . Time\Lang\STR_MENU_SYSTEM_DAYS . ' '
															. sprintf("%02d", $upTime["hours"]). ':'
															. sprintf("%02d", $upTime["mins"]) . ':'
															. sprintf("%02d", $upTime["secs"]) . ' ' . Time\Lang\STR_MENU_SYSTEM_ELAPSED . ' </span>
						' . $modeInfo . '
				   </div>' . "\n";
?>

<?php include $env_pathModule . "common/common_js.php"; ?>
