# 개발 환경  
Ubuntu 16.04.3 LTS, kernel 4.4.0-78-generic, x86  
  
# Directory 구성  
src/json_parser.cpp  
include/json_parser.h  
include/rapidjson  
libjson_parser.so  
makefile  
README.md  
sample  
sample.cpp  
test.json  
test2.json
test3.json
  
# sample file 컴파일 옵션  
arm-linux-gnueabihf-g++ -o sample sample.cpp -I./include -L./ -ljson_parser -std=c++11  
  
# sample file 실행하기 위한 LD_LIBRARY_PATH 설정  
export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:$(pwd)  


