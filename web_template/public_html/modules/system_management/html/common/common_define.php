<?php
	// PHP에서 사용되는 매크로(define), 및 상수들의 선언들의 집합 문서(header) 입니다.
	namespace System_management\Def {

        // - Define : HTTP path
        const PATH_HOME                 =           "modules/system_management/html";
        const PATH_WEB_CSS_STYLE        =           "modules/system_management/html/css/style.css";
		const PATH_WEB_CSS_STYLE_MOBILE =           "modules/system_management/html/css/style_m.css";
		const PATH_UPLOAD_PROCESS 		=           "modules/system_management/html/common/common_upload.php";
		const PATH_SYSTEM_PROCESS 		=           "modules/system_management/html/common/system_process.php";
		
		const UPGRADE_FILE_NAME			=			"firmware_upgrade.imkp";
		
		const NUM_LOGIN_TRY_COUNT		=			5;
		const NUM_MIN_PASSWD_LENGTH		=			8;
    }
?>
