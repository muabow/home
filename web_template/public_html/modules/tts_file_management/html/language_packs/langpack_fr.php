<?php // fr, language_packs 위에 존재하는 모든 언어팩들의 내용은 동일해야 함
	namespace TTS_file_management\Lang {
		const STR_MENU_NAME									=	"Gestion des fichiers TTS";

		const STR_TTS_BUTTON_CREATE							=	"Créer";
		const STR_TTS_BUTTON_PREVIEW						=	"Aperçu";
		const STR_TTS_BUTTON_SAVE							=	"Aperçu";
		const STR_TTS_BUTTON_RESET							=	"Réinitialiser";
		
		const STR_TTS_TITLE_FORM							=	"Créer un fichier TTS";
		const STR_TTS_TITLE_INPUT							=	"Entrez le contenu TTS";
		const STR_TTS_TITLE_INPUT_NAME						=	"Veuillez saisir le titre.";
		const STR_TTS_TITLE_INPUT_TEXT						=	"Veuillez saisir le contenu TTS.";
		const STR_TTS_TITLE_INPUT_LANGUAGE					=	"Choisir la langue";
		const STR_TTS_TITLE_INPUT_GENDER					=	"Sélectionnez le sexe";
		const STR_TTS_TITLE_CHIME_SETUP						=	"Configuration du fonctionnement du CHIME";
		const STR_TTS_TITLE_CHIME_BEGIN						=	"Sélectionnez Démarrer le carillon";
		const STR_TTS_TITLE_CHIME_END						=	"Sélectionnez Terminer le carillon";
		const STR_TTS_TITLE_TTS_OPTION						=	"Configuration des options vocales TTS";

		const STR_TTS_TITLE_OPT_GENDER_MALE					=	"Mâle";
		const STR_TTS_TITLE_OPT_GENDER_FEMALE				=	"Femme";
		const STR_TTS_TITLE_OPT_CHIME_NONE					=	"Non séléctionné";
		const STR_TTS_TITLE_OPT_PITCH						=	"Pas";
		const STR_TTS_TITLE_OPT_SPEED						=	"La vitesse";
		const STR_TTS_TITLE_OPT_VOLUME						=	"Le volume";
		const STR_TTS_TITLE_OPT_SENTENCE_PAUSE				=	"Délai entre les phrases";
		const STR_TTS_TITLE_OPT_COMMA_PAUSE					=	"Délai entre virgule";

		const STR_TTS_TABLE									=	"Liste des fichiers TTS";
		const STR_TTS_TABLE_NUMBER							=	"Nombre";
		const STR_TTS_TABLE_NAME							=	"Titre";
		const STR_TTS_TABLE_CONTENT							=	"Contenu";
		const STR_TTS_TABLE_PLAY_TIME						=	"Récréation";

		const STR_TTS_ACT_DEL								=	"Supprimer";
		const STR_TTS_ACT_DEL_APPLY							=	"Supprimer le fichier sélectionné.";
		const STR_TTS_ACT_COPY_APPLY						=	"Copiez le TTS sélectionné dans BGM.";
		const STR_TTS_ACT_COPY_COMPLETE						=	"La copie est terminée.";
		const STR_TTS_ACT_INPUT_TITLE						=	"Veuillez saisir le titre TTS.";
		const STR_TTS_ACT_INPUT_DUP_TITLE					=	"Il existe des titres TTS en double. Veuillez changer le titre.";
		const STR_TTS_ACT_INPUT_TEXT						=	"Veuillez saisir le contenu TTS.";
		const STR_TTS_ACT_INPUT_LANGUAGE					=	"Veuillez sélectionner une langue.";
		const STR_TTS_ACT_INPUT_GENDER						=	"Veuillez sélectionner un sexe.";
		const STR_TTS_ACT_INPUT_PREVIEW						=	"Veuillez créer un fichier TTS.";
		const STR_TTS_ACT_INPUT_CONFIRM_SAVE				=	"Souhaitez-vous économiser?";
		const STR_TTS_ACT_LIMIT_BYTES_OVER					=	"Limite d'entrée de contenu dépassée.";
		const STR_TTS_ACT_EXCEED_CAPACITY					=	"Le TTS ne peut pas être généré en raison d'une capacité d'utilisation dépassée.";

		const STR_TTS_INFO_LIMIT_BYTES 						=	"Restrictions d'entré";
		const STR_TTS_INFO_AVAIL_SIZE 						=	"Capacité utilisable";

		const STR_EXT_SELECT_UPLOAD_STORAGE					=	"Sélectionnez le stockage de téléchargement";
		const STR_EXT_SELECT_STORAGE_INTERNAL				=	"Stockage interne";
		const STR_EXT_SELECT_STORAGE_EXTERNAL				=	"Stockage externe (SD)";
		const STR_EXT_SRCFILE_ADD_AVAILABLE_MEM				=	"Capacité disponible externe";

		const STR_TTS_ERROR_ALERT							=	"Une erreur de conversion TTS est produite. Veuillez vérifier le contenu.";
		const STR_TTS_INVALID_WORD							=	"Les caractères spéciaux ('&) ne peuvent pas être utilisés dans le titre ou le contenu.";
	}
?>