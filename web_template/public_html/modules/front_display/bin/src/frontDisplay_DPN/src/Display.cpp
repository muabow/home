#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>		// O_RDWR
#include <unistd.h>		// close, unlink
#include <poll.h>

#include <avmsapi/GFX.h>//

#include <avmsapi/SPI.h>
#include <avmsapi/OLED.h>
#include <avmsapi/RotaryEncoder.h>
#include <avmsapi/DeviceFactory.h>

#include "Display.h"
#include "MessagePage.h"
#include "LogoPage.h"



const std::string Display::PIPE = "/tmp/frontDisplay_pipe";

Display::Display(const uint32_t screenSaverGracePeriod /* = 0 */) throw (const char*)
	: _oled(NULL)
	, _controller(NULL)
	, _currentPage(0)
	, _isRunning(false)
	, _isRequestedStop(false)
	, _messagePage(NULL)
	, _isMessagePage(false)
	, _pipeFD(-1)
	, _latestUpdateTick(0)
	, _screenSaverGracePeriod(screenSaverGracePeriod)
	, _latestTouchTick(0)
	, _connector(NULL)
	, _isVolumeControlDevice(false)
	, _isVolumeControlModeOff(false)
	, _volume(0)
	, _headerCanvas(NULL)
	, _bodyCanvas(NULL)
{
	// DeviceFactory with configuration file
	
	// OLED
	_oled = DeviceFactory::createOLED();

	try {
		// Controller
		_controller = DeviceFactory::createRotaryEncoder("main", &onControllerEventHandler, 1, this);
	}
	catch (const char* msg) {
		printf("Exception : %s\n", msg);
	}

	// Connector
	_connector = new AVMS_Connector();


	// Message Page
	_messagePage = new MessagePage("Emergency");
	if (unlink(PIPE.c_str()) < 0) {
//		printf("Error : Can not remove FIFO.\n");
	}
	// 다른 프로세서로부터 메세지 수신을 위한 FIFO 생성
	if (mkfifo(PIPE.c_str(), 0666) < 0) {
//		printf("Error : Can not make FIFO.\n");
	}
	if ( (_pipeFD = ::open(PIPE.c_str(), O_RDWR | O_NONBLOCK)) < 0) {	// 주의! FIFO에 O_RDONLY 적용후 poll() 호출시, POLLHUP 이벤트가 무한 수신 됨
		printf("Error : Can not open FIFO.\n");
	}
	else {
		printf("[Message Pipe]\n");
		printf("Pipe File : %s\n", PIPE.c_str());
		printf("\n");
	}


    // minho, Canvas
	_headerCanvas = new Canvas1(128,19);
    _bodyCanvas = new Canvas1(128,45);


	_latestTouchTick = ClockTick::current();
}

Display::~Display()
{
	if (_isRunning) {
		requestStop();
		join();
	}

	for (std::vector<AbstractPage*>::iterator it = _backupPageList.begin(); it != _backupPageList.end(); ++it) {
		delete *it;
	}
	delete _messagePage;
	_messagePage = NULL;

	delete _controller;
	_controller = NULL;

	delete _oled;
	_oled = NULL;

	if (_pipeFD >= 0) {
		::close(_pipeFD);
	}

	if (unlink(PIPE.c_str()) < 0) {
		printf("Error : Can not remove FIFO.\n");
	}

    // minho, Canvas
	delete _headerCanvas;
    _headerCanvas = NULL;

    delete _bodyCanvas; 
    _bodyCanvas = NULL; 
}

static int longkey_mode = 0;
void Display::onControllerEventHandler(const int32_t id, const int32_t rotaryValue, const int32_t continuousRotaryValue, const KeyButton::EventType pushEventType, void* param)
{
	Display* display = (Display*)param;

	if(!display->isVolumeControlDevice()) {
		// 페이지 이동

		if (rotaryValue > 0 && display->_oled->isOn() == true) {
			display->showNext();
		} 
		else if (rotaryValue < 0 && display->_oled->isOn() == true) {
			display->showPrev();
		}
	}
	else {
		// Volume Control Device {
		// 페이지 이동 또는 Volume 변경 (for PN Series)

		if(display->getVolumeControlModeOff()) {
			// 페이지 이동

			if (rotaryValue > 0 && display->_oled->isOn() == true) {
				display->showNextForSkipFirst();
			} 
			else if (rotaryValue < 0 && display->_oled->isOn() == true) {
				display->showPrevForSkipFirst();
			}
		}
		else {
			// 페이지 이동 없이 Volume 변경

			if (rotaryValue > 0 && display->_oled->isOn() == true) {
				uint32_t currentVolume = display->getVolume();
				if(currentVolume < 100) {
					currentVolume += 5;
					if(currentVolume >= 100)
						currentVolume = 100;
					display->setVolume(currentVolume);
					dynamic_cast<LogoPage*>(display->_pageList.at(0))->setVolume(currentVolume);
					
					display->_connector->setDspMasterVolume(currentVolume);
				}
			}
			else if (rotaryValue < 0 && display->_oled->isOn() == true) {
				int32_t currentVolume = display->getVolume();
				if(currentVolume > 0) {
					currentVolume -= 5;
					if(currentVolume <= 0)
						currentVolume = 0;
					display->setVolume((uint32_t)currentVolume);
					dynamic_cast<LogoPage*>(display->_pageList.at(0))->setVolume((uint32_t)currentVolume);
					
					display->_connector->setDspMasterVolume(currentVolume);
				}
			}
			
			display->showFirst();
		}
		
		if (pushEventType == KeyButton::PRESS) {
			display->changeVolumeControlMode();
		}
		// Volume Control Device }
	}

    if(longkey_mode != 1)
    {
        if (pushEventType == KeyButton::LONG_PRESS) {
            display->_displayMutex.lock();
            display->_pageList = display->_backupPageList;
            display->_currentPage = 0;
            display->showPage(display->_currentPage);
            display->_displayMutex.unlock();
        }
	}

	display->_displayMutex.lock();
	display->_latestTouchTick = ClockTick::current();
	display->_oled->on();
	display->_displayMutex.unlock();
}


int32_t Display::run() {
	_isRunning = true;

	// Volume Control Device {
	_isVolumeControlDevice = dynamic_cast<LogoPage*>(this->_pageList.at(0))->isVolumeControlDevice();
	if (_isVolumeControlDevice && _connector) {
		_connector->start();
	}
	// Volume Control Device }

	while (!_isRequestedStop) {

		// 화면 업데이트
		if (ClockTick::differenceFromCurrent(_latestUpdateTick) >= DISPLAY_UPDATE_PERIOD) {
			show();
		}


		// Screen Saver
		if (_screenSaverGracePeriod > 0) {
			_displayMutex.lock();
			if (ClockTick::differenceFromCurrent(_latestTouchTick) >= _screenSaverGracePeriod) {
				_oled->off();
			}
			_displayMutex.unlock();
		}


		// MessagePage
		if (_pipeFD < 0) {
			Thread::sleep(100);
			continue;
		}
		struct pollfd pollFDs[1];
		pollFDs[0].fd = _pipeFD;
		pollFDs[0].events = POLLIN | POLLPRI;
		pollFDs[0].revents = 0;
		int32_t ret = poll(pollFDs, 1, POLLING_TIMEOUT);
		if (ret > 0) {
			if (pollFDs[0].revents & (POLLIN | POLLPRI)) {
				char buf[6] = {0, };
				int32_t length = ::read(_pipeFD, buf, 6);
				if (length < 0){
					printf("fail to call read()\n");
					continue;
				}
				const uint8_t magicNumber[4] = {0x00, 0x00, 0x44, 0x50};
				if (memcmp(buf, magicNumber, 4) == 0) {
					// Volume Control Device {
					FrontDisplayControlHeader header;
					memcpy(&header, buf, sizeof(FrontDisplayControlHeader));

#if 0
					printf("Type : %d\t", header.type);
					printf("Key : %d\t", header.key);
					printf("Value : %d\n", header.value);
#endif

					_displayMutex.lock();
					setVolume(header.value);
					dynamic_cast<LogoPage*>(_pageList.at(0))->setVolume(header.value);
					showPage(_currentPage);
					_latestTouchTick = ClockTick::current();
					_displayMutex.unlock();

					// Volume Control Device }
				}
                else if (buf[0] == 0xEA) { // STX:0xEA / Start EMG:0xEE TEST:0xFF / STOP EMG:0xE0 TEST:0xF0
                    std::string message;
                    //printf("buf : [0x%x][0x%x][0x%x][0x%x][0x%x][0x%x]\n", buf[0], buf[1], buf[2], buf[3], buf[4], buf[5]);
                    //if(buf[1] == 0xEE)  // Emergency Display On
                    if((buf[1] == 0xE1) || (buf[1] == 0xE2) || (buf[1] == 0xE4))  // Emergency Display On
                    { 
                        longkey_mode = 1;
                        _displayMutex.lock();
                        //_messagePage->setMessage(buf);
                        _pageList.clear();
                        _oled->clear();
                        //_pageList.push_back(_messagePage);

                        _headerCanvas->fillScreen(0);
                        _headerCanvas->setTextColor(1, 0);
                        _headerCanvas->setTextSize(1);

                        message = " Emergency ";
                        _headerCanvas->drawStr((128 - _headerCanvas->getTextWidth(message.c_str())) / 2, 4, message.c_str());

                        // 가로 : Center, 세로 : Top
                        _oled->drawBitmap((128 - 128) / 2, 0, _headerCanvas->getBuffer(), 128, 19);
                        // Header }

                        _bodyCanvas->fillScreen(0);
                        _bodyCanvas->setTextColor(1, 0);
                        _bodyCanvas->setTextSize(1);

                        if(buf[1] == 0xE1)
                        {
                            if(buf[2] == 0xff) message = "1CH : EMG";
                            else message = "1CH : ";
                            _bodyCanvas->drawStr((128 - _bodyCanvas->getTextWidth(message.c_str())) / 2, 16, message.c_str());
                        }
                        else if(buf[1] == 0xE2)
                        {
                            if(buf[2] == 0xff) message = "1CH : EMG";
                            else message = "1CH : ";
                            _bodyCanvas->drawStr((128 - _bodyCanvas->getTextWidth(message.c_str())) / 2, 16, message.c_str());

                            if(buf[3] == 0xff) message = "2CH : EMG";
                            else message = "2CH : ";                        
                            _bodyCanvas->drawStr((128 - _bodyCanvas->getTextWidth(message.c_str())) / 2, 26, message.c_str());
                        }
                        else
                        {
                            if(buf[2] == 0xff) message = "1CH : EMG";
                            else message = "1CH : ";
                            _bodyCanvas->drawStr((128 - _bodyCanvas->getTextWidth(message.c_str())) / 2, 6, message.c_str());

                            if(buf[3] == 0xff) message = "2CH : EMG";
                            else message = "2CH : ";
                            _bodyCanvas->drawStr((128 - _bodyCanvas->getTextWidth(message.c_str())) / 2, 16, message.c_str());

                            if(buf[4] == 0xff) message = "3CH : EMG";
                            else message = "3CH : ";                        
                            _bodyCanvas->drawStr((128 - _bodyCanvas->getTextWidth(message.c_str())) / 2, 26, message.c_str());

                            if(buf[5] == 0xff) message = "4CH : EMG";
                            else message = "4CH : ";
                            _bodyCanvas->drawStr((128 - _bodyCanvas->getTextWidth(message.c_str())) / 2, 36, message.c_str());
                        }
                        // 가로 : Center, 세로 : Bottom
                        _oled->drawBitmap((128 - 128) / 2, 64 - 45, _bodyCanvas->getBuffer(), 128, 45);
                        // Body }

                        _oled->update();

                        _currentPage = 0;
                        showPage(_currentPage);
                        _screenSaverGracePeriod = 0;
                        _oled->on(true);

					    _displayMutex.unlock();
                    }
                    else if(buf[1] == 0xE0) // Emergency Display Off
                    {
                        longkey_mode = 0;
                        _displayMutex.lock();
                        _pageList = _backupPageList;
                        _currentPage = 0;
                        showPage(_currentPage);
                        _screenSaverGracePeriod = 60000;
                        _displayMutex.unlock();
                    }
                    else if(buf[1] == 0xFF) // Test Mode Dispaly On 
                    {
                        longkey_mode = 1;
                        _displayMutex.lock();
                        //_messagePage->setMessage(buf);
                        _pageList.clear();
                        //_pageList.push_back(_messagePage);
                        
                        _headerCanvas->fillScreen(1);
                        _oled->drawBitmap((128 - 128) / 2, 0, _headerCanvas->getBuffer(), 128, 19);
                        _bodyCanvas->fillScreen(1);
		                _oled->drawBitmap((128 - 128) / 2, 64 - 45, _bodyCanvas->getBuffer(), 128, 45);
                        _oled->update();
                        
                        _currentPage = 0;
                        showPage(_currentPage);
                        _screenSaverGracePeriod = 0;
                        _oled->on(true);
                        _displayMutex.unlock();
                    }
                    else if(buf[1] == 0xF0) // Test Mode Display Off
                    {
                        longkey_mode = 0;
                        _displayMutex.lock();
                        _pageList = _backupPageList;
                        _currentPage = 0;
                        showPage(_currentPage);
                        _screenSaverGracePeriod = 60000;
                        _displayMutex.unlock();
                    }
                }
				else {
					// Lagacy (Simple Message)
					//buf[20] = 0x0;	// 화면 출력 가능한 문자수 제한 (20자)
					printf("Received Message : [%s]\n", buf);

					_displayMutex.lock();
					_messagePage->setMessage(buf);

					_pageList.clear();
					_pageList.push_back(_messagePage);
					_currentPage = 0;
					showPage(_currentPage);
					_screenSaverGracePeriod = 0;
					_oled->on(true);

					_displayMutex.unlock();
				}
			}
		}


//		Thread::sleep(100);	// poll 을 통해 Waiting and Time Out 처리 되므로, 부하를 줄이기 위한 별도의 Sleep 불필요
	}

	_isRunning = false;
	return 0;
}


