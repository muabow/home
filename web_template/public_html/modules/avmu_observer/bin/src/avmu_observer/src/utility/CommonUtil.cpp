/**
	@file	
	@brief	Common Utility
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2020.01.16
 */

#include <cassert>
#include <cstdarg>
#include <cstring>
#include <typeinfo>

#include <fcntl.h>
#include <netdb.h>
#include <netinet/in.h>
#include <resolv.h>
#include <sys/socket.h>
#include <unistd.h>


#include <curl/curl.h>


//#include <avmsapi/rapidjson/document.h>
//#include <avmsapi/rapidjson/prettywriter.h>
//#include <avmsapi/rapidjson/istreamwrapper.h>
#include <avmsapi/rapidjson/document.h>
#include <avmsapi/rapidjson/pointer.h>
#include <avmsapi/rapidjson/writer.h>
#include <avmsapi/rapidjson/prettywriter.h>
#include <avmsapi/rapidjson/stringbuffer.h>
//#include <avmsapi/rapidjson/error/en.h>
//#include <avmsapi/rapidjson/error/error.h>


#include <avmsapi/utility/SystemUtil.h>


#include "CommonUtil.h"

#include "FormatableBuffer.h"



/**
	@brief	checksum - standard 1s complement checksum
	@param	[in] data	데이터
	@param	[in] length	데이터 길이
	@return	체크썸
 */
uint16_t CommonUtil::checksum(void* data, int32_t length) {
	uint16_t*	buf = (uint16_t*)data;
	uint32_t	sum = 0;
	uint16_t	result;

	for (sum = 0; length > 1; length -= 2) {
		sum += *buf++;
	}

	if (length == 1) {
		sum += *(uint8_t*)buf;
	}

	sum = (sum >> 16) + (sum & 0xFFFF);
	sum += (sum >> 16);
	result = ~sum;

	return result;
}



/**
	@brief	Ping 테스트
	@param	[in] address	주소
	@param	[in] mil	테스트 주기 (millisecond)
	@param	[in] maxCount	테스트 횟수
 */
bool CommonUtil::ping(const std::string address, const uint32_t mil /* = 200 */, const int32_t maxCount /* = 5 */)
{
#if 1
	float timeout = (mil * maxCount + 100) / 1000.0;
	char cmd[128] = {0, };
	sprintf(cmd, "timeout %0.2f ping %s -i %0.2f -c %d > /dev/null ; echo $?", timeout, address.c_str(), (mil / 1000.0), maxCount);
	std::string result;
	SystemUtil::runCommand(cmd, result);
//	printf("%s : %s\n", cmd, result.c_str());

	try {
		if (std::stoi(result) == 0) {
			return true;
		}
	}
	catch (const std::exception& e) {
	}
	return false;

#else
	int32_t pid = getpid();
	struct protoent* proto = NULL;
	int32_t num_sequence = 1;

	const int32_t val = 255;
	int32_t idx;
	int32_t socket_fd;

	struct packet 		pckt;
	struct sockaddr_in	r_addr;
	struct hostent*		hname;
    struct sockaddr_in	addr_ping;
	struct sockaddr_in*	addr;

	int32_t size_packet_msg = sizeof(pckt.msg);

	socklen_t len;

	proto = getprotobyname("ICMP");
	hname = gethostbyname(address.c_str());
	bzero(&addr_ping, sizeof(addr_ping));
	addr_ping.sin_family = hname->h_addrtype;
	addr_ping.sin_port = 0;
	addr_ping.sin_addr.s_addr = *(long*)hname->h_addr; 

    addr = &addr_ping;

	if ( (socket_fd = socket(PF_INET, SOCK_RAW, proto->p_proto)) < 0 ) {
		printf("icmp() socket open failed : [%02d] %s\n", errno, strerror(errno));
		return false;
	}

	if ( setsockopt(socket_fd, SOL_IP, IP_TTL, &val, sizeof(val)) != 0 ) {
		printf("icmp() set TTL option failed : [%02d] %s\n", errno, strerror(errno));
		return false;
	}

	if ( fcntl(socket_fd, F_SETFL, O_NONBLOCK) != 0 ) {
		printf("icmp() request nonblocking I/O failed : [%02d] %s\n", errno, strerror(errno));
		return false;
	}

	for ( int loop_cnt = 0 ; loop_cnt < maxCount ; loop_cnt++ ) {
		len = sizeof(r_addr);
		if ( recvfrom(socket_fd, &pckt, sizeof(pckt), 0x00, (struct sockaddr*)&r_addr, &len) > 0 ) {
			close(socket_fd);
			return true;
		}

		bzero(&pckt, sizeof(pckt));
		pckt.hdr.type = ICMP_ECHO;
		pckt.hdr.un.echo.id = pid;

		for ( idx = 0; idx < size_packet_msg - 1 ; idx++ ) {
			pckt.msg[idx] = idx + '0';
		}

		pckt.msg[idx] = 0;
		pckt.hdr.un.echo.sequence = num_sequence++;
		pckt.hdr.checksum = checksum(&pckt, sizeof(pckt));

		if ( sendto(socket_fd, &pckt, sizeof(pckt), 0, (struct sockaddr*)addr, sizeof(*addr)) <= 0 ) {
			printf("icmp() sendto failed : [%02d] %s\n", errno, strerror(errno));
		}
			
		usleep(mil * 1000);
	}
	
	close(socket_fd);

	return false;
#endif
}



uint32_t CommonUtil::ipAddressToUint32(const char* ip)
{
    uint32_t field[4];
    ::sscanf(ip, "%d.%d.%d.%d", &field[0], &field[1], &field[2], &field[3]);

    return ((field[0] & 0xFF) << 24) + ((field[1] & 0xFF) << 16) +
           ((field[2] & 0xFF) << 8) + ((field[3] & 0xFF));
}

const char* CommonUtil::ipAddressFromUint32(uint32_t ip)
{
    static char __s[32];
    ::sprintf(__s, "%d.%d.%d.%d", (ip & 0xFF000000) >> 24, (ip & 0x00FF0000) >> 16,
                                  (ip & 0x0000FF00) >> 8, (ip & 0x000000FF));
    __s[31] = '\0';
    return __s;
}



std::string CommonUtil::getJsonString(const std::string jsonString)
{
	Document jsonDocument;

	ParseResult parseResult = jsonDocument.Parse((char *)jsonString.c_str());
	if (parseResult.IsError()) {
		throw FormatableBuffer<>("Incorrect JSON Data	%s::%d", __FILE__, __LINE__).c_str();
	}
	
	StringBuffer dataBuffer;
	Writer<StringBuffer> data_writer(dataBuffer);	// Writer / PrettyWriter
	jsonDocument.Accept(data_writer);
	
	std::string retJsonString = dataBuffer.GetString();
	dataBuffer.Clear();

	return retJsonString;
}



std::string CommonUtil::getJsonString(const Document& jsonDocument)
{

	StringBuffer dataBuffer;
	Writer<StringBuffer> data_writer(dataBuffer);	// Writer / PrettyWriter
	jsonDocument.Accept(data_writer);
	
	std::string retJsonString = dataBuffer.GetString();
	dataBuffer.Clear();

	return retJsonString;
}



std::string CommonUtil::getJsonPrettyString(const std::string jsonString)
{
	Document jsonDocument;

	ParseResult parseResult = jsonDocument.Parse((char *)jsonString.c_str());
	if (parseResult.IsError()) {
		throw "Incorrect JSON Data";
	}
	
	StringBuffer dataBuffer;
	PrettyWriter<StringBuffer> data_writer(dataBuffer);	// Writer / PrettyWriter
	jsonDocument.Accept(data_writer);
	
	std::string retJsonString = dataBuffer.GetString();
	dataBuffer.Clear();

	return retJsonString;
}



std::string CommonUtil::getJsonPrettyString(const Document& jsonDocument)
{
	StringBuffer dataBuffer;
	PrettyWriter<StringBuffer> data_writer(dataBuffer);	// Writer / PrettyWriter
	jsonDocument.Accept(data_writer);
	
	std::string retJsonString = dataBuffer.GetString();
	dataBuffer.Clear();

	return retJsonString;
}


/**
	@brief	현재 프로세스의 절대 경로 반환
	@remark
		MFC GetModuleFileName()
	@return	현재 프로세스의 절대 경로
 */
std::string CommonUtil::getAbsoluteFilePath()
{
	char filePath[1024] = {0, };
	readlink("/proc/self/exe", filePath, 1024);

	return std::string(filePath);
}


/**
	@brief	디렉트리 반환
	@remark
		dirname()
	@return	디렉토리 반환
 */
std::string CommonUtil::getDirectoryName(std::string path /* = "" */)
{
	if (path.empty()) {
		path = getAbsoluteFilePath();
	}

	const int32_t idx = path.find_last_of("/");
	if (idx >= 0) {
		path.erase(idx, path.size());
	}

	return path;
}


/**
	@brief	파일명 반환
	@remark
		filename()
	@return	디렉토리 경로 반환
 */
std::string CommonUtil::getFileName(std::string path /* = "" */)
{
	if (path.empty()) {
		path = getAbsoluteFilePath();
	}

	const int32_t idx = path.find_last_of("/");
	if (idx >= 0) {
		path.erase(0, idx + 1);
	}

	return path;
}



std::string CommonUtil::replaceAll(std::string &str, const std::string& from, const std::string& to)
{
	size_t start_pos = 0; //string처음부터 검사
	while((start_pos = str.find(from, start_pos)) != std::string::npos)  //from을 찾을 수 없을 때까지
	{
		str.replace(start_pos, from.length(), to);
		start_pos += to.length(); // 중복검사를 피하고 from.length() > to.length()인 경우를 위해서
	}
	return str;
}




// Curl 응답 결과를 받으려면 필요
size_t CommonUtil::writeCallback(void *contents, size_t size, size_t nmemb, void *userp)
{
   ((std::string*)userp)->append((char*)contents, size * nmemb);
    return size * nmemb;
}



bool CommonUtil::requestAPI(const HTTPMethod method, std::string ip, uint32_t port, std::string api, std::string& result, std::string jsonString)
{
	CURL* curl = curl_easy_init();

	CURLcode res;


	if (curl == NULL) {
		return false;
	};

	try {
#if 0
		// 접속가능한지 검사
		if (CommonUtil::ping(ip, 100, 2) == false) {
			std::string msg = "WARNING : [" + ipCamera->getIP() + "] Ping Fail";
			throw msg.c_str();
		}
#endif
		curl_easy_setopt(curl, CURLOPT_TIMEOUT, 1); //timeout in seconds
		curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, CommonUtil::writeCallback);
		curl_easy_setopt(curl, CURLOPT_WRITEDATA, &result);


		// URL
		curl_easy_setopt(curl, CURLOPT_URL, FormatableBuffer<>("http://%s:%u/%s", ip.c_str(), port, api.c_str()).c_str());
		printf("%s\n", FormatableBuffer<>("http://%s:%u/%s", ip.c_str(), port, api.c_str()).c_str());


		struct curl_slist* headers = NULL;


		if (method == GET) {
			//
		}
		else if (method == POST) {
			headers = curl_slist_append(headers, "Content-Type: application/json");
			curl_easy_setopt(curl, CURLOPT_HTTPHEADER, headers);

			curl_easy_setopt(curl, CURLOPT_POST, 1L); //POST option
			curl_easy_setopt(curl, CURLOPT_POSTFIELDS, jsonString.c_str()); //string의 data라는 내용을 전송 할것이다
		}


		res = curl_easy_perform(curl);
		if (res != CURLE_OK) {
			std::string msg = "WARNING : [" + ip + "] curl_easy_perform() " + curl_easy_strerror(res);
			throw msg.c_str();
		}


		curl_slist_free_all(headers);	// CURLOPT_HTTPHEADER
	}
	catch (const char* msg) {
		if (::strlen(msg) > 0) {
			printf("%s::%d	%s\n", __FILE__, __LINE__, msg);
		}
		return false;
	}

	curl_easy_cleanup(curl); // curl_easy_init 과 세트


	return true;
}



