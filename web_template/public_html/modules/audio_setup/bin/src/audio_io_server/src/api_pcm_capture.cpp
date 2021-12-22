#include "api_pcm_capture.h"

PCM_CaptureHandler::PCM_CaptureHandler(bool _is_debug_print) {
	this->is_debug_print = _is_debug_print;
	this->set_queue      = NULL;

	this->frame_latency	 = -1;
	
	memset(this->device_name, 0x00, sizeof(this->device_name));
	
	this->print_debug_info("PCM_CaptureHandler() create instance\n");
	
	return ;
}

PCM_CaptureHandler::~PCM_CaptureHandler(void) {
	this->print_debug_info("PCM_CaptureHandler() instance destructed : [%s]\n", this->device_name);
	
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

void PCM_CaptureHandler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	fprintf(stdout, "PCM_CaptureHandler::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	return ;
}

void PCM_CaptureHandler::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

bool PCM_CaptureHandler::init(string _device_name, int _chunk_size, int _sample_rate, int _channels, int _size_pcm_periods, int _time_div_periods) {
	if( this->is_init ) {
		this->print_debug_info("init() alreay init\n");
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

	bool is_device_imx = this->is_device_imx();
	if( is_device_imx ) {
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
		this->print_debug_info("init() Free PCM device [%s]\n", this->device_name);

		snd_pcm_drop(this->t_pcm_handler);	
		snd_pcm_drain(this->t_pcm_handler);	
		snd_pcm_close(this->t_pcm_handler);

		this->t_pcm_handler = NULL;

		return false;
	}

	this->print_debug_info("init() PCM device init success [%s]\n", this->device_name);
	
	this->is_init = true;
	
	return true;
}

void PCM_CaptureHandler::stop(void) {
	if( !this->is_run ) {
		this->print_debug_info("stop() capture is not running\n");
		
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

void PCM_CaptureHandler::run(void) {
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
	this->thread_func = thread(&PCM_CaptureHandler::execute, this);

	return ;
}

bool PCM_CaptureHandler::set_pcm_driver(void) {
	int 	err;

	snd_pcm_hw_params_t *hw_params = NULL;
	snd_pcm_hw_params_alloca(&hw_params);

	snd_pcm_format_t format = SND_PCM_FORMAT_S16_LE;
	snd_pcm_stream_t stream = SND_PCM_STREAM_CAPTURE;

	if( this->t_pcm_handler != NULL ) {
		this->print_debug_info("set_pcm_driver() resetting capture parameter\n");
		/*
		snd_pcm_drop(this->t_pcm_handler);
		
		snd_pcm_drain(this->t_pcm_handler);
		usleep(PCM_CaptureHandler::TIME_SLEEP_RESET);
		*/
		snd_pcm_close(this->t_pcm_handler);
		this->t_pcm_handler = NULL;
	}

	if( (err = snd_pcm_open(&this->t_pcm_handler, this->device_name, stream, 0)) < 0 ) {
		this->print_debug_info("set_pcm_driver() cannot open audio device [%s] : %s\n",this->device_name , snd_strerror(err));

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

	if( (err = snd_pcm_hw_params_set_rate(this->t_pcm_handler, hw_params, this->sample_rate, 0) ) < 0) {
	   this->print_debug_info("set_pcm_driver() cannot set near sample rate : %s\n", snd_strerror(err));

	   return false;
	}


	if( (err = snd_pcm_hw_params_set_periods(this->t_pcm_handler, hw_params, this->size_pcm_periods, 0)) < 0) {
		this->print_debug_info("set_pcm_driver() error setting periods : %s\n", snd_strerror(err));

		return false;
	}

	if( (err = snd_pcm_nonblock(this->t_pcm_handler, 0)) < 0 ) {
		this->print_debug_info("set_pcm_driver() nonblock failed : %s\n", snd_strerror(err));

		return false;
	}

	snd_pcm_uframes_t t_buffer_size = (this->chunk_size / this->channels * this->size_pcm_periods) >> 2;
	if( (err = snd_pcm_hw_params_set_buffer_size_near(this->t_pcm_handler, hw_params, &t_buffer_size)) < 0 ) {
		this->print_debug_info("set_pcm_driver() buffer size failed : %s\n", snd_strerror(err));
		
		return false;
	}

	if( (err = snd_pcm_hw_params(this->t_pcm_handler, hw_params)) < 0 ) {
		this->print_debug_info("set_pcm_driver() cannot set parameters : %s\n", snd_strerror(err));

		return false;
	}

	if( (err = snd_pcm_prepare(this->t_pcm_handler)) < 0 ) { 
		this->print_debug_info("set_pcm_driver() cannot prepare audio interface for use : %s\n", snd_strerror(err));

		return false;
	}

	snd_pcm_hw_params_get_channels(hw_params, 	 &this->channels);
	snd_pcm_hw_params_get_buffer_time(hw_params, &this->buffer_size, 0);
	snd_pcm_hw_params_get_period_time(hw_params, &this->period_size, 0);
	
	snd_pcm_uframes_t t_info_buffer_size, t_info_period_size;
	snd_pcm_hw_params_get_buffer_size(hw_params, &t_info_buffer_size);
	snd_pcm_hw_params_get_period_size(hw_params, &t_info_period_size, 0);

	this->frame_bytes 	= snd_pcm_frames_to_bytes(this->t_pcm_handler, 1);
	this->frame_latency = (double)(this->chunk_size / this->channels) / this->frame_bytes / this->sample_rate;
	
	this->print_debug_info("set_pcm_driver() h/w params set information..\n");
	this->print_debug_info("set_pcm_driver() h/w params set - sample rate     : [%d]\n", this->sample_rate);
	this->print_debug_info("set_pcm_driver() h/w params set - channels        : [%d]\n", this->channels);
	this->print_debug_info("set_pcm_driver() h/w params set - pcm buffer time : [%d]\n", this->buffer_size);
	this->print_debug_info("set_pcm_driver() h/w params set - pcm period time : [%d]\n", this->period_size);
	this->print_debug_info("set_pcm_driver() h/w params set - pcm buffer size : [%d]\n", t_info_buffer_size);
	this->print_debug_info("set_pcm_driver() h/w params set - pcm period size : [%d]\n", t_info_period_size);
	this->print_debug_info("set_pcm_driver() h/w params set - pcm frame bytes : [%d]\n", this->frame_bytes);
	this->print_debug_info("set_pcm_driver() h/w params set - frame latency   : [%lf]\n",this->frame_latency);
	this->print_debug_info("set_pcm_driver() h/w params set - pcm width       : [%d]\n", snd_pcm_format_width(SND_PCM_FORMAT_S16_LE));

	return true;
}

void PCM_CaptureHandler::execute(void) {
	bool	is_print_interval = false;
	
	int 	err;
	int		frame_size = this->chunk_size / this->frame_bytes;
	char	chunk_data[this->chunk_size] = {0x00, };

	system_clock::time_point t_time_begin, t_time_end;
	milliseconds t_diff_msec;
	
	this->print_debug_info("execute() start capture\n");

	while( !this->is_loop ) {
		if( is_print_interval ) t_time_begin = system_clock::now();

		if( (err = snd_pcm_readi(this->t_pcm_handler, chunk_data, frame_size)) < 0 ) {
			this->print_debug_info("execute() read from audio interface failed : [%02d] %s\n", err, snd_strerror(err));

			if( !this->set_pcm_driver() ) break;
		
			continue;
		}

	    if( is_print_interval ) t_time_end = system_clock::now();
	    if( is_print_interval ) t_diff_msec = duration_cast<milliseconds>(t_time_end - t_time_begin);
    	
		if( is_print_interval ) this->print_debug_info("recording interval : %3d ms, read: %d \r", (int)t_diff_msec.count(), (int)err);
		
		if( this->set_queue != NULL ) {
			this->set_queue(chunk_data, this->chunk_size);
		}
	}
	
	this->print_debug_info("execute() stop capture\n");
	this->is_run = false;
	
	return ;
}

unsigned int PCM_CaptureHandler::get_pcm_buffer_size(void) {

	return this->buffer_size;
}

unsigned int PCM_CaptureHandler::get_pcm_period_size(void) {

	return this->period_size;
}

double	PCM_CaptureHandler::get_frame_latency(void) {
	
	return this->frame_latency;
}

int	PCM_CaptureHandler::get_sample_rate(void) {
	
	return this->sample_rate;
}

int PCM_CaptureHandler::get_channels(void) {
	
	return this->channels;
}
int PCM_CaptureHandler::get_chunk_size(void) {
	if( this->chunk_size == 4608 ) {
		// send to client, mp3 frame size
		return 1152;
	}
	
	return this->chunk_size;
}

void PCM_CaptureHandler::set_queue_handler(void (*_func)(char *, int)) {
	this->print_debug_info("set_queue_handler() set queue function\n");
	
	this->set_queue = *_func;
	
	return ;
}

int PCM_CaptureHandler::get_pcm_periods(void) {

	return SIZE_PCM_PERIODS;
}


bool PCM_CaptureHandler::is_device_imx(void) {
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