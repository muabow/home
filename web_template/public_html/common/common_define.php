<?php
namespace Common\Def {
	// - Define : HTTP path
	const PATH_WEB_CSS_STYLE		=			"/css/style.css";
	const PATH_WEB_CSS_STYLE_MOBILE	=			"/css/style_m.css";
	const PATH_WEB_CSS_JQUERY_UI	=			"/css/jquery-ui.css";
	const PATH_WEB_IMG_FAVICON		= 			"/img/favicon.ico";
	const PATH_WEB_JS_JQUERY 		=			"/js/jquery-3.1.1.js";
	const PATH_WEB_JS_JQUERY_UI		=			"/js/jquery-ui.js";
	const PATH_WEB_JS_AJAX 			=			"/js/ajax_request.js";

	// - Define : System path
	const PATH_SYS_ENV_JSON			=			"../conf/env.json";
	const PATH_AJAX_INDEX			=			"/index.php";
	const PATH_AJAX_COMMON_PROCESS  =           "/common/common_process.php";
	const PATH_AUTH_UPLOAD_PROCESS	=			"/common/common_auth_upload.php";

	// - Common
	// -- Header

	// -- Banner
	const PATH_IMG_ICON_USER		=			"/img/icon_user.png";
	const PATH_IMG_ICON_SETUP 		=			"/img/icon_setup.png";
	const PATH_IMG_ICON_LOGOUT 		=			"/img/icon_logout.png";
	const PATH_IMG_ICON_LANGUAGE 	=			"/img/icon_language.png";
	const PATH_IMG_ICON_HOME 		=			"/img/icon_home.png";

	// -- Main
	const AJAX_ARGS_CONTENTS 		=			"main_contents";

	// -- Footer
	const ENV_LANGUAGE_KOR 			=			"kor";

	// - define : Common
	const JSON_MODULE_CATEGORY 		=			"category";
	const JSON_MODULE_MENU 			=			"menu";
	const JSON_MODULE_VIEW 			=			"view";

	const READY_STAT_SUCCESS 		=			4;

	const STATUS_SUCCESS 			=			200;
	const STATUS_FORBIDDEN 			= 			403;
	const STATUS_NOT_FOUND 			= 			404;
	const STATUS_INT_ERROR 			= 			500;

	const RESPONSE_SUCCESS 			= 			0;
	const RESPONSE_ERROR 			= 			1;

	const SYSTEM_VENDOR_MAC_ADDR	=			"00:1D:1D:";
	const SESSION_LOGOUT_TIME		=			600;	// 10분

	const FIFO_WEB_PIPE				=			"/tmp/web_fifo";

	const NUM_LOGIN_TRY_COUNT		=			5;

	// - define : Script - LOG

	const SIZE_LOG_BYTE             =           1024;	// bytes = 1K bytes
	const SIZE_LOG_LINE             =           10240;	// 1K x Line = 10M bytes

	const LOG_LEVEL_FATAL           =           "[FATAL] ";
	const LOG_LEVEL_ERROR           =           "[ERROR] ";
	const LOG_LEVEL_WARN            =           "[WARN]  ";
	const LOG_LEVEL_INFO            =           "[INFO]  ";
	const LOG_LEVEL_DEBUG           =           "[DEBUG] ";

	const AJAX_PROC_TYPE			=			"type";

	const AJAX_TYPE_LOG				=			"log";

	const TYPE_LOG_MODULE			=			"module";
	const TYPE_LOG_LEVEL			=			"level";
	const TYPE_LOG_MESSAGE			=			"message";
	const TYPE_LOG_ACT				=			"act";
	const TYPE_LOG_ACT_CLEAR		=			"clear";
	const TYPE_LOG_ACT_REMOVE		=			"remove";
	const TYPE_LOG_ACT_WRITE		=			"write";

	include_once "{$_SERVER['DOCUMENT_ROOT']}/common/common_define_etc.php";
}

?>
