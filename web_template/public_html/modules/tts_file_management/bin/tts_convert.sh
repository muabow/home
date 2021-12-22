#!/bin/bash

if [ -z "$1" ]; then
	echo "usage: ${0} ./tts_convert source_filename filepath"
	exit
fi


input=$1
filepath=$2

filename=$(basename -- "$input")
extension="${filename##*.}"
output="${filename%.*}"

duration=$(avprobe -show_format "$input" -v quiet | sed -n 's/duration=//p')
# echo "duration=$duration"
echo $duration

avconv -y -i "$input" $time -ar 44100 -ac 1 -f s16le -acodec pcm_s16le "${filepath}/44100_${output}_1.pcm"
avconv -y -i "$input" $time -ar 44100 -ac 2 -f s16le -acodec pcm_s16le "${filepath}/44100_${output}_2.pcm"
avconv -y -i "$input" $time -ar 48000 -ac 1 -f s16le -acodec pcm_s16le "${filepath}/48000_${output}_1.pcm"
avconv -y -i "$input" $time -ar 48000 -ac 2 -f s16le -acodec pcm_s16le "${filepath}/48000_${output}_2.pcm"

#validation
#$ sudo aplay -f S16_LE -c 1 -r 48000 48000_${output}.pcm
#$ sudo aplay -f S16_LE -c 1 -r 44100 44100_${output}.pcm
