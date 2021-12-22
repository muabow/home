<script type="text/javascript">
	$(document).ready(function() {
		// only server scope variables
		var json_alive_info;
		var json_oper_info;
		var json_client_list_info;

		var status_run_button = "";

		var is_enable_multicast = "<?=$server_handler->is_enable_multicast(true) ?>";
		
		var audio_server_eq_func = new AudioEqulizerFunc("outputVolume_server");

		// setup functions
		function setup_operation_info(_is_alive) {
			var data = json_oper_info;
			var cast_type = data.castType;

			// setup view
			$("input:radio[name=radio_encodeType][value=" + (data.mp3_mode == 0 ? "pcm" : "mp3") + "]").prop('checked', true);
			$("#select_sampleRate").val(data.sampleRate);
			$("#select_channels").val(data.channels);

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

			if( data.mp3_mode == 0 ) {
				$("#radio_encode_pcm").trigger("click");

			}  else {
				$("#radio_encode_mp3").trigger("click");
			}

			$("#select_mp3_sampleRate").val(data.sampleRate);
			$("#select_mp3_channels").val(data.channels);
			$("#select_mp3_quality").val(data.mp3_quality);

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
			$("#radio_run_encode_label").html((data.mp3_mode == 0 ? "PCM" : "MP3"));

			var notice = ( cast_type != "Unicast" && cast_type != "-" ) ? "block" : "none";
			$(".div_no_support_for_multicast").css("display", notice);

			$("#select_run_sampleRate").val(data.sampleRate);
			$("#select_sampleRate").val(data.sampleRate);
			$("#select_run_channels").val(data.channels);
			$("#select_run_mp3_channels").val(data.channels);

			$("#select_run_mp3_quality").val(data.mp3_quality);
			$("#select_run_mp3_channels").val(data.channels);
			$("#select_run_mp3_sampleRate").val(data.sampleRate);

			if( data.mp3_mode == 0 ) {
				$(".div_server_run_mp3").hide();
				$(".div_server_run_pcm").show();
			}  else {
				$(".div_server_run_mp3").show();
				$(".div_server_run_pcm").hide();
			}

			$("#select_run_mp3_quality").val(data.mp3_quality);

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


		// websocket event handler functions
		function ws_open_server_func(_this) {
			_this.send(0x01, null);

			return ;
		}

		function ws_recv_server_func(_cmd_id, _is_binary, _length, _data, _this) {
			if( _cmd_id == 1 && _data == null ) return ;
			var data = JSON.parse(_data);
			
			switch( parseInt(_cmd_id) ) {
				case 1   : // alive info
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

				case 10  : // operation info
					json_oper_info = data;
					break;
				
				case 11	 : // volume info
					if( g_num_server_volume > 0 ) {
						g_num_server_volume--;
						break;
					}

					$("#slider_server_value").val(data.volume);
					$(".slider_server").val(data.volume);

					break;

				case 12  : // level info
					if( data.level == 0 ) 		data_level = 0 ;
					else if( data.level > 40 ) 	data_level = 10;
					else data.level = (data.level / 4);

					$(".level_outputVolume_server").html(data.level);

					break;

				case 21 : // client list info
					json_client_list_info = data;
					setup_client_list_info();
					break;
			}

			return ;
		}

		// setup page button
		$("#div_button_server_apply").click(function() {
			if( !is_valid_port($("#input_server_port").val()) ) {
				alert("<?=Audio_setup\Lang\STR_JS_WRONG_PORT ?>");
				$("#input_server_port").focus();

				return ;
			}

			if( !confirm("<?=Audio_setup\Lang\STR_JS_START_AUDIO_SERVER ?>") ) {

				return ;
			}

			var encode_type 		= $("[name=radio_encodeType]:checked").val();
			var audio_sample_rate	= $("#select_sampleRate").val();
			var audio_channels		= $("#select_channels").val();

			if( encode_type == "mp3" ) {
				audio_sample_rate	= $("#select_mp3_sampleRate").val();
				audio_channels		= $("#select_mp3_channels").val();
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

			var setup_handler = new SetupHandler();

			var args = "";
			args += setup_handler.makeArgs("type", "audio");
			args += setup_handler.makeArgs("act",  "operation_status");
			args += setup_handler.makeArgs("mode",  "server");
			args += setup_handler.makeArgs("sample_rate", audio_sample_rate);
			
			var json_data = JSON.parse(setup_handler.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", args));
			if( json_data.result != "ok" ) {
				alert("<?=Audio_setup\Lang\STR_MATCH_SAMPLE_RATE_CLIENT ?>");
				return ;
			}

			status_run_button = "apply";
			ws_sndif_svr_handler.send(0x02, "audio_server_control");

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
			var setup_handler = new SetupHandler();

			var args = "";
			args += setup_handler.makeArgs("type", "audio");
			args += setup_handler.makeArgs("act",  "operation_status");
			args += setup_handler.makeArgs("mode", "server");
			args += setup_handler.makeArgs("sample_rate", 0);
			
			var json_data = JSON.parse(setup_handler.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", args));
			if( json_data.result != "ok" ) {
				alert("<?=Audio_setup\Lang\STR_MATCH_SAMPLE_RATE_CLIENT ?>");
				return ;
			}

			status_run_button = "run";
			ws_sndif_svr_handler.send(0x02, "audio_server_control");

			return ;
		});

		$("#div_button_server_stop").click(function() {
			ws_svr_ctrl_handler.send(0x12, null);

			return ;
		});

		$("#div_button_server_setup").click(function() {
			ws_svr_ctrl_handler.send(0x20, null);

			return ;
		});

		$("#slider_server_value").keydown(function(_evt) {
			if( _evt.keyCode == 13 ) {
				$("#div_button_apply_server_volume").trigger("click");
			}

			return ;
		});

		var g_num_server_volume = 0;
		$("#div_button_apply_server_volume").click(function() {
			var volume = $("#slider_server_value").val();

			if( !$.isNumeric(volume) || volume > 100 || volume < 0 ) {
				volume = $("#slider_server_volume").val();
			}
			volume = parseInt(volume);

			$("#slider_server_value").val(volume);
			$("#slider_server_volume").val(volume);

			var arr_args = {};
			arr_args["audio_volume"] = volume;
					
			g_num_server_volume++;
			var json_args = JSON.stringify(arr_args);
			ws_svr_ctrl_handler.send(0x15, json_args);

			return ;
		});

		$("#slider_server_volume").change(function() {
			var volume = $(this).val();
			$("#slider_server_value").val(volume);

			var arr_args = {};
			arr_args["audio_volume"] = volume;

			g_num_server_volume++;	
			var json_args = JSON.stringify(arr_args);
			ws_svr_ctrl_handler.send(0x15, json_args);

			return ;
		});

		function ws_recv_sndif_svr_func(_cmd_id, _is_binary, _length, _data, _this) {
			if( _cmd_id == 0x02 ) {
				if( status_run_button == "apply" || status_run_button == "operation" ) {
					var encode_type 		= $("[name=radio_encodeType]:checked").val();
					var audio_sample_rate	= $("#select_sampleRate").val();
					var audio_channels		= $("#select_channels").val();

					if( encode_type == "mp3" ) {
						audio_sample_rate	= $("#select_mp3_sampleRate").val();
						audio_channels		= $("#select_mp3_channels").val();
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
					arr_args["audio_encode_type"] 		= $("input:radio[name=radio_encodeType]:checked").val();
					arr_args["network_cast_type"] 		= cast_type;
					arr_args["audio_pcm_sample_rate"] 	= audio_sample_rate;
					arr_args["audio_pcm_channels"] 		= audio_channels;
					arr_args["network_mcast_ip_addr"] 	= $("#input_server_ip_addr").val();
					arr_args["network_mcast_port"] 		= $("#input_server_port").val();
					arr_args["network_ucast_port"] 		= $("#input_server_port").val();
					arr_args["audio_mp3_quality"] 		= $("#select_mp3_quality").val();
					arr_args["audio_volume"]			= $("#slider_server_value").val();
					
					var json_args = JSON.stringify(arr_args);
					ws_svr_ctrl_handler.send(0x21, json_args);

				} else if( status_run_button == "run" ) {
					ws_svr_ctrl_handler.send(0x11, null);
				}

				status_run_button = "";
			}

			return ;
		}

		// websocket instance
		var ws_server_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "audio_server");
		ws_server_handler.setOnmessageHandler(ws_recv_server_func);
		ws_server_handler.run();

		var ws_sndif_svr_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "snd_interface");
		ws_sndif_svr_handler.setOnmessageHandler(ws_recv_sndif_svr_func);
		ws_sndif_svr_handler.run();

		var ws_svr_ctrl_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "audio_server_control");
		ws_svr_ctrl_handler.setOnopenHandler(ws_open_server_func);
		ws_svr_ctrl_handler.run();
	});

</script>
