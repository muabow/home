<?php
	if( $commonFunc->procModuleStatus(basename(__FILE__)) ) return ;

	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";

	$tts_handle = new TTS_file_management\Func\TTS_Handler();

	$str_ext_status = "display: none";
	if( $tts_handle->is_exist_ext_storage() ) {
		$str_ext_status = "";
	}
?>

<link rel="stylesheet" href="<?=TTS_file_management\Def\PATH_WEB_CSS_STYLE ?>" type="text/css">

<div id="div_contents">
	<div id="div_title"> <?=TTS_file_management\Lang\STR_MENU_NAME ?> </div>
	<hr>

	<div id="div_contents_table">
		<div class="div_contents_title">
			<?=TTS_file_management\Lang\STR_TTS_TITLE_FORM ?>
		</div>

		<div class="div_contents_sub_title">
		<?=TTS_file_management\Lang\STR_TTS_TITLE_INPUT ?>
		</div>
		<div class="div_contents_cell">
			<div class="div_contents_cell_2 display_flex">
				<input type="text" id="text_tts_title" placeholder="<?=TTS_file_management\Lang\STR_TTS_TITLE_INPUT_NAME ?>" maxlength="50" />

				<select class="select_tts" id="select_tts_language">
					<option value="" selected hidden value=""> <?=TTS_file_management\Lang\STR_TTS_TITLE_INPUT_LANGUAGE ?> </option>
					<?php echo $tts_handle->load_support_language(); ?>
				</select>
				
				<select class="select_tts" id="select_tts_gender">
					<option value="" selected hidden> <?=TTS_file_management\Lang\STR_TTS_TITLE_INPUT_GENDER ?> </option>
					<option value="male"  > <?=TTS_file_management\Lang\STR_TTS_TITLE_OPT_GENDER_MALE ?> </option>
					<option value="female"> <?=TTS_file_management\Lang\STR_TTS_TITLE_OPT_GENDER_FEMALE ?> </option>
				</select>
			</div>
			
			<div class="div_contents_cell_2" style="margin-top: 5px;">
				<textarea id="text_tts_speak" placeholder="<?=TTS_file_management\Lang\STR_TTS_TITLE_INPUT_TEXT ?>"></textarea>
				<span class="span_tts_text_limit_info">- <?=TTS_file_management\Lang\STR_TTS_INFO_LIMIT_BYTES ?> : [<span id="span_tts_text_limit">0</span>/<?=TTS_file_management\Def\MAX_BYTES_TTS_TEXT ?>] bytes </span>
			</div>
			
			<div class="div_contents_sub_title">
			<?=TTS_file_management\Lang\STR_TTS_TITLE_CHIME_SETUP ?>
			</div>

			<div class="div_contents_cell_2 display_flex">
				<select class="select_tts" id="select_chime_begin">
					<option value="-" class="select_opt_placeholder" selected hidden> <?=TTS_file_management\Lang\STR_TTS_TITLE_CHIME_BEGIN ?> </option>
					<option value=""> <?=TTS_file_management\Lang\STR_TTS_TITLE_OPT_CHIME_NONE ?> </option>
					<?php echo $tts_handle->load_chime_list(); ?>
				</select>
				
				<select class="select_tts" id="select_chime_end">
					<option value="-" class="select_opt_placeholder" selected hidden> <?=TTS_file_management\Lang\STR_TTS_TITLE_CHIME_END ?> </option>
					<option value=""> <?=TTS_file_management\Lang\STR_TTS_TITLE_OPT_CHIME_NONE ?> </option>
					<?php echo $tts_handle->load_chime_list(); ?>
				</select>
			</div>
			
			<div class="div_contents_sub_title" style="margin-top: 5px;">
				<?=TTS_file_management\Lang\STR_TTS_TITLE_TTS_OPTION ?>
			</div>

			<div class="div_contents_cell_2">
				<div class="div_contents_cell_2_inner display_flex" style="margin-bottom: 0px;">
					<div class="div_col_slider_1">
						<div class="div_wrap_slider">
							<div class="div_slider_title">
								<?=TTS_file_management\Lang\STR_TTS_TITLE_OPT_PITCH ?> [%]
							</div>
							<div class="div_wrap_slider_ctrl">
								<input type="text" class="slider_info_min" id="input_pct_pitch_min" value="50" disabled>
								<div class="slider_bar" id="slide_pct_pitch"></div> 
								<input type="text" class="slider_info_max" id="input_pct_pitch_max" value="200" disabled>
								<input type="text" class="tts_slider_value" maxlength="3" id="option_pct_pitch">
							</div>
						</div>
						<div class="div_wrap_slider">
							<div class="div_slider_title">
								<?=TTS_file_management\Lang\STR_TTS_TITLE_OPT_SPEED ?> [%]
							</div>
							<div class="div_wrap_slider_ctrl">
								<input type="text" class="slider_info_min" id="input_pct_speed_min" value="50" disabled>
								<div class="slider_bar" id="slide_pct_speed"></div>
								<input type="text" class="slider_info_max" id="input_pct_speed_max" value="400" disabled>
								<input type="text" class="tts_slider_value" maxlength="3" id="option_pct_speed">
							</div>
						</div>
						<div class="div_wrap_slider">
							<div class="div_slider_title">
								<?=TTS_file_management\Lang\STR_TTS_TITLE_OPT_VOLUME ?> [%]
							</div>
							<div class="div_wrap_slider_ctrl">
								<input type="text" class="slider_info_min" id="input_pct_volume_min" value="0" disabled>
								<div class="slider_bar" id="slide_pct_volume"></div>
								<input type="text" class="slider_info_max" id="input_pct_volume_max" value="500" disabled>
								<input type="text" class="tts_slider_value" maxlength="3" id="option_pct_volume">
							</div>
						</div>
					</div>
					<div class="div_col_slider_2">
						<div class="div_wrap_slider">
							<div class="div_slider_title">
							<?=TTS_file_management\Lang\STR_TTS_TITLE_OPT_SENTENCE_PAUSE ?> [ms]
							</div>
							<div class="div_wrap_slider_ctrl">
								<input type="text" class="slider_info_min" id="input_num_sp_min" value="0" disabled>
								<div class="slider_bar" id="slide_num_sp"></div>
								<input type="text" class="slider_info_max" id="input_num_sp_max" style="width: 40px;" value="65536" disabled>
								<input type="text" class="tts_slider_value" maxlength="5" id="option_num_sp">
							</div>
						</div>
						<div class="div_wrap_slider">
							<div class="div_slider_title">
							<?=TTS_file_management\Lang\STR_TTS_TITLE_OPT_COMMA_PAUSE ?> [ms]
							</div>
							<div class="div_wrap_slider_ctrl">
								<input type="text" class="slider_info_min" id="input_num_cp_min" value="0" disabled>
								<div class="slider_bar" id="slide_num_cp"></div>
								<input type="text" class="slider_info_max" id="input_num_cp_mam" style="width: 40px;" value="65536" disabled>
								<input type="text" class="tts_slider_value" maxlength="5" id="option_num_cp">
							</div>
						</div>
					</div>
				</div>	
			</div>
			
			<div id="div_ext_upload_storage" style="<?=$str_ext_status ?>">
				<div class="div_contents_sub_title">
					<?=TTS_file_management\Lang\STR_EXT_SELECT_UPLOAD_STORAGE ?>
				</div>

				<div class="div_contents_cell_2">
					<div class="div_contents_cell_2_inner display_flex">
						<div style="float : left; ">
							<input type="radio" name="radio_upload_case" value="upload_internal" checked="checked" /> <?=TTS_file_management\Lang\STR_EXT_SELECT_STORAGE_INTERNAL ?> </input> <br />
							<input type="radio" name="radio_upload_case" value="upload_external" /> <?=TTS_file_management\Lang\STR_EXT_SELECT_STORAGE_EXTERNAL ?> </input>
						</div>
					</div>
				</div>
			</div>
			<div class="div_contents_cell_line"></div>

			<div class="div_button_wrap">
				<div id="button_preview_tts" class="div_button div_left"> <?=TTS_file_management\Lang\STR_TTS_BUTTON_PREVIEW ?> </div>
				<span id="span_tts_duration"> [00:00.00] </span>

				<div id="button_reset_tts" class="div_button"> <?=TTS_file_management\Lang\STR_TTS_BUTTON_RESET ?> </div>
				<div id="button_save_tts" class="div_button"> <?=TTS_file_management\Lang\STR_TTS_BUTTON_SAVE ?> </div>
			</div>
		</div>
	</div>

	<div id="div_content_table_tts_list">
		<div class="div_contents_title">
			<?=TTS_file_management\Lang\STR_TTS_TABLE ?> 
			<span class="span_tts_avail_info"> | <?=TTS_file_management\Lang\STR_TTS_INFO_AVAIL_SIZE ?> : <span id="span_tts_avail_size"><?=$tts_handle->get_size_upload_available() ?></span> Mbytes 
			<span class="span_tts_avail_info" name="div_ext_avail_info" style="<?=$str_ext_status ?>"> | <?=TTS_file_management\Lang\STR_EXT_SRCFILE_ADD_AVAILABLE_MEM ?> : <span id="span_ext_avail_info"><?=$tts_handle->get_size_upload_available_ext() ?></span> Mbytes 
			<input type="text" id="text_size_upload_available_ext" value="0" hidden />

		</div>

		<div class="div_contents_cell_1">
			<div class="div_title_row">
				<div class="div_title_number">		<?=TTS_file_management\Lang\STR_TTS_TABLE_NUMBER ?>		</div>
				<div class="div_title_tts_name">	<?=TTS_file_management\Lang\STR_TTS_TABLE_NAME ?> 		</div>
				<div class="div_title_tts_text">	<?=TTS_file_management\Lang\STR_TTS_TABLE_CONTENT ?>	</div>
				<div class="div_title_play_time">	<?=TTS_file_management\Lang\STR_TTS_TABLE_PLAY_TIME ?>  </div>
				<div class="div_title_checkBox"><input type="checkbox" class="input_tts_check_all"> 		</div>
			</div>
			<div class="div_row_wrap" id="sortable">
			</div>
		</div>

		<div class="div_contents_cell_line"></div>

		<div class="div_tts_controller">
			<div class="div_control_button_copy"   id="control_button_copy"> </div>
			<div class="div_control_button_remove" id="control_button_remove"> </div>
		</div>
	</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>
