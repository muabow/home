#!/bin/bash

if [ -z "$1" ]; then
	echo "usage: ${0} ./audio_info source_filename "
	exit
fi

input=$1

filename=$(basename -- "$input")

duration=$(avprobe -show_format "$input" -v quiet | sed -n 's/duration=//p')
echo $duration
