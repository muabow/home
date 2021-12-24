#include <iostream>
#include <vector>

using namespace std;

vector<string> explode(const string _delimiter, const string _string) {
	size_t pos_start = 0, pos_end, delim_len = _delimiter.length();
	string token;
	vector<string> v_list;

	while ((pos_end = _string.find(_delimiter, pos_start)) != std::string::npos) {
		token = _string.substr (pos_start, pos_end - pos_start);
		pos_start = pos_end + delim_len;
		v_list.push_back(token);
	}

	v_list.push_back(_string.substr(pos_start));
	
	return v_list;
}

int main(void) {
    string str_num_list = "one_two_three";
    
    vector<string> v_num_list = explode("_", str_num_list);
    for( int idx = 0 ; idx < (int)v_num_list.size() ; idx++ ) {
        cout << v_num_list[idx] << endl;
    }

    return 0;
}