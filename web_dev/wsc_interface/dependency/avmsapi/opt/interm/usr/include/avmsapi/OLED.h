/**
	@file	
	@brief	OLED Device
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.12.14
 */

#ifndef __OLED_H__
#define __OLED_H__

#include <stdint.h>

#include "AVMSAPI.h"


/**
	@class	OLED Device
	@brief	OLED 제어
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.12.14
 */
class OLED {
public:
	/**
		@brief	색상 표현 방식(버퍼 포멧)
	 */
	enum ColorFormat {
		BW_1,
		GRAY_8,
		RGB_565,
		RGB_888,
		ARGB_1555,
		ARGB_8888,
	};

	/**
		@brief	Rotation
	 */
	enum Rotation {
		CW_000,
		CW_090,
		CW_180,
		CW_270,
		MIRROR,
	};


public:
	/**
		@brief	OLED 생성자
		@param	[in] width	가로 크기
		@param	[in] height	세로 크기
		@param	[in] colorFormat	컬러 포멧
		@throw	에러 메세지
	 */
	OLED(const int32_t width, const int32_t height, const OLED::ColorFormat colorFormat) throw (const char*);



	/**
		@brief	OLED 소멸자
		@throw	에러 메세지
	 */
	virtual ~OLED() throw (const char*);
	/**
		@brief	가로 폭 반환
		@return	폭
	 */
	int32_t getWidth() { return _width; };
	/**
		@brief	세로 높이 반환
		@return	높이
	 */
	int32_t getHeight() { return _height; };
	/**
		@brief	픽셀당 비트 수 반환 Bit Per Pixel
		@return	Bit Per Pixel
	 */
	int32_t getBpp() { return OLED::BPP[_colorFormat]; };
	/**
		@brief	OLED On/Off 상태
		@return	true : On, false : Off
	 */
	bool isOn() { return _isOn; };

	/**
		@brief	OLED On
		@param	[in] isForce	강재로 실행
	 */
	virtual void on(const bool isForce = false) = 0;
	/**
		@brief	OLED Off
		@param	[in] isForce	강재로 실행
	 */
	virtual void off(const bool isForce = false) = 0;

	/**
		@brief	Screen Buffer 초기화
	 */
	virtual void clear() = 0;
	/**
		@brief	Screen Buffer 에 Pixel 출력
		@param	[in] x	x좌표
		@param	[in] y	y좌표
		@param	[in] color	색상
	 */
	virtual void drawPixel(const int32_t x, const int32_t y, const uint32_t color) = 0;
	/**
		@brief	Screen Buffer 에 Bitmap 출력
		@param	[in] x	x 좌표
		@param	[in] y	y 좌표
		@param	[in] bitmap	출력할 이미지 (ColorFormat이 동일해야 함)
		@param	[in] width	출력할 이미지의 폭
		@param	[in] height	출력할 이미지의 높이
	 */
	virtual void drawBitmap(const int32_t x, const int32_t y, void* bitmap, const int32_t width, const int32_t height) = 0;
	/**
		@brief	Screen Buffer 를 화면에 출력
	*/
	virtual void update() = 0;


protected:
	static const uint32_t BPP[];	///< ColorFormat 에 따른 BPP

	bool _isOn;						///< 화면 On/Off 상태

	const int32_t _width;			///< 폭
	const int32_t _height;			///< 높이
	const ColorFormat _colorFormat;	///< Screen Buffer 에 Pixel 을 구성하는 Color Data Format
};




#endif	// __OLED_H__

