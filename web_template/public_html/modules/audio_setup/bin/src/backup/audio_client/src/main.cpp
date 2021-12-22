#include <stdio.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>
#include <sys/select.h>
#include <fcntl.h>
#include <stdlib.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <arpa/inet.h>
#include <netinet/in.h>
#include <netinet/tcp.h>
#include <sqlite3.h>

#include "muteRelay.cpp"
#include "common_class.h"
#include "mp3_dec_interface.h"

#define PATH_PIPE_WRITE                 "/tmp/pipe_audio_client_write"
#define PATH_PIPE_READ                  "/tmp/pipe_audio_client_read"
#define PATH_INFO_JSON_NETWORK 			"/opt/interm/public_html/modules/network_setup/conf/network_stat.json"
#define PATH_INFO_DB_AUDIO				"/opt/interm/public_html/modules/audio_setup/conf/audio_stat.db"

#define NUM_EXT_PORT                    2001
#define NUM_GATEWAY_PORT                2100
#define STR_CLIENT_IP_ADDR              "127.0.0.1"

#define ORDER_CMD_PROC_ALIVE            1
#define ORDER_CMD_AUDIO_INIT            10
#define ORDER_CMD_AUDIO_VOLUME_INFO     11
#define ORDER_CMD_AUDIO_LEVEL           12


using namespace Common;

struct PIPE_DATA {
	char    code;
	int     dataLength;
} typedef PIPE_DATA_t;

struct ORDER_PACKET {
	char    cmd;
	char    rsvd[3];
	int     bodyLen;
	char    *data;
} typedef ORDER_PACKET_t;

struct PROC_STATUS {
	char	view[128];
	char	act[128];
} typedef PROC_STATUS_t;

struct JSON_PARSER {
		char string[24];
} typedef JSON_PARSER_t;


bool gFlagInit  = false;
bool gFlagRun   = false;
bool gFlagStop  = false;
bool gFlagStat  = false;

bool g_is_amp_device = false;

PROC_STATUS_t	gtProcStatus;
JSON_PARSER_t 	*tJson;
CLIENT_INFO_t 	tClientInfo;
PIPE_DATA_t     tPipeData;
ClientFunc 		audioClient;

IPC_msgQueueFunc  gMsgQueueFunc;

bool IsAmpDevice(void) {
	bool    rc  = false;
	char    line[1024];
	FILE    *fp;

	if( (fp = fopen("/opt/interm/conf/env.json","r")) == NULL ) {
		fprintf(stderr, "env.json not found.\n");

		return rc;
	}

	while( fgets(line, 1024, fp) ) {
		if( strstr(line, "\"device_type\"") && strstr(line, "\"amp\"") ) {
			rc = true;
			break;
		}
	}

	fclose(fp);

	return rc;
}

// Network info json parse, read hostname 
void ParseNetworkInfo(void) {
	int 	tokenIndex = 0;   
	int 	pos = 0, fileSize = 0, stringLength = 0;
	char 	*begin, *end, *buffer;
	char 	hostname[80];
	
	FILE 	*fp;
	
	sprintf(tClientInfo.hostName, "hostName:%s", "-");
	
	if( (fp = fopen(PATH_INFO_JSON_NETWORK, "rb")) == NULL ) {
		fprintf(stderr,"ParseNetworkInfo() JSON open error [%s] : [%02d] %s\n", 
				PATH_INFO_JSON_NETWORK, errno, strerror(errno));
		
		return ;
	}

	fseek(fp, 0, SEEK_END);
	fileSize = ftell(fp);
	fseek(fp, 0, SEEK_SET);

	buffer = (char *)malloc(fileSize + 1);
	memset(buffer, 0x00, (fileSize + 1));

	if( fread(buffer, fileSize, 1, fp) < 0 ) {
		fileSize = 0;
		free(buffer);
		fclose(fp);
		
		fprintf(stderr,"ParseNetworkInfo() JSON read error [%s] : [%02d] %s\n", 
				PATH_INFO_JSON_NETWORK, errno, strerror(errno));
		
		free(buffer);
		return ;
	}

	fclose(fp);    

	if (buffer[pos] != '{') {
		
		free(buffer);
		return;
	}

	while( pos < fileSize ) {
		switch( buffer[pos] ) {
			case '"':
				begin = buffer + pos + 1;
				end = strchr(begin, '"');
					
				if( end == NULL ) {
					break;          
				}

				stringLength = end - begin; 
					
				if( tokenIndex == 3 ) {
					memset(hostname, 0x00, sizeof(hostname));
					memcpy(hostname, begin, stringLength);
					sprintf(tClientInfo.hostName, "hostName:%s", hostname);
					fprintf(stderr," - hostname : [%s]\n",tClientInfo.hostName);
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

	free(buffer);
	
	return ;
} // end of ParseNetworkInfo()


// sql callback function, query status, for websocket 
static int sql_callback_status(void *_notUsed, int _argc, char **_argv, char **_azColName) {
	int idx;

	for( idx = 0 ; idx < _argc ; idx++ ) {
		if( strcmp(_azColName[idx], "stat") == 0 ) {
			strcpy(gtProcStatus.view, _argv[idx] ? _argv[idx] : "NULL");

		} else if( strcmp(_azColName[idx], "actStat") == 0 ) {
			strcpy(gtProcStatus.act, _argv[idx] ? _argv[idx] : "NULL");
		}
	}
	
	return 0;
} // end of sql_callback_status()


// sql parse function, query status, for websocket
bool sql_parse_status(char *_type) {
	int  	rc;
	char 	queryMsg[1024];
	char	*query = queryMsg;
	char 	*zErrMsg = 0;
	const char* data = "Callback function called";
	sqlite3 *sqlHandler;

	if( (rc = sqlite3_open(PATH_INFO_DB_AUDIO, &sqlHandler)) ) {
		fprintf(stderr, "sql_parse_status() sqlite3_exec() open failed : %s\n" , sqlite3_errmsg(sqlHandler));

		return false;
	}
	
	sprintf(query, "SELECT * FROM %s", _type);
	if( (rc = sqlite3_exec(sqlHandler, query, sql_callback_status, (void *)data, &zErrMsg)) != SQLITE_OK ) {
		fprintf(stderr, "sql_parse_status() sqlite3_exec() exec error : %s\n" , zErrMsg);
		sqlite3_free(zErrMsg);

		return false;
	}

	sqlite3_close(sqlHandler);
	
	return true;
}


// sql callback function, query status, for process
static int sql_callback_process(void *_notUsed, int _argc, char **_argv, char **_azColName) {
	int idx;
	char use[24], stat[24], actStat[24], operType[24];

	for( idx = 0 ; idx < _argc ; idx++ ) {
		if( strcmp(_azColName[idx], "use") == 0 ) {
			strcpy(use, _argv[idx] ? _argv[idx] : "NULL");

		} else if( strcmp(_azColName[idx], "stat") == 0 ) {
			strcpy(stat, _argv[idx] ? _argv[idx] : "NULL");
			strcpy(gtProcStatus.view, _argv[idx] ? _argv[idx] : "NULL");

		} else if( strcmp(_azColName[idx], "actStat") == 0 ) {
			strcpy(actStat, _argv[idx] ? _argv[idx] : "NULL");
			strcpy(gtProcStatus.act, _argv[idx] ? _argv[idx] : "NULL");

		} else if( strcmp(_azColName[idx], "operType") == 0 ) {
			strcpy(operType, _argv[idx] ? _argv[idx] : "NULL");

		} else if( strcmp(_azColName[idx], "castType") == 0 ) {
			strcpy(tClientInfo.castType, _argv[idx] ? _argv[idx] : "unicast");
		
		} else if( strcmp(_azColName[idx], "protocol") == 0 ) {
			tClientInfo.typeProtocol = (strcmp(_argv[idx], "tcp") == 0 ? true : false);
		
		} else if( strcmp(_azColName[idx], "buffer_sec") == 0 ) {
			tClientInfo.delay = atoi(_argv[idx]);

		} else if( strcmp(_azColName[idx], "buffer_msec") == 0 ) {
			tClientInfo.delayMs = atoi(_argv[idx]);

		} else if( strcmp(_azColName[idx], "volume") == 0 ) {
			tClientInfo.playVolume= atoi(_argv[idx]);

		} else if( strcmp(_azColName[idx], "redundancy") == 0 ) {
			if(strcmp(_argv[idx], "master") == 0 ) { 
				tClientInfo.serverCnt = 1; 
			
			} else { 
				tClientInfo.serverCnt = 2;
			}
		
		} else if( strcmp(_azColName[idx], "deviceName") == 0 ) {
					strcpy(tClientInfo.deviceName, _argv[idx] ? _argv[idx] : "plughw");
		
		} else {
			if( strcmp(operType, "change") == 0 ) {
				if( strcmp(_azColName[idx], "change_unicast_ipAddr") == 0 ) {
					strcpy(tClientInfo.ipAddr1, _argv[idx] ? _argv[idx] : "NULL");

				} else if( strcmp(_azColName[idx], "change_unicast_port") == 0 ) {
					tClientInfo.port1 = atoi(_argv[idx]);

				} else if( strcmp(_azColName[idx], "change_unicast_rep_ipAddr") == 0 ) {
					strcpy(tClientInfo.ipAddr2, _argv[idx] ? _argv[idx] : "NULL");

				} else if( strcmp(_azColName[idx], "change_unicast_rep_port") == 0 ) {
					tClientInfo.port2 = atoi(_argv[idx]);

				} else if( strcmp(_azColName[idx], "change_multicast_ipAddr") == 0 ) {
					strcpy(tClientInfo.mIpAddr, _argv[idx] ? _argv[idx] : "NULL");

				} else if( strcmp(_azColName[idx], "change_multicast_port") == 0 ) {
					tClientInfo.mPort = atoi(_argv[idx]);
				}
				
			} else {
				if( strcmp(_azColName[idx], "default_unicast_ipAddr") == 0 ) {
					strcpy(tClientInfo.ipAddr1, _argv[idx] ? _argv[idx] : "NULL");

				} else if( strcmp(_azColName[idx], "default_unicast_port") == 0 ) {
					tClientInfo.port1 = atoi(_argv[idx]);

				} else if( strcmp(_azColName[idx], "default_unicast_rep_ipAddr") == 0 ) {
					strcpy(tClientInfo.ipAddr2, _argv[idx] ? _argv[idx] : "NULL");

					fprintf(stderr,"ipAddr2 : %s\n",tClientInfo.ipAddr2) ;
				} else if( strcmp(_azColName[idx], "default_unicast_rep_port") == 0 ) {
					tClientInfo.port2 = atoi(_argv[idx]);

				} else if( strcmp(_azColName[idx], "default_multicast_ipAddr") == 0 ) {
					strcpy(tClientInfo.mIpAddr, _argv[idx] ? _argv[idx] : "NULL");

				} else if( strcmp(_azColName[idx], "default_multicast_port") == 0 ) {
					tClientInfo.mPort = atoi(_argv[idx]);
				}
			}
		}
	}
	
	if( (strcmp(use, "enabled") == 0) && (strcmp(stat, "operation") == 0) && (strcmp(actStat, "run")) == 0 ) { 
		gFlagStat = true;
	
	} else {
		gFlagStat = false;
	}

	return 0;
} // end of sql_callback_process()


// sql parse function, query status, for process
bool sql_parse_process(char *_type) {
	int  	rc;
	char 	queryMsg[1024];
	char 	*query = queryMsg;
	char 	*zErrMsg = 0;
	const char* data = "Callback function called";
	sqlite3 *sqlHandler;

	if( (rc = sqlite3_open(PATH_INFO_DB_AUDIO, &sqlHandler)) ) {
		fprintf(stderr, "sql_parse_status() sqlite3_exec() open failed : %s\n" , sqlite3_errmsg(sqlHandler));

		return false;
	}
	
	sprintf(queryMsg, "SELECT * FROM %s", _type);
	if( (rc = sqlite3_exec(sqlHandler, query, sql_callback_process, (void *)data, &zErrMsg)) != SQLITE_OK ) {
		fprintf(stderr, "sql_parse_status() sqlite3_exec() exec error : %s\n" , zErrMsg);
		sqlite3_free(zErrMsg);

		return false;
	}

	sqlite3_close(sqlHandler);
	
	return true;
} // end of sql_parse_process()


// websocket function, get server alive
void GetClientAlive(char _cmd, int _socketFd) {
	char tmpMsg[128];
	ORDER_PACKET_t  tSendPacket;

	memset(&tSendPacket, 0x00, sizeof(tSendPacket));
	memset(&tmpMsg,      0x00, sizeof(tmpMsg));

	sprintf(tmpMsg, "{\"type\":\"%d\", \"data\":{\"stat\":\"%d\", \"view\":\"%s\", \"act\":\"%s\"}}", 
			_cmd, gFlagStop, gtProcStatus.view, gtProcStatus.act);

	if( (tSendPacket.data = (char *)malloc((strlen(tmpMsg) + 1) * sizeof(char))) == NULL ) {
		fprintf(stderr, "GetClientAlive() malloc() data failed : [%02d] %s\n", errno, strerror(errno));

		return ;
	}

	tSendPacket.cmd = _cmd;
	strcpy(tSendPacket.data, tmpMsg);
	tSendPacket.bodyLen = strlen(tSendPacket.data);
	tSendPacket.data[tSendPacket.bodyLen] = '\0';

	if( send(_socketFd, &tSendPacket, sizeof(tSendPacket) - sizeof(tSendPacket.data), MSG_DONTWAIT) < 0 ) {
		fprintf(stderr, "GetClientAlive() send() head failed : [%02d] %s\n", errno, strerror(errno));

		free(tSendPacket.data);
		return ;
	}

	if( send(_socketFd, tSendPacket.data, tSendPacket.bodyLen, MSG_DONTWAIT) < 0 ) {
		fprintf(stderr, "GetClientAlive() send() body failed : [%02d] %s\n", errno, strerror(errno));

		free(tSendPacket.data);
		return ;
	}

	free(tSendPacket.data);

	return ;
}


// websocket function, get client conn info
void GetClientInfo(char _cmd, int _socketFd) {
	char    tmpMsg[1024 * 8];
	char    *ptrMsg = NULL;

	ORDER_PACKET_t  tSendPacket;
	memset(&tSendPacket, 0x00, sizeof(tSendPacket));
	memset(&tmpMsg, 0x00, sizeof(tmpMsg));

	switch( _cmd ) {
		case ORDER_CMD_AUDIO_INIT :
			ptrMsg = audioClient.getClientInfo();
			break;

		case ORDER_CMD_AUDIO_VOLUME_INFO :
			ptrMsg = audioClient.getVolumeInfo();
			break;

		case ORDER_CMD_AUDIO_LEVEL :
			ptrMsg = audioClient.getLevelValue();
			break;

	}

	sprintf(tmpMsg, "{\"type\":\"%d\", \"data\":%s}", _cmd, ptrMsg);

	if( (tSendPacket.data = (char *)malloc((strlen(tmpMsg) + 1) * sizeof(char))) == NULL ) {
		fprintf(stderr, "GetClientInfo() malloc() data failed : [%02d] %s\n", errno, strerror(errno));

		return ;
	}

	tSendPacket.cmd = _cmd;
	strcpy(tSendPacket.data, tmpMsg);
	tSendPacket.bodyLen = strlen(tSendPacket.data);
	tSendPacket.data[tSendPacket.bodyLen] = '\0';
	if( send(_socketFd, &tSendPacket, sizeof(tSendPacket) - sizeof(tSendPacket.data), MSG_DONTWAIT) < 0 ) {
		fprintf(stderr, "GetClientInfo() send() head failed : [%02d] %s\n", errno, strerror(errno));

		free(tSendPacket.data);
		return ;
	}

	if( send(_socketFd, tSendPacket.data, tSendPacket.bodyLen, MSG_DONTWAIT) < 0 ) {
		fprintf(stderr, "GetClientInfo() send() body failed : [%02d] %s\n", errno, strerror(errno));

		free(tSendPacket.data);
		return ;
	}

	free(tSendPacket.data);

	return ;
} // end of GetClientInfo()


// websocket function, send to ws_router
bool SendToWebBlock(int _cmdId) {
	int     clientSockFd;
	int     extId = NUM_EXT_PORT;
	char    tmpMsg[128];

	struct sockaddr_in  tClientAddr;

	ORDER_PACKET_t  tSendPacket;

	memset(&tSendPacket, 0x00, sizeof(tSendPacket));
	memset(&tmpMsg,      0x00, sizeof(tmpMsg));

	if( (clientSockFd = socket(AF_INET, SOCK_STREAM, 0)) < 0 ) {
		fprintf(stderr, "SendToWebBlock() socket() failed : [%02d] %s\n", errno, strerror(errno));

		return false;
	}

	memset(&tClientAddr, 0x00, sizeof(tClientAddr));
	tClientAddr.sin_family     = AF_INET;
	tClientAddr.sin_port       = htons(NUM_GATEWAY_PORT);
	tClientAddr.sin_addr.s_addr= inet_addr(STR_CLIENT_IP_ADDR);

	if( connect(clientSockFd, (struct sockaddr*)&tClientAddr, sizeof(tClientAddr)) < 0 ) {
		fprintf(stderr, "SendToWebBlock() connect() failed : [%02d] %s\n", errno, strerror(errno));

		close(clientSockFd);
		return false;
	}

	if( send(clientSockFd, &extId, sizeof(extId), 0) < 0 ) {
		fprintf(stderr, "SendToWebBlock() send() port failed : [%02d] %s\n", errno, strerror(errno));

		close(clientSockFd);
		return false;
	}
	
	switch( _cmdId ) {
		case ORDER_CMD_PROC_ALIVE :
			GetClientAlive(ORDER_CMD_PROC_ALIVE, clientSockFd);
			break;

		case ORDER_CMD_AUDIO_INIT :
			GetClientInfo(ORDER_CMD_AUDIO_INIT, clientSockFd);
			break;

		case ORDER_CMD_AUDIO_VOLUME_INFO :
			GetClientInfo(ORDER_CMD_AUDIO_VOLUME_INFO, clientSockFd);
			break;

		case ORDER_CMD_AUDIO_LEVEL :
			GetClientInfo(ORDER_CMD_AUDIO_LEVEL, clientSockFd);
			break;
	}

	close(clientSockFd);

	return true;
} // end of SendToWebBlock()


// websocket function, get socket desc. 
int GetWebSocketFd(void) {
	int     sockFd;
	int     option = 1;
	int     optionLen = sizeof(option);

	struct  sockaddr_in cliAddr;

	if( (sockFd = socket(AF_INET, SOCK_STREAM, 0)) < 0 ) {
		fprintf(stderr, "GetWebSocketFd() failed : [%02d] %s\n", errno, strerror(errno));

		return -1;
	}

	cliAddr.sin_family      = AF_INET;
	cliAddr.sin_addr.s_addr = inet_addr("127.0.0.1");
	cliAddr.sin_port        = htons(NUM_EXT_PORT);

	if( setsockopt(sockFd, SOL_SOCKET, SO_REUSEADDR, &option, optionLen) < 0 ) {
		fprintf(stderr, "GetWebSocketFd() setsockopt() SO_REUSEADDR failed : [%02d]\n", errno);

		return -1;
	}

	if( bind(sockFd, (struct sockaddr *)&cliAddr, sizeof(cliAddr)) < 0 ) {
		fprintf(stderr, "GetWebSocketFd() bind() failed : [%02d] %s\n", errno, strerror(errno));
		close(sockFd);

		return -1;
	}

	listen(sockFd, 40);

	return sockFd;
}


// main function
int main(int _argc, char *_argv[]) {
	int     rc;
	int     writeFd, readFd;
	int 	webSocketFd;
	struct  timeval timeout;
	fd_set  fdReads;

	gMsgQueueFunc.init();

	/*
	remove(PATH_PIPE_WRITE);
	remove(PATH_PIPE_READ);

	umask(001);
	if( mkfifo(PATH_PIPE_WRITE, 0666) < 0 ) {
		if( errno != 17 ) {
			fprintf(stderr, "mkfifo [%s] [%02d] : %s\n", PATH_PIPE_WRITE, errno, strerror(errno));
		}
	}

	if( mkfifo(PATH_PIPE_READ, 0666) < 0 ) {
		if( errno != 17 ) {
			fprintf(stderr, "mkfifo [%s] [%02d] : %s\n", PATH_PIPE_READ, errno, strerror(errno));
		}
	}
	*/

	if( (writeFd = open(PATH_PIPE_WRITE, O_RDWR)) < 0 ) {
		fprintf(stderr, "open failed [%s] [%02d] : %s\n", PATH_PIPE_WRITE, errno, strerror(errno));

		return false;
	}

	if( (readFd = open(PATH_PIPE_READ, O_RDWR)) < 0 ) {
		fprintf(stderr, "open failed [%s] [%02d] : %s\n", PATH_PIPE_READ, errno, strerror(errno));

		return false;
	}
	
	/*
	   Data format
	   +-------------+-------------+-----------+
	   | Header Code | Data Length | Data      |
	   | (1 Byte)    | (4 Bytes)   | (N Bytes) |
	   +-------------+-------------+-----------+

	   Header Code (1 Byte)
	   +-------+----------------+------------------+------------+---------------------------------+
	   | Code  | Function       | Arguments        | Return     | Desc                            |
	   +-------+----------------+------------------+------------+---------------------------------+
	   | 0x00  | init           | SERVER_INFO_t    | -          | -                               |
	   | 0x01  | run            | -                | -          | -                               |
	   | 0x02  | stop           | -                | -          | -                               |
	   | 0x03  | getAliveStatus | -                | -          | -                               |
	   | 0x10  | getClientInfo  | -                | N Bytes    | -                               |
	   | 0x11  | getVolume      | 8 Bytes          | -          | -                               |
	   | 0x20  | setVolume      | 8 Bytes          | -          | [int _volume]                   |
	   +-------+----------------+------------------+------------+---------------------------------+

	 */

	int  volumeIdx = 0;
	char buffData[4096];

	if( (webSocketFd = GetWebSocketFd()) < 0 ) {
		return false;
	}

	int connSockFd;
	struct  sockaddr_in clientAddr;
	struct  linger      tLinger;
	struct  timeval tTimeo = {1, 0};

	socklen_t       clientLen;
	ORDER_PACKET_t  tRecvPacket;

	clientLen = sizeof(clientAddr);

	/* sqlite parser */
	if( sql_parse_process((char *)"audio_client") ) {
		if( gFlagStat ) {
			ParseNetworkInfo();

			SendToWebBlock(ORDER_CMD_PROC_ALIVE);
			SendToWebBlock(ORDER_CMD_AUDIO_LEVEL);

			audioClient.init(&tClientInfo);
			gFlagInit = true;

			fprintf(stderr, " - sql_parse_process() parser init \n");

			if( !gFlagRun  ) {
				if( !audioClient.run() ) {
					
					gFlagInit = false;
					gFlagStat = false; 
					
					gMsgQueueFunc.decCntAudioMute();

					audioClient.stop();
					
					SendToWebBlock(ORDER_CMD_PROC_ALIVE);
					SendToWebBlock(ORDER_CMD_AUDIO_LEVEL);

				} else {
					gFlagRun = true;
					gFlagStop = true;
					gFlagStat = false; 
					
					SendToWebBlock(ORDER_CMD_PROC_ALIVE);
					SendToWebBlock(ORDER_CMD_AUDIO_LEVEL);
					SendToWebBlock(ORDER_CMD_AUDIO_INIT);
				}

			} else {
				fprintf(stderr, "run() already running.\n");
			}
		} 	
	} else {

		fprintf(stderr, "Sqlite parser Error.\n");

		return false;
	}

	// amp device
	g_is_amp_device = IsAmpDevice();

	while( !common.getFlagTerm() ) {
		FD_ZERO(&fdReads);
		FD_SET(readFd, &fdReads);
		FD_SET(webSocketFd, &fdReads);

		timeout.tv_sec  = Common::TIMEOUT_ACCEPT_SEC;
		timeout.tv_usec = Common::TIMEOUT_ACCEPT_MSEC;

		if( common.getReconnFlag() ){
			SendToWebBlock(ORDER_CMD_AUDIO_INIT);
			common.setReconnFlag();
		}

		if( (rc = select(webSocketFd + 1, &fdReads, NULL, NULL, &timeout)) < 0 ) {
			switch( errno ) {
				case 4 :
					break;
				
				default :
					fprintf(stderr, "main() - select() failed : [%02d] %s\n", errno, strerror(errno));
					break;
			}

		} else if ( rc == 0 ) {
			// timeout
			SendToWebBlock(ORDER_CMD_PROC_ALIVE);
			SendToWebBlock(ORDER_CMD_AUDIO_LEVEL);

		} else {
			if( FD_ISSET(webSocketFd, &fdReads) ) {
				if( (connSockFd = accept(webSocketFd, (struct sockaddr *)&clientAddr, &clientLen)) < 0 ) {
					fprintf(stderr, "main() accept() failed : [%02d] %s\n", errno, strerror(errno));

					close(connSockFd);
					continue;
				}

				tLinger.l_onoff  = 1;
				tLinger.l_linger = 1;

				if( setsockopt(connSockFd, SOL_SOCKET, SO_LINGER, (char *)&tLinger, sizeof(tLinger)) < 0 ) {
					fprintf(stderr, "setsockopt() SO_LINGER failed : [%02d] %s\n", errno, strerror(errno));
				}

				if( setsockopt(connSockFd, SOL_SOCKET, SO_RCVTIMEO, &tTimeo, sizeof(tTimeo)) < 0 ) {
					fprintf(stderr, "setsockopt() SO_RCVTIMEO failed : [%02d] %s\n", errno, strerror(errno));
				}

				memset(&tRecvPacket, 0x00, sizeof(tRecvPacket));
				tRecvPacket.data = NULL;

				if( recv(connSockFd, &tRecvPacket, sizeof(tRecvPacket) - sizeof(tRecvPacket.data), MSG_WAITALL) < 0 ) {
					fprintf(stderr, "recv() headLen failed : [%02d] %s\n", errno, strerror(errno));

					close(connSockFd);
					continue;
				}
				// body data 수신 안함 (alive 요청만)
				switch( tRecvPacket.cmd ) {
					case ORDER_CMD_PROC_ALIVE :
						SendToWebBlock(ORDER_CMD_PROC_ALIVE);
						SendToWebBlock(ORDER_CMD_AUDIO_INIT);
						SendToWebBlock(ORDER_CMD_AUDIO_LEVEL);
						SendToWebBlock(ORDER_CMD_AUDIO_VOLUME_INFO);

						break;
				}

				close(connSockFd);
				continue;
			}

			read(readFd, &tPipeData, sizeof(tPipeData));
			fprintf(stderr, "Receive Code : [0x%02x][%d]\n", tPipeData.code, tPipeData.dataLength);

			memset(buffData, 0x00, sizeof(buffData));

			switch( tPipeData.code ) {
				case 0x00 : // init
					memset(&tClientInfo, 0x00, sizeof(tClientInfo));
					read(readFd, &tClientInfo, tPipeData.dataLength);

					audioClient.init(&tClientInfo);

					SendToWebBlock(ORDER_CMD_AUDIO_INIT);

					gFlagInit = true;

					fprintf(stderr, "init()ready.\n");
					break;

				case 0x01 :
					if( !gFlagInit ) {
						tPipeData.code       = tPipeData.code;
						tPipeData.dataLength = 1;
						buffData[0] = 0;

						write(writeFd, &tPipeData, sizeof(tPipeData));
						write(writeFd, buffData, tPipeData.dataLength);

						fprintf(stderr, "init() not ready.\n");
						break;
					}

					if( !gFlagRun  ) {
						if( !audioClient.run() ) {
							gFlagInit = false;
							gFlagStop = false;

							fprintf(stderr, "pipe stop()\n");
							sql_parse_status((char *)"audio_client");
							SendToWebBlock(ORDER_CMD_PROC_ALIVE);

							buffData[0] = 1;
							tPipeData.code       = 0x02;
							tPipeData.dataLength = 1;

							write(writeFd, &tPipeData, sizeof(tPipeData));
							write(writeFd, buffData, tPipeData.dataLength);
							
							gMsgQueueFunc.decCntAudioMute();
							
							audioClient.stop();
							
						} else {
							gFlagRun = true;
							gFlagStop = true;

							sql_parse_status((char *)"audio_client");
							SendToWebBlock(ORDER_CMD_PROC_ALIVE);
							SendToWebBlock(ORDER_CMD_AUDIO_INIT);
							
							buffData[0] = 1;
							fprintf(stderr, "pipe run() .\n");

							tPipeData.code       = tPipeData.code;
							tPipeData.dataLength = 1;

							write(writeFd, &tPipeData, sizeof(tPipeData));
							write(writeFd, buffData, tPipeData.dataLength);
						}

					} else {
						fprintf(stderr, "run() already running.\n");

						buffData[0] = 0;
						tPipeData.code       = tPipeData.code;
						tPipeData.dataLength = 1;

						write(writeFd, &tPipeData, sizeof(tPipeData));
						write(writeFd, buffData, tPipeData.dataLength);
					}
					break;

				case 0x02 : // stop
					if( gFlagStop ) {
						gFlagStop = false;
						gFlagInit = false;
						gFlagRun = false;

						gMsgQueueFunc.decCntAudioMute();
						
						// fprintf(stderr,"stop() mute delay : %d us\n", gMuteTime);
						// usleep(gMuteTime);

						audioClient.stop();

						buffData[0] = 1;
						
					} else {
						fprintf(stderr, "stop() is not running.\n");

						buffData[0] = 0;
					}

					tPipeData.code       = tPipeData.code;
					tPipeData.dataLength = 1;

					write(writeFd, &tPipeData, sizeof(tPipeData));
					write(writeFd, buffData, tPipeData.dataLength);

					break;

				case 0x03 : // getAliveStatus
					tPipeData.code       = tPipeData.code;
					tPipeData.dataLength = 1;
					buffData[0] = (gFlagStop == true ? 1 : 0);

					write(writeFd, &tPipeData, sizeof(tPipeData));
					write(writeFd, buffData, tPipeData.dataLength);

					break;

				case 0x04 : // init run
					memset(&tClientInfo, 0x00, sizeof(tClientInfo));
					read(readFd, &tClientInfo, tPipeData.dataLength);

					audioClient.init(&tClientInfo);
					gFlagInit = true;

					if( !gFlagRun  ) {
						if(audioClient.run() != true) {
							fprintf(stderr, "pipe stop()\n");
							
							gFlagInit = false;
							gFlagStat = false; 
							
							gMsgQueueFunc.decCntAudioMute();
							
							audioClient.stop();
							
						} else {
							gFlagRun = true;
							gFlagStop = true;
							gFlagStat = false; 
							
							SendToWebBlock(ORDER_CMD_AUDIO_INIT);
						}
					}
					
					sql_parse_status((char *)"audio_client");

					break;

				case 0x10 : // getClientInfo
				case 0x11 : // getVolume
					if( tPipeData.code == 0x10 ) {
						strcpy(buffData, audioClient.getClientInfo());

					} else if( tPipeData.code == 0x11 ) {
						strcpy(buffData, audioClient.getVolumeInfo());
					}

					tPipeData.code       = tPipeData.code;
					tPipeData.dataLength = strlen(buffData);

					write(writeFd, &tPipeData, sizeof(tPipeData));
					write(writeFd, buffData, tPipeData.dataLength);


					break;

				case 0x20 : //setVolume
					read(readFd, buffData, tPipeData.dataLength);
					memcpy(&volumeIdx, buffData, sizeof(int));

					rc = audioClient.setVolume(volumeIdx);

					SendToWebBlock(ORDER_CMD_AUDIO_VOLUME_INFO);

					// tPipeData.code       = tPipeData.code;
					// tPipeData.dataLength = 1;
					// buffData[0] = rc;

					// write(writeFd, &tPipeData, sizeof(tPipeData));
					// write(writeFd, buffData, tPipeData.dataLength);

					break;
			}
		}
	}
	
	gMsgQueueFunc.decCntAudioMute();
	
	gFlagStop = false;
	sql_parse_status((char *)"audio_client");
	SendToWebBlock(ORDER_CMD_PROC_ALIVE);

	close(readFd);
	close(writeFd);

	// remove(PATH_PIPE_READ);
	// remove(PATH_PIPE_WRITE);

	fprintf(stderr, "process termed\n");

	return 0;
}
