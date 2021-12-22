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

#include "api_mute_relay.h"
#include "api_pcm_playback.h"
#include "api_socket_client.h"
#include "api_web_log.h"

#include "class_main.h"
#include "class_audio.h"


#define	PATH_MODULE_DB_FILE			"/opt/interm/public_html/modules/source_file_setup/conf/source_file_io.db"

#define	SIZE_TEMP_DATA				1024 * 8

#define TIME_WS_CHECK_LOOP			1			// sec
#define TIME_SET_MUTE_RELAY			100000		// 500ms
#define TIME_WS_LEVEL_METER			500000		// 1000ms -> 500ms

#define VOLUME_LOG_MIN				-40
#define VOLUME_LOG_MAX				0

#define NUM_QUEUE_SCALE				10
#define NUM_QUEUE_SIZE				1152
#define NUM_MIN_DEQUEUE_CNT			0


// from WEB/UI/API
#define WS_RCV_CMD_CONNECT			0x01
#define WS_RCV_CMD_INIT				0x10
#define WS_RCV_CMD_RUN				0x11
#define WS_RCV_CMD_STOP				0x12
#define WS_RCV_CMD_VOLUME			0x13

#define WS_RCV_CMD_CTRL_SETUP		0x20
#define WS_RCV_CMD_CTRL_APPLY		0x21

// to WEB (string type)
#define WS_SND_CMD_ALIVE_INFO		1
#define WS_SND_CMD_OPER_INFO		10
#define WS_SND_CMD_VOLUME_INFO		11
#define WS_SND_CMD_LEVEL_INFO		12

#define	NETWORK_CONNECT_NORNAL		0
#define NETWORK_CONNECT_FAILED		1

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
bool	g_is_debug_print = false;

mutex	g_mutex_ws_recv_event;
mutex	g_mutex_ws_send_event;
mutex	g_mutex_network_client_event;
mutex	g_mutex_network_data_event;


// ##
// functions: common
// ##
void	print_debug_info(const char *_format, ...);


// ##
// functions: event handler
// ##
void	signal_event_handler(void);
void 	ws_send_event_handler(string _uri_name, char _cmd_id);
void	ws_recv_event_handler(const char _cmd_id, const char _is_binary, const int _length, const void *_data, WebsocketHandler *_this);


// ##
// functions: audio_client
// ##
void	init_audio_client(string _data);

// functions: audio_client - pcm playback
void	run_pcm_playback(int _chunk_size, int _sample_rate, int _channels);
void	stop_pcm_playback(void);

// functions: audio_client - pcm playback event handler
void	pcm_playback_event_handler(char **_data, int *_length);
void 	pcm_playback_control_handler(char **_data, int *_length);
	
// functions: audio_client - network
void	init_client_unicast(void);
void	run_client_unicast(void);

void	init_client_multicast(void);
void	run_client_multicast(void);

void 	stop_client_all(void);

// functions: audio_client - network event handler
void 	network_client_connect_handler(string _type, bool _is_connect, int _connect_case);
void	network_client_data_handler(char *_data, int _length);

void 	playback_mute_handler(bool _status);
void 	playback_term_handler(void);


// ##
// global instance
// ##
SignalHandler			g_sig_handler;
MAIN_Handler			g_main_handler;
AUDIO_Handler			g_audio_handler;

WebsocketHandler		g_ws_audio_control_handler;
WebsocketHandler		g_ws_audio_client_handler;

PCM_PlaybackHandler		g_pcm_playback_handler;

IPC_msgQueueFunc 		g_mute_relay_handler;

QueueHandler			g_queue_pcm_playback;

SOCKET_MulticastClient	g_socket_multicast_client;
SOCKET_UnicastClient	g_socket_unicast_client;

WebLogHandler 			g_log_handler("source_file_setup");

#endif