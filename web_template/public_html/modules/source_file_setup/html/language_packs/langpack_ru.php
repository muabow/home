<?php // ru, language_packs 위에 존재하는 모든 언어팩들의 내용은 동일해야 함
	namespace Source_file_setup\Lang {
		const STR_MENU_NAME					=					"Потоковый поток BGM";

		const STR_COMMON_IP_ADDR			=					"IP-адрес";
		const STR_COMMON_IP_M_ADDR			=					"IP-адрес (Multicast)";
		const STR_COMMON_PORT				=					"Порт";
		const STR_COMMON_APPLY				=					"Применить";
		const STR_COMMON_CANCEL				=					"Отмена";
		const STR_COMMON_SETUP				=					"Настройки";
		const STR_COMMON_STOP				=					"Стоп";
		const STR_COMMON_START				=					"Старт";
		const STR_COMMON_SERVER				=					"Сервер";
		const STR_COMMON_CLIENT				=					"клиент";
		const STR_COMMON_SEC				=					"сек";
		const STR_COMMON_MSEC				=					"мсек";

		const STR_JS_WRONG_IP_ADDR			=					"Неверный IP-адрес.";
		const STR_JS_WRONG_PORT				=					"Неверный порт.";
		const STR_JS_START_SERVER			=					"Запускает сервер вещания.";
		const STR_JS_START_CLIENT			=					"Запускает широковещательный клиент.";
		const STR_JS_WRONG_VOLUME			=					"Введите значение от 0 до 100.";

		const STR_SETUP_SERVER				=					"Широковещательный сервер";
		const STR_SETUP_CLIENT				=					"Широковещательный клиент";

		const STR_SERVER_SETUP_TITLE		=					"Параметры широковещательного сервера";
		const STR_SERVER_PROTOCOL			=					"Протокол передачи аудио";
		const STR_SERVER_PROTOCOL_INFO		=					"Информация";
		const STR_SERVER_CAST_TYPE			=					"Метод передачи";
		const STR_SERVER_ENCODE				=					"Метод кодирования";
		const STR_SERVER_PLAY_INFO			=					"Параметры кодирования";
		const STR_SERVER_PCM_SETUP			=					"Настройки PCM";
		const STR_SERVER_PCM_INFO			=					"Инфо. PCM";
		const STR_SERVER_SAMPLE_RATE		=					"Частота дискр.";
		const STR_SERVER_CHANNEL			=					"Канал";
		const STR_SERVER_MP3_SETUP			=					"Настройки MP3";
		const STR_SERVER_MP3_INFO			=					"Инфо. MP3";
		const STR_SERVER_MP3_SAMPLE_RATE	=					"Частота дискр.";
		const STR_SERVER_MP3_BIT_RATE		=					"битрейт";
		const STR_SERVER_MP3_HIGH			=					"High";
		const STR_SERVER_MP3_MEDIUM			=					"Medium";
		const STR_SERVER_MP3_LOW			=					"Low";
		const STR_SERVER_MP3_QUALITY		=					"качество";
		const STR_SERVER_OPER_INFO			=					"Сетевые настройки";
		const STR_SERVER_OPER_DEFAULT		=					"По умолчанию";
		const STR_SERVER_OPER_CHANGE		=					"Изменить";
		const STR_SERVER_OPER_SETUP			=					"Инфо";
		const STR_SERVER_OP_TITLE			=					"О программе передачи данных";
		const STR_SERVER_OP_RUN				=					"Широковещательный сервер работает.";
		const STR_SERVER_OP_STOP			=					"Сервер широковещательной передачи данных заблокирован.";
		const STR_SERVER_OP_SETUP_INFO		=					"Сведения о настройках широковещательного сервера";
		const STR_SERVER_LIST_TITLE			=					"Список подкл. клиентов";
		const STR_SERVER_LIST_NUM			=					"Список";
		const STR_SERVER_LIST_HOSTNAME		=					"Наименование";
		const STR_SERVER_LIST_STATUS		=					"Состояние";
		const STR_SERVER_LIST_CONN_TIME		=					"Время соединения";
		const STR_SERVER_LIST_NOTICE		=					"Многоадресная рассылка не поддерживает списки клиентских подключений.";
		const STR_SERVER_UNICAST		 	= 					"Unicast";
		const STR_SERVER_MULTICAST		 	= 					"Multicast";
		const STR_SERVER_CLIENT_CONNECT		=					"Клиент подключен.";
		const STR_SERVER_CLIENT_DISCONNECT	=					"Клиент отключился.";
		const STR_SERVER_SERVER_NOT_RUN		=					"Сервер не работает.";

		const STR_CLIENT_SETUP_TITLE		=					"Параметры широковещательного клиента";
		const STR_CLIENT_BUFFER				=					"Время буфер";
		const STR_CLIENT_BUFFER_SETUP		=					"Установка времени";
		const STR_CLIENT_REDUNDANCY			=					"Резервирование";
		const STR_CLIENT_REDUNDANCY_SETUP	=					"Параметры резервирования";
		const STR_CLIENT_REDUNDANCY_MASTER	=					"Один аудиосервер";
		const STR_CLIENT_REDUNDANCY_SLAVE	=					"Два аудиосервера";
		const STR_CLIENT_OP_TITLE			=					"Сведения о широкополосном вещании";
		const STR_CLIENT_OP_RUN				=					"Широковещательный клиент выполняется.";
		const STR_CLIENT_OP_STOP			=					"Широковещательный клиент прекращен";
		const STR_CLIENT_INFO_TITLE			=					"Широковещательный адрес клиента настройки информации";
		const STR_CLIENT_INFO_BUFFER		=					"Буферизация";
		const STR_CLIENT_INFO_SERVER		=					"Сведения о сервере";
		const STR_CLIENT_INFO_SERVER_MASTER	=					"Основной сервер";
		const STR_CLIENT_INFO_SERVER_SLAVE	=					"Резервный сервер";
		const STR_CLIENT_INFO_LEVEL_METER	=					"Уровень сигнала";
		const STR_CLIENT_INFO_VOLUME		=					"Громкость";
		const STR_CLIENT_INFO_VOLUME_COMPLETE=					"Громкость изменена.";
		const STR_CLIENT_CONNECT_FAILED		=					"соединение не удалось.";

		const STR_SRCFILE_TABLE				=					"Список аудиофайлов";
		const STR_SRCFILE_TABLE_COL_INDEX	=					"Номер";
		const STR_SRCFILE_TABLE_COL_FNAME	=					"Название";

		const STR_TITLE_NUMBER				=					"Число";
		const STR_TITLE_NAME				=					"название";
		const STR_TITLE_TYPE				=					"Тип";
		const STR_TITLE_INFO				=					"Информация.";
		const STR_TITLE_CHANNEL				=					"канал";
		const STR_TITLE_PLAY_TIME			=					"Время";
		const STR_TITLE_LOOP				=					"петля";

		const STR_SRC_LIST_TIME_MIN			=					"минут";
		const STR_SRC_LIST_TIME_SEC			=					"второй";
	}
?>
