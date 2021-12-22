<?php // fr, language_packs 위에 존재하는 모든 언어팩들의 내용은 동일해야 함
	namespace Audio_setup\Lang {
		const STR_MENU_NAME 				=					"Réglage audio";

		const STR_COMMON_IP_ADDR 			=					"Adresse IP";
		const STR_COMMON_IP_M_ADDR			=					"Adresse IP MultiCast";
		const STR_COMMON_PORT 				=					"Port";
		const STR_COMMON_APPLY 				=					"Appliquer";
		const STR_COMMON_CANCEL 			=					"Annuler";
		const STR_COMMON_SETUP				=					"Réglage";
		const STR_COMMON_STOP 				=					"Arrêtez";
		const STR_COMMON_START 				=					"Démarrer";
		const STR_COMMON_SERVER				=					"Serveur";
		const STR_COMMON_CLIENT				=					"Client";
		const STR_COMMON_SEC 				=					"Secondes";
		const STR_COMMON_MSEC 				=					"Millisecondes";

		const STR_JS_WRONG_IP_ADDR 			=					"Adresse IP non valide.";
		const STR_JS_WRONG_PORT 			=					"Port invalide.";
		const STR_JS_START_AUDIO_SERVER 	=					"Démarre le serveur audio.";
		const STR_JS_START_AUDIO_CLIENT 	=					"Démarre le client audio.";
		const STR_JS_WRONG_VOLUME			=					"Volume invalide. (0 ~ 100)";

		const STR_SETUP_AUDIO_SERVER 		=					"Serveur audio";
		const STR_SETUP_AUDIO_CLIENT 		=					"Client audio";

		const STR_SERVER_SETUP_TITLE 		=					"Paramètres du serveur audio";
		const STR_SERVER_PROTOCOL 			=					"Méthode de communication";
		const STR_SERVER_PROTOCOL_INFO 		=					"Informations de communication";
		const STR_SERVER_CAST_TYPE 			=					"Méthode de transmission";
		const STR_SERVER_ENCODE 			=					"Méthode d&#39;encodage";
		const STR_SERVER_PLAY_INFO 			=					"Lire l&#39;information";
		const STR_SERVER_PCM_SETUP 			=					"Réglage de la méthode PCM";
		const STR_SERVER_PCM_INFO 			=					"Informations de codage PCM";
		const STR_SERVER_SAMPLE_RATE 		=					"Taux d&#39;échantillonnage";
		const STR_SERVER_CHANNEL 			=					"Canal";
		const STR_SERVER_MP3_SETUP 			=					"Réglage de la méthode MP3";
		const STR_SERVER_MP3_INFO 			=					"Informations d&#39;encodage MP3";
		const STR_SERVER_MP3_SAMPLE_RATE 	=					"Taux d&#39;échantillonnage";
		const STR_SERVER_MP3_BIT_RATE 		=					"débit binaire";
		const STR_SERVER_MP3_HIGH			=					"High";
		const STR_SERVER_MP3_MEDIUM			=					"Medium";
		const STR_SERVER_MP3_LOW			=					"Low";
		const STR_SERVER_MP3_QUALITY		=					"La qualité";
		const STR_SERVER_OPER_INFO 			=					"Informations sur le fonctionnement";
		const STR_SERVER_OPER_DEFAULT 		=					"Informations de base";
		const STR_SERVER_OPER_CHANGE 		=					"Changement d&#39;information";
		const STR_SERVER_OPER_SETUP 		=					"Réglage des informations de fonctionnement";
		const STR_SERVER_OP_TITLE 			=					"Informations sur le fonctionnement du serveur audio";
		const STR_SERVER_OP_RUN 			=					"Le serveur audio est en cours d&#39;exécution.";
		const STR_SERVER_OP_STOP 			=					"Le serveur audio a été arrêté.";
		const STR_SERVER_OP_SETUP_INFO 		=					"Informations de configuration du serveur audio";
		const STR_SERVER_LIST_TITLE 		=					"Liste de connexion client";
		const STR_SERVER_LIST_NUM 			=					"Liste";
		const STR_SERVER_LIST_HOSTNAME 		=					"Nom d&#39;hôte";
		const STR_SERVER_LIST_STATUS 		=					"Statut";
		const STR_SERVER_LIST_CONN_TIME 	=					"Temps d&#39;accès/disconn.";
		const STR_SERVER_LIST_NOTICE		=					"La multidiffusion ne prend pas en charge les listes de connexions client.";
		const STR_SERVER_UNICAST		 	= 					"Unicast";
		const STR_SERVER_MULTICAST		 	= 					"Multicast";
		const STR_SERVER_CLIENT_CONNECT		=					"Le client est connecté.";
		const STR_SERVER_CLIENT_DISCONNECT	=					"Le client s&#39;est déconnecté.";

		const STR_CLIENT_SETUP_TITLE 		=					"Paramètres du client audio";
		const STR_CLIENT_BUFFER 			=					"Temps tampon";
		const STR_CLIENT_BUFFER_SETUP 		=					"Définir le temps de mise en mémoire tampon";
		const STR_CLIENT_REDUNDANCY 		=					"Redondance";
		const STR_CLIENT_REDUNDANCY_SETUP 	=					"Définir les informations d&#39;opération de redondance";
		const STR_CLIENT_REDUNDANCY_MASTER 	=					"Opération sur un seul serveur";
		const STR_CLIENT_REDUNDANCY_SLAVE	=					"Fonctionnement sur deux serveurs";
		const STR_CLIENT_OP_TITLE 			=					"Informations sur le fonctionnement du client audio";
		const STR_CLIENT_OP_RUN 			=					"Le client audio est en cours d&#39;exécution.";
		const STR_CLIENT_OP_STOP 			=					"Le client audio a été arrêté.";
		const STR_CLIENT_INFO_TITLE 		=					"Informations de configuration du client audio";
		const STR_CLIENT_INFO_BUFFER		=					"Informations de mise en mémoire tampon";
		const STR_CLIENT_INFO_SERVER 		=					"Informations sur le serveur";
		const STR_CLIENT_INFO_SERVER_MASTER =					"Serveur principal";
		const STR_CLIENT_INFO_SERVER_SLAVE 	=					"Serveur redondant";
		const STR_CLIENT_INFO_LEVEL_METER 	=					"Indicateur de niveau";
		const STR_CLIENT_INFO_VOLUME 		=					"Le volume";
		const STR_CLIENT_INFO_VOLUME_COMPLETE=					"Il a été changé en.";
		const STR_CLIENT_CONNECT_FAILED		=					"la connexion a échoué.";

		const STR_MATCH_SAMPLE_RATE_SERVER	=					"Doit utiliser la même fréquence d'échantillonnage que le serveur audio en cours d'exécution.";
		const STR_MATCH_SAMPLE_RATE_CLIENT	=					"Doit utiliser la même fréquence d'échantillonnage que le client audio en cours d'exécution.";
	}
?>