#ifndef __API_MUTE_RELAY_H__
#define __API_MUTE_RELAY_H__

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
#include <stdarg.h>


class IPC_msgQueueFunc {
	const int	ADDR_MSG_QUEUE	= 7593;

	public:
		struct RELAY_DATA {
			uint8_t			type;		// 0: audio mute relay, 1: audio player volume control
			uint8_t			status;		// 0: off,  1: on
		} typedef RELAY_DATA_t;

		struct MSG_QUEUE {
			uint16_t		m_type;
			RELAY_DATA_t	t_relay_data;
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
		bool		is_debug_print 		= false;
		void		print_debug_info(const char *_format, ...);
		
		bool		is_stat_mute;
		bool		is_stat_player;
		
		int			time_mute_delay = 0;
		
		key_t		msg_key;
		uint16_t	m_type;
		
		bool init_msg_queue(uint32_t _addr_msg_queue);
		bool sendData(MSG_QUEUE_t *_t_msg_queue);
		bool recvData(MSG_QUEUE_t *_t_msg_queue);
		
	public:
		IPC_msgQueueFunc(bool _is_debug_print = false);
		~IPC_msgQueueFunc(void);
		
		void set_debug_print(void);
		
		bool init(void);
		bool init(uint32_t	_addr_msg_queue);
		
		bool remove(void);
		bool recv(RELAY_DATA_t *_t_relay_data);

		void setMsgType(uint16_t _type);
		bool send(uint8_t _type, uint8_t _status);
		
		bool increase_audio_mute(void);
		bool decrease_audio_mute(void);
		bool is_unmute(void);

		bool incCntAudioPlayer(void);
		bool decCntAudioPlayer(void);
		bool isPlayer(void);		
}; // end of class : IPC_msgQueueFunc

class IPC_shmMemoryFunc {
	const int	ADDR_SHM_QUEUE	= 7593;

	struct COUNT_DATA {
		uint32_t		audio_mute;
		uint32_t		audio_player;
	} typedef COUNT_DATA_t;

	private:
		bool		is_debug_print 		= false;
		void		print_debug_info(const char *_format, ...);
			
		int			shm_key;
		void 		*p_shm_addr;
		COUNT_DATA_t *pt_count_data;

	public:
		IPC_shmMemoryFunc(bool _is_debug_print = false);
		~IPC_shmMemoryFunc(void);
		
		void set_debug_print(void);
		
		bool init(void);
		bool detach(void);
		bool remove(void);
		void printShm(void);
		
		void increase_audio_mute(void);
		void decrease_audio_mute(void);
		void set_count_audio_mute(uint32_t _cnt);
		uint32_t get_count_audio_mute(void);

		void incCntAudioPlayer(void);
		void decCntAudioPlayer(void);
		void setCntAudioPlayer(uint32_t _cnt);
		uint32_t getCntAudioPlayer(void);
}; // end of class : IPC_shmMemoryFunc



#endif