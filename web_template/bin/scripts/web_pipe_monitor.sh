#!/bin/bash

arc=`lscpu | grep Architecture | grep arm | wc -l`

EXEC="web_pipe"
FILE="web_pipe.php"
EXEC_PATH="/opt/interm/bin/"

if [ $arc -ge 1 ]; then
    # ARM type
    PATH_EXEC_FILE=$EXEC_PATH$EXEC
    EXEC_CMD=$PATH_EXEC_FILE
else
    # X86 type
    PATH_EXEC_FILE=$EXEC_PATH$FILE
    EXEC_CMD=`bash -c "/usr/bin/php "$PATH_EXEC_FILE`
fi

echo "Starting "$PATH_EXEC_FILE

while [ 1 ]; do
    pid=`pgrep -x $EXEC`
    if [ -z $pid ]; then
        if [ -x $PATH_EXEC_FILE ]; then
            $EXEC_CMD
        fi
    fi
    sleep 1
done
