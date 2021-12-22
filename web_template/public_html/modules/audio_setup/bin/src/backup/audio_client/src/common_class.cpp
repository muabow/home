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
#include <net/if.h>
#include <sys/ioctl.h>
#include <sys/time.h>
#include <netinet/ether.h>

#include <iostream>
#include <thread>

#include "muteRelay.cpp"
#include "mp3_dec_interface.h"

extern	IPC_msgQueueFunc  gMsgQueueFunc;
extern 	bool gFlagStop;
extern	bool g_is_amp_device;

int		gMuteTime = 0;

namespace Common {
	using namespace std;

	/* const variables */
	const int TIME_SLEEP			= 1000; 		// usec
	const int TIME_BUFFER_WAIT    	= 10 * 1000;	// usec
	const int TIME_RCV_TIMEOUT 		= 2; 			// sec 
	const int TIME_TIMEOUT_SEC 		= 2;			// sec
	const int TIME_SET_MUTE_RELAY	= 100000;		// usec,	3000 -> 100000
	const int TIME_RECOVER_NULL		= 1000000;		// usec
	const int TIME_RECOVER_SCALE	= 10;			// sec

	const int FLAG_ODD				= 1;
	const int FLAG_EVEN				= 2;

	const int SIZE_PCM_PERIODS    	= 4;			// 4 -> 0
	const int SIZE_ALSA_BUFF_SCALE	= 16;			// 1024 -> 16384, x16
	const int SIZE_QUEUE_SCALE		= 5;
	const int SIZE_QUEUE_STACK		= 1024;
	const int SIZE_DEFAULT_VOLUME 	= 100;
 	const int SIZE_KBYTE          	= 1024;
 	const int SIZE_MBYTE          	= 1024 * 1024;

	const int BYTE_PCM_FRAME 		= 1;
	const int BYTE_MP3_FRAME 		= 2;
	const int SIZE_SCALE_RCVBUF 	= 10;

	const int FLAG_TCP 				= 1;
	const int FLAG_UDP 				= 0;
	const int FLAG_MODE_MP3 		= 1;
	const int FLAG_MODE_PCM 		= 0;
	const int FLAG_ENCODE_MP3 		= 1;

	const char *STR_DEVICE_NAME		= "plughw:0,0";	// default -> plughw:0,0
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
	const char *STR_ENCODE_PCM 		= "PCM";
	const char *STR_ENCODE_MP3 		= "MP3";

	const char *PATH_CONFIG_NETWORK	= "/opt/interm/public_html/modules/network_setup/conf/network_stat.json";
	const char *PATH_SAMPLE_RATE 	= "/tmp/sampleRate";

 	// MP3 paramters
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

	
	// Common functions
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

	double DiffTime(struct timeval _x, struct timeval _y) {
		double x_ms , y_ms , diff;

		x_ms = (double)_x.tv_sec * 1000000 + (double)_x.tv_usec;
		y_ms = (double)_y.tv_sec * 1000000 + (double)_y.tv_usec;

		diff = (double)y_ms - (double)x_ms;

		return diff;
	} // end of DiffTime()
	
	// DiffTime() example
	/*
		struct timeval before , after;
		gettimeofday(&before , NULL);
		gettimeofday(&after , NULL);
		printf("Total time elapsed : %.0lf us" , DiffTime(before, after));
	*/
	
	
	// Common class
	class CommonFunc {
		private :
			bool    flagTerm 	= false;
			bool    reconFlag 	= false;
		
		public  :
			// CommonFunc(void);
			void    handler(void);
			void    term(void);
			bool 	getFlagTerm(void);
			bool 	getReconnFlag(void);
			bool    setReconnFlag(void);
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
		int			 	type;       // TCP or UDP
		int 			cast;       // STREAM or DGRAM
		int				sockFd;     // socket descriptor for UDP
	} typedef CAST_INFO_t;

	struct CLIENT_PARAMS {
		int				serverCnt;
	} typedef CLIENT_PARAMS_t;

	struct CLIENT_INFO {
		int				delay;
		int				delayMs;
		int				playVolume;

		// server parameter
		int				typeProtocol;
		int				serverCnt;
		char			castType[24];
		char			ipAddr1[24];
		char			ipAddr2[24];
		int				port1;
		int    			port2;
		char			mIpAddr[24];
		int				mPort;

		int 			chunkSize;
		int 			sampleRate;
		int 			channels;
		int 			mp3_mode;
		int 			mp3_chunkSize;
		int 			mp3_bitRate;
		int				mp3_sampleRate;
		int 			ipStatus;

		char			hostName[128];
		char			deviceName[128];
	} typedef CLIENT_INFO_t;

	struct DELAY_INFO {
		int				mbScale;
		int				bufRate;
		int				bufRateMs;
		int				playVolume; 
	} typedef DELAY_INFO_t;

	struct ENCODE_INFO {
		int				chunkSize;
		short			bitRate;
		int				sampleRate;
	} typedef ENCODE_INFO_t;

	struct HOST_INFO {
		char 			hostName[128];
		char			macAddr[128];
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
		int  			port1;
		int  			port2;
		int  			mPort;
		int	 			serverCnt;
		int  			typeProtocol;
		char 			castType[24];
		char 			ipAddr1[24];
		char 			ipAddr2[24];
		char			mIpAddr[24];
	
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
		int				recvCnt;
		int				sendCnt;
		int				stackSize;
		char			**stack;
	} typedef QUEUE_STACK_t;

	struct SERVER_LIST {
		int				port;
		char 			ipAddr[128];
		char			stat;
	} typedef SERVER_LIST_t;

	struct VAR_FLAG {
		char			threadRecv;
		char			extOrder;
		char			term;
		char			alsaFrame;
		char			closeHandle;
		char			connFlag;
		char			encMode;
		char			playLoop;
	} typedef VAR_FLAG_t;

	struct QUEUE_INFO {
		int				queueSize;		// 해당 queue의 크기
		int				queuePos;		// 해당 queue의 위치
		char			queueFlag;		// 해당 queue의 상태 (end queue 표현 : flag 1)
	} typedef QUEUE_INFO_t;

	struct LINEAR_QUEUE {
		int				queueSize;      // 사용자 설정, 단일 queue 크기 (평균치로 사용)
		int     		queueScale;     // 사용자 설정, MB 단위 (ex. 1,2,3, ,,)
		int     		queueCount;     // queue 갯수
		int     		queuePos;       // 전체 queue의 위치
		int				setIndex;       // 현재 저장된 queue의 index
		int				getIndex;       // 현재 선택된 queue의 index
		int				linearSize;     // 전체 queue 크기
		int				bufferCount;    // 버퍼링된 queue의 갯수
		char			rewindFlag;     // queue 순환을 알리는 flag
		char			*combineQueue;  // 순회용 임시 queue
		char			*queueBody;     // 전체 queue
		QUEUE_INFO_t	*tQueueInfo;    // frame 에 대한 개별 정보(크기, 포인터 위치, 플래그 상태)

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
			int 	sockFd 		= -1;
			int 	ipStatus	= -1;
			bool 	flagTerm	= false;
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
			
			bool    getSelect(void);
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
			int 	setServerList(IP_INFO_t *_tIpInfo);
			int 	initSocket(void);
			int 	reconnSocket(void);
			bool 	initSocketOption(int _sockFd, char *_hostName);
			bool 	sendHostName(int _sockFd); 
			bool 	recvPlayInfo();
			bool 	getMacAddress(void);
			void 	execute();
			void 	closeSocket(void);
			void 	procRecvPcmData(void);
			void 	procRecvMp3Data(void);
			LINEAR_QUEUE_t *getMp3QueuePtr(void);
	}; // end of class : SocketClient
	SocketClient socketClient;

	
	class PcmHandle {
		private:
			bool 	flagTerm		= false;
			short 	*feedPcmData	= NULL;
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
			void 	setVolume(int _volume);	
			int 	setPcmHandle(void); 
			int 	setEncMode(int _type); 
			int 	getEncMode(void); 
			int 	getTerm(void); 
			int 	getLevelValue(void);
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
			void 	stop(void);	
			bool 	run(void);	
			char 	*getClientInfo(void);
			char 	*getVolumeInfo(void);
			char 	*getLevelValue(void);
	};

	class ClientMp3Dec {
		private:
			bool	flagTerm = false;
			int 	memInfoNumReqs;
			
			const int sampleRates[4] = {44100, 48000, 32000};
			const int bitRates[16]   = {0,  32000,  40000,  48000,  56000,  64000,  80000,  96000, 112000, 128000, 160000, 192000, 224000, 256000, 320000, 0};
		
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
		fprintf(stderr, "CommonFunc::term() called\n");
		this->handler();
		
		return ;
	}
	
	void CommonFunc::handler(void) {
		if( this->flagTerm ) {
			return ;
		}
		
		this->flagTerm = true;

		return ;
	}

	bool CommonFunc::getFlagTerm(void) {
		
		return this->flagTerm;
	}
	
	bool CommonFunc::getReconnFlag(void) {
		
		return this->reconFlag;
	}
	
	bool CommonFunc::setReconnFlag(void) {
		if( this->reconFlag ) {
			this->reconFlag = false;
		
		} else { 
			this->reconFlag = true;
		}
		
		return this->reconFlag;
	}

	void ClientFunc::init(CLIENT_INFO_t *_tClientInfo) {
		pcmHandle.initFlag();

		memcpy(&this->tClientInfo, _tClientInfo, sizeof(CLIENT_INFO_t));

		this->tDelayInfo.bufRate 		= this->tClientInfo.delay;
		this->tDelayInfo.bufRateMs 		= this->tClientInfo.delayMs;
		this->tDelayInfo.playVolume 	= this->tClientInfo.playVolume;
		this->tIpInfo.typeProtocol 		= this->tClientInfo.typeProtocol;
		this->tIpInfo.serverCnt 		= this->tClientInfo.serverCnt;
		this->tIpInfo.port1 			= this->tClientInfo.port1;
		this->tIpInfo.port2 			= this->tClientInfo.port2;
		this->tIpInfo.mPort 			= this->tClientInfo.mPort;

		// TODO, device name
		// strcpy(this->tClientInfo.deviceName, "default");
		// strcpy(this->tClientInfo.deviceName, Common::STR_DEVICE_NAME);
		strcpy(this->tHostInfo.hostName, 	 this->tClientInfo.hostName); 
		strcpy(this->deviceName,		 	 this->tClientInfo.deviceName);	
		strcpy(this->tIpInfo.castType,	 	 this->tClientInfo.castType);	
		strcpy(this->tIpInfo.ipAddr1, 		 this->tClientInfo.ipAddr1);	
		strcpy(this->tIpInfo.ipAddr2, 	 	 this->tClientInfo.ipAddr2);	
		strcpy(this->tIpInfo.mIpAddr,	 	 this->tClientInfo.mIpAddr);	

		return ;
	}

	bool ClientFunc::run(void) {
		gMsgQueueFunc.init();

		socketClient.setServerList(&tIpInfo); 
		pcmHandle.getIpList(&tIpInfo);
		
		fprintf(stderr, "ClientFunc::run() init Socket \n");
		if( (this->socketNo = socketClient.initSocket()) < 0 ) {
			fprintf(stderr,"ClientFunc::run() init Socket failed \n");
			
			return false;	
		}

		if( !socketClient.initSocketOption(this->socketNo, this->tHostInfo.hostName) ) {
			fprintf(stderr,"ClientFunc::run() init Socket option failed \n");
			
			return false;	
		}

		if( !socketClient.recvPlayInfo() ) {
			fprintf(stderr,"ClientFunc::run() recvPlayInfo() failed \n");
			
			return false;	
		}

		//socket thread
		socketClient.execute();

		//alsa, pcm setting
		pcmHandle.setFlag(&tDelayInfo, this->deviceName);

		if( pcmHandle.setPcmHandle() )	{
			if( pcmHandle.getEncMode() == Common::FLAG_MODE_MP3 )
				clientMp3Dec.execute();
			
			pcmHandle.execute();
			pcmHandle.printClientInfo();

		} else {
			fprintf(stderr,"ClientFunc::run() setPcmHandle() failed \n");
			socketClient.closeSocket();

			return false;	
		}
		return true;
	}

	int ClientFunc::setVolume(int _volume) {
		if( _volume < 0 || _volume > 100 ) {
			fprintf(stderr,"ClientFunc::setVolume() invalid value : [%d] \n", _volume);
			
			return false;
		}

		this->tClientInfo.playVolume = _volume;
		pcmHandle.setVolume(_volume);

		return true;
	}

	void ClientFunc::stop(void) {
		pcmHandle.pcmStop();
		stackClient.freeStack();
		
		common.term();
		
		return ;
	}

	//json send level
	char *ClientFunc::getLevelValue() {
		int levelStat = pcmHandle.getLevelValue();
		
		memset(this->level, 0x00, sizeof(this->level));
		sprintf(this->level, "{\"level\": \"%d\"}",levelStat);

		return this->level;
	}

	//json send volume
	char *ClientFunc::getVolumeInfo() {
		memset(this->volume, 0x00, sizeof(this->volume));
		if( this->tClientInfo.chunkSize == 0 ) {
			sprintf(this->volume, "{\"type\": \"audio_client\", \"playVolume\": \"%d\"}", -1);

		} else {
			sprintf(this->volume, "{\"type\": \"audio_client\", \"playVolume\": \"%d\"}", this->tClientInfo.playVolume);
		}

		return this->volume;
	}

	//json send clientInfo
	char *ClientFunc::getClientInfo() {

		this->tClientInfo.chunkSize  = socketClient.getPlayInfoChunkSize();
		this->tClientInfo.sampleRate = socketClient.getPlayInfoRate();
		this->tClientInfo.channels   = socketClient.getPlayInfoChannels();
		this->tClientInfo.ipStatus   = socketClient.getIpStatus();

		this->tClientInfo.mp3_mode 		 = socketClient.getPlayInfoEncode();
		this->tClientInfo.mp3_chunkSize  = socketClient.getPlayInfoChunkSize();
		this->tClientInfo.mp3_bitRate 	 = socketClient.getEncRate();
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
		
		return ;
	}

	void PcmHandle::setVolume(int _volume) {
		this->tDelayInfo.playVolume = _volume;
		
		return ;
	}

	QueueStack::QueueStack(void) {

		return ;
	}

	QueueStack::~QueueStack(void) {
		// this->freeStack();

		return ;
	}

	void QueueStack::init(int _idx, int _bufferRate, int _chunkSize) {
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
			fprintf(stderr, "QueueStack::init() malloc() stack[] failed : [%02d]\n", errno);

			return ;
		}

		for( int idx = 0 ; idx < this->tQueueStack.stackSize ; idx++ ) {
			if( (this->tQueueStack.stack[idx] = (char *)malloc(this->chunkSize * sizeof(char))) == NULL ) {
				fprintf(stderr, "QueueStack::init() malloc() stack[][] failed : [%02d]\n", errno);

				return ;
			}
		}
		
		return ;
	}

	void QueueStack::freeStack(void) {
		if( this->tQueueStack.stack != NULL ) {
			fprintf(stderr, "QueueStack::freeStack() [%d] stack is freed\n", this->idx);
			
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
		int	chVal = this->tPlayInfo.channels == 1 ? 2 : 1;
		
		if(this->tVarFlag.encMode == Common::FLAG_MODE_PCM) {
			sec = this->tDelayInfo.bufRate + ((double)this->tDelayInfo.bufRateMs / 1000) + 0.1; // 0.3
		
		} else {
			sec = this->tDelayInfo.bufRate + ((double)this->tDelayInfo.bufRateMs / 1000) + 0.8; // 0.8
			chVal *= 2;
		}
		
		// return (this->tPlayInfo.rate * this->tPlayInfo.channels * sec) / (this->tPlayInfo.chunkSize / 2);
		return (this->tPlayInfo.rate * this->tPlayInfo.channels * sec) / (this->tPlayInfo.chunkSize / this->tPlayInfo.channels / chVal);
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
		stackClient.init(0, 0, this->tPlayInfo.chunkSize);
		
		return ;
	} 

	void PcmHandle::setPlayInfo() {
		if( socketClient.getPlayInfoEncode() == Common::FLAG_MODE_PCM ) {
			this->tPlayInfo.rate 			= socketClient.getPlayInfoRate();
			this->tPlayInfo.chunkSize 		= socketClient.getPlayInfoChunkSize();
		
		} else {
			this->tPlayInfo.rate 			= socketClient.getEncSampleRate(); 
			this->tPlayInfo.chunkSize 		= socketClient.getEncChunkSize();

		}

		this->tPlayInfo.channels 		= socketClient.getPlayInfoChannels();
		this->tPlayInfo.pcmBufferSize 	= socketClient.getPlayInfoPcmBuf();
		this->tPlayInfo.pcmPeriodSize 	= socketClient.getPlayInfoPcmPer();
		this->tPlayInfo.idx 			= 0;
		
		return ;
	}

	int PcmHandle::setPcmHandle(void) {
		int		err;
		int 			periods	 	= Common::SIZE_PCM_PERIODS;
		unsigned int 	rate 		= this->tPlayInfo.rate;
		unsigned int	channels 	= this->tPlayInfo.channels;

		snd_pcm_hw_params_t	*tPlaybackParams;
		snd_pcm_sw_params_t *tSwParams;
	
		snd_pcm_hw_params_alloca(&tPlaybackParams);
		snd_pcm_sw_params_alloca(&tSwParams);

		snd_pcm_uframes_t buffer_size; 

		snd_pcm_format_t format = SND_PCM_FORMAT_S16_LE;
		snd_pcm_stream_t stream = SND_PCM_STREAM_PLAYBACK;
		
		if( this->tPlaybackHandle != NULL ) {
			fprintf(stderr, "PcmHandle::setPcmHandle() alsa handler resetting..\n");
			
			snd_pcm_drain(this->tPlaybackHandle);
			snd_pcm_drop(this->tPlaybackHandle);
			snd_pcm_close(this->tPlaybackHandle);
		
			this->tPlaybackHandle = NULL;
		}

		if( (err = snd_pcm_open(&this->tPlaybackHandle, this->deviceName, stream, SND_PCM_NONBLOCK)) < 0 ) {
			fprintf(stderr, "ALSA - Cannot open audio device %s (%s)\n",this->deviceName, snd_strerror (err));

			return false;
		}
		fprintf(stderr, "ALSA - open audio device : %s \n",this->deviceName);

		if( (err = snd_pcm_hw_params_any(this->tPlaybackHandle, tPlaybackParams)) < 0 ) {
			fprintf(stderr, "ALSA - Cannot initialize hardware parameter structure : %s\n", snd_strerror(err));

			return false;
		}

		if( (err = snd_pcm_hw_params_set_access(this->tPlaybackHandle, tPlaybackParams, SND_PCM_ACCESS_RW_INTERLEAVED)) < 0 ) {
			fprintf(stderr, "ALSA - Cannot set access type : %s\n", snd_strerror(err));

			return false;
		}

		if( (err = snd_pcm_hw_params_set_format(this->tPlaybackHandle, tPlaybackParams, format)) < 0 ) {
			fprintf(stderr, "ALSA - Cannot set sample format : %s\n", snd_strerror(err));

			return false;
		}
		if( (err = snd_pcm_hw_params_set_channels(this->tPlaybackHandle, tPlaybackParams, channels) ) < 0 ) {
			fprintf(stderr, "ALSA - Cannot set channel count : %s\n", snd_strerror(err));

			return false;
		}

		if( (err = snd_pcm_hw_params_set_rate_near(this->tPlaybackHandle, tPlaybackParams, &rate, 0)) < 0 ) {
			fprintf(stderr, "ALSA - Cannot set near sample rate : %s\n", snd_strerror(err));

			return false;
		}

		if( (err = snd_pcm_hw_params_set_periods(this->tPlaybackHandle, tPlaybackParams, periods, 0)) < 0) {
			fprintf(stderr, "ALSA - error setting periods: %s\n", snd_strerror(err));

			return false;
		}
		
		static snd_pcm_uframes_t period_frames = 0;
		period_frames = this->tPlayInfo.chunkSize;
		snd_pcm_hw_params_set_period_size_near(this->tPlaybackHandle, tPlaybackParams, &period_frames, 0); 

		snd_pcm_uframes_t exact_buffersize;
		snd_pcm_uframes_t bufferSize;
		// bufferSize = (period_frames  * periods) / 4;
		bufferSize = Common::SIZE_ALSA_BUFF_SCALE * period_frames;
		exact_buffersize = bufferSize;
		
		fprintf(stderr, "ALSA - period_frames     : %d\n", (int)period_frames);
		fprintf(stderr, "ALSA - bufferSize        : %d\n", (int)bufferSize);
		fprintf(stderr, "ALSA - exact_buffersize  : %d\n", (int)exact_buffersize);
		
		
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

		snd_pcm_hw_params_get_buffer_size(tPlaybackParams, &buffer_size);
		snd_pcm_sw_params_current(this->tPlaybackHandle, tSwParams);
		
		// TODO,
		/*
		if( (err = snd_pcm_sw_params_set_avail_min(this->tPlaybackHandle, tSwParams, this->tPlayInfo.chunkSize / 2)) < 0 ) {
			fprintf(stderr, "ALSA - Unable to set avail min for playback : %s\n", snd_strerror(err));

			return false;
		}
		snd_pcm_sw_params_set_start_threshold(this->tPlaybackHandle, tSwParams, buffer_size);
		snd_pcm_sw_params_set_stop_threshold(this->tPlaybackHandle, tSwParams, buffer_size); 
		*/
		

		if( (err = snd_pcm_sw_params(this->tPlaybackHandle, tSwParams)) < 0 ) {
			fprintf(stderr, "ALSA - Unable to set sw params for playback : %s\n", snd_strerror(err));

			return false;
		}

		if( (err = snd_pcm_prepare(this->tPlaybackHandle)) < 0 ) {
			fprintf(stderr, "ALSA - Cannot prepare audio interface for use : %s\n", snd_strerror(err));

			return false;
		}

		//처리해야할 flag
		this->tVarFlag.alsaFrame = true;

		fprintf(stderr, "PcmHandle::setPcmHandle() pcm handler init success..\n");

		this->tVarFlag.closeHandle 	= false;

		fprintf(stderr, "ALSA - applied buffer   : %d\n", (int)buffer_size);

		return true;
	} // end of setPcmHandle()


	void PcmHandle::pcmPlayLoop(void) {
		int 	err, idx;
		int 	frameBytes;
		int		chunkSize;
		int 	frameSize;
		int 	encFrame = Common::BYTE_MP3_FRAME;
		short	*feedNullData = NULL;
		
		// time check
		bool	resetTimeFlag = false;
		int		nullCnt = 0;
		int		bufferCnt;
		int		muteTime;
		int		nullTime;
		int		recoverTime;
		struct	timeval muteBeginTime, muteEndTime;
		struct	timeval nullBeginTime, nullEndTime;
		
		
		this->tVarFlag.threadRecv = false;

		snd_pcm_sframes_t tPcmAvail;

		chunkSize   = this->tPlayInfo.chunkSize;
		frameBytes  = snd_pcm_frames_to_bytes(this->tPlaybackHandle, 1);

		recoverTime = Common::TIME_RECOVER_NULL * Common::TIME_RECOVER_SCALE;
		bufferCnt   = this->getBufferChunk() / 2;
		
		fprintf(stderr, "PcmHandle::pcmPlayLoop() recover time : [%02d] \n", recoverTime);
		fprintf(stderr, "PcmHandle::pcmPlayLoop() buffer count : [%02d] \n", bufferCnt);
		if( this->tVarFlag.encMode == Common::FLAG_MODE_PCM ) {
			frameSize  = chunkSize / frameBytes;
		
		} else {
			frameSize  = chunkSize / frameBytes / encFrame;
		}
		
		if( (feedNullData = (short *)malloc(chunkSize * sizeof(char))) == NULL ) {
			fprintf(stderr, "PcmHandle::pcmPlayLoop() malloc() failed : [%02d] %s\n", errno, strerror(errno));
		}
		bzero(feedNullData, chunkSize);
		
		while( stackClient.getStoreCount() <= this->getBufferChunk() ) {
			if( this->flagTerm ) break;
			usleep(Common::TIME_SLEEP);
		}
		
		gettimeofday(&nullBeginTime, NULL);
							
		while( !this->flagTerm ) {
			if( this->tVarFlag.closeHandle ) {
				if( !this->setPcmHandle() ) {
					this->flagTerm = true;
					
				} else {
					this->tVarFlag.closeHandle = false;
				}
			}

			// PcmHandle() 호출로 frame size 변경 시 frame size 갱신 용도
			if( this->tVarFlag.alsaFrame ) {
				frameBytes = snd_pcm_frames_to_bytes(this->tPlaybackHandle, 1);

				this->tVarFlag.alsaFrame = false;
			}
			
			if( stackClient.getStoreCount() > 0 ) {
				this->feedPcmData = (short *)stackClient.getQueueInStack();
				stackClient.incSendCnt();
				
			} else {
				this->feedPcmData = feedNullData;
				nullCnt++;
				
				// fprintf(stderr, "+ store count/null feed : [%d / %d]\n", stackClient.getStoreCount(), nullCnt);
			}
			
			// change volume
			if( this->tDelayInfo.playVolume != Common::SIZE_DEFAULT_VOLUME && !g_is_amp_device ) {
				for( idx = 0 ; idx < chunkSize / 2 ; idx++ ) {
					this->feedPcmData[idx] = (int32_t)this->feedPcmData[idx] * this->tDelayInfo.playVolume / 100;
				}
			}
			
			// amp device
			
			if( (tPcmAvail = snd_pcm_avail(this->tPlaybackHandle)) == -EPIPE ) {
				snd_pcm_prepare(this->tPlaybackHandle);
				fprintf(stderr, "PcmCapture::execute() snd_pcm_prepare() prepared\n");
				
				snd_pcm_wait(this->tPlaybackHandle, -1);
				fprintf(stderr, "PcmCapture::execute() snd_pcm_wait() waited\n");
			}
				
			// feed audio frame
			if( (err = snd_pcm_writei(this->tPlaybackHandle, this->feedPcmData, frameSize)) < 0 ) {
				fprintf(stderr, "ALSA - [pcm] write to audio interface failed : [%d/%d] [%02d] %s\n",
						frameSize, (int)tPcmAvail, err, snd_strerror(err));

				this->setPcmHandle();
				nullCnt = 0;
			}
			
			gettimeofday(&nullEndTime, NULL);
			nullTime = (int)DiffTime(nullBeginTime, nullEndTime);
			
			// null feed recovery
			if( nullTime >= recoverTime ) {
				if( stackClient.getStoreCount() > bufferCnt && nullCnt > 0 ) {
					stackClient.incSendCnt();
					nullCnt--;
					
					// fprintf(stderr, "- store count/null feed : [%d / %d]\n", stackClient.getStoreCount(), nullCnt);
				}
				
				memset(&nullBeginTime, 0x00, sizeof(nullBeginTime));
				gettimeofday(&nullBeginTime, NULL);
			}
			
			// control mute relay
			if( !gMsgQueueFunc.isMute() ) {
				if( !resetTimeFlag ) {
					memset(&muteBeginTime, 0x00, sizeof(muteBeginTime));
					gettimeofday(&muteBeginTime, NULL);
					resetTimeFlag = true;
				}
				
				gettimeofday(&muteEndTime, NULL);
				muteTime = (int)DiffTime(muteBeginTime, muteEndTime);
				
				if( muteTime >= Common::TIME_SET_MUTE_RELAY && !common.getFlagTerm() ) {
					if( gFlagStop ) {
						fprintf(stderr, "PcmCapture::execute() unmute, time elapsed : %d us \n" , muteTime);
						gMuteTime = muteTime;
						memset(&muteEndTime, 0x00, sizeof(muteEndTime));

						gMsgQueueFunc.incCntAudioMute();

						nullCnt = 0;
					}
				}
				
			} else {
				resetTimeFlag = false;
			}
		} // end of while()

		while( true ) {
			if( this->flagTerm ) break;

			usleep(Common::TIME_SLEEP);
		}
		
		if( this->tPlaybackHandle != NULL ) {
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
		if( !this->setPcmHandle() ) { 
			this->flagTerm = true;
		
		} else {
			this->tVarFlag.closeHandle = false;
		}

		return true;
	} 

	int PcmHandle::getLevelValue(void) {
		int     volVal, rc, dcVal;
		int     valLevel = 10;

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

		this->pcmFunc = thread(&PcmHandle::pcmPlayLoop, this);
		this->pcmFunc.detach();
	}

	//mp3 decoder thread
	void ClientMp3Dec::execute() {
		this->flagTerm = false;
		fprintf(stderr, "Client - ProcMp3Decode() thread called... \n");

		threadMp3Dec = thread(&ClientMp3Dec::procMp3Decode, this);
		threadMp3Dec.detach();
	}


	void SocketClient::execute() {
		this->flagTerm = false;

		if( pcmHandle.getEncMode() == Common::FLAG_MODE_MP3 ) {
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
		fprintf(stderr,"Create SocketClient instance\n");
		
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
		
		tServerList = (SERVER_LIST_t *)malloc(sizeof(SERVER_LIST_t) * this->tClientParams.serverCnt);

		if( strcmp(_tIpInfo->castType, "unicast") == 0 ) {
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
	

	int SocketClient::initSocket(void) {
		bool	isConn = false;
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
			fprintf(stderr, "SocketClient::initsocket() cast type : unicast \n");
			
			while( true ) {
				if( (serverSockFd = socket(AF_INET, this->tCastInfo.cast, 0)) < 0 ) {
					fprintf(stderr, "SocketClient::initsocket() failed : [%02d] %s\n", errno, strerror(errno));
					return false;
				}
				
				// server 순회
				for( idx = 0 ; idx < this->tClientParams.serverCnt ; idx++ ) {
					if( this->tServerList[idx].stat == true ) break;
				}

				if( idx == this->tClientParams.serverCnt ) {
					fprintf(stderr, "SocketClient::initsocket() not found server \n");
					serverSockFd = -1;
					
					break;
				}
				
				strcpy(serverIpAddr, this->tServerList[idx].ipAddr);
				serverPort = this->tServerList[idx].port;

				this->ipStatus = idx;

				serverAddr.sin_family = AF_INET;
				serverAddr.sin_addr.s_addr = inet_addr(serverIpAddr);
				serverAddr.sin_port = htons(serverPort);
				

				if( getsockopt(serverSockFd, SOL_SOCKET, SO_RCVBUF, &recvBufSize, &len) < 0 ) {
					fprintf(stderr, "SocketClient::initsocket() getsockopt() SO_RCVBUF failed : [%02d] %s\n", errno, strerror(errno));
				}

				optLen = recvBufSize * Common::SIZE_SCALE_RCVBUF;
				if( setsockopt(serverSockFd, SOL_SOCKET, SO_RCVBUF, (char*)&optLen, sizeof(optLen)) < 0 ) {
					fprintf(stderr, "SocketClient::initsocket() setsockopt() SO_RCVBUF failed : [%02d] %s\n", errno, strerror(errno));
				}

				optval.l_onoff  = 1;
				optval.l_linger = 0;

				if( setsockopt(serverSockFd, SOL_SOCKET, SO_LINGER, &optval, sizeof(optval)) < 0 ) {
					fprintf(stderr, "SocketClient::initsocket() setsockopt() SO_LINGER failed : [%02d] %s\n", errno, strerror(errno));
				}
				
				fcntlFlag = fcntl(serverSockFd, F_GETFL, 0);
				nonblockFlag = fcntlFlag | O_NONBLOCK;

				if( (rc = fcntl(serverSockFd, F_SETFL, nonblockFlag)) < 0 ) {
					fprintf(stderr,"SocketClient::initsocket() fcntl() set socket nonblock failed : [%02d] %s\n", errno, strerror(errno));
				}
				
				FD_ZERO(&fdset); 
				FD_SET(serverSockFd, &fdset); 
				fdwset = fdset; 

				timeout.tv_sec  = 1;
				timeout.tv_usec = 0;

				connect(serverSockFd, (struct sockaddr *)&serverAddr, sizeof(serverAddr));
				
				if( (rc = select(serverSockFd + 1, &fdset, &fdwset, NULL, &timeout)) < 0 ) {
					switch( errno ) {
						case 4 :
							break;
							
						default :
							fprintf(stderr, "SocketClient::initsocket() select() failed : [%02d] %s\n", errno, strerror(errno));
							break;
					}
				}

				getsockopt(serverSockFd, SOL_SOCKET, SO_ERROR, (void*) &connectError, &len);
				
				if ( (FD_ISSET(serverSockFd, &fdwset ) > 0 ) && (connectError == 0) ) { 
					for( int statIdx = 0 ; statIdx < this->tClientParams.serverCnt ; statIdx++ ) {
						this->tServerList[statIdx].stat = true;
					}
					
					fprintf(stderr, "SocketClient::initsocket() Server info[%s/%d] connect success\n",
							this->tServerList[idx].ipAddr, this->tServerList[idx].port);
							
					isConn = true;
					
				} else {
					fprintf(stderr, "SocketClient::initsocket() Server info[%s/%d] connect failed : [%02d] %s\n",
							this->tServerList[idx].ipAddr, this->tServerList[idx].port, 
							errno, strerror(errno));
					
					this->tServerList[idx].stat = false;
					
					isConn = false;
				}
			
				if( (rc = fcntl(serverSockFd, F_SETFL, fcntlFlag)) < 0 ) {
					fprintf(stderr, "SocketClient::initsocket() unset socket nonblock failed : [%02d] %s\n", errno, strerror(errno));
				}
				
				if( isConn ) {
					break;
				
				} else {
					close(serverSockFd);
				}
			}
			
		} else {  /* multi-cast type */
			fprintf(stderr, "SocketClient::initsocket() cast type : multicast \n");
			
			// set server ipaddr
			strcpy(serverIpAddr, this->tServerList[0].ipAddr);
			serverPort = this->tServerList[0].port;
			mCastAddr.sin_addr.s_addr = inet_addr(serverIpAddr) ; 

			if( (serverSockFd = socket(AF_INET, this->tCastInfo.cast, 0)) < 0 ) {
				fprintf(stderr, "SocketClient::initsocket() failed : [%02d] %s\n", errno, strerror(errno));
			
				return false;
			}

			serverAddr.sin_family = AF_INET;
			serverAddr.sin_addr.s_addr = htonl(INADDR_ANY);
			serverAddr.sin_port = htons(serverPort);

			this->ipStatus = 2;

			// multicast ip address check
			if( !IN_MULTICAST(ntohl(mCastAddr.sin_addr.s_addr)) ) {
				fprintf(stderr, "Given address [%s] is not multicast...\n", inet_ntoa(mCastAddr.sin_addr));
				return false;
			}

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

	
	bool SocketClient::initSocketOption(int _sockFd, char *_hostName) {
		bool rc		 = true;
		int	 optval  = true;

		struct  timeval tTimeo = {Common::TIME_RCV_TIMEOUT, 0};
		struct  linger  tLinger;

		this->sockFd = _sockFd;

		tLinger.l_onoff  = true;
		tLinger.l_linger = 0;

		if( this->tCastInfo.type == Common::FLAG_TCP ) {
			if( setsockopt(this->sockFd, SOL_SOCKET, SO_LINGER, (char *)&tLinger, sizeof(tLinger)) < 0 ) {
				fprintf(stderr, "setsockopt() SO_LINGER failed : [%02d] %s\n", errno, strerror(errno));
			}
	
			if( setsockopt(this->sockFd, IPPROTO_TCP, TCP_NODELAY, &optval, sizeof(optval)) < 0 ) {
				fprintf(stderr, "setsockopt() SO_NODELAY failed : [%02d] %s\n", errno, strerror(errno));
			}
	
			if( setsockopt(this->sockFd, SOL_SOCKET, SO_RCVTIMEO, &tTimeo, sizeof(tTimeo) ) < 0 ) {
				fprintf(stderr, "setsockopt() SO_RCVTIMEO failed : [%02d] %s\n", errno, strerror(errno));
			}
			
			strcpy(this->tHostInfo.hostName, _hostName);
			
			fprintf(stderr, "SocketClient::initSocketOption() Send client hostname..\n");
			rc = this->sendHostName(this->sockFd);
		
		} else {
			// multicast return true
		}

		return rc;
	}

		
	bool SocketClient::getMacAddress(void) {
		int nSD; // Socket descriptor
		struct ifreq *ifr; // Interface request
		struct ifconf ifc;
		int idx, numIf;

		memset(this->macAddr, 0x00, sizeof(this->macAddr));

		memset(&ifc, 0, sizeof(ifc));
		ifc.ifc_ifcu.ifcu_req = NULL;
		ifc.ifc_len = 0;

		// Create a socket that we can use for all of our ioctls
		if( (nSD = socket( PF_INET, SOCK_DGRAM, 0 )) < 0 ) {
			return false;
		}

		if( ioctl(nSD, SIOCGIFCONF, &ifc) < 0 ) {
			return false;
		}

		if ((ifr = (ifreq*)  malloc(ifc.ifc_len)) == NULL) {
			close(nSD);
			free(ifr);

			return false;
		
		} else {
			ifc.ifc_ifcu.ifcu_req = ifr;
			if( ioctl(nSD, SIOCGIFCONF, &ifc) < 0 ) {
				close(nSD);
				free(ifr);

				return false;
			}

			numIf = ifc.ifc_len / sizeof(struct ifreq);
			for (idx = 0; idx < numIf; idx++) {
				struct ifreq *r = &ifr[idx];

				if( !strcmp(r->ifr_name, "lo") ) {
					continue; // skip loopback interface
				}

				if( ioctl(nSD, SIOCGIFHWADDR, r) < 0 ) {
					break;
				}

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

	bool SocketClient::sendHostName(int _sockFd) {
		char    hostName[128];

		static int macFlag = false;

		memset(&hostName, 0x00, sizeof(hostName));

		// Get MAC address
		if( macFlag == false ) {
			if( !this->getMacAddress() ) {
				fprintf(stderr, "SocketClient::sendHostName() get mac address failed..\n");

				return false;

			} else {
				strcpy(this->tHostInfo.macAddr, this->macAddr);
				fprintf(stderr, "SocketClient::sendHostName() MAC : [%s]\n", this->tHostInfo.macAddr);
				macFlag = true;

			}
		}
		
		if( send(this->sockFd, &this->tHostInfo, sizeof(this->tHostInfo), 0) < 0 ) {
			fprintf(stderr, "SocketClient::sendHostName() send hostname failed : [%02d] %s\n", errno, strerror(errno));

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
				fprintf(stderr, "SocketClient::recvPlayInfo() recv() play information failed : [%02d] %s\n", errno, strerror(errno));

				return false;

			} else if( rc == 0 ) {
				fprintf(stderr, "SocketClient::recvPlayInfo() recv() play information connection reset by peer..\n");

				return false;
			}

			if( rc != sizeof(this->tPlayInfo) ) {
				fprintf(stderr, "SocketClient::recvPlayInfo() recv() play information invalid length..\n");

				return false;
			}

			if( this->tPlayInfo.rate == 0 && this->tPlayInfo.channels == 0 ) {
				fprintf(stderr, "SocketClient::recvPlayInfo() recv() play information invalid data..\n");

				return false;
			}

			// encode mode check
			if( this->tPlayInfo.encodeMode == Common::FLAG_MODE_MP3 ) {
				pcmHandle.setEncMode(Common::FLAG_MODE_MP3);

				if( (rc = recv(this->sockFd, &this->tEncInfo, sizeof(this->tEncInfo), MSG_WAITALL)) < 0 ) {
					fprintf(stderr, "SocketClient::recvPlayInfo() recv() encode information failed : [%02d] %s\n", 
							errno, strerror(errno));

					return false;

				} else if( rc == 0 ) {
					fprintf(stderr, "SocketClient::recvPlayInfo() recv() encode information connection reset by peer..\n");

					return false;
				}

				if( rc != sizeof(this->tEncInfo) ) {
					fprintf(stderr, "SocketClient::recvPlayInfo() recv() play encode information invalid length..\n");

					return false;
				}

				this->tPlayInfo.chunkSize = this->tEncInfo.chunkSize;
	
			} else {
				pcmHandle.setEncMode(Common::FLAG_MODE_PCM);
			}

		} else {
			fprintf(stderr, "Multicast - Play information receive from multicast channel...\n");
			
			if( !this->getSelect() ) {
				return false;
			}
			
			if( read(this->sockFd, &tHeaderInfo, sizeof(tHeaderInfo)) < 0 ) {
				fprintf(stderr, "SocketClient::recvPlayInfo() read() headerInfo failed : [%02d] %s\n", errno, strerror(errno));

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
			
			if( !this->getSelect() ) {
				return false;
			}
			
			bodyData = (char *)malloc(tHeaderInfo.chunkSize * sizeof(char));
			if( read(this->sockFd, bodyData, tHeaderInfo.chunkSize) < 0 ) {
				fprintf(stderr, "SocketClient::recvPlayInfo() read() body data failed : [%02d] %s\n", errno, strerror(errno));

				free(bodyData);
				return false;

			}
			free(bodyData);
		}
		
		if( (fp = fopen(Common::PATH_SAMPLE_RATE, "w")) == NULL ) {
			fprintf(stderr, "SocketClient::recvPlayInfo() open file [%s] failed : [%02d] %s\n",
					Common::PATH_SAMPLE_RATE, errno, strerror(errno));
			return false;

		} else {
			sprintf(sampleRate, "%d Hz", this->tPlayInfo.rate);
			fwrite(sampleRate, sizeof(char), strlen(sampleRate), fp);
			fclose(fp);
		}

		return true;
	} // end of RecvPlayInfo()


	// check date. 180813
	void SocketClient::procRecvPcmData(void) {
		int     rc = 0;
		char	*recvBodyData = NULL;

		HEADER_PCM_INFO_t   tHeaderInfo;

		if( (recvBodyData = (char *)malloc(this->tPlayInfo.chunkSize * sizeof(char))) == NULL ) {
			fprintf(stderr, "SocketClient::procRecvPcmData() PCM malloc body data failed : [%02d] %s\n", errno, strerror(errno));
			
			pcmHandle.setThreadRecv(true);
			common.term();
			
			return ;
		}

		if( this->tCastInfo.type == Common::FLAG_UDP ) {
			/* UDP(multi-cast) part */
			memset(&tHeaderInfo, 0x00, sizeof(tHeaderInfo));
			
			while( !this->flagTerm ) {
				if( !this->getSelect() ) {
					break;
				}
				
				if( (rc = read(this->sockFd, &tHeaderInfo, sizeof(tHeaderInfo))) < 0 ) {
					fprintf(stderr, "SocketClient::procRecvPcmData() Recv head information failed : [%02d] %s\n", errno, strerror(errno));

					break;
				}
				if( rc != sizeof(tHeaderInfo) ) {
					fprintf(stderr, "SocketClient::procRecvPcmData() Recv head information invalid length : [%d/%d]\n",
							rc, (int)sizeof(tHeaderInfo));
					break;
				}
				
				if( !this->getSelect() ) {
					break;
				}
				
				if( (rc = read(this->sockFd, recvBodyData, this->tPlayInfo.chunkSize)) < 0 ) {
					fprintf(stderr, "SocketClient::procRecvPcmData() Recv body data failed : [%02d] %s\n", errno, strerror(errno));
					break;
				}

				stackClient.setQueueInStack(recvBodyData);
			}
			
			gMsgQueueFunc.decCntAudioMute();
							
		} else {
			/* TCP(uni-cast) part */
			while( !this->flagTerm ) {
				while( !this->flagTerm ) {
					if( !this->getSelect() ) {
						break;
					}
	
					if( (rc = recv(this->sockFd, recvBodyData, this->tPlayInfo.chunkSize, MSG_WAITALL)) <= 0 ) {
						if( errno == EAGAIN ) {
							fprintf(stderr, "SocketClient::procRecvPcmData() body not received..\n");
	
						} else {
							fprintf(stderr, "SocketClient::procRecvPcmData() not received..\n");
						}
						break;
					}
	
					if( rc != this->tPlayInfo.chunkSize ) {
						fprintf(stderr, "SocketClient::procRecvPcmData() Recv body information invalid length : [%d/%d]\n",
								rc, this->tPlayInfo.chunkSize);
						break;
					}
	
					stackClient.setQueueInStack(recvBodyData);
				}
				
				gMsgQueueFunc.decCntAudioMute();
				
				fprintf(stderr, "SocketClient::procRecvPcmData() - Disconnected by server.. \n");
				fprintf(stderr, "                                  Find next server.. \n");
				
				this->tServerList[this->ipStatus].stat = false;
				
				if( this->reconnSocket() ) {
					pcmHandle.printClientInfo();
		
					this->flagTerm = false;
					common.setReconnFlag();
				
				} else {
					break;
				}
			} // end of while() : reconnect
		}
		

		if( recvBodyData != NULL ) {
			free(recvBodyData);
			recvBodyData = NULL;
		}

		pcmHandle.setThreadRecv(true);
		
		common.term();
	} // end of ProcRecvPcmData()

	
	// check date. 180813
	void SocketClient::procRecvMp3Data(void) {
		int     rc = 0;

		char    recvHeadMsg[128];
		char	*recvBodyData = NULL;

		HEADER_MP3_INFO_t   tHeaderInfo;
 		HEADER_MP3_CHUNK_t  tChunkInfo;

		if( this->tCastInfo.type == Common::FLAG_UDP ) {
			/* UDP(multi-cast) part */
			while( !this->flagTerm ) {
				if( !this->getSelect() ) {
					break;
				}
				
				if( (rc = read(this->sockFd, recvHeadMsg, sizeof(recvHeadMsg))) < 0 ) {
					fprintf(stderr, "SocketClient::procRecvMp3Data() recv head information failed : [%02d] %s\n", errno, strerror(errno));

					break;
				}

				if( rc != sizeof(tHeaderInfo) ) {
					fprintf(stderr, "SocketClient::procRecvMp3Data() recv head information invalid length : [%d/%d]\n", rc, sizeof(tHeaderInfo));

					break;
				}

				memcpy(&tHeaderInfo, recvHeadMsg, sizeof(tHeaderInfo));
				if( recvBodyData != NULL ) {
					free(recvBodyData);
					recvBodyData = NULL;
				}
				
				if( (recvBodyData = (char *)malloc(tHeaderInfo.chunkSize * sizeof(char))) == NULL ) {
					fprintf(stderr, "SocketClient::procRecvMp3Data() MP3 malloc body data failed : [%02d] %s\n", errno, strerror(errno));

					break;
				}
				
				if( !this->getSelect() ) {
					break;
				}

				if( (rc = read(this->sockFd, recvBodyData, tHeaderInfo.chunkSize)) < 0 ) {
					fprintf(stderr, "SocketClient::procRecvMp3Data() recv body data failed : [%02d] %s\n", errno, strerror(errno));

					break;

				}
			
				stackClient.setQueue(&tMp3Queue, recvBodyData, tHeaderInfo.chunkSize);
			}
			
			gMsgQueueFunc.decCntAudioMute();

		} else {
			/* TCP(uni-cast) part */
			while( !this->flagTerm ) {
				while( !this->flagTerm ) {
					if( !this->getSelect() ) {
						break;
					}
	
					if( recv(this->sockFd,&tChunkInfo ,sizeof(tChunkInfo), 0) <= 0 ) {
						fprintf(stderr,"SocketClient::procRecvMp3Data() recv() tChunkInfo failed : [%02d] %s\n", errno, strerror(errno));
						tHeaderInfo.chunkSize = 960;
					} 
	
					tHeaderInfo.chunkSize = tChunkInfo.chunkSize;
	
					if( recvBodyData != NULL ) {
						free(recvBodyData);
						recvBodyData = NULL;
					}
	
					if( (recvBodyData = (char *)malloc(tHeaderInfo.chunkSize * sizeof(char))) == NULL ) {
						fprintf(stderr, "SocketClient::procRecvMp3Data() MP3 malloc body data failed : [%02d] %s\n", errno, strerror(errno));
	
						break;
					}
	
					if( (rc = recv(this->sockFd, recvBodyData, tHeaderInfo.chunkSize , MSG_WAITALL)) <= 0 ) {
						if( errno == EAGAIN ) {
							fprintf(stderr, "SocketClient::procRecvMp3Data() body not received..\n");
	
						} else {
							fprintf(stderr, "SocketClient::procRecvMp3Data() recv body data failed : [%02d] %s\n", errno, strerror(errno));
						}
						break;
					}
	
					stackClient.setQueue(&tMp3Queue, recvBodyData, tHeaderInfo.chunkSize);
				} // end of while()
				
				fprintf(stderr, "SocketClient::procRecvMp3Data() - Disconnected by server.. \n");
				fprintf(stderr, "                                  Find next server.. \n");
		
				this->tServerList[this->ipStatus].stat = false;  
				
				if( this->reconnSocket() ) {
					pcmHandle.printClientInfo();
						
					this->flagTerm = false;
					common.setReconnFlag();
				
				} else {
					break;
				}
			} // end of while() : reconnect
		}

		if( recvBodyData != NULL ) {
			free(recvBodyData);
			recvBodyData = NULL;
		}

		pcmHandle.setThreadRecv(true);
		common.term();
	} // end of ProcRecvMp3Data()
	

	int SocketClient::reconnSocket(void) {
		int     sleepTime;

		sleepTime = this->tPlayInfo.idx * 50;

		fprintf(stderr, "SocketClient::reconnSocket() Sleep for [%d] usec\n", sleepTime);
		usleep(sleepTime);

		// socket re-declaration
		fprintf(stderr, "SocketClient::reconnSocket() Close current socket..\n");
		this->closeSocket();

		fprintf(stderr, "SocketClient::reconnSocket() Init new socket..\n");
		if( (this->sockFd = this->initSocket()) < 0 ) {
			return false;
		}

		fprintf(stderr, "SocketClient::reconnSocket() Set new socket option..\n");
		if( !this->initSocketOption(this->sockFd, this->tHostInfo.hostName) ) {
			return false;
		}

		fprintf(stderr, "SocketClient::reconnSocket() Recv play information from server..\n");
		if( !this->recvPlayInfo() ) {
			return false;
		}

		fprintf(stderr, "SocketClient::reconnSocket() SetPcmHandle called..\n");

		pcmHandle.setPlayInfo();
		pcmHandle.setCloseHandle(true);

		return true;
	}

	void SocketClient::closeSocket(void) {
		this->flagTerm = true;

		if( this->sockFd != -1 ) {
			fprintf(stderr, "SocketClient::closeSocket() close socket client..\n");
			close(this->sockFd);
		
			this->sockFd = -1;
		}

		return ;
	}	
	
	LINEAR_QUEUE_t *SocketClient::getMp3QueuePtr(void) {

		return &this->tMp3Queue;
	}

	void PcmHandle::printClientInfo(void) {
		char nowTime[128];
		Common::GetTime(nowTime);

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
		int length = 0, bufLen = 0;
		int frameSize, accConsume = 0;
		int leftInLength = 0, leftLength = 0, inBufLen;
		int numSamples;
		int flagRoutine = false;
		const int   sizeThreshold = Common::SIZE_MP3_THRESHOLD;
		const int   inputSize     = Common::SIZE_MP3_FRAME_BUFFER;

		MP3D_INT8               mp3Input[inputSize];
		MP3D_RET_TYPE           tRetVal;
		MP3D_Decode_Params      tDecParams;
		MP3D_Mem_Alloc_Info_Sub *tMemInfo;
		LINEAR_QUEUE_t          *tMp3Queue;

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
		tMp3Queue  = socketClient.getMp3QueuePtr();
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
				// debug message
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
					if( !flagRoutine ) {
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

		// this->tMp3Header.bitrate = gBitRates[(_mp3Frame[2] & 0xf0) >> 4];
		// this->tMp3Header.freq = gSampleRates[(_mp3Frame[2] & 0x0c) >> 2];
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

