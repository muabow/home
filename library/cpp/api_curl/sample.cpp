#include <stdio.h>

#include "api_curl.h"

int main(int _argc, char *_argv[]) {
    CURL_Handler	curl_handle;
    
    curl_handle.set_debug_print();
    curl_handle.init();
		
    curl_handle.set_header_content("Content-Type", 	"application/json");
    
    // POST 사용 시 string data를 인자로 사용
    // curl_handle.set_post(str_curl_data);

    curl_handle.set_server_info("https://httpbin.org/get");
    curl_handle.request();
    
    string response = curl_handle.response();
    
    printf("response : [%s]\n", response.c_str());
    
    curl_handle.clear();

    return 0;
}

