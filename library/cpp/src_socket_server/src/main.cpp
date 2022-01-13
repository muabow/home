#include "../include/main.h"


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
// signal handler
void signal_event_handler(int _sig_num) {
	print_debug_info("signal_event_handler() event : [%d] %s\n", _sig_num, strsignal(_sig_num));
	
	return ;
}

// functions: network event handler
void network_server_event_handler(int _index, bool _is_connect) {
	string str_ip_addr  = g_socket_unicast_server.get_client_info(_index, "ip_addr");	
	
	if( _is_connect ) {
		print_debug_info("network_server_event_handler() connect client : [%d] %s\n", _index, str_ip_addr.c_str());
		g_socket_unicast_server.inc_current_count();
		
	} else {
		print_debug_info("network_server_event_handler() disconnect audio client : [%d] %s\n", _index, str_ip_addr.c_str());
		g_socket_unicast_server.dec_current_count();
	}
	
	int num_current_count	= g_socket_unicast_server.get_current_count();
	int num_accure_count	= g_socket_unicast_server.get_accrue_count();
	int num_max_count		= g_socket_unicast_server.get_max_client_count();
	print_debug_info("network_server_event_handler() max[%d], accrue[%d], current[%d]\n", num_max_count, num_accure_count, num_current_count);
	
	return ;
}

// functions: network function
void run_server_unicast(void) {
	print_debug_info("run_server_unicast() run : [unicast] \n");
	g_socket_unicast_server.set_event_handler(&network_server_event_handler);
	
	g_socket_unicast_server.init();
	g_socket_unicast_server.run();
	
	return ;
}

void stop_server_unicast(void) {
	print_debug_info("stop_server_all() stop : [unicast] \n");
	
	g_socket_unicast_server.stop();
	
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
				g_socket_unicast_server.set_debug_print();
				
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
	
	run_server_unicast();

	// test data
	char arr_data[1024] = {0x00, };
	int  size_data = sizeof(arr_data);
	int	 index = 0;
	
	while( !g_sig_handler.is_term() ) {
		sprintf(arr_data, "hello world : [%d]", index++);
		g_socket_unicast_server.send_data_handler(arr_data, size_data);

		sleep(TIME_CHECK_LOOP);
		
	}
	stop_server_unicast();
	
	print_debug_info("main() process has been terminated.\n");

}