#ifndef __API_PCM_PLAYBACK_H__
#define __API_PCM_PLAYBACK_H__

#include <stdio.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>
#include <stdarg.h>
#include <math.h>
#include <alsa/asoundlib.h>

#include <thread>
#include <vector>
#include <tuple>
#include <mutex>

using namespace std;

class PCM_PlaybackHandler {
	const int 			SIZE_PCM_PERIODS	= 4;
	const int 			TIME_DIV_PERIODS	= 4;
	const int			DFLT_CHUNK_SIZE		= 1024;
	
	const int 			NUM_BUFFER_SCALE 	= 4;
	const int			NUM_DROP_SCALE		= 4;
	const int			NUM_DROP_FRAME		= 2;
	
	const int			TIME_SLEEP_RESET	= 1000;
	const int			TIME_WAIT_MUTE		= 100000;
	
	struct HEADER_INFO {
		char		status;			// 1  // stop, play, pause
		char		encode_type;    // 2  // pcm, mp3
		short		data_size;     	// 4  // 1152
		int			sample_rate;   	// 8  // 44100
		short		channels;     	// 10 // 2
		short		mp3_quality;	// 12 // 2
	} typedef HEADER_INFO_t;
	
	private:
		bool			is_init				= false;
		bool			is_run				= false;
		bool			is_loop				= false;
		bool			is_debug_print 		= false;
		
		unsigned int	size_pcm_periods	= SIZE_PCM_PERIODS;
		unsigned int	time_div_periods	= TIME_DIV_PERIODS;

		char			device_name[128];
		unsigned int	sample_rate;
		unsigned int	channels;
		unsigned int	chunk_size;
		unsigned int	frame_bytes;
		unsigned int	buffer_size;
		unsigned int	period_size;
		
		int				time_buffer_delay;				
		double			frame_latency;
		
		void			print_debug_info(const char *_format, ...);

		void    		execute(void);
		
		void			(*playback_handle)(char **, int *);
		void			(*control_handle)(char **,  int *);
		void			(*mute_handle)(bool);
		void			(*term_handle)(void);
		
		snd_pcm_t       *t_pcm_handler = NULL;
		
		thread			thread_func;
		mutex			mutex_func;
		
		vector<tuple<char *, int>>	v_queue_data;

	public :
		PCM_PlaybackHandler(bool _is_debug_print = false);
		~PCM_PlaybackHandler(void);
		
		
		void				run(void);
		void				stop(void);
		void				close(void);
		
		bool				init(string _device_name		= "", 
								 int    _chunk_size			= -1, 
								 int    _sample_rate		= -1, 
								 int    _channels			= -1, 
								 int    _size_pcm_periods	= -1, 
								 int    _time_div_periods	= -1
							);
			
		bool				set_pcm_driver(void);
		void				close_pcm_driver(void);
		
		void				set_debug_print(void);
		void				change_debug_print(bool _is_debug_print);
		
		int					get_play_info(string _type);
		double				get_frame_latency(void);

		void				set_time_buffer_delay(int _time);
		
		void				set_playback_handler(void (*_func)(char **, int *));
		void				set_control_handler(void (*_func)(char **, int *));
		void				set_mute_handler(void (*_func)(bool));
		void				set_term_handler(void (*_func)(void));
		void				reset_control_handler(void);
		
		void				set_data_handler(char *_data, int _size);
		
		bool				is_term(void);
};

#endif