#include "class_audio.h"

AUDIO_Handler::AUDIO_Handler(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	this->print_debug_info("AUDIO_Handler() create instance\n");
	
	this->str_ps_name		= "";
	
	this->level_value		= DFLT_LEVEL_VALUE;
	this->is_p_amp_device	= false;

	this->is_server_alive	= false;
	this->is_client_alive	= false;
	this->is_mp3_encode		= false;
	
	this->path_module_db	= "";
	this->network_cast_type = "";
	this->network_ip_addr	= "";
	
	this->audio_volume		= -1;

	this->str_current_ip	= "";
	this->num_current_port	= -1;

	return ;
}

AUDIO_Handler::~AUDIO_Handler(void) {
	this->print_debug_info("AUDIO_Handler() instance destructed\n");
	
	return ;
}
		
void AUDIO_Handler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	printf("\033[33m");
	
	fprintf(stdout, "AUDIO_Handler::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	printf("\033[0m");
	
	return ;
}

void AUDIO_Handler::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

void AUDIO_Handler::set_ps_name(string _name) {
	this->print_debug_info("set_ps_name() set process name : [%s]\n", _name.c_str());
	this->str_ps_name = _name;

	return ;
}

string AUDIO_Handler::get_ps_name(void) {

	return this->str_ps_name;
}

void AUDIO_Handler::get_env_status(void) {
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

int AUDIO_Handler::get_level_value(void) {
	
	return this->level_value;
}

void AUDIO_Handler::set_level_value(int _value) {
	this->mutex_level_info.lock();
	
	this->level_value = _value;

	this->mutex_level_info.unlock();
	return ;
}

void AUDIO_Handler::set_database_path(string _path) {
	this->path_module_db = _path;

	return ;
}

int AUDIO_Handler::update_databse_status(string _type, string _field, string _value, string _format) {
	this->mutex_db_handler.lock();
	
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return -1;
	}
	
	if( _value.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return -1;
	}
	
	SqlHandler sql_audio_handler;

	int result = 0;
	while( true ) {
		sql_audio_handler.init(this->path_module_db);
		while( !sql_audio_handler.set_table(_type) ) {
			usleep(TIME_DB_LOCK_WAIT);
		}

		this->print_debug_info("update_databse_status() update set [%s] value [%s]\n", _field.c_str(), _value.c_str());
		
		if( _format.compare("string") == 0 ) {
			result = sql_audio_handler.set_str(_field, _value); 
		
		} else {
			result = sql_audio_handler.set_int(_field, stoi(_value));
		}
		
		while( !sql_audio_handler.set_table(_type) ) {
			usleep(TIME_DB_LOCK_WAIT);
		}

		string str_status = sql_audio_handler.get(_field);

		if( str_status.compare(_value) == 0 ) {
			break;

		} else {
			this->print_debug_info("update_databse_status() update error, retry update : set : [%s], current : [%s]\n",str_status.c_str(), _value.c_str());
		}
	}
	
	this->mutex_db_handler.unlock();
	
	return result;
}

int AUDIO_Handler::update_databse_status(string _type, string _field, string _value) {
	this->mutex_db_handler.lock();
	
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return -1;
	}
	if( _value.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return -1;
	}
	
	SqlHandler sql_audio_handler;
	
	int result = 0;
	while( true ) {
		sql_audio_handler.init(this->path_module_db);
		while( !sql_audio_handler.set_table(_type) ) {
			usleep(TIME_DB_LOCK_WAIT);
		}

		this->print_debug_info("update_databse_status() update set [%s] value [%s]\n", _field.c_str(), _value.c_str());
		
		result = sql_audio_handler.set_str(_field, _value);
		
		while( !sql_audio_handler.set_table(_type) ) {
			usleep(TIME_DB_LOCK_WAIT);
		}

		string str_status = sql_audio_handler.get(_field);

		if( str_status.compare(_value) == 0 ) {
			break;

		} else {
			this->print_debug_info("update_databse_status() update error, retry update : set : [%s], current : [%s]\n",str_status.c_str(), _value.c_str());
		}
	}

	this->mutex_db_handler.unlock();
	
	return result; 
}

int AUDIO_Handler::update_databse_status(string _type, string _field, int _value) {
	this->mutex_db_handler.lock();
	
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return -1;
	}
	
	if( _value == -1 ) {
		this->mutex_db_handler.unlock();
		
		return -1;
	}
	
	SqlHandler sql_audio_handler;
	
	int result = 0;
	while( true ) {
		sql_audio_handler.init(this->path_module_db);
		while( !sql_audio_handler.set_table(_type) ) {
			usleep(TIME_DB_LOCK_WAIT);
		}

		this->print_debug_info("update_databse_status() update set [%s] value [%d]\n", _field.c_str(), _value);
		
		result = sql_audio_handler.set_int(_field, _value);
		
		while( !sql_audio_handler.set_table(_type) ) {
			usleep(TIME_DB_LOCK_WAIT);
		}

		string str_status = sql_audio_handler.get(_field);

		if( stoi(str_status) == _value ) {
			break;

		} else {
			this->print_debug_info("update_databse_status() update error, retry update : set : [%s], current : [%d]\n",str_status.c_str(), _value);
		}
	}
	
	this->mutex_db_handler.unlock();

	return result;
}

bool AUDIO_Handler::is_amp_device(void) {
	
	return this->is_p_amp_device;
}

bool AUDIO_Handler::is_module_use(string _type) {
	this->mutex_db_handler.lock();
	
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return false;
	}
	
	SqlHandler sql_audio_handler;
	
	sql_audio_handler.init(this->path_module_db);
	while( !sql_audio_handler.set_table(_type) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}

	string module_use = sql_audio_handler.get("module_use");
	if( module_use.compare("enabled") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return true;		
	}
	this->mutex_db_handler.unlock();
	
	return false;
}

bool AUDIO_Handler::is_module_status(string _type) {
	this->mutex_db_handler.lock();
	
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return false;
	}
	
	SqlHandler sql_audio_handler;
	
	sql_audio_handler.init(this->path_module_db);
	while( !sql_audio_handler.set_table(_type) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}

	string module_use = sql_audio_handler.get("module_status");
	if( module_use.compare("run") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return true;		
	}
	this->mutex_db_handler.unlock();
	
	return false;
}

bool AUDIO_Handler::is_alive_status(string _type) {
	this->mutex_alive_info.lock();
	
	if( _type.compare("audio_server") == 0 ) {
		this->mutex_alive_info.unlock();
		
		return this->is_server_alive;
		
	} else if( _type.compare("audio_client") == 0 ) {
		this->mutex_alive_info.unlock();
		
		return this->is_client_alive;
	}
	this->mutex_alive_info.unlock();
	
	return false;
}

void AUDIO_Handler::set_alive_status(string _type, bool _is_alive) {
	this->mutex_alive_info.lock();
	
	if( _type.compare("audio_server") == 0 ) {
		this->is_server_alive = _is_alive;
		
	} else if( _type.compare("audio_client") == 0 ) {
		this->is_client_alive = _is_alive;
	}
	this->mutex_alive_info.unlock();
	
	return ;
}

bool AUDIO_Handler::is_network_cast_type(string _type, string _cast_type) {
	this->mutex_db_handler.lock();
	
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
	
		return false;
	}
		
	SqlHandler sql_audio_handler;
	
	sql_audio_handler.init(this->path_module_db);
	while( !sql_audio_handler.set_table(_type) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}

	string network_cast_type = sql_audio_handler.get("network_cast_type");
	if( network_cast_type.compare(_cast_type) == 0 ) {
		this->network_cast_type = _cast_type;
		
		this->mutex_db_handler.unlock();
		
		return true;		
	}
	this->mutex_db_handler.unlock();
	
	return false;
}

string AUDIO_Handler::get_network_cast_type(void) {
	
	return this->network_cast_type;
}

void AUDIO_Handler::set_network_ip_addr(string _ip_addr) {
	
	this->network_ip_addr = _ip_addr;
}

string AUDIO_Handler::get_network_ip_addr(void) {
	
	return this->network_ip_addr;
}

string AUDIO_Handler::get_database_status(string _type, string _field) {
	this->mutex_db_handler.lock();
	
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return "";
	}
			
	SqlHandler sql_audio_handler;
	
	sql_audio_handler.init(this->path_module_db);
	while( !sql_audio_handler.set_table(_type) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}

	string result = sql_audio_handler.get(_field);
	
	this->mutex_db_handler.unlock();
	
	return result;
}

void AUDIO_Handler::set_playback_info(string _type, int _value) {
	this->mutex_playback_info.lock();
	
	int idx;
	int num_list = (int)this->v_playback_info.size();
	
	tuple<string, int> tp_play_info;
	
	for( idx = 0 ; idx < num_list ; idx++ ) {
		tp_play_info = this->v_playback_info[idx];
		if( get<0>(tp_play_info).compare(_type) == 0 ) {
			this->v_playback_info[idx] = make_tuple(_type, _value);
			
			break;
		}
	}
	
	if( idx == num_list ) {
		this->v_playback_info.push_back(make_tuple(_type, _value));
	}
	this->mutex_playback_info.unlock();
	
	return ;
}
	
int AUDIO_Handler::get_playback_info(string _type) {
	this->mutex_playback_info.lock();
	
	int num_list = (int)this->v_playback_info.size();
		
	tuple<string, int> tp_play_info;
	
	for( int idx = 0 ; idx < num_list ; idx++ ) {
		tp_play_info = this->v_playback_info[idx];
		if( get<0>(tp_play_info).compare(_type) == 0 ) {
				
			this->mutex_playback_info.unlock();
			return get<1>(tp_play_info);
		}
	}
	this->mutex_playback_info.unlock();
	
	return -1;
}

double AUDIO_Handler::calc_diff_time(struct timeval _x, struct timeval _y) {
	double x_ms , y_ms , diff;

	x_ms = (double)_x.tv_sec * 1000000 + (double)_x.tv_usec;
	y_ms = (double)_y.tv_sec * 1000000 + (double)_y.tv_usec;

	diff = (double)y_ms - (double)x_ms;

	return diff;
}

int AUDIO_Handler::get_audio_volume(void) {
	
	return this->audio_volume;
}

void AUDIO_Handler::set_audio_volume(int _volume) {
	this->mutex_volume.lock();
	
	this->audio_volume = _volume;
	
	this->mutex_volume.unlock();
	
	return ;
}

bool AUDIO_Handler::is_encode_status(void) {
	
	return this->is_mp3_encode;
}

void AUDIO_Handler::set_encode_status(string _encode_mode) {
	this->mutex_encode.lock();
	
	this->print_debug_info("set_encode_status() set encode mode [%s]\n", _encode_mode.c_str());
	if( _encode_mode.compare("mp3") == 0 ) {
		this->is_mp3_encode = true;
	
	} else {
		this->is_mp3_encode = false;
	}
	this->mutex_encode.unlock();
	
	return ;
}

void AUDIO_Handler::set_current_server_info(string _ip_addr, int _port) {
	this->print_debug_info("set_current_server_info() set current server [%s/%d]\n", _ip_addr.c_str(), _port);
	
	this->str_current_ip 	= _ip_addr;
	this->num_current_port	= _port;
	
	return ;
}

tuple<string, int> AUDIO_Handler::get_current_server_info(void) {
	
	return make_tuple(this->str_current_ip, this->num_current_port);
}

bool AUDIO_Handler::is_exist_db_colume(string _table, string _colume) {
	this->mutex_db_handler.lock();
	
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return false;
	}
	
	SqlHandler sql_audio_handler;
	
	sql_audio_handler.init(this->path_module_db);
	while( !sql_audio_handler.set_table(_table) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}

	bool is_exist = true;
	string colume_status = sql_audio_handler.get(_colume);
	if( colume_status.compare("") == 0 ) {
		is_exist = false;
	}

	this->mutex_db_handler.unlock();
	
	return is_exist;
}

int AUDIO_Handler::add_db_colume(string _table, string _colume, string _type) {
	this->mutex_db_handler.lock();
	
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return false;
	}
	
	SqlHandler sql_audio_handler;
	
	sql_audio_handler.init(this->path_module_db);
	while( !sql_audio_handler.set_table(_table) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}

	char query[1024];
	sprintf(query, "alter table %s add column %s %s;", _table.c_str(), _colume.c_str(), _type.c_str());
	int result = sql_audio_handler.update(query);
	this->mutex_db_handler.unlock();
	
	return result;
}

int AUDIO_Handler::query(string _table, string _query) {
	this->mutex_db_handler.lock();
	
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler.unlock();
		
		return false;
	}
	
	SqlHandler sql_audio_handler;
	
	sql_audio_handler.init(this->path_module_db);
	while( !sql_audio_handler.set_table(_table) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}

	char query[1024];
	sprintf(query, "%s", _query.c_str());
	int result = sql_audio_handler.update(query);
	this->mutex_db_handler.unlock();
	
	return result;
}


/* CHIME_handler */
CHIME_Handler::CHIME_Handler(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	this->print_debug_info("CHIME_Handler() create instance\n");
	
	this->fp				= NULL;

	this->is_play_chime		= false;
	this->is_mix_chime		= false;
	this->num_chime_index	= 0;
	this->num_volume		= 0;

	return ;
}

CHIME_Handler::~CHIME_Handler(void) {
	this->print_debug_info("CHIME_Handler() instance destructed\n");
	this->close();

	return ;
}
		
void CHIME_Handler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	printf("\033[33m");
	
	fprintf(stdout, "CHIME_Handler::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	printf("\033[0m");
	
	return ;
}

void CHIME_Handler::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

void CHIME_Handler::close(void) {
	if( this->fp != NULL ) {
		fclose(this->fp);
		this->fp = NULL;
	}

	return ;
}

void CHIME_Handler::open(string _path_chime_file) {
	this->fp = fopen(_path_chime_file.c_str(), "r");

	return ;
}

void CHIME_Handler::set_chime_play_status(bool _status) {
	this->print_debug_info("set_chime_play() set chime status [%d]\n", _status);
	this->is_play_chime = _status;

	return ;
}

void CHIME_Handler::set_chime_mix_status(int _mix_value) {
	this->print_debug_info("set_chime_mix_status() set mix mode : [%s]\n", (_mix_value == 1 ? "enabled" : "disabled"));
	this->is_mix_chime = (_mix_value == 1 ? true : false);;

	return ;
}

void CHIME_Handler::set_chime_index(int _idx) {
	this->print_debug_info("set_chime_index() set chime index [%d]\n", _idx);
	this->num_chime_index = _idx;

	return ;
}

void CHIME_Handler::set_chime_volume(int _volume) {
	this->print_debug_info("set_chime_index() set chime volume [%d]\n", _volume);
	this->num_volume = _volume;

	return ;
}

bool CHIME_Handler::is_chime_play_status(void) {
	return this->is_play_chime;
}

bool CHIME_Handler::is_chime_mix_status(void) {
	return this->is_mix_chime;
}

int CHIME_Handler::get_chime_index(void) {
	return this->num_chime_index;
}

int CHIME_Handler::get_chime_volume(void) {
	return this->num_volume;
}

bool CHIME_Handler::is_eof(void) {
	if( feof(this->fp) == 0 ) {
		return false;
	
	} else {
		return true;
	}
}

int CHIME_Handler::read(short *_data, int _size) {
	return (int)fread(_data, 2, _size, this->fp);
}


/* TTS_Handler */
TTS_Handler::TTS_Handler(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	this->print_debug_info("TTS_Handler() create instance\n");
	
	this->fp				= NULL;

	this->is_play_tts		= false;
	this->num_tts_index		= -1;

	this->str_tts_file		= "";
	this->str_chime_begin 	= "";
	this->str_chime_end  	= "";
	this->str_current_set	= "";

	this->str_tts_info 		= "";

	return ;
}

TTS_Handler::~TTS_Handler(void) {
	this->print_debug_info("TTS_Handler() instance destructed\n");
	this->close();

	return ;
}
		
void TTS_Handler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	printf("\033[33m");
	
	fprintf(stdout, "TTS_Handler::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	printf("\033[0m");
	
	return ;
}

void TTS_Handler::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

void TTS_Handler::close(void) {
	if( this->fp != NULL ) {
		fclose(this->fp);
		this->fp = NULL;
	}

	return ;
}

void TTS_Handler::open(void) {
	this->str_current_set = "";

	return ;
}

void TTS_Handler::reset_index(void) {
	this->num_tts_index = -1;

	return ;
}

void TTS_Handler::set_tts_play_status(bool _status) {
	this->print_debug_info("set_tts_play() set tts status [%d]\n", _status);
	this->is_play_tts = _status;

	return ;
}

void TTS_Handler::set_tts_index(int _idx) {
	this->print_debug_info("set_tts_index() set tts index [%d]\n", _idx);
	this->num_tts_index = _idx;

	return ;
}


void TTS_Handler::set_tts_file(string _name) {
	this->print_debug_info("set_tts_file() set tts file [%s]\n", _name.c_str());
	
	this->str_tts_file = _name;

	return ;
}

void TTS_Handler::set_tts_info(string _name) {
	this->print_debug_info("set_tts_info() set tts info [%s]\n", _name.c_str());
	
	this->str_tts_info = _name;

	return ;
}

void TTS_Handler::set_tts_chime(string _type, string _name) {
	this->print_debug_info("set_tts_chime() set tts chime type [%s], name [%s]\n", _type.c_str(), _name.c_str());
	
	if( _type.compare("begin") == 0 ) {
		this->str_chime_begin = _name;

	} else {
		this->str_chime_end   = _name;
	}

	return ;
}

int TTS_Handler::get_tts_index(void) {
	return this->num_tts_index;
}

string TTS_Handler::get_tts_info(void) {
	return this->str_tts_info;
}

bool TTS_Handler::is_tts_play_status(void) {
	return this->is_play_tts;
}

bool TTS_Handler::is_eof(void) {
	if( this->str_current_set.compare("") == 0 ) {
		if( this->str_chime_begin.compare("") != 0 ) {
			this->fp = fopen(this->str_chime_begin.c_str(), "r");
			this->str_current_set = "begin";

		} else {
			this->fp = fopen(this->str_tts_file.c_str(), "r");
			this->str_current_set = "tts";
		}
	}

	if( feof(this->fp) == 0 ) {
		return false;
	
	} else {
		if( this->str_current_set.compare("begin") == 0 ) {
			this->fp = fopen(this->str_tts_file.c_str(), "r");
			this->str_current_set = "tts";
			return false;

		} else if( this->str_current_set.compare("tts") == 0 ) {
			if( this->str_chime_end.compare("") != 0 ) {
				this->fp = fopen(this->str_chime_end.c_str(), "r");
				this->str_current_set = "end";
				return false;
				
			} else {
				return true;
			}
		}

		return true;
	}
}

int TTS_Handler::read(short *_data, int _size) {
	return (int)fread(_data, 2, _size, this->fp);
}