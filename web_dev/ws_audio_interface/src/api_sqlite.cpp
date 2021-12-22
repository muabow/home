#include <stdio.h>
#include <errno.h>
#include <signal.h>
#include <string.h>

#include <fstream>

#include "api_sqlite.h"

SqlHandler::SqlHandler(bool _is_debug_print) {
	this->is_debug_print = _is_debug_print;
	
	this->print_debug_info("SqlHandler() create instance\n");
	
	this->is_init = false;
	this->handler = NULL;
		
	return ;
}

SqlHandler::~SqlHandler(void) {
	this->print_debug_info("SqlHandler() instance destructed : [%s]\n", this->file_path.c_str());

	this->close_database();
		
	return ;
}

bool SqlHandler::init(string _file_path) {
	this->db_info.clear(); 
	
	this->file_path = _file_path;
	ifstream ifile(_file_path.c_str());
	
	if( !ifile.good() ) {
		this->print_debug_info("init() not found database file : [%s]\n", _file_path.c_str());
		
		return false;
	}
	
	if( sqlite3_open(_file_path.c_str(), &this->handler) ) {
		this->print_debug_info("init() can't open database file : %s [%s]\n" , sqlite3_errmsg(this->handler), _file_path.c_str());

		return false;
	}
	
	this->print_debug_info("init() open database successfully : [%s]\n", _file_path.c_str());
	
	this->is_init = true;

	this->close_database();
	
	return true;
}

bool SqlHandler::open_database(void) {
	if( !is_init ) {
		this->print_debug_info("select() not opened database : [%s]\n", this->file_path.c_str());
		
		return false;
	}
	
	if( sqlite3_open(this->file_path.c_str(), &this->handler) ) {
		this->print_debug_info("open() can't open database file : %s [%s]\n" , sqlite3_errmsg(this->handler), this->file_path.c_str());

		return false;
	}
	
	return true;
}


void SqlHandler::close_database(void) {
	if( !is_init ) {
		this->print_debug_info("close_database() not opened database : [%s]\n", this->file_path.c_str());
		
		return ;
	}
	
	if( this->handler != NULL ) {
		sqlite3_close(this->handler);
	}
	
	return ;
}

int SqlHandler::callback_handler(void *_this, int _argc, char **_argv, char **_col_name) {
	SqlHandler *p_handler = (SqlHandler *)_this;
	
	for( int idx = 0 ; idx < _argc ; idx++ ) {
		p_handler->db_info[_col_name[idx]] = _argv[idx] ? _argv[idx] : "NULL";
	}
	
	return 0;
}
		
bool SqlHandler::set_table(string _table_name) {
	if( !is_init ) {
		this->print_debug_info("set_table() not opened database : [%s]\n", this->file_path.c_str());
		
		return false;
	}
	
	if( !this->open_database() ) {
		return false;
	}
	this->db_info.clear();

	char *err_msg;
	bool  rc = true;
			
	this->table_name   = _table_name;
	this->c_file_path  = this->file_path; 
	this->c_table_name = this->table_name;
	
	string query_string = "select * from ";
	query_string.append(_table_name);
	
	if( sqlite3_exec(this->handler, query_string.c_str(), callback_handler, (void *)this, &err_msg) != SQLITE_OK ) {
		this->print_debug_info("set_table() sqlite3_exec() exec error : %s\n" , err_msg);
		sqlite3_free(err_msg);
		
		rc = false;
	}
	
	this->close_database();
	
	return rc;
}

string SqlHandler::get(string _key) {
	if( !is_init ) {
		this->print_debug_info("get() not opened database : [%s]\n", this->file_path.c_str());
		
		return "";
	}
	
	if( this->c_file_path != this->file_path || this->c_table_name != this->table_name ) {
		this->set_table(this->table_name);
	}
	
	if( this->db_info.find(_key) == this->db_info.end() ) {
		this->print_debug_info("get() error, not found value : key [%s]\n", _key.c_str());
		
		return "";
	}
	
	return this->db_info.find(_key)->second;
}

string SqlHandler::get_str(string _key) {
	if( !is_init ) {
		this->print_debug_info("get_str() not opened database : [%s]\n", this->file_path.c_str());
		
		return "";
	}
	
	if( this->c_file_path != this->file_path || this->c_table_name != this->table_name ) {
		this->set_table(this->table_name);
	}
		
	if( this->db_info.find(_key) == this->db_info.end() ) {
		this->print_debug_info("get_str() error, not found value : key [%s]\n", _key.c_str());
				
		return "";
	}
	
	return this->db_info.find(_key)->second;
}

int SqlHandler::get_int(string _key) {
	int rc = 0;
	
	if( !is_init ) {
		this->print_debug_info("get_int() not opened database : [%s]\n", this->file_path.c_str());
		
		return 0;
	}
	

	if( this->c_file_path != this->file_path || this->c_table_name != this->table_name ) {
		this->set_table(this->table_name);
	}
	
	if( this->db_info.find(_key) == this->db_info.end() ) {
		this->print_debug_info("get_int() error, not found value : key [%s]\n", _key.c_str());
	
		return 0;
	}
	
	try {
		rc = stoi(this->db_info.find(_key)->second);
	
	} catch( invalid_argument& _err ) {
		this->print_debug_info("get_int() error, invalid argument : [%s]\n",this->db_info.find(_key)->second.c_str());
		
		return 0;
		
	} catch( out_of_range& _err ) {
		this->print_debug_info("get_int() error, out of range : [%s]\n", this->db_info.find(_key)->second.c_str());
		
		return 0;
	}
	
	return rc;
}

void SqlHandler::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

void SqlHandler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	va_list arg;
	
	fprintf(stderr, "SqlHandler::");
	
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	return ;
}
