/**
	@file	
	@brief	GFX (Graphic Effect)
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.07.18
 */

#ifndef __GFX_H__
#define __GFX_H__


#include <stdint.h>
#include <deque>

#include "Adafruit-GFX/Adafruit_GFX.h"
#include "utility/Mutex.h"


class Canvas1 : public Adafruit_GFX {
public:
	Canvas1(uint16_t w, uint16_t h);
	virtual ~Canvas1(void);
	void drawPixel(int16_t x, int16_t y, uint16_t color),
	fillScreen(uint16_t color);
	uint8_t* getBuffer(void);
private:
	uint8_t* buffer;
};

class Canvas8 : public Adafruit_GFX {
public:
	Canvas8(uint16_t w, uint16_t h);
	virtual ~Canvas8(void);
	void drawPixel(int16_t x, int16_t y, uint16_t color),
	fillScreen(uint16_t color),
	writeFastHLine(int16_t x, int16_t y, int16_t w, uint16_t color);
	uint8_t* getBuffer(void);
private:
	uint8_t* buffer;
};

class Canvas16 : public Adafruit_GFX {
public:
	Canvas16(uint16_t w, uint16_t h);
	virtual ~Canvas16(void);
	void drawPixel(int16_t x, int16_t y, uint16_t color),
	fillScreen(uint16_t color);
	uint16_t* getBuffer(void);
private:
	uint16_t* buffer;
};



/**
	@class	Graph1
	@brief	Graph 출력
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.07.17
 */
class Graph1
{
public:
	/**
		@brief	Graph1 생성자
		@param	[in] width	가로
		@param	[in] height	세로
		@param	[in] min	최소 값
		@param	[in] max	최대 값
	 */
	Graph1(const int16_t width, const int16_t height, const uint16_t min = 0, const uint16_t max = 100);

	/**
		@brief	Graph1 소멸자
	 */
	virtual ~Graph1();

	/**
		@brief	값 추가
		@param	[in] value	추가할 값
	 */
	void add(const int16_t value);

	/**
		@brief	버퍼 반환
		@return	Canvas 버퍼
	 */
	uint8_t* getBuffer();
	
	/**
		@brief	폭 반환
		@return	가로 폭
	 */
	int16_t width() const;

	/**
		@brief	높이 반환
		@return	세로 높이
	 */
	 int16_t height() const;

private:
	/**
		@brief	그래프 가이드라인(테두리) 그리기
		@param	[in] color	색상
	 */
	void drawGuideline(const uint16_t color);

private:
	int16_t _min;	///< 최소값
	int16_t _max;	///< 최대값
	
	std::deque<int16_t> _levels;	///< 값을 그래프 크기에 맞춰 변한한 모음
	Canvas1* _canvas;				///< 그래프가 그려질 Canvas

	Mutex _mutex;
};

#endif // __GFX_H__
