<?php
	include_once "/opt/interm/public_html/modules/time_setup/html/common/common_script.php";
	$timeFunc = new Time_setup\Func\TimeSetupFunc();

	// authentication
	$envData = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/env.json"));
	if( isset($envData->info->auth_key) ) {
		if( !file_exists($envData->info->auth_key) ) {

			include_once $_SERVER["DOCUMENT_ROOT"] . "/auth.php";

			exit ;
		}
	}

	// session
	const SESSION_TIMEOUT_SECOND = 1800;

	session_start();

	// set remote ip address
	$remote_addr = $_SERVER['REMOTE_ADDR'];
	if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
		$remote_addr = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
	}
	$_SESSION['ip_addr'] = $remote_addr;

	// get session list
	if( file_exists("/tmp/session_list") ) {
		$fd = fopen("/tmp/session_list", "r");
		$str_session_list = fread($fd, filesize("/tmp/session_list"));
		fclose($fd);

		$json_session_list = json_decode($str_session_list);

		// get limit login count
		$load_envData = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . "../conf/config-limit-login.json");
		$envData      = json_decode($load_envData);

		$limit_login_count  = $envData->limit_login_count;

		if( $json_session_list->count == $limit_login_count ) {
			$is_exist = false;

			for( $idx = 0 ; $idx < $json_session_list->count ; $idx++ ) {
				if( session_id() == $json_session_list->list[$idx]->session_id ) {
					$is_exist = true;
					break;
				}
			}

			if( !$is_exist ) {
				session_destroy();
				header("Location: http://" . $_SERVER["HTTP_HOST"] . "/deny_login.php");
				exit;
			}
		}
	}

	// keep session
	$is_keep_session = false;
	if( file_exists("/opt/interm/key_data/keep_session_list.json") ) {
		$load_data = file_get_contents("/opt/interm/key_data/keep_session_list.json");
		$arr_keep_list = json_decode($load_data);
		
		if( !empty($arr_keep_list) ) {
			foreach ($arr_keep_list as $ip_addr => $username ) {
				if( $remote_addr == $ip_addr ) {
					$is_keep_session = true;
					break;
				}
			}
		}
	}

	$filename = basename($_SERVER['PHP_SELF'] );

	if( $filename == "login.php" ) {
		if( isset($_SESSION["timeout"]) && isset($_SESSION['username'])) {
			

			return ;
		}

	} else {
		if (isset($_SESSION["check_password"])) {
			include_once $_SERVER["DOCUMENT_ROOT"] . "/pswd_change.php";
			
			//unset($_SESSION["check_password"]);
			exit;
		}
		
		if( $is_keep_session ) {
			$_SESSION["username"] = $username;
			$_SESSION['ip_addr']  = $ip_addr;
			$_SESSION["timeout"]  = time();

			if( isset($_POST["pc_view"]) ) {
				$_SESSION["pc_view"] = true;
			}

			if( isset($_POST["mobile_view"]) ) {
				unset($_SESSION["pc_view"]);
			}

		} else if( empty($_SESSION['username']) || empty($_SESSION["timeout"]) || $_SESSION["timeout"] + SESSION_TIMEOUT_SECOND < time() ) {
			session_destroy();

			include_once $_SERVER["DOCUMENT_ROOT"] . "/login.php";

			exit ;

		} else {
			if ( isset($_SESSION["timeout"]) ){
				// session_start();
				$_SESSION["timeout"] = time();

				if( isset($_POST["pc_view"]) ) {
					$_SESSION["pc_view"] = true;
				}

				if( isset($_POST["mobile_view"]) ) {
					unset($_SESSION["pc_view"]);
				}

				return ;
			}
			
			
		}
	}
?>
