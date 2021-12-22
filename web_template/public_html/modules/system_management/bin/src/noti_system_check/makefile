CC		= arm-linux-gnueabihf-g++

CFLAGS  = -O3 -Wall
LFLAGS  = -std=c++11 -lapi_web_log -g

INCLUDES = -I./include -I/opt/interm/usr/include

LIB_DIR = -L./lib -L/opt/interm/usr/lib

OBJS = src/*.cpp
TARGET = noti_system_check

noti_system_check:
	$(CC) $< -o $@ $(OBJS) $(INCLUDES) $(LIB_DIR) $(LFLAGS) $(CFLAGS)

install:
	cp $(TARGET) ../../.

clean:
	rm -f $(TARGET)

