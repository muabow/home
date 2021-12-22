#include <stdio.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>
#include <signal.h>

#include <iostream>
#include <thread>
#include <string> 

#include "api_audio.h"
#include "api_signal.h"
#include "api_sqlite.h"
#include "api_queue.h"
#include "api_websocket.h"
#include "api_json_parser.h"

#define	 TIME_LOOP_SLEEP		100000		// 100ms

QueueHandler		queueHandler(true);
PCM_PlaybackHandler pcmPlayback(true);


// functions 
static void ws_event_handler(const char _cmd_id, const char _is_binary, const int _length, const void *_data, WebsocketHandler *_this) {
	string uri_name = _this->get_uri_name();
	
	if( uri_name.compare("monitor_device") == 0 ) {
		if( _is_binary ) {
			queueHandler.enqueue((char *)_data, _length);	
		}

	} else if( uri_name.compare("audio_controller") == 0 ) {
		fprintf(stderr, "cmd_id: [%d], length: [%d], data: [%s]\n", _cmd_id, _length, (char *)_data);
		switch( _cmd_id ) {
			case 0x01:	//	init (connect, refresh, init)
				break;
				
			case 0x10:	//	action (play/stop)
				if( strcmp((char *)_data, "play") == 0 ) {
					if( pcmPlayback.init() ) {
						queueHandler.reset_queue_cnt();
						pcmPlayback.run();
					}
					
				} else {
					pcmPlayback.stop();
				}
				break;
			
			case 0x11:	// action (json type)
				JsonParser jsonParser(true);
				
				if( jsonParser.json_parse_to_object((char *)_data) ) {
					printf("\n%s\n\n", jsonParser.json_parse_to_string().c_str());
					
					cout << "number              : " << jsonParser.json_get_value("number") << endl;
					cout << "number/int          : " << jsonParser.json_get_value("number/int") << endl;
					cout << "number/double       : " << jsonParser.json_get_value("number/double") << endl;
					cout << "string              : " << jsonParser.json_get_value("string") << endl;
					cout << "bool/true           : " << jsonParser.json_get_value("bool/true") << endl;
					cout << "bool/false          : " << jsonParser.json_get_value("bool/false") << endl;
					cout << "array               : " << jsonParser.json_get_value("array") << endl;
					cout << "array/0             : " << jsonParser.json_get_value("array/0") << endl;
					cout << "array/1             : " << jsonParser.json_get_value("array/1") << endl;
					cout << "null                : " << jsonParser.json_get_value("null") << endl;

					cout << "json_array          : " << jsonParser.json_get_value("json_array") << endl;
					cout << "json_array/0        : " << jsonParser.json_get_value("json_array/0") << endl;
					cout << "json_array/0/int    : " << jsonParser.json_get_value("json_array/0/int") << endl;
					cout << "json_array/0/double : " << jsonParser.json_get_value("json_array/0/double") << endl;
					
					cout << "json_array/1/int    : " << jsonParser.json_get_value("json_array/1/int") << endl;
					cout << "json_array/1/double : " << jsonParser.json_get_value("json_array/1/double") << endl;
					
					cout << "json_array/2/int    : " << jsonParser.json_get_value("json_array/2/int") << endl;
					cout << "json_array/2/double : " << jsonParser.json_get_value("json_array/2/double") << endl;
				}
				
				break;
		}
	}
	
	return ;
}

static void pcm_queue_handler(short **_data, int *_length) {
	tuple<char *, int> deq = queueHandler.dequeue();
	
	*_data   = (short *)get<0>(deq);
	*_length = get<1>(deq);
	
	return ;
}

int main(void) {
	// ## signal handling
	SignalHandler sigHandler;
	sigHandler.set_signal(SIGINT);
	sigHandler.set_signal(SIGKILL);
	sigHandler.set_signal(SIGTERM);

	// ## WS handler
	// WebsocketHandler device_ws_handler("127.0.0.1",  "monitor_device", true);
	WebsocketHandler control_ws_handler("127.0.0.1", "audio_controller", true);
	
	// device_ws_handler.set_event_handler(&ws_event_handler);
	control_ws_handler.set_event_handler(&ws_event_handler);
	
	// ## sqlite3 handling
	SqlHandler sqlHandle(true);
	sqlHandle.init("monitor_device.db");
	sqlHandle.set_table("audio_pcm_info");
	
	// ## PCM driver handling
	if( !pcmPlayback.init(	sqlHandle.get_str("device_name"), 
							sqlHandle.get_int("chunk_size"), 
							sqlHandle.get_int("sample_rate"),
							sqlHandle.get_int("channels")
						 ) ) {
		
		return false;
	}
	
	pcmPlayback.get_queue_handler(&pcm_queue_handler);
	pcmPlayback.run();
	
	while( !sigHandler.is_term()  ) {
		usleep(TIME_LOOP_SLEEP);
	}

	// device_ws_handler.term();
	control_ws_handler.term();
	
	pcmPlayback.stop();
	
	return true;
}
