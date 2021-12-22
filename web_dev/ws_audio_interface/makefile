CC		= g++

CFLAGS  = -O3 -Wall
LFLAGS  = -std=c++11 -lasound -lpthread -lsqlite3 -lapi_websocket -g

INCLUDES = -I./include -I/opt/interm/usr/include

LIB_DIR = -L./lib -L/opt/interm/usr/lib

OBJS = src/*.cpp
TARGET = audio_interface

audio_interface :
	$(CC) $< -o $@ $(OBJS) $(INCLUDES) $(LIB_DIR) $(LFLAGS) $(CFLAGS)

clean:
	rm -f $(TARGET)

