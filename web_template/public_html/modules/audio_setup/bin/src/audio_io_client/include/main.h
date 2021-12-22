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
#include <sys/resource.h>

#include "api_signal.h"
#include "api_sqlite.h"
#include "api_websocket.h"
#include "api_json_parser.h"
#include "api_queue.h"

#include "api_mute_relay.h"
#include "api_pcm_playback.h"
#include "api_socket_client.h"
#include "api_mp3_decode.h"
#include "api_web_log.h"

#include "class_main.h"
#include "class_audio.h"

#define PATH_MODULE_DB_FILE			"/opt/interm/public_html/modules/audio_setup/conf/audio_io.db"

#define	SIZE_TEMP_DATA				1024 * 8
#define SIZE_LEVEL_DATA				128

#define TIME_WS_CHECK_LOOP			1			// sec
#define TIME_SET_MUTE_RELAY			500000		// 500ms
#define TIME_WS_LEVEL_METER			500000		// 1000ms -> 500ms
#define TIME_SND_DEVICE_LOOP		100000		// 100ms
#define	TIME_DB_LOCK_WAIT			10000		// 10ms

#define VOLUME_LOG_MIN				-40
#define VOLUME_LOG_MAX				0

#define NUM_QUEUE_SCALE				10
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

// to audio_server
#define WS_RCV_CMD_CAPTURE_STOP		0x13
#define WS_RCV_CMD_CAPTURE_RUN		0x14


using namespace std;

// ##
// global variables
// ##
bool	g_is_debug_print  = false;

mutex	g_mutex_ws_recv_event;
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
void	run_pcm_playback(void);
void	stop_pcm_playback(void);

// functions: audio_client - pcm playback event handler
void	pcm_playback_volume_handler(char **_data, int _length);
void 	pcm_playback_level_handler(char *_data, int _length);
void 	pcm_playback_level_func(void);
void 	pcm_playback_control_handler(void);
	
// functions: audio_client - network
void	init_client_unicast(void);
void	run_client_unicast(void);

void	init_client_multicast(void);
void	run_client_multicast(void);

void 	stop_client_all(void);

// functions: audio_client - network event handler
void 	network_client_connect_handler(string _type, bool _is_connect, int _connect_case, int *_status);
void 	network_client_data_func(void);
void	network_client_data_handler(char *_data, int _length);

void 	decode_hip_report_handler(const char *_format, va_list _args);

// ##
// global instance
// ##
SignalHandler			g_sig_handler;
MAIN_Handler			g_main_handler;
AUDIO_Handler			g_audio_handler;

WebsocketHandler		g_ws_audio_control_handler;
WebsocketHandler		g_ws_audio_client_handler;
WebsocketHandler		g_ws_audio_server_handler;

PCM_PlaybackHandler		g_pcm_playback_handler;
MP3_Decoder				g_mp3_decoder;

IPC_msgQueueFunc 		g_mute_relay_handler;

QueueHandler			g_queue_rcv_data;
QueueHandler			g_queue_pcm_level;
QueueHandler			g_queue_pcm_playback;

SOCKET_MulticastClient	g_socket_multicast_client;
SOCKET_UnicastClient	g_socket_unicast_client;

WebLogHandler 			g_log_handler("audio_setup");

thread 					g_thread_client_data_func;
thread 					g_thread_pcm_level_func;

#endif
