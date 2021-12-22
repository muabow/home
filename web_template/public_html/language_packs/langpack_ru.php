<?php // ru, language_packs 위에 존재하는 모든 언어팩들의 내용은 동일해야 함
	namespace Common\Lang {
		// Common
		const STR_FIRMWARE_VERSION 			=					"Версия ПО";
		const STR_TITLE_SETUP_PAGE 			=					"Настройки";

		// Script
		const STR_STAT_FORBIDDEN_MSG 		=					"Доступ к странице закрыт.";
		const STR_STAT_NOT_FOUND_MSG 		=					"Страница не существует.";
		const STR_STAT_INT_ERROR_MSG 		=					"Ошибка сервера.";
		const STR_STAT_UNKNOWN_ERR_MSG 		=					"Неизвестная ошибка.";

		// Menu
		const STR_MENU_COMMON 				=					"Общие";
		const STR_MENU_COMMON_MAIN 			=					"Главная";
		const STR_MENU_COMMON_HELP 			=					"помощь";

		const STR_MENU_SETUP_FUNCTION 		=					"Конфигурация";
		const STR_MENU_SETUP_SYSTEM 		=					"Система";
		const STR_MENU_SETUP_ADDON			=					"Сервер";
		const STR_MENU_SETUP_MANAGEMENT		=					"Управление";
		const STR_MENU_SETUP_INFORMATION	=					"Системная информация";
		const STR_MENU_SETUP_API			=					"Управление API";

		// Login
		const STR_LOGIN_FORM_TITLE			= 					"Вход";
		const STR_LOGIN_FORM_ID				= 					"ID";
		const STR_LOGIN_FORM_PASSWD			= 					"Пароль";
		const STR_LOGIN_FORM_KEEP			= 					"Охрана состояния логина";
		const STR_LOGIN_ENTER_ID 			= 					"Пожалуйста, введите ID.";
		const STR_LOGIN_ENTER_PASSWORD 		= 					"Пожалуйста, введите пароль.";
		const STR_LOGIN_WRONG_INFO 			= 					"Неверный ID или пароль.";
		const STR_LOGIN_WRONG_PASSWD		= 					"Ошибка пароля пользователя";
		const STR_LOGIN_SUCCESS				=					"Вход выполнен.";
		const STR_LOGIN_VIEW_PC				=					"Версия ПК";
		const STR_LOGIN_VIEW_MOBILE			=					"Мобильная версия";
		const STR_LOGIN_LIMIT				=					"Подключение ограничено.";
		const STR_LOGIN_PASSWD_CHANGE		=					"Сменить пароль";
		const STR_LOGIN_CURRENT_PASSWD		=					"Текущий пароль";
		const STR_LOGIN_CHANGE_PASSWD		=					"Пароль изменения";
		const STR_LOGIN_CHECK_PASSWD		=					"Подтвердите изменения";
		const STR_LOGIN_CHANGE_CONFIRM		=					"Сменить пароль";
		const STR_LOGIN_CHANGE_NEXT			=					"Следующее изменение";
		const STR_LOGIN_CONFIRM_PASSWD		=					"Мы используем неверный пароль. <br /> Вы хотите сменить пароль?";
		const STR_LOGIN_INVALID_ID			=					"Неверный идентификатор.";
		const STR_LOGIN_PASSWD_CHECK_CURRENT	=				"Пожалуйста, проверьте ваш текущий пароль.";
		const STR_LOGIN_PASSWD_CHECK_SAME		=				"Текущий пароль и пароль, который необходимо изменить, не могут быть одинаковыми.";
		const STR_LOGIN_PASSWD_CHECK_WRONG		=				"Смена паролей не совпадает.";
		const STR_LOGIN_PASSWD_CHECK_COMPLETE	=				"Ваш пароль был изменен.";
		const STR_LOGIN_PASSWD_MIN_LENGTH		=				"Пожалуйста, используйте не менее 8 букв в сочетании с буквами, цифрами, специальными символами и т. Д.";

		const STR_LOADER_COMPLETE			=					"Успешно завершен.";

		// Auth
		const STR_AUTH_TITLE				=					"Аутентификация";
		const STR_AUTH_FILE_FIND			=					"Выбор файла";
		const STR_AUTH_FILE_SELECT			=					"Пожалуйста, выберите файл.";
		const STR_AUTH_FILE_ALERT			=					"Расширение файла не imkp.";
		const STR_AUTH_UPLOAD				=					"Загрузка";
		const STR_AUTH_UPLOAD_LIMIT			=					"Превышена емкость загрузки.";
		const STR_AUTH_CONFIRM				=					"Запустить идентификацию?";

		const STR_AUTH_NOT_FOUND_FILE		=					"Файл не найден.";
		const STR_AUTH_UPLOAD_FAIL			=					"Ошибка загрузки.";
		const STR_AUTH_FAIL					=					"Ошибка идентификации.";
		const STR_AUTH_SUCCESS				=					"Идентификация прошла успешно.";
		const STR_AUTH_FINISH				=					"Индентификация завершена.";

		const STR_AUTH_BUTTON_SET			=					"Применить";

		const STR_SYSTEM_CHECK_MSG_1		=					"Проверка системы запущена.";
		const STR_SYSTEM_CHECK_MSG_2		=					"Обновите (F5) через некоторое время.";
		const STR_SYSTEM_CHECK_LOG			=					"Проверка системы выполнена.";
		const STR_SYSTEM_CHECK_END_MSG_1	=					"Проверка системы завершена.";
		const STR_SYSTEM_CHECK_END_MSG_2	=					"Это уведомление исчезнет при нажатии.";

		// Display info
		const STR_MENU_SYSTEM_DAYS			=					"дней";
		const STR_MENU_SYSTEM_ELAPSED		=					"истекшее";

		// limit login
		const STR_LIMIT_LOGIN_TITLE			=					"Превышение максимального пользователя";
		const STR_LIMIT_LOGIN_USER			=					"пользователь";
		const STR_LIMIT_LOGIN_IP_ADDR		=					"айпи адрес";
		const STR_LIMIT_LOGIN_TIME			=					"Время доступа";
		const STR_LIMIT_LOGIN_DISCONNECT	=					"Отключить";
		const STR_LIMIT_LOGIN_CONFIRM_DISCN	=					"Отключить соединение.";
		const STR_LIMIT_LOGIN_ALERT_REFRESH	=					"Эта связь не может быть найдена. Пожалуйста, проверьте после обновления (F5).";

		// add-on language pack
		include_once "langpack_ru_etc.php";
	}
?>