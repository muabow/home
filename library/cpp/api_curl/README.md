# 개발 환경
Ubuntu 16.04.3 LTS, kernel 4.4.0-78-generic, x86

# directory 구성
src/api_curl.cpp
include/api_curl.h
libapi_curl.so
makefile
README.md
sample
sample.cpp

# curl library 설치
sudo apt-get install libcurl4-openssl-dev

# sample file 컴파일
g++ -o sample sample.cpp -I./include -L. -lapi_curl -lcurl -std=c++11

# sample file 실행하기 위한 LD_LIBRARY_PATH 설정
export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:$(pwd)
./sample
