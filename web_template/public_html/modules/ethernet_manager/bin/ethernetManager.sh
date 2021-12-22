#!/bin/bash

path="/opt/interm/bin"
daemon="ethernetMonitor"

echo "Starting ${path}/${daemon}"

function routeAdd() {
	local dev=$1
#	echo "Route Add ${dev}"

	if [ `route -n | grep ${dev} | grep "224.0.0.0" | wc -l` -eq 0 ]; then
		echo "Route Add ${dev} : run"
		route add -net 224.0.0.0 netmask 240.0.0.0 ${dev}
	fi
}

function routeDel() {
	local dev=$1
#	echo "Route Del ${dev}"

	count=5
	while [ `route -n | grep ${dev} | grep 224 | wc -l` -gt 0 -a ${count} -gt 0 ]; do
		echo "Route Del ${dev} : ${count}"
		route del -net 224.0.0.0 netmask 240.0.0.0 ${dev}
		count=`expr ${count} - 1`
	done
}



isBonding=`jq ".network_bonding.use" /opt/interm/public_html/modules/network_setup/conf/network_stat.json | grep -i "enabled" | wc -l`



eth0LinkPrev=`cat /sys/class/net/eth0/operstate`
if [ _${eth0LinkPrev} == _"up" ]; then
	eth0LinkPrev="down"
else
	eth0LinkPrev="up"
fi
eth0Static=`cat /etc/network/interfaces | grep static | grep eth0 | wc -l`


eth1LinkPrev=`cat /sys/class/net/eth1/operstate 2>/dev/null`
if [ _${eth1LinkPrev} == _"up" ]; then
	eth1LinkPrev="down"
else
	eth1LinkPrev="up"
fi
eth1Static=`cat /etc/network/interfaces | grep static | grep eth1 | wc -l`

while [ 1 ] ; do
	### Bonding 인 경우 동작하지 않음
	if [ ${isBonding} -eq 1 ]; then
		sleep 2
		continue
	fi

	### Static 인 경우에만 제어 함
	
	eth0LinkCurrent=`cat /sys/class/net/eth0/operstate`
	if [ ${eth0Static} -eq 1 ]; then
#		echo eth0 Previous : ${eth0LinkPrev}
#		echo eth0 Current : ${eth0LinkCurrent}

		if [ _${eth0LinkCurrent} == _"down" ]; then
			/sbin/ifconfig eth0 0.0.0.0
			eth0LinkPrev=${eth0LinkCurrent}
		fi
		if [ _${eth0LinkPrev} != _${eth0LinkCurrent} -a _${eth0LinkCurrent} == _"up" ]; then
			/sbin/ifdown eth0; /sbin/ifup eth0
			eth0LinkPrev=${eth0LinkCurrent}
		fi
	fi
	if [ _${eth0LinkCurrent} == _"down" ]; then
		routeDel eth0
	else
		routeAdd eth0
	fi


	eth1LinkCurrent=`cat /sys/class/net/eth1/operstate 2>/dev/null`
	if [ _${eth1LinkCurrent} == _ ]; then
		sleep 2
		continue
	fi
	if [ ${eth1Static} -eq 1 ]; then
#		echo eth1 Previous : ${eth1LinkPrev}
#		echo eth1 Current : ${eth1LinkCurrent}

		if [ _${eth1LinkCurrent} == _"down" ]; then
			/sbin/ifconfig eth1 0.0.0.0
			eth1LinkPrev=${eth1LinkCurrent}
		fi
		if [ _${eth1LinkPrev} != _${eth1LinkCurrent} -a _${eth1LinkCurrent} == _"up" ]; then
			/sbin/ifdown eth1; /sbin/ifup eth1
			eth1LinkPrev=${eth1LinkCurrent}
		fi
	fi
	if [ _${eth1LinkCurrent} == _"down" ]; then
		routeDel eth1
	else
		routeAdd eth1
	fi

#	echo
	sleep 2
done

