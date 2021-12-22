#!/bin/bash

EXEC="ws_priority_interface"
FILE="ws_priority_interface.php"
EXEC_PATH="/opt/interm/bin/"
PATH_EXEC_FILE=$EXEC_PATH$FILE

echo "Starting "$PATH_EXEC_FILE

while [ 1 ]; do
	pid=`pgrep -x $EXEC`
	if [ -z $pid ]; then
		if [ -x $PATH_EXEC_FILE ]; then
			bash -c "/usr/bin/php "$PATH_EXEC_FILE
		fi
	fi
	sleep 1
done
