#include "api_mute_relay.h"


IPC_msgQueueFunc::IPC_msgQueueFunc(bool _is_debug_print) {
	this->is_debug_print 	= _is_debug_print;
	
	this->m_type = 1;
	return ;
}

IPC_msgQueueFunc::~IPC_msgQueueFunc(void) {
	return ;
}

void IPC_msgQueueFunc::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	fprintf(stdout, "IPC_msgQueueFunc::");
	
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	return ;
}

void IPC_msgQueueFunc::set_debug_print(void) {
	this->is_debug_print = true;

	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

bool IPC_msgQueueFunc::init_msg_queue(uint32_t _addr_msg_queue) {
	struct	msqid_ds msqStat;

	if( (this->msg_key = msgget((key_t)_addr_msg_queue, IPC_CREAT|0666)) < 0 ) {
		this->print_debug_info("init_msg_queue() msgget() failed : [%02d] %s\n", errno, strerror(errno));
		return false;
	}

	if( msgctl(this->msg_key, IPC_STAT, &msqStat) < 0 )  {
		this->print_debug_info("init_msg_queue() msgctl() IPC_STAT failed : [%02d] %s\n", errno, strerror(errno));
		return false;
	}

	this->is_stat_mute = false;
	
	return true;
}

bool IPC_msgQueueFunc::sendData(IPC_msgQueueFunc::MSG_QUEUE_t *_t_msg_queue) {
	if( msgsnd(this->msg_key, _t_msg_queue, sizeof(IPC_msgQueueFunc::MSG_QUEUE_t) - sizeof(uint16_t), 0) < 0 ) {
		this->print_debug_info("msgsnd() failed : [%02d] %s\n", errno, strerror(errno));
		return false;
	 }

	return true;
}

bool IPC_msgQueueFunc::recvData(IPC_msgQueueFunc::MSG_QUEUE_t *_t_msg_queue) {
	if( msgrcv(this->msg_key, _t_msg_queue, sizeof(IPC_msgQueueFunc::MSG_QUEUE_t) - sizeof(uint16_t), 0, 0) < 0 ) {
		this->print_debug_info("msgrcv() failed : [%02d] %s\n", errno, strerror(errno));
		return false;
	  }

	return true;
}

bool IPC_msgQueueFunc::init(void) {
	return this->init_msg_queue(ADDR_MSG_QUEUE);
}

bool IPC_msgQueueFunc::init(uint32_t _addr_msg_queue) {
	return this->init_msg_queue(_addr_msg_queue);
}

bool IPC_msgQueueFunc::remove(void) {
	if( msgctl(this->msg_key, IPC_RMID, 0) < 0 ) {
	   return false;
   }

   return true;
}

void IPC_msgQueueFunc::setMsgType(uint16_t _type) {
	this->m_type = _type;

	return ;
}

bool IPC_msgQueueFunc::send(uint8_t _type, uint8_t _status) {
	IPC_msgQueueFunc::MSG_QUEUE_t	t_msg_queue;
	memset(&t_msg_queue, 0x00, sizeof(t_msg_queue));

	t_msg_queue.m_type = this->m_type;
	t_msg_queue.t_relay_data.type    = _type;
	t_msg_queue.t_relay_data.status  = _status;

	return this->sendData(&t_msg_queue);
}

bool IPC_msgQueueFunc::increase_audio_mute(void) {
	if( this->is_stat_mute ) {
		this->print_debug_info("increase_audio_mute() already unset mute\n");
		
		return false;
	}
	this->print_debug_info("increase_audio_mute() unset mute\n");
	this->is_stat_mute = true;
	
	return this->send(0, 1);
}

bool IPC_msgQueueFunc::decrease_audio_mute(void) {
	bool rc;

	if( !this->is_stat_mute ) {
		this->print_debug_info("decrease_audio_mute() already set mute\n");
	
		return false;
	}
	this->print_debug_info("decrease_audio_mute() set mute\n");
	this->is_stat_mute = false;
				
	rc = this->send(0, 0);

	this->print_debug_info("decrease_audio_mute() mute delay : %d us\n", this->time_mute_delay);
	usleep(this->time_mute_delay);

	return rc;
}

bool IPC_msgQueueFunc::incCntAudioPlayer(void) {
	if( this->is_stat_player ) {
		this->print_debug_info("incCntAudioPlayer() already set player\n");
		
		return false;
	}
	this->print_debug_info("incCntAudioPlayer() set mute\n");
	this->is_stat_player = true;
				
	return this->send(1, 1);
}

bool IPC_msgQueueFunc::decCntAudioPlayer(void) {
	if( !this->is_stat_player ) {
		this->print_debug_info("decCntAudioPlayer() already unset player\n");
			
		return false;
	}
	this->print_debug_info("decCntAudioPlayer() set mute\n");
	this->is_stat_player = false;
	
	return this->send(1, 0);
}

bool IPC_msgQueueFunc::recv(IPC_msgQueueFunc::RELAY_DATA_t *_t_relay_data) {
	IPC_msgQueueFunc::MSG_QUEUE_t	t_msg_queue;

	if( this->recvData(&t_msg_queue) == false ) {
		return false;

	} else {
		memcpy(_t_relay_data, &t_msg_queue.t_relay_data, sizeof(IPC_msgQueueFunc::RELAY_DATA_t));
		return true;
	}

	return true;
}

bool IPC_msgQueueFunc::is_unmute(void) {
	
	return this->is_stat_mute;
}

bool IPC_msgQueueFunc::isPlayer(void) {
	
	return this->is_stat_player; 
}

		
IPC_shmMemoryFunc::IPC_shmMemoryFunc(bool _is_debug_print) {
	this->is_debug_print 	= _is_debug_print;
	
	this->p_shm_addr 	= NULL;
	this->pt_count_data = NULL;

	return ;
}

IPC_shmMemoryFunc::~IPC_shmMemoryFunc(void) {

	return ;
}

void IPC_shmMemoryFunc::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	fprintf(stdout, "IPC_shmMemoryFunc::");
	
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	return ;
}

void IPC_shmMemoryFunc::set_debug_print(void) {
	this->is_debug_print = true;

	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

bool IPC_shmMemoryFunc::init(void) {
	if( (this->shm_key = shmget((key_t)this->ADDR_SHM_QUEUE, sizeof(COUNT_DATA_t), IPC_CREAT|0666)) < 0 ) {
		this->print_debug_info("shmget() failed : [%02d] %s\n", errno, strerror(errno));
		return false;
	}

	if( (this->p_shm_addr = shmat(this->shm_key, (void *)0, 0)) < 0 ) {
		this->print_debug_info("shmat() failed : [%02d] %s\n", errno, strerror(errno));
		return false;
	}

	this->pt_count_data = (COUNT_DATA_t *)this->p_shm_addr;

	return true;
}

void IPC_shmMemoryFunc::increase_audio_mute(void) {
	this->pt_count_data->audio_mute++;

	return ;
}

void IPC_shmMemoryFunc::decrease_audio_mute(void) {
	if( this->pt_count_data->audio_mute == 0 ) {
		return ;

	} else {
		this->pt_count_data->audio_mute--;
	}

	return ;
}

void IPC_shmMemoryFunc::set_count_audio_mute(uint32_t _cnt) {
	this->pt_count_data->audio_mute = _cnt;

	return ;
}

uint32_t IPC_shmMemoryFunc::get_count_audio_mute(void) {
	return this->pt_count_data->audio_mute;
}

void IPC_shmMemoryFunc::incCntAudioPlayer(void) {
	this->pt_count_data->audio_player++;

	return ;
}

void IPC_shmMemoryFunc::decCntAudioPlayer(void) {
	if( this->pt_count_data->audio_player == 0 ) {
		return ;

	} else {
		this->pt_count_data->audio_player--;
	}

	return ;
}

uint32_t IPC_shmMemoryFunc::getCntAudioPlayer(void) {
	return this->pt_count_data->audio_player;
}

void IPC_shmMemoryFunc::setCntAudioPlayer(uint32_t _cnt) {
	this->pt_count_data->audio_player = _cnt;

	return ;
}

void IPC_shmMemoryFunc::printShm(void) {
	this->print_debug_info("audio mute count   : [%d]\n", this->pt_count_data->audio_mute);
	this->print_debug_info("audio player count : [%d]\n", this->pt_count_data->audio_player);

	return ;
}

bool IPC_shmMemoryFunc::detach(void) {
	if( shmdt(this->p_shm_addr) < 0 ) {
	   return false;
   }

   return true;
}

bool IPC_shmMemoryFunc::remove(void) {
	if( shmctl(this->shm_key, IPC_RMID, NULL) < 0 ) {
	   return false;
   }

   return true;
}