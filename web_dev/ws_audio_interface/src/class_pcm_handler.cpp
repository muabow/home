#include <stdio.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>
#include <alsa/asoundlib.h>

#include <iostream>
#include <thread>

#include "class_pcm_handler.h"

namespace COMMON {
	using namespace std;
	
	PCM_CaptureHandler::PCM_CaptureHandler(bool _is_debug_print) {
		this->is_debug_print = _is_debug_print;
		
		this->print_debug_info("PCM_CaptureHandler() create instance\n");
		
		memset(this->device_name, 0x00, sizeof(this->device_name));
		
		return ;
	}

	PCM_CaptureHandler::~PCM_CaptureHandler(void) {
		this->print_debug_info("PCM_CaptureHandler() instance destructed : [%s]\n", this->device_name);
		
		this->stop();

		return ;
	}
	
	void PCM_CaptureHandler::print_debug_info(const char *_format, ...) {
		if( !this->is_debug_print ) return ;
		
		va_list arg;
		
		fprintf(stderr, "PCM_CaptureHandler::");
		
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
			this->print_debug_info("init() already running \n");
			return false;
		}
		
		this->is_init		 = false;
		this->is_run		 = false;
		this->is_loop		 = false;
		this->chunk_data     = NULL;
		this->t_pcm_handler  = NULL;
		
		this->set_queue      = NULL;

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
			this->print_debug_info("init() change parameter [size_pcm_periods] : [%d] change to [%d] \n", this->size_pcm_periods, _size_pcm_periods);
			this->size_pcm_periods = _size_pcm_periods;
		}
		
		if( _time_div_periods != -1 ) {
			this->print_debug_info("init() change parameter [time_div_periods] : [%d] change to [%d] \n", this->time_div_periods, _time_div_periods);
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
			this->t_pcm_handler = NULL;

			return false;
		}

		this->print_debug_info("init() PCM device init success [%s]\n", this->device_name);
		
		this->chunk_data = (char *)malloc(this->chunk_size * sizeof(char));
		
		this->is_init = true;
		
		return true;
	}

	void PCM_CaptureHandler::stop(void) {
		if( !this->is_init ) {
			this->print_debug_info("stop() init is not ready\n");
			
			return ;
		}
		
		this->is_loop = true;
		
		if( this->t_pcm_handler != NULL ) {
			if( this->thread_func.joinable() ) {
				this->thread_func.join();
			}
			
			this->print_debug_info("stop() Free PCM device [%s]\n", this->device_name);

			snd_pcm_drop(this->t_pcm_handler);	
			snd_pcm_drain(this->t_pcm_handler);	
			snd_pcm_close(this->t_pcm_handler);
			
			this->t_pcm_handler = NULL;

			if( this->chunk_data != NULL ) {
				delete this->chunk_data;
				this->chunk_data = NULL;
			}
			
			this->set_queue = NULL;
		}

		return ;
	}

	void PCM_CaptureHandler::run(void) {
		if( !this->is_init ) {
			this->print_debug_info("run() init is not ready\n");
			
			return ;
		}
		
		if( this->is_run ) {
			this->print_debug_info("run() already running \n");
			return ;
		}
		this->is_run = true;
		
		this->print_debug_info("run() create execute thread \n");
		
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
			this->print_debug_info("set_pcm_driver() resetting capture parameter \n");
			snd_pcm_drain(this->t_pcm_handler);
			usleep(PCM_CaptureHandler::TIME_SLEEP_RESET);
			
			snd_pcm_close(this->t_pcm_handler);
			usleep(PCM_CaptureHandler::TIME_SLEEP_RESET);
			
			this->t_pcm_handler = NULL;
			usleep(PCM_CaptureHandler::TIME_SLEEP_RESET);
		}

		if( (err = snd_pcm_open(&this->t_pcm_handler, this->device_name, stream, SND_PCM_NONBLOCK)) < 0 ) {
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
		
		/* latency = periodsize * periods / (rate * bytes_per_frame) */
		if( snd_pcm_hw_params_set_buffer_size(this->t_pcm_handler, hw_params, (this->chunk_size * this->size_pcm_periods) >> this->channels) < 0 ) {
			this->print_debug_info("set_pcm_driver() Error setting buffersize.\n");
			
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

		snd_pcm_hw_params_get_channels(hw_params, 	 &this->channels);
		snd_pcm_hw_params_get_buffer_time(hw_params, &this->buffer_size, 0);
		snd_pcm_hw_params_get_period_time(hw_params, &this->period_size, 0);
		
		if( (err = snd_pcm_prepare(this->t_pcm_handler)) < 0 ) { 
			this->print_debug_info("set_pcm_driver() cannot prepare audio interface for use : %s\n", snd_strerror(err));

			return false;
		}

		this->frame_bytes = snd_pcm_frames_to_bytes(this->t_pcm_handler, 1);
		
		this->print_debug_info("set_pcm_driver() h/w params set information..\n");
		this->print_debug_info("set_pcm_driver() h/w params set - channels        : [%d]\n", this->channels);
		this->print_debug_info("set_pcm_driver() h/w params set - pcm buffer size : [%d]\n", this->buffer_size);
		this->print_debug_info("set_pcm_driver() h/w params set - pcm period size : [%d]\n", this->period_size);
		this->print_debug_info("set_pcm_driver() h/w params set - pcm frame bytes : [%d]\n", this->frame_bytes);

		return true;
	}

	void PCM_CaptureHandler::execute(void) {
		int 	err;
		int		frame_size;
		
		snd_pcm_sframes_t t_pcm_avail;
		// struct 	timeval before, after;	// 동작 시간 측정용
		
		this->print_debug_info("execute() start capture\n");
		
		while( !this->is_loop ) {
			// gettimeofday(&before , NULL);
			frame_size = this->chunk_size / this->frame_bytes;
			
			if( (t_pcm_avail = snd_pcm_avail(this->t_pcm_handler)) == -EPIPE ) {
				snd_pcm_prepare(this->t_pcm_handler);
				this->print_debug_info("execute() snd_pcm_prepare() prepared\n");
							
				snd_pcm_wait(this->t_pcm_handler, -1);
				this->print_debug_info("execute() snd_pcm_wait() waited\n");
			}
			
			if( (err = snd_pcm_readi(this->t_pcm_handler, this->chunk_data, frame_size)) < 0 ) {
				this->print_debug_info("execute() read from audio interface failed : [%02d] %s\n", err, snd_strerror(err));

				if( !this->set_pcm_driver() )	break;

				continue;
			}

			if( this->set_queue != NULL ) {
				this->set_queue(this->chunk_data, this->chunk_size);
			}
		}
		
		this->print_debug_info("execute() stop capture\n");
		
		return ;
	}

	unsigned int PCM_CaptureHandler::get_pcm_buffer_size(void) {

		return this->buffer_size;
	}

	unsigned int PCM_CaptureHandler::get_pcm_period_size(void) {

		return this->period_size;
	}
	
	
	void PCM_CaptureHandler::set_queue_handler(void (*_func)(char *, int)) {
		this->print_debug_info("PcmCaputure::set_queue_handler() set queue function\n");
		
		this->set_queue = *_func;
		
		return ;
	}
	
	// before init
	void PCM_CaptureHandler::set_pcm_period_size(int _size_pcm_periods) {
		this->print_debug_info("PcmCaputure::set_pcm_period_size() change PCM period size : [%d] -> [%d]\n", this->size_pcm_periods, _size_pcm_periods);
		
		this->size_pcm_periods = _size_pcm_periods;
		
		return ;
	}
	
	void PCM_CaptureHandler::set_pcm_div_period_time(int _time_div_periods) {
		this->print_debug_info("PcmCaputure::setPcmDeviceName() change PCM division period time : [%d] -> [%d]\n", this->time_div_periods, _time_div_periods);
		
		this->time_div_periods = _time_div_periods;
		
		return ;
	}
	
	
	/* ---------------------------------------------------------------------------------------------------------------------------------------------------------------- */

	
	PCM_PlaybackHandler::PCM_PlaybackHandler(bool _is_debug_print) {
		this->is_debug_print = _is_debug_print;
		
		this->get_queue      = NULL;
		
		this->print_debug_info("PCM_PlaybackHandler() create instance\n");

		memset(this->device_name, 0x00, sizeof(this->device_name));
		
		return ;
	}

	PCM_PlaybackHandler::~PCM_PlaybackHandler(void) {
		this->print_debug_info("PCM_PlaybackHandler() instance destructed : [%s]\n", this->device_name);
		
		this->stop();

		return ;
	}
	
	void PCM_PlaybackHandler::print_debug_info(const char *_format, ...) {
		if( !this->is_debug_print ) return ;
		
		va_list arg;
		
		fprintf(stderr, "PCM_PlaybackHandler::");
		
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
	
	bool PCM_PlaybackHandler::init(string _device_name, int _chunk_size, int _sample_rate, int _channels, int _size_pcm_periods, int _time_div_periods) {
		if( this->is_init ) {
			this->print_debug_info("init() already running \n");
			return false;
		}
		
		this->is_init		 = false;
		this->is_run		 = false;
		this->is_loop		 = false;
		this->chunk_data     = NULL;
		this->t_pcm_handler  = NULL;
		
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
			this->print_debug_info("init() change parameter [size_pcm_periods] : [%d] change to [%d] \n", this->size_pcm_periods, _size_pcm_periods);
			this->size_pcm_periods = _size_pcm_periods;
		}
		
		if( _time_div_periods != -1 ) {
			this->print_debug_info("init() change parameter [time_div_periods] : [%d] change to [%d] \n", this->time_div_periods, _time_div_periods);
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
			this->t_pcm_handler = NULL;

			return false;
		}

		this->print_debug_info("init() PCM device init success [%s]\n", this->device_name);
		
		this->is_init = true;
		
		return true;
	}

	void PCM_PlaybackHandler::stop(void) {
		if( !this->is_init ) {
			this->print_debug_info("stop() init is not ready\n");
			
			return ;
		}
		
		this->is_loop = true;
		
		if( this->t_pcm_handler != NULL ) {
			if( this->thread_func.joinable() ) {
				this->thread_func.join();
			}
			
			this->print_debug_info("stop() Free PCM device [%s]\n", this->device_name);

			snd_pcm_drop(this->t_pcm_handler);	
			snd_pcm_drain(this->t_pcm_handler);	
			snd_pcm_close(this->t_pcm_handler);
			
			this->t_pcm_handler = NULL;
		}
		
		this->is_init = false;
		
		return ;
	}

	void PCM_PlaybackHandler::run(void) {
		if( !this->is_init ) {
			this->print_debug_info("run() init is not ready\n");
			
			return ;
		}
		
		if( this->is_run ) {
			this->print_debug_info("run() already running \n");
			return ;
		}
		this->is_run = true;
		
		this->print_debug_info("run() create execute thread \n");
		
		this->thread_func = thread(&PCM_PlaybackHandler::execute, this);

		return ;
	}
	
	bool PCM_PlaybackHandler::set_pcm_driver(void) {
		int 	err;

		snd_pcm_hw_params_t *hw_params = NULL;
		snd_pcm_hw_params_alloca(&hw_params);

		snd_pcm_format_t format = SND_PCM_FORMAT_S16_LE;
	    snd_pcm_stream_t stream = SND_PCM_STREAM_PLAYBACK;

		if( this->t_pcm_handler != NULL ) {
			this->print_debug_info("set_pcm_driver() resetting playback parameter \n");
			snd_pcm_drain(this->t_pcm_handler);
			usleep(PCM_PlaybackHandler::TIME_SLEEP_RESET);
			
			snd_pcm_close(this->t_pcm_handler);
			usleep(PCM_PlaybackHandler::TIME_SLEEP_RESET);
			
			this->t_pcm_handler = NULL;
			usleep(PCM_PlaybackHandler::TIME_SLEEP_RESET);
		}

		if( (err = snd_pcm_open(&this->t_pcm_handler, this->device_name, stream, SND_PCM_NONBLOCK)) < 0 ) {
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

		/* latency = periodsize * periods / (rate * bytes_per_frame) */
		if( snd_pcm_hw_params_set_buffer_size(this->t_pcm_handler, hw_params, (this->chunk_size * this->size_pcm_periods) >> this->channels) < 0 ) {
			this->print_debug_info("set_pcm_driver() Error setting buffersize.\n");
			
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

		this->frame_bytes = snd_pcm_frames_to_bytes(this->t_pcm_handler, 1);
		
		this->print_debug_info("set_pcm_driver() h/w params set information..\n");
		this->print_debug_info("set_pcm_driver() h/w params set - channels        : [%d]\n", this->channels);
		this->print_debug_info("set_pcm_driver() h/w params set - pcm buffer size : [%d]\n", this->buffer_size);
		this->print_debug_info("set_pcm_driver() h/w params set - pcm period size : [%d]\n", this->period_size);
		this->print_debug_info("set_pcm_driver() h/w params set - pcm frame bytes : [%d]\n", this->frame_bytes);

		return true;
	}
	
	void PCM_PlaybackHandler::execute(void) {
		int 	bytes;
		int		frame_size;
		int		chunk_size  = this->chunk_size;
		short	*feed_data  = (short *)new char[chunk_size];
		
		memset(feed_data, 0x00, this->chunk_size);
		
		snd_pcm_sframes_t t_pcm_avail;
		// struct 	timeval before, after;	// 동작 시간 측정용
		
		this->print_debug_info("execute() start capture\n");
		
		
		while( !this->is_loop ) {
			// gettimeofday(&before , NULL);
			
			if( this->get_queue != NULL ) {
				this->get_queue(&this->chunk_data, &chunk_size);
			}
			
			if( this->chunk_data == NULL ) {
				this->chunk_data = feed_data;
				chunk_size       = this->chunk_size;
				this->print_debug_info("execute() feed null data\n");
			}
			frame_size = this->chunk_size / this->frame_bytes;
			 
			if( (t_pcm_avail = snd_pcm_avail(this->t_pcm_handler)) == -EPIPE ) {
				snd_pcm_prepare(this->t_pcm_handler);
				this->print_debug_info("execute() snd_pcm_prepare() prepared\n");
							
				snd_pcm_wait(this->t_pcm_handler, -1);
				this->print_debug_info("execute() snd_pcm_wait() waited\n");
			}
			
			if( (bytes = snd_pcm_writei(this->t_pcm_handler, (short *)this->chunk_data, frame_size)) < 0 ) {
				this->print_debug_info("execute() write to audio interface failed : [%02d] %s\n", bytes, snd_strerror(bytes));

				if( !this->set_pcm_driver() ) break;

				continue;
			}
		}
		
		delete feed_data;
		
		this->print_debug_info("execute() stop capture\n");
		
		return ;
	}
	
	void PCM_PlaybackHandler::get_queue_handler(void (*_func)(short **, int *)) {
		this->print_debug_info("get_queue_handler() get queue function\n");
		
		this->get_queue = *_func;
		
		return ;
	}
}
