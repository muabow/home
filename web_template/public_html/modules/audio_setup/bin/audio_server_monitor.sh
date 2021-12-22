#!/bin/bash

EXEC="audio_server"

export LD_LIBRARY_PATH=/opt/interm/usr/lib

EXEC_PATH=`dirname $(realpath $0)`
PATH_EXEC_FILE=$EXEC_PATH"/"$EXEC
NICE_EXEC="nice -n -3 "

echo "Starting "$PATH_EXEC_FILE

killall $PATH_EXEC_FILE

while [ 1 ]
   do
        pid=`ps -ef | grep -w $EXEC | grep -v 'grep' | grep -v 'monitor' | awk '{print $2}'`
		if [ -z $pid ]; then
			if [ -x $PATH_EXEC_FILE ]; then
				$NICE_EXEC $PATH_EXEC_FILE
			fi
		fi
		sleep 1
   done
