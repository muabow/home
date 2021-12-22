#ifndef __API_WEBSOCKET_H__
#define __API_WEBSOCKET_H__

#include <thread>
#include <mutex>

using namespace std;
	
// 20190521, ver 1.1
class WebsocketHandler {
		const int 	DFLT_WSIF_SOCKET_PORT	= 2100;		// websocket interface unix socket port
		const int	NUM_TYPE_PROCESS_PORT	= 0;		// session type: 0, socket type: ws_router.xml mapping port
		const int 	NUM_SOCKET_TIMEOUT_SEC	= 1;
		const int 	NUM_SOCKET_TIMEOUT_MSEC	= 0;
		
	private :
		struct WS_DATA {
			char	cmd_id;			// 1
			char	route_case;		// 2
			char	is_binary;		// 3
			char	is_extend;		// 4
			int		length;			// 8	
			char	*data;			
		} typedef WS_DATA_t;
		
		struct WS_EXT_DATA {
			int		tag;		// 0x00: uri, 0x01: regist, 0x10: data
			int		length;
			char	*value;
		} typedef WS_EXT_DATA_t;
		
		string  route_case[4] = {"EACH_OTHER", "WEB_ONLY", "NATIVE_ONLY", "ALL"};
		
		bool	is_p_term;
		bool	is_p_run;
		bool	is_route_wscif;
		
		bool	is_debug_print;

		int		socket_fd;
		int		port;
		char	ip_addr[16];
		int		ws_route_to;
		string	uri;
		
		thread		thread_func;
		mutex 		mutex_execute;
		
		bool init_connect(void);
		bool init_socket_option(void);
		void execute(void);
		
		void print_debug_info(const char *_format, ...);

	public :
		enum WS_ROUTE_TO {
			EACH_OTHER	= 0,	// default
			WEB_ONLY,			// web browser
			NATIVE_ONLY,		// native process(binary, interface,, )
			ALL
		};
		
		WebsocketHandler(void);
		~WebsocketHandler(void);
		
		typedef void (*type_event_handler)(const char _cmd_id, const char _is_binary, const int _length, const void *_data, WebsocketHandler *_this);
		typedef void (*type_adv_event_handler)(const char _cmd_id, const char _is_binary, const int _length, const void *_data, WebsocketHandler *_this, void *_ptr_void);

#if 0
		void (*func_event_handler)(const char, const char, const int, const void *, WebsocketHandler *);
#else
		type_event_handler		func_event_handler;
		type_adv_event_handler	func_adv_event_handler;
		void	*ptr_void;
#endif


		bool   init(string _ip_addr, string _uri, const bool _is_debug_print = false);
		bool   init(string _ip_addr, string _uri, int _port, const bool _is_debug_print = false);
		bool   reconnect(void);
		void   term(void);

		bool   is_run(void);
		bool   is_term(void);
		
		bool   send(const int _cmd_id, const string _data);
		bool   send(const int _cmd_id, const void *_data, const int _length);
		
		void   set_event_handler(type_event_handler _func_ptr);
		void   set_event_handler(type_adv_event_handler _func_ptr, void *_ptr_void = NULL);
		void   set_target_info(string _ip_addr, string _uri); 
		void   set_route_to(const int _type);
		void   set_route_wscif(void);
		void   set_debug_print(void);
		
		string get_uri_name(void);
		string get_ip_addr(void);
		int    get_port(void);
		string get_route_name(int _type);
}; // end of class : WebsocketHandler

#endif
