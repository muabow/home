<script src="http://code.jquery.com/jquery.min.js"></script>

<html>
	<b>Audio control</b>
	<div style="margin-bottom:10px; margin-top: 5px;">
		<button id="regist"  	 style="width: 160px; height: 40px;">regist</button>
		<button id="remove"  	 style="width: 160px; height: 40px;">remove</button>
		<button id="send_data" 	 style="width: 160px; height: 40px;">send</button>
		<button id="display" 	 style="width: 160px; height: 40px;">display (debug)</button>
	</div>

	<div style="width: 246px;display:flex; flex-direction: column;">
		<b>regist client</b>
		<textarea id="text_regist" style="height: 100px; width: 490px;">
{
	"ip_addr"	: "192.168.46.11",
	"uri"		: "audio_server"
}
		</textarea>
		<!--button id="send" style="margin-top:5px; height: 40px;">send</button-->
	</div>

</p>
	<div style="width: 246px;display:flex; flex-direction: column;">
		<b>remove client</b>
		<textarea id="text_remove" style="height: 100px; width: 490px;">
{
	"ip_addr"	: "192.168.46.11",
	"uri"		: "audio_server"
}
		</textarea>
	</div>

	<p />
	<div style="width: 246px;display:flex; flex-direction: column;">
		<b>send data</b>
		<textarea id="text_send_data" style="height: 100px; width: 490px;">
{
	"ip_addr"	: "192.168.46.11",
	"uri"		: "audio_server",
	"cmd_id"	: "0xA0",
	"data"		: null
}
		</textarea>
	</div>
</html>

<script type="text/javascript">
	class WebsocketHandler {
		constructor(_ipAddr, _uri, _is_debug = false) {
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
			this.uri		= _uri;
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
									data = String.fromCharCode.apply(null, new Int8Array(data));
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
								data = String.fromCharCode.apply(null, new Int8Array(data));
							}
						}

						if( _self.is_debug ) {
							console.log("cmd_id: [%s], is_binary: [%s], length: [%d], data: [%s]", cmd_id, is_binary, length, data);
						}

						_self.onmessageFunc(cmd_id, is_binary, length, data, _self);

					} else {
						var pData = JSON.parse(_msg.data);
						var sData = JSON.stringify(pData.data);

						if( isset(pData.type) && isset(sData.length) ) {
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
			var hostInfo = "ws://" + this.ipAddr + "/" + this.uri;

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
					_self.onopenFunc(_msg, _self);

				} else {
					_self.send(0x01, null);
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

	// TODO. 한글 인코딩
	// TODO. disconnect 시 status 체크 후 reconnect 전송

	var binary_data = null;

	function ws_recv_data(_cmd_id, _is_binary, _length, _data, _this) {
		var data = null;
		if( _is_binary ) {
			if( _data != null ) {
				data = new Int32Array(_data)[0];

				binary_data = new Int8Array(_data);
			}
		} else {
			data = _data;
		}

		console.log("uri: [%s], cmd_id: [%s], is_binary: [%s], length: [%d], data: [%s]", _this.uri, _cmd_id, _is_binary, _length, data);

		return ;
	}

	var ws_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "wsc_interface");

	ws_handler.setOnmessageHandler(ws_recv_data);

	ws_handler.run();

	$("#regist").click(function() {
		ws_handler.send(0x10, $("#text_regist").val());
	});

	$("#remove").click(function() {
		ws_handler.send(0x11, $("#text_remove").val());
	});

	$("#send_data").click(function() {
		var obj_data = JSON.parse($("#text_send_data").val());
		obj_data.cmd_id = parseInt(obj_data.cmd_id);
		var str_data = JSON.stringify(obj_data);

		ws_handler.send(0x12, str_data);
	});

	$("#display").click(function() {
		ws_handler.send(0x20, null);
	});

</script>

