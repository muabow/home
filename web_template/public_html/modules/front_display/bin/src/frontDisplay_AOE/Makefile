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

UID			:= $(shell id -u)
GID			:= $(shell id -g)

# Build Infomation
RESOURCE			:= interm
VERSION				:= $(shell head -1 version)
BUILD_DATE			:= $(shell date +%Y%m%d)
BUILD_TIME			:= $(shell date +%H%M%S)
LATEST_BUILD_INFO	 = latestBuildInfo


# Build Flags
CC	= arm-linux-gnueabihf-gcc
CXX	= arm-linux-gnueabihf-g++

### C/C++ 컴파일러 : CFLAGS/CXXFLAGS
CXXFLAGS	+= -g
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
INCS	+= -I./src
INCS	+= -I./include
ifeq ($(shell uname -a | grep -i 'arm' | wc -l), 0)
	# for Host
	INCS	+= -I./dependency/opt/interm/usr/include
else
	# for Target
	INCS	+= -I/opt/interm/usr/include
endif

### 전처리기 : CPPFLAGS, -D / -I (대문자 I) / $(INCS)
CPPFLAGS	+= -DVERSION=\"$(VERSION)\"
CPPFLAGS	+= -DBUILD_DATE=\"$(BUILD_DATE)\"
CPPFLAGS	+= -DBUILD_TIME=\"$(BUILD_TIME)\"
CPPFLAGS	+= $(INCS)


### 라이브러리 : LDLIBS, -l (소문자 L)
LDLIBS	+= -lpthread
LDLIBS	+= -lavmsapi -lu8g2

### 링커 : LDFLAGS, -L / -l (소문자 L) / $(LDLIBS)
ifeq ($(shell uname -a | grep -i 'arm' | wc -l), 0)
	# for Host
	LDFLAGS	+= -L./dependency/opt/interm/usr/lib
else
	# for Target
	LDFLAGS	+= -L/opt/interm/usr/lib
endif
LDFLAGS	+= $(LDLIBS)




SRCS	= $(shell find ./src -type f -name '*.cpp')
OBJS	= $(patsubst %.cpp, %.o, $(SRCS))
TARGET 	= frontDisplay
PACKAGE	= $(TARGET)+AOE-N300


.PHONY: all check_user show_info dependency_clean dist dist_clean clean install src/$(TARGET).cpp



all: show_info
	@$(MAKE) dependency

	@echo "$(ANSI_COLOR_CYAN)"
	@echo "### $(TARGET)"
	@echo "### `cat version`+$(BUILD_DATE)-$(BUILD_TIME)"
	@echo "$(COLOR_RESET)"
	@$(MAKE) $(TARGET) RESOURCE=$(RESOURCE) 



check_user:
	@(	\
		if [ $(UID) -ne 0 ] && [ $(UID) -ne 1000 ] ; then	\
			echo "$(COLOR_ERROR)"	;\
			echo "UID and GID must be root(0:0) or 1000:1000"	;\
			echo "Please use 'sudo' command."	;\
			echo "$(COLOR_RESET)"	;\
			echo	;\
			exit 1	;\
		fi	;\
	)
	@echo



show_info:
	@echo "$(COLOR_INFO)# Information$(COLOR_RESET)"

	@echo -n "$(ANSI_COLOR_BLUE)"
	@echo OEM : $(OEM)
	@echo Resource : $(RESOURCE)
	@echo Version : $(VERSION)
	@echo Build Date : $(BUILD_DATE)
	@echo Build Time : $(BUILD_TIME)
	@echo Target : $(TARGET)
	@echo Package : $(PACKAGE)
	@echo -n "$(COLOR_RESET)"
	@(	\
		if [ `which arm-linux-gnueabihf-g++ | wc -l` -eq 0 ] ; then	\
			echo "$(COLOR_RED)Compiler is not installled$(COLOR_RESET)"	;\
			echo	;\
			exit 1	;\
		fi	;\
	)
	@echo



$(TARGET): $(OBJS)
	@echo
	@echo "$(COLOR_INFO)TARGET : $@	$(COLOR_RESET)"

	$(CXX) -o $@ $^ $(LDFLAGS)

	@echo $(VERSION) > $(LATEST_BUILD_INFO)
	@echo $(BUILD_DATE) >> $(LATEST_BUILD_INFO)
	@echo $(BUILD_TIME) >> $(LATEST_BUILD_INFO)
	
	@chown `echo $${SUDO_UID}`:`echo $${SUDO_GID}` $@

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
			repo="http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs"	;\
			#packages='libavmsapi libapi_queue libapi_signal libapi_sqlite libapi_web_log libapi_websocket libcurl+interm'	;\
			packages='libavmsapi libapi_signal'	;\
			for package in $${packages} ; do	\
				debs=`ls -1r ../../../../../../../../../common/debs/$${subpath}/$${package}_*.deb 2>>/dev/null | head -1`	;\
				if [ _$${debs} != _ ] && [ -f $${debs} ] ; then	\
					cp $${debs} .	;\
				else	\
					debs=`svn list $${repo}/$${subpath}/ | grep $${package} | LC_ALL=C sort -r | head -1`	;\
					svn export $${repo}/$${subpath}/$${debs}	;\
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



dist:
	@$(MAKE) check_user

	@echo "$(COLOR_INFO)# Make Package$(COLOR_RESET)"

	@echo -n "$(ANSI_COLOR_BLUE)"
	@echo OEM : $(OEM)
	@echo Resource : $(RESOURCE)
	@echo Version : `sed -n '1p' $(LATEST_BUILD_INFO)`
	@echo Build Date : `sed -n '2p' $(LATEST_BUILD_INFO)`
	@echo Build Time : `sed -n '3p' $(LATEST_BUILD_INFO)`
	@echo Target : $(TARGET)
	@echo Package : $(PACKAGE)
	@echo -n "$(COLOR_RESET)"
	@echo


	@chmod a+x *.sh
	@chmod a+x ./dpkg/DEBIAN/p*

	@install -o1000 -g1000 -m755 -d ./dpkg/opt/interm/bin/
	@install -o1000 -g1000 -m755 $(TARGET) ./dpkg/opt/interm/bin/
	@install -o1000 -g1000 -m755 S50_frontDisplay.sh ./dpkg/opt/interm/bin/
	@install -o1000 -g1000 -m644 ./resource/Logo_$(RESOURCE).bmp ./dpkg/opt/interm/bin/$(TARGET).bmp
	@chown 1000:1000 -R ./dpkg/opt

	@(	\
		latest_version=`sed -n '1p' $(LATEST_BUILD_INFO)`		&&\
		latest_build_date=`sed -n '2p' $(LATEST_BUILD_INFO)`		&&\
		latest_build_time=`sed -n '3p' $(LATEST_BUILD_INFO)`		&&\
		package_file=$(PACKAGE)_"$${latest_version}"_armhf.deb		&&\
		[ _$(OEM) != _ ] && package_file=$(PACKAGE)_$(RESOURCE)_"$${latest_version}"_armhf.deb	;\
		sed -i 's/Version:.*/Version: '$${latest_version}'+'$${latest_build_date}'-'$${latest_build_time}'/g' ./dpkg/DEBIAN/control		&&\
		[ _$(OEM) != _ ] && sed -i 's/Version:.*/Version: '$${latest_version}'+'$${latest_build_date}'-'$${latest_build_time}+'$(RESOURCE)/g' ./dpkg/DEBIAN/control	;\
		dpkg -b ./dpkg $${package_file}		&&\
		echo '$(ANSI_COLOR_GREEN)'$${package_file} : Succeeded'$(COLOR_RESET)'	&&\
		echo &&\
		dpkg -I $${package_file}		;\
		chown `echo $${SUDO_UID}`:`echo $${SUDO_GID}` $${package_file}	;\
	)

	@echo "$(COLOR_INFO)# Make Package : Done$(COLOR_RESET)"
	@echo



dist_clean:
	@$(MAKE) check_user

	@echo "$(COLOR_INFO)# Clean distribution files$(COLOR_RESET)"

	rm -Rf ./dpkg/opt
	rm -Rf $(PACKAGE)_*.deb

	@echo "$(COLOR_INFO)# Clean distribution files: Done$(COLOR_RESET)"
	@echo



clean:
	@echo "$(COLOR_INFO)# Clean execution and object files$(COLOR_RESET)"

	rm -Rf $(TARGET) $(OBJS)
	rm -Rf $(LATEST_BUILD_INFO)

	@echo "$(COLOR_INFO)# Clean execution and object files : Done$(COLOR_RESET)"
	@echo



%.o: %.c
	@echo
	@echo "$(COLOR_INFO)OBJECT : $@	$(COLOR_RESET)"
	@$(MAKE) dependency
	$(CC) -c -o $@ $< $(CPPFLAGS) $(CXXFLAGS)



%.o: %.cpp
	@echo
	@echo "$(COLOR_INFO)OBJECT : $@	$(COLOR_RESET)"
	@$(MAKE) dependency
	$(CXX) -c -o $@ $< $(CPPFLAGS) $(CXXFLAGS)

