<?php
	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";
	
	$chime_handle = new Chime_file_management\Func\ChimeFileHandler();
	$remain_size = $chime_handle->get_size_upload_available();

	$json_file_list = $chime_handle->get_file_list();

	$str_ext_status = "display: none";
	if( $chime_handle->is_exist_ext_storage() ) {
		$str_ext_status = "";
	}
?>

<link rel="stylesheet" href="<?=Chime_file_management\Def\PATH_WEB_CSS_STYLE_MOBILE ?>" type="text/css">

<div id="div_contents">
	<div id="div_title"> <?=Chime_file_management\Lang\STR_MENU_NAME ?> </div>
	<hr>

	<div id="div_contents_table">
		<div class="div_contents_cell">
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_category">
					<?=Chime_file_management\Lang\STR_SRCFILE_ADD ?>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Chime_file_management\Lang\STR_SRCFILE_ADD_FIND ?>
				</div>

				<div class="div_contents_cell_contents">
					<div class="filebox_upload">
						<input class="upload-name" id="label_uploadFile" value="<?=Chime_file_management\Lang\STR_SRCFILE_ADD_FIND ?>" disabled="disabled">
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
					<?=Chime_file_management\Lang\STR_EXT_SELECT_UPLOAD_STORAGE ?>
				</div>

				<div class="div_contents_cell_contents" style="display: flex; flex-direction: column;">
					<div style="float : left;">
						<input type="radio" name="radio_upload_case" value="upload_internal" checked="checked" /> <?=Chime_file_management\Lang\STR_EXT_SELECT_STORAGE_INTERNAL ?> </input> 
					</div>
					<div style="float : left; padding-top: 5px;">
						<input type="radio" name="radio_upload_case" value="upload_external" /> <?=Chime_file_management\Lang\STR_EXT_SELECT_STORAGE_EXTERNAL ?> </input>
					</div>
				</div>
			</div>
			
			<div class="div_button_wrap" id="columnButton" style="padding-top: 5px;">
			<div style="padding-bottom: 5px;">
					<input type="text" id="text_size_upload_available" value="0" hidden />
					<input type="text" id="text_size_upload_available_ext" value="0" hidden />

					<div class="div_upload_avail_size_info"> <?=Chime_file_management\Lang\STR_SRCFILE_ADD_AVAILABLE_MEM . " : " ?> <span id="span_upload_available"></span> Mbytes </div> </p>
					<div class="div_upload_avail_size_info" style="<?=$str_ext_status ?>" name="div_ext_avail_info"> <?=Chime_file_management\Lang\STR_EXT_SRCFILE_ADD_AVAILABLE_MEM . " : " ?> <span id="span_ext_avail_info"></span> Mbytes </div>
				</div>
				<div>
					<div id="div_button_upload_clear" class="div_button"> <?=Chime_file_management\Lang\STR_SRCFILE_BUTTON_RESET ?> </div>
					<div id="div_button_upload_apply" class="div_button"> <?=Chime_file_management\Lang\STR_SRCFILE_ADD_UPLOAD ?> </div>
				</div>
			</div>
			<div class="div_button_wrap" style="font-size: 12px;">
				* <?=Chime_file_management\Lang\STR_CHIME_HELP_1 ?> <br />
				* <?=Chime_file_management\Lang\STR_CHIME_HELP_2 ?>
			</div>
			<div class="div_contents_cell_line"></div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_category">
					<?=Chime_file_management\Lang\STR_SRCFILE_TABLE ?>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_1">
					<div class="div_title_row">
						<div class="div_title_number">		<?=Chime_file_management\Lang\STR_TITLE_NUMBER ?> 	</div>
						<div class="div_title_chime_name">	<?=Chime_file_management\Lang\STR_TITLE_NAME ?> 	</div>
						<div class="div_title_play_time">	<?=Chime_file_management\Lang\STR_TITLE_PLAY_TIME ?> </div>
						<div class="div_title_checkBox"><input type="checkbox" class="input_chime_check_all"> 	</div>
					</div>
					<div class="div_row_wrap"></div>
				</div>

				<div class="div_contents_cell_line"></div>

				<div class="div_chime_controller">
					<div class="div_control_button_download"	id="control_button_download">	</div>
					<div class="div_control_button_remove" 		id="control_button_remove">		</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>
