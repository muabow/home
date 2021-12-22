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
#define PATH_JSON_NETWORK_INFO  "/opt/interm/public_html/modules/network_setup/conf/network_stat.json"


static	sem_t g_sem_lock_key;
bool	g_flag_init_run = 0;  // basic value

CLIENT_INFO_t	gtClientInfo;


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

void parse_network_hostname(void) {
	int tokenIndex = 0;
	int pos = 0, fileSize = 0, stringLength = 0;
	char *begin, *end, *buffer;
	char tmpHostName[80];

	FILE *fp;

	if( (fp = fopen(PATH_JSON_NETWORK_INFO, "rb")) == NULL ) {
		dbg_printf("JSON File Error\n");
		sprintf(gtClientInfo.hostName, "hostName:-");

		return ;
	}

	// 파일 크기 구하기
	fseek(fp, 0, SEEK_END);
	fileSize = ftell(fp);
	fseek(fp, 0, SEEK_SET);

	// 파일 크기 + NULL 공간만큼 메모리를 할당하고 0으로 초기화
	buffer = (char *)malloc(fileSize + 1);
	memset(buffer, 0, sizeof(buffer));

	// 파일 내용 읽기
	if( fread(buffer, fileSize, 1, fp) < 1 ) {
		fileSize = 0;
		free(buffer);
		fclose(fp);
		dbg_printf("JSON read Error\n");
	
		sprintf(gtClientInfo.hostName, "hostName:-");
	}
	fclose(fp);
	
	if( buffer[pos] != '{' ) {   // 문서의 시작이 {인지 검사
		sprintf(gtClientInfo.hostName, "hostName:-");
		
		return;
	}

	while( true ) {
		while( pos < fileSize ) {
			switch( buffer[pos] ) {
				case '"':
					begin = buffer + pos + 1;

					end = strchr(begin, '"');
					if( end == NULL ) {
						break;
					}

					stringLength = end - begin;

					if ( tokenIndex == 3 ) {
						memset(tmpHostName, 0,sizeof(tmpHostName));
						memcpy(tmpHostName, begin, stringLength);
						sprintf(gtClientInfo.hostName, "hostName:%s", tmpHostName);
						
						dbg_printf("json %s\n",gtClientInfo.hostName);
					}

					tokenIndex++;
					pos = pos + stringLength + 1;

					break;
			}
			pos++;
			
			if( tokenIndex == 4 ) {
				break;
			}
		}
		break;
	}

	free(buffer);    // 문서 동적 메모리 해제
}

static int SqliteCallBack(void *NotUsed, int argc, char **argv, char **azColName) {
	int idx;
	char use[24], stat[24], actStat[24], operType[24];

	parse_network_hostname();

	for(idx = 0; idx < argc; idx++ ) {		
		if( strcmp(azColName[idx], "use") == 0 ) {
			strcpy(use, argv[idx] ? argv[idx] : "NULL");
			dbg_printf("use : %s\n",use) ;

		} else if( strcmp(azColName[idx], "stat") == 0 ) {
			strcpy(stat, argv[idx] ? argv[idx] : "NULL");
			dbg_printf("stat : %s\n",stat) ;

		} else if( strcmp(azColName[idx], "actStat") == 0 ) {
			strcpy(actStat, argv[idx] ? argv[idx] : "NULL");
			dbg_printf("actStat : %s\n",actStat) ;

			if ( strncmp(actStat, "run", 3) != 0 ) {
				// init state stop
				g_flag_init_run = 1;
			
			} else {
				// init state run
				g_flag_init_run = 2;
			}
			
		} else if( strcmp(azColName[idx], "operType") == 0 ) {
			strcpy(operType, argv[idx] ? argv[idx] : "NULL");
			dbg_printf("operType : %s\n",operType) ;

		}  else if( strcmp(azColName[idx], "protocol") == 0 ) {
			gtClientInfo.typeProtocol = (strcmp(argv[idx], "tcp") == 0 ? true : false);
		
		} else if( strcmp(azColName[idx], "buffer_sec") == 0 ) {
			gtClientInfo.delay = atoi(argv[idx]);
			dbg_printf("delay: %d\n",gtClientInfo.delay) ;

		} else if( strcmp(azColName[idx], "buffer_msec") == 0 ) {
			gtClientInfo.delayMs = atoi(argv[idx]);
			dbg_printf("delayMs: %d\n",gtClientInfo.delayMs) ;

		} else if( strcmp(azColName[idx], "volume") == 0 ) {
			gtClientInfo.playVolume= atoi(argv[idx]);
			dbg_printf("volume: %d\n",gtClientInfo.playVolume) ;

		} else if( strcmp(azColName[idx], "redundacy") == 0 ) {
			if( strcmp(argv[idx], "master") == 0 ) { 
				gtClientInfo.serverCnt = 1; 
			
			} else { 
				gtClientInfo.serverCnt = 2;
			}

		}  else if( strcmp(azColName[idx], "deviceName") == 0 ) {
			strcpy(gtClientInfo.deviceName, argv[idx] ? argv[idx] : "plughw");

		} else if( strcmp(azColName[idx], "castType") == 0 ) {
			strcpy(gtClientInfo.castType, argv[idx] ? argv[idx] : "unicast");

		} else {
			if( strcmp(operType, "change") == 0 ) {
				if( strcmp(azColName[idx], "change_unicast_ipAddr") == 0 ) {
					strcpy(gtClientInfo.ipAddr1, argv[idx] ? argv[idx] : "NULL");
					dbg_printf("ipAddr1 : %s\n",gtClientInfo.ipAddr1) ;
				
				} else if( strcmp(azColName[idx], "change_unicast_port") == 0 ) {
					gtClientInfo.port1 = atoi(argv[idx]);
					dbg_printf("port : %d\n",gtClientInfo.port1) ;
				
				} else if( strcmp(azColName[idx], "change_unicast_rep_ipAddr") == 0 ) {
					strcpy(gtClientInfo.ipAddr2, argv[idx] ? argv[idx] : "NULL");
					dbg_printf("ipAddr2 : %s\n",gtClientInfo.ipAddr2) ;
				
				} else if( strcmp(azColName[idx], "change_unicast_rep_port") == 0 ) {
					gtClientInfo.port2 = atoi(argv[idx]);
					dbg_printf("port2 : %d\n",gtClientInfo.port2) ;
				
				} else if( strcmp(azColName[idx], "change_multicast_ipAddr") == 0 ) {
					strcpy(gtClientInfo.mIpAddr, argv[idx] ? argv[idx] : "NULL");
					dbg_printf("mIpAddr : %s\n",gtClientInfo.mIpAddr) ;

				} else if( strcmp(azColName[idx], "change_multicast_port") == 0 ) {
					gtClientInfo.mPort = atoi(argv[idx]);
					dbg_printf("mPort : %d\n",gtClientInfo.mPort) ;
				}
				
			} else {
				if( strcmp(azColName[idx], "default_unicast_ipAddr") == 0 ) {
					strcpy(gtClientInfo.ipAddr1, argv[idx] ? argv[idx] : "NULL");
					dbg_printf("ipAddr1 : %s\n",gtClientInfo.ipAddr1) ;
				
				} else if( strcmp(azColName[idx], "default_unicast_port") == 0 ) {
					gtClientInfo.port1 = atoi(argv[idx]);
					dbg_printf("port : %d\n",gtClientInfo.port1) ;
				
				} else if( strcmp(azColName[idx], "default_unicast_rep_ipAddr") == 0 ) {
					strcpy(gtClientInfo.ipAddr2, argv[idx] ? argv[idx] : "NULL");
					dbg_printf("ipAddr2 : %s\n",gtClientInfo.ipAddr2) ;
				
				} else if( strcmp(azColName[idx], "default_unicast_rep_port") == 0 ) {
					gtClientInfo.port2 = atoi(argv[idx]);
					dbg_printf("port2 : %d\n",gtClientInfo.port2) ;
				
				} else if( strcmp(azColName[idx], "default_multicast_ipAddr") == 0 ) {
					strcpy(gtClientInfo.mIpAddr, argv[idx] ? argv[idx] : "NULL");
					dbg_printf("mIpAddr : %s\n",gtClientInfo.mIpAddr) ;
				
				} else if( strcmp(azColName[idx], "default_multicast_port") == 0 ) {
					gtClientInfo.mPort = atoi(argv[idx]);
					dbg_printf("mPort : %d\n",gtClientInfo.mPort) ;
				}
			}
		}
	}
	
	return 0;
}

int SqliteParser(char *_type) {
	sqlite3 *db;
	char *zErrMsg = 0;
	int  rc;
	char *sql;
	char tmp[100];
	const char* data = "Callback function called";

	/* Open database */
	rc = sqlite3_open(PATH_AUDIO_CONF_DB, &db) ;
	if( rc ) {
		dbg_printf("Can't open database: %s\n" , sqlite3_errmsg(db) );

		return 0;
		
	} else {
		dbg_printf("Opened database successfully\n") ;
	}
	/* Create SQL statement */
	//sql = "SELECT * from audio_client";

	sprintf(tmp, "SELECT * FROM %s", _type);
	dbg_printf("select %s\n",tmp);
	sql = tmp;

	/* Execute SQL statement */
	rc = sqlite3_exec(db, sql, SqliteCallBack, (void*) data, &zErrMsg);
	if( rc != SQLITE_OK ) {
		dbg_printf("SQL error: %s\n" , zErrMsg) ;
		sqlite3_free(zErrMsg) ;

		return 0;
		
	} else {
		dbg_printf("Operation done successfully\n") ;
	}

	sqlite3_close(db) ;
	
	return 1;
}

bool is_client_alive(void) {
	int writeFd, readFd;

	if( (writeFd = open(PATH_PIPE_WRITE, O_RDWR)) < 0 ) {
		dbg_printf("open failed [%s] [%02d] : %s\n", PATH_PIPE_WRITE, errno, strerror(errno));

		return false;
	}

	if( (readFd = open(PATH_PIPE_READ, O_RDWR)) < 0 ) {
		dbg_printf("open failed [%s] [%02d] : %s\n", PATH_PIPE_READ, errno, strerror(errno));

		close(writeFd);
		return false;
	}

	close(writeFd);
	close(readFd);

	return true;
}

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
	char	recvData[1024];
	
	PIPE_DATA_t     tPipeData;

	tPipeData.code 		  = _code;
	tPipeData.dataLength  = _dataLength;
	
	FILE *fp;
	char readMsg[1024];
	int	 readRc;
	memset(recvData, 0x00, sizeof(recvData));
	memset(readMsg,  0x00, sizeof(readMsg));
	
	switch( _code ) {
		case 0x00 : // init
			if( (writeFd = get_pipe_fd(PATH_PIPE_WRITE)) < 0 ) {
				rc = false;
				break;
			}
			write(writeFd, &tPipeData, sizeof(tPipeData));
			
			write(writeFd, (CLIENT_INFO_t *)_ptr, _dataLength);

			break;	

		case 0x01 : // run
       	case 0x02 : // stop
        case 0x03 : // getAliveStatus
			if( (writeFd = get_pipe_fd(PATH_PIPE_WRITE)) < 0 ) {
				rc = false;
				break;
			}
			
			if( (readFd = get_pipe_fd(PATH_PIPE_READ)) < 0 ) {
				rc = false;
				break;
			}
			write(writeFd, &tPipeData, sizeof(tPipeData));
			
           	read(readFd, &tPipeData, sizeof(tPipeData));
		    read(readFd, recvData,   tPipeData.dataLength);

		    rc = (recvData[0] == 1 ? true : false);

		    break;

		case 0x04 : // init run
			if( (writeFd = get_pipe_fd(PATH_PIPE_WRITE)) < 0 ) {
				return false;
			}
			write(writeFd, &tPipeData, sizeof(tPipeData));
			
			write(writeFd, (CLIENT_INFO_t *)_ptr, _dataLength);

			break;	

		case 0x10 : // getClientInfo
			if( (writeFd = get_pipe_fd(PATH_PIPE_WRITE)) < 0 ) {
				rc = false;
				break;
			}
			
			if( (readFd = get_pipe_fd(PATH_PIPE_READ)) < 0 ) {
				rc = false;
				break;
			}
			write(writeFd, &tPipeData, sizeof(tPipeData));

			read(readFd, &tPipeData, sizeof(tPipeData));
			read(readFd, _ptr,       tPipeData.dataLength);

			break;
			
		case 0x11 : // getVolumeInfo
			if( (fp = popen("ps -ef | grep audio_player | grep -v grep | wc -l", "r")) == NULL ) {
				dbg_printf("failed run command : grep audio_player\n");
		
				break;
			}

			fgets(readMsg, sizeof(readMsg), fp);
			readRc = atoi(readMsg);
			fclose(fp);
			
			if( readRc == 0 ) {
				if( (writeFd = get_pipe_fd(PATH_PIPE_WRITE)) < 0 ) {
					rc = false;
					break;
				}
				
				if( (readFd = get_pipe_fd(PATH_PIPE_READ)) < 0 ) {
					rc = false;
					break;
				}
			
			} else {
				if( (writeFd = get_pipe_fd(PATH_PIPE_WRITE_PLAYER)) < 0 ) {
					rc = false;
					break;
				}
				
				if( (readFd = get_pipe_fd(PATH_PIPE_READ_PLAYER)) < 0 ) {
					rc = false;
					break;
				}
			}
			write(writeFd, &tPipeData, sizeof(tPipeData));

			read(readFd, &tPipeData, sizeof(tPipeData));
			read(readFd, _ptr,       tPipeData.dataLength);

			break;

		case 0x20 : // setVolume
			if( (fp = popen("ps -ef | grep audio_player | grep -v grep | wc -l", "r")) == NULL ) {
				dbg_printf("failed run command : grep audio_player\n");
				break;
			}

			fgets(readMsg, sizeof(readMsg), fp);
			readRc = atoi(readMsg);
			fclose(fp);
			
			if( readRc == 0 ) {
				if( (writeFd = get_pipe_fd(PATH_PIPE_WRITE)) < 0 ) {
					rc = false;
					break;
				}
				
				if( (readFd = get_pipe_fd(PATH_PIPE_READ)) < 0 ) {
					rc = false;
					break;
				}

				write(writeFd, &tPipeData, sizeof(tPipeData));
				write(writeFd, _ptr, _dataLength);
			}

			break;	

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

int initRunAudioClient(void)
{
	if(SqliteParser((char *)"audio_client")){
		if( g_flag_init_run == 2 ) {
			SendData(0x00, sizeof(gtClientInfo), (char *)&gtClientInfo);
		}
	}
	
	return 1;
}	

int init_plugin(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);
	
	sem_init(&g_sem_lock_key, 0, 1);
	
	system("killall audio_client_monitor.sh");
	system("/opt/interm/public_html/modules/audio_setup/bin/audio_client_monitor.sh &");
	
	if( is_client_alive() != true ) {
		dbg_printf("Launch audio client!!\n");
	}
	
	initRunAudioClient();
	
    return 0;
}

int deinit_plugin(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);
	
	sem_destroy(&g_sem_lock_key);

	system("killall audio_client_monitor.sh");
	
    return 0;
}

// 0x00 : init 
int setInitAudioClient(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);
	
	CLIENT_SET_VALUE *csv = (CLIENT_SET_VALUE *)_args;

	CLIENT_INFO_t tClientInfo;

	tClientInfo.delay			= csv->delay;
	tClientInfo.delayMs			= csv->delayMs;
	tClientInfo.typeProtocol	= (csv->typeProtocol == 0 ? false : true);
	tClientInfo.serverCnt		= csv->serverCnt;
	tClientInfo.port1			= csv->port1;
	tClientInfo.port2			= csv->port2;
	tClientInfo.mPort			= csv->mPort;
	tClientInfo.playVolume		= csv->playVolume;
	
	strcpy(tClientInfo.castType,	csv->castType);
	strcpy(tClientInfo.ipAddr1, 	csv->ipAddr1);
	strcpy(tClientInfo.ipAddr2,		csv->ipAddr2);
	strcpy(tClientInfo.mIpAddr,		csv->mIpAddr);
	strcpy(tClientInfo.hostName,	csv->hostName);
	
	strcpy(tClientInfo.deviceName,	gtClientInfo.deviceName);

	return SendData(0x00, sizeof(tClientInfo), (char *)&tClientInfo);
}

// 0x01 : run
int setRunAudioClient(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);

	return SendData(0x01, 0, NULL);	
}

// 0x02 : stop
int setStopAudioClient(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);

	return SendData(0x02, 0, NULL);
}

// 0x04 : init run
int setInitRunAudioClient(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);
	
	CLIENT_SET_VALUE *csv = (CLIENT_SET_VALUE *)_args;

	CLIENT_INFO_t tClientInfo;

	tClientInfo.delay			= csv->delay;
	tClientInfo.delayMs			= csv->delayMs;
	tClientInfo.typeProtocol	= (csv->typeProtocol == 0 ? false : true);
	tClientInfo.serverCnt		= csv->serverCnt;
	tClientInfo.port1			= csv->port1;
	tClientInfo.port2			= csv->port2;
	tClientInfo.mPort			= csv->mPort;
	tClientInfo.playVolume		= csv->playVolume;
	
	strcpy(tClientInfo.castType,	csv->castType);
	strcpy(tClientInfo.ipAddr1,		csv->ipAddr1);
	strcpy(tClientInfo.ipAddr2, 	csv->ipAddr2);
	strcpy(tClientInfo.mIpAddr, 	csv->mIpAddr);
	strcpy(tClientInfo.hostName, 	csv->hostName);
	strcpy(tClientInfo.deviceName,	gtClientInfo.deviceName);
	
	return SendData(0x04, sizeof(tClientInfo), (char *)&tClientInfo);
}

// 0x03 : getAliveStatus, not used
int getAliveStatus(void *_args) {
	DATA_STT *json = (DATA_STT *)_args;
	
	json->data = (char *)malloc(4096);
	memset(json->data, 0x00, 4096);

	if( SendData(0x03, 0, NULL) ) {
		sprintf(json->data, "{\n\t\"AUDIO_CLIENT_STATUS\": \"ALIVE\"\n}\0");
	
	} else {
		sprintf(json->data, "{\n\t\"AUDIO_CLIENT_STATUS\": \"DEAD\"\n}\0");
	}
	
	free(json->data);
	
	return 0x4000;
}	

// 0x10 : getServerInfo, Data format : JSON, not used
int getClientInfo(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);

	DATA_STT *json = (DATA_STT *)_args;
	
	json->data = (char *)malloc(4096);
	memset(json->data, 0x00, 4096);

	if( SendData(0x10, 0, json->data) ) {
		dbg_printf("result: [%s]\n", json->data);
	}
	
	free(json->data);

	return 0x4000;
}

// 0x11 : getVolume, Data format : JSON, not used
int getVolume(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);

	DATA_STT *json = (DATA_STT *)_args;
	
	json->data = (char *)malloc(4096);
	memset(json->data, 0x00, 4096);

	
	if( SendData(0x11, 0, json->data) ) {
		dbg_printf("result: [%s]\n", json->data);
	}
	
	free(json->data);
	
	return 0x4000;
}

// 0x20 : setVolume
int setVolume(void *_args) {
	int rc;
	
	dbg_printf("%s\n", __FUNCTION__);

	CLIENT_VOL_VALUE *stVolume = (CLIENT_VOL_VALUE *)_args;
	rc = SendData(0x20, sizeof(int), (char *)&stVolume->volume);
	
	return rc;
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
