/**
	@file	
	@brief	Common Utility
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2020.01.16
 */

#ifndef __COMMON_UTIL_H__
#define __COMMON_UTIL_H__


#include <cstdio>
#include <cstdlib>
#include <string>

#include <netinet/ip_icmp.h>


#include <avmsapi/rapidjson/document.h>



using namespace rapidjson;



/**
	@class	CommonUtil
	@brief	Common Utility
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2020.01.16
 */
class CommonUtil {
public:
	enum HTTPMethod {
		GET,
		POST,
	};

	
	/**
		@brief	CommonUtil 생성자
		@param	[in] isDebugPrint	Debug Message 출력 여부
	 */
	CommonUtil();

	/**
		@brief	CommonUtil 소멸자
	 */
	virtual ~CommonUtil();




	/**
		@brief	Ping 테스트
		@param	[in] address	주소
		@param	[in] mil	테스트 주기 (millisecond)
		@param	[in] maxCount	테스트 횟수
	 */
	static bool ping(const std::string address, const uint32_t mil = 200, const int32_t maxCount = 5);



	/**
		@brief	IP 주소 문자열을 32bit 로 변환
		@param	[in] ip	IP 주소 (문자열)
		@param	return	IP 주소 (32Bit)
	 */
	static uint32_t ipAddressToUint32(const char* ip);


	/**
		@brief	IP 주소 32Bit 를 문자로 변환
		@param	[in] ip	IP 주소 (32Bit)
		@param	return	IP 주소 (문자열)
	 */
	 static const char* ipAddressFromUint32(uint32_t ip);

private:
	/**
		@brief	checksum - standard 1s complement checksum
		@param	[in] data	데이터
		@param	[in] length	데이터 길이
		@return	체크썸
	 */
	static uint16_t checksum(void* data, int32_t length);



public:
	 static std::string getJsonString(const std::string jsonString);
	 static std::string getJsonString(const rapidjson::Document& jsonDocument);
	 static std::string getJsonPrettyString(const std::string jsonString);
	 static std::string getJsonPrettyString(const rapidjson::Document& jsonDocument);



	static std::string getAbsoluteFilePath();
	static std::string getDirectoryName(std::string path = "");
	static std::string getFileName(std::string path = "");

	static std::string replaceAll(std::string& str, const std::string& from, const std::string& to);


	// API Request by CURL
	static size_t writeCallback(void *contents, size_t size, size_t nmemb, void *userp);
	static bool requestAPI(const HTTPMethod method, std::string ip, uint32_t port, std::string api, std::string& result, std::string jsonString);

private:
	static const int32_t PACKET_SIZE = 64;		///< Ping Test 에서 사용되는 패킷 크기

	/**
		@brief	icmp Packet
	 */
	struct packet {
	    struct icmphdr hdr;
	    char msg[PACKET_SIZE - sizeof(struct icmphdr)];
	};


	bool _isDebugPrint;		///< Debug Message 출력 여부
};



#endif	// __COMMON_UTIL_H__
