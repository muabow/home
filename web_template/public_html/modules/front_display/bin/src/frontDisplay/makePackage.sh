#!/bin/bash

COLOR_RED='\033[0;31m'
COLOR_GREEN='\033[0;32m'
COLOR_YELLOW='\033[0;33m'
COLOR_BLUE='\033[0;34m'
COLOR_MAGENTA='\033[0;35m'
COLOR_CYAN='\033[0;36m'
COLOR_RESET='\033[0m'

VERSION=`head -1 version`
BUILD_DATE=`date +%Y%m%d`
BUILD_TIME=`date +%H%M%S`

error() {
	local errorCode=1
	[ _$1 != _ ] && errorCode=$1 
	echo -e "${COLOR_RED}# Make Firmware : Error (${errorCode})${COLOR_RESET}"

	exit ${errorCode}
}


echo -e "${COLOR_CYAN}# Make Packages${COLOR_RESET}"
corenum=`cat /proc/cpuinfo | grep cores | wc -l`

make dependency_clean || error 1
make dist_clean || error 2
while read LINE; do
	if [ _"${LINE}" = _ -o _"${LINE:0:1}" = _"#" ] ; then
		continue
	fi

	oem=`echo ${LINE} | awk '{print $1}'`
	resource=`echo ${LINE} | awk '{print $2}'`

	echo -e "${COLOR_CYAN}================================================================================${COLOR_RESET}"
	make OEM=${oem} RESOURCE=${resource} VERSION=${VERSION} BUILD_DATE=${BUILD_DATE} BUILD_TIME=${BUILD_TIME} -j${corenum}
	[ $? -ne 0 ] && error 11
	make OEM=${oem} RESOURCE=${resource} VERSION=${VERSION} BUILD_DATE=${BUILD_DATE} BUILD_TIME=${BUILD_TIME} -j${corenum} dist
	[ $? -ne 0 ] && error 12
	echo
done < "OEM.lst"


echo -e "${COLOR_CYAN}================================================================================${COLOR_RESET}"
echo -e "${COLOR_CYAN}# Make Packages : Done${COLOR_RESET}"
echo

