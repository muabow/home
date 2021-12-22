<?php
	if( $commonFunc->procModuleStatus(basename(__FILE__)) ) return ;

	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";

	$timeFunc = new Time_setup\Func\TimeSetupFunc();
?>

<link rel="stylesheet" href="<?=Time_setup\Def\PATH_WEB_CSS_STYLE ?>" type="text/css">

<div id="div_contents">

	<div id="div_time_title"> <?=Time_setup\Lang\STR_MENU_NAME ?> </div>
	<hr>

	<div id="div_contents_table">

		<div class="div_contents_title">
			<?=Time_setup\Lang\STR_DATE_SETUP ?>
		</div>
		<div class="div_contents_cell">

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Time_setup\Lang\STR_DATE_SETUP_STANDARD ?>
				</div>
				<div class="div_contents_cell_contents">
					<select id="select_timezoneList">
						<?=$timeFunc->printZoneInfo() ?>
					</select>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Time_setup\Lang\STR_DATE_SETUP_NOW ?>
				</div>
				<div class="div_contents_cell_contents">
					<div id="present_time"></div>
				</div>
			</div>
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Time_setup\Lang\STR_DATE_SETUP_DATE ?>
				</div>
				<div class="div_contents_cell_contents" style="width:645px;">

					<div class="div_radio_wrap">
						<input type="radio" name="radio" id="radio_timeServerCustom" class="radio"  <?=$timeFunc->getTimeServerStat("off") ?> />
						<label class="label_radio" for="radio_timeServerCustom"><?=Time_setup\Lang\STR_DATE_SETUP_USER ?></label>
					</div>

					<div class="div_radio_wrap">
						<input type="radio" name="radio" id="radio_timeServerAuto" class="radio" <?=$timeFunc->getTimeServerStat("on") ?> />
						<label class="label_radio" for="radio_timeServerAuto"><?=Time_setup\Lang\STR_DATE_SETUP_AUTO ?></label>
					</div>

					<div style="width : 645px; display :flex; flex-direction: column; float:left;">

						<div class="div_client_time" <?=$timeFunc->getTimeServerMode("custom") ?> >
							<select class="select_systemCheck" id="select_systemCheckYear">
								<?=$timeFunc->printYear() ?>
							</select>
							<span class="span_systemDate">
								<?=Time_setup\Lang\STR_DATE_SETUP_YEAR ?>
							</span>

							<select class="select_systemCheck" id="select_systemCheckMonth">
								<?=$timeFunc->printMonth() ?>
							</select>
							<span class="span_systemDate">
								<?=Time_setup\Lang\STR_DATE_SETUP_MONTH ?>
							</span>

							<select class="select_systemCheck" id="select_systemCheckDay">
								<?=$timeFunc->printDay() ?>
							</select>
							<span class="span_systemDate">
								<?=Time_setup\Lang\STR_DATE_SETUP_DAY ?>
							</span>
						</div>

						<div class="div_client_time" <?=$timeFunc->getTimeServerMode("custom") ?> >
							<select class="select_systemCheck" id="select_systemCheckHour">
								<?=$timeFunc->printHour() ?>
							</select>
							<span class="span_systemDate">
								<?=Time_setup\Lang\STR_DATE_SETUP_HOUR ?>
							</span>

							<select class="select_systemCheck" id="select_systemCheckMinute">
								<?=$timeFunc->printMinute() ?>
							</select>
							<span class="span_systemDate">
								<?=Time_setup\Lang\STR_DATE_SETUP_MINUTE ?>
							</span>

							<select class="select_systemCheck" id="select_systemCheckSecond">
								<?=$timeFunc->printSec() ?>
							</select>
							<span class="span_systemDate">
								<?=Time_setup\Lang\STR_DATE_SETUP_SECOND ?>
							</span>
						</div>

					</div>

					<div class="div_table_visible" <?=$timeFunc->getTimeServerMode("auto") ?>>
						<div class="div_table_title_wrap">
							<div class="div_contents_table_text">
								<?=Time_setup\Lang\STR_DATE_SETUP_SERVER ?>
							</div>
							
							<div class="div_contents_table_text">
								<?=Time_setup\Lang\STR_DATE_NEXT_SYNC_TIME ?>
							</div>
						</div>
						<div class="div_table_contents_wrap">
							<div class="div_contents_table_box">
								<div class="textbox">
									<label for="text_autoTime"><?=Time_setup\Lang\STR_DATE_SETUP_SERVER ?></label>
									<input type="text" id="div_autoTime" value="<?=$timeFunc->getTimeServerUrl() ?>">
								</div>
							</div>
							
							<div class="div_contents_table_box">
								<div class="textbox">
									<label for="text_autoTime"><?=Time_setup\Lang\STR_DATE_SETUP_SERVER ?></label>
									<input type="text" id="div_nextSyncTime" value="<?=$timeFunc->getSyncTime() ?>"  style="width: 173px; float: left; color: grey;" readonly />
									<div class="div_time_button" id="div_button_syncTime"> <?=Time_setup\Lang\STR_DATE_SYNC_TIME ?> </div>
								</div>
							</div>
						</div>
						
					</div>

				</div>
			</div>

			<div class="div_contents_cell_line"></div>

			<div class="div_button_wrap">
				<div id="div_button_apply" class="div_time_button"> <?=Time_setup\Lang\STR_DATE_BUTTON_SET ?> </div>
			</div>
		</div>
	</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>