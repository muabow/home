#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <arpa/inet.h>
#include <errno.h>
#include <string>

#include "api_web_log.h"

WebLogHandler::WebLogHandler(const char *_module) {
	this->is_set = false;
	
	if( strlen(_module) > sizeof(this->module) ) {
		fprintf(stdout, "Module name length is a maximum of [%d] characters.\n", sizeof(this->module));
		
		return ;
	}
	this->is_set = true;
	
	memset(this->module, 0x00, sizeof(this->module));
	strcpy(this->module, _module);

	this->port 				= WebLogHandler::NUM_LOG_INTERFACE_PORT;
	this->is_debug_level		= false;
	this->is_debug_print	= false;

	return ;
}

WebLogHandler::~WebLogHandler(void) {
	this->print_debug_info("[%s] instance destructed\n", this->module);
	
	return ;
}

void WebLogHandler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	fprintf(stdout, "WebLogHandler::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	return ;
}

void WebLogHandler::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() set debug print\n");
	
	return ;
}

void WebLogHandler::set_info_level(void) {
	this->is_debug_level	= true;

	return ;
}

bool WebLogHandler::clear(void) {
	
	return this->send("clear", "");
}

bool WebLogHandler::remove(void) {
	
	return this->send("remove", "");
}

bool WebLogHandler::fatal(const char *_msg) {
	
	return this->send("fatal", _msg);
}

bool WebLogHandler::error(const char *_msg) {
	
	return this->send("error", _msg);
}

bool WebLogHandler::debug(const char *_msg) {
	
	return this->send("debug", _msg);
}

bool WebLogHandler::warn(const char *_msg) {
	
	return this->send("warn", _msg);
}

bool WebLogHandler::info(const char *_msg) {
	int rc;
	
	if( this->is_debug_level ) {
		rc = this->send("pinfo", _msg);

	} else {
		rc = this->send("info", _msg);
	}

	return rc;
}

bool WebLogHandler::send(const char *_log_level, const char *_message) {
	if( !this->is_set ) {
		this->print_debug_info("The instance is not ready.\n");
		return false;
	}
	
	int		rc = true;
	int 	sock_fd;
	struct 	sockaddr_in t_sock_addr;

	WebLogHandler::LOG_DATA_t t_log_data;

	memset(&t_log_data, 0x00, sizeof(t_log_data));

	strcpy(t_log_data.module, 	this->module);
	strcpy(t_log_data.type,		_log_level);
	strcpy(t_log_data.message,	_message);

	if( (sock_fd = socket(AF_INET, SOCK_DGRAM, 0)) < 0 ) {
		this->print_debug_info("send() socket() failed : [%02d] %s\n", errno, strerror(errno));
		
		return false;
	}

	bzero((char *) &t_sock_addr, sizeof(t_sock_addr));
	t_sock_addr.sin_family 	    = AF_INET;
	t_sock_addr.sin_port   		= htons(this->port);
	t_sock_addr.sin_addr.s_addr  = inet_addr("127.0.0.1");

	if( sendto(sock_fd, &t_log_data, sizeof(WebLogHandler::LOG_DATA_t), 0, (struct sockaddr *)&t_sock_addr, sizeof(t_sock_addr)) < 0 ) {
		this->print_debug_info("send() sendto() failed : [%02d] %s\n", errno, strerror(errno));
		
		rc = false;
	
	} else {
		this->print_debug_info("module:[%s] type:[%s] %s\n", this->module, _log_level, _message);	
	}

	close(sock_fd);

	return rc;
}
