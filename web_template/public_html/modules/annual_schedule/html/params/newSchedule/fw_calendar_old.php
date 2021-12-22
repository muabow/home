<?php

$today = date("Y-m-d H:i:s"); 	//달력 입력창 입력시간 설정
$calendar_minhour = 0; 			// 0시부터
$calendar_maxhour = 23;			// 23시 까지
$calendar_minute_interval = 5;	//5분 각격으로

$list_mp3 = array();
if (!$handle = @opendir("/opt/interm/www/html/mp3")){
} 
else{
    $cnt = 0;
    while(($file = readdir($handle)) !== false){
		if($file == '.' ||  $file == '..'){
			continue;
		}
		$exp = explode('.', $file);
		$ext = end($exp);		
        $ext = strtolower($ext);
		if ($ext == "mp3" || $ext == "wav"){
			$list_mp3[$cnt] = $file;
			$cnt++;
		}	
	} 
    closedir($handle);
	reset($list_mp3);
 	sort($list_mp3);
}



/**
$xml = simplexml_load_file('/opt/interm/www/html/auth_admin/params/newSchedule/info_holiday.xml', 'SimpleXMLElement', LIBXML_NOCDATA);	
foreach($xml->children() as $value) {
	$ref_start = $value->begin_date." ".$value->begin_h.":".$value->begin_m.":00";
	$ref_end = $value->end_date." ".$value->end_h.":".$value->end_m.":00";
	echo $ref_start." ~ ".$ref_end."<br>";	
}
unset($xml);
*/

?>

<link rel="stylesheet" href='/auth_admin/params/newSchedule/full/jquery/jquery-ui.css'>
<link rel="stylesheet" href='/auth_admin/params/newSchedule/full/jquery/jquery-ui-timepicker-0.2.9/jquery.ui.timepicker.css'>
<script type='text/javascript' src='/auth_admin/params/newSchedule/full/jquery/jquery-ui.min.js'></script>
<script type='text/javascript' src='/auth_admin/params/newSchedule/full/simple_colorpicker.js'></script>
<script type='text/javascript' src='/auth_admin/params/newSchedule/full/jquery/jquery-ui-timepicker-0.2.9/jquery.ui.timepicker.js'></script>

<script type='text/javascript'>
	Calendar = {};
Calendar.timePickerMinHour = <?php echo $calendar_minhour?>;
Calendar.timePickerMaxHour = <?php echo $calendar_maxhour?>;
Calendar.timePickerMinuteInterval = <?php echo $calendar_minute_interval?>;

PopupTitle = '<?php echo SIBO_MAIN_15?>';
PopupTitleCopy = '<?php echo COPY_PASTE_STR?>';
m = 'cal';

getPlayTime = false;
addEventPending = false;
updateEventPending = false;
pendingStart = "";
pendingEnd = "";

calendar_left = 'month';
calendar_resourceMonth = '';
calendar_defaultView = 'month';
calendar_resources = '';
calendar_time = '<?php echo $today?>';
str_hour = '<?php echo SIBO_HOUR_STR?>';
str_minute = '<?php echo SIBO_MINUTE_STR?>';
str_now = '<?php echo SIBO_NOW_STR?>';
str_save = '<?php echo SIBO_MAIN_10?>';
str_remove = '<?php echo RADIO_USER_DEL?>';

str_holiday = '<?php echo SIBO_HOLIDAY_STR?>';
str_download = '<?php echo SIBO_DOWNLOAD_STR?>';
str_upload = '<?php echo SIBO_UPLOAD_STR?>';
str_copy = '<?php echo SIBO_COPY_STR?>';
str_today = '<?php echo SIBO_TODAY_STR?>';
str_copy_only = '<?php echo COPY_STR?>';

str_monthly = '<?php echo SIBO_DISPLAY_MONTH?>';
str_weekly = '<?php echo SIBO_DISPLAY_WEEK?>';
str_daily = '<?php echo SIBO_DISPLAY_DAY?>';
str_yearly = '<?php echo SIBO_DISPLAY_YEAR?>';
str_close = '<?php echo SIBO_CLOSE?>';
str_delete_confirm = '<?php echo SIBO_DELETE_STR?>';

</script>

<style>
	#feedback { font-size: 1.4em; }
	#selectable .ui-selecting { background: #FECA40; }
	#selectable .ui-selected { background: #F39814; color: white; }
	#selectable { list-style-type: none; margin: 0; padding: 0; width: 100%; }
	#selectable li { margin: 3px; padding: 0.4em; font-size: 1.0em; height: 18px; }
</style>

<script type='text/javascript' src='/auth_admin/params/newSchedule/full/fullcalendar/lib/moment.min.js'></script>
<script type='text/javascript' src='/auth_admin/params/newSchedule/fw_events.js?v=2019040429'></script>
<script type='text/javascript' src='/auth_admin/params/newSchedule/full/fullcalendar/fullcalendar.js'></script>

<link rel="stylesheet" href='/auth_admin/params/newSchedule/full/fullcalendar/fullcalendar.css' rel='stylesheet'>
<link rel="stylesheet" href='/auth_admin/params/newSchedule/full/fullcalendar/fullcalendar.print.css' rel='stylesheet' media='print'>

<div id='calendar' ></div>
<!--
<div class="fw_tip">
[TIP] <br>
1. 일정을 마우스 왼쪽버튼을 누른 채 잡아 끌면 날짜를 쉽게 변경할 수 있습니다.<br>
</div>
-->
<div id="dialog-message" title="Agenda-item toevoegen" style="display: none;background-color:gray;font-weight:bold;">
	<form>
		<div style="text-align:right;">
			<div style="margin-left:10px;height:20px;text-align:left;"><?php echo SIBO_NAME?></div>
			<textarea cols="30" rows="1" id="edited_title" style="width:98%;resize:none;" maxlength="64"></textarea>
			<div style="height:5px;">&nbsp;</div>
			<div style="width:100%;text-align:right;">
				<table style="width:100%;margin-right:10px;border:1px;"><tr><td><?php echo DATE_TYPE_STR?><br />
					<select id="date_format">
						<option value="YYYY-MM-DD">YYYY-MM-DD</option>
						<option value="MM-DD-YYYY">MM-DD-YYYY</option>
						<option value="DD-MM-YYYY">DD-MM-YYYY</option>
					</select>
				</td>
				<td>
				<div style="margin-bottom: 5px;">
					<span style="float:right;"><?php echo SIBO_DATE_STR?>
						<input type="text" id="datepicker_startdate" style="width: 90px;" class="ed" readonly='readonly'>
						~
						<input type="text" id="datepicker_enddate" style="width: 90px;margin-bottom:4px;" class="ed" readonly='readonly'>
					</span>
				</div>
				<div style="margin-bottom: 5px;">
					<span style="float:right;"><?php echo SIBO_TIME_STR?>
						<input type="text" id="timepicker_starttime" style="width: 90px;" class="ed" maxlength="5">
						~
						<input type="text" id="timepicker_endtime" style="width: 90px;margin-bottom:4px;" class="ed" maxlength="5" value="23:59">
					</span>
				</div>
				</td></tr></table>
			</div>
			<div style="clear:both;"></div>
			<div style="float:right"><input type="checkbox" id="dow_0" value='0' style="vertical-align: middle;" onclick="javascript:dow_click();"><label for="dow_0"><?php echo SIBO_CENTER_STR6?> </label>
			<input type="checkbox" id="dow_1" value='1' style="vertical-align: middle;" onclick="javascript:dow_click();"><label for="dow_1"><?php echo SIBO_CENTER_STR7?> </label>
			<input type="checkbox" id="dow_2" value='2' style="vertical-align: middle;" onclick="javascript:dow_click();"><label for="dow_2"><?php echo SIBO_CENTER_STR8?> </label>
			<input type="checkbox" id="dow_3" value='3' style="vertical-align: middle;" onclick="javascript:dow_click();"><label for="dow_3"><?php echo SIBO_CENTER_STR9?> </label>
			<input type="checkbox" id="dow_4" value='4' style="vertical-align: middle;" onclick="javascript:dow_click();"><label for="dow_4"><?php echo SIBO_CENTER_STR10?> </label>
			<input type="checkbox" id="dow_5" value='5' style="vertical-align: middle;" onclick="javascript:dow_click();"><label for="dow_5"><?php echo SIBO_CENTER_STR11?> </label>
			<input type="checkbox" id="dow_6" value='6' style="vertical-align: middle;" onclick="javascript:dow_click();"><label for="dow_6"><?php echo SIBO_CENTER_STR12?> </label></div>
			<div style="clear:both;"></div>
			<div>
				<input type="radio" name="repeat_radio" id="repeat_everyday_checkbox" style="vertical-align: middle;" />
				<label for="repeat_everyday_checkbox"><?php echo REPEAT_EVERYDAY_STR?></label>
				<input type="radio" name="repeat_radio" id="repeat_once_checkbox" checked style="vertical-align: middle;" />
				<label for="repeat_once_checkbox"><?php echo REPEAT_ONCE_STR?></label>
				<input type="checkbox" id="allday_checkbox" value='1' style="vertical-align: middle;margin-top:3px;">
				<label for="allday_checkbox"><?php echo SIBO_ALLDAY_STR?></label>	
			</div>			
			<div style="margin:1px 0 0 0;text-align: left;">
				<span class="dialog_formfield" id="ColorPicker1"></span>
				<?php echo SIBO_FILE_SELECT?><span id="ColorSelectionTarget1">&nbsp;</span>
			</div>
			<div style="height:5px;">&nbsp;</div>
			<div>
				<input type="checkbox" id="edited_autoplayback" value='1' style="vertical-align: middle;">
				<label for="edited_autoplayback"><?php echo SIBO_AUTOEND_STR?></label>
				 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo MSG_REP_STR?>
				<select id="edited_repeat" onchange="RepeatChange()">
					<option value=""><?php echo SIBO_NONE_STR?></option>
					<option value="week"><?php echo SIBO_WEEK_STR?></option>
					<option value="month"><?php echo SIBO_MONTH_STR?></option>
					<option value="year"><?php echo SIBO_YEAR_STR?></option>
				</select>
				<select id="enable_week">
					<option value="every"><?php echo SIBO_EVERYWEEK_STR?></option>
					<option value="first"><?php echo SIBO_FIRSTWEEK_STR?></option>
					<option value="last"><?php echo SIBO_LASTWEEK_STR?></option>
				</select>
			</div>
			<div>----------------------------------------------------------------------------------</div>
			<div style="margin:5px 0 5px 0;">
				<?php echo SIBO_ACTIONTYPE_STR?>
				<select  id="edited_progress" onchange="Display_action()">
					<option value="">- - -</option>
					<option value="MP3 Play">MP3 Play</option>
					<option value="contact">Contact</option>
					<option value="TTS">TTS</option>
					<option value="Script">Script</option>
				</select>
				<?php echo SIBO_REPEAT_CNT_STR?>
				<input type="text" id="edited_repeat_count" style="width: 40px;" class="ed" maxlength="3" />
			</div>
			<div id="file_area">
				<div style="margin-left:10px;height:20px;text-align:left;"><?php echo SIBO_FILENAME_STR?></div>
				<select id="select_file" name="select_file" style="width:97%;font-size:12px;line-height:100%;" size='5' multiple="multiple" onchange="FileSelect();">
				<?php
					for ($idx = 0; $idx < $cnt; $idx++){
						echo '<option name="select" style="line-height:100%;padding-top:6px;" value="'.$list_mp3[$idx].'">'.$list_mp3[$idx].'</option>';
					}
				?>
				</select>
			</div>
			<div id="tts_area">
				<div style="margin-left:10px;height:20px;text-align:left;">Text for TTS</div>
				<textarea cols="32" rows="6" id="edited_tts" style="width:97%;resize:none;" maxlength="256"></textarea>
			</div>
			<div id="no_action">
				<textarea cols="30" rows="1" style="width:95%;resize:none;background-color:gray;border-color:gray;" maxlength="16" readonly='readonly'><?php echo SIBO_NOTSUPPORTED_STR?></textarea>
			</div>
			<div>----------------------------------------------------------------------------------</div>
			<div style="display:none;">
				DeviceName
				<input type="text" id="edited_equipment" style="width: 100px;" value="127.0.0.1" readonly="readonly">
				Option
				<select id="edited_option" disabled>
					<option value="">none</option>
					<option value="file">file</option>
					<option value="text">text</option>
					<option value="folder">folder</option>
				</select>
			</div>
			<div style="display:none;">
				Priority
				<input type="text" id="edited_priority" style="width: 50px;" class="ed" readonly="readonly">
				Area
				<select id="edited_area" disabled>
					<option value="">none</option>
					<option value="area1">area1</option>
					<option value="area2">area2</option>
					<option value="G1">G1</option>
					<option value="G2">G2</option>
					<option value="all">all</option>
				</select>
			</div>
			<div id="more_click"></div>
			<div id="more_select"></div>
			<div id= "error_message" style="position:absolute; bottom:0; padding:5px; color:#cc3300; text-align:left; font-weight:bold;" value=""></div>
		</div>
	</form>
</div>

<div id="dialog-copy" title="Agenda-item toevoegen" style="display: none;background-color:gray;font-weight:bold;">
	<form>
		<div style="text-align:right;">
			<div style="margin-left:10px;height:20px;text-align:left;"><?php echo SIBO_NAME?></div>
			<div style="height:5px;">&nbsp;</div>
		</div>
		<textarea cols="30" rows="1" id="copy_title" style="width:98%;resize:none;" maxlength="64"></textarea>
		<hr style="border: solid 1px #666666" />
		<div style="vertical-align: top left;margin-top:3px;"><input type="text" style="width:280px" id="add_sche_datepicker" /><div id="add_schedule" style="">+</div><div id="remove_schedule" style="">-</div></div>
		<hr style="border: dashed 1px #666666" />
		<div></div>
		<div style="vertical-align: top left;float:left;width:250px;border:dashed 0px white;">
			<ol id="selectable">
			</ol>
		</div>
	</form>
</div>

<script type="text/javascript">
	var start_time = $("#timepicker_starttime").val();
	var end_time = $("#timepicker_endtime").val();
	var selected = new Array();
	
	function dow_click() {
		if ($("#dow_0").is(":checked") || $("#dow_1").is(":checked") ||
		$("#dow_2").is(":checked") || $("#dow_3").is(":checked") ||
		$("#dow_4").is(":checked") || $("#dow_5").is(":checked") || $("#dow_6").is(":checked")) {
			$("#repeat_everyday_checkbox").attr('disabled', 'disabled');
			$("#repeat_once_checkbox").attr('disabled', 'disabled');
		} else {
			$("#repeat_everyday_checkbox").removeAttr('disabled');
			$("#repeat_everyday_checkbox").removeAttr('readonly');
			$("#repeat_once_checkbox").removeAttr('disabled');
			$("#repeat_once_checkbox").removeAttr('readonly');
		}
	}
	
	$( "#add_schedule" ).button().click(function() {
		ttime = moment($( "#add_sche_datepicker" ).datepicker('getDate')).format("YYYY-MM-DD");
		dtime = moment($( "#add_sche_datepicker" ).datepicker('getDate')).format(DATE_FORMAT);
		$('li[title=""]').remove();
		$('li[title="' + ttime + '"]').remove();
		$('#selectable').append('<li class="ui-widget-content" dtime="' + dtime + '" title="' + ttime + '">' + dtime + '</li>');
		
		var datelist = $('#selectable');
		var listitems = datelist.children('li').get();
		listitems.sort(function(a, b) {
		   return $(a).text().toUpperCase().localeCompare($(b).text().toUpperCase());
		})
		datelist.empty().append(listitems);
	});
	$( "#remove_schedule" ).button().click(function() {
		selected.forEach(function (item, index, array) {
			$('li[dtime="' + item + '"]').remove();
		});
	});;
	$("#file_area").hide();
	$("#tts_area").hide();

	$("#date_format").val(DATE_FORMAT);

	var repeat_type = $("#edited_repeat").val();
	if (repeat_type == "week")
		$("#enable_week").hide();
	else
		$("#enable_week").hide();		
	
	$("#date_format").change(function() {
		console.log("date format changed " + $("#date_format").val());
		dataString = '&dateformat='+ $("#date_format").val();
		DATE_FORMAT = $("#date_format").val();
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
		$.ajax({
				type:"POST",
				url: "/auth_admin/params/newSchedule/fw_events.php?action=setDateFormat",
				data: dataString,
				success:function(html){
					$( "#datepicker_startdate" ).datepicker( "option", "dateFormat", DATE_FICKER_FORMAT );
					$( "#datepicker_enddate" ).datepicker( "option", "dateFormat", DATE_FICKER_FORMAT );
					$( "#add_sche_datepicker" ).datepicker( "option", "dateFormat", DATE_FICKER_FORMAT );
					switch ($("#date_format").val()) 
					{
						case "YYYY-MM-DD" :
							$( "#datepicker_startdate" ).datepicker( "option", "defaultDate", '2000-01-01' );
							$( "#datepicker_enddate" ).datepicker( "option", "defaultDate", '2000-01-01' );
							$( "#add_sche_datepicker" ).datepicker( "option", "defaultDate", '2000-01-01' );
							break;
						case "MM-DD-YYYY" :
							$( "#datepicker_startdate" ).datepicker( "option", "defaultDate", '01-01-2000' );
							$( "#datepicker_enddate" ).datepicker( "option", "defaultDate", '01-01-2000' );
							$( "#add_sche_datepicker" ).datepicker( "option", "defaultDate", '01-01-2000' );
							break;
						case "DD-MM-YYYY" :
							$( "#datepicker_startdate" ).datepicker( "option", "defaultDate", '01-01-2000' );
							$( "#datepicker_enddate" ).datepicker( "option", "defaultDate", '01-01-2000' );
							$( "#add_sche_datepicker" ).datepicker( "option", "defaultDate", '01-01-2000' );
							break;
					}
					dtime = moment($( "#datepicker_startdate" ).datepicker('getDate')).format(DATE_FORMAT);
					$("#datepicker_startdate").datepicker('setDate', dtime); 
					dtime = moment($( "#datepicker_enddate" ).datepicker('getDate')).format(DATE_FORMAT);
					$("#datepicker_enddate").datepicker('setDate', dtime);
					dtime = moment($( "#add_sche_datepicker" ).datepicker('getDate')).format(DATE_FORMAT);
					$("#add_sche_datepicker").datepicker('setDate', dtime);
				}
		});
	});

	$("#timepicker_starttime, #timepicker_endtime ").change(function() {
		var s_time = $("#timepicker_starttime").val();
		var e_time = $("#timepicker_endtime").val();
		var s_first_part = s_time.substr(0, 2);
		var s_last_part = s_time.substr(-2);

		var e_first_part = e_time.substr(0, 2);
		var e_last_part = e_time.substr(-2);

		if (s_first_part < 00 || s_first_part > 23) {
			alert("Please Input Correct Begin Time");
			$("#timepicker_starttime").val("00:00");
			$("#timepicker_starttime").focus();
			return;
		}

		if (e_first_part < 00 || e_first_part > 23) {
			alert("Please Input Correct End Time");
			$("#timepicker_endtime").val("23:59");
			$("#timepicker_endtime").focus();
			return;
		}

		if (s_last_part < 00 || s_last_part > 59) {
			alert("Please Input Correct Begin Time");
			$("#timepicker_starttime").val("00:00");
			$("#timepicker_starttime").focus();
			return;
		}

		if (e_last_part < 00 || e_last_part > 59) {
			alert("Please Input Correct End Time");
			$("#timepicker_endtime").val("23:59");
			$("#timepicker_endtime").focus();
			return;
		}
	}); 
	
	function Display_action(){
		var action_type = $("#edited_progress").val();
		if (action_type == "MP3 Play"){
			$("#file_area").show();
			$("#tts_area").hide();
			$("#no_action").hide();
		}
//		else if (action_type == "TTS"){
//			$("#file_area").hide();
//			$("#tts_area").show();
//			$("#no_action").hide();
//		}
		else{
			$("#no_action").show();
			$("#file_area").hide();
			$("#tts_area").hide();
		}
	};
	
	function RepeatChange(){
		var r_type = $("#edited_repeat").val();
		if (r_type == "week"){
			$("#enable_week").hide();
		}
		else{
			$("#enable_week").hide();
		}
	};

	
	function FileSelect(){
		var action_type = $("#edited_progress").val();
		getPlayTime = true;
		if (action_type == "MP3 Play"){
			var fileName = $("#select_file").val();
			if (fileName == null)
				return;
			var s_time = $("#timepicker_starttime").val();
			var dataString = '&file_name='+ fileName;
			console.log("file select");
			$.ajax({
				type:"POST",
				url: "/auth_admin/params/newSchedule/fw_events.php?action=dur",
				data: dataString,
				success:function(html){
					if (html == "no file."){
						alert("You did not get the playing time from the file.");
					}
					else{
						console.log(html);
						var a = html.split(':');
						var a2 = s_time.split(':');
						repeatCnt = $("#edited_repeat_count").val();
						var gh = parseInt(a[0])*repeatCnt + parseInt(a2[0]);
						var gm = parseInt(a[1])*repeatCnt + parseInt(a2[1]);
						if (a[2] * repeatCnt > 0){
							leastMin = parseInt(a[2]*repeatCnt/60);
							if (leastMin == 0)
								leastMin = 1;
							gm = parseInt(gm + leastMin);
							if (gm > 59){
								gh = gh + parseInt(gm/60);
								gm = parseInt(gm%60);
							}
						}
						var eh = (gh < 10) ? '0'+gh : gh;
						var em = (gm < 10) ? '0'+gm : gm;
						$("#timepicker_endtime").val(eh + ":" + em);
						getPlayTime = false;
						if (addEventPending == true) {
							console.log("run pending add");
							addEventPending = false;
							addEvent(pendingStart, pendingEnd);
						} else if (updateEventPending == true) {
							console.log("run pending update");
							updateEventPending = false;
							var bln_correct = updateEvent(event);
							if(bln_correct) 
							{
								$('#error_message').html('');
								$( this ).dialog( "close" );
							}
						}
					}
				}
			});
		}
	}

	$("#edited_repeat_count").change(function() {
		if ($.isNumeric(this.value)) {
			if (this.value < 100) {
				FileSelect();
			} else {
				$("#edited_repeat_count").val(1);
			}
		} else {
			$("#edited_repeat_count").val(1);
		}
	});

	$( function() {
		$( "#selectable" ).selectable({
			stop: function() {
				var result = $( "#select-result" ).empty();
				$( ".ui-selected", this ).each(function() {
				var index = $( "#selectable li" ).index( this );
				result.append( " #" + ( index + 1 ) );
				});
			},
			selected: function( event, ui ) {
				//console.log(ui);
				//selected.push(ui.selected.innerText);
			},
			unselected: function( event, ui ) {
				//console.log(ui);
			},
			stop: function() {
				selected = [];
				$( ".ui-selected", this ).each(function() {
					selected.push(this.innerText);
				});
			}
		});
	});
</script>