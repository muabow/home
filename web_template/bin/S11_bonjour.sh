#!/bin/bash

if [ ! -f "/opt/interm/bin/mdnsd" ]; then
	echo "start & enable service : avahi-daemon"
	systemctl start avahi-daemon
	systemctl enable avahi-daemon
	exit
fi

# avahi-daemon service disable
echo "stop & disable service : avahi-daemon"
systemctl stop avahi-daemon
systemctl disable avahi-daemon

# mdns(bonjour) service enable
/opt/interm/bin/scripts/mdnsd_monitor.sh &

