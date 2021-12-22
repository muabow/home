<?php
	function get_account_list() {
		$str_user_list = "";

		$env_auth  = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../key_data/user_auth_list.json");
		$auth_info = json_decode($env_auth);

		foreach( $auth_info as $user => $value ) {
			$str_user_list .= "<option value=\"$user\">$user</option>";
		}
		echo $str_user_list;

		return;
	}
?>

<script type="text/javascript">
	$(document).ready(function() {
		var commonFunc = new CommonFunc();

		$("#button_submit").click(function() {
			if( $("#input_password").val() == "" ) {
				alert("비밀번호를 입력하세요.");
				return ;
			}

			var rsaCrypt    = new CryptFunc();
			var username    = $("#select_accountList :selected").val();
			var encPassword = rsaCrypt.encrypt(username, $("#input_password").val());

			if( !commonFunc.changePassword(username, encPassword) ) {
				alert("잘못된 비밀번호 입니다.");

			} else {
				alert("패스워드 변경이 완료 되었습니다.");
			}

		});
	});

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

<script type="text/javascript" src="/js/crypt/jsbn.js" ></script>
<script type="text/javascript" src="/js/crypt/jsbn2.js" ></script>
<script type="text/javascript" src="/js/crypt/prng4.js" ></script>
<script type="text/javascript" src="/js/crypt/rng.js" ></script>
<script type="text/javascript" src="/js/crypt/sha1.js" ></script>
<script type="text/javascript" src="/js/crypt/aes-enc.js" ></script>

<div id="user-div_page_title_name"> 비밀번호 변경 (Change password) </div>

<hr class="title-hr" />

<div id="user-div_contents_table">
	<div class="user-div_table_content_inner">
		<select id="select_accountList" style="width: 100px; height: 30px; padding-left: 10px;">
			<?=get_account_list(); ?>
		</select>

		<input type="password" id="input_password" maxlength="32" placeholder="변경할 비밀번호를 입력하세요." style="width: 300px; height: 26px; padding-left: 10px;" />
		<input type="button"   id="button_submit" class="div_module_button" value="적용" />

		<div style="margin-top: 10px;">
			<span style="font-weight: bold; font-size: 13px;">* 비밀번호를 잊었거나 강제로 변경해야할 경우 사용됩니다.</span>
		</div>
	</div>
</div>