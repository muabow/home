CC      = g++

CFLAGS  = -O3
LFLAGS  = -std=c++11 -lpthread -lasound -lsqlite3 -g

INCLUDES = -I./include

LIBRARY = -L./lib

LIB_MP3_DEC = ./lib/lib_mp3_dec_arm9_elinux.so 

OBJS = src/*.cpp
TARGET = audio_player 

audio_player:
	$(CC) $< -o $@ $(OBJS) $(INCLUDES) $(LIBRARY) $(LIB_MP3_DEC) $(LFLAGS) $(CFLAGS)

clean:
	rm -f $(TARGET)

