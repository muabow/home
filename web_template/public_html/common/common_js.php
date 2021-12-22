<?php
/************************************
 * Javascript 구간
 * 언어팩, 상수, 경로 등 메크로를 사용하기 위해서 PHP 내에 script 작성
 ************************************/
?>

<script type="text/javascript">
	function notiServerState(_state) {
		switch( _state ) {
			case <?=Common\Def\STATUS_FORBIDDEN ?> :
				alert('[' + <?=Common\Def\STATUS_FORBIDDEN ?> + '] ' + '<?=Common\Lang\STR_STAT_FORBIDDEN_MSG ?>');
				break;

			case <?=Common\Def\STATUS_NOT_FOUND ?> :
				alert('[' + <?=Common\Def\STATUS_NOT_FOUND ?> + '] ' + '<?=Common\Lang\STR_STAT_NOT_FOUND_MSG ?>');
				break;

			case <?=Common\Def\STATUS_INT_ERROR ?> :
				alert('[' + <?=Common\Def\STATUS_INT_ERROR ?> + '] ' + '<?=Common\Lang\STR_STAT_INT_ERROR_MSG ?>');
				break;

			default :
				// alert('[' + state + '] ' + '<?=Common\Lang\STR_STAT_UNKNOWN_ERR_MSG ?>');
				break;
		}
	}

	class CommonFunc {
		constructor() {
		 // 클래스 내 변수 선언
		}

		makeArgs(_key, _value) {
			var args = "&" + _key + "=" + _value;

			return args;
		}

		postArgsAsync(_target, _args, _func) {
			new ajax.xhr.Request(_target, _args, _func, "POST");

			return ;
		}

		submitedReload(_req) {
			if( _req.readyState == <?=Common\Def\READY_STAT_SUCCESS ?> ) {
				if( _req.status == <?=Common\Def\STATUS_SUCCESS ?> ) {
					var responseText = _req.responseText;

					if( responseText == "pc" ) {
						location.reload();

					} else {
						location.reload();
					}

				} else {
					notiServerState(_req.status);
				}
			}
		}

		submitedReloadIndex(_req) {
			if( _req.readyState == <?=Common\Def\READY_STAT_SUCCESS ?> ) {
				if( _req.status == <?=Common\Def\STATUS_SUCCESS ?> ) {
					location.reload();

				} else {
					notiServerState(_req.status);
				}
			}
		}

		submitedRefresh(_req) {
			if( _req.readyState == <?=Common\Def\READY_STAT_SUCCESS ?> ) {
				if( _req.status == <?=Common\Def\STATUS_SUCCESS ?> ) {
					location.reload();

				} else {
					notiServerState(_req.status);
				}
			}
		}

		resizeElement(_element, _addSize, _isWidth) {
			var menu_length = [];

			if ( _isWidth ) {
				_element.css("width", '');

				_element.each(function(i, e) {
					menu_length.push($(e).width());
				});

				var max_length = Math.max.apply(null, menu_length) + _addSize;
				_element.css("width", max_length);
			} else {
				_element.css("height", '');

				_element.each(function(i, e) {
					menu_length.push($(e).height());
				});

				var max_length = Math.max.apply(null, menu_length) + _addSize;
				_element.css("height", max_length);
			}
		}

		resizeMenu() {
			var size_mainMenu	  = parseInt($("#div_main_menu").css("height"));
			var size_MainContents = parseInt($("#div_main_contents").css("height"))

			if( size_mainMenu > size_MainContents ) {
				$("#div_main_contents").css("height", size_mainMenu + "px");

			} else {
				$("#div_main_menu").css("height", size_MainContents + "px");
			}
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

		getRsaKey(_username) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("<?=Common\Def\AJAX_PROC_TYPE   ?>", "login");
			submitArgs	+= this.makeArgs("act", "rsa_key");
			submitArgs	+= this.makeArgs("username", _username);

			return this.postArgs("<?=Common\Def\PATH_AJAX_COMMON_PROCESS ?>", submitArgs);
		}

		getPubKey(_username) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("<?=Common\Def\AJAX_PROC_TYPE   ?>", "login");
			submitArgs	+= this.makeArgs("username", _username);
			submitArgs	+= this.makeArgs("act", "pub_key");
			return this.postArgs("<?=Common\Def\PATH_AJAX_COMMON_PROCESS ?>", submitArgs);
		}

		login(_username, _password, _is_check) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("<?=Common\Def\AJAX_PROC_TYPE   ?>", "login");
			submitArgs	+= this.makeArgs("act", "user_info");
			submitArgs	+= this.makeArgs("hash", "login");
			submitArgs	+= this.makeArgs("username", _username);
			submitArgs	+= this.makeArgs("password", _password);
			submitArgs	+= this.makeArgs("is_check", _is_check);

			return this.postArgs("<?=Common\Def\PATH_AJAX_COMMON_PROCESS ?>", submitArgs);
		}

		checkLogin(_username, _password) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("<?=Common\Def\AJAX_PROC_TYPE   ?>", "login");
			submitArgs	+= this.makeArgs("act", "user_info");
			submitArgs	+= this.makeArgs("hash", "checkLogin");
			submitArgs	+= this.makeArgs("username", _username);
			submitArgs	+= this.makeArgs("password", _password);

			return this.postArgs("<?=Common\Def\PATH_AJAX_COMMON_PROCESS ?>", submitArgs);
		}

		pass_password_change() {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("<?=Common\Def\AJAX_PROC_TYPE   ?>", "login");
			submitArgs	+= this.makeArgs("act", "pass_check_password");

			return this.postArgs("<?=Common\Def\PATH_AJAX_COMMON_PROCESS ?>", submitArgs);
		}

		checkPassword() {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("<?=Common\Def\AJAX_PROC_TYPE   ?>", "login");
			submitArgs	+= this.makeArgs("act", "check_password");

			return this.postArgs("<?=Common\Def\PATH_AJAX_COMMON_PROCESS ?>", submitArgs);
		}

		changePassword(_username, _password) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("<?=Common\Def\AJAX_PROC_TYPE   ?>", "login");
			submitArgs	+= this.makeArgs("act", "user_info");
			submitArgs	+= this.makeArgs("hash", "change");
			submitArgs	+= this.makeArgs("username", _username);
			submitArgs	+= this.makeArgs("password", _password);

			return this.postArgs("<?=Common\Def\PATH_AJAX_COMMON_PROCESS ?>", submitArgs);
		}

		check_try_count(_username) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("<?=Common\Def\AJAX_PROC_TYPE   ?>", "login");
			submitArgs	+= this.makeArgs("act",      "check_count");
			submitArgs	+= this.makeArgs("username", _username);

			return this.postArgs("<?=Common\Def\PATH_AJAX_COMMON_PROCESS ?>", submitArgs);
		}

		get_try_count(_username) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("<?=Common\Def\AJAX_PROC_TYPE   ?>", "login");
			submitArgs	+= this.makeArgs("act",      "get_count");
			submitArgs	+= this.makeArgs("username", _username);

			return this.postArgs("<?=Common\Def\PATH_AJAX_COMMON_PROCESS ?>", submitArgs);
		}

		reset_try_count(_username) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("<?=Common\Def\AJAX_PROC_TYPE   ?>", "login");
			submitArgs	+= this.makeArgs("act",      "reset_count");
			submitArgs	+= this.makeArgs("username", _username);

			return this.postArgs("<?=Common\Def\PATH_AJAX_COMMON_PROCESS ?>", submitArgs);
		}

		logout() {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("<?=Common\Def\AJAX_PROC_TYPE   ?>", "logout");

			return this.postArgs("<?=Common\Def\PATH_AJAX_COMMON_PROCESS ?>", submitArgs);
		}

		is_exist_user(_username) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("<?=Common\Def\AJAX_PROC_TYPE   ?>", "login");
			submitArgs	+= this.makeArgs("act",      "is_exist_user");
			submitArgs	+= this.makeArgs("username", _username);

			return this.postArgs("<?=Common\Def\PATH_AJAX_COMMON_PROCESS ?>", submitArgs);
		}

	} // end of CommonFunc()

	class CommonLogFunc {
		constructor(_moduleName) {
			this.moduleName	 = _moduleName;
			this.logPath	 = "<?=Common\Def\PATH_AJAX_COMMON_PROCESS ?>";
			this.setInfoFlag = false;
		}

		makeArgs(_key, _value) {
			var args = "&" + _key + "=" + _value;

			return args;
		}

		postArgs(_target, _args) {
			$.ajax({
				type	: "POST",
				url		: _target,
				data	: _args,
				async	: false,
				success	: function(data) {
					if( data != null ) {
						// do something. not yet.
					}
				}
			});

			return ;
		}

		writeLog(_logLevel, _message) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("<?=Common\Def\AJAX_PROC_TYPE   ?>", "<?=Common\Def\AJAX_TYPE_LOG ?>");
			submitArgs	+= this.makeArgs("<?=Common\Def\TYPE_LOG_ACT    ?>", "<?=Common\Def\TYPE_LOG_ACT_WRITE ?>");
			submitArgs	+= this.makeArgs("<?=Common\Def\TYPE_LOG_MODULE  ?>", this.moduleName);
			submitArgs	+= this.makeArgs("<?=Common\Def\TYPE_LOG_LEVEL   ?>", _logLevel);
			submitArgs	+= this.makeArgs("<?=Common\Def\TYPE_LOG_MESSAGE ?>", _message);

			this.postArgs(this.logPath, submitArgs);
		}

		clearLog() {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("<?=Common\Def\AJAX_PROC_TYPE  ?>", "<?=Common\Def\AJAX_TYPE_LOG ?>");
			submitArgs	+= this.makeArgs("<?=Common\Def\TYPE_LOG_ACT    ?>", "<?=Common\Def\TYPE_LOG_ACT_CLEAR ?>");
			submitArgs	+= this.makeArgs("<?=Common\Def\TYPE_LOG_MODULE ?>", this.moduleName);

			this.postArgs(this.logPath, submitArgs);
		}

		removeLog() {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("<?=Common\Def\AJAX_PROC_TYPE  ?>", "<?=Common\Def\AJAX_TYPE_LOG ?>");
			submitArgs	+= this.makeArgs("<?=Common\Def\TYPE_LOG_ACT    ?>", "<?=Common\Def\TYPE_LOG_ACT_REMOVE ?>");
			submitArgs	+= this.makeArgs("<?=Common\Def\TYPE_LOG_MODULE ?>", this.moduleName);

			this.postArgs(this.logPath, submitArgs);
		}

		setLogInfo(_stat) {
			this.setInfoFlag = _stat;

			return ;
		}

		fatal(_message) {	this.writeLog("<?=Common\Def\LOG_LEVEL_FATAL ?>", _message);	}
		error(_message) {	this.writeLog("<?=Common\Def\LOG_LEVEL_ERROR ?>", _message);	}
		warn (_message) {	this.writeLog("<?=Common\Def\LOG_LEVEL_WARN  ?>", _message);	}
		debug(_message) {	this.writeLog("<?=Common\Def\LOG_LEVEL_DEBUG ?>", _message);	}
		info (_message) {
			if( this.setInfoFlag == true ) {
			this.writeLog("<?=Common\Def\LOG_LEVEL_INFO ?>", _message);

			} else {
				this.writeLog("", _message);
			}
		}
	} // end of CommonLogFunc()

	var displayInterval;
	class CommonDisplayFunc {
		constructor() {		}

		showLoader() {
			window.scrollTo(0, 0);
			window.onscroll = function () { window.scrollTo(0, 0); };

			document.body.focus();
			$('#viewLoading').fadeIn(400);
			$("#viewBackground").css('opacity','0.3').fadeIn(400);
		}

		clearDotLoader() {
			clearInterval(displayInterval);
			$('#viewLoading').fadeOut(400);
			$('#percentView').fadeOut(400);
			$('#viewBackground').fadeOut(400);

			window.onscroll = function () { };

			return;
		}

		hideDotLoader(_setTime, _popup) {
			var num = 0;
			$("#percentView").html("loading");
			$('#percentView').fadeIn(400);

			var dot = "";
			displayInterval = setInterval(function() {
									num += 1;
									dot += ".";
									if( num % 10 == 0 ) dot = ".";
									$("#percentView").html("loading" + dot);
								}, 1000);

			return ;
		}

		hideLoader(_setTime, _popup) {
			if( _setTime == 0 ) {
				$('#viewLoading').fadeOut(400);
				$('#viewBackground').fadeOut(400);
				setTimeout (function() {
						alert("<?=Common\Lang\STR_LOADER_COMPLETE ?>");
						window.onscroll = function () { };
						}, _setTime + 400);


			} else if( _setTime == -1 ) {
				$('#viewLoading').fadeOut(400);
				$('#viewBackground').fadeOut(400);

				window.onscroll = function () { };

			} else {
				var num = 0;
				$("#percentView").html("loading... " + num + "%");
				$('#percentView').fadeIn(400);

				_setTime *= 1000;
				var max = 100;
				var interval = setInterval(function() {
									num += 1;

									$("#percentView").html("loading... " + num + "%");
									if(num >= max) clearInterval(interval);
								}, (_setTime - 1200) / max);

				setTimeout ("$('#viewLoading').fadeOut(400)", 	 _setTime);
				setTimeout ("$('#viewBackground').fadeOut(400)", _setTime);
				setTimeout ("$('#percentView').fadeOut(400)", 	 _setTime);

				setTimeout (function() {
						if(typeof _popup !== "undefined") {
							if( _popup == true ) {
							alert("<?=Common\Lang\STR_LOADER_COMPLETE ?>");
							}
						}
						window.onscroll = function () { };
						}, _setTime + 400);
			}
		}
	} // end of CommonDisplayFunc()


	// class 객체 선언
	var commonFunc = new CommonFunc();
	commonFunc.resizeElement($(".label_radio"), 0, true);

	// 제품명(Header) 클릭 시 초기화면으로 변경
	$("#span_header_name").click(function() {
		var submitArgs = commonFunc.makeArgs("<?=Common\Def\AJAX_ARGS_CONTENTS ?>", $(this).attr("name"));

		commonFunc.postArgsAsync("<?=Common\Def\PATH_AJAX_INDEX ?>", submitArgs, commonFunc.submitedReloadIndex);
	});


	// 언어 목록 출력
	$("#span_footer_language").click(function() {

		if( $("#div_footer_language_list").is(":visible") ) {
			$("#div_footer_language_list").hide();

		} else {
			$("#div_footer_language_list").show();
		}
	});

	// 언어 클릭 시 언어 변경
	$("a[id^=a_language_]").click(function() {
		var currentLangSet = $("#span_footer_language").text().trim();

		if( $(this).html() != currentLangSet ) {
			var thisId = $(this).attr("id");
			var langType = thisId.split("_")[2];

			var submitArgs = commonFunc.makeArgs("language", langType);

			commonFunc.postArgsAsync("<?=Common\Def\PATH_AJAX_INDEX ?>", submitArgs, commonFunc.submitedRefresh);

		} else {
			$("#div_footer_language_list").hide();
		}
	});

	// 메뉴 클릭 시 페이지 변경
	$("div[id^=div_main_menu_id_]").click(function() {
		var submitArgs = commonFunc.makeArgs("<?=Common\Def\AJAX_ARGS_CONTENTS ?>", $(this).attr("name"));

		commonFunc.postArgsAsync("<?=Common\Def\PATH_AJAX_INDEX ?>", submitArgs, commonFunc.submitedReload);
	});

	// 로그아웃 메뉴
	$("#img_banner_logout").click(function() {
		commonFunc.logout();
		location.reload();
	});

	// 모바일 모드
	$("#div_banner_contents").click(function(){
		var visibleStat = $("#span_banner_version").is(':visible');
		if( visibleStat ) {
			$("span[id^=span_banner_]").hide();
			$("#span_banner_user").show();
			$("#span_banner_hostname").show();
			$("#span_banner_location").show();
			$("#right_arrow").show();

		} else {
			$("span[id^=span_banner_]").show();
			$("#right_arrow").hide();
		}
	});

	$("#div_footer_pc_view").click(function() {
		var submitArgs	= commonFunc.makeArgs("pc_view", true);

		commonFunc.postArgs("/common/common_session.php", submitArgs);
		location.reload();
	});

	$("#div_footer_mobile_view").click(function() {
		var submitArgs	= commonFunc.makeArgs("mobile_view", true);

		commonFunc.postArgs("/common/common_session.php", submitArgs);
		location.reload();
	});

	$(document).on("mouseover", "#span_banner_ipAddr", function(){
		var bonding 	= "<?=$commonInfoFunc->gethostIpAddr("network_bonding") ?>";
		var primary 	= "<?=$commonInfoFunc->gethostIpAddr("network_primary") ?>";
		var secondary 	= "<?=$commonInfoFunc->gethostIpAddr("network_secondary") ?>";

		var tooltip = '	<div class="tooltip">										\
							<div class="tooltip_name_wrap">							\
								<div class="tooltip_name">Bonding</div>				\
								<div class="tooltip_name">Primary</div>				\
								<div class="tooltip_name">Secondary</div>			\
							</div>													\
							<div class="tooltip_value_wrap">						\
								<div class="tooltip_value">' + bonding   + '</div>	\
								<div class="tooltip_value">' + primary   + '</div>	\
								<div class="tooltip_value">' + secondary + '</div>	\
							</div>													\
						</div>';

		if ( !((secondary == "-") && (bonding == "-")) ) {
			$(this).append(tooltip);
		}
	});

	$(document).on("mouseout", "#span_banner_ipAddr", function(){
		$(".tooltip").remove();
	});

	$(".span_main_menu_sub").mouseover(function() {
		var wid = $(this).outerWidth();
		$(this).addClass("div_main_menu_sub_hover");
		$(this).css("width", wid);
	});

	$(".span_main_menu_sub").mouseout(function() {
		$(this).removeClass("div_main_menu_sub_hover");
		$(this).css("width", '');
	});

	$("#div_system_check_end_alert").click(function() {
		$(this).hide();
		$("#div_system_check_end_blur").hide();

		var submitArgs	= commonFunc.makeArgs("noti_system_check", true);
		commonFunc.postArgs("/index.php", submitArgs);

		return ;
	});

	// glolbal websocket
	class WebsocketHandler {
		constructor(_ipAddr, _url, _is_debug = false) {
			/*
			 - websocket readyState
				CONNECTING	0	연결이 수립되지 않은 상태입니다.
				OPEN		1	연결이 수립되어 데이터가 오고갈 수 있는 상태입니다.
				CLOSING		2	연결이 닫히는 중 입니다.
				CLOSED		3	연결이 종료되었거나, 연결에 실패한 경우입니다.
			*/

			this.is_run		= false;
			this.is_binary	= false;
			this.is_debug   = _is_debug;

			this.ipAddr		= _ipAddr;
			this.url		= _url;
			this.sock_fd	= null;

			this.WS_ROUTE_TO = {
				EACH_OTHER	:	0,	// default
				WEB_ONLY	:	1,	// web browser
				NATIVE_ONLY	:	2,	// native process(binary, interface,, )
				ALL			:	3
			};

			this.route_case	= 0;

			// Event handler wrapper
			this.onmessageFunc = null;
			this.oncloseFunc   = null;
			this.onerrorFunc   = null;
			this.onopenFunc    = null;

			return ;
		}

		// private methods
		// Event handler wrapper
		_onmessage() {
			var _self = this;

			this.sock_fd.onmessage = function(_msg) {
				if( _self.onmessageFunc != null ) {
					if( _msg.data instanceof Blob ) {
						var reader = new FileReader();
						reader.onload = function(_event) {
							var result = _event.target.result;
							var cmd_id 	   = new Int8Array(result.slice(0, 1))[0];
							var route_case = new Int8Array(result.slice(1, 2))[0];
							var is_binary  = new Int8Array(result.slice(2, 3))[0];
							var is_extend  = new Int8Array(result.slice(3, 4))[0];
							var length     = new Int32Array(result.slice(4, 8))[0];
							var data       = null;

							if( length > 0 ) {
								data = result.slice(8, 8 + length);

								if( is_binary == 0 ) {
									data = String.fromCharCode.apply(null, new Uint8Array(data));
									data = decodeURIComponent(escape(data));
								}
							}

							if( _self.is_debug ) {
								console.log("cmd_id: [%s], is_binary: [%s], length: [%d], data: [%s]", cmd_id, is_binary, length, data);
							}

							_self.onmessageFunc(cmd_id, is_binary, length, data, _self);
						};

						reader.readAsArrayBuffer(_msg.data);

					} else if( _msg.data instanceof ArrayBuffer ) {
						var result = _msg.data;
						var cmd_id 	   = new Int8Array(result.slice(0, 1))[0];
						var route_case = new Int8Array(result.slice(1, 2))[0];
						var is_binary  = new Int8Array(result.slice(2, 3))[0];
						var is_extend  = new Int8Array(result.slice(3, 4))[0];
						var length     = new Int32Array(result.slice(4, 8))[0];
						var data       = null;

						if( length > 0 ) {
							data = result.slice(8, 8 + length);

							if( is_binary == 0 ) {
								data = String.fromCharCode.apply(null, new Uint8Array(data));
								data = decodeURIComponent(escape(data));
							}
						}

						if( _self.is_debug ) {
							console.log("cmd_id: [%s], is_binary: [%s], length: [%d], data: [%s]", cmd_id, is_binary, length, data);
						}

						_self.onmessageFunc(cmd_id, is_binary, length, data, _self);

					} else {
						var pData = JSON.parse(_msg.data);
						var sData = JSON.stringify(pData.data);

						if(    (typeof(pData.type) != "undefined" && pData.type !== null)
							&& (typeof(sData.length) != "undefined" && sData.length !== null) ) {
							if( _self.is_debug ) {
								console.log("cmd_id: [%s], is_binary: [%s], length: [%d], data: [%s]", pData.type, is_binary, sData.length, sData);
							}

							_self.onmessageFunc(pData.type, is_binary, sData.length, sData, _self);

						} else {
							if( _self.is_debug ) {
								console.log("cmd_id: [%d], is_binary: [%s], length: [%d], data: [%s]", 0, is_binary, _msg.data.length, _msg.data);
							}

							_self.onmessageFunc(0, is_binary, _msg.data.length, _msg.data, _self);
						}
					}
				}

				return ;
			}
		}

		_onclose() {
			var _self = this;

			this.sock_fd.onclose = function(_msg) {
				if( _self.oncloseFunc != null ) {
					_self.oncloseFunc(_msg, _self);
				}

				return ;
			};

		}

		_onerror() {
			var _self = this;

			this.sock_fd.onerror = function(_msg) {
				if( _self.onerrorFunc != null ) {
					_self.onerrorFunc(_msg, _self);
				}

				return ;
			};

		}

		_init() {
			var hostInfo = "ws://" + this.ipAddr + "/" + this.url;

			if( this.sock_fd != null ) {
				this.stop();
			}

			this.sock_fd = new WebSocket(hostInfo);

			if( this.is_binary ) {
				this.sock_fd.binaryType = 'arraybuffer';
			}

			this._open();

			return ;
		}

		_open() {
			var _self = this;

			this.sock_fd.onopen = function(_msg) {
				if( _self.onopenFunc != null ) {
					_self.onopenFunc(_self);
				}

				return ;
			};

		}

		// public methods
		set_binary_mode() {
			this.is_binary = true;

			return ;
		}

		run() {
			if( this.is_run ) return ;

			this._init();
			this._onmessage();
			this._onclose();
			this._onerror();

			this.is_run = true;

			return ;
		}

		stop() {
			if( !this.is_run ) return ;

			this.sock_fd.close();

			this.sock_fd = null;

			this.is_run = false;

			return ;
		}

		set_route_to(_route_case) {
			this.route_case = _route_case;

			return ;
		}

		send(_cmd_id, _data, _length = null) {
			if( this.sock_fd.readyState == 3 ) {
				this._init();
			}

			if( this.sock_fd.readyState != 1 ) {
				return this.sock_fd.readyState;
			}

			if( _data == null ) _data = "";

			var is_binary		= (_length == null ? 0 : 1);
			var length			= (_length == null ? _data.length : _length);

			var arr_int8_data	= new Int8Array(length);

			for( var idx = 0 ; idx < length ; idx++ ) {
				if( is_binary ) {
					arr_int8_data[idx] = _data[idx];

				} else {
					arr_int8_data[idx] = _data.charCodeAt(idx);
				}
			}

			var arrSendMsg = new Int8Array(8 + length);
			var convInt32 = new Int32Array(1);
			convInt32.set([length], 0, 1);
			var arrLength = new Int8Array(convInt32.buffer);

			arrSendMsg.set([_cmd_id], 			 0, 1);	// cmd_id
			arrSendMsg.set([this.route_case],    1, 2);	// route_case
			arrSendMsg.set([is_binary], 	 	 2, 3);	// is_binary
			arrSendMsg.set([1], 				 3, 4);	// is_extend
			arrSendMsg.set(arrLength, 			 4, 8); // length

			if( length > 0 ) {
				arrSendMsg.set(arr_int8_data, 8, length);
			}

			this.sock_fd.send(arrSendMsg);

			return this.sock_fd.readyState;
		}

		// setter : event handler
		setOnmessageHandler(_func) {
			this.onmessageFunc = _func;

			return ;
		}

		setOncloseHandler(_func) {
			this.oncloseFunc = _func;

			return ;
		}

		setOnerrorHandler(_func) {
			this.onerrorFunc = _func;

			return ;
		}

		setOnopenHandler(_func) {
			this.onopenFunc = _func;

			return ;
		}
	} // end of class : WebsocketHandler()

	// info_uptime
	class UptimeFunc {
		constructor() {
			this.timeLimit = 60;
			this.now       = this.getUnixTime();
			this.upTime    = parseInt(this.getUpTime());
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

		getUnixTime() {
			var submitParams = "";
			submitParams += this.makeArgs("type",				"time");
			submitParams += this.makeArgs("act",  				"get_time");

			var rc = this.postArgs("modules/time_setup/html/common/time_process.php", submitParams);

			var t = rc.split(/[- :]/);
			var d = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
			var actiondate = new Date(d);

			return actiondate.getTime();
		}

		getUpTime() {
			var submitParams = "";
			submitParams += this.makeArgs("type",				"time");
			submitParams += this.makeArgs("act",  				"get_uptime");

			var rc = this.postArgs("modules/time_setup/html/common/time_process.php", submitParams);

			return rc;
		}

		updateTime() {
			this.now     = this.getUnixTime();
			this.upTime  = parseInt(this.getUpTime());
			this.timeCnt = 0;

			return ;
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
				this.upTime  = parseInt(this.getUpTime());
				this.timeCnt = 0;
			}

			var now    = new Date(this.now);
			var year   = this.addZero(now.getFullYear());
			var month  = this.addZero((now.getMonth() + 1));
			var date   = this.addZero(now.getDate());
			var hour   = this.addZero(now.getHours());
			var minute = this.addZero(now.getMinutes());
			var second = this.addZero(now.getSeconds());

			$("#span_menu_currentTime").html(year + "." + month + "." + date + " / " + hour + ":" + minute + ":" + second);
			this.now += 1000;

			var upTime = this.upTime;
			var upTime_secs = upTime % 60;
			upTime = parseInt(upTime / 60);
			var upTime_mins = upTime % 60;
			upTime = parseInt(upTime / 60);
			var upTime_hours = upTime % 24;
			upTime = parseInt(upTime / 24);
			var upTime_days = upTime;

			$("#span_menu_upTime").html(  this.addZero(upTime_days)  + " " + "<?=Common\Lang\STR_MENU_SYSTEM_DAYS ?> "
										+ this.addZero(upTime_hours) + ":"
										+ this.addZero(upTime_mins)  + ":"
										+ this.addZero(upTime_secs)  + " <?=Common\Lang\STR_MENU_SYSTEM_ELAPSED ?>");
			this.upTime = this.upTime + 1;

			this.timeCnt++;

			var self = this;
			setTimeout(function() { self.printTime(); }, 1000);
		}
	} // end of UptimeFunc()

	var uptimeFunc = new UptimeFunc();
	uptimeFunc.printTime();

	var nativeAlert = window.alert;
	window.alert = function(_msg) {
		nativeAlert(_msg);

		// time sync
		uptimeFunc.updateTime();

		return ;
	};

	var nativeConfirm = window.confirm;
	window.confirm = function(_msg) {
		var rc = nativeConfirm(_msg);

		// time sync
		uptimeFunc.updateTime();

		return rc;
	};

	// websocket : system_management module
	function ws_recv_system_management(_cmd_id, _is_binary, _length, _data, _this) {
		var type = parseInt(_cmd_id);

		switch( type ) {
			case -11 : // incorrect process
			break;

			case 1 : // system check
				$("#div_blur").show();
				$("#div_system_check_alert").css("display", "flex");
			break;

		} // switch

		return ;
	}

	var ws_system_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "system_management");
	ws_system_handler.setOnmessageHandler(ws_recv_system_management);
	ws_system_handler.run();

	$("[type=text]").bind("keyup focusout", function() {
		re = /['"]/gi;
		var temp = $(this).val();

		if( re.test(temp) ) {
			$(this).val(temp.replace(re, ""));
		}
	});

</script>

<?php
	include_once "{$_SERVER['DOCUMENT_ROOT']}/common/common_js_etc.php";
?>
