<?php
    function get_http_status_message($_status) {
        $arr_http_status = array(
                100 => 'Continue',
                101 => 'Switching Protocols',
                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',
                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Found',
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                306 => '(Unused)',
                307 => 'Temporary Redirect',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Timeout',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Entity Too Large',
                414 => 'Request-URI Too Long',
                415 => 'Unsupported Media Type',
                416 => 'Requested Range Not Satisfiable',
                417 => 'Expectation Failed',
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported');

        if( $_status == null ) {
            // return http status array
            return $arr_http_status;
        }

        return ($arr_http_status[$_status]) ? $arr_http_status[$_status] : $status[500];
    }

    function curl_request_data($_target, $_data = "", $_arr_opt = "", $_timeout = 2) {
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $_target);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, $_timeout);

        if( $_data != "" ) {
            curl_setopt($curlsession, CURLOPT_POST, true);
            curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $_data);
        }

        if( $_arr_opt != "" ) {
            if( isset($_arr_opt["auth"]) ) {
                curl_setopt($curl_handle, CURLOPT_HTTPAUTH, $_arr_opt["auth"]);
            }

            if( isset($_arr_opt["username"]) && isset($_arr_opt["password"]) ) {
                curl_setopt($curl_handle, CURLOPT_USERPWD, "{$_arr_opt["username"]}:{$_arr_opt["password"]}");
            }
        }

        $response = curl_exec($curl_handle);
        $http_code = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);

        // 1. curl 오류 검출
        if( curl_errno($curl_handle) != 0 ) {
            curl_close($curl_handle);

            return '{
                    "code": 503,
                    "message": "Service Unavailable",
                    "result": ""
            }';
        }
        curl_close($curl_handle);

        // 2. http response code 오류 검출
        if( $http_code != 200 ) {
            return '{
                    "code": ' . $http_code . ',
                    "message": "' . get_http_status_message($http_code) . '",
                    "result": ""
            }';
        }

        // 3. json format 오류 검출
        json_decode($response);
        if( json_last_error() != JSON_ERROR_NONE ) {
            return '{
                    "code": 500,
                    "message": "Internal Server Error",
                    "result": "invalid JSON format"
            }';
        }

        // 4. 정상 case 출력
        return $response;
    }
?>
