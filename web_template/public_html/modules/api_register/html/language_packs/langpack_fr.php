<?php // fr, language_packs 위에 존재하는 모든 언어팩들의 내용은 동일해야 함
	namespace Api_register\Lang {
		const STR_MENU_NAME				=					"Ouvrir le registre de l&#39;API";

		const STR_ADD_USER_CONFIRM		=					"Ajouter une clé.";
		const STR_ADD_USER_REGIST		= 					"Il a été enregistré.";
		const STR_REMOVE_USER_NONE	 	= 					"Il n'y a pas de cible.";
		const STR_REMOVE_USER_REMOVE	=					"Supprimer une clé.";
		const STR_ADD_KEY_ADD			=					"La clé a été ajoutée.";
		const STR_ADD_KEY_DELETE		=					"La clé a été supprimée.";

		const STR_ADD_IPADDR_CHECK		= 					"Vérifiez l'adresse IP.";
		const STR_ADD_IPADDR_CONFIRM	=					"Ajouter l'appareil.";
		const STR_ADD_IPADDR_SAME		= 					"Il y a la même adresse IP.";

		const STR_BODY_REMOVE			=					"Effacer";
		const STR_BODY_SUBMIT			=					"Ajouter";

		const STR_MENU_REGISTER			=					"Enregistrer";
		const STR_MENU_LIST				=				 	"Liste";

		const STR_MENU_EMAIL			=					"Email";
		const STR_MENU_CONTACT			=					"Informations de contact";
		const STR_MENU_COMPANY			=					"Compagnie";
		const STR_MENU_SERVER_ADDR		=					"Serveur émetteur";

		const STR_MENU_ID_KEY			=					"Clé d&#39;identification";
		const STR_MENU_SECRET_KEY		=					"Clef secrète";
		const STR_MENU_DAY_USAGE		=					"Utilisation de jour";
		const STR_MENU_CUM_USAGE		=					"Accumuler l&#39;utilisation";

		const STR_JS_EMPTY_ADDR			=					"Veuillez entrer l'adresse du serveur.";
		const STR_JS_EMPTY_KEY			=					"Veuillez entrer la clé d'identité.";
		const STR_JS_EMPTY_SECRET		=					"Veuillez entrer une clé secrète.";
		const STR_JS_DUP_ID				=					"Il y a la même clé secrète.";
		
		const STR_JS_SERVER_CANT_CONN			= 			"Les données de clé ne peuvent pas être ajoutées car il n'y a pas de communication avec le serveur.";
		const STR_JS_SERVER_INVALID_VER 		= 			"Les données de clé ne peuvent pas être ajoutées car la version du serveur n'est pas valide.";
		const STR_JS_SERVER_ISNOT_COMPATIBLE 	= 			"TLes données de clé ne peuvent pas être ajoutées car la version du serveur n'est pas compatible avec le périphérique actuel.";
		const STR_JS_SERVER_INVALID_KEY_INFO 	= 			"Il ne correspond pas aux informations clés émises par le serveur.";
	}
?>