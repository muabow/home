#include "api_pcm_playback.h"


PCM_PlaybackHandler::PCM_PlaybackHandler(bool _is_debug_print) {
	this->is_debug_print = _is_debug_print;
	this->playback_handle = NULL;
	this->control_handle  = NULL;

	this->time_buffer_delay = 0;
	
	this->print_debug_info("PCM_PlaybackHandler() create instance\n");

	memset(this->device_name, 0x00, sizeof(this->device_name));
	
	return ;
}

PCM_PlaybackHandler::~PCM_PlaybackHandler(void) {
	this->print_debug_info("PCM_PlaybackHandler() instance destructed : [%s]\n", this->device_name);
	
	if( this->thread_func.joinable() ) {
		this->thread_func.join();
	}

	if( this->t_pcm_handler != NULL ) {
		snd_pcm_drop(this->t_pcm_handler);	
		snd_pcm_drain(this->t_pcm_handler);	
		snd_pcm_close(this->t_pcm_handler);
	}
	this->t_pcm_handler = NULL;

	return ;
}

void PCM_PlaybackHandler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	fprintf(stdout, "PCM_PlaybackHandler::");
	
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	return ;
}

void PCM_PlaybackHandler::set_debug_print(void) {
	this->is_debug_print = true;

	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}


void PCM_PlaybackHandler::change_debug_print(bool _is_debug_print) {
	this->is_debug_print = _is_debug_print;

	return ;
}

bool PCM_PlaybackHandler::init(string _device_name, int _chunk_size, int _sample_rate, int _channels, int _size_pcm_periods, int _time_div_periods) {
	if( this->is_init ) {
		this->print_debug_info("init() already init\n");
		return false;
	}
	
	if( this->is_run ) {
		this->print_debug_info("init() already running\n");
		return false;
	}
	
	this->is_init		 = false;
	this->is_run		 = false;
	this->is_loop		 = false;
	
	if( _device_name.compare("") != 0 ) { 
		strcpy(this->device_name, _device_name.c_str());
	}
	
	if( _chunk_size != -1 ) {
		this->chunk_size = _chunk_size;
	}
	
	if( _sample_rate != -1 ) {
		this->sample_rate = _sample_rate;
	}
	
	if( _channels != -1 ) {
		this->channels = _channels;
	}
	
	if( _size_pcm_periods != -1 ) {
		this->print_debug_info("init() change parameter [size_pcm_periods] : [%d] change to [%d]\n", this->size_pcm_periods, _size_pcm_periods);
		this->size_pcm_periods = _size_pcm_periods;
	}
	
	if( _time_div_periods != -1 ) {
		this->print_debug_info("init() change parameter [time_div_periods] : [%d] change to [%d]\n", this->time_div_periods, _time_div_periods);
		this->time_div_periods = _time_div_periods;
	}
	
	this->print_debug_info("init() parameter information..\n");
	this->print_debug_info("init() parameter - period size      : [%d]\n", this->size_pcm_periods);
	this->print_debug_info("init() parameter - division periods : [%d]\n", this->time_div_periods);
	this->print_debug_info("init() parameter - chunk size       : [%d]\n", this->chunk_size);
	this->print_debug_info("init() parameter - sample rate      : [%d]\n", this->sample_rate);
	this->print_debug_info("init() parameter - channels         : [%d]\n", this->channels);
	this->print_debug_info("init() parameter - deivce name      : [%s]\n", this->device_name);
	
	if( !this->set_pcm_driver() ) {
		this->print_debug_info("init() PCM device init failed [%s]\n", this->device_name);
		this->close_pcm_driver();

		return false;
	}

	this->print_debug_info("init() PCM device init success [%s]\n", this->device_name);
	
	this->v_queue_data.clear();
	
	this->is_init = true;
	
	return true;
}

void PCM_PlaybackHandler::stop(void) {
	if( !this->is_run ) {
		this->print_debug_info("stop() playback is not running\n");
		
		return ;
	}
	this->is_loop = true;
	
	if( this->thread_func.joinable() ) {
		this->print_debug_info("stop() join & wait audio thread term\n");
		this->thread_func.join();
	}

	if( this->t_pcm_handler != NULL ) {
		this->print_debug_info("stop() Free PCM device [%s]\n", this->device_name);

		snd_pcm_drop(this->t_pcm_handler);	
		snd_pcm_drain(this->t_pcm_handler);	
		snd_pcm_close(this->t_pcm_handler);
	}
	this->t_pcm_handler = NULL;
	
	this->v_queue_data.clear();
	
	this->is_init = false;
	
	return ;
}

void PCM_PlaybackHandler::run(void) {
	if( !this->is_init ) {
		this->print_debug_info("run() init is not ready\n");
		
		return ;
	}
	
	if( this->is_run ) {
		this->print_debug_info("run() already running\n");
		return ;
	}
	
	if( this->thread_func.joinable() ) {
		this->thread_func.join();
	}
	
	this->is_run = true;
	
	this->print_debug_info("run() create execute thread\n");
	this->thread_func = thread(&PCM_PlaybackHandler::execute, this);
	
	return ;
}

void PCM_PlaybackHandler::close_pcm_driver(void) {
	if( this->t_pcm_handler != NULL ) {
		this->print_debug_info("close_pcm_driver() close pcm handler\n");
		snd_pcm_drop(this->t_pcm_handler);	
		snd_pcm_drain(this->t_pcm_handler);	
		snd_pcm_close(this->t_pcm_handler);

		this->t_pcm_handler = NULL;
	}
	
	return ;
}

bool PCM_PlaybackHandler::set_pcm_driver(void) {
	int 	err;
	
	snd_pcm_hw_params_t *hw_params = NULL;
	snd_pcm_hw_params_alloca(&hw_params);

	snd_pcm_format_t format = SND_PCM_FORMAT_S16_LE;
	snd_pcm_stream_t stream = SND_PCM_STREAM_PLAYBACK;

	if( this->t_pcm_handler != NULL ) {
		this->print_debug_info("set_pcm_driver() resetting playback parameter\n");
		snd_pcm_drop(this->t_pcm_handler);
		snd_pcm_drain(this->t_pcm_handler);
		snd_pcm_close(this->t_pcm_handler);
		
		this->t_pcm_handler = NULL;
	}

	if( (err = snd_pcm_open(&this->t_pcm_handler, this->device_name, stream, SND_PCM_NONBLOCK)) < 0 ) {
		this->print_debug_info("set_pcm_driver() cannot open audio device [%s] : %s\n",this->device_name, snd_strerror(err));

		return false;
	}

	if( (err = snd_pcm_hw_params_any(this->t_pcm_handler, hw_params)) < 0 ) {
		this->print_debug_info("set_pcm_driver() cannot initialize hardware parameter structure : %s\n", snd_strerror(err));

		return false;
	}

	if( (err = snd_pcm_hw_params_set_access(this->t_pcm_handler, hw_params, SND_PCM_ACCESS_RW_INTERLEAVED)) < 0 ) {
		this->print_debug_info("set_pcm_driver() cannot set access type : %s\n", snd_strerror(err));

		return false;
	}

	if( (err = snd_pcm_hw_params_set_channels(this->t_pcm_handler, hw_params, this->channels)) < 0 ) {
		this->print_debug_info("set_pcm_driver() cannot set channel count : %s\n", snd_strerror(err));
		this->print_debug_info("set_pcm_driver() near set channel..\n");

		return false;
	}

	if( (err = snd_pcm_hw_params_set_format(this->t_pcm_handler, hw_params, format)) < 0 ) {
		this->print_debug_info("set_pcm_driver() cannot set sample format : %s\n", snd_strerror(err));

		return false; 
	}

	if( (err = snd_pcm_hw_params_set_rate(this->t_pcm_handler, hw_params, this->sample_rate, 0) ) < 0 ) {
	   this->print_debug_info("set_pcm_driver() cannot set near sample rate : %s\n", snd_strerror(err));

	   return false;
	}

	if( (err = snd_pcm_hw_params_set_periods(this->t_pcm_handler, hw_params, this->size_pcm_periods, 0)) < 0 ) {
		this->print_debug_info("set_pcm_driver() error setting periods : %s\n", snd_strerror(err));

		return false;
	}

	/* latency = periodsize * periods / (rate * bytes_per_frame) */
	// snd_pcm_uframes_t t_buffer_size = (this->chunk_size * this->size_pcm_periods) >> this->channels;
	snd_pcm_uframes_t t_buffer_size = this->chunk_size;
	
	this->print_debug_info("set_pcm_driver() s/w params set information - [buffer_size]\n");
	this->print_debug_info("set_pcm_driver() s/w params set - chunk size       : [%d]\n", this->chunk_size);
	this->print_debug_info("set_pcm_driver() s/w params set - size_pcm_periods : [%d]\n", this->size_pcm_periods);
	this->print_debug_info("set_pcm_driver() s/w params set - channels         : [%d]\n", this->channels);
	this->print_debug_info("set_pcm_driver() s/w params set - buffer_size      : [%d]\n", (int)t_buffer_size);
	
	if( (err = snd_pcm_hw_params_set_buffer_size_near(this->t_pcm_handler, hw_params, &t_buffer_size)) < 0 ) {
		this->print_debug_info("set_pcm_driver() Error setting buffersize [%d] : [%02d] %s\n", (int)t_buffer_size, err, snd_strerror(err));
		
		return false;
	}
	
	if( (err = snd_pcm_nonblock(this->t_pcm_handler, 0)) < 0 ) {
		this->print_debug_info("set_pcm_driver() nonblock failed : %s\n", snd_strerror(err));

		return false;
	}

	if( (err = snd_pcm_hw_params(this->t_pcm_handler, hw_params)) < 0 ) {
		this->print_debug_info("set_pcm_driver() cannot set parameters : %s\n", snd_strerror(err));

		return false;
	}

	snd_pcm_hw_params_get_channels(hw_params,    &this->channels);
	snd_pcm_hw_params_get_buffer_time(hw_params, &this->buffer_size, 0);
	snd_pcm_hw_params_get_period_time(hw_params, &this->period_size, 0);
	
	if( (err = snd_pcm_prepare(this->t_pcm_handler)) < 0 ) { 
		this->print_debug_info("set_pcm_driver() cannot prepare audio interface for use : %s\n", snd_strerror(err));

		return false;
	}

	this->frame_bytes   = snd_pcm_frames_to_bytes(this->t_pcm_handler, 1);
	this->frame_latency = (double)(this->chunk_size / this->frame_bytes) / this->sample_rate;
	
	this->print_debug_info("set_pcm_driver() h/w params set information..\n");
	this->print_debug_info("set_pcm_driver() h/w params set - chunk size      : [%d]\n", this->chunk_size);
	this->print_debug_info("set_pcm_driver() h/w params set - sample rate     : [%d]\n", this->sample_rate);
	this->print_debug_info("set_pcm_driver() h/w params set - channels        : [%d]\n", this->channels);
	this->print_debug_info("set_pcm_driver() h/w params set - pcm buffer size : [%d]\n", this->buffer_size);
	this->print_debug_info("set_pcm_driver() h/w params set - pcm period size : [%d]\n", this->period_size);
	this->print_debug_info("set_pcm_driver() h/w params set - pcm frame bytes : [%d]\n", this->frame_bytes);
	this->print_debug_info("set_pcm_driver() frame latency : [%lf]\n", this->frame_latency);

	return true;
}

void PCM_PlaybackHandler::execute(void) {
	int 	bytes;
	int		frame_size     = this->chunk_size / this->frame_bytes;
	int		frame_loop_cnt = (this->buffer_size / this->period_size) * 2;
	
	char	*feed_data = NULL;
	int		data_size  = this->chunk_size;
	
	tuple<char *, int> tp_data;
	HEADER_INFO_t t_header_info;
	int header_size = sizeof(HEADER_INFO_t);
	
	this->print_debug_info("execute() start playback\n");
	
	// buffer delay, feeding null data
	char *arr_delay_buffer = new char[this->chunk_size];
	memset(arr_delay_buffer, 0x00, this->chunk_size);
	
	int delay_frame_cnt = this->time_buffer_delay / (this->frame_latency * 1000);
	int num_drop_frame = NUM_DROP_SCALE / this->frame_latency;

	this->print_debug_info("execute() delay frame count : [%d]\n", delay_frame_cnt);
	this->print_debug_info("execute() feed loop count   : [%d]\n", frame_loop_cnt);
	this->print_debug_info("execute() frame latency     : [%lf] sec\n", this->frame_latency);
	this->print_debug_info("execute() num_drop_frame    : [%d]\n", num_drop_frame);

	int queue_cnt;
	int cnt_over_queue = 0;

	while( !this->is_loop ) {
		queue_cnt = (int)this->v_queue_data.size();

		if( this->is_debug_print ) {
			printf("[%d][%d] count : %-5d\r", this->sample_rate, this->channels, (int)this->v_queue_data.size());
		}

		if( queue_cnt <= delay_frame_cnt ) {
			feed_data = arr_delay_buffer;
			
		} else {
			tp_data = this->v_queue_data.front();
			
			this->v_queue_data.erase(this->v_queue_data.begin());
			feed_data = get<0>(tp_data);

			memcpy(&t_header_info, feed_data, sizeof(t_header_info));
			if( t_header_info.status == 0x00 ) {		// stop
				break;
			
			} else if( t_header_info.status == 0x01 ) { // play
				feed_data = feed_data + header_size;
				
			} else if( t_header_info.status == 0x02 ) {	// pause
				feed_data = arr_delay_buffer;
			}
		}

		if( this->playback_handle != NULL ) {
			this->playback_handle(&feed_data, &data_size);
		}
		
		if( this->control_handle != NULL ) {
			this->control_handle(&feed_data, &data_size);
		}

		if( (bytes = snd_pcm_writei(this->t_pcm_handler, feed_data, frame_size)) < 0 ) {
			switch( bytes ) {
				default :
					this->print_debug_info("execute() write to audio interface failed : [%02d] %s\n", bytes, snd_strerror(bytes));
					break;
			}

			if( !this->set_pcm_driver() ) {
				this->close_pcm_driver();
			}
		}

		// over queue 처리
		queue_cnt = (int)this->v_queue_data.size();
		if( queue_cnt > delay_frame_cnt + frame_loop_cnt ) {
			if( cnt_over_queue++ == num_drop_frame ) {
				cnt_over_queue = 0;
				
				this->v_queue_data.erase(this->v_queue_data.begin());
			}

		} else {
			cnt_over_queue = 0;
		}
	}
	
	if( this->mute_handle != NULL ) {
		this->mute_handle(false);
	}
	
	usleep(TIME_WAIT_MUTE);
	
	this->print_debug_info("execute() stop playback\n");
	
	this->v_queue_data.clear();
	delete arr_delay_buffer;
	
	if( this->t_pcm_handler != NULL ) {
		this->print_debug_info("stop() Free PCM device [%s]\n", this->device_name);

		snd_pcm_drop(this->t_pcm_handler);	
		snd_pcm_drain(this->t_pcm_handler);	
		snd_pcm_close(this->t_pcm_handler);
	}
	this->t_pcm_handler = NULL;
	
	if( this->term_handle != NULL ) {
		this->term_handle();
	}
	
	this->is_loop = true;
	this->is_init = false;
	this->is_run  = false;
	
	return ;
}

void PCM_PlaybackHandler::set_time_buffer_delay(int _time) {
	this->time_buffer_delay = _time;
	this->print_debug_info("set_time_buffer_delay() set delay time : [%d]\n", _time);
	
	return ;
}

int PCM_PlaybackHandler::get_play_info(string _type) {
	if( _type.compare("sample_rate") == 0 ) {
		return this->sample_rate;
				
	} else if( _type.compare("channels") == 0 ) {
		return this->channels;
	}
	return -1;
}

double PCM_PlaybackHandler::get_frame_latency(void) {

	return this->frame_latency;
}


void PCM_PlaybackHandler::set_playback_handler(void (*_func)(char **, int *)) {
	this->print_debug_info("set_playback_handler() set playback queue function\n");
	
	this->playback_handle = *_func;
	
	return ;
}

void PCM_PlaybackHandler::set_control_handler(void (*_func)(char **, int *)) {
	this->print_debug_info("set_control_handler() set playback control function\n");
	
	this->control_handle = *_func;
	
	return ;
}

void PCM_PlaybackHandler::set_mute_handler(void (*_func)(bool)) {
	this->print_debug_info("set_mute_handler() set playback control function\n");
	
	this->mute_handle = *_func;
	
	return ;
}

void PCM_PlaybackHandler::set_term_handler(void (*_func)(void)) {
	this->print_debug_info("set_term_handler() set playback control function\n");
	
	this->term_handle = *_func;
	
	return ;
}

void PCM_PlaybackHandler::reset_control_handler(void) {
	this->print_debug_info("reset_control_handler() reset playback control function\n");
	
	this->control_handle = NULL;
	
	return ;
}

void PCM_PlaybackHandler::set_data_handler(char *_data, int _size) {
	this->v_queue_data.push_back(make_tuple(_data, _size));
	
	return ;
}

bool PCM_PlaybackHandler::is_term(void) {
	
	return this->is_run;
}