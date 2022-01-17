CC=g++
SRCS=$(shell find ./src/ -type f -name '*.cpp')
OBJS=$(patsubst %.cpp, %.o, $(SRCS))
TARGET=libapi_curl.so


$(TARGET): $(OBJS)
	@echo
	@echo "# Create library"

	$(CC) -shared -o $@ $(OBJS)

%.o: %.cpp
	$(CC) $(FLAGS) -fPIC -I./include -lcurl -c $< -o $@


clean:
	rm -Rf $(OBJS) $(TARGET)
	rm -Rf $(latestBuildInfo)
