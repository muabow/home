<?php // eng, language_packs 위에 존재하는 모든 언어팩들의 내용은 동일해야 함
	namespace Source_file_management\Lang {
		const STR_MENU_NAME									=					"BGM Play";

		const STR_SRCFILE_BUTTON_RESET						=					"Initialize";

		const STR_SRCFILE_ADD								=					"Source File Upload";
		const STR_SRCFILE_ADD_FIND							=					"Select Source File";
		const STR_SRCFILE_ADD_UPLOAD						=					"Upload";
		const STR_SRCFILE_ADD_SELECT						=					"Please select source file.";
		const STR_SRCFILE_ADD_AVAILABLE_MEM					=					"Available Capacity";
		const STR_SRCFILE_ADD_LIMIT_MEM_ALL					=					"Insufficient storage space.";
		const STR_SRCFILE_ADD_LIMIT_COUNT					=					"Exceed the number of files that can be uploaded.";
		const STR_SRCFILE_ADD_INVALID_TYPE					=					"The sound file extension is not in WAV/MP3 format.";
		const STR_SRCFILE_ADD_INVALID_NAME					=					"The file name contains a special character(` * | \\\ / ? \\\" : < > + # $ %)that is not appropriate.";
		const STR_SRCFILE_ADD_INVALID_LENGTH				=					"The file name cannot exceed 255 characters including the extension.";
		const STR_SRCFILE_ADD_DOUBLE_SPACE_NAME				=					"The file name contains consecutive spaces.";
		const STR_SRCFILE_ADD_NOT_FOUND_FILE				=					"The file cannot be found.";
		const STR_SRCFILE_ADD_UPLOAD_CONFIRM				=					"Would you like to upload this file?";
		const STR_SRCFILE_ADD_UPLOAD_APPLY					=					"Upload source file.";
		const STR_SRCFILE_ADD_UPLOAD_SUCCESS 				=					"Upload was successful.";
		const STR_SRCFILE_ADD_UPLOAD_FAIL					=					"Upload failed.";

		const STR_SRCFILE_ADD_UPLOAD_FAIL_NOTFOUND_FILE		= 					"No Files";
		const STR_SRCFILE_ADD_UPLOAD_FAIL_OVER_FILECNT		= 					"Exceed number of files";
		const STR_SRCFILE_ADD_UPLOAD_FAIL_OVER_SAVESIZE		= 					"Exeed Storage";
		const STR_SRCFILE_ADD_UPLOAD_FAIL_UPLOAD_SIZE		= 					"Upload Capacity";

		const STR_SRCFILE_TABLE								=					"Source File List";
		const STR_SRCFILE_TABLE_COL_INDEX					=					"Number";
		const STR_SRCFILE_TABLE_COL_FNAME					=					"File name";

		const STR_SRCFILE_DEL								=					"Delete";
		const STR_SRCFILE_DEL_SELECT						=					"Please select a file to delete.";
		const STR_SRCFEIL_DEL_EMPTY							=					"No source file to delete.";
		const STR_SRCFILE_DEL_SELECT_APPLY					=					"Deletes the selected source file.";
		const STR_SRCFILE_DEL_SELECT_ALL_APPLY				=					"Delete the entire source file.";
		const STR_SRCFILE_DEL_SELECT_APPLY_AFTER_STOP		=					"After stopping the sound being played, delete the file.";

		const STR_SRCFILE_PLAY		 		  			    =					"Play";
		const STR_SRCFILE_PLAY_SETUP 		  			    =					"Play Setting";
		const STR_SRCFILE_PLAY_ALL	 		  			    =					"Play All";
		const STR_SRCFILE_PLAY_REPEAT					    =					"Number of repetitions";
		const STR_SRCFILE_PLAY_REPEAT_OVERRANGE   			=					"It does not fit within the iteration count range.";
		const STR_SRCFILE_PLAY_STOP 		  			    =					"Pause";
		const STR_SRCFILE_PLAY_STOP_APPLY		  			=					"Pause audio playback.";
		const STR_SRCFEIL_PLAY_EMPTY						=					"No source file to play.";
		const STR_SRCFILE_PLAY_SELECT	 	  			    =					"Please select a file to play.";
		const STR_SRCFILE_PLAY_SELECT_APPLY				    =					"Play the selected source file.";
		const STR_SRCFILE_PLAY_SELECT_ALL_APPLY				=					"Play the entire source file.";

		const STR_SRCFILE_SRC_PLAY							=					"Source File Play";

		const STR_OPER_INFO									=					"Operation Information";
		const STR_INFO_LEVEL_METER							=					"Level Meter";
		const STR_INFO_VOLUME								=					"Volume";
		const STR_COMMON_APPLY								=					"Apply";

		const STR_OTHER										=					"Count";
		const STR_AND										=					"Except";
		const STR_JS_WRONG_VOLUME							=					"Invalid volume value.(0~100)";

		const STR_TITLE_NUMBER								=					"Number";
		const STR_TITLE_NAME								=					"Name";
		const STR_TITLE_TYPE								=					"Type";
		const STR_TITLE_INFO								=					"Info.";
		const STR_TITLE_CHANNEL								=					"Channel";
		const STR_TITLE_PLAY_TIME							=					"Play time";
		const STR_TITLE_LOOP								=					"Loop";

		const STR_SRC_LIST_TIME_MIN							=					"Min.";
		const STR_SRC_LIST_TIME_SEC							=					"Sec.";
		
		const STR_HELP_SRC_MODE								= 					"The source device is only provided for uploading/downloading/deleting sound files.";

		const STR_EXT_SELECT_UPLOAD_STORAGE					=					"Select upload storage";
		const STR_EXT_SELECT_STORAGE_INTERNAL				=					"Internal storage";
		const STR_EXT_SELECT_STORAGE_EXTERNAL				=					"External storage (SD)";
		const STR_EXT_SRCFILE_ADD_AVAILABLE_MEM				=					"External available capacity";
	}
?>