#ifndef __AVMS_CONNECTOR_H__
#define __AVMS_CONNECTOR_H__

/*
	1. 컴파일    : g++ -o avms_connector avms_connector.cpp -std=c++11
	2. 적용 대상 : PN-Series - DSP master volume
*/
#include <stdio.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>
#include <stdlib.h>
#include <list>
#include <sys/types.h>
#include <sys/stat.h>
#include <arpa/inet.h>
#include <netinet/in.h>
#include <netinet/tcp.h>

class AVMS_Connector : public Thread {
	const int		NUM_AVMS_DEV_PORT			= 8888;
	const char	   *STR_SERVER_ADDR				= "127.0.0.1";

	const int		SIZE_BUFFER_DATA			= 1024;
	const int		SIZE_RECV_HEADER			= 5;
	const int 		TIME_LINGER_TIMEOUT_SEC		= 1;
	const int 		TIME_LINGER_TIMEOUT_MSEC	= 0;
	const int 		SOCKET_RCV_TIMEOUT_SEC		= 1;
	const int 		SOCKET_RCV_TIMEOUT_MSEC		= 0;

	typedef struct {
		char	header;
		short	length;
		char	type;
	} __attribute__((packed)) DSP_DATA_t;

	private :
		int		socketFd;
		char	serverIpAddr[24];
		int		serverPort;
		
		std::list<std::string> sendMessageList;
		Mutex sendMessageMutex;
		
		bool isRequestedStop;

		
		std::string popSendData () {
			std::string sendData = "";
				
			this->sendMessageMutex.lock();
			if(this->sendMessageList.empty() == false) {
				// 마지막 한개만 처리
				sendData = this->sendMessageList.back();
				this->sendMessageList.clear();
			}
			this->sendMessageMutex.unlock();
			
			return sendData;
		}
		
		void pushSendData (std::string sendData) {			
			
			this->sendMessageMutex.lock();
			
			this->sendMessageList.push_back(sendData);
			
			this->sendMessageMutex.unlock();
			
			return ;
		}
		
		bool sendData(char *_data) {
			int rc;

			DSP_DATA_t	tDspData;

			tDspData.header = 0xEF;
			tDspData.length = (int)strlen(_data);
			tDspData.type	= 2;

			if( (rc = send(this->socketFd, &tDspData, sizeof(tDspData), 0)) < 0 ) {
				fprintf(stderr, "AVMS_Connector - send() failed : [%02d] %s\n", errno, strerror(errno));

				close(this->socketFd);
				this->socketFd = -1;
				return false;
			}

			if( tDspData.length > 0 ) {
				if( (rc = send(this->socketFd, _data, tDspData.length, 0)) < 0 ) {
					fprintf(stderr, "AVMS_Connector - send() failed : [%02d] %s\n", errno, strerror(errno));

					close(this->socketFd);
					this->socketFd = -1;
					return false;
				}
				//printf("send message : \n%s\n", _data);
			}

			char recvData[AVMS_Connector::SIZE_BUFFER_DATA];
			rc = recv(socketFd, recvData, AVMS_Connector::SIZE_BUFFER_DATA, 0);

			memmove(recvData, recvData + AVMS_Connector::SIZE_RECV_HEADER,
					AVMS_Connector::SIZE_BUFFER_DATA - AVMS_Connector::SIZE_RECV_HEADER);

			//printf("recv message : \n%s\n", recvData);

			return true;
		}

	public :	
		AVMS_Connector(void) {
			//printf("AVMS_Connector - construction\n");

			sprintf(this->serverIpAddr, "%s", AVMS_Connector::STR_SERVER_ADDR);
			this->serverPort = AVMS_Connector::NUM_AVMS_DEV_PORT;
			this->socketFd = -1;
			this->isRequestedStop = false;

			return ;
		}

		virtual ~AVMS_Connector(void) {
			requestStop();
			join();
			
			if( this->socketFd != -1 ) {
				//printf("AVMS_Connector - closed socket : [%d]\n", this->socketFd);

				close(this->socketFd);
			}

			return ;
		}
		
		void requestStop() {
			isRequestedStop = true;
		}

		void setServerIpAddr(char *_ipAddr) {
			//printf("AVMS_Connector - Change server IP address : [%s] -> [%s]\n", this->serverIpAddr, _ipAddr);
			sprintf(this->serverIpAddr, "%s", _ipAddr);

			return ;
		}

		void setServerPort(int _port) {
			//printf("AVMS_Connector - Change server port : [%d] -> [%d]\n", this->serverPort, _port);
			this->serverPort = _port;

			return ;
		}

		void closeServer(void) {
			if( this->socketFd != -1 ) {
				//printf("AVMS_Connector - closed socket : [%d]\n", this->socketFd);

				close(this->socketFd);
			}
		}

		bool connectServer(void) {
			if( this->socketFd != -1 ) {
				//printf("AVMS_Connector - closed socket : [%d]\n", this->socketFd);

				close(this->socketFd);
			}

			if( (this->socketFd = socket(AF_INET, SOCK_STREAM, 0)) < 0 ) {
				fprintf(stderr, "AVMS_Connector - connect() socket() failed : [%02d] %s\n", errno, strerror(errno));

				this->socketFd = -1;
				return false;
			}

			struct sockaddr_in  tServerAddr;
			memset(&tServerAddr, 0x00, sizeof(tServerAddr));
			tServerAddr.sin_family     = AF_INET;
			tServerAddr.sin_port       = htons(this->serverPort);
			tServerAddr.sin_addr.s_addr= inet_addr(this->serverIpAddr);

			if( connect(this->socketFd, (struct sockaddr*)&tServerAddr, sizeof(tServerAddr)) < 0 ) {
				fprintf(stderr, "AVMS_Connector - connect() connect() failed : [%02d] %s\n", errno, strerror(errno));

				close(this->socketFd);
				this->socketFd = -1;
				return false;
			}

			struct  timeval tTimeo = {AVMS_Connector::SOCKET_RCV_TIMEOUT_SEC, AVMS_Connector::SOCKET_RCV_TIMEOUT_MSEC};
			struct  linger  tLinger;

			tLinger.l_onoff  = true;
			tLinger.l_linger = AVMS_Connector::TIME_LINGER_TIMEOUT_SEC;

			if( setsockopt(this->socketFd, SOL_SOCKET, SO_LINGER, (char *)&tLinger, sizeof(tLinger)) < 0 ) {
				fprintf(stderr, "AVMS_Connector - connect() setsockopt() SO_LINGER failed : [%02d] %s\n", errno, strerror(errno));
			}

			if( setsockopt(this->socketFd, SOL_SOCKET, SO_RCVTIMEO, &tTimeo, sizeof(tTimeo)) < 0 ) {
				fprintf(stderr, "AVMS_Connector - connect() setsockopt() SO_RCVTIMEO failed : [%02d] %s\n", errno, strerror(errno));
			}

			return true;
		}

		bool setDspVolume(int _index, int _volume) {
			char sendData[AVMS_Connector::SIZE_BUFFER_DATA];
			sprintf(sendData, "{\"PLUGIN\": \"plugin-dsp-setup\", \"FUNCTION\": \"setVolume\", \"set_volume\": {\"index\": %d, \"volume\": %d}}", _index, _volume);
			
			this->pushSendData((std::string)sendData);
			
			return true;
		}

		bool setDspMasterVolume(int _volume) {

			return (this->setDspVolume(0, _volume) > 0);
		}
		
		int32_t run() {
			
			while (isRequestedStop == false) {
				
				std::string sendData = this->popSendData();
				
				if(sendData.size() > 0) {
					this->connectServer();
					this->sendData((char *)sendData.c_str());
					this->closeServer();
				}

				Thread::sleep(100);
			}
			return 0;
		}
};

#endif	// __AVMS_CONNECTOR_H__
