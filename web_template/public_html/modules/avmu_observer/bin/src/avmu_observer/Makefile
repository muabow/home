# ANSI Color Code
ANSI_COLOR_RED		= \033[0;31m
ANSI_COLOR_GREEN	= \033[0;32m
ANSI_COLOR_YELLOW	= \033[0;33m
ANSI_COLOR_BLUE		= \033[0;34m
ANSI_COLOR_MAGENTA	= \033[0;35m
ANSI_COLOR_CYAN		= \033[0;36m
ANSI_COLOR_RESET	= \033[0m

COLOR_ERROR			= $(ANSI_COLOR_RED)
COLOR_WARNING		= $(ANSI_COLOR_MAGENTA)
COLOR_INFO			= $(ANSI_COLOR_YELLOW)
COLOR_DEBUG			= $(ANSI_COLOR_GREEN)
COLOR_TRACE			= $(ANSI_COLOR_CYAN)

COLOR_RESET			= $(ANSI_COLOR_RESET)


#RELEASE	= 1

# Build Infomation
VERSION				:= $(shell head -1 version)
BUILD_DATE			:= $(shell date +%Y%m%d)
BUILD_TIME			:= $(shell date +%H%M%S)
LATEST_BUILD_INFO	 = latestBuildInfo


# Build Flags
CC	= arm-linux-gnueabihf-gcc
CXX	= arm-linux-gnueabihf-g++

### C/C++ 컴파일러 : CFLAGS/CXXFLAGS
CXXFLAGS	 = -g
CXXFLAGS	+= -Wall
CXXFLAGS	+= -std=c++11

ifeq ($(RELEASE), 1)
	CXXFLAGS	+= -O2
	CXXFLAGS	+= -DNDEBUG
else
	CXXFLAGS	+= -D_DEBUG
	CXXFLAGS	+= -D_TRACE
endif


### 헤더파일 (사용자매크로) : INCS, -I (대문자 I)
ifeq ($(shell uname -a | grep -i 'arm' | wc -l), 0)
	# for host
	INCS		 = -I./dependency/opt/interm/usr/include
else
	# for target
	INCS		 = -I/opt/interm/usr/include
endif

### 전처리기 : CPPFLAGS, -D / -I (대문자 I) / $(INCS)
CPPFLAGS	+= -DVERSION=\"$(VERSION)\"
CPPFLAGS	+= -DBUILD_DATE=\"$(BUILD_DATE)\"
CPPFLAGS	+= -DBUILD_TIME=\"$(BUILD_TIME)\"
CPPFLAGS	+= $(INCS)


### 라이브러리 : LDLIBS, -l (소문자 L)
LDLIBS	 = -lpthread
LDLIBS	+= -lavmsapi -lu8g2
LDLIBS	+= -lapi_websocket
LDLIBS	+= -lNotificationListener -lapi_signal -lcurl

### 링커 : LDFLAGS, -L / -l (소문자 L) / $(LDLIBS)
ifeq ($(shell uname -a | grep -i 'arm' | wc -l), 0)
	# for host
	LDFLAGS		 = -L./dependency/opt/interm/usr/lib
else
	# for target
	LDFLAGS		 = -L/opt/interm/usr/lib
endif
LDFLAGS	+= $(LDLIBS)




SRCS	= $(shell find ./src -type f -name '*.cpp')
OBJS	= $(patsubst %.cpp, %.o, $(SRCS))
TARGET	= avmu_observer
PACKAGE	= $(TARGET)


.PHONY: all dist distclean clean src/$(TARGET).cpp



all: dependency
	@echo "$(ANSI_COLOR_CYAN)"
	@echo "### $(TARGET)"
	@echo "### `cat version`+$(BUILD_DATE)-$(BUILD_TIME)"
	@echo "$(COLOR_RESET)"
	@$(MAKE) $(TARGET)



$(TARGET): $(OBJS)
	@echo
	@echo "$(COLOR_INFO)TARGET : $@	$(COLOR_RESET)"

	$(CXX) -o $@ $^ $(LDFLAGS)

	@echo $(VERSION) > $(LATEST_BUILD_INFO)
	@echo $(BUILD_DATE) >> $(LATEST_BUILD_INFO)
	@echo $(BUILD_TIME) >> $(LATEST_BUILD_INFO)
	
	@chown `echo $${SUDO_UID}`:`echo $${SUDO_GID}` $@	;\

	@echo
	@echo "$(ANSI_COLOR_GREEN)$@ : Succeeded$(COLOR_RESET)"
	@echo



dependency:
	@echo "$(COLOR_INFO)# Prepare dependency library$(COLOR_RESET)"
	@(	\
		if [ `uname -a | grep -i 'arm' | wc -l` -eq 0 ] ; then	\
			mkdir -p dependency/	&&\
			cd dependency/	&&\
			subpath='./'	;\
			#packages='libavmsapi libapi_queue libapi_signal libapi_sqlite libapi_web_log libapi_websocket libcurl+interm'	;\
			packages='libavmsapi libapi_signal libapi_websocket libcurl+interm libNotification'	;\
			for package in $${packages} ; do	\
				debs=`ls -1r ../../../../../../../../../common/debs/$${subpath}/$${package}_*.deb 2>>/dev/null | head -1`	;\
				if [ _$${debs} != _ ] && [ -f $${debs} ] ; then	\
					cp $${debs} .	;\
				else	\
					debs=`svn list  http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/$${subpath}/ | grep $${package} | LC_ALL=C sort -r | head -1`	;\
					svn export http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/$${subpath}/$${debs}	;\
				fi	;\
				dpkg -x $${debs} ./	;\
			done	;\
			package='libasound2-dev'	;\
		fi	;\
	)
	@echo "$(COLOR_INFO)# Prepare dependency library : Done$(COLOR_RESET)"
	@echo



dependency_clean: clean
	@echo "$(COLOR_INFO)# Clean up dependency library$(COLOR_RESET)"
	rm -rf dependency/
	@echo "$(COLOR_INFO)# Clean up dependency library : Done$(COLOR_RESET)"
	@echo



clean:
	@echo "$(COLOR_INFO)# Clean execution and object files$(COLOR_RESET)"

	rm -Rf $(TARGET) $(OBJS)
	rm -Rf $(LATEST_BUILD_INFO)

	@echo "$(COLOR_INFO)# Clean execution and object files : Done$(COLOR_RESET)"
	@echo



install:
	@echo "$(COLOR_INFO)# Install execution files$(COLOR_RESET)"

	cp $(TARGET) ../../
	cp *.json ../../

	@echo "$(COLOR_INFO)# Install execution files : Done$(COLOR_RESET)"
	@echo



%.o: %.c
	@echo
	@echo "$(COLOR_INFO)TARGET : $@	$(COLOR_RESET)"
	@$(MAKE) dependency
	$(CC) -c -o $@ $< $(CPPFLAGS) $(CXXFLAGS)



%.o: %.cpp
	@echo
	@echo "$(COLOR_INFO)OBJECT : $@	$(COLOR_RESET)"
	@$(MAKE) dependency
	$(CXX) -c -o $@ $< $(CPPFLAGS) $(CXXFLAGS)

