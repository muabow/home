<script src="/js/jquery-3.1.1.js"></script>
<script src="/js/jquery-ui.js"></script>

<html>
	<b>Audio control</b>
	<div style="margin-bottom:10px; margin-top: 5px; padding-left: 20px;">
		<b>Audio server</b>
		<br />
		<button id="button_server_init"  style="width: 120px; height: 40px;">INIT&RUN</button>
		<button id="button_server_run"  style="width: 120px; height: 40px;">RUN</button>
		<button id="button_server_stop" style="width: 120px; height: 40px;">STOP</button>

		<p />
		<b> - Cast type</b> <br />
		<input type="radio" name="radio_server_cast_type" value="unicast" checked="checked">UNICAST
		<input type="radio" name="radio_server_cast_type" value="multicast">MULTICAST
		<input type="radio" name="radio_server_cast_type" value="all">ALL

		<p />
		<b> - Encode type</b> <br />
		<input type="radio" name="radio_server_encode_type" value="pcm" checked="checked">PCM
		<input type="radio" name="radio_server_encode_type" value="mp3">MP3

		<p />
		<b> - MP3 Quality</b> <br />
		<input type="radio" name="radio_server_mp3_quality" value="2" checked="checked">High
		<input type="radio" name="radio_server_mp3_quality" value="5">Medium
		<input type="radio" name="radio_server_mp3_quality" value="7">Low

		<p />
		<b> - Multicast IP address</b> <br />
		<input type="text" id="text_server_ip_addr" value="224.124.0.200" style="height: 30px; font-size: 11pt; padding-left: 5px;" style="display: none;">
	</div>
	<div style="margin-bottom:10px; margin-top: 5px; padding-left: 20px;">
		<b>Audio client</b>
		<br />
		<button id="button_client_init"  style="width: 120px; height: 40px;">INIT&RUN</button>
		<button id="button_client_run"  style="width: 120px; height: 40px;">RUN</button>
		<button id="button_client_stop" style="width: 120px; height: 40px;">STOP</button>
		<button id="button_client_volume" style="width: 120px; height: 40px;">SET VOLUME</button>
		<br />
		<b> - Status</b>
		<div id="div_client_status" style="width: 200px; height: 30px; background: red; text-align: center; font-weight: bold;">
		</div>
		<b> - Cast type</b> <br />
		<input type="radio" name="radio_client_cast_type" value="unicast" checked="checked">UNICAST
		<input type="radio" name="radio_client_cast_type" value="multicast">MULTICAST
		<br />
		<b> - Redundancy </b> <br />
		<input type="radio" name="radio_client_redundancy" value="master" checked="checked">MASTER
		<input type="radio" name="radio_client_redundancy" value="slave">SLAVE
		<br />
		<b> - Unicast IP address : master</b> <br />
		<input type="text" id="text_client_unicast_ip_addr_master" value="192.168.48.55" style="height: 30px; font-size: 11pt; padding-left: 5px;">
		<br />
		<b> - Unicast IP address : slave</b> <br />
		<input type="text" id="text_client_unicast_ip_addr_slave" value="192.168.1.99" style="height: 30px; font-size: 11pt; padding-left: 5px;">
		<br />
		<b> - Multicast IP address</b> <br />
		<input type="text" id="text_client_multicast_ip_addr" value="224.124.0.200" style="height: 30px; font-size: 11pt; padding-left: 5px;">
		<br />
		<b> - Delay (sec/msec)</b> <br />
		<input type="text" id="text_delay_sec" style="height: 30px; width: 40px; font-size: 11pt; padding-left: 5px;">
		<input type="text" id="text_delay_msec" style="height: 30px; width: 40px; font-size: 11pt; padding-left: 5px;">
		<br />
		<b> - Current volume</b> <br />
		<input type="text" id="text_client_volume" value="100" style="height: 30px; width: 80px; font-size: 11pt; padding-left: 5px;">
		<br />
		<b> - Current level</b> <br />
		<input type="text" id="text_client_level" style="height: 30px; width: 80px; font-size: 11pt; padding-left: 5px;" disabled>
	</div>

</html>

<script type="text/javascript">
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

	function ws_recv_data(_cmd_id, _is_binary, _length, _data, _this) {
		if( _data == null ) return ;

		console.log("cmd_id: [%s], is_binary: [%s], length: [%d], data: [%s]", _cmd_id, _is_binary, _length, _data);


		if( _this.url == "audio_client" ) {
			switch( _cmd_id ) {
				case 1 :
					var data = JSON.parse(_data).data;
					if( parseInt(data.stat) == 1 ) {
						$("#div_client_status").css("background", "green");

					} else {
						$("#div_client_status").css("background", "red");
					}

					break;

				case 10 :
					var data = JSON.parse(_data).data;
					$("#text_delay_sec").val(data.delay_sec);
					$("#text_delay_msec").val(data.delay_msec);
					break;

				case 11 :
					var data = JSON.parse(_data).data;
					$("#text_client_volume").val(data.playVolume);
					break;

				case 12 :
					var data = JSON.parse(_data).data;
					$("#text_client_level").val(data.level);
					break;
			}
		}
		return ;
	}

	function ws_open_data(_this) {
		_this.send(0x01, null);

		return ;
	}

	var ws_audio_server_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "audio_server");
	var ws_audio_client_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "audio_client");

	ws_audio_server_handler.setOnmessageHandler(ws_recv_data);
	ws_audio_client_handler.setOnmessageHandler(ws_recv_data);

	ws_audio_server_handler.setOnopenHandler(ws_open_data);
	ws_audio_client_handler.setOnopenHandler(ws_open_data);

	ws_audio_server_handler.run();
	ws_audio_client_handler.run();

	$("#button_server_init").click(function() {
		// var server_json_value = '{"audio_encode_type" : "pcm", "network_mcast_ip_addr" : "' + $("#text_server_ip_addr").val() + '", "network_cast_type" : "' + $("input:radio[name=radio_server_cast_type]:checked").val() +'"}';
		var server_json_value = "";
		server_json_value += '{';
		server_json_value += '"audio_encode_type" : "'		+ $("input:radio[name=radio_server_encode_type]:checked").val() + '", ';
		server_json_value += '"audio_mp3_quality" : '		+ $("input:radio[name=radio_server_mp3_quality]:checked").val() + ', ';
		server_json_value += '"network_mcast_ip_addr" : "'	+ $("#text_server_ip_addr").val() + '", ';
		server_json_value += '"network_cast_type" : "'		+ $("input:radio[name=radio_server_cast_type]:checked").val() + '"';
		server_json_value += '}';


		ws_audio_server_handler.send(0x10, server_json_value);
		return ;
	});
	$("#button_server_run").click(function() {
		ws_audio_server_handler.send(0x11, null);
		return ;
	});

	$("#button_server_stop").click(function() {
		ws_audio_server_handler.send(0x12, null);
		return ;
	});

	$("#button_client_init").click(function() {
		var client_json_value = "";
		client_json_value += '{';
		client_json_value += '"network_cast_type" : "'		+ $("input:radio[name=radio_client_cast_type]:checked").val() + '", ';
		client_json_value += '"network_redundancy": "' 		+ $("input:radio[name=radio_client_redundancy]:checked").val() + '", ';
		client_json_value += '"network_master_ip_addr" : "' + $("#text_client_unicast_ip_addr_master").val() + '", ';
		client_json_value += '"network_slave_ip_addr" : "' 	+ $("#text_client_unicast_ip_addr_slave").val() + '", ';
		client_json_value += '"network_mcast_ip_addr" : "' 	+ $("#text_client_multicast_ip_addr").val() +'", ';
		client_json_value += '"audio_volume" : ' 			+ $("#text_client_volume").val() + ', ';
		client_json_value += '"audio_play_buffer_sec" : ' 	+ $("#text_delay_sec").val() + ', ';
		client_json_value += '"audio_play_buffer_msec" : ' 	+ $("#text_delay_msec").val();
		client_json_value += '}';

		ws_audio_client_handler.send(0x10, client_json_value);
		return ;
	});

	$("#button_client_run").click(function() {
		ws_audio_client_handler.send(0x11, null);
		return ;
	});

	$("#button_client_stop").click(function() {
		ws_audio_client_handler.send(0x12, null);
		return ;
	});

	$("#button_client_volume").click(function() {
		var client_json_value = '{"audio_volume" : ' + $("#text_client_volume").val() + '}';
		ws_audio_client_handler.send(0x13, client_json_value);
		return ;
	});


</script>
