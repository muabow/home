<script type="text/javascript">
	$(document).ready(function() {
		var audioElement = document.createElement('audio');

		var systemFunc = new SystemFunc();
		var displayFunc = new CommonDisplayFunc();

		var maxAvailableMemSize = <?=Source_file_management\Def\MAX_AVAILABLE_MEM_SIZE ?>;
		var playSettings = <?php echo json_encode($srcFileMngFunc->getPlaySettings())  ?>;
		var checkPlayerTimerId = 0;
		var readPlayStatTimerId = 0;
		var playVolume = <?php echo json_encode($srcFileMngFunc->getVolumeStat())  ?>;
		var gMemoryLeft = 1024;
		var srcLogFunc = new CommonLogFunc("source_file_management");

		initEqulizer("outputVolume_1");
		setValue(playVolume.audio_player.volume, 1, false);
		LoadAvailableMemSize();
		LoadFileListView();

		var ws_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "audio_player");
		ws_handler.setOnmessageHandler(ws_recv_func);
		ws_handler.setOnopenHandler(ws_open_func);
		ws_handler.run();

		// 파일 선택
		var fileTarget = $('.filebox_upgrade .upload-hidden');
		fileTarget.on('change', function(){ // 값이 변경되면
			var fileCount = 0;
			if( window.FileReader ) { // modern browser
				if( $(this).val() == "" ) {
					var filename = "<?=Source_file_management\Lang\STR_SRCFILE_ADD_FIND ?>";
				} else {
					var filename = $(this)[0].files[0].name;
					var fileCount = $(this)[0].files.length;
				}

			} else { // old IE
				var filename = $(this).val().split('/').pop().split('\\').pop(); // 파일명만 추출
			} // 추출한 파일명 삽입

			if (fileCount <= 1) {
				$(this).siblings('.upload-name').val(filename);
			} else {
				$(this).siblings('.upload-name').val(filename + " <?=Source_file_management\Lang\STR_AND ?> " + (fileCount-1) + " <?=Source_file_management\Lang\STR_OTHER ?>");
			}
		});

		// 1.1. 파일 업로드 - 적용
		$("#div_buttonApplyFileUpload").click(function() {
			var fileData = $("#file_uploadFile")[0].files;

			// 파일 첨부 검출
			if( fileData.length == 0 ) {
				alert("<?=Source_file_management\Lang\STR_SRCFILE_ADD_SELECT ?>");
				return ;
			}

			var specialCheck = "";
			var doubleCheck = "";
			var typeCheck = false;
			$.each(fileData, function(i, e) {
				var onlyFileName 	= e.name;

				// 파일명 띄어쓰기 2개 연속 체크
				var doubleSpace = "  ";
				if(onlyFileName.indexOf(doubleSpace) > -1) {
					doubleCheck += onlyFileName + "\n";
				}

				// 파일명 특수문자 여부 체크
				var specialChars = /[`'*|\\\"\/?#%:<>&$+]/g;
				if(specialChars.test(onlyFileName) == true) {
					specialCheck += onlyFileName + "\n";
					checkFile = false;
				}

				// 파일 확장자 검출
				var fileType = e.type;
				if( !(fileType.toLowerCase() == "audio/mp3") ) {
					typeCheck = true;
				}
			});

			if ( specialCheck != "" || doubleCheck != "") {
				var alertString = "";
				if(specialCheck != "") {
					alertString += "<?=Source_file_management\Lang\STR_SRCFILE_ADD_INVALID_NAME ?>\n-------- <?=Source_file_management\Lang\STR_SRCFILE_TABLE ?> --------\n" + specialCheck;
				}

				if(doubleCheck != "") {
					if(specialCheck != "") {
						alertString += "\n";
					}
					alertString += "<?=Source_file_management\Lang\STR_SRCFILE_ADD_DOUBLE_SPACE_NAME ?>\n-------- <?=Source_file_management\Lang\STR_SRCFILE_TABLE ?> --------\n" + doubleCheck;
				}

				alert(alertString);
				InitSelectedFile();
				return;
			}

			if ( typeCheck ) {
				alert("<?=Source_file_management\Lang\STR_SRCFILE_ADD_INVALID_TYPE ?>");
				InitSelectedFile();
				return;
			}

			// 파일 사이즈 검출
			var memoryLeft  = gMemoryLeft * 1024 * 1024;

			var fileSize = 0;
			$.each(fileData, function(i, e) {
				fileSize += e.size;
			});

			if ( memoryLeft < fileSize) {
				var uploadSize = parseInt(fileSize / 1024 / 1024) + "MB";
				alert("<?=Source_file_management\Lang\STR_SRCFILE_ADD_LIMIT_MEM_ALL ?>\n" + "<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL_UPLOAD_SIZE ?> : " + uploadSize + " / <?=Source_file_management\Lang\STR_SRCFILE_ADD_AVAILABLE_MEM ?> : " + gMemoryLeft + "MB");
				return;
			}

			if( confirm("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_CONFIRM ?>") ) {
				srcLogFunc.info("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_APPLY ?>");
			} else {
				return;
			}

			displayFunc.showLoader();

			// 파일 업로드
			var formData   = new FormData();
			var result;

			$.each(fileData, function(i, e) {
				formData.append("file_" + i, e);
				srcLogFunc.info("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD ?> : " + e.name);
			});

			var request = new XMLHttpRequest();
			request.onreadystatechange = function(){

				if( request.readyState == 4 ) {
					$(".container").hide();

					try {
						result = JSON.parse(request.response);

						displayFunc.clearDotLoader();

						if( result.code == 0 ) {
							alert("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_SUCCESS ?>");
							srcLogFunc.info("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_SUCCESS ?>");

							LoadAvailableMemSize();
							LoadFileListView();

							InitSelectedFile();

						} else {
							setTimeout(function() {
								switch( result.code ) {
									case -1 :
										alert("<?=Source_file_management\Lang\STR_SRCFILE_ADD_NOT_FOUND_FILE ?>");
										srcLogFunc.info("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL ?> (<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL_NOTFOUND_FILE ?>)");
									break;

									case -2 :
										alert("<?=Source_file_management\Lang\STR_SRCFILE_ADD_LIMIT_COUNT ?>");
										srcLogFunc.info("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL ?> (<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL_OVER_FILECNT ?>)");
									break;

									case -3 :
										alert("<?=Source_file_management\Lang\STR_SRCFILE_ADD_LIMIT_MEM_ALL ?>");
										srcLogFunc.info("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL ?> (<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL_OVER_SAVESIZE ?>)");
									break;

									default :
										alert("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL ?>");
										srcLogFunc.info("<?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD_FAIL ?> (" + responseText + ")");
										// console.log("Upgrade error code : " + responseText);
									break;
								}

								InitSelectedFile();
							}, 800);
						}
					} catch (e){
						var resp = {
							status: 'error',
							data: 'Unknown error occurred: [' + request.responseText + ']'
						};
					}

					ws_handler.send(1, null);
				}
			};

			request.open('POST', "<?=Source_file_management\Def\PATH_UPLOAD_PROCESS ?>");
			request.send(formData);

			return ;
		});

		// 1.2. 파일 업로드 - 초기화
		$("#div_buttonCancelFileUpload").click(function() {
			InitSelectedFile();

			return ;
		});

		// 2.1. 파일 목록 - 체크박스
		$("#checkbox_select_all").click(function() {
			var stat = false;

			if( this.checked ) {
				stat = true;
			}

			$(':checkbox').each(function() {
				this.checked = stat;
			});
		});

		// 2.3. 파일 목록 - 전체 재생
		$("#div_buttonPlayAll").click(function() {

			// 재생시킬 파일이 있는지 체크
			if( $("#div_src_num_0").length == 0 )
			{
				alert("<?=Source_file_management\Lang\STR_SRCFEIL_PLAY_EMPTY ?>");
				return ;
			}

			var volume = $("#slidervalue1").html();

			var submitParams = "";

			submitParams += systemFunc.makeArgs("act",  			"play");
			submitParams += systemFunc.makeArgs("storage",  		"<?php echo $audioStoragePath?>");

			if( !confirm("<?=Source_file_management\Lang\STR_SRCFILE_PLAY_SELECT_ALL_APPLY ?>") ) {
				return ;
			}

			srcLogFunc.info("<?=Source_file_management\Lang\STR_SRCFILE_PLAY_SELECT_ALL_APPLY ?>");

			submitParams += systemFunc.makeArgs("type",			"dir");
			submitParams += systemFunc.makeArgs("volume", 		volume);

			systemFunc.postArgs("<?=Source_file_management\Def\PATH_SRCFILE_PROCESS ?>", submitParams);

			if(true == setPlaySettings("play", "dir", " ", 0, volume, true)) {
				//setAllPlayStat(true);
				drawPlayStat(-1);
			}

			ClearAllCheckbox();
			InitAllRetryCnt();

			return ;
		});

		// 2.4 파일 목록 - 재생 정지
		$("#div_buttonStop").click(function() {
			// 파일이 있는지 체크
			if( $("#div_src_num_0").length == 0 )
			{
				return ;
			}

			if( !confirm("<?=Source_file_management\Lang\STR_SRCFILE_PLAY_STOP_APPLY ?>") ) {
				return ;
			}
			var volume = $("#slidervalue1").html();

			srcLogFunc.info("<?=Source_file_management\Lang\STR_SRCFILE_PLAY_STOP_APPLY ?>");

			var submitParams = systemFunc.makeArgs("act",  			"stop");
			submitParams += systemFunc.makeArgs("volume", 			volume);
			systemFunc.postArgs("<?=Source_file_management\Def\PATH_SRCFILE_PROCESS ?>", submitParams);

			if(true == setPlaySettings("stop", "", " ", 0, volume, true)) {
				//setAllPlayStat(false);
				//drawPlayStat(-1);
			}

			InitAllRetryCnt();
		});

		// 2.5. 파일 목록 - 삭제
		$("#div_buttonDel").click(function() {
			// 파일이 있는지 체크
			if( $("#div_src_num_0").length == 0 )
			{
				alert("<?=Source_file_management\Lang\STR_SRCFEIL_DEL_EMPTY ?>");
				return ;
			}

			var delFiles = new Array();
			var isAllDel = false;
			var isPlayingFile = false;

			if(true == $("#checkbox_select_all").is(":checked")) {
				isAllDel = true;

				// 파일 검색해서 src 되어 있는 녀석 있으면 isPlayingFile = true로 하고 return;
				var fileCnt = $("[id^=checkbox_src_]").length;
				for(var cnt = 0; cnt < fileCnt; cnt++) {
					var id = "div_src_playStat_" + cnt;
					if("" != $("#" + id).attr('src')) {
						isPlayingFile = true;
						break;
					}
				}
			}
			else {
				$("[id^=checkbox_src_]").each(function() {
					if( this.checked ) {
						var arrId = $(this).attr("id").split("_");
						var index = arrId[arrId.length - 1];

						delFiles.push($("#div_src_fname_" + index).html().trim());

						// 파일 검색해서 src 되어 있는 녀석 있으면 isPlayingFile = true
						var id = "div_src_playStat_" + index;
						if("" != $("#" + id).attr('src')) {
							isPlayingFile = true;
						}
					}
				});
			}

			var submitParams = new FormData();
			submitParams.append("act", "delete");
			submitParams.append("storage", "<?php echo $audioStoragePath?>");
			submitParams.append("volume", playSettings.volume);

			if( (true != isAllDel) && (0 == delFiles.length) ) {
				alert("<?=Source_file_management\Lang\STR_SRCFILE_DEL_SELECT ?>");
				return ;
			}

			if(false == isPlayingFile) {
				if( !confirm("<?=Source_file_management\Lang\STR_SRCFILE_DEL_SELECT_APPLY ?>") ) {
					return ;
				}

				submitParams.append("stopPlay", "no");
			}
			else {
				if( !confirm("<?=Source_file_management\Lang\STR_SRCFILE_DEL_SELECT_APPLY_AFTER_STOP ?>") ) {
					return ;
				}

				submitParams.append("stopPlay", "yes");
			}

			if(true == isAllDel) {
				submitParams.append("type",  		"dir");
				srcLogFunc.info("<?=Source_file_management\Lang\STR_SRCFILE_DEL_SELECT_ALL_APPLY ?>");
			}
			else {
				submitParams.append("type",  		"file");
				submitParams.append("srcfile",  	JSON.stringify(delFiles));

				srcLogFunc.info("<?=Source_file_management\Lang\STR_SRCFILE_DEL_SELECT_APPLY ?>");
				for(var cnt = 0; cnt < delFiles.length; cnt++) {
					srcLogFunc.info("<?=Source_file_management\Lang\STR_SRCFILE_DEL ?> : " + delFiles[cnt]);
				}
			}

			displayFunc.showLoader();

			var request = new XMLHttpRequest();
			request.onreadystatechange = function(){
				if( request.readyState == 4 ) {
					try {
						displayFunc.hideLoader(-1);

						ClearAllCheckbox();
						LoadAvailableMemSize();
						LoadFileListView();
					} catch (e){
						var resp = {
							status: 'error',
							data: 'Unknown error occurred: [' + request.responseText + ']'
						};
					}
				}
			};

			request.open('POST', "<?=Source_file_management\Def\PATH_SRCFILE_PROCESS ?>");
			request.send(submitParams);

			$("[id^=button_monitor_]").each(function() {
				 if( $(this).attr("class") ) {
					 audioElement.pause();
					 audioElement.currentTime = 0;

					 $(this).removeClass("monitor_play");
				 }
			});

			return ;
		});

		// 2.6. 음량 적용
		$("#div_button_apply_volume").click(function() {
			var volume = $("#slidervalue1").html();

			var submitParams = "";
			submitParams += systemFunc.makeArgs("act",  			"setVolume");
			submitParams += systemFunc.makeArgs("volume", 			volume);

			systemFunc.postArgs("<?=Source_file_management\Def\PATH_SRCFILE_PROCESS ?>", submitParams);

			return ;
		});

		// 모바일 음량 적용
		$("#div_button_apply_volume_mobile").click(function() {
			if( !systemFunc.checkNum($("#text_clientVolume").val()) ) {
				$("#text_clientVolume").focus();
				alert("<?=Source_file_management\Lang\STR_JS_WRONG_VOLUME ?>");
				$("#text_clientVolume").val(<?=$srcFileMngFunc->getOperVolume() ?>);
				$("#range_clientVolume").val(<?=$srcFileMngFunc->getOperVolume() ?>);
				return ;
			} else {

				var volume = $("#text_clientVolume").val();

				var submitParams = "";
				submitParams += systemFunc.makeArgs("act",  			"setVolume");
				submitParams += systemFunc.makeArgs("volume", 			volume);

				systemFunc.postArgs("<?=Source_file_management\Def\PATH_SRCFILE_PROCESS ?>", submitParams);

				return ;

			}
		});

		$("#slider1").change(function() {
			var volume = $.trim($("#slider1").val());

			var submitParams = "";
			submitParams += systemFunc.makeArgs("act",  			"setVolume");
			submitParams += systemFunc.makeArgs("volume", 			volume);

			systemFunc.postArgs("<?=Source_file_management\Def\PATH_SRCFILE_PROCESS ?>", submitParams);


			return ;
		});

		$("#range_clientVolume").change(function() {
			var volume = $.trim($("#range_clientVolume").val());

			var submitParams = "";
			submitParams += systemFunc.makeArgs("act",  			"setVolume");
			submitParams += systemFunc.makeArgs("volume", 			volume);

			systemFunc.postArgs("<?=Source_file_management\Def\PATH_SRCFILE_PROCESS ?>", submitParams);


			return ;
		});

		// 체크 박스 일괄 해제
		function ClearAllCheckbox() {
			$(':checkbox').each(function() {
				if( this.checked ) {
					this.checked = false;
				}
			});
		}

		// 체크 박스 비활성화 설정
		function setDisableCheckbox(isDisable, index) {
			var targetId;

			if(-1 == index) {
				targetId = "checkbox_select_all";
			}
			else {
				targetId = "checkbox_src_" + index;
			}

			if(true == isDisable) {
				$("#" + targetId).prop("checked", false);
				$("#" + targetId).prop("checked", false);
				$("#"+targetCheckId).attr("disabled", true);
			}
			else {
				$("#" + targetId).prop("checked", true);
				$("#" + targetId).removeAttr("disabled");
			}
		}

		// 재생 횟수 초기화 (0)
		function InitAllRetryCnt() {
			$('[id^=input_playRepeat_]').each(function() {
				this.value = 0;
			});
		}

		// 선택된 파일 정보 관련 초기화
		function InitSelectedFile() {
			$("#file_uploadFile").val("");
			$("#label_uploadFile").val("<?=Source_file_management\Lang\STR_SRCFILE_ADD_FIND ?>");
		}

		// 파일명을 이용하여 해당 파일에 재생 상태 표시
		function setEachPlayStatByFname(fileName, isPlay)
		{
			setAllPlayStat(false);

			var fileCnt = $("[id^=checkbox_src_]").length;
			for(var cnt = 0; cnt < fileCnt; cnt++) {
				var id = "div_src_fname_" + cnt;

				if(fileName == $("#" + id).text()) {
					var targetImgId = "div_src_playStat_" + cnt;
					var targetRetryId = "input_playRepeat_" + cnt;
					var targetCheckId = "checkbox_src_" + cnt;
					if(true == isPlay) {
						$("#"+targetImgId).show();
						$("#"+targetRetryId).val(playSettings.replayCnt);
						//setDisableCheckbox(true, cnt);
					}
					else {
						$("#"+targetImgId).hide();
						//setDisableCheckbox(false, cnt);
					}

					break;
				}
			}
		}

		// 인덱스를 이용하여 해당 파일에 재생 상태 표시
		function setEachPlayStatByIdx(idx, isPlay)
		{
			var targetId = "div_src_playStat_" + idx;

			setAllPlayStat(false);

			if(true == isPlay) {
				$("#"+targetId).show();
			}
			else {
				$("#"+targetId).hide();
			}
		}

		function setAllPlayStat(isPlay) {
			$("[id^=div_src_playStat_]").each(function(i, e) {
				if(true == isPlay) {
					$(e).show();
				}
				else {
					$(e).hide();
				}
			});
		}

		function drawPlayStat(idx)
		{
			var cnt = 0;

			clearInterval(checkPlayerTimerId);

			var request = new XMLHttpRequest();
			request.onreadystatechange = function(){
				if( request.readyState == 4 ) {
					try {
						if(5 == cnt) {
							clearInterval(checkPlayerTimerId);
						}
						cnt = cnt+1;

						var isPlay = (request.response);
						if(0 == isPlay) {
							isPlay = false;
						}
						else {
							isPlay = true;
						}

						if("dir" == playSettings.ftype) {
							setAllPlayStat(isPlay);
						}
						else {
							if(-1 == idx) {
								//setEachPlayStatByFname(playSettings.fname, isPlay);
							}
							else {
								setEachPlayStatByIdx(idx, isPlay);
							}
						}

						if(("play" == playSettings.state) && (true == isPlay)
							|| ("stop" == playSettings.state) && (false == isPlay)) {
								clearInterval(checkPlayerTimerId);
							}
					} catch (e){
						var resp = {
							status: 'error',
							data: 'Unknown error occurred: [' + request.responseText + ']'
						};
					}
				}
			};

		}

		function drawFileListView(files)
		{
			var fileForm = "";
			var fileCnt = files.length;

			$("#divSrcFileList").empty();

			if(0 == fileCnt) {

			} else {
				var cnt = 0;
				for( ; cnt< fileCnt ; cnt++)
				{

					fileForm += '<div class="divTableRow"> <div class="divTableCell_left" id="div_src_num_' + cnt + '">' + '<input type="button" id="button_monitor_ ' + cnt + '" value="' + (cnt + 1) + '" style="width: 30px; border: solid 1px grey; "/>' + '</div>'
									+ '<div class="divTableCell"> '
									+ '<img id="div_src_playStat_' + cnt + '" style="align:center; display:none; width:18px"/ src="<?=Source_file_management\Def\PATH_IMG_PLAYLIST ?>"> </div>'
									+ '<div class="divTableCell" id="div_src_fname_' + cnt + '">' + files[cnt] + '</div>'
									+ '<div class="divTableCell" id="div_src_playsetting_' + cnt + '">'
									+ '<input type="number" id="input_playRepeat_' + cnt + '" value=0 min="0" max="20" style="float:left; height: 13px; width: 40px;">'
									+ '<div id="div_buttonPlay_' + cnt + '" class="div_button_mini" style="float: left;">' + '▶' + '</div>'
									+ '</div>'
									+ '<div class="divTableCell">'
									+ '<input type="checkbox" id="checkbox_src_' + cnt +'" /> </div> </div>';

				}

				$("#divSrcFileList").append(fileForm);

				// 모니터링 버튼
				$("[id^=button_monitor_]").click(function() {
					if( $(this).attr("class") ) {
						audioElement.pause();
						audioElement.currentTime = 0;

						$(this).removeClass("monitor_play");

						return ;
					}

					$("[id^=button_monitor_]").removeClass("monitor_play");

					$(this).addClass("monitor_play");

					var splitId  = $(this).attr("id").split("_");
					var index    = splitId[splitId.length - 1].trim();
					var fileName = $("#div_src_fname_" + index).text().trim();
					var filePath = 'modules/source_file_management/html/data/audiofiles/' + fileName;

					// console.log(fileName);
					// console.log(filePath);

					audioElement.setAttribute('preload', "none");
					audioElement.setAttribute('src', 	 filePath);

					// console.log(audioElement.src);

					audioElement.play();

					audioElement.onended = function() {
						audioElement.pause();
						audioElement.currentTime = 0;

						$("[id^=button_monitor_]").removeClass("monitor_play");
					};

					return ;
				});

				// 재생 버튼 이벤트 설정
				$("[id^=div_buttonPlay_]").click(function() {
					var splitId = $(this).attr("id").split("_");
					var index = splitId[splitId.length - 1];
					var fileName = $("#div_src_fname_" + index).html().trim();
					var replayCnt = $("#input_playRepeat_" + index).val();
					var volume = $("#slidervalue1").html();

					if( !confirm("<?=Source_file_management\Lang\STR_SRCFILE_PLAY_SELECT_APPLY ?>") ) {
						return ;
					}

					srcLogFunc.info("<?=Source_file_management\Lang\STR_SRCFILE_PLAY_SELECT_APPLY ?>");
					srcLogFunc.info("<?=Source_file_management\Lang\STR_SRCFILE_PLAY ?> : " + fileName + "(<?=Source_file_management\Lang\STR_SRCFILE_PLAY_REPEAT ?>-" + replayCnt +")");

					var submitParams = new FormData();
					submitParams.append("act", "play");
					submitParams.append("storage", "<?php echo $audioStoragePath?>");
					submitParams.append("type", "file");
					submitParams.append("srcfile", fileName);
					submitParams.append("repeatCount", replayCnt);
					submitParams.append("volume", volume);

					var request = new XMLHttpRequest();
					request.onreadystatechange = function(){
						if( request.readyState == 4 ) {
							try {
								setPlaySettings("play", "file", fileName, replayCnt, volume, true);
								drawPlayStat(index);
								ClearAllCheckbox();
							} catch (e){
								var resp = {
									status: 'error',
									data: 'Unknown error occurred: [' + request.responseText + ']'
								};
							}
						}
					};

					request.open('POST', "<?=Source_file_management\Def\PATH_SRCFILE_PROCESS ?>");
					request.send(submitParams);

					return ;
				});

				// 체크 박스 이벤트 설정
				$("[id^=checkbox_src_]").click(function() {
					var isAllChecked = true;

					var fileCnt = $("[id^=checkbox_src_]").length;
					for(var cnt = 0; cnt < fileCnt; cnt++) {
						var id = "checkbox_src_" + cnt;
						if(false == $("#" + id).is(":checked")) {
							isAllChecked = false;
							break;
						}
					}

					$("#checkbox_select_all").prop("checked", isAllChecked);

					return ;
				});

				// 재생 반복 이벤트 설정
				$("[id^=input_playRepeat]").on('change', function() {

					if(<?=Source_file_management\Def\MAX_PLAY_REPEAT_COUNT ?>  < this.value) {
						alert("<?= Source_file_management\Lang\STR_SRCFILE_PLAY_REPEAT_OVERRANGE?>"
								+ " (<?=Source_file_management\Def\MIN_PLAY_REPEAT_COUNT ?>~<?=Source_file_management\Def\MAX_PLAY_REPEAT_COUNT ?>)");

						this.value = <?=Source_file_management\Def\MAX_PLAY_REPEAT_COUNT ?>;
					}
					else if(<?=Source_file_management\Def\MIN_PLAY_REPEAT_COUNT ?> > this.value) {
						alert("<?= Source_file_management\Lang\STR_SRCFILE_PLAY_REPEAT_OVERRANGE?>"
								+ " (<?=Source_file_management\Def\MIN_PLAY_REPEAT_COUNT ?>~<?=Source_file_management\Def\MAX_PLAY_REPEAT_COUNT ?>)");

						this.value = <?=Source_file_management\Def\MIN_PLAY_REPEAT_COUNT ?>;
					}
				});
			}
		}


		function DrawAvailableMemSize(MemUsage)
		{
			//console.log("max : " + maxAvailableMemSize + ' usage : ' + MemUsage);

			var avaliMem = maxAvailableMemSize - MemUsage;
			var avaliMemSize = avaliMem.toString().length;
			//console.log("remain : " + avaliMem + " memSize : " + avaliMemSize);

			var unit = "";
			var needComma = parseInt(avaliMemSize / 3) - 1;
			//console.log("needComma : " + needComma);

			switch(needComma)
			{
				case 1:
					unit = " KB";
					avaliMem = parseInt(avaliMem / 1024);
					break;

				case 2:
					unit = " MB";
					avaliMem = parseInt(avaliMem / (1024 * 1024));
				break;

				default:
					unit = " bytes";
				break;
			}

			var avaliMemStr = Number(avaliMem).toLocaleString('en');
			gMemoryLeft = avaliMemStr;
			div_availMem.innerHTML = avaliMemStr + unit;
		}

		function LoadAvailableMemSize()
		{
			var request = new XMLHttpRequest();
			request.onreadystatechange = function(){
				if( request.readyState == 4 ) {
					try {
						var memUsage = JSON.parse(request.response);
						//console.log("memUsage : " + memUsage );
						DrawAvailableMemSize(memUsage);
					} catch (e){
						var resp = {
							status: 'error',
							data: 'Unknown error occurred: [' + request.responseText + ']'
						};
					}
				}
			};

			var submitParams = new FormData();
			submitParams.append("act", "getAvailableMemSize");
			submitParams.append("storage", "<?php echo $audioStoragePath?>");

			request.open('POST', "<?=Source_file_management\Def\PATH_SRCFILE_PROCESS ?>");
			request.send(submitParams);
		}

		function LoadFileListView() {
			var request = new XMLHttpRequest();
			request.onreadystatechange = function(){
				if( request.readyState == 4 ) {
					try {
						var files = JSON.parse(request.response);
						drawFileListView(files);
						drawPlayStat(-1);
					} catch (e){
						var resp = {
							status: 'error',
							data: 'Unknown error occurred: [' + request.responseText + ']'
						};
					}
				}
			};

			var submitParams = new FormData();
			submitParams.append("act", "reload");
			submitParams.append("storage", "<?php echo $audioStoragePath?>");

			request.open('POST', "<?=Source_file_management\Def\PATH_SRCFILE_PROCESS ?>" , false);
			request.send(submitParams);
		}

		function setPlaySettings(state, ftype, fname, replay, volume, doWrite)
		{
			if( (playSettings.state != state) || (playSettings.ftype != ftype)
				|| (playSettings.fname != fname) || (playSettings.replayCnt != replay) ) {

				playSettings.state 		= state;
				playSettings.ftype 		= ftype;
				playSettings.fname 		= fname;
				playSettings.replayCnt 	= replay;
				playSettings.volume 	= volume;

				if(true == doWrite) {
					var submitParams = new FormData();
					submitParams.append("act", "setPlaySettings");
					submitParams.append("state", state);
					submitParams.append("ftype", ftype);
					submitParams.append("fname", fname);
					submitParams.append("replay", replay);
					submitParams.append("volume", volume);

					var request = new XMLHttpRequest();
					request.open('POST', "<?=Source_file_management\Def\PATH_SRCFILE_PROCESS ?>");
					request.send(submitParams);
				}

				return true;
			}
			else {
				return false;
			}
		}



	});

	class SystemFunc {
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
				async	: true,
				success	: function(data) {
					if( data != null ) {
						result = data;
					}
				}
			});

			return result;
		}

		checkNum(_num) {
			if( $.isNumeric(_num) && _num >= 0 && _num <= 100 ) {
				return true;

			} else {
				return false;
			}
		}

	} // end of SystemFunc()

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
		var bigval 		= 520; 	/* widest or tallest value depending on orientation */
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

	function ws_open_func(_this) {
		_this.send(1, null);

		return ;
	}

	function ws_recv_func(_cmd_id, _is_binary, _length, _data, _this) {
		var type = parseInt(_cmd_id);
		if( type == 1 && _data == null ) return ;

		var data = JSON.parse(_data).data;

		switch( type ) {
			case -11 : // incorrect process
			break;

			case 1   : // alive info
				if( data.stat == 0 ) {
					var fileCnt = $("[id^=checkbox_src_]").length;

					for( var cnt = 0 ; cnt < fileCnt ; cnt++ ) {
						var id = "div_src_fname_" + cnt;
						var targetImgId = "div_src_playStat_" + cnt;
						var targetRetryId = "input_playRepeat_" + cnt;
						var targetCheckId = "checkbox_src_" + cnt;

						$("#"+targetImgId).hide();
					}
				}
			break;

			case 10  : // operation setup
				setValue(data.playVolume, 1, false);
				var fileCnt = $("[id^=checkbox_src_]").length;

				//ftype = 1 -> file, 0 -> dir
				if( data.ftype == 1 ) {
					for( var cnt = 0 ; cnt < fileCnt ; cnt++ ) {
						if( data.fname == $("#div_src_fname_" + cnt).html().trim() ) {
							var targetImgId = "div_src_playStat_" + cnt;
							var targetRetryId = "input_playRepeat_" + cnt;
							var targetCheckId = "checkbox_src_" + cnt;

							$("#"+targetImgId).attr('src','<?=Source_file_management\Def\PATH_IMG_PLAY ?>');

							$("#"+targetImgId).show();
							$("#"+targetRetryId).val(data.replayCnt);

							break;
						}
					}

				} else {
					for( var cnt = 0 ; cnt < fileCnt ; cnt++ ) {
						var targetImgId = "div_src_playStat_" + cnt;
						var targetRetryId = "input_playRepeat_" + cnt;
						var targetCheckId = "checkbox_src_" + cnt;

						if(data.fname == $("#div_src_fname_" + cnt).html().trim()) {
							$("#"+targetImgId).attr('src','<?=Source_file_management\Def\PATH_IMG_PLAY ?>');

						} else {
							$("#"+targetImgId).attr('src','<?=Source_file_management\Def\PATH_IMG_PLAYLIST ?>');
						}

						$("#"+targetImgId).show();
						$("#"+targetRetryId).val(data.replayCnt);
					}
				}
			break;

			case 11 : // volume
				setValue(data.playVolume, 1, false);
			break;

			case 12 : // level meter
				$(".level_outputVolume").html(data.level);
			break;
		} // switch

		return ;
	}
</script>

<?php
	include_once 'common_js_etc.php';
?>
