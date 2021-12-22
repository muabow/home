CC = arm-linux-gnueabihf-gcc

TARGET = network_info_receiver 
OBJECTS = network_info_receiver.o
SRCS = network_info_receiver.c

$(TARGET) : $(OBJECTS)
	$(CC) -g -o $(TARGET) $(OBJECTS)
	
$(OBJECTS) : $(SRCS)
	$(CC) -g -c $(SRCS)

clean:
	rm -rf $(TARGET) $(OBJECTS)
