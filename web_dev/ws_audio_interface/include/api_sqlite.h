#ifndef __API_SQLITE_H__
#define __API_SQLITE_H__

#include <sqlite3.h>
#include <stdarg.h>
#include <iostream>
#include <string>
#include <map>

/* # SQLITE Handler API
  1. 개발 완료 목록 (2019.03.27, v0.0.0.1)
   + SqlHandler
	: query - select, get_str(), get_int()
	
  2. 개발 예정 목록 
    : query - update
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
		
		map<string, string> db_info;
		
		bool	is_init;
		bool	is_debug_print = false;
		
		bool	open_database(void);
		void	close_database(void);

		static int	callback_handler(void *_type, int _argc, char **_argv, char **_colName);

	public :
		SqlHandler(bool _is_debug_print = false);
		~SqlHandler(void);
		
		bool 	init(string _file_path);
		
		bool	set_table(string _table_name);
		string 	get(string _key);
		string 	get_str(string _key);
		int		get_int(string _key);
		
		void	set_debug_print(void);
		void	print_debug_info(const char *_format, ...);
};

#endif