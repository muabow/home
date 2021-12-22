<?php
	// PHP에서 사용되는 매크로(define), 및 상수들의 선언들의 집합 문서(header) 입니다.
	namespace Source_file_management\Def {

        // - Define : HTTP path
        const PATH_HOME                 =           "modules/source_file_management/html";
		const PATH_BIN 	                =           "modules/source_file_management/bin";
        const PATH_WEB_CSS_STYLE        =           "modules/source_file_management/html/css/style.css";
		const PATH_WEB_CSS_STYLE_MOBILE =           "modules/source_file_management/html/css/style_m.css";
		const PATH_UPLOAD_PROCESS 		=           "modules/source_file_management/html/common/common_upload.php";
		const PATH_SRCFILE_PROCESS 		=           "modules/source_file_management/html/common/source_file_process.php";
		const PATH_IMG_PLAYLIST			=           "modules/source_file_management/html/img/black.svg";
		const PATH_IMG_PLAY				=           "modules/source_file_management/html/img/blue.svg";
		
		// Bin Name
		const BIN_NAME_AUDIOPLAYER 		=           "audio_player";
		
		// STORE Path
		const PATH_SRCFILE_STORAGE		=			"data/audiofiles/";
		
		// PLAY
		const MAX_AVAILABLE_MEM_SIZE 	= 1073741824; //1024 * 1024 * 1024
		
		const MAX_SRCFILE_COUNT			= 700;
		
		const MIN_PLAY_REPEAT_COUNT		= 0; 
		const MAX_PLAY_REPEAT_COUNT		= 20;
		
		const NUM_SERVER_PORT			= 8888;
		 
    }
?>
