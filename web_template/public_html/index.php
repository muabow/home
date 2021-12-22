<?php
	header("Pragma: no-cache");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Content-Type: text/html; charset=UTF-8");

	include_once "common/common_session.php";

	$mobileDisplayStat = 'style="display: none;"';
	if( preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $_SERVER['HTTP_USER_AGENT'])
		 || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($_SERVER['HTTP_USER_AGENT'], 0, 4)) ) {
		if( !isset($_SESSION["pc_view"]) ) {
			include_once "index_m.php";
			return ;
		 }
		$mobileDisplayStat = "";
	}

	include_once "common/common_define.php";
	include_once "common/common_script.php";

	// Common script 상속/재처리를 위한 extend 처리
	include_once "common/common_script_ext.php";

	// Post 처리
	if( isset($_POST['language']) ) 			{	$commonFunc->setEnvInfoLanguage($_POST['language']);				return ;	}
	if( isset($_POST['main_contents']) )		{	$commonFunc->setMainContents($_POST['main_contents']);	echo "pc"; 	return ;	}
	if( isset($_POST['noti_system_check']) )	{	unlink("/opt/interm/system_check_web"); 							return ;	}

	$str_main_contents = $commonFunc->getMainContents();

	$set_menu_focus = "";
	$set_span_focus = "";
	if( $commonFunc->is_set_main_contents() ) {
		$set_menu_focus = "div_main_menu_focus";
		$set_span_focus = "span_main_menu_focus";
	}
?>

<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="content-type"  content="text/html; charset=UTF-8" />
		<meta http-equiv="cache-control" content="no-cache" />
		<meta http-equiv="expires" content="0" />
		<meta http-equiv="pragma"  content="no-cache" />

	   	<link rel="stylesheet"    href="<?=Common\Def\PATH_WEB_CSS_STYLE     ?>" type="text/css" />
	   	<link rel="stylesheet"    href="<?=Common\Def\PATH_WEB_CSS_JQUERY_UI ?>" type="text/css" />
		<link rel="shortcut icon" href="<?=Common\Def\PATH_WEB_IMG_FAVICON ?>" />

		<script type="text/javascript" src="<?=Common\Def\PATH_WEB_JS_JQUERY ?>"   ></script>
		<script type="text/javascript" src="<?=Common\Def\PATH_WEB_JS_JQUERY_UI ?>"></script>
		<script type="text/javascript" src="<?=Common\Def\PATH_WEB_JS_AJAX ?>"     ></script>

	   	<meta name="author"     content="<?=$commonFunc->getEnvCompanyName() ?>" />
	   	<meta name="keywords"   content="<?=$commonFunc->getEnvDeviceName() ?>"  />

		<title>[<?=$commonFunc->getEnvDeviceName() ?>] <?=Common\Lang\STR_TITLE_SETUP_PAGE ?></title>
	</head>

	<body onselectstart="return false" ondragstart="return false">
		<div id="viewLoading"> <img src="/img/loader.gif"></img> </div>
		<div id="viewBackground"> </div>
		<div id="percentView"> 100% </div>

		<!-- Header -->
		<div id="div_header">
			<div id="div_header_top">
				<img id="img_header_ci" src="<?=$commonFunc->getEnvCompanyLogo() ?>" />
				<div id="div_header_name">
					<span id="span_header_name" name="main_contents.php"><?=$commonFunc->getEnvDeviceName() ?></span>
				</div>
				<div id="div_header_user">
					<span class="span_header_user">[&nbsp;</span>
					<span id="span_header_user"><?=$_SESSION['username'] ?></span>
					<span class="span_header_user">&nbsp;]&nbsp;</span>
					<img class="img_icon_banner" id="img_banner_logout" src="<?=Common\Def\PATH_IMG_ICON_LOGOUT ?>" />
				</div>
			</div>
		</div>

		<!-- Banner -->
		<div id="div_banner">
			<div id="div_banner_contents">
				<span id="span_banner_hostname"> <?=$commonFunc->getHeaderInfoHostName() ?> </span>&nbsp;|
				<span id="span_banner_location"> <?=$commonFunc->getHeaderInfoLocation() ?> </span>&nbsp;|
				<span id="span_banner_ipAddr"> <?=$_SERVER['SERVER_NAME'] ?>  </span>&nbsp;|
				<?=Common\Lang\STR_FIRMWARE_VERSION ?>:&nbsp;<span="span_banner_version"> <?=$commonFunc->getEnvDeviceVersion() ?> </span>
			</div>
		</div>

		<!-- Main -->
		<div id="div_main">
			<div id="div_main_menu">
				<div class="div_main_menu_category">
					<span class="span_main_menu_category"> <?=Common\Lang\STR_MENU_COMMON ?> </span>
				</div>
				<div class="div_main_menu_sub <?=$set_menu_focus ?>" id="div_main_menu_id_main" name="main_contents.php">
					<span class="span_main_menu_sub <?=$set_span_focus ?>"> <?=Common\Lang\STR_MENU_COMMON_MAIN ?> </span>
				</div>

				<?=$commonFunc->getMainMenu() ?>
			</div>

			<div id="div_main_contents">
				<?php include $str_main_contents; ?>
			</div>
		</div>

		<!-- Footer -->
		<div id="div_footer">
			<div id="div_footer_shell">
				<span id="span_footer_language">
					<img class="img_icon_banner" src="<?=Common\Def\PATH_IMG_ICON_LANGUAGE ?>" />
					<?=$commonFunc->getEnvInfoLanguageName() ?>
					<br />
				</span>
				<div id="div_footer_language_list">
					<?=$commonFunc->getEnvInfoLanguageList() ?>
				</div>
				<div id="div_footer_mobile_view" <?=$mobileDisplayStat ?>>[<?=Common\Lang\STR_LOGIN_VIEW_MOBILE ?>]</div>
				Copyright (c) <?=$commonFunc->getEnvInfoMadeYear() ?> <a class="copyright_link" href=<?=$commonFunc->getEnvInfoHomepageURL() ?> target="_new"> <?=$commonFunc->getEnvCompanyName()  ?> </a> All rights reserved.
			</div>
		</div>

		<!-- system check alert -->
		<div id="div_blur" class="div_blur"></div>
		<div id="div_system_check_alert" class="div_system_check_alert">
			<div><?=Common\Lang\STR_SYSTEM_CHECK_MSG_1 ?> <br /> <?=Common\Lang\STR_SYSTEM_CHECK_MSG_2 ?></div>
		</div>

		<?php
			if( file_exists("/opt/interm/system_check_web") ) {
				echo '<div id="div_system_check_end_blur" class="div_blur" style="display: block;"></div>';
				echo '<div id="div_system_check_end_alert" class="div_system_check_alert" style="display: flex;">';
				echo '<div> ' . Common\Lang\STR_SYSTEM_CHECK_END_MSG_1 . ' <br /> ' . Common\Lang\STR_SYSTEM_CHECK_END_MSG_2 . ' </div>';
				echo '</div>';
			}
		?>
	</body>
</html>

<?php include "common/common_js.php"; ?>
