#ifndef __MAIN_H__
#define __MAIN_H__

#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <stdbool.h>
#include <time.h>
#include <unistd.h>
#include <dlfcn.h>
#include <stdarg.h>
#include <getopt.h>
#include <errno.h>

#include <iostream>
#include <string>
#include <algorithm>
#include <vector>

#include "vtapi.h"
#include "slicense.h"
#include "api_json_parser.h"


using namespace std;

#define PATH_VTAPI_LIBRARY			"/opt/interm/usr/lib"
#define ENV_DFLT_OUTPUT_PATH		"/tmp/tts_output.wav"
#define ENV_USER_KEYWORD			"interM"

struct ATTR_INFO {
	int 	pitch;				// % (50 ~ 200)
	int 	speed;				// % (50 ~ 400)
	int		volume;				// % (0  ~ 500)
	int		sentence_pause;		// msec (0 ~ 65536)
    int     dict_idx;           // index(0 ~ 1023)
	int		comma_pause;		// msec (0 ~ 65536)
    int     parent_hesisnum;    // 0 ~ n
    int     emphasis_factor;    // -n > 0 < n
} typedef ATTR_INFO_t;


// ##
// global variables
// ##
bool g_is_debug_print = false;

vector<string> g_list_attr_name{"ATTR_PITCH", 
                                "ATTR_SPEED", 
                                "ATTR_VOLUME", 
                                "ATTR_PAUSE", 
                                "ATTR_DICTIDX", 
                                "ATTR_COMMAPAUSE", 
                                "ATTR_PARENTHESISNUM", 
                                "ATTR_EMPHASISFACTOR"
                                };
#endif