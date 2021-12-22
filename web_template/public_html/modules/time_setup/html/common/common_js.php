<script type="text/javascript">
	$(document).ready(function() {
		var setupDay = $("#select_systemCheckDay :selected").val();
		var lastDay  = SetMaxDay($("#select_systemCheckYear :selected").val(), $("#select_systemCheckMonth :selected").val());

		var beforeTimeZoneIdx  = $("#select_timezoneList :selected").val();
	
		SetDayItems(lastDay);

		$("#select_systemCheckDay").val(setupDay);

		var timeFunc    = new TimeFunc();
		var displayFunc = new CommonDisplayFunc();

		var placeholderTarget = $('.textbox input[type="text"], .textbox input[type="password"]');

		placeholderTarget.on('focus', function() {
			$(this).siblings('label').fadeOut('fast');
		});

		placeholderTarget.on('focusout', function() {
			if( $(this).val() == '' ) {
				$(this).siblings('label').fadeIn('fast');
			}
		});

		placeholderTarget.on('textchange', function() {
			$(this).siblings('label').fadeOut('fast');
		});

		$("#radio_timeServerCustom").change(function(){
			$(".div_client_time").show();
			$(".div_table_visible").css("display", "none");
		});

		$("#select_systemCheckYear").change(function() {
			var lastDay = SetMaxDay($(this).val(), $("#select_systemCheckMonth").val());
			SetDayItems(lastDay);
		});

		$("#select_systemCheckMonth").change(function() {
			var lastDay = SetMaxDay($("#select_systemCheckYear").val(), $(this).val());
			SetDayItems(lastDay);
		});

		$('#radio_timeServerAuto').change(function(){
			$(".div_client_time").hide();
			$(".div_table_visible").css("display", "flex");
		});

		$("#div_button_apply, #div_button_syncTime").click(function() {
			displayFunc.showLoader();

			var enableStat 		= "on";
			var setTimeMethod 	= "auto";
			
			if( $("input[name=radio]:checked").attr("id") == "radio_timeServerCustom" ) {
				enableStat 		= "off";
				setTimeMethod	= "manual";
				
			} else {
				if( $(this).attr("id") == "div_button_syncTime" ) {
					setTimeMethod = "sync";
				}
			}

			var submitParams = "";
			submitParams += timeFunc.makeArgs("type",				"time");
			submitParams += timeFunc.makeArgs("act",  				"set_time");
			submitParams += timeFunc.makeArgs("timezone",  			$("#select_timezoneList :selected").html().split(")")[1].trim());
			submitParams += timeFunc.makeArgs("timezone_idx", 		$("#select_timezoneList :selected").val());
			submitParams += timeFunc.makeArgs("timeserver_enable", 	enableStat);
			submitParams += timeFunc.makeArgs("timeserver_url", 	encodeURIComponent($("#div_autoTime").val()));

			submitParams += timeFunc.makeArgs("setYear", 			$("#select_systemCheckYear :selected").val());
			submitParams += timeFunc.makeArgs("setMonth", 			$("#select_systemCheckMonth :selected").val());
			submitParams += timeFunc.makeArgs("setDay", 			$("#select_systemCheckDay :selected").val());
			submitParams += timeFunc.makeArgs("setHour", 			$("#select_systemCheckHour :selected").val());
			submitParams += timeFunc.makeArgs("setMinute", 			$("#select_systemCheckMinute :selected").val());
			submitParams += timeFunc.makeArgs("setSecond", 			$("#select_systemCheckSecond :selected").val());
			
			timeFunc.postTimeApply(setTimeMethod, beforeTimeZoneIdx, "<?=Time_setup\Def\PATH_TIME_PROCESS ?>", submitParams, procTimeSetResult);
			
			beforeTimeZoneIdx  = $("#select_timezoneList :selected").val();
		});

		timeFunc.printTime();
	});

	function SetMaxDay(year, month) {
		var lastDate = new Date(year, month, "");
		return lastDate.getDate();
	}

	function SetDayItems(lastDay) {
		$("#select_systemCheckDay").find("option").remove();
		var dayItems = "";
		for(var day = 0; day < lastDay; day++) {
			var tmpDay = AddZero(day+1);
			dayItems += '<option value = "' + tmpDay + '">' + tmpDay + '</option>';
		}
		$("#select_systemCheckDay").append(dayItems);
	}

	function AddZero(_num) {
		if( _num < 10 ) {
			_num = "0" + _num;
		}
		return _num;
	}
	
	function procTimeSetResult(_setTimeMethod, _beforeTimeZoneIdx, _data) {
		var displayFunc = new CommonDisplayFunc();
		var timeLog     = new CommonLogFunc("time_setup");
		
		var syncTime   = $("#present_time").html().trim();
		var syncUrl    = $("#div_autoTime").val();
		var applyLog   = syncUrl + " <?=Time_setup\Lang\LOG_DATE_APPLY_BODY ?> [<?=Time_setup\Lang\STR_DATE_SETUP_AUTO ?>] ";
		
		if( $("input[name=radio]:checked").attr("id") == "radio_timeServerCustom" ) {
			var selectTime = $("#select_systemCheckYear").val()
							 + "."   + $("#select_systemCheckMonth").val()
							 + "."   + $("#select_systemCheckDay").val()
							 + " / " + $("#select_systemCheckHour").val()
							 + ":"   + $("#select_systemCheckMinute").val()
							 + ":"   + $("#select_systemCheckSecond").val()

			applyLog = selectTime + " <?=Time_setup\Lang\LOG_DATE_APPLY_BODY ?> [<?=Time_setup\Lang\STR_DATE_SETUP_USER ?>] ";
		}
			
		var arr_sync_time = _data.split("|");
		$("#div_nextSyncTime").val(arr_sync_time[0]);

		var logMsg;
		if( arr_sync_time.length > 1 ) {
			logMsg = syncUrl + "<?=Time_setup\Lang\LOG_DATE_SYNC_FAIL ?>";

		} else {
			if( _setTimeMethod === "sync" ) {
				logMsg = syncTime + ", " + syncUrl + "<?=Time_setup\Lang\LOG_DATE_SYNC_TAIL ?>";

			} else {
				logMsg = applyLog + " <?=Time_setup\Lang\LOG_DATE_APPLY_TAIL ?>";
			}
		}
		
		timeLog.info(logMsg);
		
		if( $("#select_timezoneList :selected").val() != _beforeTimeZoneIdx ) {
			var beforeTimeZoneName = $("#select_timezoneList option[value='" + _beforeTimeZoneIdx + "']").html().split(")")[1].trim();
			
			timeLog.info("<?=Time_setup\Lang\LOG_DATE_ZONE_HEAD ?> ["
							+ beforeTimeZoneName
							+ "] <?=Time_setup\Lang\LOG_DATE_ZONE_BODY ?> ["
							+ $("#select_timezoneList :selected").html().split(")")[1].trim()
							+ "] <?=Time_setup\Lang\LOG_DATE_ZONE_TAIL ?>"
			);
		}

		if( arr_sync_time.length > 1 ) {
			alert(syncUrl + "<?=Time_setup\Lang\LOG_DATE_SYNC_FAIL ?>");
			displayFunc.hideLoader(-1, false);
			
		} else {
			displayFunc.hideLoader(5, false);	
		}
	}
	
	class TimeFunc {
		constructor() {
			this.timeLimit = 60;
			this.now       = this.getUnixTime();
			this.timeCnt   = 0;
		}

		makeArgs(_key, _value) {
			var args = "&" + _key + "=" + _value;

			return args;
		}

		postArgs(_target, _args) {
			var result;

			$.ajax({
				type	: "POST",
				url		: _target,
				data	: _args,
				async	: false,
				success	: function(data) {
					if( data != null ) {
						result = data;
					}
				}
			});

			return result;
		}

		postTimeApply(_setTimeMethod, _beforeTimeZone, _target, _args, _procFunc) {
			var self = this;
			var displayFunc = new CommonDisplayFunc();
			var result;

			$.ajax({
				type	: "POST",
				url		: _target,
				data	: _args,
				async	: true,
				success	: function(data) {
					if( data != null ) {
						self.timeCnt   = 1;
						self.timeLimit = 1;
						self.printTimeDiv("#span_menu_currentTime");
						self.printSyncTimeDiv("#div_nextSyncTime");

						setTimeout(function() {
							self.printTimeDiv("#span_menu_currentTime");
							self.timeLimit = 60;
							}, 5000);
						
						_procFunc(_setTimeMethod, _beforeTimeZone, data);
					}
				}
			});
		}

		getUnixTime() {
			var submitParams = "";
			submitParams += this.makeArgs("type",				"time");
			submitParams += this.makeArgs("act",  				"get_time");

			var rc = this.postArgs("<?=Time_setup\Def\PATH_TIME_PROCESS ?>", submitParams);

    		var t = rc.split(/[- :]/);
			var d = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
			var actiondate = new Date(d);

			return actiondate.getTime();
		}

		getSyncTime() {
			var submitParams = "";
			submitParams += this.makeArgs("type",				"time");
			submitParams += this.makeArgs("act",  				"get_syncTime");

			return this.postArgs("<?=Time_setup\Def\PATH_TIME_PROCESS ?>", submitParams);

		}

		addZero(_num) {
			if( _num < 10 ) {
				_num = "0" + _num;
			}

			return _num;
		}

		printTime() {
			if( this.timeCnt >= this.timeLimit ) {
				this.now     = this.getUnixTime();
				this.timeCnt = 0;

				this.printSyncTimeDiv("#div_nextSyncTime");
			}
			var now    = new Date(this.now);
			var year   = this.addZero(now.getFullYear());
			var month  = this.addZero((now.getMonth() + 1));
			var date   = this.addZero(now.getDate());
			var hour   = this.addZero(now.getHours());
			var minute = this.addZero(now.getMinutes());
			var second = this.addZero(now.getSeconds());

			$("#present_time").html(year + "." + month + "." + date + " / " + hour + ":" + minute + ":" + second);
			this.now += 1000;

			this.timeCnt++;

			var self = this;

			setTimeout(function() { self.printTime(); }, 1000);

			uptimeFunc.updateTime();
		}

		printSyncTimeDiv(_div) {
			$(_div).val(this.getSyncTime());

			return ;
		}

		printTimeDiv(_div) {
			var now    = new Date(this.getUnixTime());
			var year   = this.addZero(now.getFullYear());
			var month  = this.addZero((now.getMonth() + 1));
			var date   = this.addZero(now.getDate());
			var hour   = this.addZero(now.getHours());
			var minute = this.addZero(now.getMinutes());
			var second = this.addZero(now.getSeconds());

			$(_div).html(year + "." + month + "." + date + " / " + hour + ":" + minute + ":" + second);

			return ;
		}
	} // end of TimeFunc()
</script>

<?php
	include_once 'common_js_etc.php';
?>
