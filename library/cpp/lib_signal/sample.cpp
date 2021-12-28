#include <stdio.h>
#include <unistd.h>
#include <string.h>

#include <iostream>
#include <string>
#include <thread>

#include "lib_signal.h"

using namespace std;

bool g_sig_term = false;

// signal event 처리 함수 
void signal_event_handler(int _sig_num) {
	printf("signal_event_handler() event : [%d] %s\n", _sig_num, strsignal(_sig_num));
	g_sig_term = true;

	return ;
}


void counter(int _id) {
	int idx = 0;
	while( !g_sig_term ) {
		printf("counter[%d] : %d\n", _id, idx++);

		sleep(1);
	}
	printf("counter[%d] termed\n", _id);

	return ;
}

int main(void) {
	// signal handler instance 생성
	// * signal handler는 하나의 process 에서 한개의 instance 만 생성 가능, 즉 signal handler 복수 생성 불가.
	// * signal event의 중복 처리는 원칙적으로 불가하기 때문.
	SignalHandler signal_handler;

	// debug print 출력
	signal_handler.set_debug_print();

	// SIGNAL case 설정, 각각의 SIGNAL은 errno.h 를 참조
	// args(<SIGNAL>)
	signal_handler.set_signal(SIGINT);
	signal_handler.set_signal(SIGKILL);
	signal_handler.set_signal(SIGTERM);
	signal_handler.set_signal(SIGPIPE);

	// signal event의 추가 처리를 위한 event handler 등록
	// args(<function ptr>)
	// function(int _sig_num)
	signal_handler.set_signal_handler(&signal_event_handler);

	thread thread_func_1(counter, 1);
	thread thread_func_2(counter, 2);

	thread_func_1.join();
	thread_func_2.join();

	// signal handler가 signal event를 통해 종료가 되었는지 확인
	// return - true: term, false: running
	while( !signal_handler.is_term() ) {
		sleep(1);
	}

	printf("main() termed\n");

	return 0;
}


