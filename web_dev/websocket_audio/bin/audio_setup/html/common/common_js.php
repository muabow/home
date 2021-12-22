<script type="text/javascript">
	$(document).ready(function() {
		var serverFunc = new ServerFunc();
		var clientFunc = new ClientFunc();
	
		$(".tabs_list").click(function() {
			var number = $(".tabs_list").index(this) + 1;

			$(".tabs_list").removeClass("tabs_list_click");
			$(this).addClass("tabs_list_click");
			$("div[id^=tabs-]").hide();
			$("#tabs-"+(number)).show();

			var tabStat = "server";
			if( number == 2 ) tabStat = "client";

			var submitParams = "";
			submitParams += serverFunc.makeArgs("type",				"audio_server");
			submitParams += serverFunc.makeArgs("act",				"set_tab");
			submitParams += serverFunc.makeArgs("tab_stat",			tabStat);

			serverFunc.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", submitParams);

			return ;
		});

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


		$("input[type=radio][name=radio_encodeType]").change(function() {
			if( $(this).attr("id") == "radio_encode_pcm" ) {
				$("#div_server_encode").hide();
				$("#div_server_run_encode").hide();
				$("#div_server_pcm").show();
				$("#div_server_run_pcm").show();

			} else {
				$("#div_server_encode").show();
				$("#div_server_run_encode").show();
				$("#div_server_pcm").hide();
				$("#div_server_run_pcm").hide();
			}
		});


		$("input[type=radio][name=radio_castType]").change(function() {
			if( $(this).attr("id") == "radio_cast_unicast" ) {
				var submitParams = "";
				submitParams += serverFunc.makeArgs("type", "audio_server");
				submitParams += serverFunc.makeArgs("act",  "get_stat");

				var rc = serverFunc.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", submitParams);
				var stat = JSON.parse(rc);

				$("#input_server_ipAddr").val(stat.default_ipAddr);

				$("#div_server_multicast_ipAddr").show();
			}
		});

		$("input[type=radio][name=radio_operType]").change(function() {
			var submitParams = "";
			submitParams += serverFunc.makeArgs("type", "audio_server");
			submitParams += serverFunc.makeArgs("act",  "get_stat");

			var rc = serverFunc.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", submitParams);
			var stat = JSON.parse(rc);

			if( $(this).attr("id") == "radio_oper_default" ) {
				$("#input_server_ipAddr").attr("disabled", true);
				$("#input_server_ipAddr").css("color", "#808080").css("background", "#ffffff");
				$("#input_server_ipAddr").val(stat.default_ipAddr);

				$("#input_server_port").attr("disabled", true);
				$("#input_server_port").css("color", "#808080").css("background", "#ffffff");
				$("#input_server_port").val(stat.default_port);

			} else {
				$("#input_server_ipAddr").attr("disabled", false);
				$("#input_server_ipAddr").css("color", "#000000");
				$("#input_server_ipAddr").val(stat.change_ipAddr);

				$("#input_server_port").attr("disabled", false);
				$("#input_server_port").css("color", "#000000");
				$("#input_server_port").val(stat.change_port);
			}
		});

		function setServerApply() {
			var submitParams = "";
			submitParams += serverFunc.makeArgs("type",				"audio_server");
			submitParams += serverFunc.makeArgs("act",  			"set_stat");
			submitParams += serverFunc.makeArgs("protocol", 		$("[name=radio_protocolType]:checked").attr("id").split("_").pop());
			//submitParams += serverFunc.makeArgs("castType", 		$("[name=radio_castType]:checked").attr("id").split("_").pop());
			submitParams += serverFunc.makeArgs("castType", 		"unicast");
			submitParams += serverFunc.makeArgs("encodeType", 		$("[name=radio_encodeType]:checked").attr("id").split("_").pop());
			submitParams += serverFunc.makeArgs("pcm_sampleRate", 	$("#select_sampleRate").val());
			submitParams += serverFunc.makeArgs("pcm_channels", 	$("#select_channels").val());
			submitParams += serverFunc.makeArgs("mp3_sampleRate", 	$("#select_mp3_sampleRate").val());
			submitParams += serverFunc.makeArgs("mp3_bitRate",		$("#select_mp3_bitRate").val());
			submitParams += serverFunc.makeArgs("operType", 		$("[name=radio_operType]:checked").attr("id").split("_").pop());
			submitParams += serverFunc.makeArgs("ipAddr", 			$("#input_server_ipAddr").val());
			submitParams += serverFunc.makeArgs("port", 			$("#input_server_port").val());
			submitParams += serverFunc.makeArgs("stat", 			"operation");

			serverFunc.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", submitParams);

			$("#radio_run_protocol_label").text($("[name=radio_protocolType]:checked").attr("id").split("_").pop() == "tcp" ? "TCP/IP" : "RTSP");
			//$("#radio_run_castType_label").text($("[name=radio_castType]:checked").attr("id").split("_").pop() == "unicast" ? "Unicast" : "Multicast");
			$("#radio_run_castType_label").text("Unicast");
			$("#radio_run_encode_label").text($("[name=radio_encodeType]:checked").attr("id").split("_").pop() == "pcm" ? "PCM" : "MP3");
			$("#select_run_sampleRate").val($("#select_sampleRate").val());
			$("#select_run_channels").val($("#select_channels").val());
			$("#select_run_mp3_sampleRate").val($("#select_mp3_sampleRate").val());
			$("#select_run_mp3_bitRate").val($("#select_mp3_bitRate").val());

			var ipAddr;
			ipAddr = "<?=$_SERVER['SERVER_NAME'] ?>";
			
			$("#input_server_run_ipAddr1").val(ipAddr);
			$("#input_server_run_port1").val($("#input_server_port").val());
			$("#input_server_run_ipAddr2").val($("#input_server_ipAddr").val());
			$("#input_server_run_port2").val($("#input_server_port").val());

			submitParams = "";
			submitParams += serverFunc.makeArgs("type",				"audio_server");
			submitParams += serverFunc.makeArgs("act",  			"run");
			serverFunc.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", submitParams);

			$("#div_display_server_setup").hide(200);
			$("#div_display_server_operation").show(200);

			return
		}

		$("#div_button_server_apply_hidden").click(function() {
			setServerApply();

			return ;
		});

		$("#div_button_server_apply").click(function() {
			if( !serverFunc.checkIpAddr($("#input_server_ipAddr").val()) ) {
				$("#input_server_ipAddr").focus();
				alert("<?=Audio_setup\Lang\STR_JS_WRONG_IP_ADDR ?>");

				return ;
			}

			if( !serverFunc.checkPort($("#input_server_port").val()) ) {
				alert("<?=Audio_setup\Lang\STR_JS_WRONG_PORT ?>");
				$("#input_server_port").focus();

				return ;
			}

			if( !confirm("<?=Audio_setup\Lang\STR_JS_START_AUDIO_SERVER ?>") ) {
				return ;
			}

			setServerApply();

			return ;
		});

		$("#div_button_server_cancel").click(function() {
			var submitParams = "";
			submitParams += serverFunc.makeArgs("type", "audio_server");
			submitParams += serverFunc.makeArgs("act",  "get_stat");

			var rc = serverFunc.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", submitParams);
			var stat = JSON.parse(rc);

			$('#radio_protocol_' + stat.protocol).trigger("click");
			$('#radio_cast_' + stat.castType).trigger("click");
			$('#radio_encode_' + stat.encode).trigger("click");

			$("#select_sampleRate").val(stat.pcm_sampleRate);
			$("#select_channels").val(stat.pcm_channels);
			$("#select_mp3_sampleRate").val(stat.mp3_sampleRate);
			$("#select_mp3_bitRate").val(stat.mp3_bitRate);

			$('#radio_oper_' + stat.operType).trigger("click");

			if( stat.operType == "default" ) {
				$("#input_server_ipAddr").attr("disabled", true);
				$("#input_server_ipAddr").css("color", "#808080").css("background", "#ffffff");
				$("#input_server_ipAddr").val(stat.default_ipAddr);

				$("#input_server_port").attr("disabled", true);
				$("#input_server_port").css("color", "#808080").css("background", "#ffffff");
				$("#input_server_port").val(stat.default_port);

			} else {
				$("#input_server_ipAddr").attr("disabled", false);
				$("#input_server_ipAddr").css("color", "#000000");
				$("#input_server_ipAddr").val(stat.change_ipAddr);

				$("#input_server_port").attr("disabled", false);
				$("#input_server_port").css("color", "#000000");
				$("#input_server_port").val(stat.change_port);
			}

			return ;
		});

		$("#div_button_server_setup").click(function() {
			var submitParams = "";
			submitParams += serverFunc.makeArgs("type",				"audio_server");
			submitParams += serverFunc.makeArgs("act",  			"stop");
			submitParams += serverFunc.makeArgs("stat",  			"setup");

			serverFunc.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", submitParams);

			$("#div_display_server_operation").hide(200);
			$("#div_display_server_setup").show(200);

			return ;
		});

		$("#div_button_apply_volume").click(function() {
			var volume = $("#slidervalue1").html();

			var submitParams = "";
			submitParams += serverFunc.makeArgs("type",				"audio_client");
			submitParams += serverFunc.makeArgs("act",  			"setVolume");
			submitParams += serverFunc.makeArgs("volume", 			volume);

			clientFunc.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", submitParams);

			return ;
		});
		
		$("input[type=radio][name=radio_client_castType]").change(function() {
			var submitParams = "";
			submitParams += serverFunc.makeArgs("type", "audio_client");
			submitParams += serverFunc.makeArgs("act",  "get_stat");

			var rc = clientFunc.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", submitParams);
			var stat = JSON.parse(rc);

			if( $(this).attr("id") == "radio_client_cast_unicast" ) {
				
				if( $("[name=radio_client_operType]:checked").attr("id") == "radio_client_oper_default" )	 {
					$("#input_client_ipAddr_master").val(stat.default_unicast_ipAddr);
					$("#input_client_port_master").val(stat.default_unicast_port);
					$("#input_client_ipAddr_slave").val(stat.default_unicast_rep_ipAddr);
					$("#input_client_port_slave").val(stat.default_unicast_rep_port);
				} else {
					$("#input_client_ipAddr_master").val(stat.change_unicast_ipAddr);
					$("#input_client_port_master").val(stat.change_unicast_port);
					$("#input_client_ipAddr_slave").val(stat.change_unicast_rep_ipAddr);
					$("#input_client_port_slave").val(stat.change_unicast_rep_port);
					
				}

				$("#div_redundancyStat").show();

				var redundancyMode = $("[name=radio_client_redundancy]:checked").attr("id").split("_").pop();
				if( redundancyMode == "slave" ) {
					$("#div_client_redundancy").show();

				} else {
					$("#div_client_redundancy").hide();
				}

			} else {
				if( $("[name=radio_client_operType]:checked").attr("id") == "radio_client_oper_default" )	 {
					$("#input_client_ipAddr_master").val(stat.default_multicast_ipAddr);
					$("#input_client_port_master").val(stat.default_multicast_port);
				} else {
					$("#input_client_ipAddr_master").val(stat.change_multicast_ipAddr);
					$("#input_client_port_master").val(stat.change_multicast_port);
				} 
				$("#div_redundancyStat").hide();
				$("#div_client_redundancy").hide();
			}
		});

		$("input[type=radio][name=radio_client_redundancy]").change(function() {
			if( $(this).attr("id") == "radio_client_redundancy_master" ) {
				$("#div_client_redundancy").hide();

			} else {
				$("#div_client_redundancy").show();
			}
		});

		$("input[type=radio][name=radio_client_operType]").change(function() {
			var submitParams = "";
			submitParams += serverFunc.makeArgs("type", "audio_client");
			submitParams += serverFunc.makeArgs("act",  "get_stat");

			var rc = clientFunc.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", submitParams);
			var stat = JSON.parse(rc);

			if( $(this).attr("id") == "radio_client_oper_default" ) {
					$("#input_client_ipAddr_master").css("color", "#808080").css("background", "#ffffff").attr("disabled", true);
					$("#input_client_port_master").css("color", "#808080").css("background", "#ffffff").attr("disabled", true);
					$("#input_client_ipAddr_slave").css("color", "#808080").css("background", "#ffffff").attr("disabled", true);
					$("#input_client_port_slave").css("color", "#808080").css("background", "#ffffff").attr("disabled", true);

				if( $("[name=radio_client_castType]:checked").attr("id") == "radio_client_cast_unicast" ) {
					$("#input_client_ipAddr_master").val(stat.default_unicast_ipAddr);
					$("#input_client_port_master").val(stat.default_unicast_port);
					$("#input_client_ipAddr_slave").val(stat.default_unicast_rep_ipAddr);
					$("#input_client_port_slave").val(stat.default_unicast_rep_port);

				} else {
					$("#input_client_ipAddr_master").val(stat.default_multicast_ipAddr);
					$("#input_client_port_master").val(stat.default_multicast_port);
				}

			} else {
				$("#input_client_ipAddr_master").css("color", "#000000").attr("disabled", false);
				$("#input_client_port_master").css("color", "#000000").attr("disabled", false);
				$("#input_client_ipAddr_slave").css("color", "#000000").attr("disabled", false);
				$("#input_client_port_slave").css("color", "#000000").attr("disabled", false);

				if( $("[name=radio_client_castType]:checked").attr("id") == "radio_client_cast_unicast" ) {
					$("#input_client_ipAddr_master").val(stat.change_unicast_ipAddr);
					$("#input_client_port_master").val(stat.change_unicast_port);
					$("#input_client_ipAddr_slave").val(stat.change_unicast_rep_ipAddr);
					$("#input_client_port_slave").val(stat.change_unicast_rep_port);

				} else {
					$("#input_client_ipAddr_master").val(stat.change_multicast_ipAddr);
					$("#input_client_port_master").val(stat.change_multicast_port);
				}
			}

		});

		$("#div_button_client_cancel").click(function() {
			var submitParams = "";
			submitParams += serverFunc.makeArgs("type", "audio_client");
			submitParams += serverFunc.makeArgs("act",  "get_stat");

			var rc = serverFunc.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", submitParams);
			var stat = JSON.parse(rc);

			$('#radio_client_protocol_' + stat.protocol).trigger("click");
			$('#radio_client_cast_' + stat.castType).trigger("click");
			$('#radio_client_oper_' + stat.operType).trigger("click");

			$("#select_client_buffer_sec").val(stat.buffer_sec);
			$("#select_client_buffer_msec").val(stat.buffer_msec);

			if( stat.redundancy == "slave" ) {
				$("#radio_client_redundancy_slave").trigger("click");
			} else {
				$("#radio_client_redundancy_master").trigger("click");
			}

			if( stat.operType == "default" ) {
				$("#input_client_ipAddr_master").css("color", "#808080").css("background", "#ffffff").attr("disabled", true);
					$("#input_client_port_master").css("color", "#808080").css("background", "#ffffff").attr("disabled", true);
					$("#input_client_ipAddr_slave").css("color", "#808080").css("background", "#ffffff").attr("disabled", true);
					$("#input_client_port_slave").css("color", "#808080").css("background", "#ffffff").attr("disabled", true);


				if( $("[name=radio_client_castType]:checked").attr("id") == "radio_client_cast_unicast" ) {
					$("#input_client_ipAddr_master").val(stat.default_unicast_ipAddr);
					$("#input_client_port_master").val(stat.default_unicast_port);
					$("#input_client_ipAddr_slave").val(stat.default_unicast_rep_ipAddr);
					$("#input_client_port_slave").val(stat.default_unicast_rep_port);

				} else {
					$("#input_client_ipAddr_master").val(stat.default_multicast_ipAddr);
					$("#input_client_port_master").val(stat.default_multicast_port);
				}

			} else {
				$("#input_client_ipAddr_master").css("color", "#000000").attr("disabled", false);
				$("#input_client_port_master").css("color", "#000000").attr("disabled", false);
				$("#input_client_ipAddr_slave").css("color", "#000000").attr("disabled", false);
				$("#input_client_port_slave").css("color", "#000000").attr("disabled", false);

				if( $("[name=radio_client_castType]:checked").attr("id") == "radio_client_cast_unicast" ) {
					$("#input_client_ipAddr_master").val(stat.change_unicast_ipAddr);
					$("#input_client_port_master").val(stat.change_unicast_port);
					$("#input_client_ipAddr_slave").val(stat.change_unicast_rep_ipAddr);
					$("#input_client_port_slave").val(stat.change_unicast_rep_port);

				} else {
					$("#input_client_ipAddr_master").val(stat.change_multicast_ipAddr);
					$("#input_client_port_master").val(stat.change_multicast_port);
				}
			}

			return ;
		});

		function setClientApply() {
			var submitParams = "";
			submitParams += clientFunc.makeArgs("type",						"audio_client");
			submitParams += clientFunc.makeArgs("act",  					"set_stat");
			submitParams += clientFunc.makeArgs("protocol", 				$("[name=radio_client_protocolType]:checked").attr("id").split("_").pop());
			submitParams += clientFunc.makeArgs("castType", 				$("[name=radio_client_castType]:checked").attr("id").split("_").pop());
			submitParams += clientFunc.makeArgs("operType", 				$("[name=radio_client_operType]:checked").attr("id").split("_").pop());
			submitParams += clientFunc.makeArgs("buffer_sec", 				$("#select_client_buffer_sec").val());
			submitParams += clientFunc.makeArgs("buffer_msec", 				$("#select_client_buffer_msec").val());
			submitParams += clientFunc.makeArgs("redundancy", 				$("[name=radio_client_redundancy]:checked").attr("id").split("_").pop());
			submitParams += clientFunc.makeArgs("client_ipAddr_master", 	$("#input_client_ipAddr_master").val());
			submitParams += clientFunc.makeArgs("client_port_master", 		$("#input_client_port_master").val());
			if( $("[name=radio_client_redundancy]:checked").attr("id").split("_").pop() == "slave" ) {
				submitParams += clientFunc.makeArgs("client_ipAddr_slave", 		$("#input_client_ipAddr_slave").val());
				submitParams += clientFunc.makeArgs("client_port_slave", 		$("#input_client_port_slave").val());
			}
			submitParams += clientFunc.makeArgs("stat", 					"operation");

			clientFunc.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", submitParams);

			$("#radio_run_client_protocol_label").text($("[name=radio_client_protocolType]:checked").attr("id").split("_").pop() == "tcp" ? "TCP/IP" : "RTSP");
			$("#radio_run_client_castType_label").text($("[name=radio_client_castType]:checked").attr("id").split("_").pop() == "unicast" ? "Unicast" : "Multicast");
			$("#select_run_client_buffer_sec").val($("#select_client_buffer_sec").val());
			$("#select_run_client_buffer_msec").val($("#select_client_buffer_msec").val());
			$("#input_client_run_master_ipAddr").val($("#input_client_ipAddr_master").val());
			$("#input_client_run_master_port").val($("#input_client_port_master").val());
			$("#input_client_run_slave_ipAddr").val($("#input_client_ipAddr_slave").val());
			$("#input_client_run_slave_port").val($("#input_client_port_slave").val());

			$("#div_redundancy_view").hide();
			if( $("[name=radio_client_redundancy]:checked").attr("id").split("_").pop() == "slave"
				&& $("[name=radio_client_castType]:checked").attr("id").split("_").pop() == "unicast" ) {
				$("#div_redundancy_view").show();
			}

			submitParams = "";
			submitParams += clientFunc.makeArgs("type",				"audio_client");
			submitParams += clientFunc.makeArgs("act",  			"run");
			clientFunc.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", submitParams);

			$("#div_display_client_setup").hide(200);
			$("#div_display_client_operation").show(200);

			return ;
		}

		$("#div_button_client_apply_hidden").click(function() {
			setClientApply();

			return ;
		});
		
		$("#div_button_apply_volume_mobile").click(function() {
			if( !clientFunc.checkNum($("#text_clientVolume").val()) ) {
				$("#text_clientVolume").focus();
				alert("<?=Audio_setup\Lang\STR_JS_WRONG_VOLUME ?>");
				$("#text_clientVolume").val(<?=$clientFunc->getOperVolume() ?>);
				$("#range_clientVolume").val(<?=$clientFunc->getOperVolume() ?>);
				return ;
			} else {
			
				var volume = $("#text_clientVolume").val();
	
				var submitParams = "";
				submitParams += serverFunc.makeArgs("type",				"audio_client");
				submitParams += serverFunc.makeArgs("act",  			"setVolume");
				submitParams += serverFunc.makeArgs("volume", 			volume);
	
				clientFunc.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", submitParams);
	
				return ;
				
			}
		});

		$("#div_button_client_apply").click(function() {
			if( !clientFunc.checkIpAddr($("#input_client_ipAddr_master").val()) ) {
				$("#input_client_ipAddr_master").focus();
				alert("<?=Audio_setup\Lang\STR_JS_WRONG_IP_ADDR ?>");

				return ;
			}

			if( !clientFunc.checkPort($("#input_client_port_master").val()) ) {
				$("#input_client_port_master").focus();
				alert("<?=Audio_setup\Lang\STR_JS_WRONG_PORT ?>");

				return ;
			}

			if( $("[name=radio_client_redundancy]:checked").attr("id").split("_").pop() == "slave"
				&& $("[name=radio_client_castType]:checked").attr("id").split("_").pop() == "unicast" ) {
				if( !clientFunc.checkIpAddr($("#input_client_ipAddr_slave").val()) ) {
					$("#input_client_ipAddr_slave").focus();
					alert("<?=Audio_setup\Lang\STR_JS_WRONG_IP_ADDR ?>");

					return ;
				}

				if( !clientFunc.checkPort($("#input_client_port_slave").val()) ) {
					$("#input_client_port_slave").focus();
					alert("<?=Audio_setup\Lang\STR_JS_WRONG_PORT ?>");

					return ;
				}
			}

			if( !confirm("<?=Audio_setup\Lang\STR_JS_START_AUDIO_CLIENT ?>") ) {
				return ;
			}

			setClientApply();

			return ;
		});

		$("#div_button_client_setup").click(function() {
			var submitParams = "";
			submitParams += clientFunc.makeArgs("type",				"audio_client");
			submitParams += clientFunc.makeArgs("act",  			"stop");
			submitParams += clientFunc.makeArgs("stat", 			"setup");
		
			if($("#div_client_operation_run").css("display") == "none")
			{
				submitParams += clientFunc.makeArgs("con", 			"no");
			} else {
				submitParams += clientFunc.makeArgs("con", 			"yes");
			}
			

			clientFunc.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", submitParams);

			$("#div_display_client_operation").hide(200);
			$("#div_display_client_setup").show(200);
	
			return ;
		});
		
		$("#div_button_client_setup_mobile").click(function() {
			var submitParams = "";
			submitParams += clientFunc.makeArgs("type",				"audio_client");
			submitParams += clientFunc.makeArgs("act",  			"stop");
			submitParams += clientFunc.makeArgs("stat", 			"setup");
		
			if($("#div_client_operation_run").css("display") == "none")
			{
				submitParams += clientFunc.makeArgs("con", 			"no");
			} else {
				submitParams += clientFunc.makeArgs("con", 			"yes");
			}
			

			clientFunc.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", submitParams);

			$("#div_display_client_operation").hide(200);
			$("#div_display_client_setup").show(200);
	
			return ;
		});
		
		$("#div_button_client_apply_mobile").click(function() {
			var submitParams = "";
			submitParams += clientFunc.makeArgs("type",				"audio_client");
			submitParams += clientFunc.makeArgs("act",  			"stop");
			submitParams += clientFunc.makeArgs("stat", 			"setup");
		
			if($("#div_client_operation_run").css("display") == "none")
			{
				submitParams += clientFunc.makeArgs("con", 			"no");
			} else {
				submitParams += clientFunc.makeArgs("con", 			"yes");
			}

			clientFunc.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", submitParams);

			
			if( !clientFunc.checkIpAddr($("#input_client_ipAddr_master").val()) ) {
				$("#input_client_ipAddr_master").focus();
				alert("<?=Audio_setup\Lang\STR_JS_WRONG_IP_ADDR ?>");

				return ;
			}

			if( !clientFunc.checkPort($("#input_client_port_master").val()) ) {
				$("#input_client_port_master").focus();
				alert("<?=Audio_setup\Lang\STR_JS_WRONG_PORT ?>");

				return ;
			}

			if( $("[name=radio_client_redundancy]:checked").attr("id").split("_").pop() == "slave"
				&& $("[name=radio_client_castType]:checked").attr("id").split("_").pop() == "unicast" ) {
				if( !clientFunc.checkIpAddr($("#input_client_ipAddr_slave").val()) ) {
					$("#input_client_ipAddr_slave").focus();
					alert("<?=Audio_setup\Lang\STR_JS_WRONG_IP_ADDR ?>");

					return ;
				}

				if( !clientFunc.checkPort($("#input_client_port_slave").val()) ) {
					$("#input_client_port_slave").focus();
					alert("<?=Audio_setup\Lang\STR_JS_WRONG_PORT ?>");

					return ;
				}
			}

			if( !confirm("<?=Audio_setup\Lang\STR_JS_START_AUDIO_CLIENT ?>") ) {
				return ;
			}

			setClientApply();

			return ;
		});


		initEqulizer("outputVolume_1");

		var submitParams = "";
		submitParams += clientFunc.makeArgs("type",				"audio_client");
		submitParams += clientFunc.makeArgs("act",  			"get_volume");

		var volume = clientFunc.postArgs("<?=Audio_setup\Def\PATH_AUDIO_PROCESS ?>", submitParams);

		setValue(volume, 1, false);

		var wsServerInfo = new WebSockInfo("<?=$_SERVER['SERVER_NAME'] ?>", "audio_server");
		var wsClientInfo = new WebSockInfo("<?=$_SERVER['SERVER_NAME'] ?>", "audio_client");

	});

	class ServerFunc {
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
		

	} // end of ServerFunc()

	class ClientFunc {
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
		
		checkNum(_num) {
			if( $.isNumeric(_num) && _num >= 0 && _num <= 100 ) {
				return true;

			} else {
				return false;
			}
		}
	} // end of ClientFunc()


	// TODO : onclose, onerror handle, try-catch handle
	class WebSockInfo {
		constructor(_ipAddr, _url) {
			this.ipAddr = _ipAddr;
			this.url    = _url;
			this.sockFd = this.init();

			this.open();
			this.recv(this.url);

			this.sockFd.onclose = function(_msg) {
				return ;
			};

			this.sockFd.onerror = function(_msg) {
				return ;
			};

		}

		init() {
			var hostInfo = "ws://" + this.ipAddr + "/" + this.url;
			var webSocketFd = new WebSocket(hostInfo);
			return webSocketFd;
		}

		open() {
			var sockFd = this.sockFd;

			this.sockFd.onopen = function( _msg) {
				sendMsgToProc(sockFd, 1, null);

				return ;
			};

		}
		close() {
			this.sockFd.close();

			return ;
		}

		recv(_url) {
			this.sockFd.onmessage = function(_msg) {
				var recvMsg = JSON.parse(_msg.data);

				if( _url == "audio_server" ) {
					switch( recvMsg.type ) {
						case "-11" : // incorrect process
							$("#div_server_operation_run").hide();
							$("#div_server_operation_stop").show();

							break;

						case "1"   : // alive info
							var data = recvMsg.data;

							if( data.stat == 1 ) { // run
								if( $("#div_server_operation_run").css("display") == "none") {

									$("#div_server_operation_run").show();
									$("#div_server_operation_stop").hide();

									$("#div_display_server_setup").hide(200);
									$("#div_display_server_operation").show(200);
								}

							} else {
								$("#div_server_operation_run").hide();
								$("#div_server_operation_stop").show();
							}

							break;

						case "10"  : // operation setup
							var data = recvMsg.data;
							if( data.queueCnt == 0 ) break;

							$("[id=radio_cast_" + data.castType).trigger("click");

							$("#radio_run_castType_label").html((data.castType == "unicast" ? "Unicast" : "Multicast"));
							$("#radio_run_encode_label").html((data.mp3_mode == 1 ? "MP3" : "PCM"));

							$("#select_sampleRate").val(data.sampleRate);
							$("#select_run_sampleRate").val(data.sampleRate);

							$("#select_channels").val(data.channels);
							$("#select_run_channels").val(data.channels);

							$("#radio_oper_change").trigger("click");

							var ipAddr = "<?=$_SERVER["SERVER_NAME"] ?>";

							$("#input_server_ipAddr").val(data.ipAddr);
							$("#input_server_run_ipAddr1").val(ipAddr);
							$("#input_server_run_ipAddr2").val(data.ipAddr);

							$("#input_server_port").val(data.port);
							$("#input_server_run_port1").val(data.port);
							$("#input_server_run_port2").val(data.port);

							break;

						case "21" : // client list
							$("#table_server_connList").empty();
							var appendData = "";
							var data     = recvMsg.data;
							var accCount = data.accCount;
							var dataList = data.list;

							for( var idx = 0 ; idx < accCount ; idx++ ) {
								var data  = dataList[idx];
								var stat  = (data.currentStat == "connect" ? "Activate" : "Deactivate");
								var color = (stat == "Activate" ? "green" : "red");

								appendData += '							\
									<div class="divTableRow">			\
										<div class="divTableData">			\
											' + (idx + 1) + '				\
										</div>								\
										<div class="divTableData">			\
											' + data.ipAddr  + '			\
										</div>								\
										<div class="divTableData">			\
											' + data.hostName + '			\
										</div>								\
										<div class="divTableData" style="color: ' + color + ';">	\
											' + stat + ' \
										</div>								\
										<div class="divTableData">			\
											' + data.currentTime + '		\
										</div>								\
									</div>';
							}

							$("#table_server_connList").append(appendData);
							break;
					} // switch
				}
				else if( _url == "audio_client" ) {
					switch( recvMsg.type ) {
						case "-11" : // incorrect process
							$(".level_outputVolume").html(0);

							$("#div_client_operation_run").hide();
							$("#div_client_operation_stop").show();

							$("#input_client_run_master_ipAddr").attr("class", "divServerInfo_deact");
							$("#input_client_run_master_port").attr("class", "divServerInfo_deact");
							$("#input_client_run_slave_ipAddr").attr("class", "divServerInfo_deact");
							$("#input_client_run_slave_port").attr("class", "divServerInfo_deact");

							break;

						case "1"   : // alive info
							var data = recvMsg.data;

							if( data.stat == 1 ) { // run
								if( $("#div_client_operation_run").css("display") == "none") {
									$("#div_client_operation_run").show();
									$("#div_client_operation_stop").hide();

									$("#div_display_client_setup").hide(200);
									$("#div_display_client_operation").show(200);
									
									$("#div_button_client_setup_mobile").show();
									$("#div_button_client_apply_mobile").hide();
								}

							} else {
								$(".level_outputVolume").html(0);

								$("#div_client_operation_run").hide();
								$("#div_client_operation_stop").show();
								
								$("#div_button_client_apply_mobile").show();
								$("#div_button_client_setup_mobile").hide();

								$("#input_client_run_master_ipAddr").attr("class", "divServerInfo_deact");
								$("#input_client_run_master_port").attr("class", "divServerInfo_deact");
								$("#input_client_run_slave_ipAddr").attr("class", "divServerInfo_deact");
								$("#input_client_run_slave_port").attr("class", "divServerInfo_deact");
							}

							break;

						case "10"  : // operation setup
							var data = recvMsg.data;
							if( data.serverCnt == 0 ) break;

							$("#select_run_client_buffer_sec").val(data.delay);
							$("#select_run_client_buffer_msec").val(data.delayMs);

							if( data.castType == "unicast" ) {
								$("#input_client_run_master_ipAddr").val(data.ipAddr1);
								$("#input_client_run_master_port").val(data.port1);

								$("#input_client_run_slave_ipAddr").val(data.ipAddr2);
								$("#input_client_run_slave_port").val(data.port2);

							} else {
								$("#input_client_run_master_ipAddr").val(data.mIpAddr);
								$("#input_client_run_master_port").val(data.mPort)
							}

							$("#radio_run_client_encode_label").html(data.mp3_mode == 0 ? "PCM" : "MP3");
							$("#div_client_run_pcm").show();

							if( data.mp3_mode == 1 ) {	// MP3 encode
								$("#div_client_run_encode").show();
								$("#div_client_run_pcm").hide();

								$("#select_run_client_mp3_sampleRate").val(data.mp3_sampleRate);
								$("#select_run_client_mp3_bitRate").val(data.mp3_bitRate);
								$("#select_mobile_client_mp3_sampleRate").html(data.mp3_sampleRate == 44100 ? "44,100 Hz" : "48,000 Hz");
								if(data.mp3_bitRate == 32) {
									$("#select_mobile_client_mp3_bitRate").html("32,000 bps");
								} else if(data.mp3_bitRate == 40) {
									$("#select_mobile_client_mp3_bitRate").html("40,000 bps");
								} else if(data.mp3_bitRate == 48) {
									$("#select_mobile_client_mp3_bitRate").html("48,000 bps");
								} else if(data.mp3_bitRate == 56) {
									$("#select_mobile_client_mp3_bitRate").html("56,000 bps");
								} else if(data.mp3_bitRate == 64) {
									$("#select_mobile_client_mp3_bitRate").html("64,000 bps");
								} else if(data.mp3_bitRate == 80) {
									$("#select_mobile_client_mp3_bitRate").html("80,000 bps");
								} else if(data.mp3_bitRate == 96) {
									$("#select_mobile_client_mp3_bitRate").html("96,000 bps");
								} else if(data.mp3_bitRate == 112) {
									$("#select_mobile_client_mp3_bitRate").html("112,000 bps");
								} else if(data.mp3_bitRate == 128) {
									$("#select_mobile_client_mp3_bitRate").html("128,000 bps");
								} else if(data.mp3_bitRate == 160) {
									$("#select_mobile_client_mp3_bitRate").html("160,000 bps");
								} else if(data.mp3_bitRate == 192) {
									$("#select_mobile_client_mp3_bitRate").html("192,000 bps");
								} else if(data.mp3_bitRate == 224) {
									$("#select_mobile_client_mp3_bitRate").html("224,000 bps");
								} else if(data.mp3_bitRate == 256) {
									$("#select_mobile_client_mp3_bitRate").html("256,000 bps");
								} else if(data.mp3_bitRate == 320) {
									$("#select_mobile_client_mp3_bitRate").html("320,000 bps");
								} 
								
							} else {
								$("#div_client_run_encode").hide();
								$("#div_client_run_pcm").show();
								
								$("#select_run_client_sampleRate").val(data.sampleRate);
								$("#select_run_client_channels").val(data.channels);
								$("#select_mobile_client_sampleRate").html(data.sampleRate == 44100 ? "44,100 Hz" : "48,000 Hz");
								$("#select_mobile_client_channels").html(data.channels == 1 ? "( 1 ch ) Mono" : " ( 2 ch ) Stereo");
								
							}
														
							if( data.serverCnt == 1 ) {
								$("#div_redundancy_view").hide();
							} else {
								$("#div_redundancy_view").show();
							}

							if( data.ipStatus == 0 ) {
								$("#input_client_run_master_ipAddr").attr("class", "divServerInfo_act");
								$("#input_client_run_master_port").attr("class", "divServerInfo_act");
								$("#input_client_run_slave_ipAddr").attr("class", "divServerInfo_deact");
								$("#input_client_run_slave_port").attr("class", "divServerInfo_deact");

							} else if( data.ipStatus == 2 ) {
								$("#input_client_run_master_ipAddr").attr("class", "divServerInfo_act");
								$("#input_client_run_master_port").attr("class", "divServerInfo_act");

							}else {
								$("#input_client_run_master_ipAddr").attr("class", "divServerInfo_deact");
								$("#input_client_run_master_port").attr("class", "divServerInfo_deact");
								$("#input_client_run_slave_ipAddr").attr("class", "divServerInfo_act");
								$("#input_client_run_slave_port").attr("class", "divServerInfo_act");
							}

							setValue(data.playVolume, 1, false);

							break;

						case "11" : // volume
							var data = recvMsg.data;
							if( $("#div_client_operation_run").css("display") != "none" ) {
								setValue(data.playVolume, 1, false);
							}

						case "12" : // level meter
							var data = recvMsg.data;

							$(".level_outputVolume").html(data.level);

							break;

					} // switch
				}
			}
		}
	} // end of WebSockInfo()

	function showValue(_val, _slidernum, _vertical) {
		/* setup variables for the elements of our slider */
		var thumb	 	= document.getElementById("sliderthumb" + _slidernum);
		var shell 		= document.getElementById("slidershell" + _slidernum);
		var track 		= document.getElementById("slidertrack" + _slidernum);
		var fill 		= document.getElementById("sliderfill" + _slidernum);
		var rangevalue 	= document.getElementById("slidervalue" + _slidernum);
		var slider 		= document.getElementById("slider" + _slidernum);

		var pc 			= _val/(slider.max - slider.min); /* the percentage slider value */
		var thumbsize 	= 25; 	/* must match the thumb size in your css */
		var bigval 		= 540; 	/* widest or tallest value depending on orientation */
		var smallval 	= 40; 	/* narrowest or shortest value depending on orientation */
		var tracksize 	= bigval - thumbsize;
		var fillsize 	= 16;
		var filloffset 	= 10;
		var bordersize 	= 2;
		var loc 		= _vertical ? (1 - pc) * tracksize : pc * tracksize;

		rangevalue.innerHTML = _val;

		/* rotating
		var degrees = 360 * pc;
		var rotation = "rotate(" + degrees + "deg)";


		thumb.style.webkitTransform = rotation;
		thumb.style.MozTransform = rotation;
		thumb.style.msTransform = rotation;
		*/
		fill.style.opacity = pc + 0.2 > 1 ? 1 : pc + 0.2;

		rangevalue.style.top 	= (_vertical ? loc : 0) + "px";
		rangevalue.style.left 	= (_vertical ? 0 : loc) + "px";
		thumb.style.top 		= (_vertical ? loc : 0) + "px";
		thumb.style.left 		= (_vertical ? 0 : loc) + "px";
		fill.style.top 			= (_vertical ? loc + (thumbsize/2) : filloffset + bordersize) + "px";
		fill.style.left 		= (_vertical ? filloffset + bordersize : 0) + "px";
		fill.style.width 		= (_vertical ? fillsize : loc + (thumbsize/2)) + "px";
		fill.style.height 		= (_vertical ? bigval - filloffset - fillsize - loc : fillsize) + "px";
		shell.style.height 		= (_vertical ? bigval : smallval) + "px";
		shell.style.width 		= (_vertical ? smallval : bigval) + "px";
		track.style.height 		= (_vertical ? bigval - 4 : fillsize) + "px"; /* adjust for border */
		track.style.width 		= (_vertical ? fillsize : bigval - 4) + "px"; /* adjust for border */
		track.style.left 		= (_vertical ? filloffset + bordersize : 0) + "px";
		track.style.top 		= (_vertical ? 0 : filloffset + bordersize) + "px";
	}

	/* we often need a function to set the slider values on page load */
	function setValue(_val, _num, _vertical) {
		document.getElementById("slider" + _num).value = _val;
		showValue(_val, _num, _vertical);
	}

	function sendMsgToProc(_sockFd, _cmdId, _bodyString) {
		if( _bodyString == null ) _bodyString = "";
		var arrBodyString = new Uint8Array(_bodyString.length);

		for( idx = 0 ; idx < _bodyString.length ; idx++ ) {
			arrBodyString[idx] = _bodyString.charCodeAt(idx);
		}

		var arrSendMsg = new Uint8Array(5 + _bodyString.length);

		arrSendMsg.set([_cmdId]);
		arrSendMsg.set([_bodyString.length], 1, 4);
		if( _bodyString.length > 0 ) {
			arrSendMsg.set(arrBodyString, 5, _bodyString.length);
		}

		_sockFd.send(arrSendMsg);

		return ;
	}


</script>
