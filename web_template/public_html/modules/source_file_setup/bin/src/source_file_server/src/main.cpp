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

void ws_recv_event_handler(const char _cmd_id, const char _is_binary, const int _length, const void *_data, WebsocketHandler *_this) {
	g_mutex_ws_recv_event.lock();
	
	string str_ps_name = g_main_handler.get_ps_name();
	string str_ps_ctrl = g_main_handler.get_ps_name().append("_control");
	
	if( !g_main_handler.is_module_use(str_ps_name) ) {
		print_debug_info("ws_recv_event_handler() module [%s] disabled\n", str_ps_name.c_str());
		g_mutex_ws_recv_event.unlock();
		
		return ;
	}

	char	cmd_id	 = _cmd_id;
	string 	uri_name = _this->get_uri_name();
	string  str_cast_type;
	
	if( uri_name.compare(str_ps_ctrl) == 0 ) {
		print_debug_info("ws_recv_event_handler() uri[%s] code[0x%02X] called\n", uri_name.c_str(), _cmd_id);
		
		switch( cmd_id ) {
			// ONLY API
			case WS_RCV_CMD_CTRL_SOURCE_NAME_LIST : 
				ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_NAME_LIST);
				g_mutex_ws_recv_event.unlock();
				return ;
				break;

			case WS_RCV_CMD_SVR_APPLY	:
				cmd_id 	 = WS_RCV_CMD_INIT;
				g_main_handler.update_databse_status(str_ps_name, "module_view", "operation");
				break;
			
			case WS_RCV_CMD_SVR_SETUP	:
				cmd_id 	 = WS_RCV_CMD_STOP;
				g_main_handler.update_databse_status(str_ps_name, "module_view", "setup");
				break;
			
			default :
				break;
		}

		uri_name = str_ps_name;
	}
	
	if( uri_name.compare(str_ps_name) == 0 ) {
		print_debug_info("ws_recv_event_handler() uri[%s] code[0x%02X] called\n", uri_name.c_str(), _cmd_id);
		
		switch( cmd_id ) {
			case WS_RCV_CMD_CONNECT :
				ws_send_event_handler(uri_name, WS_SND_CMD_CLIENT_LIST);
				ws_send_event_handler(uri_name, WS_SND_CMD_OPER_INFO);
				ws_send_event_handler(uri_name, WS_SND_CMD_ALIVE_INFO);
				
				ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_LIST);
				ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_OPER_INFO);
				break;
			
				
			case WS_RCV_CMD_INIT 	: // init & run
				if( init_server(string((char *)_data)) ) {
					print_debug_info("ws_recv_server_handle() bypass init & run API \n");
					break ;
				}

				stop_server_all(true);
				ws_recv_source_handle(uri_name, WS_RCV_CMD_CTRL_STOP, _data);
				
				// case non-break
				
				
			case WS_RCV_CMD_RUN 	: // run
				g_main_handler.update_databse_status(uri_name, "module_view",   "operation");
				g_main_handler.update_databse_status(uri_name, "module_status", "run");
				g_main_handler.set_alive_status(true);
				
				str_cast_type = g_main_handler.get_database_status(uri_name, "network_cast_type");
				
				if( str_cast_type.compare("unicast") == 0 ) {
					print_debug_info("ws_recv_server_handle() [%s] run : [unicast] \n", uri_name.c_str());
					run_server_unicast();
					
				} else if( str_cast_type.compare("multicast") == 0 ) {
					print_debug_info("ws_recv_server_handle() [%s] run : [multicast] \n", uri_name.c_str());
					run_server_multicast();
				
				} else if( str_cast_type.compare("all") == 0 ) {
					print_debug_info("ws_recv_server_handle() [%s] run : [unicast/multicast] \n", uri_name.c_str());
					run_server_unicast();
					run_server_multicast();
				}
				
				ws_send_event_handler(uri_name, WS_SND_CMD_CLIENT_LIST);
				ws_send_event_handler(uri_name, WS_SND_CMD_OPER_INFO);
				ws_send_event_handler(uri_name, WS_SND_CMD_ALIVE_INFO);
				
				break;
				
				
			case WS_RCV_CMD_STOP 	: // stop
				ws_recv_source_handle(uri_name, WS_RCV_CMD_CTRL_STOP, _data);
				
				g_main_handler.update_databse_status(uri_name, "module_status", "stop");
				g_main_handler.set_alive_status(false);
				
				stop_server_all();
				
				ws_send_event_handler(uri_name, WS_SND_CMD_CLIENT_LIST);
				ws_send_event_handler(uri_name, WS_SND_CMD_OPER_INFO);
				ws_send_event_handler(uri_name, WS_SND_CMD_ALIVE_INFO);
				
				break;

				
			default :
				ws_recv_source_handle(uri_name, cmd_id, _data);
				break;
		}
	
	} else if( uri_name.compare("audio_player_control") == 0 ) {
			switch( cmd_id ) {
				// source_file_management module file upload
				case WS_RCV_CMD_CTRL_UPLOAD :	
				case WS_RCV_CMD_CTRL_RELOAD :	
					print_debug_info("ws_recv_event_handler() uri[%s] code[0x%02X] called\n", uri_name.c_str(), cmd_id);
					ws_recv_source_handle(str_ps_name, WS_RCV_CMD_CTRL_RELOAD, _data);
					break;
				
				case WS_RCV_CMD_CTRL_REMOVE :
					print_debug_info("ws_recv_event_handler() uri[%s] code[0x%02X] called\n", uri_name.c_str(), cmd_id);
					ws_recv_source_handle(str_ps_name, WS_RCV_CMD_CTRL_REMOVE, _data);
					break;
					
				default :
					break;
			}
		}
	g_mutex_ws_recv_event.unlock();
	
	return ;
}

void ws_send_event_handler(string _uri_name, char _cmd_id) {
	g_mutex_ws_send_event.lock();
	print_debug_info("ws_send_event_handler() uri[%s] code[0x%02X] called\n", _uri_name.c_str(), _cmd_id);
	
	Document doc_data;
	string str_ps_name = g_main_handler.get_ps_name();
	string str_ps_ctrl = g_main_handler.get_ps_name().append("_control");

	string str_client_list;
	
	if( _uri_name.compare(str_ps_name) == 0 ) {
		Pointer("/type").Set(doc_data, to_string(_cmd_id).c_str());
		
		switch( _cmd_id ) {
			case WS_SND_CMD_ALIVE_INFO	:
				Pointer("/data/stat"				).Set(doc_data, to_string(g_main_handler.is_alive_status()).c_str());
				Pointer("/data/view"				).Set(doc_data, g_main_handler.get_database_status(_uri_name, "module_view").c_str());
				break;
			
			case WS_SND_CMD_OPER_INFO	:
				Pointer("/data/castType"			).Set(doc_data, g_main_handler.get_database_status(_uri_name, "network_cast_type").c_str());
				Pointer("/data/ipAddr"				).Set(doc_data, g_main_handler.get_database_status(_uri_name, "network_mcast_ip_addr").c_str());
				Pointer("/data/port"				).Set(doc_data, stoi(g_main_handler.get_database_status(_uri_name, "network_mcast_port")));
				Pointer("/data/unicast_ip_addr"		).Set(doc_data, g_main_handler.get_database_status(_uri_name, "network_ucast_ip_addr").c_str());
				Pointer("/data/unicast_port"		).Set(doc_data, g_main_handler.get_database_status(_uri_name, "network_ucast_port").c_str());
				Pointer("/data/multicast_ip_addr"	).Set(doc_data, g_main_handler.get_database_status(_uri_name, "network_mcast_ip_addr").c_str());
				Pointer("/data/multicast_port"		).Set(doc_data, g_main_handler.get_database_status(_uri_name, "network_mcast_port").c_str());				
				break;
				
			case WS_SND_CMD_CLIENT_LIST	:
				Pointer("/data/maxCount"			).Set(doc_data, g_socket_unicast_server.get_max_client_count());
				Pointer("/data/accCount"			).Set(doc_data, g_socket_unicast_server.get_accrue_count());
				Pointer("/data/connCount"			).Set(doc_data, g_socket_unicast_server.get_current_count());
				Pointer("/data/list"				).Set(doc_data, g_socket_unicast_server.get_client_list().c_str());
				break;
			
			case WS_SND_CMD_SOURCE_LIST	:
				Pointer("/data/source_list"			).Set(doc_data, g_source_handler.make_json_source_list().c_str());
				break;
				
			case WS_SND_CMD_SOURCE_OPER_INFO	:
				Pointer("/data/is_run"				).Set(doc_data, g_player_handler.get_player_status("is_run"));
				Pointer("/data/is_play"				).Set(doc_data, g_player_handler.get_player_status("is_play"));
				Pointer("/data/is_pause"			).Set(doc_data, g_player_handler.get_player_status("is_pause"));
				Pointer("/data/is_loop"				).Set(doc_data, g_player_handler.get_player_status("is_loop"));
				Pointer("/data/audio_play_index"	).Set(doc_data, g_player_handler.get_player_index());
				break;
				
			case WS_SND_CMD_CTRL_STATUS			:
				Pointer("/data/stat"				).Set(doc_data, to_string(g_main_handler.is_alive_status()).c_str());
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
		data_buffer.Clear();
		
		g_ws_server_handler.send(_cmd_id, json_data);
	
	}  else if( _uri_name.compare(str_ps_ctrl) == 0 ) {
		Pointer("/type").Set(doc_data, to_string(_cmd_id).c_str());
		
		switch( _cmd_id ) {
			case WS_SND_CMD_SOURCE_NAME_LIST	:
				Pointer("/data/source_name_list").Set(doc_data, g_source_handler.make_json_source_name_list().c_str());
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
		g_ws_control_handler.send(_cmd_id, json_data);
	}
	
	g_mutex_ws_send_event.unlock();
	
	return ;
}

void ws_recv_source_handle(string _uri_name, int _cmd_id, const void *_data) {
	int    cmd_id   = _cmd_id;
	string uri_name = _uri_name;
	
	bool is_run		= g_player_handler.get_player_status("is_run"); 
	bool is_play	= g_player_handler.get_player_status("is_play");
	bool is_pause	= g_player_handler.get_player_status("is_pause");
	int  idx = 0;
	
	switch( cmd_id ) {
		case WS_RCV_CMD_CTRL_PLAY	:
			// before status : play
			if( is_run && is_play && !is_pause ) {
				break;
			}

			// before status : pause
			if( is_run && is_play && is_pause ) {
				set_audio_player_status("is_pause", false);
				
				g_playback_handler.set_playback_play();
				
				ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_LIST);
				ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_OPER_INFO);
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
				
				g_playback_handler.set_playback_pause();
				
				ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_LIST);
				ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_OPER_INFO);
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
				ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_STOP_STATUS);
				break;
			}
			
			// before status : play & pause
			if( is_run && is_play  ) {
				set_audio_player_status("is_run",   false);
				set_audio_player_status("is_play",  false);
				set_audio_player_status("is_pause", false);
				
				g_player_handler.set_player_control("is_play_stop", true);
				
				g_playback_handler.stop();
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
				
				g_playback_handler.set_playback_play();
			}
			
			g_playback_handler.stop();
							
			break;
		
			
		case WS_RCV_CMD_CTRL_NEXT	:
			// before status : stop
			if( !is_run ) {
				break;
			}
			
			g_player_handler.set_player_control("is_play_next", true);

			if( is_pause ) {
				set_audio_player_status("is_pause", false);
				
				g_playback_handler.set_playback_play();
			}
			
			g_playback_handler.stop();
			
			break;
		
			
		case WS_RCV_CMD_CTRL_LOOP	:
			set_audio_player_status("is_loop");
			
			ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_OPER_INFO);
			
			break;
		
			
		case WS_RCV_CMD_CTRL_REMOVE	:
			set_audio_player_status("is_run",   false);
			set_audio_player_status("is_play",  false);
			set_audio_player_status("is_pause", false);

			g_player_handler.set_player_control("is_force_stop", true);
			g_player_handler.set_player_control("is_play_stop",  true);
							
			// before status : play & pause
			if( is_run && is_play ) {
				g_playback_handler.stop();
			}
			
			remove_source_list((char *)_data);
			
			ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_LIST);
			
			break;
		
			
		case WS_RCV_CMD_CTRL_SORT	:
			set_audio_player_status("is_run",   false);
			set_audio_player_status("is_play",  false);
			set_audio_player_status("is_pause", false);

			g_player_handler.set_player_control("is_play_stop", true);
							
			// before status : play & pause
			if( is_run && is_play ) {
				g_playback_handler.stop();
			}
			
			sort_source_list((char *)_data);
			
			ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_LIST);

			break;
			
			
		case WS_RCV_CMD_CTRL_RELOAD :
			ws_send_event_handler(uri_name, WS_SND_CMD_DISPLAY_LOAD);

			set_audio_player_status("is_run",   false);
			set_audio_player_status("is_play",  false);
			set_audio_player_status("is_pause", false);

			g_player_handler.set_player_control("is_play_stop", true);
							
			// before status : play & pause
			if( is_run && is_play ) {
				g_playback_handler.stop();
			}
			
			g_source_handler.read_source_list();
			
			ws_send_event_handler(uri_name, WS_SND_CMD_SOURCE_LIST);
			
			break;
			
			
		case WS_RCV_CMD_CTRL_PLAY_INDEX :
			idx = parse_audio_play_index((char *)_data);
			
			g_player_handler.set_player_index(idx);
			g_player_handler.set_player_control("is_change_index", true);
			g_source_handler.update_source_info(idx, "is_play", true);
			
			// before status : play
			if( is_run && is_play && !is_pause ) {
				g_playback_handler.stop();
				break;
			}
			
			// before status : pause
			if( is_run && is_play && is_pause ) {
				set_audio_player_status("is_pause", false);

				g_playback_handler.set_playback_play();
				g_playback_handler.stop();
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
		
		case WS_RCV_CMD_CTRL_API_PLAY_SINGLE	:
			idx = listup_source_name_single((char *)_data);
			
			g_player_handler.set_player_index(idx);
			g_player_handler.set_player_control("is_change_index", true);
			g_source_handler.update_source_info(idx, "is_play", true);
							
			// before status : play
			if( is_run && is_play && !is_pause ) {
				g_player_handler.set_player_control("is_play_stop", true);
				
				g_playback_handler.stop();
				break;
			}

			// before status : pause
			if( is_run && is_play && is_pause ) {
				set_audio_player_status("is_pause", false);

				g_player_handler.set_player_control("is_play_stop", true);

				g_playback_handler.stop();
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
			
		case WS_RCV_CMD_CTRL_API_PLAY_ALL	:
			listup_source_name_all();
			
			g_player_handler.set_player_index(0);
			g_player_handler.set_player_control("is_change_index", true);
			g_source_handler.update_source_info(0, "is_play", true);
			
			// before status : play
			if( is_run && is_play && !is_pause ) {
				g_player_handler.set_player_control("is_play_stop", true);
				
				g_playback_handler.stop();
				break;
			}

			// before status : pause
			if( is_run && is_play && is_pause ) {
				set_audio_player_status("is_pause", false);

				g_player_handler.set_player_control("is_play_stop", true);
				
				g_playback_handler.stop();
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

		case WS_RCV_CMD_CTRL_SOURCE_NAME_LIST : 
			ws_send_event_handler(_uri_name, WS_SND_CMD_SOURCE_NAME_LIST);
			
			break;
		

		default :
			break;
	}
	
	ws_send_event_handler(uri_name, WS_SND_CMD_CTRL_STATUS);
	
	return ;
}


// ##
// function: common
// ##
bool init_server(string _data) {
	print_debug_info("\033[33minit_server() receive data[%s] \033[0m\n", _data.c_str());
	
	string str_ps_name = g_main_handler.get_ps_name();
	
	JsonParser json_parser;
	json_parser.parse(_data);
	
	string str_bypass = json_parser.select("/init_bypass");
	if( str_bypass.compare("true") == 0 ) {
		if( g_main_handler.is_alive_status() ) {
			return true;
		}
	} 

	g_main_handler.update_databse_status(str_ps_name, "module_view", 			json_parser.select("/module_view"));
	g_main_handler.update_databse_status(str_ps_name, "module_status", 			json_parser.select("/module_status"));
	g_main_handler.update_databse_status(str_ps_name, "network_cast_type", 		json_parser.select("/network_cast_type"));
	g_main_handler.update_databse_status(str_ps_name, "network_client_count", 	json_parser.select("/network_client_count"));
	g_main_handler.update_databse_status(str_ps_name, "network_mcast_ip_addr",	json_parser.select("/network_mcast_ip_addr"));
	g_main_handler.update_databse_status(str_ps_name, "network_mcast_port", 	json_parser.select("/network_mcast_port"));
	g_main_handler.update_databse_status(str_ps_name, "network_ucast_port", 	json_parser.select("/network_ucast_port"));
	
	return false;
}

void run_server_unicast(void) {
	string str_ps_name = g_main_handler.get_ps_name();
	
	string	str_server_ip_addr	= g_main_handler.get_database_status(str_ps_name, "network_mcast_ip_addr");
	int 	num_server_port		= stoi(g_main_handler.get_database_status(str_ps_name, "network_ucast_port"));
	int 	num_max_client		= stoi(g_main_handler.get_database_status(str_ps_name, "network_client_count"));

	g_main_handler.set_network_ip_addr(str_server_ip_addr);
	
	g_socket_unicast_server.set_server_port(num_server_port);
	g_socket_unicast_server.set_num_max_client(num_max_client);
	g_socket_unicast_server.set_event_handler(&network_server_event_handler);
	
	g_socket_unicast_server.init();
	g_socket_unicast_server.run();
	
	g_log_handler.info("[{STR_SETUP_SERVER}] {STR_SERVER_UNICAST} {STR_JS_START_SERVER}");
	
	return ;
}

void run_server_multicast(void) {
	string str_ps_name = g_main_handler.get_ps_name();
	
	string	str_server_ip_addr	= g_main_handler.get_database_status(str_ps_name, 	   "network_mcast_ip_addr");
	int 	num_server_port		= stoi(g_main_handler.get_database_status(str_ps_name, "network_mcast_port"));
	
	g_main_handler.set_network_ip_addr(str_server_ip_addr);
	
	g_socket_multicast_server.set_server_port(num_server_port);
	g_socket_multicast_server.set_ip_addr(str_server_ip_addr);
	
	g_socket_multicast_server.init();
	g_socket_multicast_server.run();
	
	g_log_handler.info("[{STR_SETUP_SERVER}] {STR_SERVER_MULTICAST} {STR_JS_START_SERVER}");
	
	return ;
}

void stop_server_all(bool _is_init) {
	string str_ps_name = g_main_handler.get_ps_name();
	
	print_debug_info("stop_server_all() %s stop : [all] \n", str_ps_name.c_str());
	
	g_socket_unicast_server.stop();
	g_socket_multicast_server.stop();
	
	if( !_is_init ) {
		g_log_handler.info("[{STR_SETUP_SERVER}] {STR_SERVER_OP_STOP}");
	}
	
	return ;
}

// functions: audio_server - network event handler
void network_server_event_handler(int _index, bool _is_connect) {
	g_mutex_network_server_event.lock();
	
	string str_ps_name  = g_main_handler.get_ps_name();
	string str_hostname = g_socket_unicast_server.get_client_info(_index, "hostname");
	string str_ip_addr  = g_socket_unicast_server.get_client_info(_index, "ip_addr");	
	
	char str_log_msg[1024];
	
	if( _is_connect ) {
		print_debug_info("network_server_event_handler() connect audio client : [%d]\n", _index);
		g_socket_unicast_server.inc_current_count();
		
		sprintf(str_log_msg, "[{STR_SETUP_SERVER}] [%s/%s] {STR_SERVER_CLIENT_CONNECT}", str_ip_addr.c_str(), str_hostname.c_str());
		
	} else {
		print_debug_info("network_server_event_handler() disconnect audio client : [%d]\n", _index);
		g_socket_unicast_server.dec_current_count();
		
		sprintf(str_log_msg, "[{STR_SETUP_SERVER}] [%s/%s] {STR_SERVER_CLIENT_DISCONNECT}", str_ip_addr.c_str(), str_hostname.c_str());
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

// functions: playback event handler
void playback_event_handler(char *_data, int _length) {
	if( g_sig_handler.is_term() ) {
		return ;
	}
	
	struct timeval t_time_begin, t_time_end;
	gettimeofday(&t_time_begin, NULL);
	
	g_queue_pcm_capture.enqueue(_data, _length);
	tuple<char *, int> offset_info = g_queue_pcm_capture.dequeue();
	
	char *ptr_data = get<0>(offset_info);
	int	 data_size = get<1>(offset_info);
	
	g_socket_unicast_server.send_data_handler(ptr_data, data_size);
	g_socket_multicast_server.send_data_handler(ptr_data, data_size);
	
	gettimeofday(&t_time_end, NULL);
	
	g_playback_handler.set_period_delay((int)g_main_handler.calc_diff_time(t_time_begin, t_time_end));
	
	return ;
}

// functions: source_file_management
void parse_audio_play_data(string _data) {
	print_debug_info("\033[33mparse_audio_play_data() receive data[%s] \033[0m\n", _data.c_str());
	
	string str_ps_name = g_main_handler.get_ps_name();
	
	JsonParser json_parser;
	json_parser.parse(_data);
	
	g_main_handler.update_databse_status(str_ps_name, "is_run",		json_parser.select("/is_run"));
	g_main_handler.update_databse_status(str_ps_name, "is_play",	json_parser.select("/is_play"));
	g_main_handler.update_databse_status(str_ps_name, "is_pause",	json_parser.select("/is_pause"));
	g_main_handler.update_databse_status(str_ps_name, "is_loop",	json_parser.select("/is_loop"));

	return ;
}

int	parse_audio_play_index(string _data) {
	JsonParser json_parser;
	json_parser.parse(_data);
	
	int play_index = stoi(json_parser.select("/play_index"));
	
	return play_index;
}

void set_audio_player_status(string _type, bool _status) {
	string str_ps_name	= g_main_handler.get_ps_name();
	int num_status 	= (_status ? 1 : 0);
	
	if( _type.compare("is_loop") == 0 ) {
		// toggle loop status
		int is_loop = g_player_handler.get_player_status("is_loop");
		num_status  = (is_loop == 0 ? 1 : 0);
	}
	
	g_main_handler.update_databse_status(str_ps_name, _type, num_status);
	g_player_handler.set_player_status(_type, num_status);
	
	return ;
}

void set_audio_player_loop(bool _status) {
	string str_ps_name 	= g_main_handler.get_ps_name();
	string type			= "is_loop";

	int	num_status 	= (_status ? 1 : 0);
	
	g_main_handler.update_databse_status(str_ps_name, type, num_status);
	g_player_handler.set_player_status(type, num_status);
	
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
		return 0;
	}
	
	JsonParser json_parser;
	json_parser.parse(_data);
	
	string	str_source_name	= json_parser.select("/source_name");
	string	str_loop_count 	= json_parser.select("/source_loop_count");
	
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
	
	return 0;
}

void listup_source_name_all(void) {
	print_debug_info("listup_source_name_all() called\n");
	
	set_audio_player_loop(false);
	
	vector<AUDIO_SourceInfo> v_src_list = g_source_handler.get_source_list();
	
	for( int idx = 0 ; idx < (int)v_src_list.size() ; idx++ ) {
		g_source_handler.listup_source_info(v_src_list[idx].get_source_info("source_hash_id"), true, 1);
	}

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

	string str_ps_name 		= g_main_handler.get_ps_name();

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
				ws_send_event_handler(str_ps_name, WS_SND_CMD_SOURCE_OPER_INFO);
				
				int loop_count = v_src_list[idx].get_play_info("audio_loop_count");

				// 단일 음원 반복 재생
				for( int loop_idx = 0 ; loop_idx < loop_count ; loop_idx++ ) {
					g_playback_handler.init( 
												v_src_list[idx].get_play_info("num_sample_rate"), 
												v_src_list[idx].get_play_info("num_channels"),  
												v_src_list[idx].get_play_info("num_mp3_skip_bytes"),
												v_src_list[idx].get_play_info("num_audio_format"),
												v_src_list[idx].get_play_info("num_bit_rate"),
												v_src_list[idx].get_play_info("num_end_skip_bytes")
												);
					
					g_playback_handler.run(v_src_list[idx].get_source_info("source_file_path"));
					
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
						
						g_playback_handler.set_playback_play();
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
				ws_send_event_handler(str_ps_name, WS_SND_CMD_SOURCE_OPER_INFO);
				
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
				ws_send_event_handler(str_ps_name, WS_SND_CMD_SOURCE_OPER_INFO);
				
				break;
			}
		}
	}

	ws_send_event_handler(str_ps_name, WS_SND_CMD_SOURCE_LIST);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_SOURCE_OPER_INFO);
	
		
	print_debug_info("execute_playback_source() termed\n");
	ws_send_event_handler(str_ps_name, WS_SND_CMD_SOURCE_STOP_STATUS);
	
	return ;
}

void playback_source_list(void) {
	print_debug_info("playback_source_list() thread create\n");
	g_thread_playback = thread(&execute_playback_source);
	g_thread_playback.detach();
	
	return ;
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

void add_extend_db_colume_integer(string _table_name, string _colume_name, int _dflt_value) {
	if( !g_main_handler.is_exist_db_colume(_table_name, _colume_name) ) {
		g_main_handler.add_db_colume(_table_name, _colume_name, "INTEGER");
	}

	char query[1024];
	sprintf(query, "update %s set %s = %d where %s is null;", _table_name.c_str(), _colume_name.c_str(), _dflt_value, _colume_name.c_str());
	g_main_handler.query(_table_name, query);

	return ;
}


// ##
// function: main
// ##
int main(int _argc, char *_argv[]) {
	int opt;
	while( (opt = getopt(_argc, _argv, "vV")) != -1 ) {
		switch( opt ) {
			case 'v' :
				g_is_debug_print = true;
				print_debug_info("main() set print debug\n");
				
				g_sig_handler.set_debug_print();
				g_log_handler.set_debug_print();
				
				g_main_handler.set_debug_print();
				g_player_handler.set_debug_print();
				g_source_handler.set_debug_print();
				g_playback_handler.set_debug_print();
				
				g_socket_unicast_server.set_debug_print();
				g_socket_multicast_server.set_debug_print();
				
				g_ws_server_handler.set_debug_print();

				g_ws_source_ctrl_handler.set_debug_print();
				
				g_queue_pcm_capture.set_debug_print();
				
				break;

			case 'V' :
				print_debug_info("main() set print verbose\n");
				g_source_handler.set_debug_verbose();
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

	
	// init instance
	g_main_handler.set_database_path(PATH_MODULE_DB_FILE);
	g_main_handler.set_ps_name(basename(_argv[0]));
		
	string str_ps_name = g_main_handler.get_ps_name();
	string str_ps_ctrl = g_main_handler.get_ps_name().append("_control");
	
	g_main_handler.update_databse_status(str_ps_name, "network_ucast_ip_addr", g_main_handler.get_network_info());
	
	if( !g_main_handler.is_module_use(str_ps_name) ) {
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
	
	if( !g_ws_server_handler.init("127.0.0.1", str_ps_name) ) {
		print_debug_info("main() websocket interface connection failed, restart process\n");
		return -1;
	}
	g_ws_server_handler.set_route_to(WebsocketHandler::ALL);
	g_ws_server_handler.set_event_handler(&ws_recv_event_handler);
	
	g_ws_control_handler.init("127.0.0.1", str_ps_ctrl);
	g_ws_control_handler.set_route_to(WebsocketHandler::NATIVE_ONLY);
	g_ws_control_handler.set_event_handler(&ws_recv_event_handler);
	
	g_ws_source_ctrl_handler.init("127.0.0.1", "audio_player_control");
	g_ws_source_ctrl_handler.set_event_handler(&ws_recv_event_handler);
	
	// init [pcm capture] queue handler
	g_queue_pcm_capture.init();
	g_queue_pcm_capture.reset_queue_unit(NUM_QUEUE_SIZE + sizeof(HEADER_INFO_t));
	g_queue_pcm_capture.set_min_dequeue_cnt(NUM_MIN_DEQUEUE_CNT);
	
	
	// set default parameter
	g_main_handler.update_databse_status(str_ps_name, "is_pause", 0);

	// init playback handler
	g_playback_handler.set_playback_handler(&playback_event_handler);
	
	
	g_player_handler.set_player_status("is_run", 	stoi(g_main_handler.get_database_status(str_ps_name, "is_run")));
	g_player_handler.set_player_status("is_play", 	stoi(g_main_handler.get_database_status(str_ps_name, "is_play")));
	g_player_handler.set_player_status("is_pause", 	stoi(g_main_handler.get_database_status(str_ps_name, "is_pause")));
	g_player_handler.set_player_status("is_loop",	stoi(g_main_handler.get_database_status(str_ps_name, "is_loop")));
	g_player_handler.set_player_index(stoi(g_main_handler.get_database_status(str_ps_name, "audio_play_index")));
	g_player_handler.set_player_volume(0);
	
	
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
	g_source_handler.set_mutex_db_handler(g_main_handler.get_mutex_db_handler());
	
	ws_send_event_handler(str_ps_name, WS_SND_CMD_DISPLAY_LOAD);
	g_source_handler.read_source_list();
	ws_send_event_handler(str_ps_name, WS_SND_CMD_DISPLAY_CLEAR);

	playback_source_list();
	
	if( !g_main_handler.is_module_status(str_ps_name) ) {
		g_main_handler.set_alive_status(false);
		
	} else {
		g_main_handler.set_alive_status(true);
		
		string str_cast_type = g_main_handler.get_database_status(str_ps_name, "network_cast_type");
		
		if( str_cast_type.compare("unicast") == 0 ) {
			print_debug_info("main() [%s] run : [unicast] \n", str_ps_name.c_str());
			run_server_unicast();
			
		} else if( str_cast_type.compare("multicast") == 0 ) {
			print_debug_info("main() [%s] run : [multicast] \n", str_ps_name.c_str());
			run_server_multicast();
			
		} else if( str_cast_type.compare("all") == 0 ) {
			print_debug_info("main() [%s] run : [unicast/multicast] \n", str_ps_name.c_str());
			run_server_unicast();
			run_server_multicast();
		}
	}
	
	while( !g_sig_handler.is_term() ) {
		sleep(TIME_WS_CHECK_LOOP);
		
		if( g_ws_server_handler.is_term() ) {
			g_ws_server_handler.reconnect();
		}
	}
	
	g_queue_pcm_capture.free();
	
	print_debug_info("main() process has been terminated.\n");
	
	return 0;
}
