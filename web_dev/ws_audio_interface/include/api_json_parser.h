#ifndef __API_JSON_PARSER_H__
#define __API_JSON_PARSER_H__

#include <stdarg.h>
#include <vector>
#include <string>

#include "rapidjson/document.h"
#include "rapidjson/writer.h"
#include "rapidjson/stringbuffer.h"
#include "rapidjson/error/en.h"
#include "rapidjson/error/error.h"

using namespace std;
using namespace rapidjson;

class JsonParser {
	private :
		bool     is_debug_print = false;
		
		Document t_document;

		vector<string> split(string _string, string _delimiter);
		
	public :
		JsonParser(bool _is_debug_print = false);
		~JsonParser(void);
		
		bool	json_parse_to_object(const char *_data);
		string 	json_parse_to_string(void);
		string 	json_get_value(string _name);
		
		void 	set_debug_print(void);
		void	print_debug_info(const char *_format, ...);
};

#endif
