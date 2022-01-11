# 개발 환경
Ubuntu 16.04.3 LTS, kernel 4.4.0-78-generic, x86  
  
# Directory 구성  
src  
└ socket_server.cpp  
└ src/main.cpp  
include  
└ socket_server.h  
└ include/main.h  
usr  
└/lib  
  └/libsignal.so  
└/include  
  └/lib_signal.h  
makefile  
README.md  
socket_server
  
# 컴파일 방법  
make clean ; make  
  
# LD_LIBRARY_PATH 설정  
export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:"$(pwd)/usr/lib"  
  
# Socket server 실행 방법, -v (verbose)  
 ./socket_server -v  
main::main() set print debug  
SignalHandler::set_debug_print() set debug print  
SOCKET_UnicastServer::set_debug_print() is set on  
SignalHandler::set_signal() bind signal event [Interrupt]  
SignalHandler::set_signal() bind signal event [Killed]  
SignalHandler::set_signal() bind signal event [Terminated]  
SignalHandler::set_ignore() bind ignore event [Broken pipe]  
SignalHandler::set_signal_handler() bind user term function  
main::run_server_unicast() run : [unicast]  
SOCKET_UnicastServer::set_event_handler() set function  
SOCKET_UnicastServer::init() mutex_worker_func lock()  
SOCKET_UnicastServer::init() mutex_worker_func unlock()  
SOCKET_UnicastServer::init() init unicast socket success  
SOCKET_UnicastServer::run() create execute thread  
SOCKET_UnicastServer::execute() run unicast thread  
SOCKET_UnicastServer::execute() find the client reconnects.  
SOCKET_UnicastServer::execute() mutex_worker_func lock()  
SOCKET_UnicastServer::execute() num_worker_thread : [0]  
SOCKET_UnicastServer::is_reconnect() check reconnect : [false]  
SOCKET_UnicastServer::execute() not found connected client. add new client.  
SOCKET_UnicastWorker::set_debug_print() is set on  
SOCKET_UnicastWorker::init() [/127.0.0.1] worker ready  
SOCKET_UnicastWorker::set_index() worker instance index set [0]  
SOCKET_UnicastWorker::set_event_handler() set function  
main::network_server_event_handler() connect client : [0] 127.0.0.1  
main::network_server_event_handler() max[20], accrue[1], current[1]  
SOCKET_UnicastWorker::run() create worker thread  
SOCKET_UnicastServer::execute() worker index[0] add complete.  
SOCKET_UnicastServer::execute() mutex_worker_func unlock()  
SOCKET_UnicastWorker::execute() run worker thread : [0/127.0.0.1]  
SOCKET_UnicastWorker::execute() worker send failed [127.0.0.1] : [-1] [32] Broken pipe  
SOCKET_UnicastWorker::execute() close socket [4]  
main::network_server_event_handler() disconnect audio client : [0] 127.0.0.1  
main::network_server_event_handler() max[20], accrue[1], current[0]  
SOCKET_UnicastWorker::execute() stop worker thread  
  
^Cmain::signal_event_handler() event : [2] Interrupt  
main::stop_server_all() stop : [unicast]  
SOCKET_UnicastServer::stop() join & wait excute thread term  
SOCKET_UnicastServer::execute() stop unicast thread  
SOCKET_UnicastServer::stop() mutex_worker_func lock()  
SOCKET_UnicastServer::stop() join & wait worker thread term : [127.0.0.1]  
SOCKET_UnicastWorker::stop() join & wait worker thread term  
SOCKET_UnicastWorker::stop() worker thread delete  
SOCKET_UnicastServer::stop() mutex_worker_func unlock()  
SOCKET_UnicastServer::stop() unicast socket closed  
main::main() process has been terminated.  
SOCKET_UnicastServer::SOCKET_UnicastServer() instance destructed  
SOCKET_UnicastServer::SOCKET_UnicastServer() mutex_worker_func lock()  
SOCKET_UnicastServer::SOCKET_UnicastServer() mutex_worker_func unlock()  
SOCKET_UnicastWorker::SOCKET_UnicastWorker() instance destructed  
  
# 실행 종료 방법  
CTRL + C (SIGINT)  