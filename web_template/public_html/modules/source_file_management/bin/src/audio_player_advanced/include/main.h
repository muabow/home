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
#include <sys/inotify.h>

#include <avmsapi/utility/SystemUtil.h>

#include "api_signal.h"
#include "api_sqlite.h"
#include "api_websocket.h"
#include "api_json_parser.h"
#include "api_queue.h"

#include "api_mute_relay.h"
#include "api_pcm_playback.h"
#include "api_mp3_decode.h"
#include "api_web_log.h"

#include "class_main.h"
#include "class_audio.h"


#define PATH_MODULE_DB_FILE				"/opt/interm/public_html/modules/source_file_management/conf/source_file_management.db"
#define PATH_SOURCE_DIR					"/opt/interm/public_html/modules/source_file_management/html/data/audiofiles/"
#define PATH_EXT_CONFIG					"/opt/interm/conf/config-external-storage.json"

#define	SIZE_TEMP_DATA					1024 * 8
#define SIZE_CHUNK_DATA					1024

#define TIME_WS_CHECK_LOOP				1			// sec
#define TIME_WAIT_ALSA_HANDLER			100000		// 100ms
#define TIME_SET_MUTE_RELAY				100000		// 500ms
#define TIME_WS_LEVEL_METER				500000		// 1000ms -> 500ms

#define VOLUME_LOG_MIN					-40
#define VOLUME_LOG_MAX					0

#define NUM_MIN_DEQUEUE_CNT				0

#define INTY_MAX_EVENTS					1024                                                    /* Max. number of events to process at one go */
#define INTY_LEN_NAME					16                                                      /* Assuming that the length of the filename won't exceed 16 bytes */
#define INTY_EVENT_SIZE					(sizeof (struct inotify_event))                         /* size of one event */
#define INTY_BUF_SIZE					(INTY_MAX_EVENTS * (INTY_EVENT_SIZE + INTY_LEN_NAME))   /* buffer to store the data of events */

#define TIME_READ_SEC					1
#define TIME_READ_MSEC					0


// from WEB/UI/API
#define WS_RCV_CMD_CONNECT				0x01

#define WS_RCV_CMD_CTRL_PLAY			0x10
#define WS_RCV_CMD_CTRL_PAUSE			0x11
#define WS_RCV_CMD_CTRL_STOP			0x12
#define WS_RCV_CMD_CTRL_PREV			0x13
#define WS_RCV_CMD_CTRL_NEXT			0x14
#define WS_RCV_CMD_CTRL_LOOP			0x15
#define WS_RCV_CMD_CTRL_REMOVE			0x16
#define WS_RCV_CMD_CTRL_SORT			0x17
#define WS_RCV_CMD_CTRL_VOLUME			0x18
#define WS_RCV_CMD_CTRL_FORCE_STOP		0x19
#define WS_RCV_CMD_CTRL_RELOAD			0x20
#define WS_RCV_CMD_CTRL_PLAY_INDEX		0x21
#define WS_RCV_CMD_SOURCE_NAME_LIST		0x22
#define WS_RCV_CMD_CTRL_API_PLAY_SINGLE	0x23
#define WS_RCV_CMD_CTRL_API_PLAY_ALL	0x24
#define WS_RCV_CMD_CTRL_UPLOAD			0x25
#define WS_RCV_CMD_CTRL_INOTY_IGN		0x30


// to WEB (string type)
#define WS_SND_CMD_ALIVE_INFO			1
#define WS_SND_CMD_VOLUME_INFO			11
#define WS_SND_CMD_LEVEL_INFO			12

#define WS_SND_CMD_OPER_INFO			0x20
#define WS_SND_CMD_SOURCE_LIST			0x21
#define WS_SND_CMD_SOURCE_NAME_LIST		0x22
#define WS_SND_CMD_STOP_STATUS			0x23
#define WS_SND_CMD_SOURCE_UPLOAD		0x25
#define WS_SND_CMD_DISPLAY_LOAD 		0x26
#define WS_SND_CMD_DISPLAY_CLEAR 		0x27


using namespace std;

// ##
// global variables
// ##
bool	g_is_debug_print 	= false;
bool	g_is_first_execute	= false;
bool	g_is_act_file_io	= false;

thread	g_thread_playback;

mutex	g_mutex_ws_recv_event;
mutex	g_mutex_ws_send_event;


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

void 	set_audio_player_status(string _type, bool _status = false);
void 	set_audio_player_loop(bool _status);
void 	parse_audio_play_data(string _data);
int		parse_audio_play_index(string _data);

// functions: audio_client - pcm playback event handler
void	pcm_playback_event_handler(char **_data, int *_length, bool _is_encode, int _peak_volume);
void 	pcm_playback_control_handler(char **_data, int *_length);
void	pcm_playback_mute_handler(void);
void 	pcm_playback_error_handler(void);
	
void	playback_source_list(void);
void	remove_source_list(char *_data);
void	listup_source_list(char *_data);
int		listup_source_name_single(char *_data);
bool	listup_source_name_all(void);
void	sort_source_list(char *_data);
void 	add_extend_db_colume_integer(string _table_name, string _colume_name, int _dflt_value);

int 	func_daemon_mode(string _basename);
int 	func_stand_mode(string _filepath, string _device_name, int _volume);


// ##
// global instance
// ##
SignalHandler			g_sig_handler;
MAIN_Handler			g_main_handler;
AUDIO_Handler			g_audio_handler;
AUDIO_PlayerHandler		g_player_handler;
AUDIO_SourceHandler		g_source_handler;

WebsocketHandler		g_ws_audio_control_handler;
WebsocketHandler		g_ws_audio_player_handler;

PCM_PlaybackHandler		g_pcm_playback_handler;

IPC_msgQueueFunc 		g_mute_relay_handler;

WebLogHandler 			g_log_handler("source_file_management");

#endif