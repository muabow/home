<?php
    $env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

    include_once $env_pathModule . "common/common_define.php";
    include_once $env_pathModule . "common/common_script.php";

	$systemFunc = new System_management\Func\SystemFunc();
?>

<link rel="stylesheet" href="<?=System_management\Def\PATH_WEB_CSS_STYLE_MOBILE ?>" type="text/css">

<div id="div_contents">
	<div id="div_log_title"> <?=System_management\Lang\STR_MENU_NAME ?> </div>
	<hr>

	<div id="div_contents_table">

		<?php
			// 시스템 체크 관련
		?>
		<div class="div_contents_cell">
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_category">
					<?=System_management\Lang\STR_SYSTEM_CHECK_INFO ?>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=System_management\Lang\STR_SYSTEM_CHECK_TIME ?>
				</div>

				<div class="div_contents_cell_contents">
					<?php
						echo $systemFunc->getTimeHour()
						. " : "
						. $systemFunc->getTimeMinute();

					?>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=System_management\Lang\STR_SYSTEM_CHECK_OPERATION ?>
				</div>

				<div class="div_contents_cell_contents">
					<span style="color: <?=$systemFunc->getTimeStatColor() ?>;"><?php echo strtoupper($systemFunc->getTimeStat()); ?></span>
				</div>
			</div>

			<div class="div_contents_cell_line"></div>
		</div>

		<?php
			// 계정 잠금 관련
		?>
		<div class="div_contents_cell">
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_category">
					<?=System_management\Lang\STR_SYSTEM_LOCK_ACCOUNT_INFO ?>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<div class="title_sub"><?=System_management\Lang\STR_SYSTEM_LOCK_ACCOUNT_NAME ?></div>
					<div class="title_sub"><?=System_management\Lang\STR_SYSTEM_LOCK_STATUS ?></div>
				</div>

				<div class="div_contents_column">
					<div class="div_contents_cell_line">
						<div class="div_contents_cell_contents">
							<div class="div_id">setup</div>
							<div class="div_lock_img">
								<div id="div_img_count_m_setup" class="<?=$systemFunc->get_lock_status("setup") ?>"></div>
							</div>
						</div>
					</div>
					<div class="div_contents_cell_line">
						<div class="div_contents_cell_contents">
							<div class="div_id">user</div>
							<div class="div_lock_img">
								<div id="div_img_count_m_user" class="<?=$systemFunc->get_lock_status("user") ?>"></div>
							</div>
						</div>
					</div>
					<div class="div_contents_cell_line"> 
						<div class="div_contents_cell_contents">
							<div class="div_id">guest</div>
							<div class="div_lock_img">
								<div id="div_img_count_m_guest" class="<?=$systemFunc->get_lock_status("guest") ?>"></div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="div_contents_cell_line"></div>
		</div>


		<?php
			// 시스템 재시작 관련
		?>
		<div class="div_contents_cell">
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_category">
					<?=System_management\Lang\STR_SYSTEM_REBOOT ?>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=System_management\Lang\STR_SYSTEM_REBOOT_MSG ?>
				</div>
				<div class="div_contents_cell_contents">
					<div class="div_button_wrap">
						<div class="div_button" id="div_buttonApplyReboot">
							<?=System_management\Lang\STR_SYSTEM_BUTTON_SET ?>
						</div>
					</div>
				</div>
			</div>

			<div class="div_contents_cell_line"></div>

		</div>
	</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>