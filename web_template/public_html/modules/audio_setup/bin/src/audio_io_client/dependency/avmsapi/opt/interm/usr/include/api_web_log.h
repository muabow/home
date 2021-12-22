#ifndef __API_WEB_LOG_H__
#define __API_WEB_LOG_H__

#include <stdarg.h>


class WebLogHandler {
	static const int   NUM_LOG_INTERFACE_PORT  =   25004;

	struct LOG_DATA {
		char	module [32];
		char	type   [6];
		char	message[4096];
	} typedef LOG_DATA_t;

	private:
		bool	is_set;
		bool    is_debug_level;
		bool	is_debug_print;
		
		int     port;
		char    module[32];

		void print_debug_info(const char *_format, ...);
		
		bool send(const char *_log_level, const char *_message);
		
	public:
		WebLogHandler(const char *_module);
		~WebLogHandler(void);

		void set_debug_print(void);
		void set_info_level(void);

		bool clear(void);
		bool remove(void);
		bool fatal(const char *_msg);
		bool error(const char *_msg);
		bool debug(const char *_msg);
		bool warn(const char *_msg);
		bool info(const char *_msg);
}; // end of class : WebLogHandler

#endif
