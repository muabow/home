#include "api_socket_client.h"

//
// ## Unicast client class
//
SOCKET_UnicastClient::SOCKET_UnicastClient(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	this->print_debug_info("SOCKET_UnicastClient() create instance\n");
	
	this->is_init			= false;
	this->is_run			= false;
	this->is_loop		 	= false;
		
	this->socket_fd			= -1;
	
	this->is_redundancy		= false;
	this->server_index		= -1;
	this->current_server_ip = "";

	this->master_ip_addr	= "";
	this->master_port		= -1;
	this->slave_ip_addr		= "";
	this->slave_port		= -1;
	
	this->hostname			= "";
	
	this->event_data_handle		= NULL;
	this->event_connect_handle	= NULL;
	
	this->v_server_list.clear();
	
	memset(&this->t_host_info, 0x00, sizeof(this->t_host_info));
	this->get_mac_address();
	
	return ;
}

SOCKET_UnicastClient::~SOCKET_UnicastClient(void) {
	this->print_debug_info("SOCKET_UnicastClient() instance destructed\n");
	
	this->is_loop = true;
	if( this->thread_func.joinable() ) {
		this->thread_func.join();
	}

	return ;
}

void SOCKET_UnicastClient::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	printf("\033[32m");
	fprintf(stdout, "SOCKET_UnicastClient::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	printf("\033[0m");
	
	return ;
}

void SOCKET_UnicastClient::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

bool SOCKET_UnicastClient::_get_mac_address(void) {
	int socket_fd; // Socket descriptor
	int count_if;
	
	struct ifreq  *t_if_req; // Interface request
	struct ifconf  t_if_conf;

	memset(this->t_host_info.mac_addr, 0x00, sizeof(this->t_host_info.mac_addr));
	sprintf(this->t_host_info.mac_addr, "00:1d:1d:00:00:00");
	memset(&t_if_conf, 0, sizeof(t_if_conf));
	
	t_if_conf.ifc_ifcu.ifcu_req = NULL;
	t_if_conf.ifc_len = 0;
	
	// Create a socket that we can use for all of our ioctls
	if( (socket_fd = socket(PF_INET, SOCK_DGRAM, 0)) < 0 ) {
		return false;
	}

	if( ioctl(socket_fd, SIOCGIFCONF, &t_if_conf) < 0 ) {
		return false;
	}

	if( (t_if_req = (ifreq *)malloc(t_if_conf.ifc_len)) == NULL ) {
		close(socket_fd);
		free(t_if_req);

		return false;
	
	} else {
		t_if_conf.ifc_ifcu.ifcu_req = t_if_req;
		if( ioctl(socket_fd, SIOCGIFCONF, &t_if_conf) < 0 ) {
			close(socket_fd);
			free(t_if_req);

			return false;
		}

		count_if = t_if_conf.ifc_len / sizeof(struct ifreq);
		for( int idx = 0; idx < count_if; idx++ ) {
			struct ifreq *req = &t_if_req[idx];

			if( !strcmp(req->ifr_name, "lo") ) {
				continue; // skip loopback interface
			}

			if( ioctl(socket_fd, SIOCGIFHWADDR, req) < 0 ) {
				break;
			}

			sprintf(this->t_host_info.mac_addr, "%02x:%02x:%02x:%02x:%02x:%02x",
					(unsigned char)req->ifr_hwaddr.sa_data[0],
					(unsigned char)req->ifr_hwaddr.sa_data[1],
					(unsigned char)req->ifr_hwaddr.sa_data[2],
					(unsigned char)req->ifr_hwaddr.sa_data[3],
					(unsigned char)req->ifr_hwaddr.sa_data[4],
					(unsigned char)req->ifr_hwaddr.sa_data[5]);
			break;
		}
	}

	close(socket_fd);
	free(t_if_req);

	this->print_debug_info("get_mac_address() MAC address info : [%s]\n", this->t_host_info.mac_addr);

	return true;
}

bool SOCKET_UnicastClient::get_mac_address(void) {
	JsonParser json_parser;
	string json_network_info = json_parser.read_file(PATH_MODULE_NETWORK_CONF);
	json_parser.parse(json_network_info);
	
	string str_mac_addr = "00:1d:1d:00:00:00";
	if( json_parser.select("/network_bonding/use").compare("enabled") == 0 ) {
		str_mac_addr = json_parser.select("/network_bonding/mac_address");

	} else if( json_parser.select("/network_primary/use").compare("enabled") == 0 ) {
		str_mac_addr = json_parser.select("/network_primary/mac_address");

	} else if( json_parser.select("/network_secondary/use").compare("enabled") == 0 ) {
		str_mac_addr = json_parser.select("/network_secondary/mac_address");
	}

	transform(str_mac_addr.begin(), str_mac_addr.end(), str_mac_addr.begin(), ::tolower);

	memset(this->t_host_info.mac_addr, 0x00, sizeof(this->t_host_info.mac_addr));
	sprintf(this->t_host_info.mac_addr, str_mac_addr.c_str());


	this->print_debug_info("get_mac_address() MAC address info : [%s]\n", this->t_host_info.mac_addr);

	return true;
}

void SOCKET_UnicastClient::init_server_list(void) {
	this->v_server_list.clear();
	this->v_server_list.push_back(make_tuple(this->master_ip_addr,		this->master_port, 	true));
	this->v_server_list.push_back(make_tuple(this->slave_ip_addr,	this->slave_port, 	this->is_redundancy));
	
	return ;
}

// public
void SOCKET_UnicastClient::set_server_info(string _type, string _ip_addr, int _port) {
	if( _type.compare("master") == 0 ) {
		this->master_ip_addr	= _ip_addr; 
		this->master_port		= _port;
		
	} else if( _type.compare("slave") == 0 ) {
		this->slave_ip_addr		= _ip_addr; 
		this->slave_port		= _port;
		
	} else {
		this->print_debug_info("set_server_info() unknown server type [%s] : [%s/%d]\n", _type.c_str(), _ip_addr.c_str(), _port);
	
		return ;
	}
	
	this->print_debug_info("set_server_info() set [%-6s] : [%s/%d]\n", _type.c_str(), _ip_addr.c_str(), _port);
	
	return ;
}

void SOCKET_UnicastClient::set_server_redundancy(string _redundancy) {
	this->is_redundancy = (_redundancy.compare("master") == 0 ? false : true); 

	this->print_debug_info("set_server_redundancy() set server redundancy : [%s]\n", this->is_redundancy ? "enabled" : "disabled");
	
	return ;
}


void SOCKET_UnicastClient::set_hostname(string _hostname) {
	this->hostname = _hostname;
	this->print_debug_info("set_hostname() set hostname : [%s]\n", _hostname.c_str());

	string str_hostname = "hostName:";
	str_hostname.append(this->hostname);
	
	memset(this->t_host_info.hostname, 0x00, sizeof(this->t_host_info.hostname));
	strcpy(this->t_host_info.hostname, str_hostname.c_str());
	
	return ;
}

string SOCKET_UnicastClient::get_hostname(void) {
	
	return this->hostname;
}


string SOCKET_UnicastClient::get_server_ip_addr(string _type) {
	if( _type.compare("master") == 0 ) {
		return this->master_ip_addr; 
		
	} else if( _type.compare("slave") == 0 ) {
		return this->slave_ip_addr;
	}
	
	return "";
}
	
int SOCKET_UnicastClient::get_server_port(string _type) {
	if( _type.compare("master") == 0 ) {
		return this->master_port;
		
	} else if( _type.compare("slave") == 0 ) {
		return this->slave_port;
	}
	
	return -1;
}

int SOCKET_UnicastClient::get_play_info(string _type) {
	if( 	 _type.compare("encode_mode")		== 0 ) 	return this->t_play_info.encode_mode;
	else if( _type.compare("pcm_sample_rate")	== 0 ) 	return this->t_play_info.rate;
	else if( _type.compare("pcm_channels") 		== 0 )	return this->t_play_info.channels;
	else if( _type.compare("pcm_chunk_size") 	== 0 ) 	return this->t_play_info.chunk_size;
	else if( _type.compare("pcm_buffer_size") 	== 0 )	return this->t_play_info.pcm_buffer_size;
	else if( _type.compare("pcm_period_size") 	== 0 )	return this->t_play_info.pcm_period_size;
	else if( _type.compare("encode_quality") 	== 0 )	return this->t_encode_info.quality;
	else return -1;

}

int SOCKET_UnicastClient::get_current_server_index(void) {
	
	return this->server_index;
}

string SOCKET_UnicastClient::get_current_server_ip(void) {
	
	return this->current_server_ip;
}

int SOCKET_UnicastClient::get_current_server_port(void) {
	
	return this->current_server_port;
}

void SOCKET_UnicastClient::set_data_handler(void (*_func)(char *, int)) {
	this->print_debug_info("set_data_handler() set event function\n");
	this->event_data_handle = _func;
	
	return ;
}

void SOCKET_UnicastClient::set_connect_handler(void (*_func)(string, bool, int, int *)) {
	this->print_debug_info("set_connect_handler() set event function\n");
	this->event_connect_handle = _func;
	
	return ;
}

void SOCKET_UnicastClient::run(void) {
	if( !this->is_init ) {
		this->print_debug_info("run() init is not ready\n");
		
		return ;
	}
	
	if( this->is_run ) {
		this->print_debug_info("run() already running\n");
		return ;
	}
	
	this->print_debug_info("run() create execute thread\n");
	this->thread_func = thread(&SOCKET_UnicastClient::execute, this);

	return ;
}

void SOCKET_UnicastClient::stop(void) {
	if( !this->is_init ) {
		this->print_debug_info("stop() unicast is not init\n");
		
		return ;
	}
	this->is_loop = true;

	if( this->thread_func.joinable() ) {
		this->print_debug_info("stop() join & wait excute thread term\n");
		this->thread_func.join();
	}

	if( this->socket_fd != -1 ) {
		this->print_debug_info("stop() socket client\n");
		close(this->socket_fd);
	
		this->socket_fd = -1;
	}
	
	this->is_init = false;

	return ;
}

bool SOCKET_UnicastClient::init(void) {
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
	
	this->init_server_list();
	
	this->server_index 		  = -1;
	this->current_server_ip   = "";
	this->current_server_port = -1;
	
	this->print_debug_info("init() init success \n");
	this->is_init = true;

	return true;
}

void SOCKET_UnicastClient::set_socket_option(void) {
	/*
	int opt_value_rcvbuf;
	socklen_t opt_size_rcvbuf = sizeof(opt_value_rcvbuf);
	if( getsockopt(this->socket_fd, SOL_SOCKET, SO_RCVBUF, &opt_value_rcvbuf, &opt_size_rcvbuf) < 0 ) {
		this->print_debug_info("set_socket_option() getsockopt() SO_RCVBUF failed : [%02d] %s\n", errno, strerror(errno));
	}
	this->print_debug_info("set_socket_option() getsockopt() rcv_buf_size : [%d]\n", opt_value_rcvbuf);

	opt_value_rcvbuf *= SIZE_SCALE_RCVBUF;
	if( setsockopt(this->socket_fd, SOL_SOCKET, SO_RCVBUF, (char *)&opt_value_rcvbuf, sizeof(opt_value_rcvbuf)) < 0 ) {
		this->print_debug_info("set_socket_option() setsockopt() SO_RCVBUF failed : [%02d] %s\n", errno, strerror(errno));
	}
	this->print_debug_info("set_socket_option() getsockopt() reset rcv_buf_size : [%d]\n", opt_value_rcvbuf);
	*/

	struct linger t_opt_value_linger;
	t_opt_value_linger.l_onoff  = 1;
	t_opt_value_linger.l_linger = 0;
	if( setsockopt(this->socket_fd, SOL_SOCKET, SO_LINGER, &t_opt_value_linger, sizeof(t_opt_value_linger)) < 0 ) {
		this->print_debug_info("set_socket_option() setsockopt() SO_LINGER failed : [%02d] %s\n", errno, strerror(errno));
	}

	struct timeval t_opt_value_recv_timeo = {TIME_RCV_TIMEOUT_SEC, TIME_RCV_TIMEOUT_MSEC};
	if( setsockopt(this->socket_fd, SOL_SOCKET, SO_RCVTIMEO, &t_opt_value_recv_timeo, sizeof(t_opt_value_recv_timeo) ) < 0 ) {
		this->print_debug_info("set_socket_option() setsockopt() SO_RCVTIMEO failed : [%02d] %s\n", errno, strerror(errno));
	}
			
	int opt_value_nodelay = 1;
	if( setsockopt(this->socket_fd, IPPROTO_TCP, TCP_NODELAY, &opt_value_nodelay, sizeof(opt_value_nodelay)) < 0 ) {
		this->print_debug_info("set_socket_option() setsockopt() SO_RCVTIMEO failed : [%02d] %s\n", errno, strerror(errno));
	}
	
	/*
	int opt_value_cork = 0;
	if( setsockopt(this->socket_fd, IPPROTO_TCP, TCP_CORK, &opt_value_cork, sizeof(opt_value_cork)) < 0 ) {
		this->print_debug_info("set_socket_option() setsockopt() TCP_CORK failed : [%02d] %s\n", errno, strerror(errno));
	}
	*/
	
	return ;
}

bool SOCKET_UnicastClient::connect_server(void) {
	if( send(this->socket_fd, &this->t_host_info, sizeof(this->t_host_info), 0) < 0 ) {
		this->print_debug_info("connect_server() send hostname failed : [%02d] %s\n", errno, strerror(errno));
				
		return false;
	}

	int rc;
	this->print_debug_info("connect_server() recv play information from server..\n");
	if( !this->select_socket_event() ) {
		this->print_debug_info("connect_server() recv() play information timeout\n");
	
		return false;
	}
	
	if( (rc = recv(this->socket_fd, &this->t_play_info, sizeof(this->t_play_info), MSG_WAITALL)) < 0 ) {
		this->print_debug_info("connect_server() recv() play information failed : [%02d] %s\n", errno, strerror(errno));

		return false;

	} else if( rc == 0 ) {
		this->print_debug_info("connect_server() recv() play information connection reset by peer..\n");

		return false;
	}

	if( !(this->t_play_info.channels == 1 || this->t_play_info.channels == 2) 
		|| this->t_play_info.rate < 0
		|| this->t_play_info.chunk_size < 0 ) {
		this->print_debug_info("connect_server() invaild received play info\n");
		this->print_debug_info("connect_server() - sample_rate     [%d]\n", this->t_play_info.rate);
		this->print_debug_info("connect_server() - channels        [%d]\n", this->t_play_info.channels);
		this->print_debug_info("connect_server() - chunk_size      [%d]\n", this->t_play_info.chunk_size);
		
		return false;
	}
	
	if( this->t_play_info.encode_mode ) {
		if( !this->select_socket_event() ) {
			this->print_debug_info("connect_server() recv() encode information timeout\n");
		
			return false;
		}
		
		if( (rc = recv(this->socket_fd, &this->t_encode_info, sizeof(this->t_encode_info), MSG_WAITALL)) < 0 ) {
			this->print_debug_info("connect_server() recv() encode information failed : [%02d] %s\n", errno, strerror(errno));

			return false;

		} else if( rc == 0 ) {
			this->print_debug_info("connect_server() recv() encode information connection reset by peer..\n");

			return false;
		}
	}
	int encode_bit_rate = 0;
	switch( this->t_encode_info.quality ) {
		case 2:  encode_bit_rate  = 192; break;
		case 5:  encode_bit_rate  = 128; break;
		case 7:  encode_bit_rate  = 64;  break;
		default: encode_bit_rate  = 128; break;
	}
	
	this->print_debug_info("connect_server() - sample_rate     [%d]\n", this->t_play_info.rate);
	this->print_debug_info("connect_server() - channels        [%d]\n", this->t_play_info.channels);
	this->print_debug_info("connect_server() - chunk_size      [%d]\n", this->t_play_info.chunk_size);
	this->print_debug_info("connect_server() - idx             [%d]\n", this->t_play_info.idx);
	this->print_debug_info("connect_server() - encode_mode     [%s]\n", this->t_play_info.encode_mode == 0 ? "pcm" : "mp3");
	this->print_debug_info("connect_server() - encode_quality  [%d/%d kbps]\n", this->t_encode_info.quality, encode_bit_rate);
	this->print_debug_info("connect_server() - pcm_buffer_size [%d]\n", this->t_play_info.pcm_buffer_size);
	this->print_debug_info("connect_server() - pcm_period_size [%d]\n", this->t_play_info.pcm_period_size);
	
	
	this->print_debug_info("connect_server() init unicast socket success \n");
	
	return true;
}

bool SOCKET_UnicastClient::select_socket_event(void) {
	int     rc;
	struct  timeval t_select_timeout;
	fd_set  t_read_fds;

	FD_ZERO(&t_read_fds);
	FD_SET(this->socket_fd, &t_read_fds);

	t_select_timeout.tv_sec  = TIME_TIMEOUT_SEC;
	t_select_timeout.tv_usec = TIME_TIMEOUT_MSEC;

	if( this->socket_fd < 0 ) {
		this->print_debug_info("select_socket_event() select() socket already closed\n");
		return false;
	}
	
	if( (rc = select(this->socket_fd + 1, &t_read_fds, NULL, NULL, &t_select_timeout)) < 0 ) {
		switch( errno ) {
			case 4 :
				this->print_debug_info("select_socket_event() select() error  : [%02d] %s\n", errno, strerror(errno));
				break;
			default :
				this->print_debug_info("select_socket_event() select() failed : [%02d] %s\n", errno, strerror(errno));
				break;
		}

		return false;

	} else if ( rc == 0 ) {
		this->print_debug_info("select_socket_event() select() timeout\n");
		return false;
	}

	return true;
} // method: select_socket_event()

void SOCKET_UnicastClient::execute(void) {
	this->print_debug_info("execute() run unicast thread\n");
	tuple<string, int, bool> server_info;
	
	int 	num_server_list = (int)this->v_server_list.size();
	bool	is_connected 	= false;
	int		sample_status = 0;
	
	string 	str_server_ip;
	int 	num_server_port;
	struct  sockaddr_in t_sock_addr;
	
	int 	server_idx;
	int 	opt_value_error;
	int 	flag_fcntl		= 0;
	int 	flag_nonblock	= 0;
	
	fd_set	t_read_fds, t_write_fds;
	struct 	timeval t_timeout_nonblock;
	
	int     rc = 0;
	int		data_size  = 0;
	char	*recv_data = NULL;
	
	while( !this->is_loop ) {
		while( !this->is_loop ) {	// alive check loop
			if( this->socket_fd != -1 ) {
				close(this->socket_fd);
				this->socket_fd = -1;
			}
			is_connected = false;
			
			if( (this->socket_fd = socket(AF_INET, SOCK_STREAM, 0)) < 0 ) {
				this->print_debug_info("execute() socket() failed : [%02d] %s\n", errno, strerror(errno));
				this->socket_fd = -1;
				break;
			}
			
			// server 순회
			for( server_idx = 0 ; server_idx < num_server_list ; server_idx++ ) {
				server_info = this->v_server_list[server_idx];
				if( get<2>(server_info) == true ) break;
			}
	
			if( server_idx == num_server_list ) {
				this->print_debug_info("execute() not found alive server \n");
				this->server_index = -1;
				break;
			}
			str_server_ip	= get<0>(server_info);
			num_server_port = get<1>(server_info);
		
			this->server_index = server_idx;
			
			t_sock_addr.sin_family 		= AF_INET;
			t_sock_addr.sin_addr.s_addr = inet_addr(str_server_ip.c_str());
			t_sock_addr.sin_port 		= htons(num_server_port);
			
			flag_fcntl		= fcntl(this->socket_fd, F_GETFL, 0);
			flag_nonblock 	= flag_fcntl | O_NONBLOCK;
			
			if( fcntl(this->socket_fd, F_SETFL, flag_nonblock) < 0 ) {
				this->print_debug_info("execute() fcntl() set socket nonblock failed : [%02d] %s\n", errno, strerror(errno));
			}
			
			FD_ZERO(&t_read_fds); 
			FD_SET(this->socket_fd, &t_read_fds); 
			t_write_fds = t_read_fds; 
			
			t_timeout_nonblock.tv_sec  = 1;
			t_timeout_nonblock.tv_usec = 0;
	
			connect(this->socket_fd, (struct sockaddr *)&t_sock_addr, sizeof(t_sock_addr));
			if( select(this->socket_fd + 1, &t_read_fds, &t_write_fds, NULL, &t_timeout_nonblock) < 0 ) {
				switch( errno ) {
					default :
						this->print_debug_info("execute() select() failed : [%02d] %s\n", errno, strerror(errno));
						
						break;
				}
			}
			
			socklen_t opt_size_error = sizeof(opt_value_error);
			getsockopt(this->socket_fd, SOL_SOCKET, SO_ERROR, (void*) &opt_value_error, &opt_size_error);
			
			if( this->socket_fd < 0 ) {
				this->print_debug_info("execute() socket already closed \n");
				this->socket_fd = -1;
				break;
			}
			
			this->current_server_ip		= str_server_ip;
			this->current_server_port	= num_server_port;
			
			if( (FD_ISSET(this->socket_fd, &t_write_fds) > 0) && (opt_value_error == 0) ) { 
				this->print_debug_info("execute() server [%s/%d] connect success\n", str_server_ip.c_str(), num_server_port);
						
				is_connected = true;
				
				// break loop
				break;
				
			} else {
				this->print_debug_info("execute() server info[%s/%d] connect failed : [%02d] %s\n",	str_server_ip.c_str(), num_server_port, errno, strerror(errno));
				this->v_server_list[server_idx] = make_tuple(str_server_ip, num_server_port, false);
				
				// rewind loop
				sample_status = 0;
				if( this->event_connect_handle != NULL ) {
					this->event_connect_handle("unicast", this->is_run, NETWORK_CONNECT_FAILED, &sample_status);
				}
				
				sleep(TIME_SLEEP_LOOP);
			}
		} // end of while() : alive check loop
		
		if( !this->is_loop ) {
			this->init_server_list();
			
			if( !is_connected ) {
				this->print_debug_info("execute() retry find server..\n");
				
				sleep(TIME_SLEEP_LOOP);
				continue;
			}
			
			this->set_socket_option();
			
			if( !this->connect_server() ) {
				this->print_debug_info("execute() connect server failed, rewind connect loop\n");
				
				sleep(TIME_SLEEP_LOOP);
				continue;
			}
			data_size = this->t_play_info.chunk_size;
			
			if( recv_data != NULL ) {
				delete recv_data;
				recv_data = NULL;
			}
			recv_data = new char[data_size];
			
			this->is_run = true;
			
			sample_status = 0;
			if( this->event_connect_handle != NULL ) {
				this->event_connect_handle("unicast", this->is_run, NETWORK_CONNECT_NORNAL, &sample_status);
			}

			if( sample_status != 0 ) {
				this->is_loop = true;
			}
		}
		
		if( fcntl(this->socket_fd, F_SETFL, 0) < 0 ) {
			this->print_debug_info("execute() fcntl() set socket block failed : [%02d] %s\n", errno, strerror(errno));
		}
		
		while( !this->is_loop ) { // recv data loop
			if( (rc = recv(this->socket_fd, recv_data, data_size, MSG_WAITALL)) < 0 ) {
				this->print_debug_info("execute() recv() failed : [%02d] %s\n", errno, strerror(errno));
				
				if( errno == EAGAIN ) {
					this->print_debug_info("execute() recv() recv bytes : [%d]\n", rc);
				}
				
				break;
			
			} else if( rc == 0 ) {
				this->print_debug_info("execute() recv() [source data] Disconnected by peer.. \n");
				break;
			}
			
			if( this->event_data_handle != NULL ) {
				this->event_data_handle(recv_data, data_size);
			}
		} // end of while() : // recv data loop
		
		this->is_run = false;
		if( this->event_connect_handle != NULL ) {
			this->event_connect_handle("unicast", this->is_run, NETWORK_CONNECT_NORNAL, &sample_status);
		}
	} 
	this->is_loop = true;
		
	if( recv_data != NULL ) {
		delete recv_data;
		recv_data = NULL;
	}
	
	if( this->socket_fd != -1 ) {
		close(this->socket_fd);
		this->socket_fd = -1;
	}
	
	this->print_debug_info("execute() stop unicast thread\n");

	this->is_run = false;
	
	return ;
}



//
// ## Multicast client class
//
SOCKET_MulticastClient::SOCKET_MulticastClient(bool _is_debug_print) {
	if( this->is_debug_print ) {
		this->set_debug_print();
	}
	this->print_debug_info("SOCKET_MulticastClient() create instance\n");
	
	this->is_init		 = false;
	this->is_run		 = false;
	this->is_loop		 = false;
		
	this->socket_fd		= -1;
	
	this->str_ip_addr	= "";
	this->num_port		= -1;

	
	return ;
}

SOCKET_MulticastClient::~SOCKET_MulticastClient(void) {
	this->print_debug_info("SOCKET_MulticastClient() instance destructed\n");
	
	this->is_loop = true;
	if( this->thread_func.joinable() ) {
		this->thread_func.join();
	}

	return ;
}

void SOCKET_MulticastClient::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	printf("\033[33m");
	fprintf(stdout, "SOCKET_MulticastClient::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	printf("\033[0m");
	
	return ;
}

void SOCKET_MulticastClient::set_debug_print(void) {
	this->is_debug_print = true;
	
	this->print_debug_info("set_debug_print() is set on\n");
	
	return ;
}

void SOCKET_MulticastClient::set_server_info(string _ip_addr, int _port) {
	this->str_ip_addr	= _ip_addr; 
	this->num_port		= _port;
	
	this->print_debug_info("set_server_info() set : [%s/%d]\n", _ip_addr.c_str(), _port);
	
	return ;
}

string SOCKET_MulticastClient::get_server_ip(void) {

	return this->str_ip_addr; 
}

int SOCKET_MulticastClient::get_server_port(void) {

	return this->num_port; 
}

int SOCKET_MulticastClient::get_play_info(string _type) {
	if( 	 _type.compare("encode_mode")		== 0 ) 	return this->t_play_info.encode_mode;
	else if( _type.compare("pcm_sample_rate")	== 0 ) 	return this->t_play_info.rate;
	else if( _type.compare("pcm_channels") 		== 0 )	return this->t_play_info.channels;
	else if( _type.compare("pcm_chunk_size") 	== 0 ) 	return this->t_play_info.chunk_size;
	else if( _type.compare("pcm_buffer_size") 	== 0 )	return this->t_play_info.pcm_buffer_size;
	else if( _type.compare("pcm_period_size") 	== 0 )	return this->t_play_info.pcm_period_size;
	else if( _type.compare("encode_quality") 	== 0 )	return this->t_encode_info.quality;
	else return -1;

}

void SOCKET_MulticastClient::set_data_handler(void (*_func)(char *, int)) {
	this->print_debug_info("set_data_handler() set event function\n");
	this->event_data_handle = _func;
	
	return ;
}

void SOCKET_MulticastClient::set_connect_handler(void (*_func)(string, bool, int, int *)) {
	this->print_debug_info("set_connect_handler() set event function\n");
	this->event_connect_handle = _func;
	
	return ;
}

bool SOCKET_MulticastClient::init(void) {
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
	
	memset(&this->t_play_info, 0x00, sizeof(PLAY_INFO_t));
	
	this->print_debug_info("init() init success \n");
	this->is_init = true;

	return true;
}

void SOCKET_MulticastClient::run(void) {
	if( !this->is_init ) {
		this->print_debug_info("run() init is not ready\n");
		
		return ;
	}
	
	if( this->is_run ) {
		this->print_debug_info("run() already running\n");
		return ;
	}
	
	this->print_debug_info("run() create execute thread\n");
	this->thread_func = thread(&SOCKET_MulticastClient::execute, this);

	return ;
}

void SOCKET_MulticastClient::stop(void) {
	if( !this->is_init ) {
		this->print_debug_info("stop() multicast is not init\n");
		
		return ;
	}
	this->is_loop = true;

	if( this->thread_func.joinable() ) {
		this->print_debug_info("stop() join & wait excute thread term\n");
		this->thread_func.join();
	}

	if( this->socket_fd != -1 ) {
		this->print_debug_info("stop() socket client\n");
		close(this->socket_fd);
	
		this->socket_fd = -1;
	}
	
	this->is_init = false;
	
	return ;
}

bool SOCKET_MulticastClient::select_socket_event(void) {
	int     rc;
	struct  timeval t_select_timeout;
	fd_set  t_read_fds;

	FD_ZERO(&t_read_fds);
	FD_SET(this->socket_fd, &t_read_fds);

	t_select_timeout.tv_sec  = TIME_TIMEOUT_SEC;
	t_select_timeout.tv_usec = TIME_TIMEOUT_MSEC;

	if( this->socket_fd < 0 ) {
		this->print_debug_info("select_socket_event() select() socket already closed\n");
		return false;
	}
	
	if( (rc = select(this->socket_fd + 1, &t_read_fds, NULL, NULL, &t_select_timeout)) < 0 ) {
		switch( errno ) {
			case 4 :
				this->print_debug_info("select_socket_event() select() error  : [%02d] %s\n", errno, strerror(errno));
				break;
			default :
				this->print_debug_info("select_socket_event() select() failed : [%02d] %s\n", errno, strerror(errno));
				break;
		}

		return false;

	} else if ( rc == 0 ) {
		this->print_debug_info("select_socket_event() select() timeout\n");
		return false;
	}

	return true;
} // method: select_socket_event()


void SOCKET_MulticastClient::execute(void) {
	this->print_debug_info("execute() run multicast thread\n");
	
	bool	is_connected = false;
	int 	sample_status = 0;

	string 	str_server_ip;
	int 	num_server_port;
	struct  sockaddr_in t_sock_addr;
	struct	ip_mreq t_mcast_group;
	int		opt_reuse;
	
	int     rc = 0;
	int		data_size  = 0;
	char	*recv_data = NULL;
	
	HEADER_INFO_t t_header_info;
	
	while( !this->is_loop ) {
		while( !this->is_loop ) {	// alive check loop
			if( this->socket_fd != -1 ) {
				close(this->socket_fd);
				this->socket_fd = -1;
			}
			is_connected = false;
			
			if( (this->socket_fd = socket(AF_INET, SOCK_DGRAM, 0)) < 0 ) {
				this->print_debug_info("execute() socket() failed : [%02d] %s\n", errno, strerror(errno));
				break;
			}
			
			// option : reuse
			opt_reuse = 1;
			if( setsockopt(this->socket_fd, SOL_SOCKET, SO_REUSEADDR, (char *)&opt_reuse, sizeof(opt_reuse)) < 0 ) {
				this->print_debug_info("execute() setsockopt() SO_REUSEADDR failed : [%02d] %s\n", errno, strerror(errno));
				break;
			}
			
			str_server_ip	= this->str_ip_addr;
			num_server_port = this->num_port;
		
			t_sock_addr.sin_family 		= AF_INET;
			t_sock_addr.sin_addr.s_addr = INADDR_ANY;
			t_sock_addr.sin_port 		= htons(num_server_port);
			
			// BIND init
			if( bind(this->socket_fd, (struct sockaddr *)&t_sock_addr, sizeof(t_sock_addr)) < 0 ) {
				this->print_debug_info("execute() bind() failed : [%02d] %s\n", errno, strerror(errno));
				break;
			}
			
			
			// multicast ip address check
			if( !IN_MULTICAST(ntohl(inet_addr(str_server_ip.c_str()))) ) {
				this->print_debug_info("execute() IN_MULTICAST [%s] is not multicast\n", str_server_ip.c_str());
				break;
			}
			
			// Set multicast address
			if( inet_aton(str_server_ip.c_str(), &t_mcast_group.imr_multiaddr) < 0 ) {
				this->print_debug_info("execute() inet_aton failed : [%02d] %s\n", errno, strerror(errno));
				break;
			}
			t_mcast_group.imr_interface.s_addr = htonl(INADDR_ANY);
			
			if( setsockopt(this->socket_fd, IPPROTO_IP, IP_ADD_MEMBERSHIP, &t_mcast_group, sizeof(t_mcast_group)) < 0 ) {
				this->print_debug_info("execute() setsockopt() IP_ADD_MEMBERSHIP failed : [%02d] %s\n", errno, strerror(errno));
				break;
			}

			this->print_debug_info("execute() play information receive from multicast channel...\n");
			if( !this->select_socket_event() ) {
				this->print_debug_info("execute() read [header info] timeout..\n");
				break;
			}
			
			if( (rc = read(this->socket_fd, &t_header_info, sizeof(t_header_info))) < 0 ) {
				this->print_debug_info("execute() read() header info failed : [%02d] %s\n", errno, strerror(errno));
				break;
			
			} else if( rc == 0 ) {
				this->print_debug_info("execute() [header info] Disconnected by peer..\n");
				break;
			}
			
			this->t_play_info.rate				= t_header_info.pcm_sample_rate;
			this->t_play_info.channels			= t_header_info.pcm_channels;
			this->t_play_info.chunk_size		= t_header_info.pcm_chunk_size;
			this->t_play_info.idx          	    = 0;
			this->t_play_info.encode_mode       = t_header_info.encode_mode;
			this->t_play_info.pcm_buffer_size	= t_header_info.pcm_buffer_size;
			this->t_play_info.pcm_period_size	= t_header_info.pcm_period_size;
			this->t_encode_info.chunk_size		= t_header_info.pcm_chunk_size;
			this->t_encode_info.sample_rate		= t_header_info.pcm_sample_rate;
			this->t_encode_info.quality			= t_header_info.mp3_bit_rate;
			
			if( this->t_play_info.chunk_size == 0 ) {
				this->t_play_info.chunk_size = t_header_info.data_size;
			}
			
			if( !(this->t_play_info.channels == 1 || this->t_play_info.channels == 2) 
				|| this->t_play_info.rate < 0
				|| this->t_play_info.chunk_size < 0 ) {
				this->print_debug_info("execute() invaild received play info\n");
				this->print_debug_info("execute() - sample_rate     [%d]\n", this->t_play_info.rate);
				this->print_debug_info("execute() - channels        [%d]\n", this->t_play_info.channels);
				this->print_debug_info("execute() - chunk_size      [%d]\n", this->t_play_info.chunk_size);
				
				break;
			}
			
			int encode_bit_rate = 0;
			switch( this->t_encode_info.quality ) {
				case 2:  encode_bit_rate  = 192; break;
				case 5:  encode_bit_rate  = 128; break;
				case 7:  encode_bit_rate  = 64;  break;
				default: encode_bit_rate  = 128; break;
			}
			
			this->print_debug_info("execute() - sample_rate     [%d]\n", this->t_play_info.rate);
			this->print_debug_info("execute() - channels        [%d]\n", this->t_play_info.channels);
			this->print_debug_info("execute() - chunk_size      [%d]\n", this->t_play_info.chunk_size);
			this->print_debug_info("execute() - encode_mode     [%s]\n", this->t_play_info.encode_mode == 0 ? "pcm" : "mp3");
			this->print_debug_info("execute() - encode_quality  [%d/%d kbps]\n", this->t_encode_info.quality, encode_bit_rate);
			this->print_debug_info("execute() - pcm_buffer_size [%d]\n", this->t_play_info.pcm_buffer_size);
			this->print_debug_info("execute() - pcm_period_size [%d]\n", this->t_play_info.pcm_period_size);
			
			char *ptr_body_data = new char[this->t_play_info.chunk_size];
			
			if( !this->select_socket_event() ) {
				this->print_debug_info("execute() read() [source data] timeout..\n");
				is_connected = false;
			
			} else {
				if( (rc = read(this->socket_fd, ptr_body_data, this->t_play_info.chunk_size)) < 0 ) {
					this->print_debug_info("execute() read() source data failed : [%02d] %s\n", errno, strerror(errno));
					is_connected = false;
				
				} else if( rc == 0 ) {
					this->print_debug_info("execute() [source data] Disconnected by peer..\n");
					is_connected = false;
	
				} else {
					is_connected = true;
				}
			}

			delete ptr_body_data;
			break;
		} // end of while() : alive check loop
		
		if( !this->is_loop ) {
			if( !is_connected ) {
				this->print_debug_info("execute() retry connect to multicast channel..\n");
				sample_status = 0;
				if( this->event_connect_handle != NULL ) {
					this->event_connect_handle("multicast", this->is_run, NETWORK_CONNECT_FAILED, &sample_status);
				}
	
				sleep(TIME_SLEEP_LOOP);
				continue;
			}
			
			data_size = this->t_play_info.chunk_size;
			
			if( recv_data != NULL ) {
				delete recv_data;
				recv_data = NULL;
			}
			recv_data = new char[data_size];
			
			this->is_run = true;
			sample_status = 0;
			if( this->event_connect_handle != NULL ) {
				this->event_connect_handle("multicast", this->is_run, NETWORK_CONNECT_NORNAL, &sample_status);
			}
		}
		
		while( !this->is_loop ) { // recv data loop
			if( !this->select_socket_event() ) {
				break;
			}
			data_size = this->t_play_info.chunk_size;
			
			if( !this->select_socket_event() ) {
				this->print_debug_info("execute() read() [header info] timeout..\n");
				break;
			}

			if( (rc = read(this->socket_fd, &t_header_info, sizeof(t_header_info))) < 0 ) {
				this->print_debug_info("execute() read() header info failed : [%02d] %s\n", errno, strerror(errno));
				break;
			
			} else if( rc == 0 ) {
				this->print_debug_info("execute() recv() [header info] Disconnected by peer.. \n");
				break;
			}
			
			if( 	this->t_play_info.rate 		  != t_header_info.pcm_sample_rate
				||	this->t_play_info.channels 	  != t_header_info.pcm_channels 
				||	this->t_play_info.encode_mode != t_header_info.encode_mode
				|| this->t_encode_info.quality 	  != t_header_info.mp3_bit_rate ) {
				
				this->print_debug_info("execute() recv() change header info, disconnect connection.. \n");
				if( this->event_data_handle != NULL ) {
				this->event_data_handle(NULL, -1);
			}
				break;
			}
			
			if( data_size < t_header_info.data_size ) {
				// this->print_debug_info("execute() recv() encode size over, realloc [%d] -> [%d]\n", data_size, t_header_info.data_size);
				if( recv_data != NULL ) {
					delete recv_data;
					recv_data = new char[t_header_info.data_size];
				}
			}
			data_size = t_header_info.data_size;
			
			if( !this->select_socket_event() ) {
				this->print_debug_info("execute() read() [source data] timeout..\n");
				break;
			}

			if( (rc = read(this->socket_fd, recv_data, data_size)) < 0 ) {
				this->print_debug_info("execute() read() source data failed : [%02d] %s\n", errno, strerror(errno));
				break;
			
			} else if( rc == 0 ) {
				this->print_debug_info("execute() recv() [source data] Disconnected by peer.. \n");
				break;
			}
							
			if( this->event_data_handle != NULL ) {
				this->event_data_handle(recv_data, data_size);
			}
		} // end of while() : // recv data loop
		
		this->is_run = false;
	} 
	
	this->is_loop = true;
		
	if( recv_data != NULL ) {
		delete recv_data;
		recv_data = NULL;
	}
	
	if( this->socket_fd != -1 ) {
		close(this->socket_fd);
		this->socket_fd = -1;
	}
	
	this->print_debug_info("execute() stop multicast thread\n");

	this->is_run = false;
	if( this->event_connect_handle != NULL ) {
		this->event_connect_handle("multicast", this->is_run, NETWORK_CONNECT_NORNAL, &sample_status);
	}
	
	return ;
}
