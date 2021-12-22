/**
	@file	
	@brief	RotaryEncoder 제어
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.04.10
 */
 
#ifndef __ROTARY_ENCODER_H__
#define __ROTARY_ENCODER_H__


#include <stdint.h>
#include <cstdio>
#include <atomic>
#include <thread>
#include <mutex>

#include "AVMSAPI.h"
#include "GPIO.h"
#include "KeyButton.h"




/**
	@brief	Event 발생시 호출되는 Handler 의 Prototype 정의
	@param	[in] id	ID
	@param	[in] rotaryValue	회전 값 (1:CW / -1:CCW)
	@param	[in] continuousRotaryValue	연속된 회전 값 (+:CW / -:CCW)
	@param	[in] pushEventType	Push Button 의 Event Type (Release / Press / DoublePress/ LongPress / None)
	@param	[in,out] param	EventHandler 등록시 전달한 인자
 */
typedef void (*RotaryEncoderEventHandler)(const int32_t id, const int32_t rotaryValue, const int32_t continuousRotaryValue, const KeyButton::EventType pushEventType, void* param);



/**
	@class	RotaryEncoder
	@brief	RotaryEncoder 제어
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.04.10
 */
class RotaryEncoder {
public:
	typedef struct Info {
		std::string deviceNode;	///< 해당 값의 유무에 따라 Device Type 식별 가능 - "":gpio, "/device/input/eventX":input
		int32_t encA;	///< GPIO Pin 번호
		int32_t encB;	///< GPIO Pin 번호
		int32_t encSW;	///< GPIO Pin 번호
		bool invert;	///< KeyButton 의 Up/Down 시 Value 상태 반전 - 0:pull-down, 1:pull-up
		Info()
			: deviceNode("")
			, encA(-1)
			, encB(-1)
			, encSW(-1)
			, invert(false)
		{}
	} Info;

private:
	/**
		@brief	Input Device 를 이용한 RotaryEncoder 설정
		@throw	에러 메세지
	*/
	void initGPIO(const int32_t encA, const int32_t encB) throw (const char*);

	/**
		@brief	Input Device 를 이용한 RotaryEncoder 설정
		@throw	에러 메세지
	*/
	void initInput() throw (const char*);

public:
	/**
		@brief	RotaryEncoder 생성자 (Real GPIO)
		@param	[in] encA	GPIO 핀 번호
		@param	[in] encB	GPIO 핀 번호
		@param	[in] push	GPIO 핀 번호
		@param	[in] handler	Event 발생시 호출되는 Handler
		@param	[in] id	Event Handler 호출시 반환될 ID
		@param	[in,out] param	Event Handler 호출시 전달할 인자
		@param	[in] invert	KeyButton 의 Up/Down 시 Value 상태 반전
		@throw	에러 메세지
	 */
	RotaryEncoder(const int32_t encA, const int32_t encB, const int32_t push, const RotaryEncoderEventHandler handler, const int32_t id, void* param = NULL, const bool invert = false) throw (const char*);

	/**
		@brief	RotaryEncoder 생성자 (Real GPIO)
		@param	[in] devNode	Encoder 장치명 (Device Node)
		@param	[in] push	GPIO 핀 번호
		@param	[in] handler	Event 발생시 호출되는 Handler
		@param	[in] id	Event Handler 호출시 반환될 ID
		@param	[in,out] param	Event Handler 호출시 전달할 인자
		@param	[in] invert	KeyButton 의 Up/Down 시 Value 상태 반전
		@throw	에러 메세지
	 */
	RotaryEncoder(const std::string devNode, const int32_t push, const RotaryEncoderEventHandler handler, const int32_t id, void* param = NULL, const bool invert = false) throw (const char*);

	/**
		@brief	RotaryEncoder 생성자 (Real GPIO)
		@param	[in] devNode	Encoder 장치명 (Device Node)
		@param	[in] push	GPIO 핀 번호
		@param	[in] handler	Event 발생시 호출되는 Handler
		@param	[in] id	Event Handler 호출시 반환될 ID
		@param	[in,out] param	Event Handler 호출시 전달할 인자
		@throw	에러 메세지
	 */
	RotaryEncoder(const RotaryEncoder::Info info, const RotaryEncoderEventHandler handler, const int32_t id, void* param = NULL) throw (const char*);

	/**
		@brief	RotaryEncoder 소멸자
		@throw	에러 메세지
	 */
	virtual ~RotaryEncoder() throw (const char*);

private:
	/**
		@brief	GPIO 에서 Event 발생시 호출 될 Handler
				\n전달받은 값으로 RotaryEncoder 의 회전 상태를 분석하여 등록된 Event Hander 호출
		@see	init()
		@param	[in] id GPIO_EVENT_HANDLER_ID_ENC_A / GPIO_EVENT_HANDLER_ID_ENC_A
		@param	[in] value	GPIO 상태 값
		@param	[in,out] param	rotaryEncoder 의 this
	 */
	static void onGPIOEventHandler(const int32_t id, const int32_t value, void* param);

	/**
		@brief	KeyButton 에서 Event 발생시 호출 될 Handler
				\n전달받은 값에 회전 상태를 추가하여 등록된 Event Hander 호출
		@see	init()
		@param	[in] id UID
		@param	[in] eventType	Event 종류 (Release / Press / DoublePress / LongPress)
		@param	[in,out] param	rotaryEncoder 의 this
	 */
	static void onKeyButtonEventHandler(const int32_t id, const /* KeyButton::EventType */ int32_t eventType, void* param);
	
	static void runEventReceiverThread(void* param);

private:
	/**
		@brief	내부에서 사용될 ID
	 */
	enum ID {
		GPIO_EVENT_HANDLER_ID_ENC_A = 0,	///< EncA
		GPIO_EVENT_HANDLER_ID_ENC_B,		///< EncB

		GPIO_EVENT_HANDLER_ID_MAX,			///< Encoder Pin ID 최대 갯수
	};

	static const uint32_t CONTINOUS_ROTARY_INPUT_TIME = 200;		///< 연속입력된 회전에 대한 기준 시간 (ms)

	std::string _devNode;						///< Encoder 장치명 (Device Node)
	GPIO* _encA;								///< GPIO Device
	GPIO* _encB;								///< GPIO Device
	KeyButton _pushButton;						///< Push KeyButton Device
	
	const RotaryEncoderEventHandler _eventHandler;	///< EventHandler
	const int32_t _eventHandlerID;					///< EventHandler 호출시 반환될 ID
	void* _param;									///< EventHandler 호출시 반환될 인자
	const bool _invert;								///< H/W 회로에 따라 버튼의 Down/Up 상태에 따른 값이 High/Low 로 반대일 수 있음


	uint32_t _lastRotaryEventClockTick; 	///< RotaryEvent 의 연속입력을 감지하기 위한 마지막 TimeTick 목록 [ID]
	int32_t _continuousRotaryValue;			///< RotaryEvent 의 연속입력된 회전 값 목록 [ID]

	int32_t _position; 
	int32_t _lastEventID; 
	
	int32_t _lastGpioValues[GPIO_EVENT_HANDLER_ID_MAX];				///< RotaryEvent 의  비정상적인 입력 필터링을 위한 GPIO 의 마지막 상태 목록 [GPIO]

	std::thread* _eventReceiverThread;
//	int32_t _runRotaryEncoderThreadState;			///< Thread 실행 상태 - 0:정지, 1:실행중
	std::atomic<bool> _isRequestedStop;
	int32_t _fd;		///< Rotary Encoder FD
//	std::mutex _mutex;


};

#endif	// __ROTARY_ENCODER_H__
