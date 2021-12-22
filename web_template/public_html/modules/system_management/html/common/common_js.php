<script type="text/javascript" src="/js/crypt/jsbn.js" ></script>
<script type="text/javascript" src="/js/crypt/jsbn2.js" ></script>
<script type="text/javascript" src="/js/crypt/prng4.js" ></script>
<script type="text/javascript" src="/js/crypt/rng.js" ></script>
<script type="text/javascript" src="/js/crypt/sha1.js" ></script>
<script type="text/javascript" src="/js/crypt/aes-enc.js" ></script>

<script type="text/javascript">
	$(document).ready(function() {
		var systemFunc  = new SystemFunc();
		var displayFunc = new CommonDisplayFunc();
		var systemLog   = new CommonLogFunc("system_management");
		var logMsg;

		// 0. textbox script
		var placeholderTarget = $('.textbox input[type="text"], .textbox input[type="password"]');

		placeholderTarget.on('focus', function() {
			$(this).siblings('label').fadeOut('fast');
		});

		placeholderTarget.on('focusout', function() {
			if( $(this).val() == '' ) {
				$(this).siblings('label').fadeIn('fast');
			}
		});

		placeholderTarget.on('textchange', function() {
			$(this).siblings('label').fadeOut('fast');
		});

		$(".div_systemCheckOperation").click(function() {
			$(".div_systemCheckToggle").toggleClass("div_switchBackOnOff", 300, "easeOutSine");
			$(this).toggleClass("div_switchBackground", 300, "easeOutSine");
		});

		var fileTarget = $('.filebox_upgrade .upload-hidden');
		fileTarget.on('change', function(){ // 값이 변경되면
			if( window.FileReader ) { // modern browser
				if( $(this).val() == "" ) var filename = "<?=System_management\Lang\STR_SYSTEM_UPGRADE_FIND ?>";
				else var filename = $(this)[0].files[0].name;

			} else { // old IE
				var filename = $(this).val().split('/').pop().split('\\').pop(); // 파일명만 추출
			} // 추출한 파일명 삽입

			$(this).siblings('.upload-name').val(filename);
		});

		// 1.1. 시스템 업그레이드 - apply
		$("#div_buttonApplyFileUpload").click(function() {
			var fileName = $("#file_uploadFile").val();
			var fileData = $("#file_uploadFile")[0].files[0];

			// 파일 첨부 검출
			if( !fileName ) {
				alert("<?=System_management\Lang\STR_SYSTEM_UPGRADE_SELECT ?>");

				return ;
			}

			// 파일 확장자 검출
			var arrFileName = fileName.split('.');
			var fileType = arrFileName[arrFileName.length - 1];

			if( !(fileType.toLowerCase() == "imkp") ) {
				alert("<?=System_management\Lang\STR_SYSTEM_UPGRADE_FILE_ALERT ?>");

				return ;
			}

			// 파일 사이즈 검출
			var maxFileSize = "<?php echo ini_get('upload_max_filesize'); ?>";
			var limitSize   = parseInt(maxFileSize) * 1024 * 1024;
			var fileSize    = fileData.size;

			if( fileSize >= limitSize ) {
				alert("<?=System_management\Lang\STR_SYSTEM_UPGRADE_LIMIT ?> (" + maxFileSize + ")");

				return ;
			}

			if( !confirm("<?=System_management\Lang\STR_SYSTEM_UPGRADE_CONFIRM ?>") ) {
				return ;
			}

			displayFunc.showLoader();

			// 파일 업로드
			var formData   = new FormData();
			var result;


			formData.append('file', fileData);

			var request = new XMLHttpRequest();
			request.onreadystatechange = function(){
				if( request.readyState == 4 ) {
					$(".container").hide();

					try {
						result = JSON.parse(request.response);

						if( result.code == 0 ) {
							displayFunc.hideDotLoader();

							var submitParams = systemFunc.makeArgs("filePath", result.msg)
							var filePath = systemFunc.postArgs("<?=System_management\Def\PATH_UPLOAD_PROCESS ?>", submitParams);

							submitParams += systemFunc.makeArgs("type",		 "system");
							submitParams += systemFunc.makeArgs("act", 		 "upgrade");
							submitParams += systemFunc.makeArgs("filePath",   filePath);

							commonFunc.postArgsAsync("<?=System_management\Def\PATH_SYSTEM_PROCESS ?>", submitParams, submitedUpgrade);


						} else {
							displayFunc.hideLoader(-1);

							setTimeout(function() {
								switch( result.code ) {
									case -1 :
										alert("<?=System_management\Lang\STR_SYSTEM_UPGRADE_NOT_FOUND_FILE ?>");
									break;

									default :
										alert("<?=System_management\Lang\STR_SYSTEM_UPGRADE_UPLOAD_FAIL ?>");
									break;
								}
							}, 800);
						}

					} catch (e){
						var resp = {
							status: 'error',
							data: 'Unknown error occurred: [' + request.responseText + ']'
						};
					}
				}
			};

			request.upload.addEventListener('progress', function(e) {
 				var progressBar = document.getElementById('div_fileUpload_progress');

				var percentage = parseInt(e.loaded/e.total * 100) + '%';
				progressBar.style.width = percentage;

				$(".container").show();
				$("#div_fileUpload_progress").html("&nbsp;&nbsp;" + percentage);

			}, false);

			request.open('POST', "<?=System_management\Def\PATH_UPLOAD_PROCESS ?>");
			request.send(formData);

			return ;
		});

		// 1.2. 시스템 업그레이드 - cancel
		$("#div_buttonCancelFileUpload").click(function() {
			$("#file_uploadFile").val("");
			$("#label_uploadFile").val("<?=System_management\Lang\STR_SYSTEM_UPGRADE_FIND ?>");

			return ;
		});


		// 2.1. 패스워드 변경 - apply
		$("#div_buttonApplyPassword").click(function() {


			if( ($("#password_newPassword").val() == "" || $("#password_checkPassword").val() == "")
				|| ($("#password_newPassword").val() != $("#password_checkPassword").val()) ) {
				alert("<?=System_management\Lang\STR_SYSTEM_PASSWD_CHECK_NEW ?>");

				if( $("#password_newPassword").val() ) {
					$("#password_checkPassword").focus();
				} else {
					$("#password_newPassword").focus();
				}

				return ;
			}

			var rsaCrypt    = new CryptFunc();
			var username    = $("#select_accountList :selected").html().trim();
			var encPassword = rsaCrypt.encrypt(username, $("#password_currentPassword").val());

			if( $("#password_currentPassword").val() == "" || !commonFunc.checkLogin(username, encPassword) ) {
				alert("<?=System_management\Lang\STR_SYSTEM_PASSWD_CHECK_CURRENT ?>");
				$("#password_currentPassword").focus();

				return ;
			}

			if( $("#password_currentPassword").val() == $("#password_newPassword").val() ) {
				alert("<?=System_management\Lang\STR_SYSTEM_PASSWD_CHECK_SAME ?>");
				$("#password_currentPassword").focus();

				return ;
			}

			// 8~16자리, 영문, 숫자, 특수문자 조합
			var regex = /^(?=.*[a-zA-Z])(?=.*[!@#$%^*+=-])(?=.*[0-9]).{8,16}/;
			if( !regex.test($("#password_newPassword").val()) ) {
				alert("<?=System_management\Lang\STR_SYSTEM_PASSWD_MIN_LENGTH ?>");

				$("#password_checkPassword").val("");
				$("#password_checkPassword").focus();

				$("#password_newPassword").val("");
				$("#password_newPassword").focus();

				return ;
			}

			if( !confirm("<?=System_management\Lang\STR_SYSTEM_PASSWD_NEW_MSG ?>") ) {

				return ;
			}

			var encNewPassword = rsaCrypt.encrypt(username, $("#password_newPassword").val());

			if( !commonFunc.changePassword(username, encNewPassword) ) {
				alert("<?=System_management\Lang\STR_SYSTEM_PASSWD_CHECK_WRONG ?>");
				$("#password_newPassword").focus();

				return ;

			} else {
				alert("<?=System_management\Lang\STR_SYSTEM_PASSWD_CHECK_COMPLETE ?>");
				$("#div_buttonCancelPassword").trigger("click");
				logMsg = username +" <?=System_management\Lang\STR_SYSTEM_PASSWD_CHECK_COMPLETE ?>";
			}

			systemLog.info(logMsg);

			return ;
		});

		// 2.2. 패스워드 변경 - cancel
		$("#div_buttonCancelPassword").click(function() {
			$("#password_currentPassword").val("").trigger("focusout");
			$("#password_newPassword").val("").trigger("focusout");
			$("#password_checkPassword").val("").trigger("focusout");

			return ;
		});

		// 2.3. 패스워드 변경 - keyup
		$("#password_currentPassword").keyup(function(_ex) {
			if( _ex.keyCode == 13 ) { // enter key
				$("#password_newPassword").focus();
			}

			return ;
		});

		$("#password_newPassword").keyup(function(_ex) {
			if( _ex.keyCode == 13 ) { // enter key
				$("#password_checkPassword").focus();
			}

			return ;
		});


		// 3.1. 시스템 점검 설정 - apply
		$("#div_buttonApplySystemCheck").click(function() {
			var submitParams = "";
			var statMode = "off";

			if( $(".div_systemCheckOperation").hasClass("div_switchBackground") == true ) {
				statMode = "on";
			}

			submitParams += systemFunc.makeArgs("type", "system");
			submitParams += systemFunc.makeArgs("act",  "set_time");
			submitParams += systemFunc.makeArgs("hour",   $("#select_systemCheckHour").val());
			submitParams += systemFunc.makeArgs("minute", $("#select_systemCheckMinute").val());
			submitParams += systemFunc.makeArgs("stat",   statMode);

			systemFunc.postArgs("<?=System_management\Def\PATH_SYSTEM_PROCESS ?>", submitParams);

			alert("<?=System_management\Lang\STR_SYSTEM_CHECK_CHECK_COMPLETE ?>");

			if(statMode == "on") {
				logMsg = "<?=System_management\Lang\STR_SYSTEM_CHECK_TIME_ ?> " + $("#select_systemCheckHour").val() + "<?=System_management\Lang\STR_SYSTEM_CHECK_HOUR ?> "
				+ $("#select_systemCheckMinute").val()+ "<?=System_management\Lang\STR_SYSTEM_CHECK_MINUTE ?>"+" <?=System_management\Lang\STR_SYSTEM_CHECK_COMPLETE ?>";

			} else {
				logMsg = "<?=System_management\Lang\STR_SYSTEM_CHECK_DISABLED ?>";

			}

			systemLog.info(logMsg);
			return ;
		});

		// 3.2. 시스템 점검 설정 - cancel
		$("#div_buttonCancelSystemCheck").click(function() {
			var submitParams = "";
			submitParams += systemFunc.makeArgs("type", "system");
			submitParams += systemFunc.makeArgs("act",  "get_time");

			var rc = systemFunc.postArgs("<?=System_management\Def\PATH_SYSTEM_PROCESS ?>", submitParams);
			var statTime = JSON.parse(rc);

			$("#select_systemCheckHour").val(statTime.hour);
			$("#select_systemCheckMinute").val(statTime.minute);

			if( statTime.stat == "on" ) {
				$(".div_systemCheckOperation").addClass("div_switchBackground", 300, "easeOutSine");
				$(".div_systemCheckToggle").addClass("div_switchBackOnOff", 300, "easeOutSine");

			} else {
				$(".div_systemCheckOperation").removeClass("div_switchBackground", 300, "easeOutSine");
				$(".div_systemCheckToggle").removeClass("div_switchBackOnOff", 300, "easeOutSine");
			}

			return ;
		});

		// 4.1. 시스템 재시작 - apply
		$("#div_buttonApplyReboot").click(function() {
			logMsg = "<?=System_management\Lang\STR_SYSTEM_REBOOT_MSG ?>";
			systemLog.info(logMsg);

			if( !confirm("<?=System_management\Lang\STR_SYSTEM_REBOOT_MSG ?>") ) {
				return ;
			}
			displayFunc.showLoader();
			displayFunc.hideLoader(30);

			var submitParams = "";
			submitParams += systemFunc.makeArgs("type", "system");
			submitParams += systemFunc.makeArgs("act",  "reboot");

			systemFunc.postArgs("<?=System_management\Def\PATH_SYSTEM_PROCESS ?>", submitParams);

			return ;
		});


		// 4.2. 시스템 초기화 - apply
		$("#div_buttonApplyFactory").click(function() {

			if( !confirm("<?=System_management\Lang\STR_SYSTEM_FACTORY_MSG ?>") ) {
				return ;
			}
			displayFunc.showLoader();

			displayFunc.hideDotLoader();

			var submitParams = "";
			submitParams += systemFunc.makeArgs("type", "system");
			submitParams += systemFunc.makeArgs("act",  "factory");

			commonFunc.postArgsAsync("<?=System_management\Def\PATH_SYSTEM_PROCESS ?>", submitParams, submitedFactory);

			return ;
		});

		// 5.1 계정 잠금 상태
		$("[id^=div_button_lock_reset_]").click(function() {
			var arr_id = $(this).attr("id").split("_");
			var username = arr_id[arr_id.length - 1];

			var submitParams = "";
			submitParams += systemFunc.makeArgs("type", 	 "system");
			submitParams += systemFunc.makeArgs("act", 		 "reset_count");
			submitParams += systemFunc.makeArgs("username",  username);

			var rc = commonFunc.postArgs("<?=System_management\Def\PATH_SYSTEM_PROCESS ?>", submitParams);

			$("#span_count_" + username).html(rc);
			$("#div_img_count_" + username).removeClass("lock_img");
			$("#div_img_count_" + username).addClass("unlock_img");

			var rsaCrypt    = new CryptFunc();
			var encNewPassword = rsaCrypt.encrypt(username, "1");
			commonFunc.changePassword(username, encNewPassword);

			return ;
		});

		$("[id^=div_img_count_m_]").click(function() {
			var arr_id = $(this).attr("id").split("_");
			var username = arr_id[arr_id.length - 1];
			if( !$("#div_img_count_m_" + username).hasClass("lock_img") ) {
				return ;
			}

			var submitParams = "";
			submitParams += systemFunc.makeArgs("type", 	 "system");
			submitParams += systemFunc.makeArgs("act", 		 "reset_count");
			submitParams += systemFunc.makeArgs("username",  username);

			var rc = commonFunc.postArgs("<?=System_management\Def\PATH_SYSTEM_PROCESS ?>", submitParams);

			$("#div_img_count_m_" + username).removeClass("lock_img");
			$("#div_img_count_m_" + username).addClass("unlock_img");

			var rsaCrypt    = new CryptFunc();
			var encNewPassword = rsaCrypt.encrypt(username, "1");
			commonFunc.changePassword(username, encNewPassword);

			return ;
		});

		function submitedUpgrade(_req) {
			if( _req.readyState == <?=Common\Def\READY_STAT_SUCCESS ?> ) {
				if( _req.status == <?=Common\Def\STATUS_SUCCESS ?> ) {
					var responseText = _req.responseText;

					displayFunc.clearDotLoader();

					if( responseText == 0 ) {
						alert("<?=System_management\Lang\STR_SYSTEM_REBOOT_MSG ?>");

						displayFunc.showLoader();
						displayFunc.hideLoader(30);

						var submitParams = "";
						submitParams += systemFunc.makeArgs("type", "system");
						submitParams += systemFunc.makeArgs("act",  "reboot");

						systemFunc.postArgs("<?=System_management\Def\PATH_SYSTEM_PROCESS ?>", submitParams);
						logMsg = "<?=System_management\Lang\STR_SYSTEM_UPGRADE_SUCCESS ?>";
						systemLog.info(logMsg);

					} else {
						alert("<?=System_management\Lang\STR_SYSTEM_UPGRADE_FAILED ?>");
						console.log("Upgrade error code : " + responseText);
						logMsg = "<?=System_management\Lang\STR_SYSTEM_UPGRADE_FAILED ?>";
						systemLog.info(logMsg);
					}

				} else {
					// this.notiServerState(_req.status);
				}
			}
		}

		function submitedFactory(_req) {
			if( _req.readyState == <?=Common\Def\READY_STAT_SUCCESS ?> ) {
				if( _req.status == <?=Common\Def\STATUS_SUCCESS ?> ) {
					var responseText = _req.responseText;

					displayFunc.clearDotLoader();

					if( responseText == 0 ) {
						alert("<?=System_management\Lang\STR_SYSTEM_REBOOT_MSG ?>");

						displayFunc.showLoader();
						displayFunc.hideLoader(30);

						var submitParams = "";
						submitParams += systemFunc.makeArgs("type", "system");
						submitParams += systemFunc.makeArgs("act",  "reboot");

						systemFunc.postArgs("<?=System_management\Def\PATH_SYSTEM_PROCESS ?>", submitParams);
						logMsg = "<?=System_management\Lang\STR_SYSTEM_FACTORY_MSG ?>";
						systemLog.info(logMsg);

					} else {
						alert("<?=System_management\Lang\STR_SYSTEM_FACTORY_FAILED ?>");
						console.log("Upgrade error code : " + responseText);
						logMsg = "<?=System_management\Lang\STR_SYSTEM_FACTORY_FAILED ?>";
						systemLog.info(logMsg);

					}

				} else {
					// this.notiServerState(_req.status);
				}
			}
		}
	});


	class SystemFunc {
		constructor() {		}

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

	} // end of SystemFunc()

	class CryptFunc {
		constructor() {
			this.rng = new SecureRandom();
		}

		pack(_source) {
			var temp = "";
			for( var i = 0; i < _source.length ; i+=2 ) {
				temp += String.fromCharCode(parseInt(_source.substring(i, i + 2), 16));
			}

			return temp;
		}

		char2hex(_source) {
			var hex = "";

			for( var i = 0 ; i < _source.length ; i+=1 ) {
				var temp = _source[i].toString(16);

				switch( temp.length ) {
					case 1:
						temp = "0" + temp;
						break;

					case 0:
						temp = "00";
				}

				hex += temp;
			}

			return hex;
		}

		xor(_a, _b) {
			var length = Math.min(_a.length, _b.length);
			var temp = "";

			for( var i = 0 ; i < length ; i++ ) {
				temp += String.fromCharCode(_a.charCodeAt(i) ^ _b.charCodeAt(i));
			}
			length = Math.max(_a.length, _b.length) - length;

			for( var i = 0 ; i < length ; i++ ) {
				temp += "\x00";
			}

			return temp;
		}

		mgf1(_mgfSeed, _maskLen) {
			var t = "";
			var hLen = 20;
			var count = Math.ceil(_maskLen / hLen);

			for( var i = 0 ; i < count ; i++ ) {
				var c = String.fromCharCode((i >> 24) & 0xFF, (i >> 16) & 0xFF, (i >> 8) & 0xFF, i & 0xFF);
				t += this.pack(sha1Hash(_mgfSeed + c));
			}

			return t.substring(0, _maskLen);
		}

		rsaes_oaep_encrypt(_rsaKey, _m) {
			var n = new BigInteger(_rsaKey.n, 16);
			var k = _rsaKey.k; // length of n in bytes
			var e = new BigInteger(_rsaKey.e, 16);
			var hLen = 20;
			var mLen = _m.length;

			if( mLen > k - 2 * hLen - 2 ) {
				// message too long
			}

			var lHash = "\xda\x39\xa3\xee\x5e\x6b\x4b\x0d\x32\x55\xbf\xef\x95\x60\x18\x90\xaf\xd8\x07\x09"; // pack(sha1Hash(""))

			var ps = "";
			var temp = k - mLen - 2 * hLen - 2;

			for( var i = 0 ; i < temp ; i++ ) {
				ps+= "\x00";
			}

			var db = lHash + ps + "\x01" + _m;
			var seed = "";

			for( var i = 0 ; i < hLen + 4 ; i+=4 ) {
				temp = new Array(4);
				this.rng.nextBytes(temp);
				seed += String.fromCharCode(temp[0], temp[1], temp[2], temp[3]);
			}
			seed = seed.substring(4 - seed.length % 4);

			var dbMask     = this.mgf1(seed, k - hLen - 1);
			var maskedDB   = this.xor(db, dbMask);
			var seedMask   = this.mgf1(maskedDB, hLen);
			var maskedSeed = this.xor(seed, seedMask);
			var em = "\x00" + maskedSeed + maskedDB;

			_m = new Array();
			for( var i = 0 ; i < em.length ; i++ ) {
				_m[i] = em.charCodeAt(i);
			}
			_m = new BigInteger(_m, 256);

			var c = _m.modPow(e, n);
			c = c.toString(16);

			if( c.length & 1 ) {
				c = "0" + c;
			}

			return c;
		}

		pkcs7pad(_plaintext) {
			var pad = 16 - (_plaintext.length & 15);
			for( var i = 0 ; i < pad ; i++ ) {
				_plaintext += String.fromCharCode(pad);
			}

			return _plaintext;
		}

		aes_encrypt(_plaintext, _key, _iv) {
			var ciphertext = new Array();

			_plaintext = this.pkcs7pad(_plaintext);
			_key = new keyExpansion(_key);

			for( var i = 0 ; i < _plaintext.length ; i+=16 ) {
				var block = new Array(16);

				for( var j = 0 ; j < 16 ; j++ ) {
					block[j] = _plaintext.charCodeAt(i + j) ^ _iv[j];
				}
				block = AESencrypt(block, _key);

				for( var j = 0 ; j < 16 ; j++ ) {
					_iv[j] = block[j];
				}
				ciphertext = ciphertext.concat(block);
			}

			return ciphertext;
		}

		encrypt(_username, _plaintext) {
			var temp = new Array(32);
			this.rng.nextBytes(temp);

			var iv = temp.slice(0, 16);
			var key = "";

			for( var i = 16 ; i < 32 ; i++ ) { // eg. temp.slice(16, 32)
				key += String.fromCharCode(temp[i]);
			}
			var rc = "";
			if( !(rc = commonFunc.getRsaKey(_username)) ) {
				return false;
			}
			var rsaKey = JSON.parse(rc);
			var encPassword = this.rsaes_oaep_encrypt(rsaKey, key) + this.char2hex(iv) + this.char2hex(this.aes_encrypt(_plaintext, key, iv));

			return encPassword;
		}
	} // end of CryptFunc()

</script>

<?php
	include_once 'common_js_etc.php';
?>
