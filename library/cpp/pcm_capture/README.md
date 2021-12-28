# 개발 환경
Ubuntu 16.04.3 LTS, kernel 4.4.0-78-generic, x86

# Directory 구성
src/pcm_capture.cpp
src/main.cpp
include/pcm_capture.h
include/main.h
usr/lib/libsignal.so
usr/include/lib_signal.h
makefile
README.md
pcm_capture

# ALSA capture device 확인 방법
arecord -L
-------------------------------------------------------------------------------------
default
    Playback/recording through the PulseAudio sound server
null
    Discard all samples (playback) or generate zero samples (capture)
pulse
    PulseAudio Sound Server
-------------------------------------------------------------------------------------

# 컴파일 방법
make clean ; make

# libasound library 오류가 발생할 때
sudo apt-get update -y
sudo apt-get install -y libasound2-dev

# PCM Capture 실행 방법, -v (verbose)
sudo ./pcm_capture -v

# PCM 파일 생성 확인 (main.cpp 내에서 변경 가능)
ls /tmp/capture.pcm 

# PCM 파일 재생 방법 (main.cpp Capture parameter 참고)
aplay -D default -t raw -r 48000 -c 1 -f S16_LE /tmp/capture.pcm
