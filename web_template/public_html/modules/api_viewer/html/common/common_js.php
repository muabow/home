<script type="text/javascript">
	$(document).ready(function() {
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
			var userMail    = $("#input_open_api_user_mail").val().trim();
			var userContact = $("#input_open_api_user_contact").val().trim();
			var userCompany = $("#input_open_api_user_company").val().trim();

			if( apiviewerFunc.checkUserEmail(userMail) == false ) {
				$("#input_open_api_user_name").focus();

				alert("<?=Api_viewer\Lang\STR_JS_INVALID_EMAIL ?>");

				return ;
			}

			if( !userContact ) {
				$("#input_open_api_user_contact").focus();

				alert("<?=Api_viewer\Lang\STR_JS_EMPTY_CONTACT ?>");

				return ;
			}
			if( !userCompany ) {
				$("#input_open_api_user_company").focus();

				alert("<?=Api_viewer\Lang\STR_JS_EMPTY_COMPANY ?>");

				return ;
			}

			if( !confirm("[" + userMail + "(" + userCompany + ")] <?=Api_viewer\Lang\STR_ADD_USER_CONFIRM ?>") ) {
				return ;
			}

			if( !apiviewerFunc.setUserList(userMail, userContact, userCompany) ) {
				alert("<?=Api_viewer\Lang\STR_JS_DUP_ID ?>");

				return ;
			}
			alert("<?=Api_viewer\Lang\STR_ADD_USER_REGIST ?>");

			$("[id^=input_open_api_device_").val("");
			$("#input_open_api_user_name").focus();

			$("#div_open_api_table_body").html(apiviewerFunc.getUserList());


			return ;
		});

		$("#div_open_api_device_remove").click(function() {
			var rc;
			var check = 0;
			$("[id^=input_open_api_check_user]:checked").each(function(i) {
				check++;
				var arrId  = $(this).attr("id").split("_");
				var idx    = arrId[arrId.length-1];
				var secretKey = $("#span_open_api_table_secretKey_" + idx).html();

				if( secretKey == "" ) return ;
				secretKey.trim();
				
				rc = apiviewerFunc.removeDeviceList(secretKey);
			});
			
			if (check == 0) {
				alert("<?=Api_viewer\Lang\STR_REMOVE_USER_NONE ?>");
				return;
			}
			
			$("#div_open_api_table_body").html(rc);
			return;
		});

		$("#input_open_api_user_name").keyup(function(_ex) {
			if( _ex.keyCode == 13 ) { // enter key
				if( apiviewerFunc.checkUserEmail($(this).val()) == false ) {
					$("#input_open_api_user_name").focus();
					alert("<?=Api_viewer\Lang\STR_JS_INVALID_EMAIL ?>");

					return ;
				}

				$("#input_open_api_user_contact").focus();

				return ;
			}
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
		
//[20190528:다운] start
		$(document).on('click','.copy_button', function(){
		//$(".copy_button").click(function() {
//[20190528:다운] end
			var idKey 	  = $(this).closest(".divTableRow").find(".divTableCell").eq(2).html().trim();
			var secretKey = $(this).closest(".divTableRow").find(".divTableCell").eq(3).find("span").html().trim();
			
			$("body").append('<input type="text" id="clip_board">');
			
			$("#clip_board").val("interm_api_key" + ":" + idKey + ":" + secretKey + ":" + window.location.hostname);
	        $("#clip_board").select();
	        document.execCommand('Copy');
	        alert('<?=Api_viewer\Lang\STR_JS_COPY ?>');
	        
	        $("#clip_board").remove();
		});

	});

	class ApiViewerFunc {
		constructor() {
			this.apiPath = "<?=Api_viewer\Def\PATH_AJAX_OPEN_API_PROCESS ?>";
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

		setUserList(_userMail, _userContact, _userCompany) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("type",     "open_api");
			submitArgs	+= this.makeArgs("act",      "set_user");
			submitArgs	+= this.makeArgs("userMail",   _userMail);
			submitArgs	+= this.makeArgs("userContact", _userContact);
			submitArgs	+= this.makeArgs("userCompany", _userCompany);

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

		checkUserEmail(_email) {
			var regex=/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/;

			if(regex.test(_email) === false) {
			    return false;
			} else {
			    return true;
			}
		}

	} // end of apiviewerFunc()

	var apiviewerFunc = new ApiViewerFunc();



</script>

<?php
	include_once 'common_js_etc.php';
?>