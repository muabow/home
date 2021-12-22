#include <stdio.h>
#include <errno.h>
#include <signal.h>
#include <string.h>

#include <iostream>

#include "api_signal.h"

int  g_num_sig_handle = 0;
bool g_is_sig_term    = false;
void (*p_user_signal_handler)(void) = NULL;


SignalHandler::SignalHandler(void) {
	this->num_sig_handle = g_num_sig_handle;
	g_num_sig_handle++;
	
	if( this->num_sig_handle > 0 ) {
		fprintf(stderr, "SignalHandler() instance can not be created.\n");
		return ;
	}
	
	fprintf(stderr, "SignalHandler() create instance\n");
	
	// init global var/function 
	g_is_sig_term 		  = false;
	p_user_signal_handler = NULL;
				
	return ;
}

SignalHandler::~SignalHandler(void) {
	fprintf(stderr, "SignalHandler() instance destructed.\n");
	
	return ;
}

void SignalHandler::signal_handler(int _sig_num) {
	if( g_is_sig_term ) {
		fprintf(stderr, "term() already terminated\n");
				
		return ;
	}
				
	g_is_sig_term = true;

	if( p_user_signal_handler != NULL ) {
		p_user_signal_handler();
	}
								
	return ;
}

void SignalHandler::set_signal(int _sig_num) {
	if( this->num_sig_handle > 0 ) {
		fprintf(stderr, "SignalHandler() instance can not be created.\n");
		
		return ;
	}
	
	fprintf(stderr, "set_signal() bind singal event [%s]\n", strsignal(_sig_num));
			
	signal(_sig_num, signal_handler);
				
	return ;
}

void SignalHandler::set_signal_handler(void (*_func)(void)) {
	if( this->num_sig_handle > 0 ) {
		fprintf(stderr, "SignalHandler() instance can not be created.\n");
		
		return ;
	}
	
	fprintf(stderr, "set_signal_handler() bind user term function\n");
				
	p_user_signal_handler = _func;
				
	return ;
}

bool SignalHandler::is_term(void) {
	return g_is_sig_term;
	
}

void SignalHandler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	fprintf(stderr, "SignalHandler::");
	
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	return ;
}

void SignalHandler::set_debug_print(void) {
	this->is_debug_print = true;

	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}