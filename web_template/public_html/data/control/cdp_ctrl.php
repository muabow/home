<script type="text/javascript" src="/js/jquery-3.1.1.js"></script>
<script type="text/javascript" src="/js/jquery-ui.js"></script>

<style>
body {
	padding 		: 0px;
	margin 			: 0px;
}

.cdp_control {
	width 			: 385px;
	height 			: 80px;
	display 		: flex;
	margin-bottom 	: 10px;
	background 		: #dee0e0;
	-webkit-box-shadow: 1px 1px 3px 1px rgba(0,0,0,0.5);
	-moz-box-shadow: 1px 1px 3px 1px rgba(0,0,0,0.5);
	box-shadow: 1px 1px 3px 1px rgba(0,0,0,0.5);
}

.cdp_control_title {
	width 			: 75px;
	height 			: 70px;
	line-height		: 70px;
	margin 			: 5px 0 0 5px;
	float 			: left;
	text-align 		: center;
	font-weight 	: bold;
}

.cdp_control_right {
	flex 			: 1 1 0;
	margin 			: 5px 5px 5px 0;
	border-left		: 1px solid black;
	float 			: left;
	color 			: white;
	font-weight 	: bold;
	font-size 		: 9pt;
}

.cdp_display {
	background 		: black;
	width 			: 92px;
	height 			: 70px;
	float 			: left;
	margin 			: 0 5px;
}

.cdp_left_control {
	width 			: 95px;
	height 			: 70px;
	float 			: left;
	cursor 			: pointer;
}

.cdp_left_control_top {
	float 			: left;
	display 		: flex;
	width 			: 95px;
}

.cdp_left_control_top_left {
	float 			: left;
}

.cdp_eject {
	width 			: 18px;
	height 			: 18px;
	background 		: #ababab;
	border 			: 1px solid #555555;
	margin-bottom 	: 5px;
	text-align 		: center;
}

.cdp_prev {
	width 			: 18px;
	height 			: 18px;
	line-height 	: 15px;
	background 		: #ababab;
	border 			: 1px solid #555555;
	border-radius 	: 2px;
	text-align 		: center;
	font-size 		: 11pt;
	text-indent 	: -1px;
}

.cdp_play {
	flex 			: 1 1 0;
	height 			: 43px;
	line-height		: 35px;
	background 		: #ababab;
	border 			: 1px solid #555555;
	border-radius 	: 2px;
	float 			: left;
	margin 			: 0 5px;
	text-align 		: center;
	font-size 		: 27pt;
}

.cdp_left_control_top_right {
	float 			: left;
}

.cdp_stop {
	width 			: 18px;
	height 			: 18px;
	background 		: #ababab;
	border 			: 1px solid #555555;
	border-radius 	: 2px;
	text-align 		: center;
	margin-bottom 	: 5px;
	font-size 		: 9pt;
	text-indent 	: 1px;
}

.cdp_next {
	width 			: 18px;
	height 			: 18px;
	line-height		: 15px;
	background 		: #ababab;
	border 			: 1px solid #555555;
	border-radius 	: 2px;
	text-align 		: center;
	font-size 		: 11pt;
}

.cdp_repeat {
	width 			: 93px;
	height 			: 18px;
	line-height		: 18px;
	text-align 		: center;
	background 		: #ababab;
	border 			: 1px solid #555555;
	border-radius 	: 2px;
	float 		 	: left;
	margin-top 		: 5px;
}

.button_background_gray {
	background: -moz-radial-gradient(center, ellipse cover, rgba(165,165,165,1) 0%, rgba(201,201,201,1) 100%);
	background: -webkit-gradient(radial, center center, 0px, center center, 100%, color-stop(0%, rgba(165,165,165,1)), color-stop(100%, rgba(201,201,201,1)));
	background: -webkit-radial-gradient(center, ellipse cover, rgba(165,165,165,1) 0%, rgba(201,201,201,1) 100%);
	background: -o-radial-gradient(center, ellipse cover, rgba(165,165,165,1) 0%, rgba(201,201,201,1) 100%);
	background: -ms-radial-gradient(center, ellipse cover, rgba(165,165,165,1) 0%, rgba(201,201,201,1) 100%);
	background: radial-gradient(ellipse at center, rgba(165,165,165,1) 0%, rgba(201,201,201,1) 100%);
}

.cdp_right_control {
	float 			: left;
	width 			: 100px;
	cursor 			: pointer;
}

.control_number_row {
	display 		: flex;
	width 			: 100px;
}

.cdp_right_control_number {
	width  			: 18px;
	height 			: 18px;
	line-height		: 18px;
	text-align 		: center;
	margin 			: 0 0px 5px 5px;
	background 		: #e57d51;
	float 			: left;
	border-radius 	: 2px;
	border 			: 1px solid #ac5a45;
}

.cdp_right_control_function {
	width  			: 18px;
	height 			: 18px;
	line-height		: 18px;
	text-align 		: center;
	margin 			: 0 0px 5px 5px;
	float 			: left;
	border-radius 	: 2px;
	border 			: 1px solid #ac8445;
	background 		: #e5ba51;
}
</style>

<div class="cdp_control">
	<div class="cdp_control_title">CDP</div>
	<div class="cdp_control_right">
		<div id="cdp_ctrl_display" class="cdp_display"></div>
		<div class="cdp_left_control">
			<div class="cdp_left_control_top">
				<div class="cdp_left_control_top_left">
					<div id="cdp_ctrl_eject" class="cdp_eject button_background_gray">▲</div>
					<div id="cdp_ctrl_prev" class="cdp_prev button_background_gray">≪</div>
				</div>
				<div id="cdp_ctrl_play" class="cdp_play button_background_gray">▶</div>
				<div class="cdp_left_control_top_right">
					<div id="cdp_ctrl_stop" class="cdp_stop button_background_gray">■</div>
					<div id="cdp_ctrl_next" class="cdp_next button_background_gray">≫</div>
				</div>
			</div>
			<div id="cdp_ctrl_repeat" class="cdp_repeat button_background_gray">REPEAT</div>
		</div>
		<div class="cdp_right_control">
			<div class="control_number_row">
				<div id="cdp_ctrl_1" class="cdp_right_control_number">1</div>
				<div id="cdp_ctrl_2" class="cdp_right_control_number">2</div>
				<div id="cdp_ctrl_3" class="cdp_right_control_number">3</div>
				<div id="cdp_ctrl_4" class="cdp_right_control_number">4</div>
			</div>
			<div class="control_number_row">
				<div id="cdp_ctrl_5" class="cdp_right_control_number">5</div>
				<div id="cdp_ctrl_6" class="cdp_right_control_number">6</div>
				<div id="cdp_ctrl_7" class="cdp_right_control_number">7</div>
				<div id="cdp_ctrl_8" class="cdp_right_control_number">8</div>
			</div>
			<div class="control_number_row">
				<div id="cdp_ctrl_9" class="cdp_right_control_number">9</div>
				<div id="cdp_ctrl_10" class="cdp_right_control_number">0</div>
				<div class="cdp_right_control_function">C</div>
				<div class="cdp_right_control_function">S</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	const CMD_CHANGE_CD 		= 0x03;
	const CMD_CHANGE_USB 		= 0x07;
	const CMD_CHANGE_INNERMEM 	= 0x08;
	const CMD_REQ_STATUS 		= 0x0E;
	const CMD_REQ_TIME 			= 0x0F;
	const CD_KEY0 				= 0x10;
	const CD_KEY1 				= 0x11;
	const CD_KEY2 				= 0x12;
	const CD_KEY3 				= 0x13;
	const CD_KEY4 				= 0x14;
	const CD_KEY5 				= 0x15;
	const CD_KEY6 				= 0x16;
	const CD_KEY7 				= 0x17;
	const CD_KEY8 				= 0x18;
	const CD_KEY9 				= 0x19;

	const CMD_CD_PLAY 			= 0x20;
	const CMD_CD_STOP 			= 0x21;
	const CMD_CD_PAUSE 			= 0x22;
	const CMD_CD_NEXT 			= 0x2F;
	const CMD_CD_RW 			= 0x30;
	const CMD_CD_REPEAT 		= 0x29;
	const CMD_INSTANT_P1 		= 0x90;
	const CMD_INSTANT_P2 		= 0x91;
	const CMD_INSTANT_P3 		= 0x92;
	const CMD_INSTANT_P4 		= 0x93;
	const CMD_INSTANT_P5 		= 0x94;
	const CMD_INSTANT_P6 		= 0x95;
	const CMD_INSTANT_P7 		= 0x96;
	const CMD_INSTANT_P8 		= 0x97;
	const CMD_INSTANT_P9 		= 0x98;
	const CMD_INSTANT_P10 		= 0x99;

	const CMD_USB_PLAY 			= 0xC0;
	const CMD_USB_STOP 			= 0xC1;
	const CMD_USB_PAUSE 		= 0xC2;
	const CMD_USB_NEXT 			= 0xCF;
	const CMD_USB_RW 			= 0xD0;
	const CMD_USB_REPEAT 		= 0xC9;

	const CMD_SD_PLAY 			= 0xE0;
	const CMD_SD_STOP 			= 0xE1;
	const CMD_SD_PAUSE 			= 0xE2;
	const CMD_SD_NEXT 			= 0xEF;
	const CMD_SD_RW 			= 0xF0;
	const CMD_SD_REPEAT 		= 0xEA;
	const CMD_DISK1 			= 0xF1;
	const CMD_DISK2 			= 0xF2;
	const CMD_DISK3 			= 0xF3;
	const CMD_DISK4 			= 0xF4;
	const CMD_DISK5 			= 0xF5;
	const CMD_DISK6 			= 0xF6;
	const CMD_SET_CD6208_MOFF 	= 0x1E;
	const CMD_SET_CD6208_MON 	= 0x1F;


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


	$("#cdp_ctrl_eject").click(function() {
		switch( g_cdp_ctrl["cdp_mode"] ) {
			case "CD" 	: ws_handler.send(CMD_CHANGE_USB, null);		break;
			case "USB"	: ws_handler.send(CMD_CHANGE_INNERMEM, null);	break;
			case "MEM"	: ws_handler.send(CMD_CHANGE_CD, null);			break;
			default		: break;
		}

	});

	$("#cdp_ctrl_play").click(function() {
		switch ( g_cdp_ctrl["cdp_mode"] ) {
			case "CD" :
				if ( (g_cdp_ctrl["action"] == "RESUME") && (g_cdp_ctrl["run"] == "STOP") ) {
					this.innerHTML = "∥";
					ws_handler.send(CMD_CD_PLAY, null);

				} else if ( (g_cdp_ctrl["action"] == "RESUME") && (g_cdp_ctrl["run"] == "PLAY") ) {
					this.innerHTML = "▶";
					ws_handler.send(CMD_CD_PAUSE, null);

				} else if ( (g_cdp_ctrl["action"] == "PAUSE") && (g_cdp_ctrl["run"] == "PLAY") ) {
					this.innerHTML = "∥";
					ws_handler.send(CMD_CD_PLAY, null);
				}

			break;

			case "USB" :
				if ( (g_cdp_ctrl["action"] == "RESUME") && (g_cdp_ctrl["run"] == "STOP") ) {
					this.innerHTML = "∥";
					ws_handler.send(CMD_USB_PLAY, null);

				} else if ( (g_cdp_ctrl["action"] == "RESUME") && (g_cdp_ctrl["run"] == "PLAY") ) {
					this.innerHTML = "▶";
					ws_handler.send(CMD_USB_PAUSE, null);

				} else if ( (g_cdp_ctrl["action"] == "PAUSE") && (g_cdp_ctrl["run"] == "PLAY") ) {
					this.innerHTML = "∥";
					ws_handler.send(CMD_USB_PLAY, null);
				}

			break;

			case "MEM" :
				if ( (g_cdp_ctrl["action"] == "RESUME") && (g_cdp_ctrl["run"] == "STOP") ) {
					this.innerHTML = "∥";
					ws_handler.send(CMD_SD_PLAY, null);

				} else if ( (g_cdp_ctrl["action"] == "RESUME") && (g_cdp_ctrl["run"] == "PLAY") ) {
					this.innerHTML = "▶";
					ws_handler.send(CMD_SD_PAUSE, null);

				} else if ( (g_cdp_ctrl["action"] == "PAUSE") && (g_cdp_ctrl["run"] == "PLAY") ) {
					this.innerHTML = "∥";
					ws_handler.send(CMD_SD_PLAY, null);
				}

			break;

			default	: break;
		}
	});

	$("#cdp_ctrl_stop").click(function() {
		switch( g_cdp_ctrl["cdp_mode"] ) {
			case "CD" 	: ws_handler.send(CMD_CD_STOP, null);	break;
			case "USB"	: ws_handler.send(CMD_USB_STOP, null);	break;
			case "MEM"	: ws_handler.send(CMD_SD_STOP, null);	break;
			default		: break;
		}

	});

	$("#cdp_ctrl_repeat").click(function() {
		switch( g_cdp_ctrl["cdp_mode"] ) {
			case "CD" 	: ws_handler.send(CMD_CD_REPEAT, null);	 break;
			case "USB"	: ws_handler.send(CMD_USB_REPEAT, null); break;
			case "MEM"	: ws_handler.send(CMD_SD_REPEAT, null);	 break;
			default		: break;
		}

	});

	$("#cdp_ctrl_prev").click(function() {
		switch( g_cdp_ctrl["cdp_mode"] ) {
			case "CD" 	: ws_handler.send(CMD_CD_RW, null);	 break;
			case "USB"	: ws_handler.send(CMD_USB_RW, null); break;
			case "MEM"	: ws_handler.send(CMD_SD_RW, null);	 break;
			default		: break;
		}

	});

	$("#cdp_ctrl_next").click(function() {
		switch( g_cdp_ctrl["cdp_mode"] ) {
			case "CD" 	: ws_handler.send(CMD_CD_NEXT, null);	break;
			case "USB"	: ws_handler.send(CMD_USB_NEXT, null);	break;
			case "MEM"	: ws_handler.send(CMD_SD_NEXT, null);	break;
			default		: break;
		}

	});

	$("#cdp_ctrl_1").click(function() {
		if (g_cdp_ctrl["cdp_mode"] == "MEM")
		{
			ws_handler.send(CMD_INSTANT_P1, null);
		}
	});

	$("#cdp_ctrl_2").click(function() {
		if (g_cdp_ctrl["cdp_mode"] == "MEM")
		{
			ws_handler.send(CMD_INSTANT_P2, null);
		}
	});

	$("#cdp_ctrl_3").click(function() {
		if (g_cdp_ctrl["cdp_mode"] == "MEM")
		{
			ws_handler.send(CMD_INSTANT_P3, null);
		}
	});

	$("#cdp_ctrl_4").click(function() {
		if (g_cdp_ctrl["cdp_mode"] == "MEM")
		{
			ws_handler.send(CMD_INSTANT_P4, null);
		}
	});

	$("#cdp_ctrl_5").click(function() {
		if (g_cdp_ctrl["cdp_mode"] == "MEM")
		{
			ws_handler.send(CMD_INSTANT_P5, null);
		}
	});

	$("#cdp_ctrl_6").click(function() {
		if (g_cdp_ctrl["cdp_mode"] == "MEM")
		{
			ws_handler.send(CMD_INSTANT_P6, null);
		}
	});

	$("#cdp_ctrl_7").click(function() {
		if (g_cdp_ctrl["cdp_mode"] == "MEM")
		{
			ws_handler.send(CMD_INSTANT_P7, null);
		}
	});

	$("#cdp_ctrl_8").click(function() {
		if (g_cdp_ctrl["cdp_mode"] == "MEM")
		{
			ws_handler.send(CMD_INSTANT_P8, null);
		}
	});

	$("#cdp_ctrl_9").click(function() {
		if (g_cdp_ctrl["cdp_mode"] == "MEM")
		{
			ws_handler.send(CMD_INSTANT_P9, null);
		}
	});

	$("#cdp_ctrl_10").click(function() {
		if (g_cdp_ctrl["cdp_mode"] == "MEM")
		{
			ws_handler.send(CMD_INSTANT_P10, null);
		}
	});

	var g_cdp_ctrl = {	"cdp_mode" 	 	: "none",
					 	"action" 		: "none",
					 	"run" 			: "none",
					 	"instant_mode"	: "unset",
					 	"instant_setid" :	0 	};


	function ws_recv_data(_cmd_id, _is_binary, _length, _data, _this) {
		switch( _cmd_id ) {
			case 10 :
				var data = JSON.parse(_data);

				if( data.RETURN_CODE == 17 ) {
					g_cdp_ctrl["run"] 	 	= data.RUN;
					g_cdp_ctrl["action"] 	= data.ACTION;
					g_cdp_ctrl["cdp_mode"] 	= data.CDP_MODE;

					switch( g_cdp_ctrl["cdp_mode"] ) {
						case "CD" 	: $("#cdp_ctrl_eject").html("○");	break;
						case "USB"	: $("#cdp_ctrl_eject").html("Ｕ");	break;
						case "MEM"	: $("#cdp_ctrl_eject").html("⌂");	break;
						default		: break;
					}

					$("#cdp_ctrl_display").html(g_cdp_ctrl["cdp_mode"] + " ");

					if ( (g_cdp_ctrl["action"] == "RESUME") && (g_cdp_ctrl["run"] == "STOP") )
					{
						$("#cdp_ctrl_play").html("▶");
						$("#cdp_ctrl_display").append("STOP" + "<br>");
					}
					else if ( (g_cdp_ctrl["action"] == "RESUME") && (g_cdp_ctrl["run"] == "PLAY") )
					{
						$("#cdp_ctrl_play").html("∥");
						$("#cdp_ctrl_display").append("PLAY" + "<br>");
					}
					else if ( (g_cdp_ctrl["action"] == "PAUSE") && (g_cdp_ctrl["run"] == "PLAY") )
					{
						$("#cdp_ctrl_play").html("▶");
						$("#cdp_ctrl_display").append("PAUSE" + "<br>");
					}

					switch( g_cdp_ctrl["cdp_mode"] ) {
						case "CD" 	: $("#cdp_ctrl_display").append("R:" + data.REPEAT + " R:" + data.RANDOM + "<br>");		break;
						case "USB"	: $("#cdp_ctrl_display").append("R:" + data.REPEAT + " C:" + data.CONNECT + "<br>");	break;
						case "MEM"	: $("#cdp_ctrl_display").append("R:" + data.REPEAT + " R:" + data.RANDOM + "<br>");		break;
						default		: break;
					}

					$("#cdp_ctrl_display").append("Tr : " + data.TRACK_INFO_NUM);

				}
				else if ( data.RETURN_CODE == 241 )
				{
					//CDP TIME CMD
					g_cdp_ctrl["run"] 	 	= data.RUN;
					g_cdp_ctrl["action"] 	= data.ACTION;
					g_cdp_ctrl["cdp_mode"]	= data.AUDIO_TYPE;

					switch( g_cdp_ctrl["cdp_mode"] ) {
						case "CD" 	: $("#cdp_ctrl_eject").html("○");	break;
						case "USB"	: $("#cdp_ctrl_eject").html("Ｕ");	break;
						case "MEM"	: $("#cdp_ctrl_eject").html("⌂");	break;
						default		: break;
					}

					$("#cdp_ctrl_display").html(data.DEV_INDEX_NUM + " " + g_cdp_ctrl["cdp_mode"] + " ");

					if ( (g_cdp_ctrl["action"] == "RESUME") && (g_cdp_ctrl["run"] == "STOP") )
					{
						$("#cdp_ctrl_play").html("▶");
						$("#cdp_ctrl_display").append("STOP" + "<br>");
					}
					else if ( (g_cdp_ctrl["action"] == "RESUME") && (g_cdp_ctrl["run"] == "PLAY") )
					{
						$("#cdp_ctrl_play").html("∥");
						$("#cdp_ctrl_display").append("PLAY" + "<br>");
					}
					else if ( (g_cdp_ctrl["action"] == "PAUSE") && (g_cdp_ctrl["run"] == "PLAY") )
					{
						$("#cdp_ctrl_play").html("▶");
						$("#cdp_ctrl_display").append("PAUSE" + "<br>");
					}

					$("#cdp_ctrl_display").append("Tr : " + data.TRACK_INFO_NUM + "<br>");
					$("#cdp_ctrl_display").append(data.CURRENT_PLAY_TIME + " " + data.TRACK_TOTAL_TIME);
				}

			break;
		}
		return ;
	}

	function ws_open_data(_this) {
		_this.send(CMD_REQ_STATUS, null);

		return ;
	}

	var ws_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "cdp_ctrl");

	ws_handler.setOnmessageHandler(ws_recv_data);
	ws_handler.setOnopenHandler(ws_open_data);
	ws_handler.run();


</script>
