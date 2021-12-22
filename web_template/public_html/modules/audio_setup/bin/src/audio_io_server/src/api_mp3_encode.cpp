#include "api_mp3_encode.h"

MP3_Encoder::MP3_Encoder(bool _is_debug_print) {
	this->is_debug_print = _is_debug_print;
	
	this->is_init			= false;
	
	this->encoder			= NULL;
	this->num_chunk_size	= -1;
	this->num_sample_rate	= -1;
	this->num_channels		= -1;
	this->num_vbr_quality	= -1;
	
	this->num_pcm_buffer_length	= -1;
	this->num_mp3_buffer_size	= -1;
	this->num_byte_per_sample	= DFLT_BYTE_PER_SAMPLE;

	this->ptr_mp3_data		= NULL;
	
	this->print_debug_info("MP3_Encoder() create instance\n");
	
	return ;
}

MP3_Encoder::~MP3_Encoder(void) {
	this->print_debug_info("MP3_Encoder() instance destructed\n");

	if( this->encoder != NULL ) {
		lame_close(this->encoder);
		this->encoder = NULL;
	}
	
	if( this->ptr_mp3_data != NULL ) {
		delete this->ptr_mp3_data;
		this->ptr_mp3_data = NULL;
	}

	return ;
}

void MP3_Encoder::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	fprintf(stdout, "MP3_Encoder::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	return ;
}

void MP3_Encoder::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

void MP3_Encoder::set_byte_per_sample(int _value) {
	if( _value != this->num_byte_per_sample ) {
		this->print_debug_info("set_byte_per_sample() set [%d] -> [%d]\n", _value, this->num_byte_per_sample);
		
		this->num_byte_per_sample = _value;
	}
	
	return ;
}

void MP3_Encoder::init(int _chunk_size, int _sample_rate, int _channels, int _quality) {
	if( this->is_init ) {
		this->print_debug_info("init() alreay init\n");
		return ;
	}
	this->is_init = false;
	this->is_encoding = false;
	
	this->num_chunk_size	= _chunk_size;
	this->num_sample_rate 	= _sample_rate;
	this->num_channels	 	= _channels;
	this->num_vbr_quality	= _quality;
	
	if( this->num_channels == NUM_CHANNEL_STEREO ) { 
		this->stereo_mode = JOINT_STEREO; 
	
	} else {
		this->stereo_mode = MONO;
	}
	
	this->encoder = lame_init();

	// lame_set VBR, no use 
	// lame_set_quality(this->encoder, 		this->num_vbr_quality);
	// lame_set_VBR(this->encoder, 			vbr_default);
	
	int encode_bit_rate = 0;
	switch( this->num_vbr_quality ) {
		case 2:  encode_bit_rate  = 192; break;
		case 5:  encode_bit_rate  = 128; break;
		case 7:  encode_bit_rate  = 64;  break;
		default: encode_bit_rate  = 128; break;
	}
	
	lame_set_in_samplerate(this->encoder, 	this->num_sample_rate);
	lame_set_num_channels(this->encoder, 	this->num_channels);
	lame_set_brate(this->encoder,			encode_bit_rate);
	lame_set_mode(this->encoder, 			this->stereo_mode);
	lame_init_params(this->encoder);

	this->print_debug_info("init() paramter - sample rate : [%d] \n", this->num_sample_rate);
	this->print_debug_info("init() paramter - channels    : [%d] \n", this->num_channels);
	this->print_debug_info("init() paramter - vbr mode    : [%s] \n", "CBR");
	this->print_debug_info("init() paramter - stereo mode : [%s] \n", this->num_channels == NUM_CHANNEL_STEREO ? "JOINT_STEREO" : "MONO");
	this->print_debug_info("init() paramter - quality     : [%d/%d kbps] \n", this->num_vbr_quality, encode_bit_rate);
	
	// this->num_mp3_buffer_size	= 1.25 * this->num_pcm_buffer_length + 7200;
	// this->num_mp3_buffer_size	= this->num_chunk_size * 2;
	this->num_pcm_buffer_length = this->num_chunk_size / this->num_byte_per_sample / this->num_channels;
	this->num_mp3_buffer_size	= this->num_chunk_size * 4;
	
	this->print_debug_info("init() data - byte per sample : [%d] \n", this->num_byte_per_sample);
	this->print_debug_info("init() data - pcm sample count: [%d] \n", this->num_pcm_buffer_length);
	this->print_debug_info("init() data - mp3 buffer size : [%d] \n", this->num_mp3_buffer_size);
	
	if( this->ptr_mp3_data != NULL ) {
		delete this->ptr_mp3_data;
		this->ptr_mp3_data = NULL;
	}

	this->ptr_mp3_data = new unsigned char[this->num_mp3_buffer_size];
	memset(this->ptr_mp3_data, 0x00, this->num_mp3_buffer_size);
	
	this->print_debug_info("init() init success \n");
	
	this->is_init = true;
	
	return ;
}

void MP3_Encoder::stop(void) {
	if( this->is_encoding ) {
		this->print_debug_info("stop() waiting encoding termed\n");
	}
	while( this->is_encoding ) {
		usleep(10000);
	}

	if( this->encoder != NULL ) {
		lame_close(this->encoder);
		this->encoder = NULL;
	}
	
	if( this->ptr_mp3_data != NULL ) {
		delete this->ptr_mp3_data;
		this->ptr_mp3_data = NULL;
	}
	
	this->is_init = false;
	
	return ;
}

tuple<char *, int> MP3_Encoder::encode(char *_input_data) {
	this->is_encoding = false;
	
	if( !this->is_init ) {
		this->print_debug_info("stop() is not running\n");
		
		return make_tuple((char *)NULL, 0);
	}
	
	this->is_encoding = true;

	int encoded_length;
	short *ptr_data = (short *)_input_data;
	
	if( this->num_channels == NUM_CHANNEL_STEREO ) {
		encoded_length = lame_encode_buffer_interleaved(this->encoder, ptr_data, this->num_pcm_buffer_length, this->ptr_mp3_data, this->num_mp3_buffer_size);
	
	} else {
		encoded_length = lame_encode_buffer(this->encoder, ptr_data, NULL, this->num_pcm_buffer_length, this->ptr_mp3_data, this->num_mp3_buffer_size);
	}
	
	if( encoded_length <= 0 ) {
		encoded_length = 0;
	}
	
	this->is_encoding = false;

	return make_tuple((char *)this->ptr_mp3_data, encoded_length);
}