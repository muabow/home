<?php
	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";

	$modeFunc = new Mode_management\Func\ModeFunc();
?>

<link rel="stylesheet" href="<?=Mode_management\Def\PATH_WEB_CSS_STYLE_MOBILE ?>" type="text/css">

<div id="div_contents">
	<div id="div_log_title"> <?=Mode_management\Lang\STR_MENU_NAME ?> </div>
	<hr>

	<div id="div_contents_table">
<?php
	if( $modeFunc->getSupportMode() ) {
		$currentMode = "[ " . $modeFunc->getCurrentMode() ." ]";

	} else {
		$currentMode = 	Mode_management\Lang\STR_MODE_NOTI_NOT_SUPPORT;
	}
?>
		<div class="div_contents_cell">
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_category">
					<?=Mode_management\Lang\STR_MODE_HEAD_MODE ?>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Mode_management\Lang\STR_MODE_HEAD_SET_MODE ?>
				</div>
				<div class="div_contents_cell_contents">
					<?php echo $currentMode; ?>
				</div>
			</div>

			<div class="div_contents_cell_line"></div>

		</div>
	</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>