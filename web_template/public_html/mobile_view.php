<?php
	header("Pragma: no-cache");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Content-Type: text/html; charset=UTF-8");

 	include_once "common/common_session.php";
	include_once "common/common_define.php";
	include_once "common/common_script.php";

	// Template Class 선언 (common_script.php)
	$commonFunc = new Common\Func\CommonFunc();
	// Post 처리
	if( isset($_POST['language']) ) 		{	$commonFunc->setEnvInfoLanguage($_POST['language']);					return ;	}
	if( isset($_POST['main_contents']) )	{	$commonFunc->setMainContents($_POST['main_contents']);	echo "mobile"; 	return ;	}
?>

<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="content-type"  content="text/html; charset=UTF-8" />
		<meta http-equiv="cache-control" content="no-cache" />
		<meta http-equiv="expires" content="0" />
		<meta http-equiv="pragma"  content="no-cache" />
		<meta name="viewport" content="width=divice-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">

	   	<link rel="stylesheet"    href="<?=Common\Def\PATH_WEB_CSS_STYLE_MOBILE ?>" type="text/css" />
	   	<link rel="stylesheet"    href="<?=Common\Def\PATH_WEB_CSS_JQUERY_UI ?>" type="text/css" />
		<link rel="shortcut icon" href="<?=Common\Def\PATH_WEB_IMG_FAVICON ?>" />

		<script type="text/javascript" src="<?=Common\Def\PATH_WEB_JS_JQUERY ?>"   ></script>
		<script type="text/javascript" src="<?=Common\Def\PATH_WEB_JS_JQUERY_UI ?>"></script>
		<script type="text/javascript" src="<?=Common\Def\PATH_WEB_JS_AJAX ?>"     ></script>

	   	<meta name="author"     content="<?=$commonFunc->getEnvCompanyName() ?>" />
	   	<meta name="keywords"   content="<?=$commonFunc->getEnvDeviceName() ?>"  />

		<title>[<?=$commonFunc->getEnvDeviceName() ?>] <?=Common\Lang\STR_TITLE_SETUP_PAGE ?></title>
	</head>

	<body>
		<!-- Header -->
		<div id="div_header">
			<div id="div_header_top">
				<img id="img_header_ci" src="<?=$commonFunc->getEnvCompanyLogoMobile() ?>" />
				<div id="div_header_name">
					<span id="span_header_name" name="main_contents.php"><?=$commonFunc->getEnvDeviceName() ?></span>
				</div>
				<img class="img_icon_banner" id="img_banner_logout" src="<?=Common\Def\PATH_IMG_ICON_LOGOUT ?>" />
			</div>
		</div>

		<!-- Banner -->
		<div id="div_banner">
			<div id="div_banner_contents">
				<span id="span_banner_user">[ <?=$_SESSION['username'] ?> ]</span>
		<!---->	<span id="span_banner_hostname">&nbsp;| <?=$commonFunc->getHeaderInfoHostName() ?></span>
		<!---->	<span id="span_banner_location">&nbsp;| <?=$commonFunc->getHeaderInfoLocation() ?> </span>
		<!---->	<span id="span_banner_ipAddr">&nbsp;| <?=$_SERVER['SERVER_NAME']?>  </span>
		<!---->	<span id="span_banner_version">&nbsp;| <?=Common\Lang\STR_FIRMWARE_VERSION ?> :&nbsp;<?=$commonFunc->getEnvDeviceVersion() ?> </span>
		<!---->	<span id="right_arrow">&nbsp;&#187;</span>
			</div>
		</div>

		<!-- Main -->
		<div id="div_main">
			<div id="div_main_contents">
				<?php include $commonFunc->getMainContents(); ?>
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
				<div id="div_footer_pc_view">[<?=Common\Lang\STR_LOGIN_VIEW_PC ?>]</div>
				Copyright (c) <?=$commonFunc->getEnvInfoMadeYear() ?> <a class="copyright_link" href=<?=$commonFunc->getEnvInfoHomepageURL() ?> target="_new"> <?=$commonFunc->getEnvCompanyName()  ?> </a> All rights reserved.
			</div>
		</div>
	</body>
</html>

<?php include "common/common_js.php"; ?>
