#ifndef __CLASS_SIGNAL_HANDLER_H__
#define __CLASS_SIGNAL_HANDLER_H__

namespace COMMON {
	class SignalHandler {
		private :
			static void termFunc(int _sigNum);
			
			int		num_sig_handle;	
			
			bool	is_debug_print = false;
		
		public :
			SignalHandler(void);
			
			void setSignal(int _sigNum);
			void setSigFunc(void (*_func)(void));
	
			bool isTerm(void);
			
			void print_debug_info(const char *_format, ...);
			void set_debug_print(void);
	};
}

#endif