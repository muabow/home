<?php // kor, language_packs 위에 존재하는 모든 언어팩들의 내용은 동일해야 함
	// namespace에 모듈명 입력, 첫글자는 대문자
	namespace Annual_schedule\Lang {
		// 좌측 메뉴에 출력되는 메뉴명 입력, 모든 모듈 공통
		const STR_MENU_NAME				=					"연간스케줄러";
		const DISPLAY_MONTH				=					"달";
		const DISPLAY_WEEK				=					"주";
		const DISPLAY_DAY				=					"일";
		const DISPLAY_YEAR				=					"년";
		const DISPLAY_TODAY				=					"오늘";
		const DISPLAY_HOLIDAY			=					"휴일";
		const DISPLAY_DOWNLOAD			= 					"스케줄 다운로드";
		const DISPLAY_UPLOAD			=					"스케줄 업로드";
		const DISPLAY_MONTHCOPY			=					"월간 복사";

		const SIBO_ADD 					=  					"시보 추가";
		const SIBO_NAME					=					"이름";
		const SIBO_FILESELECT			=				"파일 선택";
		const SIBO_TIMESELECT			=             "시간 선택";
		const SIBO_WEEKSELECT			=             "요일 선택";
		const SIBO_FILE_SELECT_ERR		=		"시보 파일을 선택해 주세요.";
		const HOLI_FILE_SELECT_ERR		=		"휴일 파일을 선택해 주세요.";

		const SIBO_MOD					=					"시보 변경";
		const SIBO_MOD_SUCCESS			= 			"변경되었습니다.";
		const SIBO_LISTSELECT			=				"목록 선택";

		const SIBO_TIME_1				=					"매 시간";
		const SIBO_TIME_2				=					"적용 시간(예:9,10)";
		const SIBO_TIME_3				=					"정해진 시간";
		const SIBO_TIME_4				=					"시작 시간";
		const SIBO_TIME_5				=					"종료 시간";
		const SIBO_TIME_6				=					"시";
		const SIBO_TIME_7				=					"분";
		const SIBO_TIME_8				=					"그외";
		const SIBO_TIME_9				=					"분";
		const SIBO_TIME_CHECK_MSG       =         "종료시간이 이릅니다.";
		const SIBO_TIME_CHECK_FILE       =  		"파일을 선택해 주세요.";
		const SIBO_TIME_CURTIME_STR       =       "현재 시간 :";
		const SIBO_APPLY_TIME_ERROR       = 		"적용시간을 입력해 주세요.";
		const SIBO_USER_TIME_ERROR       =  		"정해진 시간을 입력해 주세요.";
		const SIBO_WEEK_ERROR       = 			"요일을 체크해 주세요.";
		const SIBO_TIME_DUPLICATE       =			"시간이 중복됩니다.";
		const SIBO_ADD_SUCCESS       = 			"추가되었습니다.";
		const SIBO_NOT_NUMBER       = 			"문자는 입력 할 수 없습니다.";
		const SIBO_NOT_NUMBER_HOUR       = 		"범위에 맞는 값을 넣어주세요.(0~23)";
		const SIBO_NOT_NUMBER_MIN       = 		"범위에 맞는 값을 넣어주세요.(0~59)";
		const SIBO_NOT_AVALIABLE_TIME       =     "시간 정보가 올바르지 않습니다. 시작 시간과 종료 시간을 확인해 주시길 바랍니다.";

		const SIBO_MAIN_1       =					"시보 설정";
		const SIBO_MAIN_2       =					"시간 테이블 보기";
		const SIBO_MAIN_3       =					"설정된 테이블";
		const SIBO_MAIN_4       =					"시간 테이블 목록";
		const SIBO_MAIN_5       =					"추가";
		const SIBO_MAIN_6       =					"삭제";
		const SIBO_MAIN_7       =					"휴일 설정";
		const SIBO_MAIN_8       =					"설정";
		const SIBO_MAIN_9       =					"음원 파일 관리";
		const SIBO_MAIN_10       =				"저장";
		const SIBO_MAIN_11       =				"사용";
		const SIBO_MAIN_12       =				"사용 안함";
		const SIBO_MAIN_13       =				"설정파일 관리";
		const SIBO_MAIN_14       = 				"사용";
		const SIBO_MAIN_15       =				"설정";
		const SIBO_MAIN_16       =  				"수정";
		const SIBO_MAIN_17       =				"휴일파일 관리";

		const SIBO_CENTER_STR1       =			"스케줄 방송 목록";
		const SIBO_CENTER_STR3       =			"적용 요일";
		const SIBO_CENTER_STR2       =			"시간";
		const SIBO_CENTER_STR4       =			"제목";
		const SIBO_CENTER_STR5       =			"파일";
		const SIBO_CENTER_STR6       =			"일";
		const SIBO_CENTER_STR7       =			"월";
		const SIBO_CENTER_STR8       =			"화";
		const SIBO_CENTER_STR9       =			"수";
		const SIBO_CENTER_STR10       =			"목";
		const SIBO_CENTER_STR11       =			"금";
		const SIBO_CENTER_STR12       =			"토";

		const SIBO_FILE_STR1       =				"미리듣기";
		const SIBO_FILE_STR2       =				"정지";
		const SIBO_FILE_STR3       =				"파일올리기";
		const SIBO_FILE_DISK       =				"총사용량";

		const SIBO_FILE_SPACE_MSG       =         "파일 이름에 공백은 들어갈 수 없습니다";
		const SIBO_EDIT_RETRY_MSG       =         "현재 해당 기능은 사용하실 수 없습니다. 재생되고 있는 시보 방송이 종료 된 이후에 다시 시도해 주시길 바랍니다.";
		const DISK_USAGE_ERROR       =            "디스크의 용량이 부족합니다.";
		const SIBO_MAX_LIST       =               "더 이상 목록을 추가할 수 없습니다.";
		const SIBO_MAX_FILE       =               "선택 할 수 있는 파일의 최대 개수는 10 입니다.";
		const SIBO_NOTMATCH_MP3       =           "mp3 파일 이 외에는 사용할 수 없습니다.";

		const SIBO_LEVEL_STR1       =				"볼륨(%)";

		const SIBO_DEL_TITLE        =  			"시보 삭제";
		const SIBO_DEL_CONFIRM        = 			"삭제하시겠습니까?";
		const SIBO_DEL_INIT        =				"초기화하시겠습니까?";
		const SIBO_DEL_SAVE        =				"저장하시겠습니까?";
		const SIBO_DEL_ANYTHING    =          "삭제할 데이터를 선택해 주세요.";
		const SIBO_ALL_SELECT       =				"전체선택";
		const SIBO_ALL_UNSELECT       =			"전체해제";

		const HOLI_DEL_TITLE 		=  			"휴일 삭제";
		const HOLI_EVENT_TITLE       = 			"휴일 제목";
		const SIBO_HOLI_TITLE        = 			"휴일 설정";
		const SIBO_HOLI_BLANK        =       		"제목이나 날짜가 빈칸이 있습니다.";
		const SIBO_HOLI_ADD_TITLE        =        "제 목";
		const SIBO_HOLI_START_DATE       =		"시작날짜";
		const SIBO_HOLI_START_TIME       =		"시작시간";
		const SIBO_HOLI_END_DATE       =			"종료날짜";
		const SIBO_HOLI_END_TIME       =			"종료시간";
		const SIBO_HOLI_FUNCTION       =			"변경/삭제";
		const SIBO_HOLI_ACTION       = 			"동작";
		const SIBO_HOLI_UPDATE       =			"변경";
		const SIBO_HOLI_ADD_DIALOG_TITLE       = 	"휴일 추가";
		const SIBO_HOLI_DELETE_MSG       = 		"휴일을 삭제하시겠습니까?";
		const SIBO_HOUR_STR       	= 				"시";
		const SIBO_MINUTE_STR       = 			"분";
		const SIBO_NOW_STR       = 				"지금";
		const SIBO_DATE_STR       = 				"날짜";
		const SIBO_TIME_STR       = 				"시간";
		const SIBO_ALLDAY_STR       = 			"하루종일";
		const SIBO_AUTOEND_STR       =			"자동으로 재생 종료";
		const SIBO_WEEK_STR       = 				"주마다";
		const SIBO_MONTH_STR       = 				"달마다";
		const SIBO_YEAR_STR       = 				"년마다";
		const SIBO_NONE_STR       =  				"없음";
		const SIBO_ACTIONTYPE_STR       = 		"실행타입";
		const SIBO_REPEAT_CNT_STR       =			"반복횟수";
		const SIBO_NOTSUPPORTED_STR       = 		"지원하지 않음!";
		const SIBO_FILENAME_STR       = 			"파일이름";
		const SIBO_HOLIDAY_STR       = 			"휴일";
		const SIBO_DOWNLOAD_STR       =			"스케줄 내려받기";
		const SIBO_UPLOAD_STR       =				"스케줄 올려두기";
		const SIBO_COPY_STR       =  				"스케줄 복사";
		const SIBO_TODAY_STR       = 				"오늘";
		const SIBO_DISPLAY_MONTH       =			"달";
		const SIBO_DISPLAY_WEEK       =			"주";
		const SIBO_DISPLAY_DAY       =			"일";
		const SIBO_DISPLAY_YEAR       =			"년";
		const SIBO_CHOOSE_FROM       = 			"복사 할 달을 선택해주세요.";
		const SIBO_CHOOSE_TO       =	 			"붙혀넣기 할 달을 선택해주세요.";
		const SIBO_EXECUTE       = 				"복사 실행";
		const SIBO_CLOSE       = 					"닫기";
		const SIBO_DELETE_STR       = 			"스케줄을 삭제하시겠습니까?";
		const SIBO_EVERYWEEK_STR       =			"매주마다";
		const SIBO_FIRSTWEEK_STR       = 			"첫번째주만";
		const SIBO_LASTWEEK_STR       = 			"마지막째주만";

		const SIBO_FILE_TITLE       =            "파일 관리";
		const SIBO_FILE_UPLOAD       = 			"업로드";
		const SIBO_FILE_SELECT       =           "선택";

		const SIBO_FILE_UPLOAD_STR       =        "시보 파일 업로드 성공";
		const SIBO_FILE_UPLOAD_FAIL_STR       =   "시보 파일 업로드 실패";
		const SIBO_FILE_DOWNLOAD_STR       =      "시보 파일 다운로드 성공";
		const SIBO_FILE_DOWNLOAD_FAIL_STR       = "시보 파일 다운로드 실패";


		const SIBO_HELP_MSG1       =	            "시보를 설정 할 수 있습니다.";
		const SIBO_HELP_MSG2       =	            "테이블보기에서 제목이 붉은 글씨로 표시되면, 재생할 파일이 존재하지 않음을 의미합니다.";
		const SIBO_HELP_MSG3       = 				"디스크 용량의 95%까지 업로드 할 수 있습니다.";
		const SIBO_HELP_MSG4       =  			"시보의 최대 목록수는 50개입니다.";
		const SIBO_HELP_MSG5       =  			"휴일의 최대 목록수는 30개입니다.";
		const SIBO_HELP_MSG6       =				"스케줄러에 사용하는 파일을 관리 할 수 있습니다.";
		const SIBO_HELP_MSG7       = 				"붉은 글씨는 휴일을 의미합니다.";

		const SIBO_ADD_CONFIRM       = 			"저장하시겠습니까?";
		const SIBO_ADD_TITLE_ERROR       =        "제목을 넣어주세요.";
		const SIBO_MOD_CONFIRM       =            "변경하시겠습니까?";

		const SIBO_PRIORITY       =  				"우선순위";
		const SIBO_USE_STR       =				"시보를 사용하시겠습니까?";
		const SIBO_NOUSE_STR       =				"시보를 정지시키겠습니까?";

		const SIBO_AUX       =					"AUX 입력 타입 설정";
		const SIBO_AUX_MSG       =	            "AUX의 입력 타입을 설정 할 수 있습니다.";
		const SIBO_AUX_CHANG_MSG       =          "AUX의 타입을 변경합니다.";

		const DATE_TYPE_STR				= 		"날짜 형식";
		const REPEAT_EVERYDAY_STR 		= 		"매일반복";
		const REPEAT_ONCE_STR 			= 		"한번만";
		const MSG_REP_STR				= 		"반복";
		const COPY_STR 					=		"복사";
		const RADIO_USER_DEL 			= 		"삭제";

		const COPY_PASTE_STR			=		"복사";

		const CANCEL_STR				=		"취소";
		const OK_STR					=		"확인";
		const SIBO_DELETE_STR			=		"삭제하시겠습니까?";
		const DELETE_RECUR_SCHEDULE_STR = 		"스케줄 삭제";
		const DELETE_THIS_SCHEDULE_STR	=		"이 일정만 삭제";
		const DELETE_ALL_SCHEDULE_STR 	=		"반복된 일정 모두 삭제";

		const RADIO_USER_TITLE			=		"휴일 제목";
		const RADIO_USER_ADD			=		"추가";
		const SIBO_COPY_NAME			=		"스케줄 제목";
		const SIBO_COPY_TARGETDATE		=		"복사한 스케줄을 입력 할 날짜";
		const SIBO_COPY_COPYEDDATE		=		"복사한 날짜들";
	}

?>
