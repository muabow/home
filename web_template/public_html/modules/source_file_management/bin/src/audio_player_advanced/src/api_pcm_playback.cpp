#include "api_pcm_playback.h"

PCM_PlaybackHandler::PCM_PlaybackHandler(bool _is_debug_print) {
	this->is_debug_print 	= _is_debug_print;
	
	this->playback_handle	= NULL;
	this->control_handle  	= NULL;
	this->mute_handle		= NULL;
	this->error_handle		= NULL;
	
	this->time_buffer_delay = 0;
	this->num_volume		= 0;
	
	this->print_debug_info("PCM_PlaybackHandler() create instance\n");

	memset(this->device_name, 0x00, sizeof(this->device_name));
	
	return ;
}

PCM_PlaybackHandler::~PCM_PlaybackHandler(void) {
	this->print_debug_info("PCM_PlaybackHandler() instance destructed : [%s]\n", this->device_name);
	
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

bool PCM_PlaybackHandler::init(string _device_name, int _chunk_size, int _sample_rate, int _channels, int _skip_bytes, int _audio_format, int _byte_per_sample, int _skip_end_bytes) {
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

	strcpy(this->device_name, _device_name.c_str());
	this->chunk_size		= _chunk_size;
	this->sample_rate		= _sample_rate;
	this->channels			= _channels;
	this->skip_bytes		= _skip_bytes;
	this->audio_format 		= _audio_format;
	this->byte_per_sample	= _byte_per_sample;
	this->skip_end_bytes	= _skip_end_bytes;
	
	
	this->print_debug_info("init() parameter information..\n");
	this->print_debug_info("init() parameter - deivce name      : [%s]\n", this->device_name);
	this->print_debug_info("init() parameter - sample rate      : [%d]\n", this->sample_rate);
	this->print_debug_info("init() parameter - channels         : [%d]\n", this->channels);
	this->print_debug_info("init() parameter - audio format     : [0x%04x]\n", this->audio_format);
	this->print_debug_info("init() parameter - byte per sample  : [%d]\n", this->byte_per_sample);
	this->print_debug_info("init() parameter - chunk size       : [%d]\n", this->chunk_size);
	this->print_debug_info("init() parameter - skip bytes       : [%d]\n", this->skip_bytes);
	this->print_debug_info("init() parameter - skip end bytes   : [%d]\n", this->skip_end_bytes);
	
	if( !this->set_pcm_driver() ) {
		this->print_debug_info("init() PCM device init failed [%s]\n", this->device_name);
		this->close_pcm_driver();

		return false;
	}

	this->print_debug_info("init() PCM device init success [%s]\n", this->device_name);
	
	this->is_init = true;
	
	return true;
}

void PCM_PlaybackHandler::stop(void) {
	this->print_debug_info("stop() playback loop disable\n");
	this->is_loop = true;
	
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

	// snd_pcm_format_t format = this->format;
	
	snd_pcm_format_t format;
	snd_pcm_stream_t stream = SND_PCM_STREAM_PLAYBACK;
	
	this->print_debug_info("set_pcm_driver() audio_format : [0x%04x]\n", this->audio_format);
	if( this->audio_format == MP3_FORMAT || this->audio_format == WAV_FORMAT_PCM ) {
		format = SND_PCM_FORMAT_S16_LE;
	
	} else if( this->audio_format == WAV_FORMAT_IEEE_FLOAT ) {
		format = SND_PCM_FORMAT_S16_LE;
		
	} else if( this->audio_format == WAV_FORMAT_ALAW ) {
		format = SND_PCM_FORMAT_A_LAW;
		
	} else if( this->audio_format == WAV_FORMAT_MULAW ) {
		format = SND_PCM_FORMAT_MU_LAW;
		
	} else if( this->audio_format == WAV_FORMAT_EXTENSIBLE ) {
		format = SND_PCM_FORMAT_IMA_ADPCM;
	}

	if( this->t_pcm_handler != NULL ) {
		this->print_debug_info("set_pcm_driver() resetting playback parameter\n");
		// snd_pcm_drain(this->t_pcm_handler);
		// usleep(PCM_PlaybackHandler::TIME_SLEEP_RESET);
		
		snd_pcm_close(this->t_pcm_handler);
		usleep(PCM_PlaybackHandler::TIME_SLEEP_RESET);
		
		this->t_pcm_handler = NULL;
		usleep(PCM_PlaybackHandler::TIME_SLEEP_RESET);
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

	/* stream only
	if( (err = snd_pcm_hw_params_set_periods(this->t_pcm_handler, hw_params, this->size_pcm_periods, 0)) < 0 ) {
		this->print_debug_info("set_pcm_driver() error setting periods : %s\n", snd_strerror(err));

		return false;
	}

	// latency = periodsize * periods / (rate * bytes_per_frame)
	snd_pcm_uframes_t t_buffer_size = (this->chunk_size * this->size_pcm_periods) >> this->channels;

	this->print_debug_info("set_pcm_driver() s/w params set information - [buffer_size]\n");
	this->print_debug_info("set_pcm_driver() s/w params set - chunk size       : [%d]\n", this->chunk_size);
	this->print_debug_info("set_pcm_driver() s/w params set - size_pcm_periods : [%d]\n", this->size_pcm_periods);
	this->print_debug_info("set_pcm_driver() s/w params set - channels         : [%d]\n", this->channels);
	this->print_debug_info("set_pcm_driver() s/w params set - buffer_size      : [%d]\n", (int)t_buffer_size);

	if( (err = snd_pcm_hw_params_set_buffer_size_near(this->t_pcm_handler, hw_params, &t_buffer_size)) < 0 ) {
		this->print_debug_info("set_pcm_driver() Error setting buffersize [%d] : [%02d] %s\n", (int)t_buffer_size, err, snd_strerror(err));
		
		return false;
	}
	*/
	snd_pcm_uframes_t buffer_size = this->chunk_size;
		
	snd_pcm_hw_params_set_period_size_near(this->t_pcm_handler, hw_params, &buffer_size, NULL);
	
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
	
	
	snd_pcm_sw_params_t *sw_params;

	snd_pcm_sw_params_malloc (&sw_params);
	snd_pcm_sw_params_current (this->t_pcm_handler, sw_params);
	
	snd_pcm_sw_params_set_start_threshold(this->t_pcm_handler, sw_params, buffer_size);
	snd_pcm_sw_params_set_avail_min(this->t_pcm_handler, sw_params, buffer_size);

	snd_pcm_sw_params(this->t_pcm_handler, sw_params);
	
	if( (err = snd_pcm_prepare(this->t_pcm_handler)) < 0 ) { 
		this->print_debug_info("set_pcm_driver() cannot prepare audio interface for use : %s\n", snd_strerror(err));

		return false;
	}

	this->frame_bytes   = snd_pcm_frames_to_bytes(this->t_pcm_handler, 1);
	this->frame_latency = (double)(this->chunk_size / this->channels) / this->frame_bytes / this->sample_rate;
	
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

void PCM_PlaybackHandler::run(string _file_path) {
	int 	bytes		= 0;
	int		frame_size	= 0;
	int		read_count	= 0;
	int		chunk_size	= 0;
	
	int		peak_volume	= 0;

	char	*feed_data 		= NULL;
	int		data_size  		= 0;
	int		frame_latency	= (this->frame_latency * 1000 * 1000) / this->frame_bytes;

	int 	sum_end_skip_size = 0;

	MP3_Decoder mp3_decoder(this->is_debug_print);
	
	tuple<char *, int *, int> t_decode_info;
	int	 *arr_decode_length = NULL;
	int  loop_count		= 1;
	int  decode_offset	= 0;
	
	string file_path = _file_path;
	
	string ext_type = file_path.substr(file_path.find_last_of(".") + 1);
	for_each(ext_type.begin(), ext_type.end(), [](char &_char) {
		_char = tolower(_char);
	});

	bool is_encoded = false;
	if( ext_type.compare("mp3") == 0 ) {
		is_encoded = true;
		chunk_size = mp3_decoder.get_decode_samples();
	
	} else {
		is_encoded = false;
		chunk_size = this->chunk_size;
	}
	
	char buffer[chunk_size];
	FILE *fp = fopen(_file_path.c_str(), "rb");
	
	fseek(fp, this->skip_bytes, SEEK_SET);
	this->print_debug_info("run() skip bytes : [%d]\n", this->skip_bytes);
	
	if( is_encoded ) {
		mp3_decoder.init(this->sample_rate, this->channels);
	}

	this->print_debug_info("run() start playback\n");
	this->print_debug_info("run() frame latency     : [%d] ms\n", frame_latency);
	
	this->is_run = true;

	bool is_skip_end_bytes = false;

	while( !this->is_loop && feof(fp) == 0 ) {
		if( this->is_pause ) {
			memset(buffer, 0x00, sizeof(buffer));

			feed_data   = buffer;
			data_size   = chunk_size;
			frame_size  = data_size / this->frame_bytes;
			peak_volume = 0;

			if( this->playback_handle != NULL ) {
				this->playback_handle(&feed_data, &data_size, is_encoded, peak_volume);
			}
			
			if( this->control_handle != NULL ) {
				this->control_handle(&feed_data, &data_size);
			}
			
			if( (bytes = snd_pcm_writei(this->t_pcm_handler, feed_data, frame_size)) < 0 ) {
				switch( bytes ) {
					default :
						this->print_debug_info("run() write to audio interface failed : [%02d] %s\n", bytes, snd_strerror(bytes));
						
						break;
				}
				this->set_pcm_driver();
			}
			
		} else {
			if( this->skip_end_bytes != 0 && (sum_end_skip_size + chunk_size) > this->skip_end_bytes ) {
				chunk_size = this->skip_end_bytes - sum_end_skip_size;
				this->print_debug_info("run() set skip end bytes : [%d] [%d/%d]\n", chunk_size, this->skip_end_bytes, sum_end_skip_size);
				is_skip_end_bytes = true;

			}

			read_count = fread(buffer, 1, chunk_size, fp);
			sum_end_skip_size += read_count;
			
			if( is_encoded ) {
				mp3_decoder.set_volume(this->num_volume);
				t_decode_info = mp3_decoder.decode(buffer, read_count);
				
				feed_data			= get<0>(t_decode_info);
				arr_decode_length	= get<1>(t_decode_info);
				loop_count			= get<2>(t_decode_info);
				
				if( loop_count < 0 ) {
					if( this->error_handle != NULL ) {
						this->error_handle();
					}
					
					break;
				}
				
			} else {
				feed_data  = buffer;			
				loop_count = 1;
			}

			decode_offset = 0;
			
			for( int idx = 0 ; idx < loop_count ; idx++ ) {
				if( is_encoded ) {
					data_size	= arr_decode_length[idx];
					peak_volume	= mp3_decoder.get_decode_peak_volume();
							
				} else {
					data_size	= read_count;
					peak_volume	= 0;
				}
				frame_size	= data_size / this->frame_bytes;
				
				if( this->playback_handle != NULL ) {
					this->playback_handle(&feed_data, &data_size, is_encoded, peak_volume);
				}
				
				if( this->control_handle != NULL ) {
					this->control_handle(&feed_data, &data_size);
				}
				
				if( (bytes = snd_pcm_writei(this->t_pcm_handler, feed_data + decode_offset, frame_size)) < 0 ) {
					switch( bytes ) {
						default :
							this->print_debug_info("run() write to audio interface failed : [%02d] %s\n", bytes, snd_strerror(bytes));
							
							break;
					}
					this->set_pcm_driver();
				}
				decode_offset += data_size;
			}

			if( is_skip_end_bytes ) {
				this->print_debug_info("run() set file fp seek end, break loop\n");
				break;
			}
		}
	}
	
	for( int idx = 0 ; idx < NUM_TERM_LOOP ; idx++ ) {
		if( this->is_loop ) break; 
		usleep(TIME_SLEEP_TERM);
	}
	
	this->print_debug_info("run() stop playback\n");
	fclose(fp);
	
	if( this->mute_handle != NULL ) {
		this->mute_handle();
	}

	if( this->t_pcm_handler != NULL ) {
		this->print_debug_info("run() Free PCM device [%s]\n", this->device_name);

		snd_pcm_drop(this->t_pcm_handler);	
		snd_pcm_drain(this->t_pcm_handler);	
		snd_pcm_close(this->t_pcm_handler);
	}
	this->t_pcm_handler = NULL;
	
	this->is_init = false;
	this->is_run  = false;
	this->is_loop = true;
	
	return ;
}

void PCM_PlaybackHandler::set_playback_pause(void) {
	this->print_debug_info("set_playback_pause() set playback : [pause]\n");
	this->is_pause = true;
	
	return ;
}

void PCM_PlaybackHandler::set_playback_play(void) {
	this->print_debug_info("set_playback_play() set playback : [play]\n");
	this->is_pause = false;
	
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


void PCM_PlaybackHandler::set_playback_handler(void (*_func)(char **, int *, bool, int)) {
	this->print_debug_info("set_playback_handler() set playback queue function\n");
	
	this->playback_handle = *_func;
	
	return ;
}

void PCM_PlaybackHandler::set_control_handler(void (*_func)(char **, int *)) {
	this->print_debug_info("set_control_handler() set playback control function\n");
	
	this->control_handle = *_func;
	
	return ;
}

void PCM_PlaybackHandler::reset_control_handler(void) {
	this->print_debug_info("reset_control_handler() reset playback control function\n");
	
	this->control_handle = NULL;
	
	return ;
}

void PCM_PlaybackHandler::set_mute_handler(void (*_func)(void)) {
	this->print_debug_info("set_mute_handler() set mute control function\n");
	
	this->mute_handle = *_func;
	
	return ;
}

void PCM_PlaybackHandler::set_error_handler(void (*_func)(void)) {
	this->print_debug_info("set_error_handler() set error control function\n");
	
	this->error_handle = *_func;
	
	return ;
}


void PCM_PlaybackHandler::set_decode_volume(int _volume) {
	if( this->num_volume != _volume ) {
		this->num_volume = _volume;
	}
	
	return ;
}