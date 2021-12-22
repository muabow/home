#ifndef __API_SQLITE_H__
#define __API_SQLITE_H__

#include <sqlite3.h>
#include <stdarg.h>
#include <iostream>
#include <string>
#include <map>
#include <mutex>

/* # SQLITE Handler API
  1. 개발 완료 목록 (2019.04.17, v0.0.0.2)
   + SqlHandler
	: query - select, get_str(), get_int()
    : query - update, set_str(), set_int()
	
  2. 개발 예정 목록 
    : query - insert
    : query - delete
    : query - select, get_array()
 */


using namespace std;

class SqlHandler {
	private:
		sqlite3 *handler;
		
		string	file_path	 = "";
		string	table_name 	 = "";
		string  c_file_path  = "";
		string  c_table_name = "";
		string  query        = "";
		
		map<string, string> db_info;
		mutex	mutex_handle;
		
		bool	is_init;
		bool	is_debug_print = false;
		
		void	print_debug_info(const char *_format, ...);

		bool	open_database(void);
		void	close_database(void);

		static int	callback_handler(void *_type, int _argc, char **_argv, char **_colName);

	public :
		SqlHandler(bool _is_debug_print = false);
		~SqlHandler(void);
		
		void	set_debug_print(void);

		bool 	init(string _file_path);
		
		bool	set_table(string _table_name, string _query = "");
		string	get_table(void);
		
		string 	get(string _key);
		string 	get_str(string _key);
		int		get_int(string _key);
		
		int		set_int(string _key, int _value);
		int		set_str(string _key, string _value);
		
		int 	update(string _query);
		
		sqlite3 *get_handler(void);
};


// key-value type database
class SqliteHandler {
	private:
		sqlite3 *handler;
		
		string	file_path;
		string	table_name;
		
		map<string, string> db_info;
		mutex	mutex_handle;
		
		bool	is_init;
		bool	is_debug_print = false;
		
		void	print_debug_info(const char *_format, ...);

		bool	open_database(void);
		void	close_database(void);

		static int	callback_handler(void *_type, int _argc, char **_argv, char **_colName);

	public :
		SqliteHandler(bool _is_debug_print = false);
		~SqliteHandler(void);
		
		void	set_debug_print(void);

		bool 	init(string _file_path);
		
		bool	set_table(string _table_name = "");
		string	get_table(void);
		
		string 	get(string _key);
		string 	get_str(string _key);
		int		get_int(string _key);
		
		string 	get_string	(string _key);
		int		get_integer	(string _key);
		float	get_float	(string _key);
		double	get_double	(string _key);
		
		bool	set(string _key, string _value, bool _force_set = false);
		bool	set_str(string _key, string _value, bool _force_set = false);
		bool	set_int(string _key, int _value, bool _force_set = false);
				
		bool	set_string	(string _key, string _value, bool _force_set = false);
		bool	set_integer	(string _key, int _value, bool _force_set = false);
		bool	set_float	(string _key, float _value, bool _force_set = false);
		bool	set_double	(string _key, double _value, bool _force_set = false);
		
		bool	unset(string _key);
		
		int 	query(string _query);
		
		sqlite3 *get_handler(void);
};

#endif
