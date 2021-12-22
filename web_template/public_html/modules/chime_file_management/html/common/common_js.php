<script type="text/javascript">
	class ChimeFileHandler {
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
		// 1.1. 음원 파일 업로드 - 음원 파일 선택
		$('.filebox_upload .upload-hidden').on('change', function() {
			var str_chime_name = "";
			var num_uploaded_chime = 0;

			if( window.FileReader ) {
				if( $(this).val() == "" ) {
					str_chime_name = "<?=Chime_file_management\Lang\STR_SRCFILE_ADD_FIND ?>";

				} else {
					str_chime_name = $(this)[0].files[0].name;
					num_uploaded_chime = $(this)[0].files.length;
				}

			} else { // old IE
				str_chime_name = $(this).val().split('/').pop().split('\\').pop();
			}

			if( num_uploaded_chime > 1 ) {
				$(this).siblings('.upload-name').val(str_chime_name + " <?=Chime_file_management\Lang\STR_AND ?> " + (num_uploaded_chime - 1) + " <?=Chime_file_management\Lang\STR_OTHER ?>");

			} else {
				$(this).siblings('.upload-name').val(str_chime_name);
			}

			return ;
		});

		// 1.2. 음원 파일 업로드 - 음원 파일 업로드
		$("#div_button_upload_apply").click(function() {
			var upload_chime_list = $("#file_uploadFile")[0].files;

			// 파일 첨부 검출
			if( upload_chime_list.length == 0 ) {
				alert("<?=Chime_file_management\Lang\STR_SRCFILE_ADD_SELECT ?>");
				return ;
			}
			
			var args = "";
			args += chime_handler.makeArgs("act", "get_file_list");

			var json_data = chime_handler.postArgs("<?=Chime_file_management\Def\PATH_COMMON_PROCESS ?>", args);
			
			var num_file_list = 0;
			if( JSON.parse(json_data).chime_list != undefined ) {
				num_file_list = JSON.parse(json_data).chime_list.length;
			}
			
			if( upload_chime_list.length + parseInt(num_file_list) > <?=Chime_file_management\Def\MAX_UPLOAD_FILE_COUNT ?> ) {
				var alert_msg = "<?=Chime_file_management\Lang\STR_SRCFILE_ADD_LIMIT_COUNT ?> ";
				alert_msg += "[" + (upload_chime_list.length + parseInt(num_file_list)) + "/" + <?=Chime_file_management\Def\MAX_UPLOAD_FILE_COUNT ?> + "]";

				alert(alert_msg);
				return ;
			}

			var invalid_name_list_special 		= "";
			var invalid_name_list_double_space	= "";
			var invalid_name_list_ext_type		= "";
			var invalid_name_length				= "";

			$.each(upload_chime_list, function(_i, _e) {
				var str_chime_name = _e.name;

				// 파일명 길이 체크
				for( bc = idx = 0 ; c = str_chime_name.charCodeAt(idx++) ; bc += (c >> 11 ? 3 : (c >> 7 ? 2 : 1)) );
				if( bc >= 255 ) {
					invalid_name_length += str_chime_name + "\n";
				}

				// 파일명 띄어쓰기 2개 연속 체크
				var invalid_case_double_space = "  ";
				if( str_chime_name.indexOf(invalid_case_double_space) > -1 ) {
					invalid_name_list_double_space += str_chime_name + "\n";
				}

				// 파일명 특수문자 여부 체크
				var invalid_case_special = /[`'*|\\\"\/?#%:<>&$+]/g;
				if( invalid_case_special.test(str_chime_name) == true ) {
					invalid_name_list_special += str_chime_name + "\n";
				}

				// 파일 확장자 검출
				var chime_ext_type = _e.type.toLowerCase();
				var chime_ext_name = _e.name.toLowerCase();

				var reg = /(.*?)\.(mp3)$/;
				if( !(chime_ext_type == "audio/mp3" || chime_ext_type == "audio/wav" || (chime_ext_name.match(reg) && chime_ext_type == "audio/mpeg")) ) {
					invalid_name_list_ext_type += str_chime_name + "\n";
				}
			});

			if( invalid_name_length != "" || invalid_name_list_special != "" || invalid_name_list_double_space != "" || invalid_name_list_ext_type != "" ) {
				var noti_invalid_case = "";

				if( invalid_name_length != "" ) {
					noti_invalid_case += "<?=Chime_file_management\Lang\STR_SRCFILE_ADD_INVALID_LENGTH ?>\n-------- <?=Chime_file_management\Lang\STR_SRCFILE_TABLE ?> --------\n" + invalid_name_length;
					noti_invalid_case += "\n";
				}
				
				if( invalid_name_list_ext_type != "" ) {
					noti_invalid_case += "<?=Chime_file_management\Lang\STR_SRCFILE_ADD_INVALID_TYPE ?>\n-------- <?=Chime_file_management\Lang\STR_SRCFILE_TABLE ?> --------\n" + invalid_name_list_ext_type;
					noti_invalid_case += "\n";
				}

				if( invalid_name_list_special != "" ) {
					noti_invalid_case += "<?=Chime_file_management\Lang\STR_SRCFILE_ADD_INVALID_NAME ?>\n-------- <?=Chime_file_management\Lang\STR_SRCFILE_TABLE ?> --------\n" + invalid_name_list_special;
					noti_invalid_case += "\n";
				}

				if( invalid_name_list_double_space != "" ) {
					noti_invalid_case += "<?=Chime_file_management\Lang\STR_SRCFILE_ADD_DOUBLE_SPACE_NAME ?>\n-------- <?=Chime_file_management\Lang\STR_SRCFILE_TABLE ?> --------\n" + invalid_name_list_double_space;
					noti_invalid_case += "\n";
				}

				alert(noti_invalid_case);

				$("#div_button_upload_clear").trigger("click");

				return;
			}

			// 파일 사이즈 검출
			var size_upload_available = $("#text_size_upload_available").val();
			var sum_size_chime_list	= 0;
			
			if( $("[name=radio_upload_case]:checked").val() == "upload_external" ) {
				size_upload_available	= $("#text_size_upload_available_ext").val();
			}

			$.each(upload_chime_list, function(_i, _e) {
				sum_size_chime_list += _e.size;
			});
			sum_size_chime_list = Math.round(sum_size_chime_list / 1024 / 1024);

			if( size_upload_available < sum_size_chime_list ) {
				alert("<?=Chime_file_management\Lang\STR_SRCFILE_ADD_LIMIT_MEM_ALL ?>\n" + "<?=Chime_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL_UPLOAD_SIZE ?> : " + sum_size_chime_list + " Mbytes / <?=Chime_file_management\Lang\STR_SRCFILE_ADD_AVAILABLE_MEM ?> : " + size_upload_available + " Mbytes");

				return;
			}

			if( confirm("<?=Chime_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_CONFIRM ?>") ) {
				common_log_handler.info("<?=Chime_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_APPLY ?>");

			} else {
				return;
			}

			common_display_handler.showLoader();


			// 파일 업로드
			var form_chime_list = new FormData();
			var json_data;

			$.each(upload_chime_list, function(_idx, _evt) {
				form_chime_list.append("file_" + _idx, _evt);
				common_log_handler.info("<?=Chime_file_management\Lang\STR_SRCFILE_ADD_UPLOAD ?> : " + _evt.name);
			});

			form_chime_list.append("storage", $("[name=radio_upload_case]:checked").val());

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
							func_async_req_set_table();

							alert("<?=Chime_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_SUCCESS ?>");
							common_display_handler.clearDotLoader();

						} else {
							setTimeout(function() {
								switch( json_data.code ) {
									case -1 :
										alert("<?=Chime_file_management\Lang\STR_SRCFILE_ADD_NOT_FOUND_FILE ?>");
										common_log_handler.info("<?=Chime_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL ?> (<?=Chime_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL_NOTFOUND_FILE ?>)");
									break;

									case -2 :
										alert("<?=Chime_file_management\Lang\STR_SRCFILE_ADD_LIMIT_COUNT ?>");
										common_log_handler.info("<?=Chime_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL ?> (<?=Chime_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL_OVER_FILECNT ?>)");
									break;

									case -3 :
										alert("<?=Chime_file_management\Lang\STR_SRCFILE_ADD_LIMIT_MEM_ALL ?>");
										common_log_handler.info("<?=Chime_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL ?> (<?=Chime_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL_OVER_SAVESIZE ?>)");
									break;

									default :
										alert("<?=Chime_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL ?>");
										common_log_handler.info("<?=Chime_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL ?> (" + responseText + ")");
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

			request.open('POST', "<?=Chime_file_management\Def\PATH_UPLOAD_PROCESS ?>");
			request.send(form_chime_list);

			var post_data = chime_handler.makeArgs("act", "preview_clear");
			chime_handler.postArgs("<?=Chime_file_management\Def\PATH_COMMON_PROCESS ?>", post_data);

			return ;
		});

		// 1.3. 음원 파일 업로드 - 초기화
		$("#div_button_upload_clear").click(function() {
			$("#file_uploadFile").val("");
			$("#label_uploadFile").val("<?=Chime_file_management\Lang\STR_SRCFILE_ADD_FIND ?>");

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
			var fileName = $("#div_chime_name_" + index).text().trim();
			var filePath = 'modules/chime_file_management/html/data/audiofiles/' + fileName;

			var is_exist_ext = ($(this).attr("name") == "external" ? true : false);

			if( is_exist_ext ) {
				var post_data = "";
				post_data += chime_handler.makeArgs("act", "preview");
				post_data += chime_handler.makeArgs("src", fileName);
				chime_handler.postArgs("<?=Chime_file_management\Def\PATH_COMMON_PROCESS ?>", post_data);
				
				filePath = 'modules/chime_file_management/html/data/' + fileName;
			}

			common_audio_handler.setAttribute('preload', "none");
			common_audio_handler.setAttribute('src', 	 filePath);

			common_audio_handler.play();

			common_audio_handler.onended = function() {
				common_audio_handler.pause();
				common_audio_handler.currentTime = 0;

				$("[id^=button_monitor_]").removeClass("monitor_play");
			};

			common_audio_handler.ontimeupdate = function(_event) {
				if( _event.path[0].currentTime > 10 ) {
					common_audio_handler.pause();
					common_audio_handler.currentTime = 0;

					$("[id^=button_monitor_]").removeClass("monitor_play");
				}
			};

			return ;
		});
		
		setSortableEvent();

		$(".input_chime_check_all").click(function() {
			var is_checked = $(this).is(":checked");

			$(".input_chime_check").prop("checked", is_checked);

			gCheckChimeList = [];
			$(".input_chime_check").each(function(_idx, _evt) {
				if ( $(_evt).is(":checked") ) {
					var check_id = $(_evt).closest(".div_table_row").attr("id");
					gCheckChimeList.push(check_id);
				}
			});
		});

		$(document).on("click", ".input_chime_check", function() {
			if ( !$(this).is(":checked") ) {
				$(".input_chime_check_all").prop("checked", false);
			}

			gCheckChimeList = [];
			$(".input_chime_check").each(function(_idx, _evt) {
				if ( $(_evt).is(":checked") ) {
					var check_id = $(_evt).closest(".div_table_row").attr("id");
					gCheckChimeList.push(check_id);
				}
			});
		});

		$(document).on("mouseover", ".overflow_ellipsis", function() {
			var text = $(this).html();
			var tooltip = '<div class="chime_name_tooltip"><p>' + text + '</p></div>';
			$('html').append(tooltip);

			var divLeft = $(this).offset().left;
			var divTop = $(this).offset().top + 25;

			$('.chime_name_tooltip').css({
				"top": divTop,
				"left": divLeft
			});
		});

		$(document).on("mouseout", ".overflow_ellipsis", function() {
			$(".chime_name_tooltip").remove();
		});

		$(".div_row_wrap").scroll(function() {
			$(".chime_name_tooltip").remove();
		});
		
		$("#control_button_download").click(function() {
			var checked_chime = [];

			$(".input_chime_check").each(function(_idx, _evt) {
				if( $(_evt).is(":checked") ) {
					var chime_name = $(_evt).closest(".div_table_row").find(".div_chime_name").html();
					var file_path = 'modules/chime_file_management/html/data/audiofiles/';

					var is_exist_ext = $(_evt).parent().parent().find(".div_is_ext_storage_file").length;
					if( is_exist_ext ) {
						var post_data = "";
						post_data += chime_handler.makeArgs("act", "preview");
						post_data += chime_handler.makeArgs("src", chime_name);
						chime_handler.postArgs("<?=Chime_file_management\Def\PATH_COMMON_PROCESS ?>", post_data);
						
						file_path = 'modules/chime_file_management/html/data/';
					}

					file_path += chime_name;
					
					var download_link = $("<a>").attr("href", file_path).attr("download", chime_name).appendTo("body");
					download_link[0].click();
					download_link.remove();
				}
			});

			$(".input_chime_check_all").prop("checked", false);
			$(".input_chime_check").prop("checked", false);

			return ;
		});

		$("#control_button_remove").click(function() {
			var checked_source = [];
			$(".input_chime_check").each(function(_idx, _evt) {
				if( $(_evt).is(":checked") ) {
					checked_source.push($(_evt).closest(".div_table_row").attr("id"));
				}
			});

			if( checked_source.length == 0 ) {
				return ;
			}

			if( !confirm("<?=Chime_file_management\Lang\STR_SRCFILE_DEL_SELECT_APPLY ?>") ) {
				return ;
			}

			str_checked_list = "";
			for( var idx = 0 ; idx < checked_source.length ; idx++ ) {
				common_log_handler.info("<?=Chime_file_management\Lang\STR_SRCFILE_DEL ?> : " + checked_source[idx]);

				str_checked_list += checked_source[idx];
				if( idx + 1 != checked_source.length ) {
					str_checked_list += "|";
				}
			}

			$(".input_chime_check_all").prop("checked", false);
			$(".input_chime_check").prop("checked", false);

			var args = "";
			args += chime_handler.makeArgs("act",         "remove_file");
			args += chime_handler.makeArgs("source_name", str_checked_list);

			var result = chime_handler.postArgs("<?=Chime_file_management\Def\PATH_COMMON_PROCESS ?>", args);
			set_table(result);

			var post_data = chime_handler.makeArgs("act", "preview_clear");
			chime_handler.postArgs("<?=Chime_file_management\Def\PATH_COMMON_PROCESS ?>", post_data);

			
			return ;
		});


		var gChimeOrderList = [];
		var gCheckChimeList = [];

		function pad(_n, _width) {
			_n = _n + '';
			return _n.length >= _width ? _n : new Array(_width - _n.length + 1).join('0') + _n;
		}
		
		function setSortableEvent() {
			$( "#sortable" ).sortable({
				stop: function(_event, _ui) {
					update_chime_order();
				}
			});
		}
		
		function update_chime_order() {
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

			if( JSON.stringify(gChimeOrderList) == JSON.stringify(update_order_list) ) {
				return ;
			}

			str_sort_list = "";
			for( var idx = 0 ; idx < update_order_list.length ; idx++ ) {
				str_sort_list += update_order_list[idx];
				if( idx + 1 != update_order_list.length ) {
					str_sort_list += "|";
				}
			}

			var args = "";
			args += chime_handler.makeArgs("act",         "sort_file_list");
			args += chime_handler.makeArgs("source_name", str_sort_list);

			var result = chime_handler.postArgs("<?=Chime_file_management\Def\PATH_COMMON_PROCESS ?>", args);
			set_table(result);

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
			
			var remain_size = JSON.parse(_data).remain_size;
			$("#text_size_upload_available").val(remain_size);
			$("#span_upload_available").html(remain_size);

			var post_data = chime_handler.makeArgs("act", "reload_ext");
			var response  = chime_handler.postArgs("<?=Chime_file_management\Def\PATH_COMMON_PROCESS ?>", post_data);

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
			
			var chime_list = JSON.parse(_data).chime_list;
			var row_data = "";

			gChimeOrderList = [];
			$.each(chime_list, function(_idx, _chime_info) {
				var play_time_min = pad(Math.floor(_chime_info.audio_play_time / 60), 2);
				var play_time_sec = pad(_chime_info.audio_play_time % 60, 2);
				var play_time = play_time_min + ':' + play_time_sec;
				
				var str_monitor_class = "";
				if( num_play_monitor == _idx ) {
					str_monitor_class = 'class="monitor_play"';
				}
				
				var is_ext_storage_file = "div_is_ext_storage_file_empty";
				var storage_type = "internal";
				if( _chime_info.ext_storage ) {
					is_ext_storage_file = "div_is_ext_storage_file";
					storage_type = "external";
				}

				row_data += '<div class="div_table_row" id="' + _chime_info.chime_name + '">';
				row_data += '	<div class="div_title_number"><button id="button_monitor_' + _idx + '" ' + str_monitor_class + ' style="width : 36px; border : solid 1px grey; cursor : pointer;" name="' + storage_type + '">' + (_idx + 1) + ' </button></div>';
				row_data += '	<div class="div_title_chime_name" style="font-size: 8pt;">';
				row_data += '		<div class="' + is_ext_storage_file + '"></div>';
				row_data += '		<div class="div_chime_name" id="div_chime_name_' + _idx + '">' + _chime_info.chime_name + '</div>';
				row_data += '	</div>';
				row_data += '	<div class="div_title_play_time" style="font-size: 8pt;">' + play_time + '</div>';
				row_data += '	<div class="div_title_checkBox" style="font-size: 8pt;"><input type="checkbox" class="input_chime_check"></div>';
				row_data += '</div>';

				gChimeOrderList.push(_chime_info.chime_name);
			});

			$(".div_row_wrap").empty();
			$(".div_row_wrap").append(row_data);

			$(".div_chime_name").each(function(_idx, _evt) {
				if ($(_evt)[0].scrollWidth > $(_evt).innerWidth()) {
					$(_evt).addClass("overflow_ellipsis");
				}
			});
			
			setSortableEvent();
			
			// update chime_ctrl option list
			ws_handler.send(0x04, null);
			
			return ;
		}

		function func_async_req_set_table() {
			common_display_handler.showLoader();
			var args = commonFunc.makeArgs("act",         "get_file_list");
			common_func_handler.postArgsAsync("<?=Chime_file_management\Def\PATH_COMMON_PROCESS ?>", args, func_async_res_set_table);

			return ;
		}

		function func_async_res_set_table(_req) {
			if( _req.readyState == <?=Common\Def\READY_STAT_SUCCESS ?> ) {
				if( _req.status == <?=Common\Def\STATUS_SUCCESS ?> ) {
					var responseText = _req.responseText;
					set_table(responseText);

					common_display_handler.clearDotLoader();
				}
			}

			return ;
		}

		function ws_recv_data(_cmd_id, _is_binary, _length, _data, _this) {
			switch( parseInt(_cmd_id) ) {
				case 0x20 : // reload page
					func_async_req_set_table();

					break;
			}

			return ;
		}


		// create instance
		var ws_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "chime_ctrl");
		ws_handler.run();
		
		var ws_chime_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "chime_file_management");
		ws_chime_handler.setOnmessageHandler(ws_recv_data);
		ws_chime_handler.run();

		var common_audio_handler	= document.createElement('audio');

		var common_display_handler	= new CommonDisplayFunc();
		var common_log_handler		= new CommonLogFunc("chime_file_management");
		var chime_handler			= new ChimeFileHandler();
	
		var common_func_handler = new CommonFunc();

		func_async_req_set_table();

	});

</script>

<?php
	include_once 'common_js_etc.php';
?>
