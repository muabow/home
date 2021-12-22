#include <stdio.h>
#include <iostream>

#include "api_json_parser.h"


JsonParser::JsonParser(bool _is_debug_print) {
	this->is_debug_print	= _is_debug_print;
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
	
	fprintf(stderr, "JsonParser::");
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


vector<string> JsonParser::split(string _string, string _delimiter) {
	size_t pos_start = 0, pos_end, delim_len = _delimiter.length();
	string token;
	vector<string> res;

	while ((pos_end = _string.find (_delimiter, pos_start)) != string::npos) {
		token = _string.substr (pos_start, pos_end - pos_start);
		pos_start = pos_end + delim_len;
		res.push_back(token);
	}

	res.push_back(_string.substr(pos_start));
	
	return res;
}

bool JsonParser::json_parse_to_object(const char *_data) {
	this->t_document = NULL;
	
	if( _data == NULL ) {
		this->print_debug_info("json_parse_to_object() JSON parse error : [%02d] %s (offset: %u)\n",
				kParseErrorDocumentEmpty, GetParseError_En(kParseErrorDocumentEmpty), 0);
		
		return false;
	}
	
	ParseResult is_ok = this->t_document.Parse(_data);
	
	if( !is_ok ) {
		this->print_debug_info("json_parse_to_object() JSON parse error : [%02d] %s (offset: %u)\n",
				is_ok.Code(), GetParseError_En(is_ok.Code()), is_ok.Offset());
		
		this->t_document = NULL;
		
		return false;
	}

	return true;
}

string JsonParser::json_parse_to_string(void) {
	StringBuffer buffer;
	buffer.Clear();
	
	Writer<StringBuffer> writer(buffer);
	this->t_document.Accept(writer);
	 
	return buffer.GetString(); 
}

string JsonParser::json_get_value(string _name) {
	int   cnt_vector_name;
	bool  is_last_vector = false;
	Value obj_value;
	
	StringBuffer buffer;
	buffer.Clear();
	Writer<StringBuffer> writer(buffer);
			
	vector<string> vector_json_name = this->split(_name, "/");
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
