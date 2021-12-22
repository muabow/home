<?php
	if( $commonFunc->procModuleStatus(basename(__FILE__)) ) return ;

	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";

	$systemFunc = new System_management\Func\SystemFunc();
?>

<link rel="stylesheet" href="<?=System_management\Def\PATH_WEB_CSS_STYLE ?>" type="text/css">

<div id="div_contents">
	<div id="div_log_title"> <?=System_management\Lang\STR_MENU_NAME ?> </div>
	<hr>

	<div id="div_contents_table">
		<?php
			// 시스템 업그레이드 관련
		?>
		<div class="div_contents_title">
			<?=System_management\Lang\STR_SYSTEM_UPGRADE ?>
		</div>
		<div class="div_contents_cell">

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=System_management\Lang\STR_SYSTEM_UPGRADE_FIND ?>
				</div>

				<div class="div_contents_cell_contents">
					<div style="float : left;">
						<div class="filebox_upgrade">
							<input class="upload-name" id="label_uploadFile" value="<?=System_management\Lang\STR_SYSTEM_UPGRADE_FIND ?>" disabled="disabled">
							<label for="file_uploadFile"><?=System_management\Lang\STR_SYSTEM_UPGRADE_UPLOAD ?></label>
							<input type="file" id="file_uploadFile" class="upload-hidden" accept=".imkp" />
						</div>
					</div>

					<div class="container">
						<div class="progress_outer">
							<div id="div_fileUpload_progress" class="progress"></div>
						</div>
					</div>
				</div>

			</div>
			<div class="div_contents_cell_line"></div>

			<div class="div_button_wrap">
				<div id="div_buttonCancelFileUpload" class="div_button"> <?=System_management\Lang\STR_SYSTEM_BUTTON_RESET ?> </div>
				<div id="div_buttonApplyFileUpload" class="div_button"> <?=System_management\Lang\STR_SYSTEM_BUTTON_SET ?> </div>
			</div>
		</div>

		<?php
			// 패스워드 변경 관련
		?>
		<div class="div_contents_title">
			<?=System_management\Lang\STR_SYSTEM_PASSWD_CHANGE ?>
		</div>

		<div class="div_contents_cell">

			<div class="div_contents_cell_line">

				<div class="div_contents_cell_title">
					<?=System_management\Lang\STR_SYSTEM_PASSWD_ACCOUNT ?>
				</div>

				<div class="div_contents_cell_contents">
					<select id="select_accountList">
						<?=$systemFunc->getAccountList($_SESSION['username']); ?>
					</select>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=System_management\Lang\STR_SYSTEM_PASSWD_CURRENT ?>
				</div>

				<div class="div_contents_cell_contents">
					<div class="textbox">
						<label for="password_currentPassword"><?=System_management\Lang\STR_SYSTEM_PASSWD_CURRENT ?></label>
						<input type="password" id="password_currentPassword" maxlength="16" />
					</div>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=System_management\Lang\STR_SYSTEM_PASSWD_NEW ?>
				</div>

				<div class="div_contents_cell_contents">
					<div class="textbox">
						<label for="password_newPassword"><?=System_management\Lang\STR_SYSTEM_PASSWD_NEW ?></label>
						<input type="password" id="password_newPassword" maxlength="16" />
					</div>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=System_management\Lang\STR_SYSTEM_PASSWD_CHECK ?>
				</div>

				<div class="div_contents_cell_contents">
					<div class="textbox">
						<label for="password_checkPassword"> <?=System_management\Lang\STR_SYSTEM_PASSWD_CHECK ?> </label>
						<input type="password" id="password_checkPassword" maxlength="16" />
					</div>
				</div>
			</div>

			<div class="div_contents_cell_line"></div>

			<div class="div_button_wrap">
				<div class="div_button" id="div_buttonCancelPassword">
					<?=System_management\Lang\STR_SYSTEM_BUTTON_RESET ?>
				</div>

				<div class="div_button" id="div_buttonApplyPassword">
					<?=System_management\Lang\STR_SYSTEM_BUTTON_SET ?>
				</div>
			</div>
		</div>

		<?php
			// 계정 잠금 설정
		?>
		<div class="div_contents_title">
			<?=System_management\Lang\STR_SYSTEM_LOCK_TITLE ?>
		</div>

		<div class="div_contents_cell">
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=System_management\Lang\STR_SYSTEM_LOCK_ACCOUNT_INFO ?>
				</div>

				<div class="div_contents_cell_contents">
					<div class="div_table">
						<div class="div_table_title">
							<div class="div_table_cell table_cell_id"><?=System_management\Lang\STR_SYSTEM_LOCK_ACCOUNT_NAME ?></div>
							<div class="div_table_cell table_cell_count"><?=System_management\Lang\STR_SYSTEM_LOCK_TRY_COUNT ?></div>
							<div class="div_table_cell table_cell_lock"><?=System_management\Lang\STR_SYSTEM_LOCK_STATUS ?></div>
							<div class="div_table_cell table_cell_reset"><?=System_management\Lang\STR_SYSTEM_LOCK_RESET_COUNT ?></div>
						</div>
						<div class="div_talbe_contents">
							<div class="div_table_row">
								<div class="div_table_cell table_cell_id">setup</div>
								<div class="div_table_cell table_cell_count"><span id="span_count_setup"><?=$systemFunc->get_try_count("setup") ?></span></div>
								<div class="div_table_cell table_cell_lock">
									<div id="div_img_count_setup" class="<?=$systemFunc->get_lock_status("setup") ?>"></div>
									</div>
								<div class="div_table_cell table_cell_reset">
									<div id="div_button_lock_reset_setup" class="div_button"><?=System_management\Lang\STR_SYSTEM_LOCK_RESET ?></div>
								</div>
							</div>
							<div class="div_table_row">
								<div class="div_table_cell table_cell_id">user</div>
								<div class="div_table_cell table_cell_count"><span id="span_count_user"><?=$systemFunc->get_try_count("user") ?></span></div>
								<div class="div_table_cell table_cell_lock">
									<div id="div_img_count_user" class="<?=$systemFunc->get_lock_status("user") ?>"></div>
								</div>
								<div class="div_table_cell table_cell_reset">
									<div id="div_button_lock_reset_user" class="div_button"><?=System_management\Lang\STR_SYSTEM_LOCK_RESET ?></div>
								</div>
							</div>
							<div class="div_table_row">
								<div class="div_table_cell table_cell_id">guest</div>
								<div class="div_table_cell table_cell_count"><span id="span_count_guest"><?=$systemFunc->get_try_count("guest") ?></span></div>
								<div class="div_table_cell table_cell_lock">
									<div id="div_img_count_guest" class="<?=$systemFunc->get_lock_status("guest") ?>"></div>
								</div>
								<div class="div_table_cell table_cell_reset">
									<div id="div_button_lock_reset_guest" class="div_button"><?=System_management\Lang\STR_SYSTEM_LOCK_RESET ?></div>
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>


			<div class="div_contents_cell_line"></div>
			<div class="div_button_wrap">
			</div>
		</div>

		<?php
			// 시스템 체크 관련
		?>
		<div class="div_contents_title">
			<?=System_management\Lang\STR_SYSTEM_CHECK?>
		</div>

		<div class="div_contents_cell">
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=System_management\Lang\STR_SYSTEM_CHECK_TIME ?>
				</div>

				<div class="div_contents_cell_contents">
					<select class="select_systemCheck" id="select_systemCheckHour">
						<?=$systemFunc->getTimeListHour() ?>
					</select>
					<?=System_management\Lang\STR_SYSTEM_CHECK_HOUR ?>
					&nbsp;

					<select class="select_systemCheck" id="select_systemCheckMinute">
						<?=$systemFunc->getTimeListMinute() ?>
					</select>
					<?=System_management\Lang\STR_SYSTEM_CHECK_MINUTE ?>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=System_management\Lang\STR_SYSTEM_CHECK_OPERATION ?>
				</div>

				<div class="div_contents_cell_contents">
					<div class="div_systemCheckOperation <?=$systemFunc->getTimeStatClassOperation() ?>">
						<div class="div_systemCheckToggle <?=$systemFunc->getTimeStatClassToggle() ?>"></div>
						<span class="span_systemCheckOn"> ON </span>
						<span class="span_systemCheckOff"> OFF </span>
					</div>
				</div>
			</div>

			<div class="div_contents_cell_line"></div>

			<div class="div_button_wrap">
				<div class="div_button" id="div_buttonCancelSystemCheck">
					<?=System_management\Lang\STR_SYSTEM_BUTTON_RESET ?>
				</div>
				<div class="div_button" id="div_buttonApplySystemCheck">
					<?=System_management\Lang\STR_SYSTEM_BUTTON_SET ?>
				</div>
			</div>
		</div>

		<?php
			// 시스템 재시작 관련
		?>
		<div class="div_contents_title">
			<?=System_management\Lang\STR_SYSTEM_REBOOT ?>
		</div>

		<div class="div_contents_cell">

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=System_management\Lang\STR_SYSTEM_REBOOT ?>
				</div>
				<div class="div_contents_cell_contents">
					<?=System_management\Lang\STR_SYSTEM_REBOOT_MSG ?>
				</div>
			</div>

			<div class="div_contents_cell_line"></div>

			<div class="div_button_wrap">
				<div class="div_button" id="div_buttonApplyReboot">
					<?=System_management\Lang\STR_SYSTEM_BUTTON_SET ?>
				</div>
			</div>
		</div>

		<div class="div_contents_title">
			<?=System_management\Lang\STR_SYSTEM_FACTORY ?>
		</div>

		<?php
			// 시스템 초기화 관련
		?>
		<div class="div_contents_cell">
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=System_management\Lang\STR_SYSTEM_FACTORY ?>
				</div>

				<div class="div_contents_cell_contents">
					<?=System_management\Lang\STR_SYSTEM_FACTORY_MSG ?>
				</div>
			</div>

			<div class="div_contents_cell_line"></div>

			<div class="div_button_wrap">
				<div  class="div_button" id="div_buttonApplyFactory">
					<?=System_management\Lang\STR_SYSTEM_BUTTON_SET ?>
				</div>
			</div>
		</div>

	</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>