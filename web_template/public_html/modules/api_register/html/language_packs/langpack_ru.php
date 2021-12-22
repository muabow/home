<?php // ru, language_packs 위에 존재하는 모든 언어팩들의 내용은 동일해야 함
	namespace Api_register\Lang {
		const STR_MENU_NAME				=					"Ключи регистрации";

		const STR_ADD_USER_CONFIRM		=					"Добавить ключ?";
		const STR_ADD_USER_REGIST		= 					"Ключ добавлен.";
		const STR_REMOVE_USER_NONE 		= 					"Не удалось добавить ключ.";
		const STR_REMOVE_USER_REMOVE	=					"Удалить ключ?";
		const STR_ADD_KEY_ADD			=					"Ключ добавлен.";
		const STR_ADD_KEY_DELETE		=					"Ключ удален.";

		const STR_ADD_IPADDR_CHECK		= 					"Некорректный IP-адрес.";
		const STR_ADD_IPADDR_CONFIRM	=					"Добавить устройство.";
		const STR_ADD_IPADDR_SAME		= 					"IP-адрес уже используется.";

		const STR_BODY_REMOVE			=					"Удалить";
		const STR_BODY_SUBMIT			=					"Добавить";

		const STR_MENU_REGISTER			=					"Регистрация";
		const STR_MENU_LIST				=					"Список";

		const STR_MENU_EMAIL			=					"Электронная почта";
		const STR_MENU_CONTACT			=					"Контакты";
		const STR_MENU_COMPANY			=					"Компания";
		const STR_MENU_SERVER_ADDR		=					"IP-адрес сервера";

		const STR_MENU_ID_KEY			=					"Ключ идентификации";
		const STR_MENU_SECRET_KEY		=					"Секретный ключ";
		const STR_MENU_DAY_USAGE		=					"Кол-во подключений в день";
		const STR_MENU_CUM_USAGE		=					"Кол-во подключений";

		const STR_JS_EMPTY_ADDR			=					"Введите IP-адрес сервера.";
		const STR_JS_EMPTY_KEY			=					"Введите ключ идентификации.";
		const STR_JS_EMPTY_SECRET		=					"Введите секретный ключ.";
		const STR_JS_DUP_ID				=					"Секретный ключ уже используется.";
		
		const STR_JS_SERVER_CANT_CONN			= 			"Данные ключа не могут быть добавлены, потому что нет связи с сервером.";
		const STR_JS_SERVER_INVALID_VER 		= 			"Данные ключа не могут быть добавлены, поскольку версия сервера недействительна.";
		const STR_JS_SERVER_ISNOT_COMPATIBLE 	= 			"Данные ключа не могут быть добавлены, поскольку версия сервера не совместима с текущим устройством.";
		const STR_JS_SERVER_INVALID_KEY_INFO 	= 			"Он не соответствует ключевой информации, выданной сервером.";
	}
?>