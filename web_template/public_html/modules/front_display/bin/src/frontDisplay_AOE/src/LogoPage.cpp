#include <fcntl.h>		// O_WRONLY	// temp
#include <unistd.h>		// read
#include <fstream>


#include <avmsapi/GFX.h>
#include <avmsapi/OLED.h>
#include <avmsapi/utility/SystemUtil.h>

#include "LogoPage.h"



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
{
}


void LogoPage::draw(OLED* oled)
{
	char ipAddr[3][16] = {};
	SystemUtil::getIpAddress(_interface1.c_str(), ipAddr[0]);


	oled->clear();

	int32_t y = 6;
	_bodyCanvas.fillScreen(0);

	// Logo {
	_bodyCanvas.drawBitmap((BODY_WIDTH - _logo.width()) / 2, y, _logo.data(), _logo.width(), _logo.height(), 1);
	y += _logo.height() + 20;
	// Logo }


	// Version {
	string message;

	_bodyCanvas.setTextColor(1, 0);
	_bodyCanvas.setTextSize(1);

#if 0
	// Version {


	message = _name;
	if (message.empty() == false) {
		message.append(" ");
	}
	message.append(_version);
	_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())) / 2, y, message.c_str());
	y += 14;
	// Version }
#endif


	// IPs {
	message = ipAddr[0];
	_bodyCanvas.drawStr((BODY_WIDTH - _bodyCanvas.getTextWidth(message.c_str())) / 2, y, message.c_str());

	// IPs }

	// 가로 : Center, 세로 : Bottom
	oled->drawBitmap((WIDTH - BODY_WIDTH) / 2, HEIGHT - BODY_HEIGHT + CANVAS_Y_OFFSET, _bodyCanvas.getBuffer(), BODY_WIDTH, BODY_HEIGHT);

	oled->update();
}

