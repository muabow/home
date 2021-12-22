#include "class_audio.h"

AUDIO_Handler::AUDIO_Handler(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	this->print_debug_info("AUDIO_Handler() create instance\n");
	
	this->level_value		= DFLT_LEVEL_VALUE;

	this->is_mp3_encode		= false;
	
	this->audio_volume		= -1;

	return ;
}

AUDIO_Handler::~AUDIO_Handler(void) {
	this->print_debug_info("AUDIO_Handler() instance destructed\n");
	
	return ;
}
		
void AUDIO_Handler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	printf("\033[33m");
	
	fprintf(stdout, "AUDIO_Handler::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	printf("\033[0m");
	
	return ;
}

void AUDIO_Handler::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

int AUDIO_Handler::get_level_value(void) {
	
	return this->level_value;
}


void AUDIO_Handler::set_level_value(int _value) {
	this->mutex_level_info.lock();
	
	this->level_value = _value;

	this->mutex_level_info.unlock();
	return ;
}

void AUDIO_Handler::set_playback_info(string _type, int _value) {
	this->mutex_playback_info.lock();
	
	int idx;
	int num_list = (int)this->v_playback_info.size();
	
	tuple<string, int> tp_play_info;
	
	for( idx = 0 ; idx < num_list ; idx++ ) {
		tp_play_info = this->v_playback_info[idx];
		if( get<0>(tp_play_info).compare(_type) == 0 ) {
			this->v_playback_info[idx] = make_tuple(_type, _value);
			
			break;
		}
	}
	
	if( idx == num_list ) {
		this->v_playback_info.push_back(make_tuple(_type, _value));
	}
	this->mutex_playback_info.unlock();
	
	return ;
}
	
int AUDIO_Handler::get_playback_info(string _type) {
	this->mutex_playback_info.lock();
	
	int num_list = (int)this->v_playback_info.size();
		
	tuple<string, int> tp_play_info;
	
	for( int idx = 0 ; idx < num_list ; idx++ ) {
		tp_play_info = this->v_playback_info[idx];
		if( get<0>(tp_play_info).compare(_type) == 0 ) {
				
			this->mutex_playback_info.unlock();
			return get<1>(tp_play_info);
		}
	}
	this->mutex_playback_info.unlock();
	
	return -1;
}

int AUDIO_Handler::get_audio_volume(void) {
	
	return this->audio_volume;
}

void AUDIO_Handler::set_audio_volume(int _volume) {
	this->mutex_volume.lock();
	
	this->audio_volume = _volume;
	
	this->mutex_volume.unlock();
	
	return ;
}

bool AUDIO_Handler::is_encode_status(void) {
	
	return this->is_mp3_encode;
}

void AUDIO_Handler::set_encode_status(string _encode_mode) {
	this->mutex_encode.lock();
	
	this->print_debug_info("set_encode_status() set encode mode [%s]\n", _encode_mode.c_str());
	if( _encode_mode.compare("mp3") == 0 ) {
		this->is_mp3_encode = true;
	
	} else {
		this->is_mp3_encode = false;
	}
	this->mutex_encode.unlock();
	
	return ;
}


/* audio_player */
AUDIO_PlayerHandler::AUDIO_PlayerHandler(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	this->print_debug_info("AUDIO_PlayerHandler() create instance\n");
	
	this->is_run			= false;
	this->is_play			= false;
	this->is_pause			= false;
	this->is_loop			= false;
	
	this->audio_play_index	= 0;
	this->audio_volume		= 0;
	
	this->is_play_prev		= false;
	this->is_play_next		= false;
	this->is_play_stop		= false;
	this->is_force_stop		= false;
	this->is_invalid_source	= false;
	this->is_change_index	= false;
	
	return ;
}

AUDIO_PlayerHandler::~AUDIO_PlayerHandler(void) {
	this->print_debug_info("AUDIO_PlayerHandler() instance destructed\n");
	
	return ;
}
		
void AUDIO_PlayerHandler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	printf("\033[33m");
	
	fprintf(stdout, "AUDIO_PlayerHandler::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	printf("\033[0m");
	
	return ;
}

void AUDIO_PlayerHandler::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

void AUDIO_PlayerHandler::set_player_status(string _type, bool _status) {
	if( _type.compare("is_run") == 0 ) {
		this->is_run 	= _status;
		
	} else if( _type.compare("is_play") == 0 ) {
		this->is_play	= _status;
	
	} else if( _type.compare("is_pause") == 0 ) {
		this->is_pause	= _status;
	
	} else if( _type.compare("is_loop") == 0 ) {
		this->is_loop	= _status;
	}
	
	return ;
}

bool AUDIO_PlayerHandler::get_player_status(string _type) {
	bool status = false;
	
	if( _type.compare("is_run") == 0 ) {
		status = this->is_run;
		
	} else if( _type.compare("is_play") == 0 ) {
		status = this->is_play;
	
	} else if( _type.compare("is_pause") == 0 ) {
		status = this->is_pause;
	
	} else if( _type.compare("is_loop") == 0 ) {
		status = this->is_loop;
	}
	
	return status;
}

void AUDIO_PlayerHandler::set_player_control(string _type, bool _status) {
	if( _type.compare("is_play_prev") == 0 ) {
		this->is_play_prev 	= _status;
		
	} else if( _type.compare("is_play_next") == 0 ) {
		this->is_play_next	= _status;
	
	} else if( _type.compare("is_play_stop") == 0 ) {
		this->is_play_stop	= _status;
	
	} else if( _type.compare("is_force_stop") == 0 ) {
		this->is_force_stop	= _status;
	
	} else if( _type.compare("is_invalid_source") == 0 ) {
		this->is_invalid_source	= _status;
	
	} else if( _type.compare("is_change_index") == 0 ) {
		this->is_change_index	= _status;
	}
	
	return ;
}

bool AUDIO_PlayerHandler::get_player_control(string _type) {
	bool status = false;
	
	if( _type.compare("is_play_prev") == 0 ) {
		status = this->is_play_prev;
		
	} else if( _type.compare("is_play_next") == 0 ) {
		status = this->is_play_next;
	
	} else if( _type.compare("is_play_stop") == 0 ) {
		status = this->is_play_stop;
	
	} else if( _type.compare("is_force_stop") == 0 ) {
		status = this->is_force_stop;
	
	} else if( _type.compare("is_invalid_source") == 0 ) {
		status = this->is_invalid_source;
	
	} else if( _type.compare("is_change_index") == 0 ) {
		status = this->is_change_index;
	}
	
	return status;
}

void AUDIO_PlayerHandler::set_player_index(int _index) {
	this->audio_play_index = _index;
	
	return ;
}

int AUDIO_PlayerHandler::get_player_index(void) {

	return this->audio_play_index;
}

void AUDIO_PlayerHandler::set_player_volume(int _volume) {
	this->audio_volume = _volume;
	
	return ;
}

int AUDIO_PlayerHandler::get_player_volume(void) {

	return this->audio_volume;
}


/* source info */
AUDIO_SourceInfo::AUDIO_SourceInfo(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	// this->print_debug_info("AUDIO_SourceInfo() create instance\n");
	
	this->is_play			= false;
	this->is_pause			= false;
	this->is_playlist		= false;
	this->is_valid_source	= false;
	
	this->source_hash_id	= "";
	this->source_file_path	= "";
	this->source_name		= "";
	this->source_type		= "";
	
	this->audio_play_time	= 0;
	this->audio_loop_count	= 1;

	this->num_audio_format		= 0;
	this->num_sample_rate		= 0;
	this->num_channels			= 0;
	this->num_bit_rate			= 0;
	this->num_bits_per_sample	= 0;
	
	this->num_mp3_skip_bytes	= 0;
	
	return ;
}

AUDIO_SourceInfo::~AUDIO_SourceInfo(void) {
	// this->print_debug_info("AUDIO_SourceInfo() instance destructed\n");
	
	return ;
}
		
void AUDIO_SourceInfo::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	printf("\033[33m");
	
	fprintf(stdout, "AUDIO_SourceInfo::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	printf("\033[0m");
	
	return ;
}

void AUDIO_SourceInfo::set_debug_print(void) {
	this->is_debug_print = true;
	
	// this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

bool AUDIO_SourceInfo::is_valid_ext_type(string _file_path) {
	string ext_type = this->get_info_file_ext(_file_path);
	
	for_each(ext_type.begin(), ext_type.end(), [](char &_char) {
		_char = tolower(_char);
	});
		
	if( ext_type.compare("wav") == 0 || ext_type.compare("mp3") == 0 ) {
		return true;
	}
	
	return false;
}

bool AUDIO_SourceInfo::parse_source_wav(void) {
	FILE* fp;
		
	if( (fp = fopen(this->source_file_path.c_str(), "r")) == NULL ) {
		this->print_debug_info("parse_source_info() Unable to open file : [%s]\n", this->source_file_path.c_str());
		
		return false;
	}
	
	WAV_HEADER_t	t_wav_header;

	fread(&t_wav_header, 1, sizeof(t_wav_header), fp);
	
	this->audio_play_time		= (t_wav_header.chunk_size - 36) / t_wav_header.bytes_per_sec;
	this->num_sample_rate		= t_wav_header.sample_rate;
	this->num_channels 			= t_wav_header.channels;
	this->num_bits_per_sample	= t_wav_header.bits_per_sample;
	this->num_audio_format		= t_wav_header.audio_format;
	fclose(fp);
	
	if( t_wav_header.audio_format == WAV_FORMAT_PCM  ) {
		return true;
		
	}else if(		t_wav_header.audio_format == WAV_FORMAT_IEEE_FLOAT
				||	t_wav_header.audio_format == WAV_FORMAT_ALAW
				||	t_wav_header.audio_format == WAV_FORMAT_MULAW
				||	t_wav_header.audio_format == WAV_FORMAT_EXTENSIBLE ) {
		
		return false;
	}
	
	return false;
}

bool AUDIO_SourceInfo::parse_source_mp3(void) {
	FILE* fp;
		
	if( (fp = fopen(this->source_file_path.c_str(), "r")) == NULL ) {
		this->print_debug_info("parse_source_info() Unable to open file : [%s]\n", this->source_file_path.c_str());
		
		return false;
	}
	
	MP3_ID3_TAG_t	t_mp3_tag_info;
	MP3_HEADER_t	t_mp3_header;
	
	char 	mp3_aau_info[4];
	int		id3_tag_size	= 0;
	int 	mp3_file_size	= 0;
	int		skip_bytes		= 0;
	
	int	 	read_bytes		= 0;
	int	 	sum_read_bytes	= 0;
	int	 	frame_count 	= 0;
	double	avg_bit_rate	= 0;
			
	
	// get mp3 file size
	fseek(fp, 0, SEEK_END);
	mp3_file_size = ftell(fp);
	fseek(fp, 0, SEEK_SET);
	
	// find ID3 tag
	fread(&t_mp3_tag_info, 1, sizeof(t_mp3_tag_info), fp);
	if( t_mp3_tag_info.id[0] == 'I' && t_mp3_tag_info.id[1] == 'D' && t_mp3_tag_info.id[2] == '3' ) {
		id3_tag_size += (t_mp3_tag_info.size[0] << 21);
		id3_tag_size += (t_mp3_tag_info.size[1] << 14);
		id3_tag_size += (t_mp3_tag_info.size[2] << 7);
		id3_tag_size += (t_mp3_tag_info.size[3]);
		id3_tag_size += (int)sizeof(t_mp3_tag_info);
		
		int has_footer = (t_mp3_tag_info.flags >> 4) & 1;
		if( has_footer == 1 ) {
			id3_tag_size += SIZE_ID3_TAG_FOOT;
		}
		
		fseek(fp, id3_tag_size, SEEK_SET);
		
	} else {
		rewind(fp);
	}
	
	while( (read_bytes = (int)fread(mp3_aau_info, 1, sizeof(mp3_aau_info), fp)) != 0 ) {
		if( ((mp3_aau_info[0] & 0xFF) == 0xFF) && (((mp3_aau_info[1] >> 5) & 0x07) == 0x07) ) {
			break;
		}
		skip_bytes += (int)sizeof(mp3_aau_info); 
	}
	
	t_mp3_header.version	= (mp3_aau_info[1] >> 3 & 0x03);
	t_mp3_header.layer		= (mp3_aau_info[1] >> 1 & 0x03);
	t_mp3_header.errp		= (mp3_aau_info[1] & 0x01);
	
	t_mp3_header.bitrate	= (mp3_aau_info[2] >> 4 & 0x0F);
	t_mp3_header.freq		= (mp3_aau_info[2] >> 2 & 0x03);
	t_mp3_header.pad		= (mp3_aau_info[2] >> 1 & 0x01);
	t_mp3_header.priv		= (mp3_aau_info[2] & 0x01);
	
	t_mp3_header.mode		= (mp3_aau_info[3] >> 6 & 0x03);
	t_mp3_header.modex		= (mp3_aau_info[3] >> 4 & 0x03);
	t_mp3_header.copyright	= (mp3_aau_info[3] >> 3 & 0x01);
	t_mp3_header.original	= (mp3_aau_info[3] >> 2 & 0x01);
	t_mp3_header.emphasis	= (mp3_aau_info[3] & 0x03);
	
	int bit_rate_index = 0;
	switch( t_mp3_header.version ) {
		case MPEG_VERSION_2_5 :
		case MPEG_VERSION_2_0 :
			switch( t_mp3_header.layer ) {
				case MPEG_LAYER_RSVD :	bit_rate_index = 6;	break; // rsvd
				case MPEG_LAYER_3 	 :	bit_rate_index = 4;	break; // MPEG_v2.0 or MPEG_v2.5/Layer3
				case MPEG_LAYER_2 	 :	bit_rate_index = 4;	break; // MPEG_v2.0 or MPEG_v2.5/Layer2
				case MPEG_LAYER_1 	 :	bit_rate_index = 3;	break; // MPEG_v2.0 or MPEG_v2.5/Layer1
			}
			break;
	
		case MPEG_VERSION_1_0 :
			switch( t_mp3_header.layer ) {
				case MPEG_LAYER_RSVD :	bit_rate_index = 6;	break; // rsvd
				case MPEG_LAYER_3	 :	bit_rate_index = 2;	break; // MPEG_v1.0/Layer3
				case MPEG_LAYER_2	 :	bit_rate_index = 1;	break; // MPEG_v1.0/Layer2
				case MPEG_LAYER_1	 :	bit_rate_index = 0;	break; // MPEG_v1.0/Layer1
			}
			break;
			
		case MPEG_VERSION_RSVD 		 :	bit_rate_index = 6; break; // rsvd
		default 					 :	bit_rate_index = 6; break; // rsvd
	}
	
	if( bit_rate_index == 6 ) {
		this->print_debug_info("parse_source_info() Unable to parse file : [%s]\n", this->source_file_path.c_str());
		return false;
	}
	
	int sample_per_frame = LIST_SAMPLE_PER_FRAME[t_mp3_header.version][t_mp3_header.layer];
	int sample_rate		 = LIST_SAMPLE_RATE[t_mp3_header.version][t_mp3_header.freq];
	int bit_rate		 = LIST_BIT_RATE[bit_rate_index][t_mp3_header.bitrate] * 1000;

	if( sample_per_frame == 0 || sample_rate == 0 || bit_rate == 0 ) {
		this->print_debug_info("parse_source_info() sample_per_frame[%d], sample_rate[%d], bit_rate[%d]\n", 
				sample_per_frame, sample_rate, bit_rate);
		
		this->print_debug_info("parse_source_info() Unable to parse file : [%s]\n", this->source_file_path.c_str());
		return false;
	}	
	
	int frame_size = (sample_per_frame / 8) * bit_rate / sample_rate + t_mp3_header.pad;
	if( t_mp3_header.layer == MPEG_LAYER_1 ) {
		frame_size = (frame_size * 4); 
	}
	fseek(fp, -4, SEEK_CUR);
	
	char *mp3_aau_frame  = new char[frame_size];
	int  loop_frame_size = 0;
	while( (read_bytes = (int)fread(mp3_aau_frame, 1, frame_size, fp)) != 0 ) {
		sum_read_bytes += read_bytes;
		
		if( ((mp3_aau_frame[0] & 0xFF) == 0xFF) && (((mp3_aau_frame[1] >> 5) & 0x07) == 0x07) ) {
			t_mp3_header.version	= (mp3_aau_frame[1] >> 3 & 0x03);
			t_mp3_header.layer		= (mp3_aau_frame[1] >> 1 & 0x03);
			t_mp3_header.errp		= (mp3_aau_frame[1] & 0x01);
	
			t_mp3_header.bitrate	= (mp3_aau_frame[2] >> 4 & 0x0F);
			t_mp3_header.freq		= (mp3_aau_frame[2] >> 2 & 0x03);
			t_mp3_header.pad		= (mp3_aau_frame[2] >> 1 & 0x01);
			t_mp3_header.priv		= (mp3_aau_frame[2] & 0x01);
	
			t_mp3_header.mode		= (mp3_aau_frame[3] >> 6 & 0x03);
			t_mp3_header.modex		= (mp3_aau_frame[3] >> 4 & 0x03);
			t_mp3_header.copyright	= (mp3_aau_frame[3] >> 3 & 0x01);
			t_mp3_header.original	= (mp3_aau_frame[3] >> 2 & 0x01);
			t_mp3_header.emphasis	= (mp3_aau_frame[3] & 0x03);
	
			sample_per_frame = LIST_SAMPLE_PER_FRAME[t_mp3_header.version][t_mp3_header.layer];
			sample_rate		 = LIST_SAMPLE_RATE[t_mp3_header.version][t_mp3_header.freq];
			bit_rate		 = LIST_BIT_RATE[bit_rate_index][t_mp3_header.bitrate] * 1000;
			
			if( sample_per_frame == 0 || sample_rate == 0 || bit_rate == 0 ) continue;
			loop_frame_size = (sample_per_frame / 8) * bit_rate / sample_rate + t_mp3_header.pad;
			
			if( t_mp3_header.layer == MPEG_LAYER_1 ) {
				loop_frame_size = (loop_frame_size * 4); 
			}
			
			if( loop_frame_size != frame_size ) {
				frame_size = loop_frame_size;
				delete []mp3_aau_frame;
				mp3_aau_frame  = new char[frame_size];
			}
			
			avg_bit_rate += bit_rate;
			frame_count++;
		}
	}
	delete []mp3_aau_frame;
	avg_bit_rate = avg_bit_rate / frame_count;
	bit_rate = (int)avg_bit_rate;
	
	string str_channel_info = "";
	switch( t_mp3_header.mode ) {
		case	0 	: 
			str_channel_info = "Stereo";
			this->num_channels	= 2;
			break;
			
		case	1 	: 
			str_channel_info = "Joint Stereo";
			this->num_channels	= 2;
			break;
		case	2 	: 
			str_channel_info = "2 mono channel";
			this->num_channels	= 1;
			break;
		case	3 	: 
			str_channel_info = "Mono";
			this->num_channels	= 1;
			break;
		default		: 
			str_channel_info = "Unknown";
			this->num_channels	= 0;
			break;
	}
	
	this->audio_play_time 		= (int)((mp3_file_size - id3_tag_size - skip_bytes) * 8.0 / bit_rate);
	this->num_sample_rate 		= sample_rate;
	this->num_bit_rate    		= bit_rate;
	this->num_bits_per_sample	= 16;
	
	this->num_mp3_skip_bytes	= id3_tag_size + skip_bytes;
	
	fclose(fp);
	
	return true;
}

void AUDIO_SourceInfo::set_source_info(string _file_path) {
	hash<string> hasher;
	size_t hashed = hasher(_file_path);
	
	this->source_file_path	= _file_path;
	this->source_name		= this->get_info_file_name(_file_path);
	this->source_type		= this->get_info_file_ext(_file_path);
	this->source_hash_id	= to_string(hashed);
	
	bool parse_result = false;
	
	if( this->source_type.compare("wav") == 0 ) {
		parse_result = this->parse_source_wav();
		
	} else if( this->source_type.compare("mp3") == 0 ) {
		parse_result = this->parse_source_mp3();
	}
	

	this->print_debug_info("set_source_info() \n");
	this->print_debug_info("set_source_info() source type [%s]\n", this->source_type.c_str());
	this->print_debug_info("set_source_info()  - file path       : [%s]\n", this->source_file_path.c_str());
	this->print_debug_info("set_source_info()  - file name       : [%s]\n", this->source_name.c_str());
	this->print_debug_info("set_source_info()  - file hash       : [%s]\n", this->source_hash_id.c_str());
	this->print_debug_info("set_source_info()  - play time       : [%02d:%02d] \n", (int)this->audio_play_time / 60, (int)this->audio_play_time % 60);
	this->print_debug_info("set_source_info()  - sample rate     : [%d]\n", this->num_sample_rate);
	this->print_debug_info("set_source_info()  - channels        : [%d]\n", this->num_channels);
	this->print_debug_info("set_source_info()  - audio format    : [0x%04x]\n", this->num_audio_format);
	
	if( this->source_type.compare("wav") == 0 ) {
		this->print_debug_info("set_source_info()  - bits per sample : [%d]\n", this->num_bits_per_sample);
	
	} else {
		this->print_debug_info("set_source_info()  - bit rate        : [%d]\n", this->num_bit_rate);
	}

	if( !parse_result ) {
		this->print_debug_info("set_source_info() # invalid source [%s][%s]\n", this->source_type.c_str(), this->source_name.c_str());
		return ;
	}

	this->is_valid_source = true;
	
	return ;
}

string AUDIO_SourceInfo::get_info_file_ext(string _file) {
	string ext_type = _file.substr(_file.find_last_of(".") + 1);
	
	for_each(ext_type.begin(), ext_type.end(), [](char &_char) {
		_char = tolower(_char);
	});
	
	return ext_type;
}

string AUDIO_SourceInfo::get_info_file_name(string _file) {
	string base_name = string(basename(_file.c_str()));
	
	return base_name.substr(0, _file.find_last_of("."));
}

bool AUDIO_SourceInfo::get_source_status(string _type) {
	if( _type.compare("is_play") == 0 ) {
		return this->is_play;
	
	} else if( _type.compare("is_pause") == 0 ) {
		return this->is_pause;
	
	} else if( _type.compare("is_playlist") == 0 ) {
		return this->is_playlist;
	
	} else if( _type.compare("is_valid_source") == 0 ) {
		return this->is_valid_source;
	}
	
	return false;
}

string AUDIO_SourceInfo::get_source_info(string _type) {
	if( _type.compare("source_hash_id") == 0 ) {
		return this->source_hash_id;
	
	} else if( _type.compare("source_file_path") == 0 ) {
		return this->source_file_path;
	
	} else if( _type.compare("source_name") == 0 ) {
		return this->source_name;
	
	} else if( _type.compare("source_type") == 0 ) {
		return this->source_type;
	}
	
	return "";
}

int AUDIO_SourceInfo::get_play_info(string _type) {
	if( _type.compare("audio_play_time") == 0 ) {
		return this->audio_play_time;
	
	} else if( _type.compare("audio_loop_count") == 0 ) {
		return this->audio_loop_count;
	
	} else if( _type.compare("num_sample_rate") == 0 ) {
		return this->num_sample_rate;
	
	} else if( _type.compare("num_channels") == 0 ) {
		return this->num_channels;
	
	} else if( _type.compare("num_bit_rate") == 0 ) {
		return this->num_bit_rate;
	
	} else if( _type.compare("num_bits_per_sample") == 0 ) {
		return this->num_bits_per_sample;
	
	} else if( _type.compare("num_mp3_skip_bytes") == 0 ) {
		return this->num_mp3_skip_bytes;
	
	} else if( _type.compare("num_audio_format") == 0 ) {
		return this->num_audio_format;
	}
	
	return 0;
}

void AUDIO_SourceInfo::set_source_status(string _type, bool _value) {
	if( _type.compare("is_play") == 0 ) {
		this->is_play = _value;
	
	} else if( _type.compare("is_pause") == 0 ) {
		this->is_pause = _value;
	
	} else if( _type.compare("is_playlist") == 0 ) {
		this->is_playlist = _value;
	
	} else if( _type.compare("is_valid_source") == 0 ) {
		this->is_valid_source = _value;
	}
	
	return ;
}

void AUDIO_SourceInfo::set_source_info(string _type, string _value) {
	if( _type.compare("source_hash_id") == 0 ) {
		this->source_hash_id = _value;
	
	} else if( _type.compare("source_file_path") == 0 ) {
		this->source_file_path = _value;
	
	} else if( _type.compare("source_name") == 0 ) {
		this->source_name = _value;
	
	} else if( _type.compare("source_type") == 0 ) {
		this->source_type = _value;
	}
	
	return ;
}

void AUDIO_SourceInfo::set_play_info(string _type, int _value) {
	if( _type.compare("audio_play_time") == 0 ) {
		this->audio_play_time = _value;
	
	} else if( _type.compare("audio_loop_count") == 0 ) {
		this->audio_loop_count = _value;
	
	} else if( _type.compare("num_sample_rate") == 0 ) {
		this->num_sample_rate = _value;
	
	} else if( _type.compare("num_channels") == 0 ) {
		this->num_channels = _value;
	
	} else if( _type.compare("num_bit_rate") == 0 ) {
		this->num_bit_rate = _value;
	
	} else if( _type.compare("num_bits_per_sample") == 0 ) {
		this->num_bits_per_sample = _value;
	
	} else if( _type.compare("num_mp3_skip_bytes") == 0 ) {
		this->num_mp3_skip_bytes = _value;
	}
	
	return ;
}



/* source handler */
AUDIO_SourceHandler::AUDIO_SourceHandler(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	this->print_debug_info("AUDIO_SourceHandler() create instance\n");
	
	this->path_source_dir = "";
	this->path_module_db  = "";
	
	this->mutex_db_handler = NULL;
	
	return ;
}

AUDIO_SourceHandler::~AUDIO_SourceHandler(void) {
	this->print_debug_info("AUDIO_SourceHandler() instance destructed\n");
	
	return ;
}
		
void AUDIO_SourceHandler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	printf("\033[33m");
	
	fprintf(stdout, "AUDIO_SourceHandler::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	printf("\033[0m");
	
	return ;
}

void AUDIO_SourceHandler::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

void AUDIO_SourceHandler::set_source_path(string _path) {
	if( this->path_source_dir.compare(_path) != 0 ) { 
		this->print_debug_info("set_source_path() path set [%s]\n", _path.c_str());
		this->path_source_dir = _path;
	}
	
	return ;
}

void AUDIO_SourceHandler::set_database_path(string _path) {
	if( this->path_module_db.compare(_path) != 0 ) { 
		this->print_debug_info("set_database_path() path set [%s]\n", _path.c_str());
		this->path_module_db = _path;
	}
	
	return ;
}

void AUDIO_SourceHandler::set_mutex_db_handler(mutex *_mutex_handler) {
	this->print_debug_info("set_mutex_db_handler() set audio_handler instance mutex handler\n");
	this->mutex_db_handler = _mutex_handler;
	
	return ;
}


void AUDIO_SourceHandler::read_source_list(void) {
	mutex_source_list.lock();
	
	DIR* dirp = opendir(this->path_source_dir.c_str());
	if( dirp == NULL ) {
		this->print_debug_info("read_source_list() invalid path : [%s]\n", this->path_source_dir.c_str());
		mutex_source_list.unlock();
		return ;
	}
	struct dirent *dp;
	hash<string> hasher;
	size_t 		 hashed;

	this->v_source_list.clear();
	
	while( (dp = readdir(dirp)) != NULL ) {
		if( dp->d_type == DIRENT_TYPE_FILE ) {
			string file_path = this->path_source_dir;
			file_path.append(dp->d_name);
			
			AUDIO_SourceInfo source_info;
			if( this->is_debug_print ) {
				source_info.set_debug_print();
			}
			
			if( source_info.is_valid_ext_type(file_path) ) {
				hashed = hasher(file_path);
				string hash_id = to_string(hashed);
				
				source_info.set_source_info(file_path);
				this->v_source_list.push_back(source_info);
			}
		} 
	}
	closedir(dirp);
	
	// update source_list database
	this->sync_database_list();
		
	mutex_source_list.unlock();
	return ;
}

void AUDIO_SourceHandler::sort_source_list(vector<string> _v_hash_id_list) {
	mutex_source_list.lock();
	
	vector<AUDIO_SourceInfo> v_source_list;
	bool is_file_exist = false;
	int idx = 0, v_idx = 0;
	
	for( idx = 0 ; idx < (int)_v_hash_id_list.size() ; idx++ ) {
		string hash_id = _v_hash_id_list[idx];
		is_file_exist = false;
		
		for( v_idx = 0 ; v_idx < (int)this->v_source_list.size() ; v_idx++ ) {
			if( this->v_source_list[v_idx].get_source_info("source_hash_id").compare(hash_id) == 0 ) {
				is_file_exist = true;
				break;
			}
		}
		
		if( is_file_exist ) {
			v_source_list.push_back(this->v_source_list[v_idx]);
		}
	}
	
	this->v_source_list = v_source_list;
	
	// update source_list database
	this->change_database_list();
	
	mutex_source_list.unlock();
	return ;
}


void AUDIO_SourceHandler::remove_source_list(string _hash_id) {
	mutex_source_list.lock();
	bool is_file_exist = false;
	int  idx = 0;
	
	for( idx = 0 ; idx < (int)this->v_source_list.size() ; idx++ ) {
		if( this->v_source_list[idx].get_source_info("source_hash_id").compare(_hash_id) == 0 ) {
			is_file_exist = true;
			break;
		}
	}
	
	if( is_file_exist ) {
		string file_path = this->v_source_list[idx].get_source_info("source_file_path");
		
		if( remove(file_path.c_str()) != 0 ) {
			this->print_debug_info("remove_source_list() remove failed [%s] : [%02d] %s\n", file_path.c_str(), errno, strerror(errno));
		
		} else {
			this->print_debug_info("remove_source_list() remove success [%s]\n", file_path.c_str());
			this->v_source_list.erase(this->v_source_list.begin() + idx);
		}
	
	} else {
		this->print_debug_info("remove_source_list() not found hash_id [%s]\n", _hash_id.c_str());
	}
	
	// update source_list database
	this->sync_database_list();
	
	mutex_source_list.unlock();
	return ;
}

int AUDIO_SourceHandler::callback_is_exist(void *_this, int _argc, char **_argv, char **_col_name) {
	AUDIO_SourceHandler *p_handler = (AUDIO_SourceHandler *)_this;
	
	AUDIO_SourceInfo source_info;
	
	source_info.set_source_status("is_valid_source",	stoi(_argv[COL_IS_VALID_SOURCE]));
	source_info.set_source_status("is_play", 			stoi(_argv[COL_IS_PLAY]));
	source_info.set_source_status("is_playlist",		stoi(_argv[COL_IS_PLAYLIST]));
	
	source_info.set_source_info("source_hash_id", 		string(_argv[COL_SOURCE_HASH_ID]));
	source_info.set_source_info("source_file_path",		string(_argv[COL_SOURCE_FILE_PATH]));
	source_info.set_source_info("source_name", 			string(_argv[COL_SOURCE_NAME]));
	source_info.set_source_info("source_type", 			string(_argv[COL_SOURCE_TYPE]));

	source_info.set_play_info("audio_play_time", 		stoi(_argv[COL_AUDIO_PLAY_TIME]));
	source_info.set_play_info("audio_loop_count", 		stoi(_argv[COL_AUDIO_LOOP_COUNT]));
	
	p_handler->v_exist_source_list.push_back(source_info);
	
	return 0;
}


void AUDIO_SourceHandler::sync_database_list(void) {
	this->mutex_db_handler->lock();
		
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler->unlock();
		
		return ;
	}
	
	sqlite3 *handler;
	
	if( sqlite3_open(this->path_module_db.c_str(), &handler) ) {
		this->print_debug_info("open() can't open database file : %s [%s]\n" , sqlite3_errmsg(handler), this->path_module_db.c_str());
		this->mutex_db_handler->unlock();
		
		return ;
	}
	sqlite3_exec(handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	char query_string[1024];
	bool is_exist = false;
	
	this->v_exist_source_list.clear();
	sprintf(query_string, "select * from source_info_list;");
	sqlite3_exec(handler, query_string, callback_is_exist, (void *)this, NULL);
	
	for( int idx = 0 ; idx < (int)this->v_source_list.size() ; idx++ ) {
		is_exist = false;
		
		for( int ex_idx = 0 ; ex_idx < (int)this->v_exist_source_list.size() ; ex_idx++ ) {
			if( this->v_source_list[idx].get_source_info("source_hash_id").compare(this->v_exist_source_list[ex_idx].get_source_info("source_hash_id")) == 0 ) {
				is_exist = true;
				
				this->v_source_list[idx].set_source_status("is_play", 			this->v_exist_source_list[ex_idx].get_source_status("is_play"));
				this->v_source_list[idx].set_source_status("is_playlist", 		this->v_exist_source_list[ex_idx].get_source_status("is_playlist"));
				this->v_source_list[idx].set_play_info("audio_play_time",		this->v_exist_source_list[ex_idx].get_play_info("audio_play_time"));
				this->v_source_list[idx].set_play_info("audio_loop_count",		this->v_exist_source_list[ex_idx].get_play_info("audio_loop_count"));
				
				break;
			}
		}
		
		if( !is_exist ) {
			sprintf(query_string, "insert into source_info_list values(%d, %d, %d, '%s', '%s', '%s', '%s', %d, %d);",
				this->v_source_list[idx].get_source_status("is_valid_source"),
				this->v_source_list[idx].get_source_status("is_play"),
				this->v_source_list[idx].get_source_status("is_playlist"),
				
				this->v_source_list[idx].get_source_info("source_hash_id").c_str(),
				this->v_source_list[idx].get_source_info("source_file_path").c_str(),
				this->v_source_list[idx].get_source_info("source_name").c_str(),
				this->v_source_list[idx].get_source_info("source_type").c_str(),
				
				this->v_source_list[idx].get_play_info("audio_play_time"),
				this->v_source_list[idx].get_play_info("audio_loop_count")
			);
			sqlite3_exec(handler, query_string, NULL, NULL, NULL);
		
		} else {
			sprintf(query_string, "update source_info_list set is_valid_source=%d where source_hash_id='%s';",
							this->v_source_list[idx].get_source_status("is_valid_source"),
							this->v_source_list[idx].get_source_info("source_hash_id").c_str());
			
			sqlite3_exec(handler, query_string, NULL, NULL, NULL);
		}
	}
	
	this->v_exist_source_list.clear();
	sprintf(query_string, "select * from source_info_list;");
	sqlite3_exec(handler, query_string, callback_is_exist, (void *)this, NULL);
	
	for( int idx = 0 ; idx < (int)this->v_exist_source_list.size() ; idx++ ) {
		is_exist = false;
		
		for( int ex_idx = 0 ; ex_idx < (int)this->v_source_list.size() ; ex_idx++ ) {
			if( this->v_exist_source_list[idx].get_source_info("source_hash_id").compare(this->v_source_list[ex_idx].get_source_info("source_hash_id")) == 0 ) {
				is_exist = true;
				break;
			}
		}
		
		if( !is_exist ) {
			sprintf(query_string, "delete from source_info_list where source_hash_id='%s';", this->v_exist_source_list[idx].get_source_info("source_hash_id").c_str());
			sqlite3_exec(handler, query_string, NULL, NULL, NULL);
		}
	}

	this->v_exist_source_list.clear();
	sprintf(query_string, "select * from source_info_list;");
	sqlite3_exec(handler, query_string, callback_is_exist, (void *)this, NULL);
	
	for( int idx = 0 ; idx < (int)this->v_exist_source_list.size() ; idx++ ) {
		for( int ex_idx = 0 ; ex_idx < (int)this->v_source_list.size() ; ex_idx++ ) {
			if( this->v_exist_source_list[idx].get_source_info("source_hash_id").compare(this->v_source_list[ex_idx].get_source_info("source_hash_id")) == 0 ) {
				this->v_exist_source_list[idx] = this->v_source_list[ex_idx];
				break;
			}
		}
	}
	this->v_source_list = this->v_exist_source_list;
	
	sqlite3_exec(handler, "END TRANSACTION;", NULL, NULL, NULL);
	sqlite3_close(handler);
	
	this->mutex_db_handler->unlock();
	
	return ;
}

void AUDIO_SourceHandler::change_database_list(void) {
	this->mutex_db_handler->lock();
		
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler->unlock();
		
		return ;
	}
	
	sqlite3 *handler;
	
	if( sqlite3_open(this->path_module_db.c_str(), &handler) ) {
		this->print_debug_info("open() can't open database file : %s [%s]\n" , sqlite3_errmsg(handler), this->path_module_db.c_str());
		this->mutex_db_handler->unlock();
		
		return ;
	}
	sqlite3_exec(handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	char query_string[1024];

	sprintf(query_string, "delete from source_info_list;");
	sqlite3_exec(handler, query_string, callback_is_exist, (void *)this, NULL);
	
	for( int idx = 0 ; idx < (int)this->v_source_list.size() ; idx++ ) {
		sprintf(query_string, "insert into source_info_list values(%d, %d, %d, '%s', '%s', '%s', '%s', %d, %d);",
			this->v_source_list[idx].get_source_status("is_valid_source"),
			this->v_source_list[idx].get_source_status("is_play"),
			this->v_source_list[idx].get_source_status("is_playlist"),
			
			this->v_source_list[idx].get_source_info("source_hash_id").c_str(),
			this->v_source_list[idx].get_source_info("source_file_path").c_str(),
			this->v_source_list[idx].get_source_info("source_name").c_str(),
			this->v_source_list[idx].get_source_info("source_type").c_str(),
			
			this->v_source_list[idx].get_play_info("audio_play_time"),
			this->v_source_list[idx].get_play_info("audio_loop_count")
		);
		sqlite3_exec(handler, query_string, NULL, NULL, NULL);
	}
	
	sqlite3_exec(handler, "END TRANSACTION;", NULL, NULL, NULL);
	sqlite3_close(handler);
	
	this->mutex_db_handler->unlock();
	
	return ;
}


vector<AUDIO_SourceInfo> AUDIO_SourceHandler::get_source_list(void) {
	
	return this->v_source_list;
}

string AUDIO_SourceHandler::make_json_source_list(void) {
	mutex_source_list.lock();
	
	int 	num_source_list = (int)this->v_source_list.size();
	string	str_source_list = "[";
	
	Document 	doc_data;
	StringBuffer data_buffer;
	Writer<StringBuffer> data_writer(data_buffer);
	
	string json_data;
	
	for( int idx = 0 ; idx < num_source_list ; idx++ ) {
		data_writer.Reset(data_buffer); 
		Pointer("/is_valid_source"	).Set(doc_data, this->v_source_list[idx].get_source_status("is_valid_source"));
		Pointer("/is_play"			).Set(doc_data, this->v_source_list[idx].get_source_status("is_play"));
		Pointer("/is_playlist"		).Set(doc_data, this->v_source_list[idx].get_source_status("is_playlist"));
		Pointer("/source_hash_id"	).Set(doc_data, this->v_source_list[idx].get_source_info("source_hash_id").c_str());
		Pointer("/source_name"		).Set(doc_data, this->v_source_list[idx].get_source_info("source_name").c_str());
		Pointer("/source_type"		).Set(doc_data, this->v_source_list[idx].get_source_info("source_type").c_str());
		Pointer("/audio_play_time"	).Set(doc_data, this->v_source_list[idx].get_play_info("audio_play_time"));
		Pointer("/audio_loop_count"	).Set(doc_data, this->v_source_list[idx].get_play_info("audio_loop_count"));
		Pointer("/num_sample_rate"	).Set(doc_data, this->v_source_list[idx].get_play_info("num_sample_rate"));
		Pointer("/num_channels"		).Set(doc_data, this->v_source_list[idx].get_play_info("num_channels"));
		Pointer("/num_bit_rate"		).Set(doc_data, this->v_source_list[idx].get_play_info("num_bit_rate"));

		doc_data.Accept(data_writer);
		
		json_data = data_buffer.GetString();
		str_source_list.append(json_data);
		if( idx + 1 < num_source_list ) {
			str_source_list.append(",");
		}
		data_buffer.Clear();
	}
	str_source_list.append("]");
	
	mutex_source_list.unlock();
	
	return str_source_list;
}

string AUDIO_SourceHandler::make_json_source_name_list(void) {
	mutex_source_list.lock();
	
	int 	num_source_list = (int)this->v_source_list.size();
	string	str_source_list = "";
	
	for( int idx = 0 ; idx < num_source_list ; idx++ ) {
		if( !this->v_source_list[idx].get_source_status("is_valid_source") ) {
			continue;
		}

		str_source_list.append(this->v_source_list[idx].get_source_info("source_name"));
		str_source_list.append("|");
	}
	str_source_list = str_source_list.substr(0, str_source_list.size() - 1);

	mutex_source_list.unlock();
	
	return str_source_list;
}
void AUDIO_SourceHandler::update_source_info(int _idx, string _type, bool _value) {
	this->mutex_db_handler->lock();
			
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler->unlock();
		
		return ;
	}
	
	sqlite3 *handler;
	
	if( sqlite3_open(this->path_module_db.c_str(), &handler) ) {
		this->print_debug_info("open() can't open database file : %s [%s]\n" , sqlite3_errmsg(handler), this->path_module_db.c_str());
		this->mutex_db_handler->unlock();
		
		return ;
	}
	sqlite3_exec(handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	char query_string[1024];
	sprintf(query_string, "update source_info_list set %s=%d where source_hash_id='%s';", _type.c_str(), (int)_value, this->v_source_list[_idx].get_source_info("source_hash_id").c_str());
	sqlite3_exec(handler, query_string, NULL, NULL, NULL);

	sqlite3_exec(handler, "END TRANSACTION;", NULL, NULL, NULL);
	sqlite3_close(handler);
	
	this->v_source_list[_idx].set_source_status(_type, _value);
	
	this->mutex_db_handler->unlock();
	
	return ;
}

void AUDIO_SourceHandler::update_source_info(int _idx, string _type, string _value) {
	this->mutex_db_handler->lock();
			
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler->unlock();
		
		return ;
	}
	
	sqlite3 *handler;
	
	if( sqlite3_open(this->path_module_db.c_str(), &handler) ) {
		this->print_debug_info("open() can't open database file : %s [%s]\n" , sqlite3_errmsg(handler), this->path_module_db.c_str());
		this->mutex_db_handler->unlock();
		
		return ;
	}
	sqlite3_exec(handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	char query_string[1024];
	sprintf(query_string, "update source_info_list set %s='%s' where source_hash_id='%s';", _type.c_str(), _value.c_str(), this->v_source_list[_idx].get_source_info("source_hash_id").c_str());
	sqlite3_exec(handler, query_string, NULL, NULL, NULL);
	
	sqlite3_exec(handler, "END TRANSACTION;", NULL, NULL, NULL);
	sqlite3_close(handler);
	
	this->v_source_list[_idx].set_source_info(_type, _value);
	
	this->mutex_db_handler->unlock();
	
	return ;
}


void AUDIO_SourceHandler::update_source_info(int _idx, string _type, int _value) {
	this->mutex_db_handler->lock();
			
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler->unlock();
		
		return ;
	}
	
	sqlite3 *handler;
	
	if( sqlite3_open(this->path_module_db.c_str(), &handler) ) {
		this->print_debug_info("open() can't open database file : %s [%s]\n" , sqlite3_errmsg(handler), this->path_module_db.c_str());
		this->mutex_db_handler->unlock();
		
		return ;
	}
	
	char query_string[1024];
	sprintf(query_string, "update source_info_list set %s=%d where source_hash_id='%s';", _type.c_str(), _value, this->v_source_list[_idx].get_source_info("source_hash_id").c_str());
	sqlite3_exec(handler, query_string, NULL, NULL, NULL);
	sqlite3_close(handler);
	
	this->v_source_list[_idx].set_play_info(_type, _value);
	
	this->mutex_db_handler->unlock();
	
	return ;
}

void AUDIO_SourceHandler::listup_source_info(string _hash_id, bool _is_listup, int _loop_count) {
	this->mutex_db_handler->lock();
			
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler->unlock();
		
		return ;
	}
	
	sqlite3 *handler;
	
	if( sqlite3_open(this->path_module_db.c_str(), &handler) ) {
		this->print_debug_info("open() can't open database file : %s [%s]\n" , sqlite3_errmsg(handler), this->path_module_db.c_str());
		this->mutex_db_handler->unlock();
		
		return ;
	}
	sqlite3_exec(handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	char query_string[1024];
	sprintf(query_string, "update source_info_list set is_playlist=%d where source_hash_id='%s';", (int)_is_listup, _hash_id.c_str());
	sqlite3_exec(handler, query_string, NULL, NULL, NULL);
	
	sqlite3_exec(handler, "END TRANSACTION;", NULL, NULL, NULL);
	sqlite3_close(handler);

	if( _loop_count != -1 ) {
		sprintf(query_string, "update source_info_list set audio_loop_count=%d where source_hash_id='%s';", _loop_count, _hash_id.c_str());
		sqlite3_exec(handler, query_string, NULL, NULL, NULL);
		
		sqlite3_exec(handler, "END TRANSACTION;", NULL, NULL, NULL);
		sqlite3_close(handler);
	}
	
	for( int idx = 0 ; idx < (int)this->v_source_list.size() ; idx++ ) {
		if( this->v_source_list[idx].get_source_info("source_hash_id").compare(_hash_id) == 0 ) {
			this->v_source_list[idx].set_source_status("is_playlist", _is_listup);
			
			if( _loop_count != -1 ) {
				this->v_source_list[idx].set_play_info("audio_loop_count", _loop_count);
			}
			break;
		}
	}
	
	this->mutex_db_handler->unlock();
	
	return ;
}


////////////////////////////////////////


AUDIO_PlaybackHandler::AUDIO_PlaybackHandler(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	this->print_debug_info("AUDIO_PlaybackHandler() create instance\n");
	
	this->playback_handle	= NULL;
	
	this->is_init	= false;
	this->is_run	= false;
	this->is_loop	= false;
	this->is_pause	= false;

	this->sample_rate	= -1;
	this->channels		= -1;
	this->skip_bytes	= -1;
	this->audio_format	= -1;
	
	return ;
}

AUDIO_PlaybackHandler::~AUDIO_PlaybackHandler(void) {
	this->print_debug_info("AUDIO_Handler() instance destructed\n");
	
	return ;
}
		
void AUDIO_PlaybackHandler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	fprintf(stdout, "AUDIO_PlaybackHandler::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	return ;
}

void AUDIO_PlaybackHandler::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

void AUDIO_PlaybackHandler::init(int _sample_rate, int _channels, int _skip_bytes, int _audio_format) {
	if( this->is_init ) {
		this->print_debug_info("init() already init\n");
		return ;
	}
	
	if( this->is_run ) {
		this->print_debug_info("init() already running\n");
		return ;
	}
	
	this->is_init		 = false;
	this->is_run		 = false;
	this->is_loop		 = false;

	this->sample_rate		= _sample_rate;
	this->channels			= _channels;
	this->skip_bytes		= _skip_bytes;
	this->audio_format 		= _audio_format;
	
	this->print_debug_info("init() parameter information..\n");
	this->print_debug_info("init() parameter - sample rate      : [%d]\n", this->sample_rate);
	this->print_debug_info("init() parameter - channels         : [%d]\n", this->channels);
	this->print_debug_info("init() parameter - skip bytes       : [%d]\n", this->skip_bytes);
	this->print_debug_info("init() parameter - audio format     : [0x%04x]\n", this->audio_format);
	
	this->print_debug_info("init() init success\n");
	
	this->is_init = true;
	
	return ;
}

void AUDIO_PlaybackHandler::stop(void) {
	this->print_debug_info("stop() playback loop disable\n");
	this->is_loop = true;
	
	return ;
}

void AUDIO_PlaybackHandler::run(string _file_path) {
	int	 frame_latency = 0;
	int	 skip_bytes = 0;
	int	 read_size  = 0;
	int	 read_count = 0;
	char arr_skip_data[NUM_PERIOD_SIZE];
	char arr_read_data[NUM_PERIOD_SIZE], arr_null_data[NUM_PERIOD_SIZE];
	
	char *ptr_data = NULL;
	
	memset(arr_null_data, 0x00, NUM_PERIOD_SIZE);
	
	string 	file_path = _file_path;
	string 	ext_type  = file_path.substr(file_path.find_last_of(".") + 1);
	
	for_each(ext_type.begin(), ext_type.end(), [](char &_char) {
		_char = tolower(_char);
	});
	FILE *fp = fopen(_file_path.c_str(), "rb");

	
	if( ext_type.compare("mp3") == 0 ) {
		frame_latency = (NUM_PERIOD_SIZE * NUM_MP3_DATA_SCALE * 1000 * 1000) / (this->sample_rate * this->channels);
		read_size = NUM_PERIOD_SIZE;
		
	} else {
		frame_latency = (NUM_PERIOD_SIZE * 1000 * 1000) / (this->sample_rate * this->channels * 2);
		read_size = NUM_PCM_OFFSET;
		this->skip_bytes = NUM_PCM_OFFSET;
	}
	
	while( !this->is_loop && feof(fp) == 0 ) {
		read_count = fread(arr_skip_data, 1, read_size, fp);
		skip_bytes += read_count;
		
		if( skip_bytes == this->skip_bytes ) {
			break;
		}
		
		if( skip_bytes + read_size > this->skip_bytes ) {
			read_size = this->skip_bytes - skip_bytes;
		}
	}
	
	this->print_debug_info("run() skip bytes: [%d]\n", skip_bytes);
	this->print_debug_info("run() start read file format: [%s], frame_latency: [%d]\n", ext_type.c_str(), frame_latency);
	
	this->is_run = true;
	
	while( !this->is_loop && feof(fp) == 0 ) {
		if( this->is_pause ) {
			read_count = NUM_PERIOD_SIZE;
			ptr_data   = arr_null_data;
					
		} else {
			read_count = fread(arr_read_data, 1, NUM_PERIOD_SIZE, fp);
			ptr_data   = arr_read_data;
		}

		if( this->playback_handle != NULL ) {
			this->playback_handle(&ptr_data, &read_count);
		}
		
		usleep(frame_latency);
	}
	
	this->print_debug_info("run() stop playback\n");
	
	this->is_init = false;
	this->is_run  = false;
	this->is_loop = true;
	
	return ;
}

void AUDIO_PlaybackHandler::set_playback_pause(void) {
	this->print_debug_info("set_playback_pause() set playback : [pause]\n");
	this->is_pause = true;
	
	return ;
}

void AUDIO_PlaybackHandler::set_playback_play(void) {
	this->print_debug_info("set_playback_play() set playback : [play]\n");
	this->is_pause = false;
	
	return ;
}

void AUDIO_PlaybackHandler::set_playback_handler(void (*_func)(char **, int *)) {
	this->print_debug_info("set_playback_handler() set playback queue function\n");
	
	this->playback_handle = *_func;
	
	return ;
}