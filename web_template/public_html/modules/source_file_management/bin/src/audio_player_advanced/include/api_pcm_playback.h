#ifndef __API_PCM_PLAYBACK_H__
#define __API_PCM_PLAYBACK_H__

#include <stdio.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>
#include <stdarg.h>
#include <math.h>
#include <alsa/asoundlib.h>
#include <sys/types.h>

#include <thread>
#include <vector>
#include <tuple>
#include <mutex>
#include <algorithm>
#include <memory>


#include "api_mp3_decode.h"

using namespace std;

class PCM_PlaybackHandler {
	const int 			SIZE_PCM_PERIODS	= 4;
	
	const int 			TIME_DIV_PERIODS	= 4;
	const int			TIME_SLEEP_RESET	= 1000;
	const int			TIME_SLEEP_TERM		= 1000;
	
	const int			NUM_PCM_OFFSET		= 44;
	const int			NUM_TERM_LOOP		= 1000;
	
	const static int	 MP3_FORMAT				= 0x0000;
	const static int	 WAV_FORMAT_PCM			= 0x0001;
	const static int	 WAV_FORMAT_IEEE_FLOAT	= 0x0003;
	const static int	 WAV_FORMAT_ALAW		= 0x0006;
	const static int	 WAV_FORMAT_MULAW		= 0x0007;
	const static int	 WAV_FORMAT_EXTENSIBLE	= 0xFFFE;

	private:
		bool			is_init				= false;
		bool			is_run				= false;
		bool			is_loop				= false;
		bool			is_debug_print 		= false;
		
		bool			is_pause			= false;
		
		unsigned int	size_pcm_periods	= SIZE_PCM_PERIODS;
		unsigned int	time_div_periods	= TIME_DIV_PERIODS;

		char			device_name[128];
		unsigned int	sample_rate;
		unsigned int	channels;
		unsigned int	chunk_size;
		unsigned int	frame_bytes;
		unsigned int	buffer_size;
		unsigned int	period_size;
		
		int				num_volume;
		
		int				time_buffer_delay;		
		double			frame_latency;
		
		int				skip_bytes;
		int				skip_end_bytes;
		int				audio_format;
		int				byte_per_sample;
		
		void			print_debug_info(const char *_format, ...);

		void			(*playback_handle)(char **, int *, bool, int);
		void			(*control_handle)(char **,  int *);
		void			(*mute_handle)(void);
		void			(*error_handle)(void);
		
		snd_pcm_t       *t_pcm_handler	= NULL;
		snd_pcm_format_t format			= SND_PCM_FORMAT_S16_LE;
		
	public :
		PCM_PlaybackHandler(bool _is_debug_print = false);
		~PCM_PlaybackHandler(void);
		
		void				run(string _file_path); 
		void				stop(void);
		void				close(void);
		
		bool				init(string _device_name, 
								 int    _chunk_size, 
								 int    _sample_rate, 
								 int    _channels,
								 int    _skip_bytes,
								 int	_audio_format,
								 int	_byte_per_sample,
								 int    _skip_end_bytes = -1
							);
			
		bool				set_pcm_driver(void);
		void				close_pcm_driver(void);
		
		void				set_debug_print(void);
		void				change_debug_print(bool _is_debug_print);
		
		int					get_play_info(string _type);
		double				get_frame_latency(void);

		void				set_playback_pause(void);
		void				set_playback_play(void);
		
		void				set_time_buffer_delay(int _time);
		
		void				set_playback_handler(void (*_func)(char **, int *, bool, int));
		void				set_control_handler(void (*_func)(char **, int *));
		void				set_mute_handler(void (*_func)(void));
		void				set_error_handler(void (*_func)(void));
		void				reset_control_handler(void);
		
		void				set_decode_volume(int _volume);
};

#endif