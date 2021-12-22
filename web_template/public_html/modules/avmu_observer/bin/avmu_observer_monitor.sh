#!/bin/bash

path=`dirname $0 | xargs readlink -f`
daemon=`basename $0 | sed 's/_monitor.sh//g'`

echo "Starting ${path}/${daemon}"

killall ${daemon}
export LD_LIBRARY_PATH=/opt/interm/usr/lib


while [ 1 ] ; do 
	pid=`ps -ef | grep -w ${daemon} | grep -v 'grep' | grep -v '_monitor.sh' | awk '{print $2}'`
	if [ _"${pid}" == _ ]; then
		if [ -x ${path}/${daemon} ]; then
			${path}/${daemon} -v
		fi
	fi
	sleep 1
done
