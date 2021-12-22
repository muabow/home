#include <stdio.h>
#include <unistd.h>
#include <string.h>

#include <iostream>
#include <string>
#include <thread>

#include "api_queue.h"

using namespace std;

// * QueueHandler 는 tuple을 이용한 linear queue 구조를 생성함
// * queue 는 tuple<char *_data, int _length> pair로 이루어짐
// * data는 char array 를 사용하는데 어떠한 data type 도 상관없이 1byte의 연속된 집합으로 생각하면 됨.
// * char array 의 pointer 를 return 

// queue handler instance 생성
// 전역/로컬 모든 영역에 생성 가능하며 본 sample 에선 thread를 이용한 enqueue/dequeue test를 위해
// 전역으로 생성 함.
// instance 생성 시 debug print set 가능
// args([bool _is_debug_print])
QueueHandler g_queue_handler;


void counter(int _id) {
	// tuple type 변수 선언
	tuple<char *, int> deq;

	// 5초 후 실행
	printf("count thread sleep 5 second\n");
	sleep(5);
	
	char data[1024];
	int  length;
	
	// linear queue의 queue count 획득
	while( g_queue_handler.get_queue_cnt() > 0 ) {
		// dequeue data & data length
		deq = g_queue_handler.dequeue();
		
		length = get<1>(deq);
		snprintf(data, length, "%s", get<0>(deq));
		
		printf("queue_cnt:%02d - dequeue:%s\n", g_queue_handler.get_queue_cnt(), data);
		
		sleep(1);
	}
	
	printf("counter[%d] termed\n", _id);
	
	return ;
}

int main(void) {
	// debug print 출력
	g_queue_handler.set_debug_print();
	
	// queue 생성
	// 기본 단위 1024 bytes * 1024 * scale[default: 5] size, 즉 5MBytes queue 생성
	// init 시 인자를 통해서 queue 사이즈 조절 가능
	// args([int _scale])
	// return - true: success, false: failed(already created) 
	g_queue_handler.init();
	
	// 최소한으로 linear queue에 남겨둘 queue count 설정
	// default: 0
	g_queue_handler.set_min_dequeue_cnt(0);
	
	// linear queue에서 최초 접근 index 지정, enqueue 당 index count 1증가
	// default: 0
	g_queue_handler.set_offset_index(0);
	
	thread thread_func_1(counter, 1);
	
	char data[1024];
	for( int idx = 0 ; idx < 50 ; idx++ ) {
		sprintf(data, "queue_test_data_index[%d]", idx);
		
		// enqueue data & data length
		g_queue_handler.enqueue(data, strlen(data) + 1);
		usleep(500000);
	}
	
	thread_func_1.join();

	// * 그 외 method 목록
	// 최소한으로 linear queue에 남겨둘 queue count 호출
	// g_queue_handler.get_min_dequeue_cnt();
	
	// linear queue에서 최초 접근하는 index 획득
	// g_queue_handler.get_offset_index();

	// linear queue에 쌓인 queue count를 0으로 초기화, 즉 버퍼 초기화 수행
	// g_queue_handler.reset_queue_cnt();
	
	// dequeue를 하지 않고 linear queue의 queue count 를 감소
	// g_queue_handler.decrease_buffer_cnt();
	
	
	// 생성된 queue 해제 queue 해제, 해제 시 init을 통해 queue 재할당 가능
	g_queue_handler.free();
	
	printf("main() termed\n");
	
	return 0;
}

