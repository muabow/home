<?php
	// Javascript Web Log 관련 Process

	include_once "common_define.php";
	include_once "common_script.php";
	include_once "Crypt/RSA.php";
	include_once "Crypt/AES.php";
	include_once "Math/BigInteger.php";

	// Log Process
	if( $_POST[Common\Def\AJAX_PROC_TYPE] == Common\Def\AJAX_TYPE_LOG ) {
		if( isset($_POST[Common\Def\TYPE_LOG_ACT]) && isset($_POST[Common\Def\TYPE_LOG_MODULE]) ) {
			$logAct     = $_POST[Common\Def\TYPE_LOG_ACT];
			$moduleName = $_POST[Common\Def\TYPE_LOG_MODULE];

			$logger = new Common\Func\CommonLogFunc($moduleName);

			if( $logAct == Common\Def\TYPE_LOG_ACT_WRITE ) {
				$logLevel   = $_POST[Common\Def\TYPE_LOG_LEVEL];
				$message	= $_POST[Common\Def\TYPE_LOG_MESSAGE];

				$logger->writeLog($logLevel, $message);

				return ;
			}
			else if( $logAct == Common\Def\TYPE_LOG_ACT_CLEAR ) {
				$logger->clearLog();

				return ;
			}
			else if( $logAct == Common\Def\TYPE_LOG_ACT_REMOVE ) {
				$logger->removeLog();

				return ;
			}
		}
	}

	if( $_POST[Common\Def\AJAX_PROC_TYPE] == "login" ) {
		if( $_POST["act"] == "check_password" ) {
			session_start();
			$_SESSION["check_password"] = true;

			return;
		}

		if( $_POST["act"] == "pass_check_password" ) {
			session_start();
			unset($_SESSION["check_password"]);

			return;
		}

		if( $_POST["act"] == "pub_key" ) {
			$commonFunc = new Common\Func\CommonFunc();

			$userName = $_POST["username"];

			echo $commonFunc->getPubKey($userName);

			return ;
		}

		if( $_POST["act"] == "rsa_key" ) {
			$commonFunc = new Common\Func\CommonFunc();

			$userName = $_POST["username"];

			if( !($pubKey = $commonFunc->getPubKey($userName)) ) {
				return false;
			}

			$rsa = new Crypt_RSA();
			$rsa->loadKey($pubKey);

			$publickey = $rsa->getPublicKey(CRYPT_RSA_PUBLIC_FORMAT_RAW);

			$e = $publickey['e']->toHex();
			$n = $publickey['n']->toHex();
			$k = strlen($n) >> 1;

			echo '{"e":"' . $e . '", "n":"' . $n . '", "k":"' . $k . '"}';

			return ;
		}

		if( $_POST["act"] == "user_info" ) {
			$commonFunc = new Common\Func\CommonFunc();

			$userName = $_POST["username"];
			$passWord = $_POST["password"];

			if( !($priKey = $commonFunc->getPriKey($userName)) ) {
				return false;
			}

			$rsa = new Crypt_RSA();
			$rsa->loadKey($priKey);

			$publickey = $rsa->getPublicKey(CRYPT_RSA_PUBLIC_FORMAT_RAW);

			$e = $publickey['e']->toHex();
			$n = $publickey['n']->toHex();
			$k = strlen($n) >> 1;

			$cipherText = pack('H*', $passWord);
			$key = substr($cipherText, 0, $k);
			$iv  = substr($cipherText, $k, 16);
			$cipherText = substr($cipherText, $k + 16);

			$aes = new Crypt_AES();
			$aes->setKey($rsa->decrypt($key));
			$aes->setIV($iv);

			$decPassWord = $aes->decrypt($cipherText);

			if( $_POST['hash'] == "login" ) {
				$is_check = $_POST["is_check"];
				echo $commonFunc->login($userName, $decPassWord, $is_check);
			}
			else if( $_POST['hash'] == "checkLogin" ) {
				echo $commonFunc->checkLogin($userName, $decPassWord);
			}
			else if( $_POST['hash'] == "change" ) {
				echo $commonFunc->changePassHash($userName, $decPassWord);
			}
		}

		if( $_POST["act"] == "check_count" ) {
			$commonFunc = new Common\Func\CommonFunc();

			$username = $_POST["username"];

			// admin accounts bypass
			if( $commonFunc->is_admin_auth($username) ) {
				echo "unlock";
				return ;
			}

			$path_try_count = $_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/" . $username . "/try_count";
			if( !file_exists($path_try_count) ) {
				echo "lock";
				return ;
			}
			$try_cnt = file_get_contents($path_try_count);

			if( $try_cnt == Common\Def\NUM_LOGIN_TRY_COUNT ) {
				echo "lock";
			} else {
				echo "unlock";
			}

			return ;
		}

		if( $_POST["act"] == "get_count" ) {
			$username = $_POST["username"];

			$path_try_count = $_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/" . $username . "/try_count";
			if( !file_exists($path_try_count) ) {
				echo 0;
				return ;
			}

			$try_cnt = file_get_contents($path_try_count);

			echo $try_cnt;

			return ;
		}

		if( $_POST["act"] == "reset_count" ) {
			$username = $_POST["username"];

			$path_try_count = $_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/" . $username . "/try_count";
			file_put_contents($path_try_count, 0);

			return ;
		}


		if( $_POST["act"] == "is_exist_user" ) {
			$username = $_POST["username"];

			if( file_exists($_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/" . $username) ) {
				echo 1;

			} else {
				echo 0;
			}

			return ;
		}

		return ;
	}

	if( $_POST[Common\Def\AJAX_PROC_TYPE] == "logout" ) {
		$commonFunc = new Common\Func\CommonFunc();

		$commonFunc->logout();

		return ;
	}

	include_once "{$_SERVER['DOCUMENT_ROOT']}/common/common_process_etc.php";
?>
