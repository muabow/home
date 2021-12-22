#include <stdio.h>
#include <sys/socket.h>
#include <arpa/inet.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h> 
#include <errno.h>

#define NUM_DFLT_BC_PORT		7593
#define NUM_DFLT_MAGIC_PKT		0x7593

struct DISCOVER_DATA {
	int		magic_pkt;		// 0x7593
	char	s_dev_id[32];
	char	d_dev_id[32];
	short	svc_type;		// 0x01(IP change), 0x10(change noti)
	short	data_length;
	char	data[1024];
} typedef DISCOVER_DATA_t;


int	g_debug_print = 1;


void get_device_id(char *_devId) {
	FILE *fp;

	if( (fp = fopen("/etc/machine-id", "r")) == NULL ) {
		printf("get_device_id() fopen() failed : [%02d] %s\n", errno, strerror(errno));	

		strcpy(_devId, "");
	}

	fgets(_devId, 1024, fp);

	_devId[strlen(_devId) -1] = '\0';

	fclose(fp);

	return ;
}

int	init_socket(int _port) {
	int 	sockFd;
	int		bcPerm = 1;
	struct 	sockaddr_in tBcSockAddr; /* Broadcast Address */

	if( (sockFd = socket(PF_INET, SOCK_DGRAM, IPPROTO_UDP)) < 0 ) {
		printf("Init_socket() socket() failed : [%02d] %s\n", errno, strerror(errno));

		return -1;
	}
	if( setsockopt(sockFd, SOL_SOCKET, SO_BROADCAST, (void *) &bcPerm, sizeof(bcPerm)) < 0 ) { 
		printf("init_socket() setsockopt() failed : [%02d] %s\n", errno, strerror(errno));
	}

	memset(&tBcSockAddr, 0x00, sizeof(tBcSockAddr));
	tBcSockAddr.sin_family 		= AF_INET;;
	tBcSockAddr.sin_addr.s_addr = htonl(INADDR_ANY);
	tBcSockAddr.sin_port 		= htons(_port);

	if( bind(sockFd, (struct sockaddr *) &tBcSockAddr, sizeof(tBcSockAddr)) < 0 ) { 
		printf("Init_socket() bind() failed : [%02d] %s\n", errno, strerror(errno));

		return -1;
	}

	return sockFd;
}

int check_header(DISCOVER_DATA_t *_tData) {
	static char deviceId[32] = "";

	if( strcmp(deviceId, "") == 0 ) {
		get_device_id(deviceId);
	}
	
	if( _tData->magic_pkt != NUM_DFLT_MAGIC_PKT ) {
		if( g_debug_print ) printf("check_header() invalid magic : [0x%x]\n", _tData->magic_pkt);

		return -1;
	}

	if( strncmp(_tData->d_dev_id, deviceId, sizeof(deviceId)) != 0 ) { 
		if( g_debug_print ) printf("check_header() not matched device_id : [%s/%s] \n", _tData->d_dev_id, deviceId);

		return -1;
	}

	return 0;
}

int send_data(DISCOVER_DATA_t *_tData, int _port) {
	int sockFd; 
	int bcPerm = 1;

	struct sockaddr_in tBcSockAddr;

	DISCOVER_DATA_t tData;

	tData.magic_pkt		= _tData->magic_pkt;
	tData.svc_type		= 0x10;
	tData.data_length	= strlen(_tData->data);
	strncpy(tData.s_dev_id, _tData->d_dev_id, sizeof(tData.s_dev_id));
	strncpy(tData.d_dev_id, _tData->s_dev_id, sizeof(tData.d_dev_id));
	strcpy(tData.data, _tData->data);

	if( (sockFd = socket(PF_INET, SOCK_DGRAM, IPPROTO_UDP)) < 0 ) {
		printf("send_data() socket() failed : [%02d] %s\n", errno, strerror(errno));

		return -1;
	}

	if( setsockopt(sockFd, SOL_SOCKET, SO_BROADCAST, (void *) &bcPerm, sizeof(bcPerm)) < 0 ) {
		printf("send_data() setsockopt() failed : [%02d] %s\n", errno, strerror(errno));
	}

	memset(&tBcSockAddr, 0, sizeof(tBcSockAddr)); 
	tBcSockAddr.sin_family = AF_INET;
	tBcSockAddr.sin_addr.s_addr = inet_addr("255.255.255.255");
	tBcSockAddr.sin_port = htons(_port);

	if( g_debug_print ) {
		printf("- recv data information\n");
		printf("# magic_pkt        : [0x%d]\n",		tData.magic_pkt);
		printf("# s_dev_id         : [%.32s]\n",	tData.s_dev_id);
		printf("# d_dev_id         : [%.32s]\n",	tData.d_dev_id);
		printf("# svc_type         : [0x%02x]\n",	tData.svc_type);
		printf("# data_length      : [%d]\n", 		tData.data_length);
		printf("# data             : [%s]\n\n",		tData.data);
	}

	if( sendto(sockFd, &tData, sizeof(tData), 0, (struct sockaddr *) &tBcSockAddr, sizeof(tBcSockAddr)) < 0 ) {
		printf("send_data() sendto() failed : [%02d] %s\n", errno, strerror(errno));

		close(sockFd);
		return -1;
	}

	close(sockFd);

	return 0;
}

void exec_network_info(char *_type, char *_input_data, char *_output_data) {
	char	exec_data[4096];
	FILE	*fp;

	sprintf(exec_data, "/usr/bin/php /opt/interm/public_html/modules/network_setup/bin/change_network_info.php '%s' '%s'", _type, _input_data);

	fp = popen(exec_data, "r");
	fgets(_output_data, 2048, fp);
	pclose(fp);
	
	return ;
}


int main(int _argc, char *_argv[]) {
	int		port = NUM_DFLT_BC_PORT;
	int 	sockFd;
	int 	rc;
	char	read_data[2048];

	DISCOVER_DATA_t tData;

	if( _argc == 2 ) {
		port = atoi(_argv[1]);
		printf("- Change port : [%d] -> [%d]\n", NUM_DFLT_BC_PORT, port);
	}

	if( (sockFd = init_socket(port)) < 0 ) {

		return -1;
	}

	while( 1 ) {
		if( (rc = recvfrom(sockFd, &tData, sizeof(tData), 0, NULL, 0)) < 0 ) {
			printf("recv_data() recvfrom() header failed : [%02d] %s\n", errno, strerror(errno));

			continue;
		}

		if( check_header(&tData) < 0 ) {
			continue;
		}

		if( g_debug_print ) {
			printf("- recv data information\n");
			printf("# recv legnth      : [%d]\n",   	rc);	
			printf("# magic_pkt        : [0x%d]\n",		tData.magic_pkt);
			printf("# s_dev_id         : [%.32s]\n",	tData.s_dev_id);
			printf("# d_dev_id         : [%.32s]\n",	tData.d_dev_id);
			printf("# svc_type         : [0x%02x]\n",	tData.svc_type);
			printf("# data_length      : [%d]\n", 		tData.data_length);
			printf("# data             : [%s]\n\n",		tData.data);
		}
		
		switch( tData.svc_type ) {
			case 0x01	: // IP change
				printf("- service type : IP change\n");

				exec_network_info("check", tData.data, read_data);
				
				if( strcmp(read_data, "OK") != 0 ) {
					strcpy(tData.data, read_data);
					send_data(&tData, port);
					
					break;
				}

				exec_network_info("change", tData.data, read_data);
				
				strcpy(tData.data, read_data);
				send_data(&tData, port);

				system("sh /opt/interm/bin/reboot.sh");

				break;

			default		:
				printf("- Unknown service type : [0x%02d]\n", tData.svc_type);
				break;
		}
	}

	close(sockFd);

	return 0;
}
