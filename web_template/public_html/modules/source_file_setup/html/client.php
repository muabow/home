<?php
	$json_env_info = json_decode(file_get_contents("/opt/interm/conf/env.json"));
    $is_device_amp = ($json_env_info->device->device_type == "amp" ? true : false);

    $status_style_amp_device = "";
    if( $is_device_amp ) {
        $status_style_amp_device = "display: none;";
	}
?>
<div class="div_contents_table" id="div_display_client_setup">
	<div class="div_contents_title">
		<?=Source_file_setup\Lang\STR_CLIENT_SETUP_TITLE ?>
	</div>

	<div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Source_file_setup\Lang\STR_SERVER_PROTOCOL ?>
			</div>
			<div class="div_contents_cell_contents">
				<div class="div_contents_radio_wrap">
					<input class="input_class_radio" type="radio" name="radio_client_protocol_type" id="radio_client_protocol_tcp" checked />
					<label class="label_class_radio" for="radio_client_protocol_tcp">TCP/IP</label>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Source_file_setup\Lang\STR_SERVER_CAST_TYPE ?>
			</div>
			<div class="div_contents_cell_contents">
				<div class="div_contents_radio_wrap">
					<input class="input_class_radio" type="radio" name="radio_client_cast_type" id="radio_client_cast_unicast" />
					<label class="label_class_radio" for="radio_client_cast_unicast">Unicast</label>
				</div>

				<div class="div_contents_radio_wrap">
					<input class="input_class_radio" type="radio" name="radio_client_cast_type" id="radio_client_cast_multicast"/>
					<label class="label_class_radio" for="radio_client_cast_multicast">Multicast</label>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Source_file_setup\Lang\STR_CLIENT_BUFFER ?>
			</div>

			<div class="div_contents_cell_contents">
				<div class="div_contents_cell_row_wrap">
					<div class="div_contents_table_sub_title">
						<b><?=Source_file_setup\Lang\STR_CLIENT_BUFFER_SETUP ?></b>
					</div>

					<div class="div_contents_cell_column_wrap">
						<div class="div_contents_table_text_wrap">
							<div class="div_contents_table_text">
								<?=Source_file_setup\Lang\STR_COMMON_SEC ?>
							</div>
							<div class="div_contents_table_text" >
								<?=Source_file_setup\Lang\STR_COMMON_MSEC ?>
							</div>
						</div>

						<div class="div_contents_table_box_wrap">
							<div class="div_contents_table_box">
								<select id="select_client_buffer_sec">
									<option value="0"> 0 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
									<option value="1"> 1 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
									<option value="2"> 2 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
									<option value="3"> 3 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
									<option value="4"> 4 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
									<option value="5"> 5 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
									<option value="6"> 6 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
									<option value="7"> 7 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
									<option value="8"> 8 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
									<option value="9"> 9 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
									<option value="10"> 10 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
								</select>
							</div>

							<div class="div_contents_table_box">
								<select id="select_client_buffer_msec">
									<option value="0"> 0 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
									<option value="100"> 100 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
									<option value="200"> 200 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
									<option value="300"> 300 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
									<option value="400"> 400 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
									<option value="500"> 500 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
									<option value="600"> 600 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
									<option value="700"> 700 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
									<option value="800"> 800 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
									<option value="900"> 900 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line" id="div_redundancy_status">
			<div class="div_contents_cell_title">
				<?=Source_file_setup\Lang\STR_CLIENT_REDUNDANCY ?>
			</div>
			<div class="div_contents_cell_contents">
				<div class="div_contents_radio_wrap">
					<input class="input_class_radio" type="radio" name="radio_client_redundancy" id="radio_client_redundancy_master" />
					<label class="label_class_radio" for="radio_client_redundancy_master"><?=Source_file_setup\Lang\STR_CLIENT_REDUNDANCY_MASTER ?></label>
				</div>

				<div class="div_contents_radio_wrap">
					<input class="input_class_radio" type="radio" name="radio_client_redundancy" id="radio_client_redundancy_slave" />
					<label class="label_class_radio" for="radio_client_redundancy_slave"><?=Source_file_setup\Lang\STR_CLIENT_REDUNDANCY_SLAVE ?></label>
				</div>

			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Source_file_setup\Lang\STR_SERVER_OPER_INFO ?>
			</div>
			<div class="div_contents_cell_row_wrap">
				<div class="div_contents_cell_contents" id="div_client_multicast">
					<div class="div_contents_cell_row_wrap">
						<div class="div_contents_table_sub_title">
							<b><?=Source_file_setup\Lang\STR_SERVER_OPER_SETUP ?></b>
						</div>

						<div class="div_contents_cell_column_wrap">
							<div class="div_contents_table_text_wrap">
								<div class="div_contents_table_text">
									<?=Source_file_setup\Lang\STR_COMMON_IP_ADDR ?>
								</div>
								<div class="div_contents_table_text">
									<?=Source_file_setup\Lang\STR_COMMON_PORT ?>
								</div>
							</div>

							<div class="div_contents_table_box_wrap">
								<div class="div_contents_table_box">
									<div class="div_contents_textbox">
										<input type="text" id="input_client_ip_addr_multicast" />
										<label for="input_client_ip_addr_multicast"><?=Source_file_setup\Lang\STR_COMMON_IP_ADDR ?></label>
									</div>
								</div>

								<div class="div_contents_table_box">
									<div class="div_contents_textbox">
										<input type="text" id="input_client_port_multicast" />
										<label for="input_client_port_multicast"><?=Source_file_setup\Lang\STR_COMMON_PORT ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="div_contents_cell_contents" id="div_client_redundancy_master">
					<div class="div_contents_cell_row_wrap">
						<div class="div_contents_table_sub_title">
							<b><?=Source_file_setup\Lang\STR_SERVER_OPER_SETUP ?></b>
						</div>

						<div class="div_contents_cell_column_wrap">
							<div class="div_contents_table_text_wrap">
								<div class="div_contents_table_text">
									<?=Source_file_setup\Lang\STR_COMMON_IP_ADDR ?>
								</div>
								<div class="div_contents_table_text">
									<?=Source_file_setup\Lang\STR_COMMON_PORT ?>
								</div>
							</div>

							<div class="div_contents_table_box_wrap">
								<div class="div_contents_table_box">
									<div class="div_contents_textbox">
										<input type="text" id="input_client_ip_addr_master" />
										<label for="input_client_ip_addr_master"><?=Source_file_setup\Lang\STR_COMMON_IP_ADDR ?></label>
									</div>
								</div>

								<div class="div_contents_table_box">
									<div class="div_contents_textbox">
										<input type="text" id="input_client_port_master" />
										<label for="input_client_port_master"><?=Source_file_setup\Lang\STR_COMMON_PORT ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="div_contents_cell_contents" id="div_client_redundancy_slave">
					<div class="div_contents_cell_row_wrap">
						<div class="div_contents_table_sub_title">
							<b><?=Source_file_setup\Lang\STR_CLIENT_REDUNDANCY_SETUP ?></b>
						</div>

						<div class="div_contents_cell_column_wrap">
							<div class="div_contents_table_text_wrap">
								<div class="div_contents_table_text">
									<?=Source_file_setup\Lang\STR_COMMON_IP_ADDR ?>
								</div>

								<div class="div_contents_table_text">
									<?=Source_file_setup\Lang\STR_COMMON_PORT ?>
								</div>
							</div>

							<div class="div_contents_table_box_wrap">
								<div class="div_contents_table_box">
									<div class="div_contents_textbox">
										<label for="input_client_ip_addr_slave"><?=Source_file_setup\Lang\STR_COMMON_IP_ADDR ?></label>
										<input type="text" id="input_client_ip_addr_slave" />
									</div>
								</div>

								<div class="div_contents_table_box">
									<div class="div_contents_textbox">
										<label for="input_client_port_slave"><?=Source_file_setup\Lang\STR_COMMON_PORT ?></label>
										<input type="text" id="input_client_port_slave" />
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line"></div>

		<div class="div_class_button_wrap">
			<div id="div_button_client_cancel" class="div_class_button">
				<?=Source_file_setup\Lang\STR_COMMON_CANCEL ?>
			</div>
			<div id="div_button_client_apply" class="div_class_button">
				<?=Source_file_setup\Lang\STR_COMMON_APPLY ?>
			</div>
		</div>
	</div>
</div>


<!-- 동작 모드 화면 -->
<div class="div_contents_table" id="div_display_client_operation">
	<div class="div_contents_title">
		<?=Source_file_setup\Lang\STR_CLIENT_OP_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div id="div_client_operation_wait" class="div_class_operation_wait">
			<span></span>
		</div>
		<div id="div_client_operation_run" class="div_class_operation_run" style="display: none;" >
			<span id="span_client_operation_status"><?=Source_file_setup\Lang\STR_CLIENT_OP_RUN ?></span>
		</div>

		<div id="div_client_operation_stop" class="div_class_operation_stop" style="display: none;" >
			<span id="span_client_operation_status"><?=Source_file_setup\Lang\STR_CLIENT_OP_STOP ?></span>
		</div>

		<div class="div_contents_cell_line" style="margin-top: 10px; border-top: 1px dashed #cccccc;"></div>
		<div class="div_class_button_wrap">
			<div id="div_button_client_setup" class="div_class_button">
				<?=Source_file_setup\Lang\STR_COMMON_SETUP ?>
			</div>
			<div id="div_button_client_stop" class="div_class_button">
				<?=Source_file_setup\Lang\STR_COMMON_STOP ?>
			</div>
			<div id="div_button_client_start" class="div_class_button">
				<?=Source_file_setup\Lang\STR_COMMON_START ?>
			</div>
		</div>
		<div class="div_contents_cell_line" style="margin-top: 10px;"></div>

	</div>

	<div class="div_contents_title">
		<?=Source_file_setup\Lang\STR_CLIENT_INFO_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_run_cell_title">
				<?=Source_file_setup\Lang\STR_SERVER_PROTOCOL_INFO ?>
			</div>
			<div class="div_contents_cell_row_wrap">
				<div class="div_contents_cell_column_wrap">
					<div class="div_radio_double_wrap_title">
						|&nbsp;<?=Source_file_setup\Lang\STR_SERVER_PROTOCOL ?>
					</div>
					<div class="div_radio_double_wrap_title">
						|&nbsp;<?=Source_file_setup\Lang\STR_SERVER_CAST_TYPE ?>
					</div>
					<div class="div_radio_double_wrap_title">
						|&nbsp;<?=Source_file_setup\Lang\STR_SERVER_ENCODE ?>
					</div>
				</div>

				<div class="div_contents_cell_column_wrap">
					<div class="div_contents_run_cell_contents">
						<input type="radio" id="radio_run_client_protocol" class="input_class_radio" checked />
						<label class="label_class_radio" id="radio_run_client_protocol_label" for="radio_run_client_protocol" style="cursor:default;">-</label>
					</div>
					<div class="div_contents_run_cell_contents">
						<input type="radio" id="radio_run_cast_type" class="input_class_radio" checked />
						<label class="label_class_radio" id="radio_run_client_castType_label" for="radio_run_cast_type" style="cursor:default;">-</label>
					</div>
					<div class="div_contents_run_cell_contents">
						<input type="radio" id="radio_run_client_encode" class="input_class_radio" checked />
						<label class="label_class_radio" id="radio_run_client_encode_label" for="radio_run_client_encode" style="cursor:default;">-</label>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line" id="div_play_info" style="display: none;">
			<div class="div_contents_run_cell_title">
				<?=Source_file_setup\Lang\STR_SERVER_PLAY_INFO ?>
			</div>

			<div class="div_contents_cell_contents" style="padding: 10px 0px 10px 0px;">
				<div id="div_client_run_pcm">
					<div class="div_contents_table_run_sub_title">
						<b><?=Source_file_setup\Lang\STR_SERVER_PCM_INFO ?></b>
					</div>

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

				<div id="div_client_run_encode" style="display: none;">
					<div class="div_contents_table_run_sub_title">
						<b><?=Source_file_setup\Lang\STR_SERVER_MP3_INFO ?></b>
					</div>
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
							<option value="32"> 32 Kbps </option>
							<option value="96"> 96 Kbps </option>
							<option value="128"> 128 Kbps </option>
							<option value="160"> 160 Kbps </option>
							<option value="256"> 256 Kbps </option>
							<option value="320"> 320 Kbps </option>
						</select>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_run_cell_title">
				<?=Source_file_setup\Lang\STR_CLIENT_INFO_BUFFER ?>
			</div>

			<div class="div_contents_cell_contents" style="padding: 10px 0px 10px 0px;">
				<div class="div_contents_run_table_box">
					<select id="select_run_client_buffer_sec" disabled>
						<option style="display:none;" selected>-</option>
						<option value="0"> 0 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="1"> 1 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="2"> 2 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="3"> 3 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="4"> 4 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="5"> 5 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="6"> 6 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="7"> 7 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="8"> 8 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="9"> 9 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="10"> 10 <?=Source_file_setup\Lang\STR_COMMON_SEC ?> </option>
					</select>
				</div>

				<div class="div_contents_run_table_box">
					<select id="select_run_client_buffer_msec" disabled>
						<option style="display:none;" selected>-</option>
						<option value="0"> 0 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="100"> 100 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="200"> 200 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="300"> 300 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="400"> 400 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="500"> 500 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="600"> 600 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="700"> 700 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="800"> 800 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="900"> 900 <?=Source_file_setup\Lang\STR_COMMON_MSEC ?> </option>
					</select>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line"></div>
	</div>

<!--서버 정보-->
	<div class="div_contents_title">
		<?=Source_file_setup\Lang\STR_CLIENT_INFO_SERVER ?>
	</div>
	<div class="div_contents_cell">

		<div class="div_contents_cell_line" id="div_server_info_master">
			<div class="div_contents_run_cell_title">
				<?=Source_file_setup\Lang\STR_CLIENT_INFO_SERVER_MASTER ?>
			</div>
			<div class="div_contents_cell_contents" style="padding: 10px 0px 10px 0px;">
				<div class="div_contents_run_table_box">
					<div class="div_contents_textbox">
						<label for="input_client_run_master_ipAddr"><?=Source_file_setup\Lang\STR_COMMON_IP_ADDR ?></label>
						<input type="text" class="div_server_info_deact" id="input_client_run_master_ipAddr" value="-" disabled />
					</div>
				</div>
				<div class="div_contents_run_table_box">
					<div class="div_contents_textbox">
						<label for="input_client_run_master_port"><?=Source_file_setup\Lang\STR_COMMON_PORT ?></label>
						<input type="text" class="div_server_info_deact" id="input_client_run_master_port" value="-" disabled />
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line" id="div_server_info_slave">
			<div class="div_contents_run_cell_title">
				<?=Source_file_setup\Lang\STR_CLIENT_INFO_SERVER_SLAVE ?>
			</div>
			<div class="div_contents_cell_contents" style="padding: 10px 0px 10px 0px;">
				<div class="div_contents_run_table_box">
					<div class="div_contents_textbox">
						<label for="input_client_run_slave_ipAddr"><?=Source_file_setup\Lang\STR_COMMON_IP_ADDR ?></label>
						<input type="text" class="div_server_info_deact" id="input_client_run_slave_ipAddr" value="-" disabled />
					</div>
				</div>
				<div class="div_contents_run_table_box">
					<div class="div_contents_textbox">
						<label for="input_client_run_slave_port"><?=Source_file_setup\Lang\STR_COMMON_PORT ?></label>
						<input type="text" class="div_server_info_deact" id="input_client_run_slave_port" value="-" disabled />
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line" id="div_server_info_multicast">
			<div class="div_contents_run_cell_title">
				<?=Source_file_setup\Lang\STR_CLIENT_INFO_SERVER_MASTER ?>
			</div>
			<div class="div_contents_cell_contents" style="padding: 10px 0px 10px 0px;">
				<div class="div_contents_run_table_box">
					<div class="div_contents_textbox">
						<label for="input_client_run_slave_ipAddr"><?=Source_file_setup\Lang\STR_COMMON_IP_ADDR ?></label>
						<input type="text" class="div_server_info_deact" id="input_client_run_multicast_ipAddr" value="-" disabled />
					</div>
				</div>
				<div class="div_contents_run_table_box">
					<div class="div_contents_textbox">
						<label for="input_client_run_slave_port"><?=Source_file_setup\Lang\STR_COMMON_PORT ?></label>
						<input type="text" class="div_server_info_deact" id="input_client_run_multicast_port" value="-" disabled />
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
		</div>

	</div>


<!--동작 정보-->
	<div class="div_contents_title">
		<?=Source_file_setup\Lang\STR_SERVER_OPER_INFO ?>
	</div>
	<div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_run_cell_title">
				<?=Source_file_setup\Lang\STR_CLIENT_INFO_LEVEL_METER ?>
			</div>
			<div class="div_contents_cell_contents">
				<div class="outputVolume_1" style="clear:both; padding-top: 10px; width: 615px;"></div>
				<div class="level_outputVolume_1" style="display:none;">0</div>
			</div>
		</div>

		<div class="div_contents_cell_line" style="<?php echo $status_style_amp_device; ?>">
			<div class="div_contents_run_cell_title">
				<?=Source_file_setup\Lang\STR_CLIENT_INFO_VOLUME ?>
			</div>
			<div class="div_contents_cell_contents" style="width: 615px; padding-top: 10px;">
				<div class="div_contents_cell_column_wrap">
				    <input class="slider" id="slider_volume" type="range" min="0" max="100" value="0" />
				    <input type="text" id="slider_value" maxlength=3>
					<div id="div_button_apply_volume" class="div_class_button" style="width: 80px; height: 25px; line-height: 25px; margin: 5px 0px 0px 10px;">
						<?=Source_file_setup\Lang\STR_COMMON_APPLY ?>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
		</div>
	</div>
</div>
