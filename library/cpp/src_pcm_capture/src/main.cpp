#include "main.h"


// functions: common
void print_debug_info(const char *_format, ...) {
	if( !g_is_debug_print ) return ;
	
	fprintf(stdout, "main::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	return ;
}


// function: event handler
void pcm_capture_event_handler(char *_data, int _length) {
	if( g_fptr != NULL || _length != 0 ) {
		fwrite(_data, sizeof(char), _length, g_fptr);

		print_debug_info("pcm_capture_event_handler() Creating PCM file...\r");
	}

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
				
				g_signal_handler.set_debug_print();
				g_pcm_capture_handler.set_debug_print();
				
				break;
				
			default :
				printf("usage: %s [option]\n", basename(_argv[0]));
				printf("  -v : print normal debug message \n");
				return -1;
				
				break;
		}
	}
	
	// signal handler
	g_signal_handler.set_signal(SIGINT);
	g_signal_handler.set_signal(SIGKILL);
	g_signal_handler.set_signal(SIGTERM);
	g_signal_handler.set_ignore(SIGPIPE);

	// [user define] PCM file target
	string	str_target_path	= "/tmp/capture.pcm";
	
	// [user define] ALSA capture parameter
	string	str_device_name = "default";
	int		num_chunk_size	= 4096;
	int		num_sample_rate	= 48000;
	int 	num_channels	= 1;

	g_fptr = fopen(str_target_path.c_str(), "wb");

	if( !g_pcm_capture_handler.init(str_device_name, num_chunk_size, num_sample_rate, num_channels) ) {
		print_debug_info("main() pcm capture handler init() failed.\n");
		return -1;
	}
	g_pcm_capture_handler.set_queue_handler(&pcm_capture_event_handler);
	g_pcm_capture_handler.run();
	
	while( !g_signal_handler.is_term() ) {
		sleep(TIME_ALIVE_LOOP);
	}
	
	g_pcm_capture_handler.stop();
	fclose(g_fptr);
	
	print_debug_info("main() process has been terminated.\n");

	return 0;
}
