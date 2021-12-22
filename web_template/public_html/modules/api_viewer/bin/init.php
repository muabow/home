<?php
    $_SERVER['DOCUMENT_ROOT'] = "/opt/interm/public_html";
    include_once "{$_SERVER['DOCUMENT_ROOT']}/common/common_define.php";
    include_once "{$_SERVER['DOCUMENT_ROOT']}/common/common_script.php";
    
    $headerFunc	= new Common\Func\CommonHeaderFunc();

    $str_machine_id = trim(shell_exec('cat /etc/machine-id 2>/dev/null'));
    $str_header_key = $headerFunc->makeHeaderId($str_machine_id);
    $str_secret_key = $headerFunc->makeHeaderSecretKey($str_machine_id, 0);
    printf("# Create Open API key pair, start.\n");
    printf(" - header key : %s\n", $str_header_key);
    printf(" - secret key : %s\n", $str_secret_key);
    
    $arr_hash_table = $headerFunc->getHashTable();
    
    $is_exist_master_key = false;
    $exist_header_key = "";
    $exist_secret_key = "";
    foreach( $arr_hash_table as $header_key => $header_info ) {
        if( $header_key == "stdDate" || $header_key == "maxCount" ) continue;

        foreach( $header_info as $secret_key => $secret_info ) {
            if( $is_exist_master_key ) {
                printf("# Remove duplicated master_key : %s\n", $header_key);
                unset($arr_hash_table[$header_key]);
                continue;
            }

            if( isset($secret_info["master_key"]) ) {
                $is_exist_master_key = true;

                $exist_header_key = $header_key;
                $exist_secret_key = $secret_key;
            }
        }
    }
    
    if( $is_exist_master_key ) {
        if( $exist_header_key != $str_header_key ) {
            printf("# Change header master_key : %s\n", $str_header_key);

            $arr_hash_table[$str_header_key] = $arr_hash_table[$exist_header_key];
            unset($arr_hash_table[$exist_header_key]);
        }

        if( $exist_secret_key != $str_secret_key ) {
            printf("# Change secret master_key : %s\n", $str_secret_key);
            $arr_hash_table[$str_header_key][$str_secret_key] = $arr_hash_table[$str_header_key][$exist_secret_key];
            unset($arr_hash_table[$str_header_key][$exist_secret_key]);
        }
        $headerFunc->set_hash_info($arr_hash_table);

    } else {
        $arr_user_info = array();
        $arr_user_info['companyName'] = "-";
        $arr_user_info['userName']    = "-";
        $arr_user_info['contact']     = "-";

        $headerFunc->setHashTable($str_header_key, $str_secret_key, $arr_user_info, true);
    }

    printf("Create Open API key pair, end.\n");
    return ;
?>
