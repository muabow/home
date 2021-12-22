#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/socket.h>
#include <arpa/inet.h>
#include <netinet/in.h>
#include <netinet/tcp.h>
#include <pthread.h>
#include <errno.h>
#include <unistd.h>
#include <sys/select.h>
#include <sys/time.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <resolv.h>
#include <sys/types.h>
#include <inttypes.h>
#include <stdarg.h>
#include <sys/ioctl.h>

#include <iostream>
#include <thread>

#include "api_websocket.h"


WebsocketHandler::WebsocketHandler(void) {
	this->print_debug_info("WebsocketHandler() constructor\n");

	this->func_event_handler = NULL;
	this->func_adv_event_handler = NULL;
	
	this->ws_route_to        = WS_ROUTE_TO::EACH_OTHER;		// default: each other

	this->is_debug_print	= false;
	this->is_route_wscif    = false;
	this->is_p_run          = false;
	this->is_p_term			= true;

	return ;
}

WebsocketHandler::~WebsocketHandler(void) {
	this->term();

	return ;
}


// private methods
void WebsocketHandler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;

	if( this->uri.compare("") != 0 ) {
		fprintf(stdout, "WebsocketHandler[%s]::", this->uri.c_str());

	} else {
		fprintf(stdout, "WebsocketHandler::");
	}
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);

	return ;
}



bool WebsocketHandler::init_connect(void) {
	struct sockaddr_in  tServerAddr;

	if( (this->socket_fd = socket(AF_INET, SOCK_STREAM, 0)) < 0 ) {
		this->print_debug_info("init_connect() socket() failed : [%02d] %s\n", errno, strerror(errno));

		return false;
	}

	memset(&tServerAddr, 0x00, sizeof(tServerAddr));
	tServerAddr.sin_family      = AF_INET;
	tServerAddr.sin_port        = htons(this->port);
	tServerAddr.sin_addr.s_addr = inet_addr(this->ip_addr);

	int optval = 1;
	if( setsockopt(this->socket_fd, SOL_SOCKET, SO_REUSEADDR, &optval, sizeof(optval)) < 0 ) {
		this->print_debug_info("init_connect() SO_NODELAY failed : [%02d] %s\n", errno, strerror(errno));

		return false;
	}

	int 	flag_fcntl, flag_nonblock;
	int 	err_connect, rc;
	int     size_recv_buffer;
	bool	is_connected;

	fd_set  fdset, fdwset;
	struct  timeval timeout;

	socklen_t len = sizeof(size_recv_buffer);

	flag_fcntl    = fcntl(this->socket_fd, F_GETFL, 0);
	flag_nonblock = flag_fcntl | O_NONBLOCK;

	struct  linger  tLinger;
	tLinger.l_onoff  = true;
	tLinger.l_linger = 0;

	if( setsockopt(this->socket_fd, SOL_SOCKET, SO_LINGER, (char *)&tLinger, sizeof(tLinger)) < 0 ) {
		this->print_debug_info("init_connect() SO_LINGER failed : [%02d] %s\n", errno, strerror(errno));

		return false;
	}

	if( (rc = fcntl(this->socket_fd, F_SETFL, flag_nonblock)) < 0 ) {
		this->print_debug_info("init_connect() fcntl() set socket nonblock failed : [%02d] %s\n", errno, strerror(errno));
	}

	FD_ZERO(&fdset);
	FD_SET(this->socket_fd, &fdset);
	fdwset = fdset;

	timeout.tv_sec  = WebsocketHandler::NUM_SOCKET_TIMEOUT_SEC;
	timeout.tv_usec = WebsocketHandler::NUM_SOCKET_TIMEOUT_MSEC;

	connect(this->socket_fd, (struct sockaddr*)&tServerAddr, sizeof(tServerAddr));

	if( (rc = select(this->socket_fd + 1, &fdset, &fdwset, NULL, &timeout)) < 0 ) {
		switch( errno ) {
			case 4 :
				break;

			default :
				this->print_debug_info("initsocket() select() failed : [%02d] %s\n", errno, strerror(errno));
				break;
		}
	}

	getsockopt(this->socket_fd, SOL_SOCKET, SO_ERROR, (void*) &err_connect, &len);

	if ( (FD_ISSET(this->socket_fd, &fdwset ) > 0 ) && (err_connect == 0) ) {
		this->print_debug_info("init_connect() connect success\n");
		is_connected = true;

	} else {
		this->print_debug_info("init_connect() connect failed : %s\n", this->ip_addr);
		is_connected = false;
	}

	if( (rc = fcntl(this->socket_fd, F_SETFL, flag_fcntl)) < 0 ) {
		this->print_debug_info("init_connect() unset socket nonblock failed : [%02d] %s\n", errno, strerror(errno));
	}

	if( !is_connected ) {
		close(this->socket_fd);
		return false;
	}

	if( !this->init_socket_option() ) {
		this->print_debug_info("init_connect() setSockopt failed\n");

		return false;
	}

	string str_regist = "uri_regist";
	if( this->is_route_wscif ) {
		str_regist = "wscif_regist";
	}

	if( !this->send(0, str_regist) ) {
		this->print_debug_info("init_connect() URI registration failed\n");
		return false;
	}

	this->is_p_term = false;

	if( this->thread_func.joinable() ) {
		this->thread_func.join();
	}
	
	this->thread_func = thread(&WebsocketHandler::execute, this);
	// this->thread_func.detach();

	this->print_debug_info("init_connect() init success, URI : [%s] \n", this->uri.c_str());

	return true;
}

bool WebsocketHandler::init_socket_option(void) {
	int	 optval  = true;

	struct timeval tTimeo = {WebsocketHandler::NUM_SOCKET_TIMEOUT_SEC, WebsocketHandler::NUM_SOCKET_TIMEOUT_MSEC};

	if( setsockopt(this->socket_fd, IPPROTO_TCP, TCP_NODELAY, &optval, sizeof(optval)) < 0 ) {
		this->print_debug_info("init_socket_option() SO_NODELAY failed : [%02d] %s\n", errno, strerror(errno));

		return false;
	}

	if( setsockopt(this->socket_fd, SOL_SOCKET, SO_RCVTIMEO, &tTimeo, sizeof(tTimeo) ) < 0 ) {
		this->print_debug_info("init_socket_option() SO_RCVTIMEO failed : [%02d] %s\n", errno, strerror(errno));

		return false;
	}

	if( setsockopt(this->socket_fd, SOL_SOCKET, SO_SNDTIMEO, &tTimeo, sizeof(tTimeo) ) < 0 ) {
			this->print_debug_info("init_socket_option() SO_SNDTIMEO failed : [%02d] %s\n", errno, strerror(errno));

			return false;
		}

	return true;
}

void WebsocketHandler::execute(void) {
	this->mutex_execute.lock();
	this->is_p_run = true;

	int	rc;
	struct	timeval	timeout;
	fd_set  fdReads;

	WS_DATA_t  t_ws_data;
	
	while( !this->is_p_term ) {
		FD_ZERO(&fdReads);
		FD_SET(this->socket_fd, &fdReads);

		timeout.tv_sec  = 1;
		timeout.tv_usec = 0;

		if( (rc = select(this->socket_fd + 1, &fdReads, NULL, NULL, &timeout)) < 0 ) {
			this->print_debug_info("execute() select failed : [%02d] %s\n", errno, strerror(errno));
			break;

		} else if ( rc == 0 ) {
			// timeout

		} else {
				memset(&t_ws_data, 0x00, sizeof(t_ws_data));
				if( (rc = recv(this->socket_fd, &t_ws_data, sizeof(t_ws_data) - sizeof(t_ws_data.data), MSG_WAITALL)) < 0 ) {
					this->print_debug_info("execute() recv header failed : [%02d] %s\n", errno, strerror(errno));
					break;

				} else if( rc == 0 ) {
					this->print_debug_info("execute() connection disconnected\n");
					break;
				}

				// data valid check
				if(    !(t_ws_data.route_case >= 0 && t_ws_data.route_case <= 3)
					|| !(t_ws_data.is_binary  >= 0 && t_ws_data.is_binary  <= 1)
					|| !(t_ws_data.is_extend  >= 0 && t_ws_data.is_extend  <= 1) ) {
					this->print_debug_info("execute() recv invalid header data, connection disconnected\n");
					break;
				}

				if( t_ws_data.length > 0 ) {
					t_ws_data.data = new char[t_ws_data.length + 1];
					memset(t_ws_data.data, 0x00, t_ws_data.length + 1);

					if( (rc = recv(this->socket_fd, t_ws_data.data, t_ws_data.length, MSG_WAITALL)) < 0 ) {
						this->print_debug_info("execute() recv body failed : [%02d] %s\n", errno, strerror(errno));

						delete [] t_ws_data.data;

						break;

					} else if( rc == 0 ) {
						this->print_debug_info("execute() connection disconnected\n");

						delete [] t_ws_data.data;

						break;
					}
				}


				if( this->func_event_handler != NULL ) {
					this->func_event_handler(t_ws_data.cmd_id, t_ws_data.is_binary, t_ws_data.length, t_ws_data.data, this);
				}
				if( this->func_adv_event_handler != NULL ) {
					this->func_adv_event_handler(t_ws_data.cmd_id, t_ws_data.is_binary, t_ws_data.length, t_ws_data.data, this, this->ptr_void);
				}


				delete [] t_ws_data.data;
		}
	}

	this->print_debug_info("execute() thread termed : [%s/%s]\n", this->ip_addr, this->uri.c_str());

	if( this->socket_fd != -1 ) {
		close(this->socket_fd);
		this->socket_fd = -1;
	}

	this->is_p_term = true;
	this->is_p_run  = false;

	this->mutex_execute.unlock();

	return ;
}



// public methods
bool WebsocketHandler::init(string _ip_addr, string _uri, const bool _is_debug_print) {
	if( _is_debug_print ) {
		this->set_debug_print();
	}

	if( this->is_p_run ) {
		this->print_debug_info("init() already thread running\n");
		this->is_p_term = false;

		return false;
	}

	this->socket_fd = -1;

	this->is_p_term = false;

	this->uri = _uri;
	strcpy(this->ip_addr, _ip_addr.c_str());

	this->port = 80;
	if( _ip_addr.compare("127.0.0.1") == 0 ) {
		this->port = WebsocketHandler::DFLT_WSIF_SOCKET_PORT;
	}

	this->print_debug_info("init() IP address : [%s] \n", this->ip_addr);
	this->print_debug_info("init() port       : [%d] \n", this->port);

	if( !this->init_connect() ) {
		this->print_debug_info("init() init failed, URI : [%s] \n", this->uri.c_str());

		this->is_p_term = true;

		return false;
	}

	return true;
}

bool WebsocketHandler::reconnect(void) {
	if( this->is_p_run ) {
		this->print_debug_info("reconnect() already thread running\n");
		this->is_p_term = false;

		return false;
	}

	this->socket_fd = -1;

	this->is_p_term = false;

	this->print_debug_info("reconnect() IP address : [%s] \n", this->ip_addr);
	this->print_debug_info("reconnect() port       : [%d] \n", this->port);

	if( !this->init_connect() ) {
		this->print_debug_info("reconnect() reconnect failed, URI : [%s] \n", this->uri.c_str());

		this->is_p_term = true;

		return false;
	}

	return true;
}

void WebsocketHandler::term(void) {
	this->is_p_term = true;

	if( this->socket_fd != -1 ) {
		close(this->socket_fd);
		this->socket_fd = -1;
	}

	if( this->thread_func.joinable() ) {
		this->print_debug_info("term() wait execute thread term\n");
		this->thread_func.join();
	}
	this->print_debug_info("term() termed\n");

	return ;
}



bool WebsocketHandler::is_run(void) {

	return this->is_p_run;
}

bool WebsocketHandler::is_term(void) {

	return this->is_p_term;
}



bool WebsocketHandler::send(const int _cmd_id, const string _data) {
	if( this->is_p_term ) {
		this->print_debug_info("send() [%s/%s] send failed, session termed\n", this->ip_addr, this->uri.c_str());

		return false;
	}

	int  size_extend_header = sizeof(WS_EXT_DATA_t) - sizeof(char *);
	int  size_data_header   = sizeof(WS_DATA_t) - sizeof(char *);
	int	 length_regist = 0, length_data = 0;
	int  offset = 0;
	int  rc = 0;
	bool is_success = true;
	bool is_regist  = false;

	if( _data.compare("uri_regist") == 0 || _data.compare("wscif_regist") == 0 ) {
		is_regist = true;
	}
	WS_EXT_DATA_t t_extend_regist, t_extend_data;
	WS_DATA_t     t_ws_data;

	// send to ws interface : set header format
	t_ws_data.cmd_id     = _cmd_id;
	t_ws_data.route_case = this->ws_route_to;
	t_ws_data.is_binary  = false;
	t_ws_data.is_extend  = true;
	t_ws_data.length     = 0;

	if( is_regist ) {
		t_extend_regist.tag      = 0x00; // uri
		t_extend_regist.length   = this->uri.length();
		t_extend_regist.value    = new char[t_extend_regist.length + 1];

		memset(t_extend_regist.value, 0x00, t_extend_regist.length + 1);
		strcpy(t_extend_regist.value, this->uri.c_str());

		length_regist = size_extend_header + t_extend_regist.length;
	}

	if( is_regist ) {
		t_extend_data.tag    = 0x01; // regist
		t_extend_data.length = 0;

		if( _data.compare("wscif_regist") == 0 ) {
			t_extend_data.tag    = 0x02; // wsc_regist
		}

	} else {
		t_extend_data.tag    = 0x10; // data
		t_extend_data.length = _data.length();
	}

	if( t_extend_data.length > 0 ) {
		t_extend_data.value = new char[t_extend_data.length + 1];

		memset(t_extend_data.value, 0x00, t_extend_data.length + 1);
		strcpy(t_extend_data.value, _data.c_str());

	} else {
		t_extend_data.value = NULL;
	}
	length_data = size_extend_header + t_extend_data.length;

	t_ws_data.length = length_regist + length_data;
	t_ws_data.data   = new char[t_ws_data.length];
	memset(t_ws_data.data, 0x00, t_ws_data.length);

	if( is_regist ) {
		memcpy(t_ws_data.data + offset, &t_extend_regist, size_extend_header);
		offset += size_extend_header;

		memcpy(t_ws_data.data + offset, t_extend_regist.value, t_extend_regist.length);
		offset += t_extend_regist.length;
	}
	memcpy(t_ws_data.data + offset, &t_extend_data, size_extend_header);
	offset += size_extend_header;

	if( t_extend_data.length > 0 ) {
		memcpy(t_ws_data.data + offset, t_extend_data.value, t_extend_data.length);
		offset += t_extend_data.length;
	}

	try {
		// send to ws interface : process type
		int	ps_type = WebsocketHandler::NUM_TYPE_PROCESS_PORT;

		if( ::send(this->socket_fd, &ps_type, sizeof(ps_type), 0) < 0 ) {
			this->print_debug_info("send() send process type failed : [%02d] %s\n", errno, strerror(errno));
			throw "error";
		}

		// send to ws interface : data
		if( (rc = ::send(this->socket_fd, &t_ws_data, size_data_header, 0)) < 0 ) {
			this->print_debug_info("\033[36msend() send header data failed : [%02d] %s\033[00m\n", errno, strerror(errno));
			this->print_debug_info("\033[36msend() [%d/%d]\033[00m\n", rc, t_ws_data.length);
			throw "error";
		}

		if( (rc = ::send(this->socket_fd, t_ws_data.data, t_ws_data.length, 0)) < 0 ) {
			this->print_debug_info("\033[36msend() send body data failed : [%02d] %s\033[00m\n", errno, strerror(errno));
			this->print_debug_info("\033[36msend() [%d/%d]\033[00m\n", rc, t_ws_data.length);
			throw "error";
		}

	} catch( ... ) {
		is_success = false;
	}

	if( is_regist ) {
		delete [] t_extend_regist.value;
	}

	if( t_extend_data.length > 0 && t_extend_data.value != NULL ) {
		delete [] t_extend_data.value;
	}

	delete [] t_ws_data.data;

	t_ws_data.data = NULL;

	return is_success;
}

bool WebsocketHandler::send(const int _cmd_id, const void *_data, const int _length) {
	if( this->is_p_term ) {
		this->print_debug_info("send() [%s/%s] send failed, session termed\n", this->ip_addr, this->uri.c_str());

		return false;
	}

	int  size_extend_header = sizeof(WS_EXT_DATA_t) - sizeof(char *);
	int  size_data_header   = sizeof(WS_DATA_t)     - sizeof(char *);
	int	 length_data = 0;
	int  offset = 0;
	int  rc = 0;
	bool is_success = true;

	WS_EXT_DATA_t t_extend_data;
	WS_DATA_t     t_ws_data;

	// send to ws interface : set header format
	t_ws_data.cmd_id     = _cmd_id;
	t_ws_data.route_case = this->ws_route_to;
	t_ws_data.is_binary  = true;
	t_ws_data.is_extend  = true;
	t_ws_data.length     = 0;

	t_extend_data.tag    = 0x10; // data
	t_extend_data.length = _length;

	if( t_extend_data.length > 0 ) {
		t_extend_data.value = new char[t_extend_data.length + 1];
		memcpy(t_extend_data.value, _data, _length);
	}

	length_data = size_extend_header + t_extend_data.length;

	t_ws_data.length = length_data;
	t_ws_data.data   = new char[t_ws_data.length + 1];

	memcpy(t_ws_data.data + offset, &t_extend_data, size_extend_header);
	offset += size_extend_header;

	memcpy(t_ws_data.data + offset, t_extend_data.value, t_extend_data.length);
	offset += t_extend_data.length;

	try {
		// send to ws interface : process type
		int	ps_type = NUM_TYPE_PROCESS_PORT;

		if( ::send(this->socket_fd, &ps_type, sizeof(ps_type), 0) < 0 ) {
			this->print_debug_info("send() send process type failed : [%02d] %s\n", errno, strerror(errno));
			throw "error";
		}

		// send to ws interface : data
		if( (rc = ::send(this->socket_fd, &t_ws_data, size_data_header, 0)) < 0 ) {
			this->print_debug_info("\033[36msend() send data header failed : [%02d] %s\033[00m\n", errno, strerror(errno));
			this->print_debug_info("\033[36msend() [%d/%d]\033[00m\n", rc, t_ws_data.length);
			throw "error";
		}

		if( (rc = ::send(this->socket_fd, t_ws_data.data, t_ws_data.length, 0)) < 0 ) {
			this->print_debug_info("\033[36msend() send data body failed : [%02d] %s\033[00m\n", errno, strerror(errno));
			this->print_debug_info("\033[36msend() [%d/%d]\033[00m\n", rc, t_ws_data.length);
			throw "error";
		}

	} catch( ... ) {
		is_success = false;
	}

	if( t_extend_data.length > 0 ) {
		delete [] t_extend_data.value;
		t_extend_data.value = NULL;
	}

	delete [] t_ws_data.data;
	t_ws_data.data = NULL;

	return is_success;
}


// public: setter
void WebsocketHandler::set_event_handler(type_event_handler _func_ptr) {
	this->print_debug_info("set_event_handler() set event function\n");
	this->func_event_handler = _func_ptr;
	this->func_adv_event_handler = NULL;
	return ;
}

void WebsocketHandler::set_event_handler(type_adv_event_handler _func_ptr, void *_ptr_void /* = NULL */) {
	this->print_debug_info("set_event_handler() set event function\n");
	this->func_event_handler = NULL;
	this->func_adv_event_handler = _func_ptr;
	this->ptr_void = _ptr_void;

	return ;
}


void WebsocketHandler::set_target_info(string _ip_addr, string _uri) {
	strcpy(this->ip_addr, _ip_addr.c_str());
	this->uri = _uri;

	this->print_debug_info("set_target_info() IP address : [%s] \n", this->ip_addr);
	this->print_debug_info("set_target_info() port       : [%d] \n", this->port);

	return ;
}

void WebsocketHandler::set_route_to(const int _type) {
	this->ws_route_to = _type;

	this->print_debug_info("set_route_to() set route [%s]\n", this->route_case[_type].c_str());

	return ;
}

void WebsocketHandler::set_route_wscif(void) {
	this->is_route_wscif = true;

	this->print_debug_info("set_route_wscif() set route wscif\n");

	return ;
}

void WebsocketHandler::set_debug_print(void) {
	this->is_debug_print = true;

	this->print_debug_info("set_debug_print() set debug print\n");

	return ;
}


// public: getter
string WebsocketHandler::get_uri_name(void) {

	return this->uri;
}

string WebsocketHandler::get_ip_addr(void) {

	return string(this->ip_addr);
}

string WebsocketHandler::get_route_name(const int _type) {
	int max_size = sizeof(this->route_case) / sizeof(this->route_case[0]);

	if( _type < 0 || _type >= max_size ) {
		return "";
	}

	return this->route_case[_type];
}
