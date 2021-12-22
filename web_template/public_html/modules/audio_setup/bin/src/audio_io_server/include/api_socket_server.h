#ifndef __API_SOCKET_SERVER_H__
#define __API_SOCKET_SERVER_H__

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

#include <iostream>
#include <string>
#include <thread>
#include <list>
#include <vector>
#include <mutex>
#include <tuple>
#include <memory>
#include <ctime>
#include <queue>

#include "api_json_parser.h"

using namespace std;

class SOCKET_UnicastWorker {
	const	int			TIME_LOOP_SLEEP	= 10;	// us
	
	struct PLAY_INFO {
		int       		rate;
		int        		channels;
		int       		chunk_size;
		int       		idx;
		int				encode_mode;
		unsigned int    pcm_buffer_size;
		unsigned int    pcm_period_size;
	} typedef PLAY_INFO_t;
	
	struct ENCODE_INFO {
		int   			chunk_size;
		short   		quality;
		int     		sample_rate;
	} typedef ENCODE_INFO_t;
	
	private :
		bool			is_debug_print = false;
		void			print_debug_info(const char *_format, ...);
		
		string			ip_addr;
		string			mac_addr;
		string			hostname;
		int				time_connect;
		int				time_disconnect;
		
		double			frame_latency;
		
		bool			is_run;
		bool			is_loop;
		
		int				index;
		int				socket_fd;
		int				num_reconnect;
		
		void			(*event_handle)(int, bool);
		
		thread			*p_thread_func;
		
		queue<tuple<char *, int>>	li_queue_data;

		PLAY_INFO_t		t_play_info;
		ENCODE_INFO_t	t_encode_info;
		
	public  :
		SOCKET_UnicastWorker(bool _is_debug_print = false);
		~SOCKET_UnicastWorker(void);
		
		void 			set_debug_print(void);

		void			set_index(int _index);
		void			set_play_info(int _sample_rate, int _channels, int _chunk_size, int _encode_mode, int _encode_quality);
		void			set_hostname(string _hostname);
		
		bool			is_reconnect(string _ip_addr, string _mac_addr);
		bool			is_alive(void);	// get is_loop

		void			set_frame_latency(double _frame_latency);
		double			get_frame_latency(void);
		
		string			get_ip_addr(void);
		string			get_mac_addr(void);
		string			get_hostname(void);
		int				get_num_reconnect(void);
		int				get_timestamp(void);
		
		int				get_time_connect(void);
		int				get_time_disconnect(void);
		
		void			init(string _ip_addr, string _mac_addr, string _hostname, int _socket_fd);
		void			stop(void);
		void			run(void);
		void			reconnect(int _socket_fd);
		
		void			set_event_handler(void (*_func)(int, bool));
		void			set_queue_data(char *_data, int _size);

		void			execute(void);
		
};


class SOCKET_UnicastServer {
	typedef void (*func_ptr)(int, bool);

	struct HOST_INFO {
		char 			hostname[128];
		char			mac_addr[128];
	} typedef HOST_INFO_t;
	
	struct PLAY_INFO {
		int       		rate;
		int        		channels;
		int       		chunk_size;
		int       		idx;
		int				encode_mode;
		unsigned int    pcm_buffer_size;
		unsigned int    pcm_period_size;
	} typedef PLAY_INFO_t;
	
	struct ENCODE_INFO {
		int   			chunk_size;
		short   		quality;
		int     		sample_rate;
	} typedef ENCODE_INFO_t;
	
	const	char		*STR_MSG_HOSTNAME	= "hostName:";
	
	const	int			DFLT_SERV_PORT		= 5454;
	const	int			DFLT_NUM_MAX_CLIENT	= 20;
	
	const	int			SIZE_SCALE_SNDBUF	= 10;
	const 	int			SIZE_BACK_LOG		= 40;
	
	const	int			TIME_ACCEPT_SEC		= 1;
	const	int			TIME_ACCEPT_MSEC	= 0;
	const	int			TIME_RCV_TIMEOUT	= 2;
		
	private :
		bool			is_debug_print	= false;
		void			print_debug_info(const char *_format, ...);
		
		bool			is_init;
		bool			is_run;
		bool			is_loop;
				
		int				socket_fd;
		int				port;
		
		int				num_max_client;
		int				num_current_client;
		int				num_accrue_client;
		
		double			frame_latency;
		
		mutex			mutex_counter;
		mutex			mutex_worker_func;
		
		thread			thread_func;
		
		void			(*event_handle)(int, bool);
		
		vector<unique_ptr<SOCKET_UnicastWorker>> v_worker_func;
		
		PLAY_INFO_t		t_play_info;
		ENCODE_INFO_t	t_encode_info;
		
	public  :
		SOCKET_UnicastServer(bool _is_debug_print = false);
		~SOCKET_UnicastServer(void);
		
		void 			set_debug_print(void);
		
		bool			is_alive_status(void);
		
		void			set_server_port(int _port);
		void			set_num_max_client(int _count);
		void			set_socket_option(int _socket_fd);
		
		void			set_play_info(int _sample_rate, int _channels, int _chunk_size, string _encode_mode, int _encode_quality);
		void			set_frame_latency(double _frame_latency);
		
		string			get_client_list(void);
		string 			get_client_info(int _idx, string _type);
		
		int				get_play_info(string _type);
		double			get_frame_latency(void);
		
		int				get_max_client_count(void);
		
		int				get_current_count(void);
		void			inc_current_count(void);
		void			dec_current_count(void);
		
		int				get_accrue_count(void);
		void			inc_accrue_count(void);

		void			reset_current_count(void);
		void			reset_accrue_count(void);

		bool			init(void);
		void			stop(void);
		void			run(void);
		
		func_ptr		get_event_handler(void);
		void			set_event_handler(void (*_func)(int, bool));
		void			send_data_handler(char *_data, int _size);

		void			execute(void);	// thread pool
};


class SOCKET_MulticastServer {
	struct HOST_INFO {
		char 			hostname[128];
		char			mac_addr[128];
	} typedef HOST_INFO_t;
	
	struct PLAY_INFO {
		int       		rate;
		int        		channels;
		int       		chunk_size;
		int       		idx;
		int				encode_mode;
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
		short           pcm_channels;      	// 10
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
	
	const	char		*STR_MSG_HOSTNAME	= "hostName:";
	
	const	int			DFLT_SERV_PORT		= 5454;
	
	const	int			SIZE_SCALE_SNDBUF	= 10;
	
	const	int			TIME_ACCEPT_SEC		= 1;
	const	int			TIME_ACCEPT_MSEC	= 0;
	const	int			TIME_RCV_TIMEOUT	= 2;
		
	private :
		bool			is_debug_print	= false;
		void			print_debug_info(const char *_format, ...);
		
		bool			is_init;
		bool			is_run;
		bool			is_loop;
				
		int				socket_fd;
		int				port;
		string			ip_addr;
		
		double			frame_latency;
		
		thread			thread_func;
		
		PLAY_INFO_t		t_play_info;
		ENCODE_INFO_t	t_encode_info;
		
		struct sockaddr_in	t_sock_addr;
		
		queue<tuple<char *, int>>	li_queue_data;
		
		
	public  :
		SOCKET_MulticastServer(bool _is_debug_print = false);
		~SOCKET_MulticastServer(void);
		
		void 			set_debug_print(void);
		
		bool			is_alive_status(void);
		
		void			set_server_port(int _port);
		void			set_ip_addr(string _ip_addr);
		void			set_frame_latency(double _frame_latency);
		void			set_play_info(int _sample_rate, int _channels, int _chunk_size, string _encode_mode, int _encode_quality);

		int				get_play_info(string _type);
		string			get_ip_addr(void);
		
		bool			init(void);
		void			stop(void);
		void			run(void);

		void			send_data_handler(char *_data, int _size);
		
		void			execute(void);
};
#endif