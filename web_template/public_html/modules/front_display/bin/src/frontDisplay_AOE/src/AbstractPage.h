#ifndef __ABSTRACT_PAGE_H__
#define __ABSTRACT_PAGE_H__

#include <avmsapi/GPIO.h>

class OLED;



class AbstractPage {
public:
	AbstractPage() throw (const char*) {};
	virtual ~AbstractPage() throw (const char*) {};

	virtual void draw(OLED* oled) = 0;

protected:
	const static int32_t WIDTH = 128;
	const static int32_t HEIGHT = 64;
};

#endif	// __ABSTRACT_PAGE_H__
