CC=arm-linux-gnueabihf-g++
#CC=gcc
#RELEASE=1

.SUFFIXES: .cpp .o

# Variables
version          := $(shell head -1 version)
build_date       := $(shell date +%Y%m%d)
build_time       := $(shell date +%H%M%S)
#prefix          ?= /usr
prefix          ?= ..
libdir          ?= lib
includedir      ?= include
inst_path        = $(prefix)/$(libdir)
include_path     = $(prefix)/$(includedir)

latestBuildInfo  = latestBuildInfo


# Build Flags
FLAGS = -g
FLAGS += -std=c++11

ifeq ($(RELEASE), 1)
FLAGS += -O2
FLAGS += -DNDEBUG
else
FLAGS += -D_DEBUG
FLAGS += -D_TRACE
endif

FLAGS += -DVERSION=\"$(version)\"
FLAGS += -DBUILD_DATE=\"$(build_date)\"
FLAGS += -DBUILD_TIME=\"$(build_time)\"


SRCS=$(shell find ./src/ -type f -name '*.cpp')
OBJS=$(patsubst %.cpp, %.o, $(SRCS))
TARGET=libapi_web_log.so

.PHONY: all dist install install_headers distclean clean uninstall uninstall_headers

all: $(TARGET)

$(TARGET): $(OBJS)
	@echo
	@echo "# Create library"
	
	$(CC) -shared -o $@ $(OBJS)
	@echo $(version) > $(latestBuildInfo)
	@echo $(build_date) >> $(latestBuildInfo)
	@echo $(build_time) >> $(latestBuildInfo)
	
	@echo "# Create library: Done"
	@echo

%.o: %.cpp
	$(CC) $(FLAGS) -fPIC -I./include -c $< -o $@

dist: $(TARGET)
	@echo "# Make Package"
	make install prefix=./dpkg/opt/interm/usr
	chmod a+x ./dpkg/DEBIAN/p*

	@(\
	LATEST_VERSION=`sed -n '1p' $(latestBuildInfo)`		&&\
	LATEST_BUILD_DATE=`sed -n '2p' $(latestBuildInfo)`		&&\
	sed -i 's/Version:.*/Version: '$$LATEST_VERSION'+'$$LATEST_BUILD_DATE'/g' ./dpkg/DEBIAN/control		&&\
	dpkg -b ./dpkg libapi_web_log_"$$LATEST_VERSION"_armhf.deb		&&\
	echo Package: libapi_web_log_"$$LATEST_VERSION"_armhf.deb	&&\
	dpkg -I libapi_web_log_"$$LATEST_VERSION"_armhf.deb		\
	)

	@echo "# Make Package: Done"
	@echo

install: install_headers
	@echo "# Install library"

	mkdir -p $(inst_path)
	rm -Rf $(inst_path)/$(TARGET) $(inst_path)/$(TARGET).$(version)
	cp -f $(TARGET) $(inst_path)/$(TARGET)
	
	@echo "# Install library: Done"
	@echo

install_headers:
	@echo "# Install header files"

	mkdir -p $(include_path)
	cp -f ./include/*.h $(include_path)

	@echo "# Install header files: Done"
	@echo

distclean: clean
	@echo "# Clean distribution files"

	rm -Rf ./dpkg/opt
	rm -Rf libapi_web_log_*.deb

	@echo "# Clean distribution files: Done"
	@echo

clean:
	@echo "# Clean library and object folder"

	rm -Rf $(OBJS) $(TARGET)
	rm -Rf $(latestBuildInfo)

	@echo "# Clean library and object folder: Done"
	@echo

uninstall: uninstall_headers
	@echo "# Uninstall library"

	rm -Rf $(inst_path)/$(TARGET)

	@echo "# Uninstall library: Done"
	@echo

uninstall_headers:
	@echo "# Unnstall header files"

	rm -Rf $(include_path)
	
	@echo "# Uninstall header files: Done"
	@echo

