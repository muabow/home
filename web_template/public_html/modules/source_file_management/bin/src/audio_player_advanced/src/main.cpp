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
	
	g_player_handler.set_player_control("is_force_stop", true);
	g_pcm_playback_handler.stop();
	
	return ;
}

void ws_send_event_handler(string _uri_name, char _cmd_id) {
	g_mutex_ws_send_event.lock();
	
	string str_ps_name = g_audio_handler.get_ps_name();
	string str_ps_ctrl = g_audio_handler.get_ps_name().append("_control");

	Document doc_data;
	
	if( !g_audio_handler.is_module_use(str_ps_name) ) {
		print_debug_info("ws_send_event_handler() module [%s] disabled\n", str_ps_name.c_str());
		g_mutex_ws_send_event.unlock();
		
		return ;
	}

	// debug print filter
	switch( _cmd_id) {
		case WS_SND_CMD_LEVEL_INFO : 
			break;
		
		default :
			print_debug_info("ws_send_event_handler() uri[%s] code[0x%02X] called\n", _uri_name.c_str(), _cmd_id);
			break;
	}

	if( _uri_name.compare(str_ps_name) == 0 ) {
		Pointer("/type").Set(doc_data, to_string(_cmd_id).c_str());
		
		switch( _cmd_id ) {
			case WS_SND_CMD_ALIVE_INFO	:
				Pointer("/data/stat"			).Set(doc_data, g_player_handler.get_player_status("is_run"));
				break;
		
			case WS_SND_CMD_SOURCE_LIST	:
				Pointer("/data/source_list"		).Set(doc_data, g_source_handler.make_json_source_list().c_str());
				break;
				
			case WS_SND_CMD_SOURCE_NAME_LIST	:
				Pointer("/data/source_name_list").Set(doc_data, g_source_handler.make_json_source_name_list().c_str());
				break;
			
			case WS_SND_CMD_SOURCE_UPLOAD	:
				Pointer("/data/source_list"		).Set(doc_data, g_source_handler.make_json_source_list().c_str());
				break;
				
			case WS_SND_CMD_OPER_INFO	:
				Pointer("/data/is_run"			).Set(doc_data, g_player_handler.get_player_status("is_run"));
				Pointer("/data/is_play"			).Set(doc_data, g_player_handler.get_player_status("is_play"));
				Pointer("/data/is_pause"		).Set(doc_data, g_player_handler.get_player_status("is_pause"));
				Pointer("/data/is_loop"			).Set(doc_data, g_player_handler.get_player_status("is_loop"));
				Pointer("/data/audio_play_index").Set(doc_data, g_player_handler.get_player_index());
				break;
				
			case WS_SND_CMD_VOLUME_INFO :
				Pointer("/data/playVolume"		).Set(doc_data, to_string(g_player_handler.get_player_volume()).c_str());
				Pointer("/data/type"			).Set(doc_data, _uri_name.c_str());
				break;
			
			case WS_SND_CMD_LEVEL_INFO	:
				Pointer("/data/level"			).Set(doc_data, to_string(g_audio_handler.get_level_value()).c_str());
				break;
				
			case WS_SND_CMD_STOP_STATUS	:
				Pointer("/data/stat"			).Set(doc_data, g_player_handler.get_player_status("is_run"));
				break;

			case WS_SND_CMD_DISPLAY_LOAD	:
				Pointer("/data/display"			).Set(doc_data, "load");
				break;

			case WS_SND_CMD_DISPLAY_CLEAR	:
				Pointer("/data/display"			).Set(doc_data, "clear");
				break;
							
			default :
				g_mutex_ws_send_event.unlock();
				return ;
				break;
		}
		StringBuffer data_buffer;
		Writer<StringBuffer> data_writer(data_buffer);
		doc_data.Accept(data_writer);
		
		string json_data = data_buffer.GetString();
		g_ws_audio_player_handler.send(_cmd_id, json_data);
	
	} else if( _uri_name.compare(str_ps_ctrl) == 0 ) {
		Pointer("/type").Set(doc_data, to_string(_cmd_id).c_str());
		
		switch( _cmd_id ) {
			case WS_SND_CMD_SOURCE_NAME_LIST	:
				Pointer("/data/source_name_list").Set(doc_data, g_source_handler.make_json_source_name_list().c_str());
				break;

			case WS_SND_CMD_SOURCE_UPLOAD	:
				Pointer("/data/source_list"		).Set(doc_data, g_source_handler.make_json_source_list().c_str());
				break;

			default :
				g_mutex_ws_send_event.unlock();
				return ;
				break;
		}
		StringBuffer data_buffer;
		Writer<StringBuffer> data_writer(data_buffer);
		doc_data.Accept(data_writer);
		
		string json_data = data_buffer.GetString();
		g_ws_audio_control_handler.send(_cmd_id, json_data);
	}
		
	g_mutex_ws_send_event.unlock();
	
	return ;
}

void ws_recv_event_handler(const char _cmd_id, const char _is_binary, const int _length, const void *_data, WebsocketHandler *_this) {
	g_mutex_ws_recv_event.lock();
	
	string str_ps_name = g_audio_handler.get_ps_name();
	string str_ps_ctrl = g_audio_handler.get_ps_name().append("_control");

	if( !g_audio_handler.is_module_use(str_ps_name) ) {
		print_debug_info("ws_recv_event_handler() module [%s] disabled\n", str_ps_name.c_str());
		g_mutex_ws_recv_event.unlock();
		
		return ;
	}
	
	char	cmd_id	 = _cmd_id;
	string 	uri_name = _this->get_uri_name();
	print_debug_info("ws_recv_event_handler() uri[%s] code[0x%02X] called\n", uri_name.c_str(), _cmd_id);
	
	if( uri_name.compare(str_ps_ctrl) == 0 ) {
		switch( cmd_id ) {
			// API ONLY
			case WS_RCV_CMD_SOURCE_NAME_LIST : 
				ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_NAME_LIST);
				g_mutex_ws_recv_event.unlock();
				return ;

				break;
			
			default :
				break;
		}

		uri_name = str_ps_name;
	}

	bool is_run		= g_player_handler.get_player_status("is_run"); 
	bool is_play	= g_player_handler.get_player_status("is_play");
	bool is_pause	= g_player_handler.get_player_status("is_pause");
	int  idx = 0;
	
	print_debug_info("ws_recv_event_handler() status : is_run[%d], is_play[%d], is_pause[%d]\n", is_run, is_play, is_pause);
	
	if( uri_name.compare(str_ps_name) == 0 ) {
		switch( cmd_id ) {
			case WS_RCV_CMD_CONNECT : 
				ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_LIST);
				ws_send_event_handler(uri_name, WS_SND_CMD_OPER_INFO);
				ws_send_event_handler(uri_name, WS_SND_CMD_VOLUME_INFO);
				
				break;
			
				
			case WS_RCV_CMD_CTRL_PLAY	:
				// before status : play
				if( is_run && is_play && !is_pause ) {
					break;
				}

				// before status : pause
				if( is_run && is_play && is_pause ) {
					set_audio_player_status("is_pause", false);
					
					g_pcm_playback_handler.set_playback_play();
					
					ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_LIST);
					ws_send_event_handler(uri_name, WS_SND_CMD_OPER_INFO);
					break;
				}
				
				// before status : stop
				if( !is_run ) {
					set_audio_player_status("is_run",   true);
					set_audio_player_status("is_play",  true);
					set_audio_player_status("is_pause", false);
					
					listup_source_list((char *)_data);

					playback_source_list();
					break;
				}
				
				break;
			
				
			case WS_RCV_CMD_CTRL_PAUSE	:
				// before status : play
				if( is_run && is_play && !is_pause ) {
					set_audio_player_status("is_run",   true);
					set_audio_player_status("is_play",  true);
					set_audio_player_status("is_pause", true);
					
					g_pcm_playback_handler.set_playback_pause();
					
					ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_LIST);
					ws_send_event_handler(uri_name, WS_SND_CMD_OPER_INFO);
					break;
				}
				
				// before status : pause
				if( is_run && is_play && is_pause ) {
					break;
				}
				
				// before status : stop
				if( !is_run ) {
					break;
				}
				
				break;
			
				
			case WS_RCV_CMD_CTRL_FORCE_STOP	:
				g_player_handler.set_player_control("is_force_stop", true);
				g_player_handler.set_player_control("is_play_stop",  true);
				
			case WS_RCV_CMD_CTRL_STOP	:
				// before status : stop
				if( !is_run ) {
					ws_send_event_handler(uri_name, WS_SND_CMD_STOP_STATUS);
					break;
				}
				
				// before status : play & pause
				if( is_run && is_play  ) {
					set_audio_player_status("is_run",   false);
					set_audio_player_status("is_play",  false);
					set_audio_player_status("is_pause", false);
					
					g_player_handler.set_player_control("is_play_stop", true);
					g_pcm_playback_handler.stop();
					break;
				}
				
				break;
			
				
			case WS_RCV_CMD_CTRL_PREV	:
				// before status : stop
				if( !is_run ) {
					break;
				}
				
				g_player_handler.set_player_control("is_play_prev", true);
				
				if( is_pause ) {
					set_audio_player_status("is_pause", false);
					g_pcm_playback_handler.set_playback_play();
				}
				g_pcm_playback_handler.stop();
								
				break;
			
				
			case WS_RCV_CMD_CTRL_NEXT	:
				// before status : stop
				if( !is_run ) {
					break;
				}
				
				g_player_handler.set_player_control("is_play_next", true);

				if( is_pause ) {
					set_audio_player_status("is_pause", false);
					g_pcm_playback_handler.set_playback_play();
				}
				g_pcm_playback_handler.stop();
				
				break;
			
				
			case WS_RCV_CMD_CTRL_LOOP	:
				set_audio_player_status("is_loop");
				
				ws_send_event_handler(uri_name, WS_SND_CMD_OPER_INFO);
				
				break;
			
				
			case WS_RCV_CMD_CTRL_REMOVE	:
				g_is_act_file_io = true;

				set_audio_player_status("is_run",   false);
				set_audio_player_status("is_play",  false);
				set_audio_player_status("is_pause", false);

				g_player_handler.set_player_control("is_force_stop", true);
				g_player_handler.set_player_control("is_play_stop",  true);
								
				// before status : play & pause
				if( is_run && is_play ) {
					g_pcm_playback_handler.stop();
				}
				
				remove_source_list((char *)_data);
				
				ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_LIST);
				
				g_is_act_file_io = false;

				break;
			
				
			case WS_RCV_CMD_CTRL_SORT	:
				set_audio_player_status("is_run",   false);
				set_audio_player_status("is_play",  false);
				set_audio_player_status("is_pause", false);

				g_player_handler.set_player_control("is_play_stop", true);
								
				// before status : play & pause
				if( is_run && is_play ) {
					g_pcm_playback_handler.stop();
				}
				
				sort_source_list((char *)_data);
				
				ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_LIST);

				break;
				
				
			case WS_RCV_CMD_CTRL_VOLUME	:
				parse_audio_play_data(string((char *)_data));
				
				ws_send_event_handler(uri_name, WS_SND_CMD_VOLUME_INFO);
				
				break;
				
				
			case WS_RCV_CMD_CTRL_RELOAD :
				ws_send_event_handler(str_ps_name, WS_SND_CMD_DISPLAY_LOAD);

				set_audio_player_status("is_run",   false);
				set_audio_player_status("is_play",  false);
				set_audio_player_status("is_pause", false);

				g_player_handler.set_player_control("is_play_stop", true);
								
				// before status : play & pause
				if( is_run && is_play ) {
					g_pcm_playback_handler.stop();
				}
				
				g_source_handler.read_source_list();
				
				ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_LIST);
				
				break;

			case WS_RCV_CMD_CTRL_UPLOAD :
				set_audio_player_status("is_run",   false);
				set_audio_player_status("is_play",  false);
				set_audio_player_status("is_pause", false);

				g_player_handler.set_player_control("is_play_stop", true);
								
				// before status : play & pause
				if( is_run && is_play ) {
					g_pcm_playback_handler.stop();
				}
				
				g_source_handler.read_source_list();
				
				ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_UPLOAD);

				g_is_act_file_io = false;
				break;
				
				
			case WS_RCV_CMD_CTRL_PLAY_INDEX :
				idx = parse_audio_play_index((char *)_data);
							
				g_player_handler.set_player_index(idx);
				g_player_handler.set_player_control("is_change_index", true);
				g_source_handler.update_source_info(idx, "is_play", true);
				
				// before status : play
				if( is_run && is_play && !is_pause ) {
					g_pcm_playback_handler.stop();
					break;
				}
				
				// before status : pause
				if( is_run && is_play && is_pause ) {
					set_audio_player_status("is_pause", false);

					g_pcm_playback_handler.set_playback_play();
					g_pcm_playback_handler.stop();
					break;
				}

				// before status : stop
				if( !is_run ) {
					set_audio_player_status("is_run",   true);
					set_audio_player_status("is_play",  true);
					set_audio_player_status("is_pause", false);
					
					playback_source_list();
					break;
				}
				
				break;
			
			case WS_RCV_CMD_SOURCE_NAME_LIST : 
				ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_NAME_LIST);
				
				break;
			
			
			case WS_RCV_CMD_CTRL_API_PLAY_SINGLE	:
				idx = listup_source_name_single((char *)_data);
				if( idx == -1 ) {
					print_debug_info("ws_recv_event_handler() case [WS_RCV_CMD_CTRL_API_PLAY_SINGLE] invalid data\n");
					break;
				}
							
				g_player_handler.set_player_index(idx);
				g_player_handler.set_player_control("is_change_index", true);
				g_source_handler.update_source_info(idx, "is_play", true);
				
				// before status : play
				if( is_run && is_play && !is_pause ) {
					g_player_handler.set_player_control("is_play_stop", true);
					g_pcm_playback_handler.stop();
					break;
				}

				// before status : pause
				if( is_run && is_play && is_pause ) {
					set_audio_player_status("is_pause", false);

					g_player_handler.set_player_control("is_play_stop", true);
					g_pcm_playback_handler.stop();
					break;
				}
				
				// before status : stop
				if( !is_run ) {
					set_audio_player_status("is_run",   true);
					set_audio_player_status("is_play",  true);
					set_audio_player_status("is_pause", false);

					playback_source_list();
					break;
				}
				
			case WS_RCV_CMD_CTRL_API_PLAY_ALL	:
				if( !listup_source_name_all() ) {
					print_debug_info("ws_recv_event_handler() case [WS_RCV_CMD_CTRL_API_PLAY_ALL] empty playlist\n");
					break;
				}
				
				g_player_handler.set_player_index(0);
				g_player_handler.set_player_control("is_change_index", true);
				g_source_handler.update_source_info(0, "is_play", true);
				
				// before status : play
				if( is_run && is_play && !is_pause ) {
					g_player_handler.set_player_control("is_play_stop", true);
					g_pcm_playback_handler.stop();
					break;
				}
	
				// before status : pause
				if( is_run && is_play && is_pause ) {
					set_audio_player_status("is_pause", false);
	
					g_player_handler.set_player_control("is_play_stop", true);
					g_pcm_playback_handler.stop();
					break;
				}
				
				// before status : stop
				if( !is_run ) {
					set_audio_player_status("is_run",   true);
					set_audio_player_status("is_play",  true);
					set_audio_player_status("is_pause", false);
	
					playback_source_list();
					break;
				}
				
				break;
			
			case WS_RCV_CMD_CTRL_INOTY_IGN :
				g_is_act_file_io = true;
				break;

			default :
				break;
		}
	}
	
	g_mutex_ws_recv_event.unlock();
	
	return ;
}

void set_audio_player_status(string _type, bool _status) {
	string str_ps_name	= g_audio_handler.get_ps_name();
	string str_status 	= (_status ? "1" : "0");
	
	if( _type.compare("is_loop") == 0 ) {
		// toggle loop status
		int is_loop = g_player_handler.get_player_status("is_loop");
		str_status  = (is_loop == 0 ? "1" : "0");
	}
	
	g_audio_handler.update_databse_status(str_ps_name, _type, str_status, "integer");
	g_player_handler.set_player_status(_type, stoi(str_status));
	
	return ;
}

void set_audio_player_loop(bool _status) {
	string str_ps_name 	= g_audio_handler.get_ps_name();
	string str_status 	= (_status ? "1" : "0");
	string type			= "is_loop";
	
	g_audio_handler.update_databse_status(str_ps_name, type, str_status, "integer");
	g_player_handler.set_player_status(type, stoi(str_status));
	
	return ;
}

void parse_audio_play_data(string _data) {
	print_debug_info("\033[33mparse_audio_play_data() receive data[%s] \033[0m\n", _data.c_str());
	
	string str_ps_name = g_audio_handler.get_ps_name();
	
	JsonParser json_parser;
	json_parser.parse(_data);
	
	g_audio_handler.update_databse_status(str_ps_name, "is_run",		 			json_parser.select("/is_run"),	 				"integer");
	g_audio_handler.update_databse_status(str_ps_name, "is_play",					json_parser.select("/is_play"), 				"integer");
	g_audio_handler.update_databse_status(str_ps_name, "is_pause",					json_parser.select("/is_pause"), 				"integer");
	g_audio_handler.update_databse_status(str_ps_name, "is_loop",					json_parser.select("/is_loop"), 				"integer");
	g_audio_handler.update_databse_status(str_ps_name, "audio_volume",				json_parser.select("/audio_volume"),			"integer");

	string str_audio_volume = g_audio_handler.get_database_status(str_ps_name, "audio_volume");
	g_player_handler.set_player_volume(stoi(str_audio_volume));
	
	int volume = stoi(str_audio_volume);
	g_audio_handler.set_audio_volume(volume);

	if( g_audio_handler.is_amp_device() ) {
		volume = 100;
	}
	g_pcm_playback_handler.set_decode_volume(volume);
	
	return ;
}

int	parse_audio_play_index(string _data) {
	JsonParser json_parser;
	json_parser.parse(_data);
	
	int play_index = stoi(json_parser.select("/play_index"));
	
	return play_index;
}


// functions: pcm playback event handler
void pcm_playback_event_handler(char **_data, int *_length, bool _is_encode, int _peak_volume) {
	static struct timeval t_time_begin, t_time_end;
	static bool   is_reset_time = false;

	string str_ps_name = g_audio_handler.get_ps_name();
	
	short *ptr_data = (short *)*_data;
	int	 length     = *_length;
	int	abs_value  = 0;
	int peak_value = 0;
		
	int  audio_volume	= g_audio_handler.get_audio_volume();
	bool is_amp_deivce	= g_audio_handler.is_amp_device(); 
	
	// change volume
	if( ptr_data != NULL ) {
		if( !_is_encode ) {
			for( int idx = 0 ; idx < length / 2 ; idx++ ) {
				if( is_amp_deivce ) {
					abs_value = abs((int32_t)ptr_data[idx] * audio_volume / 100);
					
				} else {
					ptr_data[idx] = (int32_t)ptr_data[idx] * audio_volume / 100;
					abs_value = abs(ptr_data[idx]);
				}
							
				if( peak_value < abs_value ) {
					peak_value = abs_value;
				}
			}
			
		} else {
			_peak_volume = abs((int32_t)_peak_volume * audio_volume / 100);
			
			if( peak_value < _peak_volume ) {
				peak_value = _peak_volume;
			}
		}
		
		if( !is_reset_time ) {
			memset(&t_time_begin, 0x00, sizeof(t_time_begin));
			gettimeofday(&t_time_begin, NULL);
			is_reset_time = true;
		}
		
		gettimeofday(&t_time_end, NULL);
		int elapsed_time = (int)g_audio_handler.calc_diff_time(t_time_begin, t_time_end);
				
		if( elapsed_time > TIME_WS_LEVEL_METER ) {
			int log_value = round(20 * log10((peak_value * 1.0) / (65536/2)));
			
			memset(&t_time_end, 0x00, sizeof(t_time_end));
			is_reset_time = false;
			
			int level_status = 0;
			
			if( log_value < VOLUME_LOG_MIN || peak_value == 0 ) {
				level_status = 0;
				
			} else {
				switch( log_value ) {
					case -4  ...  0		: level_status = 10;	break;
					case -8  ... -5		: level_status = 9;		break;
					case -12 ... -9		: level_status = 8;		break;
					case -16 ... -13	: level_status = 7;		break;
					case -20 ... -17	: level_status = 6;		break;
					case -24 ... -21	: level_status = 5;		break;
					case -28 ... -25	: level_status = 4;		break;
					case -32 ... -29	: level_status = 3;		break;
					case -36 ... -33	: level_status = 2;		break;
					case -40 ... -37	: level_status = 1;		break;
				}
			}
			
			g_audio_handler.set_level_value(level_status);
			ws_send_event_handler(str_ps_name, WS_SND_CMD_LEVEL_INFO);
		}
	}
	
	return ;
}

void pcm_playback_control_handler(char **_data, int *_length) {
	static bool   is_reset_mute_time = false;
	static struct timeval t_mute_time_begin, t_mute_time_end;
	
	// control mute relay
	int muteTime;
	
	if( !g_mute_relay_handler.is_unmute() ) {
		if( !is_reset_mute_time ) {
			memset(&t_mute_time_begin, 0x00, sizeof(t_mute_time_begin));
			gettimeofday(&t_mute_time_begin, NULL);
			is_reset_mute_time = true;
		}

		gettimeofday(&t_mute_time_end, NULL);
		muteTime = (int)g_audio_handler.calc_diff_time(t_mute_time_begin, t_mute_time_end);
		
		if( muteTime >= TIME_SET_MUTE_RELAY ) {
			print_debug_info("pcm_playback_control_handler() unmute, time elapsed : %d us \n", muteTime);
			memset(&t_mute_time_end, 0x00, sizeof(t_mute_time_end));

			g_mute_relay_handler.increase_audio_mute();
		}
		
	} else {
		is_reset_mute_time = false;
	}
	
	return ;
}

void pcm_playback_mute_handler(void) {
	print_debug_info("pcm_playback_mute_handler() set mute\n");
	
	while( g_mute_relay_handler.is_unmute() ) {
		g_mute_relay_handler.decrease_audio_mute();
		usleep(TIME_SET_MUTE_RELAY);
	}
	
	return ;
}

void pcm_playback_error_handler(void) {
	print_debug_info("pcm_playback_error_handler() set error\n");

	int index = g_player_handler.get_player_index();
	
	g_source_handler.update_source_info(index, "is_valid_source", false);
	g_player_handler.set_player_control("is_invalid_source", true);
	
	return ;
}

int get_prev_play_index(vector<AUDIO_SourceInfo> _v_src_list, int _idx) {
	bool 	is_exist_prev	= false;
	int		num_src_list	= (int)_v_src_list.size();
	int		current_idx		= _idx;
	int		prev_idx 		= 0;

	while( !g_sig_handler.is_term() ) {
		if( current_idx == 0 ) {
			if( g_player_handler.get_player_status("is_loop") ) {
				for( prev_idx = (num_src_list - 1) ; prev_idx > 0 ; prev_idx-- ) {
					if( _v_src_list[prev_idx].get_source_status("is_playlist") ) {
						is_exist_prev = true;
						break;
					}
				}
				if( !is_exist_prev ) {
					prev_idx = 0;
				}
				
			} else {
				prev_idx = 0;
			}
					
		} else {
			for( prev_idx = (current_idx - 1) ; prev_idx >= 0 ; prev_idx-- ) {
				if( _v_src_list[prev_idx].get_source_status("is_playlist") ) {
					is_exist_prev = true;
					break;
				}
			}
			
			if( !is_exist_prev ) {
				prev_idx = 0;
			}
		}
		
		if( _v_src_list[prev_idx].get_source_status("is_valid_source") ) {
			break;
		
		} else {
			current_idx = prev_idx;
		}
	}
	
	return (prev_idx - 1);
}

void execute_playback_source(void) {
	bool is_run		= g_player_handler.get_player_status("is_run"); 
	bool is_play	= g_player_handler.get_player_status("is_play");
	bool is_pause	= g_player_handler.get_player_status("is_pause");

	print_debug_info("execute_playback_source() status : is_run[%d], is_play[%d], is_pause[%d]\n", is_run, is_play, is_pause);
	
	bool	is_exist_playlist 	= false;

	string str_ps_name 		= g_audio_handler.get_ps_name();
	string str_device_name	= g_audio_handler.get_database_status(str_ps_name, "audio_device_name");
	vector<AUDIO_SourceInfo> v_src_list = g_source_handler.get_source_list();
	
	if( g_player_handler.get_player_status("is_run") && g_player_handler.get_player_status("is_play") ) {
		int num_src_list = (int)v_src_list.size();
		
		while( !g_sig_handler.is_term() && g_player_handler.get_player_status("is_play") ) {
			for( int idx = 0 ; idx < num_src_list ; idx++ ) {
				
				// 프로세스 종료 시 or play 정지 시 loop break
				if( g_sig_handler.is_term() || !g_player_handler.get_player_status("is_play") ) {
					break;
				}

				if( g_player_handler.get_player_control("is_change_index") ) {
					g_player_handler.set_player_control("is_change_index", false);
					
					v_src_list = g_source_handler.get_source_list();
					num_src_list = (int)v_src_list.size();
					
					idx = g_player_handler.get_player_index();

				} else {
					// 프로세스 시작 시 최초 1회 실행, 플레이 리스트를 순회하여 마지막 상태가 재생인 음원 검색
					if( !g_is_first_execute ) {
						g_is_first_execute = true;
						
						for( int cidx = 0 ; cidx < num_src_list ; cidx++ ) {
							if( v_src_list[cidx].get_source_status("is_play") ) {
								idx = cidx;
								break;
							}
						}
					}
				}
				
				// 플레이리스트가 아니라면 목록 순회
				if( !v_src_list[idx].get_source_status("is_playlist") ) {
					continue;
				}
				
				// 재생불가한 음원이라면 목록 순회
				if( !v_src_list[idx].get_source_status("is_valid_source") ) {
					continue;
				}
				is_exist_playlist = true;
				
				print_debug_info("execute_playback_source() audio source type : [%s]\n", v_src_list[idx].get_source_info("source_type").c_str());
				
				g_player_handler.set_player_index(idx);
				g_source_handler.update_source_info(idx, "is_play", true);
				
				ws_send_event_handler(str_ps_name, WS_SND_CMD_SOURCE_LIST);
				ws_send_event_handler(str_ps_name, WS_SND_CMD_OPER_INFO);
				ws_send_event_handler(str_ps_name, WS_SND_CMD_ALIVE_INFO);
				
				int loop_count = v_src_list[idx].get_play_info("audio_loop_count");
				
				// 단일 음원 반복 재생
				for( int loop_idx = 0 ; loop_idx < loop_count ; loop_idx++ ) {
					g_pcm_playback_handler.init(str_device_name, 
												SIZE_CHUNK_DATA, 
												v_src_list[idx].get_play_info("num_sample_rate"), 
												v_src_list[idx].get_play_info("num_channels"),  
												v_src_list[idx].get_play_info("num_mp3_skip_bytes"),
												v_src_list[idx].get_play_info("num_audio_format"),
												v_src_list[idx].get_play_info("num_bits_per_sample"),
												v_src_list[idx].get_play_info("num_end_skip_bytes"));
					
					g_pcm_playback_handler.run(v_src_list[idx].get_source_info("source_file_path"));
					
					if( g_player_handler.get_player_control("is_invalid_source") ) { 
						g_player_handler.set_player_control("is_invalid_source", false);
						
						v_src_list = g_source_handler.get_source_list();
					}
	
					// 이전/다음/정지/중지 일 때 일시정지 해제
					if( 	g_player_handler.get_player_control("is_play_prev") 
						|| 	g_player_handler.get_player_control("is_play_next")
						||	g_player_handler.get_player_control("is_play_stop") 
						||	g_player_handler.get_player_control("is_force_stop") ) {
						
						g_source_handler.update_source_info(idx, "is_pause", false);
						g_pcm_playback_handler.set_playback_play();
						break;
					}
				}
				
				// 다음곡/정지는 다음곡 넘어갈때마다 상태 초기화
				g_player_handler.set_player_control("is_play_next", false);
				g_player_handler.set_player_control("is_play_stop", false);
				
				// 종료 시: 마지막 재생 유지, 정지 시: 마지막 재생 초기화
				if( g_player_handler.get_player_control("is_force_stop") ) {
					g_player_handler.set_player_control("is_force_stop", false);
				
				} else {
					g_source_handler.update_source_info(idx, "is_play",  false);
				}
				
				// 이전곡 재생 시
				if( g_player_handler.get_player_control("is_play_prev") ) {
					g_player_handler.set_player_control("is_play_prev", false);
					
					idx = get_prev_play_index(v_src_list, idx);
				}
				
				if( g_player_handler.get_player_control("is_change_index") ) {
					idx = 0;
				}
			}
			
			// 종료 및 반복 재생이 아닌 경우 정지 상태 진입
			// case: 정지, 반복 재생이 아닐 때 마지막 음원 재생 후
			if( !g_sig_handler.is_term() && !g_player_handler.get_player_status("is_loop") ) {
				// status : stop
				set_audio_player_status("is_run",   false);				
				set_audio_player_status("is_play",  false);
				set_audio_player_status("is_pause", false);
				
				ws_send_event_handler(str_ps_name, WS_SND_CMD_SOURCE_LIST);
				ws_send_event_handler(str_ps_name, WS_SND_CMD_OPER_INFO);
				ws_send_event_handler(str_ps_name, WS_SND_CMD_ALIVE_INFO);
				
				break;
			}
			
			// 플레이 리스트가 없는 경우 정지 상태 진입
			// case: 재생 가능한 음원이 없을 때
			if( !is_exist_playlist ) {
				// status : stop
				set_audio_player_status("is_run",   false);				
				set_audio_player_status("is_play",  false);
				set_audio_player_status("is_pause", false);
				
				ws_send_event_handler(str_ps_name, WS_SND_CMD_SOURCE_LIST);
				ws_send_event_handler(str_ps_name, WS_SND_CMD_OPER_INFO);
				ws_send_event_handler(str_ps_name, WS_SND_CMD_ALIVE_INFO);
				
				break;
			}
		}
	}

	g_audio_handler.set_level_value(0);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_LEVEL_INFO);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_SOURCE_LIST);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_OPER_INFO);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_ALIVE_INFO);
	
		
	print_debug_info("execute_playback_source() termed\n");
	ws_send_event_handler(str_ps_name, WS_SND_CMD_STOP_STATUS);
	
	return ;
}

void playback_source_list(void) {
	print_debug_info("playback_source_list() thread create\n");
	g_thread_playback = thread(&execute_playback_source);
	g_thread_playback.detach();
	
	return ;
}

void remove_source_list(char *_data) {
	print_debug_info("remove_source_list() recv data : [%s]\n", _data);
	
	if( _data == NULL ) {
		return ;
	}
	
	JsonParser json_parser;
	json_parser.parse(_data);
	
	string	str_hash_id 	= json_parser.select("/source_hash_id");
	int		num_source_list = stoi(json_parser.select("/num_source_list"));

	if( num_source_list == 0 ) {
		return ;
	}
	
	vector<string> v_hash_list = json_parser.split(str_hash_id, ",");
	
	for( int idx = 0 ; idx < (int)v_hash_list.size() ; idx++ ) {
		g_source_handler.remove_source_list(v_hash_list[idx]);
	}
	
	return ;
}

void listup_source_list(char *_data) {
	print_debug_info("listup_source_list() recv data : [%s]\n", _data);
	
	if( _data == NULL ) {
		return ;
	}
	
	JsonParser json_parser;
	json_parser.parse(_data);
	
	string	str_hash_id 	= json_parser.select("/source_hash_id");
	string	str_loop_count 	= json_parser.select("/source_loop_count");
	int		num_source_list = stoi(json_parser.select("/num_source_list"));

	if( num_source_list == 0 ) {
		return ;
	}
	
	vector<string> v_hash_list	= json_parser.split(str_hash_id, ",");
	vector<string> v_loop_count = json_parser.split(str_loop_count, ",");
	vector<AUDIO_SourceInfo> v_src_list = g_source_handler.get_source_list();

	for( int idx = 0 ; idx < (int)v_src_list.size() ; idx++ ) {
		g_source_handler.listup_source_info(v_src_list[idx].get_source_info("source_hash_id"), false, -1);
	}
	
	for( int idx = 0 ; idx < (int)v_hash_list.size() ; idx++ ) {
		g_source_handler.listup_source_info(v_hash_list[idx], true, stoi(v_loop_count[idx]));
	}
	
	return ;
}

int listup_source_name_single(char *_data) {
	print_debug_info("listup_source_name_single() recv data : [%s]\n", _data);
	
	if( _data == NULL ) {
		return -1;
	}
	
	JsonParser json_parser;
	json_parser.parse(_data);
	
	string	str_source_name	= json_parser.select("/source_name");
	string	str_loop_count 	= json_parser.select("/source_loop_count");
	
	if( str_source_name.compare("") == 0 || str_loop_count.compare("") == 0 ) {
		return -1;
	}

	int loop_count = stoi(str_loop_count);
	
	if( loop_count == 0 ) {
		set_audio_player_loop(true);
		loop_count = 1;
		
	} else {
		set_audio_player_loop(false);
	}
	
	vector<AUDIO_SourceInfo> v_src_list = g_source_handler.get_source_list();
	
	for( int idx = 0 ; idx < (int)v_src_list.size() ; idx++ ) {
		g_source_handler.listup_source_info(v_src_list[idx].get_source_info("source_hash_id"), false, -1);
	}
	
	for( int idx = 0 ; idx < (int)v_src_list.size() ; idx++ ) {
		if( v_src_list[idx].get_source_info("source_name").compare(str_source_name) == 0 ) {
			g_source_handler.listup_source_info(v_src_list[idx].get_source_info("source_hash_id"), true, loop_count);
			
			return idx;
			break;
		}
	}
	
	return -1;
}

bool listup_source_name_all(void) {
	print_debug_info("listup_source_name_all() called\n");
	
	set_audio_player_loop(false);
	
	vector<AUDIO_SourceInfo> v_src_list = g_source_handler.get_source_list();
	
	// API 전체 재생 호출 시 재생할 곡이 없는 경우, 동작 정지
	if( (int)v_src_list.size() == 0 ) {
		return false;
	}

	for( int idx = 0 ; idx < (int)v_src_list.size() ; idx++ ) {
		g_source_handler.listup_source_info(v_src_list[idx].get_source_info("source_hash_id"), true, 1);
	}

	return true;
}

void sort_source_list(char *_data) {
	print_debug_info("sort_source_list() recv data : [%s]\n", _data);
	
	if( _data == NULL ) {
		return ;
	}
	
	JsonParser json_parser;
	json_parser.parse(_data);
	
	string	str_hash_id = json_parser.select("/source_hash_id");
	
	vector<string> v_hash_list	= json_parser.split(str_hash_id, ",");
	
	g_source_handler.sort_source_list(v_hash_list);
	
	return ;
}

void add_extend_db_colume_integer(string _table_name, string _colume_name, int _dflt_value) {
	if( !g_audio_handler.is_exist_db_colume(_table_name, _colume_name) ) {
		g_audio_handler.add_db_colume(_table_name, _colume_name, "INTEGER");
	}

	char query[1024];
	sprintf(query, "update %s set %s = %d where %s is null;", _table_name.c_str(), _colume_name.c_str(), _dflt_value, _colume_name.c_str());
	g_audio_handler.query(_table_name, query);

	return ;
}


int func_daemon_mode(string _basename) {
	// init audio instance
	g_audio_handler.set_database_path(PATH_MODULE_DB_FILE);
	g_audio_handler.set_ps_name(_basename);
	
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

	// add extended colume 
	add_extend_db_colume_integer("source_info_list", "is_ext_storage", 		-1);
	add_extend_db_colume_integer("source_info_list", "num_sample_rate", 	-1);
	add_extend_db_colume_integer("source_info_list", "num_channels", 		-1);
	add_extend_db_colume_integer("source_info_list", "num_bit_rate", 		-1);
	add_extend_db_colume_integer("source_info_list", "num_bits_per_sample", -1);
	add_extend_db_colume_integer("source_info_list", "num_mp3_skip_bytes", 	-1);
	add_extend_db_colume_integer("source_info_list", "num_audio_format", 	-1);
	add_extend_db_colume_integer("source_info_list", "num_end_skip_bytes", 	-1);

	// init websocket handler : audio_player_control
	if( !g_ws_audio_control_handler.init("127.0.0.1", str_ps_ctrl) ) {
		print_debug_info("main() websocket interface connection failed, restart process\n");
		return -1;
	}
	g_ws_audio_control_handler.set_route_to(WebsocketHandler::NATIVE_ONLY);
	g_ws_audio_control_handler.set_event_handler(&ws_recv_event_handler);
	
	g_ws_audio_player_handler.init("127.0.0.1", str_ps_name);
	g_ws_audio_player_handler.set_route_to(WebsocketHandler::ALL);
	g_ws_audio_player_handler.set_event_handler(&ws_recv_event_handler);
	
	// load env json parameter
	g_audio_handler.get_env_status();
	
	// init [pcm playback] handler 
	g_pcm_playback_handler.set_playback_handler(&pcm_playback_event_handler);
	g_pcm_playback_handler.set_control_handler(&pcm_playback_control_handler);
	g_pcm_playback_handler.set_mute_handler(&pcm_playback_mute_handler);
	g_pcm_playback_handler.set_error_handler(&pcm_playback_error_handler);
				
	// set default parameter
	g_audio_handler.update_databse_status(str_ps_name, "is_pause", "0", "integer");
	
	g_player_handler.set_player_status("is_run", 	stoi(g_audio_handler.get_database_status(str_ps_name, "is_run")));
	g_player_handler.set_player_status("is_play", 	stoi(g_audio_handler.get_database_status(str_ps_name, "is_play")));
	g_player_handler.set_player_status("is_pause", 	stoi(g_audio_handler.get_database_status(str_ps_name, "is_pause")));
	g_player_handler.set_player_status("is_loop",	stoi(g_audio_handler.get_database_status(str_ps_name, "is_loop")));
	g_player_handler.set_player_index(stoi(g_audio_handler.get_database_status(str_ps_name, "audio_play_index")));
	g_player_handler.set_player_volume(stoi(g_audio_handler.get_database_status(str_ps_name, "audio_volume")));
	
	int volume = stoi(g_audio_handler.get_database_status(str_ps_name, "audio_volume"));
	g_audio_handler.set_audio_volume(volume);
	
	if( g_audio_handler.is_amp_device() ) {
		volume = 100;
	}
	g_pcm_playback_handler.set_decode_volume(volume);

	// init source file list
	g_source_handler.set_database_path(PATH_MODULE_DB_FILE);
	g_source_handler.set_source_path(PATH_SOURCE_DIR);
	
	JsonParser json_parser(g_is_debug_print);
	string str_env_json = json_parser.read_file(PATH_EXT_CONFIG);
	json_parser.parse(str_env_json);
	
	string str_mnt_dir = json_parser.select("/mnt_info/target_dir");
	string str_src_dir = json_parser.select("/mnt_info/sub_dir_list/source_file");
	str_mnt_dir.append(str_src_dir).append("/");

	g_source_handler.set_source_path_ext(str_mnt_dir);
	g_source_handler.set_mutex_db_handler(g_audio_handler.get_mutex_db_handler());

	ws_send_event_handler(str_ps_name, WS_SND_CMD_DISPLAY_LOAD);
	g_source_handler.read_source_list();
	ws_send_event_handler(str_ps_name, WS_SND_CMD_DISPLAY_CLEAR);
	
	playback_source_list();
	
	ws_send_event_handler(str_ps_name, WS_SND_CMD_VOLUME_INFO);
	
	// init inotify
    int inty_fd, inty_wd;
    if( (inty_fd = inotify_init()) < 0 ) {
        print_debug_info("main() inotify_init() failed : [%02d] %s\n", errno, strerror(errno));
        return -1;
    }

    if( (inty_wd = inotify_add_watch(inty_fd, PATH_SOURCE_DIR, IN_CREATE | IN_DELETE)) == -1 ) {
        print_debug_info("main() inotify_add_watch() failed [%s] : [%02d] %s \n", PATH_SOURCE_DIR, errno, strerror(errno));
        return -1;
    }
    print_debug_info("main() inotify_add_watch() watching : [%s]\n", PATH_SOURCE_DIR);

	int		rc;
    int 	inty_length, inty_idx = 0;
    char 	inty_buffer[INTY_BUF_SIZE];
	bool	is_noty_evt_status = false;

	struct	timeval	timeout;
	fd_set  fd_reads;

	while( !g_sig_handler.is_term() ) {
		if( g_ws_audio_control_handler.is_term() ) {
			g_ws_audio_control_handler.reconnect();
		}
		
		if( g_ws_audio_player_handler.is_term() ) {
			g_ws_audio_player_handler.reconnect();
		}

		FD_ZERO(&fd_reads);
		FD_SET(inty_fd, &fd_reads);
			
		timeout.tv_sec  = TIME_READ_SEC;
		timeout.tv_usec = TIME_READ_MSEC;

		if( (rc = select(inty_fd + 1, &fd_reads, NULL, NULL, &timeout)) < 0 ) {
			switch( errno ) {
				default :
					print_debug_info("main() select() failed : [%02d] %s\n", errno, strerror(errno));
					break;
			}
			continue;

		} else if( rc == 0 ) {
			if( is_noty_evt_status ) {
				is_noty_evt_status = false;

				bool is_run		= g_player_handler.get_player_status("is_run"); 
				bool is_play	= g_player_handler.get_player_status("is_play");

				g_player_handler.set_player_control("is_play_stop", true);
				
				// before status : play & pause
				if( is_run && is_play ) {
					set_audio_player_status("is_run",   false);
					set_audio_player_status("is_play",  false);
					set_audio_player_status("is_pause", false);
					g_pcm_playback_handler.stop();
				}
				g_source_handler.read_source_list();
				ws_send_event_handler(str_ps_name, WS_SND_CMD_SOURCE_LIST);
				ws_send_event_handler(str_ps_ctrl, WS_SND_CMD_SOURCE_UPLOAD);

			}

			continue;
		}

        inty_idx = 0;
        if( (inty_length = read(inty_fd, inty_buffer, INTY_BUF_SIZE)) < 0 ) {
            print_debug_info("main() read() failed : [%02d] %s\n", errno, strerror(errno));
        }

        while( inty_idx < inty_length ) {
			if( g_sig_handler.is_term() ) break;

            struct inotify_event *event = (struct inotify_event *)&inty_buffer[inty_idx];
            
            if( event->len ) {
                if( event->mask & IN_CREATE || event->mask & IN_DELETE ) {
					print_debug_info("main() inotify event status: [%s], file: [%s]\n", (event->mask == 256 ? "create" : "remove"), event->name);
		
					if( g_is_act_file_io ) {
						print_debug_info("main() web upload/remove action, ignore the event. \n");
						break;
					}

					is_noty_evt_status = true;
                }
            }
            
			inty_idx += INTY_EVENT_SIZE + event->len;
        }
	}
	
	print_debug_info("main() audio mute relay set off\n");
	g_pcm_playback_handler.reset_control_handler();
	while( g_mute_relay_handler.is_unmute() ) {
		g_mute_relay_handler.decrease_audio_mute();
		usleep(TIME_SET_MUTE_RELAY);
	}
	
	g_pcm_playback_handler.stop();
	
	g_audio_handler.set_level_value(0);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_OPER_INFO);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_ALIVE_INFO);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_VOLUME_INFO);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_LEVEL_INFO);
	
	inotify_rm_watch(inty_fd, inty_wd);
	close(inty_fd);

	return 0;
}


int func_stand_mode(string _filepath, string _device_name, int _volume) {
	if( !g_main_handler.file_exist(_filepath) ) {
		printf("file not found : [%s]\n", _filepath.c_str());

		return -1;
	}

	// set volume 
	int volume = _volume;
	if( g_audio_handler.is_amp_device() ) {
		volume = 100;
	}
	g_audio_handler.set_audio_volume(volume);
	g_pcm_playback_handler.set_decode_volume(volume);

	// set source info
	AUDIO_SourceInfo source_info;
	if( g_is_debug_print ) source_info.set_debug_print();
	if( g_is_debug_print ) source_info.set_debug_verbose();

	if( source_info.is_valid_ext_type(_filepath) ) {
		source_info.set_file_info(_filepath);

		// init [pcm playback] handler 
		g_pcm_playback_handler.set_playback_handler(&pcm_playback_event_handler);
		g_pcm_playback_handler.set_control_handler(&pcm_playback_control_handler);
		g_pcm_playback_handler.set_mute_handler(&pcm_playback_mute_handler);
		g_pcm_playback_handler.set_error_handler(&pcm_playback_error_handler);
		

		if( !g_pcm_playback_handler.init(_device_name, 
									1024, 
									source_info.get_play_info("num_sample_rate"), 
									source_info.get_play_info("num_channels"),  
									source_info.get_play_info("num_mp3_skip_bytes"),
									source_info.get_play_info("num_audio_format"),
									source_info.get_play_info("num_bits_per_sample"),
									source_info.get_play_info("num_end_skip_bytes")) ) {
			return -1;
		}
					
		g_pcm_playback_handler.run(source_info.get_source_info("source_file_path"));

	} else {
		printf("# invalid file format : [%s]\n", _filepath.c_str());
		return -1;
	}

	return 0;
}


// ##
// function: main
// ##
int main(int _argc, char *_argv[]) {
	bool	is_daemon_process = false;
	bool	is_stand_process  = false;
	
	string 	str_filepath 	= "";
	string	str_device_name = "default";
	int		num_volume 		= 100;
	int opt;
	while( (opt = getopt(_argc, _argv, "vbdf:D:V:")) != -1 ) {
		switch( opt ) {
			case 'v' :
				g_is_debug_print = true;
				print_debug_info("main() set print debug\n");
				
				g_sig_handler.set_debug_print();
				g_main_handler.set_debug_print();
				g_audio_handler.set_debug_print();
				g_player_handler.set_debug_print();
				g_source_handler.set_debug_print();
				g_mute_relay_handler.set_debug_print();
				
				g_ws_audio_control_handler.set_debug_print();
				g_ws_audio_player_handler.set_debug_print();
				
				g_pcm_playback_handler.set_debug_print();
				
				g_log_handler.set_debug_print();
				
				break;
			
			case 'b' :
				print_debug_info("main() set print verbose\n");
				g_source_handler.set_debug_verbose();
				break;
				
			case 'd' :
				print_debug_info("main() init daemon process \n");
				is_daemon_process = true;
				break;

			case 'f' :
				print_debug_info("main() init stand alone process \n");
				is_stand_process = true;
				str_filepath = optarg;
				break;
			
			case 'D' :
				str_device_name = optarg;
				break;
			
			case 'V' :
				try {
					num_volume = stoi(optarg);

				} catch( ... ) {
					printf("# invalid volume, set default volume : [100]\n");
					num_volume = 100;
				}

				if( num_volume < 0 || num_volume > 100 ) {
					printf("# invalid volume, set default volume : [100]\n");
					num_volume = 100;
				}
				
				break;

			default :
				printf("usage: %s [option]\n", basename(_argv[0]));
				printf("  -v : print normal debug message \n");
				printf("  -b : print verbose debug message \n");
				printf("  -d : init daemon process \n");
				printf("  -f : init standalone process, ex) -f <file path> \n");
				printf("  -D : Select PCM device by name, default: [default], ex) -D plughw:0,0 \n");
				printf("  -V : Select Volume, range: [0~100], default: [100], ex) -V 100 \n");
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
	
	int  rc = 0;
	char pid_file[256] = {};
	string str_basename = basename(_argv[0]);

	if( is_daemon_process ) {
		sprintf(pid_file, "/var/run/%s.pid", str_basename.c_str());
		if( SystemUtil::isDuplicatedRun(pid_file) ) {
			printf("# This process seems to have been duplicated run or seomething wrong. \n# Please check and run again. \n");
			return 0;
		}
		
		rc = func_daemon_mode(str_basename);
	
	} else if( is_stand_process ) {
		sprintf(pid_file, "/var/run/%s_stand.pid", str_basename.c_str());
		if( SystemUtil::isDuplicatedRun(pid_file) ) {
			printf("# This process seems to have been duplicated run or seomething wrong. \n# Please check and run again. \n");
			return 0;
		}

		rc = func_stand_mode(str_filepath, str_device_name, num_volume);

	} else {
		printf("invalid option, -d init daemon process\n");
	}

	print_debug_info("main() process has been terminated.\n");
	
	return rc;
}
