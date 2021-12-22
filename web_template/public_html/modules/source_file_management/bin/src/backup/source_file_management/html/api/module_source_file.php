<?php
	include_once '/opt/interm/public_html/api/api_websocket.php';

	$pathSrcfileStorage = $_SERVER["DOCUMENT_ROOT"] . "/modules/source_file_management/html/data/audiofiles/";
	$pathSrcfileModule = 'http://' . $_SERVER["HTTP_HOST"] . '/modules/source_file_management/html/common/source_file_process.php';

		use SQLite3;

		const ERR_DB_LOCK		= 5;
		const TIME_LOCK_PERIOD	= 20000;	// msec

		function updateDatabase($_filePath, $_tableName, $_setData) {
			if( !($db = new SQLite3($_filePath)) ) {
				shell_exec("echo " . $db->lastErrorMsg() . " > /tmp/sqllite3_error");
			}

			while( true ) {
				if( !($query = $db->query("update " . $_tableName . " set " . $_setData))  ) {
					shell_exec("echo " . $db->lastErrorMsg() . " > /tmp/sqllite3_error");
					if( $db->lastErrorCode() == ERR_DB_LOCK ) { // db lock
						usleep(TIME_LOCK_PERIOD);
						continue;
					}
				}
				break;
			}

			$db->close();

			return $query;
		}

	function readFilesFromDir($_dirPath)	{
		$storage = opendir($_dirPath);

		if( null == $storage ) {
			return null;
		}
		$files = array();

		for( $fileCnt = 0 ; true == ($file = readdir($storage)) ; ) 	{
			if( ('.' != $file) && ('..' != $file) ) {
				//var_export('[' . $fileCnt . '] : ' . $file . ' :::: ' );
				$files[$fileCnt] = $file;
				$fileCnt++;
			}
		}

		closedir($storage);
		return $files;
	}

	function stopPlayer($_targetName)
	{
		$count = 0;
		$execmd = "pkill " . $_targetName;
		pclose(popen('echo "' . $execmd . '" > /tmp/web_fifo', "r"));

		while(exec("ps -ef | grep audio_player | grep -v grep | grep -v php | grep -v sh | wc -l")) {
			$count++;
			if($count == 100) break;
			usleep(20000);
		}
	}

	function stopAudioClient()
	{
		$ws_handler = new WebsocketHandler("127.0.0.1", "audio_client");
		$ws_handler->send(0x12, null);

		return ;
	}

	function playPlayerForDir($_dirPath)
	{
		$pathAudioPlayer = $_SERVER['DOCUMENT_ROOT'] . '/modules/source_file_management/bin/audio_player';

		$execmd = $pathAudioPlayer . " " . $_dirPath . " -c 0 &";
		pclose(popen('echo "' . $execmd . '" > /tmp/web_fifo', "r"));
	}

	function playPlayerForFile($_dirPath, $_filePath, $_repeatCnt)
	{
		$pathAudioPlayer = $_SERVER['DOCUMENT_ROOT'] . '/modules/source_file_management/bin/audio_player';

		$execmd = $pathAudioPlayer ." \\\"". $_dirPath . htmlspecialchars_decode($_filePath) .  "\\\" -c ". $_repeatCnt . " &";
		pclose(popen('echo "' . $execmd . '" > /tmp/web_fifo', "r"));
	}

	function checkIsAlive($_targetName)
	{
		$proc = shell_exec('ps -ef | grep -w "'. $_targetName .'" | grep -v \'grep\'');

		if(null == $proc) {
			return 0;
		}
		else {
			return 1;
		}
	}

	function setEnvData($_state, $_ftype, $_fname, $_replay) { // for ajax
		$pathPlayConfFile = $_SERVER['DOCUMENT_ROOT'] . '/modules/source_file_management/conf/audio_player.db';

			$setList  = "";
			$setList .= "ftype       = '".$_ftype."', ";
			$setList .= "fname       = '".$_fname."', ";
			$setList .= "replayCnt 	 = '".$_replay."', ";
			$setList .= "state 		 = '".$_state."'";


			updateDatabase($pathPlayConfFile, "audio_player", $setList);

			return ;
		}


	$app->post(
		"/source_file/setPlayInfo",
		function() use($app) {
			global $pathSrcfileModule;
			global $pathSrcfileStorage;

			$inputData = $app->getPostContent();

			if( "play" == $inputData->action ) {
				stopAudioClient();
				stopPlayer("audio_player");

				if( true == $inputData->is_dir ) {
					setEnvData("play", "dir", "", 0);
					usleep(100);
					playPlayerForDir($pathSrcfileStorage);

				}
				else {
					setEnvData("play", "file", $inputData->fileName, $inputData->count);
					usleep(100);
					playPlayerForFile($pathSrcfileStorage, $inputData->fileName, $inputData->count);

				}
			}
			else {
				stopPlayer("audio_player");
				setEnvData("stop", "", "", 0);
			}

			$pathPlayConfFile = $_SERVER['DOCUMENT_ROOT'] . '/modules/source_file_management/conf/audio_player.db';

			$app->setResponseMessage("ok");
			/*
			$app->setResponseResult(array(
											"action" => $inputData->action,
											"storage" => $pathSrcfileModule,
											"isDir" => $inputData->is_dir,
											"srcfile" => $inputData->fileName,
											"count" => $inputData->count,
											"storage" => $pathSrcfileStorage,
											"confpath" => $pathPlayConfFile,
										 )
									);
			*/

			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	$app->get(
		"/source_file/getFileList",
		function() use($app) {
			global $pathSrcfileStorage;

 			$app->setResponseMessage("ok");
			$app->setResponseResult(array(
											"files" => readFilesFromDir($pathSrcfileStorage)
										 )
									);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);

	$app->get(
		"/source_file/getPlayStatus",
		function() use($app) {
 			$app->setResponseMessage("ok");
			$app->setResponseResult(array(
												"isRun" => checkIsAlive("audio_player")
												  )
											);
			$app->setResponseCode(200);

			return $app->getResponseData();
		}
	);
?>
