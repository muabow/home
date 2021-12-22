<?php
	if( $commonFunc->procModuleStatus(basename(__FILE__)) ) return ;

	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";

	$registerFunc = new Api_register\Func\ApiRegisterFunc();
?>

<link rel="stylesheet"	href="<?=Api_register\Def\PATH_WEB_CSS_STYLE ?>" type="text/css" />

<div id="div_contents_open_api">
	<div id="div_open_api_title"> <?=Api_register\Lang\STR_MENU_NAME ?> </div>

	<hr />

	<div id="div_open_api_form">
		<div id="div_open_api_add_fold" class="div_open_api_banner_title">
			API Key <?=Api_register\Lang\STR_MENU_REGISTER ?>
			<div id="div_open_api_arrow" class="div_arrow_up"></div>
		</div>

		<div id="div_open_api_add">
			<div class="divTable">
				<div class="divTableBody">
					<div class="divTableRow">
						<div class="divTableCell divTableHead">
							<?=Api_register\Lang\STR_MENU_ID_KEY ?>
						</div>
						<div class="divTableCell divTableHead">
							<?=Api_register\Lang\STR_MENU_SECRET_KEY ?>
						</div>
						<div class="divTableCell_right divTableHead">
							<?=Api_register\Lang\STR_MENU_SERVER_ADDR ?>
						</div>
					</div>
					<div class="divTableRow">
						<div class="divTableCell">
							<input type="text" class="input_open_api_device_add" id="input_open_api_id" />
						</div>
						<div class="divTableCell">
							<input type="text" class="input_open_api_device_add" id="input_open_api_secret" />
						</div>
						<div class="divTableCell_right">
							<input type="text" class="input_open_api_device_add" id="input_open_api_server_addr" />
						</div>
					</div>
				</div>
			</div>
			<div id="div_open_api_user_add"   class="div_open_api_button div_open_api_button_add"  > <?=Api_register\Lang\STR_BODY_SUBMIT ?> </div>
		</div>

		<div id="div_open_api_list_title" class="div_open_api_banner_title">
			API Key <?=Api_register\Lang\STR_MENU_LIST ?>  (<?php echo $registerFunc->getStdDate(); ?>)
		</div>

		<div id="div_open_api_list_resize">
			<div id="div_open_api_list_user">

				<div id="div_open_api_device_remove" class="div_open_api_button div_open_api_button_remove"> <?=Api_register\Lang\STR_BODY_REMOVE ?> </div>
				<div class="divTable">
					<div class="divTableBody">
						<div class="divTableRow">
							<div class="divTableCell divTableHead divTableCell_checkbox">
								#
							</div>
							<div class="divTableCell divTableHead divTableCell_number">
								No.
							</div>
							<div class="divTableCell divTableHead">
								<?=Api_register\Lang\STR_MENU_ID_KEY ?>
							</div>
							<div class="divTableCell divTableHead">
								<?=Api_register\Lang\STR_MENU_SECRET_KEY ?>
							</div>
							<div class="divTableCell divTableHead">
								<?=Api_register\Lang\STR_MENU_SERVER_ADDR ?>
							</div>
							<div class="divTableCell divTableHead">
								<?=Api_register\Lang\STR_MENU_DAY_USAGE ?>
							</div>
							<div class="divTableCell_right divTableHead">
								<?=Api_register\Lang\STR_MENU_CUM_USAGE ?>
							</div>
						</div>
					</div>
					<div class="divTableBody" id="div_open_api_table_body">

						<?php echo $registerFunc->makeUserList(); ?>

					</div> <!-- end of tableBody -->
				</div> <!-- end of table -->
			</div> <!-- end of list_device -->

			<div class="div_open_api_banner_bottom">
			</div>
		</div>
	</div>

</div>

<?php include $env_pathModule . "common/common_js.php"; ?>
