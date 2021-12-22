<div class="div_contents_table" id="div_display_server_setup">
	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_SERVER_SETUP_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_PROTOCOL ?>
			</div>
			<div class="div_contents_cell_contents">
				<div class="div_contents_radio_wrap">
					<input type="radio" name="radio_protocolType" id="radio_protocol_tcp" class="input_class_radio" checked />
					<label class="label_class_radio" for="radio_protocol_tcp">TCP/IP</label>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_CAST_TYPE ?>
			</div>
			<div class="div_contents_cell_contents">
				<div class="div_contents_radio_wrap">
					<input type="checkbox" name="checkbox_cast_type" id="checkbox_cast_unicast" value="unicast" class="input_class_radio" />
					<label class="label_class_radio" for="checkbox_cast_unicast">Unicast</label>
				</div>

				<div class="div_contents_radio_wrap" <?=$server_handler->set_enable_multicast() ?>>
					<input type="checkbox" name="checkbox_cast_type" id="checkbox_cast_multicast" value="multicast" class="input_class_radio" />
					<label class="label_class_radio" for="checkbox_cast_multicast">Multicast</label>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_ENCODE ?>
			</div>
			<div class="div_contents_cell_contents">
				<div class="div_contents_radio_wrap">
					<input type="radio" name="radio_encodeType" id="radio_encode_pcm" value="pcm" class="input_class_radio" />
					<label class="label_class_radio" for="radio_encode_pcm">PCM</label>
				</div>

				<div class="div_contents_radio_wrap">
					<input type="radio" name="radio_encodeType" id="radio_encode_mp3" value="mp3" class="input_class_radio" />
					<label class="label_class_radio" for="radio_encode_mp3">MP3</label>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_PLAY_INFO ?>
			</div>

			<div class="div_contents_cell_contents">
				<div class="div_contents_cell_row_wrap" id="div_server_pcm">
					<div class="div_contents_table_sub_title">
						<b><?=Audio_setup\Lang\STR_SERVER_PCM_SETUP ?></b>
					</div>

					<div class="div_contents_cell_column_wrap">
						<div class="div_contents_table_text_wrap">
							<div class="div_contents_table_text">
								<?=Audio_setup\Lang\STR_SERVER_SAMPLE_RATE ?>
							</div>

							<div class="div_contents_table_text">
								<?=Audio_setup\Lang\STR_SERVER_CHANNEL ?>
							</div>
						</div>

						<div class="div_contents_table_box_wrap">
							<div class="div_contents_table_box">
								<select id="select_sampleRate">
									<option value="44100"> 44,100 Hz </option>
									<option value="48000"> 48,000 Hz </option>
								</select>
							</div>

							<div class="div_contents_table_box">
								<select id="select_channels" <?=$server_handler->set_enable_channels() ?>>
									<option value="1"> ( 1 ch ) Mono </option>
									<option value="2"> ( 2 ch ) Stereo </option>
								</select>
							</div>
						</div>
					</div>
				</div>

				<!-- Encode type : MP3 -->
				<div class="div_contents_cell_row_wrap" id="div_server_mp3">
					<div class="div_contents_table_sub_title">
						<b><?=Audio_setup\Lang\STR_SERVER_MP3_SETUP ?></b>
					</div>
					<div class="div_contents_cell_column_wrap">
						<div class="div_contents_table_text_wrap">
							<div class="div_contents_table_text">
								<?=Audio_setup\Lang\STR_SERVER_SAMPLE_RATE ?>
							</div>
							<div class="div_contents_table_text">
								<?=Audio_setup\Lang\STR_SERVER_CHANNEL ?>
							</div>
							<div class="div_contents_table_text">
								<?=Audio_setup\Lang\STR_SERVER_MP3_QUALITY ?>
							</div>
						</div>

						<div class="div_contents_table_box_wrap">
							<div class="div_contents_table_box">
								<select id="select_mp3_sampleRate">
									<option value="44100"> 44,100 Hz </option>
									<option value="48000"> 48,000 Hz </option>
								</select>
							</div>

							<div class="div_contents_table_box">
								<select id="select_mp3_channels" <?=$server_handler->set_enable_channels() ?>>
									<option value="1"> ( 1 ch ) Mono </option>
									<option value="2"> ( 2 ch ) Stereo </option>
								</select>
							</div>
							<div class="div_contents_table_box">
								<select id="select_mp3_quality">
									<option value="2"> <?=Audio_setup\Lang\STR_SERVER_MP3_HIGH ?> </option>
									<option value="5"> <?=Audio_setup\Lang\STR_SERVER_MP3_MEDIUM ?> </option>
									<option value="7"> <?=Audio_setup\Lang\STR_SERVER_MP3_LOW ?> </option>
								</select>
							</div>

						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?>
			</div>
			<div class="div_contents_cell_contents" style="padding: 0px 0px 10px 0px;">
				<div class="div_contents_cell_row_wrap">
					<div class="div_contents_cell_row_wrap">
						<div class="div_contents_table_sub_title">
							<b><?=Audio_setup\Lang\STR_SERVER_OPER_SETUP ?></b>
						</div>

						<div class="div_contents_cell_column_wrap" id="div_mcast_ip_addr" style="display: none;">
							<div class="div_contents_table_text_wrap">
								<div class="div_contents_table_text">
									<?=Audio_setup\Lang\STR_COMMON_IP_M_ADDR ?>
								</div>
							</div>

							<div class="div_contents_table_box_wrap">
								<div class="div_contents_table_box">
									<div class="div_contents_textbox">
										<label for="input_server_ip_addr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
										<input type="text" id="input_server_ip_addr"/>
									</div>
								</div>
							</div>
						</div>

						<div class="div_contents_cell_column_wrap">
							<div class="div_contents_table_text_wrap">
								<div class="div_contents_table_text">
									<?=Audio_setup\Lang\STR_COMMON_PORT ?>
								</div>
							</div>

							<div class="div_contents_table_box_wrap">
								<div class="div_contents_table_box">
									<div class="div_contents_textbox">
										<label for="input_server_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
										<input type="text" id="input_server_port"/>
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
			<div id="div_button_server_cancel" class="div_class_button">
				<?=Audio_setup\Lang\STR_COMMON_CANCEL ?>
			</div>
			<div id="div_button_server_apply" class="div_class_button">
				<?=Audio_setup\Lang\STR_COMMON_APPLY ?>
			</div>
		</div>
	</div>
</div>



<!-- 동작 모드 화면 -->
<div class="div_contents_table" id="div_display_server_operation">
	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_SERVER_OP_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div id="div_server_operation_wait" class="div_class_operation_wait" >
			<span></span>
		</div>
		<div id="div_server_operation_run" class="div_class_operation_run" style="display: none;">
			<span><?=Audio_setup\Lang\STR_SERVER_OP_RUN ?></span>
		</div>

		<div id="div_server_operation_stop" class="div_class_operation_stop" style="display: none;">
			<span><?=Audio_setup\Lang\STR_SERVER_OP_STOP ?></span>
		</div>

		<div class="div_contents_cell_line" style="margin-top: 10px; border-top: 1px dashed #cccccc;"></div>
		<div class="div_class_button_wrap">
			<div id="div_button_server_setup" class="div_class_button">
				<?=Audio_setup\Lang\STR_COMMON_SETUP ?>
			</div>
			<div id="div_button_server_stop" class="div_class_button">
				<?=Audio_setup\Lang\STR_COMMON_STOP ?>
			</div>
			<div id="div_button_server_start" class="div_class_button">
				<?=Audio_setup\Lang\STR_COMMON_START ?>
			</div>
		</div>
		<div class="div_contents_cell_line" style="margin-top: 10px;"></div>
	</div>

	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_SERVER_OP_SETUP_INFO ?>
	</div>
	<div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_run_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_PROTOCOL_INFO ?>
			</div>

			<div class="div_contents_cell_row_wrap">
				<div class="div_contents_cell_column_wrap">
					<div class="div_radio_double_wrap_title">
						|&nbsp;<?=Audio_setup\Lang\STR_SERVER_PROTOCOL ?>
					</div>
					<div class="div_radio_double_wrap_title">
						|&nbsp;<?=Audio_setup\Lang\STR_SERVER_CAST_TYPE ?>
					</div>
					<div class="div_radio_double_wrap_title">
						|&nbsp;<?=Audio_setup\Lang\STR_SERVER_ENCODE ?>
					</div>
				</div>

				<div class="div_contents_cell_column_wrap">
					<div class="div_contents_run_cell_contents">
						<input type="radio" id="radio_run_protocol" class="input_class_radio" checked />
						<label class="label_class_radio" id="radio_run_protocol_label" for="radio_run_protocol" style="cursor:default;">-</label>
					</div>
					<div class="div_contents_run_cell_contents">
						<input type="radio" id="radio_run_castType" class="input_class_radio" checked />
						<label class="label_class_radio" id="radio_run_castType_label" for="radio_run_castType" style="cursor:default;">-</label>
					</div>
					<div class="div_contents_run_cell_contents">
						<input type="radio" id="radio_run_encode" class="input_class_radio" checked />
						<label class="label_class_radio" id="radio_run_encode_label" for="radio_run_encode" style="cursor:default;">-</label>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_run_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_PLAY_INFO ?>
			</div>

			<div class="div_contents_cell_contents" style="padding: 10px 0px 10px 0px;">
				<div class="div_server_run_pcm">
					<div class="div_contents_table_run_sub_title">
						<b><?=Audio_setup\Lang\STR_SERVER_PCM_INFO ?></b>
					</div>

					<div class="div_contents_run_table_box">
						<select id="select_run_sampleRate" disabled>
							<option style="display:none;" selected>-</option>
							<option value="44100"> 44,100 Hz </option>
							<option value="48000"> 48,000 Hz </option>
						</select>
					</div>

					<div class="div_contents_run_table_box" >
						<select id="select_run_channels" disabled>
							<option style="display:none;" selected>-</option>
							<option value="1"> ( 1 ch ) Mono </option>
							<option value="2"> ( 2 ch ) Stereo </option>
						</select>
					</div>
				</div>

				<div class="div_server_run_mp3" style="display: none;">
					<div class="div_contents_table_run_sub_title">
						<b><?=Audio_setup\Lang\STR_SERVER_MP3_INFO ?></b>
					</div>

					<div class="div_contents_run_table_box">
						<select id="select_run_mp3_sampleRate" disabled>
							<option style="display:none;" selected>-</option>
							<option value="44100"> 44,100 Hz </option>
							<option value="48000"> 48,000 Hz </option>
						</select>
					</div>

					<div class="div_contents_run_table_box" >
						<select id="select_run_mp3_channels" disabled>
							<option style="display:none;" selected>-</option>
							<option value="1"> ( 1 ch ) Mono </option>
							<option value="2"> ( 2 ch ) Stereo </option>
						</select>
					</div>

					<div class="div_contents_run_table_box">
						<select id="select_run_mp3_quality" disabled>
							<option style="display:none;" selected>-</option>
							<option value="2"> <?=Audio_setup\Lang\STR_SERVER_MP3_HIGH ?> </option>
							<option value="5"> <?=Audio_setup\Lang\STR_SERVER_MP3_MEDIUM ?> </option>
							<option value="7"> <?=Audio_setup\Lang\STR_SERVER_MP3_LOW ?> </option>
						</select>
					</div>

				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_run_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?>
			</div>
			<div class="div_contents_cell_contents" style="padding-bottom: 0px;">
				<div class="div_contents_cell_contents">
					<div id="div_operation_view_unicast">
						<div class="div_contents_table_run_sub_title" style="margin-top: 10px;">
							<b><?=Audio_setup\Lang\STR_SERVER_UNICAST ?>&nbsp;<?=Audio_setup\Lang\STR_COMMON_SERVER ?>&nbsp;<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?></b>
						</div>
						<div class="div_contents_run_table_box">
							<div class="div_contents_textbox">
								<label for="input_server_run_ipAddr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
								<input type="text" id="input_server_run_ipAddr1" value="-" disabled style="color: #808080; background: #ffffff;" />
							</div>
						</div>
						<div class="div_contents_run_table_box">
							<div class="div_contents_textbox">
								<label for="input_server_run_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
								<input type="text" id="input_server_run_port1" value="-" disabled style="color: #808080; background: #ffffff;" />
							</div>
						</div>
					</div>

					<div id="div_operation_view_multicast" style="display: none;">
						<div class="div_contents_table_run_sub_title" style="margin-top: 10px;">
							<b><?=Audio_setup\Lang\STR_SERVER_MULTICAST ?>&nbsp;<?=Audio_setup\Lang\STR_COMMON_SERVER ?>&nbsp;<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?></b>
						</div>

						<div class="div_contents_run_table_box">
							<div class="div_contents_textbox">
								<label for="input_server_run_ipAddr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
								<input type="text" id="input_server_run_ipAddr2" value="-" disabled style="color: #808080; background: #ffffff;" />
							</div>
						</div>

						<div class="div_contents_run_table_box">
							<div class="div_contents_textbox">
								<label for="input_server_run_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
								<input type="text" id="input_server_run_port2" value="-" disabled style="color: #808080; background: #ffffff;" />
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line"></div>
	</div>

	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?>
	</div>
	<div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_run_cell_title">
				<?=Audio_setup\Lang\STR_CLIENT_INFO_LEVEL_METER ?>
			</div>
			<div class="div_contents_cell_contents">
				<div class="outputVolume_server" style="clear:both; padding-top: 10px; width: 615px;"></div>
				<div class="level_outputVolume_server" style="display:none;">0</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_run_cell_title">
				<?=Audio_setup\Lang\STR_CLIENT_INFO_VOLUME ?>
			</div>
			<div class="div_contents_cell_contents" style="width: 615px; padding-top: 10px;">
				<div class="div_contents_cell_column_wrap">
				    <input class="slider_server" id="slider_server_volume" type="range" min="0" max="100" value="0" />
				    <input type="text" id="slider_server_value" maxlength=3>
					<div id="div_button_apply_server_volume" class="div_class_button" style="width: 80px; height: 25px; line-height: 25px; margin: 5px 0px 0px 10px;">
						<?=Audio_setup\Lang\STR_COMMON_APPLY ?>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
		</div>
	</div>

	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_SERVER_LIST_TITLE ?>
	</div>
	<div class="div_contents_cell" style="margin-left: 10px;">
		<div class="div_no_support_for_multicast">
			<?=Audio_setup\Lang\STR_SERVER_LIST_NOTICE ?>
		</div>
		<div class="divTable">
			<div class="divTableBody">
				<div class="divTableRow">
					<div class="divTableCell" style="width: 70px;">
						<?=Audio_setup\Lang\STR_SERVER_LIST_NUM ?>
					</div>
					<div class="divTableCell" style="width: 160px;">
						<?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?>
					</div>
					<div class="divTableCell" style="width: 230px;">
						<?=Audio_setup\Lang\STR_SERVER_LIST_HOSTNAME ?>
					</div>
					<div class="divTableCell" style="width: 100px;">
						<?=Audio_setup\Lang\STR_SERVER_LIST_STATUS ?>
					</div>
					<div class="divTableCell" style="width: 180px;">
						<?=Audio_setup\Lang\STR_SERVER_LIST_CONN_TIME ?>
					</div>
				</div>
			</div>
			<div class="divTableBody" id="table_server_connList">
			</div>
		</div>
	</div>
</div>
