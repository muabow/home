
<style>
	body {
		padding 					: 0px;
		margin 						: 0px;
	}
	
	.source_file_circle_act {
		width						: 16px;
		height                		: 16px;
		border-radius         	 	: 50%;
		background             		: green;
		opacity            			: 0.7;
	}

	.streaming_server_status {
		display  					: flex;
		width 						: 45px;
		justify-content 			: space-around;
	}

   .source_file_circle_deact {
		background       			: red;
		width               		: 16px;
		height             		    : 16px;
		border-radius          		: 50%;
		opacity           			: 0.7;
   }
   
   .source_file_circle_default {
		background       			: #dee0e0;
		width               		: 16px;
		height             		    : 16px;
		border-radius          		: 50%;
		opacity           			: 0.7;
   }
   
   .control_playback_repeat_folder {
		background 					: url("../../img/source/control_playback_repeat_folder.svg");
		background-repeat 			: no-repeat;
		background-size 			: 100%;
		background-position 		: center center;
		float 						: left;
      	width   	           		: 17px;
      	height 	               		: 15px;
   }
   
   .control_playback_repeat {
		background 					: url("../../img/source/control_playback_repeat.svg");
		background-repeat 			: no-repeat;
		background-size 			: 100%;
		background-position 		: center center;
		float 						: left;
      	width             			: 17px;
      	height              		: 15px;
   }
   
   
	.streaming_control {
		width 						: 385px;
		height 						: 80px;
		display 					: flex;
		margin-bottom 				: 10px;
		background 					: #dee0e0;
		-webkit-box-shadow 			: 1px 1px 3px 1px rgba(0,0,0,0.5);
		-moz-box-shadow 			: 1px 1px 3px 1px rgba(0,0,0,0.5);
		box-shadow 					: 1px 1px 3px 1px rgba(0,0,0,0.5);
	}

	.streaming_control_right {
		flex 						: 1 1 0;
		margin 						: 5px 5px 5px 0;
		border-left					: 1px solid black; 
		float 						: left;
		color 						: black;
		font-weight 				: bold;
		font-size 					: 9pt;
	}
	
	.streaming_control_box {
		margin-left					: 5px;
		display 					: flex;
		height 						: 30px;
		justify-content 			: center;
	}
	
	.streaming_play_control {
		background 					: #dee0e0;
		margin 						: 0px 5px;
		display 					: flex;
		width 						: 240px;
	}
	
	.streaming_div_button {
		background    				: linear-gradient(#5da8dd, #1869a3);
		border 						: 1px solid #7c7c7c;
		border-radius       		: 10px;
		color 						: white;
		text-align 					: center;
		text-decoration 			: none;
		display 					: inline-block;
		cursor 						: pointer;
		font-size 					: 12px;
		width 						: 80px;
		height 						: 24px;
		line-height					: 24px;
		-webkit-transition-duration	: 0.4s; /* Safari */
		transition-duration			: 0.4s;
	}
	
	.streaming_div_button:hover {
		color 						: white;
	}
	
	.streaming_div_button:active {
		background    				: linear-gradient(#FF7070, #ce3b3b);
		border 						: 1px solid #7c7c7c;
		border-radius       		: 10px;
		color 						: black;
		text-align 					: center;
		text-decoration 			: none;
		display 					: inline-block;
		cursor 						: pointer;
		font-size 					: 12px;
		width 						: 80px;
		height 						: 24px;
		line-height					: 24px;
		-webkit-transition-duration	: 0.4s; /* Safari */
		transition-duration			: 0.4s;
	}
	
	.div_streaming_select {
		width 						: 270px;
		height 						: 30px;
		line-height					: 26px;
		font-size					: 12px;
		float						: left;
		margin-left					: 4px;
		margin-right				: 4px;
		border 						: 1px solid #7c7c7c;
		border-radius				: 2px;
		-webkit-transition-duration	: 0.4s; /* Safari */
		transition-duration			: 0.4s;
		padding-left 				: 5px;
		padding-right 				: 5px;
		text-overflow 				: ellipsis;
	}
	
	.streaming_no_drag {
		-ms-user-select 			: none; 
		-moz-user-select 			: -moz-none; 
		-webkit-user-select 		: none; 
		-khtml-user-select 			: none; 
		user-select 				: none;
	}
	
	.streaming_slider_container {
		width 						: 315px;
	}
	
	.streaming_slider_speaker {
		margin-left 				: 5px;
		height 						: 16px;
		width 						: 18px;
		background 					: url("../../img/icon_speaker.svg");
		background-repeat 			: no-repeat;
		background-size 			: 100%;
		background-position 		: center center;
		float 						: left;
	}
	
	.streaming_slider_volume {
		margin-top 					: 4px;
		border 						: solid 1px #7c7c7c;
		border-radius 				: 10px;
		height 						: 7px;
		width 						: 310px;
		outline 					: none;
		transition 					: background 450ms ease-in;
		-webkit-appearance			: none;
		float 						: left;
	}
	
	.streaming_slider_text {
		height 						: 16px;
		width 						: 29px;
		float 						: left;
		font-size 					: 12px;
		text-align 					: center;
		border 						: 1px solid #7c7c7c;
	}
	
	#control_button_prev {
		background					: url("../../img/source/control_prev.svg");	
		margin-left					: 7px;
		background-repeat 			: no-repeat;
		background-size 			: 100%;
		background-position 		: center center;
		width 						: 34px;
		height 						: 40px;
	}
	
	#control_button_prev:active {
		background 					: url("../../img/source/control_prev_reverse.svg");
		margin-left 				: 7px;
		background-repeat 			: no-repeat;
		background-size 			: 100%;
		background-position 		: center center;
		width 						: 34px;
		height 						: 40px;
	}
	
	
	#control_button_play {
		background 					: url("../../img/source/control_play.svg");	
		margin-left					: 7px;
		background-repeat			: no-repeat;
		background-size				: 100%;
		background-position 		: center center;
		width 						: 34px;
		height 						: 40px;
	}
	
	#control_button_play:active {
		background 					: url("../../img/source/control_play_reverse.svg");	
		margin-left 				: 7px;
		background-repeat 			: no-repeat;
		background-size 			: 100%;
		background-position 		: center center;
		width 						: 34px;
		height 						: 40px;
	}
	
	.control_button_play_all {
		background 					: url("../../img/source/control_play_folder.svg");	
		margin-left 				: 7px;
		background-repeat 			: no-repeat;
		background-size 			: 100%;
		background-position 		: center center;
		width 						: 34px;
		height 						: 40px;
	}
	
	.control_button_play_all_reverse {
		background 					: url("../../img/source/control_play_folder_reverse.svg");
		margin-left 				: 7px;
		background-repeat 			: no-repeat;
		background-size 			: 100%;
		background-position 		: center center;
		width 						: 34px;
		height 						: 40px;
	}
	
	#control_button_pause {
		background 					: url("../../img/source/control_pause.svg");	
		margin-left 				: 7px;
		background-repeat 			: no-repeat;
		background-size 			: 100%;
		background-position 		: center center;
		width 						: 34px;
		height 						: 40px;
		display 					: none;
	}
	
	#control_button_pause:active {
		background 					: url("../../img/source/control_pause_reverse.svg");
		margin-left 				: 7px;
		background-repeat 			: no-repeat;
		background-size 			: 100%;
		background-position 		: center center;
		width 						: 34px;
		height 						: 40px;
	}
	
	#control_button_stop {
		background 					: url("../../img/source/control_stop.svg");	
		margin-left 				: 7px;
		background-repeat 			: no-repeat;
		background-size 			: 100%;
		background-position 		: center center;
		width 						: 34px;
		height 						: 40px;
	}
	
	#control_button_stop:active {
		background 					: url("../../img/source/control_stop_reverse.svg");
		margin-left 				: 7px;
		background-repeat 			: no-repeat;
		background-size 			: 100%;
		background-position 		: center center;
		width 						: 34px;
		height 						: 40px;
	}
	
	.div_control_button_loop {
		background 					: url("../../img/source/control_loop.svg");	
		margin-left 				: 7px;
		background-repeat 			: no-repeat;
		background-size 			: 100%;
		background-position 		: center center;
		width 						: 34px;
		height 						: 40px;
	}
	
	.div_control_button_loop_reverse {
		background 					: url("../../img/source/control_loop_reverse.svg");
		margin-left 				: 7px;
		background-repeat 			: no-repeat;
		background-size 			: 100%;
		background-position 		: center center;
		width 						: 34px;
		height 						: 40px;
	}
	
	#control_button_next {
		background 					: url("../../img/source/control_next.svg");	
		margin-left					: 7px;
		background-repeat 			: no-repeat;
		background-size 			: 100%;
		background-position 		: center center;
		width 						: 34px;
		height 						: 40px;
	}
	
	#control_button_next:active {
		background 					: url("../../img/source/control_next_reverse.svg");
		margin-left 				: 7px;
		background-repeat 			: no-repeat;
		background-size 			: 100%;
		background-position 		: center center;
		width 						: 34px;
		height 						: 40px;
	}
	
	#control_seperate {
		height 						: 40px;
	    font-size					: x-large;
	    margin-left					: 10px;
	    margin-top 					: 3px;
	}
	
	.div_control_button {
		margin-top 					: 2px;
	}
	
	.streaming_control_left {
		display 					: flex;
		justify-content 			: center;
		align-items 				: center;
		flex-direction 				: column;
	}
	
	.streaming_control_title {
	    width						: 75px;
	    height						: 30px;
	    line-height					: 30px;
	    margin 						: 0px 0 0 5px;
	    float 						: left;
	    text-align 					: center;
	    font-weight 				: bold;
	}
	


</style>

<div class="streaming_control">
	
	<div class="streaming_control_left">
		<div class="streaming_control_title">STREAM</div>
		<div class="streaming_server_status">
			<div class="div_source_file_circle"></div>
			<div class="div_control_playback"></div>
		</div>
	</div>
	
	<div class="streaming_control_right">
	
		<div class="streaming_control_box">
			<select class="div_streaming_select" id="select_streaming">
				<option value="" selected disabled hidden> -- select -- </option>
			</select>
		</div>
		
		<div class="streaming_control_box">
			<div class="streaming_play_control" id="div_play_control_display">
				<div class="div_control_button" id="control_button_prev"></div>
				<div class="div_control_button div_control_button_pause" id="control_button_pause"></div>
				<div class="div_control_button div_control_button_play" id="control_button_play"></div>
				<div class="div_control_button" id="control_button_stop"></div>
				<div class="div_control_button" id="control_button_next"></div>
				<div class="div_control_button div_control_button_loop" id="control_button_loop"></div>
				<div class="div_control_button" id="control_seperate">|</div>
				<div class="div_control_button control_button_play_all" id="control_button_play_all"></div>
			</div>
		</div>

	</div>
	
</div>

<script type="text/javascript" src="/js/jquery-3.1.1.js"></script>
<script type="text/javascript" src="/js/jquery-ui.js"></script>
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
	
	class StreamingFunc {
		constructor() {
			this.path = "./streaming_ctrl.php";
			return;
		}
		
		makeArgs(_key, _value) {
			var args = "&" + _key + "=" + _value;
			
			return args;
		}
		
		postArgs(_args) {
			var result;
			
			$Array.ajax({
				type 	: "POST",
				url		: this.path,
				data	: _args,
				async	: false,
				success : function(data) {
					if (data != null) {
						result = data;
					};
				}
			});
			
			return result;
		}
	} // end of class : StreamingFunc()
	
	
	
	function pad(_n, _width) {
		_n = _n + '';
		return _n.length >= _width ? _n : new Array(_width - _n.length + 1).join('0') + _n;
	}
	
	// EVENT START/////////////////////////////////////////////
			
	$(document).on("click", "#control_button_prev", function() {
		
		ws_svr_ctrl_handler.send(0x13, null);
		
		return;
	});
	
	$(document).on("click", "#control_button_next", function() {
				
		ws_svr_ctrl_handler.send(0x14, null);
		
		return;
	});
	
	$(document).on("click", "#control_button_loop", function() {
		
		ws_svr_ctrl_handler.send(0x15, null);
		
		return;		
	});
	
	$(document).on("click", "#control_button_pause", function() {
		
		ws_svr_ctrl_handler.send(0x11, null);
		
		return;
	});
	
	$(document).on("click", "#control_button_play", function() {
		
		if(json_alive_info.stat == 0) {
			$(".div_source_file_circle").removeClass("source_file_circle_deact").addClass("source_file_circle_default");
			
			setTimeout(function() {$(".div_source_file_circle").removeClass("source_file_circle_default").addClass("source_file_circle_deact")}
			, 500);

			return;
		}
		
		if ($("#select_streaming").val() == null) {
			
			// 음원이 선택되어있지 않을 때, select 화면 색 반전 애니메이션 추가
			$("#select_streaming").animate({
				'background-color' : 'red'
			});
			
			return;
		}
				
		var str_json = '{';
		str_json += '"num_source_list": ' + $("#select_streaming option:selected").text().length + ',';
		str_json += '"source_hash_id":"' + $("#select_streaming option:selected").val() + '",';
		str_json += '"source_loop_count": 1 ';
		str_json += '}';

		ws_svr_ctrl_handler.send(0x10, str_json);
		
		return;
	});
	
	$(document).on("click", ".control_button_play_all", function() {
		if( json_alive_info.stat == 0 ) {
			// 서버가 켜져 있지 않을 때, alert창 대신 서버 상태 화면 색 반전 애니메이션 추가
			$(".div_source_file_circle").animate({
				'background-color' : '#dee0e0'
			}, 300, function() {
				$(".div_source_file_circle").animate({
				'background-color' : 'red'
				},100);	
			});
			
			return;
		}
		
		// 파일 재생 중일 때는 폴더 재생 버튼이 눌리지 않도록 조건 추가
		if ($("#control_button_play").css("display") == "none") { // play가 아닐때
			return;
		}
		
		if ($("#control_button_play_all").hasClass("control_button_play_all")) {
			 $("#control_button_play_all").removeClass("control_button_play_all").addClass("control_button_play_all_reverse");
		}
		// get option count
		var num_source_list = $("#select_streaming option").length - 1;

		// get hash list
		var str_hash_list  = "";
		var str_loop_count = "";
		$("#select_streaming option").each(function(_idx, _item) {
			if( _idx == 0 ) return true;

			str_hash_list  += $("#select_streaming option:eq(" + _idx + ")").val(); 
			str_loop_count += 1; 
			if( _idx < num_source_list ) {
				str_hash_list  += ",";
				str_loop_count += ","; 
			}
		});

		var str_json = '{';
		str_json += '"num_source_list": '   + num_source_list + ',';
		str_json += '"source_hash_id":"'    + str_hash_list   + '",';
		str_json += '"source_loop_count":"' + str_loop_count  + '"';
		str_json += '}';
		ws_svr_ctrl_handler.send(0x10, str_json);
		return;
	});
	
	$(document).on("click", "#control_button_stop", function() {
		$(".div_control_playback").removeClass("control_playback_repeat_folder").removeClass("control_playback_repeat");
		$("#control_button_play_all").removeClass("control_button_play_all_reverse").addClass("control_button_play_all");
		ws_svr_ctrl_handler.send(0x12, null);
		return;
	});
	
	// EVENT END////////////////////////////////////////////
	
	var gSourceOrderList = [];
	var repeat_action = "";
	
	
		
	// function START
	function set_list(_data) {
		var num_play_monitor = -1;	

		$("[id^=button_monitor_]").each(function() {
			if( $(this).attr("class") ) {
				num_play_monitor = $(this).attr("id").split("_")[2];
			 }
		});
		
		$( "#sortable" ).sortable("destroy");
		
		var source_list = JSON.parse(_data.source_list);
		
		var row_data = "";
		var repeat_count = 0;
		repeat_action = "";
		gSourceOrderList = [];
		
		$("#select_streaming").empty();
		$("#select_streaming").append("<option value='' selected disabled hidden> -- select -- </option>");
		$("#select_streaming").css('backgroundColor','white');
		$("#select_streaming").css('border','1px solid #7c7c7c');			
		$("#select_streaming").attr('disabled', false);
			
		$.each(source_list, function(i, source_info) {
			var is_valid = source_info.is_valid_source;
			
			if (source_info.source_type == "wav") {
				str_source_info = (source_info.num_sample_rate / 1000);
				str_source_info = str_source_info.toFixed(1);
				str_source_info = str_source_info + "Khz";
			} else {
				str_source_info = parseInt(source_info.nub_bit_rate / 1000);
				str_source_info = str_source_info + "Kbps";
			}
			
			var str_ext_type = source_info.source_type.toUpperCase();
			
			var str_monitor_class = "";
			if (num_play_monitor == i) {
				str_monitor_class = 'class="monitor_play"';
			}
			
			var paly_time_min = pad(Math.floor(source_info.audio_play_time / 60),2);
			var paly_time_sec = pad(source_info.audio_play_time % 60 , 2);
			var paly_time = paly_time_min + ':' + paly_time_sec;
			
			// 화면에 보여지기
			var option = $("<option value='"+ source_info.source_hash_id + "'> [" + paly_time + "] " + source_info.source_name + "</option>");
			$("#select_streaming").append(option);

			
			// 현재 플레이중인 애를 선택하기.(select)
			if (source_info.is_play) {
				$("#select_streaming").val(source_info.source_hash_id);
				$("#select_streaming").css('backgroundColor','turquoise');
				$("#select_streaming").attr('disabled', true);
			}
				
			if (source_info.is_playlist) { 
				repeat_count++ ;
			}
			
			gSourceOrderList.push(source_info.source_hash_id);
		});
		
		$(".div_source_name").each(function(i, e) {
			if ($(e)[0].scrollWidth > $(e).innerWidth()) {
				$(e).addClass("overflow_ellipsis");
			}
		});
		(repeat_count == source_list.length) ? repeat_action = "folder" : repeat_action = "file" ;
						
		return;		
	}
	// function END
	// ebsocket event handler functions
	function ws_open_server_func(_this) {
		_this.send(0x01, null);
		
		return;
	}
	
	function ws_recv_server_func(_cmd_id, _is_binary, _length, _data, _this) {

		if (_cmd_id == 1 && _data == null) return;
		var data = JSON.parse(_data).data;

		switch(_cmd_id) {
			case 1 : // alive info
				json_alive_info = data;
				
				if (json_alive_info.view == "setup") { // setup일때
				} else { // setup이 아닐 때
					if (json_alive_info.stat == 1) { // operation run
						$(".div_source_file_circle").removeClass("source_file_circle_deact").addClass("source_file_circle_act");
					} else { // operation stop
						$(".div_source_file_circle").removeClass("source_file_circle_act").addClass("source_file_circle_deact");
					}
				}
				break;
			
			case 2 : // operation info
				json_oper_info = data;
				break;
				
			case 3 : // client list info
				break;
			
			case 18 : // play operation info
			
				if(data.is_play == 1 && data.is_pause == 0) {
					$("#control_button_play").hide();
					$("#control_button_pause").show();
					// play 중인데 folder 재생일 때는 folder 상태를 누름으로 변경
					if (repeat_action == "folder") {
						$("#control_button_play_all").removeClass("control_button_play_all").addClass("control_button_play_all_reverse");						
					} else if (repeat_action == "file") {
						$("#control_button_play_all").removeClass("control_button_play_all_reverse").addClass("control_button_play_all");
					}
																
				} else {
					$("#control_button_pause").hide();
					$("#control_button_play").show();
					$("#control_button_play_all").removeClass("control_button_play_all_reverse").addClass("control_button_play_all");
				}
				
				if (data.is_loop == 1) {
					$("#control_button_loop").removeClass("div_control_button_loop").addClass("div_control_button_loop_reverse");
					
					if (data.is_play == 1) {
						if (repeat_action == "folder") {
							$(".div_control_playback").removeClass("control_playback_repeat").addClass("control_playback_repeat_folder");
						} else if (repeat_action == "file") {
							$(".div_control_playback").removeClass("control_playback_repeat_folder").addClass("control_playback_repeat");
						}
					}					
				} else {
					$("#control_button_loop").removeClass("div_control_button_loop_reverse").addClass("div_control_button_loop");
					$(".div_control_playback").removeClass("control_playback_repeat_folder").removeClass("control_playback_repeat");
				}
				
				break;
			
			case 16 :
				set_list(data); 
				break;
		}	
		return;
	}
	
	// websocket instance
	var ws_svr_ctrl_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST']?>","source_file_server_control");
	ws_svr_ctrl_handler.run();
	
	var ws_server_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "source_file_server");
	ws_server_handler.setOnmessageHandler(ws_recv_server_func);	
	ws_server_handler.setOnopenHandler(ws_open_server_func);
	ws_server_handler.run();

</script>
