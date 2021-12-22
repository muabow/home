<?php // eng, language_packs 위에 존재하는 모든 언어팩들의 내용은 동일해야 함
	namespace TTS_file_management\Lang {
		const STR_MENU_NAME									=	"TTS File Management";

		const STR_TTS_BUTTON_CREATE							=	"Create";
		const STR_TTS_BUTTON_PREVIEW						=	"Preview";
		const STR_TTS_BUTTON_SAVE							=	"Save";
		const STR_TTS_BUTTON_RESET							=	"Reset";
		
		const STR_TTS_TITLE_FORM							=	"Create TTS file";
		const STR_TTS_TITLE_INPUT							=	"Enter TTS content";
		const STR_TTS_TITLE_INPUT_NAME						=	"Please enter the title.";
		const STR_TTS_TITLE_INPUT_TEXT						=	"Please enter the TTS content.";
		const STR_TTS_TITLE_INPUT_LANGUAGE					=	"Select language";
		const STR_TTS_TITLE_INPUT_GENDER					=	"Select gender";
		const STR_TTS_TITLE_CHIME_SETUP						=	"CHIME operation setup";
		const STR_TTS_TITLE_CHIME_BEGIN						=	"Select Start CHIME";
		const STR_TTS_TITLE_CHIME_END						=	"Select End CHIME";
		const STR_TTS_TITLE_TTS_OPTION						=	"TTS voice options setup";

		const STR_TTS_TITLE_OPT_GENDER_MALE					=	"Male";
		const STR_TTS_TITLE_OPT_GENDER_FEMALE				=	"Female";
		const STR_TTS_TITLE_OPT_CHIME_NONE					=	"Not selected";
		const STR_TTS_TITLE_OPT_PITCH						=	"Pitch";
		const STR_TTS_TITLE_OPT_SPEED						=	"Speed";
		const STR_TTS_TITLE_OPT_VOLUME						=	"Volume";
		const STR_TTS_TITLE_OPT_SENTENCE_PAUSE				=	"Delay between sentences";
		const STR_TTS_TITLE_OPT_COMMA_PAUSE					=	"Delay between comma";

		const STR_TTS_TABLE									=	"TTS file list";
		const STR_TTS_TABLE_NUMBER							=	"Number";
		const STR_TTS_TABLE_NAME							=	"Title";
		const STR_TTS_TABLE_CONTENT							=	"Contents";
		const STR_TTS_TABLE_PLAY_TIME						=	"Play time";

		const STR_TTS_ACT_DEL								=	"Delete";
		const STR_TTS_ACT_DEL_APPLY							=	"Delete the selected file.";
		const STR_TTS_ACT_COPY_APPLY						=	"Copy the selected TTS to BGM.";
		const STR_TTS_ACT_COPY_COMPLETE						=	"Copying is complete.";
		const STR_TTS_ACT_INPUT_TITLE						=	"Please enter the TTS title.";
		const STR_TTS_ACT_INPUT_DUP_TITLE					=	"There are duplicate TTS titles. Please change the title.";
		const STR_TTS_ACT_INPUT_TEXT						=	"Please enter the TTS contents.";
		const STR_TTS_ACT_INPUT_LANGUAGE					=	"Please select a language.";
		const STR_TTS_ACT_INPUT_GENDER						=	"Please select a gender.";
		const STR_TTS_ACT_INPUT_PREVIEW						=	"Please create a TTS file.";
		const STR_TTS_ACT_INPUT_CONFIRM_SAVE				=	"Would you like to save?";
		const STR_TTS_ACT_LIMIT_BYTES_OVER					=	"Content input limit exceeded.";
		const STR_TTS_ACT_EXCEED_CAPACITY					=	"TTS cannot be generated due to exceeded usage capacity.";

		const STR_TTS_INFO_LIMIT_BYTES 						=	"Input limit";
		const STR_TTS_INFO_AVAIL_SIZE 						=	"Usable capacity";

		const STR_EXT_SELECT_UPLOAD_STORAGE					=	"Select upload storage";
		const STR_EXT_SELECT_STORAGE_INTERNAL				=	"Internal storage";
		const STR_EXT_SELECT_STORAGE_EXTERNAL				=	"External storage (SD)";
		const STR_EXT_SRCFILE_ADD_AVAILABLE_MEM				=	"External available capacity";

		const STR_TTS_ERROR_ALERT							=	"A TTS conversion error has occurred. Please check the contents.";
		const STR_TTS_INVALID_WORD							=	"Special characters ('&) cannot be used in the title or content.";
	}
?>