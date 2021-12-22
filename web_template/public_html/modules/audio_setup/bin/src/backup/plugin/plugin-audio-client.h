#define PATH_PIPE_WRITE     "/tmp/pipe_audio_client_read"
#define PATH_PIPE_READ      "/tmp/pipe_audio_client_write"

#define PATH_PIPE_WRITE_PLAYER     "/tmp/pipe_audio_player_read"
#define PATH_PIPE_READ_PLAYER      "/tmp/pipe_audio_player_write"

//#define PATH_AUDIO_STATE_JSON	"/opt/interm/public_html/modules/audio_setup/conf/audio_stat.json"
#define PATH_AUDIO_CONF_DB			"/opt/interm/public_html/modules/audio_setup/conf/audio_stat.db"

int init_plugin(void *s);
int deinit_plugin(void *s);
int setInitAudioClient(void *s);
int setRunAudioClient(void *s);
int setStopAudioClient(void *s);
int setInitRunAudioClient(void *s);
int getAliveStatus(void *s);
int getClientInfo(void *s);
int getVolume(void *s);
int setVolume(void *s);
int setVolumePlayer(void *s);

struct PIPE_DATA {
	char    code;
	int     dataLength;
} typedef PIPE_DATA_t;

struct CLIENT_INFO {
	int     delay;
	int     delayMs;
	int     playVolume;

	// server parameter
	int     typeProtocol;
	int     serverCnt;
	char    castType[24];	
	char    ipAddr1[24];
	char    ipAddr2[24];
	int     port1;
	int     port2;
	char    mIpAddr[24];
	int     mPort;

	int     chunkSize;
	int     sampleRate;
	int     channels;
	int     mp3_mode;
	int     mp3_chunkSize;
	int     mp3_bitRate;
	int     mp3_sampleRate;
	int     ipStatus;

	char    hostName[128];
	char    deviceName[128];
} typedef CLIENT_INFO_t;

typedef struct {
	int delay;
	int delayMs;
	int typeProtocol;
	int serverCnt;
	int port1;
	int port2;
	int mPort;
	int playVolume;
	char *castType;
	char *ipAddr1;
	char *ipAddr2;
	char *mIpAddr;
	char *hostName;
} __attribute__((packed)) CLIENT_SET_VALUE;

typedef struct {
	int volume;
} __attribute__((packed)) CLIENT_VOL_VALUE;

typedef struct {
	char *data;
} __attribute__((packed)) DATA_STT;

PLUG_INIT plugin_info = {
	.plugin_name = "plugin-audio-client\0",
	.comment = "control audio client\0",
	.init_plugin = init_plugin,
	.deinit_plugin = deinit_plugin
};

PLUG_FUNC_ARGU func1_argu[] = {
	{ 0x04, "ARGUMENTS\0" },
	{ 0x01, "delay\0" },
	{ 0x01, "delayMs\0" },
	{ 0x01, "typeProtocol\0" },
	{ 0x01, "serverCnt\0" },
	{ 0x01, "port1\0" },
	{ 0x01, "port2\0" },
	{ 0x01, "mPort\0" },
	{ 0x01, "playVolume\0" },
	{ 0x03, "castType\0" },	
	{ 0x03, "ipAddr1\0" },
	{ 0x03, "ipAddr2\0" },
	{ 0x03, "mIpAddr\0" },
	{ 0x03, "hostname\0" },
	{ 0x05, "ARGUMENTS\0" }
};

PLUG_FUNC_ARGU func2_argu[] = {
	{ 0x04, "ARGUMENTS\0" },
	{ 0x01, "volume\0" },
	{ 0x05, "ARGUMENTS\0" }
};

PLUG_FUNC_ARGU func4_argu[] = {
	{ 0x03, "DATA\0" }
};

PLUG_FUNC plugin_funcs[] = {
	{
		.func_name = "setInitAudioClient\0",
		.argu_count = sizeof(func1_argu) / sizeof(PLUG_FUNC_ARGU),
		.argu_size = 52, // 13 * 4
		.argu_format = func1_argu,
		.function = setInitAudioClient
	},
	{
		.func_name = "setRunAudioClient\0",
		.argu_count = 0,
		.argu_size = 0,
		.argu_format = 0,
		.function = setRunAudioClient
	},
	{
		.func_name = "setStopAudioClient\0",
		.argu_count = 0,
		.argu_size = 0,
		.argu_format = 0,
		.function = setStopAudioClient
	},
	{
		.func_name = "setInitRunAudioClient\0",
		.argu_count = sizeof(func1_argu) / sizeof(PLUG_FUNC_ARGU),
		.argu_size = 52, // 13 * 4
		.argu_format = func1_argu,
		.function = setInitRunAudioClient
	},

	{
		.func_name = "getAliveStatus\0",
		.argu_count = sizeof(func4_argu) / sizeof(PLUG_FUNC_ARGU),
		.argu_size = 4, // 1 * 4
		.argu_format = func4_argu,
		.function = getAliveStatus
	},
	{
		.func_name = "getClientInfo\0",
		.argu_count = sizeof(func4_argu) / sizeof(PLUG_FUNC_ARGU),
		.argu_size = 4, // 1 * 4
		.argu_format = func4_argu,
		.function = getClientInfo
	},
	{
		.func_name = "getVolume\0",
		.argu_count = sizeof(func4_argu) / sizeof(PLUG_FUNC_ARGU),
		.argu_size = 4, // 1 * 4
		.argu_format = func4_argu,
		.function = getVolume
	},
	{
		.func_name = "setVolume\0",
		.argu_count = sizeof(func2_argu) / sizeof(PLUG_FUNC_ARGU),
		.argu_size = 4, // 1 * 4
		.argu_format = func2_argu,
		.function = setVolume
	},
	{
		.func_name = "setVolumePlayer\0",
		.argu_count = sizeof(func2_argu) / sizeof(PLUG_FUNC_ARGU),
		.argu_size = 4, // 1 * 4
		.argu_format = func2_argu,
		.function = setVolumePlayer
	}

};

int plugin_func_count = sizeof(plugin_funcs) / sizeof(PLUG_FUNC);
