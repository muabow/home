#include <stdio.h>
#include <math.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>
#include <sys/time.h>
#include "muteRelay.cpp"

#include   "mp3_dec_interface.h"

#define DEBUG_PRINT			false

#define TIME_SET_MUTE_RELAY	500000		// us

extern bool gMainTerm;
int		gMuteTime = 0;

void ClosePcm(void);
double DiffTime(struct timeval _x, struct timeval _y) {
	double x_ms , y_ms , diff;

	x_ms = (double)_x.tv_sec * 1000000 + (double)_x.tv_usec;
	y_ms = (double)_y.tv_sec * 1000000 + (double)_y.tv_usec;

	diff = (double)y_ms - (double)x_ms;

	return diff;
} // end of DiffTime()

// DiffTime() example
/*
   struct timeval before , after;
   gettimeofday(&before , NULL);
   gettimeofday(&after , NULL);
   printf("Total time elapsed : %.0lf us" , DiffTime(before, after));
*/

extern IPC_msgQueueFunc 	gMsgQueueFunc;

/*****************************
FUNC : DecodeMp3File()
DESC : Mp3 Decoder
 ******************************/
int DecodeMp3File(char *_fileName) {
	int numReqs;
	int idx;
	int pcmFlag = 0;
	int leftLength;
	int leftInLength;
	const int sizeThreshold = MAX_FRAME_SIZE;

	MP3D_UINT32 inBufLen;
	MP3D_UINT32 fileSize;

	FILE *pFileFd;

	MP3D_INT16	*outputBuffer;
	MP3D_UINT8  *inputBuffer;
	MP3D_UINT32 wordsReadBuffer;

	MP3D_RET_TYPE retval;
	MP3D_Decode_Params tDecParams;
	MP3D_Decode_Config *tDecConfig;
	MP3D_Mem_Alloc_Info_Sub *tMp3MemInfo;

	MP3D_UINT8 inputMp3Buffer[MP3D_INPUT_BUF_PUSH_SIZE];

	// time check
	bool    resetTimeFlag = false;
	int     nullCnt = 0;
	int     bufferCnt;
	int     muteTime;
	int     nullTime;
	int     recoverTime;
	struct  timeval muteBeginTime, muteEndTime;
	struct  timeval nullBeginTime, nullEndTime;


	// fprintf(stderr, "%s \n", mp3d_decode_versionInfo());

	if( (pFileFd = fopen(_fileName, "rb")) == NULL ) {
		fprintf(stderr, "[%s] file open failed : [%02d] %s \n", _fileName, errno, strerror(errno));

		return E_ERROR;
	}

	if( (tDecConfig = (MP3D_Decode_Config *)AllocFast(sizeof(MP3D_Decode_Config))) == ((void *)0) ) { 
		fprintf(stderr, "tDecConfig AllocFast failed \n");

		return E_ERROR;
	}

	if( (retval = mp3d_query_dec_mem(tDecConfig)) != MP3D_OK ) {
		fprintf(stderr, "mp3d_query_dec_tMp3memInfo failed \n");

		return E_ERROR;
	}

	numReqs = tDecConfig->mp3d_mem_info.mp3d_num_reqs;

	for(idx = 0 ; idx < numReqs ; idx++ ) {
		tMp3MemInfo = &(tDecConfig->mp3d_mem_info.mem_info_sub[idx]);

		if( tMp3MemInfo->mp3d_type == 1 ) {
			tMp3MemInfo->app_base_ptr = AllocFast(tMp3MemInfo->mp3d_size);
			memset(tMp3MemInfo->app_base_ptr, 0xfe, tMp3MemInfo->mp3d_size);

			if (tMp3MemInfo->app_base_ptr == ((void *)0)) {
				fprintf(stderr, "tMp3MemInfo->app_base_ptr is NULL \n");

				return E_ERROR;
			}

		} else {
			if( (tMp3MemInfo->app_base_ptr = AllocSlow(tMp3MemInfo->mp3d_size)) == ((void *)0) ) {
				fprintf(stderr, "tMp3MemInfo->app_base_ptr AllocSlow() failed\n");

				return E_ERROR;
			}
		}
	}

	fileSize = 0;

	while( TRUE ) {
		wordsReadBuffer = fread (inputMp3Buffer, sizeof(MP3D_UINT8),MP3D_INPUT_BUF_SIZE, pFileFd);

		fileSize += wordsReadBuffer;
		if( wordsReadBuffer != SIZE_BUF ) {
			if( fileSize == 0 ) {
				return E_ERROR;
			}
			break;
		}

	/*	if( fileSize >= 15000000 ) {
			break;
		}*/
	}
	fclose(pFileFd);

	if( (inputBuffer = (MP3D_UINT8 *)AllocFast(fileSize)) == ((void *)0) ) {
		fprintf(stderr, "inputBuffer AllocFast() failed \n");

		return E_ERROR;
	}

	if( (outputBuffer = (MP3D_INT16 *)AllocFast ((sizeof(short))*2*MP3D_FRAME_SIZE)) == ((void *)0) ) {
		fprintf(stderr, "outputBuffer AllocFast() failed \n");

		return E_ERROR;
	}

	if( (retval = mp3d_decode_init(tDecConfig, 0, 0)) != MP3D_OK ) {
		fprintf(stderr, "mp3d_decode_init() failed : [%02d]\n", retval);

		return retval;
	}

	pFileFd = fopen(_fileName,"rb");

	fread(inputBuffer, 1, fileSize, pFileFd);

	tDecConfig->pInBuf = (MP3D_INT8 *)inputMp3Buffer;
	if( fileSize > MP3D_INPUT_BUF_PUSH_SIZE ) {
		leftInLength = MP3D_INPUT_BUF_PUSH_SIZE;

	} else {
		leftInLength = fileSize;
	}

	tDecConfig->inBufLen = leftInLength;
	tDecConfig->consumedBufLen = 0;
	memcpy(inputMp3Buffer, inputBuffer, leftInLength);
	inBufLen = leftInLength;

	// fprintf(stderr, "[%s] Start of stream!\n", _fileName );


	while( retval < MP3D_ERROR_INIT ) {
		retval = mp3d_decode_frame(tDecConfig, &tDecParams, (MP3D_INT32 *)outputBuffer);

		if( retval == MP3D_OK ) { 
			if( tDecParams.layer == 3 || tDecParams.layer == 2 || tDecParams.layer == 1 ) {
				
				// CHECK. PCM information setting
				if(	pcmFlag == 0 ) {
					if( tDecParams.mp3d_sampling_freq == 16000 ) {
						tDecParams.mp3d_frame_size = tDecParams.mp3d_frame_size * 2;
					}

					SetPcmHandle(tDecParams.mp3d_num_channels, tDecParams.mp3d_sampling_freq);
					pcmFlag = 1;

					fprintf(stderr, "[%s] :  Play Time [%d s] \n", _fileName,  (fileSize * 8 ) / (tDecParams.mp3d_bit_rate * 1000));
					PlayDummySound(&tDecParams, tDecParams.mp3d_frame_size);
				}

#if DEBUG_PRINT
				fprintf(stderr, "%-20s \n",	"MP3  Information");
				fprintf(stderr, "%-20s : %d ch\n",	"MP3  Channels", 		tDecParams.mp3d_num_channels);
				fprintf(stderr, "%-20s : %d byte\n","MP3  One Frame Size ",	tDecParams.mp3d_frame_size);
				fprintf(stderr, "%-20s : %d Hz\n", 	"MP3  Sampling Rate  ",	tDecParams.mp3d_sampling_freq);
				fprintf(stderr, "%-20s : %d kbit\n","MP3  Bit Rate ", 	tDecParams.mp3d_bit_rate);
				fprintf(stderr, "%-20s : %d bit\n", "MP3  FileSize ", 	fileSize);
				fprintf(stderr, "%-20s : %d ms\n" ,	"Play Time", (fileSize * 8 ) / (tDecParams.mp3d_bit_rate));
#endif


				if( PlayMp3File((char *)outputBuffer, &tDecParams, tDecParams.mp3d_frame_size) < 0 ) {
					ClosePcm();
					break;
				}

				// control mute relay
				if( !gMsgQueueFunc.isMute() ) {
					if( !resetTimeFlag ) {
						memset(&muteBeginTime, 0x00, sizeof(muteBeginTime));
						gettimeofday(&muteBeginTime, NULL);
						resetTimeFlag = true;
					}

					gettimeofday(&muteEndTime, NULL);
					muteTime = (int)DiffTime(muteBeginTime, muteEndTime);

					if( muteTime >= (TIME_SET_MUTE_RELAY) ) {
						if( !gMainTerm ) {
							fprintf(stderr, "PcmCapture::execute() unmute, time elapsed : %d us \n" , muteTime);
							gMuteTime = muteTime;
							memset(&muteEndTime, 0x00, sizeof(muteEndTime));

							gMsgQueueFunc.incCntAudioMute();

							nullCnt = 0;
						}
					}
				} else {
					resetTimeFlag = false;
				}

			} else {
				fprintf(stderr, " Invalid Layer identified  File Error\n" );
				retval = MP3D_ERROR_LAYER ;
				
				break;
			}
		}

		if (retval == MP3D_END_OF_STREAM) {
			fprintf(stderr, "[%s] End of stream! \n", _fileName);

			break;
		}

		if( gMainTerm ) {
			fprintf(stderr, "Signel [%s] End! \n", _fileName);
			break;
		}

		if( (leftLength = tDecConfig->inBufLen - tDecConfig->consumedBufLen) < 0 ) {
			fprintf(stderr, "File left length not enough. (leftLength < 0) \n");

			break;
		}

		if( leftLength>sizeThreshold ) {
			tDecConfig->pInBuf += tDecConfig->consumedBufLen;
			tDecConfig->inBufLen -= tDecConfig->consumedBufLen;
			tDecConfig->consumedBufLen = 0;

		} else {

			memcpy(inputMp3Buffer, (tDecConfig->pInBuf + tDecConfig->consumedBufLen), leftLength);
			leftInLength = MP3D_INPUT_BUF_PUSH_SIZE - leftLength;

			if( leftInLength > (int)(fileSize - inBufLen) ) {
				leftInLength = (int)(fileSize - inBufLen);
			}

			if( inBufLen < fileSize ) {
				memcpy(inputMp3Buffer + leftLength, (inputBuffer + inBufLen), leftInLength);
			}

			inBufLen += leftInLength;
			tDecConfig->pInBuf = (MP3D_INT8 *)inputMp3Buffer;
			tDecConfig->inBufLen = leftLength + leftInLength;

			if( tDecConfig->inBufLen < 0 ) {
				break;
			}
			tDecConfig->consumedBufLen = 0;
		}
	}

	fclose(pFileFd);

	free(inputBuffer);
	free(outputBuffer);

	for( idx = 0 ; idx < numReqs; idx++ ) {
		free (tDecConfig->mp3d_mem_info.mem_info_sub[idx].app_base_ptr);
	}
	free(tDecConfig);

	if( !gMainTerm ) {
		PlayDummySound(&tDecParams, tDecParams.mp3d_frame_size);
	}
	gMsgQueueFunc.decCntAudioMute();

	return E_SUCCESS;
} // End of DecodeMp3File()


/*****************************
FUNC : AllocFast()
DESC : Mp3 AAU Parsing
 ******************************/
void *AllocFast(int _size) {
	void *ptr = ((void *)0);

	ptr = malloc(_size + 4 );
	ptr = (void *)(((long)ptr + (long)(4 - 1)) & (long)(~(4 - 1)));

	return ptr;
} // end of AllocFast()


/*****************************
FUNC : AllocSlow()
DESC : Mp3 AAU parsing
 ******************************/
void *AllocSlow(int _size) {
	void* ptr = ((void *)0);

	ptr = malloc(_size);
	ptr = (void *)(((long)ptr + (long)4 - 1) & (long)(~(4 - 1)));

	return ptr;
} // end of AllocSlow()

