#ifndef __MAIN_H__
#define __MAIN_H__

#include <stdio.h>
#include <unistd.h>
#include <stdlib.h>
#include <string.h>
#include <getopt.h>
#include <stdarg.h>

#include <iostream>
#include <sstream>
#include <csignal>
#include <mutex>
#include <string>
#include <vector>

#include "api_signal.h"
#include "api_websocket.h"
#include "api_json_parser.h"
#include "class_main.h"


#define	INTERVAL_NETWORK_CHECK		1


void print_debug_info(const char *_format, ...);
void print_help(char *_argv);


//event handler declare
static void ws_event_interface_handler(const char _cmd_id, const char _is_binary, const int _length, const void *_data, WebsocketHandler *_this);

bool ws_event_if_func_regist(char *_data);
bool ws_event_if_func_remove(char *_data);
void ws_event_if_func_send(char *_data);
void ws_event_if_func_display_list(void);

bool ws_event_if_func_regist_str(string _ip_addr, string _uri);
bool ws_event_if_func_remove_str(string _ip_addr, string _uri);


// global instance
WebsocketHandler g_ws_interface_handler;
WS_ClientHandler g_ws_client_handler;
SignalHandler 	 sig_handler;

mutex g_event_mutex, g_event_if_mutex;
mutex g_wsif_mutex;

bool  g_is_debug_print = false;

#endif
