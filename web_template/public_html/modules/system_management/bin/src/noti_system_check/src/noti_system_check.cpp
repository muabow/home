#include <stdio.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>
#include <sys/select.h>
#include <fcntl.h>
#include <stdlib.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <arpa/inet.h>
#include <netinet/in.h>
#include <netinet/tcp.h>

#include "api_web_log.h"

#define NUM_EXT_PORT                   	3000
#define NUM_GATEWAY_PORT                2100
#define STR_CLIENT_IP_ADDR              "127.0.0.1"

#define PATH_SYSTEM_CHECK_FLAG			"/opt/interm/system_check"
#define PATH_SYSTEM_CHECK_WEB			"/opt/interm/system_check_web"

#define ORDER_CMD_SYTEM_CHECK			1

struct ORDER_PACKET {
	char    cmd;
	char    rsvd[3];
	int     bodyLen;
	char    *data;
} typedef ORDER_PACKET_t;


/*****************************
FUNC : NotiSystemCheck()
DESC : Order function - get server alive
 ******************************/
void NotiSystemCheck(char _cmd, int _socketFd) {
	char tmpMsg[128];
	ORDER_PACKET_t  tSendPacket;

	memset(&tSendPacket, 0x00, sizeof(tSendPacket));
	memset(&tmpMsg,      0x00, sizeof(tmpMsg));

	sprintf(tmpMsg, "{\"type\":\"%d\", \"data\":{\"stat\":\"%d\"}}", _cmd, true);

	if( (tSendPacket.data = (char *)malloc((strlen(tmpMsg) + 1) * sizeof(char))) == NULL ) {
		fprintf(stderr, "NotiSystemCheck() malloc() data failed : [%02d] %s\n", errno, strerror(errno));

		return ;
	}

	tSendPacket.cmd = _cmd;
	strcpy(tSendPacket.data, tmpMsg);
	tSendPacket.bodyLen = strlen(tSendPacket.data);
	tSendPacket.data[tSendPacket.bodyLen] = '\0';

	if( send(_socketFd, &tSendPacket, sizeof(tSendPacket) - sizeof(tSendPacket.data), MSG_DONTWAIT) < 0 ) {
		fprintf(stderr, "NotiSystemCheck() send() head failed : [%02d] %s\n", errno, strerror(errno));

		free(tSendPacket.data);
		return ;
	}

	if( send(_socketFd, tSendPacket.data, tSendPacket.bodyLen, MSG_DONTWAIT) < 0 ) {
		fprintf(stderr, "NotiSystemCheck() send() body failed : [%02d] %s\n", errno, strerror(errno));

		free(tSendPacket.data);
		return ;
	}

	free(tSendPacket.data);

	// create system check file flag
	int fp = open(PATH_SYSTEM_CHECK_FLAG, O_RDWR|O_CREAT, 0777);
    close(fp);
    
    fp = open(PATH_SYSTEM_CHECK_WEB, O_RDWR|O_CREAT, 0777);
    close(fp);
	
    // write log
    WebLogHandler logger("system_management");
	logger.info("{STR_SYSTEM_CHECK_BEGIN}");
	
	return ;
}

/*****************************
FUNC : SendToWebBlock()
DESC : Order function - send to web ib
 ******************************/
bool SendToWebBlock(int _cmdId) {
	int     clientSockFd;
	int     extId = NUM_EXT_PORT;
	char    tmpMsg[128];

	struct sockaddr_in  tClientAddr;

	ORDER_PACKET_t  tSendPacket;

	memset(&tSendPacket, 0x00, sizeof(tSendPacket));
	memset(&tmpMsg,      0x00, sizeof(tmpMsg));

	if( (clientSockFd = socket(AF_INET, SOCK_STREAM, 0)) < 0 ) {
		fprintf(stderr, "SendToWebBlock() socket() failed : [%02d] %s\n", errno, strerror(errno));

		return false;
	}

	memset(&tClientAddr, 0x00, sizeof(tClientAddr));
	tClientAddr.sin_family     = AF_INET;
	tClientAddr.sin_port       = htons(NUM_GATEWAY_PORT);
	tClientAddr.sin_addr.s_addr= inet_addr(STR_CLIENT_IP_ADDR);

	if( connect(clientSockFd, (struct sockaddr*)&tClientAddr, sizeof(tClientAddr)) < 0 ) {
		fprintf(stderr, "SendToWebBlock() connect() failed : [%02d] %s\n", errno, strerror(errno));

		close(clientSockFd);
		return false;
	}

	if( send(clientSockFd, &extId, sizeof(extId), 0) < 0 ) {
		fprintf(stderr, "SendToWebBlock() send() port failed : [%02d] %s\n", errno, strerror(errno));

		close(clientSockFd);
		return false;
	}
	// writeLog.info(writeLog, "Send gateway : %d\n", _cmdId);
	switch( _cmdId ) {
		case ORDER_CMD_SYTEM_CHECK :
			NotiSystemCheck(ORDER_CMD_SYTEM_CHECK, clientSockFd);
			break;
	}

	close(clientSockFd);

	return true;
} // end of SendToWebBlock()

int main(int _argc, char *_argv[]) {
	SendToWebBlock(ORDER_CMD_SYTEM_CHECK);
	
	fprintf(stderr, "process termed\n");

	return 0;
}
