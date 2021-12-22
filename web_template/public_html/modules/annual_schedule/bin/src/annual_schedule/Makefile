CC = arm-linux-gnueabihf-gcc


INCLUDES	+= -I./include/libxml2
LIBS		 = -L./lib

CFLAGS 	+= -O3
#CFLAGS	+= -Wall
CFLAGS	+= -w
CFLAGS	+= -g
CFLAGS	+= $(INCLUDES)

LFLAGS	 = -lpthread
LFLAGS	+= -lxml2
LFLAGS	+= -lz
LFLAGS	+= -llzma
LFLAGS	+= $(LIBS)


CSRCS	=$(wildcard ./*.c)
OBJS	=$(patsubst %.c, %.o, $(CSRCS))


TARGET = schedule_prc
#sibo_main-pc

.PHONY: all clean install

all: $(TARGET)
#	chmod 755 schedule_prc
#sibo_main-pc
#	cp sibo_main /home/kss/opt/interm/bin/.
#	cp sibo_main /home/kss/opt2/interm/bin/.

$(TARGET): $(OBJS)
	$(CC) $^ $(LFLAGS) -o $@


#sibo_main-pc:
#	gcc $< -o $@ $(OBJS) -I/usr/include -L/usr/lib $(LFLAG) -g


install: $(TARGET)
	[ -d ../../../bin/src/annual_schedule ] && cp $(TARGET) ../../


clean:
	rm -Rf $(OBJS)
	rm -Rf $(TARGET)


%.o: %.c
	$(CC) $(CFLAGS) -c -o $@ $<

