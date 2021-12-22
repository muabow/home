#define PATH_PIPE_WRITE     "/tmp/pipe_audio_server_read"
#define PATH_PIPE_READ      "/tmp/pipe_audio_server_write"

//#define PATH_AUDIO_STATE_JSON		"/opt/interm/public_html/modules/audio_setup/conf/audio_stat.json"
#define PATH_AUDIO_CONF_DB			"/opt/interm/public_html/modules/audio_setup/conf/audio_stat.db"

int init_plugin(void *s);
int deinit_plugin(void *s);
int setInitAudioServer(void *s);
int setRunAudioServer(void *s);
int setStopAudioServer(void *s);
int setInitRunAudioServer(void *s);
int getAliveStatus(void *s);
int getServerInfo(void *s);
int getClientList(void *s);
int setStackIdx(void *s);
int setPlayMode(void *s);

struct PIPE_DATA {
	char    code;
	int     dataLength;
} typedef PIPE_DATA_t;

struct SERVER_INFO {
	// Queue parameter
	int     queueCnt;
	int     bufferRate;
	int     chunkSize;

	// PCM parameter
	int     sampleRate;
	int     channels;

	// MP3 parameter
	bool    mp3_mode;
	int     mp3_chunkSize;
	int     mp3_bitRate;
	int     mp3_sampleRate;

	// server parameter
	bool    typeProtocol;
	char    castType[24];
	int     port;
	int     clientCnt;
	char    ipAddr[24];

	bool    typePlayMode;
	char    fileName[128];
	char    deviceName[128];
} typedef SERVER_INFO_t;


typedef struct {
	int queueCnt;
	int bufferRate;
	int chunkSize;
	int sampleRate;
	int channels;
	int mp3_mode;
	int mp3_chunkSize;
	int mp3_bitRate;
	int mp3_sampleRate;
	int typeProtocol;
	char *castType;
	char *ipAddr;
	int port;
	int clientCnt;
	int typePlayMode;
	char *fileName;
	char *deviceName;
} __attribute__((packed)) SVR_SET_VALUE;

typedef struct {
	int threadIdx;
	int queueIdx;
} __attribute__((packed)) STACK_IDX;

typedef struct {
	int index;
	char *fileName;
} __attribute__((packed)) PLAY_MODE;

typedef struct {
	char *data;
} __attribute__((packed)) DATA_STT;

PLUG_INIT plugin_info = {
	.plugin_name = "plugin-audio-server\0",
	.comment = "control audio server\0",
	.init_plugin = init_plugin,
	.deinit_plugin = deinit_plugin
};

PLUG_FUNC_ARGU func1_argu[] = {
	{ 0x04, "ARGUMENTS\0" },
	{ 0x01, "queueCnt\0" },
	{ 0x01, "bufferRate\0" },
	{ 0x01, "chunkSize\0" },
	{ 0x01, "sampleRate\0" },
	{ 0x01, "channels\0" },
	{ 0x01, "mp3_mode\0" },
	{ 0x01, "mp3_chunkSize\0" },
	{ 0x01, "mp3_bitRate\0" },
	{ 0x01, "mp3_sampleRate\0" },
	{ 0x01, "typeProtocol\0" },
	{ 0x03, "castType\0" },
	{ 0x03, "ipAddr\0" },
	{ 0x01, "port\0" },
	{ 0x01, "clientCnt\0" },
	{ 0x01, "typePlayMode\0" },
	{ 0x03, "filename\0" },
	{ 0x03, "deviceName\0" },
	{ 0x05, "ARGUMENTS\0" }
};

PLUG_FUNC_ARGU func2_argu[] = {
	{ 0x04, "ARGUMENTS\0" },
	{ 0x01, "threadIdx\0" },
	{ 0x01, "queueIdx\0" },
	{ 0x05, "ARGUMENTS\0" }
};

PLUG_FUNC_ARGU func3_argu[] = {
	{ 0x04, "ARGUMENTS\0" },
	{ 0x01, "index\0" },
	{ 0x03, "fileName\0" },
	{ 0x05, "ARGUMENTS\0" }
};

PLUG_FUNC_ARGU func4_argu[] = {
	{ 0x03, "DATA\0" }
};

PLUG_FUNC plugin_funcs[] = {
	{
		.func_name = "setInitAudioServer\0",
		.argu_count = sizeof(func1_argu) / sizeof(PLUG_FUNC_ARGU),
		.argu_size = 68, // 17 * 4
		.argu_format = func1_argu,
		.function = setInitAudioServer
	},
	{
		.func_name = "setRunAudioServer\0",
		.argu_count = 0,
		.argu_size = 0,
		.argu_format = 0,
		.function = setRunAudioServer
	},
	{
		.func_name = "setStopAudioServer\0",
		.argu_count = 0,
		.argu_size = 0,
		.argu_format = 0,
		.function = setStopAudioServer
	},
	{
		.func_name = "setInitRunAudioServer\0",
		.argu_count = sizeof(func1_argu) / sizeof(PLUG_FUNC_ARGU),
		.argu_size = 68, // 17 * 4
		.argu_format = func1_argu,
		.function = setInitRunAudioServer
	},

	{
		.func_name = "getAliveStatus\0",
		.argu_count = sizeof(func4_argu) / sizeof(PLUG_FUNC_ARGU),
		.argu_size = 4, // 1 * 4
		.argu_format = func4_argu,
		.function = getAliveStatus
	},
	{
		.func_name = "getServerInfo\0",
		.argu_count = sizeof(func4_argu) / sizeof(PLUG_FUNC_ARGU),
		.argu_size = 4, // 1 * 4
		.argu_format = func4_argu,
		.function = getServerInfo
	},
	{
		.func_name = "getClientList\0",
		.argu_count = sizeof(func4_argu) / sizeof(PLUG_FUNC_ARGU),
		.argu_size = 4, // 1 * 4
		.argu_format = func4_argu,
		.function = getClientList
	},
	{
		.func_name = "setStackIdx\0",
		.argu_count = sizeof(func2_argu) / sizeof(PLUG_FUNC_ARGU),
		.argu_size = 8, // 2 * 4
		.argu_format = func2_argu,
		.function = setStackIdx
	},
	{
		.func_name = "setPlayMode\0",
		.argu_count = sizeof(func3_argu) / sizeof(PLUG_FUNC_ARGU),
		.argu_size = 8, // 2 * 4
		.argu_format = func3_argu,
		.function = setPlayMode
	}
};

int plugin_func_count = sizeof(plugin_funcs) / sizeof(PLUG_FUNC);
