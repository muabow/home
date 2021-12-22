<?php

include_once("/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/fw_config.php");

function json_encode2($content) {

	require_once '/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/libs/JSON.php';

	$json = new Services_JSON;

	return $json->encode($content);

}

switch($_GET['action'])
{
	case 'call':callEvent(); break;
	case 'update':updateEvent(); break;
	case 'add':addEvent(); break;
	case 'del':delEvent(); break;
}

function callEvent() {
	$created_at = $_POST['id'];

	$xml1 = simplexml_load_file('/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_holiday.xml');
	foreach($xml1->children() as $holiday) {

		$holiday_created_at = $holiday->created_at->__toString();

		if ($holiday_created_at == $created_at) {
			$begin_time = $holiday->begin_h.":".$holiday->begin_m->__toString();
			$end_time = $holiday->end_h.":".$holiday->end_m->__toString();

			$return_array = array(
				"isvalid" => $holiday->isvalid->__toString(),
				"title" => $holiday->title->__toString(),
				"begin_date" => $holiday->begin_date->__toString(),
				"end_date" => $holiday->end_date->__toString(),
				"begin_time" => $begin_time,
				"end_time" => $end_time,
				"created_at" => $holiday->created_at->__toString()
			);
		}
		$i++;
	}

	$encodedJson = json_encode2($return_array);

	echo $encodedJson;
//	echo json_encode($return_array);
}

function updateEvent() {
	$created_at = $_POST['id'];
	$title = strip_tags($_POST['title']);
	$title = substr($title,0,255);
   	$title = preg_replace("#[\\\]+$#", "", $title);
	$isvalid = $_POST['isvalid'];
	$begin_date = $_POST['s_date'];
	$end_date = $_POST['e_date'];
	$begin_time = $_POST['s_time'];
	$end_time = $_POST['e_time'];

	$begin = explode(":",$begin_time);
	$end = explode(":",$end_time);

	$xml2 = simplexml_load_file('/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_holiday.xml');
	foreach($xml2->children() as $holiday) {

		$holiday_created_at = $holiday->created_at->__toString();

		if ($holiday_created_at == $created_at) {
			$holiday->title = $title;
			$holiday->begin_date = $begin_date;
			$holiday->end_date = $end_date;
			$holiday->begin_h = $begin[0];
			$holiday->begin_m = $begin[1];
			$holiday->end_h = $end[0];
			$holiday->end_m = $end[1];
			$holiday->isvalid = $isvalid;
		}
	}

	file_put_contents('/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_holiday.xml',$xml2->saveXML());

    $xmlDoc = new DOMDocument();
    $xmlDoc->preserveWhiteSpace = false;
    $xmlDoc->load("/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_holiday.xml");

    $xmlDoc->formatOutput = true;
    $xmlDoc->save("/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_holiday.xml");
    unset($xmlDoc);

	$encodedJson = json_encode2(array('success'=>true));

	echo $encodedJson;exit;
//	echo json_encode(array('success'=>true));exit;
}

function addEvent() {
	$title = strip_tags($_POST['title']);
	$title = substr($title,0,255);
   	$title = preg_replace("#[\\\]+$#", "", $title);
	$isvalid = $_POST['isvalid'];
	$begin_date = $_POST['s_date'];
	$end_date = $_POST['e_date'];
	$begin_time = $_POST['s_time'];
	$end_time = $_POST['e_time'];

	$begin = explode(":",$begin_time);
	$end = explode(":",$end_time);
	$created_at = date("Y-m-d H:i:s");

	$xml3 = simplexml_load_file('/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_holiday.xml');

	$begin = explode(':',$begin_time);
	$end = explode(':',$end_time);

	$result_holiday = $xml3->addChild('holiday');
	$result_holiday->addChild('title',$title);
	$result_holiday->addChild('begin_date',$begin_date);
	$result_holiday->addChild('end_date',$end_date);
	$result_holiday->addChild('begin_h',$begin[0]);
	$result_holiday->addChild('begin_m',$begin[1]);
	$result_holiday->addChild('end_h',$end[0]);
	$result_holiday->addChild('end_m',$end[1]);
	$result_holiday->addChild('isvalid',$isvalid);
	$result_holiday->addChild('created_at',$created_at);

	$xml3->asXml('/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_holiday.xml');

    $xmlDoc = new DOMDocument();
    $xmlDoc->preserveWhiteSpace = false;
    $xmlDoc->load("/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_holiday.xml");

    $xmlDoc->formatOutput = true;
    $xmlDoc->save("/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_holiday.xml");
    unset($xmlDoc);


	$encodedJson = json_encode2(array('success'=>true));

	echo $encodedJson;exit;
//	echo json_encode(array('success'=>true));exit;
}

function delEvent()
{
	$created_at = $_POST['id'];
	$xml4 = simplexml_load_file('/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_holiday.xml');

	foreach($xml4->children() as $holiday) {

		$holiday_created_at = $holiday->created_at->__toString();

		if ($holiday_created_at == $created_at) {
			unset($holiday[0]);
			break;
		}
	}

	file_put_contents('/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_holiday.xml',$xml4->saveXML());

    $xmlDoc = new DOMDocument();
    $xmlDoc->preserveWhiteSpace = false;
    $xmlDoc->load("/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_holiday.xml");

    $xmlDoc->formatOutput = true;
    $xmlDoc->save("/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_holiday.xml");
    unset($xmlDoc);

	$encodedJson = json_encode2(array('success'=>true));

	echo $encodedJson;exit;
//	echo json_encode(array('success'=>true));exit;
}
