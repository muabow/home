#include "api_mp3_decode.h"

MP3_Decoder::MP3_Decoder(bool _is_debug_print) {
	this->is_debug_print = _is_debug_print;
	
	this->is_init			= false;
	this->is_reset			= false;
	this->is_error			= false;
	
	this->num_sample_rate	= -1;
	this->num_channels		= -1;
	
	this->decoder			= NULL;
	this->lame_error_handle = NULL;
	
	this->num_volume		= 100;
	this->peak_volume		= 0;

	this->print_debug_info("MP3_Decoder() create instance\n");
	
	return ;
}

MP3_Decoder::~MP3_Decoder(void) {
	this->print_debug_info("MP3_Decoder() instance destructed\n");

	if( this->decoder != NULL ) {
		hip_decode_exit(this->decoder);
		this->decoder = NULL;
	}
	
	this->is_init = false;
	
	return ;
}

void MP3_Decoder::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	printf("\033[31m");
	
	fprintf(stdout, "MP3_Decoder::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	printf("\033[0m");
	
	return ;
}

void MP3_Decoder::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

void MP3_Decoder::set_report_handler(void (*_func)(const char *, va_list)) {
	this->print_debug_info("set_report_handler() set event function\n");
	this->lame_error_handle = _func;
	
	hip_set_errorf(this->decoder, this->lame_error_handle);
	hip_set_debugf(this->decoder, this->lame_error_handle);
	hip_set_msgf(this->decoder,   this->lame_error_handle);
	
	return ;
}

void MP3_Decoder::init(int _sample_rate, int _channels) {
	if( this->is_init ) {
		this->print_debug_info("init() alreay init\n");
		return ;
	}
	
	this->is_init			= false;
	this->is_reset			= false;
	this->is_error			= false;
	this->is_decoding		= false;
	
	this->decoder			= NULL;
	this->lame_error_handle = NULL;
	
	this->num_volume		= 100;
	this->peak_volume		= 0;
	
	if( _sample_rate != -1 ) {
		this->num_sample_rate 	= _sample_rate;
		this->print_debug_info("init() decode parameter set - sample rate : [%d] \n", this->num_sample_rate);
	}
	
	if( _channels != -1 ) {
		this->num_channels		= _channels;
		this->print_debug_info("init() decode parameter set - channels    : [%s] \n", (this->num_channels == NUM_CHANNEL_MONO ? "MONO" : "JOINT_STEREO"));
	}
	
	if( this->decoder != NULL ) {
		hip_decode_exit(this->decoder);
		this->decoder = NULL;
	}

	this->decoder = hip_decode_init();

	this->print_debug_info("init() init success \n");
	
	this->is_init = true;
	
	return ;
}

void MP3_Decoder::stop(void) {
	if( this->is_decoding ) {
		this->print_debug_info("stop() waiting decoding termed\n");
	}
	while( this->is_decoding ) {
		usleep(10);
	}

	if( this->decoder != NULL ) {
		hip_decode_exit(this->decoder);
		this->decoder = NULL;
	}
	
	this->is_init = false;
	this->print_debug_info("stop() decoder stopped\n");
	
	return ;
}

int MP3_Decoder::get_decoded_sample(void) {
	
	return (SIZE_DECODE_SAMPLES * this->num_channels) * sizeof(short);
}

int MP3_Decoder::get_error_skip_cnt(void) {
	
	return NUM_ERROR_SKIP_CNT;
}

int MP3_Decoder::get_error_buffer_size(void) {
	
	return SIZE_DECODED_PCM;
}


bool MP3_Decoder::is_reset_status(void) {
	
	return this->is_reset;
}

void MP3_Decoder::set_reset_status(bool _status) {
	this->is_reset = _status;
	
	return ;
}

void MP3_Decoder::set_volume(int _volume) {
	if( this->num_volume != _volume ) {
		this->num_volume = _volume;
	}
	
	return ;
}

int MP3_Decoder::get_decode_samples(void) {
	
	return SIZE_DECODE_SAMPLES;
}

int MP3_Decoder::get_decode_peak_volume(void) {
	
	return this->peak_volume;
}

tuple<char *, int *, int> MP3_Decoder::decode(char *_input_data, int _data_size) {
	this->is_decoding = false;

	if( !this->is_init ) {
		this->print_debug_info("decode() is not running\n");
		
		return make_tuple((char *)NULL, this->arr_pcm_length, 0);
	}
	
	if( this->is_error ) {
		this->print_debug_info("decode() error status\n");
				
		return make_tuple((char *)NULL, this->arr_pcm_length, -1);
	}

	this->is_decoding = true;

	int	 data_size = _data_size;
	memset(this->arr_mp3_data, 0x00, SIZE_DECODED_PCM);
	memcpy(this->arr_mp3_data, _input_data, _data_size);
	
	int decoded_sample = 0;
	
	mp3data_struct	t_mp3_data;
	memset(&t_mp3_data, 0x00, sizeof(t_mp3_data));
	
	int	pcm_size = 0;
	int loop_cnt = 0;
	int offset   = 0;
	
	bzero(this->ptr_pcm_data,   sizeof(this->ptr_pcm_data));
	bzero(this->arr_pcm_length, sizeof(this->arr_pcm_length));

	this->peak_volume = 0;
	
	do {
		decoded_sample = hip_decode1_headers(this->decoder, this->arr_mp3_data, data_size, this->decoded_pcm_left, this->decoded_pcm_right, &t_mp3_data);
		/*
		printf("# %d - %d, %d, %d, %d, %d, %d, %d, %d, %d, %d\n", 
				decoded_sample,
				t_mp3_data.header_parsed,
				t_mp3_data.stereo,
				t_mp3_data.samplerate,
				t_mp3_data.bitrate,
				t_mp3_data.mode,
				t_mp3_data.mode_ext,
				t_mp3_data.framesize,
				t_mp3_data.nsamp,
				t_mp3_data.totalframes,
				t_mp3_data.framenum);
		*/
		/*
		printf("header_parsed	: %d\n", t_mp3_data.header_parsed);
		printf("stereo			: %d\n", t_mp3_data.stereo);
		printf("samplerate		: %d\n", t_mp3_data.samplerate);
		printf("bitrate			: %d\n", t_mp3_data.bitrate);
		printf("mode			: %d\n", t_mp3_data.mode);
		printf("mode_ext		: %d\n", t_mp3_data.mode_ext);
		printf("framesize		: %d\n", t_mp3_data.framesize);
		printf("nsamp			: %d\n", t_mp3_data.nsamp);
		printf("totalframes		: %d\n", t_mp3_data.totalframes);
		printf("framenum		: %d\n", t_mp3_data.framenum);
		*/

		if( decoded_sample < 0 ) {
			this->print_debug_info("decode() lame decode error\n");
			this->is_error = true;
						
			hip_decode_exit(this->decoder);
			this->decoder = NULL;
			
			this->is_decoding = false;
			return make_tuple((char *)NULL, (int *)NULL, -1);
		}
		
		if( decoded_sample > 0 && (
				t_mp3_data.header_parsed	!= 1 
			||	t_mp3_data.stereo			!= this->num_channels
			||	t_mp3_data.samplerate		!= this->num_sample_rate
			||	t_mp3_data.mode				!= 1
			||	t_mp3_data.framesize 		!= 1152	) ) {
		
			printf("header_parsed	: %d\n", t_mp3_data.header_parsed);
			printf("stereo			: %d\n", t_mp3_data.stereo);
			printf("samplerate		: %d\n", t_mp3_data.samplerate);
			printf("bitrate			: %d\n", t_mp3_data.bitrate);
			printf("mode			: %d\n", t_mp3_data.mode);
			printf("mode_ext		: %d\n", t_mp3_data.mode_ext);
			printf("framesize		: %d\n", t_mp3_data.framesize);
			printf("nsamp			: %d\n", (int)t_mp3_data.nsamp);
			printf("totalframes		: %d\n", t_mp3_data.totalframes);
			printf("framenum		: %d\n", t_mp3_data.framenum);
			
			this->print_debug_info("decode() lame decode header parse error\n");
			
			this->is_decoding = false;
			return make_tuple((char *)NULL, (int *)NULL, -1);
		}
		
		if( decoded_sample > 0 ) {
			/* MP3 Layer 2 case 로 인한 filter 삭제
			if( decoded_sample != SIZE_DECODE_SAMPLES ) {
				this->print_debug_info("decode() lame decoded invalid sample count : [%d/%d]\n", decoded_sample, SIZE_DECODE_SAMPLES);
				data_size = 0;
				break;
			}
			this->ptr_pcm_data	 = (short *)realloc(this->ptr_pcm_data, sizeof(short) * decode_pcm_size * (loop_cnt + 1));
			this->arr_pcm_length = (int *)realloc(this->arr_pcm_length, sizeof(int) * (loop_cnt + 1));
			*/
						
			for( int idx = 0 ; idx < decoded_sample ; idx++ ) {
				this->ptr_pcm_data[offset * this->num_channels] = this->decoded_pcm_left[idx] * this->num_volume / 100;
				
				if( this->ptr_pcm_data[offset * this->num_channels] > this->peak_volume ) {
					this->peak_volume = this->ptr_pcm_data[offset * this->num_channels];
				}
				
				if( this->num_channels == NUM_CHANNEL_STEREO ) {
					this->ptr_pcm_data[offset * this->num_channels + 1] = this->decoded_pcm_right[idx] * this->num_volume / 100;
				}
				
				offset++;
			}
			
			pcm_size = (decoded_sample * this->num_channels) * sizeof(short);
			this->arr_pcm_length[loop_cnt] = pcm_size;
			
			loop_cnt++;
		}
		data_size = 0;
	} while( decoded_sample > 0 );

	this->is_decoding = false;
	return make_tuple((char *)this->ptr_pcm_data, this->arr_pcm_length, loop_cnt);
}
