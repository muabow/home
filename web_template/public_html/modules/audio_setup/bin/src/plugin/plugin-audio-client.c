#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <semaphore.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <errno.h>
#include <unistd.h>
#include <sqlite3.h>
#include <arpa/inet.h>
#include <netinet/in.h>
#include <netinet/tcp.h>
#include <stdarg.h>
#include <stdint.h>

#include <iostream>
#include <string>

#include "plugin_types.h"
#include "plugin-audio-client.h"

#define FLAG_DEBUG_PRINT		false	
#define PATH_DEBUG_FILE			"/tmp/debug_plugin-audio-client"


static	sem_t g_sem_lock_key;
bool	g_flag_init_run = 0;  // basic value


/* ext functions */
void  dbg_printf(const char *_format, ...) {
	bool is_debug = false;
	
	struct stat stInfo;

	if( stat(PATH_DEBUG_FILE, &stInfo) == 0 ) {
		is_debug = true;
	}
	
	if( !FLAG_DEBUG_PRINT && !is_debug ) {
		return ;
	}	
	
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);

	return ;
}


class WS_Connector {
	const int NUM_EXT_PORT		=	2010;	//audio player, ws_router port
	const int NUM_GATEWAY_PORT	=	2100;

	enum WS_EVENT_CASE {
		UPDATE_VOLUME_DATABASE	=	11		// update volume
	};

	struct ORDER_PACKET {
		char    cmd;
		char    rsvd[3];
		int     bodyLen;
		char    *data;
	} typedef ORDER_PACKET_t;

	private :
		int	clientSockFd;
		int	extPort;

		void updateDatabase(int _type, int _stat) {
			char tmpMsg[128];
			ORDER_PACKET_t  tSendPacket;

			memset(&tSendPacket, 0x00, sizeof(tSendPacket));
			memset(&tmpMsg, 	 0x00, sizeof(tmpMsg));

			sprintf(tmpMsg, "{\"type\":\"%d\", \"data\":{\"playVolume\":%d}}", _type, _stat);

			if( (tSendPacket.data = (char *)malloc((strlen(tmpMsg) + 1) * sizeof(char))) == NULL ) {
				dbg_printf("updateDatabase() malloc() data failed : [%02d] %s\n", errno, strerror(errno));

				return ;
			}

			tSendPacket.cmd = _type;
			strcpy(tSendPacket.data, tmpMsg);
			tSendPacket.bodyLen = strlen(tSendPacket.data);
			tSendPacket.data[tSendPacket.bodyLen] = '\0';

			if( send(this->clientSockFd, &tSendPacket, sizeof(tSendPacket) - sizeof(tSendPacket.data), MSG_DONTWAIT) < 0 ) {
				dbg_printf("updateDatabase() send() head failed : [%02d] %s\n", errno, strerror(errno));

				free(tSendPacket.data);
				return ;
			}

			if( send(this->clientSockFd, tSendPacket.data, tSendPacket.bodyLen, MSG_DONTWAIT) < 0 ) {
				dbg_printf("updateDatabase() send() body failed : [%02d] %s\n", errno, strerror(errno));

				free(tSendPacket.data);
				return ;
			}

			free(tSendPacket.data);

			return ;
		}
		
		bool sendToWebBlock(int _cmdId, int _stat) {
			int	extId = this->extPort;

			struct sockaddr_in  tServerAddr;

			if( (this->clientSockFd = socket(AF_INET, SOCK_STREAM, 0)) < 0 ) {
				dbg_printf("sendToWebBlock() socket() failed : [%02d] %s\n", errno, strerror(errno));

				return false;
			}

			memset(&tServerAddr, 0x00, sizeof(tServerAddr));
			tServerAddr.sin_family     = AF_INET;
			tServerAddr.sin_port       = htons(WS_Connector::NUM_GATEWAY_PORT);
			tServerAddr.sin_addr.s_addr= inet_addr("127.0.0.1");

			if( connect(this->clientSockFd, (struct sockaddr*)&tServerAddr, sizeof(tServerAddr)) < 0 ) {
				dbg_printf("sendToWebBlock() connect() failed : [%02d] %s\n", errno, strerror(errno));

				close(this->clientSockFd);
				return false;
			}

			if( send(this->clientSockFd, &extId, sizeof(extId), 0) < 0 ) {
				dbg_printf("sendToWebBlock() send() port failed : [%02d] %s\n", errno, strerror(errno));

				close(this->clientSockFd);
				return false;
			}

			switch( _cmdId ) {
				case WS_EVENT_CASE::UPDATE_VOLUME_DATABASE :
					this->updateDatabase(_cmdId, _stat);
					break;
				
				default :
					break;
			}

			close(this->clientSockFd);

			return true;
		}
		
	public :
		WS_Connector(void) {
			this->extPort = WS_Connector::NUM_EXT_PORT;
			return ;
		}

		~WS_Connector(void) {
			close(this->clientSockFd);

			return ;
		}

		void setExtPort(int _port) {
			this->extPort = _port;

			return ;
		}

		bool noti_ws_volume_info(int _volume) {
			return this->sendToWebBlock(WS_EVENT_CASE::UPDATE_VOLUME_DATABASE, _volume);
		}
};

int get_pipe_fd(const char *_path) {
	int pipe_fd;
	
	if( (pipe_fd = open(_path, O_RDWR)) < 0 ) {
		dbg_printf("open failed [%s] [%02d] : %s\n", _path, errno, strerror(errno));

		return -1;
	}
	
	return pipe_fd;
}

bool SendData(char _code, int _dataLength, char *_ptr) {
	bool	rc = true;
	int     writeFd, readFd;
	int     pWriteFd, pReadFd;
	
	PIPE_DATA_t     tPipeData;

	tPipeData.code 		  = _code;
	tPipeData.dataLength  = _dataLength;
	
	FILE *fp;
	int	 readRc;
	
	switch( _code ) {
		case 0x30 : // setVolumePlayer
			if( (writeFd = get_pipe_fd(PATH_PIPE_WRITE_PLAYER)) < 0 ) {
				rc = false;
				break;
			}
			write(writeFd, &tPipeData, sizeof(tPipeData));
			write(writeFd, _ptr, _dataLength);
			
			break;	
	}

	close(readFd);
	close(writeFd);

	return rc;
}

int init_plugin(void *_args) { 
	dbg_printf("%s\n", __FUNCTION__);
	
	sem_init(&g_sem_lock_key, 0, 1);
	
    return 0;
}

int deinit_plugin(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);
	
	sem_destroy(&g_sem_lock_key);

    return 0;
}

// 0x30 : setVolumePlayer
int setVolumePlayer(void *_args) {
	int rc;
	
	dbg_printf("%s\n", __FUNCTION__);

	CLIENT_VOL_VALUE *stVolume = (CLIENT_VOL_VALUE *)_args;
	rc = SendData(0x30, sizeof(int), (char *)&stVolume->volume);
	
	WS_Connector ws;
	ws.noti_ws_volume_info((int)stVolume->volume);
	
	return rc;
}
