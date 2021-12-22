/**
	@file	
	@brief	System Utility
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.04.26
 */

#ifndef __SYSTEM_UTIL_H__
#define __SYSTEM_UTIL_H__


#include <cstring>
#include <string>

#include <sys/types.h> 
#include <sys/socket.h>
#include <net/if.h>		// iferq
#include <sys/ioctl.h>

#include <netinet/in.h>
#include <arpa/inet.h>

#include <sstream>
#include <fstream>

#include <algorithm>

// for isDuplicatedRun()
#include <stdio.h>		// stdout
#include <unistd.h>		// ftruncate(), pause()
#include <sys/types.h>	// ftruncate()
#include <string.h>		// strlen()
#include <errno.h>		// error
#include <stdlib.h>		// exit()
#include <fcntl.h>		// O_CREAT



#define trace	printf
#define STR(key)    #key


using namespace std;

class SystemUtil {
public:

	static uint32_t ipAddressToUint32(const char* ip)
	{
		uint32_t field[4];
		::sscanf(ip, "%d.%d.%d.%d", &field[0], &field[1], &field[2], &field[3]);

		return ((field[0] & 0xFF) << 24) + ((field[1] & 0xFF) << 16) +
			((field[2] & 0xFF) << 8) + ((field[3] & 0xFF));
	}

	static const char* ipAddressFromUint32(uint32_t ip)
	{
		static char __s[32];
		::sprintf(__s, "%d.%d.%d.%d", (ip & 0xFF000000) >> 24, (ip & 0x00FF0000) >> 16,
									(ip & 0x0000FF00) >> 8, (ip & 0x000000FF));
		__s[31] = '\0';
		return __s;
	}

	static bool isValidIPFirstOctet(const uint32_t ip) {
		bool ret = false;
		uint32_t ipFirstOctet = (ip & 0xFF000000) >> 24;
		if(ipFirstOctet > 0 && ipFirstOctet < 224 && ipFirstOctet != 127) {
			ret = true;
		}
		return ret;
	}

	static bool isValidSubnet(const uint32_t subnetMask) {
		bool ret = false;
		uint32_t mask = 0xFFFFFFFF;
		for (int i=0; i<31 ; i++) {
			mask = mask << 1;
			if (subnetMask == mask) {
				ret = true;
				break;
			}
		}
		return ret;
	}

	static void getMacAddress(const char* ethID, char* macAddr)
	{
#if 0
		struct ifreq if_hwaddr;
		const int skfd = ::socket(AF_INET, SOCK_DGRAM, 0);
		
		if (skfd < 0) {
	//		trace("[%s] socket : %s\n", __FUNCTION__, strerror(errno));
			return;
		}

		strcpy(if_hwaddr.ifr_name, ethID);

		if (::ioctl(skfd, SIOCGIFHWADDR, &if_hwaddr) < 0) {
	//		trace("[%s] ioctl : %s\n", __FUNCTION__, strerror(errno));
			::close(skfd);
			return;
		}
		::close(skfd);

		uint8_t* addr = (uint8_t*)if_hwaddr.ifr_hwaddr.sa_data;

		for (int32_t i = 0; i < 6; i++) {
			macAddr[i] = (uint8_t)if_hwaddr.ifr_hwaddr.sa_data[i];
		}
#else
		char buf[1024] = {};
		sprintf(buf, "/sys/class/net/%s/address", ethID);
		ifstream file(buf);
		if (!file) {
	//		trace("[%s] ifstream : %s\n", __FUNCTION__, strerror(errno));
			strncpy(macAddr, "00:00:00:00:00:00", 18);
			return;
		}

		string line;
		getline(file, line);
		strncpy(macAddr, line.c_str(), 18);
	 #endif

		return;
	}

	static void getIpAddress(const char* ethID, char* ipAddr)
	{
		int sockfd = -1;
		struct ifreq ifrq;
		struct sockaddr_in* sin;

		if ((sockfd = ::socket(AF_INET, SOCK_DGRAM, 0)) < 0) {
	//		trace("[%s] socket : %s\n", __FUNCTION__, strerror(errno));
			strncpy(ipAddr, "0.0.0.0", 7);
			return;
		}

		strcpy(ifrq.ifr_name, ethID);

		if (::ioctl(sockfd, SIOCGIFADDR, &ifrq) < 0) {
	//		trace("[%s] ioctl : %s\n", __FUNCTION__, strerror(errno));
			::close(sockfd);
			strncpy(ipAddr, "0.0.0.0", 7);
			return;
		}
		close(sockfd);

		sin = (sockaddr_in*)&ifrq.ifr_addr;
		strncpy(ipAddr, inet_ntoa(sin->sin_addr), 15);
		return;
	}

	static void getIpMask(const char* ethID, char* ipMask)
	{
		int sockfd = -1;
		struct ifreq ifrq;
		struct sockaddr_in* sin;

		if ((sockfd = ::socket(AF_INET, SOCK_DGRAM, 0)) < 0) {
	//		trace("[%s] socket : %s\n", __FUNCTION__, strerror(errno));
			strncpy(ipMask, "0.0.0.0", 7);
			return;
		}

		strcpy(ifrq.ifr_name, ethID);

		if (::ioctl(sockfd, SIOCGIFNETMASK, &ifrq) < 0) {
	//		trace("[%s] ioctl : %s\n", __FUNCTION__, strerror(errno));
			close(sockfd);
			strncpy(ipMask, "0.0.0.0", 7);
			return;
		}
		::close(sockfd);

		sin = (sockaddr_in*)&ifrq.ifr_addr;
		strncpy(ipMask, inet_ntoa(sin->sin_addr), 15);
		return;
	}

	static void getGateway(const char* ethID, char* gateway)
	{
		ifstream file("/proc/net/route");
		if (!file) {
			//traceln("[%s] ifstream : %s", __FUNCTION__, strerror(errno));
			strncpy(gateway, "0.0.0.0", 7);
			return;
		}

		string line;
		bool isFound = false;
		while (getline(file, line)) {
			if (line.substr(0, line.find('\t', 0)) == ethID) {
				string::size_type pos = line.find('\t', 0);
				while (pos != string::npos) {
					line.replace(pos, 1, 1, ' ');
					pos = line.find('\t', 0);
				}
				char dev[16]; 
				unsigned long dest, gw;
				if (sscanf(line.c_str(), "%s %lx %lx", dev, &dest, &gw) == 3) {
					if (dest == 0lu) { // default gateway
						struct in_addr addr; addr.s_addr = gw;
						strncpy(gateway, inet_ntoa(addr), 15);
						isFound = true;
					}		
				}	
			}
		}
		if (isFound == false) {
			strncpy(gateway, "0.0.0.0", 7);
		}
		return;
	}

	static void getDNS(const char* ethID, char* dnsServer)
	{

		ifstream file("/etc/resolv.conf");
		if (!file) {
			//traceln("[%s] ifstream : %s", __FUNCTION__, strerror(errno));
			strncpy(dnsServer, "0.0.0.0", 7);
			return;
		}

		string line;
		bool isFound = false;
		while (getline(file, line)) {
			if (line.substr(0, 10) == "nameserver") {
				char name[16];
				if (sscanf(line.c_str(), "%s %s", name, dnsServer) == 2) {
					isFound = true;
					break;
				}	
			}
		}
		if (isFound == false) {
			strncpy(dnsServer, "0.0.0.0", 7);
		}
		return;
	}

	static int32_t runCommand(const char* cmd)
	{
//		return WEXITSTATUS(system(cmd))
		return system(cmd);
	}

	static int32_t runCommand(const char* cmd, string& result)
	{
	    //traceln("[%s,%s,%d] cmd : %s", __FILE__, __FUNCTION__, __LINE__, cmd);

	    FILE* stream;
	    if((stream = popen(cmd, "r")) == NULL) {
	//        traceln("Can not run.");
	        return -1;
	    }

	    ostringstream output;
	 
	    while( !feof(stream) && !ferror(stream) )
	    {
	        char buf[128];
	        int bytesRead = fread( buf, 1, 128, stream );
	        output.write( buf, bytesRead );
	    }
	    pclose(stream);
	    result = output.str();

	    return 0;
	}

	static inline std::string &ltrim(std::string &s) {
	        s.erase(s.begin(), std::find_if(s.begin(), s.end(), std::not1(std::ptr_fun<int, int>(std::isspace))));
	        return s;
	}

	static inline std::string &rtrim(std::string &s) {
	        s.erase(std::find_if(s.rbegin(), s.rend(), std::not1(std::ptr_fun<int, int>(std::isspace))).base(), s.end());
	        return s;
	}

	static float getCPUUsage() {
		std::string result;
//		const char* cmd = "top -bn1 | grep load | awk '{printf \"%.2f\\t\\t\\n\", $(NF-2)}'";
//		const char* cmd = "top -bn2 -d0 | grep Cpu | awk '{printf \"%.2f\\n\", $(NF-9)}'";

		// 두번을 읽어야 CPU 들 평균값이 나옴
		// BG 로 실행하려면 nohup ~~ & 로 실행해야 함
		// rc.local 에서 자동 실행시 -b 옵션이 빠지면 "top: failed tty get" 발생
		const char* cmd = "top -b -n 2 -d 0.2 | grep -i cpu\\(s\\) | tail -1 | awk '{print $8}' | tr -d \"%id,\" | awk '{print 100-$1}'";

		runCommand(cmd, result);
		float cpuUsage = 0.0;
		if (sscanf(result.c_str(), "%f", &cpuUsage) < 1) {
			return 0.0;
		}
		return cpuUsage;
	}

	/**
		@brief
		@param	[in] interface	Network Interface (ex.eth0)
		@param	[out] rx	수신 전송률 (KByte/Sec)
		@param	[out] tx	송신 전송률 (KByte/Sec)
		@param	[out] link	연결 속도 (Mbit / Sec)
		@return	true : 성공
		@return	false : 실패
	 */
	static bool getNetworkUsage(const char* interface, float &rx, float &tx, int32_t &link) {
		std::string result;

		// 0.4 초 동안 1회 측정
		char cmd[128] = {};
		const char* cmdGetTraffic = "ifstat -i %s 0.4 1 | tail -1";
		sprintf(cmd, cmdGetTraffic, interface);
		runCommand(cmd, result);
		if (sscanf(result.c_str(), "%f %f", &rx, &tx) < 1) {
			return false;
		}

		// ethtool eth0 | grep Speed | awk '{print $2}' | sed 's/Mb\/s//'
		const char* cmdGetLink = "ethtool %s | grep Speed | awk '{print $2}' | sed 's/Mb\\/s//'";
		sprintf(cmd, cmdGetLink, interface);
		runCommand(cmd, result);
		if (sscanf(result.c_str(), "%d", &link) < 1) {
			return false;
		}


		return true;
	}

	static int32_t map(const int32_t value, const int32_t inputMin, const int32_t inputMax, const int32_t outputMin, const int32_t outputMax) {
		return ((value - inputMin) * (outputMax - outputMin)) / (inputMax - inputMin) + outputMin;
	}

	static float map(const float value, const float inputMin, const float inputMax, const float outputMin, const float outputMax) {
		return (value - inputMin) * (outputMax - outputMin) / (inputMax - inputMin) + outputMin;
	}

	static void showDump(const void* data, const int32_t length, const int32_t width = 16)
	{
		printf("\nAddress = 0x%p		Length = %d		Width = %d\n", data, length, width);
	
		for (int32_t i = 0; i < length; i++) {
			if ((i) % width == 0) {
				printf("%4d %04X	", i / width, i);
			}
			printf("%02X ", ((uint8_t*)data)[i]);
			if ((i + 1) % width == 0) {
				printf("\n");
			} else if ((i + 1) % 4 == 0) {
				printf("   ");
			}
		}
		printf("\n");
	}

private:
	static inline int lock_reg(int fd, int cmd, int type, off_t offset, int whence, off_t len)
	{
		struct flock	lock;
	
		lock.l_type = type; 	/* F_RDLCK, F_WRLCK, F_UNLCK */
		lock.l_start = offset;	/* byte offset, relative to l_whence */
		lock.l_whence = whence; /* SEEK_SET, SEEK_CUR, SEEK_END */
		lock.l_len = len;		/* #bytes (0 means to EOF) */
	
		return( fcntl(fd, cmd, &lock) );	/* -1 upon error */
	}
	
public:
	/**
		@brief	record lock 을 이용한 프로그램의 중복 실행 방지
		@see	Unix Network Programming Vol2 : 9.7 Starting Only One Copy of a Daemon (p.213)
	 */
	static bool isDuplicatedRun(const char* pidFile)
	{
		int 	pidfd;
		char	line[1024];
	
		/* open the PID file, create if nonexistent */
	//	pidfd = Open(pidFile, O_RDWR | O_CREAT, FILE_MODE);
		if ( (pidfd = open(pidFile, O_RDWR | O_CREAT, S_IRUSR | S_IWUSR | S_IRGRP | S_IROTH)) == -1) {
			printf("open error for %s\n", pidFile);
			return -1;
		}
		
		if (pidfd < 0) {
			return true;
		}
	
			/* try to write lock the entire file */
		if (lock_reg(pidfd, F_SETLK, F_WRLCK, 0, SEEK_SET, 0) < 0) {
			if (errno == EACCES || errno == EAGAIN) {
				printf("unable to lock %s\n", pidFile);
				return true;
			}
			else {
				printf("unable to lock %s\n", pidFile);
				return true;
			}
		}
	
		/* 4write my PID, leave file open to hold the write lock */
		snprintf(line, sizeof(line), "%ld\n", (long) getpid());
	
		if (ftruncate(pidfd, 0) == -1) {
			printf("ftruncate error\n");
			return true;
		}
		
		if ((size_t)write(pidfd, line, strlen(line)) != strlen(line)) {
			printf("write error\n");
			return true;
		}
		return false;
	}
	
};

#endif // __SYSTEM_UTIL_H__

