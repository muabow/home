<?php
	if( $commonFunc->procModuleStatus(basename(__FILE__)) ) return ;

	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";

	$monitorFunc = new Monitor\Func\MonitorFunc();
?>

<link rel="stylesheet"	href="<?=Monitor\Def\PATH_WEB_CSS_STYLE ?>" type="text/css" />

<div id="div_contents_monitor">
	<div id="div_monitor_title"> <?=Monitor\Lang\STR_MENU_NAME ?> </div>

	<hr />

	<div id="div_monitor_form">
		<div id="div_monitor_add_fold" class="div_monitor_banner_title">
			<?=Monitor\Lang\STR_BODY_ADD_DEVICE ?>
			<div id="div_monitor_arrow" class="div_arrow_up"></div>
		</div>

		<div id="div_monitor_add">
			<div class="divTable">
				<div class="divTableBody">
					<div class="divTableRow">
						<div class="divTableCell divTableHead">
							<?=Monitor\Lang\STR_BODY_IPADDR ?>
						</div>
						<div class="divTableCell divTableHead">
							<?=Monitor\Lang\STR_BODY_HOSTNAME ?>
						</div>
						<div class="divTableCell divTableHead">
							<?=Monitor\Lang\STR_BODY_LOCATION ?>
						</div>
					</div>
					<div class="divTableRow">
						<div class="divTableCell">
							<input type="text" class="input_monitor_device_add" id="input_monitor_device_ipaddr" />
						</div>
						<div class="divTableCell">
							<input type="text" class="input_monitor_device_add" id="input_monitor_device_name" />
						</div>
						<div class="divTableCell">
							<input type="text" class="input_monitor_device_add" id="input_monitor_device_location"/>
						</div>
					</div>
				</div>
			</div>
			<div id="div_monitor_device_clear" class="div_monitor_button div_monitor_button_clear"> <?=Monitor\Lang\STR_BODY_RESET ?> </div>
			<div id="div_monitor_device_add"   class="div_monitor_button div_monitor_button_add"  > <?=Monitor\Lang\STR_BODY_SUBMIT ?> </div>
		</div>

		<div id="div_monitor_list_title" class="div_monitor_banner_title">
			<?=Monitor\Lang\STR_BODY_DEVICE_LIST ?>
		</div>

		<div id="div_monitor_list_resize">
			<div id="div_monitor_list_device">

				<div class="divTable">
					<div class="divTableBody">
						<div class="divTableRow">
							<div class="divTableCell divTableHead divTableCell_checkbox">
								#
							</div>
							<div class="divTableCell divTableHead divTableCell_number">
								No.
							</div>
							<div class="divTableCell divTableHead divTableCell_ipaddr">
								<?=Monitor\Lang\STR_BODY_IPADDR ?>
							</div>
							<div class="divTableCell divTableHead divTableCell_hostname">
								<?=Monitor\Lang\STR_BODY_HOSTNAME ?>
							</div>
							<div class="divTableCell divTableHead divTableCell_location">
								<?=Monitor\Lang\STR_BODY_LOCATION ?>
							</div>
							<div class="divTableCell divTableHead divTableCell_device">
								<?=Monitor\Lang\STR_BODY_DEVICE ?>
							</div>
							<div class="divTableCell divTableHead divTableCell_audio">
								<?=Monitor\Lang\STR_BODY_AUDIO ?>
							</div>
							<div class="divTableCell divTableHead divTableCell_levelmeter">
								<?=Monitor\Lang\STR_BODY_LEVEL ?>
							</div>
						</div>
					</div>
					<div class="divTableBody" id="div_monitor_table_body">

						<?php echo $monitorFunc->makeDeviceList(); ?>

					</div> <!-- end of tableBody -->
				</div> <!-- end of table -->

				<div id="div_monitor_device_remove" class="div_monitor_button div_monitor_button_remove"> <?=Monitor\Lang\STR_BODY_REMOVE ?> </div>
			</div> <!-- end of list_device -->

			<div class="div_monitor_banner_bottom">
			</div>
		</div>
	</div>

</div>

<?php include $env_pathModule . "common/common_js.php"; ?>
<?php include $env_pathModule . "common/monitor_equlizer.php"; ?>