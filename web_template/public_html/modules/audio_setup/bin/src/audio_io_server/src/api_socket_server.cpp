#include "api_socket_server.h"

//
// ## Unicast worker class
//
SOCKET_UnicastWorker::SOCKET_UnicastWorker(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	this->print_debug_info("SOCKET_UnicastWorker() create instance\n");

	this->is_run			= false;
	this->is_loop			= false;

	this->index				= -1;
	this->socket_fd			= -1;
	this->num_reconnect		= 0;
	
	this->time_connect		= 0;
	this->time_disconnect	= 0;
	
	this->frame_latency		= -1;
	
	this->p_thread_func 	= NULL;

	this->event_handle		= NULL;
	
	this->li_queue_data		= queue<tuple<char *, int>>();
	
	return ;
}

SOCKET_UnicastWorker::~SOCKET_UnicastWorker(void) {
	this->print_debug_info("SOCKET_UnicastWorker() instance destructed\n");
	
	this->is_loop = true;
	if( this->p_thread_func != NULL ) {
		if( this->p_thread_func->joinable() ) {
			this->p_thread_func->join();
		}
	
		delete this->p_thread_func;
	}
	this->p_thread_func = NULL;
	
	return ;
}
		
void SOCKET_UnicastWorker::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	printf("\033[31m");
	fprintf(stdout, "SOCKET_UnicastWorker::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	printf("\033[0m");
	
	return ;
}

void SOCKET_UnicastWorker::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

void SOCKET_UnicastWorker::set_play_info(int _sample_rate, int _channels, int _chunk_size, int _encode_mode, int _encode_quality) {
	this->t_play_info.rate				= _sample_rate;
	this->t_play_info.channels			= _channels;
	this->t_play_info.chunk_size 		= _chunk_size;
	this->t_play_info.encode_mode		= _encode_mode;
	this->t_play_info.idx				= 0;
	this->t_play_info.pcm_buffer_size	= 0;
	this->t_play_info.pcm_period_size	= 0;
	
	this->t_encode_info.chunk_size		= this->t_play_info.chunk_size;
	this->t_encode_info.sample_rate		= this->t_play_info.rate;
	this->t_encode_info.quality 		= _encode_quality;
	
	int encode_bit_rate = 0;
	switch( this->t_encode_info.quality ) {
		case 2:  encode_bit_rate  = 192; break;
		case 5:  encode_bit_rate  = 128; break;
		case 7:  encode_bit_rate  = 64;  break;
		default: encode_bit_rate  = 128; break;
	}
	
	this->print_debug_info("set_play_info() sample rate : [%d]\n", this->t_play_info.rate);
	this->print_debug_info("set_play_info() channels    : [%d]\n", this->t_play_info.channels);
	this->print_debug_info("set_play_info() chunk size  : [%d]\n", this->t_play_info.chunk_size);
	this->print_debug_info("set_play_info() encode mode : [%s]\n", this->t_play_info.encode_mode == 0 ? "pcm" : "mp3");
	this->print_debug_info("set_play_info() mp3 quality : [%d/%d kbps]\n", this->t_encode_info.quality, encode_bit_rate);
	
	return ;
}


void SOCKET_UnicastWorker::set_hostname(string _hostname) {
	if( this->hostname.compare(_hostname) != 0 ) {
		this->print_debug_info("set_hostname() hostname : [%s] -> [%s]\n", this->hostname.c_str(), _hostname.c_str());
		this->hostname = _hostname;
	}
	
	return ;
}

void SOCKET_UnicastWorker::set_index(int _index) {
	this->print_debug_info("set_index() worker instance index set [%d]\n", _index);
	this->index = _index;
	
	return ;
}

bool SOCKET_UnicastWorker::is_reconnect(string _ip_addr, string _mac_addr) {
	bool is_reconnect = false;
	this->print_debug_info("is_reconnect() check ip_addr: [%s/%s], mac_addr: [%s/%s]\n", 
		this->ip_addr.c_str(), _ip_addr.c_str(), this->mac_addr.c_str(), _mac_addr.c_str());

	if( this->ip_addr.compare(_ip_addr) == 0 && this->mac_addr.compare(_mac_addr) == 0 ) {
		is_reconnect = true;
	}

	return is_reconnect;
}

bool SOCKET_UnicastWorker::is_alive(void) {
	
	return this->is_run;
}

void SOCKET_UnicastWorker::set_frame_latency(double _frame_latency) {
	if( this->frame_latency != _frame_latency ) {
		this->print_debug_info("set_frame_latency() set frame latency : [%lf]\n", _frame_latency);
		this->frame_latency = _frame_latency;
	}
	
	return ;
}

double SOCKET_UnicastWorker::get_frame_latency(void) {
	
	return this->frame_latency;
}

string SOCKET_UnicastWorker::get_ip_addr(void) {

	return this->ip_addr;
}

string SOCKET_UnicastWorker::get_mac_addr(void) {

	return this->mac_addr;
}

string SOCKET_UnicastWorker::get_hostname(void) {

	return this->hostname;
}

int	SOCKET_UnicastWorker::get_num_reconnect(void) {
	
	return this->num_reconnect;
}

int SOCKET_UnicastWorker::get_timestamp(void) {
	int now_time = time(NULL);

	return now_time;
}

int SOCKET_UnicastWorker::get_time_connect(void) {
	
	return this->time_connect;
}

int SOCKET_UnicastWorker::get_time_disconnect(void) {
	
	return this->time_disconnect;
}

void SOCKET_UnicastWorker::init(string _ip_addr, string _mac_addr, string _hostname, int _socket_fd) {
	this->ip_addr   = _ip_addr;
	this->mac_addr  = _mac_addr;
	this->hostname  = _hostname;
	this->socket_fd	= _socket_fd;
	
	this->num_reconnect = 0;
	
	this->p_thread_func = NULL;
	
	this->print_debug_info("init() [%s/%s] worker ready\n", this->mac_addr.c_str(), this->ip_addr.c_str());
	
	return ;
}

void SOCKET_UnicastWorker::stop(void) {
	if( this->p_thread_func != NULL ) {
		this->is_loop = true;
		
		if( this->p_thread_func->joinable() ) {
			this->print_debug_info("stop() join & wait worker thread term\n");
			this->p_thread_func->join();
		}
		
		this->print_debug_info("stop() worker thread delete\n");
		delete this->p_thread_func;
		this->p_thread_func = NULL;
	
	} 
	this->is_run = false;

	return ;
}

void SOCKET_UnicastWorker::run(void) {
	if( this->is_run ) {
		this->print_debug_info("run() already running\n");
		return ;
	}
	this->is_run  = true;
	this->is_loop = false;

	this->time_connect = this->get_timestamp();
	this->event_handle(this->index, true);
	
	this->print_debug_info("run() create worker thread\n");
	this->p_thread_func = new thread(&SOCKET_UnicastWorker::execute, this);
	
	return ;
}

void SOCKET_UnicastWorker::reconnect(int _socket_fd) {
	this->stop();

	this->is_run  = true;
	this->is_loop = false;
	this->socket_fd = _socket_fd;

	this->time_connect = this->get_timestamp();
	this->num_reconnect++;
	this->event_handle(this->index, true);
	
	this->print_debug_info("reconnect() worker reconnect [%d] : [%s/%s]\n", this->num_reconnect, this->mac_addr.c_str(), this->ip_addr.c_str());
		
	this->p_thread_func = new thread(&SOCKET_UnicastWorker::execute, this);
	
	return ;
}

void SOCKET_UnicastWorker::set_event_handler(void (*_func)(int, bool)) {
	this->print_debug_info("set_event_handler() set function\n");
	this->event_handle = _func;
	
	return ;
}

void SOCKET_UnicastWorker::set_queue_data(char *_data, int _size) {
	if( this->is_run ) {
		this->li_queue_data.push(make_tuple(_data, _size));
	}
	
	return ;
}
	
void SOCKET_UnicastWorker::execute(void) {
	string str_hostname = this->hostname;
	str_hostname.erase(0, 9);

	this->print_debug_info("execute() run worker thread [%d/%s] - [%s/%s]\n", 
			this->index, this->ip_addr.c_str(), this->mac_addr.c_str(), str_hostname.c_str());
	
	tuple<char *, int> tp_data;
	char  *data_ptr = NULL;
	int  data_length;
	int  rc;

	this->li_queue_data	= queue<tuple<char *, int>>();
	
	while( !this->is_loop ) {
		if( this->li_queue_data.empty() ) {
			usleep(1000);
			continue;
		}

		tp_data = this->li_queue_data.front();

		data_ptr    = get<0>(tp_data);
		data_length = get<1>(tp_data);
		
		if( (rc = send(this->socket_fd, data_ptr, data_length, MSG_DONTWAIT)) < 0 ) {
			this->print_debug_info("execute() worker send failed [%s/%s] : [%d] [%02d] %s\n",
					this->mac_addr.c_str(), this->ip_addr.c_str(), rc, errno, strerror(errno));
			break;
		}

		this->li_queue_data.pop();
	}
	
	if( this->socket_fd != -1 ) {
		this->print_debug_info("execute() close socket [%d]\n", this->socket_fd);

		close(this->socket_fd);
		this->socket_fd = -1;
	}
	
	this->is_run = false;
	this->time_disconnect = this->get_timestamp();
	this->event_handle(this->index, false);

	this->is_loop = true;
	
	this->print_debug_info("execute() stop worker thread\n");
	
	return ;
}

//
// ## Unicast server class
//
SOCKET_UnicastServer::SOCKET_UnicastServer(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	this->print_debug_info("SOCKET_UnicastServer() create instance\n");

	this->is_init				= false;
	this->is_run				= false;
	this->is_loop				= false;

	this->port					= DFLT_SERV_PORT;
	this->socket_fd				= -1;
	
	this->num_max_client		= DFLT_NUM_MAX_CLIENT;
	this->num_current_client	= 0;
	this->num_accrue_client		= 0;
	
	this->event_handle			= NULL;
	
	this->frame_latency			= -1;
	
	this->v_worker_func.clear();
	
	return ;
}

SOCKET_UnicastServer::~SOCKET_UnicastServer(void) {
	this->print_debug_info("SOCKET_UnicastServer() instance destructed\n");
	
	this->is_loop = true;
	if( this->thread_func.joinable() ) {
		this->thread_func.join();
	}

	this->print_debug_info("SOCKET_UnicastServer() mutex_worker_func lock()\n");
	this->mutex_worker_func.lock();
	
	int num_worker_thread = (int)this->v_worker_func.size();
	for( int idx = 0 ; idx < num_worker_thread ; idx++ ) {
		this->v_worker_func[idx].get()->stop();
	}
	
	this->print_debug_info("SOCKET_UnicastServer() mutex_worker_func unlock()\n");
	this->mutex_worker_func.unlock();
	
	return ;
}
		
void SOCKET_UnicastServer::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	printf("\033[31m");
	fprintf(stdout, "SOCKET_UnicastServer::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	printf("\033[0m");
	
	return ;
}

void SOCKET_UnicastServer::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

void SOCKET_UnicastServer::set_server_port(int _port) {
	if( this->port == _port ) return ;
	
	this->print_debug_info("set_server_port() change to [%d] -> [%d]\n", this->port, _port);
	this->port = _port;
	
	return ;
}

bool SOCKET_UnicastServer::is_alive_status(void) {
	
	return this->is_run;
}

void SOCKET_UnicastServer::set_num_max_client(int _count) {
	if( this->num_max_client == _count ) return ;
		
	this->print_debug_info("set_num_max_client() change to [%d] -> [%d]\n", this->num_max_client, _count);
	this->num_max_client = _count;

	return ;
}

void SOCKET_UnicastServer::set_socket_option(int _socket_fd) {
	int optval = 1; // enable
	if( setsockopt(_socket_fd, IPPROTO_TCP, TCP_NODELAY, &optval, sizeof (optval)) < 0 ) {
		this->print_debug_info("set_socket_option() setsockopt() SO_NODELAY failed : [%02d] %s\n", errno, strerror(errno));
	}

	/*
	int opt_value_cork = 0;
	if( setsockopt(_socket_fd, IPPROTO_TCP, TCP_CORK, &opt_value_cork, sizeof(opt_value_cork)) < 0 ) {
		this->print_debug_info("set_socket_option() setsockopt() TCP_CORK failed : [%02d] %s\n", errno, strerror(errno));
	}
	*/

	struct linger opt_linger;
	opt_linger.l_onoff  = 1; // enable
	opt_linger.l_linger = 0;
	if( setsockopt(_socket_fd, SOL_SOCKET, SO_LINGER, (char *)&opt_linger, sizeof(opt_linger)) < 0 ) {
		this->print_debug_info("set_socket_option() setsockopt() SO_LINGER failed : [%02d] %s\n", errno, strerror(errno));
	}

	struct timeval t_rcv_timeo = {TIME_RCV_TIMEOUT, 0};
	if( setsockopt(_socket_fd, SOL_SOCKET, SO_RCVTIMEO, &t_rcv_timeo, sizeof(t_rcv_timeo) ) < 0 ) {
		this->print_debug_info("set_socket_option() setsockopt() SO_RCVTIMEO failed : [%02d] %s\n", errno, strerror(errno));
	}
	
	return ;
}

void SOCKET_UnicastServer::set_play_info(int _sample_rate, int _channels, int _chunk_size, string _encode_mode, int _encode_quality) {
	int encode_bit_rate = 0;
	switch( _encode_quality ) {
		case 2:  encode_bit_rate  = 192; break;
		case 5:  encode_bit_rate  = 128; break;
		case 7:  encode_bit_rate  = 64;  break;
		default: encode_bit_rate  = 128; break;
	}
	
	this->print_debug_info("set_play_info() sample rate : [%d]\n", _sample_rate);
	this->print_debug_info("set_play_info() channels    : [%d]\n", _channels);
	this->print_debug_info("set_play_info() chunk size  : [%d]\n", _chunk_size);
	this->print_debug_info("set_play_info() encode mode : [%s]\n", _encode_mode.c_str());
	this->print_debug_info("set_play_info() mp3 quality : [%d/%d kbps]\n", _encode_quality, encode_bit_rate);
	
	this->t_play_info.rate 			= _sample_rate;
	this->t_play_info.channels		= _channels;
	this->t_play_info.chunk_size 	= _chunk_size;
	
	this->t_encode_info.chunk_size	= _chunk_size;
	this->t_encode_info.sample_rate	= _sample_rate;
	this->t_encode_info.quality		= _encode_quality;
	
	if( _encode_mode.compare("pcm") == 0 ) {
		this->t_play_info.encode_mode	= 0;
		
	} else {
		this->t_play_info.encode_mode	= 1;
	}
	this->t_play_info.idx 				= 0;
	this->t_play_info.pcm_buffer_size 	= 0;
	this->t_play_info.pcm_period_size 	= 0;
	
	return ;
}

void SOCKET_UnicastServer::set_frame_latency(double _frame_latency) {
	if( this->frame_latency != _frame_latency ) {
		this->print_debug_info("set_frame_latency() set frame latency : [%lf]\n", _frame_latency);
		this->frame_latency = _frame_latency;
	}
	
	return ;
}

string SOCKET_UnicastServer::get_client_list(void) {
	int 		num_client = (int)this->v_worker_func.size();
	string		str_hostname;
	string		str_client_list = "[";
	
	Document 	doc_data;
	StringBuffer data_buffer;
	Writer<StringBuffer> data_writer(data_buffer);
	
	string json_data;
	
	for( int idx = 0 ; idx < num_client ; idx++ ) {
		str_hostname = this->v_worker_func[idx].get()->get_hostname();
		str_hostname.erase(0, 9);
		
		data_writer.Reset(data_buffer); 
		Pointer("/ip_addr"			).Set(doc_data, this->v_worker_func[idx].get()->get_ip_addr().c_str());
		Pointer("/mac_addr"			).Set(doc_data, this->v_worker_func[idx].get()->get_mac_addr().c_str());
		Pointer("/hostname"			).Set(doc_data, str_hostname.c_str());
		Pointer("/is_alive"			).Set(doc_data, this->v_worker_func[idx].get()->is_alive());
		Pointer("/connect_time"		).Set(doc_data, this->v_worker_func[idx].get()->get_time_connect());
		Pointer("/disconnect_time"	).Set(doc_data, this->v_worker_func[idx].get()->get_time_disconnect());
		Pointer("/num_reconnect"	).Set(doc_data, this->v_worker_func[idx].get()->get_num_reconnect());
		doc_data.Accept(data_writer);
		
		json_data = data_buffer.GetString();
		str_client_list.append(json_data);
		if( idx + 1 < num_client ) {
			str_client_list.append(",");
		}
		data_buffer.Clear();
	}
	str_client_list.append("]");
	
	return str_client_list;
}

string SOCKET_UnicastServer::get_client_info(int _idx, string _type) {
	int idx = _idx;
	if( _type.compare("hostname") == 0 ) {
		string str_hostname = this->v_worker_func[idx].get()->get_hostname();
		str_hostname.erase(0, 9);
		
		return str_hostname;
		
	} else if( _type.compare("ip_addr") == 0 ) {
		string str_ip_addr = this->v_worker_func[idx].get()->get_ip_addr();
		
		return str_ip_addr;
	}
	
	return "";
}

int SOCKET_UnicastServer::get_play_info(string _type) {
	if( _type.compare("sample_rate") == 0 ) {
		return this->t_play_info.rate;
		
	} else if( _type.compare("channels") == 0 ) {
		return this->t_play_info.channels;
		
	} else if( _type.compare("chunk_size") == 0 ) {
		return this->t_play_info.chunk_size;
		
	} else if( _type.compare("encode_mode") == 0 ) {
		return this->t_play_info.encode_mode;		
	
	} else if( _type.compare("encode_quality") == 0 ) {
		return this->t_encode_info.quality;		
	}
	
	return -1;
}

double SOCKET_UnicastServer::get_frame_latency(void) {
	
	return this->frame_latency;
}

int SOCKET_UnicastServer::get_max_client_count(void) {
	return this->num_max_client;
}

int SOCKET_UnicastServer::get_current_count(void) {
	this->mutex_counter.lock();
	
	int current_count = this->num_current_client;
	
	this->mutex_counter.unlock();
	
	return current_count;
}

void SOCKET_UnicastServer::inc_current_count(void) {
	this->mutex_counter.lock();
	
	this->num_current_client++;
	
	this->mutex_counter.unlock();
	
	return ;
}

void SOCKET_UnicastServer::dec_current_count(void) {
	this->mutex_counter.lock();
	
	this->num_current_client--;
	
	this->mutex_counter.unlock();
	
	return ;
}

int SOCKET_UnicastServer::get_accrue_count(void) {
	this->mutex_counter.lock();
		
	int accrue_client = this->num_accrue_client;
	
	this->mutex_counter.unlock();
	
	return accrue_client;
}


void SOCKET_UnicastServer::inc_accrue_count(void) {
	this->mutex_counter.lock();
	
	this->num_accrue_client++;
	
	this->mutex_counter.unlock();
	
	return ;
}

void SOCKET_UnicastServer::reset_current_count(void) {
	this->mutex_counter.lock();
	
	this->num_current_client = 0;
	
	this->mutex_counter.unlock();
	
	return ;
}

void SOCKET_UnicastServer::reset_accrue_count(void) {
	this->mutex_counter.lock();
	
	this->num_accrue_client = 0;
	
	this->mutex_counter.unlock();
	
	return ;
}

bool SOCKET_UnicastServer::init(void) {
	if( this->is_init ) {
		this->print_debug_info("init() already init\n");
		return false;
	}
	
	if( this->is_run ) {
		this->print_debug_info("init() already running\n");
		return false;
	}
	
	this->is_init		 = false;
	this->is_run		 = false;
	this->is_loop		 = false;
	
	struct	sockaddr_in 	t_sock_addr;
	
	if( (this->socket_fd = socket(AF_INET, SOCK_STREAM, 0)) < 0 ) {
		this->print_debug_info("init() socket() failed : [%02d] %s\n", errno, strerror(errno));

		return false;
	}

	// TCP uni-cast //
	t_sock_addr.sin_family 		= AF_INET;
	t_sock_addr.sin_port 		= htons(this->port);
	t_sock_addr.sin_addr.s_addr = htonl(INADDR_ANY);

	int			option 	   = true;
	socklen_t 	socket_len = sizeof(option);
	
	if( setsockopt(this->socket_fd, SOL_SOCKET, SO_REUSEADDR, &option, socket_len) < 0 ) {
		this->print_debug_info("init() setsockopt() SO_REUSEADDR failed : [%02d] %s\n", errno, strerror(errno));

		return false; 
	}
	
	// BIND init
	if( bind(this->socket_fd, (struct sockaddr *)&t_sock_addr, sizeof(t_sock_addr)) < 0 ) {
		this->print_debug_info("init() bind() failed : [%02d]\n", errno);

		return false;
	}

	int       optval;
	socklen_t optlen = sizeof(optval);
	getsockopt(this->socket_fd, SOL_SOCKET, SO_SNDBUF, (char*)&optval, &optlen);

	optval *= SIZE_SCALE_SNDBUF;
	setsockopt(this->socket_fd, SOL_SOCKET, SO_SNDBUF, (char*)&optval, sizeof(optval));

	optval = 1; // enable
	if( setsockopt(this->socket_fd, IPPROTO_TCP, TCP_NODELAY, &optval, sizeof (optval)) < 0 ) {
		this->print_debug_info("set_socket_option() setsockopt() SO_NODELAY failed : [%02d] %s\n", errno, strerror(errno));
	}

	int opt_value_cork = 0;
	if( setsockopt(this->socket_fd, IPPROTO_TCP, TCP_CORK, &opt_value_cork, sizeof(opt_value_cork)) < 0 ) {
		this->print_debug_info("set_socket_option() setsockopt() TCP_CORK failed : [%02d] %s\n", errno, strerror(errno));
	}

	// LISTEN init
	if( listen(this->socket_fd, SIZE_BACK_LOG) < 0 ) {
		this->print_debug_info("init() listen() failed : [%02d] %s\n", errno, strerror(errno));

		return false;
	}
	
	this->print_debug_info("init() mutex_worker_func lock()\n");
	this->mutex_worker_func.lock();
	
	this->v_worker_func.clear();
	
	this->print_debug_info("init() mutex_worker_func unlock()\n");
	this->mutex_worker_func.unlock();
	
	this->print_debug_info("init() init unicast socket success \n");
	this->is_init = true;
	
	return true;
} 

void SOCKET_UnicastServer::stop(void) {
	if( !this->is_run ) {
		this->print_debug_info("stop() unicast is not running\n");
		
		return ;
	}
	this->is_loop = true;

	if( this->thread_func.joinable() ) {
		this->print_debug_info("stop() join & wait excute thread term\n");
		this->thread_func.join();
	}
	
	this->print_debug_info("stop() mutex_worker_func lock()\n");
	this->mutex_worker_func.lock();
	
	int num_worker_thread = (int)this->v_worker_func.size();
	for( int idx = 0 ; idx < num_worker_thread ; idx++ ) {
		this->print_debug_info("stop() join & wait worker thread term : [%s/%s]\n", 
				this->v_worker_func[idx].get()->get_mac_addr().c_str(), this->v_worker_func[idx].get()->get_ip_addr().c_str());
		
		this->v_worker_func[idx].get()->stop();
	}
	
	this->print_debug_info("stop() mutex_worker_func unlock()\n");
	this->mutex_worker_func.unlock();
	
	if( this->socket_fd != -1 ) {
		close(this->socket_fd);
		this->socket_fd = -1;
		this->print_debug_info("stop() unicast socket closed \n");
	}
	
	this->reset_current_count();
	this->reset_accrue_count();
	
	this->is_init = false;
	
	return ;
}

void SOCKET_UnicastServer::run(void) {
	if( !this->is_init ) {
		this->print_debug_info("run() init is not ready\n");
		
		return ;
	}
	
	if( this->is_run ) {
		this->print_debug_info("run() already running\n");
		return ;
	}
	this->is_run = true;
	
	this->print_debug_info("run() create execute thread\n");
	this->thread_func = thread(&SOCKET_UnicastServer::execute, this);
	
	return ;
}

void SOCKET_UnicastServer::set_event_handler(void (*_func)(int, bool)) {
	this->print_debug_info("set_event_handler() set function\n");
	this->event_handle = _func;
	
	return ;
}

SOCKET_UnicastServer::func_ptr SOCKET_UnicastServer::get_event_handler(void) {
	
	return this->event_handle;
}

void SOCKET_UnicastServer::send_data_handler(char *_data, int _size) {
	if( _data == NULL || _size == 0 ) {
		this->print_debug_info("send_data_handler() data size is 0, %x\n", _data);
		return ;
	}

	int num_worker_thread = (int)this->v_worker_func.size();
	
	for( int idx = 0 ; idx < num_worker_thread ; idx++ ) {
		this->v_worker_func[idx].get()->set_queue_data(_data, _size);
	}
	
	return ;
}

void SOCKET_UnicastServer::execute(void) {
	this->print_debug_info("execute() run unicast thread\n");
	
	// server socket variables
	int		rc;
	struct	timeval	timeout;
	fd_set  fd_reads;

	// client socket variables
	int			client_socket_fd;
	struct		sockaddr_in t_sock_addr;
	socklen_t	sock_len = sizeof(struct sockaddr_in);
	
	bool		is_reconnect;
	string		str_ip_addr;
	thread		worker_func;
	int			worker_idx;
	
	HOST_INFO_t	t_host_info;
	
	while( !this->is_loop ) {
		FD_ZERO(&fd_reads);
		FD_SET(this->socket_fd, &fd_reads);
			
		timeout.tv_sec  = TIME_ACCEPT_SEC;
		timeout.tv_usec = TIME_ACCEPT_MSEC;

		if( (rc = select(this->socket_fd + 1, &fd_reads, NULL, NULL, &timeout)) < 0 ) {
			switch( errno ) {
				default :
					this->print_debug_info("execute() select() failed : [%02d] %s\n", errno, strerror(errno));
					break;
			}
			continue;

		} else if ( rc == 0 ) {
			// this->print_debug_info("execute() client connect - current[%d] accrue[%d]\n", this->get_current_count(), this->get_accrue_count());
			
			continue;
		}

		if( FD_ISSET(this->socket_fd, &fd_reads) ) {
			if( (client_socket_fd = accept(this->socket_fd, (struct sockaddr *)&t_sock_addr, &sock_len)) < 0 ) {
				this->print_debug_info("execute() accept() failed : [%02d] %s\n", errno, strerror(errno));
				continue;
			}
			str_ip_addr = string(inet_ntoa(t_sock_addr.sin_addr));
			
			if( this->get_current_count() + 1 > this->num_max_client ) {
				this->print_debug_info("execute() number of client exceeded : [%d/%d]\n", this->get_current_count(), this->num_max_client);
				
				close(client_socket_fd);
				this->print_debug_info("execute() client disconnected : [%s]\n", str_ip_addr.c_str());
				
				sleep(TIME_ACCEPT_SEC);
				
			} else {
				this->set_socket_option(client_socket_fd);
				
				// 1. 헤더 정보 수신
				memset(&t_host_info, 0x00, sizeof(t_host_info));
				if( (rc = recv(client_socket_fd, &t_host_info, sizeof(t_host_info), 0)) <= 0 ) {
					this->print_debug_info("execute() recv() [host_info] failed [%s] : [%02d] %s\n", str_ip_addr.c_str(), errno, strerror(errno));

					close(client_socket_fd);
					continue;
				}
				if( strncmp(t_host_info.hostname, STR_MSG_HOSTNAME, strlen(STR_MSG_HOSTNAME)) != 0 ) {
					this->print_debug_info("execute() recv() [host_info] invalid header : %s\n", str_ip_addr.c_str());

					close(client_socket_fd);
					continue;

				}
				
				// 2. 플레이 정보 송신
				if( send(client_socket_fd, &this->t_play_info, sizeof(this->t_play_info), 0) < 0 ) {
					this->print_debug_info("execute() send() [play_info] failed : [%s/%s] [%02d] %s\n", t_host_info.mac_addr, str_ip_addr.c_str(), errno, strerror(errno));

					close(client_socket_fd);
					continue;
				}
				
				// mp3 mode 동작 시 추가 정보 전송
				if( this->t_play_info.encode_mode ) { 
					if( send(client_socket_fd, &this->t_encode_info, sizeof(this->t_encode_info), 0) < 0 ) {
						this->print_debug_info("execute() send() [encode_info] failed : [%s/%s] [%02d] %s\n", t_host_info.mac_addr, str_ip_addr.c_str(), errno, strerror(errno));

						close(client_socket_fd);
						continue;
					}
				}
				
				this->print_debug_info("execute() find the client reconnects.\n");
				is_reconnect = false;
				
				this->print_debug_info("execute() mutex_worker_func lock()\n");
				this->mutex_worker_func.lock();
				
				int num_worker_thread = (int)this->v_worker_func.size();
				this->print_debug_info("execute() num_worker_thread : [%d]\n", num_worker_thread);
				for( int idx = 0 ; idx < num_worker_thread ; idx++ ) {
					this->print_debug_info("execute() worker index : [%d] - [%s/%s]\n", 
						idx, this->v_worker_func[idx].get()->get_mac_addr().c_str(), this->v_worker_func[idx].get()->get_ip_addr().c_str());

					if( this->v_worker_func[idx].get()->is_reconnect(str_ip_addr, t_host_info.mac_addr) ) {
						this->v_worker_func[idx].get()->set_hostname(t_host_info.hostname);
						this->v_worker_func[idx].get()->reconnect(client_socket_fd);

						is_reconnect = true;
						break;
					}
				}
				this->print_debug_info("is_reconnect() check reconnect : [%s]\n", is_reconnect ? "true" : "false");
				
				if( is_reconnect ) {
					// this->print_debug_info("execute() client connect - current[%d] accrue[%d]\n", this->get_current_count(), this->get_accrue_count());
					this->print_debug_info("execute() mutex_worker_func unlock() - reconnect\n");
					this->mutex_worker_func.unlock();
					
					continue;
				}
				
				this->print_debug_info("execute() not found connected client. add new client.\n");
				this->inc_accrue_count();
				
				unique_ptr<SOCKET_UnicastWorker> work_handler(new SOCKET_UnicastWorker());
				this->v_worker_func.push_back(move(work_handler));

				worker_idx = (int)this->v_worker_func.size() - 1;
				
				if( this->is_debug_print ) this->v_worker_func[worker_idx].get()->set_debug_print();
				
				this->v_worker_func[worker_idx].get()->init(str_ip_addr, 
																t_host_info.mac_addr, 
																t_host_info.hostname, 
																client_socket_fd);
				this->v_worker_func[worker_idx].get()->set_index(worker_idx);
				this->v_worker_func[worker_idx].get()->set_play_info(this->get_play_info("sample_rate"), 
																		 this->get_play_info("channels"), 
																		 this->get_play_info("chunk_size"), 
																		 this->get_play_info("encode_mode"),
																		 this->get_play_info("encode_quality"));
				this->v_worker_func[worker_idx].get()->set_frame_latency(this->get_frame_latency());
				this->v_worker_func[worker_idx].get()->set_event_handler(this->get_event_handler());
				this->v_worker_func[worker_idx].get()->run();
				
				this->print_debug_info("execute() worker index[%d] add complete.\n", worker_idx);
				
				this->print_debug_info("execute() mutex_worker_func unlock()\n");
				this->mutex_worker_func.unlock();
			}
		}
	}
	
	this->print_debug_info("execute() stop unicast thread\n");
	this->is_run = false;
	
	return ;
}


//
// ## Multicast server class
//
SOCKET_MulticastServer::SOCKET_MulticastServer(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	this->print_debug_info("SOCKET_MulticastServer() create instance\n");

	this->is_init				= false;
	this->is_run				= false;
	this->is_loop				= false;

	this->socket_fd				= -1;
	this->ip_addr				= "";
	this->port					= DFLT_SERV_PORT;
	
	this->frame_latency			= -1;
	
	return ;
}

SOCKET_MulticastServer::~SOCKET_MulticastServer(void) {
	this->print_debug_info("SOCKET_MulticastServer() instance destructed\n");
	
	this->is_loop = true;
	if( this->thread_func.joinable() ) {
		this->thread_func.join();
	}
	
	return ;
}
		
void SOCKET_MulticastServer::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	printf("\033[33m");
	fprintf(stdout, "SOCKET_MulticastServer::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	printf("\033[0m");
	
	return ;
}

void SOCKET_MulticastServer::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

bool SOCKET_MulticastServer::is_alive_status(void) {
	
	return this->is_run;
}


void SOCKET_MulticastServer::set_server_port(int _port) {
	if( this->port == _port ) return ;
	
	this->print_debug_info("set_server_port() change to [%d] -> [%d]\n", this->port, _port);
	this->port = _port;
	
	return ;
}

void SOCKET_MulticastServer::set_ip_addr(string _ip_addr) {
	if( this->ip_addr.compare(_ip_addr) == 0 ) return ;
	
	this->print_debug_info("set_ip_addr() multicast IP address change to [%s] -> [%s]\n", this->ip_addr.c_str(), _ip_addr.c_str());
	this->ip_addr = _ip_addr;
	
	return ;
}

void SOCKET_MulticastServer::set_frame_latency(double _frame_latency) {
	if( this->frame_latency != _frame_latency ) {
		this->print_debug_info("set_frame_latency() set frame latency : [%lf]\n", _frame_latency);
		this->frame_latency = _frame_latency;
	}
	
	return ;
}

void SOCKET_MulticastServer::set_play_info(int _sample_rate, int _channels, int _chunk_size, string _encode_mode, int _encode_quality) {
	int encode_bit_rate = 0;
	switch( _encode_quality ) {
		case 2:  encode_bit_rate  = 192; break;
		case 5:  encode_bit_rate  = 128; break;
		case 7:  encode_bit_rate  = 64;  break;
		default: encode_bit_rate  = 128; break;
	}
	
	this->print_debug_info("set_play_info() sample rate : [%d]\n", _sample_rate);
	this->print_debug_info("set_play_info() channels    : [%d]\n", _channels);
	this->print_debug_info("set_play_info() chunk size  : [%d]\n", _chunk_size);
	this->print_debug_info("set_play_info() encode mode : [%s]\n", _encode_mode.c_str());
	this->print_debug_info("set_play_info() mp3 quality : [%d/%d kbps]\n", _encode_quality, encode_bit_rate);
	
	this->t_play_info.rate 			= _sample_rate;
	this->t_play_info.channels		= _channels;
	this->t_play_info.chunk_size 	= _chunk_size;
	
	this->t_encode_info.chunk_size	= _chunk_size;
	this->t_encode_info.sample_rate	= _sample_rate;
	this->t_encode_info.quality		= _encode_quality;
	
	if( _encode_mode.compare("pcm") == 0 ) {
		this->t_play_info.encode_mode = 0;

	} else {
		this->t_play_info.encode_mode	= 1;
	}
	this->t_play_info.idx 				= 0;
	this->t_play_info.pcm_buffer_size	= 0;
	this->t_play_info.pcm_period_size 	= 0;
	
	return ;
}

int SOCKET_MulticastServer::get_play_info(string _type) {
	if( _type.compare("sample_rate") == 0 ) {
		return this->t_play_info.rate;
		
	} else if( _type.compare("channels") == 0 ) {
		return this->t_play_info.channels;
		
	} else if( _type.compare("chunk_size") == 0 ) {
		return this->t_play_info.chunk_size;
		
	} else if( _type.compare("encode_mode") == 0 ) {
		return this->t_play_info.encode_mode;		
	
	} else if( _type.compare("encode_quality") == 0 ) {
		return this->t_encode_info.quality;		
	}

	return -1;
}

string SOCKET_MulticastServer::get_ip_addr(void) {
	
	return this->ip_addr;
}

bool SOCKET_MulticastServer::init(void) {
	if( this->is_init ) {
		this->print_debug_info("init() already init\n");
		return false;
	}
	
	if( this->is_run ) {
		this->print_debug_info("init() already running\n");
		return false;
	}
	
	this->is_init		 = false;
	this->is_run		 = false;
	this->is_loop		 = false;
	
	if( this->ip_addr.compare("") == 0 ) {
		this->print_debug_info("init() not ready multicast IP address info[%s]\n", this->ip_addr.c_str());
		return false;
	}
	
	if( (this->socket_fd = socket(AF_INET, SOCK_DGRAM, 0)) < 0 ) {
		this->print_debug_info("init() socket() failed : [%02d] %s\n", errno, strerror(errno));

		return false;
	}

	// UDP multi-cast //
	this->t_sock_addr.sin_family		= AF_INET;
	this->t_sock_addr.sin_addr.s_addr	= inet_addr(this->ip_addr.c_str());
	this->t_sock_addr.sin_port			= htons(this->port);

	int			option 	   = true;
	socklen_t 	socket_len = sizeof(option);
	
	if( setsockopt(this->socket_fd, SOL_SOCKET, SO_REUSEADDR, &option, socket_len) < 0 ) {
		this->print_debug_info("init() setsockopt() SO_REUSEADDR failed : [%02d] %s\n", errno, strerror(errno));

		return false; 
	}
	
	int       optval;
	socklen_t optlen = sizeof(optval);
	getsockopt(this->socket_fd, SOL_SOCKET, SO_SNDBUF, (char*)&optval, &optlen);

	optval *= SIZE_SCALE_SNDBUF;
	setsockopt(this->socket_fd, SOL_SOCKET, SO_SNDBUF, (char*)&optval, sizeof(optval));

	if( !IN_MULTICAST(ntohl(this->t_sock_addr.sin_addr.s_addr)) ) {
		this->print_debug_info("init() given address [%s] is not multicast\n", inet_ntoa(this->t_sock_addr.sin_addr));

		return false;
	}

	// BIND init
	if( bind(this->socket_fd, (struct sockaddr *)&this->t_sock_addr, sizeof(this->t_sock_addr)) < 0 ) {
		this->print_debug_info("init() bind() failed : [%02d] %s \n", errno, strerror(errno));

		return false;
	}
	
	this->print_debug_info("init() init multicast socket success \n");
	this->is_init = true;
	
	return true;
} 

void SOCKET_MulticastServer::stop(void) {
	if( !this->is_run ) {
		this->print_debug_info("stop() mutlicast is not running\n");
		
		return ;
	}
	this->is_loop = true;

	if( this->thread_func.joinable() ) {
		this->print_debug_info("stop() join & wait excute thread term\n");
		this->thread_func.join();
	}

	if( this->socket_fd != -1 ) {
		close(this->socket_fd);
		this->socket_fd = -1;
		this->print_debug_info("stop() multicast socket closed \n");
	}
	
	this->is_init = false;
	
	return ;
}

void SOCKET_MulticastServer::run(void) {
	if( !this->is_init ) {
		this->print_debug_info("run() init is not ready\n");
		
		return ;
	}
	
	if( this->is_run ) {
		this->print_debug_info("run() already running\n");
		return ;
	}
	this->is_run = true;
	
	
	this->print_debug_info("run() create execute thread\n");
	this->thread_func = thread(&SOCKET_MulticastServer::execute, this);
	
	return ;
}


void SOCKET_MulticastServer::send_data_handler(char *_data, int _size) {
	if( _data == NULL || _size == 0 ) {
		this->print_debug_info("send_data_handler() data size is 0, %x\n", _data);
		return ;
	} 
	
	if( this->is_run ) {
		this->li_queue_data.push(make_tuple(_data, _size));
	}
	
	return ;
}

void SOCKET_MulticastServer::execute(void) {
	this->print_debug_info("execute() run multicast thread\n");
	
	HEADER_INFO_t   t_header_info;

	memset(&t_header_info, 0x00, sizeof(t_header_info));
	t_header_info.seq_number		= 0;
	t_header_info.pcm_channels		= this->t_play_info.channels;
	t_header_info.pcm_sample_rate	= this->t_play_info.rate; 
	t_header_info.encode_mode		= this->t_play_info.encode_mode;
	t_header_info.mp3_bit_rate		= this->t_encode_info.quality;
	t_header_info.pcm_buffer_size	= this->t_play_info.pcm_buffer_size;
	t_header_info.pcm_period_size	= this->t_play_info.pcm_period_size;
	t_header_info.pcm_chunk_size	= this->t_play_info.chunk_size;
	t_header_info.data_size			= 0;

	bzero(t_header_info.rsvd2, sizeof(t_header_info.rsvd2));
	
	tuple<char *, int> tp_data;
	char   *data_ptr = NULL;
	int		data_length;
	
	this->li_queue_data	= queue<tuple<char *, int>>();

	while( !this->is_loop ) {
		if( this->li_queue_data.empty() ) {
			usleep(1000);
			continue;
		}

		tp_data = this->li_queue_data.front();

		data_ptr    = get<0>(tp_data);
		data_length = get<1>(tp_data);
		
		t_header_info.data_size = data_length;
		
		// send : header info
		if( sendto(this->socket_fd, &t_header_info, sizeof(t_header_info), MSG_NOSIGNAL, (struct sockaddr *)&this->t_sock_addr, sizeof(this->t_sock_addr)) < 0 ) {
			this->print_debug_info("execute() send [header] failed [%s] : [%02d] %s\n", this->ip_addr.c_str(), errno, strerror(errno));
			break;
		}

		// send : body data
		if( sendto(this->socket_fd, data_ptr, data_length, MSG_NOSIGNAL, (struct sockaddr *)&this->t_sock_addr, sizeof(this->t_sock_addr)) < 0 ) {
			this->print_debug_info("execute() send [body] failed [%s] : [%02d] %s\n", this->ip_addr.c_str(), errno, strerror(errno));
			break;
		}

		this->li_queue_data.pop();
	}
		
	if( this->socket_fd != -1 ) {
		close(this->socket_fd);
		this->socket_fd = -1;
	}
	
	this->print_debug_info("execute() stop multicast thread\n");
	this->is_run = false;
	
	return ;
}
