/**
	@file
 */

#include <cstdio>
#include <cstdlib>
#include <cstdarg>
#include <unistd.h>
#include <atomic>
#include <getopt.h>


#include <avmsapi/AVMSAPI.h>
#include <avmsapi/utility/ClockTick.h>
#include <avmsapi/utility/SystemUtil.h>
#include <avmsapi/utility/Thread.h>

#include <avmsapi/rapidjson/document.h>
#include <avmsapi/rapidjson/istreamwrapper.h>
#include <avmsapi/rapidjson/pointer.h>

#include <api_signal.h>


#include "utility/ANSIColor.h"
#include "utility/CommonUtil.h"
#include "utility/FormatableBuffer.h"




using namespace rapidjson;



// global variables
bool g_isDebugPrint = false;

SignalHandler g_signalHandler;

std::string g_destinationFIFOPath;


const std::string FILE_NAME_DESTINATION 		= "notiDestination.json";
const std::string FILE_NAME_MESSAGE_DEFAULT 	= "notiMessage_Default.json";
const std::string FILE_NAME_MESSAGE_ERROR		= "notiMessage_Error.json";




#if 0
// function: common
void printDebugInfo(const char *format, ...) {
	if(g_isDebugPrint == false) return ;

	fprintf(stdout, "%u	%s::", ClockTick::current(), "main");
	va_list arg;
	va_start(arg, format);
	vprintf(format, arg);
	va_end(arg);

	return ;
}
#endif
/**
	@brief	디버그 메세지 출력
	@remark
		Class 명을 Prefix 로 하여 요청한 메세지를 출력
	@param  [in] format 출력 형식
 */
void printDebugInfo(const char* format, ...)
{
	if (g_isDebugPrint == false) {
		return;
	}

	std::string name = "avmu_observer";

	// Class Name 앞에 붙는 Pxx 제거
	name.erase(0, 1);
	while ( !(name.at(0) >= 'A' && name.at(0) <= 'Z') && !(name.at(0) >= 'a' && name.at(0) <= 'z') ) {
		name.erase(0, 1);
	}

	fprintf(stdout, "%u	%s::", ClockTick::current(), name.c_str());
	va_list arg;
	va_start(arg, format);
	vprintf(format, arg);
	va_end(arg);

	return ;
}



// function: event handler 
void signalEventHandler(int sig_num) {
	printDebugInfo("signal_event_handler() event : [%d] %s\n", sig_num, strsignal(sig_num));
	printf("\n\n%sTerminating...%s\n", COLOR_INFO, COLOR_RESET);

	return ;
}



void showLibInfo()
{
	printf("\n");

	printf("[libavmsapi] ========================================\n");
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


Document getJSONDocumentFromFile(std::string filePath)
{
	// 1. File 읽기
	std::ifstream fileStream(filePath.c_str(), std::ios::in);
	if (fileStream.is_open() == false) {
//			throw FormatableBuffer<>("%d : Incorrect JSON Data", __LINE__).c_str();
		throw std::string(FormatableBuffer<>("%d : Unable to read configation file. (%s)", __LINE__, filePath.c_str()).c_str());
	}

	// 2. JSON 포멧 확인
	IStreamWrapper configFileStreamWrapper(fileStream);
	Document jsonDocument;
	ParseResult parseResult = jsonDocument.ParseStream(configFileStreamWrapper);
	fileStream.close();
	if (parseResult == false) {
//			throw FormatableBuffer<>("%d : Incorrect JSON Data", __LINE__).c_str();
		throw std::string(FormatableBuffer<>("%d : Invalid JSON format. (%s)", __LINE__, filePath.c_str()).c_str());
	}

	return jsonDocument;
}



bool sendNotiMessage(std::string message)
{
	// TODO : FIFO 가 존재하는지 확인

	// FIFO 로 Error Message 쓰기
	int32_t fifoFD = 0;
	if ( (fifoFD = ::open(g_destinationFIFOPath.c_str(), O_WRONLY)) < 0) {
//		printf("Error : Can not open FIFO.\n");
		throw std::string(FormatableBuffer<>("%d : Can not open FIFO. (%s)", __LINE__, g_destinationFIFOPath.c_str()).c_str());
	}

	if (::write(fifoFD, message.c_str(), message.length()) <= 0) {
		return false;
	}

	::close(fifoFD);

	return true;
}



void avmuObserver()
{
	// signal handler
	g_signalHandler.set_signal(SIGINT);
	g_signalHandler.set_signal(SIGKILL);
	g_signalHandler.set_signal(SIGTERM);
	g_signalHandler.set_ignore(SIGPIPE);
	g_signalHandler.set_signal_handler(&signalEventHandler);



	std::string notiMessageDefault;
	std::string notiMessageError;

	uint32_t avmuIPAddr32[2] = {0, 0};


	try {
		{
			Document jsonDocument = getJSONDocumentFromFile(CommonUtil::getDirectoryName() + "/" + FILE_NAME_DESTINATION);
			g_destinationFIFOPath = Pointer("/destination").Get(jsonDocument)->GetString();
		}
		{
			Document jsonDocument = getJSONDocumentFromFile(CommonUtil::getDirectoryName() + "/"  + FILE_NAME_MESSAGE_DEFAULT);
			notiMessageDefault = CommonUtil::getJsonString(jsonDocument);
		}
		{
			Document jsonDocument = getJSONDocumentFromFile(CommonUtil::getDirectoryName() + "/"  + FILE_NAME_MESSAGE_ERROR);
			notiMessageError = CommonUtil::getJsonString(jsonDocument);
		}
	}
	catch (const char* msg) {
		printDebugInfo("%d%s	%s	%s\n", __LINE__, COLOR_WARNING, msg, COLOR_RESET);
	}
	catch (const std::string msg) {
 		printDebugInfo("%d%s	%s	%s\n", __LINE__, COLOR_WARNING, msg.c_str(), COLOR_RESET);
	}
	catch (...) {
		printDebugInfo("%d%s	%s	%s\n", __LINE__, COLOR_WARNING, "unknown", COLOR_RESET);
	}



	// AVMU 주소 확인
	{
		const uint32_t avmuNo = 2;	// AVMU IP 네번째 자리
		// 환경설정에서 가져옴 : 시스템 IP 는 Network 가 unlink 일 때, 0.0.0.0 을 반환하기 때문에, 정상 동작 불가
		std::string ipAddress;
		SystemUtil::runCommand("cat /opt/interm/public_html/modules/network_setup/conf/network_stat.json | jq '.network_primary.ip_address' | sed 's/\"//g'", ipAddress);
		uint32_t ipAddr32 = CommonUtil::ipAddressToUint32(ipAddress.c_str());
		avmuIPAddr32[0] = (ipAddr32 & 0xFFFF0000) | (1 << 8) | avmuNo;
		avmuIPAddr32[1] = (ipAddr32 & 0xFFFF0000) | (6 << 8) | avmuNo;
	}

	bool wasAliveAVMU = true;



	while (g_signalHandler.is_term() == false) {
		try {
			// 1. AVMU 네트워크 상태 확인
			bool isAliveAVMU = CommonUtil::ping(CommonUtil::ipAddressFromUint32(avmuIPAddr32[0]), 100, 2);
			if (isAliveAVMU == false) {
				isAliveAVMU = CommonUtil::ping(CommonUtil::ipAddressFromUint32(avmuIPAddr32[1]), 100, 2);
			}

			// 2. 상태 변경시 Message 송신
			if (wasAliveAVMU != isAliveAVMU) {
				if (isAliveAVMU == false) {
					// Error Message 송신
					sendNotiMessage(notiMessageError);
				}
				else {
					// Default Message 송신
					sendNotiMessage(notiMessageDefault);
				}
			}

			wasAliveAVMU = isAliveAVMU;
		}
		catch (const char* msg) {
			printf("%s	\n", msg);
		}
		catch (const std::string msg) {
			printf("%s	\n", msg.c_str());
		}

		Thread::sleep(1000);
	
	}

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
				g_isDebugPrint = true;

				if (g_isDebugPrint) {
					g_signalHandler.set_debug_print();
				}

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
	avmuObserver();



	return 0;
}
