<?php
if( $commonFunc->procModuleStatus(basename(__FILE__)) ) {
	echo "module error!";
	return ;
}

    $env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

    include_once $env_pathModule . "common/common_define.php";
    include_once $env_pathModule . "common/common_script.php";

?>
<link rel="stylesheet" href="<?=Annual_schedule\Def\PATH_WEB_CSS_STYLE ?>" type="text/css">
<link rel="stylesheet" href="modules/annual_schedule/html/params/newSchedule/style.css" type="text/css">
<?php include "/opt/interm/public_html/modules/annual_schedule/html/params/header_newSchedule.php" ?>
<div id="div_contents_annual_schedule">

	<div id="div_title" style="margin-left:20px;font-size:20px;"> <b><?=Annual_schedule\Lang\STR_MENU_NAME ?>
	</div>
	<hr>
	<div style="margin-left:20px;margin-top:20px;height:850px;width:920px;">
		<form name="mainForm">
			<?php include "/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/index.php" ?>
		</form>
	</div>

</div>

<?php include $env_pathModule . "common/common_js.php"; ?>
