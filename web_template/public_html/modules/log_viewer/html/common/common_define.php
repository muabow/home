<?php
	namespace Log_viewer\Def {

		// - Define : HTTP path
		const PATH_HOME                 =           "modules/log_viewer/html";
		const PATH_WEB_CSS_STYLE        =           "modules/log_viewer/html/css/style.css";
		const PATH_WEB_CSS_STYLE_MOBILE =           "modules/log_viewer/html/css/style_m.css";

		const PATH_AJAX_LOG_PROCESS		=			"modules/log_viewer/html/common/log_process.php";

		const TYPE_MODULE_COMMON		=			"common";

		const OPTION_VALUE_ENABLED		=			"enabled";
		const OPTION_VALUE_DISABLED		=			"disabled";
		const OPTION_VALUE_AUTO			=			"auto";
		const OPTION_VALUE_MANUAL		=			"manual";

		const OPTION_MAX_UPDATE_TIME	=			10;
		const LOG_DISPLAY_LINE_UNIT		=			array(100, 400, 700, 1000, 10240);
	}
?>
