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
		
		bool	open_database(void);
		void	close_database(void);

		void	print_debug_info(const char *_format, ...);

		static int	callback_handler(void *_type, int _argc, char **_argv, char **_colName);

	public :
		SqlHandler(bool _is_debug_print = false);
		~SqlHandler(void);
		
		bool 	init(string _file_path);
		
		bool	set_table(string _table_name, string _query = "");
		string	get_table(void);
		
		string 	get(string _key);
		string 	get_str(string _key);
		int		get_int(string _key);
		
		int		set_int(string _key, int _value);
		int		set_str(string _key, string _value);
		
		int 	update(string _query);

		void	set_debug_print(void);
		
		sqlite3 *get_handler(void);
};

#endif
