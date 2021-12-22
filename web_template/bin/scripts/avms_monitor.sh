#!/bin/bash

EXEC="avms-dev"
EXEC_PATH="/opt/interm/bin/"
PATH_EXEC_FILE=$EXEC_PATH$EXEC

echo "Starting "$PATH_EXEC_FILE

while [ 1 ]; do
	pid=`pgrep -x $EXEC`
	if [ -z $pid ]; then
		if [ -x $PATH_EXEC_FILE ]; then
			bash -c $PATH_EXEC_FILE
		fi
	fi
	sleep 1
done
