개발환경  
Ubuntu 16.04.3 LTS, kernel 4.4.0-78-generic, x86
  
Directory 구성  
func_curl.php  
README.md  
  
실행 방법  
<?php  
$json_data = json_decode(curl_request_data("<일반 IP 장치>"));  
print_r($json_data);  
  
/*  
stdClass Object  
(  
    [code] => 200  
    [message] => OK  
    [result] => stdClass Object  
        (  
            [Attributes] => stdClass Object  
                (  
                    [Version] => 1.0  
                    [SupportMethod] => GET  
                    [SupportAPI] => enable  
                    [AccessControl] => guest  
                )  
            [DeviceType] => default  
        )  
  
)  
*/  
?>  

