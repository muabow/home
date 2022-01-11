#ifndef __MAIN_H__
#define __MAIN_H__

#include <fstream>
#include <iostream>
#include <sstream>
#include <string>
#include <mutex>
#include <vector>
#include <tuple>
#include <thread>
#include <stdexcept>


#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdarg.h>
#include <unistd.h>
#include <getopt.h>
#include <sys/time.h>
#include <libgen.h>

#include "../usr/include/lib_signal.h"
#include "socket_server.h"

#define TIME_CHECK_LOOP			1			// sec

using namespace std;

// ##
// global variables
// ##
bool	g_is_debug_print	= false;


// ##
// functions: common
// ##
void	print_debug_info(const char *_format, ...);


// ##
// functions: event handler
// ##
void	signal_event_handler(void);

// functions: network function
void	run_server_unicast(void);
void 	stop_server_unicast(void);

// functions: network event handler
void 	network_server_event_handler(int _index, bool _is_connect);


// ##
// global instance
// ##
SignalHandler			g_sig_handler;
SOCKET_UnicastServer	g_socket_unicast_server;

#endif