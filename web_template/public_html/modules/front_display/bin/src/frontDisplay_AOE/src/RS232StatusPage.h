#ifndef __RS232_STATUS_PAGE_H__
#define __RS232_STATUS_PAGE_H__


#include <avmsapi/GFX.h>
#include <avmsapi/OLED.h>
#include <avmsapi/utility/SystemUtil.h>

#include "AbstractPage.h"


class RS232StatusPage : public AbstractPage {
public:

	RS232StatusPage() throw (const char*)
		: _headerCanvas(HEADER_WIDTH, HEADER_HEIGHT)
		, _bodyCanvas(BODY_WIDTH, BODY_HEIGHT)
	{};
	virtual ~RS232StatusPage() throw (const char*) {};

	virtual void draw(OLED* oled) {
		std::string message;

		oled->clear();

		// Header {
		_headerCanvas.fillScreen(0);
		_headerCanvas.setTextColor(1, 0);
		_headerCanvas.setTextSize(2);

		message = "RS-232";
		_headerCanvas.drawStr((HEADER_WIDTH - _headerCanvas.getTextWidth(message.c_str())) / 2, 4, message.c_str());

		// 가로 : Center, 세로 : Top
		oled->drawBitmap((WIDTH - HEADER_WIDTH) / 2, CANVAS_Y_OFFSET, _headerCanvas.getBuffer(), HEADER_WIDTH, HEADER_HEIGHT);
		// Header }


		// Body {
		const int32_t x = 76;

		int32_t y = 10;
		char cmd[MAX_BUF] = {0, };

		_bodyCanvas.fillScreen(0);
		_bodyCanvas.setTextColor(1, 0);
		_bodyCanvas.setTextSize(1);


		message = "Server ";
		_bodyCanvas.drawStr(x - _bodyCanvas.getTextWidth(message.c_str()), y, message.c_str());

		std::string serverStatus;
		sprintf(cmd, "netstat -antp | grep %s | grep %s | wc -l", SERVER_PROCESS.c_str(), "LISTEN");	// On / Off 상태
		SystemUtil::runCommand(cmd, serverStatus);
		if (std::stoi(serverStatus) > 0) {
			std::string serverPort;			// 포트번호
			sprintf(cmd, "netstat -antp | grep %s | grep %s | grep %s | awk {'split($4, net_info, \":\"); print net_info[2]'}", SERVER_PROCESS.c_str(), "0.0.0.0", "LISTEN");
			SystemUtil::runCommand(cmd, serverPort);

			std::string connectedCount;		// 연결된 Client 수
			sprintf(cmd, "netstat -antp | grep %s | grep %s | grep %d | wc -l", SERVER_PROCESS.c_str(), "ESTABLISHED", std::stoi(serverPort));
			SystemUtil::runCommand(cmd, connectedCount);
			message = "On, ";
			message.append(connectedCount);
			_bodyCanvas.drawStr(x, y, message.c_str());
		}
		else {
			_bodyCanvas.drawStr(x, y, "Off");
		}
		y += 14;



		message = "Client ";
		_bodyCanvas.drawStr(x - _bodyCanvas.getTextWidth(message.c_str()), y, message.c_str());

		std::string clientStatus;
		sprintf(cmd, "netstat -antp | grep %s | grep %s | grep -v %s | awk '{print $5}'", CLIENT_PROCESS.c_str(), "ESTABLISHED", "127.0.0.1");
		SystemUtil::runCommand(cmd, clientStatus);
		if (clientStatus.empty() == false) {
			_bodyCanvas.drawStr(x, y, "On");
			y += 10;
		
			_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(clientStatus.c_str())) / 2, y, clientStatus.c_str());
		}
		else {
			_bodyCanvas.drawStr(x, y, "Off");
		}

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

	static const uint32_t MAX_BUF = 128;

	// Process
	static const std::string SERVER_PROCESS;
	static const std::string CLIENT_PROCESS;


	Canvas1 _headerCanvas;		///< Header Canvas
	Canvas1 _bodyCanvas;		///< Body Canvas

};

const std::string RS232StatusPage::SERVER_PROCESS = "serial_232_s";
const std::string RS232StatusPage::CLIENT_PROCESS = "serial_232_c";


#endif	// __RS232_STATUS_PAGE_H__

