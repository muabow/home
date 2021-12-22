#include <stdio.h>
#include <sys/stat.h>

#include <iostream>
#include <sstream>
#include <fstream>

#include "api_json_parser.h"


JsonParser::JsonParser(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	this->t_document		= NULL;
	
	this->print_debug_info("JsonParser() create instance\n");
	
	return; 
}

JsonParser::~JsonParser(void) {
	this->print_debug_info("JsonParser() instance destructed\n");
	
	return ;
}

void JsonParser::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	fprintf(stdout, "JsonParser::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	return ;
}

void JsonParser::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

string JsonParser::read_file(string _file_path) {
	struct stat buffer;   
	if( stat(_file_path.c_str(), &buffer) != 0 ) {
		this->print_debug_info("read_file() not found file path [%s]\n", _file_path.c_str());
		return "";
	}
	  
	ostringstream read_data;
	ifstream fp(_file_path);
	read_data << fp.rdbuf();
	fp.close();
	
	this->print_debug_info("read_file() read from [%s] - [%s]\n", _file_path.c_str(), read_data.str().c_str());
	
	return read_data.str();
}

bool JsonParser::write_file(string _file_path, string _json_string) {
	ofstream out(_file_path);
	
	if( out.is_open() ) {
		out << _json_string;
		out.close();
		
		this->print_debug_info("write_file() write to [%s] - [%s]\n", _file_path.c_str(), _json_string.c_str());
		
		return true;
	
	} else {
		this->print_debug_info("write_file() open failed [%s]\n", _file_path.c_str());
		return false;
	}
}

bool JsonParser::parse(const string _json_string) {
	this->t_document = NULL;
	
	if( _json_string.compare("") == 0 ) {
		this->print_debug_info("parse() JSON parse error : [%02d] %s (offset: %u)\n",
				kParseErrorDocumentEmpty, GetParseError_En(kParseErrorDocumentEmpty), 0);
		
		return false;
	}
	
	ParseResult is_ok = this->t_document.Parse(_json_string.c_str());
	
	if( !is_ok ) {
		this->print_debug_info("parse() JSON parse error : [%02d] %s (offset: %u)\n",
				is_ok.Code(), GetParseError_En(is_ok.Code()), is_ok.Offset());
		
		this->t_document = NULL;
		
		return false;
	}

	this->print_debug_info("parse() convert to json object [%s]\n", _json_string.c_str());
	
	return true;
}

string JsonParser::to_string(void) {
	StringBuffer buffer;
	buffer.Clear();
	
	Writer<StringBuffer> writer(buffer);
	this->t_document.Accept(writer);
	
	string result = buffer.GetString();
	this->print_debug_info("to_string() convert to string [%s]\n", result.c_str());
	
	return result; 
}

// deprecated
string JsonParser::get_s(string _node_path) {
	int   cnt_vector_name;
	bool  is_last_vector = false;
	Value obj_value;
	
	StringBuffer buffer;
	buffer.Clear();
	Writer<StringBuffer> writer(buffer);
			
	vector<string> vector_json_name = this->split(_node_path, "/");
	cnt_vector_name = vector_json_name.size();
	
	Document::AllocatorType& allocator = this->t_document.GetAllocator();
	obj_value.CopyFrom(this->t_document, allocator);
	
	while( !vector_json_name.empty() ) {
		if( --cnt_vector_name == 0 ) {
			is_last_vector = true;
		}
		
		for( Value::ConstMemberIterator itr = obj_value.MemberBegin(); itr != obj_value.MemberEnd() ; ++itr ) {
			if( strcmp(itr->name.GetString(), vector_json_name.front().c_str()) == 0 ) {
				switch( itr->value.GetType() ) {
					case 3 :	// object
						if( !is_last_vector ) {
							obj_value = obj_value[itr->name.GetString()].GetObject();
							break;
						}
						obj_value[itr->name.GetString()].Accept(writer);
						
						return buffer.GetString();
						
					case 4 :	// array
						if( is_last_vector && obj_value[itr->name.GetString()].IsArray() ) {
							// array
							obj_value[itr->name.GetString()].Accept(writer);
							
							return buffer.GetString();
						
						} else {
							vector_json_name.erase(vector_json_name.begin());
							int idx = atoi(vector_json_name.front().c_str());
							if( idx >= (int)obj_value[itr->name.GetString()].Size() ) {
								return "";
							}
							
							if( --cnt_vector_name == 0 ) {
								is_last_vector = true;
							}
							
							if( is_last_vector ) {
								if( obj_value[itr->name.GetString()][idx].IsObject() ) {
									// array
									obj_value = obj_value[itr->name.GetString()][idx].GetObject();
									obj_value.Accept(writer);
																			
								} else {
									// array/index
									obj_value[itr->name.GetString()][idx].Accept(writer);
								}
								
								return buffer.GetString();
							
							} else {
								// array/index/value
								obj_value = obj_value[itr->name.GetString()][idx].GetObject();
							}
						}
						
						break;
						
					case 5 : 	// string
						if( obj_value[itr->name.GetString()].IsString() ) {
							return obj_value[itr->name.GetString()].GetString();
						}
						
						break;
						
					case 0 :	// null
					case 1 :	// false
					case 2 :	// true
					case 6 :	// number
						obj_value[itr->name.GetString()].Accept(writer);
						return buffer.GetString();
						
						break;
				}
				break;
			}
		}
		
		vector_json_name.erase(vector_json_name.begin());
	}
	
	return "";
}

string JsonParser::select(string _node_path) {
	string str_value = "";
	Value *value;

	if( (value = Pointer(_node_path.c_str()).Get(this->t_document)) == 0 ) {
		return str_value;
	}

	StringBuffer buffer;
	Writer<StringBuffer> writer(buffer);
	
	value->Accept(writer);
	str_value = buffer.GetString();
	
	// trim begin/end double quotes
	if ( str_value.front() == '"' ) {
		str_value.erase(str_value.begin());
	}

	if( str_value.back() == '"' ) {
		str_value.pop_back();
	}

	return str_value; 
}


bool JsonParser::update(string _node_path, int _value) {
	Value *value;
	if( (value = Pointer(_node_path.c_str()).Get(this->t_document)) == 0 ) {
		return false;
	}
	Pointer(_node_path.c_str()).Set(this->t_document, _value);
	
	return true;
}

bool JsonParser::update(string _node_path, double _value) {
	Value *value;
	if( (value = Pointer(_node_path.c_str()).Get(this->t_document)) == 0 ) {
		return false;
	}
	Pointer(_node_path.c_str()).Set(this->t_document, _value);
	
	return true;
}

bool JsonParser::update(string _node_path, string _value) {
	Value *value;
	if( (value = Pointer(_node_path.c_str()).Get(this->t_document)) == 0 ) {
		return false;
	}
	Pointer(_node_path.c_str()).Set(this->t_document, _value.c_str());
	
	return true;
}

bool JsonParser::add(string _node_path, int _value) {
	Value *value;
	if( (value = Pointer(_node_path.c_str()).Get(this->t_document)) == 0 ) {
		Pointer(_node_path.c_str()).Set(this->t_document, _value);
		return true;
	}
	
	return false;
}

bool JsonParser::add(string _node_path, double _value) {
	Value *value;
	if( (value = Pointer(_node_path.c_str()).Get(this->t_document)) == 0 ) {
		Pointer(_node_path.c_str()).Set(this->t_document, _value);
		return true;
	}
	
	return false;
}

bool JsonParser::add(string _node_path, string _value) {
	Value *value;
	if( (value = Pointer(_node_path.c_str()).Get(this->t_document)) == 0 ) {
		Pointer(_node_path.c_str()).Set(this->t_document, _value.c_str());
		return true;
	}
	
	return false;
}

bool JsonParser::remove(string _node_path) {
	Value *value;
	if( (value = Pointer(_node_path.c_str()).Get(this->t_document)) == 0 ) {
		return false;
	}
	Pointer(_node_path.c_str()).Erase(this->t_document);
	
	return true;
}


bool JsonParser::write_json(string _file_path) {
	StringBuffer buffer;
	PrettyWriter<StringBuffer> writer(buffer);
	
	if( this->t_document.Accept(writer) == false ){ 
		this->print_debug_info("write_json() open failed [%s]\n", _file_path.c_str());
		return false;
	}

	string temp = buffer.GetString();
	ofstream out(_file_path.c_str(),std::ofstream::trunc);
	out << temp;
	
	this->print_debug_info("write_json() write to [%s] - [%s]\n", _file_path.c_str(), temp.c_str());
	
	return true;
}

vector<string> JsonParser::split(string _string, string _delimiter) {
	size_t pos_start = 0, pos_end, delim_len = _delimiter.length();
	string token;
	vector<string> res;

	while ((pos_end = _string.find (_delimiter, pos_start)) != std::string::npos) {
		token = _string.substr (pos_start, pos_end - pos_start);
		pos_start = pos_end + delim_len;
		res.push_back(token);
	}

	res.push_back(_string.substr(pos_start));
	
	return res;
}

string JsonParser::replace_all(string &_str, const string& _from, const string& _to) {
    size_t start_pos = 0;
    
    while( (start_pos = _str.find(_from, start_pos)) != std::string::npos ) {
        _str.replace(start_pos, _from.length(), _to);
        start_pos += _to.length();
    }
    
    return _str;
}
