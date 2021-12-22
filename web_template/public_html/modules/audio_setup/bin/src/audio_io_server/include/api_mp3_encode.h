#ifndef __API_MP3_ENCODE_H__
#define __API_MP3_ENCODE_H__

#include <stdio.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>
#include <math.h>
#include <stdarg.h>

#include <cstdio>
#include <cstring>
#include <iostream>
#include <cmath>
#include <ctime>
#include <tuple>

#include <lame/lame.h>


using namespace std;

class MP3_Encoder {
	const	int			DFLT_BYTE_PER_SAMPLE	= 2;
	const	int			NUM_CHANNEL_STEREO		= 2;
	const	int			NUM_CHANNEL_MONO		= 1;
	
	
	private:
		bool			is_debug_print 			= false;
		void			print_debug_info(const char *_format, ...);

		bool			is_init;
		bool 			is_encoding;
		
		int				num_chunk_size;
		int				num_sample_rate;
		int				num_channels;
		int				num_vbr_quality;
		int				num_byte_per_sample;
		
		int				num_pcm_buffer_length;
		int				num_mp3_buffer_size;
		
		unsigned char	*ptr_mp3_data;
		
		MPEG_mode		stereo_mode;
		lame_t 			encoder;
		
	public :
		MP3_Encoder(bool _is_debug_print = false);
		~MP3_Encoder(void);	

		void			set_debug_print(void);

		void			set_byte_per_sample(int _value);
		int				get_mp3_buffer_size(void);
		
		void			init(int _chunk_size, int _sample_rate, int _channels, int _quality);
		void			stop(void);
		tuple<char *, int>	encode(char *_data);
};

#endif