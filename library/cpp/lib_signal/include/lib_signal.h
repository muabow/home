#ifndef __LIB_SIGNAL_H__
#define __LIB_SIGNAL_H__

#include <iostream>

#include <stdarg.h>
#include <signal.h>
#include <string.h>
#include <errno.h>


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

