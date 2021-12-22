#include <stdint.h>
#include <cstdio>
#include <unistd.h>
#include <atomic>
#include <getopt.h>
#include <signal.h>

#include <avmsapi/DeviceFactory.h>
#include <avmsapi/utility/BitMask32.h>
#include <avmsapi/utility/ClockTick.h>
#include <avmsapi/utility/Thread.h>
#include <avmsapi/utility/SystemUtil.h>

#include <avmsapi/rapidjson/document.h>
#include <avmsapi/rapidjson/pointer.h>

#include <NotificationLibInfo.h>
#include <NotificationListener.h>

#include "DevMem.h"
#include "BlinkLED.h"
#include "utility/FormatableBuffer.h"



#define FPGA_BASE_ADDRESS	0x43C10000
#define FACTORY_RESET		FPGA_BASE_ADDRESS + 0x0008	// FPGA Address [0]
#define LIVE_LED			FPGA_BASE_ADDRESS + 0x0048	// FPGA Address [0]


#define BUTTON_PRESSING_TIME_FACTORY_TEST		5 * 1000
#define BUTTON_PRESSING_TIME_FACTORY_DEFAULT	10 * 1000

#define LED_PERIOD_NORMAL			1000
#define LED_PERIOD_FACTORY_TEST		300
#define LED_PERIOD_FACTORY_DEFAULT	50



#if 0
/opt/interm/bin/factory_test.sh
	- 5 초, 0.5s/0.5s
/opt/interm/bin/factory_default.sh
	- 10초 이상, 0.2s/0.2s

Live 1초 On / 1초 Off
#endif



using namespace rapidjson;



// global variables
bool _isDebugPrint = false;

bool _prevButtonPressed = false;
uint32_t _prevButtonPressedTick = 0;
bool _isDefaultOn = false;
bool _isTestOn = false;

BlinkLED* _liveLED;



std::atomic<bool> quit(false);    // signal flag
void got_signal(int sig)
{
	printf("SIGNAL : %d\n", sig);
	quit.store(true);
}



void showLibInfo()
{
	printf("\n");

	printf("[libavmsapi] ========================================\n");
	printf("Version : %s\n", AVMSAPI::version().c_str());
	printf("Build Date : %s\n", AVMSAPI::buildDate().c_str());	
	printf("Build Time : %s\n", AVMSAPI::buildTime().c_str());
	printf("\n");

	printf("[libNotification] ========================================\n");
	printf("Version : %s\n", NotificationLibInfo::version().c_str());
	printf("Build Date : %s\n", NotificationLibInfo::buildDate().c_str());	
	printf("Build Time : %s\n", NotificationLibInfo::buildTime().c_str());
	printf("\n");
}



void showAppInfo(const std::string name)
{
	printf("[%s] ========================================\n", name.c_str());
	printf("Version : %s\n", VERSION);
	printf("Build Date : %s\n", BUILD_DATE);
	printf("Build Time : %s\n", BUILD_TIME);
	printf("\n");
}



/**
	@brief	Noti. Message 에서 LED Pattern 을 추출하여 적용
 */
static void onNotificationListenEventHandler(const std::string notiJSONString, void* param)
{
	int32_t pattern[BlinkLED::MAX_LED_PATTERN_LENGTH] = {0, };

#if 0	// Notification JSON String
	{
		"description" : [
			"Listener 에서 각자 필요한 것 사용",
			"message_id : 메세지ID, 다국어지원 등을 위해 정의",
			"message : 메세지",
			"led_pattern : [On, Off, On, Off, On, Off, ...,  0(종료) / -1(반복) / -2(복귀)]"
		],
		"message_id" : "NOTI_ERROR_AVMU_NETWORK",
		"message" : "AVMU 네트워크 에러",
		"led_pattern": [
			150,
			150,
			1000,
			150,
			 -1
		]
	}
#endif


	Document jsonDocument;

	try {
		// JSON Format 검토
		ParseResult parseResult = jsonDocument.Parse(notiJSONString.c_str());
		if (parseResult.IsError() || (jsonDocument.IsObject() == false) ) {
			throw FormatableBuffer<>("Incorrect JSON Data	[%d]", __LINE__).c_str();
		}

		// Key : led_pattern
		const char* const ledPatternKey = "led_pattern";
		if (jsonDocument.HasMember(ledPatternKey) == false) {
			throw FormatableBuffer<>("Incorrect JSON Data	[%d]", __LINE__).c_str();
		}
		const Value& ledPatternJsonObject = jsonDocument[ledPatternKey];
		if (ledPatternJsonObject.IsArray() == false) {
			throw FormatableBuffer<>("Incorrect JSON Data	[%d]", __LINE__).c_str();
		}

		// Pattern 정보를 배열로 변환
		for (SizeType i = 0; i < ledPatternJsonObject.Size(); i++) {
			const int32_t durationTime = Pointer(FormatableBuffer<>("/%s/%d", ledPatternKey, i).c_str()).Get(jsonDocument)->GetInt();
			pattern[i] = durationTime;
		}
	}
	catch (const char* msg) {
		printf("Exception : %s	\n", msg);
		return;
	}
	catch (...) {
		printf("Exception : %s	\n", "unknown");
		return;
	}

	if (pattern[0] == -1) {
		// Default
		_liveLED->setPeriod(1000);
	}
	else {
		_liveLED->setPattern(pattern);
	}
}



void factoryReset()
{
//	printf("\n[Factory Reset] Start ========================================\n");

	signal(SIGINT, &got_signal);
	signal(SIGTERM, &got_signal);

	try {
		_liveLED = new BlinkLED(false);	// LED 제어 안함

		NotificationListener notificationListener(_isDebugPrint);
		notificationListener.setEventHandler(onNotificationListenEventHandler);

		DevMem devMem(FACTORY_RESET);

		while (!quit.load())
		{
			const bool currButtonPressed = !BitMask32(devMem.get()).isSet(0);

			if (currButtonPressed && !_prevButtonPressed) {
//				printf("Factory Reset Button : Pressed\n");

				// Press
				_prevButtonPressed = true;
				_prevButtonPressedTick = ClockTick::current();
			}
			else if (currButtonPressed && _prevButtonPressed) {
				// Keep Press
#if 0
				if (ClockTick::differenceFromCurrent(_prevButtonPressedTick) > BUTTON_PRESSING_TIME_FACTORY_TEST) {
//					printf("%lu	: %d ms\n", ClockTick::current(), BUTTON_PRESSING_TIME_FACTORY_TEST);
					_liveLED->setPeriod(LED_PERIOD_FACTORY_TEST);
					_isTestOn = true;
				}
#endif
				if (ClockTick::differenceFromCurrent(_prevButtonPressedTick) > BUTTON_PRESSING_TIME_FACTORY_DEFAULT) {
//					printf("%lu	: %d ms\n", ClockTick::current(), BUTTON_PRESSING_TIME_FACTORY_DEFAULT);
					_liveLED->setPeriod(LED_PERIOD_FACTORY_DEFAULT);
					_isDefaultOn = true;
				}
			}
			else if (!currButtonPressed && _prevButtonPressed) {
//				printf("Factory Reset Button : Released\n");

				// Release
				if (_isDefaultOn) {
					printf("Run Default\n");
					SystemUtil::runCommand("/opt/interm/bin/factory_default.sh");
					SystemUtil::runCommand("[ -f /opt/interm/bin/reboot.sh ] && /opt/interm/bin/reboot.sh || reboot");
//					return;
				}
				else if (_isTestOn) {
					printf("Run Test\n");
					SystemUtil::runCommand("/opt/interm/bin/factory_test.sh");
//					return;
				}

				_liveLED->setPeriod(LED_PERIOD_NORMAL);
				_prevButtonPressed = false;
				_isDefaultOn = false;
				_isTestOn = false;
			}
			else {
				// Keep Release
			}

			Thread::sleep(100);
		}


		delete _liveLED;
	}
	catch (const char* msg) {
		printf("Exception : %s	\n", msg);
	}

//	printf("\n[Factory Reset] Finish ========================================\n");
}



int main(int argc, char* argv[])
{
	showLibInfo();

	string filename = basename(argv[0]);
	showAppInfo(filename);

	
	if (getuid() != 0) {
		printf("Please use root user.\n");
		return 1;
	}

	char pidFile[256] = {};
	sprintf(pidFile, "/var/run/%s.pid", filename.c_str());
	if (SystemUtil::isDuplicatedRun(pidFile)) {
		printf("This process seems to have been duplicated run or something wrong.\nPlease check and run again.\n");
		return 1;
	}

	int32_t opt = 0;
	while( (opt = getopt(argc, argv, "vh")) != -1 ) {
		switch( opt ) {
			case 'v' : {
				printf("main::main() set print debug\n");
				_isDebugPrint = true;

				break;
			}
			case 'h' :
			default : {
				printf("usage: %s [option]\n", filename.c_str());
				printf("  -v : print normal debug message \n");
				printf("\n");

				return 1;
				break;
			}
		}
	}




	// 메인 인스턴스 생성 및 구동
	factoryReset();



	return 0;
}
