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
	sqlite3 *p_sql_handler = sql_audio_handler.get_handler();
	
	sqlite3_exec(p_sql_handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	sql_audio_handler.init(this->path_module_db);
	while( !sql_audio_handler.set_table(_type) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}

	this->print_debug_info("update_databse_status() update set [%s] value [%s]\n", _field.c_str(), _value.c_str());
	int result = 0;
	
	if( _format.compare("string") == 0 ) {
		result = sql_audio_handler.set_str(_field, _value); 
	
	} else {
		result = sql_audio_handler.set_int(_field, stoi(_value));
	}
	
	sqlite3_exec(p_sql_handler, "END TRANSACTION;", NULL, NULL, NULL);
	
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
	sqlite3 *p_sql_handler = sql_audio_handler.get_handler();
	
	sqlite3_exec(p_sql_handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	sql_audio_handler.init(this->path_module_db);
	while( !sql_audio_handler.set_table(_type) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}
	
	this->print_debug_info("update_databse_status() update set [%s] value [%s]\n", _field.c_str(), _value.c_str());
	int result = sql_audio_handler.set_str(_field, _value);

	sqlite3_exec(p_sql_handler, "END TRANSACTION;", NULL, NULL, NULL);
	
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
	sqlite3 *p_sql_handler = sql_audio_handler.get_handler();
	
	sqlite3_exec(p_sql_handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	sql_audio_handler.init(this->path_module_db);
	while( !sql_audio_handler.set_table(_type) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}
	
	this->print_debug_info("update_databse_status() update set [%s] value [%d]\n", _field.c_str(), _value);
	int result = sql_audio_handler.set_int(_field, _value);
	
	sqlite3_exec(p_sql_handler, "END TRANSACTION;", NULL, NULL, NULL);
	
	this->mutex_db_handler.unlock();

	return result;
}

mutex *AUDIO_Handler::get_mutex_db_handler(void) {
	
	return &this->mutex_db_handler;
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
	sqlite3 *p_sql_handler = sql_audio_handler.get_handler();
	
	sqlite3_exec(p_sql_handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	sql_audio_handler.init(this->path_module_db);
	while( !sql_audio_handler.set_table(_type) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}

	string module_use = sql_audio_handler.get("module_use");
	
	sqlite3_exec(p_sql_handler, "END TRANSACTION;", NULL, NULL, NULL);
	
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
	sqlite3 *p_sql_handler = sql_audio_handler.get_handler();
	
	sqlite3_exec(p_sql_handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	sql_audio_handler.init(this->path_module_db);
	while( !sql_audio_handler.set_table(_type) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}

	string module_use = sql_audio_handler.get("module_status");
	
	sqlite3_exec(p_sql_handler, "END TRANSACTION;", NULL, NULL, NULL);
	
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
	sqlite3 *p_sql_handler = sql_audio_handler.get_handler();
	
	sqlite3_exec(p_sql_handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	sql_audio_handler.init(this->path_module_db);
	while( !sql_audio_handler.set_table(_type) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}

	string network_cast_type = sql_audio_handler.get("network_cast_type");
	
	sqlite3_exec(p_sql_handler, "END TRANSACTION;", NULL, NULL, NULL);
	
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
	sqlite3 *p_sql_handler = sql_audio_handler.get_handler();
	
	sqlite3_exec(p_sql_handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	sql_audio_handler.init(this->path_module_db);
	while( !sql_audio_handler.set_table(_type) ) {
		usleep(TIME_DB_LOCK_WAIT);
	}

	string result = sql_audio_handler.get(_field);
	
	sqlite3_exec(p_sql_handler, "END TRANSACTION;", NULL, NULL, NULL);
	
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


/* audio_player */
AUDIO_PlayerHandler::AUDIO_PlayerHandler(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	this->print_debug_info("AUDIO_PlayerHandler() create instance\n");
	
	this->is_run			= false;
	this->is_play			= false;
	this->is_pause			= false;
	this->is_loop			= false;
	
	this->audio_play_index	= 0;
	this->audio_volume		= 0;
	
	this->is_play_prev		= false;
	this->is_play_next		= false;
	this->is_play_stop		= false;
	this->is_force_stop		= false;
	this->is_invalid_source	= false;
	this->is_change_index	= false;
	
	return ;
}

AUDIO_PlayerHandler::~AUDIO_PlayerHandler(void) {
	this->print_debug_info("AUDIO_PlayerHandler() instance destructed\n");
	
	return ;
}
		
void AUDIO_PlayerHandler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	printf("\033[33m");
	
	fprintf(stdout, "AUDIO_PlayerHandler::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	printf("\033[0m");
	
	return ;
}

void AUDIO_PlayerHandler::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

void AUDIO_PlayerHandler::set_player_status(string _type, bool _status) {
	if( _type.compare("is_run") == 0 ) {
		this->is_run 	= _status;
		
	} else if( _type.compare("is_play") == 0 ) {
		this->is_play	= _status;
	
	} else if( _type.compare("is_pause") == 0 ) {
		this->is_pause	= _status;
	
	} else if( _type.compare("is_loop") == 0 ) {
		this->is_loop	= _status;
	}
	
	return ;
}

bool AUDIO_PlayerHandler::get_player_status(string _type) {
	bool status = false;
	
	if( _type.compare("is_run") == 0 ) {
		status = this->is_run;
		
	} else if( _type.compare("is_play") == 0 ) {
		status = this->is_play;
	
	} else if( _type.compare("is_pause") == 0 ) {
		status = this->is_pause;
	
	} else if( _type.compare("is_loop") == 0 ) {
		status = this->is_loop;
	}
	
	return status;
}

void AUDIO_PlayerHandler::set_player_control(string _type, bool _status) {
	if( _type.compare("is_play_prev") == 0 ) {
		this->is_play_prev 	= _status;
		
	} else if( _type.compare("is_play_next") == 0 ) {
		this->is_play_next	= _status;
	
	} else if( _type.compare("is_play_stop") == 0 ) {
		this->is_play_stop	= _status;
	
	} else if( _type.compare("is_force_stop") == 0 ) {
		this->is_force_stop	= _status;
	
	} else if( _type.compare("is_invalid_source") == 0 ) {
		this->is_invalid_source	= _status;
	
	} else if( _type.compare("is_change_index") == 0 ) {
		this->is_change_index	= _status;
	}
	
	return ;
}

bool AUDIO_PlayerHandler::get_player_control(string _type) {
	bool status = false;
	
	if( _type.compare("is_play_prev") == 0 ) {
		status = this->is_play_prev;
		
	} else if( _type.compare("is_play_next") == 0 ) {
		status = this->is_play_next;
	
	} else if( _type.compare("is_play_stop") == 0 ) {
		status = this->is_play_stop;
	
	} else if( _type.compare("is_force_stop") == 0 ) {
		status = this->is_force_stop;
	
	} else if( _type.compare("is_invalid_source") == 0 ) {
		status = this->is_invalid_source;
	
	} else if( _type.compare("is_change_index") == 0 ) {
		status = this->is_change_index;
	}
	
	return status;
}

void AUDIO_PlayerHandler::set_player_index(int _index) {
	this->audio_play_index = _index;
	
	return ;
}

int AUDIO_PlayerHandler::get_player_index(void) {

	return this->audio_play_index;
}

void AUDIO_PlayerHandler::set_player_volume(int _volume) {
	this->audio_volume = _volume;
	
	return ;
}

int AUDIO_PlayerHandler::get_player_volume(void) {

	return this->audio_volume;
}


/* source info */
AUDIO_SourceInfo::AUDIO_SourceInfo(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	// this->print_debug_info("AUDIO_SourceInfo() create instance\n");
	
	this->is_play			= false;
	this->is_pause			= false;
	this->is_playlist		= false;
	this->is_valid_source	= false;
	this->is_ext_storage	= false;
	
	this->source_hash_id	= "";
	this->source_file_path	= "";
	this->source_name		= "";
	this->source_type		= "";
	
	this->audio_play_time	= 0;
	this->audio_loop_count	= 1;

	this->num_audio_format		= 0;
	this->num_sample_rate		= 0;
	this->num_channels			= 0;
	this->num_bit_rate			= 0;
	this->num_bits_per_sample	= 0;
	
	this->num_mp3_skip_bytes	= 0;
	this->num_end_skip_bytes	= 0;
	
	return ;
}

AUDIO_SourceInfo::~AUDIO_SourceInfo(void) {
	// this->print_debug_info("AUDIO_SourceInfo() instance destructed\n");
	
	return ;
}
		
void AUDIO_SourceInfo::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	printf("\033[33m");
	
	fprintf(stdout, "AUDIO_SourceInfo::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	printf("\033[0m");
	
	return ;
}

void AUDIO_SourceInfo::print_debug_verbose(const char *_format, ...) {
	if( !this->is_debug_verbose ) return ;
	
	printf("\033[35m");
	
	fprintf(stdout, "AUDIO_SourceInfo::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	printf("\033[0m");
	
	return ;
}

void AUDIO_SourceInfo::set_debug_print(void) {
	this->is_debug_print = true;
	
	// this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

void AUDIO_SourceInfo::set_debug_verbose(void) {
	this->is_debug_verbose = true;
	
	this->print_debug_verbose("set_debug_verbose() is set on\n");
	
	return ;
}


bool AUDIO_SourceInfo::is_valid_ext_type(string _file_path) {
	string ext_type = this->get_info_file_ext(_file_path);
	
	for_each(ext_type.begin(), ext_type.end(), [](char &_char) {
		_char = tolower(_char);
	});
		
	if( ext_type.compare("wav") == 0 || ext_type.compare("mp3") == 0 ) {
		return true;
	}
	
	return false;
}

bool AUDIO_SourceInfo::parse_source_wav(void) {
	char 	str_exec[4096];
	string 	read_data = "";
	
	array<char, 128> arr_buffer;
	
	sprintf(str_exec, "avprobe -v quiet -of json -show_streams \"%s\"", this->source_file_path.c_str());
	FILE *fp = popen(str_exec, "r");
	
	while( !feof(fp) ) {
		if( fgets(arr_buffer.data(), 128, fp) != nullptr ) {
            read_data += arr_buffer.data();
		}
    }
    pclose(fp);

	this->print_debug_verbose("parse_source_wav() avprobe result [%s] \n", read_data.c_str());
	
	read_data.erase(std::find_if(read_data.rbegin(), read_data.rend(), [](int ch) {
        return !std::isspace(ch);
	}).base(), read_data.end());

	if( (fp = fopen(this->source_file_path.c_str(), "r")) == NULL ) {
		this->print_debug_info("parse_source_wav() Unable to open file : [%s]\n", this->source_file_path.c_str());
		
		return false;
	}
	
	// parse wav header
	WAV_HEAD_RIFF_t t_wav_head_riff;
	WAV_HEAD_FMT_t  t_wav_head_fmt;
	WAV_HEAD_SUB_t  t_wav_head_sub;
	int num_data_size = 0;

	int rc;
	while( (rc = fread(&t_wav_head_sub, 1, sizeof(t_wav_head_sub), fp)) > 0 ) {
		if( (int)sizeof(t_wav_head_sub) > rc ) break;

		if( strncmp(t_wav_head_sub.data_id, "RIFF", 4) == 0 ) {
			fseek(fp, (sizeof(t_wav_head_sub) * -1), SEEK_CUR);

			// WAV HEAD : RIFF
			fread(&t_wav_head_riff, 1, sizeof(t_wav_head_riff), fp);
			
			this->print_debug_verbose("parse_source_wav() wav file info - detail info [%s] \n", this->source_file_path.c_str());
			this->print_debug_verbose(" # chunk [RIFF] descriptor\n");
			this->print_debug_verbose(" - ChunkID         : [%.4s]\n", 	t_wav_head_riff.riff);
			this->print_debug_verbose(" - ChunkSize       : [%d]\n", 	t_wav_head_riff.chunk_size);
			this->print_debug_verbose(" - Format          : [%.4s]\n", 	t_wav_head_riff.wave);

			if( strncmp(t_wav_head_riff.riff, "RIFF", 4) != 0 ) {
				this->print_debug_verbose(" # riff [ChunkID] header error, invalid file.\n");
				fclose(fp);
				return false;
			}

			if( strncmp(t_wav_head_riff.wave, "WAVE", 4) != 0 ) {
				this->print_debug_verbose(" # riff [Format] header error, invalid file.\n");
				fclose(fp);
				return false;
			}

		} else if( strncmp(t_wav_head_sub.data_id, "fmt ", 4) == 0 ) {
			fseek(fp, (sizeof(t_wav_head_sub) * -1), SEEK_CUR);
			
			// WAV HEAD : FMT
			fread(&t_wav_head_fmt, 1, sizeof(t_wav_head_fmt), fp);
			
			this->print_debug_verbose(" # chunk [fmt] descriptor\n");
			this->print_debug_verbose(" - subchunk1 ID    : [%.4s]\n",	t_wav_head_fmt.fmt);
			this->print_debug_verbose(" - subchunk1 Size  : [%d]\n", 	t_wav_head_fmt.fmt_chunk_size);
			this->print_debug_verbose(" - AudioFormat     : [%d]\n", 	t_wav_head_fmt.audio_format);
			this->print_debug_verbose(" - NumChannels     : [%d]\n", 	t_wav_head_fmt.channels);
			this->print_debug_verbose(" - SampleRate      : [%d]\n", 	t_wav_head_fmt.sample_rate);
			this->print_debug_verbose(" - ByteRate        : [%d]\n", 	t_wav_head_fmt.bytes_per_sec);
			this->print_debug_verbose(" - BlockAlign      : [%d]\n", 	t_wav_head_fmt.block_align);
			this->print_debug_verbose(" - BitsPerSample   : [%d]\n", 	t_wav_head_fmt.bits_per_sample);

			if( strncmp(t_wav_head_fmt.fmt, "fmt ", 4) != 0 ) {
				this->print_debug_verbose(" # fmt [subchunk1 ID] header error, invalid file.\n");
				fclose(fp);
				return false;
			}

			fseek(fp, (t_wav_head_fmt.fmt_chunk_size - 16), SEEK_CUR);

		} else {
			this->print_debug_verbose(" # chunk [meta data] descriptor\n");
			this->print_debug_verbose(" - subchunk2 ID    : [%.4s]\n", 	t_wav_head_sub.data_id);
			this->print_debug_verbose(" - subchunk2 Size  : [%d]\n",	t_wav_head_sub.data_size);

			if ( strncmp(t_wav_head_sub.data_id, "data", 4) == 0 ) {
				this->num_mp3_skip_bytes = ftell(fp);
				this->num_end_skip_bytes = this->num_mp3_skip_bytes + t_wav_head_sub.data_size;
				num_data_size = t_wav_head_sub.data_size;
			}
			
			fseek(fp, t_wav_head_sub.data_size, SEEK_CUR);
		}
	}

	fclose(fp);
	this->print_debug_verbose(" # wav header skip begin bytes : [%d]\n", this->num_mp3_skip_bytes);
	this->print_debug_verbose(" # wav header skip end   bytes : [%d]\n", this->num_end_skip_bytes);


	if( read_data.compare("{}") == 0 ) {
		this->print_debug_verbose("parse_source_wav() parse static wav header format\n");
		
		this->print_debug_verbose("parse_source_wav() wav file info - static header type [%s] \n", this->source_file_path.c_str());
		this->print_debug_verbose(" - duration        : [%d]\n", (num_data_size / t_wav_head_fmt.bytes_per_sec));
		this->print_debug_verbose(" - sample rate     : [%d]\n", t_wav_head_fmt.sample_rate);
		this->print_debug_verbose(" - channels        : [%d]\n", t_wav_head_fmt.channels);
		this->print_debug_verbose(" - bits per sample : [%d]\n", t_wav_head_fmt.bits_per_sample);
		this->print_debug_verbose(" - audio format    : [%d]\n", t_wav_head_fmt.audio_format);
		
		switch( t_wav_head_fmt.audio_format ) {
			case	WAV_FORMAT_PCM 			:
			case	WAV_FORMAT_IEEE_FLOAT 	:
			case 	WAV_FORMAT_ALAW 		:
			case 	WAV_FORMAT_MULAW 		:
				break;
			
			default :
				this->print_debug_verbose("parse_source_wav() invalid audio_format : [%0x04x]\n", t_wav_head_fmt.audio_format);
				return false;
				break;
		}

		if( !(t_wav_head_fmt.channels == 1 || t_wav_head_fmt.channels == 2) 	// 채널이 1/2가 아닌 경우
			|| t_wav_head_fmt.sample_rate > 48000								// sample_rate가 48k 이상이 ㄴ경우
			|| t_wav_head_fmt.bits_per_sample != 16 							// bits per sample 이 16이 아닌 경우
		) return false;


		this->audio_play_time		= num_data_size / t_wav_head_fmt.bytes_per_sec;
		this->num_sample_rate		= t_wav_head_fmt.sample_rate;
		this->num_channels 			= t_wav_head_fmt.channels;
		this->num_bits_per_sample	= t_wav_head_fmt.bits_per_sample;
		this->num_audio_format		= t_wav_head_fmt.audio_format;

		return true;
	}

    JsonParser json_parser(this->is_debug_print);
    json_parser.parse(read_data.c_str());

	string str_codec_name 		= json_parser.select("/streams/0/codec_name");

	if( str_codec_name.compare("mp3") == 0 ) {
		print_debug_info("parse_source_wav() invalid codec type : [mp3] \n");
		return false;
	}

	string str_duration			= json_parser.select("/streams/0/duration");
	string str_samplerate		= json_parser.select("/streams/0/sample_rate");
	string str_channels 		= json_parser.select("/streams/0/channels");
	string str_bits_per_sample	= json_parser.select("/streams/0/bits_per_sample");
	string str_audio_format		= json_parser.select("/streams/0/codec_tag");
	
	this->print_debug_verbose("parse_source_wav() wav file info [%s] \n", this->source_file_path.c_str());
	this->print_debug_verbose(" - duration        : [%s]\n", str_duration.c_str());
	this->print_debug_verbose(" - sample rate     : [%s]\n", str_samplerate.c_str());
	this->print_debug_verbose(" - channels        : [%s]\n", str_channels.c_str());
	this->print_debug_verbose(" - bits per sample : [%s]\n", str_bits_per_sample.c_str());
	this->print_debug_verbose(" - audio format    : [%s]\n", str_audio_format.c_str());

	try {
		this->audio_play_time		= stoi(str_duration);
		this->num_sample_rate		= stoi(str_samplerate);
		this->num_channels 			= stoi(str_channels);
		this->num_bits_per_sample	= stoi(str_bits_per_sample);
		this->num_audio_format		= (int)strtol(str_audio_format.c_str(), NULL, 0);
	
	} catch( ... ) {
		print_debug_info("parse_source_wav() invalid parse type error\n");
		return false;
	}
	
	switch( this->num_bits_per_sample ) {
		case 16 : 
			break;
		
		default :
			// sined s16_le 외 처리 불가
			return false;
			break;
	}

	switch( this->num_audio_format ) {
		case WAV_FORMAT_PCM 		:
		case WAV_FORMAT_IEEE_FLOAT 	:
		case WAV_FORMAT_ALAW 		:
		case WAV_FORMAT_MULAW 		:
		case WAV_FORMAT_EXTENSIBLE 	:
			break;

		default :
			return false;
			break;
	}

	return true;
}

int AUDIO_SourceInfo::skip_id3v2_tag(char *_data) {
	if( _data[0] == 'I' && _data[1] == 'D' && _data[2] == '3' ) {
		int	id3v2_major_version = _data[3];
		int	id3v2_minor_version = _data[4];
		int	id3v2_flag			= _data[5];

		this->print_debug_verbose("skip_id3v2_tag() ID3v2 information - version \n");
		this->print_debug_verbose(" - id3v2_major_version : [%d] \n", id3v2_major_version);
		this->print_debug_verbose(" - id3v2_minor_version : [%d] \n", id3v2_minor_version);
		this->print_debug_verbose(" - id3v2_flag          : [%d] \n", id3v2_flag);

		int	flag_unsync				= id3v2_flag & 0x80 ? 1 : 0;
		int flag_extender_header	= id3v2_flag & 0x40 ? 1 : 0;
		int flag_experimental_ind	= id3v2_flag & 0x20 ? 1 : 0;
		int flag_footer_present		= id3v2_flag & 0x10 ? 1 : 0;

		this->print_debug_verbose("skip_id3v2_tag() ID3v2 information - flags \n");
		this->print_debug_verbose(" - flag_unsync           : [%d] \n", flag_unsync);
		this->print_debug_verbose(" - flag_extender_header  : [%d] \n", flag_extender_header);
		this->print_debug_verbose(" - flag_experimental_ind : [%d] \n", flag_experimental_ind);
		this->print_debug_verbose(" - flag_footer_present   : [%d] \n", flag_footer_present);

		int z0	= _data[6];
		int z1	= _data[7];
		int z2	= _data[8];
		int z3	= _data[9];

		this->print_debug_verbose("skip_id3v2_tag() ID3v2 information - size \n");
		this->print_debug_verbose(" - z0 : [%d] \n", z0);
		this->print_debug_verbose(" - z1 : [%d] \n", z1);
		this->print_debug_verbose(" - z2 : [%d] \n", z2);
		this->print_debug_verbose(" - z3 : [%d] \n", z3);

		if( ((z0 & 0x80) == 0) && ((z1 & 0x80) == 0) && ((z2 & 0x80) == 0) && ((z3 & 0x80) == 0) ) {
			int header_size = sizeof(MP3_ID3_TAG_t);
			int tag_size	= ((z0 & 0x7f) * 2097152) + ((z1 & 0x7f) * 16384) + ((z2 & 0x7f) * 128) + (z3 & 0x7f);
			int footer_size	= flag_footer_present ? 10 : 0;

			this->print_debug_verbose("skip_id3v2_tag() header_size: [%d], tag_size: [%d], footer_size: [%d] \n", header_size, tag_size, footer_size);
			return header_size + tag_size + footer_size;	// bytes_to_skip
		}
	}
	
	return 0;
}

void AUDIO_SourceInfo::parse_frame_header(char *_data, MP3_HEADER_t *_t_header_info) {
	static map <int, string> map_version;
	map_version[0x00] = "2.5";
	map_version[0x01] = "x";
	map_version[0x02] = "2";
	map_version[0x03] = "1";

	static map <int, int> map_layer;
	map_layer[0x00] = 0;
	map_layer[0x01] = 3;
	map_layer[0x02] = 2;
	map_layer[0x03] = 1;

	static map <string, int *> map_bitrates;
	map_bitrates["V1L1"] = LIST_BIT_RATE[0];
	map_bitrates["V1L2"] = LIST_BIT_RATE[1];
	map_bitrates["V1L3"] = LIST_BIT_RATE[2];
	map_bitrates["V2L1"] = LIST_BIT_RATE[3];
	map_bitrates["V2L2"] = LIST_BIT_RATE[4];
	map_bitrates["V2L3"] = LIST_BIT_RATE[5];

	static map <string, int *> map_sample_rates;
	map_sample_rates["1"] 	= LIST_SAMPLE_RATE[3];
	map_sample_rates["2"] 	= LIST_SAMPLE_RATE[2];
	map_sample_rates["2.5"] = LIST_SAMPLE_RATE[0];

	static map <int, int *> map_samples;
	map_samples[0] = LIST_SAMPLES[0];
	map_samples[1] = LIST_SAMPLES[1];

	int	b0 = _data[0];	// 0xff
	int	b1 = _data[1];
	int	b2 = _data[2];
	int	b3 = _data[3];

	_t_header_info->frame_size	= 0;		
	_t_header_info->samples		= 0;

	this->print_debug_verbose("parse_frame_header() data information\n");
	this->print_debug_verbose("parse_frame_header() data[0]: 0x%02x, data[1]: 0x%02x, data[2]: 0x%02x, data[3]: 0x%02x\n", b0, b1, b2, b3);

	// version
	int version_bits	= (b1 & 0x18) >> 3;
	this->print_debug_verbose("parse_frame_header()  - version_bits   : [%d]\n", version_bits);
	// version - invalid check
	if( !(version_bits == 0x00 || version_bits == 0x02 || version_bits == 0x03) ) return ;

	string version		= map_version[version_bits];
	int simple_version	= stoi(version);
	if( version.compare("2.5") == 0 ) {
		simple_version = 2;
	}
	this->print_debug_verbose("parse_frame_header() version           : [%s]\n", version.c_str());
	this->print_debug_verbose("parse_frame_header() simple_version    : [%d]\n", simple_version);

	// layer
	int layer_bits = (b1 & 0x06) >> 1;
	int layer = map_layer[layer_bits];
	this->print_debug_verbose("parse_frame_header() layer             : [%d]\n", layer);

	// bitrate
	char bitrate_key[4];
	sprintf(bitrate_key, "V%dL%d", simple_version, layer);
	int bitrate_idx = (b2 & 0xf0) >> 4;
	int bitrate = 0;
	if( bitrate_idx < 16 && (simple_version >= 1 && simple_version <= 2) && (layer >= 1 && layer <= 3) ) {
		bitrate = map_bitrates[string(bitrate_key)][bitrate_idx];
	}
	this->print_debug_verbose("parse_frame_header() - simple_version  : [%d]\n", simple_version);
	this->print_debug_verbose("parse_frame_header() - layer           : [%d]\n", layer);
	this->print_debug_verbose("parse_frame_header() - bitrate_key     : [%s]\n", bitrate_key);
	this->print_debug_verbose("parse_frame_header() - bitrate_idx     : [%d]\n", bitrate_idx);
	this->print_debug_verbose("parse_frame_header() bitrate           : [%d]\n", bitrate);
	
	// sample_rate
	int sample_rate_idx = (b2 & 0x0c) >> 2;
	int sample_rate = 0;
	if(	(version.compare("1") == 0 || version.compare("2") == 0 || version.compare("2.5") == 0 ) 
		&&  (sample_rate_idx >= 0 && sample_rate_idx <= 2) ) {
 		sample_rate = map_sample_rates[version][sample_rate_idx];
	}
	this->print_debug_verbose("parse_frame_header() - version         : [%s]\n", version.c_str());
	this->print_debug_verbose("parse_frame_header() - sample_rate_idx : [%d]\n", sample_rate_idx);
	this->print_debug_verbose("parse_frame_header() sample_rate       : [%d]\n", sample_rate);

	sprintf(_t_header_info->version, "%s", version.c_str());
	_t_header_info->layer		= layer;
	_t_header_info->errp		= (b1 & 0x01);
	_t_header_info->bitrate		= bitrate;
	_t_header_info->sample_rate = sample_rate;
	_t_header_info->pad			= (b2 & 0x02) >> 1;
	_t_header_info->priv		= (b2 & 0x01);
	_t_header_info->mode		= (b3 & 0xc0) >> 6;
	_t_header_info->modex		= (b3 & 0x30) >> 4;
	_t_header_info->copyright	= (b3 & 0x08) >> 3;
	_t_header_info->original	= (b3 & 0x04) >> 2;
	_t_header_info->emphasis	= (b3 & 0x03);

	// error bit check sum, bitrate, sample_rate
	if( _t_header_info->errp == 0 || bitrate == 0 || sample_rate == 0 ) return ;

	// frame_size
	if( layer == 1 ) {
		_t_header_info->frame_size = (((12 * bitrate *  1000) / sample_rate) + _t_header_info->pad) * 4;
	 
	} else { // layer 2,3
		_t_header_info->frame_size = ((144 * bitrate * 1000) / sample_rate) + _t_header_info->pad;
	}	
	this->print_debug_verbose("parse_frame_header() frame_size        : [%d]\n", _t_header_info->frame_size);

	// samples
	this->print_debug_verbose("parse_frame_header() - simple_version  : [%d]\n", simple_version);
	this->print_debug_verbose("parse_frame_header() - layer           : [%d]\n", layer);
	if( (simple_version - 1) >= 0  && (layer - 1) >= 0 ) {
		_t_header_info->samples = map_samples[simple_version - 1][layer - 1];
	}
	this->print_debug_verbose("parse_frame_header() samples           : [%d]\n", _t_header_info->samples);

	return ;
}

/* TODO, method - parse_source_mp3, file parse 방식 사용 안함. probe로 변경.
bool AUDIO_SourceInfo::parse_source_mp3(void) {
	FILE* fp;
		
	if( (fp = fopen(this->source_file_path.c_str(), "r")) == NULL ) {
		this->print_debug_info("parse_source_mp3() Unable to open file : [%s]\n", this->source_file_path.c_str());
		
		return false;
	}
	
	MP3_ID3_TAG_t	t_mp3_tag_info;
	MP3_HEADER_t	t_mp3_head_info;
	
	fread(&t_mp3_tag_info, 1, sizeof(t_mp3_tag_info), fp);

	this->num_mp3_skip_bytes = this->skip_id3v2_tag((char *)&t_mp3_tag_info);
	fseek(fp, this->num_mp3_skip_bytes, SEEK_SET);

	int  data_size = sizeof(MP3_ID3_TAG_t);
	int  read_bytes  = 0;
	int  avg_bitrate = 0;
	int  avg_sample_rate = 0;
	
	double duration  = 0;
	char data[data_size];

    string str_channel_info = "";
	bool is_first_aau = false;

	while( !feof(fp) ) {
		read_bytes = fread(data, 1, data_size, fp);

		if( (int)read_bytes < data_size ) {
			break;

		} else if( (data[0] == 0xff) && ((data[1]) & 0xe0) ) {
			this->parse_frame_header(data, &t_mp3_head_info);

			if( t_mp3_head_info.frame_size == 0 ) {
				fseek(fp, 1, SEEK_CUR);
				continue;
			}

			fseek(fp, t_mp3_head_info.frame_size - data_size, SEEK_CUR);
            duration += ((double)t_mp3_head_info.samples / t_mp3_head_info.sample_rate);

			avg_bitrate		+= t_mp3_head_info.bitrate;
			avg_sample_rate += t_mp3_head_info.sample_rate;

			if( !is_first_aau ) {
				is_first_aau = true; 
				switch( t_mp3_head_info.mode ) {
					case    0   :	this->num_channels  = 2; break;	// Stereo
					case    1   :	this->num_channels  = 2; break; // Joint Stereo
					case    2   :	this->num_channels  = 1; break; // 2 mono channel
					case    3   :	this->num_channels  = 1; break; // Mono
					default     :	this->num_channels  = 0; break; // Unknown
				}
			
			} else {
				avg_bitrate 	/= 2;
				avg_sample_rate /= 2;
			}
			
		} else if( data[0] == 'T' && data[1] == 'A' && data[2] == 'G' ) {
			fseek(fp, 128 - data_size, SEEK_CUR);	//skip over id3v1 tag size
		
		} else {
			fseek(fp, data_size - 1, SEEK_CUR);
		}
	}
	this->audio_play_time = (int)round(duration);
	this->num_bit_rate    = avg_bitrate * 1000;
	this->num_sample_rate = avg_sample_rate;

	return true;
}
*/

bool AUDIO_SourceInfo::parse_source_mp3(void) {
	char str_exec[4096];
	sprintf(str_exec, "avprobe -v quiet -of json -show_streams \"%s\"", this->source_file_path.c_str());
	FILE *fp = popen(str_exec, "r");
	array<char, 128> arr_buffer;
	string read_data = "";
	
	while( !feof(fp) ) {
		if( fgets(arr_buffer.data(), 128, fp) != nullptr ) {
            read_data += arr_buffer.data();
		}
    }
    pclose(fp);
	
	this->print_debug_verbose("parse_source_mp3() avprobe result [%s] \n", read_data.c_str());

    JsonParser json_parser(this->is_debug_print);
    json_parser.parse(read_data.c_str());

	string str_duration   = json_parser.select("/streams/0/duration");
	string str_bitrate    = json_parser.select("/streams/0/bit_rate");
	string str_samplerate = json_parser.select("/streams/0/sample_rate");
	string str_channels   = json_parser.select("/streams/0/channels");

	this->print_debug_verbose("parse_source_mp3() mp3 file info [%s] \n", this->source_file_path.c_str());
	this->print_debug_verbose(" - duration        : [%s]\n", str_duration.c_str());
	this->print_debug_verbose(" - bit rate        : [%s]\n", str_bitrate.c_str());
	this->print_debug_verbose(" - sample rate     : [%s]\n", str_samplerate.c_str());
	this->print_debug_verbose(" - channels        : [%s]\n", str_channels.c_str());

	try {
		this->num_channels	  = stoi(str_channels);
		this->audio_play_time = floor(stoi(str_duration));
		this->num_bit_rate    = floor(stoi(str_bitrate));
		this->num_sample_rate = floor(stoi(str_samplerate));
	
	} catch( ... ) {
		print_debug_info("parse_source_mp3() invalid parse type error\n");
		return false;
	}
	
	if( (fp = fopen(this->source_file_path.c_str(), "r")) == NULL ) {
		this->print_debug_info("parse_source_mp3() Unable to open file : [%s]\n", this->source_file_path.c_str());
		
		return false;
	}
	
	MP3_ID3_TAG_t	t_mp3_tag_info;
	fread(&t_mp3_tag_info, 1, sizeof(t_mp3_tag_info), fp);
	this->num_mp3_skip_bytes = this->skip_id3v2_tag((char *)&t_mp3_tag_info);
	
	fclose(fp);

	return true;
}

void AUDIO_SourceInfo::set_file_info(string _file_path, bool _is_ext_storage) {
	hash<string> hasher;
	size_t hashed = hasher(_file_path);
	
	this->source_file_path	= _file_path;
	this->source_name		= this->get_info_file_name(_file_path);
	this->source_type		= this->get_info_file_ext(_file_path);
	this->source_hash_id	= to_string(hashed);

	bool parse_result = false;
	
	if( this->source_type.compare("wav") == 0 ) {
		parse_result = this->parse_source_wav();
		
	} else if( this->source_type.compare("mp3") == 0 ) {
		parse_result = this->parse_source_mp3();
	}
	
	this->is_ext_storage = _is_ext_storage;

	this->print_debug_info("set_file_info() \n");
	this->print_debug_info("set_file_info() source type [%s]\n", this->source_type.c_str());
	this->print_debug_info("set_file_info()  - file path       : [%s]\n", this->source_file_path.c_str());
	this->print_debug_info("set_file_info()  - file name       : [%s]\n", this->source_name.c_str());
	this->print_debug_info("set_file_info()  - file hash       : [%s]\n", this->source_hash_id.c_str());
	this->print_debug_info("set_file_info()  - play time       : [%02d:%02d] \n", (int)this->audio_play_time / 60, (int)this->audio_play_time % 60);
	this->print_debug_info("set_file_info()  - sample rate     : [%d]\n", this->num_sample_rate);
	this->print_debug_info("set_file_info()  - channels        : [%d]\n", this->num_channels);
	this->print_debug_info("set_file_info()  - audio format    : [0x%04x]\n", this->num_audio_format);
	this->print_debug_info("set_file_info()  - skip bytes      : [%d]\n", this->num_mp3_skip_bytes);
	this->print_debug_info("set_file_info()  - is ext_storage  : [%d]\n", this->is_ext_storage);

	if( this->source_type.compare("wav") == 0 ) {
		this->print_debug_info("set_file_info()  - bits per sample : [%d]\n", this->num_bits_per_sample);
	
	} else {
		this->print_debug_info("set_file_info()  - bit rate        : [%d]\n", this->num_bit_rate);
	}

	if( !parse_result ) {
		this->print_debug_info("set_file_info() # invalid source [%s][%s]\n", this->source_type.c_str(), this->source_name.c_str());
		return ;
	}

	this->is_valid_source = true;
	
	return ;
}

string AUDIO_SourceInfo::get_info_file_ext(string _file) {
	string ext_type = _file.substr(_file.find_last_of(".") + 1);
	
	for_each(ext_type.begin(), ext_type.end(), [](char &_char) {
		_char = tolower(_char);
	});
	
	return ext_type;
}

string AUDIO_SourceInfo::get_info_file_name(string _file) {
	string base_name = string(basename(_file.c_str()));
	
	return base_name.substr(0, _file.find_last_of("."));
}

bool AUDIO_SourceInfo::get_source_status(string _type) {
	if( _type.compare("is_play") == 0 ) {
		return this->is_play;
	
	} else if( _type.compare("is_pause") == 0 ) {
		return this->is_pause;
	
	} else if( _type.compare("is_playlist") == 0 ) {
		return this->is_playlist;
	
	} else if( _type.compare("is_valid_source") == 0 ) {
		return this->is_valid_source;
	
	} else if( _type.compare("is_ext_storage") == 0 ) {
		return this->is_ext_storage;
	}
	
	return false;
}

string AUDIO_SourceInfo::get_source_info(string _type) {
	if( _type.compare("source_hash_id") == 0 ) {
		return this->source_hash_id;
	
	} else if( _type.compare("source_file_path") == 0 ) {
		return this->source_file_path;
	
	} else if( _type.compare("source_name") == 0 ) {
		return this->source_name;
	
	} else if( _type.compare("source_type") == 0 ) {
		return this->source_type;
	}
	
	return "";
}

int AUDIO_SourceInfo::get_play_info(string _type) {
	if( _type.compare("audio_play_time") == 0 ) {
		return this->audio_play_time;
	
	} else if( _type.compare("audio_loop_count") == 0 ) {
		return this->audio_loop_count;
	
	} else if( _type.compare("num_sample_rate") == 0 ) {
		return this->num_sample_rate;
	
	} else if( _type.compare("num_channels") == 0 ) {
		return this->num_channels;
	
	} else if( _type.compare("num_bit_rate") == 0 ) {
		return this->num_bit_rate;
	
	} else if( _type.compare("num_bits_per_sample") == 0 ) {
		return this->num_bits_per_sample;
	
	} else if( _type.compare("num_mp3_skip_bytes") == 0 ) {
		return this->num_mp3_skip_bytes;
	
	} else if( _type.compare("num_audio_format") == 0 ) {
		return this->num_audio_format;
	
	} else if( _type.compare("num_end_skip_bytes") == 0 ) {
		return this->num_end_skip_bytes;
	}
	
	return 0;
}

void AUDIO_SourceInfo::set_source_status(string _type, bool _value) {
	if( _type.compare("is_play") == 0 ) {
		this->is_play = _value;
	
	} else if( _type.compare("is_pause") == 0 ) {
		this->is_pause = _value;
	
	} else if( _type.compare("is_playlist") == 0 ) {
		this->is_playlist = _value;
	
	} else if( _type.compare("is_valid_source") == 0 ) {
		this->is_valid_source = _value;
	
	} else if( _type.compare("is_ext_storage") == 0 ) {
		this->is_ext_storage = _value;
	}
	
	return ;
}

void AUDIO_SourceInfo::set_source_info(string _type, string _value) {
	if( _type.compare("source_hash_id") == 0 ) {
		this->source_hash_id = _value;
	
	} else if( _type.compare("source_file_path") == 0 ) {
		this->source_file_path = _value;
	
	} else if( _type.compare("source_name") == 0 ) {
		this->source_name = _value;
	
	} else if( _type.compare("source_type") == 0 ) {
		this->source_type = _value;
	}
	
	return ;
}

void AUDIO_SourceInfo::set_play_info(string _type, int _value) {
	if( _type.compare("audio_play_time") == 0 ) {
		this->audio_play_time = _value;
	
	} else if( _type.compare("audio_loop_count") == 0 ) {
		this->audio_loop_count = _value;
	
	} else if( _type.compare("num_sample_rate") == 0 ) {
		this->num_sample_rate = _value;
	
	} else if( _type.compare("num_channels") == 0 ) {
		this->num_channels = _value;
	
	} else if( _type.compare("num_bit_rate") == 0 ) {
		this->num_bit_rate = _value;
	
	} else if( _type.compare("num_bits_per_sample") == 0 ) {
		this->num_bits_per_sample = _value;
	
	} else if( _type.compare("num_mp3_skip_bytes") == 0 ) {
		this->num_mp3_skip_bytes = _value;
	
	} else if( _type.compare("num_audio_format") == 0 ) {
		this->num_audio_format = _value;
	
	} else if( _type.compare("num_end_skip_bytes") == 0 ) {
		this->num_end_skip_bytes = _value;
	}

	return ;
}



/* source handler */
AUDIO_SourceHandler::AUDIO_SourceHandler(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	this->print_debug_info("AUDIO_SourceHandler() create instance\n");
	
	this->path_source_dir		= "";
	this->path_source_dir_ext	= "";
	this->path_module_db		= "";
	
	this->mutex_db_handler = NULL;
	
	return ;
}

AUDIO_SourceHandler::~AUDIO_SourceHandler(void) {
	this->print_debug_info("AUDIO_SourceHandler() instance destructed\n");
	
	return ;
}
		
void AUDIO_SourceHandler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	printf("\033[33m");
	
	fprintf(stdout, "AUDIO_SourceHandler::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	printf("\033[0m");
	
	return ;
}

void AUDIO_SourceHandler::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

void AUDIO_SourceHandler::set_debug_verbose(void) {
	this->is_debug_verbose = true;
	
	this->print_debug_info("set_debug_verbose() is set on\n");
	
	return ;
}

void AUDIO_SourceHandler::set_source_path(string _path) {
	if( this->path_source_dir.compare(_path) != 0 ) { 
		this->print_debug_info("set_source_path() path set [%s]\n", _path.c_str());
		this->path_source_dir = _path;
	}
	
	return ;
}

void AUDIO_SourceHandler::set_source_path_ext(string _path) {
	if( this->path_source_dir_ext.compare(_path) != 0 ) { 
		this->print_debug_info("set_source_path_ext() path set [%s]\n", _path.c_str());
		this->path_source_dir_ext = _path;
	}
	
	return ;
}

void AUDIO_SourceHandler::set_database_path(string _path) {
	if( this->path_module_db.compare(_path) != 0 ) { 
		this->print_debug_info("set_database_path() path set [%s]\n", _path.c_str());
		this->path_module_db = _path;
	}
	
	return ;
}

void AUDIO_SourceHandler::set_mutex_db_handler(mutex *_mutex_handler) {
	this->print_debug_info("set_mutex_db_handler() set audio_handler instance mutex handler\n");
	this->mutex_db_handler = _mutex_handler;
	
	return ;
}


void AUDIO_SourceHandler::read_source_list(void) {
	mutex_source_list.lock();
	
	DIR* dirp = opendir(this->path_source_dir.c_str());
	if( dirp == NULL ) {
		this->print_debug_info("read_source_list() invalid path : [%s]\n", this->path_source_dir.c_str());
		mutex_source_list.unlock();
		return ;
	}
	struct dirent *dp;
	
	this->get_database_list();
	vector<AUDIO_SourceInfo> lv_source_list = this->v_source_list;
	this->v_source_list.clear();

	hash<string> hasher;

	while( (dp = readdir(dirp)) != NULL ) {
		if( dp->d_type == DIRENT_TYPE_FILE ) {
			string file_path = this->path_source_dir;
			file_path.append(dp->d_name);
			
			AUDIO_SourceInfo source_info;
			if( this->is_debug_print ) {
				source_info.set_debug_print();
			}
			if( this->is_debug_verbose ) {
				source_info.set_debug_verbose();
			}
			
			if( source_info.is_valid_ext_type(file_path) ) {
				// already exist file check
				bool is_exist_file = false;
				
				size_t hashed = hasher(file_path);
				string source_hash_id = to_string(hashed);
				
				for( int idx = 0 ; idx < (int)lv_source_list.size() ; idx++ ) { 
					if( lv_source_list[idx].get_source_info("source_hash_id").compare(source_hash_id) == 0 ) {
						// check empty colume
						if(    lv_source_list[idx].get_play_info("num_sample_rate") 	== -1 
							|| lv_source_list[idx].get_play_info("num_channels") 		== -1 
							|| lv_source_list[idx].get_play_info("num_bit_rate") 		== -1 
							|| lv_source_list[idx].get_play_info("num_bits_per_sample") == -1 
							|| lv_source_list[idx].get_play_info("num_mp3_skip_bytes") 	== -1 
							|| lv_source_list[idx].get_play_info("num_audio_format") 	== -1
							|| lv_source_list[idx].get_play_info("num_end_skip_bytes") 	== -1 ) {
							
							is_exist_file = false;
							break;
						
						} else {
							is_exist_file = true;
							this->v_source_list.push_back(lv_source_list[idx]);
							break;	
						}
					}
				}
				
				if( !is_exist_file ) {
					source_info.set_file_info(file_path);
					this->v_source_list.push_back(source_info);
				}
			}
		} 
	}
	closedir(dirp);

	// check exist sd disk file
    if( access(this->path_source_dir_ext.c_str(), F_OK) != -1 ) {
		DIR* dirp = opendir(this->path_source_dir_ext.c_str());
		if( dirp == NULL ) {
			this->print_debug_info("read_source_list() invalid path : [%s]\n", this->path_source_dir_ext.c_str());
			mutex_source_list.unlock();
			return ;
		}
		struct dirent *dp;
		
		while( (dp = readdir(dirp)) != NULL ) {
			if( dp->d_type == DIRENT_TYPE_FILE ) {
				string file_path = this->path_source_dir_ext;
				file_path.append(dp->d_name);
				
				AUDIO_SourceInfo source_info;
				if( this->is_debug_print ) {
					source_info.set_debug_print();
				}
				if( this->is_debug_verbose ) {
					source_info.set_debug_verbose();
				}
				
				if( source_info.is_valid_ext_type(file_path) ) {
					// already exist file check
					bool is_exist_file = false;
					
					size_t hashed = hasher(file_path);
					string source_hash_id = to_string(hashed);
					
					for( int idx = 0 ; idx < (int)lv_source_list.size() ; idx++ ) { 
						if( lv_source_list[idx].get_source_info("source_hash_id").compare(source_hash_id) == 0 ) {
							// check empty colume
							if(    lv_source_list[idx].get_play_info("num_sample_rate") 	== -1 
								|| lv_source_list[idx].get_play_info("num_channels") 		== -1 
								|| lv_source_list[idx].get_play_info("num_bit_rate") 		== -1 
								|| lv_source_list[idx].get_play_info("num_bits_per_sample") == -1 
								|| lv_source_list[idx].get_play_info("num_mp3_skip_bytes") 	== -1 
								|| lv_source_list[idx].get_play_info("num_audio_format") 	== -1 ) {
									break;
							}

							is_exist_file = true;
							this->v_source_list.push_back(lv_source_list[idx]);
							break;	
						}
					}
					
					if( !is_exist_file ) {
						source_info.set_file_info(file_path, true);
						this->v_source_list.push_back(source_info);
					}
				}
			} 
		}
		closedir(dirp);
	}
	
	// update source_list database
	this->sync_database_list();
	mutex_source_list.unlock();
	return ;
}

void AUDIO_SourceHandler::sort_source_list(vector<string> _v_hash_id_list) {
	mutex_source_list.lock();
	
	vector<AUDIO_SourceInfo> v_source_list;
	bool is_file_exist = false;
	int idx = 0, v_idx = 0;
	
	for( idx = 0 ; idx < (int)_v_hash_id_list.size() ; idx++ ) {
		string hash_id = _v_hash_id_list[idx];
		is_file_exist = false;
		
		for( v_idx = 0 ; v_idx < (int)this->v_source_list.size() ; v_idx++ ) {
			if( this->v_source_list[v_idx].get_source_info("source_hash_id").compare(hash_id) == 0 ) {
				is_file_exist = true;
				break;
			}
		}
		
		if( is_file_exist ) {
			v_source_list.push_back(this->v_source_list[v_idx]);
		}
	}
	
	this->v_source_list = v_source_list;
	
	// update source_list database
	this->change_database_list();
	
	mutex_source_list.unlock();
	return ;
}


void AUDIO_SourceHandler::remove_source_list(string _hash_id) {
	mutex_source_list.lock();
	bool is_file_exist = false;
	int  idx = 0;
	
	for( idx = 0 ; idx < (int)this->v_source_list.size() ; idx++ ) {
		if( this->v_source_list[idx].get_source_info("source_hash_id").compare(_hash_id) == 0 ) {
			is_file_exist = true;
			break;
		}
	}
	
	if( is_file_exist ) {
		string file_path = this->v_source_list[idx].get_source_info("source_file_path");
		
		if( remove(file_path.c_str()) != 0 ) {
			this->print_debug_info("remove_source_list() remove failed [%s] : [%02d] %s\n", file_path.c_str(), errno, strerror(errno));
		
		} else {
			this->print_debug_info("remove_source_list() remove success [%s]\n", file_path.c_str());
		}
		this->v_source_list.erase(this->v_source_list.begin() + idx);
	
	} else {
		this->print_debug_info("remove_source_list() not found hash_id [%s]\n", _hash_id.c_str());
	}
	
	// update source_list database
	this->sync_database_list();
	
	mutex_source_list.unlock();
	return ;
}

int AUDIO_SourceHandler::callback_is_exist(void *_this, int _argc, char **_argv, char **_col_name) {
	AUDIO_SourceHandler *p_handler = (AUDIO_SourceHandler *)_this;
	
	AUDIO_SourceInfo source_info;
	
	source_info.set_source_status("is_valid_source",	stoi(_argv[COL_IS_VALID_SOURCE]));
	source_info.set_source_status("is_play", 			stoi(_argv[COL_IS_PLAY]));
	source_info.set_source_status("is_playlist",		stoi(_argv[COL_IS_PLAYLIST]));
	
	source_info.set_source_info("source_hash_id", 		string(_argv[COL_SOURCE_HASH_ID]));
	source_info.set_source_info("source_file_path",		string(_argv[COL_SOURCE_FILE_PATH]));
	source_info.set_source_info("source_name", 			string(_argv[COL_SOURCE_NAME]));
	source_info.set_source_info("source_type", 			string(_argv[COL_SOURCE_TYPE]));

	source_info.set_play_info("audio_play_time", 		stoi(_argv[COL_AUDIO_PLAY_TIME]));
	source_info.set_play_info("audio_loop_count", 		stoi(_argv[COL_AUDIO_LOOP_COUNT]));

	source_info.set_source_status("is_ext_storage", 	stoi(_argv[COL_IS_EXT_STORAGE]));
	
	source_info.set_play_info("num_sample_rate", 		stoi(_argv[COL_NUM_SAMPLE_RATE]));
	source_info.set_play_info("num_channels", 			stoi(_argv[COL_NUM_CHANNELS]));
	source_info.set_play_info("num_bit_rate", 			stoi(_argv[COL_NUM_BIT_RATE]));
	source_info.set_play_info("num_bits_per_sample", 	stoi(_argv[COL_NUM_BITS_PER_SAMPLE]));
	source_info.set_play_info("num_mp3_skip_bytes", 	stoi(_argv[COL_NUM_MP3_SKIP_BYTES]));
	source_info.set_play_info("num_audio_format", 		stoi(_argv[COL_NUM_AUDIO_FORMAT]));
	source_info.set_play_info("num_end_skip_bytes", 	stoi(_argv[COL_NUM_END_SKIP_BYTES]));

	p_handler->v_exist_source_list.push_back(source_info);
	
	return 0;
}


void AUDIO_SourceHandler::sync_database_list(void) {
	this->mutex_db_handler->lock();
		
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler->unlock();
		
		return ;
	}
	
	sqlite3 *handler;
	
	if( sqlite3_open(this->path_module_db.c_str(), &handler) ) {
		this->print_debug_info("open() can't open database file : %s [%s]\n" , sqlite3_errmsg(handler), this->path_module_db.c_str());
		this->mutex_db_handler->unlock();
		
		return ;
	}
	sqlite3_exec(handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	char query_string[1024];
	bool is_exist = false;
	
	// 1. 파일은 있으나 DB에 없는 소스 정보 추가
	this->v_exist_source_list.clear();
	sprintf(query_string, "select * from source_info_list;");
	sqlite3_exec(handler, query_string, callback_is_exist, (void *)this, NULL);
	
	for( int idx = 0 ; idx < (int)this->v_source_list.size() ; idx++ ) {
		is_exist = false;
		
		// 파일을 읽어 만들어진 소스 정보가 DB 정보에 있다면 재생 정보만 갱신
		for( int ex_idx = 0 ; ex_idx < (int)this->v_exist_source_list.size() ; ex_idx++ ) {
			if( this->v_source_list[idx].get_source_info("source_hash_id").compare(this->v_exist_source_list[ex_idx].get_source_info("source_hash_id")) == 0 ) {
				is_exist = true;
				
				this->v_source_list[idx].set_source_status("is_play", 			this->v_exist_source_list[ex_idx].get_source_status("is_play"));
				this->v_source_list[idx].set_source_status("is_playlist", 		this->v_exist_source_list[ex_idx].get_source_status("is_playlist"));
				this->v_source_list[idx].set_play_info("audio_loop_count",		this->v_exist_source_list[ex_idx].get_play_info("audio_loop_count"));
				break;
			}
		}
		
		// 파일을 읽어 만들어진 소스 정보가 DB 정보에 없다면 DB에 파일 기반 소스 정보 추가
		if( !is_exist ) {
			sprintf(query_string, "insert into source_info_list values(%d, %d, %d, '%s', '%s', '%s', '%s', %d, %d, %d, %d, %d, %d, %d, %d, %d, %d);",
				this->v_source_list[idx].get_source_status("is_valid_source"),
				this->v_source_list[idx].get_source_status("is_play"),
				this->v_source_list[idx].get_source_status("is_playlist"),
				
				this->v_source_list[idx].get_source_info("source_hash_id").c_str(),
				this->v_source_list[idx].get_source_info("source_file_path").c_str(),
				this->v_source_list[idx].get_source_info("source_name").c_str(),
				this->v_source_list[idx].get_source_info("source_type").c_str(),
				
				this->v_source_list[idx].get_play_info("audio_play_time"),
				this->v_source_list[idx].get_play_info("audio_loop_count"),

				this->v_source_list[idx].get_source_status("is_ext_storage"),

				this->v_source_list[idx].get_play_info("num_sample_rate"),
				this->v_source_list[idx].get_play_info("num_channels"),
				this->v_source_list[idx].get_play_info("num_bit_rate"),
				this->v_source_list[idx].get_play_info("num_bits_per_sample"),
				this->v_source_list[idx].get_play_info("num_mp3_skip_bytes"),
				this->v_source_list[idx].get_play_info("num_audio_format"),
				this->v_source_list[idx].get_play_info("num_end_skip_bytes")
			);
			sqlite3_exec(handler, query_string, NULL, NULL, NULL);
		
		} else {
			// 파일/DB 정보가 매치되어있다면 파일의 valid 한 정보를 DB에 업데이트
			// DB colume 이 extend add 된 경우 초기값이 유지되기 때문에 파일로부터 읽은 정보를 갱신
			char str_query_target[1024];

			sprintf(str_query_target, "%s=%d, %s=%d, %s=%d, %s=%d, %s=%d, %s=%d, %s=%d, %s=%d, %s=%d", 
					"is_valid_source",		this->v_source_list[idx].get_source_status("is_valid_source"),
					"is_ext_storage", 		this->v_source_list[idx].get_source_status("is_ext_storage"),
					"num_sample_rate",		this->v_source_list[idx].get_play_info("num_sample_rate"),
					"num_channels",			this->v_source_list[idx].get_play_info("num_channels"),
					"num_bit_rate",			this->v_source_list[idx].get_play_info("num_bit_rate"),
					"num_bits_per_sample",	this->v_source_list[idx].get_play_info("num_bits_per_sample"),
					"num_mp3_skip_bytes",	this->v_source_list[idx].get_play_info("num_mp3_skip_bytes"),
					"num_audio_format",		this->v_source_list[idx].get_play_info("num_audio_format"),
					"num_end_skip_bytes",	this->v_source_list[idx].get_play_info("num_end_skip_bytes")
					);

			sprintf(query_string, "update source_info_list set %s where source_hash_id='%s';",
							str_query_target,
							this->v_source_list[idx].get_source_info("source_hash_id").c_str());
			
			sqlite3_exec(handler, query_string, NULL, NULL, NULL);
		}
	}
	
	// 2. 변경된 DB 정보를 다시 읽어서 DB 에는 있으나 파일 기반 소스 정보가 없다면 해당 DB 정보 삭제
	this->v_exist_source_list.clear();
	sprintf(query_string, "select * from source_info_list;");
	sqlite3_exec(handler, query_string, callback_is_exist, (void *)this, NULL);
	
	for( int idx = 0 ; idx < (int)this->v_exist_source_list.size() ; idx++ ) {
		is_exist = false;
		
		for( int ex_idx = 0 ; ex_idx < (int)this->v_source_list.size() ; ex_idx++ ) {
			if( this->v_exist_source_list[idx].get_source_info("source_hash_id").compare(this->v_source_list[ex_idx].get_source_info("source_hash_id")) == 0 ) {
				is_exist = true;
				break;
			}
		}
		
		if( !is_exist ) {
			sprintf(query_string, "delete from source_info_list where source_hash_id='%s';", this->v_exist_source_list[idx].get_source_info("source_hash_id").c_str());
			sqlite3_exec(handler, query_string, NULL, NULL, NULL);
		}
	}

	// 3. DB 정보에 맞게 파일 정렬
	this->v_exist_source_list.clear();
	sprintf(query_string, "select * from source_info_list;");
	sqlite3_exec(handler, query_string, callback_is_exist, (void *)this, NULL);
	
	for( int idx = 0 ; idx < (int)this->v_exist_source_list.size() ; idx++ ) {
		for( int ex_idx = 0 ; ex_idx < (int)this->v_source_list.size() ; ex_idx++ ) {
			if( this->v_exist_source_list[idx].get_source_info("source_hash_id").compare(this->v_source_list[ex_idx].get_source_info("source_hash_id")) == 0 ) {
				this->v_exist_source_list[idx] = this->v_source_list[ex_idx];
				break;
			}
		}
	}
	this->v_source_list = this->v_exist_source_list;

	sqlite3_exec(handler, "END TRANSACTION;", NULL, NULL, NULL);
	sqlite3_close(handler);
	
	this->mutex_db_handler->unlock();
	
	return ;
}

void AUDIO_SourceHandler::change_database_list(void) {
	this->mutex_db_handler->lock();
		
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler->unlock();
		
		return ;
	}
	
	sqlite3 *handler;
	
	if( sqlite3_open(this->path_module_db.c_str(), &handler) ) {
		this->print_debug_info("open() can't open database file : %s [%s]\n" , sqlite3_errmsg(handler), this->path_module_db.c_str());
		this->mutex_db_handler->unlock();
		
		return ;
	}
	sqlite3_exec(handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	char query_string[1024];

	sprintf(query_string, "delete from source_info_list;");
	sqlite3_exec(handler, query_string, callback_is_exist, (void *)this, NULL);
	
	for( int idx = 0 ; idx < (int)this->v_source_list.size() ; idx++ ) {
		sprintf(query_string, "insert into source_info_list values(%d, %d, %d, '%s', '%s', '%s', '%s', %d, %d, %d, %d, %d, %d, %d, %d, %d, %d);",
			this->v_source_list[idx].get_source_status("is_valid_source"),
			this->v_source_list[idx].get_source_status("is_play"),
			this->v_source_list[idx].get_source_status("is_playlist"),
			
			this->v_source_list[idx].get_source_info("source_hash_id").c_str(),
			this->v_source_list[idx].get_source_info("source_file_path").c_str(),
			this->v_source_list[idx].get_source_info("source_name").c_str(),
			this->v_source_list[idx].get_source_info("source_type").c_str(),
			
			this->v_source_list[idx].get_play_info("audio_play_time"),
			this->v_source_list[idx].get_play_info("audio_loop_count"),
			
			this->v_source_list[idx].get_source_status("is_ext_storage"),

			this->v_source_list[idx].get_play_info("num_sample_rate"),
			this->v_source_list[idx].get_play_info("num_channels"),
			this->v_source_list[idx].get_play_info("num_bit_rate"),
			this->v_source_list[idx].get_play_info("num_bits_per_sample"),
			this->v_source_list[idx].get_play_info("num_mp3_skip_bytes"),
			this->v_source_list[idx].get_play_info("num_audio_format"),
			this->v_source_list[idx].get_play_info("num_end_skip_bytes")
		);
		sqlite3_exec(handler, query_string, NULL, NULL, NULL);
	}
	
	sqlite3_exec(handler, "END TRANSACTION;", NULL, NULL, NULL);
	sqlite3_close(handler);
	
	this->mutex_db_handler->unlock();
	
	return ;
}

void AUDIO_SourceHandler::get_database_list(void) {
	this->mutex_db_handler->lock();
		
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler->unlock();
		
		return ;
	}
	
	sqlite3 *handler;
	
	if( sqlite3_open(this->path_module_db.c_str(), &handler) ) {
		this->print_debug_info("open() can't open database file : %s [%s]\n" , sqlite3_errmsg(handler), this->path_module_db.c_str());
		this->mutex_db_handler->unlock();
		
		return ;
	}
	sqlite3_exec(handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	char query_string[1024];
	
	this->v_exist_source_list.clear();
	sprintf(query_string, "select * from source_info_list;");
	sqlite3_exec(handler, query_string, callback_is_exist, (void *)this, NULL);
	
	this->v_source_list = this->v_exist_source_list;
	
	sqlite3_exec(handler, "END TRANSACTION;", NULL, NULL, NULL);
	sqlite3_close(handler);
	
	this->mutex_db_handler->unlock();
	
	return ;
}

vector<AUDIO_SourceInfo> AUDIO_SourceHandler::get_source_list(void) {
	
	return this->v_source_list;
}

string AUDIO_SourceHandler::make_json_source_list(void) {
	mutex_source_list.lock();
	
	int 	num_source_list = (int)this->v_source_list.size();
	string	str_source_list = "[";
	
	Document 	doc_data;
	StringBuffer data_buffer;
	Writer<StringBuffer> data_writer(data_buffer);
	
	string json_data;
	
	for( int idx = 0 ; idx < num_source_list ; idx++ ) {
		data_writer.Reset(data_buffer); 
		Pointer("/is_valid_source"	).Set(doc_data, this->v_source_list[idx].get_source_status("is_valid_source"));
		Pointer("/is_play"			).Set(doc_data, this->v_source_list[idx].get_source_status("is_play"));
		Pointer("/is_playlist"		).Set(doc_data, this->v_source_list[idx].get_source_status("is_playlist"));
		Pointer("/source_hash_id"	).Set(doc_data, this->v_source_list[idx].get_source_info("source_hash_id").c_str());
		Pointer("/source_name"		).Set(doc_data, this->v_source_list[idx].get_source_info("source_name").c_str());
		Pointer("/source_type"		).Set(doc_data, this->v_source_list[idx].get_source_info("source_type").c_str());
		Pointer("/audio_play_time"	).Set(doc_data, this->v_source_list[idx].get_play_info("audio_play_time"));
		Pointer("/audio_loop_count"	).Set(doc_data, this->v_source_list[idx].get_play_info("audio_loop_count"));
		Pointer("/num_sample_rate"	).Set(doc_data, this->v_source_list[idx].get_play_info("num_sample_rate"));
		Pointer("/num_channels"		).Set(doc_data, this->v_source_list[idx].get_play_info("num_channels"));
		Pointer("/num_bit_rate"		).Set(doc_data, this->v_source_list[idx].get_play_info("num_bit_rate"));
		Pointer("/is_ext_storage"	).Set(doc_data, this->v_source_list[idx].get_source_status("is_ext_storage"));

		doc_data.Accept(data_writer);
		
		json_data = data_buffer.GetString();
		str_source_list.append(json_data);
		if( idx + 1 < num_source_list ) {
			str_source_list.append(",");
		}
		data_buffer.Clear();
	}
	str_source_list.append("]");
	
	mutex_source_list.unlock();
	
	return str_source_list;
}

string AUDIO_SourceHandler::make_json_source_name_list(void) {
	mutex_source_list.lock();
	
	int 	num_source_list = (int)this->v_source_list.size();
	string	str_source_list = "";
	
	for( int idx = 0 ; idx < num_source_list ; idx++ ) {
		if( !this->v_source_list[idx].get_source_status("is_valid_source") ) {
			continue;
		}

		str_source_list.append(this->v_source_list[idx].get_source_info("source_name"));
		str_source_list.append("|");
	}
	str_source_list = str_source_list.substr(0, str_source_list.size() - 1);

	mutex_source_list.unlock();
	
	return str_source_list;
}

void AUDIO_SourceHandler::update_source_info(int _idx, string _type, bool _value) {
	this->mutex_db_handler->lock();
			
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler->unlock();
		
		return ;
	}
	
	sqlite3 *handler;
	
	if( sqlite3_open(this->path_module_db.c_str(), &handler) ) {
		this->print_debug_info("open() can't open database file : %s [%s]\n" , sqlite3_errmsg(handler), this->path_module_db.c_str());
		this->mutex_db_handler->unlock();
		
		return ;
	}
	sqlite3_exec(handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	char query_string[1024];
	
	if( _type.compare("is_play") == 0 ) {
		sprintf(query_string, "update source_info_list set %s=0;", _type.c_str());
		sqlite3_exec(handler, query_string, NULL, NULL, NULL);
	}
	
	sprintf(query_string, "update source_info_list set %s=%d where source_hash_id='%s';", _type.c_str(), (int)_value, this->v_source_list[_idx].get_source_info("source_hash_id").c_str());
	sqlite3_exec(handler, query_string, NULL, NULL, NULL);

	sqlite3_exec(handler, "END TRANSACTION;", NULL, NULL, NULL);
	sqlite3_close(handler);
	
	this->v_source_list[_idx].set_source_status(_type, _value);
	
	this->mutex_db_handler->unlock();
	
	return ;
}

void AUDIO_SourceHandler::update_source_info(int _idx, string _type, string _value) {
	this->mutex_db_handler->lock();
			
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler->unlock();
		
		return ;
	}
	
	sqlite3 *handler;
	
	if( sqlite3_open(this->path_module_db.c_str(), &handler) ) {
		this->print_debug_info("open() can't open database file : %s [%s]\n" , sqlite3_errmsg(handler), this->path_module_db.c_str());
		this->mutex_db_handler->unlock();
		
		return ;
	}
	sqlite3_exec(handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	char query_string[1024];

	if( _type.compare("is_play") == 0 ) {
		sprintf(query_string, "update source_info_list set %s=0;", _type.c_str());
		sqlite3_exec(handler, query_string, NULL, NULL, NULL);
	}

	sprintf(query_string, "update source_info_list set %s='%s' where source_hash_id='%s';", _type.c_str(), _value.c_str(), this->v_source_list[_idx].get_source_info("source_hash_id").c_str());
	sqlite3_exec(handler, query_string, NULL, NULL, NULL);
	
	sqlite3_exec(handler, "END TRANSACTION;", NULL, NULL, NULL);
	sqlite3_close(handler);
	
	this->v_source_list[_idx].set_source_info(_type, _value);
	
	this->mutex_db_handler->unlock();
	
	return ;
}


void AUDIO_SourceHandler::update_source_info(int _idx, string _type, int _value) {
	this->mutex_db_handler->lock();
			
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler->unlock();
		
		return ;
	}
	
	sqlite3 *handler;
	
	if( sqlite3_open(this->path_module_db.c_str(), &handler) ) {
		this->print_debug_info("open() can't open database file : %s [%s]\n" , sqlite3_errmsg(handler), this->path_module_db.c_str());
		this->mutex_db_handler->unlock();
		
		return ;
	}
	sqlite3_exec(handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);

	char query_string[1024];

	if( _type.compare("is_play") == 0 ) {
		sprintf(query_string, "update source_info_list set %s=0;", _type.c_str());
		sqlite3_exec(handler, query_string, NULL, NULL, NULL);
	}
	
	sprintf(query_string, "update source_info_list set %s=%d where source_hash_id='%s';", _type.c_str(), _value, this->v_source_list[_idx].get_source_info("source_hash_id").c_str());
	sqlite3_exec(handler, query_string, NULL, NULL, NULL);
	
	sqlite3_exec(handler, "END TRANSACTION;", NULL, NULL, NULL);
	sqlite3_close(handler);
	
	this->v_source_list[_idx].set_play_info(_type, _value);
	
	this->mutex_db_handler->unlock();
	
	return ;
}

void AUDIO_SourceHandler::listup_source_info(string _hash_id, bool _is_listup, int _loop_count) {
	this->mutex_db_handler->lock();
			
	if( this->path_module_db.compare("") == 0 ) {
		this->mutex_db_handler->unlock();
		
		return ;
	}
	
	sqlite3 *handler;
	
	if( sqlite3_open(this->path_module_db.c_str(), &handler) ) {
		this->print_debug_info("open() can't open database file : %s [%s]\n" , sqlite3_errmsg(handler), this->path_module_db.c_str());
		this->mutex_db_handler->unlock();
		
		return ;
	}
	sqlite3_exec(handler, "BEGIN IMMEDIATE TRANSACTION;", NULL, NULL, NULL);
	
	char query_string[1024];
	sprintf(query_string, "update source_info_list set is_playlist=%d where source_hash_id='%s';", (int)_is_listup, _hash_id.c_str());
	sqlite3_exec(handler, query_string, NULL, NULL, NULL);
	
	if( _loop_count != -1 ) {
		sprintf(query_string, "update source_info_list set audio_loop_count=%d where source_hash_id='%s';", _loop_count, _hash_id.c_str());
		sqlite3_exec(handler, query_string, NULL, NULL, NULL);
	}

	sqlite3_exec(handler, "END TRANSACTION;", NULL, NULL, NULL);
	sqlite3_close(handler);
	
	for( int idx = 0 ; idx < (int)this->v_source_list.size() ; idx++ ) {
		if( this->v_source_list[idx].get_source_info("source_hash_id").compare(_hash_id) == 0 ) {
			this->v_source_list[idx].set_source_status("is_playlist", _is_listup);
			
			if( _loop_count != -1 ) {
				this->v_source_list[idx].set_play_info("audio_loop_count", _loop_count);
			}
			break;
		}
	}
	
	this->mutex_db_handler->unlock();
	
	return ;
}
