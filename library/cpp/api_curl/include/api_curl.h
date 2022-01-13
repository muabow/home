#ifndef __API_CURL_H__
#define __API_CURL_H__

#include <stdarg.h>

#include <iostream>
#include <string>
#include <curl/curl.h>

using namespace std;

class CURL_Handler {
	private :
		bool			is_debug_print;
		bool			is_header_print;
		void			print_debug_info(const char *_format, ...);
		
		bool			is_init;
		bool			is_set_url;
		bool			is_post;
		
		CURL 			*curl_handle;
		CURLcode 		curl_ret_code;
		string 			read_buffer;
		string			url;
		string			data;
		
		struct			curl_slist *headers;
		
		static size_t 	write_callback(void *_contents, size_t _size, size_t _nmemb, void *_userp);
		
	public  :
		CURL_Handler(bool _is_debug_print = false);
		~CURL_Handler(void);
		
		void 			set_debug_print(void);
		void 			set_header_print(void);
		
		bool			init(void);
		void			clear(void);
		
		void			set_header(string _header);
		void			set_header_content(string _content, string _info);
		void			set_post(string _data);
		void			set_url(string _url);
		void            set_server_info(string _target_url);

		
		int				request(void);
		string			response(void);
};

 
#endif