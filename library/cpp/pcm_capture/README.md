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

# LD_LIBRARY_PATH 설정
export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:"$(pwd)/usr/lib"

# PCM Capture 실행 방법, -v (verbose)
 ./pcm_capture -v
main::main() set print debug
SignalHandler::set_debug_print() set debug print
PCM_CaptureHandler::set_debug_print() is set on
SignalHandler::set_signal() bind signal event [Interrupt]
SignalHandler::set_signal() bind signal event [Killed]
SignalHandler::set_signal() bind signal event [Terminated]
SignalHandler::set_ignore() bind ignore event [Broken pipe]
PCM_CaptureHandler::init() parameter information..
PCM_CaptureHandler::init() parameter - period size      : [4]
PCM_CaptureHandler::init() parameter - division periods : [4]
PCM_CaptureHandler::init() parameter - chunk size       : [4096]
PCM_CaptureHandler::init() parameter - sample rate      : [48000]
PCM_CaptureHandler::init() parameter - channels         : [1]
PCM_CaptureHandler::init() parameter - deivce name      : [default]
PCM_CaptureHandler::set_pcm_driver() h/w params set information..
PCM_CaptureHandler::set_pcm_driver() h/w params set - sample rate     : [48000]
PCM_CaptureHandler::set_pcm_driver() h/w params set - channels        : [1]
PCM_CaptureHandler::set_pcm_driver() h/w params set - pcm buffer time : [85333]
PCM_CaptureHandler::set_pcm_driver() h/w params set - pcm period time : [21333]
PCM_CaptureHandler::set_pcm_driver() h/w params set - pcm buffer size : [4096]
PCM_CaptureHandler::set_pcm_driver() h/w params set - pcm period size : [1024]
PCM_CaptureHandler::set_pcm_driver() h/w params set - pcm frame bytes : [2]
PCM_CaptureHandler::set_pcm_driver() h/w params set - frame latency   : [0.042667]
PCM_CaptureHandler::set_pcm_driver() h/w params set - pcm width       : [16]
PCM_CaptureHandler::init() PCM device init success [default]
PCM_CaptureHandler::set_queue_handler() set queue function
PCM_CaptureHandler::run() create execute thread
PCM_CaptureHandler::execute() start capture
PCM_CaptureHandler::stop() join & wait audio thread term
PCM_CaptureHandler::execute() stop capture PCM file...
PCM_CaptureHandler::stop() Free PCM device [default]
main::main() process has been terminated.
PCM_CaptureHandler::PCM_CaptureHandler() instance destructed : [default]
SignalHandler::SignalHandler() instance destructed

# 실행 종료 방법
CTRL + C (SIGINT)

# PCM 파일 생성 확인 (main.cpp 내에서 변경 가능)
ls /tmp/capture.pcm 

# PCM 파일 재생 방법 (main.cpp Capture parameter 참고)
aplay -D default -t raw -r 48000 -c 1 -f S16_LE /tmp/capture.pcm

