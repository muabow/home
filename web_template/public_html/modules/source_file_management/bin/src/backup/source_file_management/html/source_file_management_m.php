<?php
	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";

	$srcFileMngFunc = new Source_file_management\Func\SrcFileMngFunc();

	// 재생 정보 저장
	$audioStoragePath = $env_pathModule . Source_file_management\Def\PATH_SRCFILE_STORAGE;
	if(false == is_dir($audioStoragePath))
	{
		mkdir($audioStoragePath, 755);
	}

?>
<?php include $env_pathModule . "common/audio_equlizer.php"; ?>
<link rel="stylesheet" href="<?=Source_file_management\Def\PATH_WEB_CSS_STYLE_MOBILE ?>" type="text/css">

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
						<input type="file" id="file_uploadFile" class="upload-hidden" multiple=""/>
					</div>

					<div class="container">
						<div class="progress_outer">
							<div id="div_fileUpload_progress" class="progress"></div>
						</div>
					</div>
				</div>
			</div>

			<div class="div_button_wrap" id="columnButton">
				<div>
					<div id="div_availMemTxt"  style="text-align:center; font-size:12px; display:inline-block; font-weight: bold; padding:0px 0px 0px 10px"> <?= Source_file_management\Lang\STR_SRCFILE_ADD_AVAILABLE_MEM . " : " ?> </div>
					<div id="div_availMem" 	style="text-align:center; font-size:11px; display:inline-block;"> </div>
				</div>
				<div>
					<div id="div_buttonCancelFileUpload" class="div_button"> <?=Source_file_management\Lang\STR_SRCFILE_BUTTON_RESET ?> </div>
					<div id="div_buttonApplyFileUpload" class="div_button"> <?=Source_file_management\Lang\STR_SRCFILE_ADD_UPLOAD ?> </div>
				</div>
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

				<div class="div_contents_cell_contents" style="padding:10px;">
					<div class="table_wrap">
						<div class="divTableRow">
							<div class="divTableHead_left">
								<?=Source_file_management\Lang\STR_SRCFILE_TABLE_COL_INDEX ?>
							</div>
							<div class="divTableHead">
							</div>
							<div class="divTableHead">
								<?=Source_file_management\Lang\STR_SRCFILE_TABLE_COL_FNAME ?>
							</div>
							<div class="divTableHead">
								<?=Source_file_management\Lang\STR_SRCFILE_PLAY_SETUP ?>
							</div>
							<div class="divTableHead">
								<input type="checkbox" id="checkbox_select_all" />
							</div>
						</div>
						<div id="divSrcFileList"></div>
					</div>
				</div>
			</div>

			<div class="div_button_wrap">
				<div id="div_buttonPlayAll" class="div_button" style="float: left;"> <?=Source_file_management\Lang\STR_SRCFILE_PLAY_ALL ?></div>
				<div id="div_buttonStop"	class="div_button" style="float: left;"> <?=Source_file_management\Lang\STR_SRCFILE_PLAY_STOP ?> </div>
				<div id="div_buttonDel" 	class="div_button" style="float: right;"> <?=Source_file_management\Lang\STR_SRCFILE_DEL ?> </div>
			</div>
			
			<div class="div_contents_cell_line">	
				<div class="div_contents_cell_category" >
					<?=Source_file_management\Lang\STR_OPER_INFO ?>
				</div>
			</div>
			<div class="div_contents_cell_line">
					<div class="div_contents_cell_title">
						<?=Source_file_management\Lang\STR_INFO_LEVEL_METER ?>
					</div>
					<div class="div_contents_cell_contents" >
							<div class="outputVolume_1" style="clear:both;"></div>
							<div class="level_outputVolume_1" style="display:none;">0</div>
					</div>
				</div>
		
				<div class="div_contents_cell_line">
					<div class="div_contents_cell_title">
						<?=Source_file_management\Lang\STR_INFO_VOLUME ?>
					</div>
					<div class="div_contents_cell_contents">
						<div class="slidershell" id="slidershell1">
						    <div class="sliderfill" id="sliderfill1"></div>
						    <div class="slidertrack" id="slidertrack1"></div>
						    <div class="sliderthumb" id="sliderthumb1"></div>
						    <div class="slidervalue" id="slidervalue1">0</div>
						    <input class="slider" id="slider1" type="range" min="0" max="100" value="100"
						    oninput="showValue(value, 1, false);" onchange="showValue(value, 1, false);"/>
						</div>
						<div class="level_outputVolume" style="display:none;">0</div>
						<input type="range" id="range_clientVolume" min="0" max="100" value="<?=$srcFileMngFunc->getOperVolume() ?>"/>
						<input type="text1" id="text_clientVolume" class="text_volume_value" value="<?=$srcFileMngFunc->getOperVolume() ?>"/>
						<div id="div_button_apply_volume_mobile" class="div_log_button_volume">
							<?=Source_file_management\Lang\STR_COMMON_APPLY ?>
						</div>
					</div>
				</div>
				<div class="div_contents_cell_line">
				</div>
			</div>
		</div>
	</div>	
</div>
<script type="text/javascript">
	$(document).ready(function() {
		$("#range_clientVolume").change(function() {
			$("#text_clientVolume").val($(this).val());
		});
		
		$("#text_clientVolume").change(function() {
			$("#range_clientVolume").val($(this).val());
		});
	});
	
	
</script>

<?php include $env_pathModule . "common/common_js.php"; ?>
