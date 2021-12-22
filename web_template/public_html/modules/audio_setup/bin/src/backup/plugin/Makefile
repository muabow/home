CC=arm-linux-gnueabihf-g++

ifeq ($(shell uname -a | grep -i 'arm' | wc -l), 0)
INC = -I./dependency/avmsapi/opt/interm/usr/include
INC += -I./include
LIB = -L./dependency/avmsapi/opt/interm/usr/lib
LIB += -L./lib
else
INC = -I/opt/interm/usr/include
LIB = -L/opt/interm/usr/lib
endif

TARGET_3 = plugin-audio-server.so
OBJECTS_3 = plugin-audio-server.o
SRCS_3 = plugin-audio-server.c

TARGET_4 = plugin-audio-client.so
OBJECTS_4 = plugin-audio-client.o
SRCS_4 = plugin-audio-client.c

all : $(TARGET_3) $(TARGET_4)

$(TARGET_3) : $(OBJECTS_3)
	$(CC) -std=c++11 -shared -fPIC -g -o $(TARGET_3) $(OBJECTS_3) $(LIB) -lsqlite3
			
$(OBJECTS_3) : dependency $(SRCS_3)
	$(CC) -std=c++11 -shared -fPIC -g -c $(SRCS_3) $(INC) -I../ -I../../avms-manager
			
$(TARGET_4) : $(OBJECTS_4)
	$(CC) -std=c++11 -shared -fPIC -g -o $(TARGET_4) $(OBJECTS_4) $(LIB) -lsqlite3
			
$(OBJECTS_4) : dependency $(SRCS_4)
	$(CC) -std=c++11 -shared -fPIC -g -c $(SRCS_4) $(INC) -I../ -I../../avms-manager
	
dependency :
	@echo "Prepare dependency library"
	@(	\
		if [ `uname -a | grep -i 'arm' | wc -l` -eq 0 ] ; then	\
			mkdir -p dependency/	&&\
			cd dependency/	&&\
			libavmsapi=`ls -1r ../../common/debs/libavmsapi* 2>>/dev/null | head -1`	;\
			if [ _$${libavmsapi} != _ ] && [ -f $${libavmsapi} ] ; then	\
				cp $${libavmsapi} .	;\
			else	\
				libavmsapi=`svn list  http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/ | grep libavmsapi | LC_ALL=C sort -r | head -1`	;\
				svn export http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/$$libavmsapi	;\
			fi	;\
			dpkg -x $$libavmsapi avmsapi	;\
		fi	;\
	)
	@echo "Prepare dependency library : Done"
	@echo

dependency_clean : clean
	rm -rf dependency/

clean:
	rm -rf $(TARGET_3) $(OBJECTS_3)
	rm -rf $(TARGET_4) $(OBJECTS_4)
