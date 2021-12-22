#!/bin/bash

EXEC="bonjour-register"
EXEC_PATH="/opt/interm/bin/"
PATH_EXEC_FILE=$EXEC_PATH$EXEC
NICE_EXEC="nice -n -3 "

export LD_LIBRARY_PATH=/opt/interm/usr/lib

EXEC_COMMAND="${NICE_EXEC} ${PATH_EXEC_FILE}"

echo "Starting "$EXEC

killall $PATH_EXEC_FILE

while [ 1 ]
	do
        pid=`ps -ef | grep -w $EXEC | grep -v 'grep' | awk '{print $2}'`
		if [ -z $pid ]; then
			if [ -x $PATH_EXEC_FILE ]; then
				$EXEC_COMMAND _interm._tcp 80 /opt/interm/public_html/modules/network_setup/conf/bonjour.service
			fi
		fi
		sleep 1
	done
