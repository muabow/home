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
	this->query   = "";
	
	return ;
}

SqlHandler::~SqlHandler(void) {
	this->print_debug_info("SqlHandler() instance destructed : [%s]\n", this->file_path.c_str());

	if( this->handler != NULL ) {
		sqlite3_close(this->handler);
	}
		
	return ;
}

void SqlHandler::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

void SqlHandler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	va_list arg;
	
	fprintf(stdout, "SqlHandler::");
	
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	return ;
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
		p_handler->print_debug_info("callback_handler() [%s] : %s\n", _col_name[idx], p_handler->db_info[_col_name[idx]].c_str());
	}
	
	return 0;
}



bool SqlHandler::init(string _file_path) {
	this->mutex_handle.lock();
	
	this->db_info.clear(); 
	
	this->file_path = _file_path;
	ifstream ifile(_file_path.c_str());
	
	if( !ifile.good() ) {
		this->print_debug_info("init() not found database file : [%s]\n", _file_path.c_str());
		this->mutex_handle.unlock();
		
		return false;
	}
	
	if( sqlite3_open(_file_path.c_str(), &this->handler) ) {
		this->print_debug_info("init() can't open database file : %s [%s]\n" , sqlite3_errmsg(this->handler), _file_path.c_str());
		this->mutex_handle.unlock();
		
		return false;
	}
	
	this->print_debug_info("init() open database successfully : [%s]\n", _file_path.c_str());
	
	this->is_init = true;

	this->close_database();
	
	this->mutex_handle.unlock();

	return true;
}

bool SqlHandler::set_table(string _table_name, string _query) {
	this->mutex_handle.lock();
	
	if( !is_init ) {
		this->print_debug_info("set_table() not opened database : [%s]\n", this->file_path.c_str());
		this->mutex_handle.unlock();
		
		return false;
	}
	
	if( !this->open_database() ) {
		this->mutex_handle.unlock();
		
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
	
	if( _query.compare("") != 0 ) {
		query_string.append(" ");
		query_string.append(_query);
		
		this->query = "";
		this->query.append(" ");
		this->query.append(_query);
	}
	
	if( sqlite3_exec(this->handler, query_string.c_str(), callback_handler, (void *)this, &err_msg) != SQLITE_OK ) {
		this->print_debug_info("set_table() sqlite3_exec() exec error : %s\n" , err_msg);
		sqlite3_free(err_msg);
		this->mutex_handle.unlock();
		
		rc = false;
	}
	
	this->close_database();
	this->mutex_handle.unlock();
	
	return rc;
}

string SqlHandler::get_table(void) {

	return this->table_name;
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

int SqlHandler::set_str(string _key, string _value) {
	string query_string = "update ";
	query_string.append(this->table_name);
	query_string.append(" set ");
	query_string.append(_key);
	query_string.append("=\"");
	query_string.append(_value);
	query_string.append("\"");
	query_string.append(this->query);
	query_string.append(";");
	
	return this->update(query_string);
}

int SqlHandler::set_int(string _key, int _value) {
	string query_string = "update ";
	query_string.append(this->table_name);
	query_string.append(" set ");
	query_string.append(_key);
	query_string.append("=");
	query_string.append(to_string(_value));
	query_string.append(this->query);
	query_string.append(";");
	
	return this->update(query_string);
}

int SqlHandler::update(string _query) {
	this->mutex_handle.lock();
	
	if( !is_init ) {
		this->print_debug_info("update() not opened database : [%s]\n", this->file_path.c_str());
		this->mutex_handle.unlock();
		
		return 0;
	}

	if( this->c_file_path != this->file_path || this->c_table_name != this->table_name ) {
		this->set_table(this->table_name);
	}
	
	int result = 0;
	sqlite3_stmt *statement;

	if( !this->open_database() ) {
		this->mutex_handle.unlock();
		
		return false;
	}
	
	this->print_debug_info("update() query : %s\n", _query.c_str());
	if( sqlite3_prepare(this->handler, _query.c_str(), -1, &statement, 0) == SQLITE_OK ) {
		int res = sqlite3_step(statement);
		result = res;
	
		sqlite3_finalize(statement);
	
	} else {
		this->print_debug_info("update() sqlite3_exec() exec error \n");
	}
	this->close_database();
	
	this->mutex_handle.unlock();
	
	this->set_table(this->table_name);
 
	return result;
}

sqlite3 *SqlHandler::get_handler(void) {
	
	return this->handler;
}


/////////////////////////////////////////////////////////


SqliteHandler::SqliteHandler(bool _is_debug_print) {
	this->is_debug_print = _is_debug_print;
	
	this->print_debug_info("SqliteHandler() create instance\n");
	
	this->is_init = false;
	this->handler = NULL;
	
	this->file_path		= "";
	this->table_name	= "";
	
	return ;
}

SqliteHandler::~SqliteHandler(void) {
	this->print_debug_info("SqliteHandler() instance destructed : [%s]\n", this->file_path.c_str());

	if( this->handler != NULL ) {
		sqlite3_close(this->handler);
	}
		
	return ;
}

void SqliteHandler::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

void SqliteHandler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	va_list arg;
	
	fprintf(stdout, "SqliteHandler::");
	
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	return ;
}

bool SqliteHandler::open_database(void) {
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

void SqliteHandler::close_database(void) {
	if( !is_init ) {
		this->print_debug_info("close_database() not opened database : [%s]\n", this->file_path.c_str());
		
		return ;
	}
	
	if( this->handler != NULL ) {
		sqlite3_close(this->handler);
	}
	
	return ;
}

int SqliteHandler::callback_handler(void *_this, int _argc, char **_argv, char **_col_name) {
	SqliteHandler *p_handler = (SqliteHandler *)_this;
	
	p_handler->db_info[_argv[0]] = _argv[1];
	p_handler->print_debug_info("callback_handler() [%s] : %s\n", _argv[0], _argv[1]);
	
	return 0;
}



bool SqliteHandler::init(string _file_path) {
	this->mutex_handle.lock();
	
	this->db_info.clear(); 
	
	this->file_path = _file_path;
	ifstream ifile(_file_path.c_str());
	
	if( !ifile.good() ) {
		this->print_debug_info("init() not found database file : [%s]\n", _file_path.c_str());
		this->mutex_handle.unlock();
		
		return false;
	}
	
	if( sqlite3_open(_file_path.c_str(), &this->handler) ) {
		this->print_debug_info("init() can't open database file : %s [%s]\n" , sqlite3_errmsg(this->handler), _file_path.c_str());
		this->mutex_handle.unlock();
		
		return false;
	}

	this->is_init = true;
	this->close_database();

	this->print_debug_info("init() open database successfully : [%s]\n", _file_path.c_str());
	
	this->mutex_handle.unlock();

	return true;
}

bool SqliteHandler::set_table(string _table_name) {
	if( !is_init ) {
		this->print_debug_info("set_table() not opened database : [%s]\n", this->file_path.c_str());
		
		return false;
	}
	this->mutex_handle.lock();
	
	if( !this->open_database() ) {
		this->mutex_handle.unlock();
		
		return false;
	}
	this->db_info.clear();
	
	if( _table_name.compare("") != 0 ) {
		this->table_name = _table_name;
	}

	bool rc = true;
	char query[1024];
	char *err_msg;
			
	sprintf(query, "select * from %s;", this->table_name.c_str());
	
	if( sqlite3_exec(this->handler, query, callback_handler, (void *)this, &err_msg) != SQLITE_OK ) {
		this->print_debug_info("set_table() sqlite3_exec() exec error : %s\n" , err_msg);
		
		sqlite3_free(err_msg);
		this->mutex_handle.unlock();
		
		rc = false;
	}
	
	this->close_database();
	this->mutex_handle.unlock();
	
	return rc;
}

string SqliteHandler::get_table(void) {

	return this->table_name;
}

string SqliteHandler::get(string _key) {
	if( !is_init ) {
		this->print_debug_info("get() not opened database : [%s]\n", this->file_path.c_str());
		
		return "";
	}
	
	if( this->db_info.find(_key) == this->db_info.end() ) {
		this->print_debug_info("get() error, not found value : key [%s]\n", _key.c_str());
		
		return "";
	}
	
	return this->db_info.find(_key)->second;
}

string SqliteHandler::get_str(string _key) {
	
	return this->get(_key);
}

int SqliteHandler::get_int(string _key) {
	int rc = 0;
	
	string value = this->get(_key);
	
	try {
		rc = stoi(value);
	
	} catch( invalid_argument& _err ) {
		this->print_debug_info("get_int() error, invalid argument : [%s]\n",this->db_info.find(_key)->second.c_str());
		
		return 0;
		
	} catch( out_of_range& _err ) {
		this->print_debug_info("get_int() error, out of range : [%s]\n", this->db_info.find(_key)->second.c_str());
		
		return 0;
	}
	
	return rc;
}

string SqliteHandler::get_string(string _key) {
	
	return this->get(_key);
}

int SqliteHandler::get_integer(string _key) {
	int rc = 0;
	
	string value = this->get(_key);
	
	try {
		rc = stoi(value);
	
	} catch( invalid_argument& _err ) {
		this->print_debug_info("get_int() error, invalid argument : [%s]\n",this->db_info.find(_key)->second.c_str());
		
		return 0;
		
	} catch( out_of_range& _err ) {
		this->print_debug_info("get_int() error, out of range : [%s]\n", this->db_info.find(_key)->second.c_str());
		
		return 0;
	}
	
	return rc;
}

float SqliteHandler::get_float(string _key) {
	float rc = 0;
	
	string value = this->get(_key);
	
	try {
		rc = stof(value);
	
	} catch( invalid_argument& _err ) {
		this->print_debug_info("get_int() error, invalid argument : [%s]\n",this->db_info.find(_key)->second.c_str());
		
		return 0;
		
	} catch( out_of_range& _err ) {
		this->print_debug_info("get_int() error, out of range : [%s]\n", this->db_info.find(_key)->second.c_str());
		
		return 0;
	}
	
	return rc;
}

double SqliteHandler::get_double(string _key) {
	double rc = 0;
	
	string value = this->get(_key);
	
	try {
		rc = stod(value);
	
	} catch( invalid_argument& _err ) {
		this->print_debug_info("get_int() error, invalid argument : [%s]\n",this->db_info.find(_key)->second.c_str());
		
		return 0;
		
	} catch( out_of_range& _err ) {
		this->print_debug_info("get_int() error, out of range : [%s]\n", this->db_info.find(_key)->second.c_str());
		
		return 0;
	}
	
	return rc;
}

bool SqliteHandler::set(string _key, string _value, bool _force_set) {
	char query[1024];
	sprintf(query, "update %s set value='%s' where key='%s';", this->table_name.c_str(), _value.c_str(), _key.c_str());
	
	if( this->db_info.find(_key) == this->db_info.end() ) {
		if( _force_set ) {
			sprintf(query, "insert into %s (key, value) values ('%s', '%s');", this->table_name.c_str(), _key.c_str(), _value.c_str());
		}
	}
	
	if( this->query(query) ) {
		this->set_table();
		return true;
	}
	
	return false;
}

bool SqliteHandler::set_str(string _key, string _value, bool _force_set) {

	return this->set(_key, _value, _force_set);
}

bool SqliteHandler::set_int(string _key, int _value, bool _force_set) {
	
	return this->set(_key, to_string(_value), _force_set);
}

bool SqliteHandler::set_string(string _key, string _value, bool _force_set) {

	return this->set(_key, _value, _force_set);
}

bool SqliteHandler::set_integer(string _key, int _value, bool _force_set) {
	
	return this->set(_key, to_string(_value), _force_set);
}

bool SqliteHandler::set_float(string _key, float _value, bool _force_set) {
	
	return this->set(_key, to_string(_value), _force_set);
}

bool SqliteHandler::set_double(string _key, double _value, bool _force_set) {
	
	return this->set(_key, to_string(_value), _force_set);
}

bool SqliteHandler::unset(string _key) {
	char query[1024];
	sprintf(query, "delete from %s where key='%s';", this->table_name.c_str(), _key.c_str());
	
	if( this->query(query) ) {
		this->set_table();
		return true;
	}
	
	return false;
}


int SqliteHandler::query(string _query) {
	this->mutex_handle.lock();
	
	if( !is_init ) {
		this->print_debug_info("query() not opened database : [%s]\n", this->file_path.c_str());
		this->mutex_handle.unlock();
		
		return 0;
	}
	
	int result = 0;
	sqlite3_stmt *statement;

	if( !this->open_database() ) {
		this->mutex_handle.unlock();
		
		return false;
	}
	
	this->print_debug_info("query() query : %s\n", _query.c_str());
	if( sqlite3_prepare(this->handler, _query.c_str(), -1, &statement, 0) == SQLITE_OK ) {
		int res = sqlite3_step(statement);
		result = res;
	
		sqlite3_finalize(statement);
	
	} else {
		this->print_debug_info("query() sqlite3_exec() exec error \n");
	}
	this->close_database();
	
	this->mutex_handle.unlock();
 
	return result;
}

sqlite3 *SqliteHandler::get_handler(void) {
	
	return this->handler;
}