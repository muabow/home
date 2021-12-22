<?php
	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));
	
	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";
	
	$commonInfoFunc = new Common\Func\CommonInfoFunc();

	// 장치 타입이 source 인 경우(audio_input 이 0개) 업로드만 허용.
	$json_dev_info = json_decode(file_get_contents("/opt/interm/conf/config-device-info.json"));
    $is_dev_output = ($json_dev_info->port->Audio->out == 0 ? false : true);
	
	$status_style_output = "";
	$status_style_hidden = "display: none;";
	if( !$is_dev_output ) {
		$status_style_output = "display: none;";
		$status_style_hidden = "";
	}
 
	// 장치 mode가 [INPUT CONNECTING] 라면 업로드만 허용.
	$json_env_info = json_decode(file_get_contents("/opt/interm/conf/env.json"));
	if( isset($json_env_info->mode->set) && $json_env_info->mode->set == "INPUT CONNECTING" ) {
		$status_style_output = "display: none;";
		$status_style_hidden = "";
	}

	// volume control, device_type이 amp 인 경우 사용하지 않음.
    $is_device_amp = ($json_env_info->device->device_type == "amp" ? true : false);

    $status_style_amp_device = "";
    if( $is_device_amp ) {
        $status_style_amp_device = "display: none;";
	}
	
	$source_handler = new Source_file_management\Func\SourceFileHandler();

	$str_ext_status = "display: none";
	if( $source_handler->is_exist_ext_storage() ) {
		$str_ext_status = "";
	}
?>

<link rel="stylesheet" href="<?=Source_file_management\Def\PATH_WEB_CSS_STYLE_MOBILE ?>" type="text/css">
<script src="<?=Source_file_management\Def\PATH_WEB_JS_MARQUEE ?>"></script>
<script src="<?=Source_file_management\Def\PATH_WEB_JS_MARQUEE_MIN ?>"></script>

<div id="div_contents">
	<div id="div_log_title"> <?=Source_file_management\Lang\STR_MENU_NAME ?> </div>
	<hr>

	<div id="div_contents_table">
		<div class="div_contents_cell">
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_category">
					<?=Source_file_management\Lang\STR_SRCFILE_ADD ?>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Source_file_management\Lang\STR_SRCFILE_ADD_FIND ?>
				</div>

				<div class="div_contents_cell_contents">
					<div class="filebox_upgrade">
						<input class="upload-name" id="label_uploadFile" value="<?=Source_file_management\Lang\STR_SRCFILE_ADD_FIND ?>" disabled="disabled">
						<label for="file_uploadFile">. . .</label>
						<input type="file" id="file_uploadFile" class="upload-hidden" accept=".mp3, .wav" multiple/>
					</div>

					<div class="container">
						<div class="progress_outer">
							<div id="div_fileUpload_progress" class="progress"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="div_contents_cell_line" style="<?=$str_ext_status ?>" id="div_ext_upload_storage">
				<div class="div_contents_cell_title">
					<?=Source_file_management\Lang\STR_EXT_SELECT_UPLOAD_STORAGE ?>
				</div>

				<div class="div_contents_cell_contents" style="display: flex; flex-direction: column;">
					<div style="float : left;">
						<input type="radio" name="radio_upload_case" value="upload_internal" checked="checked" /> <?=Source_file_management\Lang\STR_EXT_SELECT_STORAGE_INTERNAL ?> </input> 
					</div>
					<div style="float : left; padding-top: 5px;">
						<input type="radio" name="radio_upload_case" value="upload_external" /> <?=Source_file_management\Lang\STR_EXT_SELECT_STORAGE_EXTERNAL ?> </input>
					</div>
				</div>
			</div>

			<div class="div_button_wrap" id="columnButton" style="padding-top: 5px;">
				<div style="padding-bottom: 5px;">
					<input type="text" id="text_size_upload_available" value="0" hidden />
					<input type="text" id="text_size_upload_available_ext" value="0" hidden />

					<div class="div_upload_avail_size_info"> <?=Source_file_management\Lang\STR_SRCFILE_ADD_AVAILABLE_MEM . " : " ?> <span id="span_upload_available"></span> Mbytes </div> </p>
					<div class="div_upload_avail_size_info" style="<?=$str_ext_status ?>" name="div_ext_avail_info"> <?=Source_file_management\Lang\STR_EXT_SRCFILE_ADD_AVAILABLE_MEM . " : " ?> <span id="span_ext_avail_info"></span> Mbytes </div>
				</div>
				<div>
					<div id="div_button_upload_clear" class="div_button"> <?=Source_file_management\Lang\STR_SRCFILE_BUTTON_RESET ?> </div>
					<div id="div_button_upload_apply" class="div_button"> <?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD ?> </div>
				</div>
				<span style="padding-top: 10px; font-size: 10pt; font-weight: bolder; <?php echo $status_style_hidden; ?>">* <?=Source_file_management\Lang\STR_HELP_SRC_MODE ?> </span>
			</div>
			<div class="div_contents_cell_line"></div>


			<div class="div_contents_cell_line">
				<div class="div_contents_cell_category">
					<?=Source_file_management\Lang\STR_SRCFILE_SRC_PLAY ?>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Source_file_management\Lang\STR_SRCFILE_TABLE ?>
				</div>

				<div class="div_contents_cell_1">
					<div class="div_title_row">
						<div class="div_title_number"> 		<?=Source_file_management\Lang\STR_TITLE_NUMBER ?> 	</div>
						<div class="div_title_source_name"> <?=Source_file_management\Lang\STR_TITLE_NAME ?> 	</div>
						<div class="div_title_source_type"> <?=Source_file_management\Lang\STR_TITLE_TYPE ?> 	</div>
						<div class="div_title_source_info"> <?=Source_file_management\Lang\STR_TITLE_INFO ?> 	</div>
						<div class="div_title_channel"> 	<?=Source_file_management\Lang\STR_TITLE_CHANNEL ?> </div>
						<div class="div_title_play_time">	<?=Source_file_management\Lang\STR_TITLE_PLAY_TIME ?> </div>
						<div class="div_title_loop_count">	<?=Source_file_management\Lang\STR_TITLE_LOOP ?> 	</div>
						<div class="div_title_checkBox"><input type="checkbox" class="input_source_check_all"> 	</div>
					</div>
					<div class="div_row_wrap" id="sortable"></div>
				</div>

				<div class="div_contents_cell_line"></div>

				<div class="div_source_control_play" style="<?php echo $status_style_output; ?>">
					<div class="div_source_control_display_none" id="div_current_play_source">
					</div>
				</div>

				<div class="div_source_controller"  style="<?php echo $status_style_output; ?>">
					<div class="div_control_button_prev"		id="control_button_prev">		</div>
					<div class="div_control_button_play"		id="control_button_play">		</div>
					<div class="div_control_button_pause"		id="control_button_pause">		</div>
					<div class="div_control_button_stop"		id="control_button_stop">		</div>
					<div class="div_control_button_next"		id="control_button_next">		</div>
					<div class="div_control_button_loop"		id="control_button_loop">		</div>
					<div class="div_control_button_download"	id="control_button_download">	</div>
					<div class="div_control_button_remove" 		id="control_button_remove">		</div>
				</div>

				<div class="div_source_controller" style="<?php echo $status_style_hidden; ?>">
					<div class="div_control_button_download"	id="control_button_download">	</div>
					<div class="div_control_button_remove" 		id="control_button_remove">		</div>
				</div>
			</div>

			<div class="div_contents_cell_line" style="<?php echo $status_style_output; ?>">
				<div class="div_contents_cell_category" >
					<?=Source_file_management\Lang\STR_OPER_INFO ?>
				</div>
			</div>
			<div class="div_contents_cell_line" style="<?php echo $status_style_output; ?>">
				<div class="div_contents_cell_title">
					<?=Source_file_management\Lang\STR_INFO_LEVEL_METER ?>
				</div>

				<div class="div_contents_cell_contents" >
					<div class="outputVolume" style="clear:both;"></div>
					<div class="level_outputVolume" style="display:none;">0</div>
				</div>
			</div>

			<div class="div_contents_cell_line" style="<?php echo $status_style_output; ?> <?php echo $status_style_amp_device; ?>">
				<div class="div_contents_cell_title">
					<?=Source_file_management\Lang\STR_INFO_VOLUME ?>
				</div>

				<div class="div_contents_cell_contents" style="padding-top: 10px;">
					<div class="div_contents_cell_column_wrap">
						<input class="slider" id="slider_volume" type="range" min="0" max="100" value="0" style="margin-left: 10px; width: 100%;" />
						<input type="text" id="slider_value" maxlength=3>

						<div id="div_button_apply_volume" class="div_class_button_volume"> 
						</div>
					</div>
				</div>
			</div>
		
			<div class="div_contents_cell_line">
			</div>
		</div>
	</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>
<?php include $env_pathModule . "common/audio_equlizer.php"; ?>
