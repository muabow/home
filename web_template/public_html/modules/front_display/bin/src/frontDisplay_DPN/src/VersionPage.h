#ifndef __VERSION_PAGE_H__
#define __VERSION_PAGE_H__


#include <avmsapi/GFX.h>
#include <avmsapi/OLED.h>

#include "AbstractPage.h"


class VersionPage : public AbstractPage {
public:
	VersionPage() throw (const char*)
		: _canvas(width, height)
		, _name("")
		, _version("0.0.0.0")
	{};
	virtual ~VersionPage() throw (const char*){};

	virtual void draw(OLED* oled) {
		std::string message;

		oled->clearBuffer();

		// Header {
		oled->setFont(u8g2_font_7x13_tr);

		message = "Version Info.";
		oled->drawStr((OLED::WIDTH - oled->getStrWidth(message.c_str())) / 2, 12, message.c_str());
		// Header }


		// Body {

		// Version
		message = "Ver. ";
		message.append(_version);

#if !defined(USE_GFX)
		oled->setFont(u8g2_font_6x10_tr);
		
		oled->drawStr((128 - oled->getStrWidth(message.c_str())) / 2, 40, message.c_str());
#else	// USE_GFX
		_canvas.setTextColor(1, 0);
		_canvas.setTextSize(1);

		_canvas.drawStr((width - _canvas.getTextWidth(message.c_str())) / 2, 20, message.c_str());

		oled->drawBitmap((OLED::WIDTH - width) / 2, OLED::HEIGHT - height, width / 8, height, _canvas.getBuffer());
#endif	// USE_GFX
		// Body }

		oled->sendBuffer();
	}

private:
	static const uint32_t width = 128;	///< Body Width
	static const uint32_t height = 45;	///< Body Height

	Canvas1 _canvas;		///< Body Canvas

	std::string _name;		///< Name
	std::string _version;	///< Version

};

#endif	// __VERSION_PAGE_H__

