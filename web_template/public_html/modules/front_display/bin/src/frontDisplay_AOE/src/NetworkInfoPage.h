#ifndef __NETWORK_INFO_PAGE_H__
#define __NETWORK_INFO_PAGE_H__

#include <fcntl.h>		// O_WRONLY	// temp
#include <unistd.h>		// read
#include <fstream>

#include <rapidjson/document.h>
#include <rapidjson/prettywriter.h>
#include <rapidjson/istreamwrapper.h>


#include <avmsapi/GFX.h>
#include <avmsapi/OLED.h>
#include <avmsapi/utility/SystemUtil.h>

#include "AbstractPage.h"


using namespace rapidjson;



class NetworkInfoPage : public AbstractPage {
public:

	NetworkInfoPage(const string interface) throw (const char*)
		: _headerCanvas(HEADER_WIDTH, HEADER_HEIGHT)
		, _bodyCanvas(BODY_WIDTH, BODY_HEIGHT)
		, _interface(interface)

	{
		// 1. File 읽기
		ifstream configFileStream(NetworkInfoPage::CONFIG_FILE_NAME.c_str(), ios::in|ios::binary);
		if (configFileStream.is_open() == false) {
			char buf[MAX_BUF] = {0,};
			sprintf(buf, "Unable to read configation file. (%s)", NetworkInfoPage::CONFIG_FILE_NAME.c_str());
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
			sprintf(buf, "Invalid JSON format. (%s)", NetworkInfoPage::CONFIG_FILE_NAME.c_str());
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

				// Version
				if (deviceObject.HasMember("version")) {
					const Value& versionObject = deviceObject["version"];
					if (versionObject.IsString()) {
						_version = versionObject.GetString();		// GetValue!!!
	//					printf("Version = %s\n", _version.c_str());
					}
				}
			}
		}
	};
	virtual ~NetworkInfoPage() throw (const char*) {};

	virtual void draw(OLED* oled) {
		// MAC Address
		char macAddr[32] = {};
		SystemUtil::getMacAddress(_interface.c_str(), macAddr);

		// Network Traffic
		float rx = 0.0;
		float tx = 0.0;
		string rxStr = "0.0 KB/s";
		string txStr = "0.0 KB/s";

		int32_t link = 100;
		if (SystemUtil::getNetworkUsage(_interface.c_str(), rx, tx, link) == false) {
			printf("ERROR : Network Usage\n");
			rx = 0.0;
			tx = 0.0;
			link = 100;
		}
		const char* msgFormat = "%.2f %s";
		char buf[16] = {};
		// RX
		sprintf(buf, msgFormat, rx, "KB/s");
		rxStr = buf;
//		printf("rx : %s\n", buf);
		// TX
		sprintf(buf, msgFormat, tx, "KB/s");
		txStr = buf;
//		printf("tx : %s\n", buf);


		std::string message;

		oled->clear();

		// Header {
		_headerCanvas.fillScreen(0);
		_headerCanvas.setTextColor(1, 0);
		_headerCanvas.setTextSize(2);

		message = "Network";
		_headerCanvas.drawStr((HEADER_WIDTH - _headerCanvas.getTextWidth(message.c_str())) / 2, 4, message.c_str());

		// 가로 : Center, 세로 : Top
		oled->drawBitmap((WIDTH - HEADER_WIDTH) / 2, CANVAS_Y_OFFSET, _headerCanvas.getBuffer(), HEADER_WIDTH, HEADER_HEIGHT);
		// Header }


		// Body {
		const int32_t x = 50;

		int32_t y = 6;

		_bodyCanvas.fillScreen(0);
		_bodyCanvas.setTextColor(1, 0);
		_bodyCanvas.setTextSize(1);


		message = "";
		message.append(macAddr);
		_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())) / 2, y, message.c_str());
		y += 10;

		message = "Ver. ";
		message.append(_version);
		_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())) / 2, y, message.c_str());
		y += 10;

		message = "Tx: ";
		_bodyCanvas.drawStr(x - _bodyCanvas.getTextWidth(message.c_str()), y, message.c_str());
		_bodyCanvas.drawStr(x, y, txStr.c_str());
		y += 10;
		
		message = "Rx: ";
		_bodyCanvas.drawStr(x - _bodyCanvas.getTextWidth(message.c_str()), y, message.c_str());
		_bodyCanvas.drawStr(x, y, rxStr.c_str());


		// 가로 : Center, 세로 : Bottom
		oled->drawBitmap((WIDTH - BODY_WIDTH) / 2, HEIGHT - BODY_HEIGHT + CANVAS_Y_OFFSET, _bodyCanvas.getBuffer(), BODY_WIDTH, BODY_HEIGHT);
		// Body }

		oled->update();
	}

private:
	static const uint32_t CANVAS_Y_OFFSET = 2;

	static const uint32_t HEADER_WIDTH = 128; 		///< Header Width
	static const uint32_t HEADER_HEIGHT = 19; 		///< Header Height

	static const uint32_t BODY_WIDTH = 128;			///< Body Width
	static const uint32_t BODY_HEIGHT = 45;			///< Body Height

	static const string CONFIG_FILE_NAME;			///< Device Name, Version 정보가 있는 설정 파일 명
	static const uint32_t MAX_BUF = 128;

	Canvas1 _headerCanvas;		///< Header Canvas
	Canvas1 _bodyCanvas;		///< Body Canvas

	const string _interface;
	string _version;
};

const std::string NetworkInfoPage::CONFIG_FILE_NAME = "/opt/interm/conf/env.json";


#endif	// __NETWORK_INFO_PAGE_H__

