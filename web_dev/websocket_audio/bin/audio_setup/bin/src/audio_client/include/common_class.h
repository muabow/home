#ifndef __COMMON_CLASS_H__
#define __COMMON_CLASS_H__

#include <netinet/in.h>
#include <signal.h>
#include <thread>
#include <alsa/asoundlib.h>
#include "mp3_dec_interface.h"

namespace Common {
	using namespace std;
	const bool  TYPE_PROTOCOL_UDP   = false;
	const bool  TYPE_PROTOCOL_TCP   = true;
	const int   TIMEOUT_ACCEPT_SEC  = 2;
	const int   TIMEOUT_ACCEPT_MSEC = 0;

	void GetTime(char *_output);
	
	class CommonFunc {
		private :
			bool    flagTerm = false;
			bool    reconFlag = false;
		public  :
		//	CommonFunc(void);
			void    handler(void);
			void    term(void);
			bool 	getFlagTerm();
			bool 	getReconFlag();
			bool 	setReconFlag();
	}; // end of class : CommonFunc
	extern CommonFunc  common;


	class SigHandler {

			SigHandler(void);
			static void term(int _sigNum);
	}; // end of class : SigHandler
	extern SigHandler   handler;

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
		char 	castType[24];
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
		int     sampleRate;
		short   bitRate;
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

			thread clientMp3Rev;
			thread clientFunc;
		public  :
			SocketClient(void);
			~SocketClient(void);
			int 	setServerList(IP_INFO_t *_tIpInfo);
			int		getPlayInfoEncode(void);
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
			//void 	getMp3QueuePtr(LINEAR_QUEUE_t *_tMp3Queue);
			LINEAR_QUEUE_t *getMp3QueuePtr(void);
			unsigned short getCrcValue(char *_buf, unsigned short _len);
			bool getIpAddress();
			bool    getSelect(void);

	}; // end of class : SocketClient

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
			void 	initFlag(void);
			void 	setFlag(DELAY_INFO_t *_tDelayInfo, char *_deviceName);
			void 	pcmPlayLoop(void);
			void 	pcmStop(void);
			void 	printClientInfo(void);
			void 	execute();
			void 	setPlayInfo();
			void 	getIpList(IP_INFO_t *_tIpInfo);

	};

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
		
			MP3D_INT16          *tPcmFrame;
			MP3D_Decode_Config  *tDecConfig;
			MP3_HEADER_t 		tMp3Header;
			thread threadMp3Dec;
		public:
			void procMp3Decode();
			void *decodeAllocFast(int _size);
			void *decodeAllocSlow(int _size);
			void freeMp3Decode(void);
			void getMp3FrameInfo(char *_mp3Frame);

	};

}	

#endif
