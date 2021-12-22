<?php
	if( $commonFunc->procModuleStatus(basename(__FILE__)) ) return ;

	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";

	$modeFunc = new Mode_management\Func\ModeFunc();
?>

<link rel="stylesheet" href="<?=Mode_management\Def\PATH_WEB_CSS_STYLE ?>" type="text/css">

<div id="div_contents">
	<div id="div_log_title"> <?=Mode_management\Lang\STR_MENU_NAME ?> </div>
	<hr>

	<div id="div_contents_table">
		<div class="div_contents_title">
		<?=Mode_management\Lang\STR_MODE_TITLE_LIST ?>
		</div>

<?php
	if( !$modeFunc->getSupportMode() ) {
		goto NOT_SUPPORT_MODE;
	}
?>
		<div class="div_contents_cell">
			<div class="div_contents_cell_line" style="padding-top: 15px;">
				<div class="divTable">
					<div class="divTableBody">
						<div class="divTableRow">
							<div class="divTableHead_left" style="width: 30px;">
								<?=Mode_management\Lang\STR_MODE_HEAD_SELECT ?>
							</div>
							<div class="divTableHead"      style="width: 120px;">
								<?=Mode_management\Lang\STR_MODE_HEAD_MODE ?>
							</div>
							<div class="divTableHead"      style="width: 600px;">
								<?=Mode_management\Lang\STR_MODE_HEAD_SUPPORT ?>
							</div>
						</div>
					</div>
					<div class="divTableBody"  id="divSvrList">
						<?php echo $modeFunc->getModeList(); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line"></div>

		<div class="div_button_wrap">
			<div  class="div_button" id="div_buttonApply">
				<?=Mode_management\Lang\STR_MODE_APPLY ?>
			</div>
		</div>
	</div>
</div>

<?php
	goto SUPPORT_MODE;
?>

<?php
	NOT_SUPPORT_MODE:
?>
		<div class="div_contents_cell">
			<div class="divTable">
				<div class="divTableBody">
					<div class="divTableRow">
						<div class="divTableCell"
							 style="width: 750px; border: 1px solid #cccccc; font-size: 13px; font-weight: bolder;">
							<?=Mode_management\Lang\STR_MODE_NOTI_NOT_SUPPORT ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
	SUPPORT_MODE:
?>

<?php include $env_pathModule . "common/common_js.php"; ?>