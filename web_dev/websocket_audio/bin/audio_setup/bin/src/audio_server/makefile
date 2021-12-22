CC		= g++

CFLAGS  = -O3 -Wall
LFLAGS  = -std=c++11 -lasound -lpthread -lsqlite3 -g

INCLUDES = -I./include 

LIB_DIR = ./lib
LIB_MP3_ENC = $(LIB_DIR)/lib_mp3_enc_arm9_elinux.so 

OBJS = src/*.cpp
TARGET = audio_server

audio_server:
	$(CC) $< -o $@ $(OBJS) $(INCLUDES) $(LIB_MP3_ENC) $(LFLAGS) 
	#$(CC) $< -o $@ $(OBJS) $(INCLUDES) $(LIB_MP3_ENC) $(LFLAGS) $(CFLAGS)

clean:
	rm -f $(TARGET)

