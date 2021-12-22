<?php
	const PATH_CHIME_INFO = "/opt/interm/public_html/modules/chime_file_management/conf/chime_info.json";
	$json_info = json_decode(file_get_contents(PATH_CHIME_INFO));

	// ajax post process
	if( isset($_POST["type"]) && $_POST["type"] == "change_set" ) {
		$index 		= $_POST["index"];
		$chime_set	= $_POST["chime_set"];
		
		$json_info->chime_set[$index - 1] = $chime_set;

		file_put_contents(PATH_CHIME_INFO, json_encode($json_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

		return ;
	}

	if( isset($_POST["type"]) && $_POST["type"] == "mix_set" ) {
		$json_info->mix_set = (int)$_POST["checked"];

		file_put_contents(PATH_CHIME_INFO, json_encode($json_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

		return ;
	}

	
	if( isset($_POST["type"]) && $_POST["type"] == "chime_volume" ) {
		$json_info->chime_volume = (int)$_POST["volume"];

		file_put_contents(PATH_CHIME_INFO, json_encode($json_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

		return ;
	}

	if( isset($_POST["type"]) && $_POST["type"] == "reload_list" ) {
		$arr_option_list[0] = "";
		$arr_option_list[1] = "";

		foreach( $json_info->chime_list as $key ) {
			for( $idx = 0 ; $idx < count($json_info->chime_set) ; $idx++ ) {
				$d_time = sprintf("%02d:%02d", $key->duration / 60, $key->duration % 60);
				$file_name = substr($key->name, 0, strrpos($key->name, ".")); 
				
				if( $json_info->chime_set[$idx] == $file_name ) {
					$arr_option_list[$idx] .= "<option value=\"{$file_name}\" selected> [{$d_time}] {$file_name} &nbsp; &nbsp; </option>";
				
				} else {
					$arr_option_list[$idx] .= "<option value=\"{$file_name}\"> [{$d_time}] {$file_name} &nbsp; &nbsp; </option>";
				}
			}
		}

		echo json_encode($arr_option_list);

		return ;
	}


	// onload process
	$arr_option_list[0] = "";
	$arr_option_list[1] = "";
	foreach( $json_info->chime_list as $key ) {
		for( $idx = 0 ; $idx < count($json_info->chime_set) ; $idx++ ) {
			$d_time = sprintf("%02d:%02d", $key->duration / 60, $key->duration % 60);
			$file_name = substr($key->name, 0, strrpos($key->name, ".")); 
			
			if( $json_info->chime_set[$idx] == $file_name ) {
				$arr_option_list[$idx] .= "<option value=\"{$file_name}\" selected> [{$d_time}] {$file_name} &nbsp; &nbsp; </option>";
			
			} else {
				$arr_option_list[$idx] .= "<option value=\"{$file_name}\"> [{$d_time}] {$file_name} &nbsp; &nbsp; </option>";
			}
		}
	}

	$str_mix_option = "";
	if( $json_info->mix_set ) {
		$str_mix_option = "checked";
	}

	$num_chime_volume = $json_info->chime_volume;
	$str_chime_img = "icon_speaker";
	if( $num_chime_volume == 0 ) 									$str_chime_img = "icon_speaker_mute";
	else if( $num_chime_volume > 0  && $num_chime_volume <= 25 ) 	$str_chime_img = "icon_speaker_low";
	else if( $num_chime_volume > 25 && $num_chime_volume <= 50 ) 	$str_chime_img = "icon_speaker_middle";
	else if( $num_chime_volume > 50 && $num_chime_volume <= 75 ) 	$str_chime_img = "icon_speaker_high";
	else if( $num_chime_volume > 75 )							 	$str_chime_img = "icon_speaker";
?>

<style>
	body {
		padding 				: 0px;
		margin 					: 0px;
	}

	.chime_control {
		width 					: 385px;
		height 					: 80px;
		display 				: flex;
		margin-bottom 			: 10px;
		background 				: #dee0e0;
		-webkit-box-shadow 		: 1px 1px 3px 1px rgba(0,0,0,0.5);
		-moz-box-shadow 		: 1px 1px 3px 1px rgba(0,0,0,0.5);
		box-shadow 				: 1px 1px 3px 1px rgba(0,0,0,0.5);
	}

	.chime_control_title {
		width 					: 75px;
		margin 					: 19px 0 0 5px;
		float 					: left;
		text-align 				: center;
		font-weight 			: bold;
	}

	.chime_control_right {
		flex 					: 1 1 0;
		margin 					: 5px 5px 5px 0;
		border-left				: 1px solid black;
		float 					: left;
		color 					: black;
		font-weight 			: bold;
		font-size 				: 9pt;
	}

	.chime_control_box {
		margin-top 				: 2px;
		margin-left				: 5px;
	}

	.chime_div_button {
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

	.chime_div_button:hover {
		color 					: white;
	}

	.chime_div_button:active {
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

	.chime_select {
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

	.chime_no_drag {
		-ms-user-select 		: none; 
		-moz-user-select 		: -moz-none; 
		-webkit-user-select 	: none; 
		-khtml-user-select 		: none; 
		user-select 			: none;
	}

	.chime_control_left {
		display 				: flex;
		justify-content 		: center;
		align-items 			: center;
		flex-direction 			: column;
	}

	.chime_mix_check {
		font-size 				: 9pt;
		display 				: flex;
		align-items 			: center;
		justify-content 		: center;
	}
	
	.chime_mix_check div {
		display 				: flex;
		align-items 			: center;
		justify-content 		: center;
		font-weight 			: bold;
	}

	.chime_mix_check input {
		margin 					: 0 3px 0 0;
	}

	.chime_slider_container {
		width 					: 100%;
	}

	.chime_slider_speaker {
		margin-left 			: 5px;
		height 					: 16px;
		width 					: 18px;
		background 				: url("../../img/<?=$str_chime_img ?>.svg");
		background-repeat 		: no-repeat;
		background-size 		: 100%;
		background-position 	: center center;
		float 					: left;
	}

	.chime_slider_volume {
		margin-top 				: 4px;
		background 				: linear-gradient(to right, #82CFD0 0%, #82CFD0 <?=$num_chime_volume ?>%, #fff <?=$num_chime_volume ?>%, #fff 100%);
		border 					: solid 1px #82CFD0;
		border-radius 			: 10px;
		height 					: 7px;
		width 					: 232px;
		outline 				: none;
		transition 				: background 450ms ease-in;
		-webkit-appearance		: none;
		float 					: left;
	}

	.chime_slider_text {
		height 					: 16px;
		width 					: 29px;
		float 					: left;
		font-size 				: 12px;
		text-align 				: center;
	}
</style>

<script type="text/javascript" src="/js/jquery-3.1.1.js"></script>
<script type="text/javascript" src="/js/jquery-ui.js"></script>

<div class="chime_control">
	<div class="chime_control_left">
		<div class="chime_control_title">CHIME</div>
		<div class="chime_mix_check">
			<input type="checkbox" id="checkbox_chime_mix" <?php echo $str_mix_option; ?>>
			<div>mix</div>
		</div>
	</div>
	<div class="chime_control_right">
		<div class="chime_control_box">
			<select class="chime_select" id="select_chime_1">
				<option value="" selected disabled hidden> -- select -- </option>
				<?php echo $arr_option_list[0]; ?>
			</select>
			|
			<div id="chime_button_chime_1" class="chime_div_button chime_no_drag"> CHIME #1 </div>
		</div>
		
		<div class="chime_control_box">
			<select class="chime_select" id="select_chime_2">
				<option value="" selected disabled hidden>-- select --</option>
				<?php echo $arr_option_list[1]; ?>
			</select>
			|
			<div id="chime_button_chime_2" class="chime_div_button chime_no_drag"> CHIME #2 </div>
		</div>

		<div class="chime_control_box">
			<div class="chime_slider_speaker"></div>
			<div class="chime_slider_container">
				<input type="range" min="0" max="100" value="<?=$num_chime_volume ?>" class="chime_slider_volume" id="chime_volume" />
			</div>
			<input type="text" class="chime_slider_text" id="chime_text_slider_value" value="<?=$num_chime_volume ?>" maxlength="3" />
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

	class ChimeFunc {
		constructor() {
			this.path = "./chime_ctrl.php";
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
	} // end of class : ChimeFunc()

	function change_speaker_image(_volume) {
		var chime_volume = _volume;

		var str_chime_img = "icon_speaker";
		if( chime_volume == 0 ) 							str_chime_img = "icon_speaker_mute";
		else if( chime_volume > 0  && chime_volume <= 25 ) 	str_chime_img = "icon_speaker_low";
		else if( chime_volume > 25 && chime_volume <= 50 ) 	str_chime_img = "icon_speaker_middle";
		else if( chime_volume > 50 && chime_volume <= 75 ) 	str_chime_img = "icon_speaker_high";
		else if( chime_volume > 75 )					 	str_chime_img = "icon_speaker";

		$(".chime_slider_speaker").css("background-image", "url(../../img/" + str_chime_img + ".svg)");
		
		return ;
	}

	function ws_recv_func(_cmd_id, _is_binary, _length, _data, _self) {
		var data = JSON.parse(_data).data;
		
		switch( _cmd_id ) {
			case 0x00 :	// alive_info
				var alive_info  = parseInt(data.alive_info);
				var chime_index = parseInt(data.chime_index);
				
				if( alive_info == 1 ) {
					$("#select_chime_" + chime_index).css("backgroundColor", "linear-gradient(#5da8dd, #1869a3)");
					$("[id^=select_chime_]").attr("disabled", "disabled");
				
				} else {
					$("#select_chime_" + chime_index).css("backgroundColor", "#FFFFFF");
					$("[id^=select_chime_]").removeAttr("disabled");
				}
				break;

			case 0x02 :	// mix_set
				var mix_set = parseInt(data.mix_set);
				$("#checkbox_chime_mix").prop("checked", (mix_set == 1 ? true : false));
				break;

			case 0x03 :	// chime_volume
				var chime_volume = parseInt(data.chime_volume);
				$("#chime_volume").val(chime_volume);
				$("#chime_volume").css("background", 'linear-gradient(to right, #82CFD0 0%, #82CFD0 ' + chime_volume + '%, #fff ' + chime_volume + '%, white 100%)');
				
				change_speaker_image(chime_volume);

				$("#chime_text_slider_value").val(chime_volume);

				break;

			case 0x04 :	// update option list
				var chime_handler = new ChimeFunc();
				var args = chime_handler.makeArgs("type", "reload_list");

				var result = chime_handler.postArgs(args);
				var arr_option_list = JSON.parse(result);
				$("#select_chime_1").empty().append("<option value=\"\" selected disabled hidden> -- select -- </option>");
				$("#select_chime_2").empty().append("<option value=\"\" selected disabled hidden> -- select -- </option>");

				$("#select_chime_1").append(arr_option_list[0]);
				$("#select_chime_2").append(arr_option_list[1]);
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

	$("[id^=select_chime_]").change(function() {
		var index     = $(this).attr("id").split("_")[2];
		var chime_set = $(this).val();

		var chime_handler = new ChimeFunc();
		var args = "";
		args += chime_handler.makeArgs("type", 		"change_set");
		args += chime_handler.makeArgs("index", 	index);
		args += chime_handler.makeArgs("chime_set", chime_set);

		chime_handler.postArgs(args);

		// update option list
		ws_handler.send(0x04, null);

		return ;
	});

	$("[id^=chime_button_chime_]").click(function() {
		var index     = $(this).attr("id").split("_")[3];
		var chime_set = $("#select_chime_" + index + " option:selected").val();
		var chime_idx = $("#select_chime_" + index + " option:selected").index();
		
		if( chime_set == "" ) {
			return ;
		}
		
		var json_info = "";
		json_info += '{';
		json_info += '"chime_set" : ' + chime_idx + ', ';
		json_info += '"chime_idx" : ' + index;
		json_info += '}';

		ws_handler.send(0x01, json_info);

		return ;
	});

	$("#checkbox_chime_mix").change(function() {
		var is_checked = ($("#checkbox_chime_mix").is(":checked") == true ? 1 : 0);

		var chime_handler = new ChimeFunc();
		var args = "";
		args += chime_handler.makeArgs("type", 		"mix_set");
		args += chime_handler.makeArgs("checked",	is_checked);

		chime_handler.postArgs(args);
		
		var json_info = "";
		json_info += '{';
		json_info += '"mix_set" : ' + is_checked;
		json_info += '}';

		ws_handler.send(0x02, json_info);

		return ;
	});


	document.getElementById("chime_volume").oninput = function() {
		this.style.background = 'linear-gradient(to right, #82CFD0 0%, #82CFD0 ' + this.value + '%, #fff ' + this.value + '%, white 100%)';
		
		$("#chime_text_slider_value").val(this.value);
		change_speaker_image(this.value);
		
		var json_info = "";
		json_info += '{';
		json_info += '"chime_volume" : ' + this.value;
		json_info += '}';

		ws_handler.send(0x03, json_info);

		return ;
	};
	
	$("#chime_volume").change('mousestop', function () {
		var chime_handler = new ChimeFunc();
		var args = "";
		args += chime_handler.makeArgs("type", 		"chime_volume");
		args += chime_handler.makeArgs("volume",	$(this).val());

		chime_handler.postArgs(args);
		
		return ;
	});

	$("#chime_text_slider_value").keyup(function(_evt) {
		if( _evt.keyCode == 13 ) {	// key: enter
			var text_value = $("#chime_text_slider_value").val();

			if( $.isNumeric(text_value) && (text_value >= 0 && text_value <= 100 ) ) {
				var json_info = "";
				json_info += '{';
				json_info += '"chime_volume" : ' + this.value;
				json_info += '}';

				ws_handler.send(0x03, json_info);
			
			} else {
				var slider_value = $("#chime_volume").val();
				$("#chime_text_slider_value").val(slider_value);
				
			}
		}
		
		return ;
	});

	$("#chime_text_slider_value").focusout(function(_evt) {
		var text_value = $("#chime_text_slider_value").val();

		if( $.isNumeric(text_value) && (text_value >= 0 && text_value <= 100 ) ) {
			var json_info = "";
			json_info += '{';
			json_info += '"chime_volume" : ' + this.value;
			json_info += '}';

			ws_handler.send(0x03, json_info);
		
		} else {
			var slider_value = $("#chime_volume").val();
			$("#chime_text_slider_value").val(slider_value);
			
		}

		return ;
	});

	change_speaker_image(<?=$num_chime_volume ?>);

	var ws_handler = new WebsocketHandler("<?=$_SERVER['HTTP_HOST'] ?>", "chime_ctrl");
	ws_handler.setOnmessageHandler(ws_recv_func);
	ws_handler.setOnopenHandler(ws_open_func);
	ws_handler.run();
</script>
