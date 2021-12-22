<?php // eng, language_packs 위에 존재하는 모든 언어팩들의 내용은 동일해야 함
	namespace Common\Lang {
		// Common
		const STR_FIRMWARE_VERSION 			=					"Version";
		const STR_TITLE_SETUP_PAGE 			=					"Setting Page";

		// Script
		const STR_STAT_FORBIDDEN_MSG 		=					"Access to the page is denied.";
		const STR_STAT_NOT_FOUND_MSG 		=					"The page does not exist.";
		const STR_STAT_INT_ERROR_MSG 		=					"Internal error occurred on the server.";
		const STR_STAT_UNKNOWN_ERR_MSG 		=					"An unknown error has occurred.";

		// Menu
		const STR_MENU_COMMON 				=					"Common";
		const STR_MENU_COMMON_MAIN 			=					"Initial Screen";
		const STR_MENU_COMMON_HELP 			=					"Help";

		const STR_MENU_SETUP_FUNCTION 		=					"Operation Setting";
		const STR_MENU_SETUP_SYSTEM			=					"System Setting";
		const STR_MENU_SETUP_ADDON			=					"Additional Function";
		const STR_MENU_SETUP_MANAGEMENT		=					"Management Menu";
		const STR_MENU_SETUP_INFORMATION	=					"System Information";
		const STR_MENU_SETUP_API			=					"API Management";

		// Login
		const STR_LOGIN_FORM_TITLE			= 					"Login";
		const STR_LOGIN_FORM_ID				= 					"ID";
		const STR_LOGIN_FORM_PASSWD			= 					"Password";
		const STR_LOGIN_FORM_KEEP			= 					"Keep me signed in";
		const STR_LOGIN_ENTER_ID			= 					"Please enter your ID.";
		const STR_LOGIN_ENTER_PASSWORD		= 					"Please enter your password.";
		const STR_LOGIN_WRONG_INFO			= 					"Invalid ID or password.";
		const STR_LOGIN_WRONG_PASSWD		= 					"User password error";
		const STR_LOGIN_SUCCESS				=					"Account is logged in.";
		const STR_LOGIN_VIEW_PC				=					"PC version";
		const STR_LOGIN_VIEW_MOBILE			=					"Mobile version";
		const STR_LOGIN_LIMIT				=					"Connection is restricted.";
		const STR_LOGIN_PASSWD_CHANGE		=					"Change Password";
		const STR_LOGIN_CURRENT_PASSWD		=					"Current Password";
		const STR_LOGIN_CHANGE_PASSWD		=					"Password to change";
		const STR_LOGIN_CHECK_PASSWD		=					"Confirm password";
		const STR_LOGIN_CHANGE_CONFIRM		=					"Change password";
		const STR_LOGIN_CHANGE_NEXT			=					"Next change";
		const STR_LOGIN_CONFIRM_PASSWD		=					"using unrecommended password. <br /> Do you want to change your password?";
		const STR_LOGIN_INVALID_ID			=					"Invalid ID.";
		const STR_LOGIN_PASSWD_CHECK_CURRENT	=				"Please check your current password.";
		const STR_LOGIN_PASSWD_CHECK_SAME		=				"The current password and the password to be changed can not be the same.";
		const STR_LOGIN_PASSWD_CHECK_WRONG		=				"Change passwords do not match.";
		const STR_LOGIN_PASSWD_CHECK_COMPLETE	=				"Your password has been changed.";
		const STR_LOGIN_PASSWD_MIN_LENGTH		=				"Please use at least 8 letters in combination with letters, numbers, special characters, and so on.";

		const STR_LOADER_COMPLETE			=					"It has been completed.";

		// Auth
		const STR_AUTH_TITLE				=					"Authentication";
		const STR_AUTH_FILE_FIND			=					"Select file";
		const STR_AUTH_FILE_SELECT			=					"Please select a file.";
		const STR_AUTH_FILE_ALERT			=					"The file extension is not in imkp format.";
		const STR_AUTH_UPLOAD				=					"Upload";
		const STR_AUTH_UPLOAD_LIMIT			=					"Uploaded capacity exceeded.";
		const STR_AUTH_CONFIRM				=					"Do you want to authenticate the system?";

		const STR_AUTH_NOT_FOUND_FILE		=					"The file cannot be found.";
		const STR_AUTH_UPLOAD_FAIL			=					"Upload failed.";
		const STR_AUTH_FAIL					=					"Authentication failed.";
		const STR_AUTH_SUCCESS				=					"Authentication succeeded.";
		const STR_AUTH_FINISH				=					"Authentication is complete.";

		const STR_AUTH_BUTTON_SET			=					"Apply";

		const STR_SYSTEM_CHECK_MSG_1		=					"System check started.";
		const STR_SYSTEM_CHECK_MSG_2		=					"Refresh(F5) after a while.";
		const STR_SYSTEM_CHECK_LOG			=					"The system check has been executed.";
		const STR_SYSTEM_CHECK_END_MSG_1	=					"System check is completed.";
		const STR_SYSTEM_CHECK_END_MSG_2	=					"This notification will disappear on clicking.";

		// Display info
		const STR_MENU_SYSTEM_DAYS			=					"Days";
		const STR_MENU_SYSTEM_ELAPSED		=					"Elapsed";

		// limit login
		const STR_LIMIT_LOGIN_TITLE			=					"Exceed the maximum user";
		const STR_LIMIT_LOGIN_USER			=					"Username";
		const STR_LIMIT_LOGIN_IP_ADDR		=					"IP address";
		const STR_LIMIT_LOGIN_TIME			=					"Access time";
		const STR_LIMIT_LOGIN_DISCONNECT	=					"Disconnect";
		const STR_LIMIT_LOGIN_CONFIRM_DISCN	=					"Disconnect the connection.";
		const STR_LIMIT_LOGIN_ALERT_REFRESH	=					"This connection could not be found. Please check after refresh (F5).";

		// add-on language pack
		include_once "langpack_eng_etc.php";
	}
?>
