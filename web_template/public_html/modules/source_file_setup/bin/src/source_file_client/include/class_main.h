#ifndef __CLASS_MAIN_H__
#define __CLASS_MAIN_H__

#include <stdarg.h>
#include <unistd.h>
#include <sys/types.h>
#include <dirent.h>
#include <sys/stat.h>

#include <fstream>
#include <iostream>
#include <sstream>
#include <string>
#include <tuple>
#include <vector>
#include <mutex>
#include <algorithm>

#include "api_sqlite.h"
#include "api_json_parser.h"

using namespace std;

class MAIN_Handler {
	const char	*PATH_DEVICE_KEY_LIST 		= "/opt/interm/key_data/device_key_list.json";
	const char  *PATH_MANAGER_SERVER        = "/opt/interm/conf/config-manager-server.db";
	const char	*PATH_MACHINE_ID			= "/etc/machine-id";
	const char	*PATH_MODULE_NETWORK_INFO	= "/opt/interm/public_html/modules/network_setup/conf/network_stat.json";
	
	const int	TIME_DB_LOCK_WAIT			= 10000;	// msec
	
	private :
		bool			is_debug_print	= false;
		void			print_debug_info(const char *_format, ...);
		
		string			path_module_config;
		string			path_module_db;

		string			str_ps_name;
		string			str_network_ip_addr;
		
		bool			is_p_amp_device;
		bool			is_alive;
		
		int				status_module;
		
		mutex			mutex_db_handler;
		
		string			str_current_ip;
		int				num_current_port;
		
		bool			is_mp3_encode;
		
	public  :
		MAIN_Handler(bool _is_debug_print = false);
		~MAIN_Handler(void);
		
		void 			set_debug_print(void);
		
		string			get_machine_id(void);
		string			get_network_info(void);
		string			get_network_hostname(void);
		
		void			set_module_config_path(string _path);
		
		string			get_mng_server_info(void);
		bool			is_mng_server_extend(string _ip_addr);
		
		tuple<string, string> get_device_api_key(string _ip_addr);
		
		bool			file_exist(string _dst);
		bool			is_module_device(void);
		
		void			set_database_path(string _path);
		int 			update_databse_status(string _table_name, string _key, string _value, string _format);
		int 			update_databse_status(string _table_name, string _key, string _value);
		int 			update_databse_status(string _table_name, string _key, int _value);

		string			get_database_status(string _table_name, string _key);
		
		mutex		   *get_mutex_db_handler(void);
		
		bool			is_module_use(string _table_name);
		bool			is_module_status(string _table_name);
		
		bool			is_amp_device(void);
		
		void			set_ps_name(string _name);
		string			get_ps_name(void);
		
		void			get_env_status(void);
		
		double 			calc_diff_time(struct timeval _x, struct timeval _y);
		
		void			set_alive_status(bool _status);
		bool			is_alive_status(void);
		
		void			set_network_ip_addr(string _ip_addr);
		string			get_network_ip_addr(void);
		
		void			set_current_server_info(string _ip_addr, int _port);
		tuple<string, int>	get_current_server_info(void);
		
		bool			is_encode_status(void);
		void			set_encode_status(string _encode_mode);
};

 
#endif