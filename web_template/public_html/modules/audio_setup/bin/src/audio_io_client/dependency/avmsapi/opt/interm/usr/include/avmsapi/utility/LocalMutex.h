#ifndef __LOCAL_MUTEX_H__
#define __LOCAL_MUTEX_H__


#include "mutex"

#include "Mutex.h"



class LocalMutex {
public:
	LocalMutex(Mutex& mutex)
		: _mutex(&mutex)
		, _stdMutex(NULL)
	{ 
		_mutex->lock(); 
	}

	LocalMutex(std::mutex& stdMutex)
		: _mutex(NULL)
		, _stdMutex(&stdMutex)
	{
		_stdMutex->lock(); 
	}

	virtual ~LocalMutex()
	{
		if (_mutex) {
			_mutex->unlock();
			_mutex = NULL;
		}
		if (_stdMutex) {
			_stdMutex->unlock();
			_stdMutex = NULL;
		}
	}

private:
		Mutex* _mutex;
		std::mutex* _stdMutex;
};

#endif	// __LOCAL_MUTEX_H__
