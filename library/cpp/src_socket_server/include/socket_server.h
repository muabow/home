#ifndef __SOCKET_SERVER_H__
#define __SOCKET_SERVER_H__

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
#include <queue>

using namespace std;

class SOCKET_UnicastWorker {
	private :
		bool			is_debug_print = false;
		void			print_debug_info(const char *_format, ...);
		
		string			ip_addr;
		int				time_connect;
		int				time_disconnect;
		
		bool			is_run;
		bool			is_loop;
		
		int				index;
		int				socket_fd;
		int				num_reconnect;
		
		void			(*event_handle)(int, bool);
		
		thread			*p_thread_func;
		
		queue<tuple<char *, int>>	li_queue_data;
		
	public  :
		SOCKET_UnicastWorker(bool _is_debug_print = false);
		~SOCKET_UnicastWorker(void);
		
		void 			set_debug_print(void);

		void			set_index(int _index);
		
		bool			is_reconnect(string _ip_addr);
		bool			is_alive(void);	// get is_loop

		string			get_ip_addr(void);
		string			get_mac_addr(void);
		int				get_num_reconnect(void);
		int				get_timestamp(void);
		
		int				get_time_connect(void);
		int				get_time_disconnect(void);
		
		void			init(string _ip_addr, int _socket_fd);
		void			stop(void);
		void			run(void);
		void			reconnect(int _socket_fd);
		
		void			set_event_handler(void (*_func)(int, bool));
		void			set_queue_data(char *_data, int _size);

		void			execute(void);
};


class SOCKET_UnicastServer {
	typedef void (*func_ptr)(int, bool);
	
	const	int			DFLT_SERV_PORT		= 7593;
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
		void			set_socket_option(int _socket_fd);
		
		string			get_client_info(int _idx, string _type);
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


#endif