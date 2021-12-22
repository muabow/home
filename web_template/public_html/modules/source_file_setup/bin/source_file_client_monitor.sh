#!/bin/bash

EXEC="source_file_client"
EXEC_PATH="/opt/interm/public_html/modules/source_file_setup/bin/"
PATH_EXEC_FILE=$EXEC_PATH$EXEC
NICE_EXEC="nice -n -3 "
echo "Starting "$PATH_EXEC_FILE

killall $PATH_EXEC_FILE

while [ 1 ]
   do
        pid=`ps -ef | grep -w $EXEC | grep -v 'grep' | awk '{print $2}'`
		if [ -z $pid ]; then
			if [ -x $PATH_EXEC_FILE ]; then
				$NICE_EXEC $PATH_EXEC_FILE
			fi
		fi
		sleep 1
   done
