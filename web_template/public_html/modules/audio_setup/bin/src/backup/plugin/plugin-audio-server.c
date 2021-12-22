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

#include "plugin_types.h"
#include "plugin-audio-server.h"

#define FLAG_DEBUG_PRINT		false
#define PATH_DEBUG_FILE			"/tmp/debug_plugin-audio-server"


static	sem_t g_sem_lock_key;
bool	g_flag_init_run = 0;  // basic value

SERVER_INFO_t	gtServerInfo;


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


static int SqliteCallback(void *_notUsed, int _argc, char **_argv, char **_colName) {
	int 	idx;
	char 	use[24], stat[24], actStat[24], operType[24];

	for( idx = 0 ; idx < _argc ; idx++ ) {
		// dbg_printf("%s = %s\n", _colName[idx], _argv[idx] ? _argv[idx] : "NULL") ;

		if( strcmp(_colName[idx], "use") == 0 ) {
			strcpy(use, _argv[idx] ? _argv[idx] : "NULL");
			dbg_printf("use            : [%s]\n", use) ;

		} else if( strcmp(_colName[idx], "stat") == 0 ) {
			strcpy(stat, _argv[idx] ? _argv[idx] : "NULL");
			dbg_printf("stat           : [%s]\n", stat) ;

		} else if( strcmp(_colName[idx], "actStat") == 0 ) {
			strcpy(actStat, _argv[idx] ? _argv[idx] : "NULL");
			dbg_printf("actStat        : [%s]\n", actStat) ;
			
			if( strncmp(actStat, "run", 3) != 0 ) {
				// init state stop
				g_flag_init_run = 1;
			
			} else {
				// init state run
				g_flag_init_run = 2;
			}
			
		} else if( strcmp(_colName[idx], "castType") == 0 ) {
			strcpy(gtServerInfo.castType, _argv[idx] ? _argv[idx] : "unicast");
			dbg_printf("castType       : [%s]\n", gtServerInfo.castType) ;

		} else if( strcmp(_colName[idx], "protocol") == 0 ) {
			gtServerInfo.typeProtocol = (strcmp(_argv[idx], "tcp") == 0 ? true : false);
			dbg_printf("Protocol       : [%d]\n", gtServerInfo.typeProtocol) ;

		} else if( strcmp(_colName[idx], "encode") == 0 ) {
			gtServerInfo.mp3_mode = (strcmp(_argv[idx], "pcm") == 0 ? true : false);
			dbg_printf("mp3mode        : [%d]\n", gtServerInfo.mp3_mode) ;

		} else if( strcmp(_colName[idx], "pcm_sampleRate") == 0 ) {
			gtServerInfo.sampleRate = atoi(_argv[idx]);
			dbg_printf("sampleRate     : [%d]\n", gtServerInfo.sampleRate);

		} else if( strcmp(_colName[idx], "pcm_channels") == 0 ) {
			gtServerInfo.channels = atoi(_argv[idx]);
			dbg_printf("channels       : [%d]\n", gtServerInfo.channels);

		} else if( strcmp(_colName[idx], "pcm_chunkSize") == 0 ) {
			gtServerInfo.chunkSize = atoi(_argv[idx]);
			dbg_printf("chunkSize      : [%d]\n", gtServerInfo.chunkSize);

		} else if( strcmp(_colName[idx], "mp3_sampleRate") == 0 ) {
			gtServerInfo.mp3_sampleRate = atoi(_argv[idx]);
			dbg_printf("mp3_sampleRate : [%d]\n", gtServerInfo.mp3_sampleRate);

		} else if( strcmp(_colName[idx], "mp3_bitRate") == 0 ) {
			gtServerInfo.mp3_bitRate = atoi(_argv[idx]);
			dbg_printf("mp3_bitRate    : [%d]\n", gtServerInfo.mp3_bitRate);

		} else if( strcmp(_colName[idx], "mp3_chunkSize") == 0 ) {
			gtServerInfo.mp3_chunkSize = atoi(_argv[idx]);
			dbg_printf("mp3_chunkSize  : [%d]\n", gtServerInfo.mp3_chunkSize);

		} else if( strcmp(_colName[idx], "deviceName") == 0 ) {
			strcpy(gtServerInfo.deviceName, _argv[idx] ? _argv[idx] : "NULL");
			dbg_printf("deviceName     : [%s]\n", gtServerInfo.deviceName);

		} else if( strcmp(_colName[idx], "queueCnt") == 0 ) {
			gtServerInfo.queueCnt = atoi(_argv[idx]);
			dbg_printf("queueCnt       : [%d]\n", gtServerInfo.queueCnt);

		} else if( strcmp(_colName[idx], "clientCnt") == 0 ) {
			gtServerInfo.clientCnt = atoi(_argv[idx]);
			dbg_printf("clientCnt      : [%d]\n", gtServerInfo.clientCnt);

		} else if( strcmp(_colName[idx], "bufferRate") == 0 ) {
			gtServerInfo.bufferRate = atoi(_argv[idx]);
			dbg_printf("bufferRate     : [%d]\n", gtServerInfo.bufferRate);

		} else if( strcmp(_colName[idx], "playMode") == 0 ) {
			gtServerInfo.typePlayMode = (strcmp(_argv[idx], "pcm") == 0 ? true : false);
			dbg_printf("playMode       : [%d]\n", gtServerInfo.typePlayMode);

		} else if( strcmp(_colName[idx], "fileName") == 0 ) {
			strcpy(gtServerInfo.fileName, _argv[idx] ? _argv[idx] : "NULL");
			dbg_printf("fileName       : [%s]\n", gtServerInfo.fileName);

		} else if( strcmp(_colName[idx], "operType") == 0 ) {
			strcpy(operType, _argv[idx] ? _argv[idx] : "NULL");
			dbg_printf("operType       : [%s]\n", operType) ;

		} else {
			if( strcmp(operType, "change") == 0 ) {
				if( strcmp(_colName[idx], "change_ipAddr") == 0 ) {
					strcpy(gtServerInfo.ipAddr, _argv[idx] ? _argv[idx] : "NULL");
					dbg_printf("ipAddr         : [%s]\n", gtServerInfo.ipAddr) ;

				} else if( strcmp(_colName[idx], "change_port") == 0 ) {
					gtServerInfo.port = atoi(_argv[idx]);
					dbg_printf("port           : [%d]\n",gtServerInfo.port) ;
				}

			} else {
				if( strcmp(_colName[idx], "default_ipAddr") == 0 ) {
					strcpy(gtServerInfo.ipAddr, _argv[idx] ? _argv[idx] : "NULL");
					dbg_printf("ipAddr         : [%s]\n",gtServerInfo.ipAddr) ;

				} else if( strcmp(_colName[idx], "default_port") == 0 ) {
					gtServerInfo.port = atoi(_argv[idx]);
					dbg_printf("port           : [%d]\n",gtServerInfo.port) ;
				}
			}
		}
	}

	return 0;
} // end of SqliteCallback()


/*****************************
FUNC : SqliteParser()
DESC : sqllite function
 ******************************/
int SqliteParser(char *_type) {
	sqlite3		*dbConn;
	char 		*errMsg;
	char 		sqlMsg[1024];
	const char	*data = "Callback function called";

	/* Open database */
	if( sqlite3_open(PATH_AUDIO_CONF_DB, &dbConn) ) {
		dbg_printf("Can't open database : %s\n", sqlite3_errmsg(dbConn));

		return 0;

	} else {
		dbg_printf("Opened database successfully\n") ;
	}

	/* Create SQL statement */
	sprintf(sqlMsg, "SELECT * FROM %s", _type);

	/* Execute SQL statement */
	if( sqlite3_exec(dbConn, sqlMsg, SqliteCallback, (void*) data, &errMsg) != SQLITE_OK ) {
		dbg_printf("SQL error : %s\n" , errMsg);
		sqlite3_free(errMsg);

		return 0;

	} else {
		dbg_printf("Operation done successfully\n") ;
	}

	sqlite3_close(dbConn);

	return 1;
} // end of SqliteParser()

bool GetInstanceStatus(void) {
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

bool SendData(char _code, int _dataLength, char *_ptr) {
	bool	rc = true;
	int     writeFd, readFd;

	char	recvData[1024];

	if( (writeFd = open(PATH_PIPE_WRITE, O_RDWR)) < 0 ) {
		dbg_printf("open failed [%s] [%02d] : %s\n", PATH_PIPE_WRITE, errno, strerror(errno));

		return false;
	}

	if( (readFd = open(PATH_PIPE_READ, O_RDWR)) < 0 ) {
		dbg_printf("open failed [%s] [%02d] : %s\n", PATH_PIPE_READ, errno, strerror(errno));

		return false;
	}

	PIPE_DATA_t     tPipeData;

	tPipeData.code 		  = _code;
	tPipeData.dataLength  = _dataLength;

	write(writeFd, &tPipeData, sizeof(tPipeData));

	switch( _code ) {
		case 0x00 : // init
			write(writeFd, (SERVER_INFO_t *)_ptr, _dataLength);

			break;	

		case 0x01 : // run
        case 0x02 : // stop
        case 0x03 : // getAliveStatus
        	read(readFd, &tPipeData, sizeof(tPipeData));

        	memset(recvData, 0x00, sizeof(recvData));
	        read(readFd, recvData, tPipeData.dataLength);

		    if( recvData[0] == 1 ) {
		    	rc = true;
		    
		    } else {
		    	rc = false;
		    }
	
		    break;

		case 0x04 : // init run
			write(writeFd, (SERVER_INFO_t *)_ptr, _dataLength);

			break;	

		case 0x10 : // getServerInfo
		case 0x11 : // getClientList
			memset(_ptr, 0x00, 4096); 

			read(readFd, &tPipeData, sizeof(tPipeData));
			read(readFd, _ptr, tPipeData.dataLength);

			break;

		case 0x20 : // setStackIdx
		case 0x21 : // setPlayMode
			write(writeFd, _ptr, _dataLength);

			memset(recvData, 0x00, sizeof(recvData));
			read(readFd, recvData, tPipeData.dataLength);

		    if( recvData[0] == 1 ) {
		    	rc = true;

		    } else {
		    	rc = false;
		    }

		    break;	
	}

	close(readFd);
	close(writeFd);

	return rc;
}

int initRunAudioServer(void) {
	if( SqliteParser((char *)"audio_server") ) {
		if( g_flag_init_run == 2 ) {
			SendData(0x00, sizeof(gtServerInfo), (char *)&gtServerInfo);
		}
	}
	
	return 1;
}	

int init_plugin(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);
	
	sem_init(&g_sem_lock_key, 0, 1);
	
	system("killall audio_server_monitor.sh");
	system("/opt/interm/public_html/modules/audio_setup/bin/audio_server_monitor.sh &");


	if( GetInstanceStatus() != true ) {
		dbg_printf("Launch audio server!!\n");
	}
	
	initRunAudioServer();
	
    return 0;
}

int deinit_plugin(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);
	
	sem_destroy(&g_sem_lock_key);

	system("killall audio_server_monitor.sh");
	
    return 0;
}

int setInitAudioServer(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);

	SVR_SET_VALUE *ssv = (SVR_SET_VALUE *)_args;

	SERVER_INFO_t   tServerInfo;

	tServerInfo.queueCnt        = ssv->queueCnt;
	tServerInfo.bufferRate      = ssv->bufferRate;
	tServerInfo.chunkSize       = ssv->chunkSize;
	tServerInfo.sampleRate      = ssv->sampleRate;
	tServerInfo.channels        = ssv->channels;
	tServerInfo.mp3_chunkSize   = ssv->mp3_chunkSize; 
	tServerInfo.mp3_bitRate     = ssv->mp3_bitRate;
	tServerInfo.mp3_sampleRate  = ssv->mp3_sampleRate;
	tServerInfo.port            = ssv->port;
	tServerInfo.clientCnt       = ssv->clientCnt;				// Unicast: N, Multicast: 1
	tServerInfo.mp3_mode		= (ssv->mp3_mode == 0 ? false : true);
	tServerInfo.typeProtocol	= (ssv->typeProtocol == 0 ? false : true);
	tServerInfo.typePlayMode	= (ssv->typePlayMode == 0 ? false : true);
	strcpy(tServerInfo.castType,	(char *)(ssv->castType));	// unicast multicast 
	strcpy(tServerInfo.ipAddr,		(char *)(ssv->ipAddr));		// for multicast
	strcpy(tServerInfo.fileName, 	(char *)(ssv->fileName));   // file name
	strcpy(tServerInfo.deviceName, 	(char *)(ssv->deviceName)); // default, default1, default2

	return SendData(0x00, sizeof(tServerInfo), (char *)&tServerInfo);
}

// 0x01 : run
int setRunAudioServer(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);

	return SendData(0x01, 0, NULL);	
}

// 0x02 : stop
int setStopAudioServer(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);

	return SendData(0x02, 0, NULL);
}

int setInitRunAudioServer(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);
	
	SVR_SET_VALUE *ssv = (SVR_SET_VALUE *)_args;

	SERVER_INFO_t tServerInfo;

	tServerInfo.queueCnt        = ssv->queueCnt;
	tServerInfo.bufferRate      = ssv->bufferRate;
	tServerInfo.chunkSize       = ssv->chunkSize;
	tServerInfo.sampleRate      = ssv->sampleRate;
	tServerInfo.channels        = ssv->channels;
	tServerInfo.mp3_chunkSize   = ssv->mp3_chunkSize;
	tServerInfo.mp3_bitRate     = ssv->mp3_bitRate;
	tServerInfo.mp3_sampleRate  = ssv->mp3_sampleRate;
	tServerInfo.port            = ssv->port;
	tServerInfo.clientCnt       = ssv->clientCnt;				// Unicast: N, Multicast: 1
	tServerInfo.mp3_mode		= (ssv->mp3_mode == 0 ? false : true);
	tServerInfo.typeProtocol    = (ssv->typeProtocol == 0 ? false : true);
	tServerInfo.typePlayMode	= (ssv->typePlayMode == 0 ? false : true);
	
	strcpy(tServerInfo.castType, 	(char *)(ssv->castType));	//unicast multicast 
	strcpy(tServerInfo.ipAddr, 	 	(char *)(ssv->ipAddr));		// for multicast
	strcpy(tServerInfo.fileName, 	(char *)(ssv->fileName));	// file name
	strcpy(tServerInfo.deviceName,	(char *)(ssv->deviceName));	// default, default1, default2

	return SendData(0x04, sizeof(tServerInfo), (char *)&tServerInfo);
}

// 0x03 : getAliveStatus
int getAliveStatus(void *_args) {
	DATA_STT *json = (DATA_STT *)_args;
	
	json->data = (char *)malloc(4096);
	memset(json->data, 0x00, 4096);

	if( SendData(0x03, 0, NULL) ) {
		sprintf(json->data, "{\n\t\"AUDIO_SERVER_STATUS\": \"ALIVE\"\n}\0");
	
	} else {
		sprintf(json->data, "{\n\t\"AUDIO_SERVER_STATUS\": \"DEAD\"\n}\0");
	}
	
	free(json->data);
	
	return 0x4000;
}	

// 0x10 : getServerInfo(), Data format : JSON 
int getServerInfo(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);

	DATA_STT *json = (DATA_STT *)_args;
	
	json->data = (char *)malloc(4096);
	memset(json->data, 0x00, 4096);

	if( SendData(0x10, 0, json->data) ) {
		dbg_printf("[%s]\n", json->data);
	}

	free(json->data);
	
	return 0x4000;
}

// 0x11 : getClientList, Data format : JSON
int getClientList(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);

	DATA_STT *json = (DATA_STT *)_args;
	
	json->data = (char *)malloc(4096);
	memset(json->data, 0x00, 4096);

	if( SendData(0x11, 0, json->data) ) {
		dbg_printf("[%s]\n", json->data);
	}

	free(json->data);
	
	return 0x4000;
}

int setStackIdx(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);
	
	char buff[10];
	int offset;
	
	STACK_IDX *si = (STACK_IDX *)_args;
	
	dbg_printf("%d %d\n", si->threadIdx, si->queueIdx);
	
	memset(buff, 0x00, sizeof(buff));
	offset = 0;
	
	memcpy(buff, &(si->threadIdx), sizeof(int));
	offset += sizeof(int);

	memcpy(buff + offset, &(si->queueIdx), sizeof(int));
	offset += sizeof(int);

	return SendData(0x20, offset, buff);
}

// 0x21 : setPlayMode, PCM 재생 시 fileLength = 0 으로 설정
int setPlayMode(void *_args) {
	dbg_printf("%s\n", __FUNCTION__);

	int	fileLength = 0;
	int offset;
	char buff[16];

	PLAY_MODE *pm = (PLAY_MODE *)_args;

	dbg_printf("%d %s\n", pm->index, pm->fileName);

	offset = 0;
	fileLength = strlen(pm->fileName);

	memset(buff, 0x00, sizeof(buff));
	memcpy(buff + offset, &(pm->index), sizeof(int));
	offset += sizeof(int);

	if( fileLength != 0 ) {
		memcpy(buff + offset, &fileLength, sizeof(int));
		offset += sizeof(int);
	}

	memcpy(buff + offset, pm->fileName, fileLength);
	offset += fileLength;

	return SendData(0x21, offset, buff);	
}

