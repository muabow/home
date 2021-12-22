#include <alsa/asoundlib.h>
#include <stdio.h>
#include <math.h>
#include <errno.h>
#include <signal.h>
#include <dirent.h>
#include <unistd.h>
#include <netinet/tcp.h>
#include <netinet/in.h>
#include <stdlib.h>
#include <arpa/inet.h>
#include <pthread.h>
#include <sqlite3.h>
#include <sys/stat.h>

#include "muteRelay.cpp"
#include "audio.h"

#define NUM_EXT_PORT                    2010
#define NUM_GATEWAY_PORT                2100

#define STR_DEVICE_NAME					"default"
#define STR_CLIENT_IP_ADDR              "127.0.0.1"

#define ORDER_CMD_PROC_ALIVE            1
#define ORDER_CMD_AUDIO_INFO            10
#define ORDER_CMD_AUDIO_VOLUME_INFO     11
#define ORDER_CMD_AUDIO_LEVEL           12

#define TIMEOUT_ACCEPT_SEC 				2
#define TIMEOUT_ACCEPT_MSEC 			0

#define PATH_PIPE_WRITE                 "/tmp/pipe_audio_player_write"
#define PATH_PIPE_READ                  "/tmp/pipe_audio_player_read"
#define PATH_AUDIO_DB_FILE				"/opt/interm/public_html/modules/source_file_management/conf/audio_player.db"


struct ORDER_PACKET {
	char    cmd;
	char    rsvd[3];
	int     bodyLen;
	char    *data;
} typedef ORDER_PACKET_t;

struct PIPE_DATA {
	char    code;
	int     dataLength;
} typedef PIPE_DATA_t;

struct PLAYER_DB {
	char    state[24];
	char    ftype[24];
	char    fname[256];
	int     replayCnt;
	int     volume;
	char	deviceName[256];
} typedef PLAYER_DB_t;

//
// global variables
//
// char 	gPcmData[2304];
short	gPcmData[2304];
char	gDeviceName[80];

bool	gThreadExt = false;
bool	gFlagTerm  = false;
bool	gMainTerm  = false;
bool	gDirFlag = false;

int	 	gPlayCnt  = 0;	// set play count (option)
int  	gFileCnt  = 0, gTmpCnt = 0;

int  	connSockFd;
char 	gTmpFileName[700][255];
	

//thread 		thread_recv_order;
pthread_t   thread_recv_order;
pthread_t   thread_pipe_order;
snd_pcm_t	*gPlaybackHandle = NULL;

PIPE_DATA_t	tPipeData;
PLAYER_DB_t tPlayerData;

IPC_msgQueueFunc 	gMsgQueueFunc;

bool	g_is_amp_device = false;
//
// functions
//
//
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

static int SQL_sqliteCallback(void *_notUsed, int _argc, char **_argv, char **_azColName) {
	int idx;

	for( idx = 0 ; idx < _argc ; idx++ ) {

		if( strcmp(_azColName[idx], "state") == 0 ) {
			strcpy(tPlayerData.state, _argv[idx] );

		} else if( strcmp(_azColName[idx], "ftype") == 0 ) {
			strcpy(tPlayerData.ftype, _argv[idx]);
		
		} else if( strcmp(_azColName[idx], "fname") == 0 ) {
			strcpy(tPlayerData.fname, _argv[idx]);

		} else if( strcmp(_azColName[idx], "replayCnt") == 0 ) {
			tPlayerData.replayCnt = atoi(_argv[idx]);

		} else if( strcmp(_azColName[idx], "volume") == 0 ) {
			tPlayerData.volume = atoi(_argv[idx]);

		} else if( strcmp(_azColName[idx], "deviceName") == 0 ) {
			strcpy(tPlayerData.deviceName, _argv[idx]);
		}
	}

	return E_SUCCESS;
} // end of SQL_sqliteCallback()

int SQL_sqliteParser(void) {
	sqlite3 *dbConn;
	char 		*zErrMsg = 0;
	const char	*data    = "Callback function called";

	/* Open database */
	if( sqlite3_open(PATH_AUDIO_DB_FILE, &dbConn) ) {
		printf("Can't open database : %s\n" , sqlite3_errmsg(dbConn));

		return E_ERROR;

	} else {
		printf("Opened database successfully \n");
	}

	/* Execute SQL statement */
	if( sqlite3_exec(dbConn, "select * from audio_player", SQL_sqliteCallback, (void*)data, &zErrMsg) != SQLITE_OK ) {
		printf("SQL error : %s\n", zErrMsg);
		sqlite3_free(zErrMsg);

		return E_ERROR;

	} else {
		printf("Operation done successfully\n") ;
	}

	if( tPlayerData.state == NULL) {
		sqlite3_close(dbConn);

		return E_ERROR;
	}

	sqlite3_close(dbConn);

	return E_SUCCESS;
} // end of SQL_sqliteParser()

int GetLevelValue(void) {
	int     volVal, rc, dcVal;
	int     valLevel = 10;
	int		pcmSize = sizeof(gPcmData);

	if( gPcmData == NULL ) return 0;
	
	for( int idx = 0 ; idx < 2 ; idx++ ) {
			gPcmData[idx] = (int32_t)gPcmData[idx] * tPlayerData.volume / 500;
	}
	if( (int)gPcmData[0] == 0 && tPlayerData.volume != 0 ) {
		volVal = (int)gPcmData[1];

	} else {
		volVal = (int)gPcmData[0];
	}

	rc = volVal > 0 ? volVal : volVal * -1;
	
	if( rc > 1000 ) dcVal = 1;
	else {
		switch( rc ) {
			case 600 ... 1000 : dcVal = 2; break;
			case 500 ... 599  : dcVal = 3; break;
			case 400 ... 499  : dcVal = 4; break;
			case 300 ... 399  : dcVal = 5; break;
			case 200 ... 299  : dcVal = 6; break;
			case 150 ... 199  : dcVal = 7; break;
			case 50 ... 149   : dcVal = 8; break;
			case 16 ... 49    : dcVal = 9; break;
			default : dcVal = 10;
		}
	}
	valLevel -= dcVal;

	return valLevel;
} // end of GetLevelValue()

void GetAudioAlive(char _cmd, int _socketFd) {
	char tmpMsg[128];
	ORDER_PACKET_t  tSendPacket;

	memset(&tSendPacket, 0x00, sizeof(tSendPacket));
	memset(&tmpMsg,      0x00, sizeof(tmpMsg));

	sprintf(tmpMsg, "{\"type\":\"%d\", \"data\":{\"stat\":\"%d\"}}", _cmd, (gFlagTerm == true ? 1 : 0));

	if( (tSendPacket.data = (char *)malloc((strlen(tmpMsg) + 1) * sizeof(char))) == NULL ) {
		printf("GetAudioAlive() malloc() data failed : [%02d] %s\n", errno, strerror(errno));

		return ;
	}

	tSendPacket.cmd = _cmd;
	strcpy(tSendPacket.data, tmpMsg);
	tSendPacket.bodyLen = strlen(tSendPacket.data);
	tSendPacket.data[tSendPacket.bodyLen] = '\0';

	if( send(_socketFd, &tSendPacket, sizeof(tSendPacket) - sizeof(tSendPacket.data), MSG_DONTWAIT) < 0 ) {
		printf("GetAudioAlive() send() head failed : [%02d] %s\n", errno, strerror(errno));

		free(tSendPacket.data);
		return ;
	}

	if( send(_socketFd, tSendPacket.data, tSendPacket.bodyLen, MSG_DONTWAIT) < 0 ) {
		printf("GetAudioAlive() send() body failed : [%02d] %s\n", errno, strerror(errno));

		free(tSendPacket.data);
		return ;
	}

	free(tSendPacket.data);
 
	return ;
} // end of GetAudioAlive()

void GetAudioInfo(char _cmd, int _socketFd) {
	char    tmpMsg[1024];
	char    msg[512];
	int		type;
	
	ORDER_PACKET_t  tSendPacket;
	memset(&tSendPacket, 0x00, sizeof(tSendPacket));
	memset(tmpMsg, 0x00, sizeof(tmpMsg));
	memset(msg, 0x00, sizeof(msg));

	switch( _cmd ) {
		case ORDER_CMD_AUDIO_VOLUME_INFO :
			sprintf(msg, "{\"type\": \"audio_player\", \"playVolume\": \"%d\"}", tPlayerData.volume);
			break;

		case ORDER_CMD_AUDIO_LEVEL :
			if( tPlayerData.volume == 0) {
				sprintf(msg, "{\"level\": \"%d\"}",0 );
			
			} else {
				sprintf(msg, "{\"level\": \"%d\"}", (gFlagTerm == false ? 0 : GetLevelValue()));
			
			}
			break;

		case ORDER_CMD_AUDIO_INFO:
			if(gDirFlag) {
			//if( strcmp(tPlayerData.ftype, "dir") == 0 ) {
				strcpy(tPlayerData.fname, gTmpFileName[gFileCnt]);
				fprintf(stderr, "%s",tPlayerData.fname);
			}

			if( strcmp(tPlayerData.ftype, "file") == 0) {
				type = 1;
			} else {
				type = 0;
			}

			sprintf(msg, "{\"state\": \"%s\", \"ftype\": \"%d\", \"fname\": \"%s\", \"replayCnt\": \"%d\", \"playVolume\": \"%d\"}", tPlayerData.state, type, tPlayerData.fname, tPlayerData.replayCnt, tPlayerData.volume);
			break;
	}

	sprintf(tmpMsg, "{\"type\":\"%d\", \"data\":%s}", _cmd, msg);

	if( (tSendPacket.data = (char *)malloc((strlen(tmpMsg) + 1) * sizeof(char))) == NULL ) {
		printf("GetAudioInfo() malloc() data failed : [%02d] %s\n", errno, strerror(errno));

		return ;
	}

	tSendPacket.cmd = _cmd;
	strcpy(tSendPacket.data, tmpMsg);
	tSendPacket.bodyLen = strlen(tSendPacket.data);
	tSendPacket.data[tSendPacket.bodyLen] = '\0';
	if( send(_socketFd, &tSendPacket, sizeof(tSendPacket) - sizeof(tSendPacket.data), MSG_DONTWAIT) < 0 ) {
		printf("GetAudioInfo() send() head failed : [%02d] %s\n", errno, strerror(errno));

		free(tSendPacket.data);
		return ;
	}

	if( send(_socketFd, tSendPacket.data, tSendPacket.bodyLen, MSG_DONTWAIT) < 0 ) {
		printf("GetAudioInfo() send() body failed : [%02d] %s\n", errno, strerror(errno));

		free(tSendPacket.data);
		return ;
	}

	free(tSendPacket.data);

	return ;
} // end of GetAudioInfo()

int SendToWebBlock(int _cmdId) {
	int     clientSockFd;
	int     extId = NUM_EXT_PORT;
	char    tmpMsg[128];

	struct sockaddr_in  tClientAddr;

	ORDER_PACKET_t  tSendPacket;

	memset(&tSendPacket, 0x00, sizeof(tSendPacket));
	memset(&tmpMsg,      0x00, sizeof(tmpMsg));

	if( (clientSockFd = socket(AF_INET, SOCK_STREAM, 0)) < 0 ) {
		printf("SendToWebBlock() socket() failed : [%02d] %s\n", errno, strerror(errno));

		return E_ERROR;
	}

	memset(&tClientAddr, 0x00, sizeof(tClientAddr));
	tClientAddr.sin_family     = AF_INET;
	tClientAddr.sin_port       = htons(NUM_GATEWAY_PORT);
	tClientAddr.sin_addr.s_addr= inet_addr(STR_CLIENT_IP_ADDR);

	if( connect(clientSockFd, (struct sockaddr*)&tClientAddr, sizeof(tClientAddr)) < 0 ) {
		printf("SendToWebBlock() connect() failed : [%02d] %s\n", errno, strerror(errno));

		close(clientSockFd);
		return E_ERROR;
	}

	if( send(clientSockFd, &extId, sizeof(extId), 0) < 0 ) {
		printf("SendToWebBlock() send() port failed : [%02d] %s\n", errno, strerror(errno));

		close(clientSockFd);
		return E_ERROR;
	}
	switch( _cmdId ) {
		case ORDER_CMD_PROC_ALIVE :
			GetAudioAlive(ORDER_CMD_PROC_ALIVE, clientSockFd);
			break;

		case ORDER_CMD_AUDIO_VOLUME_INFO :
			GetAudioInfo(ORDER_CMD_AUDIO_VOLUME_INFO, clientSockFd);
			break;
		
		case ORDER_CMD_AUDIO_INFO :
			GetAudioInfo(ORDER_CMD_AUDIO_INFO, clientSockFd);
			break;

		case ORDER_CMD_AUDIO_LEVEL :
			GetAudioInfo(ORDER_CMD_AUDIO_LEVEL, clientSockFd);
			break;
	}

	close(clientSockFd);

	return E_SUCCESS;
} // end of SendToWebBlock()

int GetWebSocketFd(void) {
	int     sockFd;
	int     option = 1;
	int     optionLen = sizeof(option);

	struct  sockaddr_in cliAddr;

	if( (sockFd = socket(AF_INET, SOCK_STREAM, 0)) < 0 ) {
		printf("GetWebSocketFd() failed : [%02d] %s\n", errno, strerror(errno));

		return -1;
	}

	cliAddr.sin_family      = AF_INET;
	cliAddr.sin_addr.s_addr = inet_addr("127.0.0.1");
	cliAddr.sin_port        = htons(NUM_EXT_PORT);

	if( setsockopt(sockFd, SOL_SOCKET, SO_REUSEADDR, &option, optionLen) < 0 ) {
		printf("GetWebSocketFd() setsockopt() SO_REUSEADDR failed : [%02d]\n", errno);

		return -1;
	}

	if( bind(sockFd, (struct sockaddr *)&cliAddr, sizeof(cliAddr)) < 0 ) {
		printf("GetWebSocketFd() bind() failed : [%02d] %s\n", errno, strerror(errno));
		close(sockFd);

		return -1;
	}

	listen(sockFd, 40);

	return sockFd;
} // end of GetWebSocketFd()

void *ExtOrder(void *_param) {
	int  	webSocketFd;
	int  	rc;

	struct  sockaddr_in clientAddr;
	struct  linger      tLinger;
	struct  timeval 	timeout;
	struct  timeval 	tTimeo = {1, 0};

	socklen_t       clientLen;
	ORDER_PACKET_t  tRecvPacket;

	fd_set			fdReads;

	pthread_setcancelstate(PTHREAD_CANCEL_ENABLE, NULL);
	
	gThreadExt = true;
	gFlagTerm  = true;

	if( (webSocketFd = GetWebSocketFd()) < 0 ) {
		ClosePcm();
	}

	SendToWebBlock(ORDER_CMD_PROC_ALIVE);
	SendToWebBlock(ORDER_CMD_AUDIO_INFO);
	SendToWebBlock(ORDER_CMD_AUDIO_LEVEL);


	while( !gMainTerm ) {
		FD_ZERO(&fdReads);
		FD_SET(webSocketFd, &fdReads);

		timeout.tv_sec  = TIMEOUT_ACCEPT_SEC;
		timeout.tv_usec = TIMEOUT_ACCEPT_MSEC;
		
		if( (rc = select(webSocketFd + 1, &fdReads, NULL, NULL, &timeout)) < 0 ) {
			switch( errno ) {
				case 4 :
					break;

				default :
					printf("ExtOrder() - select() failed : [%02d] %s\n", errno, strerror(errno));
					break;
			}

		} else if ( rc == 0 ) {
			// timeout
			SendToWebBlock(ORDER_CMD_PROC_ALIVE);
			SendToWebBlock(ORDER_CMD_AUDIO_LEVEL);

		} else {
			if( FD_ISSET(webSocketFd, &fdReads) ) {
				if( (connSockFd = accept(webSocketFd, (struct sockaddr *)&clientAddr, &clientLen)) < 0 ) {
					printf("ExtOrder() accept() failed : [%02d] %s\n", errno, strerror(errno));

					close(connSockFd);
					continue;
				}

				tLinger.l_onoff  = 1;
				tLinger.l_linger = 1;

				if( setsockopt(connSockFd, SOL_SOCKET, SO_LINGER, (char *)&tLinger, sizeof(tLinger)) < 0 ) {
					printf("ExtOrder() setsockopt() SO_LINGER failed : [%02d] %s\n", errno, strerror(errno));
				}

				if( setsockopt(connSockFd, SOL_SOCKET, SO_RCVTIMEO, &tTimeo, sizeof(tTimeo)) < 0 ) {
					printf("ExtOrder() setsockopt() SO_RCVTIMEO failed : [%02d] %s\n", errno, strerror(errno));
				}

				memset(&tRecvPacket, 0x00, sizeof(tRecvPacket));
				tRecvPacket.data = NULL;

				if( recv(connSockFd, &tRecvPacket, sizeof(tRecvPacket) - sizeof(tRecvPacket.data), MSG_WAITALL) < 0 ) {
					printf("ExtOrder() recv() headLen failed : [%02d] %s\n", errno, strerror(errno));

					close(connSockFd);
					continue;
				}
				// body data 수신 안함 (alive 요청만)
				switch( tRecvPacket.cmd ) {
					case ORDER_CMD_PROC_ALIVE :
						SendToWebBlock(ORDER_CMD_PROC_ALIVE);
						SendToWebBlock(ORDER_CMD_AUDIO_LEVEL);
						SendToWebBlock(ORDER_CMD_AUDIO_INFO);

						break;
				}

				close(connSockFd);
				continue;
			}
		}
	}
	close(connSockFd);

//	pthread_exit(NULL);
} // end of ExtOrder()	

void *PipeOrder(void *_param) {
	int     writeFd, readFd;
	int  	setVolumeValue = 0;
	char 	buffData[1024];
	char 	msg[1024];

	fd_set  fdReads;
	//pthread_setcancelstate(PTHREAD_CANCEL_ENABLE, NULL);

	memset(msg, 0x00, sizeof(msg));
	/*
	sprintf(msg, "rm -rf %s", PATH_PIPE_WRITE);
	system(msg);

	if( mkfifo(PATH_PIPE_WRITE, 0777) < 0 ) {
		if( errno != 17 ) {
			printf("mkfifo [%s] [%02d] : %s\n", PATH_PIPE_WRITE, errno, strerror(errno));
		}
	}

	memset(msg, 0x00, sizeof(msg));
	sprintf(msg, "rm -rf %s", PATH_PIPE_READ);
	system(msg);

	if( mkfifo(PATH_PIPE_READ, 0777) < 0 ) {
		if( errno != 17 ) {
			printf("mkfifo [%s] [%02d] : %s\n", PATH_PIPE_READ, errno, strerror(errno));
		}
	}
	*/

	if( (writeFd = open(PATH_PIPE_WRITE, O_RDWR)) < 0 ) {
		printf("open failed [%s] [%02d] : %s\n", PATH_PIPE_WRITE, errno, strerror(errno));

		pthread_exit(NULL);
	}

	if( (readFd = open(PATH_PIPE_READ, O_RDWR)) < 0 ) {
		printf("open failed [%s] [%02d] : %s\n", PATH_PIPE_READ, errno, strerror(errno));

		pthread_exit(NULL);
	}

	while( !gMainTerm ) {
		FD_ZERO(&fdReads);
		FD_SET(readFd, &fdReads);

		read(readFd, &tPipeData, sizeof(tPipeData));
		printf("Receive Code : [0x%02x][%d]\n", tPipeData.code, tPipeData.dataLength);

		memset(buffData, 0x00, sizeof(buffData));

		switch( tPipeData.code ) {
			case 0x11 : // getVolume
				sprintf(buffData, "{\"type\": \"audio_player\", \"playVolume\": \"%d\"}", tPlayerData.volume);

				tPipeData.code       = tPipeData.code;
				tPipeData.dataLength = strlen(buffData);

				write(writeFd, &tPipeData, sizeof(tPipeData));
				write(writeFd, buffData, tPipeData.dataLength);

				break;

			case 0x30 : //setVolume
				read(readFd, buffData, tPipeData.dataLength);
				memcpy(&setVolumeValue, buffData, sizeof(int));
				tPlayerData.volume = setVolumeValue;

				SendToWebBlock(ORDER_CMD_AUDIO_VOLUME_INFO);

				break;
		}
	} 
	close(readFd);
	close(writeFd);

//	pthread_exit(NULL);
} // end of PipeOrder()

int GetOptCheck(int _argc, char *_argv[]) {
	int opt;
	int	optCnt 	= 0;

	while( (opt = getopt(_argc, _argv, "c:")) != -1 ) {
		switch( opt ) {
			case 'c' :
				gPlayCnt = atoi(optarg);
				optCnt++;

				break;

			default :
				printf("Usage : %s file name [-c conut]\n", _argv[0]);

				return E_ERROR;	
				break;
		}
	}

	return optCnt;
} // end of GetOptCheck()

int PlayMp3File(char *_outbuf, MP3D_Decode_Params *_dec_param, int _frameSize) {
	int		err;
	int		pcmSize = sizeof(gPcmData);	
	short	pcmData[2304];

	memcpy(gPcmData, _outbuf, pcmSize);
	memcpy(pcmData, _outbuf, pcmSize);

	if( tPlayerData.volume != SIZE_DEFAULT_VOLUME && !g_is_amp_device ) { 
		for( int idx = 0 ; idx < pcmSize / 2 ; idx++ ) {
			pcmData[idx] = (int32_t)pcmData[idx] * tPlayerData.volume / 100;
		}
	}

	if( (err = snd_pcm_writei(gPlaybackHandle, pcmData, (_frameSize / 2))) < 0 ) {
		printf("snd_pcm_writei() failed : [%02d] %s \n", err, snd_strerror(err));

		SetPcmHandle(_dec_param->mp3d_num_channels, _dec_param->mp3d_sampling_freq);
		// return E_ERROR;
	}
	
	return E_SUCCESS;
} // end of PlayMp3File()

int PlayDummySound(MP3D_Decode_Params *_dec_param, int _frameSize) {
	int		err;
	short	pcmData[2304];
	int	play_time_scale = 3;	// seconds;

	int frame_cnt = _dec_param->mp3d_num_channels * 2 * _dec_param->mp3d_sampling_freq * play_time_scale / (sizeof(pcmData) / 2);

	memset(pcmData, 0x00, sizeof(pcmData));
	
	for( int idx = 0 ; idx < frame_cnt ; idx++ ) {
		if( (err = snd_pcm_writei(gPlaybackHandle, pcmData, (_frameSize / 2))) < 0 ) {
			printf("snd_pcm_writei() failed : [%02d] %s \n", err, snd_strerror(err));

			SetPcmHandle(_dec_param->mp3d_num_channels, _dec_param->mp3d_sampling_freq);
			// return E_ERROR;
		}
	}
	
	return E_SUCCESS;
}

int SetPcmHandle(int _channels, unsigned int _samplingFreq) {
	int err;
 	snd_pcm_format_t format = SND_PCM_FORMAT_S16_LE;
	snd_pcm_stream_t stream = SND_PCM_STREAM_PLAYBACK;
  	unsigned int tBufferSize;

	snd_pcm_hw_params_t *tPlaybackParams = NULL;
	snd_pcm_hw_params_alloca(&tPlaybackParams);

	snd_pcm_sw_params_t *tSwParams = NULL;
    snd_pcm_sw_params_alloca(&tSwParams);

	snd_pcm_uframes_t tPeriodSize;

	if( gPlaybackHandle != NULL ) {
		snd_pcm_drain(gPlaybackHandle);
		snd_pcm_drop(gPlaybackHandle);
		snd_pcm_close(gPlaybackHandle);

		gPlaybackHandle = NULL;
	}

	// Open PCM device for playback.
	if ( (err = snd_pcm_open(&gPlaybackHandle, gDeviceName, stream, 0)) < 0 ) {
		printf("cannot open audio device : %s\n",  snd_strerror(err));

		return E_ERROR;
	}
	
	// hwparam를 초기화
	if( (err = snd_pcm_hw_params_any(gPlaybackHandle, tPlaybackParams)) < 0 ) {
		printf("cannot initialize hardware parameter structure : %s\n", snd_strerror(err));
		
		return E_ERROR;
	}

	// 액세스 타입을 설정
	if( (err = snd_pcm_hw_params_set_access(gPlaybackHandle, tPlaybackParams, SND_PCM_ACCESS_RW_INTERLEAVED)) < 0 ) {
		printf("cannot set access type (%s)\n", snd_strerror(err));

		return E_ERROR;
	}

	// Signed 16-bit little-endian format
	if( (err = snd_pcm_hw_params_set_format(gPlaybackHandle, tPlaybackParams, format)) < 0 ) {
		printf("cannot set sample format : %s\n", snd_strerror(err));

		return E_ERROR;
	}

	if( (err = snd_pcm_hw_params_set_rate_near(gPlaybackHandle, tPlaybackParams, &_samplingFreq, 0)) < 0 ) {
		printf("cannot set sample rate : %s\n", snd_strerror(err));
		
		return E_ERROR;
	}

	// stereo라면 2이고 mono이면 1이다.
	// Two channels (stereo)
	if( (err = snd_pcm_hw_params_set_channels(gPlaybackHandle, tPlaybackParams, _channels)) < 0 ) {
		printf("cannot set channel count : %s\n", snd_strerror (err));

		return E_ERROR;
	}

	if( (err = snd_pcm_hw_params(gPlaybackHandle, tPlaybackParams)) < 0 ) {
          printf("ALSA - Cannot set parameters : %s\n", snd_strerror(err));
 
     	return E_ERROR; 
	}
	
	snd_pcm_hw_params_get_period_size( tPlaybackParams, &tPeriodSize, 0 );
	snd_pcm_hw_params_get_buffer_size( tPlaybackParams, (snd_pcm_uframes_t *) &tBufferSize );

	 if( (err = snd_pcm_sw_params_current(gPlaybackHandle, tSwParams)) < 0 ) {
	          printf("ALSA - Unable to determine current swparams for playback : %s\n", snd_strerror(err));
	
		return E_ERROR;
	 }

	 if( (err = snd_pcm_sw_params_set_avail_min(gPlaybackHandle, tSwParams, tPeriodSize)) < 0 ) {
		 printf("ALSA - Unable to set avail min for playback : %s\n", snd_strerror(err));

		 return E_ERROR;
	 }

	 /* write the parameters to the playback device */
	 if( (err = snd_pcm_sw_params(gPlaybackHandle, tSwParams)) < 0 ) {
		 printf("ALSA - Unable to set sw params for playback : %s\n", snd_strerror(err));

		 return E_ERROR;
	 }

	 if( (err = snd_pcm_prepare(gPlaybackHandle)) < 0 ) {
		 printf("ALSA - Cannot prepare audio interface for use : %s\n", snd_strerror(err));

		 return E_ERROR;
	 }
	
	return E_SUCCESS;
} //end of SetPcmHandle()


/*****************************
FUNC : InitSignalHandler()
DESC : Init signals
 ******************************/
void InitSignalHandler(void) {
	signal(SIGINT,  (void (*)(int)) ClosePcm);
	signal(SIGKILL, (void (*)(int)) ClosePcm);
	signal(SIGTERM, (void (*)(int)) ClosePcm);
	signal(SIGPIPE, SIG_IGN);

	return ;
} // end of InitSignalHandler()


/*****************************
FUNC : ClosePcm()
DESC : Interupt signal catch
 ******************************/
void ClosePcm(void) {
	static bool termFlag = false;
	gMainTerm  = true;

	if( termFlag ) {
		return ;
	}

	printf("signel Handler call..\n");
	
	return ;

	
} // end of ClosePcm()


/*****************************
FUNC : main()
DESC : main function
 ******************************/
int main(int _argc, char *_argv[]) {

	char 	tmpFilePath[600];
	char 	fileFullPath[1024];
	char 	sysVolume[128];

	size_t  thStackSize;
	FILE 	*fp;

	pthread_attr_t	thAttr;

	InitSignalHandler();

	// global init
	memset(gPcmData, 	0x00, sizeof(gPcmData));
	memset(gDeviceName, 0x00, sizeof(gDeviceName));

	gMsgQueueFunc.init();

	if( GetOptCheck(_argc, _argv) < 0 ) {
		printf("Input options is invalid.. \n");

		return E_ERROR; 
	}

	g_is_amp_device = IsAmpDevice();

	while(true) {
		if( SQL_sqliteParser() == E_SUCCESS ) {	// gVolume default : 85%
			break;
		}
		usleep(1000);

		printf("sqlite Error \n");

	}

	if( strcmp(tPlayerData.deviceName, "") == 0 ) {
		strcpy(gDeviceName, STR_DEVICE_NAME);
	} else {
		strcpy(gDeviceName, tPlayerData.deviceName);
	}
	printf("device name : [%s]\n", gDeviceName);

	if( strstr(_argv[_argc - 1], ".mp3") ) {
		sprintf(fileFullPath, "%s", _argv[_argc - 1]);

	} else {
		sprintf(tmpFilePath,"%s",_argv[_argc - 1]);
		gDirFlag = true;

		//전체 음원명 정렬하여 가져옴
		struct dirent **direntp;
		int n, i;
		struct stat statbuf;

		n = scandir(_argv[_argc - 1], &direntp, 0, alphasort);

		for (i = 0; i < n; i++)
		{
			if( strstr(direntp[i]->d_name, ".mp3") ) {
				stat(direntp[i]->d_name, &statbuf);

				//printf("%s\n", direntp[i]->d_name);
				sprintf(gTmpFileName[gTmpCnt],"%s",direntp[i]->d_name);
				gTmpCnt++;
			}
		}
	}

	if( pthread_attr_init(&thAttr) != 0 ) {
		printf("ExtOrder - Initial thread attribute failed : [%02d] %s\n", errno, strerror(errno));
		ClosePcm();

	} else {
		printf("ExtOrder - Set thread detach attribute called ... \n");
		if( pthread_attr_setdetachstate(&thAttr, PTHREAD_CREATE_DETACHED) != 0 ) {
			printf("ExtOrder - Set thread detach attribute failed : [%02d] %s\n", errno, strerror(errno));
		}

		if( pthread_attr_getstacksize(&thAttr, &thStackSize) != 0 ) {
			printf("ExtOrder - Get thread stack size failed : [%02d] %s\n", errno, strerror(errno));

		} else {
			printf("ExtOrder - Current thread stack size : [%d] kbytes\n", thStackSize / 1024);
			thStackSize /= 10;
		}

		if( pthread_attr_setstacksize(&thAttr, thStackSize) != 0 ) {
			printf("ExtOrder - Set thread stack size failed : [%02d] %s\n", errno, strerror(errno));

		} else {
			printf("ExtOrder - Resized thread stack size : [%d] kbytes\n", thStackSize / 1024);
		}
	}
	
	if( pthread_create(&thread_recv_order, &thAttr, &ExtOrder, (void *)NULL) != 0 ) {
		printf("ExtOrder() thread failed : [%02d] %s\n", errno, strerror(errno));
	} 

	if( pthread_create(&thread_pipe_order, &thAttr, &PipeOrder, (void *)NULL) != 0 ) {
		printf("PipeOrder() thread failed : [%02d] %s\n", errno, strerror(errno));
	} 
	
	while( !gMainTerm ) {
		if( gDirFlag ) {
			sprintf(fileFullPath, "%s/%s", tmpFilePath, gTmpFileName[gFileCnt]);
		}
		SendToWebBlock(ORDER_CMD_AUDIO_INFO);

		if( !(strstr(fileFullPath,".mp3")) || (DecodeMp3File(fileFullPath) < 0) ) {
			printf("Play Mp3 file failed.. \n");

			break;

		} else if( strstr(fileFullPath, ".mp3") == NULL ) {
			printf("File is not supported... \n");

			break;
		}

		if( gPlayCnt != 0 ) {
			gPlayCnt--;
			
			if( gPlayCnt == 0 ) {
				break;
			}
	
		} else {
			if( gTmpCnt == (gFileCnt + 1) ) {
				gFileCnt = 0;

			} else {
				gFileCnt++;
			}

		}
	}

	gFlagTerm = false;

    SendToWebBlock(ORDER_CMD_PROC_ALIVE);
	SendToWebBlock(ORDER_CMD_AUDIO_LEVEL);

	if( gPlaybackHandle != NULL ) {
		// close latency로 인해 제거
		// snd_pcm_drain(gPlaybackHandle);
		snd_pcm_drop(gPlaybackHandle);
		snd_pcm_close(gPlaybackHandle);
		gPlaybackHandle = NULL;
		printf("playbackHandle close..\n");
	}
	
	close(connSockFd);

	printf("audio_player END... \n");

	return E_SUCCESS;
} // end of main()
