<?php
	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";

	use Common;

	$systemFunc = new System_mng_svr_info\Func\SystemFunc();
?>

<link rel="stylesheet" href="<?=System_mng_svr_info\Def\PATH_WEB_CSS_STYLE_MOBILE ?>" type="text/css">

<div id="div_contents">
	<div id="div_log_title"> <?=System_mng_svr_info\Lang\STR_MENU_NAME ?> </div>

	<hr />

	<div id="div_contents_table">
		<div class="div_contents_title">
			<?=System_mng_svr_info\Lang\STR_SYSTEM_TITLE_LIST ?>
		</div>

		<div>
			<div id="div_mng_svr_list">
				<div class="divTable">
					<div class="divTableBody">
						<div class="divTableRow">
							<div class="divTableCell divTableHead" 		style="width: 30px;">
								<?=System_mng_svr_info\Lang\STR_SYSTEM_HEAD_SELECT ?>
							</div>
							<div class="divTableCell divTableHead"      style="width: 220px;">
								<?=System_mng_svr_info\Lang\STR_SYSTEM_HEAD_ID ?>
							</div>
							<div class="divTableCell divTableHead"      style="width: 100px;">
								<?=System_mng_svr_info\Lang\STR_SYSTEM_HEAD_IP ?>
							</div>
							<div class="divTableCell divTableHead"      style="width: 40px;">
								<?=System_mng_svr_info\Lang\STR_SYSTEM_HEAD_PORT ?>
							</div>
							<div class="divTableCell divTableHead"      style="width: 110px;">
								<?=System_mng_svr_info\Lang\STR_SYSTEM_HEAD_DESC ?>
							</div>
							<div class="divTableCell divTableHead"      style="width: 110px;">
								<?=System_mng_svr_info\Lang\STR_FIRMWARE_VERSION ?>
							</div>
							<div class="divTableCell divTableHead"      style="width: 140px;">
								<?=System_mng_svr_info\Lang\STR_SYSTEM_HEAD_DATE ?>
							</div>
							<div class="divTableCell divTableHead divTableCell_checkbox">
								<input type="checkbox" id="checkbox_select_all" />
							</div>
						</div>
					</div>
					<div class="divTableBody"  id="divSvrList">
						<?php echo $systemFunc->getSvrList(); ?>
					</div>
				</div>
			</div> <!-- end of list_device -->

			<div class="div_open_api_banner_bottom">
			</div>
		</div>
	</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>

<script type="text/javascript">
	$("[type=radio]").attr("disabled", true);
</script>
