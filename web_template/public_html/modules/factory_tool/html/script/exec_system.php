<?php
	function is_toggle_ignore_background() {
		if( file_exists("/tmp/api_ignore_svr_check") ) {
			return "system-div_switch_background";

		} else {
			return "";
		}
	}

	function is_toggle_ignore_status() {
		if( file_exists("/tmp/api_ignore_svr_check") ) {
			return "system-div_switch_back_on_off";

		} else {
			return "";
		}
	}

	if( isset($_POST["type"]) && $_POST["type"] == "ignore_api_key" ) {
		if( $_POST["status"] == "on" ) {
			shell_exec("touch /tmp/api_ignore_svr_check");
		
		} else {
			@unlink("/tmp/api_ignore_svr_check");
		}

		exit ;
	}
	
?>

<script type="text/javascript">
	class IgnoreApiKeyFunc {
		constructor() {
			this.path = "http://<?php echo $_SERVER["HTTP_HOST"]; ?>/modules/factory_tool/html/script/exec_system.php";
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
	}
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

	$(document).ready(function() {
		var ignoreApiKey = new IgnoreApiKeyFunc();
		var commonFunc   = new CommonFunc();

		$("#ignore_api_key").click(function() {
			$(this).find(".system-div_system_check_toggle").toggleClass("system-div_switch_back_on_off", 0, "easeOutSine");
			$(this).toggleClass("system-div_switch_background", 0, "easeOutSine");
			
			var submitArgs = "";
			submitArgs += ignoreApiKey.makeArgs("type", "ignore_api_key");

			if( $(this).hasClass("system-div_switch_background") == true ) {
				submitArgs += ignoreApiKey.makeArgs("status", "on");
			
			} else {
				submitArgs += ignoreApiKey.makeArgs("status", "off");
			}

			ignoreApiKey.postArgs(ignoreApiKey.path, submitArgs);
			
			return ;
		});


		$("#div_button_reset_pwsd").click(function() {
			if( !confirm("admin,setup,user,guest 패스워드를 공장 초기화 상태로 되돌립니다.") ) {
				return ;
			}

			var rsaCrypt    = new CryptFunc();
			var arr_user_list = ["admin", "setup", "user", "guest"];
			
			$(arr_user_list).each(function(_idx, _username) { 
				var encPassword = rsaCrypt.encrypt(_username, "1");
				
				commonFunc.changePassword(_username, encPassword);
			});
			
			alert("패스워드 변경이 완료 되었습니다.");

			return ;
		});
	});

</script>

<script type="text/javascript" src="/js/crypt/jsbn.js" ></script>
<script type="text/javascript" src="/js/crypt/jsbn2.js" ></script>
<script type="text/javascript" src="/js/crypt/prng4.js" ></script>
<script type="text/javascript" src="/js/crypt/rng.js" ></script>
<script type="text/javascript" src="/js/crypt/sha1.js" ></script>
<script type="text/javascript" src="/js/crypt/aes-enc.js" ></script>

<div id="system-div_page_title_name" name="system"> 시스템 환경 설정 (System preferences) </div>

<hr class="title-hr" style="width: 890px"/>

<div id="system-div_contents_table">
	<div class="system-div_contents_cell">
		<div class="system-div_contents_cell_line">
			<div class="system-div_contents_cell_title">
				API KEY 등록 서버 우회 설정
			</div>

			<div class="system-div_contents_cell_contents">
				<div class="system-div_system_toggle <?=is_toggle_ignore_background() ?>" id="ignore_api_key">
					<span class="system-span_system_check_on"> ON </span>
					<span class="system-span_system_check_off"> OFF </span>
					<div class="system-div_system_check_toggle <?=is_toggle_ignore_status() ?>" id="check_ignore_api_key"></div>
				</div>
			</div>
		</div>
		<div class="system-div_contents_cell_line">
			<div class="system-div_contents_cell_title">
				사용자 계정 패스워드 초기화
			</div>

			<div class="system-div_contents_cell_contents">
				<div id="div_button_reset_pwsd" class="div_class_button">
					초기화
				</div>
			</div>
		</div>
		<div class="system-div_contents_cell_line"></div>
	</div>
</div>
