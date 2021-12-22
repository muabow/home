<script type="text/javascript">
	class SourceFileHandler {
		constructor() { }

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
	}

	$(document).ready(function() {
		var audioEqulizerFunc = new AudioEqulizerFunc("outputVolume");

		$(".div_source_control_display").marquee({
			allowCss3Support:true,
			css3easing:'linear',
			easing:'linear',
			speed: 50,
			delayBeforeStart: 0,
			direction: 'left',
			pauseOnHover: true
		});


		// 1.1. 음원 파일 업로드 - 음원 파일 선택
		$('.filebox_upgrade .upload-hidden').on('change', function() {
			var str_source_name;
			var num_uploaded_source = 0;

			if( window.FileReader ) {
				if( $(this).val() == "" ) {
					str_source_name 	= "<?=Source_file_management\Lang\STR_SRCFILE_ADD_FIND ?>";

				} else {
					str_source_name 	= $(this)[0].files[0].name;
					num_uploaded_source = $(this)[0].files.length;
				}

			} else { // old IE
				str_source_name = $(this).val().split('/').pop().split('\\').pop();
			}

			if( num_uploaded_source > 1 ) {
				$(this).siblings('.upload-name').val(str_source_name + " <?=Source_file_management\Lang\STR_AND ?> " + (num_uploaded_source - 1) + " <?=Source_file_management\Lang\STR_OTHER ?>");

			} else {
				$(this).siblings('.upload-name').val(str_source_name);
			}

			return ;
		});

		// 1.2. 음원 파일 업로드 - 음원 파일 업로드
		$("#div_button_upload_apply").click(function() {
			var upload_source_list = $("#file_uploadFile")[0].files;

			// 파일 첨부 검출
			if( upload_source_list.length == 0 ) {
				alert("<?=Source_file_management\Lang\STR_SRCFILE_ADD_SELECT ?>");
				return ;
			}

			// 동시 업로드 파일 갯수 제한 
			if( upload_source_list.length > <?=$commonInfoFunc->get_max_file_uploads() ?> ) {
				alert("<?=Source_file_management\Lang\STR_SRCFILE_ADD_LIMIT_COUNT ?> [" + upload_source_list.length + "/<?=$commonInfoFunc->get_max_file_uploads() ?>]");
				return ;
			}

			var invalid_name_list_special 		= "";
			var invalid_name_list_double_space	= "";
			var invalid_name_list_ext_type		= "";
			var invalid_name_length				= "";

			$.each(upload_source_list, function(_i, _e) {
				var str_source_name = _e.name;
				
				// 파일명 길이 체크
				for( bc = idx = 0 ; c = str_source_name.charCodeAt(idx++) ; bc += (c >> 11 ? 3 : (c >> 7 ? 2 : 1)) );
				if( bc >= 255 ) {
					invalid_name_length += str_source_name + "\n";
				}

				// 파일명 띄어쓰기 2개 연속 체크
				var invalid_case_double_space = "  ";
				if( str_source_name.indexOf(invalid_case_double_space) > -1 ) {
					invalid_name_list_double_space += str_source_name + "\n";
				}

				// 파일명 특수문자 여부 체크
				var invalid_case_special = /[`'*|\\\"\/?#%:<>&$+]/g;
				if( invalid_case_special.test(str_source_name) == true ) {
					invalid_name_list_special += str_source_name + "\n";
				}

				// 파일 확장자 검출
				var source_ext_type = _e.type.toLowerCase();
				var source_ext_name = _e.name.toLowerCase();

				var reg = /(.*?)\.(mp3)$/;
				if( !(source_ext_type == "audio/mp3" || source_ext_type == "audio/wav" || (source_ext_name.match(reg) && source_ext_type == "audio/mpeg")) ) {
					invalid_name_list_ext_type += str_source_name + "\n";
				}
			});

			if(  invalid_name_length != "" ||  invalid_name_list_special != "" || invalid_name_list_double_space != "" || invalid_name_list_ext_type != "" ) {
				var noti_invalid_case = "";
				
				if( invalid_name_length != "" ) {
					noti_invalid_case += "<?=Source_file_management\Lang\STR_SRCFILE_ADD_INVALID_LENGTH ?>\n-------- <?=Source_file_management\Lang\STR_SRCFILE_TABLE ?> --------\n" + invalid_name_length;
					noti_invalid_case += "\n";
				}

				if( invalid_name_list_ext_type != "" ) {
					noti_invalid_case += "<?=Source_file_management\Lang\STR_SRCFILE_ADD_INVALID_TYPE ?>\n-------- <?=Source_file_management\Lang\STR_SRCFILE_TABLE ?> --------\n" + invalid_name_list_ext_type;
					noti_invalid_case += "\n";
				}

				if( invalid_name_list_special != "" ) {
					noti_invalid_case += "<?=Source_file_management\Lang\STR_SRCFILE_ADD_INVALID_NAME ?>\n-------- <?=Source_file_management\Lang\STR_SRCFILE_TABLE ?> --------\n" + invalid_name_list_special;
					noti_invalid_case += "\n";
				}

				if( invalid_name_list_double_space != "" ) {
					noti_invalid_case += "<?=Source_file_management\Lang\STR_SRCFILE_ADD_DOUBLE_SPACE_NAME ?>\n-------- <?=Source_file_management\Lang\STR_SRCFILE_TABLE ?> --------\n" + invalid_name_list_double_space;
					noti_invalid_case += "\n";
				}

				alert(noti_invalid_case);

				$("#div_button_upload_clear").trigger("click");

				return;
			}
			
			// 파일 사이즈 검출
			var size_upload_available	= $("#text_size_upload_available").val();
			var sum_size_source_list	= 0;

			if( $("[name=radio_upload_case]:checked").val() == "upload_external" ) {
				size_upload_available	= $("#text_size_upload_available_ext").val();
			}
			

			$.each(upload_source_list, function(_i, _e) {
				sum_size_source_list += _e.size;
			});
			sum_size_source_list = Math.round(sum_size_source_list / 1024 / 1024);

			if( size_upload_available < sum_size_source_list ) {
				alert("<?=Source_file_management\Lang\STR_SRCFILE_ADD_LIMIT_MEM_ALL ?>\n" + "<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL_UPLOAD_SIZE ?> : " + sum_size_source_list + " Mbytes / <?=Source_file_management\Lang\STR_SRCFILE_ADD_AVAILABLE_MEM ?> : " + size_upload_available + " Mbytes");

				return;
			}

			if( confirm("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_CONFIRM ?>") ) {
				common_log_handler.info("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_APPLY ?>");

			} else {
				return;
			}
			
			// ctrl : WS_RCV_CMD_CTRL_INOTY_IGN
			ws_audio_control_handler.send(0x30, null);
			common_display_handler.showLoader();


			// 파일 업로드
			var form_source_list = new FormData();
			var json_data;

			$.each(upload_source_list, function(_i, _e) {
				form_source_list.append("file_" + _i, _e);
				common_log_handler.info("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD ?> : " + _e.name);
			});
			
			form_source_list.append("storage", $("[name=radio_upload_case]:checked").val());

			$("#div_button_upload_clear").trigger("click");

			$("[id^=button_monitor_]").each(function() {
				 if( $(this).attr("class") ) {
					 common_audio_handler.pause();
					 common_audio_handler.currentTime = 0;

					 $(this).removeClass("monitor_play");
				 }
			});

			var request = new XMLHttpRequest();
			request.onreadystatechange = function() {
				if( request.readyState == 4 ) {
					$(".container").hide();

					try {
						json_data = JSON.parse(request.response);
						
						if( json_data.code == 0 ) {
							ws_audio_control_handler.send(0x25, null);

						} else {
							setTimeout(function() {
								switch( json_data.code ) {
									case -1 :
										alert("<?=Source_file_management\Lang\STR_SRCFILE_ADD_NOT_FOUND_FILE ?>");
										common_log_handler.info("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL ?> (<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL_NOTFOUND_FILE ?>)");
									break;

									case -2 :
										alert("<?=Source_file_management\Lang\STR_SRCFILE_ADD_LIMIT_COUNT ?>");
										common_log_handler.info("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL ?> (<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL_OVER_FILECNT ?>)");
									break;

									case -3 :
										alert("<?=Source_file_management\Lang\STR_SRCFILE_ADD_LIMIT_MEM_ALL ?>");
										common_log_handler.info("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL ?> (<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL_OVER_SAVESIZE ?>)");
									break;

									default :
										alert("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL ?>");
										common_log_handler.info("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL ?> (" + responseText + ")");
									break;
								}

								$("#div_button_upload_clear").trigger("click");
							}, 800);

							common_display_handler.clearDotLoader();
						}

					} catch( _e ) {
						var resp = {
							status	: 'error',
							data	: 'Unknown error occurred: [' + request.responseText + ']'
						};
					}
				}
			};
			
			var post_data = source_handler.makeArgs("act", "preview_clear");
			source_handler.postArgs("<?=Source_file_management\Def\PATH_COMMON_PROCESS ?>", post_data);

			request.open('POST', "<?=Source_file_management\Def\PATH_UPLOAD_PROCESS ?>");
			request.send(form_source_list);

			return ;
		});

		// 1.3. 음원 파일 업로드 - 초기화
		$("#div_button_upload_clear").click(function() {
			$("#file_uploadFile").val("");
			$("#label_uploadFile").val("<?=Source_file_management\Lang\STR_SRCFILE_ADD_FIND ?>");

			return ;
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
				post_data += source_handler.makeArgs("act", "preview");
				post_data += source_handler.makeArgs("src", fileName);
				source_handler.postArgs("<?=Source_file_management\Def\PATH_COMMON_PROCESS ?>", post_data);
				
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
			var splitId  = $(this).attr("id").split("_");
			var index    = splitId[splitId.length - 1].trim();

			var arr_args = {};
			arr_args["play_index"] = index;
								
			var json_args = JSON.stringify(arr_args);

			status_run_button	= "list";
			data_run_button 	= json_args;

			ws_sndif_handler.send(0x01, "audio_player_control");

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
			status_run_button	= "play";
			data_run_button 	= "";

			ws_sndif_handler.send(0x01, "audio_player_control");

			return ;
		});

		$("#control_button_pause").click(function() {
			ws_audio_control_handler.send(0x11, null);
			return ;
		});

		$("#control_button_stop").click(function() {
			ws_audio_control_handler.send(0x12, null);
			return ;
		});

		$("#control_button_prev").click(function() {
			ws_audio_control_handler.send(0x13, null);
			return ;
		});

		$("#control_button_next").click(function() {
			ws_audio_control_handler.send(0x14, null);
			return ;
		});

		$("#control_button_loop").click(function() {
			ws_audio_control_handler.send(0x15, null);
			return ;
		});

		$("[id^=control_button_remove]").click(function() {
			var checked_source = [];
			$(".input_source_check").each(function(_i, _e) {
				if( $(_e).is(":checked") ) {
					checked_source.push($(_e).closest(".div_table_row").attr("id"));
				}
			});

			$(".input_source_check_all").prop("checked", false);
			$(".input_source_check").prop("checked", false);

			var arr_args = {};
			arr_args["num_source_list"] = checked_source.length;
			arr_args["source_hash_id"] 	= checked_source.join();
								
			var json_args = JSON.stringify(arr_args);
			ws_audio_control_handler.send(0x16, json_args);

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
			ws_audio_control_handler.send(0x18, json_args);

			return ;
		});

		$("#slider_volume").change(function() {
			var volume = $(this).val();
			$("#slider_value").val(volume);

			var arr_args = {};
			arr_args["audio_volume"] = volume;

			g_num_apply_volume++;
			var json_args = JSON.stringify(arr_args);
			ws_audio_control_handler.send(0x18, json_args);

			return ;
		});

		$("[id^=control_button_download]").click(function() {
			var checked_source = [];

			$(".input_source_check").each(function(_i, _e) {
				if( $(_e).is(":checked") ) {
					var source_name = $(_e).closest(".div_table_row").find(".div_source_name").html();
					var file_path = 'modules/source_file_management/html/data/audiofiles/';
					
					var is_exist_ext = $(_e).parent().parent().find(".div_is_ext_storage_file").length;
					if( is_exist_ext ) {
						var post_data = "";
						post_data += source_handler.makeArgs("act", "preview");
						post_data += source_handler.makeArgs("src", source_name);
						source_handler.postArgs("<?=Source_file_management\Def\PATH_COMMON_PROCESS ?>", post_data);
						
						file_path = 'modules/source_file_management/html/data/';
					}

					file_path += source_name;

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

		$(document).on("mouseover", ".overflow_ellipsis", function() {
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

		$(document).on("mouseout", ".overflow_ellipsis", function() {
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
			$( "#sortable" ).sortable({
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
			ws_audio_control_handler.send(0x17, json_args);

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
			
			<?php 
				$status_repeat = "";
				if( !$is_dev_output ) {
					$status_repeat = "disabled";
				}
			?>
			
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
				
				var str_ext_type = source_info.source_type.toUpperCase();
				
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
				row_data += '	<div class="div_title_loop_count"  style="font-size: 8pt;"><input type="number" class="input_loop_count" min="1" max="99" maxLength="2" value="' + source_info.audio_loop_count + '" ' + (( !is_valid ) ? "disabled" : "") + ' <?php echo $status_repeat; ?>></div>';
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

		function ws_recv_data(_cmd_id, _is_binary, _length, _data, _this) {
			var data = JSON.parse(_data);

			switch( parseInt(_cmd_id) ) {
				case 11 : // volume info
					if( g_num_apply_volume > 0 ) {
						g_num_apply_volume--;
						break;
					}
					
					var volume = data.playVolume;
					$("#slider_value").val(volume);
					$(".slider").val(volume);
					break;

				case 12 : // level info
					var level = data.level;
					$(".level_outputVolume").html(level);
					break;

				case 0x20 : // operation info
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

				case 0x21 :
					set_table(data);
					
					var post_data = source_handler.makeArgs("act", "reload");
					var response  = source_handler.postArgs("<?=Source_file_management\Def\PATH_COMMON_PROCESS ?>", post_data);

					$("#span_upload_available").html(response);
					$("#text_size_upload_available").val(response);

					var post_data = source_handler.makeArgs("act", "reload_ext");
					var response  = source_handler.postArgs("<?=Source_file_management\Def\PATH_COMMON_PROCESS ?>", post_data);

					$("#span_ext_avail_info").html(response);
					$("#text_size_upload_available_ext").val(response);
					
					if( parseInt(response) == -1 ) {
						$("#div_ext_upload_storage").hide();
						$("[name=div_ext_avail_info]").hide();
						$("[name=radio_upload_case][value=upload_internal]").prop("checked", true);
					
					} else {
						$("#div_ext_upload_storage").show();
						$("[name=div_ext_avail_info]").show();
					}
					
					$(".input_source_check_all").prop("checked", false);
					$(".input_source_check").prop("checked", false);

					common_display_handler.clearDotLoader();

					break;

				case 0x25 :
					set_table(data);
					
					var post_data = source_handler.makeArgs("act", "preview_clear");
					source_handler.postArgs("<?=Source_file_management\Def\PATH_COMMON_PROCESS ?>", post_data);

					var post_data = source_handler.makeArgs("act", "reload");
					var response  = source_handler.postArgs("<?=Source_file_management\Def\PATH_COMMON_PROCESS ?>", post_data);

					$("#span_upload_available").html(response);
					$("#text_size_upload_available").val(response);
					
					var post_data = source_handler.makeArgs("act", "reload_ext");
					var response  = source_handler.postArgs("<?=Source_file_management\Def\PATH_COMMON_PROCESS ?>", post_data);
					
					$("#span_ext_avail_info").html(response);
					$("#text_size_upload_available_ext").val(response);
					
					if( parseInt(response) == -1 ) {
						$("#div_ext_upload_storage").hide();
						$("[name=div_ext_avail_info]").hide();
						$("[name=radio_upload_case][value=upload_internal]").prop("checked", true);
					
					} else {
						$("#div_ext_upload_storage").show();
						$("[name=div_ext_avail_info]").show();
					}

					$(".input_source_check_all").prop("checked", false);
					$(".input_source_check").prop("checked", false);

					common_log_handler.info("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_SUCCESS ?>");
					alert("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_SUCCESS ?>");

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

		function ws_open_data(_this) {
			_this.send(0x01, null);

			return ;
		}

		function ws_recv_sndif_func(_cmd_id, _is_binary, _length, _data, _this) {
			if( _cmd_id == 0x01 ) {
				if( status_run_button == "list" ) {
					ws_audio_control_handler.send(0x21, data_run_button);

				} else if( status_run_button == "play" ) {
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
					arr_args["source_loop_count"]	= checked_loop_count.join();
										
					var json_args = JSON.stringify(arr_args);
					ws_audio_control_handler.send(0x10, json_args);
				}

				status_run_button	= "";
				data_run_button		= "";
			}

			return ;
		}

		// create instance
		var common_audio_handler	= document.createElement('audio');

		var common_display_handler	= new CommonDisplayFunc();
		var common_log_handler		= new CommonLogFunc("source_file_management");

		var source_handler			= new SourceFileHandler();

		var ws_audio_player_handler  = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "audio_player");
		ws_audio_player_handler.setOnmessageHandler(ws_recv_data);
		ws_audio_player_handler.run();

		var ws_sndif_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "snd_interface");
		ws_sndif_handler.setOnmessageHandler(ws_recv_sndif_func);
		ws_sndif_handler.run();

		var ws_audio_control_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "audio_player_control");
		ws_audio_control_handler.setOnopenHandler(ws_open_data);
		ws_audio_control_handler.run();

		common_display_handler.showLoader();
	});

</script>

<?php
	include_once 'common_js_etc.php';
?>
