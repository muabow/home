#ifndef __CLASS_PCM_HANDLER_H__
#define __CLASS_PCM_HANDLER_H__

#include <alsa/asoundlib.h>
#include <thread>
#include <stdarg.h>

namespace COMMON {
	using namespace std;

	class PCM_CaptureHandler {
		const int 			SIZE_PCM_PERIODS	= 4;
		const int 			TIME_DIV_PERIODS	= 4;
		const int			TIME_SLEEP_RESET	= 1000;
		
		private:
			bool			is_init;
			bool			is_run;
			bool			is_loop;
			bool			is_debug_print = false;
			
			char			device_name[128];
			char    		*chunk_data;
			unsigned int	frame_bytes;
			unsigned int	size_pcm_periods  = SIZE_PCM_PERIODS;
			unsigned int	time_div_periods  = TIME_DIV_PERIODS;

			int     		chunk_size;
			unsigned int	channels;
			unsigned int	sample_rate;

			unsigned int	buffer_size;
			unsigned int	period_size;

			void			(*set_queue)(char *, int);
			
			snd_pcm_t       *t_pcm_handler;

			thread			thread_func;

		public :
			PCM_CaptureHandler(bool _is_debug_print = false);
			~PCM_CaptureHandler(void);	

			void    run(void);
			void    stop(void);

			bool	init(string _device_name = "", 
						 int    _chunk_size = -1, 
						 int    _sample_rate = -1, 
						 int    _channels = -1, 
						 int    _size_pcm_periods = -1, 
						 int    _time_div_periods = -1
					);
			
			void    execute(void);
			bool    set_pcm_driver(void);
			void	set_queue_handler(void (*_func)(char *, int));
			
			void	print_debug_info(const char *_format, ...);
			void	set_debug_print(void);

			unsigned int	get_pcm_buffer_size(void);
			unsigned int	get_pcm_period_size(void);
			
			// before init
			void set_pcm_period_size(int _size_pcm_periods);
			void set_pcm_div_period_time(int _time_div_periods);
	};
	
	class PCM_PlaybackHandler {
		const int 			SIZE_PCM_PERIODS	= 4;
		const int 			TIME_DIV_PERIODS	= 4;
		const int			TIME_SLEEP_RESET	= 1000;
		
		private:
			bool			is_init;
			bool			is_run;
			bool			is_loop;
			bool			is_debug_print = false;
			
			char			device_name[128];
			unsigned int	frame_bytes;
			unsigned int	size_pcm_periods  = SIZE_PCM_PERIODS;
			unsigned int	time_div_periods  = TIME_DIV_PERIODS;

			int     		chunk_size;
			unsigned int	channels;
			unsigned int	sample_rate;

			unsigned int	buffer_size;
			unsigned int	period_size;
			
			short    		*chunk_data       = NULL;
			
			void			(*get_queue)(short **, int *);
			
			snd_pcm_t       *t_pcm_handler;

			thread			thread_func;
		
		public :
			PCM_PlaybackHandler(bool _is_debug_print = false);
			~PCM_PlaybackHandler(void);
			
			void	run(void);
			void	stop(void);
			
			bool	init(string _device_name = "", 
						 int    _chunk_size = -1, 
						 int    _sample_rate = -1, 
						 int    _channels = -1, 
						 int    _size_pcm_periods = -1, 
						 int    _time_div_periods = -1
					);
			void    execute(void);
			bool    set_pcm_driver(void);
			void	get_queue_handler(void (*_func)(short **, int *));
			
			void	print_debug_info(const char *_format, ...);
			void	set_debug_print(void);
	};
}

#endif