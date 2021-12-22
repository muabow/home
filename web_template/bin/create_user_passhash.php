<?php
	error_reporting(0);

	const SYSTEM_VENDOR_MAC_ADDR    = "00:1D:1D:";	// hash salt
	const DEFAULT_USER_PASSWORD		= 1;

	$is_single_account = false;
	$str_user_name     = "";
	$str_user_password = DEFAULT_USER_PASSWORD;

	if( !($argc == 1 || $argc == 3) ) {
		printf("invalid arguments\n");
		printf("usage : php create_user_passhash.php\n");
		printf("      - create default passhash for all accounts.\n");
		printf("usage : php create_user_passhash.php [account] [password]\n");
		printf("      - create only the passhash for the account entered.\n");

		exit ;
	}
	
	$envPath  = dirname(__FILE__);
	$keyPath  = $envPath . "/../key_data/";
	$envAuth  = file_get_contents($keyPath . "user_auth_list.json");
	$userList = json_decode($envAuth);

	$time_start = microtime(true);

	if( $argc == 3 ) {
		$is_single_account = true;
		$str_user_name     = $argv[1];
		$str_user_password = $argv[2];

		if( !isset($userList->$str_user_name) ) {
			printf("# User account not found : [%s]\n", $str_user_name);
		
			exit ;
		}
	}

	printf("# Script for create user passhash\n");

	foreach( $userList as $user => $value ) {
		if( $is_single_account ) {
			if( $str_user_name != $user ) {
				continue;	
			}
		}

		printf(" - Create user passhash : [%s]\n", $user);
		printf("   : passhash copy to [%s]\n", $keyPath . $user . '/passhash');

		$keyFile = $keyPath . $user . '/public.pem';
		if( !file_exists($keyFile) ) {
			printf(" - Failed, [%s] key not found : %s\n\n", $user, $keyFile);
			continue;
		}

		$pubKey  = trim(file_get_contents($keyFile));

		$rc = hash('sha256', $user . $pubKey . $str_user_password, false);
		printf("   : %s\n", $rc);

		file_put_contents($keyPath . $user . '/passhash', $rc);

		shell_exec('chmod 755 ' . $keyPath . $user . '/passhash');
		shell_exec('chown interm:interm ' . $keyPath . $user . '/passhash');

		printf(" - Done, [%s] passhash was created\n\n", $user);
	}

	echo '# Total execution time in seconds : ' . (microtime(true) - $time_start) . "\n";

	exit ;
?>
