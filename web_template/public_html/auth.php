<?php
	header("Pragma: no-cache");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Content-Type: text/html; charset=UTF-8");

	$envData = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/env.json"));
	if( is_null($envData->info->auth_key) || file_exists($envData->info->auth_key) ) {
		include_once "common/common_session.php";
	}

	include_once "common/common_define.php";
	include_once "common/common_script.php";

	// Template Class 선언 (common_script.php)
	$commonFunc = new Common\Func\CommonFunc();

	if( isset($_POST['language']) ) 		{	$commonFunc->setEnvInfoLanguage($_POST['language']);	return ;	}
	if( isset($_POST['main_contents']) )	{	$commonFunc->setMainContents($_POST['main_contents']);	return ;	}
?>

<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="content-type"  content="text/html; charset=UTF-8" />
		<meta http-equiv="cache-control" content="no-cache" />
		<meta http-equiv="expires" content="0" />
		<meta http-equiv="pragma"  content="no-cache" />

		<link rel="stylesheet"    href="<?=Common\Def\PATH_WEB_CSS_STYLE ?>" type="text/css" />
		<link rel="shortcut icon" href="<?=Common\Def\PATH_WEB_IMG_FAVICON ?>" />

		<script type="text/javascript" src="<?=Common\Def\PATH_WEB_JS_JQUERY    ?>"></script>
		<script type="text/javascript" src="<?=Common\Def\PATH_WEB_JS_JQUERY_UI ?>"></script>
		<script type="text/javascript" src="<?=Common\Def\PATH_WEB_JS_AJAX      ?>"></script>

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
			</div>
		</div>

		<!-- Banner -->
		<div id="div_banner">
			<div id="div_banner_contents">
				<span id="span_banner_hostname"> <?=$commonFunc->getHeaderInfoHostName() ?> </span>&nbsp;|
				<span id="span_banner_location"> <?=$commonFunc->getHeaderInfoLocation() ?> </span>&nbsp;|
				<span id="span_banner_ipAddr"> <?=$_SERVER['SERVER_NAME']?>  </span>&nbsp;|
				<?=Common\Lang\STR_FIRMWARE_VERSION ?>:&nbsp;<span="span_banner_version"> <?=$commonFunc->getEnvDeviceVersion() ?> </span>
			</div>
		</div>

		<!-- Main -->
		<div id="div_main">
			<div id="div_main_login">
				<div id="div_main_login_form">
					<br />
					<span id="span_login_form_title"><?=Common\Lang\STR_AUTH_TITLE ?></span>
					<br />

					<div id="div_login_alert_form">
					</div>

					<div class="div_contents_cell_contents">
						<div class="filebox">
							<input class="upload-name" id="label_uploadFile" value="<?=Common\Lang\STR_AUTH_FILE_FIND ?>" disabled="disabled">
							<br />
							<label for="file_uploadFile"><?=Common\Lang\STR_AUTH_UPLOAD ?></label>
							<input type="file" id="file_uploadFile" class="upload-hidden" />
						</div>
					</div>

					<div id="div_login_form_submit">
						<?=Common\Lang\STR_AUTH_BUTTON_SET ?>
					</div>
				</div>
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

				Copyright (c) <?=$commonFunc->getEnvInfoMadeYear() ?> <a class="copyright_link" href=<?=$commonFunc->getEnvInfoHomepageURL() ?> target="_new"> <?=$commonFunc->getEnvCompanyName()  ?> </a> All rights reserved.
			</div>
		</div>
	</body>
</html>

<?php include "common/common_js.php"; ?>
<?php include "common/common_auth.php"; ?>
