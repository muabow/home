#include <stdio.h>
#include <errno.h>
#include <signal.h>
#include <string.h>
#include <sqlite3.h>

#include <iostream>
#include <fstream>
#include <string>
#include <map>

namespace COMMON {
	using namespace std;

	string g_current_db_file;
	string g_current_table_name;
	map<string, string> g_mapTable;
	
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
	
	SqlHandler::SqlHandler(void) {
		fprintf(stderr, "SqlHandler::SqlHandler() create instance\n");
		
		this->is_init = false;
		this->handler = NULL;
		
		
		return ;
	}
	
	SqlHandler::~SqlHandler(void) {
		fprintf(stderr, "SqlHandler::SqlHandler() instance destructed : [%s]\n", this->filePath.c_str());

		this->close_db();
			
		return ;
	}
	
	bool SqlHandler::init(string _filePath) {
		g_mapTable.clear(); 
		
		this->filePath = _filePath;
		ifstream ifile(_filePath.c_str());
		
		if( !ifile.good() ) {
			fprintf(stderr, "SqlHandler::init() not found database file : [%s]\n", _filePath.c_str());
			
			return false;
		}
		
		if( sqlite3_open(_filePath.c_str(), &this->handler) ) {
			fprintf(stderr, "SqlHandler::init() can't open database file : %s [%s]\n" , sqlite3_errmsg(this->handler), _filePath.c_str());

			return false;
		}
		
		fprintf(stderr, "SqlHandler::init() open database successfully : [%s]\n", _filePath.c_str());
		
		this->is_init = true;

		this->close_db();
		
		return true;
	}
	
	bool SqlHandler::open_db(void) {
		if( !is_init ) {
			fprintf(stderr, "SqlHandler::select() not opened database : [%s]\n", this->filePath.c_str());
			
			return false;
		}
		
		if( sqlite3_open(this->filePath.c_str(), &this->handler) ) {
			fprintf(stderr, "SqlHandler::open() can't open database file : %s [%s]\n" , sqlite3_errmsg(this->handler), this->filePath.c_str());

			return false;
		}
		
		return true;
	}
	
	void SqlHandler::close_db(void) {
		if( this->handler != NULL ) {
			sqlite3_close(this->handler);
		}
		
		return ;
	}
	
	int SqlHandler::callbackFunc(void *_type, int _argc, char **_argv, char **_colName) {
		
		if( strcmp((char *)_type, "select") == 0 ) {
			for( int idx = 0 ; idx < _argc ; idx++ ) {
				g_mapTable[_colName[idx]] = _argv[idx] ? _argv[idx] : "NULL";
			}
		}
		
		return 0;
	}
			
	bool SqlHandler::setTable(string _tableName) {
		bool rc = true;
		
		g_mapTable.clear();
		
		if( !this->open_db() ) {
			return false;
		}
				
		this->tableName = _tableName;

		g_current_db_file    = this->filePath; 
		g_current_table_name = this->tableName;
		
		const char* type = "select";
		char *zErrMsg;
				
		string queryMsg = "select * from ";
		queryMsg.append(_tableName);
		
		if( sqlite3_exec(this->handler, queryMsg.c_str(), callbackFunc, (void *)type, &zErrMsg) != SQLITE_OK ) {
			fprintf(stderr, "SqlHandler::setTable() sqlite3_exec() exec error : %s\n" , zErrMsg);
			sqlite3_free(zErrMsg);
			
			rc = false;
		}
		
		this->close_db();
		
		return rc;
	}
	
	string SqlHandler::get(string _key) {
		if( g_current_db_file != this->filePath || g_current_table_name != this->tableName ) {
			this->setTable(this->tableName);
		}
		
		if( g_mapTable.find(_key) == g_mapTable.end() ) {
			fprintf(stderr, "SqlHandler::get() error, not found value : key [%s]\n", _key.c_str());
			
			return "";

		} else {
			return g_mapTable.find(_key)->second;
		}
	}
	
	string SqlHandler::getStr(string _key) {
		if( g_current_db_file != this->filePath || g_current_table_name != this->tableName ) {
			this->setTable(this->tableName);
		}
			
		if( g_mapTable.find(_key) == g_mapTable.end() ) {
			fprintf(stderr, "SqlHandler::getStr() error, not found value : key [%s]\n", _key.c_str());
					
			return "";
	
		} else {
			return g_mapTable.find(_key)->second;
		}
	}
	
	int SqlHandler::getInt(string _key) {
		int rc;

		if( g_current_db_file != this->filePath || g_current_table_name != this->tableName ) {
			this->setTable(this->tableName);
		}
		
		if( g_mapTable.find(_key) == g_mapTable.end() ) {
			fprintf(stderr, "SqlHandler::getInt() error, not found value : key [%s]\n", _key.c_str());
		
			return 0;
		}
		
		try {
			rc = stoi(g_mapTable.find(_key)->second);
		
		} catch( invalid_argument& _err ) {
			fprintf(stderr, "SqlHandler::getInt() error, invalid argument : [%s]\n",g_mapTable.find(_key)->second.c_str());
			
			return 0;
			
		} catch( out_of_range& _err ) {
			fprintf(stderr, "SqlHandler::getInt() error, out of range : [%s]\n", g_mapTable.find(_key)->second.c_str());
			
			return 0;
		}
		
		return rc;
	}
		
		
	
}