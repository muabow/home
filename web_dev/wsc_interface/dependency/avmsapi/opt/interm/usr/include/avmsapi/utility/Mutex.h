#ifndef __MUTEX_H__
#define __MUTEX_H__

#include <pthread.h>
#include <cerrno>


class Mutex {
public:
	Mutex();
	virtual ~Mutex();

private:
	pthread_mutex_t _handle;

public:
	virtual void lock();
	void unlock();
	bool tryLock();
};

inline Mutex::Mutex()
{
	::pthread_mutex_init(&_handle, 0);
}

inline Mutex::~Mutex()
{
	::pthread_mutex_destroy(&_handle); 
}

inline void Mutex::unlock()
{
	::pthread_mutex_unlock(&_handle); 
}

inline bool Mutex::tryLock()
{
	return ::pthread_mutex_trylock(&_handle) != EBUSY;
}

inline void Mutex::lock()
{
	::pthread_mutex_lock(&_handle);
}

#endif  // __MUTEX_H__
