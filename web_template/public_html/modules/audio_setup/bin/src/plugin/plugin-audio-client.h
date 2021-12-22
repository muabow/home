#define PATH_PIPE_WRITE_PLAYER     "/tmp/pipe_audio_player_read"
#define PATH_PIPE_READ_PLAYER      "/tmp/pipe_audio_player_write"

int init_plugin(void *s);
int deinit_plugin(void *s);
int setVolumePlayer(void *s);

struct PIPE_DATA {
	char    code;
	int     dataLength;
} typedef PIPE_DATA_t;

typedef struct {
	int volume;
} __attribute__((packed)) CLIENT_VOL_VALUE;

PLUG_INIT plugin_info = {
	.plugin_name = "plugin-audio-client\0",
	.comment = "control audio client\0",
	.init_plugin = init_plugin,
	.deinit_plugin = deinit_plugin
};

PLUG_FUNC_ARGU func2_argu[] = {
	{ 0x04, "ARGUMENTS\0" },
	{ 0x01, "volume\0" },
	{ 0x05, "ARGUMENTS\0" }
};

PLUG_FUNC plugin_funcs[] = {
	{
		.func_name = "setVolumePlayer\0",
		.argu_count = sizeof(func2_argu) / sizeof(PLUG_FUNC_ARGU),
		.argu_size = 4, // 1 * 4
		.argu_format = func2_argu,
		.function = setVolumePlayer
	}

};

int plugin_func_count = sizeof(plugin_funcs) / sizeof(PLUG_FUNC);
