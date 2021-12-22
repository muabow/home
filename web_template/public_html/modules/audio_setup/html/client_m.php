<?php
	$json_env_info = json_decode(file_get_contents("/opt/interm/conf/env.json"));
    $is_device_amp = ($json_env_info->device->device_type == "amp" ? true : false);

    $status_style_amp_device = "";
    if( $is_device_amp ) {
        $status_style_amp_device = "display: none;";
	}
?>

<!-- 동작 모드 화면 -->
<div class="div_contents_table">
	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_CLIENT_OP_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div id="div_client_operation_run" class="div_class_operation_run" style="display: none;">
			<span id="span_client_operation_status"><?=Audio_setup\Lang\STR_CLIENT_OP_RUN ?></span>
		</div>

		<div id="div_client_operation_stop" class="div_class_operation_stop" style="display: none;">
			<span id="span_client_operation_status"><?=Audio_setup\Lang\STR_CLIENT_OP_STOP ?></span>
		</div>
		
		<div id="div_client_operation_wait" class="div_class_operation_wait" style="display: none;">
			<span><?=Audio_setup\Lang\STR_CLIENT_OP_STOP ?></span>
		</div>
		
		<div id="div_operation_match_sample" class="div_class_operation_match_sample" style="display: none;">
			<span id="span_client_operation_status"><?=Audio_setup\Lang\STR_MATCH_SAMPLE_RATE_SERVER ?></span>
		</div>

		<div class="div_button_wrap">
			<div id="div_button_client_stop" class="div_class_button">
				<?=Audio_setup\Lang\STR_COMMON_STOP ?>
			</div>
			<div id="div_button_client_start" class="div_class_button">
				<?=Audio_setup\Lang\STR_COMMON_START ?>
			</div>
		</div>
		<div class="div_contents_cell_line" style="margin-top: 10px;"></div>
	</div>
	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_CLIENT_INFO_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_cell_category" >
				<?=Audio_setup\Lang\STR_SERVER_PROTOCOL_INFO ?>
			</div>
			<div class="div_contents_run_cell_contents">
				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						<label id="radio_run_client_protocol_label" for="radio_run_client_protocol" style="cursor:default;">-</label>
					</div>
					<div class="div_contents_run_table_box">
						<label id="radio_run_client_castType_label" for="radio_run_cast_type" style="cursor:default;">-</label>
					</div>
					<div class="div_contents_run_table_box">
						<label id="radio_run_client_encode_label" for="radio_run_client_encode" style="cursor:default;">-</label>
					</div>
				</div>
			</div>
		</div>
		<div class="div_contents_cell_line">
			<div class="div_contents_cell_line" style="margin-top: 10px;"></div>
			<div class="div_contents_cell_category">
				<?=Audio_setup\Lang\STR_SERVER_PLAY_INFO ?>
			</div>
			<div class="div_contents_cell_contents" >
				<div id="div_client_run_pcm">
					<div class="div_radio_double_wrap">
						<b><?=Audio_setup\Lang\STR_SERVER_PCM_INFO ?></b>
					</div>

					<div class="table_box_wrap">
						<div class="div_contents_run_table_box">
							<select id="select_run_client_sample_rate" disabled>
								<option style="display:none;" selected>-</option>
								<option value="16000"> 16,000 Hz </option>
								<option value="32000"> 32,000 Hz </option>
								<option value="44100"> 44,100 Hz </option>
								<option value="48000"> 48,000 Hz </option>
							</select>
						</div>

						<div class="div_contents_run_table_box">
							<select id="select_run_client_channels" disabled>
								<option style="display:none;" selected>-</option>
								<option value="1"> ( 1 ch ) Mono </option>
								<option value="2"> ( 2 ch ) Stereo </option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="div_contents_cell_contents" >
				<div id="div_client_run_encode" style="display: none;">
					<div class="div_radio_double_wrap">
						<b><?=Audio_setup\Lang\STR_SERVER_MP3_INFO ?></b>
					</div>
					<div class="table_box_wrap">
						<div class="div_contents_run_table_box">
							<select id="select_run_client_mp3_sampleRate" disabled>
								<option style="display:none;" selected>-</option>
								<option value="44100"> 44,100 Hz </option>
								<option value="48000"> 48,000 Hz </option>
							</select>
						</div>

						<div class="div_contents_run_table_box">
							<select id="select_run_client_mp3_channels" disabled>
								<option style="display:none;" selected>-</option>
								<option value="1"> ( 1 ch ) Mono </option>
								<option value="2"> ( 2 ch ) Stereo </option>
							</select>
						</div>

						<div class="div_contents_run_table_box">
							<select id="select_run_client_mp3_quality" disabled>
								<option style="display:none;" selected>-</option>
								<option value="2"> <?=Audio_setup\Lang\STR_SERVER_MP3_HIGH ?> </option>
								<option value="5"> <?=Audio_setup\Lang\STR_SERVER_MP3_MEDIUM ?> </option>
								<option value="7"> <?=Audio_setup\Lang\STR_SERVER_MP3_LOW ?> </option>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="div_contents_run_cell_contents">
			<div class="div_radio_double_wrap">
				<?=Audio_setup\Lang\STR_CLIENT_INFO_BUFFER ?>
			</div>

			<div class="table_box_wrap">
				<div class="div_contents_run_table_box">
					<select id="select_run_client_buffer_sec" disabled>
						<option style="display:none;" selected>-</option>
						<option value="0"> 0 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="1"> 1 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="2"> 2 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="3"> 3 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="4"> 4 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="5"> 5 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="6"> 6 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="7"> 7 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="8"> 8 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="9"> 9 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="10"> 10 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
					</select>
				</div>
				<div class="div_contents_run_table_box">
					<select id="select_run_client_buffer_msec" disabled>
						<option style="display:none;" selected>-</option>
						<option value="0"> 0 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="100"> 100 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="200"> 200 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="300"> 300 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="400"> 400 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="500"> 500 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="600"> 600 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="700"> 700 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="800"> 800 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="900"> 900 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
					</select>
				</div>
			</div>
		</div>
		<div class="div_contents_cell_line"></div>
	</div>
<!--서버 정보-->
	<div class="div_contents_cell">
		<div class="div_contents_cell_line" id="div_server_info_master">
			<div class="div_contents_cell_category">
				<?=Audio_setup\Lang\STR_CLIENT_INFO_SERVER ?>
			</div>
			<div class="div_contents_cell_contents" >
				<div class="div_radio_double_wrap">
					<?=Audio_setup\Lang\STR_CLIENT_INFO_SERVER_MASTER ?>
				</div>
				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						<div class="textbox">
							<label for="input_client_run_master_ipAddr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
							<input type="text" class="div_server_info_deact" id="input_client_run_master_ipAddr" value="-" readonly />
						</div>
					</div>
					<div class="div_contents_run_table_box" >
						<div class="textbox">
							<label for="input_client_run_master_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
							<input type="text" class="div_server_info_deact" id="input_client_run_master_port" value="-" readonly  />
						</div>
					</div>
				</div>
				<div class="div_contents_cell_line"></div>
			</div>
		</div>
		<div class="div_contents_cell_line" id="div_server_info_slave">
			<div class="div_contents_cell_contents" >
				<div class="div_radio_double_wrap">
					<?=Audio_setup\Lang\STR_CLIENT_INFO_SERVER_SLAVE ?>
				</div>
				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						<div class="textbox">
							<label for="input_client_run_slave_ipAddr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
							<input type="text" class="div_server_info_deact" id="input_client_run_slave_ipAddr" value="-" readonly />
						</div>
					</div>
					<div class="div_contents_run_table_box">
						<div class="textbox">
							<label for="input_client_run_slave_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
							<input type="text" class="div_server_info_deact" id="input_client_run_slave_port" value="-" readonly />
						</div>
					</div>
				</div>
			<div class="div_contents_cell_line"></div>
			</div>
		</div>

		<div class="div_contents_cell_line" id="div_server_info_multicast" style="display : none;">
			<div class="div_contents_cell_contents" >
				<div class="div_radio_double_wrap">
					<?=Audio_setup\Lang\STR_CLIENT_INFO_SERVER_MASTER ?>
				</div>
				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						<div class="textbox">
							<label for="input_client_run_slave_ipAddr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
							<input type="text" class="div_server_info_deact" id="input_client_run_multicast_ipAddr" value="-" readonly />
						</div>
					</div>
					<div class="div_contents_run_table_box">
						<div class="textbox">
							<label for="input_client_run_slave_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
							<input type="text" class="div_server_info_deact" id="input_client_run_multicast_port" value="-" readonly />
						</div>
					</div>
				</div>
			<div class="div_contents_cell_line"></div>
			</div>
		</div>
	</div>
<!--동작 정보-->
	<div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_cell_category">
					<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?>
			</div>
			<div class="div_contents_cell_contents" >
				<div class="div_radio_double_wrap">
					<?=Audio_setup\Lang\STR_CLIENT_INFO_LEVEL_METER ?>
				</div>
				<div class="div_contents_run_table_box">
					<div class="outputVolume_1" style="clear:both;"></div>
					<div class="level_outputVolume_1" style="display:none;">0</div>
				</div>
			</div>

			<div class="div_contents_cell_line" style="<?php echo $status_style_amp_device; ?>">
				<div class="div_contents_cell_contents" style="display : flex;">
					<div class="div_radio_double_wrap">
						<?=Audio_setup\Lang\STR_CLIENT_INFO_VOLUME ?>
					</div>
					<div class="div_contents_run_table_box" >
						<input class="slider" id="slider_volume" type="range" min="0" max="100" value="0"   />
				    	<input type="text" id="slider_value" maxlength=3>
						<div id="div_button_apply_volume" class="div_class_button_volume">
						</div>
					</div>
				</div>
			</div>
			<div class="div_contents_cell_line"></div>
		</div>
	</div>
</div>