<!-- 동작 모드 화면 -->
<div class="div_contents_table">
	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_SERVER_OP_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div id="div_server_operation_run" class="div_class_operation_run" style="display: none;">
			<span><?=Audio_setup\Lang\STR_SERVER_OP_RUN ?></span>
		</div>

		<div id="div_server_operation_stop" class="div_class_operation_stop" style="display: none;">
			<span><?=Audio_setup\Lang\STR_SERVER_OP_STOP ?></span>
		</div>

		<div id="div_server_operation_wait" class="div_class_operation_wait" style="display: none;">
			<span><?=Audio_setup\Lang\STR_SERVER_OP_STOP ?></span>
		</div>

		<div class="div_button_wrap">
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
			<div class="div_contents_cell_category">
				<?=Audio_setup\Lang\STR_SERVER_PROTOCOL_INFO ?>
			</div>
			<div class="div_contents_run_cell_contents">
				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						<label id="radio_run_protocol_label" for="radio_run_protocol" style="cursor:default;">-</label>
					</div>
					<div class="div_contents_run_table_box">
						<label id="radio_run_castType_label" for="radio_run_castType" style="cursor:default;">-</label>
					</div>
					<div class="div_contents_run_table_box">
						<label id="radio_run_encode_label" for="radio_run_encode" style="cursor:default;">-</label>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_category" style="margin-top:10px;">
				<?=Audio_setup\Lang\STR_SERVER_PLAY_INFO ?>
			</div>

		<div class="div_contents_cell_contents">
				<div class="div_server_run_pcm">
					<div class="div_radio_double_wrap">
						<b><?=Audio_setup\Lang\STR_SERVER_PCM_INFO ?></b>
					</div>
					<div class="table_box_wrap">
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
				</div>
			</div>
			<div class="div_contents_cell_contents">
				<div class="div_server_run_mp3">
					<div class="div_radio_double_wrap" >
						<b><?=Audio_setup\Lang\STR_SERVER_MP3_INFO ?></b>
					</div>

					<div class="table_box_wrap">
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
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_category" style="margin-top:10px;">
				<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?>
			</div>
			<div class="div_contents_cell_contents" id="div_operation_view_unicast">
				<div class="div_contents_table_run_sub_title">
					<b><?=Audio_setup\Lang\STR_SERVER_UNICAST ?>&nbsp;<?=Audio_setup\Lang\STR_COMMON_SERVER ?>&nbsp;<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?></b>
				</div>

				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						<input type="text" id="input_server_run_ipAddr1" value="-" disabled style="color: #808080; background: #ffffff;" />
					</div>
					<div class="div_contents_run_table_box">
						<input type="text" id="input_server_run_port1" value="-" disabled style="color: #808080; background: #ffffff;" />
					</div>
				</div>
			</div>

			<div class="div_contents_cell_contents" id="div_operation_view_multicast"  style="display: none;">
				<div class="div_contents_table_run_sub_title">
					<b><?=Audio_setup\Lang\STR_SERVER_MULTICAST ?>&nbsp;<?=Audio_setup\Lang\STR_COMMON_SERVER ?>&nbsp;<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?></b>
				</div>
				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						<input type="text" id="input_server_run_ipAddr2" value="-" disabled style="color: #808080; background: #ffffff;" />
					</div>

					<div class="div_contents_run_table_box">
						<input type="text" id="input_server_run_port2" value="-" disabled style="color: #808080; background: #ffffff;" />
					</div>
				</div>
			</div>
			<div class="div_contents_cell_contents" >
				<div class="div_radio_double_wrap">
					<?=Audio_setup\Lang\STR_CLIENT_INFO_LEVEL_METER ?>
				</div>
				<div class="div_contents_run_table_box">
					<div class="outputVolume_server" style="clear:both;"></div>
					<div class="level_outputVolume_server" style="display:none;">0</div>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_contents" style="display : flex;">
					<div class="div_radio_double_wrap">
						<?=Audio_setup\Lang\STR_CLIENT_INFO_VOLUME ?>
					</div>
					<div class="div_contents_run_table_box" >
						<input class="slider" id="slider_server_volume" type="range" min="0" max="100" value="0"   />
						<input type="text" id="slider_server_value" maxlength=3>
						<div id="div_button_apply_server_volume" class="div_class_button_volume">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="div_contents_cell_line"></div>
	</div>

	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_SERVER_LIST_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div class="divTable">
			<div class="divTableBody">
				<div class="divTableRow">
					<div class="divTableCell">
						<?=Audio_setup\Lang\STR_SERVER_LIST_NUM ?>
					</div>
					<div class="divTableCell">
						<?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?>
					</div>
					<div class="divTableCell">
						<?=Audio_setup\Lang\STR_SERVER_LIST_HOSTNAME ?>
					</div>
					<div class="divTableCell">
						<?=Audio_setup\Lang\STR_SERVER_LIST_STATUS ?>
					</div>
					<div class="divTableCell">
						<?=Audio_setup\Lang\STR_SERVER_LIST_CONN_TIME ?>
					</div>
				</div>
			</div>
			<div class="divTableBody" id="table_server_connList">
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
