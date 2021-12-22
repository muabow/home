#ifndef __API_SOCKET_CLIENT_H__
#define __API_SOCKET_CLIENT_H__

#include <stdarg.h>
#include <stdio.h>
#include <sys/socket.h>
#include <sys/types.h>
#include <arpa/inet.h>
#include <netinet/in.h>
#include <netinet/tcp.h>
#include <errno.h>
#include <string.h>
#include <stdlib.h>
#include <unistd.h>
#include <signal.h>
#include <sys/select.h>
#include <limits.h>
#include <libgen.h>
#include <fcntl.h>
#include <sys/time.h>
#include <netinet/ether.h>
#include <net/if.h>
#include <sys/ioctl.h>

#include <string>
#include <thread>
#include <vector>
#include <mutex>
#include <tuple>
#include <memory>
#include <iostream>
#include <algorithm>

#include "api_json_parser.h"

#define PATH_MODULE_NETWORK_CONF		"/opt/interm/public_html/modules/network_setup/conf/network_stat.json"

using namespace std;

class SOCKET_UnicastClient {
	const int	NETWORK_CONNECT_NORNAL	=	0;
	const int	NETWORK_CONNECT_FAILED	=	1;
	
	struct HOST_INFO {
		char 			hostname[128];
		char			mac_addr[128];
	} typedef HOST_INFO_t;

	struct PLAY_INFO {
		int             rate;
		int             channels;
		int             chunk_size;
		int             idx;
		int             encode_mode;
		unsigned int    pcm_buffer_size;
		unsigned int    pcm_period_size;
	} typedef PLAY_INFO_t;
	
	struct ENCODE_INFO {
		int   			chunk_size;
		short   		quality;
		int     		sample_rate;
	} typedef ENCODE_INFO_t;
	
	const int TIME_SLEEP_LOOP		= 1;			// sec
	
	const int TIME_TIMEOUT_SEC 		= 1;			// sec
	const int TIME_TIMEOUT_MSEC 	= 0;			// msec
			
	const int TIME_RCV_TIMEOUT_SEC	= 1; 			// sec
	const int TIME_RCV_TIMEOUT_MSEC	= 0;			// msec
	
	const int SIZE_SCALE_RCVBUF 	= 10;
	
	const int TYPE_CAST_UNICAST		= 1;
	const int TYPE_CAST_MULTICAST	= 0;
	
	const int DFLT_SERVER_PORT		= 5454;
	
	private :
		bool			is_debug_print	= false;
		void			print_debug_info(const char *_format, ...);

		bool 			_get_mac_address(void);
		bool 			get_mac_address(void);
		void			init_server_list(void);
		void			set_socket_option(void);
		bool			connect_server(void);
		bool			select_socket_event(void);
		
		bool			is_init;
		bool			is_run;
		bool			is_loop;
		
		int				socket_fd;
		
		bool			is_redundancy;
		int 			server_index;			// connected server index (0:master, 1:slave)
		string			current_server_ip;		// connected server ip
		int				current_server_port;	// coneected server port
		
		string			master_ip_addr;
		int				master_port;
		string			slave_ip_addr;
		int				slave_port;
		
		string			hostname;
		
		thread			thread_func;
		
		void			(*event_data_handle)(char *, int);
		void			(*event_connect_handle)(string, bool, int, int *);
		
		vector<tuple<string, int, bool>>	v_server_list;
		
		HOST_INFO_t		t_host_info;
		PLAY_INFO_t		t_play_info;
		ENCODE_INFO_t	t_encode_info;
		
	public  :
		SOCKET_UnicastClient(bool _is_debug_print = false);
		~SOCKET_UnicastClient(void);
		
		void 			set_debug_print(void);
		
		void			set_server_info(string _type, string _ip_addr, int _port);
		void			set_server_redundancy(string _redundancy);
		void			set_hostname(string _hostname);
		
		string			get_hostname(void);
		string			get_server_ip_addr(string _type);
		int				get_server_port(string _type);
		int				get_play_info(string _type);
		int				get_current_server_index(void);
		string			get_current_server_ip(void);
		int				get_current_server_port(void);
		
		void			set_data_handler(void (*_func)(char *, int));
		void			set_connect_handler(void (*_func)(string, bool, int, int *));
		
		bool 			init(void);
		void 			run(void);
		void 			stop(void);

		void 			execute(void);
		
}; // end of class : SOCKET_UnicastClient


class SOCKET_MulticastClient {
	const int	NETWORK_CONNECT_NORNAL	=	0;
	const int	NETWORK_CONNECT_FAILED	=	1;
		
	struct PLAY_INFO {
		int             rate;
		int             channels;
		int             chunk_size;
		int             idx;
		int             encode_mode;
		unsigned int    pcm_buffer_size;
		unsigned int    pcm_period_size;
	} typedef PLAY_INFO_t;
	
	struct ENCODE_INFO {
		int   			chunk_size;
		short   		quality;
		int     		sample_rate;
	} typedef ENCODE_INFO_t;
	
	struct HEADER_INFO {
		unsigned int    seq_number;     	// 4
		int             pcm_sample_rate;   	// 8
		short           pcm_channels;     	// 10
		short           data_size; 	    	// 12
		unsigned short  crc_value;			// 14
		short           encode_mode;    	// 16 (0 : pcm, 1 : mp3)
		int             mp3_sample_rate;	// 20
		short           mp3_bit_rate;   	// 22
		short           pcm_chunk_size; 	// 24
		unsigned int    pcm_buffer_size;	// 28
		unsigned int    pcm_period_size;	// 32
		char            rsvd2[32];      	// 64
	} typedef HEADER_INFO_t;
	
	const int TIME_SLEEP_LOOP		= 1;			// sec
	const int TIME_TIMEOUT_SEC 		= 1;			// sec
	const int TIME_TIMEOUT_MSEC 	= 0;			// msec
	
	private :
		bool			is_debug_print	= false;
		void			print_debug_info(const char *_format, ...);
		
		bool			is_init;
		bool			is_run;
		bool			is_loop;
		
		int				socket_fd;
		
		string			str_ip_addr;
		int				num_port;
		
		bool			select_socket_event(void);
		
		void			(*event_data_handle)(char *, int);
		void			(*event_connect_handle)(string, bool, int, int *);
				
		PLAY_INFO_t		t_play_info;
		ENCODE_INFO_t	t_encode_info;
		
		thread			thread_func;
			
	public :
		SOCKET_MulticastClient(bool _is_debug_print = false);
		~SOCKET_MulticastClient(void);
	
		void 			set_debug_print(void);

		void			set_server_info(string _ip_addr, int _port);
		
		string			get_server_ip(void);
		int				get_server_port(void);
		int				get_play_info(string _type);
		
		void			set_data_handler(void (*_func)(char *, int));
		void			set_connect_handler(void (*_func)(string, bool, int, int *));
		
		bool 			init(void);
		void 			run(void);
		void 			stop(void);
		
		void 			execute(void);
		
}; // end of class : SOCKET_MulticastClient


#endif