<?php
	// PHP 함수 등을 작성합니다.
    namespace System_management\Func {
       use System_management;

       class SystemFunc {
       		private $stat, $hour, $minute;

       		function __construct() {
       			$statData = $this->getSystemCheckTime();

				$this->stat = $statData->stat;
				$this->hour = $statData->hour;
				$this->minute = $statData->minute;

				return ;
       		}

			function getAccountList($_userName) {
				$envAuth  = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/user_auth_list.json");
				$userList = json_decode($envAuth, true);

				$rc = "";
				$userLevel = $userList[$_userName];

				foreach( $userList as $user => $level ) {
					if( $level >= $userLevel ) {
						$rc .= "<option value=\"$level\">$user</option>";
					}
				}
				echo $rc;

				return;
			}

       		function getTimeListHour() {
       			$statTime = $this->hour;
				$selected = "";

				for( $idx = 0 ; $idx < 24 ; $idx++ ) {
					$hour = sprintf("%02d", $idx);
					if( $statTime == $idx ) {
						$selected = "selected";
					}
					echo "<option value=\"" . $hour . "\" " . $selected .">$hour</option>";
					$selected = "";
				}

				return ;
       		}

       		function getTimeHour() {
       			return $this->hour;
       		}

			function getTimeListMinute() {
				$statTime = $this->minute;
				$selected = "";

				for( $idx = 0 ; $idx < 60 ; $idx++ ) {
					$min = sprintf("%02d", $idx);
					if( $statTime == $idx ) {
						$selected = "selected";
					}

					echo "<option value=\"" . $min . "\" " . $selected .">$min</option>";
					$selected = "";
				}

				return ;
            }

			function getTimeMinute() {
       			return $this->minute;
       		}

			function getTimeStatClassOperation() {
				if( $this->stat == "on" ) {
					return "div_switchBackground";

				} else {
					return "";
				}
			}

			function getTimeStatClassToggle() {
				if( $this->stat == "on" ) {
					return "div_switchBackOnOff";

				} else {
					return "";
				}
			}

			function getTimeStat() {
				return $this->stat;
			}

			function getTimeStatColor() {
				if( $this->stat == "on" ) {
					return "green";

				} else {
					return "red";
				}
			}

			function getSystemCheckTime() {
				$load_systemStat	= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/modules/system_management/conf/system_stat.json");
				$statData			= json_decode($load_systemStat);

				return $statData;
			}

			function getSystemStatData() { // for ajax
				$load_systemStat	= file_get_contents("../../conf/system_stat.json");
				$statData			= json_decode($load_systemStat);

				return $statData;
			}

			function setSystemStatData($_stat, $_hour, $_minute) { // for ajax
				$load_systemStat	= file_get_contents("../../conf/system_stat.json");
				$statData			= json_decode($load_systemStat);

				$statData->stat = $_stat;
				$statData->hour = $_hour;
				$statData->minute = $_minute;

				file_put_contents("../../conf/system_stat.json", json_encode($statData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

				return true;
			}

			function getSystemCheckStat()   {				return $this->stat;			}
			function getSystemCheckHour()   {				return $this->hour;			}
			function getSystemCheckMinute() {				return $this->minute;		}

			function get_try_count($_username) {
				$path_try_count = $_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/" . $_username . "/try_count";
				$try_cnt = file_get_contents($path_try_count);

				return $try_cnt;
			}

			function reset_try_count($_username) {
				$path_try_count = $_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/" . $_username . "/try_count";
				file_put_contents($path_try_count, 0);

				return 0;
			}

			function get_lock_status($_username) {
				$path_try_count = $_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/" . $_username . "/try_count";
				$try_cnt = file_get_contents($path_try_count);

				if( $try_cnt == System_management\Def\NUM_LOGIN_TRY_COUNT ) {
					return "lock_img";

				} else {
					return "unlock_img";
				}
			}
		}

		include_once "common_script_etc.php";
    }
?>
