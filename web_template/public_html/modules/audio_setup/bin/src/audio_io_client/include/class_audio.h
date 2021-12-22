#ifndef __CLASS_AUDIO_H__
#define __CLASS_AUDIO_H__

#include <stdarg.h>
#include <unistd.h>

#include <string>
#include <vector>
#include <tuple>
#include <mutex>

#include "api_sqlite.h"
#include "api_json_parser.h"

using namespace std;

class AUDIO_Handler {
		const int		TIME_DB_LOCK_WAIT	= 10000;	// msec
		const int		DFLT_LEVEL_VALUE 	= 0;
		
	private :
		bool			is_debug_print	= false;
		void			print_debug_info(const char *_format, ...);
		
		string			str_ps_name;
		
		int				audio_volume;
		int				level_value;
		bool			is_p_amp_device;
		
		bool			is_server_alive;
		bool			is_client_alive;
		bool			is_mp3_encode;
		
		string			path_module_db;
		string			network_cast_type;
		string			network_ip_addr;
		
		mutex			mutex_db_handler;
		mutex			mutex_alive_info;
		mutex			mutex_level_info;
		mutex			mutex_playback_info;
		mutex			mutex_volume;
		mutex			mutex_encode;
		
		string			str_current_ip;
		int				num_current_port;
		
		vector<tuple<string, int>> v_playback_info;
		
	public  :
		AUDIO_Handler(bool _is_debug_print = false);
		~AUDIO_Handler(void);
		
		void 			set_debug_print(void);
		
		void			set_ps_name(string _name);
		string			get_ps_name(void);
		
		void			get_env_status(void);
		
		int				get_level_value(void);
		void			set_level_value(int _value);
		
		void			set_database_path(string _path);
		int 			update_database_status(string _type, string _field, string _value, string _format);
		int 			update_database_status(string _type, string _field, string _value);
		int 			update_database_status(string _type, string _field, int _value);
		
		bool			is_amp_device(void);
		bool			is_module_use(string _type);
		bool			is_module_status(string _type);
		
		bool			is_alive_status(string _type);
		void			set_alive_status(string _type, bool _is_alive);

		bool			is_network_cast_type(string _type, string _cast_type);
		string			get_network_cast_type(void);
		
		void			set_network_ip_addr(string _ip_addr);
		string			get_network_ip_addr(void);

		string			get_database_status(string _type, string _field);
		
		void			set_playback_info(string _type, int _value);
		int				get_playback_info(string _type);
		
		double 			calc_diff_time(struct timeval _x, struct timeval _y);
		
		int				get_audio_volume(void);
		void			set_audio_volume(int _volume);
		
		bool			is_encode_status(void);
		void			set_encode_status(string _encode_mode);

		void			set_current_server_info(string _ip_addr, int _port);
		tuple<string, int> get_current_server_info(void);
		
		bool			is_exist_db_colume(string _table, string _colume);
		int				add_db_colume(string _table, string _colume, string _type);
};


class CHIME_Handler {
	private :
		bool			is_debug_print	= false;
		void			print_debug_info(const char *_format, ...);

		FILE 			*fp;

		bool 			is_play_chime;
		bool 			is_mix_chime;
		int  			num_chime_index;
		int				num_volume;

	public  :
		CHIME_Handler(bool _is_debug_print = false);
		~CHIME_Handler(void);
		
		void 			set_debug_print(void);

		void			close(void);
		void			open(string _path_chime_file);
		
		void			set_chime_play_status(bool _status);
		void			set_chime_mix_status(int _mix_value);
		void			set_chime_index(int _idx);
		void			set_chime_volume(int _volume);

		bool			is_chime_play_status(void);
		bool			is_chime_mix_status(void);
		int				get_chime_index(void);
		int				get_chime_volume(void);

		bool			is_eof(void);
		int				read(short *_data, int _size);
};
#endif