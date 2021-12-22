#include <stdio.h>      
#include <sys/socket.h> 
#include <arpa/inet.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <errno.h>

#define NUM_DFLT_BC_PORT        7593
#define NUM_DFLT_MAGIC_PKT      0x7593

struct DISCOVER_DATA {
	int     magic_pkt;      // 0x7593
	char    s_dev_id[32];
	char    d_dev_id[32];
	short   svc_type;       // 0x01(IP change), 0x10(change noti)
	short   data_length;
	char    data[1024];
} typedef DISCOVER_DATA_t;

int g_debug_print = 1;

int init_socket(int _port) {
    int     sockFd;
    int     bcPerm = 1;
    struct  sockaddr_in tBcSockAddr; /* Broadcast Address */

    if( (sockFd = socket(PF_INET, SOCK_DGRAM, IPPROTO_UDP)) < 0 ) {
        printf("Init_socket() socket() failed : [%02d] %s\n", errno, strerror(errno));

        return -1;
    }
    if( setsockopt(sockFd, SOL_SOCKET, SO_BROADCAST, (void *) &bcPerm, sizeof(bcPerm)) < 0 ) {
        printf("init_socket() setsockopt() failed : [%02d] %s\n", errno, strerror(errno));
    }

    memset(&tBcSockAddr, 0x00, sizeof(tBcSockAddr));
    tBcSockAddr.sin_family      = AF_INET;;
    tBcSockAddr.sin_addr.s_addr = htonl(INADDR_ANY);
    tBcSockAddr.sin_port        = htons(_port);

    if( bind(sockFd, (struct sockaddr *) &tBcSockAddr, sizeof(tBcSockAddr)) < 0 ) {
        printf("Init_socket() bind() failed : [%02d] %s\n", errno, strerror(errno));

        return -1;
    }

    return sockFd;
}

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


int main(int _argc, char *_argv[]) {
	int sockFd, recvSockFd;
	struct sockaddr_in tBroadcastAddr;

	char	*d_dev_id;
	char	*net_info;	

	int broadcastPermission;

	if( _argc < 2 ) {
		fprintf(stderr,"Usage : %s <dest_device_id>\n", _argv[0]);

		return -1;
	}

	d_dev_id = _argv[1];
	net_info = "{\"tabStat\":\"primary\",\"hostname\":\"IM-AOE-ONB-TEST\",\"location\":\"우리집 안방\",\"network_primary\":{\"view\":\"enabled\",\"use\":\"enabled\",\"dhcp\":\"off\",\"mac_address\":\"00:1d:1d:02:17:c6\",\"ip_address\":\"192.168.46.12\",\"subnetmask\":\"255.255.254.0\",\"gateway\":\"192.168.46.1\",\"dns_server_1\":\"8.8.8.8\",\"dns_server_2\":\"8.8.4.4\"},\"network_secondary\":{\"view\":\"disabled\",\"use\":\"disabled\",\"dhcp\":\"on\",\"ip_address\":\"192.168.2.99\",\"mac_address\":\"\",\"subnetmask\":\"255.255.255.0\",\"gateway\":\"192.168.2.1\",\"dns_server_1\":\"8.8.8.8\",\"dns_server_2\":\"8.8.4.4\"},\"network_bonding\":{\"view\":\"disabled\",\"use\":\"disabled\",\"mac_address\":\"3a:b3:00:0d:46:db\",\"ip_address\":\"192.168.1.254\",\"subnetmask\":\"255.255.255.0\",\"gateway\":\"192.168.1.2\"}}";

	if( (sockFd = socket(PF_INET, SOCK_DGRAM, IPPROTO_UDP)) < 0 ) {
		printf("socket() failed : [%02d] %s\n", errno, strerror(errno));

		return -1;
	}

	broadcastPermission = 1;
	if( setsockopt(sockFd, SOL_SOCKET, SO_BROADCAST, (void *)&broadcastPermission, sizeof(broadcastPermission)) < 0 ) {
		printf("setsockopt() failed : [%02d] %s\n", errno, strerror(errno));
	}

	memset(&tBroadcastAddr, 0x00, sizeof(tBroadcastAddr));
	tBroadcastAddr.sin_family 		= AF_INET;
	tBroadcastAddr.sin_addr.s_addr	= inet_addr("255.255.255.255");
	tBroadcastAddr.sin_port 		= htons(NUM_DFLT_BC_PORT);

	DISCOVER_DATA_t	tData;
	DISCOVER_DATA_t	tRecvData;

	recvSockFd = init_socket(NUM_DFLT_BC_PORT);

	memset(&tRecvData, 0x00, sizeof(tRecvData));

	tData.magic_pkt 	= NUM_DFLT_MAGIC_PKT;
	get_device_id(tData.s_dev_id);
	strcpy(tData.d_dev_id, d_dev_id);
	tData.svc_type		= 0x01;
	tData.data_length	= strlen(net_info);
	memset(tData.data, 0x00, sizeof(tData.data));
	strcpy(tData.data, net_info);
	
	printf("- send data information\n");
	printf("magic_pkt    : [0x%x]\n",	tData.magic_pkt);
	printf("s_dev_id     : [%.32s]\n",	tData.s_dev_id);
	printf("d_dev_id     : [%.32s]\n",	tData.d_dev_id);
	printf("svc_type     : [0x%02x]\n",	tData.svc_type);
	printf("data_length  : [%d]\n",		tData.data_length);
	printf("data         : [%s]\n\n",	tData.data);

	if( sendto(sockFd, &tData, sizeof(tData), 0, (struct sockaddr *)&tBroadcastAddr, sizeof(tBroadcastAddr)) < 0 ) {
		printf("sendto() failed : [%02d] %s\n", errno, strerror(errno));

		return -1;
	}

	while( 1 ) {
		if( recvfrom(recvSockFd, &tRecvData, sizeof(tRecvData), 0, NULL, 0) < 0 ) {
			printf("recvfrom() failed : [%02d] %s\n", errno, strerror(errno));
			break;
		}
		
		if( check_header(&tRecvData) < 0 ) {
            continue;
        }

		printf("- recv data information\n");
		printf("magic_pkt    : [0x%x]\n",	tRecvData.magic_pkt);
		printf("s_dev_id     : [%.32s]\n",	tRecvData.s_dev_id);
		printf("d_dev_id     : [%.32s]\n",	tRecvData.d_dev_id);
		printf("svc_type     : [0x%02x]\n",	tRecvData.svc_type);
		printf("data_length  : [%d]\n",		tRecvData.data_length);
		printf("data         : [%s]\n\n",	tRecvData.data);

		break;
	}

	close(sockFd);
}
