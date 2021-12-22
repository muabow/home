<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

$TEMP_SCHEDULE_FILE = "/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_schedule_temp.xml";
$SCHEDULE_FILE = "/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_schedule.xml";

$timezone = @date_default_timezone_get();
if (!isset($timezone) || $timezone == '') {
	$timezone = @ini_get('date.timezone');
}
if (!isset($timezone) || $timezone == '') {
	$timezone = 'UTC';
}
date_default_timezone_set($timezone);

include_once("/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/fw_config.php");

function json_encode2($content) {

	require_once '/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/libs/JSON.php';

	$json = new Services_JSON;

	return $json->encode($content);
}
function json_decode2($content) {

	require_once '/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/libs/JSON.php';

	$json = new Services_JSON;

	return $json->decode($content);
}

switch($_GET['action']) 
{
	case 'add':addEvent(); break;
	case 'get':getEvents(); break;
	case 'update':updateEvent(); break;
	case 'resize':resizeEvent(); break;
	case 'del':delEvent(); break;
	case 'del_this':delAnEvent(); break;
	case 'getHolidays':getHolidays(); break;
	case 'XMLUpload':XMLUpload(); break;
	case 'scheduleCopy':scheduleCopy(); break;
	case 'copySchedule':copySchedule(); break;
	case 'dur':getDuring(); break;
	case 'setDateFormat':setDateFormat(); break;
}

// ID를 구하는 함수
function getID()
{
	$big_id = 0;
	
	if (file_exists($GLOBALS['SCHEDULE_FILE']) == false) {
		file_put_contents($GLOBALS['SCHEDULE_FILE'],'<?xml version="1.0" encoding="utf-8"?><interm_schedule></interm_schedule>');
	}

	$xml1 = simplexml_load_file($GLOBALS['SCHEDULE_FILE']);
	
	foreach($xml1 as $schedule) {
		$ref_id = $schedule->schedule->created_at;
		$comp_id = (int)substr($ref_id,3,3);
		if ($big_id <= $comp_id){
			$big_id = $comp_id+1;
		}	
	}
	unset($xml1);
	$format = "id_%03d";
	$new_id = sprintf($format, $big_id); 
	return $new_id;
}

// ID를 재배열하여 적용하는 함수
function reorderID()
{
	$index = 0;
	$xml1 = simplexml_load_file($GLOBALS['SCHEDULE_FILE']);
	foreach($xml1 as $schedule) {
		$format = "id_%03d";
		$new_id = sprintf($format, $index); 
		$schedule->schedule->created_at = $new_id;
		$index++;		
	}
	file_put_contents($GLOBALS['SCHEDULE_FILE'],$xml1->saveXML());

    $xmlDoc = new DOMDocument();
    $xmlDoc->preserveWhiteSpace = false;
    $xmlDoc->load($GLOBALS['SCHEDULE_FILE']);
        
    $xmlDoc->formatOutput = true;
    $xmlDoc->save($GLOBALS['SCHEDULE_FILE']);
    unset($xmlDoc);		

}

function convert2crontab()
{
	$cmd = '/bin/php -q -f /opt/interm/www/html/crontab_convert.php';
	//shell_exec($cmd); 
}

function getEvents() {
	$start = substr($_POST['start'], 0, 10);
	$end = substr($_POST['end'], 0, 10);
	echo getEvent_list($start, $end, "");
}

function getEvent_list($start_t, $end_t, $file_name) 
{
	$m = "cal";
	$start = substr($start_t, 0, 10);
	$end = substr($end_t, 0, 10);

	$start_Y = substr($start, 0, 4);
	$start_M = substr($start, 5, 2)*1;
	$start_D = substr($start, 8, 2)*1;
	$end_Y = substr($end, 0, 4);
	$end_M = substr($end, 5, 2)*1;
	$end_D = substr($end, 8, 2)*1;
	
	$dup_check = false;

	if ($file_name == "")
		$file_name = $GLOBALS['SCHEDULE_FILE'];
	else
		$dup_check = true;

if (file_exists($file_name)) {
    $xml = simplexml_load_file($file_name, 'SimpleXMLElement', LIBXML_NOCDATA); 
	$xml_count = count($xml);

	// 날짜 $value->attributes()->date
	// 타이틀 $value->schedule->title
	// 시작날 $value->schedule->begin_date
	// 종료날 $value->schedule->end_date
	// 시작시 $value->schedule->begin_h
	// 시작분 $value->schedule->begin_m
	// 종료시 $value->schedule->end_h
	// 종료분 $value->schedule->end_m
	// 자동여부 $value->schedule->auto_yn
	// 장비명 $value->schedule->equipmentaction
	// 액션 $value->schedule->action
	// 옵션 $value->schedule->option
	// 반복 $value->schedule->repeat
	// 우선순위 $value->schedule->priority
	// 지역 $value->schedule->area
	// 종일여부 $value->schedule->allday
	// 색깔 $value->schedule->color
	// 생성시간 $value->schedule->created_at

	$list = array();
	$i = 0;

	foreach($xml->children() as $value) {
		$note_date = $value->attributes()->date;
		$title = $value->schedule->title->__toString();
		$begin_date = $value->schedule->begin_date;
		$end_date = $value->schedule->end_date;
		$begin_h = $value->schedule->begin_h;
		$begin_m = $value->schedule->begin_m;
		$end_h = $value->schedule->end_h;
		$end_m = $value->schedule->end_m;
		$auto_yn = $value->schedule->auto_yn->__toString();
		$equipment = $value->schedule->equipment->__toString();
		$action = $value->schedule->action->__toString();
		$option = $value->schedule->option->__toString();
		$repeat = $value->schedule->repeat->__toString();
		$priority = $value->schedule->priority->__toString();
		$area = $value->schedule->area->__toString();
		$allday = $value->schedule->allday;
		$color = $value->schedule->color->__toString();
		$repeat_cnt = $value->schedule->repeat_cnt->__toString();
		$action_type = $value->schedule->action_type->__toString();
		$contents = $value->schedule->contents->__toString();
		$created_at = $value->schedule->created_at->__toString();
		$enable_week = $value->schedule->enable_week->__toString();
		$repeat_everyday = $value->schedule->repeat_everyday->__toString();
		
		if ($repeat == "week") { // 현재 every 고정
			if ($enable_week == "first" || $enable_week == "last")
				continue;
		}
		
		$cal_date = date("Y-m-d\TH:i", mktime(intval($begin_h), intval($begin_m), 0, substr($begin_date,5,2), substr($begin_date,8,2), substr($begin_date,0,4)));
		
		$found = false;
		
		if (isset($value->schedule->excludes)) {
			$excludes = $value->schedule->excludes;
			//echo var_dump($excludes->children()->start_date);
			foreach ($excludes->children()->start_date as $value) {
				$comp_date = substr($value->__toString(), 0, 10);
				if ($comp_date == substr($cal_date, 0, 10)) {
					$found = true;
					break;
				}
			}
			if ($found == true) {
				continue;
			}
		}

		if ($repeat_everyday == "0" || $repeat_everyday == "") {
			$begin_time = $begin_h.":".$begin_m;
			$end_time = $end_h.":".$end_m;

			$begin_array = array($begin_date,$begin_time);
			$end_array = array($end_date,$end_time);

			$begin_result = implode("T",$begin_array);
			$end_result = implode("T",$end_array);
			
			$list[$i]['editable'] 	= true;
			$list[$i]['id']			= $created_at;
			$list[$i]['title']		= $title;
			$list[$i]['start']		= $begin_result;
			$list[$i]['end']		= $end_result;
			$list[$i]['start_base']	= $begin_result;
			$list[$i]['end_base']	= $end_result;
			$list[$i]['auto_yn']	= $auto_yn;
			$list[$i]['color']		= $color;
			$list[$i]['repeat']		= $repeat;
			$list[$i]['repeat_cnt'] = $repeat_cnt;
			$list[$i]['allDay'] 	= $allday == 0 ? false : true;
			$list[$i]['progress']	= $action;
			$list[$i]['equipment']	= $equipment;
			$list[$i]['option']		= $option;
			$list[$i]['priority']	= $priority;
			$list[$i]['area']		= $area;
			$list[$i]['action_type'] = $action_type;
			$list[$i]['contents'] 	= $contents;
			$list[$i]['month_num'] = "";//$month_num;
			$list[$i]['week_num'] = "";//$week_num;
			$list[$i]['repeat_everyday'] = $repeat_everyday;
			$list[$i]['src'] = "Day.NoRep";
			/*$list[$i]['dow'] = "[0,1,3,4,6]";
			$range = array();
			$range["start"] = "2019-02-03";
			$range["end"] = "2019-02-21";
			$list[$i]['ranges'] = $range;*/
			$i++;
		}

	} // foreach
	
	// 반복일정
	foreach($xml as $value) {

		$note_date = $value->attributes()->date;
		$title = $value->schedule->title->__toString();
		$begin_date = $value->schedule->begin_date;
		$end_date = $value->schedule->end_date;
		$begin_h = $value->schedule->begin_h;
		$begin_m = $value->schedule->begin_m;
		$end_h = $value->schedule->end_h;
		$end_m = $value->schedule->end_m;
		$auto_yn = $value->schedule->auto_yn;
		$equipment = $value->schedule->equipment->__toString();
		$action = $value->schedule->action->__toString();
		$option = $value->schedule->option->__toString();
		$repeat = $value->schedule->repeat->__toString();
		$priority = $value->schedule->priority->__toString();
		$area = $value->schedule->area->__toString();
		$allday = $value->schedule->allday;
		$repeat_cnt = $value->schedule->repeat_cnt->__toString();
		$color = $value->schedule->color->__toString();
		$action_type = $value->schedule->action_type->__toString();
		$contents = $value->schedule->contents->__toString();	
		$created_at = $value->schedule->created_at->__toString();
		$enable_week = $value->schedule->enable_week->__toString();
		$repeat_everyday = $value->schedule->repeat_everyday->__toString();

		if (isset($value->schedule->excludes))
			$excludes = $value->schedule->excludes;
		else
			$excludes = null;

		$begin_time = $begin_h.":".$begin_m;
		$end_time = $end_h.":".$end_m;

		$end_next_day = false;
		if ($begin_time > $end_time)
			$end_next_day = true;

		$begin_array = array($begin_date,$begin_time);
		$end_array = array($end_date,$end_time);


		$begin_result = implode("T",$begin_array);
		$end_result = implode("T",$end_array);
		if ($repeat_everyday == "1" && $end_next_day == true) {
			$tmp_date = new DateTime(implode("T",$end_array));
			$end_daily = $tmp_date->add(new DateInterval('P1D'))->format("Y-m-d H:m");
		} else {
			$end_daily = $end_result;
		}

		switch($repeat)
		{
			case "year":  			
			for($t = $start_Y; $t <= $end_Y; $t++)
			{
				$cal_date = $t.substr($begin_result, 4);

				if ($cal_date == ($begin_date."T".$begin_h.":".$begin_m) && $repeat_everyday == "0")
					continue;				

				if ($repeat_everyday == "1") {
					$begin_datetime = new DateTime($cal_date);
					$next_day_datetime = new DateTime($cal_date);
					$next_day_datetime->add(new DateInterval('P1D'));
					$end_datetime = new DateTime($t.substr($end_daily, 4));
					for (;$begin_datetime <= $end_datetime; $begin_datetime->add(new DateInterval('P1D')), $next_day_datetime->add(new DateInterval('P1D'))) {
						$today = $begin_datetime->format("Y-m-d");

						$begin_array = array($today,$begin_time);
						if ($end_next_day) {
							$end_array = array($next_day_datetime->format("Y-m-d"),$end_time);
						} else
							$end_array = array($today,$end_time);

						$begin_result_daily = implode("T",$begin_array);
						$end_result_daily = implode("T",$end_array);
						
						$found = false;
						if ($excludes) {
							foreach ($excludes->children()->start_date as $value) {
								$comp_date = $value->__toString();
								//echo "===" . $comp_date . ":" . $cal_date . "===<br />";
								if (substr($comp_date, 0, 10) == $today) {
									$found = true;
									break;
								}
							}
							if ($found == true)
								continue;
						}

							$list[$i]['editable'] 	= true;
						$list[$i]['id']			= $created_at;
						$list[$i]['title']		= $title;
						$list[$i]['start']		= $begin_result_daily;
						$list[$i]['end']		= $end_result_daily;
						$list[$i]['start_base']	= $begin_result;
						$list[$i]['end_base']	= $end_result;
						$list[$i]['auto_yn'] 	= $auto_yn;
						$list[$i]['color']		= $color;
						$list[$i]['repeat']		= $repeat;
						$list[$i]['allDay'] 	= $allday == 1 ? true : false;
						$list[$i]['progress']	= $action;
						$list[$i]['equipment']	= $equipment;
						$list[$i]['option']		= $option;
						$list[$i]['priority']	= $priority;
						$list[$i]['area']		= $area;
						$list[$i]['repeat_cnt'] = $repeat_cnt;
						$list[$i]['action_type'] = $action_type;
						$list[$i]['contents'] 	= $contents;	
						$list[$i]['enable_week'] = $enable_week;
						$list[$i]['repeat_everyday'] = $repeat_everyday;
						$list[$i]['src'] = "Year.Daily";
						$i++;
					}
				}
				else {
					$list[$i]['editable'] 	= true;
					$list[$i]['id']			= $created_at;
					$list[$i]['title']		= $title;
					$list[$i]['start']		= $t.substr($begin_result, 4);
					$list[$i]['end']		= $t.substr($end_result, 4);
					$list[$i]['start_base']	= $begin_result;
					$list[$i]['end_base']	= $end_result;
					$list[$i]['auto_yn'] 	= $auto_yn;
					$list[$i]['color']		= $color;
					$list[$i]['repeat']		= $repeat;
					$list[$i]['allDay'] 	= $allday == 1 ? true : false;
					$list[$i]['progress']	= $action;
					$list[$i]['equipment']	= $equipment;
					$list[$i]['option']		= $option;
					$list[$i]['priority']	= $priority;
					$list[$i]['area']		= $area;
					$list[$i]['repeat_cnt'] = $repeat_cnt;
					$list[$i]['action_type'] = $action_type;
					$list[$i]['contents'] 	= $contents;	
					$list[$i]['enable_week'] = $enable_week;
					$list[$i]['repeat_everyday'] = $repeat_everyday;
					$list[$i]['src'] = "Year.Rep";
					$i++;
				}
			};
			break;

			case "month":  			
			if ($dup_check == true)
				$max_month = 12;
			else
				$max_month = 3;
			for($t = 0; $t < $max_month; $t++)
			{
				$cal_date = date("Y-m", mktime(0,0,0, $start_M + $t, 1, $start_Y)).substr($begin_result, 7);
				
				if ($cal_date == ($begin_date."T".$begin_h.":".$begin_m) && $repeat_everyday == "0")
					continue;
				
				if ($repeat_everyday == "1") {
					$begin_datetime = new DateTime($cal_date);
					$next_day_datetime = new DateTime($cal_date);
					$next_day_datetime->add(new DateInterval('P1D'));
					$end_datetime = new DateTime(date("Y-m", mktime(0,0,0, $start_M + $t, 1, $start_Y)).substr($end_daily, 7));
					for (;$begin_datetime <= $end_datetime; $begin_datetime->add(new DateInterval('P1D')), $next_day_datetime->add(new DateInterval('P1D'))) {
						$today = $begin_datetime->format("Y-m-d");

						$begin_array = array($today,$begin_time);
						if ($end_next_day) {
							$end_array = array($next_day_datetime->format("Y-m-d"),$end_time);
						} else
							$end_array = array($today,$end_time);

						$begin_result_daily = implode("T",$begin_array);
						$end_result_daily = implode("T",$end_array);
						
						$found = false;
						if ($excludes) {
							foreach ($excludes->children()->start_date as $value) {
								$comp_date = $value->__toString();
								//echo "===" . $comp_date . ":" . $cal_date . "===<br />";
								if (substr($comp_date, 0, 10) == $today) {
									$found = true;
									break;
								}
							}
							if ($found == true)
								continue;
						}

						$list[$i]['editable'] 	= true;
						$list[$i]['id']			= $created_at;
						$list[$i]['title']		= $title;
						$list[$i]['start']		= $begin_result_daily;
						$list[$i]['end']		= $end_result_daily;
						$list[$i]['start_base']	= $begin_result;
						$list[$i]['end_base']	= $end_result;
						$list[$i]['auto_yn'] 	= $auto_yn;
						$list[$i]['color']		= $color;
						$list[$i]['repeat']		= $repeat;
						$list[$i]['allDay'] 	= $allday == 1 ? true : false;
						$list[$i]['progress']	= $action;
						$list[$i]['equipment']	= $equipment;
						$list[$i]['option']		= $option;
						$list[$i]['priority']	= $priority;
						$list[$i]['area']		= $area;
						$list[$i]['repeat_cnt'] = $repeat_cnt;
						$list[$i]['action_type'] = $action_type;
						$list[$i]['contents'] 	= $contents;
						$list[$i]['enable_week'] = $enable_week;
						$list[$i]['repeat_everyday'] = $repeat_everyday;
						$list[$i]['src'] = "Month.Daily";
						$i++;
					}
				}
				else {
					$found = false;
					if ($excludes) {
						foreach ($excludes->children()->start_date as $value) {
							$comp_date = substr($value->__toString(), 0, 10);
							//echo "===" . $comp_date . ":" . $cal_date . "===<br />";
							if ($comp_date == substr($cal_date, 0, 10)) {
								$found = true;
								break;
							}
						}
						if ($found == true)
							continue;
					}

					$list[$i]['editable'] 	= true;
					$list[$i]['id']			= $created_at;
					$list[$i]['title']		= $title;
					$list[$i]['start']		= date("Y-m", mktime(0,0,0, $start_M + $t, 1, $start_Y)).substr($begin_result, 7);
					$list[$i]['end']		= date("Y-m", mktime(0,0,0, $start_M + $t, 1, $start_Y)).substr($end_result, 7);
					$list[$i]['start_base']	= $begin_result;
					$list[$i]['end_base']	= $end_result;
					$list[$i]['auto_yn'] 	= $auto_yn;
					$list[$i]['color']		= $color;
					$list[$i]['repeat']		= $repeat;
					$list[$i]['allDay'] 	= $allday == 1 ? true : false;
					$list[$i]['progress']	= $action;
					$list[$i]['equipment']	= $equipment;
					$list[$i]['option']		= $option;
					$list[$i]['priority']	= $priority;
					$list[$i]['area']		= $area;
					$list[$i]['repeat_cnt'] = $repeat_cnt;
					$list[$i]['action_type'] = $action_type;
					$list[$i]['contents'] 	= $contents;
					$list[$i]['enable_week'] = $enable_week;
					$list[$i]['repeat_everyday'] = $repeat_everyday;
					$list[$i]['src'] = "Month.Rep";
					$i++;
				}
			};
			break;
			
			case "week":
		    $first = false;
			$last = false;
			if ($dup_check == true)
				$max_week = 55;
			else
				$max_week = 7;

			for($t = 0; $t < $max_week; $t++)
			{
				$chk_week = date("w", strtotime($begin_result));
				$chk_week1 = date("w", strtotime($end_result));
				if($chk_week > $chk_week1) $k=$t+1; else $k=$t;
				
				$cal_date = date("Y-m-d", mktime(0,0,0, $start_M, $start_D + $chk_week + $t*7, $start_Y)).substr($begin_result, 10);
				if ($cal_date == ($begin_date."T".$begin_h.":".$begin_m) && $repeat_everyday == "0")
					continue;
				
/*				
				if ($enable_week == "first"){
					$days = substr($cal_date, 8, 2)*1;
					if ($days >= 1 && $days < 8){
						if ($first == false){
							$first = true;
						}
					}
					else{
						continue;
					}					
				}	
				if ($enable_week == "last"){
					$last_day = date("t", strtotime($begin_result));
					
					$check_day = substr($cal_date, 8, 2)*1;
					//echo $last_day."===".$check_day;
					if ($check_day <= $last_day && $check_day > ($last_day-7)){
						if ($last == false)
							$last = true;
					}
					else {
						continue;
					}
				}			
*/				

				if ($repeat_everyday == "1") {
					$begin_datetime = new DateTime($cal_date);
					$next_day_datetime = new DateTime($cal_date);
					$next_day_datetime->add(new DateInterval('P1D'));
					if ($end_next_day) {
						$end_datetime = new DateTime(date("Y-m-d", mktime(0,0,0, $start_M, $start_D + $chk_week1 + $k*7 + 1, $start_Y)).substr($end_daily, 10));
					} else {
						$end_datetime = new DateTime(date("Y-m-d", mktime(0,0,0, $start_M, $start_D + $chk_week1 + $k*7, $start_Y)).substr($end_daily, 10));
					}
					
					for (;$begin_datetime <= $end_datetime; $begin_datetime->add(new DateInterval('P1D')), $next_day_datetime->add(new DateInterval('P1D'))) {
						
						$today = $begin_datetime->format("Y-m-d");

						$begin_array = array($today,$begin_time);
						if ($end_next_day) {
							$end_array = array($next_day_datetime->format("Y-m-d"),$end_time);
						} else
							$end_array = array($today,$end_time);

						$begin_result_daily = implode("T",$begin_array);
						$end_result_daily = implode("T",$end_array);
						
						$found = false;
						if ($excludes) {
							foreach ($excludes->children()->start_date as $value) {
								$comp_date = $value->__toString();
								//echo "===" . $comp_date . ":" . $cal_date . "===<br />";
								if (substr($comp_date, 0, 10) == $today) {
									$found = true;
									break;
								}
							}
							if ($found == true)
								continue;
						}

						$list[$i]['editable'] 	= true;
						$list[$i]['id']			= $created_at;
						$list[$i]['title']		= $title;
						$list[$i]['start']		= $begin_result_daily;
						$list[$i]['end']		= $end_result_daily;
						$list[$i]['start_base']	= $begin_result;
						$list[$i]['end_base']	= $end_result;
						$list[$i]['auto_yn'] 	= $auto_yn;
						$list[$i]['color']		= $color;
						$list[$i]['repeat']		= $repeat;
						$list[$i]['allDay'] 	= $allday == 1 ? true : false;
						$list[$i]['progress']	= $action;
						$list[$i]['equipment']	= $equipment;
						$list[$i]['option']		= $option;
						$list[$i]['priority']	= $priority;
						$list[$i]['area']		= $area;
						$list[$i]['repeat_cnt'] = $repeat_cnt;
						$list[$i]['action_type'] = $action_type;
						$list[$i]['contents'] 	= $contents;
						$list[$i]['enable_week'] = $enable_week;
						$list[$i]['repeat_everyday'] = $repeat_everyday;
						$list[$i]['src'] = "Week.Rep";
						$i++;
					}
				} else {
						$found = false;

						if ($excludes) {
							foreach ($excludes->children()->start_date as $value) {
								$comp_date = substr($value->__toString(), 0, 10);
								//echo "===" . $comp_date . ":" . $cal_date . "===<br />";
								if ($comp_date == substr($cal_date, 0, 10)) {
									$found = true;
									break;
								}
							}
							if ($found == true)
								continue;
						}
						$list[$i]['editable'] 	= true;
						$list[$i]['id']			= $created_at;
						$list[$i]['title']		= $title;
						$list[$i]['start']		= $cal_date;
						$list[$i]['end']		= date("Y-m-d", mktime(0,0,0, $start_M, $start_D + $chk_week1 + $k*7, $start_Y)).substr($end_result, 10);
						$list[$i]['start_base']	= $begin_result;
						$list[$i]['end_base']	= $end_result;
						$list[$i]['auto_yn'] 	= $auto_yn;
						$list[$i]['color']		= $color;
						$list[$i]['repeat']		= $repeat;
						$list[$i]['allDay'] 	= $allday == 1 ? true : false;
						$list[$i]['progress']	= $action;
						$list[$i]['equipment']	= $equipment;
						$list[$i]['option']		= $option;
						$list[$i]['priority']	= $priority;
						$list[$i]['area']		= $area;
						$list[$i]['repeat_cnt'] = $repeat_cnt;
						$list[$i]['action_type'] = $action_type;
						$list[$i]['contents'] 	= $contents;
						$list[$i]['enable_week'] = $enable_week;
						$list[$i]['repeat_everyday'] = $repeat_everyday;
						$list[$i]['src'] = "Week.Rep";
						$i++;
				}
			};
			break;

			default : // repeat : none, repeat_everyday : 1
				//echo $begin_date->__toString();
				if ($repeat_everyday == "1") {
					$begin_datetime = new DateTime($begin_date->__toString() . " 00:00");
					$next_day_datetime = new DateTime($begin_date->__toString() . " 00:00");
					$next_day_datetime->add(new DateInterval('P1D'));
					$end_datetime = new DateTime($end_date->__toString() . " 00:00");

					$begin_time = $begin_h.":".$begin_m;
					$end_time = $end_h.":".$end_m;

					for (;$begin_datetime <= $end_datetime; $begin_datetime->add(new DateInterval('P1D')), $next_day_datetime->add(new DateInterval('P1D'))) {
						
						$today = $begin_datetime->format("Y-m-d");

						$begin_array = array($today,$begin_time);

						if ($end_next_day) {
							$end_array = array($next_day_datetime->format("Y-m-d"),$end_time);
						} else
							$end_array = array($today,$end_time);

						$begin_result_daily = implode("T",$begin_array);
						$end_result_daily = implode("T",$end_array);
						
						$found = false;
						if ($excludes) {
							foreach ($excludes->children()->start_date as $value) {
								$comp_date = $value->__toString();
								//echo "===" . $comp_date . ":" . $cal_date . "===<br />";
								if (substr($comp_date, 0, 10) == $today) {
									$found = true;
									break;
								}
							}
							if ($found == true)
								continue;
						}
						$list[$i]['editable'] 	= true;
						$list[$i]['id']			= $created_at;
						$list[$i]['title']		= $title;
						$list[$i]['start']		= $begin_result_daily;
						$list[$i]['end']		= $end_result_daily;
						$list[$i]['start_base']	= $begin_result;
						$list[$i]['end_base']	= $end_result;
						$list[$i]['auto_yn']	= $auto_yn;
						$list[$i]['color']		= $color;
						$list[$i]['repeat']		= $repeat;
						$list[$i]['repeat_cnt'] = $repeat_cnt;
						$list[$i]['allDay'] 	= $allday == 0 ? false : true;
						$list[$i]['progress']	= $action;
						$list[$i]['equipment']	= $equipment;
						$list[$i]['option']		= $option;
						$list[$i]['priority']	= $priority;
						$list[$i]['area']		= $area;
						$list[$i]['action_type'] = $action_type;
						$list[$i]['contents'] 	= $contents;
						$list[$i]['month_num'] = "";//$month_num;
						$list[$i]['week_num'] = "";//$week_num;
						$list[$i]['repeat_everyday'] = $repeat_everyday;
						$list[$i]['src'] = "Day.Every";
						$i++;
					}
				}
			break;
		} // switch

	} // foreach

  } else {// if
	  echo "File Not Exist";
	  exit(-1);
  }

	$encodedJson = json_encode2($list);

	return $encodedJson;
}

function scheduleCopy()
{
	$created_at = $_POST['event_id'];
	$title = $_POST['title'];
	$datelist = json_decode_local(html_entity_decode($_POST['datelist']), true);

	$xml1 = simplexml_load_file($GLOBALS['SCHEDULE_FILE']);
	
	foreach($xml1 as $schedule) {
		$schedule_created_at = $schedule->schedule->created_at;
		if ($schedule_created_at == $created_at) {
			$created_at = getID();
			$_id = (int)substr($created_at,3,3);
			 foreach ($datelist as $date) {
			 	$result_next = $xml1->addChild('note');
			 	$result_next->addAttribute('date',substr($date, 0, 10));
			 	$result_schedule = $result_next->addChild('schedule', $schedule->schedule);
			 	$result_schedule->title = $title;
			 	$prev_begin_date = new DateTime($schedule->schedule->begin_date->__toString());
			 	$prev_end_date = new DateTime($schedule->schedule->end_date->__toString());
				$date_diff = $prev_end_date->diff($prev_begin_date);
			 	$new_end_date = new DateTime($date);
			 	$result_schedule->begin_date = substr($date, 0, 10);
			 	$result_schedule->end_date = $new_end_date->add(new DateInterval('P' . $date_diff->days . 'D'))->format('Y-m-d');
				$result_schedule->addChild('begin_h',$schedule->schedule->begin_h->__toString());
				$result_schedule->addChild('begin_m',$schedule->schedule->begin_m->__toString());
				$result_schedule->addChild('end_h',$schedule->schedule->end_h->__toString());
				$result_schedule->addChild('end_m',$schedule->schedule->end_m->__toString());
				$result_schedule->addChild('auto_yn',$schedule->schedule->auto_yn->__toString());
				$result_schedule->addChild('equipment',$schedule->schedule->equipment->__toString());
				$result_schedule->addChild('allday',$schedule->schedule->allday->__toString());
				$result_schedule->addChild('repeat_everyday',$schedule->schedule->repeat_everyday->__toString());
				$result_schedule->addChild('action',$schedule->schedule->action->__toString());
				$result_schedule->addChild('option',$schedule->schedule->option->__toString());
				$result_schedule->addChild('color',$schedule->schedule->color->__toString());
				$result_schedule->addChild('repeat',$schedule->schedule->repeat->__toString());
				$result_schedule->addChild('enable_week',$schedule->schedule->enable_week->__toString());
				$result_schedule->addChild('repeat_cnt', $schedule->schedule->repeat_cnt->__toString());
				$result_schedule->addChild('priority',$schedule->schedule->priority->__toString());
				$result_schedule->addChild('area',$schedule->schedule->area->__toString());
				$result_schedule->addChild('action_type',$schedule->schedule->action_type->__toString());
				$result_schedule->addChild('contents',$schedule->schedule->contents->__toString());
				$format = "id_%03d";
				$new_id = sprintf($format, $_id); 
			 	$result_schedule->created_at = $new_id;
				$_id = $_id + 1;
			}
			break;
		}
	}

	file_put_contents($GLOBALS['SCHEDULE_FILE'],$xml1->saveXML());

    $xmlDoc = new DOMDocument();
    $xmlDoc->preserveWhiteSpace = false;
    $xmlDoc->load($GLOBALS['SCHEDULE_FILE']);
        
    $xmlDoc->formatOutput = true;
    $xmlDoc->save($GLOBALS['SCHEDULE_FILE']);
    unset($xmlDoc);	
	
	convert2crontab();
	
	$encodedJson = json_encode2(array('success'=>true));

	echo $encodedJson;exit;
}

function updateEvent() 
{
	global $TEMP_SCHEDULE_FILE;
	if (file_exists($TEMP_SCHEDULE_FILE)) {
		$xml1 = simplexml_load_file($TEMP_SCHEDULE_FILE);
		$i = 0;
		foreach($xml1->children() as $note) {
			$schedule_created_at = $note->schedule->created_at->__toString();
			unset($xml1->note[$i]); break;
			$i++;
		}
		file_put_contents($TEMP_SCHEDULE_FILE,$xml1->saveXML());
	}

	addNewEvent($TEMP_SCHEDULE_FILE);

	$created_at = $_POST['event_id']; 

	if (getDuplicated($created_at) == true) {
		exit(-1);
	}

	$title = strip_tags($_POST['title']);
	$title = substr($title,0,255);
   	$title = preg_replace("#[\\\]+$#", "", $title);

	$begin_date = substr($_POST['date_start'], 0, 10);
	$end_date = substr($_POST['date_end'], 0, 10);
	$color = $_POST['color'];
	$begin_time = substr($_POST['date_start'], 11, 5);
	$end_time = substr($_POST['date_end'], 11, 5);
	$auto_yn = $_POST['auto_playback'];
//	$auto_yn = (isset($_POST['auto_playback'])) ? '1' : '0';
	$equipment = $_POST['equipment'];
	$option = $_POST['option'];
	$priority = $_POST['priority'];
	$area = $_POST['area'];
	$repeat = $_POST['repeat'];  
	$repeat_cnt = $_POST['repeat_cnt'];
	$enable_week = $_POST['enable_week'];

	$allday = (isset($_POST['allDay']) && $_POST['allDay']) ? '1' : '0';
	$action = $_POST['progress'];
	$action_type = $_POST['action_type'];
	$contents = $_POST['contents'];

	$repeat_everyday = (isset($_POST['repeat_everyday']) && $_POST['repeat_everyday']) ? '1' : '0';

	$begin = explode(':',$begin_time);
	$end = explode(':',$end_time);
	
	$xml1 = simplexml_load_file($GLOBALS['SCHEDULE_FILE']);
	
	foreach($xml1 as $schedule) {

		$schedule_created_at = $schedule->schedule->created_at;

		if ($schedule_created_at == $created_at) {
			$schedule->schedule->title = $title;
			$schedule->schedule->color = $color;
			$prev_begin_date = $schedule->schedule->begin_date->__toString();
			$prev_begin_dow = date("w",mktime(0,0,0, substr($prev_begin_date, 5,2), substr($prev_begin_date, 8,2), substr($prev_begin_date, 0,4)));
			$new_begin_dow = date("w",mktime(0,0,0, substr($begin_date, 5,2), substr($begin_date, 8,2), substr($begin_date, 0,4)));
			$schedule->schedule->begin_date = $begin_date;
			$prev_end_date = $schedule->schedule->end_date;
			$schedule->schedule->end_date = $end_date;
			$prev_begin_h = $schedule->schedule->begin_h->__toString();
			$schedule->schedule->begin_h = $begin[0];
			$prev_begin_m = $schedule->schedule->begin_m->__toString();
			$schedule->schedule->begin_m = $begin[1];
			$schedule->schedule->end_h = $end[0];
			$schedule->schedule->end_m = $end[1];
			$schedule->schedule->auto_yn = $auto_yn;
			$schedule->schedule->equipment = $equipment;
			$schedule->schedule->allday = $allday;
			$schedule->schedule->action = $action;
			$schedule->schedule->option = $option;
			$schedule->schedule->color = $color;
			$prev_repeat = $schedule->schedule->repeat->__toString();
			$schedule->schedule->repeat = $repeat;
			$schedule->schedule->enable_week = $enable_week;
			$schedule->schedule->repeat_cnt = $repeat_cnt;
			$schedule->schedule->priority = $priority;
			$schedule->schedule->area = $area;
			$schedule->schedule->action_type = $action_type;
			$schedule->schedule->contents = $contents;
			$schedule->schedule->repeat_everyday = $repeat_everyday;
			
			$start_date_diff = 0;
			if ($prev_begin_dow != $new_begin_dow) {
				$start_date_diff = $new_begin_dow - $prev_begin_dow;
			}
			
			// Delete all excludes. decided with Allen
			$scheduleDom=dom_import_simplexml($schedule->schedule);
			
			//var_dump($prev_repeat);
			//var_dump($repeat);
			if ($prev_begin_date != $begin_date || $prev_end_date != $end_date || $prev_repeat != $repeat) {
				$domNodeList = $scheduleDom->getElementsByTagname('excludes'); 
				$domElemsToRemove = array(); 
				//var_dump($domNodeList);
				foreach ( $domNodeList as $domElement ) { 
					$domElemsToRemove[] = $domElement; 
				} 
				foreach( $domElemsToRemove as $domElement ){ 
					$domElement->parentNode->removeChild($domElement); 
				}
			}
			/*
			foreach ($schedule->schedule->excludes as $value) {
				$date = new DateTime($value->exclude->start_date->__toString());
				if ($start_date_diff >= 0)
					$date->add(new DateInterval('P' . $start_date_diff . 'D'));
				else
					$date->sub(new DateInterval('P' . abs($start_date_diff) . 'D'));
				$value->exclude->start_date = $date->format('Y-m-d H:i');
			}
			*/
			break;
		}
	}

	file_put_contents($GLOBALS['SCHEDULE_FILE'],$xml1->saveXML());

    $xmlDoc = new DOMDocument();
    $xmlDoc->preserveWhiteSpace = false;
    $xmlDoc->load($GLOBALS['SCHEDULE_FILE']);
        
    $xmlDoc->formatOutput = true;
    $xmlDoc->save($GLOBALS['SCHEDULE_FILE']);
    unset($xmlDoc);	
	
	convert2crontab();
	
	$encodedJson = json_encode2(array('success'=>true));

	echo $encodedJson;exit;
}

function resizeEvent() {
	$created_at = $_POST['event_id'];
	$title = strip_tags($_POST['title']);
	$title = substr($title,0,255);
   	$title = preg_replace("#[\\\]+$#", "", $title);

	$begin_date = substr($_POST['date_start'], 0, 10);
	$end_date = substr($_POST['date_end'], 0, 10);
	$color = $_POST['color'];
	$begin_time = substr($_POST['date_start'], 11, 5);
	$end_time = substr($_POST['date_end'], 11, 5);
	$auto_yn = $_POST['auto_playback'];
	$equipment = $_POST['equipment'];
	$option = $_POST['option'];
	$priority = $_POST['priority'];
	$area = $_POST['area'];
	$repeat = $_POST['repeat'];  
	$enable_week = $_POST['enable_week'];
	$repeat_cnt = $_POST['repeat_cnt'];
	$allday = (isset($_POST['allDay']) && $_POST['allDay']) ? '1' : '0';
	$action = $_POST['progress'];
	$action_type = $_POST['action_type'];
	$contents = $_POST['contents'];

	$begin = explode(':',$begin_time);
	$end = explode(':',$end_time);
	
	$xml5 = simplexml_load_file($GLOBALS['SCHEDULE_FILE']);
	
	foreach($xml5->children() as $schedule) {

		$schedule_created_at = $schedule->schedule->created_at;

		if ($schedule_created_at == $created_at) {
			$schedule->attributes()->date = $begin_date;
			$schedule->schedule->title = $title;
			
			$cur_begin_dow = date("w",mktime(0,0,0, substr($begin_date, 5,2), substr($begin_date, 8,2), substr($begin_date, 0,4)));
			$prev_begin_date = $schedule->schedule->begin_date->__toString();
			$prev_begin_dow = date("w",mktime(0,0,0, substr($prev_begin_date, 5,2), substr($prev_begin_date, 8,2), substr($prev_begin_date, 0,4)));
			
			$schedule->schedule->begin_date = $begin_date;
			$schedule->schedule->end_date = $end_date;
			$schedule->schedule->begin_h = $begin[0];
			$schedule->schedule->begin_m = $begin[1];
			$schedule->schedule->end_h = $end[0];
			$schedule->schedule->end_m = $end[1];
			$schedule->schedule->auto_yn = $auto_yn;
			$schedule->schedule->equipment = $equipment;
			$schedule->schedule->allday = $allday;
			$schedule->schedule->action = $action;
			$schedule->schedule->option = $option;
			$schedule->schedule->color = $color;
			$schedule->schedule->repeat = $repeat;
			$schedule->schedule->enable_week = $enable_week;
			$schedule->schedule->repeat_cnt = $repeat_cnt;
			$schedule->schedule->priority = $priority;
			$schedule->schedule->area = $area;
			$schedule->schedule->action_type = $action_type;
			$schedule->schedule->contents = $contents;

			// Delete all excludes. decided with Allen
			$scheduleDom=dom_import_simplexml($schedule->schedule);
			
			$domNodeList = $scheduleDom->getElementsByTagname('excludes'); 
			$domElemsToRemove = array(); 
			foreach ( $domNodeList as $domElement ) { 
			  $domElemsToRemove[] = $domElement; 
			} 
			foreach( $domElemsToRemove as $domElement ){ 
			  $domElement->parentNode->removeChild($domElement); 
			}
			/*
			$start_date_diff = 0;
			if ($prev_begin_dow != $cur_begin_dow) {
				$start_date_diff = $cur_begin_dow - $prev_begin_dow;
			}
			$excludes = $schedule->schedule->excludes;
			foreach ($excludes as $value) {
				$date = new DateTime($value->start_date->__toString());
				if ($start_date_diff >= 0)
					$date->add(new DateInterval('P' . $start_date_diff . 'D'));
				else
					$date->sub(new DateInterval('P' . abs($start_date_diff) . 'D'));
				$value->exclude->start_date = $date->format('Y-m-d H:i');
			}
			*/
			break;
		}
	}

	file_put_contents($GLOBALS['SCHEDULE_FILE'],$xml5->saveXML());

    $xmlDoc = new DOMDocument();
    $xmlDoc->preserveWhiteSpace = false;
    $xmlDoc->load($GLOBALS['SCHEDULE_FILE']);
        
    $xmlDoc->formatOutput = true;
    $xmlDoc->save($GLOBALS['SCHEDULE_FILE']);
    unset($xmlDoc);	
	
	convert2crontab();

	$encodedJson = json_encode2(array('success'=>true));

	echo $encodedJson;exit;
}

function getDuplicated($event_id)
{
	global $TEMP_SCHEDULE_FILE;
	$start_date = new DateTime();
	$start_begin_dow = date("w",time());
	$start_date->sub(new DateInterval('P'.$start_begin_dow.'D'));
	$end_date = new DateTime();
	$end_date->add(new DateInterval('P365D'));
	$new_event_list = json_decode_local(getEvent_list($start_date->format('Y-m-d'), $end_date->format('Y-m-d'), $TEMP_SCHEDULE_FILE), true);
	$event_list = json_decode_local(getEvent_list($start_date->format('Y-m-d'), $end_date->format('Y-m-d'), $GLOBALS['SCHEDULE_FILE']), true);

	//echo $start_date->format('Y-m-d H:i');
	//echo $end_date->format('Y-m-d H:i');
	//echo var_dump(getEvent_list($start_date->format('Y-m-d'), $end_date->format('Y-m-d'), ""));
	//echo var_dump($new_event_list);

	foreach($event_list as $event) {
		if ($event["id"] == $event_id)
			continue;
			
		$title = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
			return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
			}, $event["title"]);

		foreach($new_event_list as $new_event) {
			if (($event["start"] <= $new_event["start"] && $new_event["start"] < $event["end"])
			|| ($event["start"] < $new_event["end"] && $new_event["end"] < $event["end"])) {
				echo html_entity_decode("Event " . $title . " : [" . $event["start"] . " ~ " . $event["end"] . "] and \rNew Event [" . $new_event["start"] . " ~ " . $new_event["end"] . "] is duplicated.");
				return true;
			}
			if (($new_event["start"] <= $event["start"] && $event["start"] < $new_event["end"])
			|| ($new_event["start"] < $event["end"] && $event["end"] < $new_event["end"])) {
				echo html_entity_decode("Event " . $title . " : [" . $event["start"] . " ~ " . $event["end"] . "] and \rNew Event [" . $new_event["start"] . " ~ " . $new_event["end"] . "] is duplicated.");
				return true;
			}
		}
	}
	return false;
}

function addEvent() {
	global $TEMP_SCHEDULE_FILE;
	if (file_exists($TEMP_SCHEDULE_FILE)) {
		$xml1 = simplexml_load_file($TEMP_SCHEDULE_FILE);
		$i = 0;
		foreach($xml1->children() as $note) {
			$schedule_created_at = $note->schedule->created_at->__toString();
			unset($xml1->note[$i]); break;
			$i++;
		}
		file_put_contents($TEMP_SCHEDULE_FILE,$xml1->saveXML());
	} else {
		file_put_contents($TEMP_SCHEDULE_FILE,'<?xml version="1.0" encoding="utf-8"?><interm_schedule></interm_schedule>');
	}

	addNewEvent($TEMP_SCHEDULE_FILE);

	if (getDuplicated(-1) == true) {
		exit(-1);
	}

	if (file_exists($TEMP_SCHEDULE_FILE) == false) {
		file_put_contents($GLOBALS['SCHEDULE_FILE'],'<?xml version="1.0" encoding="utf-8"?><interm_schedule></interm_schedule>');
	}
	addNewEvent($GLOBALS['SCHEDULE_FILE']);
	convert2crontab();
	
	$encodedJson = json_encode2(array('success'=>true));

	echo $encodedJson;exit;
}

function addNewEvent($file_name) 
{
	$title = strip_tags($_POST['title']);
	$title = substr($title,0,255);
	$title = preg_replace("#[\\\]+$#", "", $title);

	$begin_date = substr($_POST['date_start'], 0, 10);
	$end_date = substr($_POST['date_end'], 0, 10);
	$color = $_POST['color'];
	$begin_time = substr($_POST['date_start'], 11, 5);
	$end_time = substr($_POST['date_end'], 11, 5);
	$auto_yn = 	$_POST['auto_playback'];
	$repeat = $_POST['repeat'];
	$enable_week = $_POST['enable_week'];
	$repeat_cnt = $_POST['repeat_cnt'];
	$allday = (isset($_POST['allDay']) && $_POST['allDay']) ? '1' : '0';
	$repeat_everyday = (isset($_POST['repeat_everyday']) && $_POST['repeat_everyday']) ? '1' : '0';
	$action = $_POST['progress'];
	$equipment = $_POST['equipment'];
	$option = $_POST['option'];
	$priority = $_POST['priority'];
	$area = $_POST['area'];
	$actionType = $_POST['action_type'];
	$contents = $_POST['contents'];
//	$created_at = date("Y-m-d H:i:s");
	$created_at = getID();
	
	$xml2 = simplexml_load_file($file_name);

	$begin = explode(':',$begin_time);
	$end = explode(':',$end_time);

	$result_next = $xml2->addChild('note');
	$result_next->addAttribute('date',$begin_date);
	$result_schedule = $result_next->addChild('schedule');
	$result_schedule->addChild('title',$title);
	$result_schedule->addChild('begin_date',$begin_date);
	$result_schedule->addChild('end_date',$end_date);
	$result_schedule->addChild('begin_h',$begin[0]);
	$result_schedule->addChild('begin_m',$begin[1]);
	$result_schedule->addChild('end_h',$end[0]);
	$result_schedule->addChild('end_m',$end[1]);
	$result_schedule->addChild('auto_yn',$auto_yn);
	$result_schedule->addChild('equipment',$equipment);
	$result_schedule->addChild('allday',$allday);
	$result_schedule->addChild('repeat_everyday',$repeat_everyday);
	$result_schedule->addChild('action',$action);
	$result_schedule->addChild('option',$option);
	$result_schedule->addChild('color',$color);
	$result_schedule->addChild('repeat',$repeat);
	$result_schedule->addChild('enable_week',$enable_week);
	$result_schedule->addChild('repeat_cnt', $repeat_cnt);
	$result_schedule->addChild('priority',$priority);
	$result_schedule->addChild('area',$area);
	$result_schedule->addChild('action_type',$actionType);
	$result_schedule->addChild('contents',$contents);
	$result_schedule->addChild('created_at',$created_at);
		
	//and save file
	$xml2->asXml($file_name);
	
    $xmlDoc = new DOMDocument();
    $xmlDoc->preserveWhiteSpace = false;
    $xmlDoc->load($file_name);
        
    $xmlDoc->formatOutput = true;
    $xmlDoc->save($file_name);
    unset($xmlDoc);	
	
}

function delAnEvent() 
{
	$created_at = $_POST['event_id']; 

	$start_date = $_POST['start_date'];
	//$end_date = $_POST['end_date'];
	
	$xml1 = simplexml_load_file($GLOBALS['SCHEDULE_FILE']);
	$date_diff = 0;
	foreach($xml1 as $schedule) {

		$schedule_created_at = $schedule->schedule->created_at;

		if ($schedule_created_at == $created_at) {
			$prev_begin_date = new DateTime($schedule->schedule->begin_date->__toString());
			$prev_end_date = new DateTime($schedule->schedule->end_date->__toString());
			$date_diff = $prev_end_date->diff($prev_begin_date)->days;
			
			//echo var_dump($schedule->schedule);
			if (isset($schedule->schedule->excludes)) {
				$excludes = $schedule->schedule->excludes;
			} else {
				$excludes = $schedule->schedule->addChild('excludes');
			}
			$excludes->addChild('start_date', substr($start_date, 0, 10));
			break;
		}
	}

	$i = 0;
	foreach($xml1 as $schedule) {
		$schedule_created_at = $schedule->schedule->created_at;
		if ($schedule_created_at == $created_at) {
			//echo $date_diff . "---" . $schedule->schedule->excludes->count();
			if ($date_diff < $schedule->schedule->excludes->children()->start_date->count() && $schedule->schedule->repeat->__toString() == "") {
				unset($xml1->note[$i]);
			}
			break;
		}
		$i++;
	}

	//$xml1->asXml($GLOBALS['SCHEDULE_FILE']);
	file_put_contents($GLOBALS['SCHEDULE_FILE'],$xml1->saveXML());

    $xmlDoc = new DOMDocument();
    $xmlDoc->preserveWhiteSpace = false;
    $xmlDoc->load($GLOBALS['SCHEDULE_FILE']);
        
    $xmlDoc->formatOutput = true;
    $xmlDoc->save($GLOBALS['SCHEDULE_FILE']);
    unset($xmlDoc);	
	
	convert2crontab();
	
	$encodedJson = json_encode2(array('success'=>true));

	echo $encodedJson;exit;
}

function delEvent() 
{
	$created_at = $_POST['event_id'];

	$xml3 = simplexml_load_file($GLOBALS['SCHEDULE_FILE']);
	$i = 0;
	foreach($xml3->children() as $note) {
		$schedule_created_at = $note->schedule->created_at->__toString();
		if ($schedule_created_at == $created_at) {
			unset($xml3->note[$i]); break;
		}
		$i++;
	}

	file_put_contents($GLOBALS['SCHEDULE_FILE'],$xml3->saveXML());

    $xmlDoc = new DOMDocument();
    $xmlDoc->preserveWhiteSpace = false;
    $xmlDoc->load($GLOBALS['SCHEDULE_FILE']);
        
    $xmlDoc->formatOutput = true;
    $xmlDoc->save($GLOBALS['SCHEDULE_FILE']);
    unset($xmlDoc);	
	
	convert2crontab();
	
	$encodedJson = json_encode2(array('success'=>true));

	echo $encodedJson;
	exit;
}

function getDuring()
{
	$file_name = $_POST['file_name'];
	$splitNames = explode(',',$file_name);
	$cnt = count($splitNames);
	if ($cnt > 1){	// 2 over file name
		$result_time = 0;	// total sec
		for($i = 0; $i < $cnt; $i++){
			$len = strlen($splitNames[$i]);
			$pure_name = substr($splitNames[$i], 0, $len-3);
			$dur_name = $pure_name."dur";
			$re_file_name = '/opt/interm/www/html/mp3/'.$dur_name;
			
			if (file_exists($re_file_name)){
				$handele = fopen($re_file_name, "r");
				$buffer = fread($handele, filesize($re_file_name));
				$spliteTimes = explode(':',$buffer);
				$result_time = $result_time + (int)($spliteTimes[0]*3600) + (int)($spliteTimes[1]*60) + (int)($spliteTimes[2]);
				fclose($handele);
			}
		}
		$result_string = sprintf("%02d:%02d:%02d", (int)$result_time/3600, (int)(($result_time%3600)/60), (int)(($result_time%60)));
		echo $result_string;
	}
	else{
		$len = strlen($file_name);
		$pure_name = substr($file_name, 0, $len-3);
		$dur_name = $pure_name."dur";
		$re_file_name = '/opt/interm/www/html/mp3/'.$dur_name;
		
		if (file_exists($re_file_name)){
			$handele = fopen($re_file_name, "r");
			$buffer = fread($handele, filesize($re_file_name));
			fclose($handele);
		}
		else{
			echo "no file.";
			return;
		}
		echo $buffer;
	}

	exit;
}

// Holiday data
function getHolidays() 
{
	$m = $_GET['m'];
	$year = "";
	$start_Y = substr($_GET['start'], 0 ,4);
	$end_Y = substr($_GET['end'], 0 ,4);
	
	if ($start_Y < $end_Y) { 
		$year = $end_Y;
	} else {
		$year = $start_Y;
	}

	$xml4 = simplexml_load_file('/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_holiday.xml');
	$i = 0;
	$list = array();
	foreach($xml4->children() as $holiday) {
		$title = $holiday->title->__toString();
		$begin_date = $holiday->begin_date->__toString();
		$end_date = $holiday->end_date->__toString();
		$begin_h = $holiday->begin_h->__toString();
		$begin_m = $holiday->begin_m->__toString();
		$end_h = $holiday->end_h->__toString();
		$end_m = $holiday->end_m->__toString();
		$created_at = $holiday->created_at->__toString();
		
		$holiday_isvalid = $holiday->isvalid;

		if ($holiday_isvalid == 1) {
// YEAR Calibration			
			$sYear = (int)$year;
			$eYear = (int)$year;
			
			if (((int)substr($begin_date,0,2)) > ((int)substr($end_date,0,2))){
				$sYear = $sYear-1;
				$eYear = $sYear+1;
			}
 
			$start_date = strval($sYear)."-".$begin_date;
			$end_date = strval($eYear)."-".$end_date;
			$list[] = array(
						"id" 	=>  $created_at,
						"start" =>	$start_date,
						"end"	=>	$end_date,
						"title" =>	$title,
			);
		}
		$i++;
	}
	$encodedJson = json_encode2($list);
	echo $encodedJson;
}

// XML Upload
function XMLUpload() {

	// uploads디렉토리에 파일을 업로드합니다. 
	 $uploaddir = 'uploaded_xml/'; 
	 $upload_filename = "XML-".basename($_FILES['userfile']['name'])."_".date("Y-m-d")."-".date("His").".xml";
	 $uploadfile = $uploaddir.$upload_filename;

	 if($_POST['MAX_FILE_SIZE'] < $_FILES['userfile']['size']){ 
		  echo "File size is bigger than Max file size yon can.\n"; 
	 } else { 
		 if(($_FILES['userfile']['error'] > 0) || ($_FILES['userfile']['size'] <= 0)){ 
			  echo "Something wrong. You failed to file upload."; 
		 } else { 
			  // HTTP post로 전송된 것인지 체크합니다. 
			  if(!is_uploaded_file($_FILES['userfile']['tmp_name'])) { 
					echo "It's not file to send with HTTP."; 
			  } else { 
					// move_uploaded_file은 임시 저장되어 있는 파일을 ./uploads 디렉토리로 이동합니다. 
					if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) { 
						 echo "<script>alert('File Upload Complete.');</script>"; 
					} else { 
						 echo "<script>alert('File Upload Failed.');</script>"; 
					} 
			  }
		 } 
	 } 

	if (file_exists('uploaded_xml/'.$upload_filename)) { 
		$XMLUpload = simplexml_load_file('uploaded_xml/'.$upload_filename, 'SimpleXMLElement', LIBXML_NOCDATA); 
		$xml_count = count($XMLUpload);
		
		// 날짜 $value->attributes()->date
		// 타이틀 $value->schedule->title
		// 시작날 $value->schedule->begin_date
		// 종료날 $value->schedule->end_date
		// 시작시 $value->schedule->begin_h
		// 시작분 $value->schedule->begin_m
		// 종료시 $value->schedule->end_h
		// 종료분 $value->schedule->end_m
		// 자동여부 $value->schedule->auto_yn
		// 장비명 $value->schedule->equipmentaction
		// 종일 $value->schedule->allday
		// 액션 $value->schedule->action
		// 옵션 $value->schedule->option
		// 반복 $value->schedule->repeat
		// 우선순위 $value->schedule->priority
		// 지역 $value->schedule->area
		// 종일여부 $value->schedule->allday
		// 색깔 $value->schedule->color
		// 생성시간 $value->schedule->created_at
		file_put_contents($GLOBALS['SCHEDULE_FILE'],$XMLUpload->saveXML());
/*		
		$i = 0;
		foreach($XMLUpload->children() as $value) {
			$note_date = $value[$i]->attributes()->date;
			$title = $value[$i]->schedule->title->__toString();
			$begin_date = $value[$i]->schedule->begin_date;
			$end_date = $value[$i]->schedule->end_date;
			$begin_h = $value[$i]->schedule->begin_h;
			$begin_m = $value[$i]->schedule->begin_m;
			$end_h = $value[$i]->schedule->end_h;
			$end_m = $value[$i]->schedule->end_m;
			$auto_yn = $value[$i]->schedule->auto_yn;
			$equipment = $value[$i]->schedule->equipment->__toString();
			$action = $value[$i]->schedule->action->__toString();
			$option = $value[$i]->schedule->option->__toString();
			$repeat = $value[$i]->schedule->repeat->__toString();
			$priority = $value[$i]->schedule->priority->__toString();
			$area = $value[$i]->schedule->area->__toString();
			$allday = $value[$i]->schedule->allday;
			$color = $value[$i]->schedule->color->__toString();
			$created_at = $value[$i]->schedule->created_at->__toString();

			$xmlLoad = simplexml_load_file('info_schedule.xml');

			$result_next = $xmlLoad->addChild('note');
			$result_next->addAttribute('date',$note_date);
			$result_schedule = $result_next->addChild('schedule');
			$result_schedule->addChild('title',$title);
			$result_schedule->addChild('begin_date',$begin_date);
			$result_schedule->addChild('end_date',$end_date);
			$result_schedule->addChild('begin_h',$begin_h);
			$result_schedule->addChild('begin_m',$begin_m);
			$result_schedule->addChild('end_h',$end_h);
			$result_schedule->addChild('end_m',$end_m);
			$result_schedule->addChild('auto_yn',$auto_yn);
			$result_schedule->addChild('equipment',$equipment);
			$result_schedule->addChild('allday',$allday);
			$result_schedule->addChild('action',$action);
			$result_schedule->addChild('option',$option);
			$result_schedule->addChild('color',$color);
			$result_schedule->addChild('repeat',$repeat);
			$result_schedule->addChild('priority',$priority);
			$result_schedule->addChild('area',$area);
			$result_schedule->addChild('created_at',$created_at);

			//and save file
			$xmlLoad->asXml('info_schedule.xml');

			$i++;
		}
*/
   		$xmlDoc = new DOMDocument();
    	$xmlDoc->preserveWhiteSpace = false;
    	$xmlDoc->load($GLOBALS['SCHEDULE_FILE']);
        
    	$xmlDoc->formatOutput = true;
    	$xmlDoc->save($GLOBALS['SCHEDULE_FILE']);
    	unset($xmlDoc);
		
		convert2crontab();	
		echo "<script>alert('XML into Database Successfully !! ');history.back();</script>";
		
	} else { 
		exit('No XML Files.'); 
	} 
}

// Copy Schedule Monthly
function copySchedule() {

	$cm1_year = $_GET['cm1_year'];
	$cm1_month = $_GET['cm1_month'];
	$cm2_year = $_GET['cm2_year'];
	$cm2_month = $_GET['cm2_month'];

	if ($cm1_month < 10) {
		$cm1_month = "0".$cm1_month;
	}

	if ($cm2_month < 10) {
		$cm2_month = "0".$cm2_month;
	}

	$cm1 = $cm1_year."-".$cm1_month;
	$cm2 = $cm2_year."-".$cm2_month;

	if ($cm1_year != "" && $cm1_month != "" && $cm2_year != "" && $cm2_month != "") {

		$xml_cm = simplexml_load_file($GLOBALS['SCHEDULE_FILE']);
		
		$i = 0;
		foreach($xml_cm->children() as $schedule) {

			$schedule_date = $schedule->attributes()->date;
			$begin_date = $schedule->schedule->begin_date;
			$end_date = $schedule->schedule->end_date;
			
			$schedule_cm = substr($schedule_date, 0, 7);
			$schedule_dd = substr($schedule_date, 8, 2);
			$begin_dd = substr($begin_date, 8, 2);
			$end_dd = substr($end_date, 8, 2);
			
			if ($schedule_cm == $cm1) {
				$note_date = $cm2."-".$schedule_dd;
				$upbegin_date = $cm2."-".$begin_dd;
				$upend_date = $cm2."-".$end_dd;
				
				if ($schedule->schedule->repeat->__toString() != "month" && $schedule->schedule->repeat->__toString() != "week") { // repeat가 month이거나 week가 아닐 때만. (month 일 경우 복사하면 이중 생성)
					$title = $schedule->schedule->title->__toString();
					$begin_date = $schedule->schedule->begin_date;
					$end_date = $schedule->schedule->end_date;
					$begin_h = $schedule->schedule->begin_h;
					$begin_m = $schedule->schedule->begin_m;
					$end_h = $schedule->schedule->end_h;
					$end_m = $schedule->schedule->end_m;
					$auto_yn = $schedule->schedule->auto_yn;
					$user_ip = $schedule->schedule->source_ip;
					$equipment = $schedule->schedule->equipment->__toString();
					$action = $schedule->schedule->action->__toString();
					$option = $schedule->schedule->option->__toString();
					$repeat = $schedule->schedule->repeat->__toString();
					$enable_week = $schedule->schedule->enable_week->__toString();
					$repeat_cnt = $schedule->schedule->repeat_cnt->__toString();
					$priority = $schedule->schedule->priority->__toString();
					$area = $schedule->schedule->area->__toString();
					$allday = $schedule->schedule->allday;
					$color = $schedule->schedule->color->__toString();
					$created_at = $schedule->schedule->created_at->__toString();
					$action_type = $schedule->schedule->action_type->__toString();
					$contents = $schedule->schedule->contents->__toString();

					$xml_cm = simplexml_load_file($GLOBALS['SCHEDULE_FILE']);

					// make different created_at
//					$nowtime = date("Y-m-d H:i:s");
//					$one_second_later  = date("Y-m-d H:i:s" , strtotime($nowtime."+".$i." second"));

					$result_next = $xml_cm->addChild('note');
					$result_next->addAttribute('date',$note_date);
					$result_schedule = $result_next->addChild('schedule');
					$result_schedule->addChild('title',$title);
					$result_schedule->addChild('begin_date',$upbegin_date);
					$result_schedule->addChild('end_date',$upend_date);
					$result_schedule->addChild('begin_h',$begin_h);
					$result_schedule->addChild('begin_m',$begin_m);
					$result_schedule->addChild('end_h',$end_h);
					$result_schedule->addChild('end_m',$end_m);
					$result_schedule->addChild('auto_yn',$auto_yn);
					$result_schedule->addChild('equipment',$equipment);
					$result_schedule->addChild('allday',$allday);
					$result_schedule->addChild('action',$action);
					$result_schedule->addChild('option',$option);
					$result_schedule->addChild('color',$color);
					$result_schedule->addChild('repeat',$repeat);
					$result_schedule->addChild('enable_week',$enable_week);
					$result_schedule->addChild('repeat_cnt',$repeat_cnt);
					$result_schedule->addChild('priority',$priority);
					$result_schedule->addChild('area',$area);
					$result_schedule->addChild('action_type',$action_type);
					$result_schedule->addChild('contents',$contents);
					$result_schedule->addChild('created_at',$created_at);
//					$result_schedule->addChild('created_at',$one_second_later);
				}

			}

			$xml_cm->asXml($GLOBALS['SCHEDULE_FILE']);

			$i++;
		}
		
		reorderID();	// generation ID.
		
   		$xmlDoc = new DOMDocument();
    	$xmlDoc->preserveWhiteSpace = false;
    	$xmlDoc->load($GLOBALS['SCHEDULE_FILE']);
        
    	$xmlDoc->formatOutput = true;
    	$xmlDoc->save($GLOBALS['SCHEDULE_FILE']);
    	unset($xmlDoc);	
	
		convert2crontab();
		
		echo "<script>alert('Copy Successfully !! ');history.back();</script>";
		
	} else { 
		exit('Something Wrong. Try again please'); 
	} 
}

function setDateFormat() {
	$dateformat_str = $_POST['dateformat'];
	echo $dateformat_str;

	$doc = new DOMDocument();
	$doc->formatOutput = true;

	$option = $doc->createElement('option');
	$option = $doc->appendChild($option);
	$dateformat = $doc->createElement('dateformat');
	$dateformat = $option->appendChild($dateformat);
	$text = $doc->createTextNode($dateformat_str);
	$text = $dateformat->appendChild($text);

	file_put_contents('/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/date_format.xml',$doc->saveXML());
}

function json_decode_local ( $json, $assoc = false ) {

	/* by default we don't tolerate ' as string delimiters
	   if you need this, then simply change the comments on
	   the following lines: */
  
	// $matchString = '/(".*?(?<!\\\\)"|\'.*?(?<!\\\\)\')/';
	$matchString = '/".*?(?<!\\\\)"/';
	
	// safety / validity test
	$t = preg_replace( $matchString, '', $json );
	$t = preg_replace( '/[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/', '', $t );
	if ($t != '') { return null; }
  
	// build to/from hashes for all strings in the structure
	$s2m = array();
	$m2s = array();
	preg_match_all( $matchString, $json, $m );
	foreach ($m[0] as $s) {
	  $hash       = '"' . md5( $s ) . '"';
	  $s2m[$s]    = $hash;
	  $m2s[$hash] = str_replace( '$', '\$', $s );  // prevent $ magic
	}
	
	// hide the strings
	$json = strtr( $json, $s2m );
	
	// convert JS notation to PHP notation
	$a = ($assoc) ? '' : '(object) ';
	$json = strtr( $json, 
	  array(
		':' => '=>', 
		'[' => 'array(', 
		'{' => "{$a}array(", 
		']' => ')', 
		'}' => ')'
	  ) 
	);
	
	// remove leading zeros to prevent incorrect type casting
	$json = preg_replace( '~([\s\(,>])(-?)0~', '$1$2', $json );
	
	// return the strings
	$json = strtr( $json, $m2s );
  
	/* "eval" string and return results. 
	   As there is no try statement in PHP4, the trick here 
	   is to suppress any parser errors while a function is 
	   built and then run the function if it got made. */
	$f = @create_function( '', "return {$json};" );
	$r = ($f) ? $f() : null;
  
	// free mem (shouldn't really be needed, but it's polite)
	unset( $s2m ); unset( $m2s ); unset( $f );
  
	return $r;
  }
?>
