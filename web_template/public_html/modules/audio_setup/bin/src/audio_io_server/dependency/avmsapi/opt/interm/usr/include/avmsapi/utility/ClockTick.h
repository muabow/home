#ifndef __CLOCK_TICK_H__
#define __CLOCK_TICK_H__

#include <stdint.h>
#include <cstdio>
#include <time.h>
#include <sys/time.h>



class ClockTick {
public:
	enum { TicksPerSecond = 1000 };

	static uint32_t current();
	static uint32_t difference(uint32_t prev, uint32_t next);
	static uint32_t differenceAsSeconds(uint32_t prev, uint32_t next);
	static uint32_t differenceFromCurrent(uint32_t prev);
};

inline uint32_t ClockTick::current()
{
	struct timespec tp;

	if (clock_gettime(CLOCK_MONOTONIC, &tp) == 0)
	{
		return uint32_t(tp.tv_sec) * 1000 + uint32_t(tp.tv_nsec) / 1000000;
	}
	else
	{
		struct timeval tv;
		gettimeofday(&tv,0);		

		return uint32_t(tv.tv_sec) * 1000 + uint32_t(tv.tv_usec) / 1000;

	}
}

inline uint32_t ClockTick::difference(uint32_t prev, uint32_t next)
{
	return (prev <= next) ? next - prev : (0xffffffffUL - prev) + next + 1;
}

inline uint32_t ClockTick::differenceAsSeconds(uint32_t prev, uint32_t next)
{
	return difference(prev, next) / TicksPerSecond;
}

inline uint32_t ClockTick::differenceFromCurrent(uint32_t prev)
{
	return difference(prev, current());
}

#endif	// __CLOCK_TICK_H__
