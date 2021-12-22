#include <stdint.h>
#include <cstdio>
#include <unistd.h>
#include <atomic>
#include <signal.h>

#include <avmsapi/DeviceFactory.h>
#include <avmsapi/utility/SystemUtil.h>

//#define USE_GFX

#include "Display.h"
#include "LogoPage.h"
#include "NetworkInfoPage.h"
#include "NetworkUsagePage.h"
#include "CPUUsagePage.h"
#include "RecoveryPage.h"


//#define USE_BONDING



static const std::string BONDING = "Bond.";
static const std::string IPADDR = "IP.";
static const std::string INFO = "Info.";
static const std::string USAGE = "Usage.";
static const std::string PRIORITY[] = {
	"Pri.",		// "primary"
	"Sec.",		// "secondary"
	"Tert.",	// "tertiary"
	"Quan.",	// "quaternary"
};

std::atomic<bool> quit(false);    // signal flag
void got_signal(int sig)
{
	printf("SIGNAL : %d\n", sig);
	quit.store(true);
}

void showLibInfo()
{
	printf("\n[libavmsapi] ========================================\n");

	printf("Version : %s\n", AVMSAPI::version().c_str());
	printf("Build Date : %s\n", AVMSAPI::buildDate().c_str());	
	printf("Build Time : %s\n", AVMSAPI::buildTime().c_str());
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

void initPage(const std::string path, const std::string filename, const int32_t screenSaverGracePeriod)
{
//	printf("\n[Front Display] Start ========================================\n");

	signal(SIGINT, &got_signal);
	signal(SIGTERM, &got_signal);

	try {
		std::vector<std::string> networkInterfaces = DeviceFactory::getNetworkInterfaces();

		Display display(screenSaverGracePeriod);

		// Logo
		if (networkInterfaces.size() == 1) {
			const std::string networkInterface = *networkInterfaces.begin();
			display.add(new LogoPage(path, filename, IPADDR, networkInterface));
		}
		else if (networkInterfaces.size() >= 2) {
			const std::string networkInterface1 = *networkInterfaces.begin();
			const std::string networkInterface2 = *(networkInterfaces.begin() + 1);
			display.add(new LogoPage(path, filename, PRIORITY[0], networkInterface1,  PRIORITY[1], networkInterface2, BONDING, "bond0"));
		}

		// Network
		if (networkInterfaces.size() == 1) {
			const std::string networkInterface = *networkInterfaces.begin();
			display.add(new NetworkInfoPage(networkInterface, INFO));	// IP Info
			display.add(new NetworkUsagePage(networkInterface, USAGE));	// Usage
		}
		else if (networkInterfaces.size() >= 2) {
			int32_t i = 0;
			for(std::vector<std::string>::iterator it = networkInterfaces.begin(); it != networkInterfaces.end(); ++it, i++)
			{
				const std::string networkInterface = *it;
				display.add(new NetworkInfoPage(networkInterface, PRIORITY[i]));	// IP Info
				display.add(new NetworkUsagePage(networkInterface, PRIORITY[i]));	// Usage
			}
		}

		// CPU Usage
		//display.add(new CPUUsagePage());

		display.add(new RecoveryLogoPage(path, "recovery"));

		if (!quit.load()) {
			display.start();
		}

		while (!quit.load())
		{
			Thread::sleep(1000);	// 1 sec
		}
	}
	catch (const char* msg) {
		printf("Exception : %s	\n", msg);
	}

//	printf("\n[Front Display] Finish ========================================\n");
}


int main(int argc, char* argv[])
{
	showLibInfo();

	string filename = argv[0];
	const int32_t idx = filename.find_last_of("/");
	string path = filename .substr(0, idx);
	filename.erase(0, idx+1);

	showAppInfo(filename);

	
	if (getuid() != 0) {
		printf("Please use root user.\n");
		exit(1);
	}

	char pidFile[256] = {};
	sprintf(pidFile, "/var/run/%s.pid", "frontDisplay");
	if (SystemUtil::isDuplicatedRun(pidFile)) {
		printf("This process seems to have been duplicated run or something wrong.\nPlease check and run again.\n");
		exit(1);
	}

	uint32_t screenSaverGracePeriod = 0;
	if (argc >= 2) {
		sscanf(argv[1], "%d", &screenSaverGracePeriod);
		screenSaverGracePeriod *= 1000;
	}
	printf("[Screen Saver]\n");
	printf("Grace Period = %u sec\n", screenSaverGracePeriod / 1000);
	printf("\n");

	initPage(path, filename, screenSaverGracePeriod);

	exit(0);
}
