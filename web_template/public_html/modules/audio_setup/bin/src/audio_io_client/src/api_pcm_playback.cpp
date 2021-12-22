#include "api_pcm_playback.h"


PCM_PlaybackHandler::PCM_PlaybackHandler(bool _is_debug_print) {
	this->is_debug_print = _is_debug_print;
	this->playback_handle  = NULL;
	this->control_handle   = NULL;
	this->arr_delay_buffer = NULL;

	this->time_buffer_delay = 0;
	this->is_imx_device = this->get_device_type();
	
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

	if( this->arr_delay_buffer != NULL ) {
		delete this->arr_delay_buffer;
		this->arr_delay_buffer = NULL;
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

	this->is_exec_playback = false; 

	
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
	
	if( this->is_imx_device ) {
		this->print_debug_info("set_pcm_driver() change device type [I.MX]\n");
		this->size_pcm_periods = SIZE_IMX_PERIODS;
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
	
	this->li_queue_data = queue<tuple<char *, int>>();
	
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
	// snd_pcm_uframes_t t_buffer_size = (this->chunk_size * this->size_pcm_periods);
	
	snd_pcm_uframes_t t_buffer_size = (this->chunk_size / this->channels * this->size_pcm_periods) >> 2;
	if( (err = snd_pcm_hw_params_set_buffer_size_near(this->t_pcm_handler, hw_params, &t_buffer_size)) < 0 ) {
        this->print_debug_info("set_pcm_driver() buffer size failed : %s\n", snd_strerror(err));

		return false;
    }

	this->print_debug_info("set_pcm_driver() s/w params set information - [buffer_size]\n");
	this->print_debug_info("set_pcm_driver() s/w params set - chunk size       : [%d]\n", this->chunk_size);
	this->print_debug_info("set_pcm_driver() s/w params set - size_pcm_periods : [%d]\n", this->size_pcm_periods);
	this->print_debug_info("set_pcm_driver() s/w params set - channels         : [%d]\n", this->channels);
	this->print_debug_info("set_pcm_driver() s/w params set - buffer_size      : [%d]\n", (int)t_buffer_size);

	if( (err = snd_pcm_nonblock(this->t_pcm_handler, 0)) < 0 ) {
		this->print_debug_info("set_pcm_driver() nonblock failed : %s\n", snd_strerror(err));

		return false;
	}

	if( (err = snd_pcm_hw_params(this->t_pcm_handler, hw_params)) < 0 ) {
		this->print_debug_info("set_pcm_driver() cannot set parameters : %s\n", snd_strerror(err));

		return false;
	}

	snd_pcm_sw_params_t *sw_params = NULL;
	snd_pcm_sw_params_alloca(&sw_params);

	 /* get the current swparams */
    if( (err = snd_pcm_sw_params_current(this->t_pcm_handler, sw_params)) < 0 ) {
		this->print_debug_info("set_pcm_driver() Unable to determine current swparams for playback : %s\n", snd_strerror(err));
		
		return false;
    }
	
    /* start the transfer when the buffer is almost full: */
    /* (buffer_size / avail_min) * avail_min */
    if( (err = snd_pcm_sw_params_set_start_threshold(this->t_pcm_handler, sw_params, t_buffer_size)) < 0 ) {
		this->print_debug_info("set_pcm_driver() Unable to set start threshold mode for playback : %s\n", snd_strerror(err));

		return false;
	}
	
	if( (err = snd_pcm_sw_params(this->t_pcm_handler, sw_params)) < 0 ) {
		this->print_debug_info("set_pcm_driver() Unable to set sw params for playback : %s\n", snd_strerror(err));

		return false;
	}

	if( (err = snd_pcm_prepare(this->t_pcm_handler)) < 0 ) { 
		this->print_debug_info("set_pcm_driver() cannot prepare audio interface for use : %s\n", snd_strerror(err));

		return false;
	}

	snd_pcm_hw_params_get_channels(hw_params,    &this->channels);
	snd_pcm_hw_params_get_buffer_time(hw_params, &this->buffer_size, 0);
	snd_pcm_hw_params_get_period_time(hw_params, &this->period_size, 0);
	
	snd_pcm_hw_params_get_buffer_size(hw_params, &this->t_info_buffer_size);
	snd_pcm_hw_params_get_period_size(hw_params, &this->t_info_period_size, 0);

	this->frame_bytes   = snd_pcm_frames_to_bytes(this->t_pcm_handler, 1);
	this->frame_latency = (double)(this->chunk_size / this->frame_bytes) / this->sample_rate;
	
	this->print_debug_info("set_pcm_driver() h/w params set information..\n");
	this->print_debug_info("set_pcm_driver() h/w params set - chunk size      : [%d]\n", this->chunk_size);
	this->print_debug_info("set_pcm_driver() h/w params set - sample rate     : [%d]\n", this->sample_rate);
	this->print_debug_info("set_pcm_driver() h/w params set - channels        : [%d]\n", this->channels);
	this->print_debug_info("set_pcm_driver() h/w params set - pcm buffer time : [%d]\n", this->buffer_size);
	this->print_debug_info("set_pcm_driver() h/w params set - pcm period time : [%d]\n", this->period_size);
	this->print_debug_info("set_pcm_driver() h/w params set - pcm buffer size : [%d]\n", this->t_info_buffer_size);
	this->print_debug_info("set_pcm_driver() h/w params set - pcm period size : [%d]\n", this->t_info_period_size);
	this->print_debug_info("set_pcm_driver() h/w params set - pcm frame bytes : [%d]\n", this->frame_bytes);
	this->print_debug_info("set_pcm_driver() frame latency : [%lf]\n", this->frame_latency);

	return true;
}

void PCM_PlaybackHandler::execute(void) {
	bool	is_print_interval = true;

	int 	bytes;
	int		num_frame_size = 0; 
	int		num_frame_loop = (int)this->t_info_buffer_size / (int)this->t_info_period_size;

	char	*ptr_feed_data = NULL;
	int		num_data_size  = 0;
	int 	num_queue_cnt  = 0;
	
	if( this->is_encode_status() ) {
		num_frame_loop *= 2;	// mp3 encode queue 보정
	}

	tuple<char *, int> tp_data;

	int num_frame_delay = this->time_buffer_delay / (this->frame_latency * 1000);
	num_frame_loop += num_frame_delay; 

	this->print_debug_info("execute() start playback\n");
	this->print_debug_info("execute() frame latency     : [%lf] sec\n", this->frame_latency);
	this->print_debug_info("execute() delay frame count : [%d]\n", num_frame_delay);
	this->print_debug_info("execute() frame loop count  : [%d]\n", num_frame_loop);
	this->print_debug_info("execute() time buffer delay : [%d]\n", this->time_buffer_delay);
	this->print_debug_info("execute() info period size  : [%d]\n", (int)this->t_info_period_size);

	char null_data[(int)this->t_info_period_size] = {0x00, };
	int  num_over_queue = 0;

	// int num_buffer_cnt = (100 / (this->frame_latency * 1000) + 1);
	int num_buffer_cnt = this->size_pcm_periods + 1;
	//this->print_debug_info("execute() buffer store count: [%d]\n", num_buffer_cnt);
	
	system_clock::time_point t_time_begin, t_time_end;
	milliseconds t_diff_msec;

	// delay 설정 시 동작
	while( !this->is_loop ) {
		num_queue_cnt = (int)this->li_queue_data.size();
		if( num_queue_cnt > (num_frame_delay + num_buffer_cnt) ) {
			break;
		}
		usleep(1000);
	}
	
	// playback loop 
	int num_limit_queue = ((100 * 1000) / (this->frame_latency * 1000));	// 10s
	this->print_debug_info("execute() queue limit count : [%d]\n", num_limit_queue);
	while( !this->is_loop ) {
		num_queue_cnt = this->li_queue_data.size();
		
		if( num_queue_cnt > num_frame_loop ) { // num_frame_loop
			if( num_over_queue++ > num_limit_queue ) {
				tp_data = this->li_queue_data.front();
				this->li_queue_data.pop();

				num_over_queue = 0;
				num_queue_cnt = this->li_queue_data.size();
			
				this->print_debug_info("execute() queue feed over queue : [%d] %40s\n", num_queue_cnt, "");
			}
		
		} else {
			num_over_queue = 0;
		}

		// queue 가 없을 경우 null data feeding
		if ( num_queue_cnt == 0 ) {
			// this->print_debug_info("execute() queue feed null : [%d] %40s\n", num_queue_cnt, "");

			ptr_feed_data = null_data;
			num_data_size = (int)this->t_info_period_size;
			usleep(1000);
			continue;

		} else {
			tp_data = this->li_queue_data.front();
			
			ptr_feed_data = get<0>(tp_data);
			num_data_size = get<1>(tp_data);

			this->li_queue_data.pop();
		}

		num_frame_size = num_data_size / this->frame_bytes;
	
		if( is_print_interval ) t_time_begin = system_clock::now();

		if( (bytes = snd_pcm_writei(this->t_pcm_handler, ptr_feed_data, num_frame_size)) < 0 ) {
			this->print_debug_info("execute() write to audio interface failed : [%02d] %s\n", bytes, snd_strerror(bytes));

			while( !this->is_loop ) {
				num_queue_cnt = (int)this->li_queue_data.size();
				if( num_queue_cnt > (num_frame_delay + num_buffer_cnt) ) {
					break;
				}
				usleep(1000);
			}
			this->print_debug_info("execute() buffering feed data\n");
		
			this->print_debug_info("execute() set_pcm_driver() resetting device\n");
			if( !this->set_pcm_driver() ) {
				this->close_pcm_driver();
				break;
			}

			continue;
		}
		
		this->playback_handle(ptr_feed_data, num_data_size);

		if( is_print_interval ) t_time_end = system_clock::now();
	    if( is_print_interval ) t_diff_msec = duration_cast<milliseconds>(t_time_end - t_time_begin);
    	
		if( is_print_interval ) this->print_debug_info("recording interval [%d/%d]: %3d ms, [%d] %10s\r", num_over_queue, num_limit_queue, (int)t_diff_msec.count(), num_queue_cnt, "");
	}
	
	this->print_debug_info("execute() stop playback\n");
	
	this->is_run = false;
	
	return ;
}

void PCM_PlaybackHandler::write_pcm(char *_data, int _size) {
	int bytes;
	int num_frame_size = _size / this->frame_bytes;
	if( (bytes = snd_pcm_writei(this->t_pcm_handler, _data, num_frame_size)) < 0 ) {
		this->print_debug_info("write_pcm() write to audio interface failed : [%02d] %s\n", bytes, snd_strerror(bytes));

		if( !this->set_pcm_driver() ) {
			this->close_pcm_driver();
		}
	}

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


void PCM_PlaybackHandler::set_playback_handler(void (*_func)(char *, int)) {
	this->print_debug_info("set_playback_handler() set playback queue function\n");
	
	this->playback_handle = *_func;
	
	return ;
}

void PCM_PlaybackHandler::set_control_handler(void (*_func)(void)) {
	this->print_debug_info("set_control_handler() set playback control function\n");
	
	this->control_handle = *_func;
	
	return ;
}

void PCM_PlaybackHandler::reset_control_handler(void) {
	this->print_debug_info("reset_control_handler() reset playback control function\n");
	
	this->control_handle = NULL;
	
	return ;
}

void PCM_PlaybackHandler::set_data_handler(char *_data, int _size) {
	if( !this->is_exec_playback ) {
		this->is_exec_playback = true;

		if( this->time_buffer_delay != 0 ) {
			// buffer delay, feeding null data
			if( this->arr_delay_buffer != NULL ) {
				delete this->arr_delay_buffer;
				this->arr_delay_buffer = NULL;
			}
			this->arr_delay_buffer = new char[this->chunk_size];
			memset(this->arr_delay_buffer, 0x00, this->chunk_size);
			
			int num_frame_delay = this->time_buffer_delay / (this->frame_latency * 1000);
			this->print_debug_info("set_data_handler() playback first time, feeding delay buffer count [%d]\n", num_frame_delay);
			
			for( int idx = 0 ; idx < num_frame_delay ; idx++ ) {
				this->li_queue_data.push(make_tuple(this->arr_delay_buffer, this->chunk_size));
			}
		}
	}

	this->li_queue_data.push(make_tuple(_data, _size));
	
	return ;
}

bool PCM_PlaybackHandler::get_device_type(void) {
	char buffer[128];

	FILE* pipe = popen("cat /proc/cpuinfo | grep Hardware | grep Freescale | wc -l", "r");
 	fgets(buffer, sizeof(buffer), pipe);
	pclose(pipe);

	if( stoi(buffer) == 0 ) {
		return false;

	} else {
		return true;
	}
}

bool PCM_PlaybackHandler::is_set_delay_time(void) {
	
	return (this->time_buffer_delay == 0 ? false : true);
}

void PCM_PlaybackHandler::set_encode_status(bool _is_status) {
	this->is_encoded = _is_status;
	return ;
}

bool PCM_PlaybackHandler::is_encode_status(void) {
	return this->is_encoded;
}