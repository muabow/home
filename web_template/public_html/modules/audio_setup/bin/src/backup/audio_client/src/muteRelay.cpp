#include <stdio.h>
#include <stdint.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>
#include <unistd.h>
#include <sys/types.h>
#include <sys/ipc.h>
#include <sys/msg.h>
#include <sys/shm.h>
#include <signal.h>

extern int		gMuteTime;

class IPC_msgQueueFunc {
	const int	ADDR_MSG_QUEUE	= 7593;

	public:
		struct RELAY_DATA {
			uint8_t			type;		// 0: audio mute relay, 1: audio player volume control
			uint8_t			status;		// 0: off,  1: on
		} typedef RELAY_DATA_t;

		struct MSG_QUEUE {
			uint16_t		mType;
			RELAY_DATA_t	tRelayData;
		} typedef MSG_QUEUE_t;

		enum RELAY_TYPE {
			TYPE_AUDIO_MUTE 	= 0,
			TYPE_AUDIO_PLAYER
		};

		enum RELAY_STATUS {
			STAT_OFF 	= 0,
			STAT_ON
		};

	private:
		bool		isMuteFlag;
		bool		isPlayerFlag;
		
		key_t		msgKey;
		uint16_t	mType;

		bool initMsgQueue(uint32_t _addrmsgQueue) {
			struct	msqid_ds msqStat;

			if( (this->msgKey = msgget((key_t)_addrmsgQueue, IPC_CREAT|0666)) < 0 ) {
				printf("msgget() failed : [%02d] %s\n", errno, strerror(errno));
				return false;
			}

			if( msgctl(this->msgKey, IPC_STAT, &msqStat) < 0 )  {
				printf("msgctl() IPC_STAT failed : [%02d] %s\n", errno, strerror(errno));
				return false;
			}

			this->isMuteFlag = false;
			
			return true;
		}

		bool sendData(IPC_msgQueueFunc::MSG_QUEUE_t *_tMsgQueue) {
			if( msgsnd(this->msgKey, _tMsgQueue, sizeof(IPC_msgQueueFunc::MSG_QUEUE_t) - sizeof(uint16_t), 0) < 0 ) {
				printf("msgsnd() failed : [%02d] %s\n", errno, strerror(errno));
				return false;
			 }

			return true;
		}

		bool recvData(IPC_msgQueueFunc::MSG_QUEUE_t *_tMsgQueue) {
			if( msgrcv(this->msgKey, _tMsgQueue, sizeof(IPC_msgQueueFunc::MSG_QUEUE_t) - sizeof(uint16_t), 0, 0) < 0 ) {
				printf("msgrcv() failed : [%02d] %s\n", errno, strerror(errno));
				return false;
			  }

			return true;
		}

	public:
		IPC_msgQueueFunc(void)  {
			this->mType = 1;
			return ;
		}

		~IPC_msgQueueFunc(void) {
			return ;
		}

		bool init(void) {
			return this->initMsgQueue(this->ADDR_MSG_QUEUE);
		}

		bool init(uint32_t	_addrMsgQueue) {
			return this->initMsgQueue(_addrMsgQueue);
		}

		bool remove(void) {
			if( msgctl(this->msgKey, IPC_RMID, 0) < 0 ) {
			   return false;
		   }

		   return true;
	   	}

		void setMsgType(uint16_t _type) {
			this->mType = _type;

			return ;
		}

		bool send(uint8_t _type, uint8_t _status) {
			IPC_msgQueueFunc::MSG_QUEUE_t	tMsgQueue;
			memset(&tMsgQueue, 0x00, sizeof(tMsgQueue));

			tMsgQueue.mType = this->mType;
			tMsgQueue.tRelayData.type    = _type;
			tMsgQueue.tRelayData.status  = _status;

			return this->sendData(&tMsgQueue);
		}

		bool incCntAudioMute(void) {
			if( this->isMuteFlag ) {
				fprintf(stderr, "IPC_msgQueueFunc::incCntAudioMute() already unset mute\n");
				
				return false;
			}
			fprintf(stderr, "IPC_msgQueueFunc::incCntAudioMute() unset mute\n");
			this->isMuteFlag = true;
			
			return this->send(0, 1);
		}

		bool decCntAudioMute(void) {
			bool rc;

			if( !this->isMuteFlag ) {
				fprintf(stderr, "IPC_msgQueueFunc::decCntAudioMute() already set mute\n");
			
				return false;
			}
			fprintf(stderr, "IPC_msgQueueFunc::decCntAudioMute() set mute\n");
			this->isMuteFlag = false;
						
			rc = this->send(0, 0);

			fprintf(stderr, "IPC_msgQueueFunc::decCntAudioMute() mute delay : %d us\n", gMuteTime);
			usleep(gMuteTime);

			return rc;
		}

		bool incCntAudioPlayer(void) {
			if( this->isPlayerFlag ) {
				fprintf(stderr, "IPC_msgQueueFunc::incCntAudioPlayer() already set player\n");
				
				return false;
			}
			fprintf(stderr, "IPC_msgQueueFunc::incCntAudioPlayer() set mute\n");
			this->isPlayerFlag = true;
						
			return this->send(1, 1);
		}

		bool decCntAudioPlayer(void) {
			if( !this->isPlayerFlag ) {
				fprintf(stderr, "IPC_msgQueueFunc::decCntAudioPlayer() already unset player\n");
					
				return false;
			}
			fprintf(stderr, "IPC_msgQueueFunc::decCntAudioPlayer() set mute\n");
			this->isPlayerFlag = false;
			
			return this->send(1, 0);
		}

		bool recv(IPC_msgQueueFunc::RELAY_DATA_t *_tRelayData) {
			IPC_msgQueueFunc::MSG_QUEUE_t	tMsgQueue;

			if( this->recvData(&tMsgQueue) == false ) {
				return false;

			} else {
				memcpy(_tRelayData, &tMsgQueue.tRelayData, sizeof(IPC_msgQueueFunc::RELAY_DATA_t));
				return true;
			}

			return true;
		}
		
		bool isMute(void) {
			
			return this->isMuteFlag;
		}
		
		bool isPlayer(void) {
			
			return this->isPlayerFlag; 
		}
		
}; // end of class : IPC_msgQueueFunc

class IPC_shmMemoryFunc {
	const int	ADDR_SHM_QUEUE	= 7593;

	struct COUNT_DATA {
		uint32_t		audio_mute;
		uint32_t		audio_player;
	} typedef COUNT_DATA_t;

	private:
		int		shmKey;
		void 	*shmAddr;
		IPC_shmMemoryFunc::COUNT_DATA_t *gtCountData;

	public:
		IPC_shmMemoryFunc(void)  {
			this->shmAddr = NULL;
			gtCountData   = NULL;

			return ;
		}

		~IPC_shmMemoryFunc(void) {

			return ;
		}

		bool init(void) {
			if( (this->shmKey = shmget((key_t)this->ADDR_SHM_QUEUE, sizeof(IPC_shmMemoryFunc::COUNT_DATA_t), IPC_CREAT|0666)) < 0 ) {
      			printf("shmget() failed : [%02d] %s\n", errno, strerror(errno));
      			return false;
			}

  			if( (this->shmAddr = shmat(this->shmKey, (void *)0, 0)) < 0 ) {
				printf("shmat() failed : [%02d] %s\n", errno, strerror(errno));
				return false;
			}

			gtCountData = (IPC_shmMemoryFunc::COUNT_DATA_t *)this->shmAddr;

			return true;
		}

		void incCntAudioMute(void) {
			gtCountData->audio_mute++;

			return ;
		}

		void decCntAudioMute(void) {
			if( gtCountData->audio_mute == 0 ) {
				return ;

			} else {
				gtCountData->audio_mute--;
			}

			return ;
		}

		void setCntAudioMute(uint32_t _cnt) {
			gtCountData->audio_mute = _cnt;

			return ;
		}

		uint32_t getCntAudioMute(void) {
			return gtCountData->audio_mute;
		}

		void incCntAudioPlayer(void) {
			gtCountData->audio_player++;

			return ;
		}

		void decCntAudioPlayer(void) {
			if( gtCountData->audio_player == 0 ) {
				return ;

			} else {
				gtCountData->audio_player--;
			}

			return ;
		}

		uint32_t getCntAudioPlayer(void) {
			return gtCountData->audio_player;
		}

		void setCntAudioPlayer(uint32_t _cnt) {
			gtCountData->audio_player = _cnt;

			return ;
		}

		void printShm(void) {
			printf("audio mute count   : [%d]\n", gtCountData->audio_mute);
			printf("audio player count : [%d]\n", gtCountData->audio_player);

			return ;
		}

		bool detach(void) {
			if( shmdt(this->shmAddr) < 0 ) {
			   return false;
		   }

		   return true;
		}

		bool remove(void) {
			if( shmctl(this->shmKey, IPC_RMID, NULL) < 0 ) {
			   return false;
		   }

		   return true;
		}
}; // end of class : IPC_shmMemoryFunc
