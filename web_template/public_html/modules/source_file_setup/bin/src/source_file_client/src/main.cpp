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
	g_mutex_ws_send_event.lock();
	
	// debug print filter
	switch( _cmd_id) {
		case WS_SND_CMD_LEVEL_INFO	:
			break;
		
		default :
			print_debug_info("ws_send_event_handler() uri[%s] code[0x%02X] called\n", _uri_name.c_str(), _cmd_id);
			break;
	}

	Document doc_data;
	string  str_ps_name = g_main_handler.get_ps_name();
	tuple<string, int> server_info = g_main_handler.get_current_server_info();
	
	string str_cast_type;
	
	if( _uri_name.compare(str_ps_name) == 0 ) {
		Pointer("/type").Set(doc_data, to_string(_cmd_id).c_str());
		
		switch( _cmd_id ) {
			case WS_SND_CMD_ALIVE_INFO	:
				Pointer("/data/stat"			).Set(doc_data, to_string(g_main_handler.is_alive_status()).c_str());
				Pointer("/data/view"			).Set(doc_data, g_main_handler.get_database_status(_uri_name, "module_view").c_str());
				break;
				
			case WS_SND_CMD_OPER_INFO	:
				// setup network info
				str_cast_type = g_main_handler.get_database_status(str_ps_name, "network_cast_type");
				
				Pointer("/data/castType"		).Set(doc_data, str_cast_type.c_str());
				Pointer("/data/redundancy"		).Set(doc_data, g_main_handler.get_database_status(_uri_name, "network_redundancy").c_str());
				
				// unicast current connected network server info
				Pointer("/data/current_server"	).Set(doc_data, get<0>(server_info).c_str());
				Pointer("/data/current_port"	).Set(doc_data, get<1>(server_info));
				
				// unicast master/slave ip/port info
				Pointer("/data/ipAddr1"			).Set(doc_data, g_socket_unicast_client.get_server_ip_addr("master").c_str());
				Pointer("/data/port1"			).Set(doc_data,	g_socket_unicast_client.get_server_port("master"));
				Pointer("/data/ipAddr2"			).Set(doc_data,	g_socket_unicast_client.get_server_ip_addr("slave").c_str());
				Pointer("/data/port2"			).Set(doc_data, g_socket_unicast_client.get_server_port("slave"));
				
				// multicast ip/port info
				Pointer("/data/mIpAddr"			).Set(doc_data, g_socket_multicast_client.get_server_ip().c_str());
				Pointer("/data/mPort"			).Set(doc_data, g_socket_multicast_client.get_server_port());
				
				// playback info
				Pointer("/data/pcm_sample_rate"	).Set(doc_data, g_audio_handler.get_playback_info("pcm_sample_rate"));
				Pointer("/data/pcm_channels"	).Set(doc_data, g_audio_handler.get_playback_info("pcm_channels"));
				Pointer("/data/channels"		).Set(doc_data, g_audio_handler.get_playback_info("pcm_channels"));
				Pointer("/data/encode_mode"		).Set(doc_data, g_audio_handler.get_playback_info("encode_mode"));
				Pointer("/data/mp3_quality"		).Set(doc_data, g_audio_handler.get_playback_info("encode_quality"));
				Pointer("/data/playVolume"		).Set(doc_data, g_main_handler.get_database_status(_uri_name, "audio_volume").c_str());
				Pointer("/data/delay_sec"		).Set(doc_data, stoi(g_main_handler.get_database_status(_uri_name, "audio_play_buffer_sec")));
				Pointer("/data/delay_msec"		).Set(doc_data, stoi(g_main_handler.get_database_status(_uri_name, "audio_play_buffer_msec")));
				break;
				
			case WS_SND_CMD_VOLUME_INFO	:
				Pointer("/data/playVolume"		).Set(doc_data, g_main_handler.get_database_status(_uri_name, "audio_volume").c_str());
				Pointer("/data/type"			).Set(doc_data, _uri_name.c_str());
				break;
				
			case WS_SND_CMD_LEVEL_INFO	:
				Pointer("/data/level"			).Set(doc_data, to_string(g_audio_handler.get_level_value()).c_str());
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
		g_ws_audio_client_handler.send(_cmd_id, json_data);
	}
		
	g_mutex_ws_send_event.unlock();
	
	return ;
}

void ws_recv_event_handler(const char _cmd_id, const char _is_binary, const int _length, const void *_data, WebsocketHandler *_this) {
	g_mutex_ws_recv_event.lock();
	
	string str_ps_name = g_main_handler.get_ps_name();
	string str_ps_ctrl = g_main_handler.get_ps_name().append("_control");
	
	char	cmd_id	 = _cmd_id;
	string 	uri_name = _this->get_uri_name();
	print_debug_info("ws_recv_event_handler() uri[%s] code[0x%02X] called\n", uri_name.c_str(), _cmd_id);
	
	string str_cast_type;
	
	if( uri_name.compare(str_ps_ctrl) == 0 ) {
		uri_name = str_ps_name;
		
		switch( cmd_id ) {
			case WS_RCV_CMD_CTRL_APPLY	:
				cmd_id 	 = WS_RCV_CMD_INIT;
				g_main_handler.update_databse_status(uri_name, "module_view", "operation");
				break;
				
			case WS_RCV_CMD_CTRL_SETUP	:
				cmd_id 	 = WS_RCV_CMD_STOP;
				g_main_handler.update_databse_status(uri_name, "module_view", "setup");
				break;
				
			default :
				break;
		}
	}
	
	if( uri_name.compare(str_ps_name) == 0 ) {
		switch( cmd_id ) {
			case WS_RCV_CMD_CONNECT	: // connect
				if( !g_main_handler.is_alive_status() ) {
					g_audio_handler.set_level_value(0);
					ws_send_event_handler(str_ps_name, WS_SND_CMD_LEVEL_INFO);
				}
				
				ws_send_event_handler(uri_name, WS_SND_CMD_OPER_INFO);
				ws_send_event_handler(uri_name, WS_SND_CMD_VOLUME_INFO);
				ws_send_event_handler(uri_name, WS_SND_CMD_ALIVE_INFO);
				
				break;

			case WS_RCV_CMD_INIT 	: // init & run
				init_audio_client(string((char *)_data));

				stop_client_all();
				stop_pcm_playback();
				
				// case non-break
				
			case WS_RCV_CMD_RUN 	: // run
				g_main_handler.update_databse_status(uri_name, "module_view", 	 "operation");
				g_main_handler.update_databse_status(uri_name, "module_status", "run");
				
				str_cast_type = g_main_handler.get_database_status(str_ps_name, "network_cast_type");
				
				if( str_cast_type.compare("unicast") == 0 ) {
					print_debug_info("ws_recv_event_handler() [%s] run : [unicast] \n", str_ps_name.c_str());
					run_client_unicast();
					
				} else if( str_cast_type.compare("multicast") == 0 ) {
					print_debug_info("ws_recv_event_handler() [%s] run : [multicast] \n", str_ps_name.c_str());
					run_client_multicast();
				}
				
				break;
				
			case WS_RCV_CMD_STOP 	: // stop
				g_main_handler.update_databse_status(uri_name, "module_status", "stop");

				stop_client_all();

				g_main_handler.set_alive_status(false);
				g_audio_handler.set_level_value(0);
				
				ws_send_event_handler(str_ps_name, WS_SND_CMD_LEVEL_INFO);
				ws_send_event_handler(uri_name, WS_SND_CMD_VOLUME_INFO);
				ws_send_event_handler(uri_name, WS_SND_CMD_OPER_INFO);
				ws_send_event_handler(uri_name, WS_SND_CMD_ALIVE_INFO);
				
				break;
			
			case WS_RCV_CMD_VOLUME	:
				init_audio_client(string((char *)_data));
				
				ws_send_event_handler(uri_name, WS_SND_CMD_VOLUME_INFO);
				
				break;
				
				
			default :
				break;
		}
	}
	g_mutex_ws_recv_event.unlock();
	
	return ;
}


// 
// ## functions: audio_client
// 
void init_audio_client(string _data) {
	print_debug_info("\033[33minit_audio_client() receive data[%s] \033[0m\n", _data.c_str());
	
	string str_ps_name = g_main_handler.get_ps_name();
	
	JsonParser json_parser;
	json_parser.parse(_data);
	
	g_main_handler.update_databse_status(str_ps_name, "module_view", 				json_parser.select("/module_view"), 			"string");
	g_main_handler.update_databse_status(str_ps_name, "module_status", 				json_parser.select("/module_status"), 			"string");
	g_main_handler.update_databse_status(str_ps_name, "audio_play_buffer_sec",		json_parser.select("/audio_play_buffer_sec"), 	"integer");
	g_main_handler.update_databse_status(str_ps_name, "audio_play_buffer_msec",		json_parser.select("/audio_play_buffer_msec"), 	"integer");
	g_main_handler.update_databse_status(str_ps_name, "audio_volume",				json_parser.select("/audio_volume"), 			"integer");
	g_main_handler.update_databse_status(str_ps_name, "network_redundancy",	 		json_parser.select("/network_redundancy"), 		"string");
	g_main_handler.update_databse_status(str_ps_name, "network_cast_type", 			json_parser.select("/network_cast_type"),		"string");
	g_main_handler.update_databse_status(str_ps_name, "network_master_ip_addr",		json_parser.select("/network_master_ip_addr"),	"string");
	g_main_handler.update_databse_status(str_ps_name, "network_master_port", 		json_parser.select("/network_master_port"),		"integer");
	g_main_handler.update_databse_status(str_ps_name, "network_slave_ip_addr",		json_parser.select("/network_slave_ip_addr"),	"string");
	g_main_handler.update_databse_status(str_ps_name, "network_slave_port", 		json_parser.select("/network_slave_port"),		"integer");
	g_main_handler.update_databse_status(str_ps_name, "network_mcast_ip_addr",		json_parser.select("/network_mcast_ip_addr"),	"string");
	g_main_handler.update_databse_status(str_ps_name, "network_mcast_port", 		json_parser.select("/network_mcast_port"),		"integer");

	string str_audio_volume = g_main_handler.get_database_status(str_ps_name, "audio_volume");
	g_audio_handler.set_audio_volume(stoi(str_audio_volume));

	return ;
}

// functions: audio_client - pcm playback
void run_pcm_playback(void) {
	string str_ps_name = g_main_handler.get_ps_name();
	
	string device_name		= g_main_handler.get_database_status(str_ps_name, "audio_device_name");
	int    pcm_chunk_size	= g_audio_handler.get_playback_info("pcm_chunk_size");
	int    pcm_sample_rate	= g_audio_handler.get_playback_info("pcm_sample_rate");
	int    pcm_channels		= g_audio_handler.get_playback_info("pcm_channels");
	
	bool is_run = g_pcm_playback_handler.init(device_name, pcm_chunk_size, pcm_sample_rate, pcm_channels);
	if( !is_run ) {
		return ;
	}
	print_debug_info("\033[33mrun_pcm_playback() [%s] PCM init : [%s]\033[0m\n", str_ps_name.c_str(), (is_run ? "success" : "failed"));
	
	int time_delay_sec  	= stoi(g_main_handler.get_database_status(str_ps_name, "audio_play_buffer_sec"));
	int time_delay_msec		= stoi(g_main_handler.get_database_status(str_ps_name, "audio_play_buffer_msec"));
	int time_buffer_delay 	= (time_delay_sec * 1000) + time_delay_msec; // msec
	g_pcm_playback_handler.set_time_buffer_delay(time_buffer_delay);

	g_pcm_playback_handler.set_playback_handler(&pcm_playback_event_handler);
	g_pcm_playback_handler.set_control_handler(&pcm_playback_control_handler);
	
	g_pcm_playback_handler.run();
	
	return ;
}

void stop_pcm_playback(void) {
	g_pcm_playback_handler.stop();
	
	return ;
}

// functions: audio_client - pcm playback event handler
void pcm_playback_event_handler(char **_data, int *_length) {
	static struct timeval t_time_begin, t_time_end;
	static bool   is_reset_time = false;

	string str_ps_name = g_main_handler.get_ps_name();
	
	short *ptr_data = (short *)*_data;
	int	length     = *_length;
	int	abs_value  = 0;
	int peak_value = 0;	

	int  audio_volume	= g_audio_handler.get_audio_volume();
	bool is_amp_deivce	= g_main_handler.is_amp_device(); 

	// change volume
	if( ptr_data != NULL ) {
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
			
		if( !is_reset_time ) {
			memset(&t_time_begin, 0x00, sizeof(t_time_begin));
			gettimeofday(&t_time_begin, NULL);
			is_reset_time = true;
		}
		
		gettimeofday(&t_time_end, NULL);
		int elapsed_time = (int)g_main_handler.calc_diff_time(t_time_begin, t_time_end);
				
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
		muteTime = (int)g_main_handler.calc_diff_time(t_mute_time_begin, t_mute_time_end);
		
		if( muteTime >= TIME_SET_MUTE_RELAY ) {
			fprintf(stderr, "pcm_playback_control_handler() unmute, time elapsed : %d us \n", muteTime);
			memset(&t_mute_time_end, 0x00, sizeof(t_mute_time_end));

			g_mute_relay_handler.increase_audio_mute();
		}
		
	} else {
		is_reset_mute_time = false;
	}
	
	return ;
}

// functions: audio_client - network
void init_client_unicast(void) {
	string str_ps_name = g_main_handler.get_ps_name();
		
	string	str_hostname	= g_main_handler.get_network_hostname();
	string	str_redundancy	= g_main_handler.get_database_status(str_ps_name, 		"network_redundancy");
	string 	str_master_ip	= g_main_handler.get_database_status(str_ps_name, 		"network_master_ip_addr");
	string 	str_slave_ip	= g_main_handler.get_database_status(str_ps_name, 		"network_slave_ip_addr");
	string	str_master_port	= g_main_handler.get_database_status(str_ps_name, 		"network_master_port");
	string	str_slave_port	= g_main_handler.get_database_status(str_ps_name, 		"network_slave_port");
	int		num_master_port	= stoi(str_master_port);
	int		num_slave_port	= stoi(str_slave_port);
	
	g_socket_unicast_client.set_hostname(str_hostname);
	g_socket_unicast_client.set_server_redundancy(str_redundancy);
	g_socket_unicast_client.set_server_info("master", str_master_ip, num_master_port);
	g_socket_unicast_client.set_server_info("slave",  str_slave_ip,  num_slave_port);
	
	g_socket_unicast_client.set_connect_handler(&network_client_connect_handler);
	g_socket_unicast_client.set_data_handler(&network_client_data_handler);
	
	return ;
}

void run_client_unicast(void) {
	init_client_unicast();
	
	if( g_socket_unicast_client.init() ) {
		g_socket_unicast_client.run();
	}
	
	return ;
}

void init_client_multicast(void) {
	string str_ps_name = g_main_handler.get_ps_name();
		
	string	str_server_ip_addr	= g_main_handler.get_database_status(str_ps_name, 		"network_mcast_ip_addr");
	string	str_server_port		= g_main_handler.get_database_status(str_ps_name, 		"network_mcast_port");
	int 	num_server_port		= stoi(str_server_port); 
	
	g_socket_multicast_client.set_server_info(str_server_ip_addr, num_server_port);
	
	g_socket_multicast_client.set_connect_handler(&network_client_connect_handler);
	g_socket_multicast_client.set_data_handler(&network_client_data_handler);
	
	return ;
}

void run_client_multicast(void) {
	init_client_multicast();
	
	if( g_socket_multicast_client.init() ) {
		g_socket_multicast_client.run();
	}
	
	return ;
}

void stop_client_all(void) {
	string str_ps_name = g_main_handler.get_ps_name();
	
	print_debug_info("stop_client_all() %s stop : [all] \n", str_ps_name.c_str());
	
	g_socket_unicast_client.stop();
	g_socket_multicast_client.stop();
	
	return ;
}

// functions: audio_client - network event handler
void network_client_connect_handler(string _type, bool _is_connect, int _connect_case) {
	g_mutex_network_client_event.lock();
	
	string 	str_current_ip_addr;
	int		num_current_port;
	
	string	str_cast_type;
	
	if( _type.compare("unicast") == 0 ) {
		str_current_ip_addr = g_socket_unicast_client.get_current_server_ip();
		num_current_port	= g_socket_unicast_client.get_current_server_port();
		
		str_cast_type = "Unicast";
		
	} else {
		str_current_ip_addr = g_socket_multicast_client.get_server_ip();
		num_current_port	= g_socket_multicast_client.get_server_port();
		
		str_cast_type = "Multicast";
	}
	
	string str_ps_name = g_main_handler.get_ps_name();
	g_main_handler.set_alive_status(_is_connect);
	
	char str_log_msg[1024];
	
	if( _is_connect ) {
		g_main_handler.set_current_server_info(str_current_ip_addr, num_current_port);
		
		sprintf(str_log_msg, "[{STR_SETUP_CLIENT}] [%s][%s] {STR_JS_START_CLIENT}", 
					str_cast_type.c_str(), str_current_ip_addr.c_str());
		g_log_handler.info(str_log_msg);
		
	} else {
		g_main_handler.set_current_server_info("", -1);
		
		if( g_mute_relay_handler.is_unmute() ) {
			g_mute_relay_handler.decrease_audio_mute();
		}
		stop_pcm_playback();
		
		g_audio_handler.set_level_value(0);
		ws_send_event_handler(str_ps_name, WS_SND_CMD_LEVEL_INFO);
		
		if( _connect_case == NETWORK_CONNECT_NORNAL ) {
			g_log_handler.info("[{STR_SETUP_CLIENT}] {STR_CLIENT_OP_STOP}");
		
		} else if( _connect_case == NETWORK_CONNECT_FAILED ) {
			sprintf(str_log_msg, "[{STR_SETUP_CLIENT}] [%s][%s] {STR_CLIENT_CONNECT_FAILED}", 
						str_cast_type.c_str(), str_current_ip_addr.c_str());
			g_log_handler.info(str_log_msg);
		}
	}
	
	ws_send_event_handler(str_ps_name, WS_SND_CMD_OPER_INFO);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_VOLUME_INFO);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_ALIVE_INFO);
	
	g_mutex_network_client_event.unlock();
	
	return ;
}



void network_client_data_handler(char *_data, int _length) {
	g_mutex_network_data_event.lock();
	
	tuple<char *, int> offset_info;
	HEADER_INFO_t t_header_info;
	memcpy(&t_header_info, _data, sizeof(t_header_info));
	
	// playback 이 실행 중이지 않고, 수신 데이터가 'play' 일 때 
	if( !g_pcm_playback_handler.is_term() ) {
		if( t_header_info.status == 0x01 ) {
			g_audio_handler.set_playback_info("pcm_chunk_size",	 t_header_info.data_size);
			g_audio_handler.set_playback_info("pcm_sample_rate", t_header_info.sample_rate);
			g_audio_handler.set_playback_info("pcm_channels", 	 t_header_info.channels);
			g_audio_handler.set_playback_info("encode_mode", 	 t_header_info.encode_type);
			g_audio_handler.set_playback_info("encode_quality",	 t_header_info.mp3_quality);
			g_audio_handler.set_playback_info("mp3_sample_rate", t_header_info.sample_rate);
			
			run_pcm_playback();
			
			string str_ps_name = g_main_handler.get_ps_name();
			ws_send_event_handler(str_ps_name, WS_SND_CMD_OPER_INFO);
		} // end of status : 0x01
	}

	g_queue_pcm_playback.enqueue(_data, _length);
	offset_info = g_queue_pcm_playback.dequeue();
	
	char *ptr_data  = get<0>(offset_info);
	int   data_size = get<1>(offset_info);

	g_pcm_playback_handler.set_data_handler(ptr_data, data_size);

	g_mutex_network_data_event.unlock();
	
	return ;
}

void playback_mute_handler(bool _status) {
	if( _status ) {
		if( !g_mute_relay_handler.is_unmute() ) {
			g_mute_relay_handler.increase_audio_mute();
		}
		
	} else {
		if( g_mute_relay_handler.is_unmute() ) {
			g_mute_relay_handler.decrease_audio_mute();
		}
	}
	
	return ;
}

void playback_term_handler(void) {
	g_audio_handler.set_playback_info("pcm_sample_rate", -1);
	g_audio_handler.set_playback_info("pcm_channels", 	 -1);
	g_audio_handler.set_playback_info("encode_mode", 	 -1);
	g_audio_handler.set_playback_info("encode_quality",	 -1);
	g_audio_handler.set_playback_info("mp3_sample_rate", -1);
	
	string  str_ps_name = g_main_handler.get_ps_name();
	ws_send_event_handler(str_ps_name, WS_SND_CMD_OPER_INFO);
	
	g_audio_handler.set_level_value(0);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_LEVEL_INFO);
	
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
				g_main_handler.set_debug_print();
				
				g_ws_audio_control_handler.set_debug_print();
				g_ws_audio_client_handler.set_debug_print();
				
				g_pcm_playback_handler.set_debug_print();
				
				g_queue_pcm_playback.set_debug_print();
				
				g_socket_unicast_client.set_debug_print();
				g_socket_multicast_client.set_debug_print();
				
				g_log_handler.set_debug_print();
				
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
	g_main_handler.set_database_path(PATH_MODULE_DB_FILE);
	g_main_handler.set_ps_name(basename(_argv[0]));
	
	string str_ps_name = g_main_handler.get_ps_name();
	string str_ps_ctrl = g_main_handler.get_ps_name().append("_control");
	
	if( !g_main_handler.is_module_use(str_ps_name) ) {
		print_debug_info("main() module [%s] disabled\n", str_ps_name.c_str());
		
		while( !g_sig_handler.is_term() ) {
			sleep(TIME_WS_CHECK_LOOP);
		}
		
		print_debug_info("main() process has been terminated.\n");
		
		return 0;
	}

	
	// init websocket handler : audio_client_control
	if( !g_ws_audio_control_handler.init("127.0.0.1", str_ps_ctrl) ) {
		print_debug_info("main() websocket interface connection failed, restart process\n");
		return -1;
	}
	g_ws_audio_control_handler.set_route_to(WebsocketHandler::WEB_ONLY);
	g_ws_audio_control_handler.set_event_handler(&ws_recv_event_handler);
	
	g_ws_audio_client_handler.init("127.0.0.1", str_ps_name);
	g_ws_audio_client_handler.set_route_to(WebsocketHandler::ALL);
	g_ws_audio_client_handler.set_event_handler(&ws_recv_event_handler);
	
	// load env json parameter
	g_main_handler.get_env_status();
	
	
	// init [pcm playback] queue handler
	g_queue_pcm_playback.init(NUM_QUEUE_SCALE);
	g_queue_pcm_playback.reset_queue_unit(NUM_QUEUE_SIZE + sizeof(HEADER_INFO_t));
	g_queue_pcm_playback.set_min_dequeue_cnt(NUM_MIN_DEQUEUE_CNT);
	

	// init pcm audio info
	g_pcm_playback_handler.set_mute_handler(&playback_mute_handler);
	g_pcm_playback_handler.set_term_handler(&playback_term_handler);
	
	string str_audio_volume = g_main_handler.get_database_status(str_ps_name, "audio_volume");
	g_audio_handler.set_audio_volume(stoi(str_audio_volume));
	
	
	// init unicast/multicast parameter 
	init_client_unicast();
	init_client_multicast();
	
	if( !g_main_handler.is_module_status(str_ps_name) ) {
		g_main_handler.set_alive_status(false);
		g_audio_handler.set_level_value(0);
		
		ws_send_event_handler(str_ps_name, WS_SND_CMD_LEVEL_INFO);
		ws_send_event_handler(str_ps_name, WS_SND_CMD_VOLUME_INFO);
		ws_send_event_handler(str_ps_name, WS_SND_CMD_ALIVE_INFO);
		
	} else {
		g_main_handler.set_alive_status(false);
		string str_cast_type = g_main_handler.get_database_status(str_ps_name, "network_cast_type");
		
		if( str_cast_type.compare("unicast") == 0 ) {
			print_debug_info("main() [%s] run : [unicast] \n", str_ps_name.c_str());
			run_client_unicast();
			
		} else if( str_cast_type.compare("multicast") == 0 ) {
			print_debug_info("main() [%s] run : [multicast] \n", str_ps_name.c_str());
			run_client_multicast();
		}
	}
	
	while( !g_sig_handler.is_term() ) {
		sleep(TIME_WS_CHECK_LOOP);
		
		if( g_ws_audio_control_handler.is_term() ) {
			g_ws_audio_control_handler.reconnect();
		}
		
		if( g_ws_audio_client_handler.is_term() ) {
			g_ws_audio_client_handler.reconnect();
		}
	}
	
	print_debug_info("main() audio mute relay set off\n");
	g_pcm_playback_handler.reset_control_handler();
	while( g_mute_relay_handler.is_unmute() ) {
		g_mute_relay_handler.decrease_audio_mute();
		usleep(TIME_SET_MUTE_RELAY);
	}
	
	stop_client_all();
	g_queue_pcm_playback.free();
	
	g_main_handler.set_alive_status(false);
	g_audio_handler.set_level_value(0);
	
	ws_send_event_handler(str_ps_name, WS_SND_CMD_LEVEL_INFO);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_VOLUME_INFO);
	ws_send_event_handler(str_ps_name, WS_SND_CMD_ALIVE_INFO);
			
	print_debug_info("main() process has been terminated.\n");
	
	return 0;
}
