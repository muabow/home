<?php
	const PATH_TTS_INFO   = "/opt/interm/public_html/modules/tts_file_management/conf/tts_info.json";
	$json_info = json_decode(file_get_contents(PATH_TTS_INFO));
	
	if( isset($_POST["type"]) && $_POST["type"] == "reload_list" ) {
		$arr_option_list = "";
		foreach( $json_info->tts_list as $key ) {
			$d_time = $key->tts_info->duration;
			$file_name = $key->tts_info->title;
			$file_path = $key->file_path;
			
			$arr_option_list .= "<option value=\"{$file_path}\"> [{$d_time}] {$file_name} &nbsp; &nbsp; </option>";
		}
		
		echo json_encode($arr_option_list);

		return ;
	}

	// onload process
	$arr_option_list = "";
	foreach( $json_info->tts_list as $key ) {
		$d_time = $key->tts_info->duration;
		$file_name = $key->tts_info->title;
		$file_path = $key->file_path;
		
		$arr_option_list .= "<option value=\"{$file_path}\"> [{$d_time}] {$file_name} &nbsp; &nbsp; </option>";
	}
?>

<style>
	body {
		padding 				: 0px;
		margin 					: 0px;
	}

	.tts_control {
		width 					: 385px;
		height 					: 80px;
		display 				: flex;
		margin-bottom 			: 10px;
		background 				: #dee0e0;
		-webkit-box-shadow 		: 1px 1px 3px 1px rgba(0,0,0,0.5);
		-moz-box-shadow 		: 1px 1px 3px 1px rgba(0,0,0,0.5);
		box-shadow 				: 1px 1px 3px 1px rgba(0,0,0,0.5);
	}

	.tts_control_title {
		width 					: 75px;
		margin 					: 0px 0px 0px 5px;
		float 					: left;
		text-align 				: center;
		font-weight 			: bold;
	}

	.tts_control_right {
		flex 					: 1 1 0;
		margin 					: 5px 5px 5px 0;
		border-left				: 1px solid black;
		float 					: left;
		color 					: black;
		font-weight 			: bold;
		font-size 				: 9pt;
	}

	.tts_control_box {
		margin-top 				: 20px;
		margin-left				: 5px;
	}

	.tts_div_button {
		background    			: linear-gradient(#5da8dd, #1869a3);
		border 					: 1px solid #7c7c7c;
		border-radius       	: 10px;
		color 					: white;
		text-align 				: center;
		text-decoration 		: none;
		display 				: inline-block;
		cursor 					: pointer;
		font-size 				: 12px;
		width 					: 80px;
		height 					: 24px;
		line-height				: 24px;
		-webkit-transition-duration: 0.4s; /* Safari */
		transition-duration		: 0.4s;
	}

	.tts_div_button:hover {
		color 					: white;
	}

	.tts_div_button:active {
		background    			: linear-gradient(#FF7070, #ce3b3b);
		border 					: 1px solid #7c7c7c;
		border-radius       	: 10px;
		color 					: black;
		text-align 				: center;
		text-decoration 		: none;
		display 				: inline-block;
		cursor 					: pointer;
		font-size 				: 12px;
		width 					: 80px;
		height 					: 24px;
		line-height				: 24px;
		-webkit-transition-duration: 0.4s; /* Safari */
		transition-duration		: 0.4s;
	}

	.tts_select {
		width 					: 190px;
		height 					: 26px;
		line-height				: 26px;
		font-size				: 12px;
		float					: left;
		margin-left				: 4px;
		margin-right			: 4px;
		border 					: 1px solid #7c7c7c;
		border-radius			: 2px;
		-webkit-transition-duration: 0.4s; /* Safari */
		transition-duration		: 0.4s;
		padding-left 			: 5px;
		padding-right 			: 5px;
		text-overflow 			: ellipsis;
	}

	.tts_no_drag {
		-ms-user-select 		: none; 
		-moz-user-select 		: -moz-none; 
		-webkit-user-select 	: none; 
		-khtml-user-select 		: none; 
		user-select 			: none;
	}

	.tts_control_left {
		display 				: flex;
		justify-content 		: center;
		align-items 			: center;
		flex-direction 			: column;
	}
</style>

<script type="text/javascript" src="/js/jquery-3.1.1.js"></script>
<script type="text/javascript" src="/js/jquery-ui.js"></script>

<div class="tts_control">
	<div class="tts_control_left">
		<div class="tts_control_title">TTS</div>
	</div>
	<div class="tts_control_right">
		<div class="tts_control_box">
			<select class="tts_select" id="select_tts">
				<option value="" selected disabled hidden> -- select -- </option>
				<?php echo $arr_option_list; ?>
			</select>
			|
			<div id="tts_button_tts" class="tts_div_button tts_no_drag"> PLAY </div>
		</div>
	</div>
</div>

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

	class TTS_Handle {
		constructor() {
			this.path = "./tts_ctrl.php";
			return ;
		}

		makeArgs(_key, _value) {
			var args = "&" + _key + "=" + _value;

			return args;
		}

		postArgs(_args) {
			var result;

			$.ajax({
				type	: "POST",
				url		: this.path,
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
	} // end of class : TTS_Handle()

	var g_play_tts_info 	= false;
	var g_is_reload_list 	= false;

	function reload_play_list() {
		var tts_handler = new TTS_Handle();
		var args = tts_handler.makeArgs("type", "reload_list");

		var result = tts_handler.postArgs(args);
		var arr_option_list = JSON.parse(result);
		
		$("#select_tts").empty().append("<option value=\"\" selected disabled hidden> -- select -- </option>");
		$("#select_tts").append(arr_option_list);
		
		var arr_ws_data = {};
		ws_handler.send(0x02, JSON.stringify(arr_ws_data));

		return ;
	}

	function ws_recv_func(_cmd_id, _is_binary, _length, _data, _self) {
		var data = JSON.parse(_data).data;
		
		switch( _cmd_id ) {
			case 0x00 :	// alive_info
				var alive_info  = parseInt(data.alive_info);
				var tts_index   = parseInt(data.tts_index) + 1;
				var tts_file    = data.tts_file;
				
				if( alive_info == 1 ) {
					$("#select_tts").css("backgroundColor", "linear-gradient(#5da8dd, #1869a3)");
					$("#select_tts").attr("disabled", "disabled");
					
					g_play_tts_info = true;
					
				} else {
					$("#select_tts").css("backgroundColor", "#FFFFFF");
					$("#select_tts").removeAttr("disabled");
					g_play_tts_info = false;

					if( g_is_reload_list ) {
						g_is_reload_list = false;
						reload_play_list();
					}
				}

				$("#select_tts").val(tts_file);

				break;
			
			case 0x10 : // tts_file_ctrl - reload list
				if( g_play_tts_info ) {
					g_is_reload_list = true;
					return ;
				}
				reload_play_list();
				break;

			default :
				break;
		}
		return ;
	}

	function ws_open_func() {
		ws_handler.send(0x00, null);
	
		return ;
	}

	$("#tts_button_tts").click(function() {
		var tts_set = $("#select_tts option:selected").val();
		var tts_idx = $("#select_tts option:selected").index() - 1;
		
		if( tts_set == "" ) {
			return ;
		}

		var arr_ws_data = {};
		arr_ws_data["tts_idx"] = tts_idx;
		ws_handler.send(0x01, JSON.stringify(arr_ws_data));
		

		return ;
	});

	var ws_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "tts_ctrl");
	ws_handler.setOnmessageHandler(ws_recv_func);
	ws_handler.setOnopenHandler(ws_open_func);
	ws_handler.run();

	var ws_ctrl_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "tts_file_ctrl");
	ws_ctrl_handler.setOnmessageHandler(ws_recv_func);
	ws_ctrl_handler.run();
</script>
