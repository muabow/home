#include <stdio.h>
#include <errno.h>
#include <signal.h>
#include <string.h>

#include <iostream>

namespace COMMON {
	using namespace std;
	
	int  g_num_sig_handle = 0;
	bool g_is_sig_term    = false;
	void (*TermUserFunc)(void) = NULL;
	
	class SignalHandler {
		private :
			static void termFunc(int _sigNum);
			
			int num_sig_handle;	
		
		public :
			SignalHandler(void);
			~SignalHandler(void);
			
			void setSignal(int _sigNum);
			void setSigFunc(void (*_func)(void));

			bool isTerm(void);
	};
	
	SignalHandler::SignalHandler(void) {
		this->num_sig_handle = g_num_sig_handle;
		g_num_sig_handle++;
		
		if( this->num_sig_handle > 0 ) {
			fprintf(stderr, "SignalHandler::SignalHandler() instance can not be created.\n");
			return ;
		}
		
		fprintf(stderr, "SignalHandler::SignalHandler() create instance\n");
		
		// init global var/function 
		g_is_sig_term = false;
		TermUserFunc  = NULL;
					
		return ;
	}
	
	SignalHandler::~SignalHandler(void) {
		fprintf(stderr, "SignalHandler::SignalHandler() instance destructed.\n");
	}

	void SignalHandler::termFunc(int _sigNum) {
		if( g_is_sig_term ) {
			fprintf(stderr, "SignalHandler::termFunc() already terminated\n");
					
			return ;
		}
					
		g_is_sig_term = true;

		if( TermUserFunc != NULL ) {
			TermUserFunc();
		}
									
		return ;
	}

	void SignalHandler::setSignal(int _sigNum) {
		if( this->num_sig_handle > 0 ) {
			fprintf(stderr, "SignalHandler::SignalHandler() instance can not be created.\n");
			
			return ;
		}
		
		fprintf(stderr, "SignalHandler::setSignal() bind singal event [%s]\n", strsignal(_sigNum));
				
		signal(_sigNum, termFunc);
					
		return ;
	}
	
	void SignalHandler::setSigFunc(void (*_func)(void)) {
		if( this->num_sig_handle > 0 ) {
			fprintf(stderr, "SignalHandler::SignalHandler() instance can not be created.\n");
			
			return ;
		}
		
		fprintf(stderr, "SignalHandler::setSigFunc() bind user term function\n");
					
		TermUserFunc = _func;
					
		return ;
	}
	
	bool SignalHandler::isTerm(void) {
		return g_is_sig_term;
		
	}
}