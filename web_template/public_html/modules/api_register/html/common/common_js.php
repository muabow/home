<script type="text/javascript">
	$(document).ready(function() {
		var displayFunc 	= new CommonDisplayFunc();
		var systemLog		= new CommonLogFunc("api_register");
		var logMsg;
		// Log form resize
		$("#div_open_api_list_resize").resizable({
			minWidth: 938,
			maxWidth: 938,
			minHeight: 350

		}).resize(function() {
			var reSize   = (parseInt($("#div_open_api_list_resize").css("height")) - 42);
			$("#div_open_api_list_user").css("height", reSize + "px");

			var mainSize = parseInt($("#div_main_contents").css("height"));
			$("#div_main_menu").css("height", mainSize + "px");

			return ;
		});

		$("#div_open_api_add_fold").click(function() {
			$("#div_open_api_add").slideToggle('2000', "swing");
			$("#div_open_api_arrow").toggleClass("div_arrow_down");

			return ;
		});

		$("#div_open_api_user_add").click(function() {
			var serverAddr 	= $("#input_open_api_server_addr").val().trim();
			var apiKey 		= $("#input_open_api_id").val().trim();
			var apiSecret 	= $("#input_open_api_secret").val().trim();


			if( !apiKey ) {
				$("#input_open_api_id").focus();

				alert("<?=Api_register\Lang\STR_JS_EMPTY_KEY ?>");

				return ;
			}

			if( !apiSecret ) {
				$("#input_open_api_secret").focus();

				alert("<?=Api_register\Lang\STR_JS_EMPTY_SECRET ?>");

				return ;
			}

			if( !serverAddr ) {
				$("#input_open_api_server_addr").focus();

				alert("<?=Api_register\Lang\STR_JS_EMPTY_ADDR ?>");

				return ;
			}

			if( !confirm("[" + apiKey + "(" + serverAddr + ")] <?=Api_register\Lang\STR_ADD_USER_CONFIRM ?>") ) {
				return ;
			}
			
			displayFunc.showLoader();
			
			setTimeout(function() {
				var ret = apiRegisterFunc.checkIsCompatibleServer(serverAddr, apiKey, apiSecret);
				if( ret != "ok" ) {
					var msg = "";
					if( ret == "Unauthorized" ) {
						msg += "<?= Api_register\Lang\STR_JS_SERVER_INVALID_KEY_INFO ?>";
						
					} else if( ret == "Can not broadcast" ) {
						msg += "<?= Api_register\Lang\STR_JS_SERVER_CANT_CONN ?>";
						
					} else if( ret == "Unknown version" ) {
						msg += "<?= Api_register\Lang\STR_JS_SERVER_INVALID_VER ?>";
						
					} else {	// ret == "fail"
						msg += "<?= Api_register\Lang\STR_JS_SERVER_ISNOT_COMPATIBLE ?>";
					}
					
					alert(msg);
					displayFunc.clearDotLoader();
					return;
				} 
				
				if( !apiRegisterFunc.setUserList(serverAddr, apiKey, apiSecret) ) {
					alert("<?=Api_register\Lang\STR_JS_DUP_ID ?>");
					displayFunc.clearDotLoader();
					return ;
				}
				
				alert("<?=Api_register\Lang\STR_ADD_USER_REGIST ?>");
				logMsg = apiKey + " (" + serverAddr + ") <?=Api_register\Lang\STR_ADD_KEY_ADD ?>";
				systemLog.info(logMsg);
	
				$("[id^=input_open_api_").val("");
				$("#input_open_api_id").focus();
	
				$("#div_open_api_table_body").html(apiRegisterFunc.getUserList());
				
				displayFunc.clearDotLoader();
			}, 250);

			return ;
		});

		$("#div_open_api_device_remove").click(function() {
			var cnt = 0 ;
			var arrKeyList = new Array();
			var logKeyList = new Array();
			$("[id^=input_open_api_check_user]:checked").each(function() {
				var arrId  = $(this).attr("id").split("_");
				var idx    = arrId[arrId.length - 1];
				var secretKey = $("#span_open_api_table_secretKey_" + idx).html();
				var key = $("#span_open_api_table_key_"+ idx).html();
				var ip = $("#span_open_api_table_ip_"+ idx).html();

				if( secretKey == "" ) return ;

				arrKeyList[cnt] = secretKey;
				logKeyList[cnt] = key + "(" + ip + ") ";
				cnt++;

			});

			for( idx = 0 ; idx < cnt ; idx++ ) {
				if( !(rc = apiRegisterFunc.removeDeviceList(arrKeyList[idx])) ) {
					 //alert("<?=Api_register\Lang\STR_REMOVE_USER_NONE ?>");

				}
				if(idx == 0) {
				 logMsg = logKeyList[idx];
				}
				else {
					logMsg += ", "+logKeyList[idx];
				}

			}
			logMsg +=" <?=Api_register\Lang\STR_ADD_KEY_DELETE ?>";
			systemLog.info(logMsg);

			$("#div_open_api_table_body").html(apiRegisterFunc.getUserList());


			return ;
		});

		$("#input_open_api_user_name").keyup(function(_ex) {
			if( _ex.keyCode == 13 ) { // enter key
				$("#input_open_api_user_contact").focus();
			}

			return ;
		});

		$("#input_open_api_user_contact").keyup(function(_ex) {
			if( _ex.keyCode == 13 ) { // enter key
				$("#input_open_api_user_company").focus();
			}

			return ;
		});

		$("#input_open_api_user_company").keyup(function(_ex) {
			if( _ex.keyCode == 13 ) { // enter key
				$("#div_open_api_user_add").trigger("click");
			}

			return ;
		});

		$(".input_open_api_device_add").on("paste", function(_e) {
			var result;

			var pastedData = _e.originalEvent.clipboardData.getData('text');
		    pastedData = pastedData.split(":");

		    if( pastedData[0] != "interm_api_key" ) {

		    	result = true;

		    } else {
		    	$("#input_open_api_id").val(pastedData[1]);
		    	$("#input_open_api_secret").val(pastedData[2]);
		    	$("#input_open_api_server_addr").val(pastedData[3]);

		    	result = false;
		    }

		    return result;
		});
	});

	class ApiRegisterFunc {
		constructor() {
			this.apiPath = "<?=Api_register\Def\PATH_AJAX_OPEN_API_PROCESS ?>";
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

		setUserList(_serverAddr, _apiKey, _apiSecret) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("type",     "open_api");
			submitArgs	+= this.makeArgs("act",      "set_user");
			submitArgs	+= this.makeArgs("serverAddr",   _serverAddr);
			submitArgs	+= this.makeArgs("apiKey",       _apiKey);
			submitArgs	+= this.makeArgs("apiSecret",    _apiSecret);

			return this.postArgs(this.apiPath, submitArgs);
		}

		getUserList() {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("type",     "open_api");
			submitArgs	+= this.makeArgs("act",      "get_user");

			return this.postArgs(this.apiPath, submitArgs);
		}

		removeDeviceList(_secretKey) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("type",     "open_api");
			submitArgs	+= this.makeArgs("act",      "remove_user");
			submitArgs	+= this.makeArgs("secretKey", _secretKey);

			return this.postArgs(this.apiPath, submitArgs);
		}
		
		checkIsCompatibleServer(_serverAddr, _apiKey, _apiSecret) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("type",     	"open_api");
			submitArgs	+= this.makeArgs("act",      	"check_server");
			submitArgs	+= this.makeArgs("serverAddr", 	_serverAddr);
			submitArgs	+= this.makeArgs("apiKey",		_apiKey);
			submitArgs	+= this.makeArgs("apiSecret",   _apiSecret);
			
			return this.postArgs(this.apiPath, submitArgs);
		}

	} // end of ApiRegisterFunc()

	var apiRegisterFunc = new ApiRegisterFunc();



</script>

<?php
	include_once 'common_js_etc.php';
?>
