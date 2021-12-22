<script type="text/javascript" src="js/crypt/jsbn.js" ></script>
<script type="text/javascript" src="js/crypt/jsbn2.js" ></script>
<script type="text/javascript" src="js/crypt/prng4.js" ></script>
<script type="text/javascript" src="js/crypt/rng.js" ></script>
<script type="text/javascript" src="js/crypt/sha1.js" ></script>
<script type="text/javascript" src="js/crypt/aes-enc.js" ></script>

<script type="text/javascript">
	// Crypt functions
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

	var rsaCrypt = new CryptFunc();

	// 로그인 메뉴
	function ShowAlertDiv(_msg) {
		if( $("#div_login_alert").css("display") == "block" ) {
			if( _msg == $("#div_login_alert").html() ) {
				return ;

			} else {
				$("#div_login_alert").hide();
			}
		}

		$("#div_login_alert").html(_msg).show("drop", {direction: "down"}, "slow");

		return ;
	}

	$("#div_login_form_submit").click(function() {
		$("#div_login_alert").hide("drop", {direction: "down"}, "slow");

		if( !$("#input_login_username").val() ) {
			$("#input_login_username").focus();

			ShowAlertDiv("<?=Common\Lang\STR_LOGIN_ENTER_ID ?>");
			return ;
		}

		if( !$("#input_login_password").val() ) {
			$("#input_login_password").focus();

			ShowAlertDiv("<?=Common\Lang\STR_LOGIN_ENTER_PASSWORD ?>");
			return ;
		}

		var username    = $("#input_login_username").val();
		var encPassword = rsaCrypt.encrypt(username, $("#input_login_password").val());

		if ( commonFunc.is_exist_user(username) == 0 ) {
			ShowAlertDiv("<?=Common\Lang\STR_LOGIN_INVALID_ID?>");

			return;
		}

		if( commonFunc.check_try_count(username) == "lock" ) {
			$("#input_login_username").val("").focus();
			$("#input_login_password").val("");

			var retry_count = commonFunc.get_try_count(username);
			ShowAlertDiv("<?=Common\Lang\STR_LOGIN_LIMIT ?> [" + retry_count + "/<?=Common\Def\NUM_LOGIN_TRY_COUNT ?>]");

			return ;
		}
		var is_auto_login_checked = ($("input:checkbox[id = 'input_auto_login_checkbox']").is(":checked") == true ? 1 : 0);

		var is_login = commonFunc.login(username, encPassword, is_auto_login_checked);

		if( !encPassword || !is_login ) {
			$("#input_login_username").val("").focus();
			$("#input_login_password").val("");

			var retry_count   = commonFunc.get_try_count(username);
			var retry_message = "<?=Common\Lang\STR_LOGIN_WRONG_INFO ?>";

			if( retry_count != 0 ) {
				retry_message = "<?=Common\Lang\STR_LOGIN_WRONG_PASSWD ?> [" + retry_count + "/<?=Common\Def\NUM_LOGIN_TRY_COUNT ?>]";
			}

			ShowAlertDiv(retry_message);

			return;
		}

		var regex = /^(?=.*[a-zA-Z])(?=.*[!@#$%^*+=-])(?=.*[0-9]).{8,16}/;
		if( !regex.test($("#input_login_password").val()) ) {
			$("#dialog-confirm").dialog("open");
			return;
		}

		var logger = new CommonLogFunc("system");
		logger.info($("#input_login_username").val() + " <?=Common\Lang\STR_LOGIN_SUCCESS ?>");
		location.reload();
	});

	$(window).resize(function() {
	    $("#dialog-confirm").dialog("option", "position", {my: "center", at: "center", of: window});
	});

	$(function() {
		$("#dialog-confirm").dialog({
			autoOpen : false,
			resizable : false,
			height : "auto",
			draggable: true,
			width : 400,
			modal : true,
			beforeClose: function(event, ui) {
               commonFunc.logout();
            },
			buttons: {
				"<?=Common\Lang\STR_LOGIN_CHANGE_CONFIRM ?>": function() {
					var logger = new CommonLogFunc("system");
					logger.info($("#input_login_username").val() + " <?=Common\Lang\STR_LOGIN_SUCCESS ?>");

					commonFunc.checkPassword();
					location.reload();
				},
        		"<?=Common\Lang\STR_LOGIN_CHANGE_NEXT ?>": function() {
        			var logger = new CommonLogFunc("system");
					logger.info($("#input_login_username").val() + " <?=Common\Lang\STR_LOGIN_SUCCESS ?>");

					commonFunc.pass_password_change();
					location.reload();
				}
			}
		});
	});

	$("#div_after_form_submit").click(function() {
		commonFunc.pass_password_change();
		location.reload();
	});

	$("#input_login_username").focus()
	.keyup(function(_ex) {
		$("#div_login_alert").hide("drop", {direction: "down"}, "slow");

		if( _ex.keyCode == 13 ) { // enter key
			$("#input_login_password").focus();
		}
	});

	$("#input_login_password").keyup(function(_ex) {
		$("#div_login_alert").hide("drop", {direction: "down"}, "slow");

		if( _ex.keyCode == 13 ) { // return
			$("#div_login_form_submit").trigger("click");
		}
	});

	$("#input_auto_login_checkbox").keyup(function(_ex) {
		$("#div_login_alert").hide("drop", {direction: "down"}, "slow");

		if( _ex.keyCode == 13 ) { // return
			$("#div_login_form_submit").trigger("click");
		}
	});

	$("#div_password_form_submit").click(function() {
		var username = $("#input_login_username").val();

		if ( commonFunc.is_exist_user(username) == 0 ) {
			alert("<?=Common\Lang\STR_LOGIN_INVALID_ID ?>");

			return;
		}

		var encPassword = rsaCrypt.encrypt(username, $("#input_origin_password").val());
		if ( !commonFunc.checkLogin(username, encPassword) ) {
			alert("<?=Common\lang\STR_LOGIN_PASSWD_CHECK_CURRENT ?>");
			return;
		}

		if ( $("#input_new_password").val() != $("#input_retry_password").val() ) {
			alert("<?=Common\Lang\STR_LOGIN_PASSWD_CHECK_WRONG ?>");
			return;
		}

		if ( $("#input_origin_password").val() == $("#input_new_password").val() ) {
			alert("<?=Common\Lang\STR_LOGIN_PASSWD_CHECK_SAME ?>");
			return;
		}

		// 8~16자리, 영문, 숫자, 특수문자 조합
		var regex = /^(?=.*[a-zA-Z])(?=.*[!@#$%^*+=-])(?=.*[0-9]).{8,16}/;
		if( !regex.test($("#input_retry_password").val()) ) {
			alert("<?=Common\Lang\STR_LOGIN_PASSWD_MIN_LENGTH ?>");
			return ;
		}

		var encNewPassword = rsaCrypt.encrypt(username, $("#input_retry_password").val());
		if( commonFunc.changePassword(username, encNewPassword) ) {
			alert("<?=Common\Lang\STR_LOGIN_PASSWD_CHECK_COMPLETE ?>");
			commonFunc.pass_password_change();
			location.reload();
		}
	});
</script>
