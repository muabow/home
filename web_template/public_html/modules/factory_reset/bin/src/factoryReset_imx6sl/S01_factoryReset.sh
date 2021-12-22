#!/bin/sh

### Run factoryReset App.
export LD_LIBRARY_PATH=/opt/interm/usr/lib
if [ -f /usr/bin/screen ]; then
	/usr/bin/screen -S factoryReset -d -m /opt/interm/bin/factoryReset_monitor.sh &
else
	/opt/interm/bin/factoryReset_monitor.sh &
fi

