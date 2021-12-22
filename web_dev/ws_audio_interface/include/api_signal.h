#ifndef __API_SIGNAL_H__
#define __API_SIGNAL_H__

#include <stdarg.h>

/* # SIGNAL Handler API
  1. 개발 완료 목록 (2019.03.27, v0.0.0.1)
   + SignalHandler
	: set_signal(), set_signal_handler()
	
  2. 개발 예정 목록
    : x
 */


using namespace std;

class SignalHandler {
	private :
		static void signal_handler(int _sig_num);
		int		num_sig_handle;	
		bool	is_debug_print = false;
	
	public :
		SignalHandler(void);
		~SignalHandler(void);
		
		void set_signal(int _sig_num);
		void set_signal_handler(void (*_func)(void));
		void set_debug_print(void);

		bool is_term(void);
		
		void print_debug_info(const char *_format, ...);
};

#endif