# sample file 컴파일 옵션
> arm-linux-gnueabihf-g++ -o sample sample.cpp -I./include -L./ -lapi_sqlite -lsqlite3 -std=c++11

# sample file 실행하기 위한 LD_LIBRARY_PATH 설정
> export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:$(pwd)