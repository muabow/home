#include <fcntl.h>		// O_WRONLY	// temp
#include <unistd.h>		// read
#include <fstream>

#include <rapidjson/document.h>
#include <rapidjson/prettywriter.h>
#include <rapidjson/istreamwrapper.h>


#include <avmsapi/GFX.h>
#include <avmsapi/OLED.h>
#include <avmsapi/utility/SystemUtil.h>

#include "LogoPage.h"


using namespace rapidjson;


const std::string LogoPage::CONFIG_FILE_NAME = "/opt/interm/conf/env.json";

inline bool isEnabledBonding(string bondingDev = "") {
	if (bondingDev.empty()) {
		return false;
	}

	char cmd[128] = {0, };
	sprintf(cmd, "[ `cat /proc/net/bonding/%s | grep 'MII Status:' | head -1 | sed 's/^MII Status: //'` = 'down' ] ; echo $?", bondingDev.c_str());
	std::string resultString = "";
	SystemUtil::runCommand(cmd, resultString);
	
	int32_t result = 0;
	sscanf(resultString.c_str(), "%d", &result);
	if (result > 0) {
		return true;
	}
	else {
		return false;
	}
}


void LogoPage::init() throw (const char*)
{
	// 1. File 읽기
	ifstream configFileStream(LogoPage::CONFIG_FILE_NAME.c_str(), ios::in|ios::binary);
	if (configFileStream.is_open() == false) {
		char buf[MAX_BUF] = {0,};
		sprintf(buf, "Unable to read configation file. (%s)", LogoPage::CONFIG_FILE_NAME.c_str());
		std::string throwMessage = buf;
		configFileStream.close();
//		throw throwMessage.c_str();
		return;
	}


	// 2. JSON 포멧 확인
	IStreamWrapper configFileStreamWrapper(configFileStream);
	Document configDocument;
    ParseResult parseResult = configDocument.ParseStream(configFileStreamWrapper);
	configFileStream.close();
	if (parseResult == false) {
		char buf[MAX_BUF] = {0,};
		sprintf(buf, "Invalid JSON format. (%s)", LogoPage::CONFIG_FILE_NAME.c_str());
		std::string throwMessage = buf;
//		throw throwMessage.c_str();
		return;
	}
	

	// 3. Configuration 포멧 확인 및 정보 읽음
	// Device
	if (configDocument.HasMember("device"))
	{
		const Value& deviceObject = configDocument["device"];
		if (deviceObject.IsObject()) {

			// Name	
			if (deviceObject.HasMember("name")) {
				const Value& nameObject = deviceObject["name"];
				if (nameObject.IsString()) {
					_name = nameObject.GetString();		// GetValue!!!
//					printf("Name = %s\n", _name.c_str());
				}
			}
			// Version
			if (deviceObject.HasMember("version")) {
				const Value& versionObject = deviceObject["version"];
				if (versionObject.IsString()) {
					_version = versionObject.GetString();		// GetValue!!!
//					printf("Version = %s\n", _version.c_str());
				}
			}
			// Volume Control Device {
			// Type
			if (deviceObject.HasMember("device_type")) {
				const Value& typeObject = deviceObject["device_type"];
				if (typeObject.IsString()) {
					_type = typeObject.GetString();		// GetValue!!!
//					printf("Type = %s\n", _type.c_str());
					std::transform(_type.begin(), _type.end(), _type.begin(), ::toupper);
					if (_type.compare((string)"AMP") == 0) {
						_isVolumeControlDevice = true;
					}
				}
			}
			// Volume Control Device }
		}
	}
}

LogoPage::LogoPage(const string path, const string filename, const string title1, const string interface1) throw (const char*)
	: _bodyCanvas(BODY_WIDTH, BODY_HEIGHT)
	, _logo(path, filename)
	, _title1(title1)
	, _interface1(interface1)
	, _title2("")
	, _interface2("")
	, _titleBonding("")
	, _interfaceBonding("")
	, _interfaceCount(1)
	, _name("")
	, _version("0.0.0.0")
	, _isVolumeControlDevice(false)
	, _volume(0)
{
	init();
	printf("[Product]\n");
	printf("Name : %s\n", _name.c_str());
	printf("Version : %s\n", _version.c_str());
}


LogoPage::LogoPage(const string path, const string filename, const string title1, const string interface1, const string title2, const string interface2, const string titleBonding /* = "" */, const string interfaceBonding /* = "" */) throw (const char*)
	: _bodyCanvas(BODY_WIDTH, BODY_HEIGHT)
	, _logo(path, filename)
	, _title1(title1)
	, _interface1(interface1)
	, _title2(title2)
	, _interface2(interface2)
	, _titleBonding(titleBonding)
	, _interfaceBonding(interfaceBonding)
	, _interfaceCount(2)
	, _name("")
	, _version("0.0.0.0")
	, _isVolumeControlDevice(false)
	, _volume(0)
{
	init();
	printf("[Product]\n");
	printf("Name : %s\n", _name.c_str());
	printf("Version : %s\n", _version.c_str());
}


void LogoPage::draw(OLED* oled)
{
	const bool isBonding = isEnabledBonding(_interfaceBonding);

	char ipAddr[3][16] = {};
	SystemUtil::getIpAddress(_interface1.c_str(), ipAddr[0]);
	if (_interfaceCount == 2) {
		SystemUtil::getIpAddress(_interface2.c_str(), ipAddr[1]);
	}
	if (isBonding) {
		SystemUtil::getIpAddress(_interfaceBonding.c_str(), ipAddr[2]);
	}

	oled->clear();
	_bodyCanvas.fillScreen(0);

	if(!_isVolumeControlDevice) {
		// Logo {
		int32_t y = _interfaceCount == 2 ? 2 : 4;
		_bodyCanvas.drawBitmap((BODY_WIDTH - _logo.width()) / 2, y, _logo.data(), _logo.width(), _logo.height(), 1);
		y += _logo.height() + (_interfaceCount == 2 ? 2 : 6);
		// Logo }


		// Version {
		string message;

		_bodyCanvas.setTextColor(1, 0);
		_bodyCanvas.setTextSize(1);

		message = _name;
		if (message.empty() == false) {
			message.append(" ");
		}
		message.append(_version);
		_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())) / 2, y, message.c_str());
		y +=  14;
		// Version }


		// IPs {
		if (isBonding) {
			y += 4;
			message = _titleBonding;
			message.append(" ");
			message.append(ipAddr[2]);
			_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())) / 2, y, message.c_str());
		}
		else {
			message = _title1;
			message.append(" ");
			message.append(ipAddr[0]);
			_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())) / 2, _interfaceCount == 2 ? y : (y + 4), message.c_str());

			if (_interfaceCount == 2) {
				y += 10;
				message = _title2;
				message.append(" ");
				message.append(ipAddr[1]);
				_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())) / 2, y, message.c_str()); 
			}

		}
		// IPs }
	}
	else {
		// Volume Control Device {
		
		// Logo {
		int32_t y = 0;
		_bodyCanvas.drawBitmap((BODY_WIDTH - _logo.width()) / 2, y, _logo.data(), _logo.width(), _logo.height(), 1);
		y += _logo.height() + 2;
		// Logo }


		// Version {
		string message;

		_bodyCanvas.setTextColor(1, 0);
		_bodyCanvas.setTextSize(1);

		message = _name;
		if (_name != "") {
			message.append(" ");
		}
		message.append(_version);
		_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())) / 2, y, message.c_str());
		y +=  9;
		// Version }

		
		// IPs {
		if (isBonding) {
			message = _titleBonding;
			message.append(" ");
			message.append(ipAddr[2]);
			_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())) / 2, y, message.c_str());
		}
		else {
			message = _title1;
			message.append(" ");
			message.append(ipAddr[0]);
			_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())) / 2, y, message.c_str());
		}
		y += 9;
		// IPs }
		
		
		// Volume {	
		message = "Vol.";
		
		char buf[MAX_BUF] = {0,};
		uint32_t volume = getVolume();
		sprintf(buf, "%3d", volume);
		message.append(buf);
		
		_bodyCanvas.drawRect(13, y, 102, 16, 1);	
		_bodyCanvas.fillRect(14, y+1, volume, 14, 1);
		_bodyCanvas.fillRect((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())-6) / 2, y+4, _bodyCanvas.getTextWidth(message.c_str())+6, 9, 0);
		_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())) / 2, y+5, message.c_str());
		
		// Volume }

		// Volume Control Device }
	}
	
	
	oled->drawBitmap((WIDTH - BODY_WIDTH) / 2, HEIGHT - BODY_HEIGHT, _bodyCanvas.getBuffer(), BODY_WIDTH, BODY_HEIGHT);

	oled->update();
}

