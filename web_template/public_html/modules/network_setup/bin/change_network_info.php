<?php
	function json_validate($string) {
		// decode the JSON data
		$result = json_decode($string);

		// switch and check possible JSON errors
		switch( json_last_error() ) {
			case JSON_ERROR_NONE:
				$error = ''; // JSON is valid // No error has occurred
				break;
			case JSON_ERROR_DEPTH:
				$error = 'The maximum stack depth has been exceeded.';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$error = 'Invalid or malformed JSON.';
				break;
			case JSON_ERROR_CTRL_CHAR:
				$error = 'Control character error, possibly incorrectly encoded.';
				break;
			case JSON_ERROR_SYNTAX:
				$error = 'Syntax error, malformed JSON.';
				break;
				// PHP >= 5.3.3
			case JSON_ERROR_UTF8:
				$error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
				break;
				// PHP >= 5.5.0
			case JSON_ERROR_RECURSION:
				$error = 'One or more recursive references in the value to be encoded.';
				break;
				// PHP >= 5.5.0
			case JSON_ERROR_INF_OR_NAN:
				$error = 'One or more NAN or INF values in the value to be encoded.';
				break;
			case JSON_ERROR_UNSUPPORTED_TYPE:
				$error = 'A value of a type that cannot be encoded was given.';
				break;
			default:
				$error = 'Unknown JSON error occured.';
				break;
		}

		if( $error != '' ) {
			return $error;
		}

		// everything is OK
		return $result;
	}

	$svc_type = $argv[1];
	$contents = $argv[2];
	$msg	  = "OK";

	switch( $svc_type ) {
		case "check":
			if( !is_object($data = json_validate($contents)) ) {
				$msg = $data;
			}
		break;

		case "change":
			$data = json_validate($contents);
			file_put_contents("/opt/interm/public_html/modules/network_setup/conf/network_stat.json", json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
			shell_exec("/usr/bin/php /opt/interm/public_html/modules/network_setup/bin/reconfiguration.php");
		break;
	}

	echo $msg;

	return ;
?>
