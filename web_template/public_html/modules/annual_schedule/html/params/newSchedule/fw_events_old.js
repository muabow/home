Calendar.defaultEventColor = '#3DA4B7';

$(document).ready(function() {
	
	var year_v = '/auth_admin/params/newSchedule/bootstrap/index.php';
	
	var applyToObject = function(event, result) {
		event.start 	 = result.start;
		event.end 		 = result.end;
		event._start 	 = result.start;
		event._end 		 = result.end;
		event._id 		 = result.id;
		event.time_start = result.time_start;
		event.time_end   = result.time_end;
		event.color		 = result.color;
       	event.allDay	 = result.allDay;
       	event.enable_week = result.enable_week;
       	event.repeat_cnt = result.repeat_cnt;
		event.auto_playback	= result.auto_playback;
		event.action_type = result.action_type;
		event.contents 	= result.contents;
       	return event;
	};


	$('#timepicker_starttime').timepicker({
		showPeriodLabels: false,
		hourText: str_hour,
    		minuteText: str_minute,
    		showCloseButton: true,       
		closeButtonText: str_save,      
		showNowButton: true,      
		nowButtonText: str_now,
    		hours: {
			starts: Calendar.timePickerMinHour,        
		        ends: Calendar.timePickerMaxHour       
		},
		minutes: {
		        starts: 0,                				
		        ends: 55,                 				
		        interval: Calendar.timePickerMinuteInterval        
		}
	});
	
	$("#timepicker_starttime").change(function () {
		FileSelect();
	})
	
	$('#timepicker_endtime').timepicker({
		showPeriodLabels: false,
		hourText: str_hour,
    		minuteText: str_minute,
    		showCloseButton: true,       		
		closeButtonText: str_save,    
		showNowButton: true,         		
		nowButtonText: str_now,
    		hours: {
		        starts: Calendar.timePickerMinHour,          
		        ends: Calendar.timePickerMaxHour      
		},
		minutes: {
			starts: 0,                			
			ends: 55,                 				
		 	interval: Calendar.timePickerMinuteInterval      
		}
	});

	$( "#datepicker_startdate" ).datepicker({
		dateFormat: DATE_FICKER_FORMAT
	});

	$( "#datepicker_enddate" ).datepicker({
		dateFormat: DATE_FICKER_FORMAT
	});

	$( "#add_sche_datepicker" ).datepicker({
		dateFormat: DATE_FICKER_FORMAT
	});

	//상세입력시 컬러코드 변환 RGB -> HEX
	var Rgb_Hex = {
		hexDigits : ["0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"],
		hex : function (x) {
			return isNaN(x) ? "00" : this.hexDigits[(x - x % 16) / 16] + this.hexDigits[x % 16];
		},
		rgb2hex : function(rgb) {
			rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
			return "#" + this.hex(rgb[1]) + this.hex(rgb[2]) + this.hex(rgb[3]);
		}
	}

	$('#ColorPicker1').empty().addColorPicker({
		clickCallback: function(elem,c) {
			$('#ColorSelectionTarget1').css('background-color',c);
			Calendar.defaultEventColor = elem.attr('color');
		}
	});

	$('#ColorSelectionTarget1').css('background-color',Calendar.defaultEventColor2);
	$('#ColorSelectionTarget1').css('background-color',Calendar.defaultEventColor);

	var calAlert = function() {
		$( "#dialog:ui-dialog" ).dialog( "destroy" );
		$( "#dialog-message" ).dialog({
			modal: true,
			resizable: false,
			autoOpen: true,
			buttons: {
				Ok: function() {
					$("#error_message").html("");
					$( this ).dialog( "close" );
				}
			}
		});
		$("#dialog-message").dialog("open");
	};
	
	var disableTimeCombos = function() {
		$('#timepicker_starttime').attr('disabled', 'disabled');
		$('#timepicker_endtime').attr('disabled', 'disabled');
	};
	
	var enableTimeCombos = function() {
		$('#timepicker_starttime').removeAttr('disabled');
		$('#timepicker_endtime').removeAttr('disabled');
	};
	
	$('#allday_checkbox').click(function(t){
		if(t.currentTarget.checked == false) {
			if($('#edited_autoplayback').is(":checked") ==  true){
				
			}
			else{
				$('#timepicker_endtime').removeAttr('disabled');
				$('#timepicker_endtime').removeAttr('readonly');
			}
			$('#timepicker_starttime').removeAttr('disabled');
			$('#timepicker_starttime').removeAttr('readonly');
			$('#edited_autoplayback').removeAttr('disabled');
		} else {
			$('#timepicker_starttime').attr('disabled', 'disabled');
			$('#timepicker_endtime').attr('disabled', 'disabled');
			$('#edited_autoplayback').attr('disabled', 'disabled');
		}
	});
	
	$('#edited_autoplayback').click(function(t){
		if(t.currentTarget.checked == false) {
			if($('#allday_checkbox').is(":checked") ==  true){

			} else {
				$('#timepicker_endtime').removeAttr('disabled');
				$('#timepicker_endtime').removeAttr('readonly');	
			}
		} else {
			$('#timepicker_endtime').attr('disabled', 'disabled');
		}
	});
/*
	$('#repeat_everyday_checkbox').click(function(t){
		if(t.currentTarget.checked == true) {
			$('#repeat_once_checkbox').attr('checked', false);
		}
	});

	$('#repeat_once_checkbox').click(function(t){
		if(t.currentTarget.checked == true) {
			$('#repeat_everyday_checkbox').attr('checked', false);
		}
	});
*/
	var addEvent = function(start, end) {
		var title = $('#edited_title')[0].value;
		var progress = $('#edited_progress')[0].value;
		var repeat = $('#edited_repeat')[0].value;
		var enable_week = $('#enable_week')[0].value;
		var repeat_cnt = $('#edited_repeat_count')[0].value;
		var equipment = $('#edited_equipment')[0].value;
		var option = $('#edited_option')[0].value;
		var priority = $('#edited_priority')[0].value;
		var area = $('#edited_area')[0].value;
		var color = $('#ColorSelectionTarget1').css('background-color');
		var noError = true;
		
		var fileType = 'none';
		var Contents = '';
		if (progress == "MP3 Play")
			fileType = "mp3";
		else if (progress == "TTS")
			fileType = "tts";
		
		//if (fileType == "mp3"){
			var selectedValues = $('#select_file').val();
			if (selectedValues == null) {
				alert("No MP3 file selected");
				return;
			}
			for (var i = 0; i < selectedValues.length; i++){
				Contents += selectedValues[i];
				if (i < (selectedValues.length-1))
					Contents += '|';
			}
		//}
//		else if (fileType == "tts"){
//			Contents = $('#edited_tts').val();
//		}
		
		var auto_playback = '';
		var repeatEveryday = '0';

		if($('#edited_autoplayback').is(':checked'))  
			auto_playback = $('#edited_autoplayback')[0].value; 
		
		if(title) 
		{
			dp_startdate 	= $( "#datepicker_startdate" ).datepicker('getDate');
			dp_enddate 	= $( "#datepicker_enddate" ).datepicker('getDate');

			starttime = $('#timepicker_starttime')[0].value;
			endtime = $('#timepicker_endtime')[0].value;
			
			if(dp_startdate !== null && dp_enddate !== null) 
			{
				start = dp_startdate;
				end = dp_enddate;
			}
				
			if($('#allday_checkbox').is(':checked')) 
			{
				startdate = moment(start).format('YYYY-MM-DD 00:00');
				enddate = moment(end).format('YYYY-MM-DD 23:59');
				allDay = '1';
			} else {
				startdate = moment(start).format('YYYY-MM-DD') + ' ' + $('#timepicker_starttime')[0].value;
				enddate = moment(end).format('YYYY-MM-DD') + ' ' + $('#timepicker_endtime')[0].value;
				allDay = '0';
			}
			dp_startdate.setHours($('#timepicker_starttime')[0].value.substr(0,2));
			dp_startdate.setMinutes($('#timepicker_starttime')[0].value.substr(3,2));
			dp_enddate.setHours($('#timepicker_endtime')[0].value.substr(0,2));
			dp_enddate.setMinutes($('#timepicker_endtime')[0].value.substr(3,2));
			
			var diff = new Date(dp_enddate - dp_startdate);
			var minutes = diff/1000/60;
			console.log(minutes);

			if (startdate > enddate)
				noError = false;
			
			if (repeat == "week") {
				if (minutes >= 7*24*60)
					noError = false;
			}
			
			if (repeat == "month") {
				if (minutes >= 31*24*60) {
					noError = false;
				} else if (dp_enddate.getMonth() > dp_startdate.getMonth()) {
					if (dp_enddate.getDay() > dp_startdate.getDay())
						noError = false;
				} else if (dp_enddate.getMonth() < dp_startdate.getMonth()){ // start December end January case
					if (dp_enddate.getDay() > dp_startdate.getDay())
						noError = false;
				}
			}

			if (repeat == "year") {
				if (minutes >= 365*24*60) {
					noError = false;
				} else if (dp_enddate.getYear() > dp_startdate.getYear()) {
					if (moment(start).format('MM-DD') < moment(end).format('MM-DD')) {
						noError = false;
					}
				}
			}

			if($('#repeat_everyday_checkbox').is(':checked')) {
				repeatEveryday = '1';
				if (starttime > endtime)
					noError = false;
			} else {
				repeatEveryday = '0';
			}

			if(noError) 
			{
				dataString = '&title='+ title +'&date_start='+ startdate + '&date_end='+ enddate + '&color=' + Calendar.defaultEventColor + '&allDay=' + allDay + '&progress=' + progress + '&repeat=' + repeat + '&enable_week=' + enable_week + '&repeat_cnt=' + repeat_cnt + '&auto_playback=' + auto_playback + '&equipment=' + equipment + '&option=' + option + '&priority=' + priority + '&area=' + area + '&color=' + color;
				dataString += '&action_type=' + fileType + '&contents=' + Contents + '&repeat_everyday=' + repeatEveryday;
				dataString += '&dow_0=' + $('#dow_0').is(':checked') + '&dow_1=' + $('#dow_1').is(':checked') + '&dow_2=' + $('#dow_2').is(':checked') + '&dow_3=' + $('#dow_3').is(':checked') + '&dow_4=' + $('#dow_4').is(':checked') + '&dow_5=' + $('#dow_5').is(':checked') + '&dow_6=' + $('#dow_6').is(':checked');
				console.log("submit");
				$.ajax({
					type:"POST",
					url: "/auth_admin/params/newSchedule/fw_events.php?action=add",
					data: dataString,
					dataType: 'json',
					success: function(event){
						$('#calendar').fullCalendar('refetchEvents');
						var iframe = document.getElementById('yearIframe');
						iframe.src = iframe.src;
						$("#dialog-message").dialog("close");
						$('#error_message').html("");
					},
					error: function(res) {
						//console.log("fail.option" + res);
						//$('#error_message').html(res.responseText);
						alert(res.responseText);
					}
				});

			}else{
				$('#error_message').html('Please Check the Schedule Time!');
		 	}
		}
	};

	var scheduleCopy = function(event) {
		var noError = true;
		var title = $('#copy_title')[0].value;

		var selectable = $('#selectable');
		var listitems = selectable.children('li').get();
		var listarr = new Array();
		listitems.forEach(function (item, index, array) {
			listarr.push(item.title);
		});
		var datelist = JSON.stringify(listarr);

		if(title != null && title != ''){

			if(noError) {
				// opslaan in db
				var dataString = '&event_id='+ event.id + '&title=' + title + '&datelist=' + datelist;
		           	$.ajax({
			        	type:"POST",
			            url: "/auth_admin/params/newSchedule/fw_events.php?action=scheduleCopy",
			            data: dataString,
			            dataType: 'json',
						success:function(result){
							//event = applyToObject(event, result.event);
							$('#calendar').fullCalendar('refetchEvents');
							var iframe = document.getElementById('yearIframe');
							iframe.src = iframe.src;
						}
			       	});
			          return true;
			} else {       
				$('#error_message').html('Please Check the Schedule Time!!');
           		 return false;
			}
		}
	};

	var updateEvent = function(event) {
	
		var title = $('#edited_title')[0].value;
		var noError = true;
		
		//event.content = $('#edited_content')[0].value;
		event.progress = $('#edited_progress')[0].value;
		event.repeat = $('#edited_repeat')[0].value;	
		event.enable_week = $('#enable_week')[0].value;
		event.repeat_cnt = $('#edited_repeat_count')[0].value;
		event.equipment = $('#edited_equipment')[0].value;	
		event.option = $('#edited_option')[0].value;
		event.priority = $('#edited_priority')[0].value;
		event.area = $('#edited_area')[0].value;
		var color = $('#ColorSelectionTarget1').css('background-color');

		if($('#edited_autoplayback').is(':checked'))  
			event.auto_playback = $('#edited_autoplayback')[0].value; 
		else 
			event.auto_playback = '';

		if($('#repeat_everyday_checkbox').is(':checked'))
			repeatEveryday = '1';
		else
			repeatEveryday = '0';
			
		var action_type = 'none';
		var action_contents = '';
		if (event.progress == "MP3 Play")
			action_type = "mp3";
		else if (event.progress == "TTS")
			action_type = "tts";
		
		if (action_type == "mp3"){
			var selectedValues = $('#select_file').val();
			if (selectedValues == null) {
				alert("No file selected");
				return;
			}
			
			for (var i = 0; i < selectedValues.length; i++){
				action_contents += selectedValues[i];
				if (i < (selectedValues.length-1))
					action_contents += '|';
			}
		}
//		else if (action_type == "tts"){
//			action_contents = $('#edited_tts').val();
//		}			
		event.action_type = action_type;
		event.contents = action_contents;
		if(title != null && title != ''){
			event.title = title;

			var dp_startdate 	= $( "#datepicker_startdate" ).datepicker('getDate');
			var dp_enddate 	= $( "#datepicker_enddate" ).datepicker('getDate');

			starttime = $('#timepicker_starttime')[0].value;
			endtime = $('#timepicker_endtime')[0].value;

			if(dp_startdate !== null && dp_enddate !== null){
				event.start = dp_startdate;
				event.end = dp_enddate;
			}
				
			if($('#allday_checkbox').is(':checked')){
				//var startdate = moment(event.start).format('YYYY-MM-DD HH:mm');
				//var enddate = event.end !== null ? moment(event.end).format('YYYY-MM-DD HH:mm') : startdate;
				var startdate = moment(event.start).format('YYYY-MM-DD 00:00');
				var enddate = event.end !== null ? moment(event.end).format('YYYY-MM-DD 23:59') : moment(event.start).format('YYYY-MM-DD 23:59');
				event.allDay = '1';
			} else {
				var startdate 	= moment(event.start).format('YYYY-MM-DD') + ' ' + $('#timepicker_starttime')[0].value;
				var enddate 	= event.end === null ? moment(event.start).format('YYYY-MM-DD') + ' ' + $('#timepicker_endtime')[0].value  : moment(event.end).format('YYYY-MM-DD') + ' ' + $('#timepicker_endtime')[0].value;
				event.allDay = '0';
			}

			var diff = new Date(dp_enddate - dp_startdate);
			var minutes = diff/1000/60;
			console.log(minutes);

			if (startdate > enddate)
				noError = false;
			
			if (event.repeat == "week") {
				if (minutes >= 7*24*60)
					noError = false;
			}
			
			if (event.repeat == "month") {
				console.log(dp_enddate.getDay() + " " +dp_startdate.getDay() );
				if (minutes >= 31*24*60) {
					noError = false;
				} else if (dp_enddate.getMonth() > dp_startdate.getMonth()) {
					if (moment(dp_enddate).format('DD') > moment(dp_startdate).format('DD'))
						noError = false;
				} else if (dp_enddate.getMonth() < dp_startdate.getMonth()){ // start December end January case
					if (moment(dp_enddate).format('DD') > moment(dp_startdate).format('DD'))
						noError = false;
				}
			}

			if (event.repeat == "year") {
				if (minutes >= 365*24*60) {
					noError = false;
				} else if (dp_enddate.getYear() > dp_startdate.getYear()) {
					if (moment(start).format('MM-DD') < moment(end).format('MM-DD')) {
						noError = false;
					}
				}
			}

			if($('#repeat_everyday_checkbox').is(':checked')) {
				repeatEveryday = '1';
				//if (starttime > endtime) // Block codes. REASON : Playing time 23:50 ~ 00:05 can't be set.
				//	noError = false;
			} else {
				repeatEveryday = '0';
			}

			if(noError) {
				// opslaan in db
				var dataString = '&event_id='+ event.id + '&allDay=' + event.allDay + '&title='+ event.title + '&date_start='+startdate+'&date_end='+enddate+ '&color=' + Calendar.defaultEventColor+ '&progress=' + event.progress + '&repeat=' + event.repeat + '&enable_week=' + event.enable_week + '&repeat_cnt=' + event.repeat_cnt + '&auto_playback=' + event.auto_playback + '&equipment=' + event.equipment + '&option=' + event.option + '&priority=' + event.priority + '&area=' + event.area;
				dataString += '&action_type=' + event.action_type + '&contents=' + event.contents + '&repeat_everyday=' + repeatEveryday;
				dataString += '&dow_0=' + $('#dow_0').is(':checked') + '&dow_1=' + $('#dow_1').is(':checked') + '&dow_2=' + $('#dow_2').is(':checked') + '&dow_3=' + $('#dow_3').is(':checked') + '&dow_4=' + $('#dow_4').is(':checked') + '&dow_5=' + $('#dow_5').is(':checked') + '&dow_6=' + $('#dow_6').is(':checked');
		           	$.ajax({
			        	type:"POST",
			            url: "/auth_admin/params/newSchedule/fw_events.php?action=update",
			            data: dataString,
			            dataType: 'json',
						success:function(result){
							//event = applyToObject(event, result.event);
							$('#calendar').fullCalendar('refetchEvents');
							var iframe = document.getElementById('yearIframe');
							iframe.src = iframe.src;
							$("#dialog-message").dialog("close");
							$('#error_message').html("");
						},
						error: function(res) {
							alert(res.responseText);
						}
			       	});
			} else {       
				$('#error_message').html('Please Check the Schedule Time!!');
           		 return false;
			}
		}
	};

	var selectedId;
	var selectedStart;
	var selectedEnd;
	var selectedRepeat;
	var selectedEveryday;
	//일정 입력
	var onSelectEvent = function(event, date, allDay, jsEvent, view) {
	//var onSelectEvent = function(event) {
		var dp_startdate 	= moment(date).format(DATE_FORMAT);
		var dp_enddate 	= moment(date).format(DATE_FORMAT);
		var dp_starttime 	= moment(date).format('HH:mm');
		var dp_endtime 	= moment(date).format('HH:mm');

		$("#dialog:ui-dialog").dialog( "destroy" );
		$('#edited_title')[0].value = '';
		//$('#edited_content')[0].value = '';
		$('#edited_progress')[0].value = '';
		$('#edited_repeat')[0].value = '';
		
		$('#enable_week')[0].value = 'every';
		$('#edited_repeat_count')[0].value = '1';
		$('#edited_equipment')[0].value = '127.0.0.1';
		$('#edited_option')[0].value = ' ';
		$('#edited_priority')[0].value = '1';
		$('#edited_area')[0].value = 'all';

		$("#datepicker_startdate").datepicker('setDate', dp_startdate); 
		$("#datepicker_enddate").datepicker('setDate', dp_enddate);
		updateRepeat();
		$('#edited_autoplayback').attr('checked', true);
		$('#repeat_once_checkbox').attr('checked', true);
		
		$("#repeat_everyday_checkbox").removeAttr('disabled');
		$("#repeat_everyday_checkbox").removeAttr('readonly');
		$("#repeat_once_checkbox").removeAttr('disabled');
		$("#repeat_once_checkbox").removeAttr('readonly');

		$('#dow_0').attr('checked', false);
		$('#dow_1').attr('checked', false);
		$('#dow_2').attr('checked', false);
		$('#dow_3').attr('checked', false);
		$('#dow_4').attr('checked', false);
		$('#dow_5').attr('checked', false);
		$('#dow_6').attr('checked', false);

		if(dp_starttime == '00:00' && dp_endtime == '23:59'){
			now = new Date();
			$('#timepicker_starttime')[0].value = moment(now).format('HH:00');
			$('#timepicker_endtime')[0].value	= moment(now).format('HH:00');
		} else {
			$('#timepicker_starttime')[0].value = dp_starttime;
			$('#timepicker_endtime')[0].value  = dp_endtime;
		}
	
		// default
		$('#allday_checkbox').attr('checked', false);

		enableTimeCombos();
		
		$("#no_action").show();
		$("#file_area").hide();
		$("#tts_area").hide();

		var start = dp_startdate + ' ' + $('#timepicker_starttime')[0].value;
		var end = dp_enddate + ' ' + $('#timepicker_endtime')[0].value;

		$("#dialog-message").dialog({
			modal: true,
			title: PopupTitle,
			closeOnEscape: true,
			open: function(event, ui) { 
      			$(".ui-dialog-titlebar-close", $(this).parent()).hide(); 
   			},
			height: 600,
			width: 400,
			resizable : false,
			buttons: 
			[{
		        		text: str_save,
		        		click: function (a,b) 
		        		{
			        		var title = $('#edited_title')[0].value;
							if(title != null && title != '') 
							{
								$('#error_message').html('');
								if (getPlayTime == true) {
									addEventPending = true;
									pendingStart = start;
									pendingEnd = end;
								}
								else
									addEvent(start, end);
								//if ($('#error_message').html() == "")
								//	$( this ).dialog( "close" );
							} else {
								alert("Please input the title.");
								$("#edited_title").focus();
								//$('#error_message').html('내용을 입력해주세요!');
							}
		        		}
			}, 
			{
		        		text: str_close,
		        		click: function () {
							$('#error_message').html('');
							$( this ).dialog( "close" );
				}
			}]
		});

		$('#timepicker_endtime').attr('disabled', 'disabled');
		$("#file_area option").removeAttr("selected");
		$("#dialog-message").dialog("open");
		
		$('#calendar').fullCalendar('unselect');
	};

	//일정 보기/수정
	var onClickEvent = function(event) {
		if(!event.editable){

		}else{
			startdate = event.start_base;

			if (event.day_of_week != undefined) {
				event.day_of_week.indexOf("0") != -1 ? $("#dow_0").attr('checked', true) : $("#dow_0").attr('checked', false);
				event.day_of_week.indexOf("1") != -1 ? $("#dow_1").attr('checked', true) : $("#dow_1").attr('checked', false);
				event.day_of_week.indexOf("2") != -1 ? $("#dow_2").attr('checked', true) : $("#dow_2").attr('checked', false);
				event.day_of_week.indexOf("3") != -1 ? $("#dow_3").attr('checked', true) : $("#dow_3").attr('checked', false);
				event.day_of_week.indexOf("4") != -1 ? $("#dow_4").attr('checked', true) : $("#dow_4").attr('checked', false);
				event.day_of_week.indexOf("5") != -1 ? $("#dow_5").attr('checked', true) : $("#dow_5").attr('checked', false);
				event.day_of_week.indexOf("6") != -1 ? $("#dow_6").attr('checked', true) : $("#dow_6").attr('checked', false);
			}
			
			if (isDOWCheck()) {
				$("#repeat_everyday_checkbox").attr('disabled', 'disabled');
				$("#repeat_once_checkbox").attr('disabled', 'disabled');
			} else {
				$("#repeat_everyday_checkbox").removeAttr('disabled');
				$("#repeat_everyday_checkbox").removeAttr('readonly');
				$("#repeat_once_checkbox").removeAttr('disabled');
				$("#repeat_once_checkbox").removeAttr('readonly');
			}

			//if(event.end === null)
			//	enddate = startdate;
			//else
				enddate = event.end_base
			$('#edited_title')[0].value = event.title;	         
			$('#copy_title')[0].value = event.title;   
//			$('#edited_equipment')[0].value = event.equipment;
			$('#edited_equipment')[0].value = "127.0.0.1";
			$('#edited_priority')[0].value = event.priority;
			$('#edited_repeat_count')[0].value = event.repeat_cnt;
			
			$("#edited_progress").val(event.progress); //select 처리
			$("#edited_repeat").val(event.repeat); // select 처리
			$("#enable_week").val(event.enable_week); // select 처리
			$("#edited_option").val(event.option); // select 처리
			$("#edited_area").val(event.area); // select 처리
			
			if(event.auto_yn){
				$('#edited_autoplayback').attr('checked', true);
			}
			else{
				$('#edited_autoplayback').attr('checked', false);
			}
			
			if(event.repeat_everyday == "1"){
				$('#repeat_everyday_checkbox').attr('checked', true);
			}
			else{
				$('#repeat_once_checkbox').attr('checked', true);
			}
			
			if (event.progress == "MP3 Play"){
				$("#file_area").show();
				$("#tts_area").hide();
				$("#no_action").hide();
				$("#edited_tts").val("");
				$("#file_area option").removeAttr("selected");
				var fileList = event.contents;
				$.each(fileList.split("|"), function(i,e){
   				$("#file_area option[value='" + e + "']").prop("selected", true);
				});
				
			}
//			else if (event.progress == "TTS"){
//				$("#file_area option").removeAttr("selected");
//				$("#file_area").hide();
//				$("#tts_area").show();
//				$("#no_action").hide();
//				$("#edited_tts").val(event.contents);
//			}
			else{
				$("#no_action").show();
				$("#file_area").hide();
				$("#tts_area").hide();
			}

			//$('#edited_id')[0].value = event.mb_id ;
			switch (DATE_FORMAT) 
			{
				case "YYYY-MM-DD" :
					$( "#datepicker_startdate" ).datepicker({
						dateFormat: DATE_FICKER_FORMAT
					});
					$( "#datepicker_enddate" ).datepicker({
						dateFormat: DATE_FICKER_FORMAT
					});
					$( "#add_sche_datepicker" ).datepicker({
						dateFormat: DATE_FICKER_FORMAT
					});
				break;
				case "MM-DD-YYYY" :
					$( "#datepicker_startdate" ).datepicker({
						dateFormat: DATE_FICKER_FORMAT
					});
					$( "#datepicker_enddate" ).datepicker({
						dateFormat: DATE_FICKER_FORMAT
					});
					$( "#add_sche_datepicker" ).datepicker({
						dateFormat: DATE_FICKER_FORMAT
					});
				break;
				case "DD-MM-YYYY" :
					$( "#datepicker_startdate" ).datepicker({
						dateFormat: DATE_FICKER_FORMAT
					});
					$( "#datepicker_enddate" ).datepicker({
						dateFormat: DATE_FICKER_FORMAT
					});
					$( "#add_sche_datepicker" ).datepicker({
						dateFormat: DATE_FICKER_FORMAT
					});
				break;
			}
			$("#datepicker_startdate").datepicker('setDate', moment(startdate).format(DATE_FORMAT));
			$("#datepicker_enddate").datepicker('setDate', moment(enddate).format(DATE_FORMAT));
			$("#add_sche_datepicker").datepicker('setDate', moment(enddate).format(DATE_FORMAT));

			$('#timepicker_starttime')[0].value = moment(startdate).format('HH:mm');
			$('#timepicker_endtime')[0].value = moment(enddate).format('HH:mm');

			updateRepeat();

			//상세입력,상세보기 링크,일정구분 일괄수정체크 노출
			more_view = function(href){
				window.location.href = href + event.id ;
			}
			$("#more_select").hide();
			$("#more_click").show();

			if(event.allDay){
				$('#timepicker_starttime')[0].value = moment(startdate).format('00:00');
				$('#timepicker_endtime')[0].value = moment(enddate).format('23:59');
				$('#allday_checkbox').attr('checked', true);
				$('#edited_autoplayback').attr('disabled', 'disabled');
				disableTimeCombos();
			}else{
				$('#timepicker_starttime')[0].value = moment(startdate).format('HH:mm');
				$('#timepicker_endtime')[0].value = moment(enddate).format('HH:mm');
				$('#allday_checkbox').attr('checked', false);
				enableTimeCombos();
				if(event.auto_yn){
					$('#timepicker_endtime').attr('disabled', 'disabled');
				}
				else{
					$('#timepicker_endtime').removeAttr('disabled');
					$('#timepicker_endtime').removeAttr('readonly');
				}
			}
        	if(event.color === ''){
				event.color = Calendar.defaultEventColor;
			}
			$('#ColorSelectionTarget1').css('background-color',event.color);
			Calendar.defaultEventColor = event.color;

			$("#dialog-message").dialog({
				modal: true,
//				title: PopupTitle + ' ' + event.name,
				title: PopupTitle,
				closeOnEscape: true,
				open: function(event, ui) { 
      				$(".ui-dialog-titlebar-close", $(this).parent()).hide(); 
   				},	
				height: 600,
				width: 420,
				resizable : false,
				buttons: 
				[{
					text: str_copy_only,
					style:"width:90",
					click: function (a,b) 
					{
						$("#dialog-copy").dialog({
							modal: true,
							title: PopupTitleCopy,
							closeOnEscape: true,
							open: function(event, ui) { 
								$(".ui-dialog-titlebar-close", $(this).parent()).hide(); 
							},	
							height: 600,
							width: 400,
							resizable : false,
							buttons: 
							[{
								text: str_save,
								click: function (a,b) 
								{
									var title = $('#copy_title')[0].value;

									selt = $('#selectable');
									if(title != null && title != '' && selt[0].childNodes.length != 0)
									{
										var bln_correct = scheduleCopy(event);
										if(bln_correct) 
										{
											$( this ).dialog( "close" );
										}
									}else{
										alert("Please input the title. or select date");
										$("#copy_title").focus();
									}
								}
							}, {
								text: str_close,
								click: function () {
									$(this).dialog( "close" );
								}
							}]
						});
						$('#selectable').empty();
						$("#dialog-copy").dialog("open");
						$( this ).dialog( "close" );
					}
				},{
					text: str_remove,
					style:"width:90",
					click: function (a,b) 
					{
						selectedId = event.id;
						selectedStart = event.start;
						selectedEnd = event.end;
						selectedRepeat = event.repeat;
						selectedEveryday = event.repeat_everyday;
						if(event.id){
							$( "#delete_schedule_dialog" ).dialog( "open" );
							$( "#delete_schedule_button" ).button();
							$( "#delete_schedule_cancel_button" ).button();
							return false;
						}
					}
				},{
					text: str_save,
					style:"width:90",
					click: function (a,b) 
					{
				 		var title = $('#edited_title')[0].value;

	                    if(title != null && title != '') 
						{
							if (getPlayTime == true) {
								updateEventPending = true;
							} else {
								var bln_correct = updateEvent(event);
								if(bln_correct) 
								{
									$('#error_message').html('');
									$( this ).dialog( "close" );
								}
							}
						}else{
							alert("Please input the title.");
							$("#edited_title").focus();
							//$('#error_message').html('Input the Contents!');
						}
					}
				}, {
			        text: str_close,
					style:"width:90",
			        click: function () {
						$('#error_message').html('');
			        	$(this).dialog( "close" );
					}
				}]
			});
			$("#dialog-message").dialog("open");
		}
	};

	function dateDiff(_date1, _date2) {
			dt1 = new Date(_date1);
			dt2 = new Date(_date2);
			return Math.floor((Date.UTC(dt2.getFullYear(), dt2.getMonth(), dt2.getDate()) - Date.UTC(dt1.getFullYear(), dt1.getMonth(), dt1.getDate()) ) /(1000 * 60 * 60 * 24));
	}

	function isDOWCheck() {
		return ($("#dow_0").is(":checked") || $("#dow_1").is(":checked") ||
			$("#dow_2").is(":checked") || $("#dow_3").is(":checked") ||
			$("#dow_4").is(":checked") || $("#dow_5").is(":checked") || $("#dow_6").is(":checked"));
	}
	
	$("#datepicker_startdate").change(function () {
		updateRepeat();
	})
	$("#datepicker_enddate").change(function () {
		updateRepeat();
	})

	function updateRepeat() {
		dp_startdate 	= $( "#datepicker_startdate" ).datepicker('getDate');
		dp_enddate 	= $( "#datepicker_enddate" ).datepicker('getDate');
		var diff = new Date(dp_enddate - dp_startdate);
		var days = diff/1000/60/60/24;
		if (days >= 7) {
			if ($("#edited_repeat")[0].value == "week")
				$("#edited_repeat")[0].value = "";
			$("#edited_repeat option[value='week']").prop("disabled",true);
			if (days >= 28) {
				if ($("#edited_repeat")[0].value == "month" || $("#edited_repeat")[0].value == "week")
					$("#edited_repeat")[0].value = "";
				$("#edited_repeat option[value='month']").prop("disabled",true);
				if (days >= 365) {
					if ($("#edited_repeat")[0].value == "year" || $("#edited_repeat")[0].value == "month" || $("#edited_repeat")[0].value == "week")
						$("#edited_repeat")[0].value = "";
					$("#edited_repeat option[value='year']").prop("disabled",true);
				} else {
					$("#edited_repeat option[value='year']").prop("disabled",false);
				}
			} else {
				$("#edited_repeat option[value='month']").prop("disabled",false);
				$("#edited_repeat option[value='year']").prop("disabled",false);
			}
		} else {
			$("#edited_repeat option[value='week']").prop("disabled",false);
			$("#edited_repeat option[value='month']").prop("disabled",false);
			$("#edited_repeat option[value='year']").prop("disabled",false);
		}
	}
	
	var onEventDropEvent = function(event) {

		event.auto_playback = $('#edited_autoplayback')[0].value;
		event.allDay = event.allDay ? 1 : 0;

		move_to = moment(event.start).format('YYYY-MM-DD'); // 옮긴 날짜
		move_from = moment(event.start._i).format('YYYY-MM-DD'); // 이전 날짜

		diff_date = dateDiff(move_from, move_to);
		if (event.repeat_everyday == "1" && diff_date != 0)
		{
			sd = new Date(event.start_base.substr(0,10)+event.start._i.substr(10,6));
			ed = new Date(event.end_base.substr(0,10)+event.end._i.substr(10,6));
			sd.setDate(sd.getDate()+diff_date);
			event_start = sd.setTime(sd.getTime());
			ed.setDate(ed.getDate()+diff_date);
			event_end = ed.setTime(ed.getTime());
		} else {
			event_start = event.start;
			event_end = event.end;
		}

		console.log(diff_date);

		startdate = moment(event_start).format('YYYY-MM-DD HH:mm');
		
		if(event.allDay == '1') {
			enddate  = moment(event_end).format('YYYY-MM-DD 23:59');
	    }
	    else{
	        enddate  = moment(event_end).format('YYYY-MM-DD HH:mm');
	    }

		dataString = '&event_id='+ event.id + '&allDay=' + event.allDay + '&title='+ event.title + '&date_start='+startdate+'&date_end='+enddate+ '&color=' + event.color+ '&progress=' + event.progress + '&repeat=' + event.repeat + '&enable_week=' + event.enable_week + '&repeat_cnt=' + event.repeat_cnt + '&auto_playback=' + event.auto_playback + '&equipment=' + event.equipment + '&option=' + event.option + '&priority=' + event.priority + '&area=' + event.area;
		dataString += '&action_type=' + event.action_type + '&contents=' + event.contents; 
		$.ajax({
			type:"POST",
			url: "/auth_admin/params/newSchedule/fw_events.php?action=resize",
			data: dataString,
			dataType: 'json',
			success:function(html){
				$('#calendar').fullCalendar('refetchEvents');
				//var iframe = document.getElementById('yearIframe');
				//iframe.src = iframe.src;
			}
		});
	};

	var onMouseoverEvent = function(me, event) {
/*
    		var str_time = event.id.split(" ");
			var time_1st = str_time[0];
			var time_2nd = str_time[1];
			var fix_time = time_2nd.split(":");
			var fixId = fix_time[0] + fix_time[1] + fix_time[2];
*/	
			var fixId = event.id;
			
    		if(event.editable && !me.hasClass('fc-agendaList-item')) {

			layer ='<div id="events-layer" class="fc-transparent" style="position:absolute; top:3px; right:2px; z-index:100">'
					+ '<span title="delete" id="delbut'+fixId+'" style="display:inline;"><img src="/auth_admin/params/newSchedule/img/icon_del.png"></span>'
					+ '</div>';

			me.append(layer);
			$("#delbut"+fixId).show();
			$("#delbut"+fixId).fadeIn(200);
			$("#delbut"+fixId).click(function() {

				selectedId = event.id;
				selectedStart = event.start;
				selectedEnd = event.end;
				selectedRepeat = event.repeat;
				selectedEveryday = event.repeat_everyday;

				if(event.id){
					$( "#delete_schedule_dialog" ).dialog( "open" );
					$( "#delete_schedule_button" ).button();
					$( "#delete_schedule_cancel_button" ).button();
					return false;
					/*if(!confirm(str_delete_confirm))
						return false;

					var dataString = '&event_id='+ event.id ;
					$.ajax({
				        type:"POST",
				        url: "/auth_admin/params/newSchedule/fw_events.php?action=del",
				        data: dataString,
				        success:function(html){
							$('#calendar').fullCalendar('refetchEvents');
							var iframe = document.getElementById('yearIframe');
							iframe.src = iframe.src;
						}
					});
					$('#calendar').fullCalendar('removeEvents', event.id); */
				}
			});
		}
	};
	
	$( "#delete_schedule_button" ).click(function() {
		console.log("delete_schedule_button click" + $('#this_schedule').is(':checked'));

		if($('#this_schedule').is(':checked') && (selectedRepeat != "" || selectedEveryday == "1" || isDOWCheck())) {
			var startDate = moment(selectedStart).format('YYYY-MM-DD HH:mm');
			var endDate = moment(selectedEnd).format('YYYY-MM-DD HH:mm');
			var dataString = '&event_id='+ selectedId + '&start_date=' + startDate + '&end_date=' + endDate;
			$.ajax({
				type:"POST",
				url: "/auth_admin/params/newSchedule/fw_events.php?action=del_this",
				data: dataString,
				success:function(html){
					$('#calendar').fullCalendar('refetchEvents');
					var iframe = document.getElementById('yearIframe');
					iframe.src = iframe.src;
				}
			});
		} else {
			var dataString = '&event_id='+ selectedId ;
			$.ajax({
				type:"POST",
				url: "/auth_admin/params/newSchedule/fw_events.php?action=del",
				data: dataString,
				success:function(html){
					$('#calendar').fullCalendar('refetchEvents');
					var iframe = document.getElementById('yearIframe');
					iframe.src = iframe.src;
				}
			});
			$('#calendar').fullCalendar('removeEvents', selectedId);
		}
		$("#delete_schedule_dialog").dialog( "close" );
		if ($("#dialog-message").dialog('isOpen'))
			$("#dialog-message").dialog("close");
	});
	$( "#delete_schedule_cancel_button" ).click(function() {
		console.log("delete_schedule_cancel_button click");
		$( "#delete_schedule_dialog" ).dialog( "close" );
	});

	$('#calendar').fullCalendar({
		customButtons: {
			Holiday: {
				text: str_holiday,
				click: function() {
					window.open('/auth_admin/params/newSchedule/holiday_list.php');
				}
			},
			Download: {
				text: str_download,
				click: function() {
					location.href=("/auth_admin/params/newSchedule/file_download.php");
				}
			},
			Upload: {
				text: str_upload,
				click: function() {
					$("#userfile").trigger('click');
				}
			},
			Copy_Schedule: {
				text: str_copy,
				click: function() {
					$( "#copymonthly_dialog" ).dialog( "open" );
				}
			},
			Yearly: {
				text: str_yearly,
				click: function() {
					$( "#year_view" ).dialog( "open" );
				}			
			}
		},
		header: {
			left: 'month,agendaWeek,agendaDay,Yearly',
			center: 'prev, title, next today',
			right: 'Holiday,Download,Upload,Copy_Schedule'
		},
		//timezone : 'local',
		nextDayThreshold: '00:00:00', //end 다음날 기준시간
		//defaultDate: '2015-02-12',
		//defaultTimedEventDuration: '02:00', //일정 기본 시간 간격
		displayEventEnd :true, //일정종료시각 표시
		minTime: '00:00',
		maxTime: '24:00',
		selectHelper: false,
		weekPrefix: '',
		showAgendaButton: true,
		editable: true,
		selectable: false,
		firstDay: 0, //일요일부터
		resizable: true,
		eventLimit: true, // allow "more" link when too many events
		views: {
			month: {
				eventLimit: 3
			},
			agendaWeek: {
				eventLimit: 3
			},
			agendaDay: {
				eventLimit: 3
			}
		},
		//aspectRatio: 1,
		height: 'auto',
		//contentHeight: '9999',
		weekNumbers: false,

		titleFormat: {
			month: 'YYYY. MM',
			week: 'YYYY.MM.DD',
			day: 'YYYY.MM.DD',
		},
		defaultView: calendar_defaultView,
		displayEventTime: true, //일정명앞 시간표시
		timeFormat: 'H:mm',
		//timeFormat: 'H:mm{-H:mm} ',
		axisFormat: 'H:mm',
		buttonText: {
			prev: '<',
			next: '>',
			prevYear: '&nbsp;&lt;&lt;&nbsp;',
			nextYear: '&nbsp;&gt;&gt;&nbsp;',
			today: str_today,
			month: str_monthly,
			week: str_weekly,
			day: str_daily,
		},
		
		events:
		{
			url: '/auth_admin/params/newSchedule/fw_events.php?action=get',
		    type: 'POST',
		    data: 
		    {
				m: m
		    }
		},
		
		eventSources: 
		[
			{
				url: '/auth_admin/params/newSchedule/fw_events.php?action=getHolidays&m='+m, 
				color: 'lightgray',   
				textColor:'red'
			},
			
		],

		dayClick: function(date, allDay, jsEvent, view) {
			onSelectEvent(event, date, allDay, jsEvent, view); 
		},

        eventClick: function(event, element) { 
			onClickEvent(event);
		},

        eventRender: function(event, element) {
			if (event.ranges) {
				console.log("st" + event.start.isAfter(event.ranges.start));
				console.log("ed" + event.end.isBefore(event.ranges.end));
				if (event.ranges.end && event.ranges.start) {
					return (event.start.isAfter(event.ranges.start) && event.end.isBefore(event.ranges.end));
				} else {
					return true;
				}
			} else {
				return true;
			}
		},

   		eventDrop: function(event, delta, revertFunc) {
			onEventDropEvent(event);
		},

        eventResize: function(event,dayDelta,minuteDelta,revertFunc) {
			onEventDropEvent(event);
		},

        eventMouseover: function(event, jsEvent, view) {
        	
			onMouseoverEvent($(this), event);
		},

		eventMouseout: function(calEvent, domEvent) {
			$("#events-layer").remove();
		},

		loading: function(bool) {
			if (bool) {
				$('#loading').show();
		    } else {
		       	$('#loading').hide();
		    }
		}
    });
});


