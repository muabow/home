#include <stdio.h>
#include <sys/socket.h>
#include <sys/types.h>
#include <arpa/inet.h>
#include <netinet/in.h>
#include <netinet/tcp.h>
#include <errno.h>
#include <string.h>
#include <stdlib.h>
#include <unistd.h>
#include <signal.h>
#include <sys/select.h>
#include <limits.h>
#include <alsa/asoundlib.h>
#include <libgen.h>
#include <fcntl.h>
#include <sys/time.h>

#include <iostream>
#include <thread>

#include "mp3_enc_interface.h"
#include "pcm_mp3enc.h"
#include "pcm_wav_function.h"

#define MP3_OUTPUT 					false
#define DEBUG_MSG 					false

#define SLEEP_TIME                  1000
#define OUTPUT_PATH_MP3_SAMPLE      "/tmp/output.mp3"


#if MP3_OUTPUT 
FILE		*gOutputFd;
#endif

void SendClientList(void);

namespace Common {
	using namespace std;

	/* const variables */
	const bool 	TYPE_PROTOCOL_UDP	= false;
	const bool 	TYPE_PROTOCOL_TCP	= true;

	const int	SIZE_SCALE_SNDBUF	= 10;
	const int	SIZE_BACK_LOG		= 40;

	const int	DEFAULT_TRANS_CNT   = 2;
	const int	DEFAULT_THREAD_CNT  = 1;

	const int	TIMEOUT_ACCEPT_SEC	= 2;
	const int	TIMEOUT_ACCEPT_MSEC	= 0;
	const int	TIME_RCV_TIMEOUT	= 2;
	const int	TIME_SLEEP			= 1000; // us

	const int	FLAG_HB_REQUEST		= 0;
	const int	FLAG_HB_RESPONSE    = 1;
	const int	FLAG_ODD			= 1;
	const int	FLAG_EVEN			= 2;
	const int	MODE_PCM_STREAM		= 0;
	const int	MODE_MP3_STREAM		= 1;

	const int	SIZE_QUEUE_STACK	= 1024;
	const int	SIZE_QUEUE_SCALE	= 5;

	const int 	FLAG_READ_MODE 		= 0;
	const int 	FLAG_FILE_MODE 		= 1;

	const int 	SIZE_PCM_PERIODS	= 12;

	const int	COUNT_MAX_ACC_CLNT	= 40;

	const char *STR_MSG_HOSTNAME	= "hostName:";
	
	/* struct */
	struct SOCKET_INFO {
		int    			socketFd;		// socket descriptor for UDP
		int     		typeProtocol;	// TCP or UDP
		int 		    typeCast;		// SOCK_STREAM or SOCK_DGRAM
		int				port;
		char			ipAddr[24];
		int				clientCnt;
	} typedef SOCKET_INFO_t;

	struct	CLIENT_INFO {
		int				idx;
		int				socketFd;
		char			ipAddr[16];	
		char			hostName[128];
		char			connTime[128];
		char			macAddr[128];
	} typedef CLIENT_INFO_t;

	struct CLIENT_LIST {
		int             connCnt;
		char            ipAddr[24];
		char            beforeStat[128];
		char            currentStat[128];
		char            beforeTime[128];
		char            currentTime[128];
		char            workingTime[128];
		char            disconnTime[128];
		char            hostName[128];
		time_t          startTime;
		time_t          endTime;
		char            macAddr[24];
	} typedef CLIENT_LIST_t;

	struct HOST_INFO {
		char            hostName[128];
		char            macAddr[128];
	} typedef HOST_INFO_t;

	struct PLAY_INFO {
		int       		rate;
		int        		channels;
		int       		chunkSize;
		int       		idx;
		int				encodeMode;
		unsigned int    pcmBufferSize;
		unsigned int    pcmPeriodSize;
	} typedef PLAY_INFO_t;

	struct ENCODE_INFO {
		int   			chunkSize;
		short   		bitRate;
		int     		sampleRate;
	} typedef ENCODE_INFO_t;

	struct HEADER_PCM_INFO {
		unsigned int    seqNumber;      // 4
		int             rate;           // 8
		short           channels;       // 10
		short           chunkSize;      // 12
		unsigned short  crcValue;       // 14
		char            rsvd[10];       // 26
		unsigned int    pcmBufferSize;  // 30
		unsigned int    pcmPeriodSize;  // 34
		char            rsvd2[32];      // 64
	} typedef HEADER_PCM_INFO_t;

	struct HEADER_MP3_INFO {
		unsigned int    seqNumber;      // 4
		int             rate;           // 8
		short           channels;       // 10
		short           chunkSize;      // 12
		unsigned short  crcValue;       // 14
		short           encMode;        // 16 (0 : pcm, 1 : mp3)
		int             sampleRate;     // 20
		short           bitRate;        // 22
		short           pcmChunkSize;   // 24
		unsigned int    pcmBufferSize;  // 28
		unsigned int    pcmPeriodSize;  // 32
		char            rsvd2[32];      // 64
	} typedef HEADER_MP3_INFO_t;

	struct HEADER_MP3_CHUNK {
		short           chunkSize;      // 02
	} typedef HEADER_MP3_CHUNK_t;

	struct MP3_IDX_INFO {
		MP3E_INT8   *mp3Frame;
		int         index;
		int         socketFd;
	} typedef MP3_IDX_INFO_t;

	struct QUEUE_STACK {
		int     recvCnt;
		int     sendCnt;
		int     stackSize;
		char    **stack;
	} typedef QUEUE_STACK_t;

	struct PCM_CAPTURE {
		char    flagMode;
		FILE    *pcmFile;
	} typedef PCM_CAPTURE_t;

	struct SERVER_INFO {
		// Queue parameter
		int     queueCnt;
		int     bufferRate;
		int     chunkSize;

		// PCM parameter
		int     sampleRate;
		int     channels;

		// MP3 parameter
		bool    mp3_mode;
		int     mp3_chunkSize;
		int     mp3_bitRate;
		int     mp3_sampleRate;

		// server parameter
		bool    typeProtocol;
		char    castType[24];
		int     port;
		int     clientCnt;
		char    ipAddr[24];

		bool    typePlayMode;
		char    fileName[128];
		char	deviceName[128];
	} typedef SERVER_INFO_t;

	struct MP3_HEADER {
		int     version;
		int     layer;
		int     errp;
		int     bitrate;
		int     freq;
		int     pad;
		int     priv;
		int     mode;
		int     modex;
		int     copyright;
		int     original;
		int     emphasis;
	} typedef MP3_HEADER_t;

	struct MP3_FRAME_INFO {
		int     frameSize;
		int     frameLen;
		int     frameFlag;
	} typedef MP3_FRAME_INFO_t;

	const int gSampleRates[4] = {44100, 48000, 32000};
	const int gBitRates[16]   = {0,  32000,  40000,  48000,  56000,  64000,  80000,  96000,
		112000, 128000, 160000, 192000, 224000, 256000, 320000, 0};

	/* Function */
	void GetTime(char *_output) {
		time_t nowTime = time(NULL);
		struct tm *tmNow = localtime(&nowTime);

		sprintf(_output, "%04d-%02d-%02d %02d:%02d:%02d",
				tmNow->tm_year + 1900, tmNow->tm_mon + 1, tmNow->tm_mday,
				tmNow->tm_hour, tmNow->tm_min, tmNow->tm_sec);

		return ;
	} // end of GetTime()

	void ChangeTime(int _sec, char *_output) {
		int day  = 0;
		int hour = _sec / 3600;
		int min  = (_sec / 60) % 60;
		int sec  = _sec % 60;

		if( hour >= 24 ) {
			day = hour / 24;
			hour %= 24;
		}

		sprintf(_output, "%02d days %02d:%02d:%02d", day, hour, min, sec);

		return ;
	} // end of ChangeTime()

	double time_diff(struct timeval x , struct timeval y)
	{
		double x_ms , y_ms , diff;

		x_ms = (double)x.tv_sec*1000000 + (double)x.tv_usec;
		y_ms = (double)y.tv_sec*1000000 + (double)y.tv_usec;

		diff = (double)y_ms - (double)x_ms;

		return diff;
	}
	// struct timeval before , after;
	// gettimeofday(&before , NULL);
	// gettimeofday(&after , NULL);
	// printf("Total time elapsed : %.0lf us" , time_diff(before , after) ); 

	/* class */
	class SocketServer {
		private :
			int		flagTerm = false;
			struct	sockaddr_in 	tSockAddr;
			struct	sockaddr_in 	tSockAddrMulti;
			SOCKET_INFO_t			tServerInfo;

		public  :
			SocketServer(void);
			~SocketServer(void);

			bool	setInfo(SOCKET_INFO_t	*_tSocketInfo);
			bool	setInfoMulti(SOCKET_INFO_t *_tSocketInfo);
			bool	init(void);
			bool	initMulti(void);
			bool 	getSelect(void);
			bool 	getAccept(CLIENT_INFO_t *_tClientInfo);
			void	destory(void);
			int		getClientCnt(void);
			int		getServerSocketFd(void);
			char	*getServerIpAddr(void);
			struct sockaddr_in *getServerSockAddr(void);
			struct sockaddr_in *getServerSockAddrMulti(void);
	}; // end of class : SocketServer

	class ThreadClient {
		private:
			bool	flagTerm = false;
			bool	flagUsed = false;
			int		stackIdx = 0;
			int		queueIdx = 0;
			int		recvCnt	 = 0;
			int		typeProtocol;

			struct	sockaddr_in 	tSockAddr;

			HOST_INFO_t		tHostInfo;
			PLAY_INFO_t     tPlayInfo;
			ENCODE_INFO_t   tEncInfo;
			thread 			threadFunc;
			pthread_mutex_t tStackMutex;

			bool initSocket(void);
			bool sendPlayInfo(void);
			void setClientInfo(void);
			void uniExecute(void);
			void multiExecute(void);

		public:
			ThreadClient(void);
			~ThreadClient(void);

			CLIENT_INFO_t	tClientInfo;		

			void uniRun(void);
			void multiRun(void);
			void stop(void);
			bool getUseStatus(void);
			void setUseStatus(bool _stat);
			bool setThreadClient(CLIENT_INFO_t _tClientInfo);
			void setPlayInfo(int _idx, int _typeProtocol, PLAY_INFO_t *_tPlayInfo, ENCODE_INFO_t *_tEncInfo);
			void setSocketOption(void);
			void setStackIdx(int _idx);
			bool setSockAddr(struct sockaddr_in *_tSockAddr);
			bool sendUnicast(void);
			bool sendMp3Unicast(void);
			bool sendMp3Multicast(void);
			bool sendMulticast(void);
			void removeClientInfo(void);
			char *getClientIpAddr(void);
			unsigned short getCrcValue(char *_buf, unsigned short _len);
	};

	class CommonFunc {
		private :
			bool	flagTerm = false;
			int		maxTransInstance;
			int		maxThreadInstance;
			int		idxTransInstance;
			int		idxThreadInstance;
			int		idxTransInstanceMul;
			int		idxThreadInstanceMul;
			int		connCount;
			int		maxCount;
			int		accCount;
			char	clientList[4096];
			char	serverInfo[4096];

			CLIENT_LIST_t	*tClientList;
			SERVER_INFO_t 	tServerInfo;
			pthread_mutex_t tClientListMutex;

			SocketServer 	**arrTransInstance;
			ThreadClient 	**arrThreadInstance;
			SocketServer 	**arrTransInstanceMul;
			ThreadClient 	**arrThreadInstanceMul;

		public  :
			CommonFunc(void);

			void	setMaxTransIntance(int _cnt);
			void	setMaxThreadIntance(int _cnt);
			void 	setTransInstance(SocketServer *_classPtr);
			void 	setThreadInstance(ThreadClient *_classPtr);
			void 	setTransInstanceMul(SocketServer *_classPtr);
			void 	setThreadInstanceMul(ThreadClient *_classPtr);
			bool 	setClientList(CLIENT_INFO_t *_tClientInfo, bool _stat);
			void 	printClientList(void);
			bool	getFlagTerm(void);
			char	*getClientList(void);
			char 	*getServerInfo(void);
			void 	setServerInfo(SERVER_INFO_t *_tServerInfo);

			void 	handler(void);
			void 	term(void);
	}; // end of class : CommonFunc
	CommonFunc	common;

	class SigHandler {
		public  :
			SigHandler(void) {
				signal(SIGINT, this->term);

				return ;
			}

			static void term(int _sigNum) {
				static bool	flagTerm = false;

				if( flagTerm ) {
					fprintf(stderr, "already terminated\n");
					return ;
				}
				flagTerm = true;

				common.handler();
			}
	}; // end of class : SigHandler
	SigHandler	handler;

	class QueueStack {
		private:
			int		idx;
			int		storeCnt;
			int		bufferSize;
			int		bufferRate;
			int		chunkSize;
			int		recvIdx;
			bool	flagRecvCnt;
			bool	flagRecv;

			QUEUE_STACK_t	tQueueStack;
			pthread_mutex_t tStackMutex;

		public:
			QueueStack(void);
			~QueueStack(void);

			char*	getQueueInStack(void);
			char*	getQueueIndex(int _idx);
			void	setQueueInStack(char *_data);
			void	incSendCnt(void);
			int 	getRecvCnt(void);
			int		getRecvCntFlag(void);
			int 	getStackSize(void);
			int 	getBufferSize(void);
			void	freeStack(void);
			int		getStoreCount(void);
			int		getRecvFlag(void);
			int		getSendCnt(void);
			int		getChunkSize(void);
			void 	init(int _idx, int _bufferRate, int _chunkSize);
	};
	QueueStack	*stackServer;

	class PcmCapture {
		private:
			int				idx;
			char			deviceName[128];
			char    		*readPcmData;
			unsigned int	frameBytes;
			unsigned int	periodSize;

			int     		chunkSize;
			unsigned int	channels;
			unsigned int	sampleRate;

			unsigned int	pcmChannels;
			unsigned int	pcmBufferSize;
			unsigned int	pcmPeriodSize;

			snd_pcm_t       *tCaptureHandle;
			PCM_CAPTURE_t   tPcmCapture;

			thread			threadFunc;

		public :
			~PcmCapture(void);	

			void    run(void);
			void    stop(void);

			bool	init(int _idx, char *_deviceName, int _chunkSize, int _sampleRate, int _channels);
			void    execute(void);
			bool    setPcmHandle(void);
			bool    setPlayMode(const char *_fileName);

			unsigned int	getPcmBufferSize(void);
			unsigned int	getPcmPeriodSize(void);

	};
	PcmCapture *pcmCapture;

	class ServerFunc {
		private:
			int				queueCnt;
			int				bufferRate;
			int				chunkSize;
			bool			typePlayMode;
			char			fileName[128];
			char			deviceName[128];

			thread			txThread;
			SOCKET_INFO_t	tSocketInfo;
			PLAY_INFO_t     tPlayInfo;
			ENCODE_INFO_t   tEncInfo;

			ThreadClient	*pThreadClient;
			ThreadClient	*pThreadClientMulti;

			void 			sendThread();
			void 			sendThreadMulti();

		public:
			ServerFunc(void);
			~ServerFunc(void);

			void	init(SERVER_INFO_t *_tServerInfo);
			void	run(void);
			void	stop(void);
			bool 	setPlayMode(int _idx, char *_fileName);
			bool	setStackIdx(int _threadIdx, int _queueIdx);
			char	*getClientList(void);
	};
#if 1
	class Mp3Encode {
		private :
			int         headerCount;
			int         queueCount; 
			bool		flagMp3Rewind;
			bool		flagMp3QueueLoop;
			char        *W1[1];
			char        *W2[1];
			char        *W3[1];
			char        *W4[1];
			char        *W5[1];
			char        *W6[1];

			MP3E_INT16  *pcmFrame;
			MP3E_INT8   *mp3Frame;
			MP3E_INT8   *mp3Queue;

			MP3E_INT8	*mp3GetFrame;
			int 		index;
			int			socketFd;

			MP3_FRAME_INFO_t        *gArrFrameInfo;
			MP3E_Encoder_Parameter  tEncParams;


			thread 			mp3Thread;

		public  :
			int			queueIdx;
			int			bitRate;
			int 		sampleRate;
			int			channels;
			int			mbScale;
			
			void 		runMp3Stream();
			void 		procMp3Stream();

			int 		initMp3(int _queueIdx, short _bitRate, int _sampleRate);
			void 		encoderMemInfoAlloc(MP3E_Encoder_Config *_encConfig);
			int 		parseMp3Header(const MP3E_INT8 *_mp3Frame, MP3_HEADER_t *_tMp3Header);
			int 		getFrameSize(MP3_HEADER_t *_tMp3Header);
			void 		getMp3Error(MP3E_RET_VAL _val);
			int 		getMp3QueueCount(void);
			int 		getMp3QueueIndex(void);
			bool		getMp3RewindFlag(void);
			bool 		getMp3QueueLoopFlag(void);
			void 		freeEncodeMp3(void);
			

			int 		initGetMp3Frame(MP3_IDX_INFO_t *_mp3Idx, short _bitRate, int _sampleRate);
			MP3E_INT8* 	getMp3Frame(MP3_IDX_INFO_t *_mp3Idx);
			int 		getMp3FrameSize(MP3_IDX_INFO_t *_mp3Idx);
			int 		getMp3FrameLen(MP3_IDX_INFO_t *_mp3Idx);
			void		setMp3FrameIndex(MP3_IDX_INFO_t *_mp3Idx, int _index);
			int 		getMp3FrameIndex(MP3_IDX_INFO_t *_mp3Idx);
			void 		setMp3FrameSocketFd(MP3_IDX_INFO_t *_mp3Idx, int _socketFd);
			int 		sendMp3Frame(MP3_IDX_INFO_t *_mp3Idx);
			void 		freeMp3Frame(MP3_IDX_INFO_t *_mp3Idx);
			int 		incMp3FrameIndex(MP3_IDX_INFO_t *_mp3Idx);
	}; // end of class : Mp3Encode 
	Mp3Encode	*mp3Encode;
#endif
	/* Class : Common */
	CommonFunc::CommonFunc(void) {
		this->idxTransInstance  = 0;
		this->idxThreadInstance = 0;
		this->idxTransInstanceMul  = 0;
		this->idxThreadInstanceMul = 0;

		this->maxTransInstance  = Common::DEFAULT_TRANS_CNT;
		this->maxThreadInstance = Common::DEFAULT_THREAD_CNT;

		this->arrTransInstance  = new SocketServer *[this->maxTransInstance];
		this->arrThreadInstance = new ThreadClient *[this->maxThreadInstance];
		this->arrTransInstanceMul  = new SocketServer *[this->maxThreadInstance];
		this->arrThreadInstanceMul = new ThreadClient *[this->maxThreadInstance];
		
		this->connCount = 0;
		this->maxCount  = Common::COUNT_MAX_ACC_CLNT;
		this->accCount	= 0;

		this->tClientList = (CLIENT_LIST_t *)malloc(sizeof(CLIENT_LIST_t) * this->maxCount);

		for( int idx = 0 ; idx < this->maxCount ; idx++ ) {
			memset(&this->tClientList[idx], 0x00, sizeof(CLIENT_LIST_t));
			this->tClientList[idx].connCnt = 0;
		}


		return ;
	}

	void CommonFunc::setTransInstance(SocketServer *_classPtr) {
		this->arrTransInstance[this->idxTransInstance++] = _classPtr;

		return ;
	}

	void CommonFunc::setThreadInstance(ThreadClient *_classPtr) {
		this->arrThreadInstance[this->idxThreadInstance++] = _classPtr;

		return ;
	}

	void CommonFunc::setTransInstanceMul(SocketServer *_classPtr) {
		this->arrTransInstanceMul[this->idxTransInstanceMul++] = _classPtr;

		return ;
	}

	void CommonFunc::setThreadInstanceMul(ThreadClient *_classPtr) {
		this->arrThreadInstanceMul[this->idxThreadInstanceMul++] = _classPtr;

		return ;
	}



	bool CommonFunc::getFlagTerm(void) { 
		return this->flagTerm;
	}

	void CommonFunc::handler(void) {
		int idx;

		if( this->flagTerm ) return ;
		this->flagTerm = true;

		for( idx = 0 ; idx < this->idxTransInstance ; idx++ ) {
			this->arrTransInstance[idx]->destory();
		}
		
		for( idx = 0 ; idx < this->idxTransInstanceMul ; idx++ ) {
			this->arrTransInstanceMul[idx]->destory();
		}

		for( idx = 0 ; idx < this->idxThreadInstance ; idx++ ) {
			this->arrThreadInstance[idx]->stop();

		}
		
		for( idx = 0 ; idx < this->idxThreadInstanceMul ; idx++ ) {
			this->arrThreadInstanceMul[idx]->stop();

		}

		if( this->arrTransInstance != NULL ) {
			delete [] this->arrTransInstance;
			this->arrTransInstance = NULL;
		}

		if( this->arrThreadInstance != NULL ) {
			delete [] this->arrThreadInstance;
			this->arrThreadInstance = NULL;
		}

		if( this->arrTransInstanceMul != NULL ) {
			delete [] this->arrTransInstanceMul;
			this->arrTransInstanceMul = NULL;
		}

		if( this->arrThreadInstanceMul != NULL ) {
			delete [] this->arrThreadInstance;
			this->arrThreadInstanceMul = NULL;
		}

		if( this->tClientList != NULL ) {
			delete this->tClientList;
			this->tClientList = NULL;
		}

		return ;
	}

	void CommonFunc::setMaxTransIntance(int _cnt) {
		this->maxTransInstance = _cnt;

		if( this->arrTransInstance != NULL ) {
			delete [] this->arrTransInstance;
			this->arrTransInstance = NULL;
		}

		this->arrTransInstance  = new SocketServer *[this->maxTransInstance];

		return ;
	}

	void CommonFunc::setMaxThreadIntance(int _cnt) {
		this->maxThreadInstance = _cnt;

		if( this->arrThreadInstance != NULL ) {
			delete [] this->arrThreadInstance;
			this->arrThreadInstance = NULL;
		}

		this->arrThreadInstance  = new ThreadClient *[this->maxThreadInstance];

		return ;
	}

	bool CommonFunc::setClientList(CLIENT_INFO_t *_tClientInfo, bool _stat) {
		int	 idx;
		bool flagUsed = false;	

		while( pthread_mutex_trylock(&this->tClientListMutex) != 0 ) {
			usleep(Common::TIME_SLEEP);
			fprintf(stderr, "trylock\n");
		}
		
		if( _stat ) {
			// 접속 처리
			for( idx = 0 ; idx < this->maxCount ; idx++ ) {
				if( strncmp(this->tClientList[idx].ipAddr, _tClientInfo->ipAddr, strlen(_tClientInfo->ipAddr)) == 0 
						&& strncmp(this->tClientList[idx].macAddr, _tClientInfo->macAddr, strlen(_tClientInfo->macAddr)) == 0 ) {
					flagUsed = true;
					break;
				}
			}

			if( flagUsed ) {
				// reconnect
				time(&this->tClientList[idx].startTime);

				strcpy(this->tClientList[idx].beforeStat, 	"disconnect");
				strcpy(this->tClientList[idx].currentStat, 	"connect");
				strcpy(this->tClientList[idx].beforeTime, 	this->tClientList[idx].currentTime);
				strcpy(this->tClientList[idx].currentTime, 	_tClientInfo->connTime);
				Common::ChangeTime((int)difftime(this->tClientList[idx].startTime, this->tClientList[idx].endTime), this->tClientList[idx].disconnTime);
				strcpy(this->tClientList[idx].hostName, 	_tClientInfo->hostName);

				this->tClientList[idx].connCnt++;


			} else {
				// connect
				for( idx = 0 ; idx < this->maxCount ; idx++ ) {
					if( strcmp(this->tClientList[idx].ipAddr, "") == 0 ) {
						break;
					}
				}

				strcpy(this->tClientList[idx].ipAddr, 		_tClientInfo->ipAddr);
				strcpy(this->tClientList[idx].beforeStat, 	"");
				strcpy(this->tClientList[idx].currentStat, 	"connect");
				strcpy(this->tClientList[idx].beforeTime, 	"");
				strcpy(this->tClientList[idx].currentTime, 	_tClientInfo->connTime);
				Common::ChangeTime(0, this->tClientList[idx].workingTime);
				Common::ChangeTime(0, this->tClientList[idx].disconnTime);
				strcpy(this->tClientList[idx].hostName, 	_tClientInfo->hostName);
				strcpy(this->tClientList[idx].macAddr, 		_tClientInfo->macAddr);

				time(&this->tClientList[idx].startTime);
				time(&this->tClientList[idx].endTime);

				this->accCount++;
			}
			this->connCount++;

		} else {
			// disconnect
			for( idx = 0 ; idx < this->maxCount ; idx++ ) {
				if( strncmp(this->tClientList[idx].ipAddr, _tClientInfo->ipAddr, strlen(_tClientInfo->ipAddr)) == 0 
						&& strncmp(this->tClientList[idx].macAddr, _tClientInfo->macAddr, strlen(_tClientInfo->macAddr)) == 0 ) {
					break;
				}
			}

			time(&this->tClientList[idx].endTime);

			strcpy(this->tClientList[idx].beforeStat, 	"connect");
			strcpy(this->tClientList[idx].currentStat, 	"disconnect");
			strcpy(this->tClientList[idx].beforeTime, 	this->tClientList[idx].currentTime);
			strcpy(this->tClientList[idx].currentTime, 	_tClientInfo->connTime);
			Common::ChangeTime((int)difftime(this->tClientList[idx].endTime, this->tClientList[idx].startTime), this->tClientList[idx].workingTime);
			strcpy(this->tClientList[idx].hostName, 	_tClientInfo->hostName);

			this->connCount--;

		}
		 pthread_mutex_unlock(&this->tClientListMutex);

		return true;
	}

	char *CommonFunc::getClientList(void) {
		int idx, length = 0;
		char clientList[4096];
		if( this->flagTerm ) return NULL;

		memset(this->clientList, 0x00, sizeof(this->clientList));
		memset(clientList, 0x00, sizeof(clientList));

		for( idx = 0 ; idx < this->accCount ; idx++ ) {
			sprintf(clientList + length,
					"\"%d\": {\"ipAddr\": \"%s\", \"hostName\": \"%s\", \"beforeStat\": \"%s\", \"beforeTime\": \"%s\", \"currentStat\": \"%s\", \"currentTime\": \"%s\", \"connCnt\": %d, \"workingTime\": \"%s\", \"disconnTime\": \"%s\"}",
					idx,
					this->tClientList[idx].ipAddr,
					this->tClientList[idx].hostName,
					this->tClientList[idx].beforeStat,
					this->tClientList[idx].beforeTime,
					this->tClientList[idx].currentStat,
					this->tClientList[idx].currentTime,
					this->tClientList[idx].connCnt,
					this->tClientList[idx].workingTime,
					this->tClientList[idx].disconnTime);

			if( (idx + 1) < this->accCount ) {
				strcat(clientList, 
						", ");
				length = strlen(clientList);
			}
		}

		sprintf(this->clientList, 
				"{\"maxCount\": %d, \"accCount\": %d, \"connCount\": %d, \"list\": {%s}}", 
				this->maxCount, this->accCount, this->connCount, clientList);

		return this->clientList;
	}

	void CommonFunc::printClientList(void) {
		int idx;

		if( this->flagTerm ) return ;

		fprintf(stderr, "\n");
		fprintf(stderr, "State table\n");
		fprintf(stderr, "+-------+-----------------+-----------------------+----------------------------------+----------------------------------+--------+--------------------+--------------------+ \n");
		fprintf(stderr, "| Index | Client IP       | Host Name             | Before State                     | Current State                    | Reconn | P.Work Time        | Reconnection Time  | \n");
		fprintf(stderr, "+-------+-----------------+-----------------------+----------------------------------+----------------------------------+--------+--------------------+--------------------+ \n");

		for( idx = 0 ; idx < this->accCount ; idx++ ) {
			fprintf(stderr, "|    %02d | %-15s | %-21s | %-10s - %-19s | %-10s - %-19s | %-6d | %-18s | %-18s | \n",
					idx + 1,
					this->tClientList[idx].ipAddr,
					this->tClientList[idx].hostName,
					this->tClientList[idx].beforeStat,
					this->tClientList[idx].beforeTime,
					this->tClientList[idx].currentStat,
					this->tClientList[idx].currentTime,
					this->tClientList[idx].connCnt,
					this->tClientList[idx].workingTime,
					this->tClientList[idx].disconnTime);
			fprintf(stderr, "+-------+-----------------+-----------------------+----------------------------------+----------------------------------+--------+--------------------+--------------------+ \n");
		}

		fprintf(stderr, "- Summary \n");
		fprintf(stderr, " -- Maximum connection count : [%02d] clients\n", this->maxCount);
		fprintf(stderr, " -- Accumulated connection   : [%02d] clients\n", this->accCount);
		fprintf(stderr, " -- Current connection       : [%02d] clients\n", this->connCount);
		fprintf(stderr, "\n");

		// websocket, send client list
		SendClientList();

		return ;
	}

	char *CommonFunc::getServerInfo(void) {
		if( this->flagTerm ) return NULL;

		memset(this->serverInfo, 0x00, sizeof(serverInfo));

		sprintf(this->serverInfo,
				"{\"queueCnt\": \"%d\", \"bufferRate\": \"%d\", \"chunkSize\": \"%d\", \"sampleRate\": \"%d\", \"channels\": \"%d\", \"mp3_mode\": \"%d\", \"mp3_chunkSize\": %d, \"mp3_bitRate\": \"%d\", \"mp3_sampleRate\": \"%d\", \"typeProtocol\": \"%d\", \"castType\": \"%s\", \"port\": \"%d\", \"clientCnt\": \"%d\", \"ipAddr\": \"%s\", \"playMode\": \"%d\", \"fileName\": \"%s\", \"deviceName\": \"%s\"}",
				this->tServerInfo.queueCnt,
				this->tServerInfo.bufferRate,
				this->tServerInfo.chunkSize,
				this->tServerInfo.sampleRate,
				this->tServerInfo.channels,
				this->tServerInfo.mp3_mode,
				this->tServerInfo.mp3_chunkSize,
				this->tServerInfo.mp3_bitRate,
				this->tServerInfo.mp3_sampleRate,
				this->tServerInfo.typeProtocol,
				this->tServerInfo.castType,
				this->tServerInfo.port,
				this->tServerInfo.clientCnt,
				this->tServerInfo.ipAddr,
				this->tServerInfo.typePlayMode,
				this->tServerInfo.fileName,
				this->tServerInfo.deviceName);

		return this->serverInfo;
	}

	void CommonFunc::setServerInfo(SERVER_INFO_t *_tServerInfo) {
		memcpy(&this->tServerInfo, _tServerInfo, sizeof(SERVER_INFO_t));

		return ;
	}


	void CommonFunc::term(void) {
		this->handler();
		// exit(0);
	}

	/* Class : Transmssion */
	SocketServer::SocketServer(void) {
		fprintf(stderr, "Create SocketServer instance\n");

		return ;
	} // end of constructor()

	SocketServer::~SocketServer(void) {
		this->destory();
	} // end of destructor()

	void SocketServer::destory(void) { 
		if( this->flagTerm ) return ;
		this->flagTerm = true;

		close(this->tServerInfo.socketFd);
	}

	bool SocketServer::setInfo(SOCKET_INFO_t *_tSocketInfo) {
		// thread count 
		memcpy(&this->tServerInfo, _tSocketInfo, sizeof(SOCKET_INFO_t));

		fprintf(stderr, " - Server Information\n");
		fprintf(stderr, "  : Protocol type : %s\n",
				this->tServerInfo.typeProtocol == Common::TYPE_PROTOCOL_TCP ? "TCP" : "UDP");
		fprintf(stderr, "  : Cast     type : %s\n",
				this->tServerInfo.typeCast == SOCK_STREAM ? "Unicast" : "Multicast");
		fprintf(stderr, "  : port          : %d \n", this->tServerInfo.port);


		return true;
	}

	bool SocketServer::setInfoMulti(SOCKET_INFO_t *_tSocketInfo) {
		memcpy(&this->tServerInfo, _tSocketInfo, sizeof(SOCKET_INFO_t));
		// thread count 
		fprintf(stderr, " - Server Information\n");
		fprintf(stderr, "  : Protocol type : %s\n", "UDP");
		fprintf(stderr, "  : Cast     type : %s\n", "Multicast");
		fprintf(stderr, "  : IP address    : %s \n", this->tServerInfo.ipAddr);
		fprintf(stderr, "  : port          : %d \n", this->tServerInfo.port);

		return true;
	}



	bool SocketServer::initMulti() {
		// TCP socket init or UDP socket
		if( (this->tServerInfo.socketFd = socket(AF_INET, SOCK_DGRAM, 0)) < 0 ) {
			fprintf(stderr, "init - socket() failed : [%02d] %s\n", errno, strerror(errno));

			return false;
		}

		int			option 	  = true;
		socklen_t 	optionLen = sizeof(option);

		if( setsockopt(this->tServerInfo.socketFd, SOL_SOCKET, SO_REUSEADDR, &option, optionLen) < 0 ) {
			fprintf(stderr, "Server - setsockopt() SO_REUSEADDR failed : [%02d] %s\n", errno, strerror(errno));

			return false; 
		}

		this->tSockAddrMulti.sin_family = AF_INET;

		// UDP multi-cast //
		this->tSockAddrMulti.sin_addr.s_addr = inet_addr(this->tServerInfo.ipAddr);
		this->tSockAddrMulti.sin_port 		= htons(this->tServerInfo.port);

		// multicast ip address check
		if( !IN_MULTICAST(ntohl(this->tSockAddrMulti.sin_addr.s_addr)) ) {
			fprintf(stderr, "Server - given address [%s] is not multicast\n", inet_ntoa(this->tSockAddrMulti.sin_addr));

			return false;
		}

		struct sockaddr_in multicastAddr;
		multicastAddr.sin_family 		= AF_INET;
		multicastAddr.sin_addr.s_addr 	= htonl(INADDR_ANY);
		multicastAddr.sin_port 			= htons(0);

		// BIND init
		if( bind(this->tServerInfo.socketFd, (struct sockaddr *)&multicastAddr, sizeof(multicastAddr)) < 0 ) {
			fprintf(stderr, "Server - bind() failed : [%02d]\n", errno);
			return false;
		}
		return true;
	} 



	bool SocketServer::init() {
		// TCP socket init or UDP socket
		if( (this->tServerInfo.socketFd = socket(AF_INET, this->tServerInfo.typeCast, 0)) < 0 ) {
			fprintf(stderr, "init - socket() failed : [%02d] %s\n", errno, strerror(errno));

			return false;
		}

		int			option 	  = true;
		socklen_t 	optionLen = sizeof(option);

		if( setsockopt(this->tServerInfo.socketFd, SOL_SOCKET, SO_REUSEADDR, &option, optionLen) < 0 ) {
			fprintf(stderr, "Server - setsockopt() SO_REUSEADDR failed : [%02d] %s\n", errno, strerror(errno));

			return false; 
		}

		this->tSockAddr.sin_family = AF_INET;

		if( (this->tServerInfo.socketFd = socket(AF_INET, this->tServerInfo.typeCast, 0)) < 0 ) {
			fprintf(stderr, "init - socket() failed : [%02d] %s\n", errno, strerror(errno));

			return false;
		}

		if( setsockopt(this->tServerInfo.socketFd, SOL_SOCKET, SO_REUSEADDR, &option, optionLen) < 0 ) {
			fprintf(stderr, "Server - setsockopt() SO_REUSEADDR failed : [%02d] %s\n", errno, strerror(errno));

			return false; 
		}


		// TCP uni-cast //
		this->tSockAddr.sin_port 		= htons(this->tServerInfo.port);
		this->tSockAddr.sin_addr.s_addr = htonl(INADDR_ANY);

		// BIND init
		if( bind(this->tServerInfo.socketFd, (struct sockaddr *)&this->tSockAddr, sizeof(this->tSockAddr)) < 0 ) {
			fprintf(stderr, "Server - bind() failed : [%02d]\n", errno);

			return false;
		}

		int       optval;
		socklen_t optlen = sizeof(optval);
		getsockopt(this->tServerInfo.socketFd, SOL_SOCKET, SO_SNDBUF, (char*)&optval, &optlen);

		optval *= Common::SIZE_SCALE_SNDBUF;
		setsockopt(this->tServerInfo.socketFd, SOL_SOCKET, SO_SNDBUF, (char*)&optval, sizeof(optval));

		// LISTEN init
		if( listen(this->tServerInfo.socketFd, Common::SIZE_BACK_LOG) < 0 ) {
			fprintf(stderr, "Server - listen() failed : [%02d]\n", errno);

			return false;
		}

		return true;
	} 

	int  SocketServer::getClientCnt(void) {
		return this->tServerInfo.clientCnt;
	}

	bool SocketServer::getSelect(void) {
		int		rc;
		struct	timeval	timeout;
		fd_set  fdReads;

		FD_ZERO(&fdReads);
		FD_SET(this->tServerInfo.socketFd, &fdReads);

		timeout.tv_sec  = Common::TIMEOUT_ACCEPT_SEC;
		timeout.tv_usec = Common::TIMEOUT_ACCEPT_MSEC;

		if( (rc = select(this->tServerInfo.socketFd + 1, &fdReads, NULL, NULL, &timeout)) < 0 ) {
			switch( errno ) {
				case 4 : 
					break;
				default :
					fprintf(stderr, "Server - select() failed : [%02d] %s\n", errno, strerror(errno));
					break;
			}

			return false;

		} else if ( rc == 0 ) {
			return false;
		}

		return true;
	}

	bool SocketServer::getAccept(CLIENT_INFO_t *_tClientInfo) {
		int 	  clientSockFd;
		struct    sockaddr_in     clntAddr;
		socklen_t clientAddrLen = sizeof(struct sockaddr_in);

		if( (clientSockFd = accept(this->tServerInfo.socketFd, (struct sockaddr *)&clntAddr, &clientAddrLen)) < 0 ) {
			fprintf(stderr, "Server - accept() failed : [%02d] %s\n", errno, strerror(errno));

			return false;
		}

		_tClientInfo->socketFd = clientSockFd;
		strcpy(_tClientInfo->ipAddr, inet_ntoa(clntAddr.sin_addr));

		return true;
	}

	int	SocketServer::getServerSocketFd(void) {

		return this->tServerInfo.socketFd;
	}

	char *SocketServer::getServerIpAddr(void) {

		return this->tServerInfo.ipAddr;
	}
	struct sockaddr_in *SocketServer::getServerSockAddr(void) {

		return &this->tSockAddr;
	}

	struct sockaddr_in *SocketServer::getServerSockAddrMulti(void) {

		return &this->tSockAddrMulti;
	}



	ThreadClient::ThreadClient() {
		this->tStackMutex = PTHREAD_MUTEX_INITIALIZER;

		return ;
	}

	ThreadClient::~ThreadClient() {
		fprintf(stderr, "Termination ThreadClient instance\n");
		this->stop();

		return ;
	}

	void ThreadClient::multiExecute(void) { 
		// multicast
		if( this->tPlayInfo.encodeMode == Common::MODE_MP3_STREAM )
			this->sendMp3Multicast();
		else
			this->sendMulticast();

		this->stop();

		return ;
	}

	void ThreadClient::uniExecute(void) { 
			
		// Unicast
			this->setSocketOption();

			if( !this->initSocket() ) {
				this->stop();
				return ;
			}

			if( !this->sendPlayInfo() ) {
				this->stop();
				return ;
			}

		//	this->flagUsed = true;

			this->setClientInfo();
			
			if( this->tPlayInfo.encodeMode == Common::MODE_MP3_STREAM ) {
				this->sendMp3Unicast();

			}
			else {
				this->sendUnicast();
			}

			this->stop();

		return ;
	}

	bool ThreadClient::initSocket(void) {
		int 	rc;
		char 	hbData;

		// step #1 : heartheat 전송
/*		hbData = Common::FLAG_HB_REQUEST;
		if( (rc = send(this->tClientInfo.socketFd, &hbData, sizeof(hbData), 0)) < 0 ) {
			fprintf(stderr, "Thread[%d] - send request failed [%s] : [%02d] %s\n", 
					this->tClientInfo.idx, this->tClientInfo.ipAddr, errno, strerror(errno));

			return false;
		}
*/
		// step #2 : response 수신

		// step #2-2 : socket issue 시 데이터 수신
		if( (rc = recv(this->tClientInfo.socketFd, &this->tHostInfo, sizeof(this->tHostInfo), 0)) < 0 ) {
			fprintf(stderr, "Thread[%d] - recv() failed [%s] : [%02d] %s\n", 
					this->tClientInfo.idx, this->tClientInfo.ipAddr, errno, strerror(errno));

			return false;

		} else if( rc == 0 ) { // disconnect
			fprintf(stderr, "Thread[%d] - connection reset by peer [%s]\n", this->tClientInfo.idx, this->tClientInfo.ipAddr);

			return false;
		}

		// step #2-3 : 수신 데이터 검증
		if( rc != sizeof(this->tHostInfo) ) {
			fprintf(stderr, "Thread[%d] - recv() invalid length [%d/%d] %s\n", 
					this->tClientInfo.idx, rc, (int)sizeof(this->tHostInfo), this->tClientInfo.ipAddr);

			return false;
		}

		if( strncmp(this->tHostInfo.hostName, Common::STR_MSG_HOSTNAME, strlen(Common::STR_MSG_HOSTNAME)) != 0 ) {
			fprintf(stderr, "Thread[%d] - recv() invalid header : %s\n", this->tClientInfo.idx, this->tClientInfo.ipAddr);

			return false;

		}

		return true;
	}

	void ThreadClient::setPlayInfo(int _idx, int _typeProtocol, PLAY_INFO_t *_tPlayInfo, ENCODE_INFO_t *_tEncInfo) {
		this->typeProtocol = _typeProtocol;
		memcpy(&this->tPlayInfo, _tPlayInfo, sizeof(PLAY_INFO_t));
		memcpy(&this->tEncInfo,  _tEncInfo,  sizeof(ENCODE_INFO_t));
		this->tClientInfo.socketFd = -1;

		this->tPlayInfo.idx = _idx;

		return ;
	}

	bool ThreadClient::sendPlayInfo(void) {
		int 	rc;
		char 	hbData;

		// step #3-1 : PCM 정보 전송
		if( send(this->tClientInfo.socketFd, &this->tPlayInfo, sizeof(this->tPlayInfo), 0) < 0 ) {
			fprintf(stderr, "Thread[%d] - sendPlayInfo() send() failed : [PCM] [%s/%s] [%02d] %s\n", 
					this->tClientInfo.idx, this->tClientInfo.macAddr, this->tClientInfo.ipAddr, errno, strerror(errno));

			return false;
		}

		// step #3-2 : MP3 정보 전송
		// MP3 mode 로 동작 시
		if( this->tPlayInfo.encodeMode == Common::MODE_MP3_STREAM ) {
			if( send(this->tClientInfo.socketFd, &tEncInfo, sizeof(tEncInfo), 0) < 0 ) {
				fprintf(stderr, "Thread[%d] - sendPlayInfo() send() failed : [MP3] [%s/%s] [%02d] %s\n", 
						this->tClientInfo.idx, this->tClientInfo.macAddr, this->tClientInfo.ipAddr, errno, strerror(errno));

				return false;
			}
		}
		
		return true;
	}

	void ThreadClient::setClientInfo(void) {
		char	nowTime[128];
		Common::GetTime(nowTime);

		strcpy(this->tClientInfo.connTime, nowTime);
		strcpy(this->tClientInfo.macAddr,  this->tHostInfo.macAddr);
		strcpy(this->tClientInfo.hostName, this->tHostInfo.hostName + strlen(Common::STR_MSG_HOSTNAME));

		common.setClientList(&this->tClientInfo, true);
		fprintf(stderr, "\n");
		fprintf(stderr, "+- Accept new client \n");
		fprintf(stderr, "+-- index        : [%d]\n", this->tClientInfo.idx);
		fprintf(stderr, "+-- ipaddr       : [%s]\n", this->tClientInfo.ipAddr);
		fprintf(stderr, "+-- macAddr      : [%s]\n", this->tClientInfo.macAddr);
		fprintf(stderr, "+-- hostName     : [%s]\n", this->tClientInfo.hostName);
		fprintf(stderr, "+-- connect time : [%s]\n", this->tClientInfo.connTime);

		common.printClientList();

		return ;
	}

	bool ThreadClient::sendMulticast(void) {
		int		rc;
		int		bufferSize	= stackServer[this->stackIdx].getBufferSize();
		int		stackSize	= stackServer[this->stackIdx].getStackSize();
		bool	recvFlag 	= stackServer[this->stackIdx].getRecvCntFlag();

		HEADER_PCM_INFO_t   tHeaderInfo;

		memset(&tHeaderInfo, 0x00, sizeof(tHeaderInfo));
		tHeaderInfo.channels      = this->tPlayInfo.channels;
		tHeaderInfo.rate          = this->tPlayInfo.rate; 
		tHeaderInfo.chunkSize     = this->tPlayInfo.chunkSize;
		tHeaderInfo.pcmBufferSize = this->tPlayInfo.pcmBufferSize;
		tHeaderInfo.pcmPeriodSize = this->tPlayInfo.pcmPeriodSize;
		tHeaderInfo.seqNumber     = 0;

		bzero(tHeaderInfo.rsvd,  sizeof(tHeaderInfo.rsvd));
		bzero(tHeaderInfo.rsvd2, sizeof(tHeaderInfo.rsvd2));

		// 최소한 버퍼 사이즈만큼 stack 쌓이도록 대기(buffering)
		while( bufferSize > stackServer[this->stackIdx].getRecvCnt() 
				&& (stackServer[this->stackIdx].getRecvFlag() == false) ) {
			usleep(Common::TIME_SLEEP);
		}

		this->queueIdx = stackServer[this->stackIdx].getRecvCnt();
		while( !this->flagTerm ) {
			this->recvCnt = stackServer[this->stackIdx].getRecvCnt();

			if( (this->queueIdx >= this->recvCnt && recvFlag == stackServer[this->stackIdx].getRecvCntFlag()) 
					|| this->recvCnt == stackSize ) {

				usleep(Common::TIME_SLEEP);


				continue;
			}

			// send : header info
			tHeaderInfo.crcValue = this->getCrcValue(stackServer[this->stackIdx].getQueueIndex(this->queueIdx), tHeaderInfo.chunkSize);

			if( sendto(this->tClientInfo.socketFd, &tHeaderInfo, sizeof(tHeaderInfo), MSG_NOSIGNAL,
						(struct sockaddr *)&this->tSockAddr, sizeof(this->tSockAddr)) < 0 ) {
				fprintf(stderr, "Thread[%d] - sendMulticast() header send failed [%s] : [%02d] %s\n",
						this->tClientInfo.idx,this->tClientInfo.ipAddr, errno, strerror(errno));
				break;
			}

			// send : body data
			if( (rc = sendto(this->tClientInfo.socketFd, stackServer[this->stackIdx].getQueueIndex(this->queueIdx), tHeaderInfo.chunkSize, MSG_NOSIGNAL, (struct sockaddr *)&this->tSockAddr, sizeof(this->tSockAddr) )) < 0 ) {
				fprintf(stderr, "Thread[%d] - sendMulticast() body send failed [%s] : [%02d] %s\n",
						this->tClientInfo.idx, this->tClientInfo.ipAddr, errno, strerror(errno));
				break;
			}

			// 최대 queue까지 전송했을때 stack 순회
			this->queueIdx++;
			if( this->queueIdx == stackSize ) {
				this->queueIdx = 0;
				tHeaderInfo.seqNumber = 0;

				if( recvFlag == true ) recvFlag = false;
				else recvFlag = true;
			}

		}

		return false;
	}

	bool ThreadClient::sendMp3Multicast(void) {
		int     recvCnt    = 0;
		int     servSockFd = this->tClientInfo.socketFd;
		int     bufferSize = stackServer[this->stackIdx].getBufferSize();
		int     recvFlag   = mp3Encode->getMp3RewindFlag();
		int     stackSize   = mp3Encode->getMp3QueueCount();
		int     rc;

		HEADER_MP3_INFO_t   tHeaderInfo;
		MP3_IDX_INFO_t  tMp3Idx;

		mp3Encode->initGetMp3Frame(&tMp3Idx, this->tEncInfo.bitRate, this->tEncInfo.sampleRate);


		tHeaderInfo.channels      = this->tPlayInfo.channels;
		tHeaderInfo.rate          = this->tPlayInfo.rate;
		tHeaderInfo.pcmChunkSize  = this->tEncInfo.chunkSize;
		tHeaderInfo.bitRate       = this->tEncInfo.bitRate;
		tHeaderInfo.sampleRate    = this->tEncInfo.sampleRate;
		tHeaderInfo.encMode       = Common::MODE_MP3_STREAM;
		tHeaderInfo.seqNumber     = 0;
		tHeaderInfo.pcmBufferSize = this->tPlayInfo.pcmBufferSize;
		tHeaderInfo.pcmPeriodSize = this->tPlayInfo.pcmPeriodSize;
		bzero(tHeaderInfo.rsvd2, sizeof(tHeaderInfo.rsvd2));

		// 최소한 버퍼 사이즈만큼 stack 쌓이도록 대기(buffering)
/*
		while( (bufferSize >  mp3Encode->getMp3QueueIndex()) && (mp3Encode->getMp3QueueLoopFlag() == FALSE) ) {
			usleep(Common::SLEEP_TIME);
		}
	*/	

		while( !this->flagTerm ) {
			recvCnt = mp3Encode->getMp3QueueIndex();

			if( (mp3Encode->getMp3FrameIndex(&tMp3Idx) >= recvCnt && recvFlag == mp3Encode->getMp3RewindFlag()) || recvCnt == stackSize ) {
				usleep(Common::TIME_SLEEP);
				
				continue;
			}

			// send : header info
			tHeaderInfo.chunkSize = mp3Encode->getMp3FrameSize(&tMp3Idx);
			tHeaderInfo.crcValue = this->getCrcValue(mp3Encode->getMp3Frame(&tMp3Idx), tHeaderInfo.chunkSize);

			if( sendto(servSockFd, &tHeaderInfo, sizeof(tHeaderInfo), MSG_NOSIGNAL,
						(struct sockaddr *)&this->tSockAddr, sizeof(this->tSockAddr)) < 0 ) {
				fprintf(stderr, "Server - SendMp3Multicast() header send failed : [%02d] %s\n", errno, strerror(errno));

				break;
			}
			// send : body data
			if( (rc = sendto(servSockFd, mp3Encode->getMp3Frame(&tMp3Idx), tHeaderInfo.chunkSize, MSG_NOSIGNAL, (struct sockaddr *)&this->tSockAddr, sizeof(this->tSockAddr))) < 0 ) {
				fprintf(stderr, "Server - SendMp3Multicast() body send failed : [%02d] %s\n", errno, strerror(errno));

				break;
			}

			tHeaderInfo.seqNumber++;

			// 최대 queue까지 전송했을때 stack 순회
			if( mp3Encode->incMp3FrameIndex(&tMp3Idx) == true ) {
				tHeaderInfo.seqNumber = 0;

				if( recvFlag == true ) recvFlag = false;
				else recvFlag = true;
			}

			//usleep(SLEEP_TIME);
		} // end of while()

		return false;
	}


	bool ThreadClient::sendUnicast(void) {
		int		rc;
		int		stackSize	= stackServer[this->stackIdx].getStackSize();
		bool	recvFlag 	= stackServer[this->stackIdx].getRecvCntFlag();
		char	hbData;

		HEADER_PCM_INFO_t   tHeaderInfo;

		memset(&tHeaderInfo, 0x00, sizeof(tHeaderInfo));
		tHeaderInfo.channels      = this->tPlayInfo.channels;
		tHeaderInfo.rate          = this->tPlayInfo.rate; 
		tHeaderInfo.chunkSize     = this->tPlayInfo.chunkSize;
		tHeaderInfo.pcmBufferSize = this->tPlayInfo.pcmBufferSize;
		tHeaderInfo.pcmPeriodSize = this->tPlayInfo.pcmPeriodSize;
		tHeaderInfo.seqNumber     = 0;

		bzero(tHeaderInfo.rsvd,  sizeof(tHeaderInfo.rsvd));
		bzero(tHeaderInfo.rsvd2, sizeof(tHeaderInfo.rsvd2));

		// 최소한 버퍼 사이즈만큼 stack 쌓이도록 대기(buffering)
/*
		int		bufferSize	= stackServer[this->stackIdx].getBufferSize();
		while( bufferSize > stackServer[this->stackIdx].getRecvCnt() 
				&& (stackServer[this->stackIdx].getRecvFlag() == false) ) {
			usleep(Common::TIME_SLEEP);
		}
*/
		this->queueIdx = stackServer[this->stackIdx].getRecvCnt();
		//fprintf(stderr, "start Queue index : %d\n", this->queueIdx);
		
		
		if( (rc = fcntl( this->tClientInfo.socketFd, F_SETFL, O_NONBLOCK )) < 0 )
			fprintf(stderr,"NON BLOCK fail\n");

		while( !this->flagTerm ) {

			this->recvCnt = stackServer[this->stackIdx].getRecvCnt();

			if( (this->queueIdx >= this->recvCnt && recvFlag == stackServer[this->stackIdx].getRecvCntFlag()) 
					|| this->recvCnt == stackSize ) {
				usleep(Common::TIME_SLEEP);

				continue;
			}

LOOP_RETRY_DATA :
			// send : body data
			
			if( (rc = send(this->tClientInfo.socketFd, stackServer[this->stackIdx].getQueueIndex(this->queueIdx), tHeaderInfo.chunkSize, 0)) < 0 ) {
				fprintf(stderr, "Thread[%d] - sendUnicast() body send failed [%s/%s] : [%02d] %s\n",
						this->tClientInfo.idx, this->tClientInfo.macAddr, this->tClientInfo.ipAddr, errno, strerror(errno));
				break;
			}
			
			// 최대 queue까지 전송했을때 stack 순회
			this->queueIdx++;
			tHeaderInfo.seqNumber++;
			if( this->queueIdx == stackSize ) {
				this->queueIdx = 0;
				tHeaderInfo.seqNumber = 0;

				if( recvFlag == true ) recvFlag = false;
				else recvFlag = true;
			}

		//	usleep(Common::TIME_SLEEP);
		}

		return false;
	}
	
	bool ThreadClient::sendMp3Unicast(void) {

		int     rc;
		int     recvFlag   = mp3Encode->getMp3RewindFlag();
		int     stackSize  = mp3Encode->getMp3QueueCount();
		int     servSockFd = this->tClientInfo.socketFd;
		char    hbData;
		int		bufferSize	= stackServer[this->stackIdx].getBufferSize();

		MP3_IDX_INFO_t  tMp3Idx;
		
		mp3Encode->initGetMp3Frame(&tMp3Idx, this->tEncInfo.bitRate, this->tEncInfo.sampleRate);

		HEADER_MP3_INFO_t   tHeaderInfo;

		HEADER_MP3_CHUNK_t  tChunkInfo;
		
		memset(&tChunkInfo, 0x00, sizeof(tChunkInfo));
		memset(&tHeaderInfo, 0x00, sizeof(tHeaderInfo));
		tHeaderInfo.seqNumber     = 0;
		tHeaderInfo.rate          = this->tPlayInfo.rate;
		tHeaderInfo.channels      = this->tPlayInfo.channels;

		tHeaderInfo.encMode       = Common::MODE_MP3_STREAM;
		tHeaderInfo.sampleRate    = this->tEncInfo.sampleRate;
		tHeaderInfo.bitRate       = this->tEncInfo.bitRate;
		tHeaderInfo.pcmChunkSize  = this->tEncInfo.chunkSize;
		tHeaderInfo.pcmBufferSize = this->tPlayInfo.pcmBufferSize;
		tHeaderInfo.pcmPeriodSize = this->tPlayInfo.pcmPeriodSize;
 
		bzero(tHeaderInfo.rsvd2, sizeof(tHeaderInfo.rsvd2));
		
		mp3Encode->setMp3FrameSocketFd(&tMp3Idx, servSockFd);

		// 최소한 버퍼 사이즈만큼 stack 쌓이도록 대기(buffering)
		while( (bufferSize > mp3Encode->getMp3QueueIndex()) && ( mp3Encode->getMp3QueueLoopFlag() == false) ) {
			usleep(Common::TIME_SLEEP);
		}

		mp3Encode->setMp3FrameIndex(&tMp3Idx, mp3Encode->getMp3QueueIndex());
		
		while( !this->flagTerm ) {
			this->recvCnt = mp3Encode->getMp3QueueIndex();
			if( (mp3Encode->getMp3FrameIndex(&tMp3Idx) >= this->recvCnt && recvFlag == mp3Encode->getMp3RewindFlag()) || this->recvCnt == stackSize ) {
				usleep(Common::TIME_SLEEP);

				continue;
			}

			// send : header info
			tChunkInfo.chunkSize = tHeaderInfo.chunkSize = mp3Encode->getMp3FrameSize(&tMp3Idx);
			//tHeaderInfo.crcValue  = this->getCrcValue(mp3Encode->getMp3Frame(&tMp3Idx),tHeaderInfo.chunkSize );
			tHeaderInfo.crcValue  = 0;
LOOP_MP3_RETRY_DATA :

			if( (rc = send(servSockFd , &tChunkInfo, sizeof(tChunkInfo), 0)) < 0 ) {
				fprintf(stderr, "Server - SendChunkSize send failed [%02d][%s/%s] : [%02d] %s\n", this->tClientInfo.idx, this->tClientInfo.macAddr, this->tClientInfo.ipAddr, errno, strerror(errno));
				break;
			}

			// send : body data
			if( mp3Encode->sendMp3Frame(&tMp3Idx) < 0 ) {
				fprintf(stderr, "Server - SendMp3UniCast() body send failed [%02d][%s/%s] : [%02d] %s\n", this->tClientInfo.idx, this->tClientInfo.macAddr, this->tClientInfo.ipAddr, errno, strerror(errno));	
				break;
			}

		tHeaderInfo.seqNumber++;
			// 최대 queue까지 전송했을때 stack 순회
			if( mp3Encode->incMp3FrameIndex(&tMp3Idx) == true ) {
				tHeaderInfo.seqNumber = 0;

				if( recvFlag == true ) recvFlag = false;
				else recvFlag = true;
			}
		}
		mp3Encode->freeMp3Frame(&tMp3Idx);
		return false;
	}

	void ThreadClient::setStackIdx(int _idx) {
		fprintf(stderr, "[%d] thread change stack : [%d]\n", this->tClientInfo.idx, _idx);

		this->stackIdx = _idx;	

		return ;
	}

	void ThreadClient::setSocketOption(void) {
		int optval = 1; // enable
		int rc;

		if( setsockopt(this->tClientInfo.socketFd, IPPROTO_TCP, TCP_NODELAY, &optval, sizeof (optval)) < 0 ) {
			fprintf(stderr, "Thread[%d] - setsockopt() SO_NODELAY failed : [%02d] %s\n", 
					this->tClientInfo.idx, errno, strerror(errno));
		}

		struct linger optLinger;
		optLinger.l_onoff  = 1; // enable
		optLinger.l_linger = 0;
		if( setsockopt(this->tClientInfo.socketFd, SOL_SOCKET, SO_LINGER, (char *)&optLinger, sizeof(optLinger)) < 0 ) {
			fprintf(stderr, "Thread[%d] - setsockopt() SO_LINGER failed : [%02d] %s\n", 
					this->tClientInfo.idx, errno, strerror(errno));
		}

		struct timeval tRcvTimeOut = {Common::TIME_RCV_TIMEOUT, 0};
		if( setsockopt(this->tClientInfo.socketFd, SOL_SOCKET, SO_RCVTIMEO, &tRcvTimeOut, sizeof(tRcvTimeOut) ) < 0 ) {
			fprintf(stderr, "Thread[%d] - setsockopt() SO_RCVTIMEO failed : [%02d] %s\n", 
					this->tClientInfo.idx, errno, strerror(errno));
		}


		return ;
	}

	unsigned short ThreadClient::getCrcValue(char *_buf, unsigned short _len) {
		unsigned short crc_tbl_ccitt[256] = {
			0x0000,  0x1021,  0x2042,  0x3063,  0x4084,  0x50a5,  0x60C6,  0x70E7,
			0x8108,  0x9129,  0xa14a,  0xB16B,  0xC18C,  0xD1aD,  0xE1CE,  0xF1EF,
			0x1231,  0x0210,  0x3273,  0x2252,  0x52B5,  0x4294,  0x72F7,  0x62D6,
			0x9339,  0x8318,  0xB37B,  0xa35a,  0xD3BD,  0xC39C,  0xF3FF,  0xE3DE,
			0x2462,  0x3443,  0x0420,  0x1401,  0x64E6,  0x74C7,  0x44a4,  0x5485,
			0xa56a,  0xB54B,  0x8528,  0x9509,  0xE5EE,  0xF5CF,  0xC5aC,  0xD58D,
			0x3653,  0x2672,  0x1611,  0x0630,  0x76D7,  0x66F6,  0x5695,  0x46B4,
			0xB75B,  0xa77a,  0x9719,  0x8738,  0xF7DF,  0xE7FE,  0xD79D,  0xC7BC,
			0x48C4,  0x58E5,  0x6886,  0x78a7,  0x0840,  0x1861,  0x2802,  0x3823,
			0xC9CC,  0xD9ED,  0xE98E,  0xF9aF,  0x8948,  0x9969,  0xa90a,  0xB92B,
			0x5aF5,  0x4aD4,  0x7aB7,  0x6a96,  0x1a71,  0x0a50,  0x3a33,  0x2a12,
			0xDBFD,  0xCBDC,  0xFBBF,  0xEB9E,  0x9B79,  0x8B58,  0xBB3B,  0xaB1a,
			0x6Ca6,  0x7C87,  0x4CE4,  0x5CC5,  0x2C22,  0x3C03,  0x0C60,  0x1C41,
			0xEDaE,  0xFD8F,  0xCDEC,  0xDDCD,  0xaD2a,  0xBD0B,  0x8D68,  0x9D49,
			0x7E97,  0x6EB6,  0x5ED5,  0x4EF4,  0x3E13,  0x2E32,  0x1E51,  0x0E70,
			0xFF9F,  0xEFBE,  0xDFDD,  0xCFFC,  0xBF1B,  0xaF3a,  0x9F59,  0x8F78,
			0x9188,  0x81a9,  0xB1Ca,  0xa1EB,  0xD10C,  0xC12D,  0xF14E,  0xE16F,
			0x1080,  0x00a1,  0x30C2,  0x20E3,  0x5004,  0x4025,  0x7046,  0x6067,
			0x83B9,  0x9398,  0xa3FB,  0xB3Da,  0xC33D,  0xD31C,  0xE37F,  0xF35E,
			0x02B1,  0x1290,  0x22F3,  0x32D2,  0x4235,  0x5214,  0x6277,  0x7256,
			0xB5Ea,  0xa5CB,  0x95a8,  0x8589,  0xF56E,  0xE54F,  0xD52C,  0xC50D,
			0x34E2,  0x24C3,  0x14a0,  0x0481,  0x7466,  0x6447,  0x5424,  0x4405,
			0xa7DB,  0xB7Fa,  0x8799,  0x97B8,  0xE75F,  0xF77E,  0xC71D,  0xD73C,
			0x26D3,  0x36F2,  0x0691,  0x16B0,  0x6657,  0x7676,  0x4615,  0x5634,
			0xD94C,  0xC96D,  0xF90E,  0xE92F,  0x99C8,  0x89E9,  0xB98a,  0xa9aB,
			0x5844,  0x4865,  0x7806,  0x6827,  0x18C0,  0x08E1,  0x3882,  0x28a3,
			0xCB7D,  0xDB5C,  0xEB3F,  0xFB1E,  0x8BF9,  0x9BD8,  0xaBBB,  0xBB9a,
			0x4a75,  0x5a54,  0x6a37,  0x7a16,  0x0aF1,  0x1aD0,  0x2aB3,  0x3a92,
			0xFD2E,  0xED0F,  0xDD6C,  0xCD4D,  0xBDaa,  0xaD8B,  0x9DE8,  0x8DC9,
			0x7C26,  0x6C07,  0x5C64,  0x4C45,  0x3Ca2,  0x2C83,  0x1CE0,  0x0CC1,
			0xEF1F,  0xFF3E,  0xCF5D,  0xDF7C,  0xaF9B,  0xBFBa,  0x8FD9,  0x9FF8,
			0x6E17,  0x7E36,  0x4E55,  0x5E74,  0x2E93,  0x3EB2,  0x0ED1,  0x1EF0
		};

		unsigned short idx;
		unsigned short crc;

		crc = 0xffff;

		for( idx = 0 ; idx < _len; idx++ ) {
			crc = (crc << 8) ^ crc_tbl_ccitt[((crc >> 8) ^ *_buf++) & 0xff];
		}

		return crc;
	}

	void ThreadClient::uniRun() { 
		this->flagTerm = false;

		this->threadFunc = thread(&ThreadClient::uniExecute, this); 
		this->threadFunc.detach();
	}

	void ThreadClient::multiRun() { 
		this->flagTerm = false;

		this->threadFunc = thread(&ThreadClient::multiExecute, this); 
		this->threadFunc.detach();
	}



	void ThreadClient::removeClientInfo(void) {
		char	nowTime[128];
		Common::GetTime(nowTime);

		strcpy(this->tClientInfo.connTime, nowTime);

		fprintf(stderr, "\n");
		fprintf(stderr, "+- Disconnect client \n");
		fprintf(stderr, "+-- index           : [%d]\n", this->tClientInfo.idx);
		fprintf(stderr, "+-- ipaddr          : [%s]\n", this->tClientInfo.ipAddr);
		fprintf(stderr, "+-- macAddr         : [%s]\n", this->tClientInfo.macAddr);
		fprintf(stderr, "+-- hostName        : [%s]\n", this->tClientInfo.hostName);
		fprintf(stderr, "+-- disconnect time : [%s]\n", this->tClientInfo.connTime);

		common.setClientList(&this->tClientInfo, false);

		common.printClientList();

		return ;
	}

	void ThreadClient::stop() {

		while( pthread_mutex_trylock(&this->tStackMutex) != 0 ) {
			usleep(Common::TIME_SLEEP);
			fprintf(stderr, "trylock\n");
		}

		if( this->flagUsed && !this->flagTerm ) {
			fprintf(stderr, "[%d] thread stop\n", this->tClientInfo.idx);

			this->removeClientInfo();

			close(this->tClientInfo.socketFd);
			this->tClientInfo.socketFd = -1;
			memset(&this->tClientInfo, 0x00, sizeof(this->tClientInfo));
		}

		this->flagUsed = false;
		this->flagTerm = true;

		pthread_mutex_unlock(&this->tStackMutex);

		return ;
	}

	bool ThreadClient::getUseStatus(void) {
		return flagUsed;
	}

	void ThreadClient::setUseStatus(bool _stat) {
		flagUsed = _stat;
		return ; 
	}

	bool ThreadClient::setThreadClient(CLIENT_INFO_t _tClientInfo) {
		memcpy(&this->tClientInfo, &_tClientInfo, sizeof(CLIENT_INFO_t));

		return true;
	}

	bool ThreadClient::setSockAddr(struct sockaddr_in *_tSockAddr) {
		memcpy(&this->tSockAddr, _tSockAddr, sizeof(struct sockaddr_in));

		return true;
	}


	QueueStack::QueueStack(void) {

		return ;
	}

	QueueStack::~QueueStack(void) {
		this->freeStack();

		return ;
	}

	void QueueStack::init(int _idx, int _bufferRate, int _chunkSize) {

		this->tStackMutex = PTHREAD_MUTEX_INITIALIZER;

		this->idx 		 = _idx;

		this->bufferRate = _bufferRate;
		this->chunkSize  = _chunkSize;

		this->storeCnt 		= 0;
		this->flagRecv		= false;
		this->flagRecvCnt	= false;
		this->recvIdx 		= Common::FLAG_EVEN;

		this->tQueueStack.recvCnt = 0;
		this->tQueueStack.sendCnt = 0;
		this->tQueueStack.stackSize = Common::SIZE_QUEUE_STACK * Common::SIZE_QUEUE_SCALE; 

		this->bufferSize = (int)(this->tQueueStack.stackSize * this->bufferRate * 0.01);

		if( (this->tQueueStack.stack = (char **)malloc(this->tQueueStack.stackSize * sizeof(char *))) == NULL ) {
			fprintf(stderr, "malloc() stack[] failed : [%02d]\n", errno);

			return ;
		}

		for( int idx = 0 ; idx < this->tQueueStack.stackSize ; idx++ ) {
			if( (this->tQueueStack.stack[idx] = (char *)malloc(this->chunkSize * sizeof(char))) == NULL ) {
				fprintf(stderr, "malloc() stack[][] failed : [%02d]\n", errno);

				return ;
			}
		}


		return ;
	}

	void QueueStack::freeStack(void) {
		if( this->tQueueStack.stack != NULL ) { 
			fprintf(stderr, "[%d] stack is freed\n", this->idx);

			delete [] this->tQueueStack.stack;
			this->tQueueStack.stack = NULL;
		}
		return ;
	}

	char *QueueStack::getQueueInStack(void) {
		if( this->tQueueStack.sendCnt == this->tQueueStack.stackSize ) {
			this->tQueueStack.sendCnt = 0;
		}

		return this->tQueueStack.stack[this->tQueueStack.sendCnt];
	}

	char *QueueStack::getQueueIndex(int _idx) {

		// fprintf(stderr, "getQueue : %d\n", _idx);
		return this->tQueueStack.stack[_idx];
	}

	void QueueStack::setQueueInStack(char *_data) {
		if( this->tQueueStack.recvCnt == this->tQueueStack.stackSize ) {
			this->tQueueStack.recvCnt = 0;
			this->flagRecv = true;

			if( this->recvIdx % Common::FLAG_EVEN == 0 ) {	// Even 2 , odd 1
				this->flagRecvCnt = true;
				this->recvIdx = Common::FLAG_ODD;

			} else {
				this->flagRecvCnt = false;
				this->recvIdx = Common::FLAG_EVEN;
			}
		}

		while( pthread_mutex_trylock(&this->tStackMutex) != 0 ) {
			usleep(Common::TIME_SLEEP);
		}

		memmove(this->tQueueStack.stack[this->tQueueStack.recvCnt], _data, this->chunkSize);

		this->tQueueStack.recvCnt++;
		this->storeCnt++;

		pthread_mutex_unlock(&this->tStackMutex);
		// fprintf(stderr, "setQueue : %d\n", this->storeCnt);

		return ;
	}

	void QueueStack::incSendCnt(void) {
		while( pthread_mutex_trylock(&this->tStackMutex) != 0 ) {
			usleep(Common::TIME_SLEEP);
		}

		this->tQueueStack.sendCnt++;
		this->storeCnt--;
		pthread_mutex_unlock(&this->tStackMutex);

		return ;
	}

	int QueueStack::getRecvCnt(void) {

		return this->tQueueStack.recvCnt;
	} 

	int QueueStack::getRecvCntFlag(void) {

		return this->flagRecvCnt;
	}

	int	QueueStack::getStackSize(void) {

		return this->tQueueStack.stackSize;
	}

	int	QueueStack::getBufferSize(void) {

		return this->bufferSize;
	}

	int QueueStack::getStoreCount(void) {

		return this->storeCnt;
	}

	int	QueueStack::getRecvFlag(void) {

		return this->flagRecv;
	}

	int QueueStack::getSendCnt(void) {

		return this->tQueueStack.sendCnt;
	}

	int	QueueStack::getChunkSize(void) {

		return this->chunkSize;
	}

	bool PcmCapture::init(int _idx, char *_deviceName, int _chunkSize, int _sampleRate, int _channels) {
		this->idx = _idx;
		this->readPcmData 	 = NULL;
		this->tCaptureHandle = NULL;
		this->periodSize	 = Common::SIZE_PCM_PERIODS;
		this->chunkSize 	 = _chunkSize;
		this->sampleRate 	 = _sampleRate;
		this->channels 		 = _channels;
		this->readPcmData    = (char *)malloc(this->chunkSize * sizeof(char));

		strcpy(this->deviceName, _deviceName);

		if( !this->setPcmHandle() ) {
			fprintf(stderr, "[%d] init PCM device failed [%s]\n",this->idx, this->deviceName);
			this->tCaptureHandle = NULL;

			return false;
		}

		fprintf(stderr, "[%d] init PCM device success [%s]\n",this->idx, this->deviceName);

		return true;
	}

	PcmCapture::~PcmCapture(void) {
		this->stop();

		return ;
	}

	void PcmCapture::stop(void) {
		if( this->tCaptureHandle != NULL) {
			fprintf(stderr, "[%d] Free PCM device [%s]\n", this->idx, this->deviceName);

			snd_pcm_drop(this->tCaptureHandle);	
			snd_pcm_drain(this->tCaptureHandle);	
			snd_pcm_close(this->tCaptureHandle);
			this->tCaptureHandle = NULL;

			if( this->readPcmData != NULL ) {
				delete this->readPcmData;
				this->readPcmData = NULL;
			}
		}

		return ;
	}

	void PcmCapture::run(void) {
		this->threadFunc = thread(&PcmCapture::execute, this);
		this->threadFunc.detach();

		return ;
	}

	bool PcmCapture::setPcmHandle(void) {
		int 	err;

		snd_pcm_hw_params_t *tCaptureParams = NULL;
		snd_pcm_hw_params_alloca(&tCaptureParams);

		if( this->tCaptureHandle != NULL ) {
			fprintf(stderr, "Server - capture parameter resetting..\n");
			snd_pcm_drain(this->tCaptureHandle);
			usleep(SLEEP_TIME);
			snd_pcm_close(this->tCaptureHandle);
			usleep(SLEEP_TIME);
			this->tCaptureHandle = NULL;
			usleep(SLEEP_TIME);
		}

		if( (err = snd_pcm_open(&this->tCaptureHandle, this->deviceName, SND_PCM_STREAM_CAPTURE, SND_PCM_NONBLOCK)) < 0 ) {
			fprintf(stderr, "ALSA - cannot open audio device [%s] : %s\n",this->deviceName , snd_strerror(err));

			return false;
		}
		// snd_pcm_drain(this->tCaptureHandle);	

		if( (err = snd_pcm_hw_params_malloc(&tCaptureParams)) < 0 ) {
			fprintf(stderr, "ALSA - cannot allocate hardware parameter structure : %s\n", snd_strerror(err));

			return false; 
		}

		if( (err = snd_pcm_hw_params_any(this->tCaptureHandle, tCaptureParams)) < 0 ) {
			fprintf(stderr, "ALSA - cannot initialize hardware parameter structure : %s\n", snd_strerror(err));

			return false;
		}

		if( (err = snd_pcm_hw_params_set_access(this->tCaptureHandle, tCaptureParams, SND_PCM_ACCESS_RW_INTERLEAVED)) < 0 ) {
			fprintf(stderr, "ALSA - cannot set access type : %s\n", snd_strerror(err));

			return false;
		}

		if( (err = snd_pcm_hw_params_set_format(this->tCaptureHandle, tCaptureParams, SND_PCM_FORMAT_S16_LE)) < 0 ) {
			fprintf(stderr, "ALSA - cannot set sample format : %s\n", snd_strerror(err));

			return false; 
		}

		/*
		   if( (err = snd_pcm_hw_params_set_rate(this->tCaptureHandle, tCaptureParams, this->sampleRate, 0) ) < 0) {
		   fprintf(stderr, "ALSA - cannot set sample rate : %s\n", snd_strerror(err));

		   if( (err = snd_pcm_hw_params_set_rate_near(this->tCaptureHandle, tCaptureParams, &this->sampleRate, 0) ) < 0) {
		   fprintf(stderr, "ALSA - cannot set near sample rate : %s\n", snd_strerror(err));

		   return false;
		   }
		   }
		 */

		int dir, flag = 0;  
		unsigned int exact_rate;

		exact_rate = this->sampleRate;
		if( (err = snd_pcm_hw_params_set_rate_near(this->tCaptureHandle, tCaptureParams, &exact_rate, &dir) ) < 0) {
			fprintf(stderr, "ALSA - cannot set near sample rate : %s\n", snd_strerror(err));

	//		return false;
			flag = 1;	
		}

		if ( this->sampleRate != exact_rate ) {
			fprintf(stderr, "The rate %d Hz is not supported by your hardware.\n"
					"==> Using %d Hz instead.\n", this->sampleRate, exact_rate);
			
	//			return false;
		}
		if ( dir == 0 ) {
			fprintf(stderr, "exact_rate == rate --> dir = %d\n", dir);
		}
		else if ( dir < 0 ) {
			fprintf(stderr, "exact_rate > rate --> dir = %d\n", dir);
		}
		else if ( dir > 0 ) {
			fprintf(stderr, "exact_rate < rate --> dir = %d\n", dir);
		}
		else {
			fprintf(stderr, "Error setting rate: Unknown dir\n");

	//		return false;
		}


		unsigned int channel;
		snd_pcm_hw_params_get_channels(tCaptureParams, &channel);

		if( (err = snd_pcm_hw_params_set_channels(this->tCaptureHandle, tCaptureParams, this->channels)) < 0 ) {
			fprintf(stderr, "ALSA - cannot set channel count : %s\n", snd_strerror(err));
			fprintf(stderr, "ALSA - near set channel..\n");

			if( (err = snd_pcm_hw_params_set_channels_near(this->tCaptureHandle, tCaptureParams, &this->channels)) < 0 ) {
				fprintf(stderr, "ALSA - cannot set channel near : %s\n", snd_strerror(err));

				return false;
			}
		}

		snd_pcm_uframes_t periodsize = this->chunkSize / 2;
		snd_pcm_uframes_t buffersize;
		snd_pcm_uframes_t exact_buffersize;

		/* Set number of periods. Periods used to be called fragments. */ 
		if( (err = snd_pcm_hw_params_set_periods(this->tCaptureHandle, tCaptureParams, this->periodSize, 0)) < 0) {
			fprintf(stderr, "ALSA - error setting periods: %s\n", snd_strerror(err));

			return false;
		}

		buffersize = (periodsize * this->periodSize) >> 2;
		exact_buffersize = buffersize;
		fprintf(stderr, "ALSA - buffersize: %d\n", buffersize);

		if(flag ) {
			if ( snd_pcm_hw_params_set_buffer_size_near(this->tCaptureHandle, tCaptureParams, &exact_buffersize) < 0 ) {
				fprintf(stderr, "Error setting buffersize.\n");

				//return false;
			}
		}

		if ( buffersize != exact_buffersize ) {
			fprintf(stderr, "The buffersize %lu bytes is not supported by your hardware.\n"
					"==> Using %lu bytes instead.\n", buffersize, exact_buffersize);
			periodsize = (exact_buffersize << 2) / this->periodSize;
			fprintf(stderr, "periodSize : %d\n", (int)periodsize);
		}

		if( (err = snd_pcm_nonblock(this->tCaptureHandle, 0)) < 0 ) {
			fprintf(stderr, "ALSA - nonblock failed : %s\n", snd_strerror(err));

			return false;
		}

		if( (err = snd_pcm_hw_params(this->tCaptureHandle, tCaptureParams)) < 0 ) {
			fprintf(stderr, "ALSA - cannot set parameters : %s\n", snd_strerror(err));

			return false;
		}

		snd_pcm_hw_params_get_channels(tCaptureParams, 	  &this->pcmChannels);
		snd_pcm_hw_params_get_buffer_time(tCaptureParams, &this->pcmBufferSize, 0);
		snd_pcm_hw_params_get_period_time(tCaptureParams, &this->pcmPeriodSize, 0);

		snd_pcm_hw_params_free(tCaptureParams);

		if( (err = snd_pcm_prepare(this->tCaptureHandle)) < 0 ) { 
			fprintf(stderr, "ALSA - cannot prepare audio interface for use : %s\n", snd_strerror(err));

			return false;
		}

		this->frameBytes = snd_pcm_frames_to_bytes(this->tCaptureHandle, 1); 

		return true;
	}

	void PcmCapture::execute(void) {
		int 	err, rc;
		int		pcmLength;
		// struct 	timeval before, after;	// 동작 시간 측정용

		fprintf(stderr, "ALSA - start alsa device capture : [%d] %s\n", this->idx, this->deviceName);

		/////////////////////////////////////////
		const int NUM_EXT_PORT		= 4000;
		const int NUM_GATEWAY_PORT	= 2100;
		
		int     clientSockFd;
		int		extId = NUM_EXT_PORT;

		struct sockaddr_in  tServerAddr;

		struct ORDER_PACKET {
			char    cmd;
			char    rsvd[3];
			int     bodyLen;
		} typedef ORDER_PACKET_t;
		
		ORDER_PACKET_t  tSendPacket;

		////////////////////////////////////////	
		int idx;
		while( true ) {
			// gettimeofday(&before , NULL);
			pcmLength = this->chunkSize / this->frameBytes;

			if( (err = snd_pcm_readi(this->tCaptureHandle, this->readPcmData, pcmLength)) < 0 ) {
				fprintf(stderr, "ALSA - read from audio interface failed : [%02d] %s\n", err, snd_strerror(err));

				if(this->setPcmHandle() == false)
					break;

				continue;
			}

			if( this->tPcmCapture.flagMode == Common::FLAG_FILE_MODE ) {
				bzero(this->readPcmData, this->chunkSize);
				rc = fread(this->readPcmData, 1, this->chunkSize, this->tPcmCapture.pcmFile);

				if( rc != this->chunkSize ) {
				//	fseek(this->tPcmCapture.pcmFile, 0, SEEK_SET);
					this->tPcmCapture.flagMode == Common::FLAG_READ_MODE; 

					fclose(this->tPcmCapture.pcmFile);
				}
			}

			// gettimeofday(&after , NULL);
			// fprintf(stderr, "%d : %.0lf us\n" , (int)err, time_diff(before , after) ); 

			stackServer[this->idx].setQueueInStack(this->readPcmData);
			
			///////////////////////////////
			memset(&tSendPacket, 0x00, sizeof(tSendPacket));

			if( (clientSockFd = socket(AF_INET, SOCK_STREAM, 0)) < 0 ) {
				fprintf(stderr, "SendToWebBlock() socket() failed : [%02d] %s\n", errno, strerror(errno));
				
				continue;
			}

			memset(&tServerAddr, 0x00, sizeof(tServerAddr));
			tServerAddr.sin_family     = AF_INET;
			tServerAddr.sin_port       = htons(NUM_GATEWAY_PORT);
			tServerAddr.sin_addr.s_addr= inet_addr("127.0.0.1");

			if( connect(clientSockFd, (struct sockaddr*)&tServerAddr, sizeof(tServerAddr)) < 0 ) {
				fprintf(stderr, "SendToWebBlock() connect() failed : [%02d] %s\n", errno, strerror(errno));

				close(clientSockFd);
				
				continue;
			}
					
			if( send(clientSockFd, &extId, sizeof(extId), 0) < 0 ) {
				fprintf(stderr, "SendToWebBlock() send() port failed : [%02d] %s\n", errno, strerror(errno));

				close(clientSockFd);
				continue;
			}

			tSendPacket.cmd = 0;
			tSendPacket.bodyLen = this->chunkSize;

			if( send(clientSockFd, &tSendPacket, sizeof(tSendPacket), MSG_DONTWAIT) < 0 ) {
				fprintf(stderr, "send raw data send() head failed : [%02d] %s\n", errno, strerror(errno));
				close(clientSockFd);
				continue;
			}
			
		//	printf("\n");
			/*

			for( idx = 0; idx < this->chunkSize/2 ; idx=idx+2 ) {
				 short d1 = this->readPcmData[idx];
				 short d2 = this->readPcmData[idx+1];
				 short d = d2 << 8 | d1;
				{
				//	printf("%d	%x	%04x(%5d)	%04x(%5d)	%04x(%5d)\n", idx, &this->readPcmData[idx], d1, d1, d2, d2, d, d);
					printf("%5d\n",d);
				}
			}
			*/
			/*
			for( idx = 0; idx < this->chunkSize ; idx++ ) {
				this->readPcmData[idx] = idx % 10 + 250;
			}
			*/
			if( send(clientSockFd, this->readPcmData, this->chunkSize, MSG_DONTWAIT) < 0 ) {
				fprintf(stderr, "send raw data send() body failed : [%02d] %s\n", errno, strerror(errno));
				close(clientSockFd);
				continue;
			}
			
			close(clientSockFd);
			///////////////////////////////////////
		}
		
		common.term();
		
		return ;
	} 

	bool PcmCapture::setPlayMode(const char *_fileName) {
		if( _fileName == NULL ) {
			fprintf(stderr, "Set play mode : PCM\n");
			this->tPcmCapture.flagMode = Common::FLAG_READ_MODE;

		} else {
			fprintf(stderr, "Set play mode : file [%s]\n", _fileName);
			if( access(_fileName, F_OK) < 0 ) {
				fprintf(stderr, "Server - PCM file : [%s] acccess failed : [%02d] %s\n", _fileName, errno, strerror(errno));

				return false;
			}

			this->tPcmCapture.flagMode = Common::FLAG_FILE_MODE;

			if( this->tPcmCapture.pcmFile != NULL ) {
				fclose(this->tPcmCapture.pcmFile);
			}
			this->tPcmCapture.pcmFile  = fopen(_fileName, "rb");
		}

		return true;
	}

	unsigned int PcmCapture::getPcmBufferSize(void) {

		return this->pcmBufferSize;
	}

	unsigned int PcmCapture::getPcmPeriodSize(void) {

		return this->pcmPeriodSize;
	}

	ServerFunc::ServerFunc(void) {
		fprintf(stderr, "Server Instance created\n");
		this->queueCnt = 0;

		return ;
	}

	ServerFunc::~ServerFunc(void) {
		this->stop();

		return ;
	}

	void ServerFunc::init(SERVER_INFO_t *_tServerInfo) {
		common.setServerInfo(_tServerInfo);

		// Queue parameter
		this->queueCnt 		= _tServerInfo->queueCnt;
		this->bufferRate 	= _tServerInfo->bufferRate;
		
		if( _tServerInfo->mp3_mode ) {
			this->tPlayInfo.encodeMode  = Common::MODE_MP3_STREAM;
			this->chunkSize     		= _tServerInfo->mp3_chunkSize;

		} else {
			this->tPlayInfo.encodeMode  = Common::MODE_PCM_STREAM;
			this->chunkSize				= _tServerInfo->chunkSize;

		}

		// PCM parameter
		this->tPlayInfo.rate      = _tServerInfo->sampleRate;
		this->tPlayInfo.channels  = _tServerInfo->channels;
		this->tPlayInfo.chunkSize = _tServerInfo->chunkSize;
	
		this->tEncInfo.chunkSize  = _tServerInfo->mp3_chunkSize;
		this->tEncInfo.bitRate    = _tServerInfo->mp3_bitRate;
		this->tEncInfo.sampleRate = _tServerInfo->mp3_sampleRate;

		// Server parameter
 		if( strcmp(_tServerInfo->castType, "unicast") == 0 ) {
			this->tSocketInfo.typeProtocol 	= Common::TYPE_PROTOCOL_TCP;
			this->tSocketInfo.typeCast	 	= SOCK_STREAM;
			strcpy(this->tSocketInfo.ipAddr, _tServerInfo->ipAddr);

		} else {
			this->tSocketInfo.typeProtocol 	= Common::TYPE_PROTOCOL_UDP;
			this->tSocketInfo.typeCast	 	= SOCK_DGRAM;
			_tServerInfo->clientCnt			= 1;	// multicast 시 고정

			strcpy(this->tSocketInfo.ipAddr, _tServerInfo->ipAddr);
		}

		this->tSocketInfo.port		 = _tServerInfo->port;
		this->tSocketInfo.clientCnt	 = _tServerInfo->clientCnt;

		this->typePlayMode	= _tServerInfo->typePlayMode;
		strcpy(this->fileName, _tServerInfo->fileName);

		strcpy(this->deviceName, _tServerInfo->deviceName);

		return ;

	}

	void ServerFunc::run(void) {

		// Queue Stack 생성
		stackServer = new QueueStack[this->queueCnt];
		for( int idx = 0 ; idx < this->queueCnt ; idx++ ) {
			if( this->tPlayInfo.encodeMode == Common::MODE_MP3_STREAM )
				stackServer[idx].init(idx, this->bufferRate, this->tEncInfo.chunkSize);
			else
				stackServer[idx].init(idx, this->bufferRate, this->chunkSize);
		}


		// PCM capture thread 호출
		pcmCapture = new PcmCapture[this->queueCnt];
		mp3Encode = new Mp3Encode[this->queueCnt];

		char *ptrDevice;
		ptrDevice = strtok(this->deviceName, ",");

		for( int idx = 0 ; idx < this->queueCnt ; idx++ ) {
			if( ptrDevice == NULL ) ptrDevice = (char *)"default";

			if( this->tPlayInfo.encodeMode == Common::MODE_MP3_STREAM ) {
				if(!pcmCapture[idx].init(idx, ptrDevice, this->tEncInfo.chunkSize, this->tEncInfo.sampleRate, this->tPlayInfo.channels))
					common.term();

				mp3Encode[idx].initMp3(idx, this->tEncInfo.bitRate, this->tEncInfo.sampleRate);

			} else {
				if(!pcmCapture[idx].init(idx, ptrDevice, this->tPlayInfo.chunkSize, this->tPlayInfo.rate, this->tPlayInfo.channels))
					common.term();
					
			}

			if( this->typePlayMode ) {
				pcmCapture[idx].setPlayMode(this->fileName);
			}

			pcmCapture[idx].run();
			
			if( this->tPlayInfo.encodeMode == Common::MODE_MP3_STREAM )	
			mp3Encode[idx].runMp3Stream();

			ptrDevice = strtok(NULL, ",");
		}

		this->tPlayInfo.pcmBufferSize = pcmCapture[0].getPcmBufferSize();
		this->tPlayInfo.pcmPeriodSize = pcmCapture[0].getPcmPeriodSize();

		// 전송 thread 호출
		this->txThread = thread(&ServerFunc::sendThread, this); 
		txThread.detach();
		
		this->txThread = thread(&ServerFunc::sendThreadMulti, this); 
		txThread.detach();
		
		return ;
	}

	void ServerFunc::stop(void) {
		for( int idx = 0 ; idx < this->queueCnt ; idx++ ) {
			stackServer[idx].freeStack();
		}

		common.term();

		return ;
	}

	bool ServerFunc::setPlayMode(int _idx, char *_fileName) {

		return pcmCapture[_idx].setPlayMode(_fileName);
	}	

	bool ServerFunc::setStackIdx(int _threadIdx, int _queueIdx) {
		if( this->pThreadClient != NULL ) {
			if( _threadIdx > this->tSocketInfo.clientCnt ||  _queueIdx >= this->queueCnt ) {
				return false;
			}

			this->pThreadClient[_threadIdx].setStackIdx(_queueIdx);

		} else {
			return false;
		}

		return true;
	}

	char *ServerFunc::getClientList(void) {

		return common.getClientList();
	}	

	void ServerFunc::sendThreadMulti() {

		CLIENT_INFO_t	tClientInfo;		

		SocketServer servInstanceMulti;
		common.setTransInstanceMul(&servInstanceMulti);

		// Get server info
		if( !servInstanceMulti.setInfoMulti(&this->tSocketInfo) ) {
			common.term();
		}

		// Init server
		if( !servInstanceMulti.initMulti() ) {
			common.term();
		}

		ThreadClient arrThreadClientMulti;	

		common.setThreadInstanceMul(&arrThreadClientMulti);

		arrThreadClientMulti.setPlayInfo(0, 1, &this->tPlayInfo, &this->tEncInfo);

		// UDP Multicast 
		tClientInfo.socketFd = servInstanceMulti.getServerSocketFd();
		strcpy(tClientInfo.ipAddr, servInstanceMulti.getServerIpAddr());

		arrThreadClientMulti.setThreadClient(tClientInfo);
		arrThreadClientMulti.setSockAddr(servInstanceMulti.getServerSockAddrMulti());

		arrThreadClientMulti.multiRun();

		while( !common.getFlagTerm() ) {
			sleep(1);
		};

	}

	void ServerFunc::sendThread() {
		int	clientCnt, connCnt;
		int	idx;
		bool flagUsed;

		CLIENT_INFO_t	tClientInfo;		

		SocketServer servInstance;
		common.setTransInstance(&servInstance);

		// Get server info
		if( !servInstance.setInfo(&this->tSocketInfo) ) {
			common.term();
		}

		// Init server
		if( !servInstance.init() ) {
			common.term();
		}

		clientCnt = servInstance.getClientCnt();
		common.setMaxThreadIntance(clientCnt);

		ThreadClient arrThreadClient[clientCnt];	

		this->pThreadClient = arrThreadClient;

		for( idx = 0 ; idx < clientCnt ; idx++ ) {
			common.setThreadInstance(&arrThreadClient[idx]);

			arrThreadClient[idx].setPlayInfo(idx, this->tSocketInfo.typeProtocol, &this->tPlayInfo, &this->tEncInfo);
		}

		// TCP Unicast
		while( !common.getFlagTerm() ) {
			if( !servInstance.getSelect() ) {

				continue;
			}

			if( servInstance.getAccept(&tClientInfo) )  {

				connCnt = 0;
				for( idx = 0 ; idx < clientCnt ; idx++ ) {
					if( arrThreadClient[idx].getUseStatus() ) {
						connCnt++;
					}
				}

				if( connCnt >= clientCnt ) {
					flagUsed = false;
					for( idx = 0 ; idx < clientCnt ; idx++ ) {
						if( strcmp(arrThreadClient[idx].tClientInfo.ipAddr, tClientInfo.ipAddr) == 0
								&& arrThreadClient[idx].tClientInfo.socketFd != -1 ) {
							arrThreadClient[idx].stop();
							flagUsed = true;
							break;
						}
					}

					if( !flagUsed ) {	
						fprintf(stderr, "[%d/%d] Connection exceeded \n", connCnt, clientCnt);

						continue;
					}
				}

				for( idx = 0 ; idx < clientCnt ; idx++ ) {
					if( !arrThreadClient[idx].getUseStatus() ) {
						tClientInfo.idx = idx;

						arrThreadClient[idx].setUseStatus(true);
						arrThreadClient[idx].setThreadClient(tClientInfo);
						arrThreadClient[idx].uniRun();

						break;
					}
				}
			} else 
				continue;
		}
		return ;
	}

#if 1
	int Mp3Encode::initMp3(int _queueIdx, short _bitRate, int _sampleRate) {

		this->queueIdx			= _queueIdx;
		
		this->headerCount		= 0;
		this->queueCount		= 0;
		this->flagMp3Rewind		= false;
		this->flagMp3QueueLoop 	= false;

		this->mp3GetFrame		= NULL;
		this->index				= 0;
		this->socketFd			= -1;

		this->bitRate			= _bitRate;
		this->sampleRate		= _sampleRate;

		this->channels			= 2;
		this->mbScale			= 5;

		fprintf(stderr, "MP3 Encode index : %d\n", this->queueIdx);

		return 0;
	}
		
		int Mp3Encode::initGetMp3Frame(MP3_IDX_INFO_t *_mp3Idx, short _bitRate, int _sampleRate) {

		_mp3Idx->index			= 0;
		_mp3Idx->mp3Frame		= NULL;
		_mp3Idx->socketFd		= -1;
		
		this->bitRate			= _bitRate;
		this->sampleRate		= _sampleRate;

		fprintf(stderr, "MP3 Info : %d %d\n",this->bitRate ,this->sampleRate);

		return 0;
	}

	void Mp3Encode::runMp3Stream() {
	
		this->mp3Thread = thread(&Mp3Encode::procMp3Stream, this); 
		mp3Thread.detach();
	}

	/*****************************
	FUNC : procMp3Stream()
	DESC : thread function, encoding mp3
	 ******************************/
	void Mp3Encode::procMp3Stream() {
		int 	numSamples, numChannels;
		int		recvCnt;
		int		remainFrame;
		int 	queueIdx = 0;
		int		frameLen = 0, headLen = 0;
		int		queueSize, mbScale;
		int		queueStackSize, queueLastSize;
		int		recvFlag = stackServer[this->queueIdx].getRecvCntFlag();
		int		rewindMp3Flag = false;
		int		rewindPcmFlag = false;
		int		setQueueFlag  = FLAG_EVEN;
		int		stackSize = stackServer[this->queueIdx].getStackSize();
		int		nonIdxCnt = 0;

		MP3E_INT8			frameHeader[SIZE_FRAME_HEAD];
		MP3E_RET_VAL 		val;
		MP3E_Encoder_Config tEncConfig;
		MP3_HEADER_t		tMp3Header;

		memset(&tMp3Header, 0x00, sizeof(tMp3Header));

#if DEBUG_MSG
		int		idx;
#endif
#if MP3_OUTPUT
		gOutputFd = fopen(OUTPUT_PATH_MP3_SAMPLE, "wb");
		
		/////////////////////////////////////////
				const int NUM_EXT_PORT		= 4000;
				const int NUM_GATEWAY_PORT	= 2100;
				
				int     clientSockFd;
				int		extId = NUM_EXT_PORT;

				struct sockaddr_in  tServerAddr;

				struct ORDER_PACKET {
					char    cmd;
					char    rsvd[3];
					int     bodyLen;
				} typedef ORDER_PACKET_t;
				
				ORDER_PACKET_t  tSendPacket;

				////////////////////////////////////////	
				
#endif

		tEncConfig.instance_id = 0;

		mp3e_query_mem(&tEncConfig);
		this->encoderMemInfoAlloc(&tEncConfig);

		this->tEncParams.app_bit_rate 	   = this->bitRate;
		this->tEncParams.app_sampling_rate = this->sampleRate;
		this->tEncParams.app_mode 		   = DFLT_ENCODE_MODE;

		if( (val = mp3e_encode_init(&this->tEncParams, &tEncConfig)) ) {
			this->getMp3Error(val);
			// TODO. thread close
			// CloseProc();
		}

		// 샘플링 레이트 획득
		// 44.1k fixed 이기 때문에 1152 로 사용
		numSamples = (this->tEncParams.app_sampling_rate <= DIV_SAMPLING_RATE) ? MIN_SAMPLING_RATE : MAX_SAMPLING_RATE;

		numChannels = this->channels;
		this->pcmFrame  = (MP3E_INT16 *)malloc((numSamples * numChannels) * sizeof(MP3E_INT16));
		this->mp3Frame  = (MP3E_INT8 *)malloc((this->tEncParams.mp3e_outbuf_size) * sizeof(MP3E_INT8));

		mbScale = this->mbScale;

		queueSize      = (this->tEncParams.app_bit_rate * 1000) * MAX_SCALE_LAYER / this->tEncParams.app_sampling_rate + 1; 
		this->queueCount    = (SIZE_MEGA_BYTE / queueSize) * mbScale;
		queueStackSize = queueSize * this->queueCount;

		// MP3 linear queue stack
		this->mp3Queue  = (MP3E_INT8 *)malloc(queueStackSize * sizeof(MP3E_INT8));

		// MP3 linear frame size stack
		this->gArrFrameInfo = (MP3_FRAME_INFO_t *)malloc(this->queueCount * sizeof(MP3_FRAME_INFO_t));

		queueIdx = stackServer[this->queueIdx].getRecvCnt();

		while( !common.getFlagTerm() ) {
			recvCnt = stackServer[this->queueIdx].getRecvCnt();
			if( (queueIdx >= recvCnt && recvFlag == stackServer[this->queueIdx].getRecvCntFlag()) || recvCnt == stackSize ) {
				usleep(SLEEP_TIME);
				continue;
			}

			// PCM to MP3 encoding 
			memmove(this->pcmFrame, stackServer[this->queueIdx].getQueueInStack(), numSamples * numChannels * MAXIMUM_INSTANCES);
			stackServer[this->queueIdx].incSendCnt();	
		//	fprintf(stderr, "numSamples %d ,  %d\n", numSamples, (numSamples * numChannels * MAXIMUM_INSTANCES));

			mp3e_encode_frame(this->pcmFrame, &tEncConfig, this->mp3Frame);

			// 전체 queue 에 encoding 처리된 MP3 frame 추가.
			if( (frameLen + tEncConfig.num_bytes) > queueStackSize ) {
				queueLastSize = queueStackSize - frameLen;		
				memmove(this->mp3Queue + frameLen, this->mp3Frame, queueLastSize);
				frameLen = 0;

				if( rewindPcmFlag == true ) rewindPcmFlag = false;
				else rewindPcmFlag = true;

				memmove(this->mp3Queue + frameLen, this->mp3Frame + queueLastSize, tEncConfig.num_bytes - queueLastSize);
				frameLen = tEncConfig.num_bytes - queueLastSize;

			} else {
				memmove(this->mp3Queue + frameLen, this->mp3Frame, tEncConfig.num_bytes);
				frameLen += tEncConfig.num_bytes;
			}

#if MP3_OUTPUT
			// encoding 된 frame 바로 파일에 저장
			// fwrite(this->mp3Frame, sizeof(char), tEncConfig.num_bytes, gOutputFd);
#endif
			bzero(this->mp3Frame, this->tEncParams.mp3e_outbuf_size);

			// set MP3 encode info 
			// MP3 프레임 사이즈가 encoding 된 MP3 프레임보다 사이즈가 크기 때문에
			// 대기 시간 전체 queue 에 대한 대기 시간이 필요.
			// case 1 : headLen + queueSize 가 frameLen 보다 큰 경우 대기.
			// case 2 : case 1 에서 frameLen 이 loop 를 먼저 돈 경우 같이 돔.
			if( headLen + queueSize > frameLen && rewindMp3Flag == rewindPcmFlag ) {
#if DEBUG_MSG
				fprintf(stderr, "waiting for fill the frame in the queue stack..[%d/%d/%d] [%d/%d]\n",  
						headLen + queueSize, frameLen, headLen + queueSize - frameLen, rewindMp3Flag, rewindPcmFlag);
#endif
				// PCM queue 관련 처리
				if( ++queueIdx == stackSize ) {
					queueIdx = 0; 

					if( recvFlag == true ) recvFlag = false;
					else recvFlag = true;
				}

				usleep(SLEEP_TIME);
				continue;

			} else if( this->parseMp3Header(this->mp3Queue + headLen, &tMp3Header) == true ) {		
				// MP3 AAU header 파싱이 가능한 경우(정상 case, queue 마지막 case)
				// 1. 읽은 부분이 AAU header 일 때

				if( headLen + this->getFrameSize(&tMp3Header) < queueStackSize ) {
					// 1.1. headLen + frame 사이즈가 전체 queue 사이즈보다 클 때 (일반 상황의 경우)

					this->gArrFrameInfo[this->headerCount].frameLen  = headLen;
					this->gArrFrameInfo[this->headerCount].frameSize = this->getFrameSize(&tMp3Header);
					this->gArrFrameInfo[this->headerCount].frameFlag = false;
#if DEBUG_MSG
					headLen += this->getFrameSize(&tMp3Header);
					printf("[%d/%d]/[%d/%d]/[%d/%d/%d] - %d : ", 
							this->headerCount, this->queueCount,
							headLen, queueStackSize, 
							this->gArrFrameInfo[this->headerCount].frameSize, queueStackSize - headLen, frameLen,
							this->gArrFrameInfo[this->headerCount].frameFlag);

					headLen -= this->getFrameSize(&tMp3Header);
					for( idx = 0 ; idx < 7 ; idx++ ) {
						printf("%02x ", (this->mp3Queue + headLen)[idx]);
					}
					printf("\n");
#endif
					headLen += this->getFrameSize(&tMp3Header);

					if( headLen == queueStackSize ) {
						headLen = 0;
					}

				} else {
					// 1.2. headLen + frame 사이즈가 전체 queue 사이즈보다 클 때 (Rewind 상황의 경우)
					// 전체 queue 사이즈에 맞는 만큼만 채우고 다음 index 로 넘어 감.
					// 다음 index 와 합쳐야 하기 때문에 frameFlag 를 TRUE 로 설정.
					// 전체 queue 사이즈를 채웠기 때문에 headLen 은 0 으로 초기화.

					remainFrame = (headLen + this->getFrameSize(&tMp3Header)) - queueStackSize;
					this->gArrFrameInfo[this->headerCount].frameLen  = headLen;
					this->gArrFrameInfo[this->headerCount].frameSize = queueStackSize - headLen;
					this->gArrFrameInfo[this->headerCount].frameFlag = true;
#if DEBUG_MSG
					headLen = 0;
					printf("[%d/%d]/[%d/%d]/[%d/%d/%d] - %d : ", 
							this->headerCount, this->queueCount,
							headLen, queueStackSize, 
							this->gArrFrameInfo[this->headerCount].frameSize, queueStackSize - headLen, frameLen,
							this->gArrFrameInfo[this->headerCount].frameFlag);

					headLen = this->gArrFrameInfo[this->headerCount].frameLen;
					for( idx = 0 ; idx < 7 ; idx++ ) {
						printf("%02x ", (this->mp3Queue + headLen)[idx]);
					}
					printf("\n");
#endif
					headLen = 0;
					if( rewindMp3Flag == true ) rewindMp3Flag = false;
					else rewindMp3Flag = true;
#if MP3_OUTPUT
					// AAU header 기준으로 파일에 저장
					// fwrite(this->mp3Queue + this->gArrFrameInfo[this->headerCount].frameLen, sizeof(char), this->gArrFrameInfo[this->headerCount].frameSize, gOutputFd);
					
					///////////////////////////////
					memset(&tSendPacket, 0x00, sizeof(tSendPacket));

					if( (clientSockFd = socket(AF_INET, SOCK_STREAM, 0)) < 0 ) {
						fprintf(stderr, "SendToWebBlock() socket() failed : [%02d] %s\n", errno, strerror(errno));
									
						continue;
					}

					memset(&tServerAddr, 0x00, sizeof(tServerAddr));
					tServerAddr.sin_family     = AF_INET;
					tServerAddr.sin_port       = htons(NUM_GATEWAY_PORT);
					tServerAddr.sin_addr.s_addr= inet_addr("127.0.0.1");

					if( connect(clientSockFd, (struct sockaddr*)&tServerAddr, sizeof(tServerAddr)) < 0 ) {
						fprintf(stderr, "SendToWebBlock() connect() failed : [%02d] %s\n", errno, strerror(errno));

						close(clientSockFd);
									
						continue;
					}
										
					if( send(clientSockFd, &extId, sizeof(extId), 0) < 0 ) {
						fprintf(stderr, "SendToWebBlock() send() port failed : [%02d] %s\n", errno, strerror(errno));

							close(clientSockFd);
						continue;
					}

					tSendPacket.cmd = 0;
					tSendPacket.bodyLen = this->gArrFrameInfo[this->headerCount].frameSize;

					if( send(clientSockFd, &tSendPacket, sizeof(tSendPacket), MSG_DONTWAIT) < 0 ) {
						fprintf(stderr, "send raw data send() head failed : [%02d] %s\n", errno, strerror(errno));
						close(clientSockFd);
						continue;
					}
							
					if( send(clientSockFd, (this->mp3Queue + this->gArrFrameInfo[this->headerCount].frameLen), this->gArrFrameInfo[this->headerCount].frameSize, MSG_DONTWAIT) < 0 ) {
						fprintf(stderr, "send raw data send() body failed : [%02d] %s\n", errno, strerror(errno));
						close(clientSockFd);
						continue;
					}
							
					close(clientSockFd);
					///////////////////////////////
					
#endif
					// 다음 index 처리
					if( ++this->headerCount == this->queueCount ) {
						// this->headerCount가 도는 케이스도 처리
						this->headerCount  = 0;
						this->flagMp3QueueLoop = true;


						if( setQueueFlag % FLAG_EVEN == 0 ) {
							setQueueFlag = FLAG_ODD;
							this->flagMp3Rewind = true;

						} else {
							setQueueFlag = FLAG_EVEN;
							this->flagMp3Rewind = false;
						}
					}

					// 이 전 index 에서 남은 size 만큼 채우고 headLen 처리.
					this->gArrFrameInfo[this->headerCount].frameLen  = headLen;
					this->gArrFrameInfo[this->headerCount].frameSize = remainFrame;
					this->gArrFrameInfo[this->headerCount].frameFlag = false;
#if DEBUG_MSG
					headLen = remainFrame;
					printf("[%d/%d]/[%d/%d]/[%d/%d/%d] - %d : ", 
							this->headerCount, this->queueCount,
							headLen, queueStackSize, 
							this->gArrFrameInfo[this->headerCount].frameSize, queueStackSize - headLen, frameLen,
							this->gArrFrameInfo[this->headerCount].frameFlag);

					headLen = 0;
					for( idx = 0 ; idx < 7 ; idx++ ) {
						printf("%02x ", (this->mp3Queue + headLen)[idx]);
					}
					printf("\n");
#endif
					headLen = remainFrame;
				}

			} else if( queueStackSize - headLen > 0 ) {
				// 2. MP3 header 사이즈가 오버하거나 못찾는 케이스
				// headLen + queueSize 가 전체 queue 사이즈를 오버하는 경우
				// frameLen 마지막 사이즈가 4 bytes 미만인 경우
				// queueStackSize 는 frame 전체 사이즈와 같다.

				queueLastSize = queueStackSize - headLen;

				if( queueLastSize < SIZE_FRAME_HEAD ) {
					// 2.1. 남은 queue 가 1 ~ 3 bytes 로 header 4bytes 획득 가능한 경우
					// 남은 queue 가 0 bytes 일땐 정상 case 에서 순환 처리

					memmove(frameHeader, this->mp3Queue + headLen, queueLastSize);	
					memmove(frameHeader + queueLastSize, this->mp3Queue, SIZE_FRAME_HEAD - queueLastSize);	
					this->parseMp3Header(frameHeader, &tMp3Header);

				} else if( queueLastSize >= SIZE_FRAME_HEAD ) {
					// 2.2. 남은 queue 가 4 bytes 이상으로 header 4bytes 획득 가능한 경우

					this->parseMp3Header(this->mp3Queue + headLen, &tMp3Header);
				}

				// headLen + frame 사이즈가 전체 queue 사이즈보다 클 때 (Rewind 상황의 경우)
				// 전체 queue 사이즈에 맞는 만큼만 채우고 다음 index 로 넘어 감.
				// 다음 index 와 합쳐야 하기 때문에 frameFlag 를 true 로 설정.
				// 전체 queue 사이즈를 채웠기 때문에 headLen 은 0 으로 초기화.

				this->gArrFrameInfo[this->headerCount].frameLen  = headLen;
				this->gArrFrameInfo[this->headerCount].frameSize = queueLastSize;
				this->gArrFrameInfo[this->headerCount].frameFlag = true;
#if DEBUG_MSG
				headLen = 0;
				printf("[%d/%d]/[%d/%d]/[%d/%d/%d] - %d : ", 
						this->headerCount, this->queueCount,
						headLen, queueStackSize, 
						this->gArrFrameInfo[this->headerCount].frameSize, queueStackSize - headLen, frameLen,
						this->gArrFrameInfo[this->headerCount].frameFlag);

				headLen = this->gArrFrameInfo[this->headerCount].frameLen;
				for( idx = 0 ; idx < 7 ; idx++ ) {
					printf("%02x ", (this->mp3Queue + headLen)[idx]);
				}
				printf("\n");
#endif
				headLen = 0;
				if( rewindMp3Flag == true ) rewindMp3Flag = false;
				else rewindMp3Flag = true;
#if MP3_OUTPUT
				// AAU header 기준으로 파일에 저장
				// fwrite(this->mp3Queue + this->gArrFrameInfo[this->headerCount].frameLen, sizeof(char), this->gArrFrameInfo[this->headerCount].frameSize, gOutputFd);
				
				///////////////////////////////
									memset(&tSendPacket, 0x00, sizeof(tSendPacket));

									if( (clientSockFd = socket(AF_INET, SOCK_STREAM, 0)) < 0 ) {
										fprintf(stderr, "SendToWebBlock() socket() failed : [%02d] %s\n", errno, strerror(errno));
													
										continue;
									}

									memset(&tServerAddr, 0x00, sizeof(tServerAddr));
									tServerAddr.sin_family     = AF_INET;
									tServerAddr.sin_port       = htons(NUM_GATEWAY_PORT);
									tServerAddr.sin_addr.s_addr= inet_addr("127.0.0.1");

									if( connect(clientSockFd, (struct sockaddr*)&tServerAddr, sizeof(tServerAddr)) < 0 ) {
										fprintf(stderr, "SendToWebBlock() connect() failed : [%02d] %s\n", errno, strerror(errno));

										close(clientSockFd);
													
										continue;
									}
														
									if( send(clientSockFd, &extId, sizeof(extId), 0) < 0 ) {
										fprintf(stderr, "SendToWebBlock() send() port failed : [%02d] %s\n", errno, strerror(errno));

											close(clientSockFd);
										continue;
									}

									tSendPacket.cmd = 0;
									tSendPacket.bodyLen = this->gArrFrameInfo[this->headerCount].frameSize;

									if( send(clientSockFd, &tSendPacket, sizeof(tSendPacket), MSG_DONTWAIT) < 0 ) {
										fprintf(stderr, "send raw data send() head failed : [%02d] %s\n", errno, strerror(errno));
										close(clientSockFd);
										continue;
									}
											
									if( send(clientSockFd, (this->mp3Queue + this->gArrFrameInfo[this->headerCount].frameLen), this->gArrFrameInfo[this->headerCount].frameSize, MSG_DONTWAIT) < 0 ) {
										fprintf(stderr, "send raw data send() body failed : [%02d] %s\n", errno, strerror(errno));
										close(clientSockFd);
										continue;
									}
											
									close(clientSockFd);
									///////////////////////////////
									
#endif
				// 다음 index 처리
				if( ++this->headerCount == this->queueCount ) {
					// this->headerCount가 도는 케이스도 처리
					this->headerCount = 0;
					this->flagMp3QueueLoop = true;

					if( setQueueFlag % FLAG_EVEN == 0 ) {
						setQueueFlag = FLAG_ODD;
						this->flagMp3Rewind = true;

					} else {
						setQueueFlag = FLAG_EVEN;
						this->flagMp3Rewind = false;
					}
				}

				// 이 전 index 에서 남은 size 만큼 채우고 headLen 처리.
				remainFrame = this->getFrameSize(&tMp3Header) - queueLastSize;
				this->gArrFrameInfo[this->headerCount].frameLen  = headLen;
				this->gArrFrameInfo[this->headerCount].frameSize = remainFrame;
				this->gArrFrameInfo[this->headerCount].frameFlag = false;
#if DEBUG_MSG
				headLen = remainFrame;
				printf("[%d/%d]/[%d/%d]/[%d/%d/%d] - %d : ", 
						this->headerCount, this->queueCount,
						headLen, queueStackSize, 
						this->gArrFrameInfo[this->headerCount].frameSize, queueStackSize - headLen, frameLen,
						this->gArrFrameInfo[this->headerCount].frameFlag);

				headLen = 0;
				for( idx = 0 ; idx < 7 ; idx++ ) {
					printf("%02x ", (this->mp3Queue + headLen)[idx]);
				}
				printf("\n");
#endif
				headLen = remainFrame;

			} else {
				// 3. 그 밖에 예외 케이스
				fprintf(stderr, "not find AAU header index..");	
				fprintf(stderr, "[%d/%d]/[%d/%d]/[%d/%d/%d] - %d : ", 
						this->headerCount, this->queueCount,
						headLen, queueStackSize, 
						this->gArrFrameInfo[this->headerCount].frameSize, queueStackSize - headLen, frameLen,
						this->gArrFrameInfo[this->headerCount].frameFlag);

				if( nonIdxCnt++ == 10 ) {
					break;
				}
			}

#if MP3_OUTPUT
			// AAU header 기준으로 파일에 저장
			// fwrite(this->mp3Queue + this->gArrFrameInfo[this->headerCount].frameLen, sizeof(char), this->gArrFrameInfo[this->headerCount].frameSize, gOutputFd);
			
			///////////////////////////////
								memset(&tSendPacket, 0x00, sizeof(tSendPacket));

								if( (clientSockFd = socket(AF_INET, SOCK_STREAM, 0)) < 0 ) {
									fprintf(stderr, "SendToWebBlock() socket() failed : [%02d] %s\n", errno, strerror(errno));
												
									continue;
								}

								memset(&tServerAddr, 0x00, sizeof(tServerAddr));
								tServerAddr.sin_family     = AF_INET;
								tServerAddr.sin_port       = htons(NUM_GATEWAY_PORT);
								tServerAddr.sin_addr.s_addr= inet_addr("127.0.0.1");

								if( connect(clientSockFd, (struct sockaddr*)&tServerAddr, sizeof(tServerAddr)) < 0 ) {
									fprintf(stderr, "SendToWebBlock() connect() failed : [%02d] %s\n", errno, strerror(errno));

									close(clientSockFd);
												
									continue;
								}
													
								if( send(clientSockFd, &extId, sizeof(extId), 0) < 0 ) {
									fprintf(stderr, "SendToWebBlock() send() port failed : [%02d] %s\n", errno, strerror(errno));

										close(clientSockFd);
									continue;
								}

								tSendPacket.cmd = 0;
								tSendPacket.bodyLen = this->gArrFrameInfo[this->headerCount].frameSize;

								if( send(clientSockFd, &tSendPacket, sizeof(tSendPacket), MSG_DONTWAIT) < 0 ) {
									fprintf(stderr, "send raw data send() head failed : [%02d] %s\n", errno, strerror(errno));
									close(clientSockFd);
									continue;
								}
										
								if( send(clientSockFd, (this->mp3Queue + this->gArrFrameInfo[this->headerCount].frameLen), this->gArrFrameInfo[this->headerCount].frameSize, MSG_DONTWAIT) < 0 ) {
									fprintf(stderr, "send raw data send() body failed : [%02d] %s\n", errno, strerror(errno));
									close(clientSockFd);
									continue;
								}
										
								close(clientSockFd);
								///////////////////////////////
#endif
			if( ++this->headerCount == this->queueCount ) {
				this->headerCount  = 0;
				this->flagMp3QueueLoop = true;

				if( setQueueFlag % FLAG_EVEN == 0 ) {
					setQueueFlag = FLAG_ODD;
					this->flagMp3Rewind = true;

				} else {
					setQueueFlag = FLAG_EVEN;
					this->flagMp3Rewind = false;
				}
			}
			
			// PCM queue 관련 처리
			if( ++queueIdx == stackSize ) {
				queueIdx = 0; 

				if( recvFlag == true ) recvFlag = false;
				else recvFlag = true;
			}


			usleep(SLEEP_TIME);
		}

		// TODO.
		// SetEncodeMp3Flag();

		return ;
	} // end of procMp3Stream()


	/*****************************
FUNC : encoderMemInfoAlloc()
DESC : memory free
	 ******************************/
	void Mp3Encode::encoderMemInfoAlloc(MP3E_Encoder_Config *_encConfig) {
		int instanceId = _encConfig->instance_id;

		this->W1[instanceId] = (char *)malloc(sizeof(char)*_encConfig->mem_info[0].size);
		_encConfig->mem_info[0].ptr = (int*)((unsigned int )(&this->W1[instanceId][0] + _encConfig->mem_info[0].align - 1 )
				& (0xffffffff ^ (_encConfig->mem_info[0].align - 1 )));

		this->W2[instanceId] = (char *)malloc(sizeof(char)*_encConfig->mem_info[1].size);
		_encConfig->mem_info[1].ptr = (int *)((unsigned int )(&this->W2[instanceId][0] + _encConfig->mem_info[1].align - 1 )
				& (0xffffffff ^ (_encConfig->mem_info[1].align - 1 )));

		this->W3[instanceId] = (char *)malloc(sizeof(char)*_encConfig->mem_info[2].size);
		_encConfig->mem_info[2].ptr = (int *)((unsigned int )(&this->W3[instanceId][0] + _encConfig->mem_info[2].align - 1 )
				& (0xffffffff ^ (_encConfig->mem_info[2].align - 1 )));

		this->W4[instanceId] = (char *)malloc(sizeof(char)*_encConfig->mem_info[3].size);
		_encConfig->mem_info[3].ptr = (int *)((unsigned int )(&this->W4[instanceId][0] + _encConfig->mem_info[3].align - 1 )
				& (0xffffffff ^ (_encConfig->mem_info[3].align - 1 )));

		this->W5[instanceId] = (char *)malloc(sizeof(char)*_encConfig->mem_info[4].size);
		_encConfig->mem_info[4].ptr = (int *)((unsigned int )(&this->W5[instanceId][0] + _encConfig->mem_info[4].align - 1 )
				& (0xffffffff ^ (_encConfig->mem_info[4].align - 1 )));

		this->W6[instanceId] = (char *)malloc(sizeof(char)*_encConfig->mem_info[5].size);
		_encConfig->mem_info[5].ptr = (int *)((unsigned int )(&this->W6[instanceId][0] + _encConfig->mem_info[5].align - 1 )
				& (0xffffffff ^ (_encConfig->mem_info[5].align - 1 )));

		return ;
	} // end of encoderMemInfoAlloc()


	/*****************************
FUNC : getMp3Error()
DESC : display mp3 error
	 ******************************/
	void Mp3Encode::getMp3Error(MP3E_RET_VAL _val) {
		if( _val == MP3E_ERROR_INIT_BITRATE ) {
			fprintf(stderr, "In_valid bitrate initialization\n");
			fprintf(stderr, "Possible bit rates are:\n");
			fprintf(stderr, "For MPEG1: 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320 kbps\n");

		} else if( _val == MP3E_ERROR_INIT_SAMPLING_RATE ) {
			fprintf(stderr, "In_valid sampling rate initialization\n");
			fprintf(stderr, "Possible sampling rates are:\n");
			fprintf(stderr, "32000, 44100, 48000\n");

		} else if( _val == MP3E_ERROR_INIT_MODE ) {
			fprintf(stderr, "In_valid mode initialization\n");
			fprintf(stderr, "Possible modes are: j or m\n");

		} else if( _val == MP3E_ERROR_INIT_FORMAT ) {
			fprintf(stderr, "In_valid input format type\n");
			fprintf(stderr, "Possible formats are: i or l\n");

		} else if( _val == MP3E_ERROR_INIT_QUALITY ) {
			fprintf(stderr, "In_valid configuration _value\n");
			fprintf(stderr, "Possible _values are: c or s\n");

		} else if( _val == MP3E_ERROR_INIT_BITRATE ) {
			fprintf(stderr, "In_valid bitrate initialization\n");
			fprintf(stderr, "Possible bit rates are:\n");
			fprintf(stderr, "For MPEG1: 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320 kbps\n");

		} else if( _val == MP3E_ERROR_INIT_SAMPLING_RATE ) {
			fprintf(stderr, "In_valid sampling rate initialization\n");
			fprintf(stderr, "Possible sampling rates are:\n");
			fprintf(stderr, "32000, 44100, 48000\n");

		} else if( _val == MP3E_ERROR_INIT_MODE ) {
			fprintf(stderr, "In_valid mode initialization\n");
			fprintf(stderr, "Possible modes are: j or m\n");

		} else if( _val == MP3E_ERROR_INIT_FORMAT ) { if (_val == MP3E_ERROR_INIT_FORMAT) { fprintf(stderr, "In_valid input format type\n");
			fprintf(stderr, "Possible formats are: i or l\n");
		}

		} else if( _val == MP3E_ERROR_INIT_QUALITY ) {
			fprintf(stderr, "In_valid configuration _value\n");
			fprintf(stderr, "Possible _values are: c or s\n");
		}

		return ;
	} // end of getMp3Error()


	/*****************************
FUNC : parseMp3Header()
DESC : parse mp3 buffer
	 ******************************/
	int Mp3Encode::parseMp3Header(const MP3E_INT8 *_mp3Frame, MP3_HEADER_t *_tMp3Header) {
		unsigned char *mp3Frame = (unsigned char *)_mp3Frame;
		if( !(((mp3Frame[0] & 0xFF) == 0xFF) && ((mp3Frame[1] >> 5) & 0x07) == 0x07) ) {
			return false;
		}

		_tMp3Header->version = (mp3Frame[1] & 0x08) >> 3;
		_tMp3Header->layer = 4 - ((mp3Frame[1] & 0x06) >> 1);
		_tMp3Header->errp = (mp3Frame[1] & 0x01);

		_tMp3Header->bitrate = gBitRates[(mp3Frame[2] & 0xf0) >> 4];
		_tMp3Header->freq = gSampleRates[(mp3Frame[2] & 0x0c) >> 2];
		_tMp3Header->pad = (mp3Frame[2] & 0x02) >> 1;
		_tMp3Header->priv = (mp3Frame[2] & 0x01);

		_tMp3Header->mode = (mp3Frame[3] & 0xc0) >> 6;
		_tMp3Header->modex = (mp3Frame[3] & 0x30) >> 4;
		_tMp3Header->copyright = (mp3Frame[3] & 0x08) >> 3;
		_tMp3Header->original = (mp3Frame[3] & 0x04) >> 2;
		_tMp3Header->emphasis = (mp3Frame[3] & 0x03);

		if( _tMp3Header->bitrate != this->tEncParams.app_bit_rate * 1000 ) return false;
		if( _tMp3Header->freq != this->tEncParams.app_sampling_rate ) return false;

		/*
		   printf("version   = %x \n",_tMp3Header->version);
		   printf("layer     = %x \n",_tMp3Header->layer);
		   printf("errp      = %x \n",_tMp3Header->errp);
		   printf("bitrate   = %d \n",_tMp3Header->bitrate);
		   printf("freq      = %d \n",_tMp3Header->freq);
		   printf("pad       = %x \n",_tMp3Header->pad);
		   printf("priv      = %x \n",_tMp3Header->priv);
		   printf("mode      = %x \n",_tMp3Header->mode);
		   printf("modex     = %x \n",_tMp3Header->modex);
		   printf("copyright = %x \n",_tMp3Header->copyright);
		   printf("original  = %x \n",_tMp3Header->original);
		   printf("emphasis  = %x \n",_tMp3Header->emphasis);
		 */

		return true;
	} // end of ParseMP3Header()


	/*****************************
FUNC : getFrameSize()
DESC : get mp3 frame size
	 ******************************/
	int Mp3Encode::getFrameSize(MP3_HEADER_t *_tMp3Header) {
		int size;
		int scale;

		if( _tMp3Header->layer == 1) scale = MIN_SCALE_LAYER;
		else scale = MAX_SCALE_LAYER;

		size = _tMp3Header->bitrate * scale / _tMp3Header->freq;
		/* divide by an extra factor of 2 for MPEG-2? */

		if( _tMp3Header->pad ) size += 1;

		return size;
	} // end of getFrameSize()


	/*****************************
FUNC : freeEncodeMp3()
DESC : free encode mp3 parameter
	 ******************************/
	void Mp3Encode::freeEncodeMp3(void) {
		static char runFlag = false;

		fprintf(stderr, "freeEncodeMp3 called..\n");

		if( runFlag == false ) {
			runFlag = true;

			if( this->pcmFrame != NULL ) {
				free(this->pcmFrame);
				this->pcmFrame = NULL;
			}

			if( this->mp3Frame != NULL ) {
				free(this->mp3Frame);
				this->mp3Frame = NULL;
			}

			if( this->mp3Queue != NULL ) {
				free(this->mp3Queue);
				this->mp3Queue = NULL;
			}

			if( this->gArrFrameInfo != NULL ) {
				free(this->gArrFrameInfo);
				this->gArrFrameInfo = NULL;
			}

			free(this->W1[0]);
			free(this->W2[0]);
			free(this->W3[0]);
			free(this->W4[0]);
			free(this->W5[0]);
			free(this->W6[0]);

#if MP3_OUTPUT
			fclose(gOutputFd);
#endif
		}

		return ;
	} // end of freeEncodeMp3()


	/*****************************
FUNC : getMp3Frame()
DESC : get mp3 frame for index
	 ******************************/
	MP3E_INT8* Mp3Encode::getMp3Frame(MP3_IDX_INFO_t *_mp3Idx) {
		int nextIdx = _mp3Idx->index + 1;
		int	frameSize;

		if( _mp3Idx->mp3Frame != NULL ) {
			free(_mp3Idx->mp3Frame);
			_mp3Idx->mp3Frame = NULL;
		}

		if( this->gArrFrameInfo[_mp3Idx->index].frameFlag == true ) {
			if( nextIdx == this->queueCount ) {
				nextIdx = 0;
			}

			frameSize = this->gArrFrameInfo[_mp3Idx->index].frameSize + this->gArrFrameInfo[nextIdx].frameSize;
		_mp3Idx->mp3Frame	= (MP3E_INT8 *)malloc(frameSize * sizeof(MP3E_INT8));
			memmove(_mp3Idx->mp3Frame, this->mp3Queue + this->gArrFrameInfo[_mp3Idx->index].frameLen, this->gArrFrameInfo[_mp3Idx->index].frameSize);
			memmove(_mp3Idx->mp3Frame + this->gArrFrameInfo[_mp3Idx->index].frameSize, this->mp3Queue + this->gArrFrameInfo[nextIdx].frameLen, this->gArrFrameInfo[nextIdx].frameSize);

			return _mp3Idx->mp3Frame;

		} else {
			return this->mp3Queue + this->gArrFrameInfo[_mp3Idx->index].frameLen;
		}

	} // end of getMp3Frame()


	/*****************************
FUNC : getMp3QueueCount()
DESC : get mp3 queue count
	 ******************************/
	int Mp3Encode::getMp3QueueCount(void) {

		return this->queueCount;
	} // end of getMp3QueueCount()


	/*****************************
FUNC : getMp3QueueIndex()
DESC : get mp3 queue index 
	 ******************************/
	int Mp3Encode::getMp3QueueIndex(void) {

		return this->headerCount;
	} // end of getMp3QueueIndex()


	/*****************************
FUNC : getMp3FrameSize()
DESC : get mp3 frame size for index
	 ******************************/
	int Mp3Encode::getMp3FrameSize(MP3_IDX_INFO_t *_mp3Idx) {
		int nextIdx = _mp3Idx->index + 1;

		if( this->gArrFrameInfo[_mp3Idx->index].frameFlag == true ) {
			if( nextIdx == this->queueCount ) {
				nextIdx = 0;
			}
			return this->gArrFrameInfo[_mp3Idx->index].frameSize + this->gArrFrameInfo[nextIdx].frameSize;

		} else {

			return this->gArrFrameInfo[_mp3Idx->index].frameSize;
		}
	} // end of getMp3FrameSize()


	/*****************************
FUNC : getMp3FrameLen()
DESC : get mp3 frame length for index
	 ******************************/
	int Mp3Encode::getMp3FrameLen(MP3_IDX_INFO_t *_mp3Idx) {
		int nextIdx = _mp3Idx->index + 1;

		if( this->gArrFrameInfo[_mp3Idx->index].frameFlag == true ) {
			if( nextIdx == this->queueCount ) {
				nextIdx = 0;
			}

			return this->gArrFrameInfo[_mp3Idx->index].frameLen + this->gArrFrameInfo[nextIdx].frameLen;

		} else {
			return this->gArrFrameInfo[_mp3Idx->index].frameLen;
		}
	} // end of getMp3FrameLen()


	/*****************************
FUNC : setMp3FrameIndex()
DESC : set mp3 frame index
	 ******************************/
	void Mp3Encode::setMp3FrameIndex(MP3_IDX_INFO_t *_mp3Idx, int _index) {
		_mp3Idx->index = _index;

		return ;
	} // end of setMp3FrameIndex()


	/*****************************
FUNC : getMp3FrameIndex()
DESC : get mp3 frame index
	 ******************************/
	int Mp3Encode::getMp3FrameIndex(MP3_IDX_INFO_t *_mp3Idx) {

		return _mp3Idx->index;
	} // end of getMp3FrameIndex()


	/*****************************
FUNC : setMp3FrameSocketFd()
DESC : set mp3 frame socket fd
	 ******************************/
	void Mp3Encode::setMp3FrameSocketFd(MP3_IDX_INFO_t *_mp3Idx, int _socketFd) {
		_mp3Idx->socketFd = _socketFd;

		return ;
	} // end of setMp3FrameSocketFd()


	/*****************************
FUNC : sendMp3Frame()
DESC : send mp3 frame to client sockfd
	 ******************************/
	int Mp3Encode::sendMp3Frame(MP3_IDX_INFO_t *_mp3Idx) {
	
		return send(_mp3Idx->socketFd, this->getMp3Frame(_mp3Idx), this->getMp3FrameSize(_mp3Idx), MSG_DONTWAIT);
	} // end of sendMp3Frame()


	/*****************************
FUNC : freeMp3Frame()
DESC : free mp3 frame, client thread 소멸시 사용
	 ******************************/
	void Mp3Encode::freeMp3Frame(MP3_IDX_INFO_t *_mp3Idx) {
		if( _mp3Idx->mp3Frame != NULL ) {
			free(_mp3Idx->mp3Frame);
			_mp3Idx->mp3Frame = NULL;
		}

		return ;
	} // end of freeMp3Frame()


	/*****************************
FUNC : incMp3FrameIndex()
DESC : frame index 증가, queue 순환 처리
	 ******************************/
	int	Mp3Encode::incMp3FrameIndex(MP3_IDX_INFO_t *_mp3Idx) {
		char	rc = false;
		if( this->gArrFrameInfo[_mp3Idx->index].frameFlag == true ) {
			_mp3Idx->index++;

			if( _mp3Idx->index == this->queueCount ) {
				_mp3Idx->index = 0;
				rc = true;
			}
		}

		_mp3Idx->index++;

		if( _mp3Idx->index == this->queueCount ) {
			_mp3Idx->index = 0;
			rc = true;
		}

		return rc;
	} // end of incMp3FrameIndex()


	/*****************************
FUNC : getMp3RewindFlag()
DESC : get mp3 index flag
	 ******************************/
	bool Mp3Encode::getMp3RewindFlag(void) {

		return this->flagMp3Rewind;
	} // end of getMp3RewindFlag()


	/*****************************
FUNC : getMp3QueueLoopFlag()
DESC : get mp3 loop flag 
	 ******************************/
	bool Mp3Encode::getMp3QueueLoopFlag(void) {

		return this->flagMp3QueueLoop;
	} // end of getMp3QueueLoopFlag()
#endif


};
