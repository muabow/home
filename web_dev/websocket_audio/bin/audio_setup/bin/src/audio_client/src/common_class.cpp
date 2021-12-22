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

#include <iostream>
#include <thread>
#include <net/if.h>
#include <sys/ioctl.h>
#include <sys/time.h>
#include <netinet/ether.h>

#include "mp3_dec_interface.h"

namespace Common {
	using namespace std;

	/* const variables */

	const bool  TYPE_PROTOCOL_UDP   = false;
	const bool  TYPE_PROTOCOL_TCP   = true;

	const int TIME_SLEEP			= 1000; // us
	const int TIME_WAIT_REQUEST 	= 2;
	const int TIME_RCV_TIMEOUT 		= 2; // 2s, 
	const int TIME_WAIT_PLAYINFO 	= 3;
	const int TIME_TIMEOUT_SEC 		= 2;
	const int TIME_BUFFER_WAIT    	= 10 * 1000;

	const int FLAG_ODD				= 1;
	const int FLAG_EVEN				= 2;

	const int SIZE_QUEUE_STACK		= 1024;
	const int SIZE_QUEUE_SCALE		= 5;

	const int PERIODS_SIZE    		= 12;

	const int BYTE_PCM_FRAME 		= 1;
	const int BYTE_MP3_FRAME 		= 2;
	const int SIZE_SCALE_RCVBUF 	= 10;


	const int FLAG_TCP 				= 1;
	const int FLAG_UDP 				= 0;
	const int FLAG_MODE_MP3 		= 1;
	const int FLAG_MODE_PCM 		= 0;

	const int FLAG_HB_REQUEST 		= 0;
	const int FLAG_HB_RESPONSE 		= 1;
	const int COUNT_CRC_MAX     	= 20;
	const int COUNT_SEND_RETRY 		= 3; 

	const char *STR_SERVER_IP 		= "SERVER_IPADDR";
	const char *STR_CAST_MODE 		= "CAST_MODE";
	const char *STR_TCP_MODE 		= "UNI-CAST (TCP)";
	const char *STR_UDP_MODE 		= "MULTI-CAST (UDP)";
	const char *STR_MCAST_IPADDR 	= "MCAST_IPADDR";
	const char *STR_MCAST_PORT 		= "MCAST_PORT";
	const char *STR_SERVER_PORT 	= "UNI-CAST PORT";
	const char *STR_ENCODE_MODE		= "ENCODE MODE";
	const char *STR_RATE			= "RATE";
	const char *STR_CHANNELS    	= "CHANNELS";
	const char *STR_CHUNK_SIZE 		= "CHUNK_SIZE";
	const char *STR_BUFFER_RATE 	= "BUFFER_TIME(Sec)";
	const char *STR_BUFFER_RATE_MS 	= "BUFFER_TIME(Msec)";
	const char *STR_MP3_SAMPLE_RATE = "MP3 RATE";
	const char *STR_MP3_BIT_RATE	= "MP3 BIT_RATE";

	const char *JSONFILE 			= "/opt/interm/public_html/modules/network_setup/conf/network_stat.json";



	const char *STR_ENCODE_PCM 		= "PCM";
	const char *STR_ENCODE_MP3 		= "MP3";

	const char *PATH_SAMPLE_RATE 	= "/tmp/sampleRate";

	const int FLAG_ENCODE_MP3 		= 1;
	const int SIZE_DEFAULT_VOLUME 	= 100;

 	const int SIZE_KBYTE          	= 1024;
 	const int SIZE_MBYTE          	= 1024 * 1024;

	const int SIZE_MAX_SCALE_LAYER  = 144;

	const int DIV_SAMPLING_RATE     = 24000;
	const int MIN_SAMPLING_RATE     = 576;
	const int MAX_SAMPLING_RATE     = 1152;

	const int SIZE_MP3_FRAME_BUFFER = 2048 * 4;
	const int SIZE_MP3_THRESHOLD    = 2881 + 8;
	
	const int MP3_LAYER_3  			= 3;
	const int MP3_LAYER_2  			= 2;
	const int MP3_LAYER_1   		= 1;

	const int COUNT_PREPARE_RETRY   = 3;

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

	class CommonFunc {
		private :
			bool    flagTerm = false;
			bool    reconFlag = false;
		public  :
			// CommonFunc(void);
			void    handler(void);
			void    term(void);
			bool 	getFlagTerm();
			bool 	getReconFlag();
			bool    setReconFlag();
			bool 	setFlagTerm();
	}; // end of class : CommonFunc
	CommonFunc  common;

	class SigHandler {
		public  :
			SigHandler(void) {
				signal(SIGINT,  this->term);
				signal(SIGSEGV, this->term);

				return ;
			}

			static void term(int _sigNum) {
				static bool flagTerm = false;

				if( _sigNum == SIGSEGV ) {
					if( flagTerm ) {
						fprintf(stderr, "force exit.. process died\n");
						exit(0);
					} 

					fprintf(stderr, "catch segmentation fault\n");
						exit(0);
				}

				if( flagTerm ) {
					fprintf(stderr, "already terminated\n");
				}

				flagTerm = true;

				common.handler();
			}
	}; // end of class : SigHandler
	SigHandler  handler;
	
	struct CAST_INFO {
		int 	type;       // TCP or UDP
		int 	cast;       // STREAM or DGRAM
		int 	sockFd;     // socket descriptor for UDP
	} typedef CAST_INFO_t;

	struct CLIENT_PARAMS {
		int  	serverCnt;
	} typedef CLIENT_PARAMS_t;

	struct CLIENT_INFO {
		int     delay;
		int     delayMs;
		int 	playVolume;

		// server parameter
		int 	typeProtocol;
		int     serverCnt;
		char    castType[24];
		char    ipAddr1[24];
		char    ipAddr2[24];
		int     port1;
		int     port2;
		char    mIpAddr[24];
		int     mPort;

		int 	chunkSize;
		int 	sampleRate;
		int 	channels;
		int 	mp3_mode;
		int 	mp3_chunkSize;
		int 	mp3_bitRate;
		int 	mp3_sampleRate;
		int 	ipStatus;

		char    hostName[128];
		char    deviceName[128];
	} typedef CLIENT_INFO_t;

	struct DELAY_INFO {
		int		mbScale;
		int     bufRate;
		int     bufRateMs;
		int		playVolume; 
	} typedef DELAY_INFO_t;

	struct ENCODE_INFO {
		int		chunkSize;
		short   bitRate;
		int     sampleRate;
	} typedef ENCODE_INFO_t;

	struct HOST_INFO {
		char 	hostName[128];
		char	macAddr[128];
	} typedef HOST_INFO_t;

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
		char            rsvd[32];       // 64
	} typedef HEADER_MP3_INFO_t;
	
	struct HEADER_MP3_CHUNK {
		short           chunkSize;      // 02
	} typedef HEADER_MP3_CHUNK_t;

	struct IP_INFO {
		int  	port1;
		int  	port2;
		int  	mPort;
		int	 	serverCnt;
		int  	typeProtocol;
		char 	castType[24];
		char 	ipAddr1[24];
		char 	ipAddr2[24];
		char 	mIpAddr[24];
	
	} typedef IP_INFO_t;

	struct PLAY_INFO {
		int             rate;
		int             channels;
		int             chunkSize;
		int             idx;
		int             encodeMode;
		unsigned int    pcmBufferSize;
		unsigned int    pcmPeriodSize;
	} typedef PLAY_INFO_t;

	struct QUEUE_STACK {
		int     recvCnt;
		int     sendCnt;
		int     stackSize;
		char    **stack;
	} typedef QUEUE_STACK_t;

	struct SERVER_LIST {
		int  	port;
		char 	ipAddr[128];
		char 	stat;
	} typedef SERVER_LIST_t;

	struct VAR_FLAG {
		char	threadRecv;
		char    extOrder;
		char    term;
		char    alsaFrame;
		char   	closeHandle;
		char   	connFlag;
		char    encMode;
		char    playLoop;
	} typedef VAR_FLAG_t;

	struct QUEUE_INFO {
		int     queueSize;  // 해당 queue의 크기
		int     queuePos;   // 해당 queue의 위치
		char    queueFlag;  // 해당 queue의 상태 (end queue 표현 : flag 1)
	} typedef QUEUE_INFO_t;

	struct LINEAR_QUEUE {
		int             queueSize;      // 사용자 설정, 단일 queue 크기 (평균치로 사용)
		int             queueScale;     // 사용자 설정, MB 단위 (ex. 1,2,3, ,,)
		int             queueCount;     // queue 갯수
		int             queuePos;       // 전체 queue의 위치
		int             setIndex;       // 현재 저장된 queue의 index
		int             getIndex;       // 현재 선택된 queue의 index
		int             linearSize;     // 전체 queue 크기
		int             bufferCount;    // 버퍼링된 queue의 갯수
		char            rewindFlag;     // queue 순환을 알리는 flag
		char            *combineQueue;  // 순회용 임시 queue
		char            *queueBody;     // 전체 queue
		QUEUE_INFO_t    *tQueueInfo;    // frame 에 대한 개별 정보(크기, 포인터 위치, 플래그 상태)

		pthread_mutex_t *queueMutex;

	} typedef LINEAR_QUEUE_t;

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


/* class */

	class QueueStack {
		private:
			int     idx;
			int     storeCnt;
			int     bufferSize;
			int     bufferRate;
			int     chunkSize;
			int     recvIdx;
			bool    flagRecvCnt;
			bool    flagRecv;

			QUEUE_STACK_t   tQueueStack;

			pthread_mutex_t tStackMutex;

		public:
			QueueStack(void);
			~QueueStack(void);

			char*   getQueueInStack(void);
			char*   getQueueIndex(int _idx);
			void    setQueueInStack(char *_data);
			void    incSendCnt(void);
			int     getRecvCnt(void);
			int     getRecvCntFlag(void);
			int     getStackSize(void);
			int     getBufferSize(void);
			void    freeStack(void);
			int     getStoreCount(void);
			int     getRecvFlag(void);
			int     getSendCnt(void);
			int     getChunkSize(void);
			void    init(int _idx, int _bufferRate, int _chunkSize);
			void 	initQueueInfo(LINEAR_QUEUE_t *_this);
			void 	setQueueSize(LINEAR_QUEUE_t *_this, int _queueSize);
			void	setQueueScale(LINEAR_QUEUE_t *_this, int _scaleSize);
			int 	setQueueInfo(LINEAR_QUEUE_t *_this);
			void 	setQueue(LINEAR_QUEUE_t *_this, char *_queue, int _queueSize);
			char*	getQueue(LINEAR_QUEUE_t *_this);
			int 	getQueueSize(LINEAR_QUEUE_t *_this);
			void 	incQueueIndex(LINEAR_QUEUE_t *_this);
			void 	freeQueueInfo(LINEAR_QUEUE_t *_this);
			void 	bufferingQueue(LINEAR_QUEUE_t *_this, int _queueCount);
	};
	QueueStack stackClient;

	class SocketClient {
		private :
			int 	sockFd = -1;
			int 	ipStatus = -1;
			bool 	flagTerm = false;
			char 	macAddr[128];

			CLIENT_PARAMS_t  tClientParams;
			SERVER_LIST_t  	 *tServerList;
			ENCODE_INFO_t    tEncInfo;
			CAST_INFO_t      tCastInfo;
			PLAY_INFO_t		 tPlayInfo;
			HOST_INFO_t      tHostInfo;
			LINEAR_QUEUE_t 	 tMp3Queue;

			thread clientFunc;
			thread clientMp3Rev;
		public  :
			SocketClient(void);
			~SocketClient(void);
			int 	setServerList(IP_INFO_t *_tIpInfo);
			int 	getPlayInfoEncode(void);
			int 	getPlayInfoRate(void);
			int 	getPlayInfoChannels(void);
			int 	getPlayInfoChunkSize(void);
			int 	getPlayInfoPcmBuf(void);
			int 	getPlayInfoPcmPer(void);
			int 	getIpStatus(void);
			int 	getCastType(void);
			int 	getEncRate(void);
			int 	getEncSampleRate(void);
			int 	getEncChunkSize(void);
			int		getRecvBufferSize(void);
			int 	initSocket();
			int 	reconnSocket();
			bool 	initSocketOption(int _sockFd, char *_hostName);
			bool 	sendHostName(int _sockFd); 
			bool 	recvPlayInfo();
			bool 	getMacAddress();
			void 	execute();
			void 	closeSocket(void);
			void 	procRecvPcmData(void);
			void 	procRecvMp3Data(void);
		//	void 	getMp3QueuePtr(LINEAR_QUEUE_t *_tMp3Queue);
			LINEAR_QUEUE_t *getMp3QueuePtr(void);
			unsigned short getCrcValue(char *_buf, unsigned short _len);
			bool 	getIpAddress();
			bool    getSelect(void);


	}; // end of class : SocketClient
	SocketClient socketClient;

	class PcmHandle {
		private:
			bool 	flagTerm 		= false;
			short 	*feedPcmData  = NULL;
			char 	deviceName[128];

			IP_INFO_t 	 tIpInfo;
			snd_pcm_t    *tPlaybackHandle;
			VAR_FLAG_t 	 tVarFlag;
			PLAY_INFO_t  tPlayInfo;
			DELAY_INFO_t tDelayInfo;

			thread pcmFunc;
		public:
			~PcmHandle(void);
			int 	setThreadRecv(int _type); 
			int 	setCloseHandle(int _type); 
			int 	setVolume(int _volume);	
			int 	setPcmHandle(); 
			int 	setEncMode(int _type); 
			int 	getEncMode(void); 
			int 	getTerm(void); 
			int 	getLevelValue();
			int		getBufferChunk(void);
			int 	recoverAlsaHandle(snd_pcm_t *_handle, int _err); 
			void 	setFlag(DELAY_INFO_t *_tDelayInfo, char *_deviceName);
			void 	initFlag(void);
			void 	pcmPlayLoop(void);
			void 	pcmStop(void);
			void 	printClientInfo(void);
			void 	execute();
			void 	setPlayInfo();
			void 	getIpList(IP_INFO_t *_tIpInfo);

	};
	PcmHandle pcmHandle;

	class ClientFunc {
		private:
			int 	socketNo = -1;
			char 	volume[128];
			char 	level[128];
			char 	deviceName[128];
			char 	clientInfo[4096];

			CLIENT_PARAMS_t tClientParams;
			CLIENT_INFO_t 	tClientInfo;
			DELAY_INFO_t 	tDelayInfo;
			HOST_INFO_t 	tHostInfo;
			CAST_INFO_t 	tCastInfo;
			IP_INFO_t 		tIpInfo;
		public:
			int 	setVolume(int _volume);	
			void 	init(CLIENT_INFO_t *_tClientInfo);	
			void 	stop();	
			bool 	run();	
			char 	*getClientInfo(void);
			char 	*getVolumeInfo(void);
			char 	*getLevelValue(void);
	};

	class ClientMp3Dec {
		private:
			bool 	flagTerm = false;
			const int sampleRates[4] = {44100, 48000, 32000};
			const int bitRates[16]   = {0,  32000,  40000,  48000,  56000,  64000,  80000,  96000, 112000, 128000, 160000, 192000, 224000, 256000, 320000, 0};
			int memInfoNumReqs;
		
			MP3D_INT16          *pcmFrame;
			MP3D_Decode_Config  *tDecConfig;
			MP3_HEADER_t 		tMp3Header;
			thread threadMp3Dec;
		public:
			void 	procMp3Decode();
			void 	*decodeAllocFast(int _size);
			void 	*decodeAllocSlow(int _size);
			void 	freeMp3Decode(void);
			void 	getMp3FrameInfo(char *_mp3Frame);
			void 	execute();

	};
	ClientMp3Dec clientMp3Dec;


	void CommonFunc::term(void) {
		this->handler();

	}
	
	void CommonFunc::handler(void) {
		if( this->flagTerm ) return ;
		this->flagTerm = true;

		return ;
	}

	bool CommonFunc::getFlagTerm() {
		return this->flagTerm;

	}
	
	bool CommonFunc::getReconFlag() {
		return this->reconFlag;

	}
	
	bool CommonFunc::setReconFlag() {

		if(this->reconFlag)
			this->reconFlag = false;
		else 
			this->reconFlag = true;
		
		return this->reconFlag;

	}

	// 각 init 값 정의
	void ClientFunc::init(CLIENT_INFO_t *_tClientInfo) {

		pcmHandle.initFlag();
		memcpy(&this->tClientInfo, _tClientInfo, sizeof(CLIENT_INFO_t));

		strcpy(this->tClientInfo.deviceName, "default");	

		this->tDelayInfo.bufRate 			= this->tClientInfo.delay;
		this->tDelayInfo.bufRateMs 			= this->tClientInfo.delayMs;
		this->tDelayInfo.playVolume 		= this->tClientInfo.playVolume;

		strcpy(this->tHostInfo.hostName, 	this->tClientInfo.hostName); 
		strcpy(this->deviceName, 		 	this->tClientInfo.deviceName);	

		this->tIpInfo.typeProtocol 		= this->tClientInfo.typeProtocol;
		this->tIpInfo.serverCnt 		= this->tClientInfo.serverCnt;

		strcpy(this->tIpInfo.castType ,	this->tClientInfo.castType);	
		strcpy(this->tIpInfo.ipAddr1 , 	this->tClientInfo.ipAddr1);	
		strcpy(this->tIpInfo.ipAddr2 , 	this->tClientInfo.ipAddr2);	
		strcpy(this->tIpInfo.mIpAddr , 	this->tClientInfo.mIpAddr);	

		this->tIpInfo.port1 				= this->tClientInfo.port1;
		this->tIpInfo.port2 				= this->tClientInfo.port2;
		this->tIpInfo.mPort 				= this->tClientInfo.mPort;

#if 0
			fprintf(stderr,"delay : %d\n" , this->tDelayInfo.bufRate);
			fprintf(stderr,"castType : %s\n", this->tClientInfo.castType);
			fprintf(stderr,"delayms : %d\n", this->tDelayInfo.bufRateMs);
			fprintf(stderr,"volume : %d\n", this->tDelayInfo.playVolume);
			fprintf(stderr,"hostname : %s\n", this->tHostInfo.hostName);
			fprintf(stderr,"devicename : %s\n", this->deviceName);
			fprintf(stderr,"protocol : %d\n" ,  this->tIpInfo.typeProtocol);
			fprintf(stderr,"servercnt : %d\n", this->tIpInfo.serverCnt);
			fprintf(stderr,"ipAddr1 : %s\n", this->tIpInfo.ipAddr1);
			fprintf(stderr,"ipaddr2 : %s\n", this->tIpInfo.ipAddr2);
#endif

	}

	//최종 run
	bool ClientFunc::run() {

		//서버 리스트 셋
		socketClient.setServerList(&tIpInfo); 
		pcmHandle.getIpList(&tIpInfo);

		if( ( this->socketNo = socketClient.initSocket() ) == false ) {

			fprintf(stderr,"socketClient.initSocket fail\n");
			return false;	
		}

		if( socketClient.initSocketOption(this->socketNo, this->tHostInfo.hostName) == false ) {

			fprintf(stderr,"socketClient.initSocketOption fail\n");
			return false;	
		}

		if( socketClient.recvPlayInfo() == false ) {

			fprintf(stderr,"socketClient.recvPlayInfo fail\n");
			return false;	
		}

		//socket thread
		socketClient.execute();

		//alsa, pcm setting
		pcmHandle.setFlag(&tDelayInfo, this->deviceName);

		if( pcmHandle.setPcmHandle() != false )
		{
			if( pcmHandle.getEncMode() == Common::FLAG_MODE_MP3 )
				clientMp3Dec.execute();
			
			pcmHandle.execute();
			pcmHandle.printClientInfo();
		} else {

			fprintf(stderr,"pcmHandle.setPcmHandle fail\n");
			socketClient.closeSocket();

			return false;	
		}
		return true;
	}

	int ClientFunc::setVolume(int _volume) {
		if( _volume < 0 || _volume > 100 ) return false;

		this->tClientInfo.playVolume = _volume;
		pcmHandle.setVolume(_volume);

		return true;
	}

	void ClientFunc::stop() {
		pcmHandle.pcmStop();
		stackClient.freeStack();
		common.term();
	}

	//json send level
	char *ClientFunc::getLevelValue() {
		int levelStat = pcmHandle.getLevelValue();
		memset(this->level, 0x00, sizeof(this->level));
		sprintf(this->level, "{\"level\": \"%d\"}",levelStat );

		return this->level;
	}

	//json send volume
	char *ClientFunc::getVolumeInfo() {
		memset(this->volume, 0x00, sizeof(this->volume));

		sprintf(this->volume, "{\"playVolume\": \"%d\"}", this->tClientInfo.playVolume);

		return this->volume;
	}

	//json send clientInfo
	char *ClientFunc::getClientInfo() {

		this->tClientInfo.chunkSize = socketClient.getPlayInfoChunkSize();
		this->tClientInfo.sampleRate = socketClient.getPlayInfoRate();
		this->tClientInfo.channels = socketClient.getPlayInfoChannels();
		this->tClientInfo.ipStatus = socketClient.getIpStatus();

		this->tClientInfo.mp3_mode 		= socketClient.getPlayInfoEncode();
		this->tClientInfo.mp3_chunkSize = socketClient.getPlayInfoChunkSize();
		this->tClientInfo.mp3_bitRate 	= socketClient.getEncRate();
		this->tClientInfo.mp3_sampleRate = socketClient.getEncSampleRate();

		memset(this->clientInfo, 0x00, sizeof(clientInfo));

		sprintf(this->clientInfo,
				"{\"delay\": \"%d\", \"delayMs\": \"%d\", \"playVolume\": \"%d\",\"serverCnt\": \"%d\", \"chunkSize\": \"%d\", \"sampleRate\": \"%d\", \"channels\": \"%d\", \"mp3_mode\": \"%d\", \"mp3_chunkSize\": %d, \"mp3_bitRate\": \"%d\", \"mp3_sampleRate\": \"%d\", \"typeProtocol\": \"%d\", \"castType\": \"%s\", \"port1\": \"%d\", \"port2\": \"%d\", \"mPort\": \"%d\", \"ipAddr1\": \"%s\", \"ipAddr2\": \"%s\" , \"mIpAddr\": \"%s\" , \"ipStatus\": \"%d\" ,\"hostname\": \"%s\", \"deviceName\": \"%s\"}",
				this->tClientInfo.delay,
				this->tClientInfo.delayMs,
				this->tClientInfo.playVolume,
				this->tClientInfo.serverCnt,
				this->tClientInfo.chunkSize,
				this->tClientInfo.sampleRate,
				this->tClientInfo.channels,
				this->tClientInfo.mp3_mode,
				this->tClientInfo.mp3_chunkSize,
				this->tClientInfo.mp3_bitRate,
				this->tClientInfo.mp3_sampleRate,
				this->tClientInfo.typeProtocol,
				this->tClientInfo.castType,
				this->tClientInfo.port1,
				this->tClientInfo.port2,
				this->tClientInfo.mPort,
				this->tClientInfo.ipAddr1,
				this->tClientInfo.ipAddr2,
				this->tClientInfo.mIpAddr,
				this->tClientInfo.ipStatus,
				this->tClientInfo.hostName,
				this->tClientInfo.deviceName);

		//	fprintf(stderr , "clientInfo : %s\n", this->clientInfo);

		return this->clientInfo;
	}

	//IP info copy
	void PcmHandle::getIpList(IP_INFO_t *_tIpInfo) {
		memcpy( &this->tIpInfo, _tIpInfo, sizeof(IP_INFO_t) );

	}

	int PcmHandle::setVolume(int _volume) {
		this->tDelayInfo.playVolume =_volume ;

	}

	QueueStack::QueueStack(void) {

		return ;
	}

	QueueStack::~QueueStack(void) {
	//	this->freeStack();

		return ;
	}

	void QueueStack::init(int _idx, int _bufferRate, int _chunkSize) {
		//fprintf(stderr, "[%d] init Stack\n", _idx);

		this->tStackMutex = PTHREAD_MUTEX_INITIALIZER;

		this->idx        = _idx; this->bufferRate = _bufferRate;
		this->chunkSize  = _chunkSize;

		this->storeCnt      = 0;
		this->flagRecv      = false;
		this->flagRecvCnt   = false;
		this->recvIdx       = Common::FLAG_EVEN;

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

		return this->tQueueStack.stack[_idx];
	}

	void QueueStack::setQueueInStack(char *_data) {
		if( this->tQueueStack.recvCnt == this->tQueueStack.stackSize ) {
			this->tQueueStack.recvCnt = 0;
			this->flagRecv = true;

			if( this->recvIdx % Common::FLAG_EVEN == 0 ) {  // Even 2 , odd 1
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

	int QueueStack::getStackSize(void) {

		return this->tQueueStack.stackSize;
	}

	int QueueStack::getBufferSize(void) {

		return this->bufferSize;
	}

	int QueueStack::getStoreCount(void) {

		return this->storeCnt;
	}

	int QueueStack::getRecvFlag(void) {

		return this->flagRecv;
	}

	int QueueStack::getSendCnt(void) {

		return this->tQueueStack.sendCnt;
	}

	int QueueStack::getChunkSize(void) {

		return this->chunkSize;
	}
	
	void QueueStack::setQueueSize(LINEAR_QUEUE_t *_this, int _queueSize) {
		    _this->queueSize = _queueSize;

			    return ;
	} // end of SetQueueSize()
	
	void QueueStack::setQueueScale(LINEAR_QUEUE_t *_this, int _scaleSize) {
		_this->queueScale = _scaleSize;

		return ;
	} // end of SetQueueScale()

	int QueueStack::setQueueInfo(LINEAR_QUEUE_t *_this) {
		// 개별 queue 사이즈 미지정시 1Kbyte(1024byte)로 설정
		if( _this->queueSize == 0 ) {
			_this->queueSize = Common::SIZE_KBYTE;
		}

		// scale 크기 미지정시 1Mbyte로 설정
		if( _this->queueScale == 0 ) {
			_this->queueScale = 1;
		}

		// queue 갯수는 1 Mb 기준으로 몇 개나 들어가는지 정의
		_this->queueCount = (Common::SIZE_MBYTE / _this->queueSize) * _this->queueScale;

		// 전체 queue 선언을 위한 body size 측정
		_this->linearSize = _this->queueSize * _this->queueCount;

		// 전체 queue 영역 선언
		if( (_this->queueBody = (char *)malloc(_this->linearSize * sizeof(char))) == NULL ) {
			fprintf(stderr, "Create queue failed : [%02d] %s\n", errno, strerror(errno));

			return false;
		}

		// 해당 queue 정보 영억 선언
		if( (_this->tQueueInfo = (QUEUE_INFO_t *)malloc(_this->queueCount * sizeof(QUEUE_INFO_t))) == NULL ) {
			fprintf(stderr, "Create queue info failed : [%02d] %s\n", errno, strerror(errno));

			return false;
		}

		return true;
	} // end of SetQueueInfo()

	void QueueStack::setQueue(LINEAR_QUEUE_t *_this, char *_queue, int _queueSize) {
		int queueLeftSize;

		pthread_mutex_lock(_this->queueMutex);

		// 전체 queue 크기를 넘는 경우
		if( (_this->queuePos + _queueSize) > _this->linearSize ) {
			queueLeftSize = _this->linearSize - _this->queuePos;

			_this->tQueueInfo[_this->setIndex].queueSize = queueLeftSize;
			_this->tQueueInfo[_this->setIndex].queuePos  = _this->queuePos;
			_this->tQueueInfo[_this->setIndex].queueFlag = true;

			memmove(_this->queueBody + _this->queuePos, _queue, queueLeftSize);
			_this->queuePos = 0;
			_this->setIndex++;

			if( _this->setIndex == _this->queueCount ) {
				_this->setIndex = 0;
			}

			// queue 순회 시 rewindFlag 변경
			if( _this->rewindFlag == true ) {
				_this->rewindFlag = false;

			} else {
				_this->rewindFlag = true;
			}

			_this->tQueueInfo[_this->setIndex].queueSize = _queueSize - queueLeftSize;
			_this->tQueueInfo[_this->setIndex].queuePos  = _this->queuePos;
			_this->tQueueInfo[_this->setIndex].queueFlag = false;

			memmove(_this->queueBody + _this->queuePos, _queue + queueLeftSize, _queueSize - queueLeftSize);
			_this->queuePos = _queueSize - queueLeftSize;

		} else {
			_this->tQueueInfo[_this->setIndex].queueSize = _queueSize;
			_this->tQueueInfo[_this->setIndex].queuePos  = _this->queuePos;
			_this->tQueueInfo[_this->setIndex].queueFlag = false;

			memmove(_this->queueBody + _this->queuePos, _queue, _queueSize);
			_this->queuePos += _queueSize;
		}

		_this->setIndex++;

		if( _this->setIndex == _this->queueCount ) {
			_this->setIndex = 0;
		}

		_this->bufferCount++;
		pthread_mutex_unlock(_this->queueMutex);

		return;
	}

	char *QueueStack::getQueue(LINEAR_QUEUE_t *_this) {
		int nextIdx = _this->getIndex + 1;
		int queueSize;

		if( _this->combineQueue != NULL ) {
			free(_this->combineQueue);
			_this->combineQueue = NULL;
		}
		// fprintf(stderr, "[%d/%d]%d\n", _this->setIndex, _this->getIndex, _this->queueCount);
		pthread_mutex_lock(_this->queueMutex);

		// 순회 queue 처리
		if( _this->tQueueInfo[_this->getIndex].queueFlag == true ) {
			if( nextIdx == _this->queueCount ) {
				nextIdx = 0;
			}

			queueSize = _this->tQueueInfo[_this->getIndex].queueSize + _this->tQueueInfo[nextIdx].queueSize;
			if( (_this->combineQueue = (char *)malloc(queueSize * sizeof(char))) == NULL ) {
				fprintf(stderr, "GetQueue() malloc failed : [%02d] %s\n", errno, strerror(errno));
			}

			memmove(_this->combineQueue,
					_this->queueBody + _this->tQueueInfo[_this->getIndex].queuePos,
					_this->tQueueInfo[_this->getIndex].queueSize);

			memmove(_this->combineQueue + _this->tQueueInfo[_this->getIndex].queueSize,
					_this->queueBody + _this->tQueueInfo[nextIdx].queuePos,
					_this->tQueueInfo[nextIdx].queueSize);


			_this->bufferCount--;
			pthread_mutex_unlock(_this->queueMutex);

			return _this->combineQueue;

		} else {
			_this->bufferCount--;
			pthread_mutex_unlock(_this->queueMutex);

			return _this->queueBody + _this->tQueueInfo[_this->getIndex].queuePos;
		}

	} // end of GetQueue()
	
	int QueueStack::getQueueSize(LINEAR_QUEUE_t *_this) {
		int nextIdx = _this->getIndex + 1;
		int queueSize;

		// 순회 queue 처리
		if( _this->tQueueInfo[_this->getIndex].queueFlag == true ) {
			if( nextIdx == _this->queueCount ) {
				nextIdx = 0;
			}

			queueSize = _this->tQueueInfo[_this->getIndex].queueSize + _this->tQueueInfo[nextIdx].queueSize;

			return queueSize;

		} else {
			return _this->tQueueInfo[_this->getIndex].queueSize;
		}

	} // end of GetQueueSize()

	void QueueStack::incQueueIndex(LINEAR_QUEUE_t *_this) {
		// 순회 queue 처리
		if( _this->tQueueInfo[_this->getIndex].queueFlag == true ) {
			_this->getIndex++;

			if( _this->getIndex == _this->queueCount ) {
				_this->getIndex = 0;
			}
		}

		_this->getIndex++;

		if( _this->getIndex == _this->queueCount ) {
			_this->getIndex = 0;
		}

		return ;
	} // end of IncQueueIndex()

	void QueueStack::freeQueueInfo(LINEAR_QUEUE_t *_this) {
		if( _this->queueBody != NULL ) {
			free(_this->queueBody);
			_this->queueBody = NULL;
		}

		if( _this->tQueueInfo != NULL ) {
			free(_this->tQueueInfo);
			_this->tQueueInfo = NULL;
		}

		if( _this->combineQueue != NULL ) {
			free(_this->combineQueue);
			_this->combineQueue = NULL;
		}

		free(_this->queueMutex);

		return ;
	} // end of FreeQueueInfo()

	void QueueStack::bufferingQueue(LINEAR_QUEUE_t *_this, int _queueCount) {
		while( _queueCount >= _this->bufferCount ) {

			usleep(Common::TIME_BUFFER_WAIT);
		}

		return ;
	} // end of BufferingQueue()


	void QueueStack::initQueueInfo(LINEAR_QUEUE_t *_this) {
		_this->queueSize    = 0;
		_this->linearSize   = 0;
		_this->queueScale   = 0;
		_this->queueCount   = 0;
		_this->queuePos     = 0;
		_this->setIndex     = 0;
		_this->getIndex     = 0;
		_this->bufferCount  = 0;
		_this->rewindFlag   = false;
		_this->combineQueue = NULL;
		_this->queueBody    = NULL;
		_this->tQueueInfo   = NULL;

		_this->queueMutex = (pthread_mutex_t *)malloc(sizeof(pthread_mutex_t));
		pthread_mutex_init(_this->queueMutex, NULL);

		return ;
	} // end of InitQueueInfo()


	PcmHandle::~PcmHandle(void) {
	//	this->pcmStop();
		return ;

	}
	int PcmHandle::getEncMode(void) {

		return this->tVarFlag.encMode;
	}

	int PcmHandle::setEncMode(int _type) {
		this->tVarFlag.encMode = _type;

		return 0;
	}

	int PcmHandle::getTerm(void) {

		return this->tVarFlag.term;
	}

	int PcmHandle::setThreadRecv(int _type) {
		this->tVarFlag.threadRecv = _type;

		return 0;
	}

	int PcmHandle::setCloseHandle(int _type) {
		this->tVarFlag.closeHandle = _type;

		return 0;
	}

	int	PcmHandle::getBufferChunk(void) {
		double sec;

		if(this->tVarFlag.encMode == Common::FLAG_MODE_PCM)
			sec = this->tDelayInfo.bufRate + ((double)this->tDelayInfo.bufRateMs / 1000) + 0.3;
		else
			sec = this->tDelayInfo.bufRate + ((double)this->tDelayInfo.bufRateMs / 1000) + 0.8;

		return (this->tPlayInfo.rate * this->tPlayInfo.channels * sec) / (this->tPlayInfo.chunkSize / 2);

	}
	void PcmHandle::initFlag(void) {
		this->tVarFlag.term		 	= false;
		this->tVarFlag.alsaFrame	= false;
		this->tVarFlag.closeHandle 	= false;
		this->tVarFlag.connFlag		= false;
		this->tVarFlag.playLoop		= false;
		this->tVarFlag.encMode		= Common::FLAG_MODE_PCM;
		this->tPlaybackHandle 		= NULL;
	}

	void PcmHandle::setFlag(DELAY_INFO_t *_tDelayInfo, char *_deviceName) {

		this->tDelayInfo.playVolume = _tDelayInfo->playVolume;
		this->tDelayInfo.mbScale 	= 5;
		this->tDelayInfo.bufRate   	= _tDelayInfo->bufRate;
		this->tDelayInfo.bufRateMs 	=_tDelayInfo->bufRateMs;

		strcpy(this->deviceName , _deviceName);

		this->setPlayInfo();
		stackClient.init( 0, 0, this->tPlayInfo.chunkSize);

	} 

	void PcmHandle::setPlayInfo() {

		if( socketClient.getPlayInfoEncode() == Common::FLAG_MODE_PCM ) {
			this->tPlayInfo.rate 			= socketClient.getPlayInfoRate();
			this->tPlayInfo.chunkSize 		= socketClient.getPlayInfoChunkSize();
		}
		else {
			this->tPlayInfo.rate 			= socketClient.getEncSampleRate(); 
			this->tPlayInfo.chunkSize 		= socketClient.getEncChunkSize();

		}

		this->tPlayInfo.channels 		= socketClient.getPlayInfoChannels();
		this->tPlayInfo.pcmBufferSize 	= socketClient.getPlayInfoPcmBuf();
		this->tPlayInfo.pcmPeriodSize 	= socketClient.getPlayInfoPcmPer();
		this->tPlayInfo.idx 			= 0;

	}

	int PcmHandle::setPcmHandle() {
		int		err;
		int 			periods	 	= Common::PERIODS_SIZE;
		unsigned int	channels 	= this->tPlayInfo.channels;
		unsigned int 	rate 		= this->tPlayInfo.rate;

		if( this->tPlayInfo.encodeMode == Common::FLAG_MODE_MP3 ) 
			rate  = socketClient.getEncSampleRate(); 

		int	buffer = this->tPlayInfo.chunkSize / 2 ;
		unsigned int 	bufferSize 	= ((buffer) * periods) >> 2;
		unsigned int 	periodSize	= buffer;
		
		snd_pcm_hw_params_t	*tPlaybackParams = NULL;
		snd_pcm_sw_params_t *tSwParams = NULL;

		snd_pcm_hw_params_alloca(&tPlaybackParams);
		snd_pcm_sw_params_alloca(&tSwParams);

		snd_pcm_sframes_t tBufferSize;
		snd_pcm_sframes_t tPeriodSize;

		snd_pcm_uframes_t   tFrames;
		snd_pcm_uframes_t   exact_buffersize;

		if( this->flagTerm == true ) return false;
			
			fprintf(stderr, "Client - play information.. \n");
			fprintf(stderr, "       - sample rate[%d], channels[%d] \n", rate, channels);
			fprintf(stderr, "       - PCM buffer size [%d], period size : [%d]\n", bufferSize, periodSize);
		 
		if( this->tPlaybackHandle != NULL ) {
			fprintf(stderr, "Client - this->tPlaybackHandle resetting..\n");
			snd_pcm_drain(this->tPlaybackHandle);
			snd_pcm_close(this->tPlaybackHandle);
			this->tPlaybackHandle = NULL;
		}

		if( (err = snd_pcm_open(&this->tPlaybackHandle, this->deviceName, SND_PCM_STREAM_PLAYBACK, SND_PCM_NONBLOCK)) < 0 ) {
			fprintf(stderr, "ALSA - Cannot open audio device %s (%s)\n",this->deviceName, snd_strerror (err));

			// "cannot open audio device (Device or resource busy)" 메시지를 현시하고 종료되고 
			// run_audioclient에서 다시 시작되어 무한 반복을 수행함.  이때 리셋을 수행하도록 함.

			return false;
		}

		if( (err = snd_pcm_hw_params_malloc(&tPlaybackParams)) < 0 ) {
			fprintf(stderr, "ALSA - Cannot allocate hardware parameter structure : %s\n", snd_strerror(err));

			return false;
		}

		if( (err = snd_pcm_hw_params_any(this->tPlaybackHandle, tPlaybackParams)) < 0 ) {
			fprintf(stderr, "ALSA - Cannot initialize hardware parameter structure : %s\n", snd_strerror(err));

			return false;
		}

		if( (err = snd_pcm_hw_params_set_rate_resample(this->tPlaybackHandle, tPlaybackParams, 0)) < 0 ) {
			fprintf(stderr, "ALSA - Cannot set rate resample : %s\n", snd_strerror(err));

			return false;
		}

		if( (err = snd_pcm_hw_params_set_access(this->tPlaybackHandle, tPlaybackParams, SND_PCM_ACCESS_RW_INTERLEAVED)) < 0 ) {
			fprintf(stderr, "ALSA - Cannot set access type : %s\n", snd_strerror(err));

			return false;
		}

		if( (err = snd_pcm_hw_params_set_format(this->tPlaybackHandle, tPlaybackParams, SND_PCM_FORMAT_S16_LE)) < 0 ) {
			fprintf(stderr, "ALSA - Cannot set sample format : %s\n", snd_strerror(err));

			return false;
		}

		// sample rate 강제 설정

		if( (err = snd_pcm_hw_params_set_rate(this->tPlaybackHandle, tPlaybackParams, rate, 0)) < 0 ) {
			fprintf(stderr, "ALSA - Cannot set sample rate : %s\n", snd_strerror(err));

			if( (err = snd_pcm_hw_params_set_rate_near(this->tPlaybackHandle, tPlaybackParams, &rate, 0)) < 0 ) {
				fprintf(stderr, "ALSA - Cannot set near sample rate : %s\n", snd_strerror(err));

				return false;
			}
		}

		if( (err = snd_pcm_hw_params_set_channels(this->tPlaybackHandle, tPlaybackParams, channels) ) < 0 ) {
			fprintf(stderr, "ALSA - Cannot set channel count : %s\n", snd_strerror(err));

			return false;
		}

		if( (err = snd_pcm_hw_params_set_periods(this->tPlaybackHandle, tPlaybackParams, periods, 0)) < 0) {
			fprintf(stderr, "ALSA - error setting periods: %s\n", snd_strerror(err));

			return false;
		}

		exact_buffersize = bufferSize;

		if ( snd_pcm_hw_params_set_buffer_size_near(this->tPlaybackHandle, tPlaybackParams, &exact_buffersize) < 0 ) {
			fprintf(stderr, "Error setting buffersize.\n");

			return false;
		}

		if( (err = snd_pcm_nonblock(this->tPlaybackHandle, 0)) < 0 ) {
			fprintf(stderr, "ALSA - nonblock failed : %s\n", snd_strerror(err));

			return false;
		}

		if( (err = snd_pcm_hw_params(this->tPlaybackHandle, tPlaybackParams)) < 0 ) {
			fprintf(stderr, "ALSA - Cannot set parameters : %s\n", snd_strerror(err));

			return false;
		}

		snd_pcm_hw_params_get_buffer_size(tPlaybackParams, &tFrames);
		tBufferSize = tFrames;

		snd_pcm_hw_params_get_period_size(tPlaybackParams, &tFrames, 0);
		tPeriodSize = tFrames;

		snd_pcm_hw_params_free(tPlaybackParams);

		// set S/W params
		/* get the current swparams */
		if( (err = snd_pcm_sw_params_current(this->tPlaybackHandle, tSwParams)) < 0 ) {
			fprintf(stderr, "ALSA - Unable to determine current swparams for playback : %s\n", snd_strerror(err));

			return false;
		}

		if( (err = snd_pcm_sw_params_set_avail_min(this->tPlaybackHandle, tSwParams, periodSize)) < 0 ) {
			fprintf(stderr, "ALSA - Unable to set avail min for playback : %s\n", snd_strerror(err));

			return false;
		}

		/* write the parameters to the playback device */
		if( (err = snd_pcm_sw_params(this->tPlaybackHandle, tSwParams)) < 0 ) {
			fprintf(stderr, "ALSA - Unable to set sw params for playback : %s\n", snd_strerror(err));

			return false;
		}

		if( (err = snd_pcm_prepare(this->tPlaybackHandle)) < 0 ) {
			fprintf(stderr, "ALSA - Cannot prepare audio interface for use : %s\n", snd_strerror(err));

			return false;
		}

		//처리해야할 Flag
		this->tVarFlag.alsaFrame = true;

		fprintf(stderr, "Client - PCM handler init success..\n");

		this->tVarFlag.closeHandle 	= false;

		return true;
	} // end of SetPcmHandle()


	void PcmHandle::pcmPlayLoop (void) {
		int 	err, idx;
		int 	frameBytes;
		int		chunkSize, bufferSize, queueStackSize;
		int 	frameSize;
		int		prepareCnt = 0;
		int		queueDiffCnt = 0;
		int     queueIdxCnt = 0;
		int     queueIdxCntTmp = 0;

		int 	encFrame = Common::BYTE_MP3_FRAME;
		char	flagBuffer = true;
		struct	timeval before, after;

		short	*feedNullData = NULL;

		this->tVarFlag.threadRecv = false;

		snd_pcm_sframes_t tPcmAvail, tPcmDelay;

		chunkSize      = this->tPlayInfo.chunkSize;

		queueStackSize = stackClient.getStackSize();
		bufferSize     = stackClient.getBufferSize(); 
		frameBytes = snd_pcm_frames_to_bytes(this->tPlaybackHandle, 1);

		if( (feedNullData = (short *)malloc(chunkSize * sizeof(char))) == NULL ) {
			fprintf(stderr, "malloc() feedNullData failed : [%02d]\n", errno);
		}
		bzero(feedNullData, chunkSize);
		
		while( stackClient.getStoreCount() <= this->getBufferChunk() + queueDiffCnt ) {
			if( flagTerm == true ) break;
			usleep(Common::TIME_SLEEP);
		}
		
		if( this->tVarFlag.encMode == Common::FLAG_MODE_PCM ) {
			while( !this->flagTerm ) {

				// alsa device 재설정
				if( this->tVarFlag.closeHandle == true ) {
					if(this->setPcmHandle() == false)
						this->flagTerm = true;
					else
						this->tVarFlag.closeHandle = false;
				}

				// PcmHandle() 호출로 frame size 변경 시 frame size 갱신 용도
				if( this->tVarFlag.alsaFrame == true ) {
					frameBytes = snd_pcm_frames_to_bytes(this->tPlaybackHandle, 1);

					this->tVarFlag.alsaFrame = false;
				}

				if((stackClient.getStoreCount() >= (this->getBufferChunk() + 40)) || (stackClient.getStoreCount() <= queueDiffCnt)) {
					queueIdxCnt++;
					queueIdxCntTmp = 0;

					if((queueIdxCnt == 30) && (queueDiffCnt > 0)) {
						queueDiffCnt-= 1;
						queueIdxCnt = 0;
					}

				} else if((stackClient.getStoreCount() >= (this->getBufferChunk() + 10)) && (stackClient.getStoreCount() >= queueDiffCnt)) {
					queueIdxCntTmp++;
					queueIdxCnt = 0;

						if(queueIdxCntTmp == 100) {
							queueDiffCnt-= 1;
							queueIdxCntTmp = 0;

						}
				} else {
					queueIdxCnt = 0;
					queueIdxCntTmp = 0;

				}

				// chunk의 갯수가 버퍼 기준보다 높다면 기존 frame skip 하여 기준 맞춤
				//fprintf(stderr, "pcm storeCnt %d , bbuffChunk %d + %d  %d %d \n",stackClient.getStoreCount(),this->getBufferChunk(), queueDiffCnt, queueIdxCnt, queueIdxCntTmp);
				if( stackClient.getStoreCount() > (this->getBufferChunk() + queueDiffCnt)) {
					flagBuffer = false;

					while( stackClient.getStoreCount() > (this->getBufferChunk() + queueDiffCnt) ) {
						stackClient.incSendCnt();

					}

					this->feedPcmData = (short *)stackClient.getQueueInStack();
					stackClient.incSendCnt();

					// chunk가 존재하는 정상 진행 상태
				} else if( stackClient.getStoreCount() > 0 ) {
					if( flagBuffer == false ) {
						this->feedPcmData = (short *)stackClient.getQueueInStack();
						stackClient.incSendCnt();

					} else {
						if( stackClient.getStoreCount() > this->getBufferChunk() )  {
							flagBuffer = false;
						} 
						this->feedPcmData = (short *)stackClient.getQueueInStack();
						stackClient.incSendCnt();
					}

					// chunk가 없기 때문에 buffering 진입
				} 
				else {
					flagBuffer = true;
					this->feedPcmData = feedNullData;
					//fprintf(stderr,"NULL!!!!!!!!!!!!!!!!!!!!!!!!!!!\n");

					if( stackClient.getStoreCount() == 0 ) {
						if(queueDiffCnt <= 40) {
							queueDiffCnt+=2;

						} else {
							while( stackClient.getStoreCount() <= this->getBufferChunk() + queueDiffCnt ) {
								if( flagTerm == true ) break;
								usleep(Common::TIME_SLEEP);
							}
						}
					}
				}

				// volume 변화가 있을 때만 사용
				if( this->tDelayInfo.playVolume != Common::SIZE_DEFAULT_VOLUME ) {
					for( idx = 0 ; idx < chunkSize / 2 ; idx++ ) {
						this->feedPcmData[idx] = (int32_t)this->feedPcmData[idx] * this->tDelayInfo.playVolume / 100;
					}
				}

				if( this->flagTerm ) break;
				if( (err = snd_pcm_wait(this->tPlaybackHandle, 500)) < 0 ) {
					fprintf(stderr, "poll failed [%02d] %s\n", err, snd_strerror(err));
				}

				if( this->flagTerm ) break;
				if( (frameSize = snd_pcm_avail_update(this->tPlaybackHandle)) < 0 ) {
					if( frameSize == -EPIPE ) {
						fprintf(stderr, "snd_pcm_avil_update() failed : xrun\n");
					}
				}

				frameSize = frameSize > (chunkSize / frameBytes) ? (chunkSize / frameBytes) : frameSize;

				if( (err = snd_pcm_writei(this->tPlaybackHandle, this->feedPcmData, frameSize)) < 0 ) {
					fprintf(stderr, "ALSA - [pcm] write to audio interface failed : [%d/%d] [%02d] %s\n",
							frameSize, (int)tPcmAvail, err, snd_strerror(err));

					this->setPcmHandle();
				}

				// gettimeofday(&after , NULL);
				// fprintf(stderr, "%d : %.0lf us  [%d] \n" , (int)err, time_diff(before , after), stackClient.getStoreCount() );

			} // end of while()

		}
		else {
			while( !this->flagTerm )  
			{ 
				if( this->tVarFlag.closeHandle == true ) { 
					if(this->setPcmHandle() == false) 
						this->flagTerm = true; 
					else 
						this->tVarFlag.closeHandle = false; 
				} 


				if( this->tVarFlag.alsaFrame == true ) { 
					frameBytes = snd_pcm_frames_to_bytes(this->tPlaybackHandle, 1); 

					this->tVarFlag.alsaFrame = false; 
				} 

				frameSize = (chunkSize  / frameBytes) / encFrame; 
				
				if((stackClient.getStoreCount() >= (this->getBufferChunk() + 25)) || (stackClient.getStoreCount() <= queueDiffCnt)) {
					queueIdxCnt++;
					queueIdxCntTmp = 0;

					if((queueIdxCnt == 15) && (queueDiffCnt > 0)) {
						queueDiffCnt-= 1;
						queueIdxCnt = 0;
					}

				} else if((stackClient.getStoreCount() >= (this->getBufferChunk() + 10)) && (stackClient.getStoreCount() >= queueDiffCnt)) {
					queueIdxCntTmp++;
					queueIdxCnt = 0;

					if(queueIdxCntTmp == 50) {
						queueDiffCnt-= 1;
						queueIdxCntTmp = 0;

					}
				} else {
					queueIdxCnt = 0;
					queueIdxCntTmp = 0;

				}

				//fprintf(stderr, "mp3 storeCnt %d , bbuffChunk %d + %d  %d %d \n",stackClient.getStoreCount(),this->getBufferChunk(), queueDiffCnt, queueIdxCnt, queueIdxCntTmp);
				if( stackClient.getStoreCount() > (this->getBufferChunk() + queueDiffCnt)) {
					flagBuffer = false;

					while( stackClient.getStoreCount() > (this->getBufferChunk() + queueDiffCnt)) {
						stackClient.incSendCnt();

					}

					this->feedPcmData = (short *)stackClient.getQueueInStack();
					stackClient.incSendCnt();
					// chunk가 존재하는 정상 진행 상태
				} else if( stackClient.getStoreCount() > 0 ) {
					if( flagBuffer == false ) {
						this->feedPcmData = (short *)stackClient.getQueueInStack();
						stackClient.incSendCnt();

					} else {
						if( stackClient.getStoreCount() > this->getBufferChunk() )  {
							flagBuffer = false;
						}
						this->feedPcmData = (short *)stackClient.getQueueInStack();
						stackClient.incSendCnt();

					}
					
					// chunk가 없기 때문에 buffering 진입
				} 
				else {
					flagBuffer = true;
					this->feedPcmData = feedNullData;
					//fprintf(stderr,"NULL!!!!!!!!!!!!!!!!!!!!!!!!!!!\n");
					if( stackClient.getStoreCount() == 0 ) {
						if(queueDiffCnt <= 20) {
							queueDiffCnt+=2;

						} else {
							while( stackClient.getStoreCount() <= this->getBufferChunk() + queueDiffCnt ) {
								if( flagTerm == true ) break;
								usleep(Common::TIME_SLEEP);
							}
						}
					}
				}

				if( this->tDelayInfo.playVolume != Common::SIZE_DEFAULT_VOLUME ) { 
					for( idx = 0 ; idx < chunkSize / 2 ; idx++ ) { 
						feedPcmData[idx] = (int32_t)feedPcmData[idx] * this->tDelayInfo.playVolume / 100; 
					} 
				} 

				while( true ) { 
					if( (tPcmAvail = snd_pcm_avail(this->tPlaybackHandle)) == -EPIPE ) { 
						snd_pcm_prepare(this->tPlaybackHandle); 

					} 

					frameSize = (chunkSize / frameBytes) / encFrame > tPcmAvail ? (chunkSize / frameBytes) / encFrame : tPcmAvail; 

					if( frameSize < 0 ) { 
						snd_pcm_avail_delay(this->tPlaybackHandle, &tPcmAvail, &tPcmDelay); 
						snd_pcm_wait(this->tPlaybackHandle, tPcmDelay); 

						if( prepareCnt++ == Common::COUNT_PREPARE_RETRY ) { 
							fprintf(stderr, "ALSA - ALSA interface prepare failed \n"); 

							prepareCnt = 0; 
							break; 
						} 

					} else if( frameSize > (chunkSize / frameBytes) / encFrame) { 
						frameSize = (chunkSize / frameBytes) / encFrame; 
						break; 

					} else { 
						break; 
					} 
				} 

				if( (err = snd_pcm_writei(this->tPlaybackHandle, feedPcmData, frameSize)) < 0 ) { 
					fprintf(stderr, "ALSA - [pcm] read from audio interface failed : [%d/%d] [%02d] %s\n",frameSize, (int)tPcmAvail, err, snd_strerror(err)); 

					if( this->recoverAlsaHandle(this->tPlaybackHandle, err) != true ) { 

						break; 
					} 
				} 
				tPcmAvail = snd_pcm_avail(this->tPlaybackHandle); 
				snd_pcm_avail_delay(this->tPlaybackHandle, &tPcmAvail, &tPcmDelay); 
				snd_pcm_wait(this->tPlaybackHandle, tPcmDelay); 

			} // end of while() 
		}

		while( true ) {
			if( this->flagTerm == true ) break;

			usleep(Common::TIME_SLEEP);
		}
		
		if( this->tPlaybackHandle != NULL) {
			fprintf(stderr, "Free read PCM data called..\n");

			snd_pcm_drop(this->tPlaybackHandle);
			snd_pcm_drain(this->tPlaybackHandle);
			snd_pcm_close(this->tPlaybackHandle);
			this->tPlaybackHandle = NULL;
		}

		fprintf(stderr, "End of main function...\n");
	}

	int PcmHandle::recoverAlsaHandle(snd_pcm_t *_handle, int _err) {
		if( _err == -EPIPE ) {
			// under-run
			if( (_err = snd_pcm_prepare(_handle)) < 0 ) {
				fprintf(stderr, "ALSA - Can't recovery from under-run, prepare failed : %s\n", snd_strerror(_err));

			}
			return true;

		} else if( _err == -ESTRPIPE ) {
			while( (_err = snd_pcm_resume(_handle)) == -EAGAIN ) {
				/* wait until the suspend flag is released */
			}

			if( _err < 0 ) {
				if( (_err = snd_pcm_prepare(_handle)) < 0 ) {
					fprintf(stderr, "AlSA - Can't recovery from suspend, prepare failed : %s\n", snd_strerror(_err));
				}
			}

			return true;
		}
		if(this->setPcmHandle() == false)
			this->flagTerm = true;
		else
			this->tVarFlag.closeHandle = false;

		return true;
	} 

	int PcmHandle::getLevelValue(){
		int     volVal, rc, dcVal;
		int     valLevel = 10;
		static int storeVal = -1;

		if( this->feedPcmData == NULL ) return false;

		if( this->tDelayInfo.playVolume != Common::SIZE_DEFAULT_VOLUME ) {
			this->feedPcmData[0] = (int32_t)this->feedPcmData[0] * this->tDelayInfo.playVolume / 100;
		}

		if( (int)this->feedPcmData[0] == 0 && this->tDelayInfo.playVolume != 0 ) {
			volVal = (int)this->feedPcmData[1];

		} else {
			volVal = (int)this->feedPcmData[0];
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
	}

	void PcmHandle::pcmStop(void) {
		this->flagTerm = true;

	}

	//pcm capture thread
	void PcmHandle::execute() {
		this->flagTerm = false;

		this->pcmFunc = thread(&PcmHandle::pcmPlayLoop , this);
		this->pcmFunc.detach();
	}

	//mp3 decoder thread
	void ClientMp3Dec::execute() {
		this->flagTerm = false;
			fprintf(stderr, "Client - ProcMp3Decode() thread called... \n");

			threadMp3Dec = thread(&ClientMp3Dec::procMp3Decode , this);
			threadMp3Dec.detach();
	}


	void SocketClient::execute() {
		this->flagTerm = false;

		if( pcmHandle.getEncMode() == Common::FLAG_MODE_MP3) {
			fprintf(stderr, "Client - Initialize MP3 queue & parameter... \n");

			//mp3 queue setting
			stackClient.initQueueInfo(&tMp3Queue);

			stackClient.setQueueSize(&tMp3Queue, (this->tEncInfo.bitRate * 1000) * Common::SIZE_MAX_SCALE_LAYER / this->tEncInfo.sampleRate - 1);
			stackClient.setQueueScale(&tMp3Queue, 5);

			if( stackClient.setQueueInfo(&tMp3Queue) == false ) {
				fprintf(stderr, "Client - Initialize Mp3 queue error...\n");
			
	
			} else {
				fprintf(stderr, "Client - Initialize Mp3 queue success...\n");
			}

			fprintf(stderr, "Client - ProcRecvMp3Data() thread called... \n");
			
			//mp3 recv thread
			this->clientMp3Rev = thread(&SocketClient::procRecvMp3Data , this);
			this->clientMp3Rev.detach();

		} else {
			//pcm recv thread
			this->clientFunc = thread(&SocketClient::procRecvPcmData , this);
			this->clientFunc.detach();
		}

	}

	SocketClient::SocketClient(void) {
		fprintf(stderr," Create SocketClient instance\n");
		return ;

	}

	SocketClient::~SocketClient(void) {
		this->closeSocket();

		return ;

	}

	int SocketClient::getIpStatus(void) {

		return this->ipStatus;
	}
	
	int SocketClient::getPlayInfoEncode(void){

		return this->tPlayInfo.encodeMode;
	}

	int SocketClient::getPlayInfoRate(void) {

		return this->tPlayInfo.rate;
	}
	int SocketClient::getPlayInfoChannels(void) {

		return this->tPlayInfo.channels;
	}

	int SocketClient::getPlayInfoChunkSize(void) {

		return this->tPlayInfo.chunkSize;
	}

	int SocketClient::getPlayInfoPcmBuf(void) {

		return this->tPlayInfo.pcmBufferSize;
	}

	int SocketClient::getPlayInfoPcmPer(void) {

		return this->tPlayInfo.pcmPeriodSize;
	}

	int SocketClient::getCastType(void) {

		return this->tCastInfo.type;
	}
	int SocketClient::getEncRate(void) {

		return this->tEncInfo.bitRate;
	}
	int SocketClient::getEncSampleRate(void) {

		return this->tEncInfo.sampleRate;
	}
	int SocketClient::getEncChunkSize(void) {

		return this->tEncInfo.chunkSize;
	}




	int SocketClient::setServerList(IP_INFO_t *_tIpInfo) {
		//TCP 정의		
		this->tClientParams.serverCnt = _tIpInfo->serverCnt;

		tServerList = (SERVER_LIST_t *)malloc(sizeof(SERVER_LIST_t) * this->tClientParams.serverCnt );

		if( strcmp(_tIpInfo->castType, "unicast") == 0) {
			this->tCastInfo.cast = SOCK_STREAM;
			this->tCastInfo.type = Common::FLAG_TCP;

			if( this->tClientParams.serverCnt == 1 ) {
				strcpy(this->tServerList[0].ipAddr, _tIpInfo->ipAddr1); 
				this->tServerList[0].port = _tIpInfo->port1;
				this->tServerList[0].stat = true;

			} else {
				strcpy(this->tServerList[0].ipAddr, _tIpInfo->ipAddr1); 
				strcpy(this->tServerList[1].ipAddr, _tIpInfo->ipAddr2); 
				this->tServerList[0].port = _tIpInfo->port1;
				this->tServerList[1].port = _tIpInfo->port2;
				this->tServerList[0].stat = true;
				this->tServerList[1].stat = true;

			}
		} else {

			this->tCastInfo.cast = SOCK_DGRAM;
			this->tCastInfo.type = Common::FLAG_UDP;

			strcpy(this->tServerList[0].ipAddr, _tIpInfo->mIpAddr); 
			this->tServerList[0].port = _tIpInfo->mPort;
			this->tServerList[0].stat = true;
		}

		return true;

	}

	bool SocketClient::getSelect(void) {
		int     rc;
		struct  timeval timeout;
		fd_set  fdReads;

		FD_ZERO(&fdReads);
		FD_SET(this->sockFd, &fdReads);

		timeout.tv_sec  = Common::TIME_TIMEOUT_SEC;
		timeout.tv_usec = 0;

		if( (rc = select(this->sockFd + 1, &fdReads, NULL, NULL, &timeout)) < 0 ) {
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

	int SocketClient::initSocket() {
		int     idx, rc;
		int     serverSockFd;
		int     serverPort;
		int     option;
		int     recvBufSize;
		int     optLen;
		int 	fcntlFlag; 
		int 	nonblockFlag;
		int 	connectError;
		char    serverIpAddr[128];
		char    bufParameter[128];
		static int serverIdx = 0;

		socklen_t  len = sizeof(recvBufSize);

		struct  sockaddr_in serverAddr;
		struct  sockaddr_in mCastAddr;
		struct  ip_mreq     mreq;
		struct  linger      optval;

		struct  timeval timeout;
		fd_set fdset, fdwset;

		socklen_t   optionLen;
		optionLen = sizeof(option);

		if( this->tCastInfo.type == Common::FLAG_TCP ) {  /* uni-cast type */
			while( true ) {

				// server 순회
				for( idx = 0 ; idx < this->tClientParams.serverCnt ; idx++ ) {
					if( this->tServerList[idx].stat == true ) break;
				}

				if( idx == this->tClientParams.serverCnt) break;

				if( serverIdx == this->tClientParams.serverCnt ) serverIdx = 0;

				strcpy(serverIpAddr, this->tServerList[serverIdx].ipAddr);

				serverPort = this->tServerList[serverIdx].port;

				this->ipStatus = serverIdx;

				serverAddr.sin_family = AF_INET;
				serverAddr.sin_addr.s_addr = inet_addr(serverIpAddr);
				serverAddr.sin_port = htons(serverPort);
				
				if( (serverSockFd = socket(AF_INET, this->tCastInfo.cast, 0)) < 0 ) {
					fprintf(stderr, "intsocket() failed : [%02d] %s\n", errno, strerror(errno));
					return false;
				}

				if( getsockopt(serverSockFd, SOL_SOCKET, SO_RCVBUF, &recvBufSize, &len) < 0 ) {
					fprintf(stderr, "getsockopt() SO_RCVBUF failed : [%02d] %s\n", errno, strerror(errno));
				}

				optLen = recvBufSize * Common::SIZE_SCALE_RCVBUF;
				if( setsockopt(serverSockFd, SOL_SOCKET, SO_RCVBUF, (char*)&optLen, sizeof(optLen)) < 0 ) {
					fprintf(stderr, "setsockopt() SO_RCVBUF failed : [%02d] %s\n", errno, strerror(errno));
				}

				fcntlFlag = fcntl(serverSockFd, F_GETFL, 0);
				nonblockFlag = fcntlFlag | O_NONBLOCK;

				if( (rc = fcntl( serverSockFd, F_SETFL, nonblockFlag )) < 0 )
					fprintf(stderr,"NON BLOCK fail\n");

				optval.l_onoff  = 1;
				optval.l_linger = 0;

				if( setsockopt(serverSockFd, SOL_SOCKET, SO_LINGER, &optval, sizeof(optval)) < 0 ) {
					fprintf(stderr, "setsockopt() SO_LINGER failed : [%02d] %s\n", errno, strerror(errno));
					
					return false;
				}
				
				FD_ZERO( &fdset ); 
				FD_SET( serverSockFd, &fdset ); 
				fdwset = fdset; 

				timeout.tv_sec  = 1;
				timeout.tv_usec = 0;

				connect(serverSockFd, (struct sockaddr *)&serverAddr, sizeof(serverAddr));
				
				if( (rc = select(serverSockFd + 1, &fdset, &fdwset, NULL, &timeout)) < 0 ) {
					switch( errno ) {
						case 4 :
							break;
						default :
							fprintf(stderr, "Server - select() failed : [%02d] %s\n", errno, strerror(errno));
							break;
					}
				}

				getsockopt(serverSockFd, SOL_SOCKET, SO_ERROR, (void*) &connectError, &len);

				if ( (FD_ISSET(serverSockFd , &fdwset ) > 0 ) && (connectError == 0) ) { 
					serverIdx++;
					for( idx = 0 ; idx < this->tClientParams.serverCnt ; idx++ ) {
						this->tServerList[idx].stat = true;
					}
					if( (rc = fcntl( serverSockFd, F_SETFL, fcntlFlag )) < 0 )
						fprintf(stderr,"BLOCK fail\n");

					break;
				} else {
					fprintf(stderr, "Server info[%s/%d] connect failed : [%02d] %s\n",
							this->tServerList[serverIdx].ipAddr, this->tServerList[serverIdx].port, 
							errno, strerror(errno));
					this->tServerList[serverIdx].stat = false;
					serverIdx++;
				}
			}
			if( this->tServerList[this->ipStatus].stat == false ) return false;

		} else {  /* multi-cast type */
			// set server ipaddr
			strcpy(serverIpAddr, this->tServerList[0].ipAddr);
			serverPort = this->tServerList[0].port;
			mCastAddr.sin_addr.s_addr = inet_addr(serverIpAddr) ; 

			this->ipStatus = 2;

			// multicast ip address check
			if( !IN_MULTICAST(ntohl(mCastAddr.sin_addr.s_addr)) ) {
				fprintf(stderr, "Given address [%s] is not multicast...\n", inet_ntoa(mCastAddr.sin_addr));
				return false;
			}

			serverAddr.sin_family = AF_INET;
			serverAddr.sin_addr.s_addr = htonl(INADDR_ANY);
			serverAddr.sin_port = htons(serverPort);

			// BIND init
			if( bind(serverSockFd, (struct sockaddr *)&serverAddr, sizeof(serverAddr)) < 0 ) {
				fprintf(stderr, "bind() failed : [%02d] %s\n", errno, strerror(errno));
				return false;
			}

			// Set multicast address
			if (inet_aton(serverIpAddr, &mreq.imr_multiaddr) < 0 ) {
				fprintf(stderr, "inet_aton() failed : [%02d] %s\n", errno, strerror(errno));
				return false;
			}
			mreq.imr_interface.s_addr = htonl(INADDR_ANY);

			if( setsockopt(serverSockFd, IPPROTO_IP, IP_ADD_MEMBERSHIP, &mreq, sizeof(mreq)) < 0 ) {
				fprintf(stderr, "setsockopt() IP_ADD_MEMBERSHIP failed : [%02d] %s\n", errno, strerror(errno));
				return false;
			}

			option = false;
			// avoiding loopback
			if( setsockopt(serverSockFd, IPPROTO_IP, IP_MULTICAST_LOOP, &option, sizeof(optionLen)) < 0 ) {
				fprintf(stderr, "setsockopt() MULTICAST_LOOP failed : [%02d] %s\n", errno, strerror(errno));
				return false;
			}
		}

		return serverSockFd;
	} // end of InitSocket()

	bool SocketClient::initSocketOption(int _sockFd, char *_hostName)
	{
		int rc = true;
		int optval = true;

		struct  timeval tTimeo = {Common::TIME_RCV_TIMEOUT , 0};
		struct  linger  tLinger;

		this->sockFd = _sockFd;

		tLinger.l_onoff  = true;
		tLinger.l_linger = 0;

		if( setsockopt(this->sockFd, SOL_SOCKET, SO_LINGER, (char *)&tLinger, sizeof(tLinger)) < 0 ) {
			fprintf(stderr, "setsockopt() SO_LINGER failed : [%02d] %s\n", errno, strerror(errno));
			rc = false;
		}

		if( setsockopt(this->sockFd, IPPROTO_TCP, TCP_NODELAY, &optval, sizeof (optval)) < 0 ) {
			fprintf(stderr, "setsockopt() SO_NODELAY failed : [%02d] %s\n", errno, strerror(errno));
		}

		if( setsockopt(this->sockFd, SOL_SOCKET, SO_RCVTIMEO, &tTimeo , sizeof(tTimeo) ) < 0 ) {
			fprintf(stderr, "setsockopt() SO_RCVTIMEO failed : [%02d] %s\n", errno, strerror(errno));
		
		}

		//this->getIpAddress();
		strcpy(this->tHostInfo.hostName, _hostName);

		// TCP 인 경우 hostname 전송
		if(rc != false) {
			if( this->tCastInfo.type == Common::FLAG_TCP ) {
				fprintf(stderr, "Client - Send client hostname..\n");
				rc = this->sendHostName(this->sockFd);
			}
		}

		return rc;
	}

	bool SocketClient::getIpAddress() {
		int tokenIndex = 0;   
		int pos = 0, fileSize = 0, stringLength = 0;
		char *begin, *end, *buffer;
		FILE *fp;
		char tmpIpAddr[3][24];
		char tmpStat[3][12];

		if( (fp = fopen(Common::JSONFILE, "rb")) == NULL)
			fprintf(stderr,"JSON File Error\n");

		// 파일 크기 구하기
		fseek(fp, 0, SEEK_END);
		fileSize = ftell(fp);
		fseek(fp, 0, SEEK_SET);

		// 파일 크기 + NULL 공간만큼 메모리를 할당하고 0으로 초기화
		buffer = (char*)malloc(fileSize + 1);
		memset(buffer, 0, sizeof(buffer));

		// 파일 내용 읽기
		if (fread(buffer, fileSize, 1, fp) < 1)
		{
			fileSize = 0;
			free(buffer);
			fclose(fp);
			fprintf(stderr,"JSON read Error\n");
		}

		fclose(fp);    

		if (buffer[pos] != '{')   // 문서의 시작이 {인지 검사
			return false;


		memset(tmpStat, 0,sizeof(tmpStat));
		memset(tmpIpAddr, 0,sizeof(tmpIpAddr));

		while(true) {

			while (pos < fileSize) {
				switch (buffer[pos])  
				{
					case '"':        
						begin =buffer + pos + 1;

						end = strchr(begin, '"');
						if (end == NULL)    
							break;          

						stringLength = end - begin; 
						if(tokenIndex == 8){
							memcpy(tmpStat[0], begin, stringLength);

						} else if( tokenIndex == 14) {
							memcpy(tmpIpAddr[0], begin, stringLength);

						} else if( tokenIndex == 25) {
							memcpy(tmpStat[1], begin, stringLength);

						} else if( tokenIndex == 29) {
							memcpy(tmpIpAddr[1], begin, stringLength);

						} else if( tokenIndex == 42) {
							memcpy(tmpStat[2], begin, stringLength);

						} else if( tokenIndex == 46) {
							memcpy(tmpIpAddr[2], begin, stringLength);
						} 
						tokenIndex++; 
						pos = pos + stringLength + 1; 

						break;
				}
				pos++; 
				if(tokenIndex == 47)
					break;
			}
			break;
		}
#if 0
		fprintf(stderr,"%s : %s , %s : %s , %s : %s \n",tmpStat[2], tmpIpAddr[2],
				tmpStat[0],  tmpIpAddr[0], tmpStat[1], tmpIpAddr[1]);

		if(strcmp(tmpStat[2], "enabled") == 0) {
			strcpy(this->tHostInfo.ipAddr, tmpIpAddr[2]);

		} else if(strcmp(tmpStat[0], "enabled") == 0) {
			strcpy(this->tHostInfo.ipAddr, tmpIpAddr[0]);

		} else if(strcmp(tmpStat[1], "enabled") == 0) {
			strcpy(this->tHostInfo.ipAddr, tmpIpAddr[1]);
		}
#endif
		
		free(buffer);    // 문서 동적 메모리 해제
		return true;
	}

	bool SocketClient::getMacAddress() {
		int nSD; // Socket descriptor
		struct ifreq *ifr; // Interface request
		struct ifconf ifc;
		int idx, numIf;

		memset(this->macAddr, 0x00,sizeof(this->macAddr));

		memset(&ifc, 0, sizeof(ifc));
		ifc.ifc_ifcu.ifcu_req = NULL;
		ifc.ifc_len = 0;

		// Create a socket that we can use for all of our ioctls
		nSD = socket( PF_INET, SOCK_DGRAM, 0 );
		if ( nSD < 0 )  return false;

		if(ioctl(nSD, SIOCGIFCONF, &ifc) < 0) return false;

		if ((ifr = (ifreq*)  malloc(ifc.ifc_len)) == NULL) {
			close(nSD);
			free(ifr);

			return false;
		
		} else {
			ifc.ifc_ifcu.ifcu_req = ifr;
			if (ioctl(nSD, SIOCGIFCONF, &ifc) < 0) {
				close(nSD);
				free(ifr);

				return false;
			}

			numIf = ifc.ifc_len / sizeof(struct ifreq);
			for (idx = 0; idx < numIf; idx++) {
				struct ifreq *r = &ifr[idx];
				struct sockaddr_in *sin = (struct sockaddr_in *)&r->ifr_addr;

				if (!strcmp(r->ifr_name, "lo"))
					continue; // skip loopback interface

				if(ioctl(nSD, SIOCGIFHWADDR, r) < 0)
					break;

				sprintf(this->macAddr, "%02x:%02x:%02x:%02x:%02x:%02x",
						(unsigned char)r->ifr_hwaddr.sa_data[0],
						(unsigned char)r->ifr_hwaddr.sa_data[1],
						(unsigned char)r->ifr_hwaddr.sa_data[2],
						(unsigned char)r->ifr_hwaddr.sa_data[3],
						(unsigned char)r->ifr_hwaddr.sa_data[4],
						(unsigned char)r->ifr_hwaddr.sa_data[5]);
				break;
			}
		}

		close(nSD);
		free(ifr);

		return true;
	}

	bool SocketClient::sendHostName(int _sockFd)
	{
		char    hostName[128];
		char    recvMsg[128];

		int     rc;
		static int macFlag = false;

		memset(&hostName, 0x00, sizeof(hostName));

		// Get MAC address
		if( macFlag == false) {
			if( this->getMacAddress() == false ) {
				fprintf(stderr, "SendHostName() Get mac address failed..\n");

				return false;

			} else {
				strcpy(this->tHostInfo.macAddr, this->macAddr);
				fprintf(stderr, "MAC : [%s]\n", this->tHostInfo.macAddr);
				macFlag = true;

			}
		}
		
		if( send(this->sockFd, &this->tHostInfo, sizeof(this->tHostInfo), 0) < 0 ) {
			fprintf(stderr, "SendHostName() send hostname failed : [%02d] %s\n", errno, strerror(errno));

			return false;
		}

		return true;
	}

	bool SocketClient::recvPlayInfo() {
		int     rc;
		char    sampleRate[128];
		char    *bodyData;
		FILE    *fp;

		HEADER_MP3_INFO_t   tHeaderInfo;

		if( this->tCastInfo.type == Common::FLAG_TCP ) {

			if( (rc = recv(this->sockFd, &this->tPlayInfo, sizeof(this->tPlayInfo), MSG_WAITALL)) < 0 ) {
				fprintf(stderr, "Client - recv() play information failed : [%02d] %s\n", errno, strerror(errno));

				return false;

			} else if( rc == 0 ) {
				fprintf(stderr, "Client - recv() play information connection reset by peer..\n");

				return false;
			}

			if( rc != sizeof(this->tPlayInfo) ) {
				fprintf(stderr, "Client - recv() play information invalid length..\n");

				return false;
			}

			if( this->tPlayInfo.rate == 0 && this->tPlayInfo.channels == 0 ) {
				fprintf(stderr, "Client - recv() play information invalid data..\n");

				return false;
			}
			// encode mode check
			
			if( this->tPlayInfo.encodeMode == Common::FLAG_MODE_MP3 ) {

				pcmHandle.setEncMode(Common::FLAG_MODE_MP3);

				if( (rc = recv(this->sockFd, &this->tEncInfo, sizeof(this->tEncInfo), MSG_WAITALL)) < 0 ) {
					fprintf(stderr, "Client - recv() encode information failed : [%02d] %s\n", 
							errno, strerror(errno));

					return false;

				} else if( rc == 0 ) {
					fprintf(stderr, "Client - recv() encode information connection reset by peer..\n");

					return false;
				}

				if( rc != sizeof(this->tEncInfo) ) {
					fprintf(stderr, "Client - recv() play encode information invalid length..\n");

					return false;
				}

				this->tPlayInfo.chunkSize = this->tEncInfo.chunkSize;
	
			} else {
				pcmHandle.setEncMode(Common::FLAG_MODE_PCM);
			}

		} else {
			fprintf(stderr, "Client - Play information receive from multicast channel...\n");

			if( read(this->sockFd, &tHeaderInfo, sizeof(tHeaderInfo)) < 0 ) {
				fprintf(stderr, "Client - read() headerInfo failed : [%02d] %s\n", errno, strerror(errno));

				return false;
			}

			this->tPlayInfo.rate          = tHeaderInfo.rate;
			this->tPlayInfo.channels      = tHeaderInfo.channels;
			this->tPlayInfo.chunkSize     = tHeaderInfo.chunkSize;
			this->tPlayInfo.pcmBufferSize = tHeaderInfo.pcmBufferSize;
			this->tPlayInfo.pcmPeriodSize = tHeaderInfo.pcmPeriodSize;
			this->tPlayInfo.idx           = 0;

			if( tHeaderInfo.encMode == FLAG_MODE_MP3 ) {
				pcmHandle.setEncMode(Common::FLAG_MODE_MP3);
				
				this->tPlayInfo.encodeMode = Common::FLAG_MODE_MP3;
				this->tPlayInfo.chunkSize = tHeaderInfo.pcmChunkSize;
				this->tEncInfo.chunkSize  = tHeaderInfo.pcmChunkSize;
				this->tEncInfo.bitRate    = tHeaderInfo.bitRate;
				this->tEncInfo.sampleRate = tHeaderInfo.sampleRate;

			} else {
				pcmHandle.setEncMode(Common::FLAG_MODE_PCM);

			} 
		
			// UDP play information 정보 획득 후 남는 body 처리
			bodyData = (char *)malloc(tHeaderInfo.chunkSize * sizeof(char));
			if( read(this->sockFd, bodyData, tHeaderInfo.chunkSize) < 0 ) {
				fprintf(stderr, "Client - read() body data failed : [%02d] %s\n", errno, strerror(errno));

				free(bodyData);
				return false;

			}
			free(bodyData);
		}
		if( (fp = fopen(Common::PATH_SAMPLE_RATE, "w")) == NULL ) {
			fprintf(stderr, "Client - Open file [%s] failed : [%02d] %s\n",
					Common::PATH_SAMPLE_RATE, errno, strerror(errno));
			return false;

		} else {
			sprintf(sampleRate, "%d Hz", this->tPlayInfo.rate);
			fwrite(sampleRate, sizeof(char), strlen(sampleRate), fp);
			fclose(fp);
		}

		return true;
	} // end of RecvPlayInfo()


	void SocketClient::procRecvPcmData(void) {
		int     rc          = 0;
		char    hbData      = Common::FLAG_HB_RESPONSE;
		int     crcCnt      = 0;
		int     retryCnt    = 0;
		int 	recvSize;

		char    recvHeadMsg[128];
		char	*recvBodyData = NULL;

		HEADER_PCM_INFO_t   tHeaderInfo;

	//	pthread_setcancelstate(PTHREAD_CANCEL_ENABLE, NULL);

		if( (recvBodyData = (char *)malloc(this->tPlayInfo.chunkSize * sizeof(char))) == NULL ) {
			fprintf(stderr, "Client - PCM malloc body data failed : [%02d] %s\n", errno, strerror(errno));
			pcmHandle.setThreadRecv(true);

		}

		if( this->tCastInfo.type == Common::FLAG_UDP ) {
			/* UDP(multi-cast) part */
			while( !this->flagTerm ) {
				if( (rc = read(this->sockFd, recvHeadMsg, sizeof(recvHeadMsg))) < 0 ) {
					fprintf(stderr, "Client - Recv head information failed : [%02d] %s\n", errno, strerror(errno));

					break;
				}
				if( rc != sizeof(tHeaderInfo) ) {
					fprintf(stderr, "Client - Recv head information invalid length : [%d/%d]\n",
							rc, (int)sizeof(tHeaderInfo));
					break;
				}

				memcpy(&tHeaderInfo, recvHeadMsg, sizeof(tHeaderInfo));
				if( (rc = read(this->sockFd, recvBodyData, this->tPlayInfo.chunkSize)) < 0 ) {
					fprintf(stderr, "Client - Recv body data failed : [%02d] %s\n", errno, strerror(errno));
					break;
				}

				if( this->getCrcValue(recvBodyData, this->tPlayInfo.chunkSize) == tHeaderInfo.crcValue ) {
					if( this->flagTerm ) break;
					stackClient.setQueueInStack(recvBodyData);
					crcCnt = 0;

				} else {
					fprintf(stderr, "Client - [%d] PCM body data CRC error found.. recv/send - [%d]/[%d]\n", 
							crcCnt, this->getCrcValue(recvBodyData, this->tPlayInfo.chunkSize), tHeaderInfo.crcValue);

					if( crcCnt++ >= Common::COUNT_CRC_MAX ) {
						break;
					}
				}
			} // end of while()

		} else {
//			if( (rc = fcntl( this->sockFd, F_SETFL, O_NONBLOCK )) < 0 )
//				fprintf(stderr,"NON BLOCK fail\n");

START_PCM_UNICAST :
			/* TCP(uni-cast) part */
			while( !this->flagTerm ) {
				// header -> recv buffer

				if(!this->getSelect()) {
					break;
				}

				if( (rc = recv(this->sockFd, recvBodyData, this->tPlayInfo.chunkSize, MSG_WAITALL)) <= 0 ) {
					if( errno == EAGAIN ) {
						fprintf(stderr, "Client - RecvPcmUnicast() body not received..\n");

					} else {

						fprintf(stderr, "Client - RecvPcmUnicast() not received..\n");
					}
					break;
				}

				if( rc != this->tPlayInfo.chunkSize ) {
					fprintf(stderr, "Client - Recv body information invalid length : [%d/%d]\n",
							rc, this->tPlayInfo.chunkSize);

				}

				if( this->flagTerm ) break;
				stackClient.setQueueInStack(recvBodyData);

			} // end of while()
		}

		fprintf(stderr, "Client - Disconnected by server.. \n");
		fprintf(stderr, "         Find next server.. \n");

		if(	this->ipStatus != 0 ) {  

			if( this->reconnSocket() == true ) {
				pcmHandle.printClientInfo();

				this->flagTerm = false;
				common.setReconFlag();

				goto START_PCM_UNICAST;
			} 
		}

		if( recvBodyData != NULL ) {
			free(recvBodyData);
			recvBodyData = NULL;
		}

		pcmHandle.setThreadRecv(true);
		common.term();


	//	pthread_exit(NULL);
	} // end of ProcRecvPcmData()

	int SocketClient::getRecvBufferSize(void) {
		int     rc, stRc = 0, accRc, stAccRc = 0;
		char    recvMsg[this->tPlayInfo.chunkSize];

		accRc = sizeof(recvMsg);

		while( true ) {
			if( (rc = recv(this->sockFd, &recvMsg, accRc, MSG_PEEK|MSG_DONTWAIT)) < 0 ) {
				fprintf(stderr, "getRecvBufferSize() recv() MSG_PEEK failed : [%02d] %s\n", errno, strerror(errno));

				return false;
			}

			if( stRc == rc ) {
				break;

			} else {
				stRc = rc;
			}

			accRc += rc;
			stAccRc += rc;
		}
		
		return stAccRc;
	} // end of getRecvBufferSize()

	void SocketClient::procRecvMp3Data(void) {

		int     rc          = 0;
		char    hbData      = Common::FLAG_HB_RESPONSE;
		int     retryCnt    = 0;
		int 	recvSize;
		static  int crcCnt  = 0;

		char    recvHeadMsg[128];
		char	*recvBodyData = NULL;

		HEADER_MP3_INFO_t   tHeaderInfo;
 		HEADER_MP3_CHUNK_t  tChunkInfo;

		//pthread_setcancelstate(PTHREAD_CANCEL_ENABLE, NULL);


		if( this->tCastInfo.type == Common::FLAG_UDP ) {
			/* UDP(multi-cast) part */
			while( !this->flagTerm ) {

				if( (rc = read(this->sockFd, recvHeadMsg, sizeof(recvHeadMsg))) < 0 ) {
					fprintf(stderr, "Client - Recv head information failed : [%02d] %s\n", errno, strerror(errno));

					break;
				}

				if( rc != sizeof(tHeaderInfo) ) {
					fprintf(stderr, "Client - Recv head information invalid length : [%d/%d]\n", rc, sizeof(tHeaderInfo));

					break;
				}

				memcpy(&tHeaderInfo, recvHeadMsg, sizeof(tHeaderInfo));
				if( recvBodyData != NULL ) {
					free(recvBodyData);
					recvBodyData = NULL;
				}

				if( (recvBodyData = (char *)malloc(tHeaderInfo.chunkSize * sizeof(char))) == NULL ) {
					fprintf(stderr, "Client - MP3 malloc body data failed : [%02d] %s\n", errno, strerror(errno));

					break;
				}

				if( (rc = read(this->sockFd, recvBodyData, tHeaderInfo.chunkSize)) < 0 ) {
					fprintf(stderr, "Client - Recv body data failed : [%02d] %s\n", errno, strerror(errno));
					break;

				}

				if( this->getCrcValue(recvBodyData, tHeaderInfo.chunkSize) == tHeaderInfo.crcValue ) {
					stackClient.setQueue(&tMp3Queue, recvBodyData, tHeaderInfo.chunkSize);
					crcCnt = 0;

				} else {
					fprintf(stderr, "Client - [%d] MP3 body data CRC error found.. recv/send - [%d]/[%d]\n",
							crcCnt, this->getCrcValue(recvBodyData, tHeaderInfo.chunkSize), tHeaderInfo.crcValue);

					if( crcCnt++ >= Common::COUNT_CRC_MAX ) {
						break;
					}
				}
			} // end of while()
		} else {
START_MP3_UNICAST :
		/* TCP(uni-cast) part */
			while(  !this->flagTerm ) {
				
/*				if( (recvSize = this->getRecvBufferSize()) == false ) {
					// msg 크기 못 읽어오면 default 처리
					recvSize = this->tPlayInfo.chunkSize;
				}
				if( (rc = fcntl( this->sockFd, F_SETFL, O_NONBLOCK )) < 0 )
					fprintf(stderr,"NON BLOCK fail\n");

				*/
				if(!this->getSelect()) {
					break;
				}

				if(recv(this->sockFd,&tChunkInfo ,sizeof(tChunkInfo), 0) <= 0) {
					fprintf(stderr,"recv chunkSize failed\n");
					tHeaderInfo.chunkSize = 960;
				} 

				tHeaderInfo.chunkSize = tChunkInfo.chunkSize;
				retryCnt = 0;

				if( recvBodyData != NULL ) {
					free(recvBodyData);
					recvBodyData = NULL;
				}

				if( (recvBodyData = (char *)malloc(tHeaderInfo.chunkSize * sizeof(char))) == NULL ) {
					fprintf(stderr, "Client - MP3 malloc body data failed : [%02d] %s\n", errno, strerror(errno));

					break;
				}

				if( (rc = recv(this->sockFd, recvBodyData, tHeaderInfo.chunkSize , MSG_WAITALL)) <= 0 ) {
					if( errno == EAGAIN ) {
						fprintf(stderr, "Client - RecvMp3Unicast() body not received..\n");

					} else {
						fprintf(stderr, "Client - Recv body data failed : [%02d] %s\n", errno, strerror(errno));
					}
					break;
				}
/*
				if( rc != recvSize ) {
					fprintf(stderr, "Client - Recv body information invalid length : [%d/%d]\n",
							rc, recvSize );

					//break;
				}
*/
				stackClient.setQueue(&tMp3Queue, recvBodyData, tHeaderInfo.chunkSize);

			} // end of while()
		}

		fprintf(stderr, "Client - Disconnected by server.. \n");
		fprintf(stderr, "         Find next server.. \n");

		if(	this->ipStatus != 0 ) {  

			if( this->reconnSocket() == true ) {
				pcmHandle.printClientInfo();
				
				this->flagTerm = false;

				goto START_MP3_UNICAST;
			} 
		}

		if( recvBodyData != NULL ) {
			free(recvBodyData);
			recvBodyData = NULL;
		}

		pcmHandle.setThreadRecv(true);
		common.term();

	//	pthread_exit(NULL);
	} // end of ProcRecvMp3Data()

	int SocketClient::reconnSocket() {
		int     sleepTime;

		sleepTime = this->tPlayInfo.idx * 50;

		fprintf(stderr, "Client - Sleep for [%d] usec\n", sleepTime);
		usleep(sleepTime);

		// socket re-declaration
		fprintf(stderr, "Client - Close current socket..\n");
		this->closeSocket();

		fprintf(stderr, "Client - Init new socket..\n");
		if( (this->sockFd = this->initSocket()) == false ) {
			return false;
		}

		fprintf(stderr, "Client - Set new socket option..\n");
		if( this->initSocketOption(this->sockFd,this->tHostInfo.hostName) == false ) {
			return false;
		}

		fprintf(stderr, "Client - Recv play information from server..\n");
		if( this->recvPlayInfo() == false ) {
			return false;
		}

		fprintf(stderr, "Client - SetPcmHandle called..\n");

		pcmHandle.setPlayInfo();
		pcmHandle.setCloseHandle(true);

		return true;
	}

	void SocketClient::closeSocket(void) {
		this->flagTerm = true;

		if( this->sockFd != -1 ) {
			fprintf(stderr, "CloseSocket called..\n");
			close(this->sockFd);
			this->sockFd = -1;
		}

		return ;
	}	
	
	LINEAR_QUEUE_t *SocketClient::getMp3QueuePtr(void) {
	//	memcpy( &this->tMp3Queue, _tMp3Queue, sizeof(LINEAR_QUEUE_t) );
		return &this->tMp3Queue;

	}



	unsigned short SocketClient::getCrcValue(char *_buf, unsigned short _len) {
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

		for(idx = 0; idx < _len; idx++) {
			crc = (crc << 8) ^ crc_tbl_ccitt[((crc >> 8) ^ *_buf++) & 0xff];
		}

		return crc;
	}

	void PcmHandle::printClientInfo(void) {
		char nowTime[128];
		Common::GetTime(nowTime);

		//system("clear");

		fprintf(stderr, "[Client information]\n");
		fprintf(stderr, "-------------------------------\n");
		fprintf(stderr, "-%s \n",nowTime);
		fprintf(stderr, "-------------------------------\n");

		fprintf(stderr, "%-17s = %s\n", Common::STR_ENCODE_MODE, pcmHandle.getEncMode() == Common::FLAG_ENCODE_MP3 ? Common::STR_ENCODE_MP3 : Common::STR_ENCODE_PCM);
		fprintf(stderr, "%-17s = %s\n", Common::STR_CAST_MODE, socketClient.getCastType() ? Common::STR_TCP_MODE : Common::STR_UDP_MODE);

		if( socketClient.getCastType() == Common::FLAG_UDP) {
			fprintf(stderr, "%-17s = %s\n", Common::STR_MCAST_IPADDR, this->tIpInfo.mIpAddr );
			fprintf(stderr, "-------------------------------\n");

			fprintf(stderr, "%-17s = %d\n", Common::STR_MCAST_PORT, this->tIpInfo.mPort);

		} else {

			if(socketClient.getIpStatus() == 0)	{
				fprintf(stderr, "%-17s = %s\n", Common::STR_SERVER_IP, this->tIpInfo.ipAddr1 );
				fprintf(stderr, "%-17s = %d\n", Common::STR_SERVER_PORT,this->tIpInfo.port1 );
				fprintf(stderr, "-------------------------------\n");
			} else {
				fprintf(stderr, "%-17s = %s\n", Common::STR_SERVER_IP, this->tIpInfo.ipAddr2);
				fprintf(stderr, "%-17s = %d\n", Common::STR_SERVER_PORT, this->tIpInfo.port1);
				fprintf(stderr, "-------------------------------\n");
			}

		}
		fprintf(stderr, "-------------------------------\n");

		if( pcmHandle.getEncMode() == Common::FLAG_ENCODE_MP3) {
			fprintf(stderr, "%-17s = %d hz\n",  Common::STR_MP3_SAMPLE_RATE, socketClient.getEncSampleRate());
			fprintf(stderr, "%-17s = %d bps\n", Common::STR_MP3_BIT_RATE, socketClient.getEncRate());

		} else {
			fprintf(stderr, "%-17s = %d hz\n", Common::STR_RATE, this->tPlayInfo.rate);
			fprintf(stderr, "%-17s = %d ch\n", Common::STR_CHANNELS, this->tPlayInfo.channels);

		}
		fprintf(stderr, "-------------------------------\n");
		fprintf(stderr, "%-17s = %d Seconds\n", Common::STR_BUFFER_RATE,this->tDelayInfo.bufRate);
		fprintf(stderr, "%-17s = %d Milli seconds\n", Common::STR_BUFFER_RATE_MS, this->tDelayInfo.bufRateMs);
		fprintf(stderr, "%-17s = %d byte\n", Common::STR_CHUNK_SIZE, this->tPlayInfo.chunkSize);
		fprintf(stderr, "-------------------------------\n");

		return ;
	}

	void ClientMp3Dec::procMp3Decode() {
		int idx;
		int chunkSize;
		int length = 0, bufLen = 0;
		int frameSize, accConsume = 0;
		int leftInLength, leftLength, inBufLen;
		int numSamples;
		int flagRoutine = false;
		const int   sizeThreshold = Common::SIZE_MP3_THRESHOLD;
		const int   inputSize     = Common::SIZE_MP3_FRAME_BUFFER;

		MP3D_INT8               mp3Input[inputSize];
		MP3D_RET_TYPE           tRetVal;
		MP3D_Decode_Params      tDecParams;
		MP3D_Mem_Alloc_Info_Sub *tMemInfo;
		LINEAR_QUEUE_t          *tMp3Queue;

	//	pthread_setcancelstate(PTHREAD_CANCEL_ENABLE, NULL);

		if( (this->tDecConfig = (MP3D_Decode_Config *)this->decodeAllocFast(sizeof(MP3D_Decode_Config))) == ((void *)0) ) {
			fprintf(stderr, "Client - ProcMp3Decode() tDecConfig failed : [%02d] %s\n", errno, strerror(errno));

		}

		if( (tRetVal = mp3d_query_dec_mem(this->tDecConfig)) != MP3D_OK ) {
			fprintf(stderr, "Client - ProcMp3Decode() mp3d_query_dec_mem failed : [%02d] %s\n", errno, strerror(errno));

		}

		this->memInfoNumReqs = this->tDecConfig->mp3d_mem_info.mp3d_num_reqs;
		fprintf(stderr, "Client - Decode MP3 info : num req [%d]\n", memInfoNumReqs);

		for(idx = 0 ; idx < this->memInfoNumReqs ; idx++ ) {
			tMemInfo = &(this->tDecConfig->mp3d_mem_info.mem_info_sub[idx]);

			if( tMemInfo->mp3d_type == Common::MP3_LAYER_1 ) {
				tMemInfo->app_base_ptr = this->decodeAllocFast(tMemInfo->mp3d_size);
				memset(tMemInfo->app_base_ptr, 0xfe, tMemInfo->mp3d_size);

				if( tMemInfo->app_base_ptr == ((void *)0) ) {
					fprintf(stderr, "Client - ProcMp3Decode() DecodeAllocFast() app_base_ptr info failed..\n");

				}

			} else {
				tMemInfo->app_base_ptr = this->decodeAllocSlow(tMemInfo->mp3d_size);

				if( tMemInfo->app_base_ptr == ((void *)0) ) {
					fprintf(stderr, "Client - ProcMp3Decode() DecodeAllocSlow() app_base_ptr info failed..\n");

				}
			}
		}
		tMp3Queue = socketClient.getMp3QueuePtr();
		chunkSize =	socketClient.getPlayInfoChunkSize();

		numSamples = (socketClient.getEncSampleRate() <= Common::DIV_SAMPLING_RATE) ? Common::MIN_SAMPLING_RATE : Common::MAX_SAMPLING_RATE;

		if( (this->pcmFrame = (MP3D_INT16 *)this->decodeAllocFast(numSamples * socketClient.getPlayInfoChannels()* sizeof(short))) == ((void *)0) ) {
			fprintf(stderr, "Client - procMp3Decode() pcmFrame failed : [%02d] %s\n", errno, strerror(errno));
		
		}
		fprintf(stderr, "Client - Decode MP3 info : pcm frame size [%d]\n", numSamples);

		if( (tRetVal = mp3d_decode_init(this->tDecConfig, 0, 0)) != MP3D_OK ) {
			fprintf(stderr, "Client - ProcMp3Decode() mp3d_decode_init failed : [%02d]\n", tRetVal);

		}

		while( !this->flagTerm ) {

			while( true ) {

				stackClient.bufferingQueue(tMp3Queue, 1);

				if( length + stackClient.getQueueSize(tMp3Queue) > inputSize ) {
					bufLen = inputSize - length;
					break;
				}
				memcpy(mp3Input + length, stackClient.getQueue(tMp3Queue), stackClient.getQueueSize(tMp3Queue));
				length += stackClient.getQueueSize(tMp3Queue);
				stackClient.incQueueIndex(tMp3Queue);
			}

			if( bufLen > 0 ) {
				memcpy(mp3Input + length, stackClient.getQueue(tMp3Queue), bufLen);
				length += bufLen;
			}

			this->tDecConfig->pInBuf         = (MP3D_INT8 *)mp3Input;
			this->tDecConfig->inBufLen       = length;
			this->tDecConfig->consumedBufLen = 0;

			inBufLen = leftInLength = length;
			while( tRetVal < MP3D_ERROR_INIT ) {
				if( this->flagTerm ) break;

				tRetVal = mp3d_decode_frame(this->tDecConfig, &tDecParams, (MP3D_INT32 *)this->pcmFrame);
				if( tRetVal == MP3D_OK ) {
					if( tDecParams.layer == Common::MP3_LAYER_3
							|| tDecParams.layer == Common::MP3_LAYER_2
							|| tDecParams.layer == Common::MP3_LAYER_1 ) {

						// SetQueue 처리
						stackClient.setQueueInStack((char *)this->pcmFrame);

					} else {
						fprintf(stderr, "Invalid Layer identified \n" );
						tRetVal = MP3D_ERROR_LAYER;
						break;
					}
				}

#if 0
				fprintf(stderr, "\n");
				fprintf(stderr, "err code   : %d\n", tRetVal);
				fprintf(stderr, "dec config : \n");
				fprintf(stderr, "  - inBufLen      : %d \n", this->tDecConfig->inBufLen);
				fprintf(stderr, "  - consumeBufLen : %d \n", this->tDecConfig->consumedBufLen);
				fprintf(stderr, "dec Params : \n");
				fprintf(stderr, "  - sampling_feq : %d \n", tDecParams.mp3d_sampling_freq);
				fprintf(stderr, "  - num_channel  : %d \n", tDecParams.mp3d_num_channels);
				fprintf(stderr, "  - frame_size   : %d \n", tDecParams.mp3d_frame_size);
				fprintf(stderr, "  - bitRate      : %d \n", tDecParams.mp3d_bit_rate);
				fprintf(stderr, "  - layer        : %d \n", tDecParams.layer);
				fprintf(stderr, "  - remain bytes : %d \n", tDecParams.mp3d_remain_bytes);
#endif

				if( tRetVal == MP3D_END_OF_STREAM ) {
					fprintf(stderr, "End of stream : %d\n", leftLength);
					break;
				}

				leftLength = this->tDecConfig->inBufLen - this->tDecConfig->consumedBufLen;
				if( leftLength < 0 ) {
					break;
				}

				if( leftLength > sizeThreshold ) {
					this->tDecConfig->pInBuf += this->tDecConfig->consumedBufLen;
					this->tDecConfig->inBufLen -= this->tDecConfig->consumedBufLen;
					accConsume += this->tDecConfig->consumedBufLen;


					stackClient.bufferingQueue(tMp3Queue, 1);
					if( bufLen != 0 ) {
						frameSize = bufLen;

					} else {
						frameSize = stackClient.getQueueSize(tMp3Queue);
					}

					if( accConsume > frameSize && (this->tDecConfig->inBufLen + frameSize) < inputSize ) {
						// fprintf(stderr, "loop! [%d > %d] && %d < %d \n", accConsume, frameSize, (this->tDecConfig->inBufLen + frameSize), inputSize);

						memcpy(mp3Input, mp3Input + accConsume, this->tDecConfig->inBufLen);

						if( bufLen > 0 ) {
							memcpy(mp3Input + this->tDecConfig->inBufLen, stackClient.getQueue(tMp3Queue) + bufLen, stackClient.getQueueSize(tMp3Queue) - bufLen);
						this->tDecConfig->inBufLen += bufLen;
							stackClient.incQueueIndex(tMp3Queue);
							bufLen = 0;
						}

						while( true ) {
							stackClient.bufferingQueue(tMp3Queue, 1);
							frameSize = stackClient.getQueueSize(tMp3Queue);

							if( this->tDecConfig->inBufLen + frameSize > inputSize ) {
								bufLen = inputSize - length;
								break;
							}

							memcpy(mp3Input + this->tDecConfig->inBufLen, stackClient.getQueue(tMp3Queue), frameSize);
							this->tDecConfig->inBufLen += frameSize;
							stackClient.incQueueIndex(tMp3Queue);
						}

						if( bufLen > 0 ) {
							memcpy(mp3Input + this->tDecConfig->inBufLen, stackClient.getQueue(tMp3Queue), bufLen);
							this->tDecConfig->inBufLen += bufLen;
						}

						this->tDecConfig->pInBuf = (MP3D_INT8 *)mp3Input;
						leftLength = this->tDecConfig->inBufLen;
						accConsume = 0;
					}

					this->tDecConfig->consumedBufLen = 0;

				} else {
					if( flagRoutine == false ) {
						fprintf(stderr, "Client - Incorrect routine... can not play..\n");
						flagRoutine = true;
					}
					memcpy(mp3Input, (this->tDecConfig->pInBuf + this->tDecConfig->consumedBufLen), leftLength);
					leftInLength = inputSize - leftLength;

					if( leftLength > length - inBufLen ) {
						leftInLength = length - inBufLen;
					}

					if( inBufLen < length ) {
						memcpy(mp3Input + leftLength, (this->tDecConfig->pInBuf + inBufLen), leftInLength);
					}

					inBufLen += leftInLength;
					this->tDecConfig->pInBuf   = (MP3D_INT8 *)mp3Input;
					this->tDecConfig->inBufLen = leftLength + leftInLength;

					if( this->tDecConfig->inBufLen < 0 ) {
						break;
					}
					this->tDecConfig->consumedBufLen = 0;
				}

			}

			length = 0;
		}

	//	pthread_exit(NULL);
	} // end of ProcMp3Decode()

	void *ClientMp3Dec::decodeAllocFast(int _size) {
		void *ptr = ((void *)0);

		ptr = malloc(_size + 4);
		ptr = (void *)(((long)ptr + (long)(4 - 1)) & (long)(~(4 - 1)));

		return ptr;
	} // end of DecodeAllocFast()

	void *ClientMp3Dec::decodeAllocSlow(int _size) {
		void *ptr = ((void *)0);

		ptr = malloc(_size);
		ptr = (void *)(((long)ptr + (long)4 - 1) & (long)(~(4 - 1)));

		return ptr;
	} // end of DecodeAllocSlow()

	void ClientMp3Dec::freeMp3Decode(void) {
		static char runFlag = false;
		int idx;

		if( runFlag == true ) return ;
		runFlag = true;

		fprintf(stderr, "Free MP3 decode parameter..\n");

		if( this->pcmFrame != NULL ) {
			fprintf(stderr, "  - pcm frame free..\n");
			free(this->pcmFrame);
			this->pcmFrame = NULL;
		}

		if( this->tDecConfig != NULL ) {
			for( idx = 0 ; idx < memInfoNumReqs ; idx++ ) {
				fprintf(stderr, "  - [%d] pcm num req ptr free..\n", idx);
				free(this->tDecConfig->mp3d_mem_info.mem_info_sub[idx].app_base_ptr);
			}

			fprintf(stderr, "  - decode config free..\n");
			free(this->tDecConfig);
			this->tDecConfig = NULL;
		}

		return ;
	} // end of FreeMp3Decode()

	void ClientMp3Dec::getMp3FrameInfo(char *_mp3Frame) {

		this->tMp3Header.version = (_mp3Frame[1] & 0x08) >> 3;
		this->tMp3Header.layer = 4 - ((_mp3Frame[1] & 0x06) >> 1);
		this->tMp3Header.errp = (_mp3Frame[1] & 0x01);

//		this->tMp3Header.bitrate = gBitRates[(_mp3Frame[2] & 0xf0) >> 4];
//		this->tMp3Header.freq = gSampleRates[(_mp3Frame[2] & 0x0c) >> 2];
		this->tMp3Header.pad = (_mp3Frame[2] & 0x02) >> 1;
		this->tMp3Header.priv = (_mp3Frame[2] & 0x01);

		this->tMp3Header.mode = (_mp3Frame[3] & 0xc0) >> 6;
		this->tMp3Header.modex = (_mp3Frame[3] & 0x30) >> 4;
		this->tMp3Header.copyright = (_mp3Frame[3] & 0x08) >> 3;
		this->tMp3Header.original = (_mp3Frame[3] & 0x04) >> 2;
		this->tMp3Header.emphasis = (_mp3Frame[3] & 0x03);

		fprintf(stderr, "MP3 Frame information\n");
		fprintf(stderr, "  version   = %x \n",this->tMp3Header.version);
		fprintf(stderr, "  layer     = %x \n",this->tMp3Header.layer);
		fprintf(stderr, "  errp      = %x \n",this->tMp3Header.errp);
		fprintf(stderr, "  bitrate   = %d \n",this->tMp3Header.bitrate);
		fprintf(stderr, "  freq      = %d \n",this->tMp3Header.freq);
		fprintf(stderr, "  pad       = %x \n",this->tMp3Header.pad);
		fprintf(stderr, "  priv      = %x \n",this->tMp3Header.priv);
		fprintf(stderr, "  mode      = %x \n",this->tMp3Header.mode);
		fprintf(stderr, "  modex     = %x \n",this->tMp3Header.modex);
		fprintf(stderr, "  copyright = %x \n",this->tMp3Header.copyright);
		fprintf(stderr, "  original  = %x \n",this->tMp3Header.original);
		fprintf(stderr, "  emphasis  = %x \n",this->tMp3Header.emphasis);

		return ;
	} // end of Getmp3FrameInfo()
}

