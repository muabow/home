<?php // ru, language_packs 위에 존재하는 모든 언어팩들의 내용은 동일해야 함
	namespace TTS_file_management\Lang {
		const STR_MENU_NAME									=	"Управление файлами TTS";

		const STR_TTS_BUTTON_CREATE							=	"Создайте";
		const STR_TTS_BUTTON_PREVIEW						=	"Превью";
		const STR_TTS_BUTTON_SAVE							=	"Сохранить";
		const STR_TTS_BUTTON_RESET							=	"Сброс";
		
		const STR_TTS_TITLE_FORM							=	"Создать файл TTS";
		const STR_TTS_TITLE_INPUT							=	"Введите содержание TTS";
		const STR_TTS_TITLE_INPUT_NAME						=	"Пожалуйста, введите название.";
		const STR_TTS_TITLE_INPUT_TEXT						=	"Пожалуйста, введите содержание TTS.";
		const STR_TTS_TITLE_INPUT_LANGUAGE					=	"Выберите язык";
		const STR_TTS_TITLE_INPUT_GENDER					=	"Выберите пол";
		const STR_TTS_TITLE_CHIME_SETUP						=	"Настройка работы CHIME";
		const STR_TTS_TITLE_CHIME_BEGIN						=	"Выберите Начать CHIME";
		const STR_TTS_TITLE_CHIME_END						=	"Выберите конец CHIME";
		const STR_TTS_TITLE_TTS_OPTION						=	"Настройка голосовых настроек TTS";

		const STR_TTS_TITLE_OPT_GENDER_MALE					=	"мужчина";
		const STR_TTS_TITLE_OPT_GENDER_FEMALE				=	"женский";
		const STR_TTS_TITLE_OPT_CHIME_NONE					=	"Не выбран";
		const STR_TTS_TITLE_OPT_PITCH						=	"Подача";
		const STR_TTS_TITLE_OPT_SPEED						=	"скорость";
		const STR_TTS_TITLE_OPT_VOLUME						=	"объем";
		const STR_TTS_TITLE_OPT_SENTENCE_PAUSE				=	"Задержка между предложениями";
		const STR_TTS_TITLE_OPT_COMMA_PAUSE					=	"Задержка между запятой";

		const STR_TTS_TABLE									=	"Список файлов TTS";
		const STR_TTS_TABLE_NUMBER							=	"Число";
		const STR_TTS_TABLE_NAME							=	"заглавие";
		const STR_TTS_TABLE_CONTENT							=	"содержание";
		const STR_TTS_TABLE_PLAY_TIME						=	"время";

		const STR_TTS_ACT_DEL								=	"Удалить";
		const STR_TTS_ACT_DEL_APPLY							=	"Удалить выбранный файл.";
		const STR_TTS_ACT_COPY_APPLY						=	"Скопируйте выбранный TTS в BGM.";
		const STR_TTS_ACT_COPY_COMPLETE						=	"Копирование завершено.";
		const STR_TTS_ACT_INPUT_TITLE						=	"Пожалуйста, введите название TTS.";
		const STR_TTS_ACT_INPUT_DUP_TITLE					=	"Есть повторяющиеся заголовки TTS. Пожалуйста, измените название.";
		const STR_TTS_ACT_INPUT_TEXT						=	"Пожалуйста, введите содержание TTS.";
		const STR_TTS_ACT_INPUT_LANGUAGE					=	"Пожалуйста, выберите язык.";
		const STR_TTS_ACT_INPUT_GENDER						=	"Пожалуйста, выберите пол.";
		const STR_TTS_ACT_INPUT_PREVIEW						=	"Пожалуйста, создайте файл TTS.";
		const STR_TTS_ACT_INPUT_CONFIRM_SAVE				=	"Хотите сэкономить?";
		const STR_TTS_ACT_LIMIT_BYTES_OVER					=	"Превышен лимит ввода контента.";
		const STR_TTS_ACT_EXCEED_CAPACITY					=	"TTS не может быть создан из-за превышения емкости использования.";

		const STR_TTS_INFO_LIMIT_BYTES 						=	"Ограничения ввод";
		const STR_TTS_INFO_AVAIL_SIZE 						=	"Полезная емкость";

		const STR_EXT_SELECT_UPLOAD_STORAGE					=	"Выберите хранилище для загрузки";
		const STR_EXT_SELECT_STORAGE_INTERNAL				=	"Внутренняя память";
		const STR_EXT_SELECT_STORAGE_EXTERNAL				=	"Внешнее хранилище (SD)";
		const STR_EXT_SRCFILE_ADD_AVAILABLE_MEM				=	"Внешняя доступная емкость";

		const STR_TTS_ERROR_ALERT							=	"Произошла ошибка преобразования TTS. Пожалуйста, проверьте содержимое.";
		const STR_TTS_INVALID_WORD							=	"Специальные символы ('&) нельзя использовать в заголовке или содержании.";
	}
?>
