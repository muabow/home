<?php
    include_once("./func_curl.php");

    $json_data = json_decode(curl_request_data("<Rest API server>"));
    print_r($json_data);
?>