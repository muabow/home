#include <fcntl.h>		// O_WRONLY	// temp
#include <unistd.h>		// read
#include <fstream>

#include <avmsapi/GFX.h>
#include <avmsapi/OLED.h>
#include <avmsapi/utility/SystemUtil.h>

#include "PowerPage.h"

PowerLogoPage::PowerLogoPage(const string path, const string filename) throw (const char*)
	: _bodyCanvas(BODY_WIDTH, BODY_HEIGHT)
	, _powerlogo(path, filename)
{
}


void PowerLogoPage::draw(OLED* oled)
{
	oled->clear();
	_bodyCanvas.fillScreen(0);

	// Logo {
	int32_t y  = 2;
	_bodyCanvas.drawBitmap((BODY_WIDTH - _powerlogo.width()) / 2, y, _powerlogo.data(), _powerlogo.width(), _powerlogo.height(), 1);
	y += _powerlogo.height() +2;
	// Logo }


	string message;

	_bodyCanvas.setTextColor(1, 0);
	_bodyCanvas.setTextSize(1);
	message.append("AMP POWER CONTROL");

	_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())) / 2, y, message.c_str());
	y +=  17;


	oled->drawBitmap((WIDTH - BODY_WIDTH) / 2, HEIGHT - BODY_HEIGHT, _bodyCanvas.getBuffer(), BODY_WIDTH, BODY_HEIGHT);

	oled->update();
}

