#ifndef __COMMON_CLASS_H__
#define __COMMON_CLASS_H__

#include <netinet/in.h>
#include <signal.h>
#include <thread>

#include "mp3_enc_interface.h"
#include "pcm_mp3enc.h"
#include "pcm_wav_function.h"

namespace Common {
	using namespace std;

	/* const variables */
	const bool  TYPE_PROTOCOL_UDP   = false;
	const bool  TYPE_PROTOCOL_TCP   = true;
	const int	TIMEOUT_ACCEPT_SEC	= 2;
	const int	TIMEOUT_ACCEPT_MSEC	= 0;

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
		int				connCnt;
		char    		ipAddr[24];
		char    		beforeStat[128];
		char    		currentStat[128];
		char    		beforeTime[128];
		char    		currentTime[128];
		char    		workingTime[128];
		char    		disconnTime[128];
		char    		hostName[128];
		time_t  		startTime;
		time_t  		endTime;
		char    		macAddr[24];
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

	struct SERVER_INFO {
		// Queue parameter
		int		queueCnt;
		int		bufferRate;
		int		chunkSize;

		// PCM parameter
		int		sampleRate;
		int		channels;

		// MP3 parameter
		bool	mp3_mode;
		int		mp3_chunkSize;
		int 	mp3_bitRate;
		int		mp3_sampleRate;

		// server parameter
		bool	typeProtocol;
		char    castType[24];
		int		port;
		int		clientCnt;
		char	ipAddr[24];

		bool	typePlayMode;
		char	fileName[128];
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

	/* Function */
	void GetTime(char *_output);
	void ChangeTime(int _sec, char *_output);

	/* class */
	class SocketServer {
		private :
			int		flagTerm = false;
			struct	sockaddr_in 	tSockAddr;
			struct  sockaddr_in     tSockAddrMulti;
			SOCKET_INFO_t			tServerInfo;

		public  :
			SocketServer(void);
			~SocketServer(void);

			bool	setInfo(SOCKET_INFO_t *_tSocketInfo);
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
			int		stackIdx;
			int		queueidx;
			int		recvCnt;
			int		typeProtocol;

			struct  sockaddr_in     tSockAddr;

			HOST_INFO_t		tHostInfo;
			PLAY_INFO_t     tPlayInfo;
			ENCODE_INFO_t   tEncInfo;
			thread 			threadFunc;
			pthread_mutex_t	tStackMutex;

			bool initSocket(void);
			bool sendPlayInfo(void);
			void setClientInfo(void);
			void execute(void);

		public:
			ThreadClient(void);
			~ThreadClient(void);

			CLIENT_INFO_t	tClientInfo;		

			void run(void);
			void stop(void);
			bool getUseStatus(void);
			bool setThreadClient(CLIENT_INFO_t _tClientInfo);
			void setPlayInfo(int _idx, int _typeProtocol, PLAY_INFO_t *_tPlayInfo, ENCODE_INFO_t *_tEncInfo);
			void setSocketOption(void);
			void setStackIdx(int _idx);
			bool setSockAddr(struct sockaddr_in *_tSockAddr);
			bool sendUnicast(void);
			bool sendMp3Unicast(void);
			bool sendMulticast(void);
			bool sendMp3Multicast(void);
			void removeClientInfo(void);
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

			SocketServer **arrTransInstance;
			ThreadClient **arrThreadInstance;
			SocketServer **arrTransInstanceMul;
			ThreadClient **arrThreadInstanceMul;

		public  :
			CommonFunc(void);

			void	setMaxTransIntance(int _cnt);
			void	setMaxThreadIntance(int _cnt);
			void 	setTransInstance(SocketServer *_classPtr);
			void 	setThreadInstance(ThreadClient *_classPtr);
			void 	setTransInstanceMul(SocketServer *_classPtr);
			void 	setThreadInstanceMul(ThreadClient *_classPtr);
			bool    setClientList(CLIENT_INFO_t *_tClientInfo, bool _stat);
			void 	printClientList(void);
			bool	getFlagTerm(void);
			char	*getClientList(void);
			char 	*getServerInfo(void);
			void 	setServerInfo(SERVER_INFO_t *_tServerInfo);

			void 	handler(void);
			void 	term(void);
	}; // end of class : CommonFunc
	extern CommonFunc	common;

	class SigHandler {
		public  :
			SigHandler(void);
			static void term(int _sigNum);
	}; // end of class : SigHandler
	extern SigHandler	handler;

	class QueueStack {
		private:
			int		storeCnt;
			int		bufferSize;
			int		bufferRate;
			int		chunkSize;
			int		recvIdx;
			bool	flagRecvCnt;
			bool	flagRecv;
			char	name[64];

			QUEUE_STACK_t	tQueueStack;
			pthread_mutex_t	tStackMutex;

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
			void    initStack(int _idx, int _bufferRate, int _chunkSize);

	};
	extern QueueStack *stackServer;

	class ServerFunc {
		private:
			int				queueCnt;
			int             bufferRate;
			int             chunkSize;
			bool            typePlayMode;
			char            fileName[128];
			char            deviceName[128];

			thread			txThread;
			SOCKET_INFO_t	tSocketInfo;
			PLAY_INFO_t     tPlayInfo;
			ENCODE_INFO_t   tEncInfo;

			ThreadClient    *pThreadClient;

			void 	sendThread();

		public:
			ServerFunc(void);
			~ServerFunc(void);

			void 	init(SERVER_INFO_t *_tServerInfo);
			void 	run(void);
			void 	stop(void);
			bool	setPlayMode(int _idx, char *_fileName);
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
	

#endif
}

#endif
