<?php
    include_once("./func_curl.php");

    /*
        # Make option info
        auth 	 => CURLAUTH_BASIC, CURLAUTH_DIGEST, CURLAUTH_GSSNEGOTIATE, CURLAUTH_NTLM, CURLAUTH_ANY, CURLAUTH_ANYSAFE
        username => "username"
        password => "password"
    */
    $str_target = "<Rest API Server>";  // e.g. http://192.168.1.99/api/status
    $str_data	= "";	                // "" is GET method, other POST method
    $arr_opt	= array("auth" => CURLAUTH_DIGEST, "username" => "root", "password" => "root");

    // curl request
    $response = curl_request_data($str_target, $str_data, $arr_opt);

    print_r(json_decode($response));
?>