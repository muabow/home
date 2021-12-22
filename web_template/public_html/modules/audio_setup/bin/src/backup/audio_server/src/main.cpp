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

#include "common_class.h"

#define PATH_INFO_DB_AUDIO				"/opt/interm/public_html/modules/audio_setup/conf/audio_stat.db"
#define PATH_PIPE_WRITE					"/tmp/pipe_audio_server_write"
#define PATH_PIPE_READ					"/tmp/pipe_audio_server_read"

#define NUM_EXT_PORT					2000
#define NUM_GATEWAY_PORT				2100
#define STR_SERVER_IP_ADDR				"127.0.0.1"

#define ORDER_CMD_PROC_ALIVE            1
#define ORDER_CMD_AUDIO_INIT			10
#define ORDER_CMD_AUDIO_CONN_INFO       21


bool SendToWebBlock(int _cmdId);

using namespace Common;

struct PIPE_DATA {
	char	code;
	int		dataLength;
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

PROC_STATUS_t	gtProcStatus;
SERVER_INFO_t 	gtServerInfo;
ServerFunc 		gAudioServer;

bool gFlagInit  = false;
bool gFlagRun   = false;
bool gFlagStop  = false;
bool gFlagStat  = false;


// sql callback function, query status, for websocket
static int sql_callback_status(void *_notUsed, int _argc, char **_argv, char **_colName) {
	int 	idx;

	for( idx = 0 ; idx < _argc ; idx++ ) {
		if( strcmp(_colName[idx], "stat") == 0 ) {
			strcpy(gtProcStatus.view, _argv[idx] ? _argv[idx] : "NULL");

		} else if( strcmp(_colName[idx], "actStat") == 0 ) {
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
static int sql_callback_process(void *_notUsed, int _argc, char **_argv, char **_colName) {
	int 	idx;
	char 	use[24], stat[24], actStat[24], operType[24];

	for( idx = 0 ; idx < _argc ; idx++ ) {
		if( strcmp(_colName[idx], "use") == 0 ) {
			strcpy(use, _argv[idx] ? _argv[idx] : "NULL");

		} else if( strcmp(_colName[idx], "stat") == 0 ) {
			strcpy(stat, _argv[idx] ? _argv[idx] : "NULL");
			strcpy(gtProcStatus.view, _argv[idx] ? _argv[idx] : "NULL");

		} else if( strcmp(_colName[idx], "actStat") == 0 ) {
			strcpy(actStat, _argv[idx] ? _argv[idx] : "NULL");
			strcpy(gtProcStatus.act, _argv[idx] ? _argv[idx] : "NULL");

		} else if( strcmp(_colName[idx], "protocol") == 0 ) {
			gtServerInfo.typeProtocol = (strcmp(_argv[idx], "tcp") == 0 ? true : false);

		} else if( strcmp(_colName[idx], "castType") == 0 ) {
			strcpy(gtServerInfo.castType, _argv[idx] ? _argv[idx] : "unicast");

		} else if( strcmp(_colName[idx], "encode") == 0 ) {
			gtServerInfo.mp3_mode = (strcmp(_argv[idx], "pcm") == 0 ? false : true);

		} else if( strcmp(_colName[idx], "pcm_sampleRate") == 0 ) {
			gtServerInfo.sampleRate = atoi(_argv[idx]);

		} else if( strcmp(_colName[idx], "pcm_channels") == 0 ) {
			gtServerInfo.channels = atoi(_argv[idx]);

		} else if( strcmp(_colName[idx], "pcm_chunkSize") == 0 ) {
			gtServerInfo.chunkSize = atoi(_argv[idx]);

		} else if( strcmp(_colName[idx], "mp3_sampleRate") == 0 ) {
			gtServerInfo.mp3_sampleRate = atoi(_argv[idx]);

		} else if( strcmp(_colName[idx], "mp3_bitRate") == 0 ) {
			gtServerInfo.mp3_bitRate = atoi(_argv[idx]);

		} else if( strcmp(_colName[idx], "mp3_chunkSize") == 0 ) {
			gtServerInfo.mp3_chunkSize = atoi(_argv[idx]);

		} else if( strcmp(_colName[idx], "deviceName") == 0 ) {
			strcpy(gtServerInfo.deviceName, _argv[idx] ? _argv[idx] : "NULL");

		} else if( strcmp(_colName[idx], "queueCnt") == 0 ) {
			gtServerInfo.queueCnt = atoi(_argv[idx]);

		} else if( strcmp(_colName[idx], "clientCnt") == 0 ) {
			gtServerInfo.clientCnt = atoi(_argv[idx]);

		} else if( strcmp(_colName[idx], "bufferRate") == 0 ) {
			gtServerInfo.bufferRate = atoi(_argv[idx]);

		} else if( strcmp(_colName[idx], "playMode") == 0 ) {
			gtServerInfo.typePlayMode = (strcmp(_argv[idx], "pcm") == 0 ? false : true);

		} else if( strcmp(_colName[idx], "fileName") == 0 ) {
			strcpy(gtServerInfo.fileName, _argv[idx] ? _argv[idx] : "NULL");

		} else if( strcmp(_colName[idx], "operType") == 0 ) {
			strcpy(operType, _argv[idx] ? _argv[idx] : "NULL");

		} else {
			if( strcmp(operType, "change") == 0 ) {
				if( strcmp(_colName[idx], "change_ipAddr") == 0 ) {
					strcpy(gtServerInfo.ipAddr, _argv[idx] ? _argv[idx] : "NULL");

				} else if( strcmp(_colName[idx], "change_port") == 0 ) {
					gtServerInfo.port = atoi(_argv[idx]);
				}

			} else {
				if( strcmp(_colName[idx], "default_ipAddr") == 0 ) {
					strcpy(gtServerInfo.ipAddr, _argv[idx] ? _argv[idx] : "NULL");

				} else if( strcmp(_colName[idx], "default_port") == 0 ) {
					gtServerInfo.port = atoi(_argv[idx]);
				}
			}
		}
	}

	if( strcmp(use, "enabled") == 0 && strcmp(stat, "operation") == 0 && strcmp(actStat, "run") == 0 ) {
		gFlagStat  = true;

	} else {
		gFlagStat  = false;
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


/*****************************
FUNC : GetServerAlive()
DESC : Order function - get server alive
 ******************************/
void GetServerAlive(char _cmd, int _socketFd) {
	char tmpMsg[128];
	ORDER_PACKET_t  tSendPacket;

	memset(&tSendPacket, 0x00, sizeof(tSendPacket));
	memset(&tmpMsg, 	 0x00, sizeof(tmpMsg));

	sprintf(tmpMsg, "{\"type\":\"%d\", \"data\":{\"stat\":\"%d\", \"view\":\"%s\", \"act\":\"%s\"}}", 
			_cmd, gFlagStop, gtProcStatus.view, gtProcStatus.act);

	if( (tSendPacket.data = (char *)malloc((strlen(tmpMsg) + 1) * sizeof(char))) == NULL ) {
		fprintf(stderr, "GetServerAlive() malloc() data failed : [%02d] %s\n", errno, strerror(errno));

		return ;
	}

	tSendPacket.cmd = _cmd;
	strcpy(tSendPacket.data, tmpMsg);
	tSendPacket.bodyLen = strlen(tSendPacket.data);
	tSendPacket.data[tSendPacket.bodyLen] = '\0';

	if( send(_socketFd, &tSendPacket, sizeof(tSendPacket) - sizeof(tSendPacket.data), MSG_DONTWAIT) < 0 ) {
		fprintf(stderr, "GetServerAlive() send() head failed : [%02d] %s\n", errno, strerror(errno));

		free(tSendPacket.data);
		return ;
	}

	if( send(_socketFd, tSendPacket.data, tSendPacket.bodyLen, MSG_DONTWAIT) < 0 ) {
		fprintf(stderr, "GetServerAlive() send() body failed : [%02d] %s\n", errno, strerror(errno));

		free(tSendPacket.data);
		return ;
	}

	free(tSendPacket.data);

	return ;
} // end of GetServerAlive()

/*****************************
FUNC : GetServerInfo()
DESC : Order function - get client conn info
 ******************************/
void GetServerInfo(char _cmd, int _socketFd) {
	char    tmpMsg[1024 * 8];
	char	*ptrMsg = NULL;

	ORDER_PACKET_t  tSendPacket;
	memset(&tSendPacket, 0x00, sizeof(tSendPacket));
	memset(&tmpMsg, 0x00, sizeof(tmpMsg));

	switch( _cmd ) {
		case ORDER_CMD_AUDIO_INIT :
			ptrMsg = common.getServerInfo();
			break;

		case ORDER_CMD_AUDIO_CONN_INFO :
			ptrMsg = common.getClientList();
			break;
	}

	sprintf(tmpMsg, "{\"type\":\"%d\", \"data\":%s}", _cmd, ptrMsg);

	if( (tSendPacket.data = (char *)malloc((strlen(tmpMsg) + 1) * sizeof(char))) == NULL ) {
		fprintf(stderr, "GetServerInfo() malloc() data failed : [%02d] %s\n", errno, strerror(errno));

		return ;
	}

	tSendPacket.cmd = _cmd;
	strcpy(tSendPacket.data, tmpMsg);
	tSendPacket.bodyLen = strlen(tSendPacket.data);
	tSendPacket.data[tSendPacket.bodyLen] = '\0';

	if( send(_socketFd, &tSendPacket, sizeof(tSendPacket) - sizeof(tSendPacket.data), MSG_DONTWAIT) < 0 ) {
		fprintf(stderr, "GetServerInfo() send() head failed : [%02d] %s\n", errno, strerror(errno));

		free(tSendPacket.data);
		return ;
	}

	if( send(_socketFd, tSendPacket.data, tSendPacket.bodyLen, MSG_DONTWAIT) < 0 ) {
		fprintf(stderr, "GetServerInfo() send() body failed : [%02d] %s\n", errno, strerror(errno));

		free(tSendPacket.data);
		return ;
	}

	free(tSendPacket.data);

	return ;
} // end of GetServerInfo()


/*****************************
FUNC : SendToWebBlock()
DESC : Order function - send to web ib
 ******************************/
bool SendToWebBlock(int _cmdId) {
	int     clientSockFd;
	int		extId = NUM_EXT_PORT;
	char    tmpMsg[128];

	struct sockaddr_in  tServerAddr;

	ORDER_PACKET_t  tSendPacket;

	memset(&tSendPacket, 0x00, sizeof(tSendPacket));
	memset(&tmpMsg, 	 0x00, sizeof(tmpMsg));

	if( (clientSockFd = socket(AF_INET, SOCK_STREAM, 0)) < 0 ) {
		fprintf(stderr, "SendToWebBlock() socket() failed : [%02d] %s\n", errno, strerror(errno));

		return false;
	}

	memset(&tServerAddr, 0x00, sizeof(tServerAddr));
	tServerAddr.sin_family     = AF_INET;
	tServerAddr.sin_port       = htons(NUM_GATEWAY_PORT);
	tServerAddr.sin_addr.s_addr= inet_addr(STR_SERVER_IP_ADDR);

	if( connect(clientSockFd, (struct sockaddr*)&tServerAddr, sizeof(tServerAddr)) < 0 ) {
		fprintf(stderr, "SendToWebBlock() connect() failed : [%02d] %s\n", errno, strerror(errno));

		close(clientSockFd);
		return false;
	}

	if( send(clientSockFd, &extId, sizeof(extId), 0) < 0 ) {
		fprintf(stderr, "SendToWebBlock() send() port failed : [%02d] %s\n", errno, strerror(errno));

		close(clientSockFd);
		return false;
	}

	// writeLog.info(writeLog, "Send gateway : %d\n", _cmdId);
	switch( _cmdId ) {
		case ORDER_CMD_PROC_ALIVE :
			GetServerAlive(ORDER_CMD_PROC_ALIVE, clientSockFd);
			break;

		case ORDER_CMD_AUDIO_INIT :
			GetServerInfo(ORDER_CMD_AUDIO_INIT, clientSockFd);
			break;

		case ORDER_CMD_AUDIO_CONN_INFO :
			GetServerInfo(ORDER_CMD_AUDIO_CONN_INFO, clientSockFd);
			break;
	}

	close(clientSockFd);

	return true;
} // end of SendToWebBlock()


/*****************************
FUNC : SendClientList()
DESC : ConnList Send to Web
 ******************************/
void SendClientList(void) {
	SendToWebBlock(ORDER_CMD_AUDIO_CONN_INFO);	

	return ;
} // end of SendClientList()


/*****************************
FUNC : GetWebSocketFd()
DESC : get websocket server socket
 ******************************/
int GetWebSocketFd(void) {
	int     sockFd;
	int     option = 1;
	int     optionLen = sizeof(option);

	struct  sockaddr_in servAddr;

	if( (sockFd = socket(AF_INET, SOCK_STREAM, 0)) < 0 ) {
		fprintf(stderr, "GetWebSocketFd() failed : [%02d] %s\n", errno, strerror(errno));

		return -1;
	}

	servAddr.sin_family      = AF_INET;
	servAddr.sin_addr.s_addr = inet_addr("127.0.0.1");
	servAddr.sin_port        = htons(NUM_EXT_PORT);

	if( setsockopt(sockFd, SOL_SOCKET, SO_REUSEADDR, &option, optionLen) < 0 ) {
		fprintf(stderr, "GetWebSocketFd() setsockopt() SO_REUSEADDR failed : [%02d]\n", errno);

		return -1;
	}

	if( bind(sockFd, (struct sockaddr *)&servAddr, sizeof(servAddr)) < 0 ) {
		fprintf(stderr, "GetWebSocketFd() bind() failed : [%02d] %s\n", errno, strerror(errno));
		close(sockFd);

		return -1;
	}

	listen(sockFd, 40);

	return sockFd;
} // end of GetWebSocketFd()


/*****************************
FUNC : main()
DESC : main function
 ******************************/
int main(int _argc, char *_argv[]) {
	int		rc;
	int		writeFd, readFd;
	int		webSocketFd;
	struct	timeval	timeout;
	fd_set  fdReads;

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

	if( (writeFd = open(PATH_PIPE_WRITE, O_RDWR)) < 0 ) {
		fprintf(stderr, "open failed [%s] [%02d] : %s\n", PATH_PIPE_WRITE, errno, strerror(errno));

		return 0;
	}

	if( (readFd = open(PATH_PIPE_READ, O_RDWR)) < 0 ) {
		fprintf(stderr, "open failed [%s] [%02d] : %s\n", PATH_PIPE_READ, errno, strerror(errno));

		return 0;
	}

	if( sql_parse_process((char *)"audio_server") ) {
		if( gFlagStat == true ) {
			gAudioServer.init(&gtServerInfo);
			SendToWebBlock(ORDER_CMD_AUDIO_INIT);
			gFlagInit = true;

			if( !gFlagRun ) {
				gAudioServer.run();

				gFlagRun = true;
				gFlagStop = true;

				SendToWebBlock(ORDER_CMD_PROC_ALIVE);
				SendToWebBlock(ORDER_CMD_AUDIO_INIT);

			} else {
				fprintf(stderr, "run() already running.\n");
			}
		}
	}

	PIPE_DATA_t		tPipeData;

	/*
	   Data format
	   +-------------+-------------+-----------+
	   | Header Code | Data Length | Data      |
	   | (1 Byte)    | (4 Bytes)   | (N Bytes) |
	   +-------------+-------------+-----------+

	   Header Code (1 Byte)
	   +-------+----------------+------------------+------------+---------------------------------+
	   | Code  | Function		| Arguments		   | Return	    | Desc                            |
	   +-------+----------------+------------------+------------+---------------------------------+
	   | 0x00  | init		    | SERVER_INFO_t	   | -			| -                               |
	   | 0x01  | run		    | -			       | -			| -                               |
	   | 0x02  | stop 			| -		           | -			| -                               |
	   | 0x03  | getAliveStatus | -		           | -			| -                               |
	   | 0x04  | init run 		| -		           | -			| -                               |
	   | 0x10  | getServerInfo	| -				   | N Bytes	| -                               |
	   | 0x11  | getClientList	| -				   | N Bytes	| -                               |
	   | 0x20  | setStackIdx    | 8 Bytes 		   | -			| [int threadIdx, int queueIdx]   |
	   | 0x21  | setPlayMode    | N Bytes 		   | -			| [int index, char *fileName]     |
	   +-------+----------------+------------------+------------+---------------------------------+

	// method : getClientList();
	char *clientList = gAudioServer.getClientList();

	// method : setStackIdx(threadIndex, queueIndex)	
	gAudioServer.setStackIdx(0, 0);	// [0]thread -> [0] queue
	gAudioServer.setStackIdx(0, 1);	// [0]thread -> [1] queue
	gAudioServer.setStackIdx(1, 1);	// [1]thread -> [1] queue

	// method : setPlayMode(index, fileName);
	gAudioServer.setPlayMode(0, (char *)"sample_2.wav");	// File read
	gAudioServer.setPlayMode(0, NULL);					// PCM  read
	 */

	int	 buffLen   = 0;
	int	 queueIdx  = 0;
	int	 threadIdx = 0;
	int	 playIndex = 0;
	int	 fileLen   = 0;

	char fileName[128];
	char buffData[4096];

	if( (webSocketFd = GetWebSocketFd()) < 0 ) {
		return 0;
	}

	int		connSockFd;
	struct  sockaddr_in clientAddr;
	struct  linger  	tLinger;
	struct  timeval tTimeo = {1, 0};

	socklen_t       clientLen;
	ORDER_PACKET_t  tRecvPacket;

	clientLen = sizeof(clientAddr);

	while( !common.getFlagTerm() ) {
		FD_ZERO(&fdReads);
		FD_SET(readFd, &fdReads);
		FD_SET(webSocketFd, &fdReads);

		timeout.tv_sec  = Common::TIMEOUT_ACCEPT_SEC;
		timeout.tv_usec = Common::TIMEOUT_ACCEPT_MSEC;

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
						SendToWebBlock(ORDER_CMD_AUDIO_CONN_INFO);

						break;
				}

				close(connSockFd);
				continue;
			}

			read(readFd, &tPipeData, sizeof(tPipeData));
			fprintf(stderr, "Receive Code : [0x%02x][%d]\n", tPipeData.code, tPipeData.dataLength);

			memset(buffData, 0x00, sizeof(buffData));
			buffLen = 0;

			switch( tPipeData.code ) {
				case 0x00 :	// init
					memset(&gtServerInfo, 0x00, sizeof(gtServerInfo));
					read(readFd, &gtServerInfo, tPipeData.dataLength);

					gAudioServer.init(&gtServerInfo);

					SendToWebBlock(ORDER_CMD_AUDIO_INIT);	

					gFlagInit = true;

					break;

				case 0x01 : // run
					if( !gFlagInit ) {
						tPipeData.code 	 	 = tPipeData.code;
						tPipeData.dataLength = 1;
						buffData[0] = 0;

						write(writeFd, &tPipeData, sizeof(tPipeData));
						write(writeFd, buffData, tPipeData.dataLength);

						fprintf(stderr, "init() not ready.\n");
						break;
					}

					if( !gFlagRun ) {
						gAudioServer.run();

						gFlagRun  = true;
						gFlagStop = true;

						sql_parse_status((char *)"audio_server");
						SendToWebBlock(ORDER_CMD_PROC_ALIVE);	
						SendToWebBlock(ORDER_CMD_AUDIO_INIT);	
						SendToWebBlock(ORDER_CMD_AUDIO_CONN_INFO);

						buffData[0] = 1;

					} else {
						fprintf(stderr, "run() already running.\n");

						buffData[0] = 0;
					}
					tPipeData.code 	 	 = tPipeData.code;
					tPipeData.dataLength = 1;

					write(writeFd, &tPipeData, sizeof(tPipeData));
					write(writeFd, buffData, tPipeData.dataLength);

					break;

				case 0x02 : // stop
					if( gFlagStop ) {
						gAudioServer.stop();

						gFlagStop = false;
						gFlagInit = false;

						buffData[0] = 1;

					} else {
						fprintf(stderr, "stop() is not running.\n");

						buffData[0] = 0;
					}

					tPipeData.code 	 	 = tPipeData.code;
					tPipeData.dataLength = 1;

					write(writeFd, &tPipeData, sizeof(tPipeData));
					write(writeFd, buffData, tPipeData.dataLength);

					break;

				case 0x03 : // getAliveStatus
					tPipeData.code 	 	 = tPipeData.code;
					tPipeData.dataLength = 1;
					buffData[0] = (gFlagStop == true ? 1 : 0) ;

					write(writeFd, &tPipeData, sizeof(tPipeData));
					write(writeFd, buffData, tPipeData.dataLength);

					break;
				
				case 0x04 : // init run
					memset(&gtServerInfo, 0x00, sizeof(gtServerInfo));
					read(readFd, &gtServerInfo, tPipeData.dataLength);

					gAudioServer.init(&gtServerInfo);

					SendToWebBlock(ORDER_CMD_AUDIO_INIT);	

					gFlagInit = true;

					gAudioServer.run();

					gFlagRun  = true;
					gFlagStop = true;

					sql_parse_status((char *)"audio_server");
					SendToWebBlock(ORDER_CMD_PROC_ALIVE);	
					SendToWebBlock(ORDER_CMD_AUDIO_INIT);	
					SendToWebBlock(ORDER_CMD_AUDIO_CONN_INFO);

					buffData[0] = 1;
					tPipeData.code 	 	 = tPipeData.code;
					tPipeData.dataLength = 1;

					write(writeFd, &tPipeData, sizeof(tPipeData));
					write(writeFd, buffData, tPipeData.dataLength);

					break;

				case 0x10 : // getServerInfo
				case 0x11 : // getClientList
					if( tPipeData.code == 0x10 ) {
						strcpy(buffData, common.getServerInfo());

					} else if( tPipeData.code == 0x11 ) {
						strcpy(buffData, common.getClientList());
					}

					tPipeData.code 	 	 = tPipeData.code;
					tPipeData.dataLength = strlen(buffData);

					write(writeFd, &tPipeData, sizeof(tPipeData));
					write(writeFd, buffData, tPipeData.dataLength);

					break;

				case 0x20 : //setStackIdx 
					read(readFd, buffData, tPipeData.dataLength);
					memcpy(&threadIdx, buffData, sizeof(int));
					buffLen += sizeof(int);

					memcpy(&queueIdx, buffData + buffLen, sizeof(int));

					rc = gAudioServer.setStackIdx(threadIdx, queueIdx);

					tPipeData.code 	 	 = tPipeData.code;
					tPipeData.dataLength = 1;
					buffData[0] = rc;

					write(writeFd, &tPipeData, sizeof(tPipeData));
					write(writeFd, buffData, tPipeData.dataLength);

					break;

				case 0x21 : //setPlayMode
					read(readFd, buffData, tPipeData.dataLength);
					memcpy(&playIndex, buffData, sizeof(int));
					buffLen += sizeof(int);

					memcpy(&fileLen, buffData + buffLen, sizeof(int));
					buffLen += sizeof(int);

					memcpy(fileName, buffData + buffLen, fileLen);

					if( fileLen == 0 ) {
						rc = gAudioServer.setPlayMode(playIndex, NULL);

					} else {
						rc = gAudioServer.setPlayMode(playIndex, fileName);
					}

					tPipeData.code 	 	 = tPipeData.code;
					tPipeData.dataLength = 1;
					buffData[0] = rc;

					write(writeFd, &tPipeData, sizeof(tPipeData));
					write(writeFd, buffData, tPipeData.dataLength);



					break;
			}
		}
	}

	gFlagStop = false;

	sql_parse_status((char *)"audio_server");
	SendToWebBlock(ORDER_CMD_PROC_ALIVE);	

	close(readFd);
	close(writeFd);

	remove(PATH_PIPE_READ);
	remove(PATH_PIPE_WRITE);

	fprintf(stderr, "process termed\n");

	return 0;
} // end of main()
