/**
	@file	
	@brief	SPI 통신
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.04.11
 */

#ifndef __SPI_H__
#define __SPI_H__


#include <stdint.h>

#include "AVMSAPI.h"


/**
	@class	SPI
	@brief	SPI 통신
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.04.11
 */
class SPI {
public:
	typedef struct Info {
		std::string deviceNode;
		Info()
			: deviceNode("")
		{}
	} Info;

public:
	/**
		@brief	SPI 생성자
		@param	[in] device	장치명
		@param	[in] mode	모드
		@param	[in] bits	비트
		@param	[in] speed	전송 속도
		@param	[in] delay	지연시간
		@throw	에러 메세지
	 */	
	SPI(const std::string device = "/dev/spidev0.0", const uint8_t mode = 0, const uint8_t bits = 8, const uint32_t speed = 500000, const uint16_t delay = 0) throw (const char*);

	/**
		@brief	SPI 소멸자
		@throw	에러 메세지
	 */
	virtual ~SPI() throw (const char*);

	/**
		@brief	Full Duplex 방식으로 데이터를 송/수신
		@param	[in,out] tx 송신 버퍼
		@param	[in,out] rx 수신 버퍼
		@param	[in] len	데이터 길이
		@return	true : 성공
		@return	false : 실패
	 */
	bool transfer(uint8_t* tx, uint8_t* rx, const uint32_t len);
	

private:
	int32_t _fd;			///< SPI 장치의 FD
	const uint8_t _bits;	///< Bits
	const uint32_t _speed;	///< 전송 속도
	const uint16_t _delay;	///< 지연시간
};

#endif	// __SPI_H__
