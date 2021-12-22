#ifndef __CLASS_SOCKET_SERVER_H__
#define __CLASS_SOCKET_SERVER_H__

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
#include <vector>
#include <mutex>
#include <tuple>
#include <memory>
#include <ctime>

#include "api_json_parser.h"

using namespace std;

class SOCKET_UnicastWorker {
	struct HEADER_INFO {
		char		status;			// 1  // stop, play, pause
		char		encode_type;    // 2  // pcm, mp3
		short		data_size;     	// 4  // 1152
		int			sample_rate;   	// 8  // 44100
		short		channels;     	// 10 // 2
		short		mp3_quality;	// 12 // 2
	} typedef HEADER_INFO_t;
	
	const int			TIME_TIMEOUT_SEC	= 0;
	const int			TIME_TIMEOUT_MSEC	= 10000;	// 10 ms
	const int			TIME_EMPTY_WAIT		= 1000;		// 1 ms
	const int			TIME_ALIVE_PEROID	= 500000;	// 500 ms
	
	private :
		bool			is_debug_print = false;
		void			print_debug_info(const char *_format, ...);
		
		string			ip_addr;
		string			mac_addr;
		string			hostname;
		int				time_connect;
		int				time_disconnect;
		
		bool			is_run;
		bool			is_loop;
		
		int				index;
		int				socket_fd;
		int				num_reconnect;
		
		void			(*event_handle)(int, bool);
		
		thread			*p_thread_func;
		
		vector<tuple<char *, int>>	v_queue_data;

	public  :
		SOCKET_UnicastWorker(bool _is_debug_print = false);
		~SOCKET_UnicastWorker(void);
		
		void 			set_debug_print(void);

		void			set_index(int _index);
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

		bool			select_socket_event(void);
		void			execute(void);
		
};


class SOCKET_UnicastServer {
	typedef void (*func_ptr)(int, bool);

	struct HOST_INFO {
		char 			hostname[128];
		char			mac_addr[128];
	} typedef HOST_INFO_t;
	
	const	char		*STR_MSG_HOSTNAME	= "hostName:";
	
	const	int			DFLT_SERV_PORT		= 5455;
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
		
		mutex			mutex_counter;
		mutex			mutex_worker_func;
		
		thread			thread_func;
		
		void			(*event_handle)(int, bool);
		
		vector<unique_ptr<SOCKET_UnicastWorker>> v_worker_func;
		
	public  :
		SOCKET_UnicastServer(bool _is_debug_print = false);
		~SOCKET_UnicastServer(void);
		
		void 			set_debug_print(void);
		
		bool			is_alive_status(void);
		
		void			set_server_port(int _port);
		void			set_num_max_client(int _count);
		
		string			get_client_list(void);
		string 			get_client_info(int _idx, string _type);
		
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
	
	struct HEADER_INFO {
		char		status;			// 1  // stop, play, pause
		char		encode_type;    // 2  // pcm, mp3
		short		data_size;     	// 4  // 1152
		int			sample_rate;   	// 8  // 44100
		short		channels;     	// 10 // 2
		short		mp3_quality;	// 12 // 2
	} typedef HEADER_INFO_t;
	
	const	char		*STR_MSG_HOSTNAME	= "hostName:";
	
	const	int			DFLT_SERV_PORT		= 5455;
	
	const	int			SIZE_SCALE_SNDBUF	= 10;
	
	const	int			TIME_ACCEPT_SEC		= 1;
	const	int			TIME_ACCEPT_MSEC	= 0;
	const	int			TIME_RCV_TIMEOUT	= 2;
		
	const int			TIME_EMPTY_WAIT		= 1000;		// 1 ms
	const int			TIME_ALIVE_PEROID	= 500000;	// 500 ms
		
	private :
		bool			is_debug_print	= false;
		void			print_debug_info(const char *_format, ...);
		
		bool			is_init;
		bool			is_run;
		bool			is_loop;
				
		int				socket_fd;
		int				port;
		string			ip_addr;
		
		thread			thread_func;
		
		struct sockaddr_in	t_sock_addr;
		
		vector<tuple<char *, int>>	v_queue_data;
		
		
	public  :
		SOCKET_MulticastServer(bool _is_debug_print = false);
		~SOCKET_MulticastServer(void);
		
		void 			set_debug_print(void);
		
		bool			is_alive_status(void);
		
		void			set_server_port(int _port);
		void			set_ip_addr(string _ip_addr);

		string			get_ip_addr(void);
		
		bool			init(void);
		void			stop(void);
		void			run(void);

		void			send_data_handler(char *_data, int _size);
		
		void			execute(void);
};
#endif