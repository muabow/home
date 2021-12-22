#include "main.h"


// ##
// functions: common
// ##
void print_debug_info(const char *_format, ...) {
	if( !g_is_debug_print ) return ;
	
	fprintf(stdout, "main::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	return ;
}

// ##
// function: sub function
// ##
string strtolower(const string _str) {
    string ret = _str;
    transform(ret.begin(), ret.end(), ret.begin(), ::tolower);
    
	return ret;
}

void set_vt_attr_value(string _data, string _option_name, int *_attr_value) {
	string node_name = "/";
	node_name.append(_option_name);

	JsonParser json_parser;
	json_parser.parse(_data.c_str());
	
	string str_result;
	str_result = json_parser.select(node_name);
	
	*_attr_value = -1;
	if( str_result.compare("") != 0 ) { 
		*_attr_value = stoi(str_result);
		print_debug_info("  - set attribute [%-15s] : %d\n", _option_name.c_str(), *_attr_value);
	}

	return ;
}

void set_vt_attribute(VTAPI_HANDLE *_t_vtapi_handle, int _attr_type, int _attr_value) {
	if( _attr_value == -1 ) {
		// default setting
		return ;
	}
	int ret;

	print_debug_info("set_vt_attribute() VTAPI_SetAttr() set attrubute parameter : [%-19s] > %d\n", g_list_attr_name[_attr_type].c_str(), _attr_value);
	if( (ret = VTAPI_SetAttr(*_t_vtapi_handle, _attr_type, _attr_value)) < 0 ) {
		print_debug_info("set_vt_attribute() VTAPI_SetAttr() failed : [%02d] [%s/%s]\n", ret, VTAPI_GetLastErrorInfo(0)->szMsg, VTAPI_GetLastErrorInfo(*_t_vtapi_handle)->szMsg);
	}

	return ;
}

void print_usage_info(char *_basename, char *_path_output) {
	printf("usage: %s [option]\n", _basename);
	printf("  -v : [OPTIONAL] print normal debug message \n");
	printf("  -p : [OPTIONAL] input output file path. default : [%s] \n", _path_output);
	printf("  -o : [OPTIONAL] change tts attributes, using json format string\n");
	printf("                  ex. {\"pitch\": 100, \"speed\": 100, \"volume\": 100, \"sentence_pause\": 100, \"comma_pause\": 100}\n");
	printf("                  > pitch           : 100 %%,  50 ~ 200 %% \n");
	printf("                  > speed           : 100 %%,  50 ~ 400 %% \n");
	printf("                  > volume          : 100 %%,   0 ~ 500 %% \n");
	printf("                  > sentence_pause  : 0 msec,  0 ~ 65536 msec \n");
	printf("                  > comma_pause     : 0 msec,  0 ~ 65536 msec \n");
	printf("                  > dict_idx        : -1,      0 ~ 1023 index \n");
	printf("                  > parent_hesisnum : -1,      0 ~ n \n");
	printf("                  > emphasis_factor : -1,     -n ~ 0 ~ n \n");
	printf("\033[33m" "  -l : <REQUIRED> input language type  [kor, eng, rus, bre, ger, fre, esp, chi, jpn] \n" "\033[0m");
	printf("\033[33m" "  -g : <REQUIRED> input speaker gender [male, female] \n" "\033[0m");
	printf("\033[33m" "  -m : <REQUIRED> input speak string \n" "\033[0m");

	return ;
}


// ##
// function: main
// ##
int main(int _argc, char ** _argv) {
	int  opt;
	
	ATTR_INFO_t t_attr_info;

	string str_path_output = ENV_DFLT_OUTPUT_PATH;
	string str_type_lang   = "";
	string str_type_gender = "";
	string str_data_speak  = "";
	string str_data_option = "";

	while( (opt = getopt(_argc, _argv, "vp:l:m:g:o:h")) != -1 ) {
		switch( opt ) {
			case 'v' :
				g_is_debug_print = true;
				print_debug_info("main() set print debug\n");
				break;

			case 'p' :
				if( optarg != NULL ) str_path_output = string(optarg);
				break;

			case 'l' : 
				if( optarg != NULL ) str_type_lang = string(optarg);
				break;

			case 'm' : 
				if( optarg != NULL ) str_data_speak = string(optarg);
				break;
			
			case 'g' : 
				if( optarg != NULL ) str_type_gender = string(optarg);
				break;

			case 'o' : 
				if( optarg != NULL ) str_data_option = string(optarg);
				break;

			default :
				print_usage_info(basename(_argv[0]), (char *)str_path_output.c_str());
				return -1;
				
				break;
		}
	}

	// options valid check
	if( str_type_lang.compare("") == 0 || str_type_gender.compare("") == 0 || str_data_speak.compare("") == 0 ) {
		printf("> input <REQUIRED> option\n\n");
		print_usage_info(basename(_argv[0]), (char *)str_path_output.c_str());

		return EXIT_FAILURE;
	}

	str_type_lang = strtolower(str_type_lang);
	vector<string> v_lang_list{"kor", "eng", "rus", "bre", "ger", "fre", "esp", "chi", "jpn"};
	if( find(v_lang_list.begin(), v_lang_list.end(), str_type_lang) == v_lang_list.end() ) {
		printf("> not support language : [%s]\n\n", str_type_lang.c_str());
		print_usage_info(basename(_argv[0]), (char *)str_path_output.c_str());

		return EXIT_FAILURE;
	}
	
	str_type_gender = strtolower(str_type_gender);
	vector<string> v_gender_list{"male", "female"};
	if( find(v_gender_list.begin(), v_gender_list.end(), str_type_gender) == v_gender_list.end() ) {
		printf("> not support gender : [%s]\n\n", str_type_gender.c_str());
		print_usage_info(basename(_argv[0]), (char *)str_path_output.c_str());

		return EXIT_FAILURE;
	}

	if( str_data_option.compare("") != 0 ) {
		print_debug_info("main() change attribute information\n");
	}
	set_vt_attr_value(str_data_option, "pitch", 			&t_attr_info.pitch);
	set_vt_attr_value(str_data_option, "speed", 			&t_attr_info.speed);
	set_vt_attr_value(str_data_option, "volume", 			&t_attr_info.volume);
	set_vt_attr_value(str_data_option, "dict_idx", 			&t_attr_info.dict_idx);
	set_vt_attr_value(str_data_option, "sentence_pause", 	&t_attr_info.sentence_pause);
	set_vt_attr_value(str_data_option, "comma_pause",		&t_attr_info.comma_pause);
	set_vt_attr_value(str_data_option, "parent_hesisnum", 	&t_attr_info.parent_hesisnum);
	set_vt_attr_value(str_data_option, "emphasis_factor", 	&t_attr_info.emphasis_factor);


	// init vt engine 
	int ret = -1;
	int num_text_format   = TEXT_FORMAT_UTF8;
	int num_output_format = FORMAT_16PCM_WAV;

	VTAPI_ENGINE_HANDLE t_engine_handle = NULL;
	VTAPI_HANDLE 		t_vtapi_handle  = NULL;

	VTAPI_Init((char *)PATH_VTAPI_LIBRARY);
	print_debug_info("main() Verify vt library path : [%s]\n", PATH_VTAPI_LIBRARY);

	if( (t_vtapi_handle = VTAPI_CreateHandle()) == NULL ) {
		print_debug_info("main() VTAPI_CreateHandle() failed : [%s]\n", VTAPI_GetLastErrorInfo(0)->szMsg);

		VTAPI_Exit();

		return EXIT_FAILURE;
	}

	string str_gender = (str_type_gender.compare("male") == 0 ? "m" : "f");
	if( (t_engine_handle = VTAPI_GetEngineEx2((char *)str_type_lang.c_str(), (char *)str_gender.c_str())) <= 0 ) {
		print_debug_info("main() VTAPI_GetEngine() failed : [%s/%s]\n", t_engine_handle, VTAPI_GetLastErrorInfo(0)->szMsg, VTAPI_GetLastErrorInfo(t_vtapi_handle)->szMsg);
		
		VTAPI_ReleaseHandle(t_vtapi_handle);
		VTAPI_Exit();

		return EXIT_FAILURE;
	}
	print_debug_info("main() VTAPI_GetEngine information \n");
	print_debug_info("main()  - language type : [%s] \n", str_type_lang.c_str());
	print_debug_info("main()  - gender type   : [%s] \n", str_type_gender.c_str());
	print_debug_info("main()  - speak string  : [%s] \n", str_data_speak.c_str());

	if ( (ret = VTAPI_SetUserKeyword(t_engine_handle, (char *)ENV_USER_KEYWORD)) != VTAPI_SUCCESS ) {
		print_debug_info("main() VTAPI_SetUserKeyword() failed : [%02d] [%s/%s]\n", ret, VTAPI_GetLastErrorInfo(0)->szMsg, VTAPI_GetLastErrorInfo(t_vtapi_handle)->szMsg);
		
		VTAPI_ReleaseHandle(t_vtapi_handle);
		VTAPI_Exit();

		return EXIT_FAILURE;
	}

	if( (ret = VTAPI_SetEngineHandle(t_vtapi_handle, t_engine_handle)) != VTAPI_SUCCESS ) {
		print_debug_info("main() VTAPI_SetEngineHandle() failed : [%02d] [%s/%s]\n", ret, VTAPI_GetLastErrorInfo(0)->szMsg, VTAPI_GetLastErrorInfo(t_vtapi_handle)->szMsg);

		VTAPI_UnloadEngine(t_engine_handle);
		VTAPI_ReleaseHandle(t_vtapi_handle);
		VTAPI_Exit();

		return EXIT_FAILURE;
	}


	// change attribute parameter
	set_vt_attribute(&t_vtapi_handle, ATTR_PITCH,	 		t_attr_info.pitch);
	set_vt_attribute(&t_vtapi_handle, ATTR_SPEED,			t_attr_info.speed);
	set_vt_attribute(&t_vtapi_handle, ATTR_VOLUME, 			t_attr_info.volume);
	set_vt_attribute(&t_vtapi_handle, ATTR_PAUSE, 			t_attr_info.sentence_pause);
	set_vt_attribute(&t_vtapi_handle, ATTR_DICTIDX, 		t_attr_info.dict_idx);
	set_vt_attribute(&t_vtapi_handle, ATTR_COMMAPAUSE, 		t_attr_info.comma_pause);
	set_vt_attribute(&t_vtapi_handle, ATTR_PARENTHESISNUM, 	t_attr_info.parent_hesisnum);
	set_vt_attribute(&t_vtapi_handle, ATTR_EMPHASISFACTOR, 	t_attr_info.emphasis_factor);


	// vt output handle
	if( (ret = VTAPI_SetOutputFile(t_vtapi_handle, (char *)str_path_output.c_str(), num_output_format)) != VTAPI_SUCCESS ) {
		print_debug_info("main() VTAPI_SetOutputFile() failed : [%02d] [%s/%s]\n", ret, VTAPI_GetLastErrorInfo(0)->szMsg, VTAPI_GetLastErrorInfo(t_vtapi_handle)->szMsg);
		
		VTAPI_UnloadEngine(t_engine_handle);
		VTAPI_ReleaseHandle(t_vtapi_handle);
		VTAPI_Exit();

		return EXIT_FAILURE;
	}

	if( (ret = VTAPI_TextToFile(t_vtapi_handle, (void *)str_data_speak.c_str(), -1, num_text_format)) != VTAPI_SUCCESS ) {
		print_debug_info("main() VTAPI_TextToFile() failed : [%02d] [%s/%s]\n", ret, VTAPI_GetLastErrorInfo(0)->szMsg, VTAPI_GetLastErrorInfo(t_vtapi_handle)->szMsg);
		
		VTAPI_UnloadEngine(t_engine_handle);
		VTAPI_ReleaseHandle(t_vtapi_handle);
		VTAPI_Exit();

		return EXIT_FAILURE;
	}

	VTAPI_UnloadEngine(t_engine_handle);
	VTAPI_ReleaseHandle(t_vtapi_handle);

	VTAPI_Exit();
	print_debug_info("main() process termed\n");

	return EXIT_SUCCESS;
}