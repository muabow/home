/**
	@file	
	@brief	FormatableBuffer
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2020.02.17
 */


#ifndef __FORMATABLE_BUFFER_H__
#define __FORMATABLE_BUFFER_H__



#include <cstdio>
#include <cstdlib>
#include <string>
#include <cstdarg>
#include <cassert>


/**
	@class	FormatableBuffer
	@brief	서식 지정자(Format Specifier)를 사용한 문자열 생성
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2020.02.17
 */
template<int32_t size = 256>
class FormatableBuffer {
public:
	FormatableBuffer() { init(); }
	FormatableBuffer(const char* fmt, ...);
	virtual ~FormatableBuffer()	{}

	void init()				{ _buffer[0] = '\0'; _position = 0; }
	int32_t length() const	{ return (int32_t)strnlen(_buffer, size); }	

	void format(const char* fmt, ...);	
	const char* c_str() const { return _buffer; }



private:
	bool vaformat(const char* fmt, va_list vargs);



private:
	int32_t _position;
	char _buffer[size];
};



template<int32_t size>
inline FormatableBuffer<size>::FormatableBuffer(const char* fmt, ...)
	: _position(0)
{
	va_list vargs;
	va_start(vargs, fmt);
	vaformat(fmt, vargs);
	va_end(vargs);
}



template<int32_t size>
inline bool FormatableBuffer<size>::vaformat(const char* fmt, va_list vargs)
{
	int32_t remaining = size - _position;
	if (remaining <= 1) {   // +1 for null character
		return false;
	}

	int32_t ret = vsnprintf(_buffer + _position, remaining, fmt, vargs);
	bool result = remaining > ret;

	_position += result ? ret : remaining;
	assert(_position <= size);

	return result;
}



template<int32_t size>
inline void FormatableBuffer<size>::format(const char* fmt, ...)
{
	va_list vargs;
	va_start(vargs, fmt);
	_position = 0;
	vaformat(fmt, vargs);
	va_end(vargs);
}



#endif	// __FORMATABLE_BUFFER_H__
