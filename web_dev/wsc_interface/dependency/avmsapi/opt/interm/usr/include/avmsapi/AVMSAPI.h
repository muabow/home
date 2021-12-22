/**
	@file	
	@brief	AVMS I/O Library Infomation
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.05.10
 */
 
#ifndef __AVMS_IO_LIBRARY_H__
#define __AVMS_IO_LIBRARY_H__

#include <stdint.h>
#include <string>



/**
	@class	AVMSAPI
	@brief	AVMS I/O Library Infomation
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.05.10
 */
class AVMSAPI {
public:
	/**
		@brief	Version
		@return	버전
	 */
	static const std::string version();

	/**
		@brief	Build Date
		@return	빌드 날짜
	 */
	static const std::string buildDate();

	/**
		@brief	Build Time
		@return	빌드 시간
	 */
	static const std::string buildTime();
};

#endif	// __AVMS_IO_LIBRARY_H__
