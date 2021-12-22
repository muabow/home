#include <stdio.h>
#include <string.h>
#include <math.h>

#include "mp3Header.h"

/*****************************
FUNC : GetMp3FrameInfo()
DESC : get mp3 frame info
 ******************************/
int GetMp3FrameInfo(char *_mp3Frame) {

	tMp3Header.version = (_mp3Frame[1] & 0x08) >> 3;
	tMp3Header.layer = 4 - ((_mp3Frame[1] & 0x06) >> 1);
	tMp3Header.errp = (_mp3Frame[1] & 0x01);

	tMp3Header.bitrate = gBitRates[(_mp3Frame[2] & 0xf0) >> 4];
	tMp3Header.freq = gSampleRates[(_mp3Frame[2] & 0x0c) >> 2];
	tMp3Header.pad = (_mp3Frame[2] & 0x02) >> 1;
	tMp3Header.priv = (_mp3Frame[2] & 0x01);

	tMp3Header.mode = (_mp3Frame[3] & 0xc0) >> 6;
	tMp3Header.modex = (_mp3Frame[3] & 0x30) >> 4;
	tMp3Header.copyright = (_mp3Frame[3] & 0x08) >> 3;
	tMp3Header.original = (_mp3Frame[3] & 0x04) >> 2;
	tMp3Header.emphasis = (_mp3Frame[3] & 0x03);

	fprintf(stderr, "MP3 Frame information\n");
	fprintf(stderr, "  version   = %x \n",tMp3Header.version);
	fprintf(stderr, "  layer     = %x \n",tMp3Header.layer);
	fprintf(stderr, "  errp      = %x \n",tMp3Header.errp);
	fprintf(stderr, "  bitrate   = %d \n",tMp3Header.bitrate);
	fprintf(stderr, "  freq      = %d \n",tMp3Header.freq);
	fprintf(stderr, "  pad       = %x \n",tMp3Header.pad);
	fprintf(stderr, "  priv      = %x \n",tMp3Header.priv);
	fprintf(stderr, "  mode      = %x \n",tMp3Header.mode);
	fprintf(stderr, "  modex     = %x \n",tMp3Header.modex);
	fprintf(stderr, "  copyright = %x \n",tMp3Header.copyright);
	fprintf(stderr, "  original  = %x \n",tMp3Header.original);
	fprintf(stderr, "  emphasis  = %x \n",tMp3Header.emphasis);


	return E_SUCCESS;
} // end of Getmp3FrameInfo()

