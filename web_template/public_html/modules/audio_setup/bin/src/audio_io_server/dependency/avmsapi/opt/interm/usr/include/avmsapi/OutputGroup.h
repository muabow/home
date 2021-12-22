/**
	@file	
	@brief	GPIO 를 일괄 제어하기 위한 OutputGroup
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.03.31
 */

#ifndef __OUTPUT_GROUP_H__
#define __OUTPUT_GROUP_H__


#include <stdint.h>
#include <vector>

#include "AVMSAPI.h"


class GPIO;


/**
	@class	OutputGroup
	@brief	GPIO 를 일괄 제어하기 위한 OutputGroup
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.03.31
 */
class OutputGroup {
public:

	/**
		@brief	Group 에 GPIO 추가
		@see	GPIO::write()
		@warning	직접 호출 금지
		@param	[in] gpio	Group 에 추가할 GPIO
		@return	true : 성공
		@return	false : 실패
	 */	
	bool add(GPIO* gpio);

	/**
		@brief	추가된 GPIO 갯수를 반환
		@return	추가된 GPIO 갯수
	 */
	int32_t size();

	/**
		@brief	추가된 GPIO 들을 일괄 쓰기
	 */
	void flush();

	/**
		@brief	Group 초기화
	 */
	void clean();

private:
	std::vector<GPIO*> _gpioList;
};

#endif	// __OUTPUT_GROUP_H__
