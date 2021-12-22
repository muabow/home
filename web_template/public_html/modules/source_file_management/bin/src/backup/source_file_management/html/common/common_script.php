<?php

	namespace Source_file_management\Func {

		use Source_file_management;
		use SQLite3;

		const ERR_DB_LOCK		= 5;
		const TIME_LOCK_PERIOD	= 20000;	// msec

		function selectDatabase($_filePath, $_tableName) {
			if( !($db = new SQLite3($_filePath)) ) {
				shell_exec("echo " . $db->lastErrorMsg() . " > /tmp/sqllite3_error");
			}

			while( true ) {
				if( !($query = $db->query("select * from " . $_tableName))  ) {
					shell_exec("echo " . $db->lastErrorMsg() . " > /tmp/sqllite3_error");
					if( $db->lastErrorCode() == ERR_DB_LOCK ) { // db lock
						usleep(TIME_LOCK_PERIOD);
						continue;
					}
				}
				break;
			}

			$arrRow = array();
			while( ($row = $query->fetchArray(SQLITE3_ASSOC)) ) {
				$arrRow[] = $row;
			}
			$db->close();

			return $arrRow[0];
		}

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

		class SrcFileMngFunc {

			private $playSetPath;			
			private $playSet;
			private $envData;
			
 			function __construct() {
 				$this->envData = $this->loadEnvData($_SERVER['DOCUMENT_ROOT'] . "/" . "modules/source_file_management/conf/audio_player.db"); 				
   			}
			
			function loadEnvData($_filePath) {
				$db_audioPlayer =  Source_file_management\Func\selectDatabase($_filePath, "audio_player");

				$load_envData = array();
				$load_envData["audio_player"] = array(
														"state" 		=> $db_audioPlayer["state"],
														"ftype" 		=> $db_audioPlayer["ftype"],
														"fname" 	 	=> $db_audioPlayer["fname"],
														"replayCnt" 	=> $db_audioPlayer["replayCnt"],
														"volume" 	 	=> $db_audioPlayer["volume"]
														);
				$encData = json_encode($load_envData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

				return json_decode($encData);
			}
			
			function getVolumeStat() {
				return $this->envData;
				
			}
			
			function getEnvData() { // for ajax

				return $this->loadEnvData($_SERVER['DOCUMENT_ROOT'] . "/" . "modules/source_file_management/conf/audio_player.db");

			}

			function getOperVolume() {
				$value = $this->envData->audio_player->volume;

				return $value; 
			}
			
			function getPlaySettings() {
				return $this->envData->audio_player;	
			}
			
			function writePlayDBSettings($_state, $_ftype, $_fname, $_replay, $_volume) {			
				$this->envData->audio_player->state = $_state;
				$this->envData->audio_player->ftype = $_ftype;
				$this->envData->audio_player->fname = htmlspecialchars_decode($_fname);
				$this->envData->audio_player->replayCnt = $_replay;
				$this->envData->audio_player->volume = $_volume;
				
				$this->setEnvData(json_encode($this->envData));
		
				return 0;
			}
			
			
			function getFileCount($_dirPath)
			{
				return shell_exec('find ' . $_dirPath . ' -type f | wc -l');	
			}
			
			function getDirMemSize($_dirPath)
			{
				return shell_exec('du -b ' . $_dirPath . ' | awk \'{print $1}\'');
			}
			
			function readFilesFromDir($dirPath)
			{
				$storage = opendir($dirPath);
				if(null == $storage)
				{
					return null;
				}	

				$files = array();

				for($fileCnt = 0; true == ($file = readdir($storage)); )
				{
					if(('.' != $file) && ('..' != $file))
					{
						//var_export('[' . $fileCnt . '] : ' . $file . ' :::: ' );
						$files[$fileCnt] = $file;
						$fileCnt++;
					}
				}
				
				sort($files);
/*			
				for(; $fileCnt < 15; $fileCnt++)
				{
					$files[$fileCnt] = "TEST_FILE_" . $fileCnt;
				}
*/			

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
				}
			}
			
			function stopAudioClient()
			{
			$pathAudioModule = 'http://127.0.0.1/modules/audio_setup/html/common/audio_process.php';
		
			$postData["type"] 			= "audio_client";
			$postData["act"] 			= "stop";
			$postData["con"] 			= "yes";

 			$result = $this->AudioStopSync($pathAudioModule,$postData);
			
			}

			function playPlayerForDir($_player, $_dirPath)
			{
				$execmd = $_player. " " . $_dirPath . " -c 0 &";
				pclose(popen('echo "' . $execmd . '" > /tmp/web_fifo', "r"));
			}

			function playPlayerForFile($_player, $_dirPath, $_fileName, $_repeatCnt)
			{
				$execmd = $_player . " \\\"" . $_dirPath . htmlspecialchars_decode($_fileName) .  "\\\" -c ". $_repeatCnt . " &";
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
			
			function deleteAllFile($_dirPath)
			{
				shell_exec("rm -f ". $_dirPath . "*");
			}
			
			function deleteFile($_dirPath, $_files)
			{
				$fileCnt = Count($_files);
					
				for($cnt = 0; $cnt < $fileCnt; $cnt++)
				{
					$execmd = 'rm -f "'. $_dirPath . htmlspecialchars_decode($_files[$cnt]) .'"';
					shell_exec($execmd);	
				}
			}
	
			function AudioStopSync($_url, $_postData) {

				$curlsession = curl_init();
				curl_setopt($curlsession, CURLOPT_URL, $_url);
				curl_setopt($curlsession, CURLOPT_POST, 1);
				curl_setopt($curlsession, CURLOPT_POSTFIELDS, $_postData);
				curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, 1);
		
				$result = curl_exec($curlsession);
				echo 'result '.$result;
		
				curl_close($curlsession);
		
				return $result;
			}
			
			function setEnvData($_statData) { // for ajax
				$statData = json_decode($_statData);

				$statData = $statData->audio_player;
				$setList  = "";
				if( isset($statData->ftype)) {
				$setList .= "ftype       = '{$statData->ftype}', ";

				}
				if( isset($statData->fname)) {
				$setList .= "fname       = '{$statData->fname}', ";

				}
				if( isset($statData->replayCnt)) {
				$setList .= "replayCnt 	 = '{$statData->replayCnt}', ";

				}
				if( isset($statData->state)) {
				$setList .= "state 	 = '{$statData->state}', ";

				}
	
				$setList .= "volume   	 = '{$statData->volume}'";

				updateDatabase("../../conf/audio_player.db", "audio_player", $setList);
				
				while(TRUE) {
					$this->checkData = $this->loadEnvData($_SERVER['DOCUMENT_ROOT'] . "/" . "modules/source_file_management/conf/audio_player.db"); 
					if($this->checkData->audio_player->state == $statData->state) {
						
						break;
					} else {
						usleep(2000);
					
					}	
				}

				return ;
			}

			function setVolumeData($_statData) { // for ajax
				$statData = json_decode($_statData);

				$statData = $statData->audio_player;
				$setList  = "";
				$setList .= "volume   	 = '{$statData->volume}'";

				updateDatabase("../../conf/audio_player.db", "audio_player", $setList);
				
				return ;
			}
		}
		
		include_once "common_script_etc.php";
	}
?>
