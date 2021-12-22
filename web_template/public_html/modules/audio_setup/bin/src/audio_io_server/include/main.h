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

#include "api_signal.h"
#include "api_sqlite.h"
#include "api_websocket.h"
#include "api_json_parser.h"
#include "api_queue.h"

#include "api_pcm_capture.h"
#include "api_socket_server.h"
#include "api_mp3_encode.h"
#include "api_web_log.h"

#include "class_main.h"
#include "class_audio.h"


#define PATH_MODULE_DB_FILE			"/opt/interm/public_html/modules/audio_setup/conf/audio_io.db"
#define PATH_MODULE_CHIME_DATA      "/opt/interm/public_html/modules/chime_file_management/html/data/audiofiles"
#define PATH_MODULE_CHIME_CONF      "/opt/interm/public_html/modules/chime_file_management/conf/chime_info.json"
#define PATH_MODULE_TTS_DATA        "/opt/interm/public_html/modules/file_management/html/data/audiofiles"
#define PATH_MODULE_TTS_CONF        "/opt/interm/public_html/modules/file_management/conf/tts_info.json"

#define	SIZE_TEMP_DATA				1024 * 8
#define SIZE_LEVEL_DATA				128
#define SIZE_MP3_ENCODE_SET			4608
#define SIZE_MP3_FRAME_SET			1152

#define TIME_WS_CHECK_LOOP			1			// sec
#define TIME_WS_LEVEL_METER			100000		// 100ms 

#define VOLUME_LOG_MIN				-40

#define NUM_MIN_DEQUEUE_CNT			0
#define NUM_CHIME_MIX_SCALE         30          // %

// from WEB/UI/API
#define WS_RCV_CMD_CONNECT			0x01
#define WS_RCV_CMD_INIT				0x10
#define WS_RCV_CMD_RUN				0x11
#define WS_RCV_CMD_STOP				0x12
#define WS_RCV_CMD_CAPTURE_STOP		0x13
#define WS_RCV_CMD_CAPTURE_RUN		0x14
#define WS_RCV_CMD_VOLUME			0x15

#define WS_RCV_CMD_CTRL_SETUP		0x20
#define WS_RCV_CMD_CTRL_APPLY		0x21

// from chime_ctrl
#define WS_RCV_CMD_CHIME_CONNECT	0x00
#define WS_RCV_CMD_CHIME_SET		0x01
#define WS_RCV_CMD_CHIME_MIX		0x02
#define WS_RCV_CMD_CHIME_VOLUME		0x03
#define WS_RCV_CMD_CHIME_UPDATE		0x04

#define WS_SND_CMD_CHIME_CONNECT	0x00
#define WS_SND_CMD_CHIME_MIX		0x02
#define WS_SND_CMD_CHIME_VOLUME		0x03
#define WS_SND_CMD_CHIME_UPDATE		0x04

// from tts_ctrl
#define WS_RCV_CMD_TTS_CONNECT	    0x00
#define WS_RCV_CMD_TTS_SET	    	0x01
#define WS_RCV_CMD_TTS_RESET_IDX   	0x02
#define WS_RCV_CMD_TTS_STOP	    	0x10

#define WS_SND_CMD_TTS_CONNECT	    0x00
#define WS_SND_CMD_TTS_RESET_IDX   	0x02

// to WEB (string type)
#define WS_SND_CMD_ALIVE_INFO		1
#define WS_SND_CMD_OPER_INFO		10
#define WS_SND_CMD_VOLUME_INFO		11
#define WS_SND_CMD_LEVEL_INFO		12
#define WS_SND_CMD_CLIENT_LIST		21

// ISD callback cmd info
#define ISD_RCV_CMD_INIT			0x10
#define ISD_RCV_CMD_RUN				0x11
#define ISD_RCV_CMD_STOP			0x12
#define ISD_RCV_CMD_VOLUME			0x13

// SND interface cmd info
#define WS_CMD_SNDIF_PLAYBACK		0x02

using namespace std;

// ##
// global variables
// ##
bool	g_is_debug_print	= false;

mutex	g_mutex_ws_recv_event;
mutex	g_mutex_network_server_event;

char	g_encoded_data[SIZE_MP3_ENCODE_SET] = {0x00, };
int		g_encoded_offset = 0;
int     g_encoded_length = 0;

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
// functions: chime_ctrl
// ##
void 	parse_chime_ctrl(string _data);
void 	set_chime_mix_status(string _data);
int  	get_chime_mix_status(void);
void 	set_chime_volume_status(string _data);

// ##
// functions: tts_ctrl
// ##
void 	parse_tts_ctrl(string _data);

// ##
// functions: audio_server
// ##
bool	init_audio_server(string _data);

// functions: audio_server - pcm capture
bool	run_pcm_capture(void);
void	stop_pcm_capture(void);

// functions: audio_server - pcm captrue event handler
void	pcm_capture_volume_func(void);
void	pcm_capture_event_func(void);
void	pcm_capture_event_handler(char *_data, int _length);

// functions: audio_server - network
void	run_server_unicast(void);
void	run_server_multicast(void);
void 	stop_server_all(bool _is_init = false);

// functions: audio_server - network event handler
void 	network_server_event_handler(int _index, bool _is_connect);

// ##
// global instance
// ##
SignalHandler			g_sig_handler;
MAIN_Handler			g_main_handler;
AUDIO_Handler			g_audio_handler;
CHIME_Handler           g_chime_handler;
TTS_Handler             g_tts_handler;

WebsocketHandler		g_ws_audio_control_handler;
WebsocketHandler		g_ws_audio_server_handler;
WebsocketHandler		g_ws_chime_ctrl_handler;
WebsocketHandler		g_ws_tts_ctrl_handler;

PCM_CaptureHandler		g_pcm_capture_handler;
MP3_Encoder				g_mp3_encoder;

QueueHandler			g_queue_pcm_capture;
QueueHandler			g_queue_pcm_level;
QueueHandler			g_queue_mp3_encoded;

SOCKET_MulticastServer	g_socket_multicast_server;
SOCKET_UnicastServer	g_socket_unicast_server;

WebLogHandler 			g_log_handler("audio_setup");

thread 					g_thread_pcm_capture_func;
thread 					g_thread_pcm_level_func;

#endif
