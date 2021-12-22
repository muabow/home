<script type="text/javascript">
	class SetupHandler {
		constructor() {		}

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

		checkIpAddr(_ipAddr) {
			if( /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(_ipAddr) ) {
				return true;

			} else {
				return false;
			}
		}

		checkPort(_port) {
			if( $.isNumeric(_port) && _port >= 1 && _port <= 65535 ) {
				return true;

			} else {
				return false;
			}
		}
	} // end of SetupHandler()

	function is_valid_port(_port) {
		if( $.isNumeric(_port) && _port >= 1 && _port <= 65535 ) {
			return true;

		} else {
			return false;
		}
	}

	function add_date_zero(_time) {
	    return (_time < 10 ) ? "0" + _time : _time;
	}

	function change_timestamp_to_date(_timestamp) {
	    var date = new Date(_timestamp * 1000);

	    var format_date = date.getFullYear().toString() + "."
	        + add_date_zero(date.getMonth() + 1) + "."
	        + add_date_zero(date.getDate().toString()) + " / "
	        + add_date_zero(date.getHours().toString()) + ":"
	        + add_date_zero(date.getMinutes().toString()) + ":"
	        + add_date_zero(date.getSeconds().toString());

	    return format_date;
	}

	function set_tab(_number) {
		var date = new Date();
		date.setDate(date.getDate() + 7);

		var tab_cookie = "";
		tab_cookie += "audio_tab=" + _number + ";";
   		tab_cookie += "Expires=" + date.toUTCString();
		document.cookie = tab_cookie;

		get_tab();
	}

	function get_tab() {
		var cookies = document.cookie.split(";");

		for(var i in cookies) {
        	if(cookies[i].search("audio_tab") != -1) {
        		var tab = $.trim(cookies[i].replace("audio_tab" + "=", ""));
        	}
		}

		var tab = ( tab == undefined ) ? 1 : tab;

		$("div[id^=tabs-]").hide();
		$(".tabs_list").removeClass("tabs_list_click");
		if( $("#tabs_list_" + tab).css("display") == "block" ) {
			$("#tabs_list_" + tab).addClass("tabs_list_click");
			$("#tabs-"+tab).show();
		} else {
			if ( tab == 1 ) {
				$("#tabs_list_2").addClass("tabs_list_click");
				$("#tabs-2").show();
			} else {
				$("#tabs_list_1").addClass("tabs_list_click");
				$("#tabs-1").show();
			}
		}
	}

	// document on ready
	$(document).ready(function() {
		var setup_handler 	  = new SetupHandler();
		var placeholderTarget = $('.div_contents_textbox input[type="text"], .div_contents_textbox input[type="password"]');

		commonFunc.resizeElement($(".div_contents_table_text"), 0, true);
		get_tab();
		$(".tabs_list").click(function() {
			var select_tab;

			var arr_id = $(this).attr("id").split("_");
			var number = arr_id[arr_id.length - 1];

			$(".tabs_list").removeClass("tabs_list_click");
			$(this).addClass("tabs_list_click");

			set_tab(number);

			commonFunc.resizeElement($(".label_class_radio"), 0, true);
			commonFunc.resizeElement($(".div_contents_table_text"), 0, true);

			return ;
		});

		placeholderTarget.on('focus', function() {
			$(this).siblings('label').fadeOut('fast');

			return ;
		});

		placeholderTarget.on('focusout', function() {
			if( $(this).val() == '' ) {
				$(this).siblings('label').fadeIn('fast');
			}

			return ;
		});

		placeholderTarget.on('textchange', function() {
			$(this).siblings('label').fadeOut('fast');

			return ;
		});

		return ;
	});
</script>

<?php
	include_once 'common_js_server.php';
	include_once 'common_js_client.php';
	include_once 'common_js_etc.php';
?>
