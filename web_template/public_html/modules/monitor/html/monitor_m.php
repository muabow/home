<?php
	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";

	$monitorFunc = new Monitor\Func\MonitorFunc();
?>

<link rel="stylesheet"	href="<?=Monitor\Def\PATH_WEB_CSS_STYLE_MOBILE ?>" type="text/css" />

<div id="div_contents_monitor">
	<div id="div_monitor_title"> <?=Monitor\Lang\STR_MENU_NAME ?> </div>

	<hr />
	<div id="div_contents_table">
		<div class="div_contents_cell">
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Monitor\Lang\STR_BODY_DEVICE_LIST ?>
				</div>
				<div class="div_contents_cell_contents">
					<div id="div_monitor_list_device">
						<div class="divTable">
							<div class="divTableBody">
								<div class="divTableRow">
									<div class="divTableCell divTableHead divTableCell_number">
										No.
									</div>
									<div class="divTableCell divTableHead divTableCell_ipaddr">
										<?=Monitor\Lang\STR_BODY_IPADDR ?>
									</div>
									<div class="divTableCell divTableHead divTableCell_hostname">
										<?=Monitor\Lang\STR_BODY_HOSTNAME ?>
									</div>
									<div class="divTableCell divTableHead divTableCell_device">
										<?=Monitor\Lang\STR_BODY_DEVICE ?>
									</div>
									<div class="divTableCell divTableHead divTableCell_audio">
										<?=Monitor\Lang\STR_BODY_AUDIO ?>
									</div>
								</div>
							</div>
							<div class="divTableBody" id="div_monitor_table_body">
								<?php echo $monitorFunc->makeDeviceList(); ?>
							</div> <!-- end of tableBody -->
						</div> <!-- end of table -->
					</div> <!-- end of list_device -->
				</div>
			</div>
			<div class="div_contents_cell_line">
			</div>
		</div>
	</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>
<?php include $env_pathModule . "common/monitor_equlizer.php"; ?>