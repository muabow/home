<?php // fr, language_packs 위에 존재하는 모든 언어팩들의 내용은 동일해야 함, (특수문자 : ' = &#39;)
	namespace Common\Lang {
		// Common
		const STR_FIRMWARE_VERSION 			=					"Version";
		const STR_TITLE_SETUP_PAGE 			=					"Page de configuration";

		// Script
		const STR_STAT_FORBIDDEN_MSG 		=					"L&#39;accès à la page est refusé.";
		const STR_STAT_NOT_FOUND_MSG 		=					"La page n&#39;existe pas.";
		const STR_STAT_INT_ERROR_MSG 		=					"Une erreur interne s&#39;est produite sur le serveur.";
		const STR_STAT_UNKNOWN_ERR_MSG 		=					"Une erreur inconnue s&#39;est produite.";

		// Menu
		const STR_MENU_COMMON 				=					"Commun";
		const STR_MENU_COMMON_MAIN 			=					"Écran initial";
		const STR_MENU_COMMON_HELP 			=					"L&#39;aide";

		const STR_MENU_SETUP_FUNCTION 		=					"Parametres de fonctionnement";
		const STR_MENU_SETUP_SYSTEM 		=					"Parametre du système";
		const STR_MENU_SETUP_ADDON			=					"Fonction supplémentaires";
		const STR_MENU_SETUP_MANAGEMENT		=					"Menu de gestion";
		const STR_MENU_SETUP_INFORMATION	=					"Informations système";
		const STR_MENU_SETUP_API			=					"gestion de l&#39;API";

		// Login
		const STR_LOGIN_FORM_TITLE			= 					"login";
		const STR_LOGIN_FORM_ID				= 					"ID";
		const STR_LOGIN_FORM_PASSWD			= 					"password";
		const STR_LOGIN_FORM_KEEP			= 					"Conserver l&#39;état de connexion";
		const STR_LOGIN_ENTER_ID 			= 					"Veuillez entrer votre ID.";
		const STR_LOGIN_ENTER_PASSWORD 		= 					"Veuillez entrer votre mot de passe.";
		const STR_LOGIN_WRONG_INFO 			= 					"ID ou mot de passe non valide.";
		const STR_LOGIN_WRONG_PASSWD		= 					"Erreur de mot de passe utilisateur";
		const STR_LOGIN_SUCCESS				=					"Vous etes connecté .";
		const STR_LOGIN_VIEW_PC				=					"Version PC";
		const STR_LOGIN_VIEW_MOBILE			=					"Version mobile";
		const STR_LOGIN_LIMIT				=					"La connexion est restreinte.";
		const STR_LOGIN_PASSWD_CHANGE		=					"Changer le mot de passe";
		const STR_LOGIN_CURRENT_PASSWD		=					"Mot de passe actuel";
		const STR_LOGIN_CHANGE_PASSWD		=					"passe pour changer";
		const STR_LOGIN_CHECK_PASSWD		=					"Confirmer passe";
		const STR_LOGIN_CHANGE_CONFIRM		=					"Changer le mot de passe";
		const STR_LOGIN_CHANGE_NEXT			=					"Changement suivant";
		const STR_LOGIN_CONFIRM_PASSWD		=					"Nous utilisons un mot de passe invalide. <br /> Voulez-vous changer votre mot de passe?";
		const STR_LOGIN_INVALID_ID			=					"Identifiant invalide.";
		const STR_LOGIN_PASSWD_CHECK_CURRENT	=				"Veuillez vérifier votre mot de passe actuel.";
		const STR_LOGIN_PASSWD_CHECK_SAME		=				"Le mot de passe actuel et le mot de passe à modifier ne peuvent pas être les mêmes.";
		const STR_LOGIN_PASSWD_CHECK_WRONG		=				"Changer les mots de passe ne correspondent pas.";
		const STR_LOGIN_PASSWD_CHECK_COMPLETE	=				"Votre mot de passe a été changé.";
		const STR_LOGIN_PASSWD_MIN_LENGTH		=				"Veuillez utiliser au moins 8 lettres en combinaison avec des lettres, des chiffres, des caractères spéciaux, etc.";

		const STR_LOADER_COMPLETE			=					"il a été terminé.";

		// Auth
		const STR_AUTH_TITLE				=					"Authentification";
		const STR_AUTH_FILE_FIND			=					"Selection fichier";
		const STR_AUTH_FILE_SELECT			=					"Veuillez sélectionner un fichier.";
		const STR_AUTH_FILE_ALERT			=					"l&#39;extension de fichier n&#39;est pas au format imkp.";
		const STR_AUTH_UPLOAD				=					"Telecharger";
		const STR_AUTH_UPLOAD_LIMIT			=					"capacité transférée dépassée.";
		const STR_AUTH_CONFIRM				=					"Voulez-vous authentifier le système?";

		const STR_AUTH_NOT_FOUND_FILE		=					"Le fichier est introuvable.";
		const STR_AUTH_UPLOAD_FAIL			=					"Le téléchargement a échoué.";
		const STR_AUTH_FAIL					=					"Echec de l&#39;authentification.";
		const STR_AUTH_SUCCESS				=					"Authentification réussie.";
		const STR_AUTH_FINISH				=					"L&#39;authentification est terminée.";

		const STR_AUTH_BUTTON_SET			=					"Appliquer";

		const STR_SYSTEM_CHECK_MSG_1		=					"Vérification du système démarrée.";
		const STR_SYSTEM_CHECK_MSG_2		=					"Actualiser (F5) après un certain temps.";
		const STR_SYSTEM_CHECK_LOG			=					"la vérification du système a été exécutée.";
		const STR_SYSTEM_CHECK_END_MSG_1	=					"La vérification du système est terminée.";
		const STR_SYSTEM_CHECK_END_MSG_2	=					"Cette notification disparaîtra en cliquant.";

		// Display info
		const STR_MENU_SYSTEM_DAYS			=					"Journées";
		const STR_MENU_SYSTEM_ELAPSED		=					"Écoulé";

		// limit login
		const STR_LIMIT_LOGIN_TITLE			=					"Dépasser le nombre maximal d&#39;utilisateurs";
		const STR_LIMIT_LOGIN_USER			=					"Nom d&#39;utilisateur";
		const STR_LIMIT_LOGIN_IP_ADDR		=					"adresse IP";
		const STR_LIMIT_LOGIN_TIME			=					"Temps d&#39;accès";
		const STR_LIMIT_LOGIN_DISCONNECT	=					"Déconnecter";
		const STR_LIMIT_LOGIN_CONFIRM_DISCN	=					"Déconnectez la connexion.";
		const STR_LIMIT_LOGIN_ALERT_REFRESH	=					"Cette connexion n&#39;a pas pu être trouvée. Veuillez vérifier après le rafraîchissement (F5).";

		// add-on language pack
		include_once "langpack_fr_etc.php";
	}
?>