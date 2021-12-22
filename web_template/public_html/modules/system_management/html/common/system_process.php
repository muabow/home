<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_define.php";
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_script.php";

	include_once "common_define.php";
	include_once "common_script.php";

	$systemFunc = new System_management\Func\SystemFunc();
	$sysFunc    = new Common\Func\CommonSystemFunc();

	$fp  = popen("lscpu | grep Architecture | grep arm | wc -l", "r");
	$arc = fread($fp, 1024);
	pclose($fp);

	if( $_POST["type"] == "system" && isset($_POST["act"]) ) {
		$act = $_POST["act"];

		if( $act == "get_time" ) {
			$statData = $systemFunc->getSystemStatData();

			echo '{"stat":"' . $statData->stat
					. '", "hour":"' . sprintf("%02d", $statData->hour)
					. '", "minute":"' . sprintf("%02d", $statData->minute)
					. '"}';

			return ;
		}
		else if( $act == "set_time" ) {
			$stat = $_POST["stat"];
			$hour = $_POST["hour"];
			$minute = $_POST["minute"];

			$systemFunc->setSystemStatData($stat, $hour, $minute);

			if( $stat == "on" ) {
				if( $arc == 0 ) { // x86
					$schedule = $minute . ' ' . $hour . ' * * * /opt/interm/public_html/modules/system_management/bin/noti_system_check_x86.sh \n';
				} else {
					$schedule = $minute . ' ' . $hour . ' * * * /opt/interm/public_html/modules/system_management/bin/noti_system_check.sh \n';
				}

				$schedule .= $minute . ' ' . $hour . ' * * * /opt/interm/bin/reboot.sh';
				$sysFunc->execute('"echo \"' . $schedule . '\" > /tmp/crontab_sc; /usr/bin/crontab -u root /tmp/crontab_sc"');

			} else {
				$sysFunc->execute('"/usr/bin/crontab -r;"');
			}

			$sysFunc->execute('"/etc/init.d/cron restart"');

			return ;
		}
		else if( $act == "reboot" ) {
			$sysFunc->execute('"/opt/interm/bin/reboot.sh 2>&1"');

			return ;
		}
		else if( $act == "factory" ) {
			shell_exec("sudo /opt/interm/bin/factory_default.sh");

			$rcPath = "/tmp/reset_result";
			if( file_exists($rcPath) ) {
				$fp = fopen($rcPath, "r");
				echo fread($fp, filesize($rcPath));

				fclose($fp);

			} else {
				echo "-1";
			}

			return ;
		}
		else if( $act == "upgrade" ) {
			// mDNS interface kill, TAG 3.9
			shell_exec("ps -ef | grep network_info_receiver | grep -v grep | awk '{print $2}' | xargs sudo kill");

			$filePath = $_POST["filePath"];
			$handle = popen("/opt/interm/bin/diff_firmware_version.sh \"" . $filePath . "\"", "r");
			$json_diff_info = fread($handle, 1024);
			pclose($handle);

			if( $json_diff_info == "" || $json_diff_info == null ) {
				echo "-1";
				return ;
			}

			$diff_info = json_decode($json_diff_info);

			if( $diff_info->code != 0 ) {
				echo $diff_info->code;
				return ;
			}

			shell_exec("sudo /opt/interm/bin/upgrade.sh \"" . $filePath . "\"");

			$rcPath = "/tmp/upgrade_result";
			if( file_exists($rcPath) ) {
				$fp = fopen($rcPath, "r");
				echo fread($fp, filesize($rcPath));

				fclose($fp);

			} else {
				echo "-1";
			}

			return ;
		}
		else if( $act == "reset_count" ) {
			$username = $_POST["username"];
			echo $systemFunc->reset_try_count($username);

			return ;
		}
	}

	include_once 'system_process_etc.php';
?>
