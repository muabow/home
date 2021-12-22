#include "main.h"

// functions
void print_debug_info(const char *_format, ...) {
	if( !g_is_debug_print ) return ;

	fprintf(stdout, "main::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);

	return ;
}

void print_help(char *_argv) {
	fprintf(stdout, "Usage: %s [option]\n"
					"  -h           help\n"
			        "  -v           display debug message\n", _argv);
	return ;
}


// event handler functions
static void ws_event_interface_handler(const char _cmd_id, const char _is_binary, const int _length, const void *_data, WebsocketHandler *_this) {
	if( sig_handler.is_term() ) {
		return ;
	}
	g_event_if_mutex.lock();

	string uri_name = _this->get_uri_name();
	print_debug_info("\033[33mws_event_interface_handler() event - uri: [%s], cmd_id: [0x%02x], is_binary: [%d], length: [%d]\033[0m\n", uri_name.c_str(), _cmd_id, _is_binary, _length);

	if( !_is_binary ) {
		print_debug_info("\033[33mws_event_interface_handler() event - data: [%s]\033[0m\n", (char *)_data);

	} else {
		print_debug_info("\033[33mws_event_interface_handler() event - data: binary \033[0m\n");
	}

    /*
       # Header Code
       +-------+------------------+------------------+--------------+--------------------------------------+
       | Code  | Function         | Arguments        | Return       | Desc                                 |
       +-------+------------------+------------------+--------------+--------------------------------------+
       | 0x10  | device regist    | JSON string      | JSON string  | -                                    |
       | 0x11  | device remove    | JSON string      | JSON string  | -                                    |
       | 0x12  | send data        | JSON string      | JSON string  | -                                    |
       | 0x01  | connect browser  | JSON string      | JSON string  | -                                    |
       | 0x20  | display list     | JSON string      | JSON string  | -                                    |
       +-------+------------------+------------------+--------------+--------------------------------------+

       # Data Format example
       : {"ip_addr": "127.0.0.1", "uri": "audio_client", "cmd_id": 0x00}
     */

    switch( _cmd_id ) {
    	case 0x10 :	// device regist
    		if( _length == 0 ) {
    			print_debug_info("\033[33mws_event_interface_handler() connection reset by peer, process term \033[0m\n");
    			g_event_if_mutex.unlock();
    			
				raise(SIGINT);
				return ;
			}

    		if( ws_event_if_func_regist((char *)_data) ) {
    			ws_event_if_func_display_list();
    		}
    		break;

    	case 0x11 : // device remove
    		print_debug_info("\033[33mws_event_interface_handler() function: remove \033[0m\n");
    		if( ws_event_if_func_remove((char *)_data) ) {
    			ws_event_if_func_display_list();
    		}
    		break;

    	case 0x12 : // send data
    		print_debug_info("\033[33mws_event_interface_handler() function: send \033[0m\n");
    		ws_event_if_func_send((char *)_data);
    		break;

    	case 0x01 : // connect browser
    	case 0x20 : // display websocket handler list
    		print_debug_info("\033[33mws_event_interface_handler() function: display list \033[0m\n");
    		ws_event_if_func_display_list();
    		break;

    	default :
    		if( _length == 0 ) {
				print_debug_info("\033[33mws_event_interface_handler() connection reset by peer, reconnect \033[0m\n");
				g_event_if_mutex.unlock();
				
				raise(SIGINT);
				return ;
			}
    		print_debug_info("\033[33mws_event_interface_handler() unknown command : [0x%02x]\033[0m\n", _cmd_id);
    		break;
    }

    g_event_if_mutex.unlock();

	return ;
}

static void ws_event_client_handler(const char _cmd_id, const char _is_binary, const int _length, const void *_data, WebsocketHandler *_this) {
	if( sig_handler.is_term() ) {
		return ;
	}
	g_event_mutex.lock();
	
	string uri_name = _this->get_uri_name();
	string ip_addr  = _this->get_ip_addr();

	/*
	print_debug_info("ws_event_client_handler() event - ip_addr: [%s], uri: [%s], cmd_id: [0x%02x], is_binary: [%d], length: [%d]\n",
    		ip_addr.c_str(), uri_name.c_str(), _cmd_id, _is_binary, _length);

	if( !_is_binary ) {
		print_debug_info("ws_event_client_handler() event - data: [%s]\n", (char *)_data);

	} else {
		print_debug_info("ws_event_client_handler() event - data: binary \n");
	}
	*/

    ostringstream str_json;
    string s_data;

    if( (char *)_data == NULL ) {
    	s_data = "\"\"";
    } else {
    	s_data = string((char *)_data);
    }

	str_json << "{" \
			 << "\"ip_addr\": \"" << ip_addr          << "\", " \
			 << "\"uri\": \""     << uri_name         << "\", " \
			 << "\"cmd_id\": "    << to_string(_cmd_id) << ", " \
			 << "\"data\": "      << s_data \
			 << "}";

	string buffer(str_json.str());

	g_ws_interface_handler.send(_cmd_id, buffer); 

    g_event_mutex.unlock();

    return ;
}

// event function
bool ws_event_if_func_regist(char *_data) {
	JsonParser json_parser;

	string str_data(_data);
	json_parser.parse(str_data);

	string ip_addr = json_parser.select("/ip_addr");
	string uri     = json_parser.select("/uri");

	if( ip_addr.compare("") == 0 || uri.compare("") == 0 ) {
		print_debug_info("ws_event_if_func_regist() regist failed : invalid value [%s/%s]\n",
				ip_addr.c_str(), uri.c_str());
		return false;
	}

	return g_ws_client_handler.init(ip_addr, uri);
}

bool ws_event_if_func_remove(char *_data) {
	JsonParser json_parser;

	string str_data(_data);
	json_parser.parse(str_data);

	string ip_addr = json_parser.select("/ip_addr");
	string uri     = json_parser.select("/uri");

	if( ip_addr.compare("") == 0 || uri.compare("") == 0 ) {
		print_debug_info("ws_event_if_func_remove() regist valid failed : [%s/%s]\n",
				ip_addr.c_str(), uri.c_str());
		return false;
	}

	return g_ws_client_handler.remove(ip_addr, uri);
}

bool ws_event_if_func_regist_str(string _ip_addr, string _uri) {
	if( _ip_addr.compare("") == 0 || _uri.compare("") == 0 ) {
		print_debug_info("ws_event_if_func_regist() regist failed : invalid value [%s/%s]\n",
				_ip_addr.c_str(), _uri.c_str());
		return false;
	}

	return g_ws_client_handler.init(_ip_addr, _uri);
}


bool ws_event_if_func_remove_str(string _ip_addr, string _uri) {
	if( _ip_addr.compare("") == 0 || _uri.compare("") == 0 ) {
		print_debug_info("ws_event_if_func_remove_str() regist valid failed : [%s/%s]\n",
				_ip_addr.c_str(), _uri.c_str());
		return false;
	}

	return g_ws_client_handler.remove(_ip_addr, _uri);
}

void ws_event_if_func_send(char *_data) {
	if(_data == NULL) {
		return;
	}

	JsonParser json_parser;

	string str_data(_data);
	json_parser.parse(str_data);

	string ip_addr = json_parser.select("/ip_addr");
	string uri     = json_parser.select("/uri");
	string cmd_id  = json_parser.select("/cmd_id");
	string data    = json_parser.select("/data");

	if( ip_addr.compare("") == 0 || uri.compare("") == 0 || cmd_id.compare("") == 0 ) {
		print_debug_info("ws_event_if_func_send() send valid failed : [%s/%s][%s]\n",
				ip_addr.c_str(), uri.c_str(), cmd_id.c_str());
		return ;
	}

	if( g_ws_client_handler.get_alive_info(ip_addr, uri) ) {
		g_ws_client_handler.send(ip_addr, uri, stoi(cmd_id), data);
	}

	return ;
}

void ws_event_if_func_display_list(void) {
	string str_list = g_ws_client_handler.get_ws_handler_list();

	g_ws_interface_handler.send(0x20, str_list);
    
	return ;
}


int main(int _argc, char *_argv[]) {
    // signal handler
	sig_handler.set_signal(SIGINT);
	sig_handler.set_signal(SIGKILL);
	sig_handler.set_signal(SIGTERM);
	sig_handler.set_signal(SIGPIPE);

	int opt;
	while( (opt = getopt(_argc, _argv, "vh")) != -1 ) {
		switch( opt ) {
			case 'v' :
				fprintf(stdout, "# display debug message set on\n");
				g_is_debug_print = true;

				g_ws_interface_handler.set_debug_print();
				g_ws_client_handler.set_debug_print();

				break;

			case 'h' :
				print_help(_argv[0]);
				return -1;
				break;

			default :
				print_help(_argv[0]);
				return -1;
				break;
		}
	}

	if( !g_ws_interface_handler.init("127.0.0.1", "wsc_interface", g_is_debug_print) ) {
		print_debug_info("g_ws_interface_handler() failed\n");

		return -1;
	}

	g_ws_interface_handler.set_event_handler(&ws_event_interface_handler);
	g_ws_client_handler.set_event_handler(&ws_event_client_handler);

	NetworkHandler network_handler;

	while( !sig_handler.is_term() ) {
		sleep(INTERVAL_NETWORK_CHECK);
		
		if( g_ws_interface_handler.is_term() ) {
			g_ws_interface_handler.reconnect();
			continue;
		}

		int v_size = g_ws_client_handler.get_ws_handler_count();

		for( int idx = 0 ; idx < v_size ; idx++ ) {
			tuple<WebsocketHandler *, bool, bool> ws_handler = g_ws_client_handler.get_ws_handler_info(idx);

			string uri_name = get<0>(ws_handler)->get_uri_name();
			string ip_addr  = get<0>(ws_handler)->get_ip_addr();

			if( network_handler.is_device_alive(ip_addr) ) {
				if( !get<1>(ws_handler) ) {
					g_ws_client_handler.set_alive_info(ip_addr, uri_name, "eth_up", true);
					g_ws_client_handler.set_alive_info(ip_addr, uri_name, "alive",  true);

					get<0>(ws_handler)->term();
					get<0>(ws_handler)->set_route_wscif();
					get<0>(ws_handler)->init(ip_addr.c_str(), uri_name.c_str(), g_is_debug_print);

					ws_event_if_func_display_list();
				}

				if( get<0>(ws_handler)->is_term() ) {
					get<0>(ws_handler)->term();
					get<0>(ws_handler)->set_route_wscif();
					get<0>(ws_handler)->init(ip_addr.c_str(), uri_name.c_str(), g_is_debug_print);

					ws_event_if_func_display_list();
				}

			} else {
				if( get<1>(ws_handler) ) {
					g_ws_client_handler.set_alive_info(ip_addr, uri_name, "eth_up", false);
					g_ws_client_handler.set_alive_info(ip_addr, uri_name, "alive",  false);

					get<0>(ws_handler)->term();

					ws_event_if_func_display_list();
				}

				if( get<0>(ws_handler)->is_run() ) {
					get<0>(ws_handler)->term();

					ws_event_if_func_display_list();
				}
			}
		}
	}
		
	print_debug_info("main() end of process\n");

	return 0;
}
