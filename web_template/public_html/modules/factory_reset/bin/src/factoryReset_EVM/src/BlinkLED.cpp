/**
	@file	
	@brief	Blink LED
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2020.10.07
 */


//#include <stdint.h>
//#include <cstdio>
#include <cstring>
//#include <unistd.h>
#include <atomic>

#include <avmsapi/DeviceFactory.h>
#include <avmsapi/utility/BitMask32.h>
#include <avmsapi/utility/ClockTick.h>
#include <avmsapi/utility/Thread.h>

#include <NotificationListener.h>

#include "utility/ANSIColor.h"
#include "utility/FormatableBuffer.h"

#include "DevMem.h"

#include "BlinkLED.h"



BlinkLED::BlinkLED(const bool isDebugPrint /*= false*/)
	: _isDebugPrint(isDebugPrint)

	, _gpio(NULL)

	, _devMem(NULL)
	, _bit(0)
	
	, _thread(NULL)
	, _isStoppedThread(false)
{
	//// 초기화
	memset(_pattern, 0, sizeof(_pattern));
}



BlinkLED::BlinkLED(const std::string deviceFactoryKey, const uint32_t period, const bool isDebugPrint /*= false*/)
	: _isDebugPrint(isDebugPrint)

	, _gpio(NULL)

	, _devMem(NULL)
	, _bit(0)
	
	, _thread(NULL)
	, _isStoppedThread(false)
{
	//// 초기화
	memset(_pattern, 0, sizeof(_pattern));

	setPeriod(period);



	try {
		_gpio = DeviceFactory::createGPIO(deviceFactoryKey);
	}
	catch (const char* msg) {
		printDebugInfo("%d%s	Can not control LIVE_LED.	%s\n\n", __LINE__, COLOR_INFO, COLOR_RESET);
		return;
	}



	// Thread 실행
	_thread = new std::thread(&BlinkLED::run, this);
}




BlinkLED::BlinkLED(const uint32_t ioAddress, const int32_t bit, const uint32_t period, const bool isDebugPrint /*= false*/)
	: _isDebugPrint(isDebugPrint)

	, _gpio(NULL)

	, _devMem(NULL)
	, _bit(bit)
	
	, _thread(NULL)
	, _isStoppedThread(false)
{
	//// 초기화
	memset(_pattern, 0, sizeof(_pattern));

	setPeriod(period);



	try {
		_devMem = new DevMem(ioAddress);
	}
	catch (const char* msg) {
		printDebugInfo("%d%s	Can not control LIVE_LED.	%s\n\n", __LINE__, COLOR_INFO, COLOR_RESET);
		return;
	}



	// Thread 실행
	_thread = new std::thread(&BlinkLED::run, this);
}



BlinkLED::~BlinkLED()
{
	// Thread 종료
	if (_thread) {
		_isStoppedThread.store(true);
		if (_thread->joinable()) {
			_thread->join();
		}
		delete _thread;
		_thread = NULL;
	}

	if (_gpio) {
		delete _gpio;
		_gpio = NULL;
	}

	if (_devMem) {
		delete _devMem;
		_devMem = NULL;
	}	
}



void BlinkLED::setPeriod(const uint32_t period)
{
	const int32_t pattern[MAX_LED_PATTERN_LENGTH] = {(const int32_t)period, (const int32_t)period, -1};
	memcpy(_pattern, pattern, sizeof(_pattern));
}



void BlinkLED::setPattern(const int32_t pattern[])
{
	memcpy(_pattern, pattern, sizeof(_pattern));
}



void BlinkLED::printDebugInfo(const char* format, ...)
{
	if (_isDebugPrint == false) return ;

	std::string name = typeid(this).name();

	// Class Name 앞에 붙는 Pxx 제거
	name.erase(0, 1);
	while ( !(name.at(0) >= 'A' && name.at(0) <= 'Z') && !(name.at(0) >= 'a' && name.at(0) <= 'z') ) {
		name.erase(0, 1);
	}

	fprintf(stdout, "%u	%s::", ClockTick::current(), name.c_str());
	va_list arg;
	va_start(arg, format);
	vprintf(format, arg);
	va_end(arg);

	return ;
}



int32_t BlinkLED::run()
{
	while (!_isStoppedThread.load()) {

		// 페턴 출력 중 내용이 변경 될 수 있어 복제 후 사용
		int32_t pattern[MAX_LED_PATTERN_LENGTH] = {0, };
		memcpy(pattern, _pattern, sizeof(pattern));

		// 패턴에 맞춰 LED 출력
		for (int32_t i = 0; i < MAX_LED_PATTERN_LENGTH; i++) {
			const int32_t durationTime = pattern[i];

			printDebugInfo("%d%s	 [%d] %d ms	%s\n", __LINE__, COLOR_DEBUG, i, durationTime, COLOR_RESET);

			if (durationTime >= 0) {
				if ((i % 2) == 0) {
					// On
					if (_gpio != NULL) {
						_gpio->write(LED_ON);
					}
					else if (_devMem != NULL) {
						BitMask32 ledBitMask = BitMask32(_devMem->get());
						ledBitMask.set(_bit);
						_devMem->set(ledBitMask.asUint32());
					}
				}
				else {
					// Off
					if (_gpio != NULL) {
						_gpio->write(LED_OFF);
					}
					else if (_devMem != NULL) {
						BitMask32 ledBitMask = BitMask32(_devMem->get());
						ledBitMask.reset(_bit);
						_devMem->set(ledBitMask.asUint32());				
					}
				}
				Thread::sleep(durationTime);
			}
			else {
				break;
			}
		}

		printDebugInfo("%d	\n", __LINE__);
	}

	return 0;
}


