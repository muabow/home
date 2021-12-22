<script type="text/javascript">
	$(document).ready(function() {
		// only client scope variables
		var json_alive_info;
		var json_oper_info;
		var status_run_button = "";

		var audio_client_eq_func = new AudioEqulizerFunc("outputVolume_1");

		// setup functions
		function setup_operation_info(_is_alive) {
			var data = json_oper_info;

			// 초기값 설정 : encode [-1:init, 0:pcm, 1:mp3]
			if( data.encode_mode == -1 ) {
				data.encode_mode = 0;
			}

			// 초기값 설정 : cast_type ["", unicast, multicast, all]
			if( data.castType == "" ) {
				data.castType = "unicast";
			}

			// status: alive
			if( _is_alive == 1 ) {
				if( json_alive_info.view == "setup" ) {
					// setup view

				} else {
					// operation view
					var cast_type = data.castType;

					if( cast_type == "all" ) {
						cast_type = "Unicast/Multicast";

					} else {
						cast_type = cast_type.substring(0,1).toUpperCase() + cast_type.substring(1);
					}

					$("#radio_run_client_protocol_label").html("TCP/IP");
					$("#radio_run_client_castType_label").html(cast_type);
					$("#radio_run_client_encode_label").html((data.encode_mode == 0 ? "PCM" : "MP3"));

					$("#div_server_info_master").hide();
					$("#div_server_info_slave").hide();
					$("#div_server_info_multicast").hide();

					if( cast_type == "Unicast" ) {
						$("#div_server_info_master").show();
						$("#div_server_info_slave").show();

					} else if( cast_type == "Multicast" ) {
						$("#div_server_info_multicast").show();

					} else if( cast_type == "Unicast/Multicast" ) {
						$("#div_server_info_master").show();
						$("#div_server_info_slave").show();
						$("#div_server_info_multicast").show();
					}

					if( data.encode_mode == 0 ) {
						$("#div_client_run_pcm").show();
						$("#div_client_run_encode").hide();
					}  else {
						$("#div_client_run_pcm").hide();
						$("#div_client_run_encode").show();
					}

					$("#select_run_client_sample_rate").val(data.pcm_sample_rate);
					$("#select_run_client_channels").val(data.pcm_channels);

					$("#select_run_client_mp3_quality").val(data.mp3_quality);
					$("#select_run_client_mp3_channels").val(data.pcm_channels);
					$("#select_run_client_mp3_sampleRate").val(data.pcm_sample_rate);

					$("#select_run_client_buffer_sec").val(data.delay_sec);
					$("#select_run_client_buffer_msec").val(data.delay_msec);

					$("#input_client_run_master_ipAddr").val(data.ipAddr1);
					$("#input_client_run_master_port").val(data.port1);

					$("#input_client_run_slave_ipAddr").val(data.ipAddr2);
					$("#input_client_run_slave_port").val(data.port2);

					$("#input_client_run_multicast_ipAddr").val(data.mIpAddr);
					$("#input_client_run_multicast_port").val(data.mPort);

					if( data.castType == "unicast") {
						$("#div_server_info_multicast").hide();

						if( data.redundancy == "master" ) {
							$("#div_server_info_slave").hide();

						} else {
							$("#div_server_info_slave").show();
						}

					} else {
						$("#div_server_info_multicast").show();
						$("#div_server_info_master").hide();
						$("#div_server_info_slave").hide();
					}
				}

			} else {
				// status: dead
				if( json_alive_info.view == "setup" ) {
					if( status_run_button == "apply" ) {
						status_run_button = "operation";

						return ;
					}

					$("#radio_client_cast_" + data.castType).prop("checked", true);
					$("#select_client_buffer_sec").val(data.delay_sec);
					$("#select_client_buffer_msec").val(data.delay_msec);
					$("#select_client_buffer_sec").trigger("click");

					$("#radio_client_cast_" + data.castType).trigger("click");
					$("#radio_client_redundancy_" + data.redundancy).trigger("click");

					if( data.castType == "multicast" ) {
						$("#div_client_redundancy_master").hide();
						$("#div_client_redundancy_slave").hide();
						$("#div_client_multicast").show();

						$("#div_server_info_master").hide();
						$("#div_server_info_slave").hide();
						$("#div_server_info_multicast").show();
					}

					$("[id^=input_client_ip_addr]").trigger("textchange");
					$("[id^=input_client_port]").trigger("textchange");

					$("#input_client_ip_addr_master").val(data.ipAddr1);
					$("#input_client_port_master").val(data.port1);

					$("#input_client_ip_addr_slave").val(data.ipAddr2);
					$("#input_client_port_slave").val(data.port2);

					$("#input_client_ip_addr_multicast").val(data.mIpAddr);
					$("#input_client_port_multicast").val(data.mPort);

					$("#radio_run_client_protocol_label").html("TCP/IP");
					$("#radio_run_client_castType_label").html(data.castType);
					$("#radio_run_client_encode_label").html("-");

					$("#input_client_run_master_ipAddr").val(data.ipAddr1);
					$("#input_client_run_master_port").val(data.port1);

					$("#input_client_run_slave_ipAddr").val(data.ipAddr2);
					$("#input_client_run_slave_port").val(data.port2);

					$("#input_client_run_multicast_ipAddr").val(data.mIpAddr);
					$("#input_client_run_multicast_port").val(data.mPort);

				} else {
					var cast_type = data.castType;

					if( cast_type == "all" ) {
						cast_type = "Unicast/Multicast";

					} else {
						cast_type = cast_type.substring(0,1).toUpperCase() + cast_type.substring(1);
					}

					$("#radio_run_client_protocol_label").html("TCP/IP");
					$("#radio_run_client_castType_label").html(cast_type);
					$("#radio_run_client_encode_label").html("-");

					if( data.encode_mode == 1 ) {
						$("#div_client_run_pcm").hide();
						$("#div_client_run_encode").show();

					} else {
						$("#div_client_run_pcm").show();
						$("#div_client_run_encode").hide();
					}

					$("#select_run_client_sample_rate").val("-");
					$("#select_run_client_channels").val("-");

					$("#select_run_client_mp3_sampleRate").val("-");
					$("#select_run_client_mp3_channels").val("-");
					$("#select_run_client_mp3_quality").val("-");

					$("#select_run_client_buffer_sec").val("-");
					$("#select_run_client_buffer_msec").val("-");

					if( data.castType == "unicast") {
						$("#div_server_info_multicast").hide();

						if( data.redundancy == "master" ) {
							$("#div_server_info_slave").hide();

						} else {
							$("#div_server_info_slave").show();
						}

					} else {
						$("#div_server_info_multicast").show();
						$("#div_server_info_master").hide();
						$("#div_server_info_slave").hide();
					}

					$("#input_client_run_master_ipAddr").val(data.ipAddr1);
					$("#input_client_run_master_port").val(data.port1);

					$("#input_client_run_slave_ipAddr").val(data.ipAddr2);
					$("#input_client_run_slave_port").val(data.port2);

					$("#input_client_run_multicast_ipAddr").val(data.mIpAddr);
					$("#input_client_run_multicast_port").val(data.mPort);
				}
			}

			return ;
		}


		// websocket event handler functions
		function ws_open_client_func(_this) {
			_this.send(0x01, null);

			return ;
		}

		function ws_recv_client_func(_cmd_id, _is_binary, _length, _data, _this) {
			if( _cmd_id == 1 && _data == null ) return ;

			try {
				var data = JSON.parse(_data);
			} catch(e) {
				return;
			}

			switch( parseInt(_cmd_id) ) {
				case 1   : // alive info
					json_alive_info = data;

					if( json_alive_info.view == "setup" ) {
						$("#div_display_client_setup").show();
						$("#div_display_client_operation").hide();

						$("[id^=div_client_operation_]").hide();
						$("#div_client_operation_wait").show();

					} else {
						$("#div_display_client_operation").show();
						$("#div_display_client_setup").hide();

						if( json_alive_info.stat == 1 ) {
							// operation run
							$("[id^=div_client_operation_]").hide();
							$("#div_client_operation_run").show();

						} else {
							// operation stop
							$("[id^=div_client_operation_]").hide();
							$("#div_client_operation_stop").show();

							$("#input_client_run_master_ipAddr").attr("class", "div_server_info_deact");
							$("#input_client_run_master_port").attr("class", "div_server_info_deact");
							$("#input_client_run_slave_ipAddr").attr("class", "div_server_info_deact");
							$("#input_client_run_slave_port").attr("class", "div_server_info_deact");
							$("#input_client_run_multicast_ipAddr").attr("class", "div_server_info_deact");
							$("#input_client_run_multicast_port").attr("class", "div_server_info_deact");
						}
					}
					setup_operation_info(json_alive_info.stat);

					<?php
						if( $is_enable_client ) {
							echo '
							$("#div_contents").show();
							$("#div_loader").hide();
							';
						}
					?>

					break;

				case 10  : // operation info
					json_oper_info = data;

					$("#input_client_run_master_ipAddr").attr("class", "div_server_info_deact");
					$("#input_client_run_master_port").attr("class", "div_server_info_deact");
					$("#input_client_run_slave_ipAddr").attr("class", "div_server_info_deact");
					$("#input_client_run_slave_port").attr("class", "div_server_info_deact");
					$("#input_client_run_multicast_ipAddr").attr("class", "div_server_info_deact");
					$("#input_client_run_multicast_port").attr("class", "div_server_info_deact");

					if( data.current_server == data.ipAddr1 ) {
						$("#input_client_run_master_ipAddr").attr("class", "div_server_info_act");
						$("#input_client_run_master_port").attr("class", "div_server_info_act");

					} else if( data.current_server == data.ipAddr2 ) {
						$("#input_client_run_slave_ipAddr").attr("class", "div_server_info_act");
						$("#input_client_run_slave_port").attr("class", "div_server_info_act");

					} else if( data.current_server == data.mIpAddr ) {
						$("#input_client_run_multicast_ipAddr").attr("class", "div_server_info_act");
						$("#input_client_run_multicast_port").attr("class", "div_server_info_act");
					}
					
					$("#div_operation_match_sample").hide();
					if( data.is_match_sample == 0 ) {
						$("#div_operation_match_sample").show();
					}
					break;

				case 11	 : // volume info
					if( g_num_apply_volume > 0 ) {
						g_num_apply_volume--;
						break;
					}
					
					$("#slider_value").val(data.playVolume);
					$(".slider").val(data.playVolume);

					break;

				case 12  : // level info
					$(".level_outputVolume_1").html(data.level);

					break;
			}

			return ;
		}

		// setup page button
		$("#div_button_client_apply").click(function() {
			status_run_button = "apply";
			ws_sndif_clnt_handler.send(0x01, "audio_client_control");

			return ;
		});

		$("#div_button_client_cancel").click(function() {
			ws_client_ctrl_handler.send(0x01, null);

			return ;
		});

		// operation page button
		$("#div_button_client_setup").click(function() {
			ws_client_ctrl_handler.send(0x20, null);

			return ;
		});

		$("#div_button_client_start").click(function() {
			status_run_button = "run";
			ws_sndif_clnt_handler.send(0x01, "audio_client_control");

			return ;
		});

		$("#div_button_client_stop").click(function() {
			ws_client_ctrl_handler.send(0x12, null);

			return ;
		});

		$("#slider_value").keydown(function(_evt) {
			if( _evt.keyCode == 13 ) {
				$("#div_button_apply_volume").trigger("click");
			}

			return ;
		});

		var g_num_apply_volume = 0;
		$("#div_button_apply_volume").click(function() {
			var volume = $("#slider_value").val();

			if( !$.isNumeric(volume) || volume > 100 || volume < 0 ) {
				volume = $("#slider_volume").val();
			}
			volume = parseInt(volume);
			
			$("#slider_value").val(volume);
			$("#slider_volume").val(volume);

			var arr_args = {};
			arr_args["audio_volume"] = volume;
					
			g_num_apply_volume++;
			var json_args = JSON.stringify(arr_args);
			ws_client_ctrl_handler.send(0x13, json_args);

			return ;
		});

		$("#slider_volume").change(function() {
			var volume = $(this).val();
			$("#slider_value").val(volume);

			var arr_args = {};
			arr_args["audio_volume"] = volume;
			
			g_num_apply_volume++;
			var json_args = JSON.stringify(arr_args);
			ws_client_ctrl_handler.send(0x13, json_args);

			return ;
		});

		$("input[name=radio_encodeType]").click(function() {
			$("#div_server_pcm").hide();
			$("#div_server_mp3").hide();

			var type = $(this).val();

			$("#div_server_" + type).css("display", "flex");

			return;
		});

		$("input[name=radio_client_redundancy]").click(function() {
			var type = $(this).attr("id").split("_")[3];

			if( type == "slave" ) {
				$("#div_client_redundancy_slave").show();

			} else {
				$("#div_client_redundancy_slave").hide();
			}

			return ;
		});

		$("input[name=radio_client_cast_type]").click(function() {
			var type = $(this).attr("id").split("_")[3];

			if( type == "multicast" ) {
				$("#div_client_redundancy_master").hide();
				$("#div_client_redundancy_slave").hide();
				$("#div_client_multicast").show();
				$("#div_redundancy_status").hide();

			} else {
				var redundancy = $("input[name=radio_client_redundancy]:checked").attr("id");
				redundancy = ( redundancy == undefined ) ? redundancy : redundancy.split("_")[3];

				$("#div_client_redundancy_master").show();
				if( redundancy == "slave" ) {
					$("#div_client_redundancy_slave").show();
				}

				$("#div_client_multicast").hide();
				$("#div_redundancy_status").show();
			}

			return ;
		});

		$("#select_client_buffer_sec").on("click change", function() {
			$("#select_client_buffer_msec").attr("disabled", false);

			if( $(this).val() == 10 ) {
				$("#select_client_buffer_msec").val(0);
				$("#select_client_buffer_msec").attr("disabled", true);
			}

			return ;
		});

		function ws_recv_sndif_clnt_func(_cmd_id, _is_binary, _length, _data, _this) {
			if( _cmd_id == 0x01 ) {
				if( status_run_button == "apply" || status_run_button == "operation" ) {
					var arr_args = {};
					arr_args["network_cast_type"] 		= $("input:radio[name=radio_client_cast_type]:checked").attr("id").split("_")[3];
					arr_args["network_redundancy"] 		= $("input:radio[name=radio_client_redundancy]:checked").attr("id").split("_")[3];
					arr_args["network_master_ip_addr"] 	= $("#input_client_ip_addr_master").val();
					arr_args["network_master_port"] 	= $("#input_client_port_master").val();
					arr_args["network_slave_ip_addr"] 	= $("#input_client_ip_addr_slave").val();
					arr_args["network_slave_port"] 		= $("#input_client_port_slave").val();
					arr_args["network_mcast_ip_addr"] 	= $("#input_client_ip_addr_multicast").val();
					arr_args["network_mcast_port"] 		= $("#input_client_port_multicast").val();
					arr_args["audio_play_buffer_sec"]	= $("#select_client_buffer_sec").val();
					arr_args["audio_play_buffer_msec"]	= $("#select_client_buffer_msec").val();
					
					var json_args = JSON.stringify(arr_args);
					ws_client_ctrl_handler.send(0x21, json_args);

				} else if( status_run_button == "run" ) {
					ws_client_ctrl_handler.send(0x11, null);
				}

				status_run_button = "";
			}

			return ;
		}

		// websocket instance
		var ws_client_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "audio_client");
		ws_client_handler.setOnmessageHandler(ws_recv_client_func);
		ws_client_handler.run();

		var ws_sndif_clnt_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "snd_interface");
		ws_sndif_clnt_handler.setOnmessageHandler(ws_recv_sndif_clnt_func);
		ws_sndif_clnt_handler.run();

		var ws_client_ctrl_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "audio_client_control");
		ws_client_ctrl_handler.setOnopenHandler(ws_open_client_func);
		ws_client_ctrl_handler.run();
	});
</script>
