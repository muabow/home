CC=g++
SRCS=$(shell find ./src/ -type f -name '*.cpp')
OBJS=$(patsubst %.cpp, %.o, $(SRCS))
TARGET=libjson_parser.so


$(TARGET): $(OBJS)
	$(CC) -shared -o $@ $(OBJS)

%.o: %.cpp
	$(CC) $(FLAGS) -fPIC -I./include -c $< -o $@

clean:
	rm -Rf $(OBJS) $(TARGET)
