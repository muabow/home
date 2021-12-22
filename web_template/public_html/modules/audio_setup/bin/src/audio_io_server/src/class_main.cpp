#include "class_main.h"

MAIN_Handler::MAIN_Handler(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	this->print_debug_info("MAIN_Handler() create instance\n");

	this->path_module_config	= "";
	this->str_network_info		= "";
	
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
	if( this->str_network_info.compare("") == 0 ) {
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
		
		this->str_network_info = get_network_info;
	
	} else {
		this->print_debug_info("get_network_info() get network : [%s]\n", this->str_network_info.c_str());
	}
	
	return this->str_network_info;
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

string MAIN_Handler::get_info_status(string _key) {
	if( this->path_module_config.compare("") == 0 ) return "";
	
	SqlHandler sql_handler(this->is_debug_print);
	sql_handler.init(this->path_module_config);
	sql_handler.set_table("info");
	
	return sql_handler.get_str(_key);
}

void MAIN_Handler::set_info_status(string _key, string _status) {
	if( this->path_module_config.compare("") == 0 ) return ;
	
	SqlHandler sql_handler(this->is_debug_print);
	sql_handler.init(this->path_module_config);
	sql_handler.set_table("info");
	
	sql_handler.set_str(_key, _status);
	
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