#ifndef __API_MP3_DECODE_H__
#define __API_MP3_DECODE_H__

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
#include <vector>

#include <lame/lame.h>
#include <assert.h>


using namespace std;

class MP3_Decoder {
	void 				(*lame_error_handle)(const char *_format, va_list _args);
	
	static const int	SIZE_DECODE_SAMPLES	= 1152;
	static const int	SIZE_DECODED_PCM	= 4608;

	const int 			NUM_CHANNEL_MONO	= 1;
	const int 			NUM_CHANNEL_STEREO	= 2;	

	const int			NUM_ERROR_SKIP_CNT	= 3;
	
	const int			TIME_SLEEP_WAIT		= 10000;
	
	private:
		bool			is_debug_print 		= false;
		void			print_debug_info(const char *_format, ...);

		bool			is_init;
		bool			is_reset;
		bool 			is_decoding;
		
		int				num_sample_rate;
		int				num_channels;
		
		int				num_volume;
		int				peak_volume;
		
		short 			ptr_pcm_data[SIZE_DECODED_PCM  * 2 * (8 + 1)];
		int 			arr_pcm_length[(8 + 1)];

		unsigned char 	arr_mp3_data[SIZE_DECODED_PCM];
		short 			decoded_pcm_left[SIZE_DECODED_PCM];
		short 			decoded_pcm_right[SIZE_DECODED_PCM];
		
		hip_t 			decoder;
		
	public :
		MP3_Decoder(bool _is_debug_print = false);
		~MP3_Decoder(void);
		
		bool			is_error;
		
		void			set_debug_print(void);
		void			set_report_handler(void (*_func)(const char *, va_list));

		void			init(int _sample_rate = -1, int _channels = -1);
		void			stop(void);
		
		int				get_decoded_sample(void);
		int				get_error_skip_cnt(void);
		int				get_error_buffer_size(void);
		
		bool			is_reset_status(void);
		void			set_reset_status(bool _status);
		void			set_volume(int _volume);
		
		int				get_decode_samples(void);
		int				get_decode_peak_volume(void);
		
		tuple<char *, int *, int> decode(char *_input_data, int _data_size);
};

#endif