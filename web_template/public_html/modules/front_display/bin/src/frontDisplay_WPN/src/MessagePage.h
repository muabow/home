#ifndef __MESSAGE_PAGE_H__
#define __MESSAGE_PAGE_H__


#include <avmsapi/GFX.h>
#include <avmsapi/OLED.h>
#include <avmsapi/utility/SystemUtil.h>

#include "AbstractPage.h"


class MessagePage : public AbstractPage {
public:
	MessagePage(const string title = "") throw (const char*)
		: _headerCanvas(HEADER_WIDTH, HEADER_HEIGHT)
		, _bodyCanvas(BODY_WIDTH, BODY_HEIGHT)
		, _title(title)
		, _message("")
	{};
	virtual ~MessagePage() throw (const char*) {};

	virtual void draw(OLED* oled) {
		oled->clear();


		std::string message;

		// Header {
		_headerCanvas.fillScreen(0);
		// TODO : 약간 큰 폰트 적용 (freeType 또는 static font)
#if 0		
		oled->setFont(u8g2_font_7x13_tr);
#endif
		_headerCanvas.setTextColor(1, 0);
		_headerCanvas.setTextSize(1);

		message = _title;
		_headerCanvas.drawStr((HEADER_WIDTH - _headerCanvas.getTextWidth(message.c_str())) / 2, 4, message.c_str());


		// 가로 : Center, 세로 : Top
		oled->drawBitmap((WIDTH - HEADER_WIDTH) / 2, 0, _headerCanvas.getBuffer(), HEADER_WIDTH, HEADER_HEIGHT);
		// Header }


		// Body {
		int32_t y = (BODY_HEIGHT - 8) / 2;

		_bodyCanvas.fillScreen(0);

		_bodyCanvas.setTextColor(1, 0);
		_bodyCanvas.setTextSize(1);
		message = _message;

		_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())) / 2, y, message.c_str());


		// 가로 : Center, 세로 : Bottom
		oled->drawBitmap((WIDTH - BODY_WIDTH) / 2, HEIGHT - BODY_HEIGHT, _bodyCanvas.getBuffer(), BODY_WIDTH, BODY_HEIGHT);
		// Body }

		oled->update();
	}

	void setMessage(const string message) {
		_headerCanvas.fillScreen(0x0);
		_bodyCanvas.fillScreen(0x0);
		_message = message;
	}


private:
	static const uint32_t HEADER_WIDTH = 128; 		///< Header Width
	static const uint32_t HEADER_HEIGHT = 19; 		///< Header Height

	static const uint32_t BODY_WIDTH = 128;			///< Body Width
	static const uint32_t BODY_HEIGHT = 45;			///< Body Height

	Canvas1 _headerCanvas;		///< Header Canvas
	Canvas1 _bodyCanvas;		///< Body Canvas

	const string _title;

	string _message;
};

#endif	// __MESSAGE_PAGE_H__

