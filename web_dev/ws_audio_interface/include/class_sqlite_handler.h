#ifndef __CLASS_SQLITE_HANDLER_H__
#define __CLASS_SQLITE_HANDLER_H__

#include <sqlite3.h>

#include <iostream>
#include <map>

namespace COMMON {
	using namespace std;

	class SqlHandler {
		private:
			sqlite3 *handler;
			string	filePath;
			string	tableName;
			bool	is_init;
			
			bool	open_db(void);
			void	close_db(void);

			static int	callbackFunc(void *_type, int _argc, char **_argv, char **_colName);

		public :
			SqlHandler(void);
			~SqlHandler(void);
			
			bool 	init(string _filePath);
			
			bool	setTable(string _tableName);
			string 	get(string _key);
			string 	getStr(string _key);
			int		getInt(string _key);
	};
}

#endif