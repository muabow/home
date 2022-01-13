#include "api_curl.h"

CURL_Handler::CURL_Handler(bool _is_debug_print) {
	this->is_debug_print  = false;
	this->is_header_print = false;

	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	this->print_debug_info("CURL_Handler() create instance\n");


	this->curl_handle = NULL;
	this->clear();
	
	return ;
}

CURL_Handler::~CURL_Handler(void) {
	this->print_debug_info("CURL_Handler() instance destructed\n");
	
	
	if( this->curl_handle != NULL ) { 
		curl_easy_cleanup(this->curl_handle);
		this->curl_handle = NULL;
	}
	
	return ;
}
		
void CURL_Handler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	fprintf(stdout, "CURL_Handler::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	return ;
}

void CURL_Handler::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

void CURL_Handler::set_header_print(void) {
	this->is_header_print = true;
	
	this->print_debug_info("set_header_print() is set on\n");
	
	return ;
}

size_t CURL_Handler::write_callback(void *_contents, size_t _size, size_t _nmemb, void *_userp) {
	((string *)_userp)->append((char *)_contents, _size * _nmemb);
	
	return _size * _nmemb;
}

bool CURL_Handler::init(void) {
	if( (this->curl_handle = curl_easy_init()) > 0 ) {
		this->print_debug_info("init() curl_easy_init() success\n");
		this->is_init = true;
		
	} else {
		this->print_debug_info("init() curl_easy_init() failed\n");
		this->is_init = false;
	}
	
	curl_easy_setopt(this->curl_handle, CURLOPT_WRITEFUNCTION, 	this->write_callback);
	curl_easy_setopt(this->curl_handle, CURLOPT_WRITEDATA, 		&this->read_buffer);
	curl_easy_setopt(this->curl_handle, CURLOPT_HTTPHEADER,		this->headers);
	
	if( (this->is_debug_print && this->is_header_print ) ) {  
		curl_easy_setopt(this->curl_handle, CURLOPT_VERBOSE, 		true);
	}
	return this->is_init;
}

void CURL_Handler::clear(void) {
	this->headers		= NULL;

	this->read_buffer	= "";
	this->url			= "";
	this->data			= "";
	
	this->is_init		= false;
	this->is_set_url	= false;
	this->is_post		= false;
	
	if( this->curl_handle != NULL ) { 
		curl_easy_cleanup(this->curl_handle);
		this->curl_handle = NULL;
	}
	
	this->headers = curl_slist_append(this->headers, "Content-Type: application/json");
	
	this->print_debug_info("clear() clear handler\n");
	
	return ;
}

void CURL_Handler::set_header(string _header) {
	if( !this->is_init ) {
		this->print_debug_info("set_header() init not ready\n");
		return ;
	}
	
	this->headers = curl_slist_append(this->headers, _header.c_str());
	this->print_debug_info("set_header() set [%s]\n", _header.c_str());
	
	return ;
}

void CURL_Handler::set_url(string _url) {
	if( !this->is_init ) {
		this->print_debug_info("set_url() init not ready\n");
		return ;
	}
	
	this->print_debug_info("set_url() set [%s]\n", _url.c_str());
	this->is_set_url = true;
	this->url = _url;
	
	return ;
}

void CURL_Handler::set_post(string _data) {
	if( !this->is_init ) {
		this->print_debug_info("set_post() init not ready\n");
		return ;
	}
	
	this->print_debug_info("set_post() set [%s]\n", _data.c_str());
	this->is_post = true;
	this->data = _data;
	
    return ;
}

int	CURL_Handler::request(void) {
	if( !this->is_init ) {
		this->print_debug_info("request() init not ready\n");
		return -1;
	}
	
	if( !this->is_set_url ) {
		this->print_debug_info("request() set not url\n");
		return -1;
	}
	
	this->print_debug_info("request() request [%s] %s\n", 
				this->is_post ? "POST" : "GET", this->url.c_str());
	
	if( this->is_post ) {
		curl_easy_setopt(this->curl_handle, CURLOPT_POSTFIELDS,		this->data.c_str());
		curl_easy_setopt(this->curl_handle, CURLOPT_POSTFIELDSIZE,	this->data.length());
	}
	curl_easy_setopt(this->curl_handle, CURLOPT_URL, this->url.c_str());
	
	this->read_buffer   = "";
	this->curl_ret_code = curl_easy_perform(this->curl_handle);
	
	if( this->curl_ret_code != CURLE_OK ) {
		this->read_buffer = string(curl_easy_strerror(this->curl_ret_code));
	}
	
	return (int)this->curl_ret_code;
}

string CURL_Handler::response(void) {
	
	return this->read_buffer;
}

void CURL_Handler::set_header_content(string _content, string _info) {
	string content = _content;
	content.append(": ");
	content.append(_info);
	
	this->set_header(content);
	
	return ;
}

void CURL_Handler::set_server_info(string _target_url) {
	this->set_url(_target_url);
	
	return ;
}