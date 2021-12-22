#ifndef __CPU_USAGE_PAGE_H__
#define __CPU_USAGE_PAGE_H__

#include <queue>

#include <avmsapi/GFX.h>
#include <avmsapi/OLED.h>
#include <avmsapi/utility/Mutex.h>
#include <avmsapi/utility/SystemUtil.h>
#include <avmsapi/utility/Thread.h>

#include "AbstractPage.h"



class CPUUsagePage : public AbstractPage {
public:
	CPUUsagePage() throw (const char*)
		: _headerCanvas(HEADER_WIDTH, HEADER_HEIGHT)
		, _bodyCanvas(BODY_WIDTH, BODY_HEIGHT)
		, _graph(BODY_WIDTH, BODY_HEIGHT)		// Body 전체 영역 사용
		, _runSystemCheckThreadID(0)
		, _runSystemCheckThreadState(0)
	{
		// Thread 생성/실행 - runSystemCheckThread
		if (_runSystemCheckThreadState == 0) {
			_runSystemCheckThreadState = 1;	
			if (::pthread_create(&_runSystemCheckThreadID, NULL, runSystemCheckThread, this) != 0) {
				throw "Unable to create thread (CPUUsagePage)";
			}
		}
	};
	virtual ~CPUUsagePage() throw (const char*) {
		// Thread 종료 - runSystemCheckThread
		_runSystemCheckThreadState = 0;	
		::pthread_join(_runSystemCheckThreadID, NULL);
	};

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

		message = "CPU Usage";
		_headerCanvas.drawStr((HEADER_WIDTH - _headerCanvas.getTextWidth(message.c_str())) / 2, 4, message.c_str());


		// 가로 : Center, 세로 : Top
		oled->drawBitmap((WIDTH - HEADER_WIDTH) / 2, 0, _headerCanvas.getBuffer(), HEADER_WIDTH, HEADER_HEIGHT);
		// Header }


		// Body {
		_mutex.lock();

		// Graph
		// 가로 : Center, 세로 : Bottom
		oled->drawBitmap((WIDTH - BODY_WIDTH) / 2, HEIGHT - BODY_HEIGHT, _bodyCanvas.getBuffer(), BODY_WIDTH, BODY_HEIGHT);

		_mutex.unlock();
		// Body }

		oled->update();
	}


	static void* runSystemCheckThread(void* param) {
		CPUUsagePage* page = (CPUUsagePage*)param;

		while (page->_runSystemCheckThreadState) {
			if (page->_mutex.tryLock() != EBUSY) {
				if (page->_runSystemCheckThreadState == 0) {
					page->_mutex.unlock();
					break;
				}

				const int32_t width = page->BODY_WIDTH;
				const int32_t height = page->BODY_HEIGHT;

				const float cpuUsage = SystemUtil::getCPUUsage();

				page->_graph.add(cpuUsage);

				page->_bodyCanvas.fillScreen(0);

//				const int16_t bottom = height - 1;
				const int16_t right = width - 1;

				// Draw Graph
				int16_t y = 0;
//				const int16_t offset = height - height;
				page->_bodyCanvas.drawBitmap(right - width + 1, y, page->_graph.getBuffer(), width, height, 1);


				page->_mutex.unlock();
			}
			Thread::sleep(1 * 1000);	// 1 Second
		}

		pthread_exit(NULL);
	}

private:
	static const uint32_t HEADER_WIDTH = 128; 		///< Header Width
	static const uint32_t HEADER_HEIGHT = 19; 		///< Header Height

	static const uint32_t BODY_WIDTH = 128;			///< Body Width
	static const uint32_t BODY_HEIGHT = 45;			///< Body Height


	Canvas1 _headerCanvas;		///< Header Canvas
	Canvas1 _bodyCanvas;		///< Body Canvas

	Graph1 _graph;			///< CPU Graph

	pthread_t _runSystemCheckThreadID;
	int32_t _runSystemCheckThreadState;

	Mutex _mutex;

};

#endif	// __CPU_USAGE_PAGE_H__

