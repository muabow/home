<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_define.php";
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_script.php";

	include_once "common_define.php";
	include_once "common_script.php";

	$timeFunc = new Time_setup\Func\TimeSetupFunc();

	function execute($_cmd) {
		$rc = exec($_cmd . "; echo $?");

		if( $rc != 0 ) return false;
		return true;
	}

	if( $_POST["type"] == "time" && isset($_POST["act"]) ) {
		$act = $_POST["act"];
		if( $act == "get_time" ) {
			$timeFunc->getTimeData();
			$timezone_info = $timeFunc->setTimeZoneInfo();

			date_default_timezone_set($timezone_info[$timeFunc->getTimeZoneIdx()][$timeFunc::TIMEZONE_PATH]);

			$dt = new DateTime();
			$dt->setTimezone(new DateTimeZone(date_default_timezone_get()));
			echo $dt->format('Y-m-d H:i:s');

			return ;
		}
		else if( $act == "set_time" ) {
			$sysFunc = new Common\Func\CommonSystemFunc();

			$timeData = array(
							"timezone"			=>	$_POST["timezone"],
							"timezone_idx"		=>	$_POST["timezone_idx"],
							"timeserver_enable"	=>	$_POST["timeserver_enable"],
							"timeserver_url"	=>	$_POST["timeserver_url"],
							);

			$timeFunc->setTimeData($timeData);

			if( $timeData["timezone"] != "" && $timeData["timezone_idx"] != "" ) {
				$timeZoneList = $timeFunc->setTimeZoneInfo();
				$zonePath = $timeZoneList[$timeData['timezone_idx']][$timeFunc::TIMEZONE_PATH];

				$cmd = '"ln -fs /usr/share/zoneinfo/' . $zonePath . ' /etc/localtime;"';
				$sysFunc->execute($cmd);
			}

			if( $timeData['timeserver_enable'] == "on" ) {
				$str_exec = "";
				
				$checkNet = shell_exec("wget --timeout=1 --tries=1 --spider " . $timeData['timeserver_url'] . ":123 2>&1 | grep -c -e 'Connection timed out' -e 'Name or service not known'");
				if($checkNet == 1) {
					$str_exec = "error : connection timed out\n";
					
				} else {
					if( !execute("sudo timedatectl set-ntp 0") ) {
						$str_exec = "error : timedatectl failed\n";
					}
	
					if( !execute("sudo sntp -t 1 -s " . $timeData['timeserver_url']) ) {
						$str_exec = "error : sntp faild [" . $timeData['timeserver_url'] . "]\n";
	
					} else {
						if( file_exists("/dev/rtc") ) {
							if( !execute("sudo hwclock -w") ) {
								$str_exec = "error : hwclock failed\n";
							}
						}
					}
				}

				if( $str_exec != "" ) {
					$str_exec = "|error";
				}

				$period = $timeFunc->getPeriodTimeAsync();
				$dt = new DateTime();
				$dt->setTimezone(new DateTimeZone(date_default_timezone_get()));
				$dt->modify('+' . $period . ' hour');

				$syncTime = $dt->format('Y-m-d H:i:s');
				$timeFunc->setSyncTime($syncTime);
				echo $syncTime . $str_exec;

			} else {
				$today = mktime($_POST['setHour'], $_POST['setMinute'], $_POST['setSecond'], $_POST['setMonth'], $_POST['setDay'], $_POST['setYear']);
				$todayStr = date('Y-m-d H:i:s', $today);

				$cmd = '"timedatectl set-ntp 0; date -s \"' . $todayStr . '\"; hwclock -w;"';
				$sysFunc->execute($cmd);
			}

			session_start();
			$_SESSION["timeout"] = time();

			return ;
		}
		else if( $act == "get_syncTime" ) {
			echo $timeFunc->getSyncTimeAsync();

			return ;
		}
		else if( $act == "get_uptime" ) {
			$str   = file_get_contents('/proc/uptime');

			$num   = floatval($str);
			echo $num;

			return ;


			$secs  = $num % 60;
			$num   = (int)($num / 60);
			$mins  = $num % 60;
			$num   = (int)($num / 60);
			$hours = $num % 24;
			$num   = (int)($num / 24);
			$days  = $num;

			$upTime = array(
				"days"  => $days,
				"hours" => $hours,
				"mins"  => $mins,
				"secs"  => $secs
			);

			$rc = sprintf("%02d", $upTime["days"])
				. sprintf("%02d", $upTime["hours"])
				. sprintf("%02d", $upTime["mins"])
				. sprintf("%02d", $upTime["secs"]) ;

			echo floatval($str) . " / " . $rc;

			return ;
		}
	}

	include_once 'time_process_etc.php';
?>