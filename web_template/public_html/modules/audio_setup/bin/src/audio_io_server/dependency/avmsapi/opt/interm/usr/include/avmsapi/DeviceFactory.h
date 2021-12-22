/**
	@file	
	@brief	Device 생성
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.06.08
 */

#ifndef __DEVICE_FACTORY_H__
#define __DEVICE_FACTORY_H__


#include <stdint.h>
#include <string>
#include <vector>



#include "GPIO.h"
#include "SPI.h"
#include "RotaryEncoder.h"

class OLED;


/**
	@class	DeviceFactory
	@brief	Device 생성
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.06.07
 */
class DeviceFactory {

public:
	/**
		@brief	설정 파일로 부터 GPIO 정보 조회
		@param	[in] key	조회할 Key
		@param	[in] configFileName	설정 파일명
		@return	GPIO 정보 (Direction/PinNumber)
	 */
	static GPIO::Info getGPIOInfo(const std::string key, const std::string configFileName = "/opt/interm/conf/config-device-factory.json") throw (const char*);
	/**
		@brief	설정 파일로 부터 GPIO 장치 생성
		@param	[in] key	생성할 Key
		@param	[in] configFileName	설정 파일명
		@return	GPIO Instance
	 */
	static GPIO* createGPIO(const std::string key, const std::string configFileName = "/opt/interm/conf/config-device-factory.json") throw (const char*);

	/**
		@brief	설정 파일로 부터 SPI 정보 조회
		@param	[in] key	조회할 Key
		@param	[in] configFileName	설정 파일명
		@return	SPI 정보 (Device Node)
	 */
	static SPI::Info getSPIInfo(const std::string key, const std::string configFileName = "/opt/interm/conf/config-device-factory.json") throw (const char*);
	/**
		@brief	설정 파일로 부터 SPI 장치 생성
		@param	[in] key	생성할 Key
		@param	[in] configFileName	설정 파일명
		@return	SPI Instance
	 */
	static SPI* createSPI(const std::string key, const std::string configFileName = "/opt/interm/conf/config-device-factory.json") throw (const char*);

	/**
		@brief	OLED 장치 생성
		@param	[in] key	생성할 Key
		@return	OLED Instance
	 */
	static OLED* createOLED(const std::string key = "oled", const std::string configFileName = "/opt/interm/conf/config-device-factory.json") throw (const char*);

	/**
		@brief	설정 파일로 부터 RotaryEncoder 정보 조회
		@param	[in] key	조회할 Key
		@param	[in] configFileName	설정 파일명
		@return	RotaryEncoder 정보 (Device Node)
	 */
	static RotaryEncoder::Info getRotaryEncoderInfo(const std::string key, const std::string configFileName = "/opt/interm/conf/config-device-factory.json") throw (const char*);
	/**
		@brief	설정 파일로 부터 RotaryEncoder 장치 생성
		@param	[in] key	생성할 Key
		@param	[in] handler	Event 발생시 호출되는 Handler
		@param	[in] id Event Handler 호출시 반환될 ID
		@param	[in,out] param	Event Handler 호출시 전달할 인자
		@param	[in] configFileName	설정 파일명
		@return	RotaryEncoder Instance
	 */
	static RotaryEncoder* createRotaryEncoder(const std::string key, const RotaryEncoderEventHandler handler, const int32_t id, void* param = NULL, const std::string configFileName = "/opt/interm/conf/config-device-factory.json") throw (const char*);

	/**
		@brief	설정 파일로 부터 Network Interface 장치명 조회
		@param	[in] configFileName	설정 파일명
		@return	Network Interface 장치명 목록
	 */
	static std::vector<std::string> getNetworkInterfaces(const std::string configFileName = "/opt/interm/conf/config-device-factory.json") throw (const char*);

private:
	static const uint32_t MAX_BUF = 128;
};


#endif	// __DEVICE_FACTORY_H__

