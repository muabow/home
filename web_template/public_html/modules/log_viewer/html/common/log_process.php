<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . "/common/common_define.php";

	include_once "common_define.php";
	include_once "common_script.php";

	$logFunc = new Log_viewer\Func\LogViewerFunc();

	if( $_POST["type"] == "log_view" && isset($_POST["act"]) ) {
		$act = $_POST["act"];

		if( $act == "log" || $act == "update" ) {
			$currentIdx = $_POST["index"];
			$line  		= $_POST["line"];
			$logType 	= $_POST["module"];

			echo $logFunc->getLogContents($logType, $line, $currentIdx);

			if( $act == "log" ) {
				$logFunc->setLogStatInfo("module", $logType);
				$logFunc->setLogStatInfo("line",   $line);
			}

			return ;
		}

		else if( $act == "index" ) {
			$logType = $_POST["module"];

			echo $curIdx = $logFunc->getCurrentIndex($logType);

			return ;
		}

		else if( $act == "stat" ) {
			$postType = array("scroll", "update", "time");

			foreach( $postType as $name ) {
				if( isset($_POST[$name]) ) {
					$value = $_POST[$name];

					$logFunc->setLogStatInfo($name, $value);

					break;
				}
			}

			return;
		}
		else if( $act == "default" ) {	// for ajax
			$load_logStat		= file_get_contents("../../conf/log_stat.json");
			$statData			= json_decode($load_logStat);

			$statData->module	= "common/system";

			file_put_contents("../../conf/log_stat.json", json_encode($statData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

			return;
		}
	}

	include_once 'log_process_etc.php';
?>
