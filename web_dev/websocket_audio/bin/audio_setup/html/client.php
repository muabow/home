<div class="div_contents_table" id="div_display_client_setup" <?=$clientFunc->getSetupStat("setup") ?>>
	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_CLIENT_SETUP_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_PROTOCOL ?>
			</div>
			<div class="div_contents_cell_contents">
				<div class="div_radio_wrap">
					<input type="radio" name="radio_client_protocolType" id="radio_client_protocol_tcp" class="radio" <?=$clientFunc->getEnableStat("protocol", "tcp") ?> />
					<label class="label_radio" for="radio_client_protocol_tcp">TCP/IP</label>
				</div>

				<div class="div_radio_wrap" style="display: none;">
					<input type="radio" name="radio_client_protocolType" id="radio_client_protocol_rtsp" class="radio" <?=$clientFunc->getEnableStat("protocol", "rtsp") ?> />
					<label class="label_radio" for="radio_client_protocol_rtsp">RTSP</label>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_CAST_TYPE ?>
			</div>
			<div class="div_contents_cell_contents">
				<div class="div_radio_wrap">
					<input type="radio" name="radio_client_castType" id="radio_client_cast_unicast" class="radio" <?=$clientFunc->getEnableStat("castType", "unicast") ?> />
					<label class="label_radio" for="radio_client_cast_unicast">Unicast</label>
				</div>

				<div class="div_radio_wrap">
					<input type="radio" name="radio_client_castType" id="radio_client_cast_multicast" class="radio" <?=$clientFunc->getEnableStat("castType", "multicast") ?> />
					<label class="label_radio" for="radio_client_cast_multicast">Multicast</label>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Audio_setup\Lang\STR_CLIENT_BUFFER ?>
			</div>

			<div class="div_contents_cell_contents" style="padding: 0px 0px 10px 0px;">
				<div class="div_contents_table_sub_title">
					<b><?=Audio_setup\Lang\STR_CLIENT_BUFFER_SETUP ?></b>
				</div>

				<div class="div_contents_table_text">
					<?=Audio_setup\Lang\STR_COMMON_SEC ?>
				</div>
				<div class="div_contents_table_box">
					<select id="select_client_buffer_sec">
						<option value="0"  <?=$clientFunc->getBufferStat("sec", 0) ?>> 0 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="1"  <?=$clientFunc->getBufferStat("sec", 1) ?>> 1 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="2"  <?=$clientFunc->getBufferStat("sec", 2) ?>> 2 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="3"  <?=$clientFunc->getBufferStat("sec", 3) ?>> 3 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="4"  <?=$clientFunc->getBufferStat("sec", 4) ?>> 4 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="5"  <?=$clientFunc->getBufferStat("sec", 5) ?>> 5 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="6"  <?=$clientFunc->getBufferStat("sec", 6) ?>> 6 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="7"  <?=$clientFunc->getBufferStat("sec", 7) ?>> 7 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="8"  <?=$clientFunc->getBufferStat("sec", 8) ?>> 8 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="9"  <?=$clientFunc->getBufferStat("sec", 9) ?>> 9 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="10" <?=$clientFunc->getBufferStat("sec", 10) ?>> 10 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
					</select>
				</div>

				<div class="div_contents_table_text" >
					<?=Audio_setup\Lang\STR_COMMON_MSEC ?>
				</div>
				<div class="div_contents_table_box">
					<select id="select_client_buffer_msec">
						<option value="0"  <?=$clientFunc->getBufferStat("msec", 0) ?>> 0 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="100"  <?=$clientFunc->getBufferStat("msec", 100) ?>> 100 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="200"  <?=$clientFunc->getBufferStat("msec", 200) ?>> 200 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="300"  <?=$clientFunc->getBufferStat("msec", 300) ?>> 300 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="400"  <?=$clientFunc->getBufferStat("msec", 400) ?>> 400 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="500"  <?=$clientFunc->getBufferStat("msec", 500) ?>> 500 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="600"  <?=$clientFunc->getBufferStat("msec", 600) ?>> 600 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="700"  <?=$clientFunc->getBufferStat("msec", 700) ?>> 700 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="800"  <?=$clientFunc->getBufferStat("msec", 800) ?>> 800 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="900"  <?=$clientFunc->getBufferStat("msec", 900) ?>> 900 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
					</select>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line" id="div_redundancyStat" <?=$clientFunc->getRedundancyStat("setup") ?>>
			<div class="div_contents_cell_title">
				<?=Audio_setup\Lang\STR_CLIENT_REDUNDANCY ?>
			</div>
			<div class="div_contents_cell_contents">
				<div class="div_radio_wrap">
					<input type="radio" name="radio_client_redundancy" id="radio_client_redundancy_master" class="radio" <?=$clientFunc->getEnableStat("redundancy", "master") ?> />
					<label class="label_radio" for="radio_client_redundancy_master"><?=Audio_setup\Lang\STR_CLIENT_REDUNDANCY_MASTER ?></label>
				</div>

				<div class="div_radio_wrap">
					<input type="radio" name="radio_client_redundancy" id="radio_client_redundancy_slave" class="radio" <?=$clientFunc->getEnableStat("redundancy", "slave") ?> />
					<label class="label_radio" for="radio_client_redundancy_slave"><?=Audio_setup\Lang\STR_CLIENT_REDUNDANCY_SLAVE ?></label>
				</div>

			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?>
			</div>
			<div class="div_contents_cell_contents" style="padding: 0px 0px 10px 0px;">
				<div class="div_radio_wrap">
					<input type="radio" name="radio_client_operType" id="radio_client_oper_default" class="radio" <?=$clientFunc->getEnableStat("operType", "default") ?> />
					<label class="label_radio" for="radio_client_oper_default"><?=Audio_setup\Lang\STR_SERVER_OPER_DEFAULT ?></label>
				</div>

				<div class="div_radio_wrap">
					<input type="radio" name="radio_client_operType" id="radio_client_oper_change" class="radio" <?=$clientFunc->getEnableStat("operType", "change") ?> />
					<label class="label_radio" for="radio_client_oper_change"><?=Audio_setup\Lang\STR_SERVER_OPER_CHANGE ?></label>
				</div>

				<div class="div_contents_cell_contents">
					<div class="div_contents_table_sub_title">
						<b><?=Audio_setup\Lang\STR_SERVER_OPER_SETUP ?></b>
					</div>

					<div>
						<div class="div_contents_table_text">
							<?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?>
						</div>
						<div class="div_contents_table_box">
							<div class="textbox">
								<label for="input_client_ipAddr_master"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
								<input type="text" id="input_client_ipAddr_master" value="<?=$clientFunc->getOperValue("master", "ipAddr") ?>" <?=$clientFunc->getOperStat() ?> />
							</div>
						</div>
					</div>
					<div class="div_contents_table_text">
						<?=Audio_setup\Lang\STR_COMMON_PORT ?>
					</div>
					<div class="div_contents_table_box">
						<div class="textbox">
							<label for="input_client_port_master"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
							<input type="text" id="input_client_port_master" value="<?=$clientFunc->getOperValue("master", "port") ?>" <?=$clientFunc->getOperStat() ?> />
						</div>
					</div>
				</div>

				<div class="div_contents_cell_contents" id="div_client_redundancy" <?=$clientFunc->getRedundancyStat("operation") ?>>
					<div class="div_contents_table_sub_title">
						<b><?=Audio_setup\Lang\STR_CLIENT_REDUNDANCY_SETUP ?></b>
					</div>

						<div class="div_contents_table_text">
							<?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?>
						</div>
						<div class="div_contents_table_box">
							<div class="textbox">
								<label for="input_client_ipAddr_slave"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
								<input type="text" id="input_client_ipAddr_slave" value="<?=$clientFunc->getOperValue("slave", "ipAddr") ?>" <?=$clientFunc->getOperStat() ?> />
							</div>
						</div>
						<div class="div_contents_table_text">
							<?=Audio_setup\Lang\STR_COMMON_PORT ?>
						</div>
						<div class="div_contents_table_box">
							<div class="textbox">
								<label for="input_client_port_slave"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
								<input type="text" id="input_client_port_slave" value="<?=$clientFunc->getOperValue("slave", "port") ?>" <?=$clientFunc->getOperStat() ?> />
							</div>
						</div>
				</div>

			</div>
		</div>

		<div class="div_contents_cell_line"></div>

		<div class="div_button_wrap">
			<div id="div_button_client_cancel" class="div_log_button">
				<?=Audio_setup\Lang\STR_COMMON_CANCEL ?>
			</div>
			<div id="div_button_client_apply" class="div_log_button">
				<?=Audio_setup\Lang\STR_COMMON_APPLY ?>
			</div>

			<div id="div_button_client_apply_hidden" class="div_log_button" style="display: none;">
				<?=Audio_setup\Lang\STR_COMMON_APPLY ?>
			</div>
		</div>
	</div>
</div>



<!-- 동작 모드 화면 -->
<div class="div_contents_table" id="div_display_client_operation" <?=$clientFunc->getSetupStat("operation") ?>>
	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_CLIENT_OP_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div id="div_client_operation_run" class="divActClientRunStat" style="display: none;"<?php // $clientFunc->getActStat("run") ?> >
			<span id="span_client_operation_status"><?=Audio_setup\Lang\STR_CLIENT_OP_RUN ?></span>
		</div>

		<div id="div_client_operation_stop" class="divActClientStopStat" <?php // $clientFunc->getActStat("stop") ?> >
			<span id="span_client_operation_status"><?=Audio_setup\Lang\STR_CLIENT_OP_STOP ?></span>
		</div>

		<div class="div_contents_cell_line" style="margin-top: 10px; border-top: 1px dashed #cccccc;"></div>
		<div class="div_button_wrap">
			<div id="div_button_client_setup" class="div_log_button">
				<?=Audio_setup\Lang\STR_COMMON_STOP ?>
				<?php // Audio_setup\Lang\STR_COMMON_SETUP ?>
			</div>
			<!--
			<div id="div_button_client_stop" class="div_log_button">
				<?=Audio_setup\Lang\STR_COMMON_STOP ?>
			</div>
			<div id="div_button_client_start" class="div_log_button">
				<?=Audio_setup\Lang\STR_COMMON_START ?>
			</div>
			-->
		</div>
		<div class="div_contents_cell_line" style="margin-top: 10px;"></div>

	</div>

	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_CLIENT_INFO_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_run_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_PROTOCOL_INFO ?>
			</div>
			<div class="div_contents_run_cell_contents">
				<div class="div_radio_double_wrap"
					style="background: #efefef; width: 210px; height: 25px; line-height: 25px; margin: 10px 0px 5px 0px; padding: 0px 0px 0px 0px; font-weight: bold;">
					|&nbsp;<?=Audio_setup\Lang\STR_SERVER_PROTOCOL ?>
				</div>
				<div class="div_radio_double_wrap">
					<input type="radio" id="radio_run_client_protocol" class="radio" checked />
					<label class="label_radio" id="radio_run_client_protocol_label" for="radio_run_client_protocol" style="cursor:default;"><?=$clientFunc->getProtocolValue() ?></label>
				</div>
			</div>
			<div class="div_contents_run_cell_contents">
				<div class="div_radio_double_wrap"
					style="background: #efefef; width: 210px; height: 25px; line-height: 25px; margin: 10px 0px 5px 0px; padding: 0px 0px 0px 0px; font-weight: bold;">
					|&nbsp;<?=Audio_setup\Lang\STR_SERVER_CAST_TYPE ?>
				</div>
				<div class="div_radio_double_wrap">
					<input type="radio" id="radio_run_castType" class="radio" checked />
					<label class="label_radio" id="radio_run_client_castType_label" for="radio_run_castType" style="cursor:default;"><?=$clientFunc->getCastTypeValue() ?></label>
				</div>
			</div>
			<div class="div_contents_run_cell_contents">
				<div class="div_radio_double_wrap"
					style="background: #efefef; width: 210px; height: 25px; line-height: 25px; margin: 10px 0px 5px 0px; padding: 0px 0px 0px 0px; font-weight: bold;">
					|&nbsp;<?=Audio_setup\Lang\STR_SERVER_ENCODE ?>
				</div>
				<div class="div_radio_double_wrap">
					<input type="radio" id="radio_run_client_encode" class="radio" checked />
					<label class="label_radio" id="radio_run_client_encode_label" for="radio_run_client_encode" style="cursor:default;">-</label>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_run_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_PLAY_INFO ?>
			</div>

			<div class="div_contents_cell_contents" style="padding: 10px 0px 10px 0px;">
				<div id="div_client_run_pcm">
					<div class="div_contents_table_run_sub_title">
						<b><?=Audio_setup\Lang\STR_SERVER_PCM_INFO ?></b>
					</div>
	
					<div class="div_contents_run_table_box">
						<select id="select_run_client_sampleRate" disabled>
							<option value="0"> - </option>
							<option value="16000"> 16,000 Hz </option>
							<option value="32000"> 32,000 Hz </option>
							<option value="44100"> 44,100 Hz </option>
							<option value="48000"> 48,000 Hz </option>
						</select>
					</div>
	
					<div class="div_contents_run_table_box">
						<select id="select_run_client_channels" disabled>
							<option value="0"> - </option>
							<option value="1"> ( 1 ch ) Mono </option>
							<option value="2"> ( 2 ch ) Stereo </option>
						</select>
					</div>
				</div>

				<div id="div_client_run_encode" style="display: none;">
					<div class="div_contents_table_run_sub_title" style="margin-top: 10px;">
						<b><?=Audio_setup\Lang\STR_SERVER_MP3_INFO ?></b>
					</div>
					<div class="div_contents_run_table_box">
						<select id="select_run_client_mp3_sampleRate" disabled>
							<option value="0"> - </option>
							<option value="32000"> 32,000 Hz </option>
							<option value="44100"> 44,100 Hz </option>
							<option value="48000"> 48,000 Hz </option>
						</select>
					</div>
					<div class="div_contents_run_table_box">
						<select id="select_run_client_mp3_bitRate" disabled>
							<option value="0"> - </option>
							<option value="32"> 32,000 bps </option>
							<option value="40"> 40,000 bps </option>
							<option value="48"> 48,000 bps </option>
							<option value="56"> 56,000 bps </option>
							<option value="64"> 64,000 bps </option>
							<option value="80"> 80,000 bps </option>
							<option value="96"> 96,000 bps </option>
							<option value="112"> 112,000 bps </option>
							<option value="128"> 128,000 bps </option>
							<option value="160"> 160,000 bps </option>
							<option value="192"> 192,000 bps </option>
							<option value="224"> 224,000 bps </option>
							<option value="256"> 256,000 bps </option>
							<option value="320"> 320,000 bps </option>
						</select>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_run_cell_title">
				<?=Audio_setup\Lang\STR_CLIENT_INFO_BUFFER ?>
			</div>

			<div class="div_contents_cell_contents" style="padding: 10px 0px 10px 0px;">
				<div class="div_contents_run_table_box">
					<select id="select_run_client_buffer_sec" disabled>
						<option value="0"  <?=$clientFunc->getBufferStat("sec", 0) ?>> 0 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="1"  <?=$clientFunc->getBufferStat("sec", 1) ?>> 1 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="2"  <?=$clientFunc->getBufferStat("sec", 2) ?>> 2 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="3"  <?=$clientFunc->getBufferStat("sec", 3) ?>> 3 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="4"  <?=$clientFunc->getBufferStat("sec", 4) ?>> 4 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="5"  <?=$clientFunc->getBufferStat("sec", 5) ?>> 5 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="6"  <?=$clientFunc->getBufferStat("sec", 6) ?>> 6 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="7"  <?=$clientFunc->getBufferStat("sec", 7) ?>> 7 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="8"  <?=$clientFunc->getBufferStat("sec", 8) ?>> 8 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="9"  <?=$clientFunc->getBufferStat("sec", 9) ?>> 9 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
						<option value="10" <?=$clientFunc->getBufferStat("sec", 10) ?>> 10 <?=Audio_setup\Lang\STR_COMMON_SEC ?> </option>
					</select>
				</div>

				<div class="div_contents_run_table_box">
					<select id="select_run_client_buffer_msec" disabled>
						<option value="0"  <?=$clientFunc->getBufferStat("msec", 0) ?>> 0 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="100"  <?=$clientFunc->getBufferStat("msec", 100) ?>> 100 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="200"  <?=$clientFunc->getBufferStat("msec", 200) ?>> 200 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="300"  <?=$clientFunc->getBufferStat("msec", 300) ?>> 300 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="400"  <?=$clientFunc->getBufferStat("msec", 400) ?>> 400 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="500"  <?=$clientFunc->getBufferStat("msec", 500) ?>> 500 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="600"  <?=$clientFunc->getBufferStat("msec", 600) ?>> 600 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="700"  <?=$clientFunc->getBufferStat("msec", 700) ?>> 700 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="800"  <?=$clientFunc->getBufferStat("msec", 800) ?>> 800 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
						<option value="900"  <?=$clientFunc->getBufferStat("msec", 900) ?>> 900 <?=Audio_setup\Lang\STR_COMMON_MSEC ?> </option>
					</select>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line"></div>
	</div>

<!--서버 정보-->
	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_CLIENT_INFO_SERVER ?>
	</div>
	<div class="div_contents_cell">

		<div class="div_contents_cell_line">
			<div class="div_contents_run_cell_title">
				<?=Audio_setup\Lang\STR_CLIENT_INFO_SERVER_MASTER ?>
			</div>
			<div class="div_contents_cell_contents" style="padding: 10px 0px 10px 0px;">
				<div class="div_contents_run_table_box">
					<div class="textbox">
						<label for="input_client_run_master_ipAddr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
						<input type="text" class="divServerInfo_deact" id="input_client_run_master_ipAddr" value="<?=$clientFunc->getOperValue("master", "ipAddr") ?>" disabled />
					</div>
				</div>
				<div class="div_contents_run_table_box">
					<div class="textbox">
						<label for="input_client_run_master_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
						<input type="text" class="divServerInfo_deact" id="input_client_run_master_port" value="<?=$clientFunc->getOperValue("master", "port") ?>" disabled />
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line" id="div_redundancy_view" <?=$clientFunc->getRedundancyStat() ?>>
			<div class="div_contents_run_cell_title">
				<?=Audio_setup\Lang\STR_CLIENT_INFO_SERVER_SLAVE ?>
			</div>
			<div class="div_contents_cell_contents" style="padding: 10px 0px 10px 0px;">
				<div class="div_contents_run_table_box">
					<div class="textbox">
						<label for="input_client_run_slave_ipAddr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
						<input type="text" class="divServerInfo_deact" id="input_client_run_slave_ipAddr" value="<?=$clientFunc->getOperValue("slave", "ipAddr") ?>" disabled />
					</div>
				</div>
				<div class="div_contents_run_table_box">
					<div class="textbox">
						<label for="input_client_run_slave_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
						<input type="text" class="divServerInfo_deact" id="input_client_run_slave_port" value="<?=$clientFunc->getOperValue("slave", "port") ?>" disabled />
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
		</div>

	</div>


<!--동작 정보-->
	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?>
	</div>
	<div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_run_cell_title">
				<?=Audio_setup\Lang\STR_CLIENT_INFO_LEVEL_METER ?>
			</div>
			<div class="div_contents_cell_contents">
					<div class="outputVolume_1" style="clear:both;"></div>
					<div class="level_outputVolume_1" style="display:none;">0</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_run_cell_title">
				<?=Audio_setup\Lang\STR_CLIENT_INFO_VOLUME ?>
			</div>
			<div class="div_contents_cell_contents" style="width: 615px; padding-top: 10px;">
				<div class="slidershell" id="slidershell1">
				    <div class="sliderfill" id="sliderfill1"></div>
				    <div class="slidertrack" id="slidertrack1"></div>
				    <div class="sliderthumb" id="sliderthumb1"></div>
				    <div class="slidervalue" id="slidervalue1">0</div>
				    <input class="slider" id="slider1" type="range" min="0" max="100" value="0"
				    oninput="showValue(value, 1, false);" onchange="showValue(value, 1, false);"/>
				</div>
				<div class="level_outputVolume" style="display:none;">0</div>
				<div id="div_button_apply_volume" class="div_log_button" style="width: 60px; height: 30px; line-height: 30px; float: right; margin: 5px 0px 0px 0px;">
					<?=Audio_setup\Lang\STR_COMMON_APPLY ?>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
		</div>
	</div>
</div>