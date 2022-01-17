#ifndef __MAIN_H__
#define __MAIN_H__

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdarg.h>
#include <unistd.h>
#include <getopt.h>

#include <string>

#include "lib_signal.h"
#include "pcm_capture.h"


#define TIME_ALIVE_LOOP     1	// sec

using namespace std;

// ##
// global variables
// ##
bool	g_is_debug_print	= false;
FILE   *g_fptr = NULL;

// ##
// functions: common
// ##
void	print_debug_info(const char *_format, ...);


// ##
// functions: event handler
// ##
void    pcm_capture_event_handler(char *_data, int _length);


// ##
// global instance
// ##
SignalHandler			g_signal_handler;         // libsignal.so
PCM_CaptureHandler		g_pcm_capture_handler;

#endif
