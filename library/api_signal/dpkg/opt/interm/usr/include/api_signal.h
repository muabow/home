#ifndef __API_SIGNAL_H__
#define __API_SIGNAL_H__

#include <iostream>

#include <stdarg.h>
#include <signal.h>
#include <string.h>
#include <errno.h>

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
		void print_debug_info(const char *_format, ...);

		int		num_sig_handle;	
		bool	is_debug_print;
		
	public :
		SignalHandler(void);
		~SignalHandler(void);
		
		void set_debug_print(void);
		
		void set_signal(int _sig_num);
		void set_ignore(int _sig_num);
		void set_signal_handler(void (*_func)(int));

		bool is_term(void);
};

#endif
