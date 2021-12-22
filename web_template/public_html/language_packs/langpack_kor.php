<?php // kor, language_packs 위에 존재하는 모든 언어팩들의 내용은 동일해야 함
	namespace Common\Lang {
		// Common
		const STR_FIRMWARE_VERSION 			=					"버전";
		const STR_TITLE_SETUP_PAGE 			=					"설정 페이지";

		// Script
		const STR_STAT_FORBIDDEN_MSG 		=					"페이지에 접근이 거부 되었습니다.";
		const STR_STAT_NOT_FOUND_MSG 		=					"페이지가 존재하지 않습니다.";
		const STR_STAT_INT_ERROR_MSG 		=					"서버에 내부적 오류가 발생 하였습니다.";
		const STR_STAT_UNKNOWN_ERR_MSG 		=					"알 수 없는 에러가 발생 하였습니다.";

		// Menu
		const STR_MENU_COMMON 				=					"공통";
		const STR_MENU_COMMON_MAIN 			=					"초기 화면";
		const STR_MENU_COMMON_HELP 			=					"도움말";

		const STR_MENU_SETUP_FUNCTION 		=					"동작 설정";
		const STR_MENU_SETUP_SYSTEM			=					"시스템 설정";
		const STR_MENU_SETUP_ADDON			=					"추가 기능";
		const STR_MENU_SETUP_MANAGEMENT		=					"관리 메뉴";
		const STR_MENU_SETUP_INFORMATION	=					"시스템 정보";
		const STR_MENU_SETUP_API			=					"API 관리";

		// Login
		const STR_LOGIN_FORM_TITLE			= 					"로그인";
		const STR_LOGIN_FORM_ID				= 					"아이디";
		const STR_LOGIN_FORM_PASSWD			= 					"패스워드";
		const STR_LOGIN_FORM_KEEP			= 					"로그인 상태 유지";
		const STR_LOGIN_ENTER_ID			= 					"ID를 입력 하세요.";
		const STR_LOGIN_ENTER_PASSWORD		= 					"비밀번호를 입력 하세요.";
		const STR_LOGIN_WRONG_INFO			= 					"잘못된 ID 또는 비밀번호 입니다.";
		const STR_LOGIN_WRONG_PASSWD		= 					"이용자 패스워드 오류입니다.";
		const STR_LOGIN_SUCCESS				=					"계정이 로그인 하였습니다.";
		const STR_LOGIN_VIEW_PC				=					"PC 버전";
		const STR_LOGIN_VIEW_MOBILE			=					"Mobile 버전";
		const STR_LOGIN_LIMIT				=					"접속이 제한되었습니다.";
		const STR_LOGIN_PASSWD_CHANGE		=					"비밀번호 변경";
		const STR_LOGIN_CURRENT_PASSWD		=					"현재 비밀번호";
		const STR_LOGIN_CHANGE_PASSWD		=					"변경할 비밀번호";
		const STR_LOGIN_CHECK_PASSWD		=					"변경할 비밀번호 확인";
		const STR_LOGIN_CHANGE_CONFIRM		=					"비밀번호 변경";
		const STR_LOGIN_CHANGE_NEXT			=					"다음에 변경";
		const STR_LOGIN_CONFIRM_PASSWD		=					"권장하지 않는 비밀번호를 사용중입니다. <br />비밀번호를 변경하시겠습니까?";
		const STR_LOGIN_INVALID_ID			=					"잘못된 ID 입니다.";
		const STR_LOGIN_PASSWD_CHECK_CURRENT	=				"현재 패스워드를 확인하세요.";
		const STR_LOGIN_PASSWD_CHECK_SAME		=				"현재 패스워드와 변경할 패스워드는 같을 수 없습니다.";
		const STR_LOGIN_PASSWD_CHECK_WRONG		=				"변경 패스워드가 일치하지 않습니다.";
		const STR_LOGIN_PASSWD_CHECK_COMPLETE	=				"패스워드가 변경 되었습니다.";
		const STR_LOGIN_PASSWD_MIN_LENGTH		=				"문자, 숫자, 특수문자 등을 조합하여 8글자 이상 사용하십시오.";

		const STR_LOADER_COMPLETE			=					"완료되었습니다.";

		// Auth
		const STR_AUTH_TITLE				=					"인증";
		const STR_AUTH_FILE_FIND			=					"파일 선택";
		const STR_AUTH_FILE_SELECT			=					"파일을 선택해 주세요.";
		const STR_AUTH_FILE_ALERT			=					"파일 확장자가 imkp 형식이 아닙니다.";
		const STR_AUTH_UPLOAD				=					"업로드";
		const STR_AUTH_UPLOAD_LIMIT			=					"업로드 용량을 초과하였습니다.";
		const STR_AUTH_CONFIRM				=					"시스템 인증을 하겠습니까?";

		const STR_AUTH_NOT_FOUND_FILE		=					"파일을 찾을 수 없습니다.";
		const STR_AUTH_UPLOAD_FAIL			=					"업로드에 실패 했습니다.";
		const STR_AUTH_FAIL					=					"인증이 실패했습니다.";
		const STR_AUTH_SUCCESS				=					"인증이 성공했습니다.";
		const STR_AUTH_FINISH				=					"인증이 완료되었습니다.";

		const STR_AUTH_BUTTON_SET			=					"적용";

		const STR_SYSTEM_CHECK_MSG_1		=					"시스템 점검이 시작됩니다.";
		const STR_SYSTEM_CHECK_MSG_2		=					"잠시후에 새로고침(F5) 해주세요.";
		const STR_SYSTEM_CHECK_LOG			=					"시스템 점검이 실행되었습니다.";
		const STR_SYSTEM_CHECK_END_MSG_1	=					"시스템 점검이 완료 되었습니다.";
		const STR_SYSTEM_CHECK_END_MSG_2	=					"이 알림은 클릭 시 사라집니다.";

		// Display info
		const STR_MENU_SYSTEM_DAYS			=					"일";
		const STR_MENU_SYSTEM_ELAPSED		=					"경과";

		// limit login
		const STR_LIMIT_LOGIN_TITLE			=					"최대 접속자 초과";
		const STR_LIMIT_LOGIN_USER			=					"사용자명";
		const STR_LIMIT_LOGIN_IP_ADDR		=					"IP 주소";
		const STR_LIMIT_LOGIN_TIME			=					"접속시간";
		const STR_LIMIT_LOGIN_DISCONNECT	=					"연결 끊기";
		const STR_LIMIT_LOGIN_CONFIRM_DISCN	=					"해당 연결을 해제시킵니다.";
		const STR_LIMIT_LOGIN_ALERT_REFRESH	=					"해당 연결을 찾을 수 없습니다. 새로고침(F5) 후 확인하세요.";

		// add-on language pack
		include_once "langpack_kor_etc.php";
	}
?>
