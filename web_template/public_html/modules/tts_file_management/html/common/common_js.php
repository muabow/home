<script type="text/javascript">
	class PostHandler {
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

	var path_preview_tts = "";

	$(document).ready(function() {
		function pad(_n, _width) {
			_n = _n + '';
			return _n.length >= _width ? _n : new Array(_width - _n.length + 1).join('0') + _n;
		}
		
		String.format = function() {
			var format_string = arguments[0];

			for( var idx = 1 ; idx < arguments.length ; idx++ ) {
				var reg_ex = new RegExp("\\{" + (idx - 1) + "\\}", "gm");
				format_string = format_string.replace(reg_ex, arguments[idx]);
			}

			return format_string;
		}
		
		function make_slider(_id, _min_value, _max_value, _dflt_value) {
			var type = _id.slice(6);
			$("#option_" + type).val(_dflt_value);

			$("#" + _id).slider({
				min			: _min_value,
				max			: _max_value,
				value		: _dflt_value,
				step		: 1,
				range		: "min",
				animate		: 'slow',
				orientation	: "horizontal",
				slide		: function(_event, _ui) {
									var type = $(this).attr("id").slice(6);
									$("#option_" + type).val(_ui.value);
									
							},
				stop		: function(_event, _ui) {
									var type = $(this).attr("id").slice(6);
									$("#option_" + type).val(_ui.value);
							}
			});

			$("#option_" + type).on("change paste", function() {
				var type = $(this).attr("id").slice(7);
				
				$(this).val($(this).val().replace(/[^0-9]/g, ""));
				var current_value = $(this).val();

				if( current_value < parseInt($("#input_" + type + "_min").val()) ) {
					$(this).val($("#input_" + type + "_min").val());
				
				} else if( current_value > parseInt($("#input_" + type + "_max").val()) ) {
					$(this).val($("#input_" + type + "_max").val());
				}

				$("#slide_" + type).slider('value', current_value);

				return ;
			});

			return ;
		}

		function set_event_sort_table() {
			$("#sortable").sortable({
				stop: function(_event, _ui) {
					$("[id^=button_monitor_]").each(function() {
						if( $(this).attr("class") ) {
							common_audio_handler.pause();
							common_audio_handler.currentTime = 0;
			
							$(this).removeClass("monitor_play");
						}
					});
			
					var update_order_list = [];
					$(".div_table_row").each(function(_idx, _evt) {
						update_order_list.push($(_evt).attr("id"));
					});
			
					str_sort_list = "";
					for( var idx = 0 ; idx < update_order_list.length ; idx++ ) {
						str_sort_list += update_order_list[idx];
						if( idx + 1 != update_order_list.length ) {
							str_sort_list += "|";
						}
					}
			
					var postHandle = new PostHandler;
					var args = "";
					args += postHandle.makeArgs("act",         "sort");
					args += postHandle.makeArgs("source_name", str_sort_list);
			
					var result = postHandle.postArgs("<?=TTS_file_management\Def\PATH_COMMON_PROCESS ?>", args);
					
					set_table();
					ws_send_reload_table();
					
					return ;
				}
			});

			return ;
		}
		
		function set_table() {
			var num_play_monitor = -1;
			
			$("[id^=button_monitor_]").each(function() {
				if( $(this).attr("class") ) {
					num_play_monitor = $(this).attr("id").split("_")[2];
					}
			});
			
			$("#sortable").sortable("destroy");

			var postHandle = new PostHandler;

			var post_data = postHandle.makeArgs("act", "reload");
			var response  = postHandle.postArgs("<?=TTS_file_management\Def\PATH_COMMON_PROCESS ?>", post_data);

			$("#span_tts_avail_size").html(response);

			var post_data = postHandle.makeArgs("act", "reload_ext");
			var response  = postHandle.postArgs("<?=TTS_file_management\Def\PATH_COMMON_PROCESS ?>", post_data);

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
			
			var args = postHandle.makeArgs("act", "load");
			var data = postHandle.postArgs("<?=TTS_file_management\Def\PATH_COMMON_PROCESS ?>", args);
			
			var tts_list = JSON.parse(data).tts_list;
			var row_data = "";
			
			$.each(tts_list, function(_idx, _tts_info) {
				var str_monitor_class = "";
				if( num_play_monitor == _idx ) {
					str_monitor_class = 'class="monitor_play"';
				}

				var is_ext_storage_file = "div_is_ext_storage_file_empty";
				var storage_type = "internal";
				if( _tts_info.ext_storage ) {
					is_ext_storage_file = "div_is_ext_storage_file";
					storage_type = "external";
				}
				
				row_data += '<div class="div_table_row" id="' + _tts_info.file_path + '">';
				row_data += '	<div class="div_row_number"><button id="button_monitor_' + _idx + '" ' + str_monitor_class + ' style="width : 36px; border : solid 1px grey; cursor : pointer;" name="' + storage_type + '">' + (_idx + 1) + '</button></div>';
				row_data += '	<div class="div_row_tts_name" id="div_tts_name_' + _idx + '">';
				row_data += '		<div class="' + is_ext_storage_file + '"></div>';
				row_data +=     	_tts_info.tts_info.title;
				row_data +=     '</div>';
				row_data += '	<div class="div_row_tts_text"> <div class="div_tts_list_text" id="icon_tts_text_' + _idx + '"></div> </div>';
				row_data += '	<div class="div_row_play_time">' + _tts_info.tts_info.duration + '</div>';
				row_data += '	<div class="div_row_checkBox"><input type="checkbox" class="input_tts_check"></div>';
				row_data += '</div>';
			});

			$(".div_row_wrap").empty();
			$(".div_row_wrap").append(row_data);

			$(".div_row_tts_name").each(function(_idx, _evt) {
				if ($(_evt)[0].scrollWidth > $(_evt).innerWidth()) {
					$(_evt).addClass("overflow_ellipsis");
				}
			});
			
			set_event_sort_table();
			
			$("[id^=icon_tts_text]").click(function() {
				var arr_id = $(this).attr("id").split("_");
				var index  = arr_id[arr_id.length - 1];

				var postHandle = new PostHandler;
				var args = postHandle.makeArgs("act", "load");
				var data = postHandle.postArgs("<?=TTS_file_management\Def\PATH_COMMON_PROCESS ?>", args);
				
				var tts_list = JSON.parse(data).tts_list;
				
				$("#text_tts_title").val(tts_list[index].tts_info.title);
				$("#select_tts_language").val(tts_list[index].tts_info.language);
				$("#select_tts_gender").val(tts_list[index].tts_info.gender);
				$("#text_tts_speak").val(tts_list[index].tts_info.text);
				
				var str_tts_text = $("#text_tts_speak").val();
				for( byte = idx = 0 ; chr = str_tts_text.charCodeAt(idx++) ; byte += (chr >> 11 ? 3 : chr >> 7 ? 2 : 1));
				$("#span_tts_text_limit").html(byte);

				$("#select_chime_begin").val("");
				$("#select_chime_begin option").each(function(_idx, _item) {
					if( $(this).val() == tts_list[index].chime_info.begin ) {
						$("#select_chime_begin").val(tts_list[index].chime_info.begin);
						return false;
					}
				});
				
				$("#select_chime_end").val("");
				$("#select_chime_end option").each(function(_idx, _item) {
					if( $(this).val() == tts_list[index].chime_info.end ) {
						$("#select_chime_end").val(tts_list[index].chime_info.end);
						return false;
					}
				});

				$("#slide_pct_pitch").slider('value', tts_list[index].tts_info.option.pitch);
				$("#option_pct_pitch").val(tts_list[index].tts_info.option.pitch);
				$("#slide_pct_speed").slider('value', tts_list[index].tts_info.option.speed);
				$("#option_pct_speed").val(tts_list[index].tts_info.option.speed);
				$("#slide_pct_volume").slider('value', tts_list[index].tts_info.option.volume);
				$("#option_pct_volume").val(tts_list[index].tts_info.option.volume);
				$("#slide_num_sp").slider('value', tts_list[index].tts_info.option.sentence_pause);
				$("#option_num_sp").val(tts_list[index].tts_info.option.sentence_pause);
				$("#slide_num_cp").slider('value', tts_list[index].tts_info.option.comma_pause);
				$("#option_num_cp").val(tts_list[index].tts_info.option.comma_pause);

				return ;
			});

			return ;
		}

		function func_tts_create(_req) {
			if( _req.readyState == <?=Common\Def\READY_STAT_SUCCESS ?> ) {
				if( _req.status == <?=Common\Def\STATUS_SUCCESS ?> ) {
					path_preview_tts = _req.responseText;
					
					if( path_preview_tts == "" ) {
						var str_duration = String.format("[00:00.00]");
						$("#span_tts_duration").html(str_duration);
						
						common_display_handler.clearDotLoader();

						alert("<?=TTS_file_management\Lang\STR_TTS_ERROR_ALERT ?>");
						return ;
					}
					
					common_audio_handler.onloadedmetadata = function(_evt) {
						var time_min  = pad(parseInt(common_audio_handler.duration / 60), 2);
						var time_sec  = pad(parseInt(common_audio_handler.duration % 60), 2);
						var calc_msec = (common_audio_handler.duration - parseInt(common_audio_handler.duration)) * 100;
						var time_msec = pad(Math.floor(calc_msec), 2);
						
						var str_duration = String.format("[{0}:{1}.{2}]", time_min, time_sec, time_msec);
						$("#span_tts_duration").html(str_duration);
					};
				
					common_audio_handler.setAttribute('id',      "audio_handle");
					common_audio_handler.setAttribute('preload', "none");
					common_audio_handler.setAttribute('src', 	 path_preview_tts);

					common_audio_handler.load();
					
					common_display_handler.clearDotLoader();
					
					if( path_preview_tts == "" ) {
						alert("<?=TTS_file_management\Lang\STR_TTS_ACT_INPUT_PREVIEW ?>")   
						return ;
					}

					common_audio_handler.pause();
					common_audio_handler.play();
				}
			}

			return ;
		}

		function func_tts_save(_req) {
			if( _req.readyState == <?=Common\Def\READY_STAT_SUCCESS ?> ) {
				if( _req.status == <?=Common\Def\STATUS_SUCCESS ?> ) {
					path_preview_tts = _req.responseText;
					
					if( path_preview_tts == "" ) {
						var str_duration = String.format("[00:00.00]");
						$("#span_tts_duration").html(str_duration);
						
						common_display_handler.clearDotLoader();

						alert("<?=TTS_file_management\Lang\STR_TTS_ERROR_ALERT ?>");
						return ;
					}

					$("#button_reset_tts").trigger("click");

					set_table();
					ws_send_reload_table();
					
					common_display_handler.clearDotLoader();
				}
			}

			return ;
		}

		function ws_send_reload_table() {
			var arr_ws_data = {};
			arr_ws_data["type"] = 0x10;
			arr_ws_data["data"] = "";
			ws_handler.send(0x10, JSON.stringify(arr_ws_data));

			return ;
		}

		// init slider
		make_slider("slide_pct_pitch", 	50, 200,	100);
		make_slider("slide_pct_speed",	50, 400,	100);
		make_slider("slide_pct_volume",	 0, 500,	100);
		make_slider("slide_num_sp", 	 0, 65536, 	0);
		make_slider("slide_num_cp", 	 0, 65536, 	0);
		

		$("#text_tts_speak").on('keyup keydown keypress change', function() {
			var str_tts_text = $(this).val();
			for( byte = idx = 0 ; chr = str_tts_text.charCodeAt(idx++) ; byte += (chr >> 11 ? 3 : chr >> 7 ? 2 : 1));
			
			$("#span_tts_text_limit").text(byte);
			if( byte > <?=TTS_file_management\Def\MAX_BYTES_TTS_TEXT ?> ) {
				$("#span_tts_text_limit").css("color", "red");
			
			} else {
				$("#span_tts_text_limit").css("color", "black");
			}
			return ;
		});

		function create_tts_file() {
			var is_valid_input = true;
            $("[id^=text_tts], [id^=select_tts]").each(function(_idx, _item) {
                if( $.trim($(this).val()) == "" ) {
                    switch( $(this).attr("id") ) {
                        case "text_tts_title"       : alert("<?=TTS_file_management\Lang\STR_TTS_ACT_INPUT_TITLE ?>");		break;
                        case "text_tts_speak"       : alert("<?=TTS_file_management\Lang\STR_TTS_ACT_INPUT_TEXT ?>");		break;
                        case "select_tts_language"  : alert("<?=TTS_file_management\Lang\STR_TTS_ACT_INPUT_LANGUAGE ?>");	break;
                        case "select_tts_gender"    : alert("<?=TTS_file_management\Lang\STR_TTS_ACT_INPUT_GENDER ?>");		break;
                        default : break;
                    }

                    $(this).focus();
                    is_valid_input = false;
                    return false;
                }
			});
			
            if( !is_valid_input ) return false;

			var str_tts_text = $("#text_tts_speak").val();
			for( byte = idx = 0 ; chr = str_tts_text.charCodeAt(idx++) ; byte += (chr >> 11 ? 3 : chr >> 7 ? 2 : 1));
			if( byte > <?=TTS_file_management\Def\MAX_BYTES_TTS_TEXT ?> ) {
				alert("<?=TTS_file_management\Lang\STR_TTS_ACT_LIMIT_BYTES_OVER ?>");
				$("#text_tts_speak").focus();

				return false;
			}
			
            // option list - pitch, speed, volume, sentence_pause, comma_pause
            var arr_option_list = [$("#option_pct_pitch").val(), $("#option_pct_speed").val(), $("#option_pct_volume").val(), 
                                   $("#option_num_sp").val(), $("#option_num_cp").val()];

            var str_option_info = String.format("{\"pitch\":{0}, \"speed\":{1}, \"volume\":{2}, \"sentence_pause\":{3}, \"comma_pause\":{4}}", 
                                                arr_option_list[0], arr_option_list[1], arr_option_list[2], arr_option_list[3], arr_option_list[4]);

			var vtag_text_data = "<vtml_pause time=\"500\"/>" + $("#text_tts_speak").val() + "<vtml_pause time=\"500\"/>";
			var commonFunc = new CommonFunc();
            var args = "";
            args += commonFunc.makeArgs("act",      "create");
            args += commonFunc.makeArgs("language", $("#select_tts_language").val());
            args += commonFunc.makeArgs("gender",   $("#select_tts_gender").val());
            args += commonFunc.makeArgs("text",     vtag_text_data);
			args += commonFunc.makeArgs("option",   str_option_info);
			
			common_display_handler.showLoader();
			
			commonFunc.postArgsAsync("<?=TTS_file_management\Def\PATH_COMMON_PROCESS ?>", args, func_tts_create, "POST");

			return true;
		}

        $("#button_preview_tts").click(function() {
			// 파일명 특수문자 여부 체크
			var invalid_case_special = /[`&]/g;
			if( 	invalid_case_special.test($("#text_tts_title").val()) == true 
				|| 	invalid_case_special.test($("#text_tts_speak").val()) == true ) {
				
					alert("<?=TTS_file_management\Lang\STR_TTS_INVALID_WORD ?>");
				return ;
			}
			
			if( !create_tts_file() ) return ;

            return ;
        });

        $("#button_reset_tts").click(function() {
            path_preview_tts = "";
            $("#span_tts_duration").html("[00:00.00]");
            $("#text_tts_speak").css("height", $("#text_tts_speak").css("min-height"));

            $("[id^=text_tts], [id^=select_tts]").each(function(_idx, _item) {
                $(this).val("");
            });
			
			$("[id^=select_chime]").each(function(_idx, _tem) {
				$(this).val("-");
			});
			
			$("[id^=option_pct]").each(function(_idx, _item) {
                $(this).val(100);
			});

			$("[id^=slide_pct]").each(function(_idx, _item) {
				$(this).slider('value', 100);
			});
			
            $("[id^=option_num]").each(function(_idx, _item) {
				$(this).val(0);
			});
			
			$("[id^=slide_num]").each(function(_idx, _item) {
				$(this).slider('value', 0);
			});

			$("#span_tts_text_limit").html("0");
            
            return ;
        });

        $("#button_save_tts").click(function() {
			// 파일명 특수문자 여부 체크
			var invalid_case_special = /[`&]/g;
			if( 	invalid_case_special.test($("#text_tts_title").val()) == true 
				|| 	invalid_case_special.test($("#text_tts_speak").val()) == true ) {
				
					alert("<?=TTS_file_management\Lang\STR_TTS_INVALID_WORD ?>");
				return ;
			}
			
			if( $("[name=radio_upload_case]:checked").val() == "upload_internal" ) {
				if( parseInt($("#span_tts_avail_size").html()) <= 0 ) {
					alert("<?=TTS_file_management\Lang\STR_TTS_ACT_EXCEED_CAPACITY ?>");
					return ;
				}
			
			} else {
				if( parseInt($("#span_ext_avail_info").html()) <= 0 ) {
					alert("<?=TTS_file_management\Lang\STR_TTS_ACT_EXCEED_CAPACITY ?>");
					return ;
				}
			}

            var is_valid_input = true;
            $("[id^=text_tts], [id^=select_tts]").each(function(_idx, _item) {
                if( $.trim($(this).val()) == "" ) {
                    switch( $(this).attr("id") ) {
                        case "text_tts_title"       : alert("<?=TTS_file_management\Lang\STR_TTS_ACT_INPUT_TITLE ?>");		break;
                        case "text_tts_speak"       : alert("<?=TTS_file_management\Lang\STR_TTS_ACT_INPUT_TEXT ?>");		break;
                        case "select_tts_language"  : alert("<?=TTS_file_management\Lang\STR_TTS_ACT_INPUT_LANGUAGE ?>");	break;
                        case "select_tts_gender"    : alert("<?=TTS_file_management\Lang\STR_TTS_ACT_INPUT_GENDER ?>");		break;
                        default : break;
                    }

                    $(this).focus();
                    is_valid_input = false;
                    return false;
                }
            });
            if( !is_valid_input ) return ;

			/*
            if( path_preview_tts == "" ) {
                alert("<?=TTS_file_management\Lang\STR_TTS_ACT_INPUT_PREVIEW ?>");  
                return ;
			}
			*/

            if( !confirm("<?=TTS_file_management\Lang\STR_TTS_ACT_INPUT_CONFIRM_SAVE ?>") ) {
                return ;
			}

			$("[id^=select_chime]").each(function(_idx, _item) {
				if( $(this).val() == "-" ) $(this).val("");
			});

            var arr_option_list = [$("#option_pct_pitch").val(), $("#option_pct_speed").val(), $("#option_pct_volume").val(), 
                                   $("#option_num_sp").val(), $("#option_num_cp").val()];

            var str_option_info = String.format("{\"pitch\":{0}, \"speed\":{1}, \"volume\":{2}, \"sentence_pause\":{3}, \"comma_pause\":{4}}", 
                                                arr_option_list[0], arr_option_list[1], arr_option_list[2], arr_option_list[3], arr_option_list[4]);
            
			var postHandle = new PostHandler;
			var args = postHandle.makeArgs("act", "load");
			var data = postHandle.postArgs("<?=TTS_file_management\Def\PATH_COMMON_PROCESS ?>", args);
			var tts_list = JSON.parse(data).tts_list
			
			var is_exist_tts_title = false;
			$(tts_list).each(function(_idx, _tts_info) {
				if( _tts_info.tts_info.title == $("#text_tts_title").val() ) {
					is_exist_tts_title = true;
					return false;
				}
			});
			
			if( is_exist_tts_title ) {
				alert("<?=TTS_file_management\Lang\STR_TTS_ACT_INPUT_DUP_TITLE ?>");
				return ;
			}

			var commonFunc = new CommonFunc();
            var args = "";
            args += commonFunc.makeArgs("act",          "save");
            args += commonFunc.makeArgs("file_path",    path_preview_tts);
            args += commonFunc.makeArgs("title",        $("#text_tts_title").val());
            args += commonFunc.makeArgs("language",     $("#select_tts_language").val());
            args += commonFunc.makeArgs("gender",       $("#select_tts_gender").val());
            args += commonFunc.makeArgs("text",         $("#text_tts_speak").val());
            args += commonFunc.makeArgs("option",       str_option_info);
            args += commonFunc.makeArgs("duration",     $("#span_tts_duration").html().replace(/[\[\]']/g,''));
            args += commonFunc.makeArgs("chime_begin",  $("#select_chime_begin").val());
			args += commonFunc.makeArgs("chime_end",    $("#select_chime_end").val());
			args += commonFunc.makeArgs("storage",      $("[name=radio_upload_case]:checked").val());
			
			common_display_handler.showLoader();
			
			commonFunc.postArgsAsync("<?=TTS_file_management\Def\PATH_COMMON_PROCESS ?>", args, func_tts_save, "POST");

            return ;
        });

		$(document).on("click", "[id^=button_monitor_]", function() {
			common_audio_handler.onloadedmetadata = "";
		
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
			var fileName = $(this).parents().parents().attr("id");
			var filePath = 'modules/tts_file_management/html/data/audiofiles/' + fileName;

			var is_exist_ext = ($(this).attr("name") == "external" ? true : false);

			if( is_exist_ext ) {
				var postHandle = new PostHandler;

				var post_data = "";
				post_data += postHandle.makeArgs("act", "preview");
				post_data += postHandle.makeArgs("src", fileName);
				postHandle.postArgs("<?=TTS_file_management\Def\PATH_COMMON_PROCESS ?>", post_data);
				
				filePath = 'modules/tts_file_management/html/data/' + fileName;
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

		$(".input_tts_check_all").click(function() {
			var is_checked = $(this).is(":checked");

			$(".input_tts_check").prop("checked", is_checked);
			return ;
		});

		$("#control_button_remove").click(function() {
			var checked_source = [];
			$(".input_tts_check").each(function(_idx, _evt) {
				if( $(_evt).is(":checked") ) {
					checked_source.push($(_evt).closest(".div_table_row").attr("id"));
				}
			});

			if( checked_source.length == 0 ) {
				return ;
			}

			if( !confirm("<?=TTS_file_management\Lang\STR_TTS_ACT_DEL_APPLY ?>") ) {
				return ;
			}

			str_checked_list = "";
			for( var idx = 0 ; idx < checked_source.length ; idx++ ) {
				common_log_handler.info("<?=TTS_file_management\Lang\STR_TTS_ACT_DEL ?> : " + checked_source[idx]);

				str_checked_list += checked_source[idx];
				if( idx + 1 != checked_source.length ) {
					str_checked_list += "|";
				}
			}

			$(".input_tts_check_all").prop("checked", false);
			$(".input_tts_check").prop("checked", false);
			
			var postHandle = new PostHandler;
			var args = "";
			args += postHandle.makeArgs("act",         "remove");
			args += postHandle.makeArgs("source_name", str_checked_list);

			var result = postHandle.postArgs("<?=TTS_file_management\Def\PATH_COMMON_PROCESS ?>", args);
			
			set_table();
			ws_send_reload_table();

			var post_data = postHandle.makeArgs("act", "preview_clear");
			postHandle.postArgs("<?=TTS_file_management\Def\PATH_COMMON_PROCESS ?>", post_data);

			return ;
		});

		$("#control_button_copy").click(function() {
			var checked_source = [];
			$(".input_tts_check").each(function(_idx, _evt) {
				if( $(_evt).is(":checked") ) {
					checked_source.push($(_evt).closest(".div_table_row").attr("id"));
				}
			});

			if( checked_source.length == 0 ) {
				return ;
			}

			if( !confirm("<?=TTS_file_management\Lang\STR_TTS_ACT_COPY_APPLY ?>") ) {
				return ;
			}


			str_checked_list = "";
			for( var idx = 0 ; idx < checked_source.length ; idx++ ) {

				str_checked_list += checked_source[idx];
				if( idx + 1 != checked_source.length ) {
					str_checked_list += "|";
				}
			}

			$(".input_tts_check_all").prop("checked", false);
			$(".input_tts_check").prop("checked", false);
			
			var postHandle = new PostHandler;
			var args = "";
			args += postHandle.makeArgs("act",         "copy");
			args += postHandle.makeArgs("source_name", str_checked_list);

			var result = postHandle.postArgs("<?=TTS_file_management\Def\PATH_COMMON_PROCESS ?>", args);

			alert("<?=TTS_file_management\Lang\STR_TTS_ACT_COPY_COMPLETE ?>");

			return ;
		});

		function ws_recv_func(_cmd_id, _is_binary, _length, _data, _this) {
			switch( parseInt(_cmd_id) ) {
				case 0x10 :
					set_table();
					break;
				
				case 0x20 :
					set_table();
					break;

				default :
					break;
			}

			return ;
		}

        var common_audio_handler = document.createElement('audio');
		var common_log_handler   = new CommonLogFunc("tts_file_management");

		var ws_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "tts_file_management");
		ws_handler.setOnmessageHandler(ws_recv_func);
		ws_handler.set_route_to(1);
		ws_handler.run();

		var common_display_handler = new CommonDisplayFunc();

		set_event_sort_table();
		set_table();
	});

</script>

<?php
	include_once 'common_js_etc.php';
?>
