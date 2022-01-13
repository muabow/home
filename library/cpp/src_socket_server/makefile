CC		= g++
CFLAGS  = -O3 -Wall -std=c++11 -g
LFLAGS  = -lpthread -lsignal

INCLUDES = -I./include -I./usr/include
LIB_DIR = -L./usr/lib

OBJS = src/*.cpp
TARGET = socket_server

$(TARGET): 
	$(CC) $< -o $@ $(OBJS) $(INCLUDES) $(LIB_DIR) $(LFLAGS) $(CFLAGS)

clean:
	rm -f $(TARGET)
