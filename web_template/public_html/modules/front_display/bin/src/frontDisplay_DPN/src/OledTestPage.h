#ifndef __OLED_TEST_PAGE_H__
#define __OLED_TEST_PAGE_H__


#include <avmsapi/GFX.h>
#include <avmsapi/OLED.h>
#include <avmsapi/utility/SystemUtil.h>

#include "AbstractPage.h"


class OledTestPage : public AbstractPage {
public:
	OledTestPage(const string interface, const string title = "") throw (const char*)
		: _headerCanvas(HEADER_WIDTH, HEADER_HEIGHT)
		, _bodyCanvas(BODY_WIDTH, BODY_HEIGHT)
		, _interface(interface)
		, _title(title)
	{};
	virtual ~OledTestPage() throw (const char*) {};

	virtual void draw(OLED* oled) {
		//char ipAddr[16] = {};
		//SystemUtil::getIpAddress(_interface.c_str(), ipAddr);

		//char ipMask[16] = {};
		//SystemUtil::getIpMask(_interface.c_str(), ipMask);

		//char dnsServer[16] = {};
		//SystemUtil::getDNS(_interface.c_str(), dnsServer);

		//char gateway[16] = {};
		//SystemUtil::getGateway(_interface.c_str(), gateway);
		
		//char macAddr[32] = {};
		//SystemUtil::getMacAddress(_interface.c_str(), macAddr);


		oled->clear();


//		std::string message;

		// Header {
		_headerCanvas.fillScreen(1);
		// TODO : 약간 큰 폰트 적용 (freeType 또는 static font)
#if 0		
		oled->setFont(u8g2_font_7x13_tr);
#endif
//		_headerCanvas.setTextColor(1, 0);
//		_headerCanvas.setTextSize(1);

//		message = " !! TEST !! ";
//		if (_title.size() > 0) {
//			message.append(_title);
//		}
//		else {
//			message.append(_interface);
//		}
//		_headerCanvas.drawStr((HEADER_WIDTH - _headerCanvas.getTextWidth(message.c_str())) / 2, 4, message.c_str());


		// 가로 : Center, 세로 : Top
		oled->drawBitmap((WIDTH - HEADER_WIDTH) / 2, 0, _headerCanvas.getBuffer(), HEADER_WIDTH, HEADER_HEIGHT);
		// Header }


		// Body {
//		int32_t y = 6;

		_bodyCanvas.fillScreen(1);
/*
		_bodyCanvas.setTextColor(1, 0);
		_bodyCanvas.setTextSize(1);

		message = "123 ";
		//message.append(ipAddr);
		_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())) / 2, y, message.c_str());
		y += 10;

		message = "456 ";
		message.append(ipMask);
		_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())) / 2, y, message.c_str());
		y += 10;

		message = "789 ";
		message.append(dnsServer);
		_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())) / 2, y, message.c_str());
		y += 10;
		
//		message = "MAC ";
		message = "";
		message.append(macAddr);
//		std::transform(message.begin(), message.end(), message.begin(), ::toupper);
		_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())) / 2, y, message.c_str());

*/
		// 가로 : Center, 세로 : Bottom
		oled->drawBitmap((WIDTH - BODY_WIDTH) / 2, HEIGHT - BODY_HEIGHT, _bodyCanvas.getBuffer(), BODY_WIDTH, BODY_HEIGHT);
		// Body }

		oled->update();
	}


private:
	static const uint32_t HEADER_WIDTH = 128; 		///< Header Width
	static const uint32_t HEADER_HEIGHT = 19; 		///< Header Height

	static const uint32_t BODY_WIDTH = 128;			///< Body Width
	static const uint32_t BODY_HEIGHT = 45;			///< Body Height

	Canvas1 _headerCanvas;		///< Header Canvas
	Canvas1 _bodyCanvas;		///< Body Canvas

	const string _interface;
	const string _title;
};

#endif	// __OLED_TEST_PAGE_H__

