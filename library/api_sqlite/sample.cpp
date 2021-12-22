#include <stdio.h>
#include <unistd.h>

#include "api_sqlite.h"

int main(void) {
	// sqlite handler instance 생성
	SqlHandler sql_handler;
	
	// debug print 출력
	sql_handler.set_debug_print();
		
	/* test.db 
	 CREATE TABLE input(
		CP_NUM TEXT,
		VALUE  INTEGER
	 );
	 CREATE TABLE output(
		CP_NUM TEXT,
		VALUE  INTEGER
	 );
	 CREATE TABLE `info` (
		`page`  TEXT
	 );
	*/
	
	// databse open 및 init 처리
	// args(<string _path>)
	// return - true: success, false: failed
	if( !sql_handler.init("./test.db") ) {
		printf("not found database file\n");
		return -1;
	}

	// table 명 입력
	// table setting 시 table 내 모든 값들을 내부 vector table로 읽어서 query 에 따른 중복 I/O 방지 
	// args(<string _path>, [string _query])
	// return - true: success, false: failed
	// sql_handler.set_table("info");
	
	// 추가 query 인자를 통해 where 문 사용 가능
	// 추가 query 사용 시 get() method에 지정하는 column 에 모두 적용 됨.
	// sql_handler.set_table("output", "where \"CP_NUM\"=\"CP1\";");
	
	// getter method 3가지 제공
	// string _key 는 column 명을 의미
	// get(string _key)		: string value return
	// get_str(string _key)	: string value return (get() method 동일)
	// get_int(string _key)	: integer value return
	
	// setter method 3가지 제공
	// string _key 는 column 명을 의미
	// update(string _query)				: column명과 상관 없이 full query를 사용 (ex. update info set "page"="output";) 
	// set_int(string _key, int _value)		: integer colume과 integer value로 업데이트
	// set_str(string _key, string _value)  : string colume과 string value로 업데이트
	// * column 명을 사용하여 update 하더라도 set_table에서 추가 query를 지정하였다면 update도 해당 query의 영향을 받음.
		
	
	{ // 1. 단일 row 호출
		/* info table
		 +-------+
		 | page  |
		 +=======+
		 | input |
		 +-------+ 
		 */
		sql_handler.set_table("info");
		string page = sql_handler.get("page");
		cout << "page : " << page << endl;
	}
	
	{ // 2. 다중 row 호출
		/* output table
		 +---------+---------+
		 | CP_NUM  | VALUE   |
		 +=========+=========+
		 | CP1     | 0       |
		 +---------+---------+
		 | CP2     | 0       |
		 +---------+---------+
		 */
		sql_handler.set_table("output", "where \"CP_NUM\"=\"CP1\"");
		int int_value = sql_handler.get_int("VALUE");
		cout << "CP1 [get_int] value : " << int_value << endl;
		
		string str_value = sql_handler.get_str("VALUE");
		cout << "CP1 [get_str] value : " << str_value << endl;
		
		string get_value = sql_handler.get("VALUE");
		cout << "CP1 [get] value : " << get_value << endl;
	}
	
	{ // 3. 단일 row 업데이트
		/* info table
		 +-------+
		 | page  |
		 +=======+
		 | input | -> output
		 +-------+ 
		 */
		sql_handler.set_table("info");
		string value = "output";
		sql_handler.set_str("page", value);
		
		string page = sql_handler.get("page");
		cout << " - update" << endl;
		cout << "page : " << page << endl;
		
		// full query를 이용한 [value] = input 원복
		char query[1024];
		sprintf(query, "update info set \"page\"=\"input\";");
		sql_handler.update(string(query));
		
		cout << " - update restore" << endl;
		sql_handler.set_table("info");
		page = sql_handler.get("page");
		cout << "page : " << page << endl;
	}
	
	{ // 4. 다중 row 업데이트
		/* output table
		 +---------+---------+
		 | CP_NUM  | VALUE   |
		 +=========+=========+
		 | CP1     | 0       | -> 1
		 +---------+---------+
		 | CP2     | 0       |
		 +---------+---------+
		 */
		sql_handler.set_table("output", "where \"CP_NUM\"=\"CP1\"");
		sql_handler.set_int("VALUE", 1);
		
		cout << "- update" << endl;
		int int_value = sql_handler.get_int("VALUE");
		cout << "CP1 [get_int] value : " << int_value << endl;
		
		string str_value = sql_handler.get_str("VALUE");
		cout << "CP1 [get_str] value : " << str_value << endl;
		
		string get_value = sql_handler.get("VALUE");
		cout << "CP1 [get] value : " << get_value << endl;
		

		// full query를 이용한 [CP1][VALUE] = 0 원복
		char query[1024];
		sprintf(query, "update output set \"VALUE\"=0 where \"CP_NUM\"=\"CP1\";");
		sql_handler.update(string(query));
		
		cout << "- update restore" << endl;
		sql_handler.set_table("output", "where \"CP_NUM\"=\"CP1\"");
		int_value = sql_handler.get_int("VALUE");
		cout << "CP1 [get_int] value : " << int_value << endl;
		
		str_value = sql_handler.get_str("VALUE");
		cout << "CP1 [get_str] value : " << str_value << endl;
		
		get_value = sql_handler.get("VALUE");
		cout << "CP1 [get] value : " << get_value << endl;
	}

	return 0;
}
