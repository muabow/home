#ifndef __API_PCM_CAPTURE_H__
#define __API_PCM_CAPTURE_H__

#include <stdio.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>
#include <math.h>
#include <stdarg.h>
#include <alsa/asoundlib.h>
#include <sys/time.h>

#include <thread>
#include <chrono>
#include <iostream>
#include <cmath>

using namespace std;
using namespace chrono;

class PCM_CaptureHandler {
	const int 			SIZE_PCM_PERIODS	= 4;	// 64
	const int 			SIZE_IMX_PERIODS	= 64;
	const int 			TIME_DIV_PERIODS	= 4;
	const int			TIME_SLEEP_RESET	= 10000;
	
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
		double			frame_latency;

		void			print_debug_info(const char *_format, ...);

		void    		execute(void);
		void			(*set_queue)(char *, int);

		snd_pcm_t       *t_pcm_handler		= NULL;

		thread			thread_func;

	public :
		PCM_CaptureHandler(bool _is_debug_print = false);
		~PCM_CaptureHandler(void);	

		void			run(void);
		void			stop(void);
		
		bool			init(string _device_name		= "", 
							 int    _chunk_size			= -1, 
							 int    _sample_rate		= -1, 
							 int    _channels			= -1, 
							 int    _size_pcm_periods	= -1, 
							 int    _time_div_periods	= -1
						);
		
		bool			set_pcm_driver(void);
		void			set_queue_handler(void (*_func)(char *, int));
		void			set_debug_print(void);
		
		unsigned int	get_pcm_buffer_size(void);
		unsigned int	get_pcm_period_size(void);
		
		double			get_frame_latency(void);
		
		int				get_sample_rate(void);
		int				get_channels(void);
		int				get_chunk_size(void);
		int 			get_pcm_periods(void);

		bool			is_device_imx(void);
};

#endif