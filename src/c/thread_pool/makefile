CC		= gcc
CFLAGS  = -W -Wall -std=c90 -g
LFLAGS  = -lpthread

INCLUDES = -I./include
LIB_DIR = 

OBJS = src/*.c
TARGET = thread_pool 

$(TARGET): 
	$(CC) $< -o $@ $(OBJS) $(INCLUDES) $(LIB_DIR) $(LFLAGS) $(CFLAGS)

clean:
	rm -f $(TARGET)
