#ifndef __CLASS_MAIN_H__
#define __CLASS_MAIN_H__

#include <string.h>
#include <stdarg.h>
#include <fcntl.h>
#include <errno.h>
#include <sys/socket.h>
#include <resolv.h>
#include <netdb.h>
#include <netinet/in.h>
#include <netinet/ip_icmp.h>
#include <sys/types.h>
#include <unistd.h>
#include <error.h>

#include <string>
#include <thread>
#include <vector>
#include <string>
#include <tuple>
#include <mutex>

#include "api_websocket.h"
#include "api_json_parser.h"

using namespace std;

class NetworkHandler {
	static const int			PACKET_SIZE			= 64;
	static const int			COUNT_ICMP_DEAD		= 5;
	static const int			COUNT_CHECK_LOOP	= 5;
	static const int			TIME_LOOP_WAIT		= 40000;
	
	struct packet {
	    struct icmphdr hdr;
	    char msg[PACKET_SIZE - sizeof(struct icmphdr)];
	};
	
	private:
		bool			is_debug_print;
		void			print_debug_info(const char *_format, ...);

		mutex			t_mutex;
		unsigned short	checksum(void *_data, int _length);
		
		
	public :
		NetworkHandler(bool _is_debug_print = false);
		~NetworkHandler(void);
		
		void 			set_debug_print(void);
		
		bool			icmp(string _address);
		bool			is_device_alive(string _address);
		
		void			set_event_handler(void (*_func)(bool, bool));
		
};

class WS_ClientHandler {
	private :
		bool	is_debug_print;
		
		vector<tuple<WebsocketHandler *, bool, bool, string>> v_ws_handle_list;
		NetworkHandler network_handler;
		
		void print_debug_info(const char *_format, ...);
		
	public  :
		WS_ClientHandler(bool _is_debug_print = false);
		~WS_ClientHandler(void);
		
		bool init(string _ip_addr, string _uri);
		bool remove(string _ip_addr, string _uri);
		
		bool   is_created_handler(string _ip_addr, string _uri);
		int    get_index_handler(string _ip_addr, string _uri);
		int    get_ws_handler_count(void);
		string get_ws_handler_list(void);
		void   set_alive_info(string _ip_addr, string _uri, string _type, bool _is_alive);
		bool   get_alive_info(string _ip_addr, string _uri);
		tuple<WebsocketHandler *, bool, bool> get_ws_handler_info(int _index);

		void send(string _ip_addr, string _uri, int _cmd_id, string _data);
		
		void set_debug_print(void);
		
		typedef void (*typeEventHandler)(const char _cmd_id, const char _is_binary, const int _length, const void *_data, WebsocketHandler *_this);
		void set_event_handler(typeEventHandler _funcPtr);
		void (*funcEventHandler)(const char, const char, const int, const void *, WebsocketHandler *);
};

#endif
