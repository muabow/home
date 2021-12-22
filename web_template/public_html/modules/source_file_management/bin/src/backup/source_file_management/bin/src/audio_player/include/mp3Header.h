#ifndef __MP3HERDER_H__
#define __MP3HERDER_H__

#define E_ERROR			-1
#define E_SUCCESS		0

typedef struct MP3_HEADER {
	int     version;
	int     layer;
	int     errp;
	int     bitrate;
	unsigned 	int     freq;
	int     pad;
	int     priv;
	int     mode;
	int     modex;
	int     copyright;
	int     original;
	int     emphasis;
} MP3_HEADER_t;

MP3_HEADER_t tMp3Header;

int GetMp3FrameInfo(char *_mp3Frame);


const int gSampleRates[4] = {44100, 48000, 32000};
const int gBitRates[16]   = {0,  32000,  40000,  48000,  56000,  64000,  80000,  96000,
	112000, 128000, 160000, 192000, 224000, 256000, 320000, 0};

#endif
