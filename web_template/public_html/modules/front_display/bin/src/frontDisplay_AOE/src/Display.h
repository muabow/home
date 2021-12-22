/**
	@file	
	@brief	Display
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.04.18
 */

#ifndef __DISPLAY_H__
#define __DISPLAY_H__


#include <stdint.h>
#include <vector>

#include <avmsapi/KeyButton.h>
#include <avmsapi/utility/ClockTick.h>
#include <avmsapi/utility/Mutex.h>
#include <avmsapi/utility/Thread.h>

#include "AbstractPage.h"

class SPI;
class OLED;
class RotaryEncoder;
class MessagePage;


class Display : public Thread {
public:
	Display(const uint32_t screenSaverGracePeriod = 0) throw (const char*);
	virtual ~Display() throw();

	void add(AbstractPage* page)
	{
		_displayMutex.lock();
//		printf("Push\n");
		_pageList.push_back(page);
		_backupPageList.push_back(page);
		_displayMutex.unlock();
	}

	void show()
	{
		_displayMutex.lock();
		showPage(_currentPage);
		_displayMutex.unlock();
	}

 	void requestStop() {
		_isRequestedStop = true;
	}

private:
	/**
		@brief	Controller(KeyButton) 에서 Event 발생시 호출 될 Handler
		@see	init()
		@param	[in] id UID
		@param	[in] value	GPIO 상태 값
		@param	[in,out] param	KeyButton 의 this
	 */
	static void onControllerEventHandler(const int32_t id, const int32_t pushEventType, void* param);
	void showPage(const int32_t pageNumber) {
		_latestUpdateTick = ClockTick::current();
		
//		printf("ShowPage (%d)\n", pageNumber);
		const int32_t size = _pageList.size();
		if (pageNumber < 0 || pageNumber >= size) {
			return;
		}
		AbstractPage* page = _pageList.at(pageNumber);
		page->draw(_oled);
	}

	void showFirst()
	{
		_displayMutex.lock();
		_currentPage = 0;
		showPage(_currentPage);
		_displayMutex.unlock();
	}

	void showNext()
	{
		_displayMutex.lock();
		const int32_t size = _pageList.size();
		_currentPage++;
		if (_currentPage >= size) {
			_currentPage -= size;
		}

		showPage(_currentPage);
		_displayMutex.unlock();
	}

	void showPrev()
	{
		_displayMutex.lock();
		const int32_t size = _pageList.size();
		_currentPage--;
		if (_currentPage < 0) {
			_currentPage = size - 1;
		}

		showPage(_currentPage);
		_displayMutex.unlock();
	}


	int32_t run();

private:
	static const uint32_t DISPLAY_UPDATE_PERIOD = 1000;		///< 화면 업데이트 주기 : 1 sec
	static const uint32_t POLLING_TIMEOUT = 10;				///< Message 수신을 위한 대기 시간 : ms
	static const std::string PIPE;							///< Message 수신을 위한 pipe 명
	OLED* _oled;
	KeyButton* _controller;

	std::vector<AbstractPage*> _pageList;
	std::vector<AbstractPage*> _backupPageList;
	int32_t _currentPage;

	Mutex _displayMutex;				///< 화면 출력 장치(OLED) 에 대한 Mutex

	bool _isRunning;
	bool _isRequestedStop;

	MessagePage* _messagePage;
	bool _isMessagePage;
	int32_t _pipeFD;


	uint32_t _latestUpdateTick;			///< 주기적인 화면 갱신을 위해 마지막 업데이트 시간 저장

	// Screen Saver
	uint32_t _screenSaverGracePeriod;	///< 화면 보호기 유예 시간
	uint32_t _latestTouchTick;			///< 일정시간 이후 화면 Off 를 위해 마지막 사용 시간 저장
};

#endif	// __DISPLAY_H__
