/**
	@file	
	@brief	Blink LED
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2020.10.07
 */

#ifndef __BLINK_LED_H__
#define __BLINK_LED_H__

#include <atomic>

#include <avmsapi/DeviceFactory.h>



class DevMem;



class BlinkLED
{
public:
	/**
		@brief	생성자 : 사용 안함
		@param	[in] isDebugPrint		디버그 메세지 출력 여부
	 */
	BlinkLED(const bool isDebugPrint = false);

	/**
		@brief	생성자 : DeviceFactory 사용
		@param	[in] deviceFactoryKey	Key
		@param	[in] period				초기 깜빡임 주기
		@param	[in] isDebugPrint		디버그 메세지 출력 여부
	 */
	BlinkLED(const std::string deviceFactoryKey, const uint32_t period, const bool isDebugPrint = false);


	/**
		@brief	생성자 : I/O Address 사용
		@param	[in] ioAddress			I/O Address
		@param	[in] bit				자리수
		@param	[in] period				초기 깜빡임 주기
		@param	[in] isDebugPrint		디버그 메세지 출력 여부
	 */
	BlinkLED(const uint32_t ioAddress, const int32_t bit, const uint32_t period, const bool isDebugPrint = false);

	/**
		@brief	소멸자
	 */
	virtual ~BlinkLED();
	


	/**
		@brief	깜빡임 주기 설정
		@param	[in] period	깜빡임 주기 (On/Off 동일시간 적용)
	 */
	void setPeriod(const uint32_t period);

	/**
		@brief	깜빡임 패턴 설정
		@remark
	 		Pattern : {On time, Off time, On time, Off time, ...};
	 			0 이상 : 지속 시간 (ms)
	 			-1 : 반복
		@param	[in] pattern	깜빡임 패턴
	 */
	void setPattern(const int32_t pattern[]);



private:
	/**
		@brief	디버그 메세지 출력
		@param	[in] format	출력 포멧
		@param	[in] ...	출력 포멧에 적용할 인자
	 */
	void printDebugInfo(const char* format, ...);



	/**
		@brief	깜빡임 처리를 위한 쏙ㄷㅁㅇ
	 */
	int32_t run();


public:
	static const int32_t MAX_LED_PATTERN_LENGTH = 16;	///< LED Pattern 최대 길이

private:
	static const GPIO::Value LED_ON = GPIO::HIGH;		///< LED On
	static const GPIO::Value LED_OFF = GPIO::LOW;		///< LED Off

	bool	_isDebugPrint;								///< 디버그 메세지 출력 상태



	GPIO*		_gpio;							///< LED GPIO

	DevMem*		_devMem;						///< I/O Address 접근용
	int32_t		_bit;							///< LED 자리

	int32_t _pattern[MAX_LED_PATTERN_LENGTH];	///< 깜빡임  Pattern



	std::thread*		_thread;				///< 깜빡임 제어 Thread
	std::atomic<bool>	_isStoppedThread;		///< 깜빡임 제어 Thread 종료를 위한 Flag

};


#endif //__BLINK_LED_H__
