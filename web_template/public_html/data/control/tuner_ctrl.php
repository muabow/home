<script type="text/javascript" src="/js/jquery-3.1.1.js"></script>
<script type="text/javascript" src="/js/jquery-ui.js"></script>


<style>
body {
	padding 		: 0px;
	margin 			: 0px;
}

.tuner_control {
	width 			: 385px;
	height 			: 80px;
	display 		: flex;
	margin-bottom 	: 10px;
	background 		: #dee0e0;
	-webkit-box-shadow: 1px 1px 3px 1px rgba(0,0,0,0.5);
	-moz-box-shadow: 1px 1px 3px 1px rgba(0,0,0,0.5);
	box-shadow: 1px 1px 3px 1px rgba(0,0,0,0.5);
}

.tuner_control_title {
	width 			: 75px;
	height 			: 70px;
	line-height		: 70px;
	margin 			: 5px 0 0 5px;
	float 			: left;
	text-align 		: center;
	font-weight 	: bold;
}

.tuner_control_right {
	flex 			: 1 1 0;
	margin 			: 5px 5px 5px 0;
	border-left		: 1px solid black;
	float 			: left;
	color 			: white;
	font-weight 	: bold;
	font-size 		: 9pt;
}

.tuner_display {
	background 		: black;
	width 			: 92px;
	height 			: 70px;
	float 			: left;
	margin 			: 0 5px;
}

.tuner_right_control {
	float 			: left;
	width 			: 100px;
	cursor 			: pointer;
}

.control_number_row {
	display 		: flex;
	width 			: 100px;
}

.tuner_right_control_number {
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

</style>

<div class="tuner_control">
	<div class="tuner_control_title">TUNER</div>
	<div class="tuner_control_right">
		<div id="tuner_ctrl_display" class="tuner_display"></div>

		<div class="tuner_right_control">
			<div class="control_number_row">
				<div id="tuner_ctrl_1" class="tuner_right_control_number">1</div>
				<div id="tuner_ctrl_2" class="tuner_right_control_number">2</div>
				<div id="tuner_ctrl_3" class="tuner_right_control_number">3</div>
				<div id="tuner_ctrl_4" class="tuner_right_control_number">4</div>
			</div>
			<div class="control_number_row">
				<div id="tuner_ctrl_5" class="tuner_right_control_number">5</div>
				<div id="tuner_ctrl_6" class="tuner_right_control_number">6</div>
				<div id="tuner_ctrl_7" class="tuner_right_control_number">7</div>
				<div id="tuner_ctrl_8" class="tuner_right_control_number">8</div>
			</div>
			<div class="control_number_row">
				<div id="tuner_ctrl_9" class="tuner_right_control_number">9</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	const TUNER_KEY1 = 0x11;
	const TUNER_KEY2 = 0x12;
	const TUNER_KEY3 = 0x13;
	const TUNER_KEY4 = 0x14;
	const TUNER_KEY5 = 0x15;
	const TUNER_KEY6 = 0x16;
	const TUNER_KEY7 = 0x17;
	const TUNER_KEY8 = 0x18;
	const TUNER_KEY9 = 0x19;

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

	var is_written = false;
	$("[id^=tuner_ctrl_]").click(function() {
		if( is_written ) {
			return ;
		}
		is_written = true;
		$("[class^=tuner_right_control_number]").attr("style", "background: #999999;");
		 
		var arr_id 	   = $(this).attr("id").split("_");
		var preset_idx = arr_id[arr_id.length - 1];
		var tuner_key  = TUNER_KEY1 + parseInt(preset_idx) - 1;

		ws_handler.send(tuner_key, null);

		return ;
	});

	function ws_recv_data(_cmd_id, _is_binary, _length, _data, _this) {
		switch( _cmd_id ) {
			case 10 :
				var data = JSON.parse(_data);

				$("#tuner_ctrl_display").html("TUNER_KEY" + data.index + "<br>");
				$("#tuner_ctrl_display").append(data.channel + "<br>");

				$("[class^=tuner_right_control_number]").attr("style", "background: #e57d51;");
				is_written = false;

			break;
		}
	}

	var ws_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "tuner_ctrl");
	ws_handler.setOnmessageHandler(ws_recv_data);
	ws_handler.run();

</script>
