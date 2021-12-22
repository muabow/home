/**
	@file	
	@brief	GPIO 제어
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.03.22
 */

#ifndef __GPIO_H__
#define __GPIO_H__


#include <stdint.h>
#include <string>

#include "AVMSAPI.h"


class InputGroup;
class OutputGroup;



/**
	@brief	Event 발생시 호출되는 Handler Prototype 정의
	@param	[in] id	ID
	@param	[in] value	신호 값 (0:LOW / 1:HIGH)
	@param	[in,out] param	EventHandler 등록시 전달한 인자
 */
typedef void (*GPIOInputEventHandler)(const int32_t id, const /* GPIO::Value */ int32_t value, void* param);


/**
	@class	GPIO
	@brief	GPIO 제어
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.03.24
	@code
	GPIO btnPlay(GPIO::IN, 12);
	GPIO contactOut[CONTACT_OUT_MAX] = {
		GPIO(GPIO::OUT, GPIO::CH0, 1),
		GPIO(GPIO::OUT, GPIO::CH0, 2),
	}

	GPIO::Value btnValue = btnPlay.read();

	contactOut[0].write(GPIO::HIGH);
	contactOut[1].write(GPIO::LOW);
	@endcode
 */
class GPIO
//	: public IStringConvertable 
{
public:
	/**
		@brief	입출력 값
	 */
	enum Value {
		LOW = 0,	///< Low
		HIGH,		///< High
	};

	/**
		@brief	입출력 방향
	 */
	enum Direction {
		IN = 0,		///< Input
		OUT,		///< Output
	};

	/**
		@brief	Shift Register 채널
	 */
	enum Channel {
		NONE = -1,		///< None
		CH0 = 0,		///< Channel0
		CH1,			///< Channel1
		CH2,			///< Channel2
		CH3,			///< Channel3
		MAX_CHANNEL,	///< 최대 채널 수
	};

	/**
		@brief	Event 발생 조건
	 */
	enum Edge {
		BOTH = 0,		///< Both
		RISING,			///< Rising
		FALLING,		///< Falling
	};

	/**
		@brief	장치 종류
	 */
	enum DeviceType {
		DirectGPIO = 0,	///< Direct GPIO
		SR165,			///< Shift Register 165 (Input)
		SR595,			///< Shift Register 595 (Output)
	};

	typedef struct Info {
		GPIO::Direction direction;
		int32_t pinNumber;
		GPIO::Channel channel;
		int32_t bit;
		Info()
			: direction(GPIO::IN)
			, pinNumber(-1)
			, channel(NONE)
			, bit(-1)
		{}
	} Info;

private:
	bool init();
	bool cleanup();


public:
	/**
		@brief	GPIO 생성자
		@param	[in] gpioInfo	GPIO 정보
		@throw	에러 메세지
	 */
	GPIO(const GPIO::Info gpioInfo) throw (const char*);

	/**
		@brief	GPIO 생성자 (Real GPIO)
		@param	[in] direction	방향
		@param	[in] pinNumber	GPIO 핀 번호
		@throw	에러 메세지
	 */
	GPIO(const Direction direction, const int32_t pinNumber) throw (const char*);

	/**
		@brief	GPIO 생성자 (Shift Register 165 / 595)
		@param	[in] direction	방향
		@param	[in] channel	채널
		@param	[in] bit	비트
		@throw	에러 메세지
	 */
	GPIO(const Direction direction, const Channel channel, const int32_t bit) throw (const char*);
	
	/**
		@brief	GPIO 소멸자
		@throw	에러 메세지
	 */
	virtual ~GPIO() throw (const char*);

	/**
		@brief	즉시 읽기
		@return	읽은 값
	 */
	GPIO::Value read();

	/**
		@brief	InputGroup 에 등록 후 일괄 읽기
		@see	InputGroup::flush()
		@param	[out] inputGroup
		@param	[out] value 읽은 값을 반환 할 주소
		@return	true : 성공
		@return	false : 실패
	 */
	bool read(InputGroup& inputGroup, GPIO::Value* value);

	/**
		@brief	Event 발생시 호출되는 Handler 등록
		@param	[in] handler	Event 발생시 호출되는 Handler
		@param	[in] edge	Event 발생 조건
		@param	[in] id	Event Handler 호출시 반환될 ID
		@param	[in,out] param	Event Handler 호출시 전달할 인자
		@return	true : 성공
		@return	false : 실패
	 */
	bool setEventHandler(const GPIOInputEventHandler handler, const GPIO::Edge edge, const int32_t id, void* param = NULL);

	GPIOInputEventHandler getEventHandler() {	return _eventHandler;	};
	
	/**
		@brief	Event Handler ID 반환
		@see	setEventHandler()
		@return	Event Handler ID
	 */
	int32_t getEventHandlerID() const {	return _eventHandlerID;	};
	void setEventHandlerReady() {	_isEventHandlerReady = true;	};
	bool isEventHandlerReady() const {	return _isEventHandlerReady;	};



	/**
		@brief	즉시 쓰기
		@param	[in] value	쓸 값
		@return	true : 성공
		@return	false : 실패
	 */
	bool write(const GPIO::Value value);

	/**
		@brief	OutputGroup 에 등록 후 일괄 쓰기
		@see	OutputGroup::flush()
		@param	[out] outputGroup
		@param	[in] value	쓸 값
		@return	true : 성공
		@return	false : 실패
	 */
	bool write(OutputGroup& outputGroup, const GPIO::Value value);



	/**
		@brief	Device Type 반환
		@return	Device Type
	 */
	GPIO::DeviceType getDeviceType() const
	{
		GPIO::DeviceType retValue = DirectGPIO;
		if (_channel == GPIO::NONE) {
			retValue = DirectGPIO;
		}
		else
		{
			if (_direction == GPIO::IN) {
				retValue = SR165;
			}
			else if (_direction == GPIO::OUT) {
				retValue = SR595;
			}
		}
		return retValue;
	};
	
	/**
		@brief	입출력 방향 반환
		@return	입출력 뱡향
	 */
	GPIO::Direction getDirection() const {	return _direction;	};

	/**
		@brief	GPIO 핀 번호 반환
		@return	0 이상 : 성공 (Real GPIO 의 핀 번호)
		@return	-1 미만 : 실패 (Shift Register 사용)
	 */
	int32_t getPinNumber() const {	return _pinNumber;	};

	/**
		@brief	Shift Regster 의 채널 반환
		@return	0 이상 : 성공 (Shift Register 채널)
		@return	-1 미만 : 실패 (Real GPIO 사용)
	 */
	GPIO::Channel getSRChannel() const {	return _channel;	};
	
	/**
		@brief	Shift Regster 의 비트 반환
		@return	0 이상 : 성공 (Shift Register 비트)
		@return	-1 미만 : 실패 (Real GPIO 사용)
	 */
	int32_t getSRBit() const {	return _bit;	};
	void* getParam() const {	return _param;	};
	GPIO::Value getPendingWriteValue() const {	return _pendingWriteValue;	};
	GPIO::Value* getPendingReadValue() const {	return _pendingReadValue;	};



	/**
		@brief	내부 설정값으로 조합된 UniqueID 반환
		@return	UID
	 */
	int32_t getUID() const {
		// DIRECTION[4]/TYPE[3]/ (CHANNEL[2]/BIT[1:0] | PIN[2:0])
		const DeviceType deviceType = getDeviceType();
		if (deviceType == DirectGPIO) {
			return (_direction * 10000) + (deviceType * 1000) + _pinNumber;
		}
		else {
			return (_direction * 10000) + (deviceType * 1000) + (_channel * 100) + _bit;
		}
	};


#if 0
protected:
	// impl. - IStringConvertable
	virtual void makeAsString(std::string& buf) const;
#endif

public:
	static const std::string CONFIG_FILE_NALE;		///< GPIO Device 설정 파일 명

	static const std::string VALUE[];				///< Value 에 해당하는 문자
	static const std::string DIRECTION[];			///< Direction 에 해당하는 문자
	static const std::string EDGE[];				///< Edge 에 해당하는 문자


private:
	const Direction _direction;		///< 입출력 방향
	const int32_t _pinNumber;		///< GPIO 의 핀 번호
	const Channel _channel;			///< Shift Register 의 채널
	const int32_t _bit;				///< Shift Register 의 비트


	GPIOInputEventHandler _eventHandler;	///< Event Handler
	int32_t _eventHandlerID;				///< Event Handler 호출시 반환될 ID
	void* _param;							///< Event Handler 호출시 반환될 인자
	bool _isEventHandlerReady;				///< Event Handler 준비 상태 (의도치 않게 발생하는 첫번째 Event 를 무시하기 위해 사용)


	GPIO::Value _pendingWriteValue;		///< 일괄 쓰기시에 Write 할 값
	GPIO::Value* _pendingReadValue;		///< 일괄 읽기시에 Read 한 값을 반환 할 주소
};

#endif // __GPIO_H__
