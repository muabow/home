#include <stdio.h>
#include <unistd.h>

#include <vector>
#include <iostream>

#include "api_json_parser.h"

using namespace std;

/* test.json
	{
		"number": { "int": 1, "double": 1.1 },
		"string": "hello world",
		"bool": true,
		"array": [0, 2, 4, 8, 10],
		"null": null,
		"json_array": [{ "int": 2, "double": 2.2 }, { "int": 3, "double": 3.3 }, { "int": 4, "double": 4.4 }]
	}
 
 */

int main(void) {
	// json parser instance 생성
	// instance 생성 시 debug print set 가능
	// args([bool _is_debug_print])
	JsonParser json_parser;
	
	
	// debug print 출력
	json_parser.set_debug_print();
	
	
	// JSON 파일로 부터 JSON string 읽어오기
	// args(<string _file_path>)
	// return - success: string, fail: "" 
	string str_json = json_parser.read_file("./test.json");


	// JSON string 을 JSON document object로 변환
	// args(<string _json_string>)
	// return - true: success, false: failed
	json_parser.parse(str_json);
	
	
	// JSON object로 부터 JSON node value 호출
	// node value 는 단일/하위 path 까지 입력 가능
	// args(<string _node_path>)
	// return - success: string, fail: ""  
	cout << "\n# Parse JSON data" << endl;
	cout << "/number" 				<< "\t\t\t: "	<< json_parser.select("/number") 				<< endl;
	cout << "/number/int"			<< "\t\t: " 	<< json_parser.select("/number/int") 			<< endl;
	cout << "/number/double"		<< "\t\t: " 	<< json_parser.select("/number/double") 		<< endl;
	cout << "/string"				<< "\t\t\t: " 	<< json_parser.select("/string") 				<< endl;
	cout << "/bool"					<< "\t\t\t: " 	<< json_parser.select("/bool")	 				<< endl;
	cout << "/array"				<< "\t\t\t: " 	<< json_parser.select("/array")	 				<< endl;
	cout << "/array/0"				<< "\t\t: " 	<< json_parser.select("/array/0") 				<< endl;
	cout << "/array/1"				<< "\t\t: " 	<< json_parser.select("/array/1") 				<< endl;
	cout << "/array/2"				<< "\t\t: " 	<< json_parser.select("/array/2") 				<< endl;
	cout << "/array/3"				<< "\t\t: " 	<< json_parser.select("/array/3") 				<< endl;
	cout << "/array/4"				<< "\t\t: " 	<< json_parser.select("/array/4") 				<< endl;
	cout << "/null"					<< "\t\t\t: " 	<< json_parser.select("/null")	 				<< endl;
	cout << "/json_array"			<< "\t\t: " 	<< json_parser.select("/json_array")			<< endl;
	cout << "/json_array/0"			<< "\t\t: " 	<< json_parser.select("/json_array/0")			<< endl;
	cout << "/json_array/0/int"		<< "\t: " 		<< json_parser.select("/json_array/0/int")		<< endl;
	cout << "/json_array/0/double"	<< "\t: " 		<< json_parser.select("/json_array/0/double")	<< endl;
	cout << "/json_array/1"			<< "\t\t: " 	<< json_parser.select("/json_array/1")			<< endl;
	cout << "/json_array/1/int"		<< "\t: " 		<< json_parser.select("/json_array/1/int")		<< endl;
	cout << "/json_array/1/double"	<< "\t: " 		<< json_parser.select("/json_array/1/double")	<< endl;
	cout << "/json_array/2"			<< "\t\t: " 	<< json_parser.select("/json_array/2")			<< endl;
	cout << "/json_array/2/int"		<< "\t: " 		<< json_parser.select("/json_array/2/int")		<< endl;
	cout << "/json_array/2/double"	<< "\t: " 		<< json_parser.select("/json_array/2/double")	<< endl;
	cout << "/json_array/2/none"	<< "\t: " 		<< json_parser.select("/json_array/2/none")	<< endl;
	
	
	printf("\n# casting data type\n");
	// parse 된 모든 결과물은 string이기 때문에 데이터 타입에 맞게 casting 하여 사용
	int    num_int_value    = stoi(json_parser.select("/number/int"));
	double num_double_value = stod(json_parser.select("/number/double"));
	printf(" - int[%d] double[%lf] \n", num_int_value, num_double_value);
	
	
	printf("\n# split: vector<string> array\n");
	// string에서 지정한 delimiter로 분할하여 vector<string> array 로 생성
	// return - vector array
	vector<string> v_arr_values = json_parser.split("1,3,5,7,9", ",");
	for( int idx = 0 ; idx < v_arr_values.size() ; idx++ ) {
		printf(" - v_arr_values[%d] = %d\n", idx, stoi(v_arr_values.at(idx)));
	}
	
	printf("\n# JSON object to string\n");
	// instance에 저장된 JSON object를 string으로 변환
	// return - success: string, fail: "" 
	string to_string = json_parser.to_string();
	
	
	printf("\n# replace string\n");
	// string에서 해당하는 문자열을 모두 치환
	// args(<string _string, string _from, string _to>)
	// return - true: success, false: failed
	json_parser.replace_all(to_string, "hello world", "new world");
	printf(" -[%s]\n", to_string.c_str());

	
	printf("\n# write file\n");
	// json string을 file에 저장, pretty-printing 적용 안됨
	// args(<string _file_path, string _json_string>)
	// return - true: success, false: failed
	json_parser.write_file("test2.json", to_string);
	
	
	printf("\n# update json data\n");
	json_parser.update("/number/int",  5);
	json_parser.update("/number/test", 5);

	to_string = json_parser.to_string();
	printf(" -[%s]\n", to_string.c_str());
		
	
	printf("\n# add json data\n");
	json_parser.add("/number/int",  6);
	json_parser.add("/number/test", 6);
	
	to_string = json_parser.to_string();
	printf(" -[%s]\n", to_string.c_str());
	
	printf("\n# remove json data\n");
	json_parser.remove("/number/test");
	
	to_string = json_parser.to_string();
	printf(" -[%s]\n", to_string.c_str());
	
	printf("\n# write json data with pretty printing\n");
	json_parser.write_json("./test3.json");
	
	
	return 0;
}
