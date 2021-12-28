# 개발 환경
Ubuntu 16.04.3 LTS, kernel 4.4.0-78-generic, x86

# Directory 구성
src/lib_signal.cpp
include/lib_signal.h
libsignal.so
makefile
README.md
sample
sample.cpp

# sample file 컴파일 옵션 
g++ -o sample sample.cpp -I./include -L./ -lsignal -std=c++11 -lpthread

# sample file 실행하기 위한 LD_LIBRARY_PATH 설정
export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:$(pwd)