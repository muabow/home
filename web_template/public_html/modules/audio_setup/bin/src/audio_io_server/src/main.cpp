#include "main.h"


// ##
// functions: common
// ##
void print_debug_info(const char *_format, ...) {
	if( !g_is_debug_print ) return ;
	
	fprintf(stdout, "main::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	return ;
}


// ##
// function: event handler
// ##
void signal_event_handler(int _sig_num) {
	print_debug_info("signal_event_handler() event : [%d] %s\n", _sig_num, strsignal(_sig_num));
	
	return ;
}

void ws_send_event_handler(string _uri_name, char _cmd_id) {
	// debug print filter
	switch( _cmd_id) {
		case WS_SND_CMD_LEVEL_INFO	:
			break;
		
		default :
			print_debug_info("ws_send_event_handler() uri[%s] code[0x%02X] called\n", _uri_name.c_str(), _cmd_id);
			break;
	}

	Document doc_data;
	string str_ps_name = g_audio_handler.get_ps_name();
	string str_client_list;

	if( _uri_name.compare(str_ps_name) == 0 ) {
		Pointer("/type").Set(doc_data, to_string(_cmd_id).c_str());
		
		switch( _cmd_id ) {
			case WS_SND_CMD_ALIVE_INFO	:
				Pointer("/data/stat"				).Set(doc_data, to_string(g_audio_handler.is_alive_status(_uri_name)).c_str());
				Pointer("/data/view"				).Set(doc_data, g_audio_handler.get_database_status(_uri_name, "module_view").c_str());
				break;
				
			case WS_SND_CMD_OPER_INFO	:
				Pointer("/data/castType"			).Set(doc_data, g_audio_handler.get_database_status(_uri_name, "network_cast_type").c_str());
				Pointer("/data/ipAddr"				).Set(doc_data, g_audio_handler.get_database_status(_uri_name, "network_mcast_ip_addr").c_str());
				Pointer("/data/port"				).Set(doc_data, stoi(g_audio_handler.get_database_status(_uri_name, "network_mcast_port")));
				Pointer("/data/mp3_mode"			).Set(doc_data, (g_audio_handler.get_database_status(_uri_name, "audio_encode_type").compare("pcm") == 0 ? 0 : 1));
				Pointer("/data/mp3_quality"			).Set(doc_data, stoi(g_audio_handler.get_database_status(_uri_name, "audio_mp3_quality")));
				Pointer("/data/sampleRate"			).Set(doc_data, stoi(g_audio_handler.get_database_status(_uri_name, "audio_pcm_sample_rate")));
				Pointer("/data/channels"			).Set(doc_data, stoi(g_audio_handler.get_database_status(_uri_name, "audio_pcm_channels")));
				Pointer("/data/unicast_ip_addr"		).Set(doc_data, g_main_handler.get_network_info().c_str());
				Pointer("/data/unicast_port"		).Set(doc_data, g_audio_handler.get_database_status(_uri_name, "network_ucast_port").c_str());
				Pointer("/data/volume"				).Set(doc_data, g_audio_handler.get_database_status(_uri_name, "audio_volume").c_str());
				break;
				
			case WS_SND_CMD_CLIENT_LIST	:
				Pointer("/data/maxCount"			).Set(doc_data, g_socket_unicast_server.get_max_client_count());
				Pointer("/data/accCount"			).Set(doc_data, g_socket_unicast_server.get_accrue_count());
				Pointer("/data/connCount"			).Set(doc_data, g_socket_unicast_server.get_current_count());
				Pointer("/data/list"				).Set(doc_data, g_socket_unicast_server.get_client_list().c_str());
				break;
			
			case WS_SND_CMD_VOLUME_INFO	:
				Pointer("/data/volume"			).Set(doc_data, g_audio_handler.get_database_status(_uri_name, "audio_volume").c_str());
				Pointer("/data/type"			).Set(doc_data, _uri_name.c_str());
				break;
			
			case WS_SND_CMD_LEVEL_INFO	:
				Pointer("/data/level"			).Set(doc_data, to_string(g_audio_handler.get_level_value()).c_str());
				break;

			default :
				return ;
				break;
		}

		StringBuffer data_buffer;
		Writer<StringBuffer> data_writer(data_buffer);
		doc_data.Accept(data_writer);
		
		string json_data = data_buffer.GetString();
		data_buffer.Clear();
		
		g_ws_audio_server_handler.send(_cmd_id, json_data);
	
	} else if( _uri_name.compare("chime_ctrl") == 0 ) {
		Pointer("/type").Set(doc_data, to_string(_cmd_id).c_str());

		switch( _cmd_id ) {
			case WS_SND_CMD_CHIME_CONNECT	:
				Pointer("/data/alive_info").Set(doc_data, to_string(g_chime_handler.is_chime_play_status() == true ? 1 : 0).c_str());
				Pointer("/data/chime_index").Set(doc_data, to_string(g_chime_handler.get_chime_index()).c_str());
				break;

			case WS_SND_CMD_CHIME_MIX		:
				Pointer("/data/mix_set").Set(doc_data, to_string(get_chime_mix_status()).c_str());
				break;

			case WS_SND_CMD_CHIME_VOLUME	:
				Pointer("/data/chime_volume").Set(doc_data, to_string(g_chime_handler.get_chime_volume()).c_str());
				break;

			case WS_SND_CMD_CHIME_UPDATE	:
				Pointer("/data/update").Set(doc_data, "reload_list");
				break;

			default :
				return ;
				break;
		}

		StringBuffer data_buffer;
		Writer<StringBuffer> data_writer(data_buffer);
		doc_data.Accept(data_writer);
		
		string json_data = data_buffer.GetString();
		data_buffer.Clear();
		
		g_ws_chime_ctrl_handler.send(_cmd_id, json_data);
	
	} else if( _uri_name.compare("tts_ctrl") == 0 ) {
		Pointer("/type").Set(doc_data, to_string(_cmd_id).c_str());

		switch( _cmd_id ) {
			case WS_SND_CMD_TTS_CONNECT	:
				Pointer("/data/alive_info").Set(doc_data, to_string(g_tts_handler.is_tts_play_status() == true ? 1 : 0).c_str());
				Pointer("/data/tts_index").Set(doc_data, to_string(g_tts_handler.get_tts_index()).c_str());
				Pointer("/data/tts_file").Set(doc_data, g_tts_handler.get_tts_info().c_str());
				break;

			default :
				return ;
				break;
		}

		StringBuffer data_buffer;
		Writer<StringBuffer> data_writer(data_buffer);
		doc_data.Accept(data_writer);
		
		string json_data = data_buffer.GetString();
		data_buffer.Clear();
		
		g_ws_tts_ctrl_handler.send(_cmd_id, json_data);
	}

	return ;
}

void ws_recv_event_handler(const char _cmd_id, const char _is_binary, const int _length, const void *_data, WebsocketHandler *_this) {
	g_mutex_ws_recv_event.lock();
	
	string str_ps_name = g_audio_handler.get_ps_name();
	string str_ps_ctrl = g_audio_handler.get_ps_name().append("_control");
	
	char	cmd_id	 = _cmd_id;
	string 	uri_name = _this->get_uri_name();
	bool	is_init	 = false;
	print_debug_info("ws_recv_event_handler() uri[%s] code[0x%02X] called\n", uri_name.c_str(), _cmd_id);
	
	if( uri_name.compare(str_ps_ctrl) == 0 ) {
		uri_name = str_ps_name;
		
		switch( cmd_id ) {
			case WS_RCV_CMD_CTRL_APPLY	:
				cmd_id 	 = WS_RCV_CMD_INIT;
				g_audio_handler.update_databse_status(uri_name, "module_view", "operation");
				break;
				
			case WS_RCV_CMD_CTRL_SETUP	:
				cmd_id 	 = WS_RCV_CMD_STOP;
				g_audio_handler.update_databse_status(uri_name, "module_view", "setup");
				break;
				
			default :
				break;
		}
	}
	
	if( uri_name.compare(str_ps_name) == 0 ) {
		switch( cmd_id ) {
			case WS_RCV_CMD_CONNECT	: // connect
				if( !g_audio_handler.is_alive_status(str_ps_name) ) {
					g_audio_handler.set_level_value(0);
					ws_send_event_handler(str_ps_name, WS_SND_CMD_LEVEL_INFO);
				}

				ws_send_event_handler(uri_name, WS_SND_CMD_CLIENT_LIST);
				ws_send_event_handler(uri_name, WS_SND_CMD_OPER_INFO);
				ws_send_event_handler(uri_name, WS_SND_CMD_VOLUME_INFO);
				ws_send_event_handler(uri_name, WS_SND_CMD_ALIVE_INFO);
				break;

			case WS_RCV_CMD_INIT 	: // init & run
				if( init_audio_server(string((char *)_data)) ) {
					print_debug_info("ws_recv_event_handler() bypass init & run API \n");
					break ;
				}
				is_init = true;

				stop_server_all(true);
				stop_pcm_capture();
				
				// case non-break
				
			case WS_RCV_CMD_RUN 	: // run
				if( g_audio_handler.is_alive_status(str_ps_name) && !is_init ) {
					break;
				}

				g_audio_handler.update_databse_status(uri_name, "module_view", 	 "operation");
				g_audio_handler.update_databse_status(uri_name, "module_status", "run");
				
				if( run_pcm_capture() ) {
					g_audio_handler.set_alive_status(uri_name, true);
				
					if( g_audio_handler.is_network_cast_type(str_ps_name, "unicast") ) {
						print_debug_info("ws_recv_event_handler() [%s] run : [unicast] \n", str_ps_name.c_str());
						run_server_unicast();
						
					} else if( g_audio_handler.is_network_cast_type(str_ps_name, "multicast") ) {
						print_debug_info("ws_recv_event_handler() [%s] run : [multicast] \n", str_ps_name.c_str());
						run_server_multicast();
					
					} else if( g_audio_handler.is_network_cast_type(str_ps_name, "all") ) {
						print_debug_info("ws_recv_event_handler() [%s] run : [unicast/multicast] \n", str_ps_name.c_str());
						run_server_unicast();
						run_server_multicast();
					}
				
				} else {
					g_audio_handler.set_alive_status(uri_name, false);
				}
				
				ws_send_event_handler(uri_name, WS_SND_CMD_CLIENT_LIST);
				ws_send_event_handler(uri_name, WS_SND_CMD_OPER_INFO);
				ws_send_event_handler(uri_name, WS_SND_CMD_VOLUME_INFO);
				ws_send_event_handler(uri_name, WS_SND_CMD_ALIVE_INFO);
				break;
				
			case WS_RCV_CMD_STOP 	: // stop
				g_audio_handler.update_databse_status(uri_name, "module_status", "stop");
				g_audio_handler.set_alive_status(uri_name, false);
				
				stop_server_all();
				stop_pcm_capture();
				
				ws_send_event_handler(uri_name, WS_SND_CMD_LEVEL_INFO);
				ws_send_event_handler(uri_name, WS_SND_CMD_VOLUME_INFO);
				ws_send_event_handler(uri_name, WS_SND_CMD_CLIENT_LIST);
				ws_send_event_handler(uri_name, WS_SND_CMD_OPER_INFO);
				ws_send_event_handler(uri_name, WS_SND_CMD_ALIVE_INFO);

				break;
				
			case WS_RCV_CMD_CAPTURE_STOP	:
				print_debug_info("ws_recv_event_handler() [%s] stop alsa driver \n", str_ps_name.c_str());
				g_pcm_capture_handler.stop();
				break;
			
			case WS_RCV_CMD_CAPTURE_RUN	:
				print_debug_info("ws_recv_event_handler() [%s] run alsa driver \n", str_ps_name.c_str());
				g_pcm_capture_handler.init();
				g_pcm_capture_handler.run();
				break;

			case WS_RCV_CMD_VOLUME	:
				init_audio_server(string((char *)_data));
				ws_send_event_handler(uri_name, WS_SND_CMD_VOLUME_INFO);
				break;
				
			default :
				break;
		}
	
	} else if( uri_name.compare("chime_ctrl") == 0 ) {
		if( !g_audio_handler.is_alive_status(str_ps_name) ) {
			ws_send_event_handler(uri_name, WS_SND_CMD_CHIME_CONNECT);
			
			g_mutex_ws_recv_event.unlock();
			return ;
		}

		switch( cmd_id ) {
			case WS_RCV_CMD_CHIME_CONNECT	: // alive status
				ws_send_event_handler(uri_name, WS_SND_CMD_CHIME_CONNECT);
				break;

			case WS_RCV_CMD_CHIME_SET	: // connect
				parse_chime_ctrl(string((char *)_data));
				break;

			case WS_RCV_CMD_CHIME_MIX	: // mix set
				set_chime_mix_status(string((char *)_data));
				ws_send_event_handler(uri_name, WS_SND_CMD_CHIME_MIX);
				break;

			case WS_RCV_CMD_CHIME_VOLUME: // volume
				set_chime_volume_status(string((char *)_data));
				ws_send_event_handler(uri_name, WS_SND_CMD_CHIME_VOLUME);
				break;

			case WS_RCV_CMD_CHIME_UPDATE: // update set
				ws_send_event_handler(uri_name, WS_SND_CMD_CHIME_UPDATE);
				break;

			default :
				break;
		}
	
	} else if( uri_name.compare("tts_ctrl") == 0 ) {
		if( !g_audio_handler.is_alive_status(str_ps_name) ) {
			ws_send_event_handler(uri_name, WS_SND_CMD_CHIME_CONNECT);
			
			g_mutex_ws_recv_event.unlock();
			return ;
		}

		switch( cmd_id ) {
			case WS_RCV_CMD_CHIME_CONNECT	: // alive status
				ws_send_event_handler(uri_name, WS_SND_CMD_TTS_CONNECT);
				break;

			case WS_RCV_CMD_TTS_SET	: // connect
				parse_tts_ctrl(string((char *)_data));
				break;

			case WS_RCV_CMD_TTS_RESET_IDX : // reset index
				g_tts_handler.reset_index();
				ws_send_event_handler(uri_name, WS_SND_CMD_TTS_CONNECT);
				break;

			case WS_RCV_CMD_TTS_STOP	: // stop
				g_tts_handler.set_tts_play_status(false);
				ws_send_event_handler(uri_name, WS_SND_CMD_TTS_CONNECT);

				
				break;

			default :
				break;
		}
	}

	g_mutex_ws_recv_event.unlock();
	
	return ;
}


// 
// ## functions: chime_ctrl
// 
void parse_chime_ctrl(string _data) {
	print_debug_info("\033[33mparse_chime_ctrl() receive data[%s] \033[0m\n", _data.c_str());
	
	if( g_chime_handler.is_chime_play_status() ) {
		print_debug_info("parse_chime_ctrl() chime already running\n");
		return ;
	}

	JsonParser json_parser;
	json_parser.parse(_data);
	string str_chime_idx = json_parser.select("/chime_set");
	int chime_index = stoi(json_parser.select("/chime_idx"));

	string json_chime_info = json_parser.read_file(PATH_MODULE_CHIME_CONF);
	json_parser.parse(json_chime_info);
	
	char str_json_path[1024];
	sprintf(str_json_path, "/chime_list/%d/name", stoi(str_chime_idx.c_str()) - 1);
	string str_file_name = json_parser.select(str_json_path);
	int val_chime_mix    = stoi(json_parser.select("/mix_set"));

	size_t ext_index = str_file_name.find_last_of("."); 
	str_file_name = str_file_name.substr(0, ext_index); 

	string str_ps_name 		= g_audio_handler.get_ps_name();
	string str_sample_rate 	= g_audio_handler.get_database_status(str_ps_name, "audio_pcm_sample_rate");
	string str_channels 	= g_audio_handler.get_database_status(str_ps_name, "audio_pcm_channels");

	char str_chime_path[1024];
	sprintf(str_chime_path, "%s/%s_%s_%s.pcm", PATH_MODULE_CHIME_DATA, str_sample_rate.c_str(), str_file_name.c_str(), str_channels.c_str());
	print_debug_info("parse_chime_ctrl() chime file path : [%s]\n", str_chime_path);

	if( !g_main_handler.file_exist(str_chime_path) ) {
		print_debug_info("parse_chime_ctrl() file not exist : [%s]\n", str_chime_path);
		g_chime_handler.set_chime_play_status(false);
		return ;
	}
	print_debug_info("parse_chime_ctrl() set chime play\n");
	g_chime_handler.open(str_chime_path);
	g_chime_handler.set_chime_play_status(true);
	g_chime_handler.set_chime_index(chime_index);
	g_chime_handler.set_chime_mix_status(val_chime_mix);

	ws_send_event_handler("chime_ctrl", WS_SND_CMD_CHIME_CONNECT);

	return ;
}

void set_chime_mix_status(string _data) {
	print_debug_info("\033[33mset_chime_mix_status() receive data[%s] \033[0m\n", _data.c_str());

	JsonParser json_parser;
	json_parser.parse(_data);
	
	int val_chime_mix = stoi(json_parser.select("/mix_set"));
	g_chime_handler.set_chime_mix_status(val_chime_mix);

	return ;
}

int get_chime_mix_status(void) {
	JsonParser json_parser;
	json_parser.parse(json_parser.read_file(PATH_MODULE_CHIME_CONF));

	int val_chime_mix = stoi(json_parser.select("/mix_set"));

	return val_chime_mix;
}

void set_chime_volume_status(string _data) {
	print_debug_info("\033[33mset_chime_volume_status() receive data[%s] \033[0m\n", _data.c_str());

	JsonParser json_parser;
	json_parser.parse(_data);
	
	int val_chime_volume = stoi(json_parser.select("/chime_volume"));
	g_chime_handler.set_chime_volume(val_chime_volume);

	return ;
}

// 
// ## functions: tts_ctrl
// 
void parse_tts_ctrl(string _data) {
	print_debug_info("\033[33mparse_tts_ctrl() receive data[%s] \033[0m\n", _data.c_str());
	
	if( g_tts_handler.is_tts_play_status() ) {
		print_debug_info("parse_tts_ctrl() tts already running\n");
		return ;
	}

	JsonParser json_parser;
	json_parser.parse(_data);
	int tts_index = stoi(json_parser.select("/tts_idx"));

	string json_tts_info = json_parser.read_file(PATH_MODULE_TTS_CONF);
	json_parser.parse(json_tts_info);
	
	char str_json_path[1024];
	sprintf(str_json_path, "/tts_list/%d/file_path", tts_index);
	string str_file_name = json_parser.select(str_json_path);
	
	g_tts_handler.set_tts_info(str_file_name);

	size_t ext_index = str_file_name.find_last_of("."); 
	str_file_name = str_file_name.substr(1, ext_index -1); 


	string str_ps_name 		= g_audio_handler.get_ps_name();
	string str_sample_rate 	= g_audio_handler.get_database_status(str_ps_name, "audio_pcm_sample_rate");
	string str_channels 	= g_audio_handler.get_database_status(str_ps_name, "audio_pcm_channels");

	char str_tts_path[1024];
	sprintf(str_tts_path, "%s/%s_%s_%s.pcm", PATH_MODULE_TTS_DATA, str_sample_rate.c_str(), str_file_name.c_str(), str_channels.c_str());
	print_debug_info("parse_tts_ctrl() tts file path : [%s]\n", str_tts_path);

	if( !g_main_handler.file_exist(str_tts_path) ) {
		print_debug_info("parse_tts_ctrl() file not exist : [%s]\n", str_tts_path);
		g_tts_handler.set_tts_play_status(false);
		return ;
	}

	g_tts_handler.set_tts_file(str_tts_path);

	sprintf(str_json_path, "/tts_list/%d/chime_info/begin", tts_index);
	string str_chime_begin = json_parser.select(str_json_path);
	if( str_chime_begin.compare("") == 0 ) {
		g_tts_handler.set_tts_chime("begin", "");
	
	} else {
		sprintf(str_tts_path, "%s/%s_%s_%s.pcm", PATH_MODULE_CHIME_DATA, str_sample_rate.c_str(), str_chime_begin.c_str(), str_channels.c_str());
		print_debug_info("parse_tts_ctrl() tts chime begin file path : [%s]\n", str_tts_path);

		if( !g_main_handler.file_exist(str_tts_path) ) {
			print_debug_info("parse_tts_ctrl() chime begin file not exist : [%s]\n", str_tts_path);
			g_tts_handler.set_tts_chime("begin", "");
		
		} else {
			g_tts_handler.set_tts_chime("begin", str_tts_path);
		}
	}

	sprintf(str_json_path, "/tts_list/%d/chime_info/end", tts_index);
	string str_chime_end = json_parser.select(str_json_path);
	if( str_chime_end.compare("") == 0 ) {
		g_tts_handler.set_tts_chime("end", "");
	
	} else {
		sprintf(str_tts_path, "%s/%s_%s_%s.pcm", PATH_MODULE_CHIME_DATA, str_sample_rate.c_str(), str_chime_end.c_str(), str_channels.c_str());
		print_debug_info("parse_tts_ctrl() tts chime end file path : [%s]\n", str_tts_path);

		if( !g_main_handler.file_exist(str_tts_path) ) {
			print_debug_info("parse_tts_ctrl() chime end file not exist : [%s]\n", str_tts_path);
			g_tts_handler.set_tts_chime("end", "");
		
		} else {
			g_tts_handler.set_tts_chime("end", str_tts_path);
		}
	}

	print_debug_info("parse_tts_ctrl() set tts play\n");
	g_tts_handler.open();
	g_tts_handler.set_tts_play_status(true);
	g_tts_handler.set_tts_index(tts_index);

	ws_send_event_handler("tts_ctrl", WS_SND_CMD_TTS_CONNECT);

	return ;
}

// 
// ## functions: audio_server
// 
bool init_audio_server(string _data) {
	print_debug_info("\033[33minit_audio_server() receive data[%s] \033[0m\n", _data.c_str());
	
	string str_ps_name = g_audio_handler.get_ps_name();
	
	JsonParser json_parser;
	json_parser.parse(_data);
	
	string str_bypass = json_parser.select("/init_bypass");
	if( str_bypass.compare("true") == 0 ) {
		if( g_audio_handler.is_alive_status(str_ps_name) ) {
			return true;
		}
	} 

	g_audio_handler.update_databse_status(str_ps_name, "module_view", 			json_parser.select("/module_view"), 			"string");
	g_audio_handler.update_databse_status(str_ps_name, "module_display", 		json_parser.select("/module_display"), 			"string");
	g_audio_handler.update_databse_status(str_ps_name, "module_status", 		json_parser.select("/module_status"), 			"string");
	g_audio_handler.update_databse_status(str_ps_name, "audio_encode_type", 	json_parser.select("/audio_encode_type"), 		"string");
	g_audio_handler.update_databse_status(str_ps_name, "audio_pcm_sample_rate",	json_parser.select("/audio_pcm_sample_rate"),	"integer");
	g_audio_handler.update_databse_status(str_ps_name, "audio_pcm_channels", 	json_parser.select("/audio_pcm_channels"),		"integer");
	g_audio_handler.update_databse_status(str_ps_name, "audio_pcm_chunk_size", 	json_parser.select("/audio_pcm_chunk_size"),	"integer");
	g_audio_handler.update_databse_status(str_ps_name, "audio_mp3_quality", 	json_parser.select("/audio_mp3_quality"),		"integer");
	g_audio_handler.update_databse_status(str_ps_name, "audio_device_name", 	json_parser.select("/audio_device_name"),		"string");
	g_audio_handler.update_databse_status(str_ps_name, "network_cast_type", 	json_parser.select("/network_cast_type"),		"string");
	g_audio_handler.update_databse_status(str_ps_name, "network_client_count", 	json_parser.select("/network_client_count"),	"integer");
	g_audio_handler.update_databse_status(str_ps_name, "network_mcast_ip_addr",	json_parser.select("/network_mcast_ip_addr"),	"string");
	g_audio_handler.update_databse_status(str_ps_name, "network_mcast_port", 	json_parser.select("/network_mcast_port"),		"integer");
	g_audio_handler.update_databse_status(str_ps_name, "network_ucast_port", 	json_parser.select("/network_ucast_port"),		"integer");
	
	g_audio_handler.update_databse_status(str_ps_name, "audio_volume",			json_parser.select("/audio_volume"), 			"integer");
	string str_audio_volume = g_audio_handler.get_database_status(str_ps_name, "audio_volume");
	
	g_audio_handler.set_audio_volume(stoi(str_audio_volume));

	return false;
}

// functions: audio_server - pcm capture
bool run_pcm_capture(void) {
	string str_ps_name = g_audio_handler.get_ps_name();
	
	string device_name		= g_audio_handler.get_database_status(str_ps_name, "audio_device_name");
	int    pcm_chunk_size	= stoi(g_audio_handler.get_database_status(str_ps_name, "audio_pcm_chunk_size")); 
	int    pcm_sample_rate	= stoi(g_audio_handler.get_database_status(str_ps_name, "audio_pcm_sample_rate")); 
	int    pcm_channels		= stoi(g_audio_handler.get_database_status(str_ps_name, "audio_pcm_channels"));

	string encode_type = g_audio_handler.get_database_status(str_ps_name, "audio_encode_type"); 
	g_audio_handler.set_encode_status(encode_type);
	
	int size_pcm_periods = g_pcm_capture_handler.get_pcm_periods();
	
	if( g_audio_handler.is_encode_status() ) {
		pcm_chunk_size = SIZE_MP3_ENCODE_SET;
	}

	// reset captrue queue size
	g_queue_pcm_capture.reset_queue_unit(pcm_chunk_size);
	// playback callback data 사용 변경으로 정적 사이즈 할당
	g_queue_pcm_level.reset_queue_unit(SIZE_LEVEL_DATA);

	// reset encode buffer 
	memset(g_encoded_data, 0x00, sizeof(g_encoded_data));
	g_encoded_offset = 0;
	
	bool is_run = g_pcm_capture_handler.init(device_name, pcm_chunk_size, pcm_sample_rate, pcm_channels, size_pcm_periods);
	if( !is_run ) {
		return is_run;
	}
	print_debug_info("\033[33mrun_pcm_capture() [%s] PCM init : [%s]\033[0m\n", str_ps_name.c_str(), (is_run ? "success" : "failed"));
	
	// init mp3 encoder
	if( g_audio_handler.is_encode_status() ) {

		int mp3_vbr_quality = stoi(g_audio_handler.get_database_status(str_ps_name, "audio_mp3_quality"));
		g_mp3_encoder.init(pcm_chunk_size, pcm_sample_rate, pcm_channels, mp3_vbr_quality);
		print_debug_info("\033[33mrun_pcm_capture() [%s] MP3 init : [%s]\033[0m\n", str_ps_name.c_str(), (is_run ? "success" : "failed"));
		

	} else { 
		g_mp3_encoder.stop();
	}
	
	g_pcm_capture_handler.set_queue_handler(&pcm_capture_event_handler);
	g_pcm_capture_handler.run();
	
	return true;
}

void stop_pcm_capture(void) {
	g_pcm_capture_handler.stop();
	g_mp3_encoder.stop();
	
	return ;
}

// functions: audio_server - pcm capture event handler
void pcm_capture_level_func(void) {
	print_debug_info("pcm_capture_level_func() thread func start \n");	
	
	string str_ps_name = g_audio_handler.get_ps_name();
	
	static struct timeval t_time_begin, t_time_end;
	static bool   is_reset_time = false;

	tuple<char *, int> offset_info;
	char *ptr_data = NULL;
	int	 data_size = 0;

	int audio_volume	= 0;
	int	length			= 0;
	int	abs_value		= 0;
	int peak_value		= 0;
	
	short *arr_data = NULL;

	int elapsed_time	= 0;
	int log_value		= 0;
	int level_status	= 0;
	
	int idx;

	while( !g_sig_handler.is_term() ) {
		if( !g_audio_handler.is_alive_status(str_ps_name) ) {
			usleep(1000);
			continue;
		}

		offset_info = g_queue_pcm_level.dequeue();
		ptr_data  = get<0>(offset_info);
		data_size = get<1>(offset_info);

		if( data_size == 0 ) {
			usleep(1000);
			continue;
		}

		audio_volume	= g_audio_handler.get_audio_volume();
		length			= data_size;
		abs_value		= 0;
		peak_value		= 0;	

		arr_data = (short *)ptr_data;
		for( idx = 0 ; idx < length / 2 ; idx++ ) {
			arr_data[idx] = (int32_t)arr_data[idx] * audio_volume / 100;
			abs_value = abs(arr_data[idx]);

			if( peak_value < abs_value ) {
				peak_value = abs_value;
			}
		}
			
		if( !is_reset_time ) {
			memset(&t_time_begin, 0x00, sizeof(t_time_begin));
			gettimeofday(&t_time_begin, NULL);
			is_reset_time = true;
		}
		
		gettimeofday(&t_time_end, NULL);
		elapsed_time = (int)g_audio_handler.calc_diff_time(t_time_begin, t_time_end);
				
		if( elapsed_time > TIME_WS_LEVEL_METER ) {
			log_value = round(20 * log10((peak_value * 1.0) / (65536/2)));
			
			memset(&t_time_end, 0x00, sizeof(t_time_end));
			is_reset_time = false;
			
			level_status = 0;
			if( log_value < VOLUME_LOG_MIN || peak_value == 0 ) {
				level_status = 0;
				
			} else {
				level_status = 40 + log_value;
			}
			
			g_audio_handler.set_level_value(level_status);
			ws_send_event_handler(str_ps_name, WS_SND_CMD_LEVEL_INFO);
		}
	}

	print_debug_info("pcm_capture_level_func() thread func termed \n");	
	return ;
}

void pcm_capture_event_func(void) {
	print_debug_info("pcm_capture_event_func() thread func start \n");	
	
	string str_ps_name = g_audio_handler.get_ps_name();

	while( !g_sig_handler.is_term() ) {
		if( !g_audio_handler.is_alive_status(str_ps_name) ) {
			usleep(1000);
			continue;
		}

		tuple<char *, int> offset_info = g_queue_pcm_capture.dequeue();
		char *ptr_data = get<0>(offset_info);
		int	 data_size = get<1>(offset_info);

		if( data_size == 0 ) {
			usleep(1000);
			continue;
		}

		// SOURCE CTRL : CHIME Play
		if( g_chime_handler.is_chime_play_status() ) {
			short *mix_data = (short *)ptr_data;
			int   mix_size  = data_size / 2;
			short chime_data[mix_size] = {0x00, };

			if( !g_chime_handler.is_eof() ) {
				g_chime_handler.read(chime_data, mix_size);

				for( int idx = 0 ; idx < mix_size ; idx++ ) {
					if( g_chime_handler.is_chime_mix_status() ) {
						mix_data[idx] = (mix_data[idx] * NUM_CHIME_MIX_SCALE / 100) + (chime_data[idx] * g_chime_handler.get_chime_volume() / 100);
					
					} else {
						mix_data[idx] = chime_data[idx] * g_chime_handler.get_chime_volume() / 100;
					}
				}
				
			} else {
				g_chime_handler.close();
				g_chime_handler.set_chime_play_status(false);

				ws_send_event_handler("chime_ctrl", WS_SND_CMD_CHIME_CONNECT);
			}
		
		// SOURCE CTRL : TTS Play
		} else if( g_tts_handler.is_tts_play_status() ) {
			short *mix_data = (short *)ptr_data;
			int   mix_size  = data_size / 2;
			short tts_data[mix_size] = {0x00, };

			if( !g_tts_handler.is_eof() ) {
				g_tts_handler.read(tts_data, mix_size);

				for( int idx = 0 ; idx < mix_size ; idx++ ) {
					mix_data[idx] = tts_data[idx];
				}
				
			} else {
				g_tts_handler.close();
				g_tts_handler.set_tts_play_status(false);

				ws_send_event_handler("tts_ctrl", WS_SND_CMD_TTS_CONNECT);
			}
		}

		// volume control
		bool is_encode    = g_audio_handler.is_encode_status();
		int  audio_volume = g_audio_handler.get_audio_volume();
		
		if( audio_volume != 100 ) {
			short *arr_data = (short *)ptr_data;
			int	length      = data_size;

			for( int idx = 0 ; idx < length / 2 ; idx++ ) {
				arr_data[idx] = (int32_t)arr_data[idx] * audio_volume / 100;
			}
		}

		// MP3 encode
		if( is_encode ) {
			tuple<char *, int> encode_info = g_mp3_encoder.encode(ptr_data);
			
			char *ptr_encode = get<0>(encode_info);
			int  encode_size = get<1>(encode_info);
			
			if( encode_size == 0 ) continue ; // not enough pcm buffer or encode failed 
			
			memcpy(g_encoded_data + g_encoded_offset, ptr_encode, encode_size);
			g_encoded_offset += encode_size;
			
			// encode된 데이터 총량이 1152보다 작으면 encode 시도
			if( SIZE_MP3_FRAME_SET > g_encoded_offset ) continue ;

			g_queue_mp3_encoded.enqueue(g_encoded_data, SIZE_MP3_FRAME_SET);
			offset_info = g_queue_mp3_encoded.dequeue();
			
			ptr_data  = get<0>(offset_info);
			data_size = get<1>(offset_info);

			// send MP3 normal queue
			g_socket_unicast_server.send_data_handler(ptr_data, data_size);
			g_socket_multicast_server.send_data_handler(ptr_data, data_size);

			g_encoded_offset -= data_size;
			memcpy(g_encoded_data, g_encoded_data + data_size, g_encoded_offset);

			if( g_encoded_offset > SIZE_MP3_FRAME_SET ) {
				g_queue_mp3_encoded.enqueue(g_encoded_data, SIZE_MP3_FRAME_SET);
				offset_info = g_queue_mp3_encoded.dequeue();
				
				ptr_data  = get<0>(offset_info);
				data_size = get<1>(offset_info);

				// send MP3 over queue
				g_socket_unicast_server.send_data_handler(ptr_data, data_size);
				g_socket_multicast_server.send_data_handler(ptr_data, data_size);

				g_encoded_offset -= data_size;
				memcpy(g_encoded_data, g_encoded_data + data_size, g_encoded_offset);
			}
		
		} else {
			// send PCM
			g_socket_unicast_server.send_data_handler(ptr_data, data_size);
			g_socket_multicast_server.send_data_handler(ptr_data, data_size);
		}
	}

	print_debug_info("pcm_capture_event_func() thread func termed \n");	
	return ;
}

void pcm_capture_event_handler(char *_data, int _length) {
	g_queue_pcm_capture.enqueue(_data, _length);

	// 데이터 전체 길이에 대해 비교하지 않음.
	g_queue_pcm_level.enqueue(_data, SIZE_LEVEL_DATA);

	return ;
}

// functions: audio_server - network
void run_server_unicast(void) {
	string str_ps_name = g_audio_handler.get_ps_name();
	
	int num_server_port	= stoi(g_audio_handler.get_database_status(str_ps_name, "network_ucast_port"));
	int num_max_client	= stoi(g_audio_handler.get_database_status(str_ps_name, "network_client_count"));
	int encode_quality  = stoi(g_audio_handler.get_database_status(str_ps_name, "audio_mp3_quality"));
	
	g_audio_handler.set_network_ip_addr("");
	
	g_socket_unicast_server.set_server_port(num_server_port);
	g_socket_unicast_server.set_num_max_client(num_max_client);
	g_socket_unicast_server.set_event_handler(&network_server_event_handler);
	
	g_socket_unicast_server.init();
	g_socket_unicast_server.set_play_info(	g_pcm_capture_handler.get_sample_rate(), 
											g_pcm_capture_handler.get_channels(), 
											g_pcm_capture_handler.get_chunk_size(),
												g_audio_handler.get_database_status(str_ps_name, "audio_encode_type"),
											encode_quality);
	g_socket_unicast_server.set_frame_latency(g_pcm_capture_handler.get_frame_latency());
	g_socket_unicast_server.run();
	
	g_log_handler.info("[{STR_SETUP_AUDIO_SERVER}] {STR_SERVER_UNICAST} {STR_JS_START_AUDIO_SERVER}");
	
	return ;
}

void run_server_multicast(void) {
	string str_ps_name = g_audio_handler.get_ps_name();
	
	string	str_server_ip_addr	= g_audio_handler.get_database_status(str_ps_name, 		"network_mcast_ip_addr");
	int 	num_server_port		= stoi(g_audio_handler.get_database_status(str_ps_name, "network_mcast_port"));
	int 	encode_quality 		= stoi(g_audio_handler.get_database_status(str_ps_name, "audio_mp3_quality"));
	
	g_audio_handler.set_network_ip_addr(str_server_ip_addr);
	
	g_socket_multicast_server.set_server_port(num_server_port);
	g_socket_multicast_server.set_ip_addr(str_server_ip_addr);
	
	g_socket_multicast_server.init();
	g_socket_multicast_server.set_play_info(	g_pcm_capture_handler.get_sample_rate(), 
												g_pcm_capture_handler.get_channels(), 
												g_pcm_capture_handler.get_chunk_size(),
												g_audio_handler.get_database_status(str_ps_name, "audio_encode_type"),
												encode_quality);
	g_socket_multicast_server.set_frame_latency(g_pcm_capture_handler.get_frame_latency());
	g_socket_multicast_server.run();
	
	g_log_handler.info("[{STR_SETUP_AUDIO_SERVER}] {STR_SERVER_MULTICAST} {STR_JS_START_AUDIO_SERVER}");
	
	return ;
}

void stop_server_all(bool _is_init) {
	string str_ps_name = g_audio_handler.get_ps_name();
	
	print_debug_info("stop_server_all() %s stop : [all] \n", str_ps_name.c_str());
	
	g_socket_unicast_server.stop();
	g_socket_multicast_server.stop();

	g_audio_handler.set_level_value(0);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_LEVEL_INFO);
	
	if( !_is_init ) {
		g_log_handler.info("[{STR_SETUP_AUDIO_SERVER}] {STR_SERVER_OP_STOP}");
	}
	
	// reset chime condition
	g_chime_handler.close();
	g_chime_handler.set_chime_play_status(false);
	ws_send_event_handler("chime_ctrl", WS_SND_CMD_CHIME_CONNECT);

	// reset tts condition
	g_tts_handler.close();
	g_tts_handler.set_tts_play_status(false);
	ws_send_event_handler("tts_ctrl", WS_SND_CMD_TTS_CONNECT);
	
	return ;
}

// functions: audio_server - network event handler
void network_server_event_handler(int _index, bool _is_connect) {
	g_mutex_network_server_event.lock();
	
	string str_ps_name  = g_audio_handler.get_ps_name();
	string str_hostname = g_socket_unicast_server.get_client_info(_index, "hostname");
	string str_ip_addr  = g_socket_unicast_server.get_client_info(_index, "ip_addr");	
	
	char str_log_msg[1024];
	
	if( _is_connect ) {
		print_debug_info("network_server_event_handler() connect audio client : [%d]\n", _index);
		g_socket_unicast_server.inc_current_count();
		
		sprintf(str_log_msg, "[{STR_SETUP_AUDIO_SERVER}] [%s/%s] {STR_SERVER_CLIENT_CONNECT}", str_ip_addr.c_str(), str_hostname.c_str());
		
	} else {
		print_debug_info("network_server_event_handler() disconnect audio client : [%d]\n", _index);
		g_socket_unicast_server.dec_current_count();
		
		sprintf(str_log_msg, "[{STR_SETUP_AUDIO_SERVER}] [%s/%s] {STR_SERVER_CLIENT_DISCONNECT}", str_ip_addr.c_str(), str_hostname.c_str());
	}
	g_log_handler.info(str_log_msg);
	
	int num_current_count	= g_socket_unicast_server.get_current_count();
	int num_accure_count	= g_socket_unicast_server.get_accrue_count();
	int num_max_count		= g_socket_unicast_server.get_max_client_count();
	print_debug_info("network_server_event_handler() max[%d], accrue[%d], current[%d]\n", num_max_count, num_accure_count, num_current_count);
	
	ws_send_event_handler(str_ps_name, WS_SND_CMD_CLIENT_LIST);
	
	g_mutex_network_server_event.unlock();
	
	return ;
}



// ##
// function: main
// ##
int main(int _argc, char *_argv[]) {
	int opt;
	while( (opt = getopt(_argc, _argv, "v")) != -1 ) {
		switch( opt ) {
			case 'v' :
				g_is_debug_print = true;
				print_debug_info("main() set print debug\n");
				
				g_sig_handler.set_debug_print();
				g_main_handler.set_debug_print();
				g_audio_handler.set_debug_print();
				g_chime_handler.set_debug_print();
				
				g_ws_audio_control_handler.set_debug_print();
				g_ws_audio_server_handler.set_debug_print();
				g_ws_chime_ctrl_handler.set_debug_print();
				g_tts_handler.set_debug_print();
				
				g_pcm_capture_handler.set_debug_print();
				g_mp3_encoder.set_debug_print();
				
				g_queue_pcm_capture.set_debug_print("queue_pcm_capture");
				g_queue_pcm_level.set_debug_print("queue_pcm_level");
				g_queue_mp3_encoded.set_debug_print("queue_mp3_encoded");
				
				g_socket_unicast_server.set_debug_print();
				g_socket_multicast_server.set_debug_print();
				
				break;
				
			default :
				printf("usage: %s [option]\n", basename(_argv[0]));
				printf("  -v : print normal debug message \n");
				return -1;
				
				break;
		}
	}
	
	// signal handler
	g_sig_handler.set_signal(SIGINT);
	g_sig_handler.set_signal(SIGKILL);
	g_sig_handler.set_signal(SIGTERM);
	g_sig_handler.set_ignore(SIGPIPE);
	g_sig_handler.set_signal_handler(&signal_event_handler);
	
	
	// init audio instance
	g_audio_handler.set_database_path(PATH_MODULE_DB_FILE);
	g_audio_handler.set_ps_name(basename(_argv[0]));
	
	string str_ps_name = g_audio_handler.get_ps_name();
	string str_ps_ctrl = g_audio_handler.get_ps_name().append("_control");
	
	if( !g_audio_handler.is_module_use(str_ps_name) ) {
		print_debug_info("main() module [%s] disabled\n", str_ps_name.c_str());
		
		while( !g_sig_handler.is_term() ) {
			sleep(TIME_WS_CHECK_LOOP);
		}
		
		print_debug_info("main() process has been terminated.\n");
		
		return 0;
	}
	
	// init default chunk size
	g_audio_handler.update_databse_status(str_ps_name, "audio_pcm_chunk_size", 4096);

	// add extended colume : audio_volume
	if( !g_audio_handler.is_exist_db_colume(str_ps_name, "audio_volume") ) {
		g_audio_handler.add_db_colume(str_ps_name, "audio_volume", "INTEGER");
		g_audio_handler.update_databse_status(str_ps_name, "audio_volume", 100);
	
	} else {
		g_audio_handler.query(str_ps_name, "update audio_server set audio_volume = 100 where audio_volume is null;");
	}

	// init pcm audio info
	string str_audio_volume = g_audio_handler.get_database_status(str_ps_name, "audio_volume");
	if( str_audio_volume.compare("") == 0 ) {
		g_audio_handler.update_databse_status(str_ps_name, "audio_volume", 100);
	}
	g_audio_handler.set_audio_volume(stoi(str_audio_volume));

	
	// init websocket handler : audio_server_control
	if( !g_ws_audio_control_handler.init("127.0.0.1", str_ps_ctrl) ) {
		print_debug_info("main() websocket interface connection failed, restart process\n");
		return -1;
	}
	g_ws_audio_control_handler.set_route_to(WebsocketHandler::WEB_ONLY);
	g_ws_audio_control_handler.set_event_handler(&ws_recv_event_handler);
	
	g_ws_audio_server_handler.init("127.0.0.1", str_ps_name);
	g_ws_audio_server_handler.set_route_to(WebsocketHandler::WEB_ONLY);
	g_ws_audio_server_handler.set_event_handler(&ws_recv_event_handler);
	
	g_ws_chime_ctrl_handler.init("127.0.0.1", "chime_ctrl");
	g_ws_chime_ctrl_handler.set_route_to(WebsocketHandler::WEB_ONLY);
	g_ws_chime_ctrl_handler.set_event_handler(&ws_recv_event_handler);
	
	g_ws_tts_ctrl_handler.init("127.0.0.1", "tts_ctrl");
	g_ws_tts_ctrl_handler.set_route_to(WebsocketHandler::WEB_ONLY);
	g_ws_tts_ctrl_handler.set_event_handler(&ws_recv_event_handler);

	// init pcm capture thread
	g_thread_pcm_capture_func = thread(&pcm_capture_event_func);	

	// init pcm volume thread
	g_thread_pcm_level_func = thread(&pcm_capture_level_func);	

	// init chime info
	JsonParser json_parser;
	string json_chime_info = json_parser.read_file(PATH_MODULE_CHIME_CONF);
	json_parser.parse(json_chime_info);
	int val_chime_volume  = stoi(json_parser.select("/chime_volume"));
	int val_chime_mix     = stoi(json_parser.select("/mix_set"));
	g_chime_handler.set_chime_volume(val_chime_volume);
	g_chime_handler.set_chime_mix_status(val_chime_mix);

	// init [pcm capture] queue handler
	g_queue_pcm_capture.init();
	g_queue_pcm_capture.set_min_dequeue_cnt(NUM_MIN_DEQUEUE_CNT);

	// init [pcm volume] queue handler
	g_queue_pcm_level.init();
	g_queue_pcm_level.set_min_dequeue_cnt(NUM_MIN_DEQUEUE_CNT);

	// init [mp3 encoded] queue handler
	g_queue_mp3_encoded.init();
	g_queue_mp3_encoded.reset_queue_unit(SIZE_MP3_FRAME_SET);
	g_queue_mp3_encoded.set_min_dequeue_cnt(NUM_MIN_DEQUEUE_CNT);

	if( !g_audio_handler.is_module_status(str_ps_name) ) {
		g_audio_handler.set_alive_status(str_ps_name, false);
		
		g_audio_handler.set_level_value(0);
		ws_send_event_handler(str_ps_name, WS_SND_CMD_LEVEL_INFO);

	} else {
		if( run_pcm_capture() ) {
			g_audio_handler.set_alive_status(str_ps_name, true);
			
			
			if( g_audio_handler.is_network_cast_type(str_ps_name, "unicast") ) {
				print_debug_info("main() [%s] run : [unicast] \n", str_ps_name.c_str());
				run_server_unicast();
				
			} else if( g_audio_handler.is_network_cast_type(str_ps_name, "multicast") ) {
				print_debug_info("main() [%s] run : [multicast] \n", str_ps_name.c_str());
				run_server_multicast();
				
			} else if( g_audio_handler.is_network_cast_type(str_ps_name, "all") ) {
				print_debug_info("main() [%s] run : [unicast/multicast] \n", str_ps_name.c_str());
				run_server_unicast();
				run_server_multicast();
			}

		} else {
			g_audio_handler.set_alive_status(str_ps_name, false);
		}
	}
	
	// noti init status 
	ws_send_event_handler(str_ps_name, WS_SND_CMD_CLIENT_LIST);		
	ws_send_event_handler(str_ps_name, WS_SND_CMD_OPER_INFO);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_ALIVE_INFO);
	
	ws_send_event_handler("chime_ctrl", WS_SND_CMD_CHIME_CONNECT);
	ws_send_event_handler("tts_ctrl",   WS_SND_CMD_TTS_CONNECT);


	while( !g_sig_handler.is_term() ) {
		sleep(TIME_WS_CHECK_LOOP);
		
		if( g_ws_audio_control_handler.is_term() ) {
			g_ws_audio_control_handler.reconnect();
		}
		
		if( g_ws_audio_server_handler.is_term() ) {
			g_ws_audio_server_handler.reconnect();
		}

		if( g_ws_chime_ctrl_handler.is_term() ) {
			g_ws_chime_ctrl_handler.reconnect();
		}

		if( g_ws_tts_ctrl_handler.is_term() ) {
			g_ws_tts_ctrl_handler.reconnect();
		}
	}
	
	// noti term status
	stop_server_all();
	stop_pcm_capture();
	
	if( g_thread_pcm_capture_func.joinable() ) {
		print_debug_info("main() join & wait pcm capture thread func term\n");
		g_thread_pcm_capture_func.join();
	}

	if( g_thread_pcm_level_func.joinable() ) {
		print_debug_info("main() join & wait pcm level thread func term\n");
		g_thread_pcm_level_func.join();
	}

	g_queue_pcm_capture.free();
	g_queue_pcm_level.free();
	g_queue_mp3_encoded.free();
	
	g_audio_handler.set_alive_status(str_ps_name, false);
	g_audio_handler.set_level_value(0);
	
	ws_send_event_handler(str_ps_name, WS_SND_CMD_LEVEL_INFO);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_VOLUME_INFO);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_CLIENT_LIST);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_ALIVE_INFO);
	
	print_debug_info("main() process has been terminated.\n");

	return 0;
}
