<?php
	$is_single_account = false;
    $str_user_name     = "";

    if( !($argc == 1 || $argc == 2) ) {
        printf("invalid arguments\n");
        printf("usage : php create_user_rsa_key.php\n");
        printf("      - create default ras key for all accounts.\n");
        printf("usage : php create_user_passhash.php [account]\n");
        printf("      - create only the rsa key for the account entered.\n");

        exit ;
    }

	$envPath  = dirname(__FILE__);
	$keyPath  = $envPath . "/../key_data/";
	$envAuth  = file_get_contents($keyPath . "user_auth_list.json");
	$userList = json_decode($envAuth);

	$time_start = microtime(true);

	if( $argc == 2 ) {
        $is_single_account = true;
        $str_user_name     = $argv[1];

        if( !isset($userList->$str_user_name) ) {
            printf("# User account not found : [%s]\n", $str_user_name);

            exit ;
        }
    }

	printf("# Script for create user private/public key\n");

	foreach( $userList as $user => $value ) {
        if( $is_single_account ) {
            if( $str_user_name != $user ) {
                continue;
            }
        }

		printf(" - Create user rsa key : [%s]\n", $user);

		// ssh-keygen
		shell_exec('rm /tmp/id_rsa* 2> /dev/null; ssh-keygen -N "" -q -f /tmp/id_rsa');

		// copy private/public key
		printf("   : private key copy to [%s]\n", $keyPath . $user . '/private.pem');
		shell_exec('cp /tmp/id_rsa ' . $keyPath . $user . '/private.pem');

		shell_exec('chmod 755 ' . $keyPath . $user . '/private.pem');
		shell_exec('chown interm:interm ' . $keyPath . $user . '/private.pem');

		printf("   : public key  copy to [%s]\n", $keyPath . $user . '/public.pem');
		shell_exec('cp /tmp/id_rsa.pub ' . $keyPath . $user . '/public.pem');

		shell_exec('chmod 755 ' . $keyPath . $user . '/public.pem');
		shell_exec('chown interm:interm ' . $keyPath . $user . '/public.pem');

		printf(" - Done, [%s] rsa key was created\n\n", $user);
	}

	shell_exec('rm /tmp/id_rsa* 2> /dev/null');

	echo '# Total execution time in seconds : ' . (microtime(true) - $time_start) . "\n";
?>
