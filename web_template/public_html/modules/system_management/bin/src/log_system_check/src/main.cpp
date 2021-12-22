#include <stdio.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>
#include <fcntl.h>
#include <sys/types.h>
#include <sys/stat.h>

#include "api_web_log.h"

#define PATH_SYSTEM_CHECK_FLAG			"/opt/interm/system_check"

int main(int _argc, char *_argv[]) {
	struct stat   buffer;   
	
	if( stat(PATH_SYSTEM_CHECK_FLAG, &buffer) == 0 ) {
		// remove system check file flag
		remove(PATH_SYSTEM_CHECK_FLAG);
		
		// write log
		WebLogHandler logger("system_management");
		logger.info("{STR_SYSTEM_CHECK_END}");
	
		fprintf(stderr, "system checked\n");
	}
			  
	
	fprintf(stderr, "process termed\n");

	return 0;
}
