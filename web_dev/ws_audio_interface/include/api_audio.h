#ifndef __API_AUDIO_H__
#define __API_AUDIO_H__

#include <alsa/asoundlib.h>
#include <thread>
#include <stdarg.h>

/* # AUDIO Handler API
  1. 개발 완료 목록 (2019.03.27, v0.0.0.1)
   + PCM_CaptureHandler     (server/client model - server side)
    : Ananlog input to PCM data
   + PCM_PlaybackHandler    (server/client model - client side)
    : PCM data to analog output 
 
  2. 개발 예정 목록 
   - MP3_EncodeHandler      (server/client model - server side)
    : PCM chunk_data to MP3 frame_data 
   - MP3_DecodeHandler      (client/client model - client side)
    : MP3_frame_data to PCM chunk_data
   - WAV_CaptureHandler     (server/client model - server side)
    : WAV file data to client, file streaming 
   - MP3_CaptureHandler     (server/client model - server side)
    : MP3 file data to client, file streaming 
   - WAV_FILE_Player        (local player)
    : Local WAV File player (단일/복수/폴더 재생, 1회/횟수/반복 재생)
    : source_file_management module 내 data source 파일 공유 
   - MP3_FILE_Player        (local player)
    : Local MP3 File player (단일/복수/폴더 재생, 1회/횟수/반복 재생)
    : source_file_management module 내 data source 파일 공유
 */


using namespace std;

class PCM_CaptureHandler {
	const int 			SIZE_PCM_PERIODS	= 4;
	const int 			TIME_DIV_PERIODS	= 4;
	const int			TIME_SLEEP_RESET	= 1000;
	
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

		void    		execute(void);
		void			(*set_queue)(char *, int);

		char    		*chunk_data			= NULL;
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

		void			print_debug_info(const char *_format, ...);
};

class PCM_PlaybackHandler {
	const int 			SIZE_PCM_PERIODS	= 4;
	const int 			TIME_DIV_PERIODS	= 4;
	const int			TIME_SLEEP_RESET	= 1000;
	
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
		
		void    		execute(void);
		void			(*get_queue)(short **, int *);
		
		short    		*chunk_data			= NULL;
		snd_pcm_t       *t_pcm_handler		= NULL;

		thread			thread_func;
	
	public :
		PCM_PlaybackHandler(bool _is_debug_print = false);
		~PCM_PlaybackHandler(void);
		
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
		void			set_debug_print(void);

		void			get_queue_handler(void (*_func)(short **, int *));
		
		void			print_debug_info(const char *_format, ...);
};

#endif