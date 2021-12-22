CC		= g++

CFLAGS  = -O3 -Wall
LFLAGS  = -lasound -lpthread -lsqlite3 -std=c++11 -g 

INCLUDES = -I./include 
LIB = -L/opt/interm/usr/lib

LIB_DIR = ./lib
LIB_MP3_DEC = $(LIB_DIR)/lib_mp3_dec_arm9_elinux.so 

OBJS = src/*.cpp
TARGET = audio_client

audio_client:
	$(CC) $< -o $@ $(OBJS) $(INCLUDES) $(LFLAGS) $(LIB_MP3_DEC) $(LIB) 

clean:
	rm -f $(TARGET)

