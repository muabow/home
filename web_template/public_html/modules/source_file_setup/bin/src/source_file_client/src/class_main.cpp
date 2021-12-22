#include "class_main.h"

MAIN_Handler::MAIN_Handler(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	this->print_debug_info("MAIN_Handler() create instance\n");

	this->path_module_config	= "";
	this->path_module_db		= "";
	
	this->str_ps_name			= "";
	this->str_network_ip_addr	= "";
	
	this->is_p_amp_device		= false;
	this->is_alive				= false;
	this->is_mp3_encode			= false;
	
	this->status_module			= -1;

	this->str_current_ip		= "";
	this->num_current_port		= -1;
		
	return ;
}

MAIN_Handler::~MAIN_Handler(void) {
	this->print_debug_info("MAIN_Handler() instance destructed\n");
	
	return ;
}
		
void MAIN_Handler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	fprintf(stdout, "MAIN_Handler::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	return ;
}

void MAIN_Handler::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

string MAIN_Handler::get_machine_id(void) {
	string str_machine_id;
	
	ifstream fp_mid(PATH_MACHINE_ID);
	fp_mid >> str_machine_id;
	fp_mid.close();
	this->print_debug_info("get_machine_id() machine-id : [%s]\n", str_machine_id.c_str());
	
	return str_machine_id;
}

string MAIN_Handler::get_network_info(void) {
	ostringstream read_data;
	ifstream fp_net_id(PATH_MODULE_NETWORK_INFO);
	read_data << fp_net_id.rdbuf();
	fp_net_id.close();

	string json_data = read_data.str();
	
	JsonParser json_parser(this->is_debug_print);
	json_parser.parse(json_data.c_str());
	
	string get_network_info;
	if( json_parser.select("/network_bonding/use").compare("enabled") == 0 ) {
		get_network_info = json_parser.select("/network_bonding/ip_address");
	
	} else if( json_parser.select("/network_primary/use").compare("enabled") == 0 ) {
		get_network_info = json_parser.select("/network_primary/ip_address");
	
	} else if( json_parser.select("/network_secondary/use").compare("enabled") == 0 ) {
		get_network_info = json_parser.select("/network_secondary/ip_address");
	
	} else {
		get_network_info = json_parser.select("/network_primary/ip_address");
	}
	this->print_debug_info("get_network_info() using network : [%s]\n", get_network_info.c_str());

	return get_network_info;
}


string MAIN_Handler::get_network_hostname(void) {
	ostringstream read_data;
	ifstream fp_net_id(PATH_MODULE_NETWORK_INFO);
	read_data << fp_net_id.rdbuf();
	fp_net_id.close();

	string json_data = read_data.str();
	
	JsonParser json_parser(this->is_debug_print);
	json_parser.parse(json_data.c_str());

	return json_parser.select("/hostname");
}

void MAIN_Handler::set_module_config_path(string _path) {
	this->path_module_config = _path;
	
	this->print_debug_info("set_module_config_path() set module config path [%s]\n", _path.c_str());
	
	return ;
}

string MAIN_Handler::get_mng_server_info(void) {
	SqlHandler sql_handler;
	sql_handler.init(PATH_MANAGER_SERVER);
	sql_handler.set_table("mng_svr_info", "where mng_svr_used=1;");

	return sql_handler.get_str("mng_svr_ip");
}

bool MAIN_Handler::is_mng_server_extend(string _ip_addr) {
	SqlHandler sql_handler;
	char sub_query[1024];
	
	sprintf(sub_query, "where mng_svr_ip=\"%s\";", _ip_addr.c_str());
	
	sql_handler.init(PATH_MANAGER_SERVER);
	sql_handler.set_table("mng_svr_info", string(sub_query));
	
	return (sql_handler.get_int("mng_svr_extend") == 0) ? false : true;
}

tuple<string, string> MAIN_Handler::get_device_api_key(string _ip_addr) {
	JsonParser json_parser;
	string str_key_list = json_parser.read_file(PATH_DEVICE_KEY_LIST);
	
	Document document;
	document.Parse(str_key_list.c_str());
	
	bool   is_exist = false;
	string device_id     = "";
	string device_secret = "";
	
	if( document.IsObject() ) {
		for( Value::ConstMemberIterator itr = document.MemberBegin(); itr != document.MemberEnd() ; ++itr ) {
			if( itr->value.IsObject() ) {
				if( is_exist ) break;
				device_id = itr->name.GetString();
				
				for( Value::ConstMemberIterator itr_sec = itr->value.MemberBegin(); itr_sec != itr->value.MemberEnd() ; ++itr_sec ) {
					if( itr_sec->value.IsObject() ) {
						if( is_exist ) break;
						device_secret = itr_sec->name.GetString();
						
						Value::ConstMemberIterator itr_svr = itr_sec->value.FindMember("server_addr");
						string server_addr = itr_svr->value.GetString();
						
						if( server_addr.compare(_ip_addr) == 0 ) {
							this->print_debug_info("get_device_api_key() find server[%s] key[%s/%s]\n", 
									server_addr.c_str(), device_id.c_str(), device_secret.c_str());

							is_exist = true;
							break;
						}
					}
				}
			}
		}
	}
	if( !is_exist ) {
		device_id     = "";
		device_secret = "";
	}
	
	return make_tuple(device_id, device_secret);
}

bool MAIN_Handler::file_exist(string _dst) {
	struct stat buffer;   
	
	return (stat(_dst.c_str(), &buffer) == 0); 
}

bool MAIN_Handler::is_module_device(void) {
	
	return this->file_exist("/dev/rtc");
}



void MAIN_Handler::set_database_path(string _path) {
	this->path_module_db = _path;

	return ;
}

int MAIN_Handler::update_databse_status(string _table_name, string _key, string _value, string _format) {
	this->mutex_db_handler.lock();
	
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return -1;
	}
	
	if( _value.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return -1;
	}
	
	SqliteHandler sql_handler;
	sqlite3 *p_sql_handler = sql_handler.get_handler();
	
	sqlite3_exec(p_sql_handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	sql_handler.init(this->path_module_db);
	while( !sql_handler.set_table(_table_name) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}

	this->print_debug_info("update_databse_status() update set [%s] value [%s]\n", _key.c_str(), _value.c_str());
	int result = 0;
	
	if( _format.compare("string") == 0 ) {
		result = sql_handler.set_str(_key, _value); 
	
	} else {
		result = sql_handler.set_int(_key, stoi(_value));
	}
	
	sqlite3_exec(p_sql_handler, "END TRANSACTION;", NULL, NULL, NULL);
	
	this->mutex_db_handler.unlock();
	
	return result;
}

int MAIN_Handler::update_databse_status(string _table_name, string _key, string _value) {
	this->mutex_db_handler.lock();
	
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return -1;
	}
	if( _value.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return -1;
	}
	
	SqliteHandler sql_handler;
	sqlite3 *p_sql_handler = sql_handler.get_handler();
	
	sqlite3_exec(p_sql_handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	sql_handler.init(this->path_module_db);
	while( !sql_handler.set_table(_table_name) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}
	
	this->print_debug_info("update_databse_status() update set [%s] value [%s]\n", _key.c_str(), _value.c_str());
	int result = sql_handler.set_str(_key, _value);

	sqlite3_exec(p_sql_handler, "END TRANSACTION;", NULL, NULL, NULL);
	
	this->mutex_db_handler.unlock();
	
	return result; 
}

int MAIN_Handler::update_databse_status(string _table_name, string _key, int _value) {
	this->mutex_db_handler.lock();
	
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return -1;
	}
	
	if( _value == -1 ) {
		this->mutex_db_handler.unlock();
		
		return -1;
	}
	
	SqliteHandler sql_handler;
	sqlite3 *p_sql_handler = sql_handler.get_handler();
	
	sqlite3_exec(p_sql_handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	sql_handler.init(this->path_module_db);
	while( !sql_handler.set_table(_table_name) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}
	
	this->print_debug_info("update_databse_status() update set [%s] value [%d]\n", _key.c_str(), _value);
	int result = sql_handler.set_int(_key, _value);
	
	sqlite3_exec(p_sql_handler, "END TRANSACTION;", NULL, NULL, NULL);
	
	this->mutex_db_handler.unlock();

	return result;
}

string MAIN_Handler::get_database_status(string _table_name, string _key) {
	this->mutex_db_handler.lock();
	
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return "";
	}
			
	SqliteHandler sql_handler;
	sqlite3 *p_sql_handler = sql_handler.get_handler();
	
	sqlite3_exec(p_sql_handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	sql_handler.init(this->path_module_db);
	while( !sql_handler.set_table(_table_name) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}

	string result = sql_handler.get(_key);
	
	sqlite3_exec(p_sql_handler, "END TRANSACTION;", NULL, NULL, NULL);
	
	this->mutex_db_handler.unlock();
	
	return result;
}

mutex *MAIN_Handler::get_mutex_db_handler(void) {
	
	return &this->mutex_db_handler;
}

bool MAIN_Handler::is_module_use(string _table_name) {
	if(this->status_module != -1 ) {
		return (this->status_module == 1 ? true :false);
	}
	
	this->mutex_db_handler.lock();
	
	if( this->path_module_db.compare("") == 0 ) {
		this->status_module = 0;
		this->mutex_db_handler.unlock();
		
		return false;
	}
	
	SqliteHandler sql_handler;
	sqlite3 *p_sql_handler = sql_handler.get_handler();
	
	sqlite3_exec(p_sql_handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	sql_handler.init(this->path_module_db);
	while( !sql_handler.set_table(_table_name) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}

	string module_use = sql_handler.get("module_use");
	
	sqlite3_exec(p_sql_handler, "END TRANSACTION;", NULL, NULL, NULL);
	
	if( module_use.compare("enabled") == 0 ) {
		this->status_module = 1;
		this->mutex_db_handler.unlock();
		
		return true;		
	}
	
	this->status_module = 0;
	this->mutex_db_handler.unlock();
	
	return false;
}

bool MAIN_Handler::is_module_status(string _table_name) {
	this->mutex_db_handler.lock();
	
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return false;
	}
	
	SqliteHandler sql_handler;
	sqlite3 *p_sql_handler = sql_handler.get_handler();
	
	sqlite3_exec(p_sql_handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	sql_handler.init(this->path_module_db);
	while( !sql_handler.set_table(_table_name) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}

	string module_use = sql_handler.get("module_status");
	
	sqlite3_exec(p_sql_handler, "END TRANSACTION;", NULL, NULL, NULL);
	
	if( module_use.compare("run") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return true;		
	}
	this->mutex_db_handler.unlock();
	
	return false;
}

bool MAIN_Handler::is_amp_device(void) {
	
	return this->is_p_amp_device;
}

void MAIN_Handler::set_ps_name(string _name) {
	this->print_debug_info("set_ps_name() set process name : [%s]\n", _name.c_str());
	this->str_ps_name = _name;

	return ;
}

string MAIN_Handler::get_ps_name(void) {

	return this->str_ps_name;
}

void MAIN_Handler::get_env_status(void) {
	JsonParser json_parser(this->is_debug_print);
	
	string str_env_json = json_parser.read_file("/opt/interm/conf/env.json");
	json_parser.parse(str_env_json);
	
	this->is_p_amp_device = false;
	string str_device_type = json_parser.select("/device/device_type");

	this->print_debug_info("get_env_status() device type : [%s]\n", str_device_type.c_str());
	if( str_device_type.compare("amp") == 0 ) {
		this->is_p_amp_device = true;
	}
	
	return ;
}

double MAIN_Handler::calc_diff_time(struct timeval _x, struct timeval _y) {
	double x_ms , y_ms , diff;

	x_ms = (double)_x.tv_sec * 1000000 + (double)_x.tv_usec;
	y_ms = (double)_y.tv_sec * 1000000 + (double)_y.tv_usec;

	diff = (double)y_ms - (double)x_ms;

	return diff;
}

void MAIN_Handler::set_alive_status(bool _status) {
	this->is_alive = _status;
	
	return ;
}

bool MAIN_Handler::is_alive_status(void) {
	
	return this->is_alive;
}

void MAIN_Handler::set_network_ip_addr(string _ip_addr) {
	this->str_network_ip_addr = _ip_addr;
	
	return ;
}

string MAIN_Handler::get_network_ip_addr(void) {

	return this->str_network_ip_addr;
}

void MAIN_Handler::set_current_server_info(string _ip_addr, int _port) {
	this->print_debug_info("set_current_server_info() set current server [%s/%d]\n", _ip_addr.c_str(), _port);
	
	this->str_current_ip 	= _ip_addr;
	this->num_current_port	= _port;
	
	return ;
}

tuple<string, int> MAIN_Handler::get_current_server_info(void) {
	
	return make_tuple(this->str_current_ip, this->num_current_port);
}

bool MAIN_Handler::is_encode_status(void) {
	
	return this->is_mp3_encode;
}

void MAIN_Handler::set_encode_status(string _encode_mode) {
	this->print_debug_info("set_encode_status() set encode mode [%s]\n", _encode_mode.c_str());
	if( _encode_mode.compare("mp3") == 0 ) {
		this->is_mp3_encode = true;
	
	} else {
		this->is_mp3_encode = false;
	}
	
	return ;
}
