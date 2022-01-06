<?php
    include_once("./func_curl.php");

    /* Make option info
        auth 	 => CURLAUTH_BASIC, CURLAUTH_DIGEST, CURLAUTH_GSSNEGOTIATE, CURLAUTH_NTLM, CURLAUTH_ANY, CURLAUTH_ANYSAFE
        username => "username"
        password => "password"
    */
    $arr_opt	= array("auth" => CURLAUTH_DIGEST, "username" => "root", "password" => "root");
    $str_data	= "";	// "" is GET method, other POST method

    $response	= curl_request_data("<Rest API Server>", $str_data, $arr_opt);

    print_r(json_decode($response));
?>