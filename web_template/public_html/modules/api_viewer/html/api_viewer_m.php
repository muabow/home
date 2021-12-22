<?php
	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";

	use Common;

	$viewerFunc = new Api_viewer\Func\ApiViewerFunc();
?>

<link rel="stylesheet"	href="<?=Api_viewer\Def\PATH_WEB_CSS_STYLE_MOBILE ?>" type="text/css" />

<div id="div_contents_open_api">
	<div id="div_open_api_title"> <?=Api_viewer\Lang\STR_MENU_NAME ?> </div>

	<hr />

	<div id="div_open_api_form">
		<div id="div_open_api_list_title" class="div_open_api_banner_title">
			<?=Api_viewer\Lang\STR_MENU_LIST ?> (<?php echo $viewerFunc->getStdDate(); ?>)
		</div>

		<div id="div_open_api_list_user">

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
							<?=Api_viewer\Lang\STR_MENU_ID_KEY ?>
						</div>
						<div class="divTableCell divTableHead">
							<?=Api_viewer\Lang\STR_MENU_SECRET_KEY ?>
						</div>
						<div class="divTableCell divTableHead">
							<?=Api_viewer\Lang\STR_MENU_DAY_USAGE ?>
						</div>
						<div class="divTableCell divTableHead">
							<?=Api_viewer\Lang\STR_MENU_CUM_USAGE ?>
						</div>
						<div class="divTableCell divTableHead">
							<?=Api_viewer\Lang\STR_MENU_EMAIL ?>
						</div>
						<div class="divTableCell divTableHead">
							<?=Api_viewer\Lang\STR_MENU_CONTACT ?>
						</div>
						<div class="divTableCell divTableHead">
							<?=Api_viewer\Lang\STR_MENU_COMPANY ?>
						</div>
					</div>

				</div>
				<div class="divTableBody" id="div_open_api_table_body">

					<?php echo $viewerFunc->makeUserList(); ?>

				</div> <!-- end of tableBody -->
			</div> <!-- end of table -->
		</div> <!-- end of list_device -->
	</div>

</div>

<?php include $env_pathModule . "common/common_js.php"; ?>
