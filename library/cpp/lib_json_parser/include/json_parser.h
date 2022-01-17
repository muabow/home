#ifndef __JSON_PARSER_H__
#define __JSON_PARSER_H__

#include <stdarg.h>
#include <string>
#include <vector>

#include "rapidjson/document.h"
#include "rapidjson/pointer.h"
#include "rapidjson/writer.h"
#include "rapidjson/prettywriter.h"
#include "rapidjson/stringbuffer.h"
#include "rapidjson/error/en.h"
#include "rapidjson/error/error.h"

using namespace std;
using namespace rapidjson;

class JsonParser {
	private :
		bool	is_debug_print = false;
		void	print_debug_info(const char *_format, ...);
		
		Document t_document;
		
	public :
		JsonParser(bool _is_debug_print = false);
		~JsonParser(void);
		
		void 	set_debug_print(void);

		string  read_file(string _file_path);
		bool	write_file(string _file_path, string _json_string);
		bool	write_json(string _file_path);
		
		bool	parse(const string _data);
		string 	to_string(void);
		
		string 	get_s(string _node_path); // deprecated
		string 	select(string _node_path);
		bool	update(string _node_path, int    _value);
		bool	update(string _node_path, double _value);
		bool	update(string _node_path, string _value);
		bool	add(string _node_path, int    _value);
		bool	add(string _node_path, double _value);
		bool	add(string _node_path, string _value);
		bool	remove(string _node_path);
		
		vector<string>	split(string _string, string _delimiter);
		string 			replace_all(string &_str, const string& _from, const string& _to);
		
};
#endif
