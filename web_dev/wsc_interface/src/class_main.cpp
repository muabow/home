#include <stdio.h>
#include <unistd.h>

#include <iostream>
#include <sstream>
#include <fstream>

#include "class_main.h"


WS_ClientHandler::WS_ClientHandler(bool _is_debug_print) {
	this->is_debug_print   = _is_debug_print;
	this->funcEventHandler = NULL;

	this->print_debug_info("WS_ClientHandler() constructor\n");

	return ;
}

WS_ClientHandler::~WS_ClientHandler(void) {
	this->print_debug_info("WS_ClientHandler() destructor\n");

	this->print_debug_info("WS_ClientHandler() close websocket client list\n");
	for( int idx = 0 ; idx < (int)this->v_ws_handle_list.size() ; idx++ ) {
		 get<0>(this->v_ws_handle_list.at(idx))->term();
	}

	return ;
}

bool WS_ClientHandler::init(string _ip_addr, string _uri) {
	this->print_debug_info("init() websocket client..\n");
	this->print_debug_info("init() set client IP address : [%s]\n", _ip_addr.c_str());
	this->print_debug_info("init() set client URI name   : [%s]\n", _uri.c_str());

	if( this->is_created_handler(_ip_addr, _uri) ) {
		this->print_debug_info("init() already created instance\n");
		return false;
	}

	WebsocketHandler *ws_handler = new WebsocketHandler;

	ws_handler->set_route_wscif();
	ws_handler->set_route_to(WebsocketHandler::NATIVE_ONLY);

	if( !this->network_handler.is_device_alive(_ip_addr) ) {
		this->print_debug_info("init() WebsocketHandler() init network icmp failed \n");

		ws_handler->set_target_info(_ip_addr, _uri);

		this->v_ws_handle_list.push_back(make_tuple(ws_handler, false, false, ""));
		return false;
	}

	if( !ws_handler->init(_ip_addr.c_str(), _uri.c_str(), this->is_debug_print) ) {
		this->print_debug_info("init() WebsocketHandler() init failed \n");

		this->v_ws_handle_list.push_back(make_tuple(ws_handler, false, false, ""));
		return false;
	}

	if( this->funcEventHandler != NULL ) {
		ws_handler->set_event_handler(this->funcEventHandler);
	}

	this->v_ws_handle_list.push_back(make_tuple(ws_handler, true, true, ""));

	this->print_debug_info("init() created instance\n");

	return true;
}

bool WS_ClientHandler::remove(string _ip_addr, string _uri) {
	this->print_debug_info("remove() websocket client..\n");
	this->print_debug_info("remove() remove client IP address : [%s]\n", _ip_addr.c_str());
	this->print_debug_info("remove() remove client URI name   : [%s]\n", _uri.c_str());

	int idx;
	if( (idx = this->get_index_handler(_ip_addr, _uri)) < 0 ) {
		this->print_debug_info("remove() not found instance\n");
		return false;
	}

	get<0>(this->v_ws_handle_list.at(idx))->term();
	this->v_ws_handle_list.erase(this->v_ws_handle_list.begin() + idx);

	return true;
}

void WS_ClientHandler::set_alive_info(string _ip_addr, string _uri, string _type, bool _is_alive) {
	this->print_debug_info("set_alive_info() websocket client..\n");
	this->print_debug_info("set_alive_info() client IP address : [%s]\n", _ip_addr.c_str());
	this->print_debug_info("set_alive_info() client URI name   : [%s]\n", _uri.c_str());
	this->print_debug_info("set_alive_info() type              : [%s]\n", _type.c_str());

	int idx;
	if( (idx = this->get_index_handler(_ip_addr, _uri)) < 0 ) {
		this->print_debug_info("set_alive_info() not found instance\n");
		return ;
	}

	bool is_alive;
	if( _type.compare("alive") == 0 ) {
		is_alive = get<2>(this->v_ws_handle_list.at(idx));
		get<2>(this->v_ws_handle_list.at(idx)) = _is_alive;

	} else {
		is_alive = get<1>(this->v_ws_handle_list.at(idx));
		get<1>(this->v_ws_handle_list.at(idx)) = _is_alive;
	}

	this->print_debug_info("set_alive_info() change alive info : [%s] - [%d] -> [%d]\n", _type.c_str(), is_alive, _is_alive);


	return ;
}

bool WS_ClientHandler::get_alive_info(string _ip_addr, string _uri) {
	int list_size = (int)this->v_ws_handle_list.size();

	for( int idx = 0 ; idx < list_size ; idx++ ) {
		if(    get<0>(this->v_ws_handle_list.at(idx))->get_uri_name().compare(_uri) == 0
			&& get<0>(this->v_ws_handle_list.at(idx))->get_ip_addr().compare(_ip_addr) == 0 ) {

			return get<2>(this->v_ws_handle_list.at(idx));
		}
	}

	return false;
}

int WS_ClientHandler::get_ws_handler_count(void) {

	return this->v_ws_handle_list.size();
}

tuple<WebsocketHandler *, bool, bool> WS_ClientHandler::get_ws_handler_info(int _index) {
	bool   is_eth_up     = get<1>(this->v_ws_handle_list.at(_index));
	bool   is_alive      = get<2>(this->v_ws_handle_list.at(_index));

	return make_tuple(get<0>(this->v_ws_handle_list.at(_index)), is_eth_up, is_alive);
}

string WS_ClientHandler::get_ws_handler_list(void) {
	int list_size = (int)this->v_ws_handle_list.size();
	this->print_debug_info("get_ws_handler_list() display ws_handler list, count [%02d]\n", list_size);

	ostringstream str_json;
	str_json << "[";

	for( int idx = 0 ; idx < list_size ; idx++ ) {
		string ip_addr       = get<0>(this->v_ws_handle_list.at(idx))->get_ip_addr();
		string uri_name      = get<0>(this->v_ws_handle_list.at(idx))->get_uri_name();
		bool   is_eth_up     = get<1>(this->v_ws_handle_list.at(idx));
		bool   is_alive      = get<2>(this->v_ws_handle_list.at(idx));
		string latest_status = get<3>(this->v_ws_handle_list.at(idx));

		this->print_debug_info(" - [%02d] IP address/URI : [%-15s/%-20s], is_eth_up: [%d], is_alive: [%d]\n",
				idx, ip_addr.c_str(), uri_name.c_str(), is_eth_up, is_alive);

		str_json << "{" \
				 << "\"ip_addr\": \""       << ip_addr       << "\", " \
				 << "\"uri\": \""           << uri_name      << "\", " \
				 << "\"is_eth_up\": "       << is_eth_up     << ", " \
				 << "\"is_alive\": "        << is_alive      << " " \
				 << "}";

		if( (idx + 1) != list_size ) {
			str_json << ", ";
		}
	}
	str_json << "]";
	string buffer(str_json.str());
	
	this->print_debug_info("get_ws_handler_list() display end\n");

	return buffer;
}

void WS_ClientHandler::send(string _ip_addr, string _uri, int _cmd_id, string _data) {
	this->print_debug_info("get_ws_handler_list() send\n");
	this->print_debug_info("get_ws_handler_list() - IP address : [%s]\n", _ip_addr.c_str());
	this->print_debug_info("get_ws_handler_list() - uri        : [%s]\n", _uri.c_str());
	this->print_debug_info("get_ws_handler_list() - cmd_id     : [%d]\n", _cmd_id);
	this->print_debug_info("get_ws_handler_list() - data       : [%s]\n", _data.c_str());

	for( int idx = 0 ; idx < (int)this->v_ws_handle_list.size() ; idx++ ) {
		if(    get<0>(this->v_ws_handle_list.at(idx))->get_uri_name().compare(_uri) == 0
			&& get<0>(this->v_ws_handle_list.at(idx))->get_ip_addr().compare(_ip_addr) == 0 ) {
			get<0>(this->v_ws_handle_list.at(idx))->send(_cmd_id, _data);
			break;
		}
	}

	return ;
}

bool WS_ClientHandler::is_created_handler(string _ip_addr, string _uri) {
	for( int idx = 0 ; idx < (int)this->v_ws_handle_list.size() ; idx++ ) {
		if(    get<0>(this->v_ws_handle_list.at(idx))->get_uri_name().compare(_uri) == 0
		    && get<0>(this->v_ws_handle_list.at(idx))->get_ip_addr().compare(_ip_addr) == 0 ) {
			return true;
		}
	}

	return false;
}

int WS_ClientHandler::get_index_handler(string _ip_addr, string _uri) {
	for( int idx = 0 ; idx < (int)this->v_ws_handle_list.size() ; idx++ ) {
		if(    get<0>(this->v_ws_handle_list.at(idx))->get_uri_name().compare(_uri) == 0
		    && get<0>(this->v_ws_handle_list.at(idx))->get_ip_addr().compare(_ip_addr) == 0 ) {
			return idx;
		}
	}

	return -1;
}

void WS_ClientHandler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;

	fprintf(stderr, "WS_ClientHandler::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);

	return ;
}

void WS_ClientHandler::set_debug_print(void) {
	this->is_debug_print = true;

	this->print_debug_info("set_debug_print() is set on\n");

	return ;
}

void WS_ClientHandler::set_event_handler(typeEventHandler _funcPtr) {
	this->print_debug_info("set_event_handler() set event function\n");
	this->funcEventHandler = _funcPtr;

	return ;
}


NetworkHandler::NetworkHandler(bool _is_debug_print) {
	this->is_debug_print = _is_debug_print;

	this->print_debug_info("NetworkHandler() create instance\n");

	return ;
}

NetworkHandler::~NetworkHandler(void) {
	this->print_debug_info("NetworkHandler() instance destructed\n");

	return ;
}

void NetworkHandler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;

	fprintf(stderr, "NetworkHandler::");
	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);

	return ;
}

void NetworkHandler::set_debug_print(void) {
	this->is_debug_print = true;

	this->print_debug_info("set_debug_print() is set on\n");

	return ;
}

unsigned short NetworkHandler::checksum(void *_data, int _length) {
	unsigned short *p_data;
	unsigned short result;
	unsigned int   sum = 0;

	p_data = (unsigned short *)_data;

	for( sum = 0 ; _length > 1 ; _length -= 2 ) {
		sum += *p_data++;
	}

	if ( _length == 1 ) {
		sum += *(unsigned char*)p_data;
	}

	sum  = (sum >> 16) + (sum & 0xFFFF);
	sum += (sum >> 16);
	result = ~sum;

	return result;
}

bool NetworkHandler::icmp(string _address) {
	const int sock_value = 255;
	int socket_fd;
	int num_sequence = 1;

	int idx;
	int pid = getpid();

	struct packet pckt;
	struct sockaddr_in r_addr;
	struct hostent *hname;
	struct sockaddr_in addr_ping,*addr;


	struct protoent *t_proto = NULL;
	int size_packet_msg = sizeof(pckt.msg);
	socklen_t len;

	t_proto = getprotobyname("ICMP");
	hname = gethostbyname(_address.c_str());

	bzero(&addr_ping, sizeof(addr_ping));
	addr_ping.sin_family = hname->h_addrtype;
	addr_ping.sin_port   = 0;
	addr_ping.sin_addr.s_addr = *(long*)hname->h_addr;

	addr = &addr_ping;

	if( (socket_fd = socket(PF_INET, SOCK_RAW, t_proto->p_proto)) < 0 ) {
		this->print_debug_info("NetworkHandler() socket open failed : [%02d] %s\n", errno, strerror(errno));
		return false;
	}

	if( setsockopt(socket_fd, SOL_IP, IP_TTL, &sock_value, sizeof(sock_value)) != 0) {
		this->print_debug_info("NetworkHandler() set TTL option failed : [%02d] %s\n", errno, strerror(errno));
		return false;
	}

	if ( fcntl(socket_fd, F_SETFL, O_NONBLOCK) != 0 ) {
		this->print_debug_info("NetworkHandler() request nonblocking I/O failed : [%02d] %s\n", errno, strerror(errno));
		return false;
	}

	for( int loop_cnt = 0 ; loop_cnt < COUNT_CHECK_LOOP ; loop_cnt++ ) {
		len = sizeof(r_addr);

		if ( recvfrom(socket_fd, &pckt, sizeof(pckt), 0x00, (struct sockaddr*)&r_addr, &len) > 0 ) {
			close(socket_fd);
			return true;
		}

		bzero(&pckt, sizeof(pckt));
		pckt.hdr.type = ICMP_ECHO;
		pckt.hdr.un.echo.id = pid;

		for( idx = 0; idx < size_packet_msg - 1 ; idx++ ) {
			pckt.msg[idx] = idx + '0';
		}

		pckt.msg[idx] = 0;
		pckt.hdr.un.echo.sequence = num_sequence++;
		pckt.hdr.checksum = this->checksum(&pckt, sizeof(pckt));

		if( sendto(socket_fd, &pckt, sizeof(pckt), 0, (struct sockaddr*)addr, sizeof(*addr)) <= 0 ) {
			this->print_debug_info("NetworkHandler() sendto failed : [%02d] %s\n", errno, strerror(errno));
		}

		usleep(TIME_LOOP_WAIT);
	}

	close(socket_fd);

	return false;
}


bool NetworkHandler::is_device_alive(string _address) {
	this->t_mutex.lock();

	JsonParser json_parser;
	string str_device_status = json_parser.read_file("/tmp/device_stat");
	json_parser.parse(str_device_status);

	string str_list_alive = json_parser.select("/alive");
	json_parser.replace_all(str_list_alive, "\\", "");
	json_parser.replace_all(str_list_alive, "[", "");
	json_parser.replace_all(str_list_alive, "]", "");
	json_parser.replace_all(str_list_alive, "\"", "");
	
	vector<string> v_arr_alive = json_parser.split(str_list_alive, ",");
	for( int idx = 0 ; idx < (int)v_arr_alive.size() ; idx++ ) {
		if( _address.compare(v_arr_alive.at(idx)) == 0 ) {
			
			this->t_mutex.unlock();
			return true;	
		}
	}
	
	this->t_mutex.unlock();
	return false;
}
