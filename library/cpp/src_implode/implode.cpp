#include <iostream>
#include <vector>

using namespace std;

string implode(const string _glue, const vector<string> _pieces) {
    int     num_pieces = _pieces.size();
    string  str_result = "";

    for( int idx = 0 ; idx < num_pieces ; idx++ ) {
        str_result += _pieces[idx];
        
        if( idx < num_pieces - 1 ) {
            str_result += _glue;
        }
    }
    
    return str_result;
}


int main(void) {
    vector<string> v_num_list = {"one", "two", "three"};
    
    string str_result = implode("_", v_num_list);
    cout << str_result << endl;

    return 0;
}