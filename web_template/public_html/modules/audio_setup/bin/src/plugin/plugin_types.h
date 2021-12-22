/*
	argu_type : 
	0x01 : int
	0x02 : double
	0x03 : char *
	0x04 : struct start
	0x05 : struct end
*/

typedef struct {
	char argu_type;
	const char *argu_name;
} __attribute__((packed)) PLUG_FUNC_ARGU;

typedef struct {
    const char *func_name;
	int  argu_count;
	int  argu_size;
    PLUG_FUNC_ARGU *argu_format;
	
    int (* function)(void *);
} __attribute__((packed)) PLUG_FUNC;

typedef struct {
    const char *plugin_name;
	const char *comment;
	
    int (* init_plugin)(void *);
    int (* deinit_plugin)(void *);
} __attribute__((packed)) PLUG_INIT;
