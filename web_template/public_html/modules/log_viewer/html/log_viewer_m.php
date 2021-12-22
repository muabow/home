<?php
	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";

	$logFunc = new Log_viewer\Func\LogViewerFunc();

	$logFunc->setLogStatData();
?>

<link rel="stylesheet"	href="<?=Log_viewer\Def\PATH_WEB_CSS_STYLE_MOBILE ?>" type="text/css" />

<div id="div_contents_log_viewer">
	<div id="div_log_title"> <?=Log_viewer\Lang\STR_MENU_NAME ?> </div>

	<hr />

	<div id="div_log_banner">
		<select id="select_log_displayType">
			<?php echo $logFunc->makeLogFileList(); ?>
		</select>

		<select id="select_log_displayLine">
			<?php echo $logFunc->makeLogDisplayLine(); ?>
		</select>

		<select id="select_log_updateMode">
			<?php echo $logFunc->makeLogUpdateMode(); ?>
		</select>

		<select id="select_log_updateTime" <?=$logFunc->getLogUpdateStat() ?> >
			<?php echo $logFunc->makeLogUpdateTime(); ?>
		</select>

		<select id="select_log_scrollMode">
			<?php echo $logFunc->makeLogScrollMode(); ?>
		</select>

		<div id="div_log_download" class="div_log_button"> <?=Log_viewer\Lang\STR_LOG_DOWNLOAD ?> </div>
	</div>

	<div id="div_log_form_m" class="ui-widget-content">
		<div id="div_log_prev" class="div_log_more">
			<?=Log_viewer\Lang\STR_LOG_DISPLAY_TOP ?>
		</div>

		<div id="div_log_frame">
		</div>

		<div id="div_log_more" class="div_log_more">
			<?=Log_viewer\Lang\STR_LOG_DISPLAY_BOTTOM ?>
		</div>
	</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>