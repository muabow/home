<?php // kor, language_packs 위에 존재하는 모든 언어팩들의 내용은 동일해야 함
	namespace Source_file_management\Lang {
		const STR_MENU_NAME									=					"음원 파일 관리";

		const STR_SRCFILE_BUTTON_RESET						=					"초기화";

		const STR_SRCFILE_ADD								=					"음원 파일 업로드";
		const STR_SRCFILE_ADD_FIND							=					"음원 파일 선택";
		const STR_SRCFILE_ADD_UPLOAD						=					"업로드";
		const STR_SRCFILE_ADD_SELECT						=					"음원 파일을 선택해 주세요.";
		const STR_SRCFILE_ADD_AVAILABLE_MEM					=					"가용 용량";
		const STR_SRCFILE_ADD_LIMIT_MEM_ALL					=					"저장 공간이 부족합니다.";
		const STR_SRCFILE_ADD_LIMIT_COUNT					=					"업로드 할 수 있는 파일의 갯수를 초과하였습니다.";
		const STR_SRCFILE_ADD_INVALID_TYPE					=					"음원 파일 확장자가 mp3 형식이 아닙니다.";
		const STR_SRCFILE_ADD_INVALID_NAME					=					"파일명에 적절하지 않은 특수문자(` * | \\\ / ? \\\" : < >)가 입력되어있습니다.";
		const STR_SRCFILE_ADD_DOUBLE_SPACE_NAME				=					"파일명이 연속된 공백을 포함하고 있습니다.";
		const STR_SRCFILE_ADD_NOT_FOUND_FILE				=					"파일을 찾을 수 없습니다.";
		const STR_SRCFILE_ADD_UPLOAD_CONFIRM				=					"음원 파일을 업로드 하시겠습니까?";
		const STR_SRCFILE_ADD_UPLOAD_APPLY					=					"음원 파일을 업로드합니다.";
		const STR_SRCFILE_ADD_UPLOAD_SUCCESS 				=					"업로드에 성공 하였습니다.";
		const STR_SRCFILE_ADD_UPLOAD_FAIL					=					"업로드에 실패 했습니다.";

		const STR_SRCFILE_ADD_UPLOAD_FAIL_NOTFOUND_FILE		= 					"파일 없음";
		const STR_SRCFILE_ADD_UPLOAD_FAIL_OVER_FILECNT		= 					"파일 갯수 초과";
		const STR_SRCFILE_ADD_UPLOAD_FAIL_OVER_SAVESIZE		= 					"저장 용량 초과";
		const STR_SRCFILE_ADD_UPLOAD_FAIL_UPLOAD_SIZE		= 					"업로드 용량";
		
		const STR_SRCFILE_TABLE								=					"음원 파일 목록";
		const STR_SRCFILE_TABLE_COL_INDEX					=					"번호";
		const STR_SRCFILE_TABLE_COL_FNAME					=					"파일명";

		const STR_SRCFILE_DEL								=					"삭제";
		const STR_SRCFILE_DEL_SELECT						=					"삭제할 음원 파일을 선택해 주세요.";
		const STR_SRCFEIL_DEL_EMPTY							=					"삭제할 파일이 없습니다.";
		const STR_SRCFILE_DEL_SELECT_APPLY					=					"선택된 음원 파일을 삭제합니다.";
		const STR_SRCFILE_DEL_SELECT_ALL_APPLY				=					"전체 음원 파일을 삭제합니다.";
		const STR_SRCFILE_DEL_SELECT_APPLY_AFTER_STOP		=					"재생 중인 오디오를 정지한 후 파일을 삭제합니다.";

		const STR_SRCFILE_PLAY		 		  			    =					"재생";
		const STR_SRCFILE_PLAY_SETUP 		  			    =					"재생 설정";
		const STR_SRCFILE_PLAY_ALL	 		  			    =					"전체 재생";
		const STR_SRCFILE_PLAY_REPEAT					    =					"반복 횟수";
		const STR_SRCFILE_PLAY_REPEAT_OVERRANGE    			=					"반복 횟수 범위에 맞지 않습니다.";
		const STR_SRCFILE_PLAY_STOP 		  			    =					"정지";
		const STR_SRCFILE_PLAY_STOP_APPLY		  			=					"오디오 재생을 정지합니다.";
		const STR_SRCFEIL_PLAY_EMPTY						=					"재생시킬 음원 파일이 없습니다.";
		const STR_SRCFILE_PLAY_SELECT	 	  			    =					"재생시킬 음원 파일을 선택해 주세요.";
		const STR_SRCFILE_PLAY_SELECT_APPLY				    =					"선택된 음원 파일을 재생합니다.";
		const STR_SRCFILE_PLAY_SELECT_ALL_APPLY	    		=					"전체 음원 파일을 재생합니다.";

		const STR_SRCFILE_SRC_PLAY							=					"음원 파일 재생";
		
		const STR_OPER_INFO									=					"동작 정보";
		const STR_INFO_LEVEL_METER							=					"레벨 미터";
		const STR_INFO_VOLUME								=					"음량";
		const STR_COMMON_APPLY								=					"적용";
		
		const STR_OTHER										=					"개";
		const STR_AND										=					"외";
		const STR_JS_WRONG_VOLUME							=					"잘못된 볼륨 값 입니다.(0~100)";
	}
?>