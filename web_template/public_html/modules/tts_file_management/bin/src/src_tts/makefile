CC      = arm-linux-gnueabihf-g++

CFLAGS 	 = -O3 -Wall -std=c++11 -g
LFLAGS   = -ldl -lm -lpthread -lvtapi -lapi_json_parser

INCLUDES = -I./include -I./usr/include -I./dependency/avmsapi/opt/interm/usr/include -I./dependency/usr/include
LIB_DIR  = -L./usr/lib -L./dependency/avmsapi/opt/interm/usr/lib -L./dependency/usr/lib/arm-linux-gnueabih

PATH_TTS_LICENSE = ./usr/lib/libslicense.a

OBJS     = src/*.cpp 
TARGET	 = tts_bin 

$(TARGET):
	$(CC) $< -o $@ $(OBJS) $(INCLUDES) $(LIB_DIR) $(LFLAGS) $(CFLAGS) $(PATH_TTS_LICENSE)

clean:
	rm -f $(TARGET)

install:
	cp $(TARGET) ../../.

dependency :
	@echo "Prepare dependency library"
	@(  \
		if [ `uname -a | grep -i 'arm' | wc -l` -eq 0 ] ; then  \
			mkdir -p dependency/    &&\
			cd dependency/  &&\
			libavmsapi=`ls -1r ../../common/debs/libavmsapi* 2>>/dev/null | head -1`    ;\
			if [ _$${libavmsapi} != _ ] && [ -f $${libavmsapi} ] ; then \
				cp $${libavmsapi} . ;\
			else    \
				libavmsapi=`svn list http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/ | grep libavmsapi | LC_ALL=C sort -r | head -1` ;\
				svn export http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/$$libavmsapi ;\
				lib_api_json_parser=`svn list http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/ | grep libapi_json_parser | LC_ALL=C sort -r | head -1` ;\
				svn export http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/$$lib_api_json_parser ;\
				lib_voiceware_tts=`svn list http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/ | grep libVoicewareTTS | LC_ALL=C sort -r | head -1` ;\
				svn export http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/$$lib_voiceware_tts;\
			fi ;\
			dpkg -x $$libavmsapi avmsapi ;\
			dpkg -x $$lib_api_json_parser avmsapi ;\
			dpkg -x $$lib_voiceware_tts avmsapi ;\
		fi ;\
	)
	@echo "Prepare dependency library : Done"
	@echo

dependency_clean : clean
	rm -rf dependency/
