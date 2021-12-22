#!/bin/bash

EXEC="mdnsd"
EXEC_PATH="/opt/interm/bin/"
PATH_EXEC_FILE=$EXEC_PATH$EXEC

echo "Starting "$PATH_EXEC_FILE

# 프로세스 자체 백그라운드 실행
$PATH_EXEC_FILE
