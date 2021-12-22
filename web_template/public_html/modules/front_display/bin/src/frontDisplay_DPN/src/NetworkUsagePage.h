#ifndef __NETWORK_USAGE_PAGE_H__
#define __NETWORK_USAGE_PAGE_H__

#include <queue>

#include <avmsapi/GFX.h>
#include <avmsapi/OLED.h>
#include <avmsapi/utility/Mutex.h>
#include <avmsapi/utility/SystemUtil.h>
#include <avmsapi/utility/Thread.h>

#include "AbstractPage.h"



class NetworkUsagePage : public AbstractPage {
public:
	NetworkUsagePage(const string interface, const string title = "") throw (const char*)
		: _headerCanvas(HEADER_WIDTH, HEADER_HEIGHT)
		, _bodyCanvas(BODY_WIDTH, BODY_HEIGHT)
		, _interface(interface)
		, _title(title)
		, _graphRX(BODY_WIDTH - TEXT_WIDTH, BODY_HEIGHT / 2)		// 문자표시 영역 45 : 83
		, _graphTX(BODY_WIDTH - TEXT_WIDTH, BODY_HEIGHT / 2)		// RX/TX 2개로 분리 : 22
		, _runSystemCheckThreadID(0)
		, _runSystemCheckThreadState(0)
	{
		// Thread 생성/실행 - runSystemCheckThread
		if (_runSystemCheckThreadState == 0) {
			_runSystemCheckThreadState = 1;	
			if (::pthread_create(&_runSystemCheckThreadID, NULL, runSystemCheckThread, this) != 0) {
				throw "Unable to create thread (NetworkUsagePage)";
			}
		}
	};

	virtual ~NetworkUsagePage() throw (const char*) {
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

		message = "Network ";
		if (_title.size() > 0) {
			message.append(_title);
		}
		else {
			message.append(_interface);
		}
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
		NetworkUsagePage* page = (NetworkUsagePage*)param;

		while (page->_runSystemCheckThreadState) {
			if (page->_mutex.tryLock() != EBUSY) {
				if (page->_runSystemCheckThreadState == 0) {
					page->_mutex.unlock();
					break;
				}

				const int32_t width = page->_graphRX.width();
				const int32_t height = page->_graphRX.height();

				float rx = 0.0;
				float tx = 0.0;
				int32_t link = 100;
				if (SystemUtil::getNetworkUsage(page->_interface.c_str(), rx, tx, link) == false) {
					printf("ERROR : Network Usage\n");
					rx = 0.0;
					tx = 0.0;
					link = 100;
				}

				const float rxBitRate = SystemUtil::map(rx, 0.0, link * 1000.0 / 8.0, 0.0, 100.0);			// 1000 단위 사용 (1024 아님)
				const float txBitRate = SystemUtil::map(tx, 0.0, link * 1000.0 / 8.0, 0.0, 100.0);			// 1000 단위 사용 (1024 아님)


				page->_graphRX.add(rxBitRate);
				page->_graphTX.add(txBitRate);

#if 0
				printf("Network[%s]: link : %d Mbps		", page->_interface, link);
				printf("%f KB/s	%7.1f %%		", rx, rxBitRate);
				printf("%f KB/s	%7.1f %%		\n", tx, txBitRate);
#endif

				page->_bodyCanvas.fillScreen(0);

//				const int16_t bottom = height - 1;
				const int16_t right = page->BODY_WIDTH - 1;

				// Draw Graph
				int16_t y = 0;
				const int16_t offset = page->BODY_HEIGHT - height;
				page->_bodyCanvas.drawBitmap(right - width + 1, y, page->_graphRX.getBuffer(), width, height, 1);

				y += offset;
				page->_bodyCanvas.drawBitmap(right - width + 1, y, page->_graphTX.getBuffer(), width, height, 1);



				// Draw Text
				const char* msgFormat = "%s %s\n%7.2f";
				char buf[16] = {};

				// RX
				page->_bodyCanvas.setCursor(0, 2);
				page->_bodyCanvas.setTextColor(1, 0);
				page->_bodyCanvas.setTextSize(1);
				if (rx >= 10000) {
					sprintf(buf, msgFormat, "RX", "MB/s", rx / 1000.0);
				}
				else {
					sprintf(buf, msgFormat, "RX", "KB/s", rx);
				}
				page->_bodyCanvas.print(buf);

				// TX
				page->_bodyCanvas.setCursor(0, 2 + offset);
				page->_bodyCanvas.setTextColor(1, 0);
				page->_bodyCanvas.setTextSize(1);
				if (tx >= 10000) {
					sprintf(buf, msgFormat, "TX", "MB/s", tx / 1000.0);
				}
				else {
					sprintf(buf, msgFormat, "TX", "KB/s", tx);
				}
				page->_bodyCanvas.print(buf);


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

	static const uint32_t TEXT_WIDTH = 45;			///< Text Width

	Canvas1 _headerCanvas;		///< Header Canvas
	Canvas1 _bodyCanvas;		///< Body Canvas

	const string _interface;
	const string _title;


	Graph1 _graphRX;		///< RX Graph
	Graph1 _graphTX;		///< TX Graph

	pthread_t _runSystemCheckThreadID;
	int32_t _runSystemCheckThreadState;

	Mutex _mutex;

};

#endif	// __NETWORK_USAGE_PAGE_H__

