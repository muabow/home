/**
	@file	
	@brief	Thread
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.04.03
	@see	http://imarch.pe.kr/?p=1548
*/

#ifndef __THREAD_H__
#define __THREAD_H__

#include <stdint.h>
#include <pthread.h>

class Thread
{
public:
	Thread() {};
	virtual ~Thread() {};

	void start()
	{
		/* 여기서부터 스레드가 생성됩니다. */
		/* Thread::run_은 앞서 Class내에서 static으로
		선언했으므로 가능합니다. 그리고 this는 엄연한 값이므로 가능. */
		::pthread_create(&_threadID, NULL, Thread::run_, (void*)this);
	}
	int32_t join()
	{
		::pthread_join(_threadID, NULL);
		return _retValue;
	}

	/**
		@brief	ms (millisecond) 단위의 Sleep
		@param	[in] millisecond
	 */
	inline static void sleep(const int32_t millisecond)
	{
		struct timespec t;
		t.tv_sec = millisecond / 1000;
		t.tv_nsec = (millisecond % 1000) * 1000 * 1000;
		::nanosleep(&t, 0);
	}

protected:
	virtual int32_t run() = 0;
#if 0
	{
		return 0;
	}
#endif

	static void* run_(void* pthis_)	/// 중간에서 run을 다시 호출해줄 녀석
	{
		Thread* pthis = (Thread*)pthis_;
		pthis->setRetValue(pthis->run());
		::pthread_exit(NULL);
	}

	void setRetValue(int32_t r)
	{
		_retValue = r;
	}

private:
	::pthread_t _threadID;
	int _retValue;


public:
	static const uint32_t LOOPING_SLEEP_TIME = 10;		///< Thread 반복 실행간의 간격  (짧을시 CPU 사용율 상승됨)
	
};

#if 0
///< Usage
class ThreadTest : public Thread
{
private:
	int32_t num;
public:
	ThreadTest() { num = 0; }
	ThreadTest(int32_t num) { this->num = num; }
	int32_t run()
	{
		printf("%d", num);
		return num;
	}
};

int main ()
{
    ThreadTest test1 (3);
    ThreadTest test2 (4);
    test1.start ();
    test2.start ();
    test2.join ();
    test1.join ();
}

#endif

#endif	// __THREAD_H__
