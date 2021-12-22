<?php
	include "/opt/interm/public_html/modules/annual_schedule/html/common/common_define.php";

	function getDateFormat() {
		$DATE_FORMAT_FILE = '/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/date_format.xml';
		if (file_exists($DATE_FORMAT_FILE) == false) {
			file_put_contents($DATE_FORMAT_FILE,'<?xml version="1.0"?><option><dateformat>YYYY-MM-DD</dateformat></option>');
		} else {
		}
		$xmlDoc = simplexml_load_file($DATE_FORMAT_FILE, 'SimpleXMLElement', LIBXML_NOCDATA);
		$dateformat = "";
		if (isset($xmlDoc->dateformat))
			$dateformat = $xmlDoc->dateformat;

		if (strlen($dateformat) == 10)
			return $dateformat;
		else
			return strlen($dateformat);
	}
?>
<html>
<head>
<title><?=Annual_schedule\Lang\SIBO_MAIN_7?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="/modules/annual_schedule/html/params/newSchedule/js/jquery-ui.css">
<link rel="shortcut icon" href=<?=FAVICON_PATH ?> />
<script src="/modules/annual_schedule/html/params/newSchedule/js/jquery-1.12.4.js"></script>
<script src="/modules/annual_schedule/html/params/newSchedule/js/jquery-1.12.1-ui.js"></script>
<script>

var DATE_FORMAT = "<?=getDateFormat()?>";
var DATE_FICKER_FORMAT = "yy-mm-dd";

switch (DATE_FORMAT) {
	case "YYYY-MM-DD" :
		DATE_FICKER_FORMAT = "yy-mm-dd";
		break;
	case "MM-DD-YYYY" :
		DATE_FICKER_FORMAT = "mm-dd-yy";
		break;
	case "DD-MM-YYYY" :
		DATE_FICKER_FORMAT = "dd-mm-yy";
		break;
};

$( function() {

	$( "#register_dialog" ).dialog({

		autoOpen: false,
		width: 500,
		height: 300,
		scrollable:false,
		show: {
			effect: "Fade",
			duration: 1000
		},

		hide: {
			effect: "Fade",
			duration: 1000
		}

	});

	$( "#update_dialog" ).dialog({

		autoOpen: false,
		width: 500,
		height: 300,
		scrollable:false,
		show: {
			effect: "Fade",
			duration: 1000
		},

		hide: {
			effect: "Fade",
			duration: 1000
		}

	});

	$( "#register_manager" ).on( "click", function() {
		$( "#register_dialog" ).dialog( "open" );
	});

	$( "#s_datepicker" ).datepicker({ changeYear:false, dateFormat: DATE_FICKER_FORMAT }).focus(function () { $(".ui-datepicker-year").show(); });
	$( "#e_datepicker" ).datepicker({ changeYear:false, dateFormat: DATE_FICKER_FORMAT }).focus(function () { $(".ui-datepicker-year").show(); });
	$( "#s_updatepicker" ).datepicker({ changeYear:false, dateFormat: DATE_FICKER_FORMAT }).focus(function () { $(".ui-datepicker-year").show(); });
	$( "#e_updatepicker" ).datepicker({ changeYear:false, dateFormat: DATE_FICKER_FORMAT }).focus(function () { $(".ui-datepicker-year").show(); });

	$("#register_dialog").css({width:"500px",height:"300px", overflow:"hidden"});
	$("#update_dialog").css({width:"500px",height:"300px", overflow:"hidden"});
});

function delete_holiday(num) {

	if (confirm('<?php echo Annual_schedule\Lang\SIBO_DELETE_STR?>') == true) {
		var dataString = "&id=" + num;
		$.ajax({
			type : "POST",
			url : "/modules/annual_schedule/html/params/newSchedule/holiday_events.php?action=del",
			data : dataString,
			dataType : "json",
			error : function(){
				alert('Network Connect Failed !!');
			},
			success : function(data){
				location.reload();
			}

		});
	} else {
		return;
	}
}

function insert_holiday() {
	var s_date = $("#s_datepicker").val();
	var e_date = $("#e_datepicker").val();

	var s_time = $("#s_time").val();
	var e_time = $("#e_time").val();

	var title = $("#holiday_title").val();
	var isvalid = $("#isvalid").val();

	if ( $("#isvalid").is(":checked")) {
		isvalid = 1;
	} else {
		isvalid = 0;
	}

	var s_first_part = s_time.substr(0,2);
	var s_last_part = s_time.substr(-2);

	var e_first_part = e_time.substr(0,2);
	var e_last_part = e_time.substr(-2);

	var s_year = s_date.substr(0,4);
	var e_year = e_date.substr(0,4);
// cannot exceed 3year.
	if (parseInt(e_year) - parseInt(s_year) >= 3){
		alert("The difference between the years can not exceed three years.");
		$("#e_datepicker").val(s_date);
		return;
	}
	s_date = s_date.substr(5,5);
	e_date = e_date.substr(5,5);

/*
	if (s_date > e_date) {
		alert("Start date is bigger than End date");
		$("#e_datepicker").val(s_date);
		return;
	}
*/
	if (s_first_part < 00 || s_first_part > 23) {
		alert("Please Input Correct Begin Time");
		$("#s_time").focus();
		return;
	}

	if (e_first_part < 00 || e_first_part > 23) {
		alert("Please Input Correct End Time");
		$("#e_time").focus();
		return;
	}

	if (s_last_part < 00 || s_last_part > 59) {
		alert("Please Input Correct Begin Time");
		$("#s_time").focus();
		return;
	}

	if (e_last_part < 00 || e_last_part > 59) {
		alert("Please Input Correct End Time");
		$("#e_time").focus();
		return;
	}

	if (s_first_part > e_first_part) {
		alert("Begin Time is Bigger than End Time. Please Fix it.");
		$("#s_time").focus();
		return;
	} else {
		if (s_last_part > e_last_part) {
			alert("Begin Time is Bigger than End Time. Please Fix it.");
			$("#s_time").focus();
			return;
		}
	}

	var title = $("#holiday_title").val();

	if (title == false) {
		alert("Please Input the Title");
		$("#holiday_title").focus();
		return;
	}

	var dataString = "&isvalid="+isvalid+"&title="+title+"&s_date="+s_date+"&e_date="+e_date+"&s_time="+s_time+"&e_time="+e_time;

	$.ajax({
		type : "POST",
		url : "/modules/annual_schedule/html/params/newSchedule/holiday_events.php?action=add",
		data : dataString,
		dataType : "json",
		error : function(){
			alert('Network Connect Failed !!');
		},
		success : function(data){
			location.reload();
		}

	});
}
</script>
<style type="text/css">
.container {
  width: 90%;
  height: 70%;
  margin: 40px auto;
}
.outer {
  display: table;
  width: 100%;
}
.inner {
  display: table-cell;
  vertical-align: middle;
  text-align: center;
}
.centered {
  position: relative;
  display: inline-block;
  width: 50%;
  padding: 1em;
}
table {
	width:800px;
    margin-top: 10px;
}
.datahead {
    border: 1px solid gray;
    background-color:#00a0e9;
    color:white;
	text-align:center;
}
.tdl {
    width: 160px;
    border:1px solid #e5e5e5;
    height:25px;
    font-size:12px;
    color:#9e9e9e;
    font-weight:bold;
	text-align:center;
}
.tdr {
    width: 100px;
    background-color:#e5e5e5;
}
button::-moz-focus-inner,
input::-moz-focus-inner {
    border: 0;
    padding: 0;
}

.ct-btn {
	display: inline-block;
	margin: 5px 0;
	padding: .5em .75em;
	border-radius: .25em;
	box-sizing: content-box;
	-moz-box-sizing: content-box;
	background: transparent;
	outline: 0;
	vertical-align: middle;
	font-family: inherit;
	font-size: 12px;
	text-decoration: none;
	white-space: nowrap;
	cursor: pointer
}
a {
	text-decoration:none;
	color:#000;
}
a:hovor {
	text-decoration:none;
	color:#000;
}
a:link {
	text-decoration:none;
	color:#000;
}
a:visited {
	text-decoration:none;
	color:#000;
}
a:active {
	text-decoration:none;
	color:#000;
}
</style>
</head>

<body>
<div id="register_dialog" title="<?=Annual_schedule\Lang\SIBO_HOLI_ADD_DIALOG_TITLE?>">
	<table style="width:440px" cellpadding="0" cellspacing="0" border="0" height="150">
	  <tr>
	    <td style="width:150px"><?=Annual_schedule\Lang\SIBO_HOLI_ACTION?><input type="checkbox" id="isvalid" value="1" checked="checked" /></td>
	    <td style="width:100px"><?=RADIO_USER_TITLE?></td>
	    <td colspan="2" align="left"><input type="text" id="holiday_title" value="" placeholder="Holiday Title" style="width:200px;" /></td>
	  </tr>
	  <tr>
	    <td style="width:100px"><?=Annual_schedule\Lang\SIBO_HOLI_START_DATE?></td>
	    <td style="width:120px"><input type="text" id="s_datepicker" style="width:100px;" /></td>
	    <td style="width:100px"><?=Annual_schedule\Lang\SIBO_HOLI_START_TIME?></td>
	    <td style="width:120px"><input type="text" id="s_time" value="00:00" style="width:100px;" maxlength="5" /></td>
	  </tr>
	  <tr>
	    <td style="width:100px"><?=Annual_schedule\Lang\SIBO_HOLI_END_DATE?></td>
	    <td style="width:120px"><input type="text" id="e_datepicker" style="width:100px;" /></td>
	    <td style="width:100px"><?=Annual_schedule\Lang\SIBO_HOLI_END_TIME?></td>
	    <td style="width:120px"><input type="text" id="e_time" value="23:59" style="width:100px;" maxlength="5" /></td>
	  </tr>
	</table>
	<table>
	  <tr>
	    <td><button onclick="insert_holiday();return false;"><?=RADIO_USER_ADD?></button></td>
	  </tr>
	</table>
</div>
<div id="update_dialog" title="Update Dialog">
	<input type="hidden" name="update_id" id="update_id" value="" />
	<table style="width:440px" cellpadding="0" cellspacing="0" border="0" height="150">
	  <tr>
	    <td style="width:150px"><?=Annual_schedule\Lang\SIBO_HOLI_ACTION?><input type="checkbox" id="upisvalid" value="1" checked="checked" /></td>
	    <td style="width:100px"><?=RADIO_USER_TITLE?></td>
	    <td colspan="2" align="left"><input type="text" id="holiday_uptitle" value="" placeholder="Holiday Title" style="width:200px;" /></td>
	  </tr>
	  <tr>
	    <td style="width:100px"><?=Annual_schedule\Lang\SIBO_HOLI_START_DATE?></td>
	    <td style="width:120px"><input type="text" id="s_updatepicker" style="width:100px;" /></td>
	    <td style="width:100px"><?=Annual_schedule\Lang\SIBO_HOLI_START_TIME?></td>
	    <td style="width:120px"><input type="text" id="s_uptime" value="00:00" style="width:100px;" maxlength="5" /></td>
	  </tr>
	  <tr>
	    <td style="width:100px"><?=Annual_schedule\Lang\SIBO_HOLI_END_DATE?></td>
	    <td style="width:120px"><input type="text" id="e_updatepicker" style="width:100px;" /></td>
	    <td style="width:100px"><?=Annual_schedule\Lang\SIBO_HOLI_END_TIME?></td>
	    <td style="width:120px"><input type="text" id="e_uptime" value="23:59" style="width:100px;" maxlength="5" /></td>
	  </tr>
	</table>
	<table>
	  <tr>
	    <td><button onclick="update_holiday();return false;"><?=Annual_schedule\Lang\SIBO_HOLI_UPDATE?></button></td>
	  </tr>
	</table>
</div>
<div class="container">
  <div class="outer">
    <div class="inner">
      <div class="centered">
	    <table>
		  <tr>
		    <td><h2><?=Annual_schedule\Lang\SIBO_HOLI_TITLE?></h2></td>
		  </tr>
		</table>
		<table>
		  <tr>
		    <th class="datahead"><?=Annual_schedule\Lang\SIBO_HOLI_ACTION?></th>
		    <th class="datahead"><?=Annual_schedule\Lang\SIBO_HOLI_ADD_TITLE?></th>
		    <th class="datahead"><?=Annual_schedule\Lang\SIBO_HOLI_START_DATE?></th>
		    <th class="datahead"><?=Annual_schedule\Lang\SIBO_HOLI_START_TIME?></th>
		    <th class="datahead"><?=Annual_schedule\Lang\SIBO_HOLI_END_DATE?></th>
		    <th class="datahead"><?=Annual_schedule\Lang\SIBO_HOLI_END_TIME?></th>
		    <th class="datahead"><?=Annual_schedule\Lang\SIBO_HOLI_FUNCTION?></th>
		  </tr>
		<?php
			$i = 0;
			$xml_h = simplexml_load_file('/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_holiday.xml');
			foreach($xml_h->children() as $holiday) {
		?>
		  <tr>
			<td class="tdl"><?php if ($holiday->isvalid == 1) echo "<img src='/modules/annual_schedule/html/params/newSchedule/img/check_v.png' style='text-align:center;vertical-align:middle' />"; ?></td>
			<td class="tdl"><?php echo $holiday->title; ?></td>
			<td class="tdl"><?php echo $holiday->begin_date; ?></td>
			<td class="tdl"><?php echo $holiday->begin_h.":".$holiday->begin_m; ?></td>
			<td class="tdl"><?php echo $holiday->end_date; ?></td>
			<td class="tdl"><?php echo $holiday->end_h.":".$holiday->end_m; ?></td>
			<td class="tdl"><span id="<?php echo $holiday->created_at; ?>" class="update_button" style="cursor:pointer"><img src="/modules/annual_schedule/html/params/newSchedule/img/refair.png" style="text-align:center" /></span> / <a href="javascript:delete_holiday('<?php echo $holiday->created_at; ?>');"><img src="img/recyc.png" style="text-align:center" /></a></td>
		  </tr>
		<?php $i++ ?>
		<?php } ?>
		</table>
		<table>
		  <tr>
		    <td><button id="register_manager" class="ct-btn" onclick="return false;"><?=RADIO_USER_ADD?></button></td>
		  </tr>
		</table>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
$(function(){
	$(".update_button").click(function() {
		var id = $(this).attr("id");

		var dataString = '&id='+ id ;
		$.ajax({
			type : "POST",
			url : "/modules/annual_schedule/html/params/newSchedule/holiday_events.php?action=call",
			data : dataString,
			dataType : "json",
			error : function(){
				alert('Network Connect Failed !!');
			},
			success : function(data){
				var isvalid = data["isvalid"];
				var uptitle = data["title"];
				var upstart_date = data["begin_date"];
				var upend_date = data["end_date"];
				var upstart_time = data["begin_time"];
				var upend_time = data["end_time"];
				var upnum = data["created_at"];

				$("#update_id").val(upnum);
				if (isvalid == 1) {
					$("#upisvalid").attr('checked', true);
				} else {
					$("#upisvalid").attr('checked', false);
				}
				$("#holiday_uptitle").val(uptitle);
				$("#s_updatepicker").val(upstart_date);
				$("#e_updatepicker").val(upend_date);
				$("#s_uptime").val(upstart_time);
				$("#e_uptime").val(upend_time);

				$( "#update_dialog" ).dialog( "open" );
			}

		});
	});
});

function update_holiday() {
	var update_id = $("#update_id").val();
	var uptitle = $("#holiday_uptitle").val();
	var s_update = $("#s_updatepicker").val();
	var e_update = $("#e_updatepicker").val();
	var s_uptime = $("#s_uptime").val();
	var e_uptime = $("#e_uptime").val();
	var upisvalid = $("#upisvalid").val();

	if ( $("#upisvalid").is(":checked")) {
		upisvalid = 1;
	} else {
		upisvalid = 0;
	}

	var s_first_part = s_uptime.substr(0,2);
	var s_last_part = s_uptime.substr(-2);

	var e_first_part = e_uptime.substr(0,2);
	var e_last_part = e_uptime.substr(-2);

	if (s_update.length < 6){
		s_update = update_id.substr(0,4) + "-" + s_update;
	}
	if (e_update.length < 6){
		e_update = update_id.substr(0,4) + "-" + e_update;
	}
	alert(s_update);
	var s_year = s_update.substr(0,4);
	var e_year = e_update.substr(0,4);
// cannot exceed 3year.
	if (parseInt(e_year) - parseInt(s_year) >= 3){
		alert("The difference between the years can not exceed three years.");
		$("#e_updatepicker").val(s_date);
		return;
	}
	s_update = s_update.substr(5,5);
	e_update = e_update.substr(5,5);

	if (s_first_part < 00 || s_first_part > 23) {
		alert("Please Input Correct Begin Time");
		$("#s_uptime").focus();
		return;
	}

	if (e_first_part < 00 || e_first_part > 23) {
		alert("Please Input Correct End Time");
		$("#e_uptime").focus();
		return;
	}

	if (s_last_part < 00 || s_last_part > 59) {
		alert("Please Input Correct Begin Time");
		$("#s_uptime").focus();
		return;
	}

	if (e_last_part < 00 || e_last_part > 59) {
		alert("Please Input Correct End Time");
		$("#e_uptime").focus();
		return;
	}

	if (s_first_part > e_first_part) {
		alert("Begin Time is Bigger than End Time. Please Fix it.");
		$("#s_uptime").focus();
		return;
	} else {
		if (s_last_part > e_last_part) {
			alert("Begin Time is Bigger than End Time. Please Fix it.");
			$("#s_uptime").focus();
			return;
		}
	}

	var title = $("#holiday_uptitle").val();

	if (title == false) {
		alert('<?=Annual_schedule\Lang\SIBO_HOLI_BLANK?>');
		$("#holiday_uptitle").focus();
		return;
	}


	var dataString = "isvalid="+upisvalid+"&id="+update_id+"&title="+uptitle+"&s_date="+s_update+"&e_date="+e_update+"&s_time="+s_uptime+"&e_time="+e_uptime;
	$.ajax({
		type : "POST",
		url : "/modules/annual_schedule/html/params/newSchedule/holiday_events.php?action=update",
		data : dataString,
		dataType : "json",
		error : function(){
			alert('Network Connect Failed !!');
		},
		success : function(data){
			location.reload();
		}

	});
}
</script>
</body>
</html>
