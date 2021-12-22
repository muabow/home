<?php // fr, language_packs 위에 존재하는 모든 언어팩들의 내용은 동일해야 함
	namespace Source_file_management\Lang {
		const STR_MENU_NAME									=					"Gestion des fichiers sources";

		const STR_SRCFILE_BUTTON_RESET						=					"Initialiser";

		const STR_SRCFILE_ADD								=					"Téléchargement du fichier source";
		const STR_SRCFILE_ADD_FIND							=					"Sélectionner le fichier source";
		const STR_SRCFILE_ADD_UPLOAD						=					"Télécharger";
		const STR_SRCFILE_ADD_SELECT						=					"Veuillez sélectionner le fichier source.";
		const STR_SRCFILE_ADD_AVAILABLE_MEM					=					"Capacité disponible";
		const STR_SRCFILE_ADD_LIMIT_MEM_ALL					=					"Espace de stockage insuffisant.";
		const STR_SRCFILE_ADD_LIMIT_COUNT					=					"Augmenter le nombre de fichiers pouvant être téléchargés.";
		const STR_SRCFILE_ADD_INVALID_TYPE					=					"L'extension du fichier audio n'est pas au format MP3.";
		const STR_SRCFILE_ADD_INVALID_NAME					=					"Il y a des caractères spéciaux(` * | \\\ / ? \\\" : < >) qui ne sont pas conformes au nom du fichier.";
		const STR_SRCFILE_ADD_DOUBLE_SPACE_NAME				=					"Le nom de fichier contient des espaces consécutifs.";
		const STR_SRCFILE_ADD_NOT_FOUND_FILE				=					"Le fichier ne peut pas être trouvé.";
		const STR_SRCFILE_ADD_UPLOAD_CONFIRM				=					"Voulez-vous télécharger ce fichier?";
		const STR_SRCFILE_ADD_UPLOAD_APPLY					=					"Télécharger le fichier source.";
		const STR_SRCFILE_ADD_UPLOAD_SUCCESS 				=					"Le téléchargement a réussi.";
		const STR_SRCFILE_ADD_UPLOAD_FAIL					=					"Le téléchargement a échoué.";

		const STR_SRCFILE_ADD_UPLOAD_FAIL_NOTFOUND_FILE		= 					"Aucun fichier";
		const STR_SRCFILE_ADD_UPLOAD_FAIL_OVER_FILECNT		= 					"Dépasser le nombre de fichiers";
		const STR_SRCFILE_ADD_UPLOAD_FAIL_OVER_SAVESIZE		= 					"Stockage dépassé";
		const STR_SRCFILE_ADD_UPLOAD_FAIL_UPLOAD_SIZE		= 					"Capacité de téléchargement";

		const STR_SRCFILE_TABLE								=					"Liste des fichiers sources";
		const STR_SRCFILE_TABLE_COL_INDEX					=					"Nombre";
		const STR_SRCFILE_TABLE_COL_FNAME					=					"Nom de fichier";

		const STR_SRCFILE_DEL								=					"Effacer";
		const STR_SRCFILE_DEL_SELECT						=					"Veuillez sélectionner un fichier à supprimer.";
		const STR_SRCFEIL_DEL_EMPTY							=					"Aucun fichier source à supprimer.";
		const STR_SRCFILE_DEL_SELECT_APPLY					=					"Supprime le fichier source sélectionné.";
		const STR_SRCFILE_DEL_SELECT_ALL_APPLY				=					"Supprimer le fichier source entier.";
		const STR_SRCFILE_DEL_SELECT_APPLY_AFTER_STOP		=					"Après avoir arrêté le son en cours de lecture, supprimez le fichier.";

		const STR_SRCFILE_PLAY		 		  			    =					"Lecture";
		const STR_SRCFILE_PLAY_SETUP 		  			    =					"Parametres de lecture";
		const STR_SRCFILE_PLAY_ALL	 		  			    =					"Lire tout";
		const STR_SRCFILE_PLAY_REPEAT					    =					"Nombre de répétitions";
		const STR_SRCFILE_PLAY_REPEAT_OVERRANGE   			=					"Il ne rentre pas dans la plage de comptage d'itération.";
		const STR_SRCFILE_PLAY_STOP 		  			    =					"Pause";
		const STR_SRCFILE_PLAY_STOP_APPLY		  			=					"Pause de la lecture audio.";
		const STR_SRCFEIL_PLAY_EMPTY						=					"Aucun fichier source à lire.";
		const STR_SRCFILE_PLAY_SELECT	 	  			    =					"Veuillez sélectionner un fichier à lire.";
		const STR_SRCFILE_PLAY_SELECT_APPLY				    =					"Lire le fichier source sélectionné.";
		const STR_SRCFILE_PLAY_SELECT_ALL_APPLY				=					"Lire l'intégralité du fichier source.";

		const STR_SRCFILE_SRC_PLAY							=					"Lecture du fichier source";
		
		const STR_OPER_INFO									=					"Informations sur le fonctionnement";
		const STR_INFO_LEVEL_METER							=					"Indicateur de niveau";
		const STR_INFO_VOLUME								=					"Le volume";
		const STR_COMMON_APPLY								=					"Appliquer";
		
		const STR_OTHER										=					"compter";
		const STR_AND										=					"sauf";
		const STR_JS_WRONG_VOLUME							=					"Valeur de volume non valable.(0~100)";
	}
?>