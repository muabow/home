<?php // kor, language_packs 위에 존재하는 모든 언어팩들의 내용은 동일해야 함
	namespace Chime_file_management\Lang {
		const STR_MENU_NAME									=					"CHIME 파일 관리";

		const STR_SRCFILE_BUTTON_RESET						=					"초기화";

		const STR_SRCFILE_ADD								=					"CHIME 파일 업로드";
		const STR_SRCFILE_ADD_FIND							=					"파일 선택";
		const STR_SRCFILE_ADD_UPLOAD						=					"업로드";
		const STR_SRCFILE_ADD_SELECT						=					"파일을 선택해 주세요.";
		const STR_SRCFILE_ADD_AVAILABLE_MEM					=					"가용 용량";
		const STR_SRCFILE_ADD_LIMIT_MEM_ALL					=					"저장 공간이 부족합니다.";
		const STR_SRCFILE_ADD_LIMIT_COUNT					=					"업로드 할 수 있는 파일의 갯수를 초과하였습니다.";
		const STR_SRCFILE_ADD_INVALID_TYPE					=					"파일 확장자가 WAV/MP3 형식이 아닙니다.";
		const STR_SRCFILE_ADD_INVALID_NAME					=					"파일명에 적절하지 않은 특수문자(` * | \\\ / ? \\\" : < > + # $ %)가 입력되어있습니다.";
		const STR_SRCFILE_ADD_INVALID_LENGTH				=					"파일명은 확장자 포함 255글자를 넘을 수 없습니다.";
		const STR_SRCFILE_ADD_DOUBLE_SPACE_NAME				=					"파일명이 연속된 공백을 포함하고 있습니다.";
		const STR_SRCFILE_ADD_NOT_FOUND_FILE				=					"파일을 찾을 수 없습니다.";
		const STR_SRCFILE_ADD_UPLOAD_CONFIRM				=					"파일을 업로드 하시겠습니까?";
		const STR_SRCFILE_ADD_UPLOAD_APPLY					=					"파일을 업로드합니다.";
		const STR_SRCFILE_ADD_UPLOAD_SUCCESS 				=					"업로드에 성공 하였습니다.";
		const STR_SRCFILE_ADD_UPLOAD_FAIL					=					"업로드에 실패 했습니다.";

		const STR_SRCFILE_ADD_UPLOAD_FAIL_NOTFOUND_FILE		= 					"파일 없음";
		const STR_SRCFILE_ADD_UPLOAD_FAIL_OVER_FILECNT		= 					"파일 갯수 초과";
		const STR_SRCFILE_ADD_UPLOAD_FAIL_OVER_SAVESIZE		= 					"저장 용량 초과";
		const STR_SRCFILE_ADD_UPLOAD_FAIL_UPLOAD_SIZE		= 					"업로드 용량";

		const STR_SRCFILE_TABLE								=					"CHIME 파일 목록";

		const STR_SRCFILE_DEL								=					"삭제";
		const STR_SRCFILE_DEL_SELECT_APPLY					=					"선택된 파일을 삭제합니다.";

		const STR_OTHER										=					"개";
		const STR_AND										=					"외";

		const STR_TITLE_NUMBER								=					"번호";
		const STR_TITLE_NAME								=					"음원명";
		const STR_TITLE_PLAY_TIME							=					"재생 시간";

		const STR_SRC_LIST_TIME_MIN							=					"분";
		const STR_SRC_LIST_TIME_SEC							=					"초";

		const STR_CHIME_HELP_1								=					"CHIME 파일은 최대 10개까지 업로드가 가능합니다.";
		const STR_CHIME_HELP_2								=					"CHIME 파일은 최대 10초까지만 재생됩니다.";

		const STR_EXT_SELECT_UPLOAD_STORAGE					=					"업로드 저장소 선택";
		const STR_EXT_SELECT_STORAGE_INTERNAL				=					"내부 저장소";
		const STR_EXT_SELECT_STORAGE_EXTERNAL				=					"외부 저장소 (SD)";
		const STR_EXT_SRCFILE_ADD_AVAILABLE_MEM				=					"외부 저장소 가용 용량";
	}
?>
