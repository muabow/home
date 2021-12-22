#!/bin/bash

arc=`lscpu | grep Architecture | grep arm | wc -l`

if [ $arc -ge 1 ]; then
    # ARM type
    EXEC="haproxy"
else
    # X86 type
    EXEC="haproxy_x86"
fi

EXEC_PATH="/opt/interm/bin/"
PATH_EXEC_FILE=$EXEC_PATH$EXEC

echo "Starting "$PATH_EXEC_FILE

while [ 1 ]; do
	pid=`pgrep -x $EXEC`
	if [ -z $pid ]; then
		if [ -x $PATH_EXEC_FILE ]; then
			bash -c $PATH_EXEC_FILE" -f /opt/interm/conf/haproxy.cfg"
		fi
	fi
	sleep 1
done
