/**
	@file	
	@brief	KeyButton 제어
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.04.07
 */
 
#ifndef __KEY_BUTTON_H__
#define __KEY_BUTTON_H__


#include <stdint.h>
#include <cstdio>

#include "AVMSAPI.h"
#include "GPIO.h"


class Mutex;


/**
	@brief	Event 발생시 호출되는 Handler Prototype 정의
	@param	[in] id	ID
	@param	[in] eventType EventType (Release / Press / DoublePress/ LongPress)
	@param	[in,out] param	EventHandler 등록시 전달한 인자
 */
typedef void (*KeyButtonEventHandler)(const int32_t id, const /* KeyButton::EventType */ int32_t eventType, void* param);



/**
	@class	KeyButton
	@brief	KeyButton 제어
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.04.07
 */
class KeyButton {
public:
	/**
		@brief	Event 종류
	 */
	enum EventType {
		NONE = 0,		///< None : RoraryEncoder 에서만 사용
		RELEASE,		///< Release
		PRESS,			///< Press
		DOUBLE_PRESS,	///< Double Press
		LONG_PRESS,		///< Long Press
	};

private:
	bool init();
	bool cleanup();

public:
	/**
		@brief	KeyButton 생성자 (Real GPIO)
		@param	[in] pinNumber	GPIO 핀 번호
		@param	[in] handler	Event 발생시 호출되는 Handler
		@param	[in] id	Event Handler 호출시 반환될 ID
		@param	[in,out] param	Event Handler 호출시 전달할 인자
		@param	[in] invert	KeyButton 의 Up/Down 시 Value 상태 반전
		@throw	에러 메세지
	 */	
	KeyButton(const int32_t pinNumber, const KeyButtonEventHandler handler, const int32_t id, void* param = NULL, const bool invert = false) throw (const char*);

	/**
		@brief	KeyButton 생성자 (Shift Register 165)
		@param	[in] channel	채널
		@param	[in] bit	비트
		@param	[in] handler	Event 발생시 호출되는 Handler
		@param	[in] id	Event Handler 호출시 반환될 ID
		@param	[in,out] param	Event Handler 호출시 전달할 인자
		@param	[in] invert	KeyButton 의 Up/Down 시 Value 상태 반전
		@throw	에러 메세지
	 */	
	KeyButton(const GPIO::Channel channel, const int32_t bit, const KeyButtonEventHandler handler, const int32_t id, void* param = NULL, const bool invert = false) throw (const char*);

	/**
		@brief	KeyButton 소멸자
		@throw	에러 메세지
	 */	
	virtual ~KeyButton() throw (const char*);



	/**
		@brief	LongPress 인식 시간 설정
		@param	[in] time	시간(ms)
	 */
	void setLongPressTime(uint32_t time = LONG_PRESS_TIME);
	/**
		@brief	DoublePress 인식 시간 설정
		@param	[in] time	시간(ms)
	 */
	void setDoublePressTime(uint32_t time = DOUBLE_PRESS_TIME);



	KeyButtonEventHandler getEventHandler() const {	return _eventHandler;	};
	
	/**
		@brief	Event Handler ID 반환
		@see	setEventHandler()
		@return	Event Handler ID
	 */	
	int32_t getEventHandlerID() const {	return _eventHandlerID;	};
	void* getParam() const {	return _param;	};


private:
	/**
		@brief	GPIO 에서 Event 발생시 호출 될 Handler
				\n 전달받은 값을 KeyButton Event (Pressed / Released / DoublePressed) 로 변환하여 EventQueue 에 추가
		@see	init()
		@param	[in] id UID
		@param	[in] value	GPIO 상태 값
		@param	[in,out] param	KeyButton 의 this
	 */
	static void onGPIOEventHandler(const int32_t id, const int32_t value, void* param);

	/**
	 	@brief	별도 Thread로 동작하여	LongPressed Event 발생 요건을 감시
	 			\n LongPressed Event 를 생성하여 EventQueue 에 추가
		@param	[in,out] param	KeyButton 의 this
	 */
	static void* runGenerateLongPressThread(void* param);

public:
	static const std::string EVENT_TYPE[];			///< EventType 에 해당하는 문자

private:
	/**
		@brief	버튼의 물리적 Up / Down 상태
	 */
	enum State {
		DOWN = 0,	///< Down
		UP,			///< Up
	};

	static const uint32_t LONG_PRESS_TIME = 500;		///< 초기값 : Press 유지 후 LongPress 발생까지 시간 (ms)
	static const uint32_t DOUBLE_PRESS_TIME = 300;		///< 초기값 : 연속된 Press 를 DoublePress 로 판별하기 위한 시간 (ms)

	uint32_t _longPressTime;						///< Press 유지 후 LongPress 발생까지 시간 (ms)
	uint32_t _doublePressTime;						///< 연속된 Press 를 DoublePress 로 판별하기 위한 시간 (ms)

	GPIO _gpio;										///< GPIO Device
	const KeyButtonEventHandler _eventHandler;		///< EventHandler
	const int32_t _eventHandlerID;					///< EventHandler 호출시 반환될 ID
	void* _param;									///< EventHandler 호출시 반환될 인자
	const bool _invert;								///< H/W 회로에 따라 버튼의 Down/Up 상태에 따른 값이 High/Low 로 반대일 수 있음


	uint32_t _lastPressedClockTick;				///< 마지막으로 Press 가 발생된 ClockTick
	EventType _lastEventType;					///< 마지막으로 발생된 EventType
	Mutex* _lastEventTypeMutex;					///< 사용자 입력과, LongPress 생성시 각각 다른 Thread 에서 접근되기 때문에 Mutex 사용


	pthread_t _runGenerateLongPressThreadID;			///< KeyButton Device 로 부터 Event 탐지 및 Handler 를 호출하는 Thread ID
	int32_t _runGenerateLongPressThreadState;			///< Thread 실행 상태 - 0:정지, 1:실행중

};



#endif	// __KEY_BUTTON_H__
