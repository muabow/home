<?php
	// Internal Version V1.0.2.0
	// Priority MultiSource Process
	// Version History
	// 1.0.1.5 : Add priority operation, Bug fix(DB Lock, single threading(mutex))
	// 1.0.2.0 : rebuilding process, change database data, add multi channel, add or contact on/off process
	
	include_once '/opt/interm/public_html/common/common_sqlite_interface.php';
	
	$dPriorityDBPath = $_SERVER['DOCUMENT_ROOT'] . '/../conf/priority_manager.ver.1.0.db';
	
	//db에 저장되어있는 우선순위값 추출
	function getPrioirtyData() {
		global $dPriorityDBPath;
		
		$query  = "SELECT * FROM tbl_priority";
		$rows = json_decode(query_interface($dPriorityDBPath, $query));
		
		$returnVal = ($rows == "-1") ? false : $rows; 
		return $returnVal;
	}
	
	//접점 병합
	function updateContact($_a, $_b) {
		$a = explode(",", $_a);
		$b = explode(",", $_b);
		
		foreach ($a as $key => $value) {
			if ($value == null) {
				unset($a[$key]);
			}
		}
		
		foreach ($b as $key => $value) {
			if ($value == null) {
				unset($b[$key]);
			}
		}
		
		$count 	= ( count($a) >= count($b) ) ? count($a) : count($b);
		
		$merge = array();

		for ($i = 0; $i < $count; $i++) {
			$value = (isset($a[$i])) ? $a[$i] : $b[$i];
			$value = ($value == "-1") ? $b[$i] : $value;
			$value = ($value == null) ? "-1" : $value;
			array_push($merge, $value);
		}
		
		return implode(",", $merge);
	}
	
	function getContactIsRun($_contact) {
		$_contact = explode(",", $_contact);
		return in_array("1", $_contact);
	}
	
	function actionCompare($_inputAction, $_dbAction) { // data = array(priority, channel, mode, type, cp, rm)
		$retrunVal = $_inputAction == $_dbAction;
		
		return $retrunVal; 
	}
	
	//db에 inputData를 insert or update
	function insertInputData($_inputData, $_mode) {
		global $dPriorityDBPath;
		
		// 채널별 우선순위 정보 추출
		$arrTmpRunning = getRunningOper();
		// 채널별 실행&대기 동작 개수 추출(접점출력 제외)
		$arrTmpCountOper = getCountOper();
		
		foreach ($_inputData as $action) {
			$isRun		= $action->isRun;
			$priority	= $action->priority;
			$type		= $action->type;
			$serverCH	= ($action->actions[0]->data->tx === null) 		 ? "" : $action->actions[0]->data->tx;
			$arrCH		= ($action->actions[0]->data->rx === null) 		 ? array("") : explode(",", $action->actions[0]->data->rx);
			$delay 		= ($action->actions[0]->data->delayMs === null)  ? "0" : $action->actions[0]->data->delayMs;
			$serverIP 	= ($action->actions[0]->data->ip === null)		 ? "" : $action->actions[0]->data->ip;
			$port	 	= ($action->actions[0]->data->port === null) 	 ? "" : $action->actions[0]->data->port;
			$castType 	= ($action->actions[0]->data->castType === null) ? "" : $action->actions[0]->data->castType;
			$CP			= ($action->actions[0]->data->CP === null) 		 ? $action->actions[1]->data->CP : $action->actions[0]->data->CP;
			$RM			= ($action->actions[0]->data->RM === null) 		 ? $action->actions[1]->data->RM : $action->actions[0]->data->RM;
			$count	 	= ($action->actions[0]->data->count === null) 	 ? "" : $action->actions[0]->data->count;
			$fileName 	= ($action->actions[0]->data->fileName === null) ? "" : $action->actions[0]->data->fileName;
			foreach($arrCH as $channel) {
				if ( $channel == "-1" ) {
					continue;
				}

				$channelIsNull = ( $channel == "" ) ? "AND f_channel is null " : "AND f_channel = '{$channel}' ";
				
				$query  = "SELECT * ";
				$query .= "FROM tbl_priority ";
				$query .= "WHERE f_mode = '{$_mode}' ";
				$query .= 	$channelIsNull;
				$query .= 	"AND f_type = '{$type}';";
				$rows = json_decode(query_interface($dPriorityDBPath, $query), true);
				if ( ($rows == 0) || (json_encode($rows) == "[]") ) { //$rows 값이 없다
					if ( $isRun ) { //이벤트 실행
						$arrValue = array(
							$_mode,
							$priority,
							$channel,
							$type,
							"0",
							$delay,
							$serverIP,
							$serverCH,
							$port,
							$castType,
							$CP,
							$RM,
							$count,
							$fileName
						);
						
						insertDatabase("tbl_priority", $arrValue);
					} else { //이벤트 종료
						continue;
					}
				} else { //$rows 값이 있다
					if ( $_mode == "button" ) {
						if ( $isRun ) { //버튼모드 실행명령
							if (count($rows) == 1) { //$rows가 1개만 검색되었다
								if ( ($rows[0][f_cp] == "" && $rows[0][f_rm] == "") ) { //$rows에 접점값이 없다
									if ( ($CP == "") && ($RM == "") ) { //inputData에 접점값이 없다
										$query  = "UPDATE tbl_priority ";
										$query .= "SET f_priority = '{$priority}', ";
										$query .= "f_ip = '{$serverIP}', ";
										$query .= "f_svr_channel = '{$serverCH}' ";
										$query .= "WHERE f_channel = '{$channel}' ";
										$query .= 	"AND f_mode = '{$_mode}' ";
										$query .= 	"AND f_type = '{$type}';"; 
										$rows = json_decode(query_interface($dPriorityDBPath, $query), true);
									} else { //inputData에 접점값이 있다
										$arrValue = array(
											$_mode,
											$priority,
											$channel,
											$type,
											"0",
											$delay,
											$serverIP,
											$serverCH,
											$port,
											$castType,
											$CP,
											$RM,
											$count,
											$fileName
										);
										
										insertDatabase("tbl_priority", $arrValue);
									}
								} else { //$rows에 접점값이 있다
									if ( ($CP == "") && ($RM == "") ) { //inputData에 접점값이 없다
										$arrValue = array(
											$_mode,
											$priority,
											$channel,
											$type,
											"0",
											$delay,
											$serverIP,
											$serverCH,
											$port,
											$castType,
											$CP,
											$RM,
											$count,
											$fileName
										);
										
										insertDatabase("tbl_priority", $arrValue);
									} else { //inputData에 접점값이 있다
										if ( $serverIP == $rows[0][f_ip] ) {
											$CP = updateContact($CP, $rows[0][f_cp]);
											$RM = updateContact($RM, $rows[0][f_rm]);
										}
										
										$query  = "UPDATE tbl_priority ";
										$query .= "SET f_cp = '{$CP}', ";
										$query .= "f_rm = '{$RM}', ";
										$query .= "f_priority = '{$priority}', ";
										$query .= "f_ip = '{$serverIP}', ";
										$query .= "f_svr_channel = '{$serverCH}' ";
										$query .= "WHERE f_channel = '{$channel}' ";
										$query .= 	"AND f_mode = '{$_mode}' ";
										$query .= 	"AND f_type = '{$type}';"; 
										$rows = json_decode(query_interface($dPriorityDBPath, $query), true);
									}
								}
							} else { //$rows가 2개이상 검색되었다
								foreach ($rows as $key => $value) {
									$index = $rows[$key][f_no];
									if ( ($rows[$key][f_cp] == "") || ($rows[$key][f_rm] == "") ) { //$rows에 접점값이 없는 $row
										if ( ($CP == "") || ($RM == "") ) { //inputData에 접점값이 없다
											$query  = "UPDATE tbl_priority ";
											$query .= "SET f_priority = '{$priority}', ";
											$query .= "f_ip = '{$serverIP}', ";
											$query .= "f_svr_channel = '{$serverCH}' ";
											$query .= "WHERE f_channel = '{$channel}' ";
											$query .= 	"AND f_mode = '{$_mode}' ";
											$query .= 	"AND f_type = '{$type}' ";
											$query .= 	"AND f_no = '{$index}';";
											$rows = json_decode(query_interface($dPriorityDBPath, $query), true);
										}
									} else { //$rows에 접점값이 있는 $row
										if ( ($CP != "") || ($RM != "") ) { //inputData에 접점값이 있다
											if ( $serverIP == $rows[$key][f_ip] ) {
												$CP = updateContact($CP, $rows[$key][f_cp]);
												$RM = updateContact($RM, $rows[$key][f_rm]);
											}
											
											$query  = "UPDATE tbl_priority ";
											$query .= "SET f_cp = '{$CP}', ";
											$query .= "f_rm = '{$RM}', ";
											$query .= "f_priority = '{$priority}', ";
											$query .= "f_ip = '{$serverIP}', ";
											$query .= "f_svr_channel = '{$serverCH}' ";
											$query .= "WHERE f_channel = '{$channel}' ";
											$query .= 	"AND f_mode = '{$_mode}' ";
											$query .= 	"AND f_type = '{$type}' ";
											$query .= 	"AND f_no = '{$index}';"; 
											$rows = json_decode(query_interface($dPriorityDBPath, $query), true);
										}
									}
								} //end $rows foreach
							}
						} else { //버튼모드 종료명령
							if (count($rows) == 1) { //$rows가 1개만 검색되었다
								if ( $rows[0][f_cp] == "" && $rows[0][f_rm] == "" ) { //$rows에 접점값이 없다
									if ( ($CP == "") && ($RM == "") ) { //inputData에 접점값이 없다
										$query  = "UPDATE tbl_priority ";
										$query .= "SET f_status = '2' ";
										$query .= "WHERE f_channel = '{$channel}' ";
										$query .= 	"AND f_mode = '{$_mode}' ";
										$query .= 	"AND f_type = '{$type}' ";
										$query .= 	"AND f_status = '1';";
										$rows = json_decode(query_interface($dPriorityDBPath, $query), true);
									}
								} else { //$rows에 접점값이 있다
									if ( ($CP != "") || ($RM != "") ) { //inputData에 접점값이 있다
										$CP = updateContact($CP, $rows[0][f_cp]);
										$RM = updateContact($RM, $rows[0][f_rm]);
	
										//접점병합 정보 중 1이 있는지 체크
										$stat = ( (getContactIsRun($CP) || getContactIsRun($RM)) == false ) ? ", f_status = '2'" : "";
										
										$query  = "UPDATE tbl_priority ";
										$query .= "SET f_cp = '{$CP}', ";
										$query .= "f_rm = '{$RM}'";
										$query .= $stat;
										$query .= " WHERE f_channel = '{$channel}' ";
										$query .= 	"AND f_mode = '{$_mode}' ";
										$query .= 	"AND f_type = '{$type}' ";
										$query .= 	"AND f_status = '1';";
										$rows = json_decode(query_interface($dPriorityDBPath, $query), true);
									}
								}
							} else { //$rows가 2개이상 검색되었다
								foreach ($rows as $key => $value) {
									$index = $rows[$key][f_no];
									if ( ($rows[$key][f_cp] == "") || ($rows[$key][f_rm] == "") ) { //$rows에 접점값이 없는는 $row
										if ( ($CP == "") || ($RM == "") ) { //inputData에 접점값이 없다
											$query  = "UPDATE tbl_priority ";
											$query .= "SET f_status = '2' ";
											$query .= "WHERE f_channel = '{$channel}' ";
											$query .= 	"AND f_mode = '{$_mode}' ";
											$query .= 	"AND f_type = '{$type}' ";
											$query .= 	"AND f_status = '1' ";
											$query .= 	"AND f_no = '{$index}';"; 
											$rows = json_decode(query_interface($dPriorityDBPath, $query), true);
										}
									} else { //$rows에 접점값이 있는 $row
										if ( ($CP != "") || ($RM != "") ) { //inputData에 접점값이 있다
											$CP = updateContact($CP, $rows[$key][f_cp]);
											$RM = updateContact($RM, $rows[$key][f_rm]);
	
											//접점병합 정보 중 1이 있는지 체크
											$stat = ( (getContactIsRun($CP) || getContactIsRun($RM)) == false ) ? ", f_status = '2'" : "";
											
											$query  = "UPDATE tbl_priority ";
											$query .= "SET f_cp = '{$CP}', ";
											$query .= "f_rm = '{$RM}'";
											$query .= $stat;
											$query .= " WHERE f_channel = '{$channel}' ";
											$query .= 	"AND f_mode = '{$_mode}' ";
											$query .= 	"AND f_type = '{$type}' ";
											$query .= 	"AND f_status = '1' ";
											$query .= 	"AND f_no = '{$index}';";
											$rows = json_decode(query_interface($dPriorityDBPath, $query), true);
										}
									}
								} //end foreach $rows
							}
						}
					} else { //이벤트일때
						if ( $isRun ) { //이벤트 실행
							foreach ($rows as $key => $value) {
								if ($priority == $value[f_priority]) {
									$inputAction = array($channel, $type, $delay, $serverIP, $serverCH, $port, $castType, $CP, $RM, $count, $fileName);
									$dbAction	 = array($value[f_channel], $value[f_type], $value[f_delayMs], $value[f_ip], $value[f_svr_channel], $value[f_port], $value[f_cast_type], $value[f_cp], $value[f_rm], $value[f_count], $value[f_filename]);
									if (actionCompare($inputAction, $dbAction)) { //$rows와 inputData의 값이 동일하다
										continue;
									} else { //$rows와 inputData의 값이 동일하지 않다.
										$query  = "UPDATE tbl_priority ";
										$query .= "SET f_cp = '{$CP}', ";
										$query .= "f_rm = '{$RM}', ";
										$query .= "f_ip = '{$serverIP}', ";
										$query .= "f_svr_channel = '{$serverCH}', ";
										$query .= "f_delayMs = '{$delay}', ";
										$query .= "f_port = '{$port}', ";
										$query .= "f_cast_type = '{$castType}', ";
										$query .= "f_count = '{$count}', ";
										$query .= "f_filename = '{$fileName}' ";
										$query .= "WHERE f_priority = '{$priority}' ";
										$query .= $channelIsNull;
										$query .= 	"AND f_mode = '{$_mode}' ";
										$query .= 	"AND f_type = '{$type}' ";
										$rows = json_decode(query_interface($dPriorityDBPath, $query), true);
										
										continue;
									}
								}
							}

							$arrValue = array(
								$_mode,
								$priority,
								$channel,
								$type,
								"0",
								$delay,
								$serverIP,
								$serverCH,
								$port,
								$castType,
								$CP,
								$RM,
								$count,
								$fileName
							);
							
							insertDatabase("tbl_priority", $arrValue);
							
						} else { //이벤트 종료
							$query  = "UPDATE tbl_priority ";
							$query .= "SET f_status = '2' ";
							$query .= "WHERE f_priority = '{$priority}' ";
							$query .= $channelIsNull;
							$query .= 	"AND f_mode = '{$_mode}' ";
							$query .= 	"AND f_type = '{$type}' ";
							$rows = json_decode(query_interface($dPriorityDBPath, $query), true);
						}
					}
				}
			}		
		} //end $_inputData foreach
		
		
		
		$arrNowOperating = getRunningOper();
		
		if(!isExtPriority()) {
			// 비상방송 중이 아닌 경우
			$query = "";
			foreach($arrNowOperating as $key => $val) {
				$query .= "select f_no, f_mode, cast(f_priority as int) as f_priority, f_channel, f_type, f_status, f_delayMs, f_ip, f_svr_channel, f_port, f_cast_type, f_cp, f_rm, f_count, f_filename ";
				$query .= "from tbl_priority ";
				$query .= "where f_channel = '" . $val["f_channel"] . "' ";
				
				//$actionType = $val->f_type;
				$actionType = $val["f_type"];
				
				switch($actionType) {
					// arrNowRunning 의 동작정보가 방송수신이다.
					case "audio_stream" :
						$query .= "and f_type = 'audio_stream' ";
						$query .= "and f_ip = '" . $val["f_ip"] . "' ";
						$query .= "union all ";
					break;
					// arrNowRunning 의 동작정보가 파일재생이다.
					case "broad_file" :
						$query .= "and f_type = 'broad_file' ";
						$query .= "and f_ip is null ";
						$query .= "and f_filename = '" . $val["f_filename"] . "' ";
						$query .= "union all ";
					break;
					// arrNowRunning 의 동작정보가 폴더재생이다.
					case "broad_folder" :
						$query .= "and f_type = 'broad_folder' ";
						$query .= "and f_ip is null ";
						$query .= "union all ";
					break;
					default :
					break;
				}
			}
			
			$query .= "select f_no, f_mode, cast(f_priority as int) as f_priority, f_channel, f_type, f_status, f_delayMs, f_ip, f_svr_channel, f_port, f_cast_type, f_cp, f_rm, f_count, f_filename ";
			$query .= "from tbl_priority ";
			$query .= "where f_type = 'contact'; ";
			
			//return $query;
			// 접점출력 정보 추출
			$rows = json_decode(query_interface($dPriorityDBPath, $query), true);
			
			$cpList = "";
			$rmList = "";
			
			// 추출된 접점정보 병합
			foreach($rows as $key => $value) {
				$tmpCP = $rows[$key]["f_cp"];
				$tmpRM = $rows[$key]["f_rm"];
				$tmpMode = $rows[$key]["f_mode"];
				$tmpStatus = $rows[$key]["f_status"];
				if($tmpStatus == "2") {
					$tmpCP = explode(",", $tmpCP);
					$tmpRM = explode(",", $tmpRM);
					if($tmpCP != null) {
						foreach($tmpCP as $_key => $_val) {
							if($_val == "1") {
								$tmpCP[$_key] = "0";
							}
						}
						$tmpCP = implode(",", $tmpCP);
					}
					if($tmpRM != null) {
						foreach($tmpRM as $_key => $_val) {
							if($_val == "1") {
								$tmpRM[$_key] = "0";
							}
						}
						$tmpRM = implode(",", $tmpRM);
					}
				}
				if($cpList == "" && $tmpCP != null) {
					$cpList = mergeOutputValue(null, $tmpCP, $tmpMode);
				} else {
					$cpList = mergeOutputValue($cpList, $tmpCP, $tmpMode);
				}
				if($rmList == "" && $tmpCP != null) {
					$rmList = mergeOutputValue(null, $tmpRM, $tmpMode);
				} else {
					$rmList = mergeOutputValue($rmList, $tmpRM, $tmpMode);
				}
			}
			
			// 추출된 접점정보 실행
			$url			= 'http://' . $_SERVER["HTTP_HOST"] . '/api/output/setOutputList';
			$postData 		= new stdClass;
			if($cpList != "") {
				$cpList = str_replace("-1", "0", $cpList);
				$postData->CP 	= explode(",", explode("@", $cpList)[0]);
				
			}
			if($rmList != "") {
				$rmList = str_replace("-1", "0", $rmList);
				$postData->RM 	= explode(",", explode("@", $rmList)[0]);
			}
			
			if($cpList != "" || $rmList != "") {
				$postData 		= json_encode($postData);
				PostExtSync($url, $postData);
			} else {
				//CP 와 RM 둘다 켜거나 끄지 않을 경우 존재하는 CP와 RM을 모두 끔.
				offOutputList();
			}
			
			// 현재 실행중인 정보와 실행할 정보를 비교하여 채널별 동작 정보가 다르면 실행
			if( count($arrTmpRunning) != 0 ) {
				foreach($arrTmpRunning as $tmpRunning) {
					$tmpNo = $tmpRunning["f_no"];
					$tmpCh = $tmpRunning["f_channel"];
					foreach($arrNowOperating as $nowOper) {
						$no = $nowOper["f_no"];
						$ch = $nowOper["f_channel"];
						if($ch == $tmpCh) {
							if($nowOper != $tmpRunning) {
								runOper($nowOper);
							}
						}
					}
				}
			} else {
				foreach($arrNowOperating as $nowOper) {
					runOper($nowOper);
				}
			}
			
			// 현재 실행중인 정보와 실행할 정보들을 확인해서 동작정보가 없는 채널별로 방송종료
			
			foreach ($arrTmpRunning as $tmpRunning) {
				$tmpCH = $tmpRunning[f_channel];
				$isStop = true;
				foreach ($arrNowOperating as $nowOper) {
					$nowCH = $nowOper[f_channel];
					if ($tmpCH == $nowCH) {
						$isStop = false;
					}
				}
				
				if($isStop) {
					forceStop($tmpCH);
				}
			}
		} else {
			$cpList = null;
			$rmList = null;
			$query .= "select f_no, f_mode, cast(f_priority as int) as f_priority, f_channel, f_type, f_status, f_delayMs, f_ip, f_svr_channel, f_port, f_cast_type, f_cp, f_rm, f_count, f_filename ";
			$query .= "from tbl_priority ";
			$query .= "where f_type = 'contact' and f_status != '2'; ";
			
			//return $query;
			// 접점출력 정보 추출
			$rows = json_decode(query_interface($dPriorityDBPath, $query), true);
			
			// 추출된 접점정보 병합
			foreach($rows as $key => $value) {
				$tmpCP = $rows[$key]["f_cp"];
				$tmpRM = $rows[$key]["f_rm"];
				$tmpMode = $rows[$key]["f_mode"];
				
				if($cpList == "" && $tmpCP != null) {
					$cpList = mergeOutputValue(null, $tmpCP, $tmpMode);
				} else {
					$cpList = mergeOutputValue($cpList, $tmpCP, $tmpMode);
				}
				if($rmList == "" && $tmpCP != null) {
					$rmList = mergeOutputValue(null, $tmpRM, $tmpMode);
				} else {
					$rmList = mergeOutputValue($rmList, $tmpRM, $tmpMode);
				}
			}
			
			// 추출된 접점정보 실행
			$url			= 'http://' . $_SERVER["HTTP_HOST"] . '/api/output/setOutputList';
			$postData 		= new stdClass;
			if($cpList != "") {
				//$cpList = str_replace("-1", "0", $cpList);
				$postData->CP 	= explode(",", explode("@", $cpList)[0]);
				
			}
			if($rmList != "") {
				//$rmList = str_replace("-1", "0", $rmList);
				$postData->RM 	= explode(",", explode("@", $rmList)[0]);
			}
			
			if($cpList != "" || $rmList != "") {
				$postData 		= json_encode($postData);
				PostExtSync($url, $postData);
			} else {
				//CP 와 RM 둘다 켜거나 끄지 않을 경우 존재하는 CP와 RM을 모두 끔.
				//offOutputList();
			}
		}
		 
		// DB Update(stat 1인 정보를 0으로 변경)
		$query = "update tbl_priority set f_status = '0' where f_status = '1'; ";
		json_decode(query_interface($dPriorityDBPath, $query), true);
		
		// 실행할 정보의 stat 를 1로 변경
		$noList = "";
		foreach($arrNowOperating as $key => $val) {
			$noList .= "'" . $val["f_no"] . "',";
		}
		
		$noList = substr($noList, 0, strlen($noList)-1);
		
		$query = "update tbl_priority set f_status = '1' where f_no in (" . $noList . ");";
		//return count($arrNowOperating);
		json_decode(query_interface($dPriorityDBPath, $query), true);
		
		// DB Update(stat 2인 정보를 삭제)
		$query = "delete from tbl_priority where f_status = '2'; ";
		json_decode(query_interface($dPriorityDBPath, $query), true);
		
		return "ok";
	}

	// 받은 출력값 병합
	// orgOutput = 1,1,0,-1,1@e,e,b,x		(e : 이벤트, b : 버튼, x : unknown)
	// nextOutput = 1,1,1,0,-1
	function mergeOutputValue($_orgOutput = null, $_nextOutput, $_mode = "x") {
		$arrOrg = null;
		$arrMode = null;
		if($_orgOutput != null) {
			$tmpArr = explode("@", $_orgOutput);
			$arrOrg = explode(",", $tmpArr[0]);
			$arrMode = explode(",", $tmpArr[1]);
		} else {
			$arrOrg = array();
		}
		$arrNext = explode(",", $_nextOutput);
		
		$arrMergeVal = $arrOrg;
		foreach($arrNext as $key => $value) {
			if(!isset($arrOrg[$key]))
				$arrOrg[$key] = $value;
			if(!isset($arrMode[$key]))
				$arrMode[$key] = $_mode;
			// do nothing list
			// 1 : 1 == 1
			// 0 : 0 == 0
			// -1 : -1 == -1
			
			if($arrOrg[$key] != $arrNext[$key]) {
				
				// must running list
				// 1 : 0 == 1
				// 1 : -1 == 1
				// 0 : 1 == 1
				if($arrOrg[$key] == "1" || $arrNext[$key] == "1") {
					if($arrOrg[$key] != "1") {
						$arrMode[$key] = $_mode;
					}
					$arrOrg[$key] = "1";
				}
				
				// must stoping list
				// 0 : -1 == 0
				// -1 : 0 == 0
				if(($arrOrg[$key] == "0" || $arrOrg[$key] == "-1") && ($arrNext[$key] == "-1" || $arrNext[$key] == "0")) {
					if($arrOrg[$key] != "0") {
						$arrMode[$key] = $_mode;
					}
					$arrOrg[$key] = "0";
				}
			}
		}
		return implode(",", $arrOrg) . "@" . implode(",", $arrMode);
	}

	// sqlite DB에 입력
	function insertDatabase($tableName, $arrColumnValue) {
		global $dPriorityDBPath;
		// 컬럼이름과 컬럼값의 갯수가 다르면 -1 리턴
		if(14 != count($arrColumnValue)) {
			return -1;
		}
		
		$columnName = array(
			"f_mode",			// 버튼모드인지 이벤트&프리셋에서 설정한 값인지 확인
			"f_priority",		// 우선순위
			"f_channel",		// 오디오 채널 정보 입력
			"f_type",			// audio_stream, broad_file, broad_folder, contact로 구분
			"f_status",			// 삭제대상 : 2, 실행중 : 1, 대기 : 0
			"f_delayMs",		// audio_stream에서 사용하는 값
			"f_ip",				// audio_server ip
			"f_svr_channel",	// audio_server_channel
			"f_port",			// audio_server port
			"f_cast_type",		// audio_server type
			"f_cp",				// 접점값
			"f_rm",				// rm값
			"f_count",			// broad_file, broad_folder repeat
			"f_filename"		// broad_file 재생할 파일 명
		);
		
		$query = "insert into " . $tableName . " (";
		foreach($columnName as $column) {
			$query .= $column . ", ";
		}
		$query = substr($query, 0, strlen($query)-2);
		$query .= ") values (";
		foreach($arrColumnValue as $colVal) {
			$value = $colVal == null ? "null" : "'" . $colVal . "'";
			$query .= $value . ", ";
		}
		$query = substr($query, 0, strlen($query)-2);
		$query .= ");";
		
		//return $query;
		$rows = json_decode(query_interface($dPriorityDBPath, $query), true);
		return json_encode($rows);
	}

	// 가장높은 우선순위를 가진 실행정보를 가져옴(채널별이므로 접점출력 제외)
	function getRunningOper() {
		global $dPriorityDBPath;
		$query  = "select f_no, f_mode, min(cast(f_priority as int)) as f_priority, f_channel, f_type, f_status, f_delayMs, f_ip, f_svr_channel, f_port, f_cast_type, f_cp, f_rm, f_count, f_filename ";
		$query .= "from tbl_priority ";
		$query .= "where f_status != '2' ";
		$query .= "and f_channel is not null ";
		$query .= "group by f_channel; ";
		return json_decode(query_interface($dPriorityDBPath, $query), true);
	}
	
	// 채널별 실행 및 대기동작 개수 가져옴
	function getCountOper() {
		global $dPriorityDBPath;
		$query  = "select f_channel, count(f_channel) as f_cnt_channel ";
		$query .= "from tbl_priority ";
		$query .= "where f_status != '2' ";
		$query .= "and f_channel is not null ";
		$query .= "group by f_channel; ";
		return json_decode(query_interface($dPriorityDBPath, $query), true);
	}
	
	// 채널별 기능 전체종료
	function forceStop($channel = null) {
		$gEmPriorityDBPath = $_SERVER['DOCUMENT_ROOT'] . '/../conf/em_priority_manager.ver.1.0.db';
		$query = "delete from tbl_priority;";
		query_interface($gEmPriorityDBPath, $query);
		// 비상방송수신 API
		$pathEmAudioModule = 'http://' . $_SERVER["HTTP_HOST"] . '/api/audio/client/emergency/stop';
		// 방송수신 API
		$pathAudioModule = 'http://' . $_SERVER["HTTP_HOST"] . '/api/audio/client/stop';
		// 음원파일 API
		$pathSrcfileModule = 'http://' . $_SERVER["HTTP_HOST"] . '/api/source_file/setPlayInfo';
		if(channel == null) {
			// 장치의 장치정보 추출
			$url = 'http://' . $_SERVER["HTTP_HOST"] . '/api/getDeviceInfo';
			$returnVal = GetSvrSync($url);
			$outCnt = "";
			foreach($returnVal->port as $key => $value) {
				if($key == "Audio") {
					$outCnt = $value->out;
					break;
				}
			}
			if($outCnt != "") {
				for($i = 1; $i <= $outCnt; $i++) {
					// 비상방송 종료
					$ws_handler = new WebsocketHandler("127.0.0.1", "audio_client");
					$postData   = array("emergency" => false);
					$data = json_encode($postData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
					$ws_handler->send(0x20, $data);
					usleep(10000);
					$ws_handler->term();
					// 방송수신 종료
 					$result = GetExtSync($pathAudioModule);
					
					// 음원파일 종료
					unset($postData);
					$postData->action 		= "stop";
					$result = PostExtSync($pathSrcfileModule, json_encode($postData));
				}
			}
		} else {
			// 비상방송 종료
			$ws_handler = new WebsocketHandler("127.0.0.1", "audio_client");
			$postData   = array("emergency" => false);
			$data = json_encode($postData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
			$ws_handler->send(0x20, $data);
			usleep(10000);
			$ws_handler->term();
			// 방송수신 종료
 			$result = GetExtSync($pathAudioModule);
			
			unset($postData);
			// 음원파일 종료
			$postData["action"] 			= "stop";
			$result = PostExtSync($pathSrcfileModule, json_encode($postData));
		}
	}

	// DB 정보로 기능 실행
	function runOper($operData) {
		// 방송수신 API
		$pathAudioModule = 'http://' . $_SERVER["HTTP_HOST"] . '/api/audio/client/initrun';
		// 음원파일 API
		$pathSrcfileModule = 'http://' . $_SERVER["HTTP_HOST"] . '/api/source_file/setPlayInfo';
		
		$type = $operData["f_type"];
		switch($type) {
			case "audio_stream" :
				$postData["castType"]		  	= $operData['f_cast_type'];
				$postData["ipAddr1"]	= $operData['f_ip'];
				$postData["port1"]		= $operData['f_port'];
				$postData["delayMs"]  		= $operData['f_delayMs'];

				$result = PostExtSync($pathAudioModule, json_encode($postData));
			break;
			case "broad_file" :
				$postData["is_dir"]						= false;
				$postData["fileName"]					= $operData["f_filename"];
				$postData["count"]						= $operData["f_count"];
				$postData["action"]						= "play";
				
				$result = PostExtSync($pathSrcfileModule, json_encode($postData));
			break;
			case "broad_folder" :
				$postData["is_dir"]						= true;
				$postData["action"]						= "play";
				
				$result = PostExtSync($pathSrcfileModule, json_encode($postData));
			break;
			default :
				return false;
			break;
		}
	}

	function getMyIP() {
		$load_envData = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/modules/network_setup/conf/network_stat.json");
		$networkData  = json_decode($load_envData);
		
		$serverIP = "";
		if ($networkData->network_bonding->use == "enabled") {
			$serverIP = $networkData->network_bonding->ip_address;
		} else if ($networkData->network_primary->use == "enabled") {
			$serverIP = $networkData->network_primary->ip_address;
		} else if ($networkData->network_secondary->use == "enabled") {
			$serverIP = $networkData->network_secondary->ip_address;
		}
		
		return $serverIP;
	}
	
	// 접점 및 RM 포트 전체 종료
	function offOutputList() {
		$url			= 'http://' . $_SERVER["HTTP_HOST"] . '/api/output/setOutputList';
		$postData 		= new stdClass;
		$postData->CP	= array_pad(array(), 48, "0");
		$postData->RM	= array_pad(array(), 48, "0");
		$postData 		= json_encode($postData);
		PostExtSync($url, $postData);
	}
	
	// 비상상황 해제 확인
	function isExtPriority() {
		return file_exists("/tmp/action_em_status");
	}
	
	$app->post(
		"/priority/buttonPriority",  //button mode
		function() use($app) {
			$inputData = $app->getPostContent();
			/*
			$result = InsertInputData($inputData, "button");
			
			$app->setResponseMessage("ok");
			$app->setResponseCode(200);
			$app->setResponseResult($result);

			return $app->getResponseData();
			*/
			$inputData["priority_mode"] = "button";
			
			$ws_handler = new WebsocketHandler("127.0.0.1", "priority_interface", false);
			
			$data = json_encode($inputData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
			$ws_handler->send(0x00, $data);
			usleep(10000);
			$ws_handler->term();
			
			$app->setResponseMessage("ok");
			$app->setResponseCode(200);
			$app->setResponseResult("");

			return $app->getResponseData();
		}
	);

	$app->post(
		"/priority/actionPriority",
		function() use($app) {
			$inputData = $app->getPostContent();
			/*
			$result = InsertInputData($inputData, "event");
			
			$app->setResponseMessage("ok");
			$app->setResponseCode(200);
			$app->setResponseResult($result);

			return $app->getResponseData();
			*/
			$inputData["priority_mode"] = "event";
			
			$ws_handler = new WebsocketHandler("127.0.0.1", "priority_interface", false);
			$data = json_encode($inputData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
			$ws_handler->send(0x00, $data);
			usleep(10000);
			$ws_handler->term();
			
			$app->setResponseMessage("ok");
			$app->setResponseCode(200);
			$app->setResponseResult("");

			return $app->getResponseData();
		}
	);


	$app->get(
		"/priority/getPriority",
		function() use($app) {
			$returnVal = array(
				"ip"		=>	getMyIP(),
				"priority"	=>	getRunningOper()
			);
			
			$app->setResponseMessage("ok");
			$app->setResponseCode(200);
			$app->setResponseResult($returnVal);

			return $app->getResponseData();

		}
	);

	$app->get(
		"/priority/setForceStop",
		function() use($app) {
			global $dPriorityDBPath;
			/*
			//접점 전체 종료
			offOutputList();
			
			//방송종료
			forceStop();
			
			// DB 내용 전체 삭제
			$query = "delete from tbl_priority;";
			query_interface($dPriorityDBPath, $query);
			
			// em 방송 상태 삭제
			shell_exec('rm /tmp/action_em_status');
			$app->setResponseMessage("ok");
			$app->setResponseCode(200);
			$app->setResponseResult("ok");

			return $app->getResponseData();
			*/
			$ws_handler = new WebsocketHandler("127.0.0.1", "priority_interface", false);
			$ws_handler->send(0x10, null);
			usleep(10000);
			$ws_handler->term();
			
			$app->setResponseMessage("ok");
			$app->setResponseCode(200);
			$app->setResponseResult("ok");

			return $app->getResponseData();
		}
	);
?>
