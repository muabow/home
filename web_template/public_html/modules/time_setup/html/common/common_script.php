<?php // info_uptime module, commonLogFunc() class 참고
	namespace Time_setup\Func {
		use Time_setup;

		class TimeSetupFunc {
			const TIMEZONE_TYPE		= 0;
			const TIMEZONE_GMT		= 1;
			const TIMEZONE_NAME		= 2;
			const TIMEZONE_PATH		= 3;

			private $year, $month, $day, $hour, $min, $sec;
			private $timezone_info;
			private $timeData;

			function __construct() {
				$load_envData 	= file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/modules/time_setup/conf/time_stat.json");
				$this->timeData = json_decode($load_envData);

				$this->timezone_info = $this->setTimeZoneInfo();

				date_default_timezone_set($this->timezone_info[$this->timeData->timezone_idx][self::TIMEZONE_PATH]);

				$date = getdate();
				$this->year  = $date["year"];
				$this->month = $date["mon"];
				$this->day   = $date["mday"];
				$this->hour  = $date["hours"];
				$this->min   = $date["minutes"];
				$this->sec	 = $date["seconds"];

				return ;
			}

			function printYear() {
				$selected  = "";
				$limitYear = 2038;

				for( $year = 2001 ; $year < $limitYear ; $year++ ) {
					if( $this->year == $year ) $selected = "selected";

					echo '<option value="' . $year . '" ' . $selected . '>' . $year . '</option>';
					$selected = "";
				}

				return ;
			}

			function printMonth() {
				$selected  = "";

				for( $idx = 1 ; $idx < 13 ; $idx++ ) {
					if( $this->month == $idx ) $selected = "selected";

					$month = sprintf("%02d", $idx);
					echo '<option value="' . $month . '"' . $selected . '>' . $month . '</option>';
					$selected  = "";
				}

				return;
			}

			function printDay() {
				$selected  = "";
				$end_day = date("t", mktime(0, 0, 0, $this->month,1, $this->year));

       			for( $idx = 1 ; $idx < ($end_day + 1) ; $idx++ ) {
       				if( $this->day == $idx ) $selected = "selected";

					$day = sprintf("%02d", $idx);
					echo '<option value="' . $day . '"' . $selected . '>' . $day . '</option>';
					$selected  = "";
				}
   			}

            function printHour() {
            	$selected  = "";

       			for( $idx = 0 ; $idx < 24 ; $idx++ ) {
       				if( $this->hour == $idx ) $selected = "selected";

					$hour = sprintf("%02d", $idx);
					echo '<option value="' . $hour . '"' . $selected . '>' . $hour . '</option>';
					$selected  = "";
				}

				return ;
   			}

			function printMinute() {
				$selected  = "";

				for( $idx = 0 ; $idx < 60 ; $idx++ ) {
					if( $this->min == $idx ) $selected = "selected";

					$min = sprintf("%02d", $idx);
					echo '<option value="' . $min . '"' . $selected . '>' . $min . '</option>';
					$selected  = "";
				}

				return ;
			}

			function printSec() {
				$selected  = "";

				for( $idx = 0 ; $idx < 60 ; $idx++ ) {
					if( $this->sec == $idx ) $selected = "selected";

					$sec = sprintf("%02d", $idx);
					echo '<option value="' . $sec . '"' . $selected . '>' . $sec . '</option>';
					$selected  = "";
				}

				return ;
			}

			function printZoneInfo() {
				$cnt = count($this->timezone_info);

				for($idx = 0 ; $idx < $cnt ; ++$idx ) {
					$time_zone_selected = "";

					if( $idx == $this->timeData->timezone_idx ) {
						$time_zone_selected = "selected";
					}

					echo '<option value ="' . $idx . '" ' . $time_zone_selected . '> (' . $this->timezone_info[$idx][self::TIMEZONE_GMT] . ')  '. $this->timezone_info[$idx][self::TIMEZONE_NAME] .'</option>';
				}

				return ;
			}

			function printCurrentZoneInfo() {
				echo "(" . $this->timezone_info[$this->timeData->timezone_idx][self::TIMEZONE_GMT] . ") ";
				echo $this->timezone_info[$this->timeData->timezone_idx][self::TIMEZONE_NAME];
			}

			function getTimeServerStat($_stat) {
				$displayMsg = "";

				if( $this->timeData->timeserver->enable == $_stat ) {
					$displayMsg = 'checked';
				} else {
					$displayMsg = '';
				}

				echo $displayMsg;

				return ;
			}

			function getTimeServer() {
				return  $this->timeData->timeserver->enable;
			}


			function getTimeStatColor() {
				if( $this->timeData->timeserver->enable== "on" ) {
					return "green";

				} else {
					return "red";
				}
			}


			function getTimeServerMode($_type) {
				$displayMsg = "";
				if( $_type == "custom" ) {
					if( $this->timeData->timeserver->enable == "on" ) {
						$displayMsg = 'style="display: none;"';

					} else {
						$displayMsg = 'style="display: block;"';
					}

				} else {
					if( $this->timeData->timeserver->enable == "on" ) {
						$displayMsg = 'style="display: flex;"';

					} else {
						$displayMsg = 'style="display: none;"';
					}
				}

				echo $displayMsg;

				return ;
			}

			function getTimeServerUrl() {
				echo $this->timeData->timeserver->url;

				return ;
			}

			function setTimeData($_timeData) { // for ajax
				$load_envData 	= file_get_contents("../../conf/time_stat.json");
				$envData        = json_decode($load_envData);

				if( $_timeData["timezone"] != "" ) {
					$envData->timezone				= $_timeData["timezone"];
				}
				if( $_timeData["timezone_idx"] != "" ) {
					$envData->timezone_idx			= $_timeData["timezone_idx"];
				}
				$envData->timeserver->enable	= $_timeData["timeserver_enable"];
				$envData->timeserver->url		= $_timeData["timeserver_url"];

				file_put_contents("../../conf/time_stat.json", json_encode($envData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

				return ;
			}

			function setSyncTime($_time) { // for ajax
				$load_envData 	= file_get_contents("../../conf/time_stat.json");
				$envData        = json_decode($load_envData);

				$envData->timeserver->nextSync	= $_time;

				file_put_contents("../../conf/time_stat.json", json_encode($envData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

				return ;
			}

			function getSyncTime() {
				echo $this->timeData->timeserver->nextSync;

				return ;
			}

			function getPeriodTimeAsync() { // for ajax
				$load_envData 	= file_get_contents("../../conf/time_stat.json");
				$envData        = json_decode($load_envData);

				return $envData->timeserver->period;
			}

			function getSyncTimeAsync() { // for ajax
				$load_envData 	= file_get_contents("../../conf/time_stat.json");
				$envData        = json_decode($load_envData);

				return $envData->timeserver->nextSync;
			}

			function getTimeData() { // for ajax
				$load_envData 	= file_get_contents("../../conf/time_stat.json");
				$this->timeData  = json_decode($load_envData);

				return ;
			}

			function getTimeZoneIdx() {
				return $this->timeData->timezone_idx;
			}


			function setTimeZoneInfo() {
				return json_decode(file_get_contents("/opt/interm/conf/config-timezone-info.json"));
			}
		}

		include_once "common_script_etc.php";
	}
?>
