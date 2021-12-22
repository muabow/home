<?php // kor, language_packs 위에 존재하는 모든 언어팩들의 내용은 동일해야 함
	namespace TTS_file_management\Lang {
		const STR_MENU_NAME									=	"TTS 파일 관리";

		const STR_TTS_BUTTON_CREATE							=	"생성";
		const STR_TTS_BUTTON_PREVIEW						=	"미리듣기";
		const STR_TTS_BUTTON_SAVE							=	"저장";
		const STR_TTS_BUTTON_RESET							=	"초기화";
		
		const STR_TTS_TITLE_FORM							=	"TTS 파일 생성";
		const STR_TTS_TITLE_INPUT							=	"TTS 내용 입력";
		const STR_TTS_TITLE_INPUT_NAME						=	"제목을 입력해 주세요.";
		const STR_TTS_TITLE_INPUT_TEXT						=	"TTS 내용을 입력해 주세요.";
		const STR_TTS_TITLE_INPUT_LANGUAGE					=	"언어 선택";
		const STR_TTS_TITLE_INPUT_GENDER					=	"성별 선택";
		const STR_TTS_TITLE_CHIME_SETUP						=	"CHIME 동작 설정";
		const STR_TTS_TITLE_CHIME_BEGIN						=	"시작 CHIME 선택";
		const STR_TTS_TITLE_CHIME_END						=	"종료 CHIME 선택";
		const STR_TTS_TITLE_TTS_OPTION						=	"TTS 음성 옵션 설정";

		const STR_TTS_TITLE_OPT_GENDER_MALE					=	"남성";
		const STR_TTS_TITLE_OPT_GENDER_FEMALE				=	"여성";
		const STR_TTS_TITLE_OPT_CHIME_NONE					=	"선택 안함";
		const STR_TTS_TITLE_OPT_PITCH						=	"높낮이";
		const STR_TTS_TITLE_OPT_SPEED						=	"스피드";
		const STR_TTS_TITLE_OPT_VOLUME						=	"볼륨";
		const STR_TTS_TITLE_OPT_SENTENCE_PAUSE				=	"문장간 지연시간";
		const STR_TTS_TITLE_OPT_COMMA_PAUSE					=	"콤마간 지연시간";

		const STR_TTS_TABLE									=	"TTS 파일 목록";
		const STR_TTS_TABLE_NUMBER							=	"번호";
		const STR_TTS_TABLE_NAME							=	"제목";
		const STR_TTS_TABLE_CONTENT							=	"내용";
		const STR_TTS_TABLE_PLAY_TIME						=	"재생 시간";

		const STR_TTS_ACT_DEL								=	"삭제";
		const STR_TTS_ACT_DEL_APPLY							=	"선택된 파일을 삭제합니다.";
		const STR_TTS_ACT_COPY_APPLY						=	"선택한 TTS를 BGM으로 복사합니다.";
		const STR_TTS_ACT_COPY_COMPLETE						=	"복사가 완료 되었습니다.";
		const STR_TTS_ACT_INPUT_TITLE						=	"TTS 제목을 입력해 주세요.";
		const STR_TTS_ACT_INPUT_DUP_TITLE					=	"중복된 TTS 제목있습니다. 제목을 변경해 주세요.";
		const STR_TTS_ACT_INPUT_TEXT						=	"TTS 텍스트를 입력해 주세요.";
		const STR_TTS_ACT_INPUT_LANGUAGE					=	"언어를 선택해 주세요.";
		const STR_TTS_ACT_INPUT_GENDER						=	"성별을 선택해 주세요.";
		const STR_TTS_ACT_INPUT_PREVIEW						=	"TTS 파일을 생성해 주세요.";
		const STR_TTS_ACT_INPUT_CONFIRM_SAVE				=	"저장 하시겠습니까?";
		const STR_TTS_ACT_LIMIT_BYTES_OVER					=	"내용 입력 제한을 초과 하였습니다.";
		const STR_TTS_ACT_EXCEED_CAPACITY					=	"사용 용량 초과로 TTS를 생성할 수 없습니다.";

		const STR_TTS_INFO_LIMIT_BYTES 						=	"입력 제한";
		const STR_TTS_INFO_AVAIL_SIZE 						=	"가용 용량";

		const STR_EXT_SELECT_UPLOAD_STORAGE					=	"업로드 저장소 선택";
		const STR_EXT_SELECT_STORAGE_INTERNAL				=	"내부 저장소";
		const STR_EXT_SELECT_STORAGE_EXTERNAL				=	"외부 저장소 (SD)";
		const STR_EXT_SRCFILE_ADD_AVAILABLE_MEM				=	"외부 저장소 가용 용량";

		const STR_TTS_ERROR_ALERT							=	"TTS 변환 오류가 발생했습니다. 내용을 확인해주세요.";
		const STR_TTS_INVALID_WORD							=	"제목 또는 내용에 특수문자('&)는 사용할 수 없습니다.";
	}
?>
