#CC		= g++
CC		= arm-linux-gnueabihf-g++

CFLAGS  = -O3 -Wall -std=c++11 -g
LFLAGS  = -lpthread -lsqlite3 -lavmsapi -lu8g2 -lapi_json_parser -lapi_signal -lapi_websocket -lapi_queue -lapi_web_log -lmp3lame

INCLUDES = -I./include -I./usr/include -I./dependency/avmsapi/opt/interm/usr/include -I./dependency/usr/include
LIB_DIR = -L./usr/lib -L./dependency/avmsapi/opt/interm/usr/lib -L./dependency/usr/lib/arm-linux-gnueabihf

OBJS = src/*.cpp
TARGET = source_file_server 

source_file_server: 
	$(CC) $< -o $@ $(OBJS) $(INCLUDES) $(LIB_DIR) $(LFLAGS) $(CFLAGS)

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
				lib_api_signal=`svn list http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/ | grep libapi_signal | LC_ALL=C sort -r | head -1` ;\
				svn export http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/$$lib_api_signal ;\
				lib_api_websocket=`svn list http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/ | grep libapi_websocket | LC_ALL=C sort -r | head -1` ;\
				svn export http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/$$lib_api_websocket ;\
				lib_api_sqlite=`svn list http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/ | grep libapi_sqlite | LC_ALL=C sort -r | head -1` ;\
				svn export http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/$$lib_api_sqlite ;\
				lib_api_queue=`svn list http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/ | grep libapi_queue | LC_ALL=C sort -r | head -1` ;\
				svn export http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/$$lib_api_queue ;\
				lib_api_web_log=`svn list http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/ | grep libapi_web_log | LC_ALL=C sort -r | head -1` ;\
				svn export http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/$$lib_api_web_log ;\
				lib_api_json_parser=`svn list http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/ | grep libapi_json_parser | LC_ALL=C sort -r | head -1` ;\
				svn export http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/$$lib_api_json_parser ;\
				lib_asound2_dev=`svn list http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/public/ | grep libasound2-dev_ | LC_ALL=C sort -r | head -1` ;\
				svn export http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/public/$$lib_asound2_dev ;\
				lib_asound2=`svn list http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/public/ | grep libasound2_ | LC_ALL=C sort -r | head -1` ;\
				svn export http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/public/$$lib_asound2;\
            fi ;\
            dpkg -x $$libavmsapi avmsapi ;\
            dpkg -x $$lib_api_signal avmsapi ;\
            dpkg -x $$lib_api_websocket avmsapi ;\
            dpkg -x $$lib_api_queue avmsapi ;\
            dpkg -x $$lib_api_sqlite avmsapi ;\
            dpkg -x $$lib_api_web_log avmsapi ;\
            dpkg -x $$lib_api_json_parser avmsapi ;\
            dpkg -x $$lib_asound2_dev ./ ;\
            dpkg -x $$lib_asound2 ./ ;\
        fi ;\
    )
	@echo "Prepare dependency library : Done"
	@echo

dependency_clean : clean
	rm -rf dependency/

