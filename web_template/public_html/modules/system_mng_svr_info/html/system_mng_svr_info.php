<?php
	if( $commonFunc->procModuleStatus(basename(__FILE__)) ) return ;

	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";

	$systemFunc = new System_mng_svr_info\Func\SystemFunc();
?>

<link rel="stylesheet" href="<?=System_mng_svr_info\Def\PATH_WEB_CSS_STYLE ?>" type="text/css">

<div id="div_contents">
	<div id="div_log_title"> <?=System_mng_svr_info\Lang\STR_MENU_NAME ?> </div>
	<hr>

	<div id="div_contents_table">
		<div class="div_contents_title">
		<?=System_mng_svr_info\Lang\STR_SYSTEM_TITLE_LIST ?>
		</div>
		<div class="div_contents_cell">
			<div class="div_contents_cell_line" style="padding-top: 15px;">
				<div class="divTable">
					<div class="divTableBody">
						<div class="divTableRow">
							<div class="divTableHead_left" style="width: 30px;">
								<?=System_mng_svr_info\Lang\STR_SYSTEM_HEAD_SELECT ?>
							</div>
							<div class="divTableHead"      style="width: 220px;">
								<?=System_mng_svr_info\Lang\STR_SYSTEM_HEAD_ID ?>
							</div>
							<div class="divTableHead"      style="width: 100px;">
								<?=System_mng_svr_info\Lang\STR_SYSTEM_HEAD_IP ?>
							</div>
							<div class="divTableHead"      style="width: 40px;">
								<?=System_mng_svr_info\Lang\STR_SYSTEM_HEAD_PORT ?>
							</div>
							<div class="divTableHead"      style="width: 110px;">
								<?=System_mng_svr_info\Lang\STR_SYSTEM_HEAD_DESC ?>
							</div>
							<div class="divTableHead"      style="width: 110px;">
								<?=System_mng_svr_info\Lang\STR_FIRMWARE_VERSION ?>
							</div>
							<div class="divTableHead"      style="width: 140px;">
								<?=System_mng_svr_info\Lang\STR_SYSTEM_HEAD_DATE ?>
							</div>
							<div class="divTableHead">
								<input type="checkbox" id="checkbox_select_all" />
							</div>
						</div>
					</div>
					<div class="divTableBody"  id="divSvrList">
						<?php echo $systemFunc->getSvrList(); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line"></div>

		<div class="div_button_wrap">
			<div  class="div_button" id="div_buttonApply">
				<?=System_mng_svr_info\Lang\STR_SYSTEM_BUTTON_REMOVE ?>
			</div>
		</div>
	</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>