#ifndef __MAIN_H__
#define __MAIN_H__

#include <fstream>
#include <iostream>
#include <sstream>
#include <string>
#include <mutex>
#include <vector>
#include <tuple>
#include <exception>
#include <stdexcept>

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdarg.h>
#include <unistd.h>
#include <getopt.h>
#include <sys/time.h>
#include <math.h>


#include "api_signal.h"
#include "api_sqlite.h"
#include "api_websocket.h"
#include "api_json_parser.h"
#include "api_queue.h"
#include "api_web_log.h"
#include "api_mp3_decode.h"

#include "class_main.h"
#include "class_audio.h"
#include "class_socket_server.h"


#define	PATH_SOURCE_DIR						"/opt/interm/public_html/modules/source_file_management/html/data/audiofiles/"
#define	PATH_MODULE_DB_FILE					"/opt/interm/public_html/modules/source_file_setup/conf/source_file_io.db"
#define PATH_EXT_CONFIG						"/opt/interm/conf/config-external-storage.json"

#define NUM_QUEUE_SCALE						10
#define NUM_QUEUE_SIZE						1152
#define NUM_MIN_DEQUEUE_CNT					0

#define TIME_WS_CHECK_LOOP					1			// sec

#define WS_RCV_CMD_CONNECT					0x01
#define WS_RCV_CMD_INIT						0x02
#define WS_RCV_CMD_RUN						0x03
#define WS_RCV_CMD_STOP						0x04

#define WS_RCV_CMD_SVR_SETUP				0x30
#define WS_RCV_CMD_SVR_APPLY				0x31


#define WS_RCV_CMD_CTRL_PLAY				0x10
#define WS_RCV_CMD_CTRL_PAUSE				0x11
#define WS_RCV_CMD_CTRL_STOP				0x12
#define WS_RCV_CMD_CTRL_PREV				0x13
#define WS_RCV_CMD_CTRL_NEXT				0x14
#define WS_RCV_CMD_CTRL_LOOP				0x15
#define WS_RCV_CMD_CTRL_REMOVE				0x16
#define WS_RCV_CMD_CTRL_SORT				0x17
#define WS_RCV_CMD_CTRL_VOLUME				0x18
#define WS_RCV_CMD_CTRL_FORCE_STOP			0x19
#define WS_RCV_CMD_CTRL_RELOAD				0x20
#define WS_RCV_CMD_CTRL_PLAY_INDEX			0x21
#define WS_RCV_CMD_CTRL_SOURCE_NAME_LIST	0x22
#define WS_RCV_CMD_CTRL_API_PLAY_SINGLE		0x23
#define WS_RCV_CMD_CTRL_API_PLAY_ALL		0x24
#define WS_RCV_CMD_CTRL_UPLOAD				0x25

#define WS_RCV_CMD_SYNC_RELOAD				0x21


// to WEB (string type)
#define WS_SND_CMD_ALIVE_INFO				0x01
#define WS_SND_CMD_OPER_INFO				0x02
#define WS_SND_CMD_CLIENT_LIST				0x03

#define WS_SND_CMD_SOURCE_LIST				0x10
#define WS_SND_CMD_SOURCE_NAME_LIST			0x11
#define WS_SND_CMD_SOURCE_OPER_INFO			0x12
#define WS_SND_CMD_SOURCE_STOP_STATUS		0x13

#define WS_SND_CMD_CTRL_SORT				0x17
#define WS_SND_CMD_CTRL_STATUS				0x20

#define WS_SND_CMD_DISPLAY_LOAD 			0x26
#define WS_SND_CMD_DISPLAY_CLEAR 			0x27


using namespace std;

struct HEADER_INFO {
	char		status;			// 1  // stop, play, pause
	char		encode_type;    // 2  // pcm, mp3
	short		data_size;     	// 4  // 1152
	int			sample_rate;   	// 8  // 44100
	short		channels;     	// 10 // 2
	short		mp3_quality;	// 12 // 2
} typedef HEADER_INFO_t;

// ##
// global variables
// ##
bool	g_is_debug_print 	= false;
bool	g_is_first_execute	= false;

mutex	g_mutex_ws_recv_event;
mutex	g_mutex_ws_send_event;
mutex	g_mutex_network_server_event;

thread	g_thread_playback;

// ##
// functions: common
// ##
void	print_debug_info(const char *_format, ...);

bool 	init_server(string _data);
void	run_server_unicast(void);
void 	run_server_multicast(void);
void 	stop_server_all(bool _is_init = false);

void 	network_server_event_handler(int _index, bool _is_connect);

void 	parse_audio_play_data(string _data);
int		parse_audio_play_index(string _data);
void 	set_audio_player_status(string _type, bool _status = false);
void	set_audio_player_loop(bool _status);
void	listup_source_list(char *_data);
int		listup_source_name_single(char *_data);
void 	listup_source_name_all(void);

int		get_prev_play_index(vector<AUDIO_SourceInfo> _v_src_list, int _idx);
void	execute_playback_source(void);
void	playback_source_list(void);
void	sort_source_list(char *_data);
void	remove_source_list(char *_data);
void 	add_extend_db_colume_integer(string _table_name, string _colume_name, int _dflt_value);

// ##
// functions: event handler
// ##
void	signal_event_handler(void);

void	ws_send_event_handler(string _uri_name, char _cmd_id);
void	ws_recv_event_handler(const char _cmd_id, const char _is_binary, const int _length, const void *_data, WebsocketHandler *_this);
void	ws_recv_source_handle(string _uri_name, int _cmd_id, const void *_data);

// ##
// global instance
// ##
SignalHandler			g_sig_handler;
WebLogHandler 			g_log_handler("source_file_setup");

MAIN_Handler			g_main_handler;
AUDIO_PlayerHandler		g_player_handler;
AUDIO_SourceHandler		g_source_handler;
AUDIO_PlaybackHandler	g_playback_handler;

WebsocketHandler		g_ws_server_handler;
WebsocketHandler		g_ws_control_handler;
WebsocketHandler		g_ws_source_ctrl_handler;

SOCKET_UnicastServer	g_socket_unicast_server;
SOCKET_MulticastServer	g_socket_multicast_server;

QueueHandler			g_queue_pcm_capture;
#endif