#ifndef __CLASS_AUDIO_H__
#define __CLASS_AUDIO_H__

#include <stdarg.h>
#include <unistd.h>
#include <sys/types.h>
#include <dirent.h>
#include <sys/stat.h>

#include <string>
#include <vector>
#include <tuple>
#include <mutex>
#include <algorithm>

#include "api_sqlite.h"
#include "api_json_parser.h"

using namespace std;

class AUDIO_Handler {
		const int		DFLT_LEVEL_VALUE 	= 0;
		
	private :
		bool			is_debug_print	= false;
		void			print_debug_info(const char *_format, ...);
		
		int				audio_volume;
		int				level_value;
		
		bool			is_mp3_encode;
		
		mutex			mutex_level_info;
		mutex			mutex_playback_info;
		mutex			mutex_volume;
		mutex			mutex_encode;
		
		vector<tuple<string, int>> v_playback_info;
		
	public  :
		AUDIO_Handler(bool _is_debug_print = false);
		~AUDIO_Handler(void);
		
		void 			set_debug_print(void);
		
		int				get_level_value(void);
		void			set_level_value(int _value);
		
		void			set_playback_info(string _type, int _value);
		int				get_playback_info(string _type);
		
		int				get_audio_volume(void);
		void			set_audio_volume(int _volume);
		
		bool			is_encode_status(void);
		void			set_encode_status(string _encode_mode);
};


class AUDIO_PlayerHandler {
	private :
		bool			is_debug_print	= false;
		void			print_debug_info(const char *_format, ...);

		// player status
		bool			is_run;
		bool			is_play;
		bool			is_pause;
		bool			is_loop;
		
		int				audio_play_index;
		int				audio_volume;
		
		// player control
		bool			is_play_prev;
		bool			is_play_next;
		bool			is_play_stop;
		bool			is_force_stop;
		bool			is_invalid_source;
		bool			is_change_index;
			
	public  :
		AUDIO_PlayerHandler(bool _is_debug_print = false);
		~AUDIO_PlayerHandler(void);
		
		void 			set_debug_print(void);
		
		void			set_player_status(string _type, bool _status);
		bool			get_player_status(string _type);
		
		void			set_player_control(string _type, bool _status);
		bool			get_player_control(string _type);
		
		void			set_player_index(int _index);
		int				get_player_index(void);
		
		void			set_player_volume(int _volume);
		int				get_player_volume(void);
};

class AUDIO_SourceInfo {
	struct  WAV_HEADER {
		/* RIFF Chunk Descriptor */
	    char	riff[4];			// RIFF Header, Magic header
	    int		chunk_size;			// RIFF Chunk Size
	    char	wave[4];			// WAVE Header

	    /* "fmt" sub-chunk */
	    char	fmt[4];				// FMT header       
	    int		fmt_chunk_size;		// Size of the fmt chunk
	    short	audio_format;		// Audio format 1=PCM,6=mulaw,7=alaw, 257=IBM Mu-Law, 258=IBM A-Law, 259=ADPCM 
	    short	channels;			// Number of channels 1=Mono 2=Sterio                   
	    int		sample_rate;		// Sampling Frequency in Hz
	    int		bytes_per_sec;		// bytes per second
	    short	block_align;		// 2=16-bit mono, 4=16-bit stereo
	    short	bits_per_sample;	// Number of bits per sample
	    
	    /* "data" sub-chunk */
	    char	data_id[4];			// "data"  string   
	    int	data_size;				// Sampled data length    
	} typedef WAV_HEADER_t; 
	
	struct MP3_ID3_TAG {
		char	id[3];
		char	version[2];
		char	flags;
		char	size[4];
	} typedef MP3_ID3_TAG_t;
	
	struct MP3_HEADER {
		int     version;
		int     layer;
		int     errp;
		int     bitrate;
		int     freq;
		int     pad;
		int     priv;
		int     mode;
		int     modex;
		int     copyright;
		int     original;
		int     emphasis;
	} typedef MP3_HEADER_t;
	
	const static int SIZE_ID3_TAG_FOOT	= 10;
	
	const static int MPEG_VERSION_2_5	= 0;
	const static int MPEG_VERSION_RSVD	= 1;
	const static int MPEG_VERSION_2_0	= 2;
	const static int MPEG_VERSION_1_0	= 3;
	
	const static int MPEG_LAYER_RSVD	= 0;
	const static int MPEG_LAYER_3		= 1;
	const static int MPEG_LAYER_2		= 2;
	const static int MPEG_LAYER_1		= 3;
	
	int LIST_SAMPLE_PER_FRAME[4][4] = {	
										{0,	576,	1152,	384	},	// MPEG v2.5 - rsvd, Layer3, Layer2, Layer1  
										{0,	0,		0, 		0	},	// MPEG rsvd - rsvd, Layer3, Layer2, Layer1
										{0,	576, 	1152,	384	},	// MPEG v2.0 - rsvd, Layer3, Layer2, Layer1 
										{0,	1152,	1152,	1152}	// MPEG v1.0 - rsvd, Layer3, Layer2, Layer1
									};
	
	int LIST_SAMPLE_RATE[4][4]		= {
										{11025,	12000,	8000,	0},	// MPEG v2.5 - 0x 00, 01, 10, 11
										{0,		0,		0,		0},	// MPEG rsvd - 0x 00, 01, 10, 11
										{22050, 24000,	16000,	0},	// MPEG v2.0 - 0x 00, 01, 10, 11
										{44100, 48000,	32000,	0}	// MPEG v1.0 - 0x 00, 01, 10 ,11
									};
	
	int LIST_BIT_RATE[5][16]		= {
										{0,	 32,  64,  96, 128, 160, 192, 224, 256, 288, 320, 252, 384, 416, 448, 0}, 	// MPEG v1.0 	 - Layer1
										{0,  32,  48,  56,  64,  80,  96, 112, 128, 160, 192, 224, 256, 320, 384, 0}, 	// MPEG v1.0 	 - Layer2
										{0,  32,  40,  48,  56,  64,  80,  96, 112, 128, 160, 192, 224, 256, 320 ,0}, 	// MPEG v1.0 	 - Layer3
										{0,  32,  48,  56,  64,  80,  96, 112, 128, 144, 160, 176, 192, 224, 256, 0}, 	// MPEG v2.0/2.5 - Layer1
										{0,   8,  16,  24,  32,  40,  48,  56,  64,  80,  96, 112, 128, 144, 160, 0} 	// MPEG v2.0/2.5 - Layer2
									};
	
	const static int WAV_FORMAT_PCM			= 0x0001;
	const static int WAV_FORMAT_IEEE_FLOAT	= 0x0003;
	const static int WAV_FORMAT_ALAW		= 0x0006;
	const static int WAV_FORMAT_MULAW		= 0x0007;
	const static int WAV_FORMAT_EXTENSIBLE	= 0xFFFE;
	
	private :
		bool			is_debug_print	= false;
		void			print_debug_info(const char *_format, ...);
		
		bool			is_play;
		bool			is_pause;
		bool			is_playlist;
		bool			is_valid_source;
		
		string			source_hash_id;
		string			source_file_path;
		string			source_name;
		string			source_type;
		
		int				audio_play_time;
		int				audio_loop_count;
		
		int				num_audio_format;
		int				num_sample_rate;
		int				num_channels;
		int				num_bit_rate;
		int				num_bits_per_sample;
		
		int				num_mp3_skip_bytes;
		
		string			get_info_file_ext(string _file);
		string			get_info_file_name(string _file);
		
		bool			parse_source_wav(void);
		bool			parse_source_mp3(void);
		
	public  :
		AUDIO_SourceInfo(bool _is_debug_print = false);
		~AUDIO_SourceInfo(void);
		
		void 			set_debug_print(void);
		
		bool			is_valid_ext_type(string _file_path);
		void			set_source_info(string _file_path);
		
		bool			get_source_status(string _type);
		string			get_source_info(string _type);
		int				get_play_info(string _type);
		
		void			set_source_status(string _type, bool _value);
		void			set_source_info(string _type, string _value);
		void			set_play_info(string _type, int _value);
};

class AUDIO_SourceHandler {
	const int			DIRENT_TYPE_DIR			= 0x4;
	const int			DIRENT_TYPE_FILE		= 0x8;
	
	const static int	COL_IS_VALID_SOURCE		= 0;
	const static int	COL_IS_PLAY				= 1;
	const static int	COL_IS_PLAYLIST			= 2;
	const static int	COL_SOURCE_HASH_ID		= 3;
	const static int	COL_SOURCE_FILE_PATH	= 4;
	const static int	COL_SOURCE_NAME			= 5;
	const static int	COL_SOURCE_TYPE			= 6;
	const static int	COL_AUDIO_PLAY_TIME		= 7;
	const static int	COL_AUDIO_LOOP_COUNT	= 8;
	
	
	private :
		bool			is_debug_print	= false;
		void			print_debug_info(const char *_format, ...);
		
		string			path_source_dir;
		string			path_module_db;
		
		vector<AUDIO_SourceInfo> v_source_list;
		vector<AUDIO_SourceInfo> v_exist_source_list;
		
		mutex			mutex_source_list;
		mutex			*mutex_db_handler;
		
	public  :
		AUDIO_SourceHandler(bool _is_debug_print = false);
		~AUDIO_SourceHandler(void);
		
		void 			set_debug_print(void);
		
		void			set_source_path(string _path);
		void			set_database_path(string _path);
		
		void			set_mutex_db_handler(mutex *_mutex_handler);
		
		void			read_source_list(void);
		void 			sort_source_list(vector<string> _v_hash_id_list);
		void			remove_source_list(string _hash_id);
		
		static int		callback_is_exist(void *_this, int _argc, char **_argv, char **_col_name);
		void			sync_database_list(void);
		void			change_database_list(void);
		
		vector<AUDIO_SourceInfo> get_source_list(void);
		string			make_json_source_list(void);
		string			make_json_source_name_list(void);
		
		void			update_source_info(int _idx, string _type, bool _value);
		void			update_source_info(int _idx, string _type, string _value);
		void			update_source_info(int _idx, string _type, int _value);
		
		void			listup_source_info(string _hash_id, bool _is_listup, int _loop_count);
}; 


class AUDIO_PlaybackHandler {
	const int			NUM_PCM_OFFSET		= 64;
	const int			NUM_MP3_DATA_SCALE	= 4;
	
	const int			NUM_PERIOD_SIZE		= 1152;
	
	private :
		bool			is_debug_print	= false;
		void			print_debug_info(const char *_format, ...);
		
		bool			is_init;
		bool			is_run;
		bool			is_loop;
		bool			is_pause;
		
		int				sample_rate;
		int				channels;
		int				skip_bytes;
		int				audio_format;
				
		void			(*playback_handle)(char **, int *);
		
	public  :
		AUDIO_PlaybackHandler(bool _is_debug_print = false);
		~AUDIO_PlaybackHandler(void);
		
		void 			set_debug_print(void);
		
		void			init(int _sample_rate, int _channels, int _skip_bytes, int _audio_format);
		void			stop(void);
		void			run(string _file_path);
		
		void			set_playback_pause(void);
		void			set_playback_play(void);
		
		void			set_playback_handler(void (*_func)(char **, int *));
		
};

#endif