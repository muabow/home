#ifndef __CLASS_MAIN_H__
#define __CLASS_MAIN_H__

#include <stdarg.h>
#include <sys/stat.h>
#include <unistd.h>

#include <fstream>
#include <iostream>
#include <sstream>
#include <string>
#include <tuple>
#include <thread>

#include "api_sqlite.h"
#include "api_json_parser.h"

using namespace std;

class MAIN_Handler {
	const char	*PATH_DEVICE_KEY_LIST 		= "/opt/interm/key_data/device_key_list.json";
	const char  *PATH_MANAGER_SERVER        = "/opt/interm/conf/config-manager-server.db";
	const char	*PATH_MACHINE_ID			= "/etc/machine-id";
	const char	*PATH_MODULE_NETWORK_INFO	= "/opt/interm/public_html/modules/network_setup/conf/network_stat.json";
	
	private :
		bool			is_debug_print	= false;
		void			print_debug_info(const char *_format, ...);
		
		string			path_module_config;
		
	public  :
		MAIN_Handler(bool _is_debug_print = false);
		~MAIN_Handler(void);
		
		void 			set_debug_print(void);
		
		string			get_machine_id(void);
		string			get_network_info(void);
		string			get_network_hostname(void);
		
		void			set_module_config_path(string _path);
		string			get_info_status(string _key);
		void			set_info_status(string _key, string _status);
		
		string			get_mng_server_info(void);
		bool			is_mng_server_extend(string _ip_addr);
		
		tuple<string, string> get_device_api_key(string _ip_addr);
		
		bool			file_exist(string _dst);
		bool			is_module_device(void);
		
};

class MAIN_LevelInfo {
	const int	TIME_SLEEP_LOOP				= 10000;	// msec
	
	private :
		bool			is_debug_print	= false;
		void			print_debug_info(const char *_format, ...);
		bool 			is_run;
		thread 			thread_func;

		void 			execute(void);
		void			(*event_handle)(int, int);

		vector<tuple<int, int>> 	v_level_list;

	public  :
		MAIN_LevelInfo(bool _is_debug_print = false);
		~MAIN_LevelInfo(void);
		
		void 			set_debug_print(void);

		void			run(void);
		void 			stop(void);
		void			set_event_handler(void (*_func)(int, int));

		void 			set_level_info(int _index, int _level_info);

};

 
#endif