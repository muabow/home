<?php
function json_encode2($content) {

	require_once '/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/libs/JSON.php';

	$json = new Services_JSON;

	return $json->encode($content);

}

function getYearData() {

	if (file_exists('/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_schedule.xml')) {
		$xml_year = simplexml_load_file('/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_schedule.xml', 'SimpleXMLElement', LIBXML_NOCDATA);

		// 타이틀 $value->schedule->title
		// 시작날 $value->schedule->begin_date
		// 종료날 $value->schedule->end_date
		// 시작시 $value->schedule->begin_h
		// 시작분 $value->schedule->begin_m
		// 종료시 $value->schedule->end_h
		// 종료분 $value->schedule->end_m
		// 반복 $value->schedule->repeat
		// 색깔 $value->schedule->color
		// 생성시간 $value->schedule->created_at

		$list = array();
		$i = 0;

		foreach($xml_year->children() as $value) {

			$title = $value->schedule->title->__toString();
			$begin_date = $value->schedule->begin_date;
			$end_date = $value->schedule->end_date;
			$begin_h = $value->schedule->begin_h;
			$begin_m = $value->schedule->begin_m;
			$end_h = $value->schedule->end_h;
			$end_m = $value->schedule->end_m;
			$repeat = $value->schedule->repeat->__toString();
			$color = $value->schedule->color->__toString();

			$begin_time = $begin_h.":".$begin_m;
			$end_time = $end_h.":".$end_m;

			$exp_begin_data = explode("-",$begin_date);
			$exp_end_data = explode("-",$end_date);

			$begin_year = $exp_begin_data[0];
			$begin_month = $exp_begin_data[1];
			$begin_day = $exp_begin_data[2];

			$end_year = $exp_end_data[0];
			$end_month = $exp_end_data[1];
			$end_day = $exp_end_data[2];

			$name = "";
			if ($repeat != "") {
				$name = $title." (".$repeat.")";
			} else {
				$name = $title;
			}

			$list[$i]['id']		= $i;
			$list[$i]['name']	= $name;
			$list[$i]['location']	= $begin_time." ~ ".$end_time;
			$list[$i]['begin_year']	= $begin_year;
			$list[$i]['begin_month']	= $begin_month - 1;
			$list[$i]['begin_day']	= $begin_day;
			$list[$i]['end_year']	= $end_year;
			$list[$i]['end_month']	= $end_month - 1;
			$list[$i]['end_day']	= $end_day;
			$list[$i]['color']	= $color;

			$i++;

		} // foreach

	}

	$encodedJson = json_encode2($list);
	echo $encodedJson;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap-datepicker.min.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap-theme.min.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap-year-calendar.min.css">
	<link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<title>Bootstrap Year Calendar</title>
	<script src="js/respond.min.js"></script>
	<script src="js/jquery-1.10.2.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/bootstrap-datepicker.min.js"></script>
	<script src="js/bootstrap-year-calendar.min.js"></script>
	<script src="js/bootstrap-popover.js"></script>
	<script src="js/scripts.js"></script>
	<script>

	$(function() {
		var arr = [];
		var data = [];

		arr = <?php echo getYearData(); ?>;

		for(var i=0;i<arr.length;i++) {
			  data.push({
				id: arr[i].id,
				name: arr[i].name,
				location: arr[i].location,
				startDate: new Date(arr[i].begin_year,arr[i].begin_month,arr[i].begin_day),
				endDate: new Date(arr[i].end_year,arr[i].end_month,arr[i].end_day),
				color: arr[i].color
			  });
		}

		var currentYear = new Date().getFullYear();

		var calendar = $('#calendar').calendar({
			enableContextMenu: true,
			enableRangeSelection: true,
			mouseOnDay: function(e) {
				if(e.events.length > 0) {
					var content = '';

					for(var i in e.events) {
						content += '<div class="event-tooltip-content">'
										+ '<div class="event-name" style="color:' + e.events[i].color + '">' + e.events[i].name + '</div>'
										+ '<div class="event-location">' + e.events[i].location + '</div>'
									+ '</div>';
					}

					$(e.element).popover({
						trigger: 'manual',
						container: 'body',
						html:true,
						content: content
					});

					$(e.element).popover('show');
				}
			},
			mouseOutDay: function(e) {
				if(e.events.length > 0) {
					$(e.element).popover('hide');
				}
			},
			dayContextMenu: function(e) {
				$(e.element).popover('hide');
			},
			clickDay: function(e) {
				window.parent.$('#calendar').fullCalendar('gotoDate', e.date);
				window.parent.$('#year_view').dialog( "close" );
				//var t = window.parent.$('#calendar');
				//t.fullCalendar('removeEvents', 1);
				//console.log(t);
			},
/*
			dataSource: [
				{
					id: 0,
					name: 'Test 1',
					location: '2:00',
					startDate: new Date(currentYear, 4, 28),
					endDate: new Date(currentYear, 4, 29)
				},
				{
					id: 1,
					name: 'Test 2',
					location: '09:00',
					startDate: new Date(currentYear, 2, 16),
					endDate: new Date(currentYear, 2, 19)
				},
				{
					id: 2,
					name: 'Test 3',
					location: '11:00',
					startDate: new Date(currentYear, 3, 29),
					endDate: new Date(currentYear, 4, 1)
				},
				{
					id: 3,
					name: 'Apple Special Event',
					location: 'San Francisco, CA',
					startDate: new Date(currentYear, 8, 1),
					endDate: new Date(currentYear, 8, 1)
				},
				{
					id: 4,
					name: 'Apple Keynote',
					location: 'San Francisco, CA',
					startDate: new Date(currentYear, 8, 9),
					endDate: new Date(currentYear, 8, 9)
				},
				{
					id: 5,
					name: 'Chrome Developer Summit',
					location: 'Mountain View, CA',
					startDate: new Date(currentYear, 10, 17),
					endDate: new Date(currentYear, 10, 18)
				},
				{
					id: 6,
					name: 'F8 2015',
					location: 'San Francisco, CA',
					startDate: new Date(2017, 2, 25),
					endDate: new Date(2017, 2, 26)
				},
				{
					id: 7,
					name: 'Yahoo Mobile Developer Conference',
					location: 'New York',
					startDate: new Date(currentYear, 7, 25),
					endDate: new Date(currentYear, 7, 26)
				},
				{
					id: 8,
					name: 'Android Developer Conference',
					location: 'Santa Clara, CA',
					startDate: new Date(currentYear, 11, 1),
					endDate: new Date(currentYear, 11, 4)
				},
				{
					id: 9,
					name: 'LA Tech Summit',
					location: 'Los Angeles, CA',
					startDate: new Date(currentYear, 10, 17),
					endDate: new Date(currentYear, 10, 17)
				}
			]
*/
			dataSource: data
		});

	});
	</script>
 </head>

 <body>
  <div id="calendar" style="overflow:hidden;"></div>
 </body>
</html>
