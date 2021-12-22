<?php
	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";

	$timeFunc = new Time_setup\Func\TimeSetupFunc();
?>

<link rel="stylesheet"    href="<?=Time_setup\Def\PATH_WEB_CSS_STYLE_MOBILE ?>" type="text/css">

<div id="div_contents">

	<div id="div_log_title"> <?=Time_setup\Lang\STR_MENU_NAME ?> </div>
	<hr>

	<!-- hidden parameter -->
	<select class="select_systemCheck" id="select_systemCheckDay" style="display: none;">
		<?=$timeFunc->printDay() ?>
	</select>
	<select class="select_systemCheck" id="select_systemCheckYear" style="display: none;">
		<?=$timeFunc->printYear() ?>
	</select>
	<select id="select_timezoneList" style="display: none;">
		<?=$timeFunc->printZoneInfo() ?>
	</select>

	<!-- end -->

	<div id="div_contents_table">
		<div class="div_contents_cell">
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_category">
					<?=Time_setup\Lang\STR_DATE_INFO ?>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Time_setup\Lang\STR_DATE_SETUP_STANDARD ?>
				</div>
				<div class="div_contents_cell_contents">
					<?=$timeFunc->printCurrentZoneInfo(); ?>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Time_setup\Lang\STR_DATE_SETUP_NOW ?>
				</div>
				<div class="div_contents_cell_contents">
					<div id="present_time" style="height: 20px;"></div>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Time_setup\Lang\STR_DATE_SETUP_AUTO ?>
				</div>
				<div class="div_contents_cell_contents">
					<span style="color: <?=$timeFunc->getTimeStatColor() ?>;"><?php echo strtoupper($timeFunc->getTimeServer()); ?></span>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Time_setup\Lang\STR_DATE_SETUP_SERVER ?>
				</div>
				<div class="div_contents_cell_contents">
					<?=$timeFunc->getTimeServerUrl() ?>
				</div>
			</div>

			<div class="div_contents_cell_line"></div>
		</div>
	</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>