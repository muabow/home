<script type="text/javascript">
	$(document).ready(function() {
		// only server scope variables
		var json_alive_info;
		var json_oper_info;
		var json_client_list_info;

		var is_enable_multicast = "<?=$server_handler->is_enable_multicast(true) ?>";

		// setup page button
		$("#div_button_server_apply").click(function() {
			if( !is_valid_port($("#input_server_port").val()) ) {
				alert("<?=Source_file_setup\Lang\STR_JS_WRONG_PORT ?>");
				$("#input_server_port").focus();

				return ;
			}

			if( !confirm("<?=Source_file_setup\Lang\STR_JS_START_SERVER ?>") ) {

				return ;
			}

			var num_cast_checked = 0;
			$("input:checkbox[name=checkbox_cast_type]").each(function(index, item) {
				if( $(this).is(":checked") ) {
					num_cast_checked++;
				}
			});

			var cast_type = "all";
			if( num_cast_checked == 1 ) {
				cast_type = $("input:checkbox[name=checkbox_cast_type]:checked").val();
			}

			var arr_args = {};
			arr_args["network_cast_type"] 		= cast_type;
			arr_args["network_mcast_ip_addr"] 	= $("#input_server_ip_addr").val();
			arr_args["network_mcast_port"] 		= $("#input_server_port").val();
			arr_args["network_ucast_port"] 		= $("#input_server_port").val();

			var json_args = JSON.stringify(arr_args);
			ws_svr_ctrl_handler.send(0x31, json_args);

			return ;
		});

		$("input:checkbox[name=checkbox_cast_type]").change(function() {
			var is_checked = false;
			var last_value = $(this).val();

			$("input:checkbox[name=checkbox_cast_type]").each(function(index, item) {
				if( $(this).is(":checked") ) {
					is_checked = true;
				}
			});

			if( !is_checked ) {
				$("#checkbox_cast_" + last_value).prop("checked", true);
			}

			return ;
		});

		$("#checkbox_cast_multicast").change(function() {
			if( $(this).is(":checked") ) {
				$("#div_mcast_ip_addr").show();

			} else {
				$("#div_mcast_ip_addr").hide();
			}

			return ;
		})

		$("#div_button_server_cancel").click(function() {
			ws_svr_ctrl_handler.send(0x01, null);

			return ;
		});

		// operation page button
		$("#div_button_server_start").click(function() {
			ws_svr_ctrl_handler.send(0x03, null);

			return ;
		});

		$("#div_button_server_stop").click(function() {
			ws_svr_ctrl_handler.send(0x04, null);

			return ;
		});

		$("#div_button_server_setup").click(function() {
			ws_svr_ctrl_handler.send(0x30, null);

			return ;
		});


		// setup functions
		function setup_operation_info(_is_alive) {
			var data = json_oper_info;
			var cast_type = data.castType;

			// setup view
			if( cast_type == "all" ) {
				$("[id^=checkbox_cast_").prop("checked", true);

			} else {
				$("#checkbox_cast_" + cast_type).prop("checked", true);
			}

			if( is_enable_multicast == 0 ) {
				$("#checkbox_cast_unicast").prop("checked", true);
				$("#checkbox_cast_multicast").prop("checked", false);
			}
			$("#checkbox_cast_multicast").trigger("change");

			$("[id^=input_server_ip_addr]").trigger("textchange");
			$("[id^=input_server_port]").trigger("textchange");

			$("#input_server_ip_addr").val(data.ipAddr);
			$("#input_server_port").val(data.port);

			// operation view
			if( cast_type == "all" ) {
				cast_type = "Unicast/Multicast";

			} else {
				cast_type = cast_type.substring(0,1).toUpperCase() + cast_type.substring(1);
			}

			$("#radio_run_protocol_label").html("TCP/IP");
			$("#radio_run_castType_label").html(cast_type);

			var notice = ( cast_type != "Unicast" && cast_type != "-" ) ? "block" : "none";
			$(".div_no_support_for_multicast").css("display", notice);

			$("#input_server_run_ipAddr1").val(data.unicast_ip_addr);
			$("#input_server_run_port1").val(data.unicast_port);

			$("#input_server_run_ipAddr2").val(data.ipAddr);
			$("#input_server_run_port2").val(data.port);

			$("[id^=div_operation_view_]").hide();
			if( data.castType == "unicast" ) {
				$("#div_operation_view_unicast").show();

			} else if( data.castType == "multicast" ) {
				$("#div_operation_view_multicast").show();

			} else {
				$("[id^=div_operation_view_]").show();
			}


			return ;
		}

		function setup_client_list_info() {
			var data = json_client_list_info;

			// operation view
			if( typeof(data) == "undefined" || typeof(json_alive_info) == "undefined" ) {
				return ;
			}

			if( (json_alive_info.view == "operation")  ) {
				$("#table_server_connList").empty();

				var client_connect_list = "";
				var num_accrue	= data.accCount;
				var data_list	= JSON.parse(data.list);

				for( var idx = 0 ; idx < num_accrue ; idx++ ) {
					var client_info = data_list[idx];
					var is_alive	= (client_info.is_alive == 1 ? "Activate" : "Deactivate");
					var color		= (is_alive == "Activate" ? "green" : "red");
					var time_info	= (client_info.is_alive == 1 ? client_info.connect_time : client_info.disconnect_time);

					client_connect_list += '					\
						<div class="divTableRow">				\
							<div class="divTableData">			\
								' + (idx + 1) + '				\
							</div>								\
							<div class="divTableData">			\
								' + client_info.ip_addr  + '			\
							</div>								\
							<div class="divTableData">			\
								' + client_info.hostname + '			\
							</div>								\
							<div class="divTableData" style="color: ' + color + ';">	\
								' + is_alive + ' \
							</div>								\
							<div class="divTableData">			\
								' + change_timestamp_to_date(time_info) + '		\
							</div>								\
						</div>';
				}
				$("#table_server_connList").append(client_connect_list);
			}

			return ;
		}

		// source file management
		$(".div_source_control_display").marquee({
			allowCss3Support:true,
			css3easing:'linear',
			easing:'linear',
			speed: 50,
			delayBeforeStart: 0,
			direction: 'left',
			pauseOnHover: true
		});

		// 2. 모니터링 제어
		$(document).on("click", "[id^=button_monitor_]", function() {
			if( $(this).attr("class") ) {
				common_audio_handler.pause();
				common_audio_handler.currentTime = 0;

				$(this).removeClass("monitor_play");

				return ;
			}

			$("[id^=button_monitor_]").removeClass("monitor_play");

			$(this).addClass("monitor_play");

			var splitId  = $(this).attr("id").split("_");
			var index    = splitId[splitId.length - 1].trim();
			var fileName = $("#div_source_name_" + index).text().trim();
			var filePath = 'modules/source_file_management/html/data/audiofiles/' + fileName;
			
			var is_exist_ext = $(this).parent().parent().find(".div_is_ext_storage_file").length;

			if( is_exist_ext ) {
				var post_data = "";
				post_data += setup_svr_handler.makeArgs("act", "preview");
				post_data += setup_svr_handler.makeArgs("src", fileName);
				setup_svr_handler.postArgs("<?=Source_file_setup\Def\PATH_FILE_PROCESS ?>", post_data);

				filePath = 'modules/source_file_management/html/data/' + fileName;
			}

			common_audio_handler.setAttribute('preload', "none");
			common_audio_handler.setAttribute('src', 	 filePath);

			common_audio_handler.play();

			common_audio_handler.onended = function() {
				common_audio_handler.pause();
				common_audio_handler.currentTime = 0;

				$("[id^=button_monitor_]").removeClass("monitor_play");
			};

			return ;
		});

		// control panel
		// - 0x19 : force stop
		// - 0x20 : reload list
		// - 0x21 : select play

		var status_run_button 	= "";
		var data_run_button		= "";
		$(document).on("click", ".div_is_play_list_selected", function() {
			if( json_alive_info.stat == 0 ) {
				alert("<?=Source_file_setup\Lang\STR_SERVER_SERVER_NOT_RUN ?>");

				return ;
			}
			
			var splitId  = $(this).attr("id").split("_");
			var index    = splitId[splitId.length - 1].trim();

			var arr_args = {};
			arr_args["play_index"] = index;
								
			var json_args = JSON.stringify(arr_args);
			ws_svr_ctrl_handler.send(0x21, json_args);

			return ;
		});
		
		setSortableEvent();

		$(".input_source_check_all").click(function() {
			var is_checked = $(this).is(":checked");

			$(".input_source_check").prop("checked", is_checked);

			gCheckSourceList = [];
			$(".input_source_check").each(function(i, e) {
				if ( $(e).is(":checked") ) {
					var check_id = $(e).closest(".div_table_row").attr("id");
					gCheckSourceList.push(check_id);
				}
			});
		});

		$(document).on("click", ".input_source_check", function() {
			if ( !$(this).is(":checked") ) {
				$(".input_source_check_all").prop("checked", false);
			}

			gCheckSourceList = [];
			$(".input_source_check").each(function(i, e) {
				if ( $(e).is(":checked") ) {
					var check_id = $(e).closest(".div_table_row").attr("id");
					gCheckSourceList.push(check_id);
				}
			});
		});

		$("#control_button_play").click(function() {
			if( json_alive_info.stat == 0 ) {
				alert("<?=Source_file_setup\Lang\STR_SERVER_SERVER_NOT_RUN ?>");

				return ;
			}

			var checked_source = [];
			var checked_loop_count = [];

			$(".input_source_check").each(function(i, e) {
				if( $(e).is(":checked") ) {
					checked_source.push($(e).closest(".div_table_row").attr("id"));
					var loop_count = parseInt($(e).closest(".div_table_row").children(".div_title_loop_count").children(".input_loop_count").val());
					if( loop_count <= 0 ) loop_count = 1;
					if( loop_count > 99 ) loop_count = 99;

					checked_loop_count.push(loop_count);
				}
			});

			$(".input_source_check").prop("checked", false);
			$(".input_source_check_all").prop("checked", false);
			gCheckSourceList = [];
			
			var arr_args = {};
			arr_args["num_source_list"] 	= checked_source.length;
			arr_args["source_hash_id"] 		= checked_source.join();
			arr_args["source_loop_count"] 	= checked_loop_count.join();
								
			var json_args = JSON.stringify(arr_args);
			ws_svr_ctrl_handler.send(0x10, json_args);

			return ;
		});

		$("#control_button_pause").click(function() {
			ws_svr_ctrl_handler.send(0x11, null);
			return ;
		});

		$("#control_button_stop").click(function() {
			ws_svr_ctrl_handler.send(0x12, null);
			return ;
		});

		$("#control_button_prev").click(function() {
			ws_svr_ctrl_handler.send(0x13, null);
			return ;
		});

		$("#control_button_next").click(function() {
			ws_svr_ctrl_handler.send(0x14, null);
			return ;
		});

		$("#control_button_loop").click(function() {
			ws_svr_ctrl_handler.send(0x15, null);
			return ;
		});

		$("#control_button_download").click(function() {
			var checked_source = [];

			$(".input_source_check").each(function(_i, _e) {
				if( $(_e).is(":checked") ) {
					var source_name = $(_e).closest(".div_table_row").find(".div_source_name").html();
					var file_path = 'modules/source_file_management/html/data/audiofiles/' + source_name;

					var download_link = $("<a>").attr("href", file_path).attr("download", source_name).appendTo("body");
					download_link[0].click();
					download_link.remove();
				}
			});

			$(".input_source_check_all").prop("checked", false);
			$(".input_source_check").prop("checked", false);

			return ;
		});

		$(document).on('keypress', '.input_loop_count', function(evt) {
			evt.preventDefault();

		    return ;
		});

		$(document).on("mouseover", ".overflow_ellipsis", function(){
			var text = $(this).html();
			var tooltip = '<div class="source_name_tooltip"><p>' + text + '</p></div>';
			$('html').append(tooltip);

			var divLeft = $(this).offset().left;
			var divTop = $(this).offset().top + 25;

			$('.source_name_tooltip').css({
				"top": divTop,
				"left": divLeft
			});
		});

		$(document).on("mouseout", ".overflow_ellipsis", function(){
			$(".source_name_tooltip").remove();
		});

		$(".div_row_wrap").scroll(function() {
			$(".source_name_tooltip").remove();
		});

		var gSourceOrderList = [];
		var gCheckSourceList = [];

		function pad(_n, _width) {
			_n = _n + '';
			return _n.length >= _width ? _n : new Array(_width - _n.length + 1).join('0') + _n;
		}
		
		function setSortableEvent() {
			$("#sortable").sortable({
				stop: function(event, ui) {
					update_source_order();
				}
			});
		}
		
		function update_source_order() {
			$("[id^=button_monitor_]").each(function() {
				if( $(this).attr("class") ) {
					common_audio_handler.pause();
					common_audio_handler.currentTime = 0;

					$(this).removeClass("monitor_play");
				 }
			});

			var update_order_list = [];
			$(".div_table_row").each(function(i, e) {
				update_order_list.push($(e).attr("id"));
			});

			if( JSON.stringify(gSourceOrderList) == JSON.stringify(update_order_list) ) {
				return ;
			}

			var arr_args = {};
			arr_args["source_hash_id"] = update_order_list.join();
								
			var json_args = JSON.stringify(arr_args);
			ws_svr_ctrl_handler.send(0x17, json_args);
			
			return ;
		}

		function set_table(_data) {
			var num_play_monitor = -1;

			$("[id^=button_monitor_]").each(function() {
				if( $(this).attr("class") ) {
					num_play_monitor = $(this).attr("id").split("_")[2];
				 }
			});
			
			$( "#sortable" ).sortable("destroy");
			
			var source_list = JSON.parse(_data.source_list);

			var row_data = "";
			gSourceOrderList = [];
			$.each(source_list, function(i, source_info) {

				var play_time_min = pad(Math.floor(source_info.audio_play_time / 60), 2);
				var play_time_sec = pad(source_info.audio_play_time % 60, 2);
				var play_time = play_time_min + ':' + play_time_sec;

				var is_play_list = ( source_info.is_playlist ) ? "div_is_play_list_selected" : "div_is_play_list";
				var is_checked = ( $.inArray(source_info.source_hash_id, gCheckSourceList) != -1 ) ? "checked" : "";
				var is_valid = source_info.is_valid_source;

				var str_source_info = "";
				if( source_info.source_type == "wav" ) {
					str_source_info = (source_info.num_sample_rate /1000);
					str_source_info = str_source_info.toFixed(1);
					str_source_info = str_source_info + "Khz";

				} else {
					str_source_info = parseInt(source_info.num_bit_rate / 1000);
					str_source_info = str_source_info + "Kbps";
				}
				var str_channel_info = ((source_info.num_channels == 1) ? "MONO" : "STEREO");
				var str_ext_type 	 = source_info.source_type.toUpperCase();
				
				var is_ext_storage_file = "div_is_ext_storage_file_empty";
				if( source_info.is_ext_storage ) {
					is_ext_storage_file = "div_is_ext_storage_file";
				}

				var str_monitor_class = "";
				if( num_play_monitor == i ) {
					str_monitor_class = 'class="monitor_play"';
				}

				row_data += '<div class="div_table_row ' + (( !is_valid ) ? "is_valid" : "") + '" id="' + source_info.source_hash_id + '">';
				row_data += '	<div class="div_title_number"><button id="button_monitor_' + i + '" ' + str_monitor_class + ' style="width : 36px; border : solid 1px grey; cursor : pointer;" ' + (( is_valid ) ? 'style="cursor: pointer;"' : 'disabled') + '>' + (i + 1) + '</button></div>';
				row_data += '	<div class="div_title_source_name"  style="font-size: 8pt;">';
				row_data += '		<div class="' + is_play_list + '" id="div_play_list_' + i + '"></div>';
				row_data += '		<div class="' + is_ext_storage_file + '"></div>';
				row_data += '		<div class="div_source_name" id="div_source_name_' + i + '">' + source_info.source_name + '</div>';
				row_data += '	</div>';
				row_data += '	<div class="div_title_source_type"  style="font-size: 8pt;">' + str_ext_type + '</div>';
				row_data += '	<div class="div_title_source_info"  style="font-size: 8pt;">' + str_source_info + '</div>';
				row_data += '	<div class="div_title_channel"  style="font-size: 8pt;">' + str_channel_info + '</div>';
				row_data += '	<div class="div_title_play_time"  style="font-size: 8pt;">' + play_time + '</div>';
				row_data += '	<div class="div_title_loop_count"  style="font-size: 8pt;"><input type="number" class="input_loop_count" min="1" max="99" maxLength="2" value="' + source_info.audio_loop_count + '" ' + (( !is_valid ) ? "disabled" : "") + '></div>';
				row_data += '	<div class="div_title_checkBox"  style="font-size: 8pt;"><input type="checkbox" class="input_source_check" ' + is_checked + '></div>';
				row_data += '</div>';

				gSourceOrderList.push(source_info.source_hash_id);
			});

			$(".div_row_wrap").empty();
			$(".div_row_wrap").append(row_data);
			
			$(".div_source_name").each(function(i, e) {
				if ($(e)[0].scrollWidth > $(e).innerWidth()) {
					$(e).addClass("overflow_ellipsis");
				}
			});
			
			setSortableEvent();
			
			return ;
		}


		// websocket event handler functions
		function ws_open_server_func(_this) {
			_this.send(0x01, null);

			return ;
		}

		function ws_recv_server_func(_cmd_id, _is_binary, _length, _data, _this) {
			if( _cmd_id == 1 && _data == null ) return ;
			var data = JSON.parse(_data);

			switch( parseInt(_cmd_id) ) {
				case 0x01   : // alive info
					json_alive_info = data;

					if( json_alive_info.view == "setup" ) {
						$("#div_display_server_setup").show();
						$("#div_display_server_operation").hide();

						$("[id^=div_server_operation_]").hide();
						$("#div_server_operation_wait").show();
					} else {
						$("#div_display_server_operation").show();
						$("#div_display_server_setup").hide();

						if( json_alive_info.stat == 1 ) {
							// operation run
							$("[id^=div_server_operation_]").hide();
							$("#div_server_operation_run").show();

						} else {
							// operation stop
							$("[id^=div_server_operation_]").hide();
							$("#div_server_operation_stop").show();
						}
						setup_client_list_info();
					}

					setup_operation_info(json_alive_info.stat);

					<?php
						if( $is_enable_server ) {
							echo '
							$("#div_contents").show();
							$("#div_loader").hide();
							';
						}
					?>

					break;

				case 0x02  : // operation info
					json_oper_info = data;
					break;

				case 0x03 : // client list info
					json_client_list_info = data;
					setup_client_list_info();
					break;

				case 0x12 : // operation info
					var index = data.audio_play_index;
					var source_name = $("#div_source_name_" + index).html();

					if( data.is_play == 1 && data.is_pause == 0 ) {
						$("#control_button_play").hide();
						$("#control_button_pause").show();

						$("#div_play_list_" + data.audio_play_index).removeAttr('class');
						$("#div_play_list_" + data.audio_play_index).addClass("div_is_play_list_play");
						$("#div_current_play_source").html(source_name);

					} else if( data.is_play == 1 && data.is_pause == 1 ) {
						$("#control_button_pause").hide();
						$("#control_button_play").show();

						$("#div_play_list_" + data.audio_play_index).removeAttr('class');
						$("#div_play_list_" + data.audio_play_index).addClass("div_is_play_list_pause");

						$("#div_current_play_source").html(source_name);

					} else if( data.is_play == 0 ) {
						$("#control_button_pause").hide();
						$("#control_button_play").show();

						$("#div_current_play_source").html("");
					}

					if( data.is_loop == 1 ) {
						$("#control_button_loop").removeClass("div_control_button_loop").addClass("div_control_button_loop_reverse");


					} else {
						$("#control_button_loop").removeClass("div_control_button_loop_reverse").addClass("div_control_button_loop");
					}

					break;

				case 0x10 :
					set_table(data);
			
					common_display_handler.clearDotLoader();

					break;

				case 0x26 :
					common_display_handler.showLoader();
					break;

				case 0x27 :
					common_display_handler.clearDotLoader();
					break;
			}

			return ;
		}

		var common_audio_handler	= document.createElement('audio');
		var common_display_handler	= new CommonDisplayFunc();

		// websocket instance
		var ws_server_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "source_file_server");
		ws_server_handler.setOnmessageHandler(ws_recv_server_func);
		ws_server_handler.run();

		var ws_svr_ctrl_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "source_file_server_control");
		ws_svr_ctrl_handler.setOnopenHandler(ws_open_server_func);
		ws_svr_ctrl_handler.run();

		var setup_svr_handler = new SetupHandler();
	});

</script>
