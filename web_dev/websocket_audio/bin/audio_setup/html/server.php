<div class="div_contents_table" id="div_display_server_setup" <?=$serverFunc->getSetupStat("setup") ?> />
	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_SERVER_SETUP_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_PROTOCOL ?>
			</div>
			<div class="div_contents_cell_contents">
				<div class="div_radio_wrap">
					<input type="radio" name="radio_protocolType" id="radio_protocol_tcp" class="radio" <?=$serverFunc->getEnableStat("protocol", "tcp") ?> />
					<label class="label_radio" for="radio_protocol_tcp">TCP/IP</label>
				</div>

				<div class="div_radio_wrap" style="display: none;">
					<input type="radio" name="radio_protocolType" id="radio_protocol_rtsp" class="radio" <?=$serverFunc->getEnableStat("protocol", "rtsp") ?> />
					<label class="label_radio" for="radio_protocol_rtsp">RTSP</label>
				</div>
			</div>
		</div>

		<!--div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_CAST_TYPE ?>
			</div>
			<div class="div_contents_cell_contents">
				<div class="div_radio_wrap">
					<input type="radio" name="radio_castType" id="radio_cast_unicast" class="radio" <?=$serverFunc->getEnableStat("castType", "unicast") ?> />
					<label class="label_radio" for="radio_cast_unicast">Unicast</label>
				</div>

				<div class="div_radio_wrap">
					<input type="radio" name="radio_castType" id="radio_cast_multicast" class="radio" <?=$serverFunc->getEnableStat("castType", "multicast") ?> />
					<label class="label_radio" for="radio_cast_multicast">Multicast</label>
				</div>

			</div>
		</div-->

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_ENCODE ?>
			</div>
			<div class="div_contents_cell_contents">
				<div class="div_radio_wrap">
					<input type="radio" name="radio_encodeType" id="radio_encode_pcm" class="radio" <?=$serverFunc->getEnableStat("encode", "pcm") ?> />
					<label class="label_radio" for="radio_encode_pcm">PCM</label>
				</div>

				<div class="div_radio_wrap">
					<input type="radio" name="radio_encodeType" id="radio_encode_mp3" class="radio" <?=$serverFunc->getEnableStat("encode", "mp3") ?> />
					<label class="label_radio" for="radio_encode_mp3">MP3</label>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_PLAY_INFO ?>
			</div>

			<div class="div_contents_cell_contents" style="padding: 10px 0px 10px 0px;">
				<div id="div_server_pcm" <?=$serverFunc->getPcmStat() ?>>
					<div class="div_contents_table_sub_title">
						<b><?=Audio_setup\Lang\STR_SERVER_PCM_SETUP ?></b>
					</div>
	
					<div class="div_contents_table_text">
						<?=Audio_setup\Lang\STR_SERVER_SAMPLE_RATE ?>
					</div>
					<div class="div_contents_table_box">
						<select id="select_sampleRate">
							<option value="44100" <?=$serverFunc->getSelectStat("pcm", "sampleRate", "44100") ?>> 44,100 Hz </option>
							<option value="48000" <?=$serverFunc->getSelectStat("pcm", "sampleRate", "48000") ?>> 48,000 Hz </option>
						</select>
					</div>
					
					
					<div class="div_contents_table_text" <?=$serverFunc->getChannelsStat() ?>>
						<?=Audio_setup\Lang\STR_SERVER_CHANNEL ?>
					</div>
					<div class="div_contents_table_box" <?=$serverFunc->getChannelsStat() ?>>
						<select id="select_channels">
							<option value="1" <?=$serverFunc->getSelectStat("pcm", "channels", "1") ?>> ( 1 ch ) Mono </option>
							<option value="2" <?=$serverFunc->getSelectStat("pcm", "channels", "2") ?>> ( 2 ch ) Stereo </option>
						</select>
					</div>
				</div>

				<div id="div_server_encode" <?=$serverFunc->getEncodeStat() ?>>
					<div class="div_contents_table_sub_title">
						<b><?=Audio_setup\Lang\STR_SERVER_MP3_SETUP ?></b>
					</div>

					<div class="div_contents_table_text">
						<?=Audio_setup\Lang\STR_SERVER_MP3_SAMPLE_RATE ?>
					</div>
					<div class="div_contents_table_box">
						<select id="select_mp3_sampleRate">
							<option value="44100" <?=$serverFunc->getSelectStat("mp3", "sampleRate", "44100") ?>> 44,100 Hz </option>
							<option value="48000" <?=$serverFunc->getSelectStat("mp3", "sampleRate", "48000") ?>> 48,000 Hz </option>
						</select>
					</div>

					<div class="div_contents_table_text">
						<?=Audio_setup\Lang\STR_SERVER_MP3_BIT_RATE ?>
					</div>
					<div class="div_contents_table_box">
						<select id="select_mp3_bitRate">
							<option value="32"  <?=$serverFunc->getSelectStat("mp3", "bitRate", "32") ?>> 32,000 bps </option>
							<option value="40"  <?=$serverFunc->getSelectStat("mp3", "bitRate", "40") ?>> 40,000 bps </option>
							<option value="48"  <?=$serverFunc->getSelectStat("mp3", "bitRate", "48") ?>> 48,000 bps </option>
							<option value="56"  <?=$serverFunc->getSelectStat("mp3", "bitRate", "56") ?>> 56,000 bps </option>
							<option value="64"  <?=$serverFunc->getSelectStat("mp3", "bitRate", "64") ?>> 64,000 bps </option>
							<option value="80"  <?=$serverFunc->getSelectStat("mp3", "bitRate", "80") ?>> 80,000 bps </option>
							<option value="96"  <?=$serverFunc->getSelectStat("mp3", "bitRate", "96") ?>> 96,000 bps </option>
							<option value="112" <?=$serverFunc->getSelectStat("mp3", "bitRate", "112") ?>> 112,000 bps </option>
							<option value="128" <?=$serverFunc->getSelectStat("mp3", "bitRate", "127") ?>> 128,000 bps </option>
							<option value="160" <?=$serverFunc->getSelectStat("mp3", "bitRate", "160") ?>> 160,000 bps </option>
							<option value="192" <?=$serverFunc->getSelectStat("mp3", "bitRate", "192") ?>> 192,000 bps </option>
							<option value="224" <?=$serverFunc->getSelectStat("mp3", "bitRate", "224") ?>> 224,000 bps </option>
							<option value="256" <?=$serverFunc->getSelectStat("mp3", "bitRate", "256") ?>> 256,000 bps </option>
							<option value="320" <?=$serverFunc->getSelectStat("mp3", "bitRate", "320") ?>> 320,000 bps </option>
						</select>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?>
			</div>
			<div class="div_contents_cell_contents" style="padding: 0px 0px 10px 0px;">
				<div class="div_radio_wrap">
					<input type="radio" name="radio_operType" id="radio_oper_default" class="radio" <?=$serverFunc->getEnableStat("operType", "default") ?> />
					<label class="label_radio" for="radio_oper_default"><?=Audio_setup\Lang\STR_SERVER_OPER_DEFAULT ?></label>
				</div>

				<div class="div_radio_wrap">
					<input type="radio" name="radio_operType" id="radio_oper_change" class="radio" <?=$serverFunc->getEnableStat("operType", "change") ?> />
					<label class="label_radio" for="radio_oper_change"><?=Audio_setup\Lang\STR_SERVER_OPER_CHANGE ?></label>
				</div>

				<div class="div_contents_cell_contents">
					<div class="div_contents_table_sub_title">
						<b><?=Audio_setup\Lang\STR_SERVER_OPER_SETUP ?></b>
					</div>

					<div id="div_server_multicast_ipAddr" <?=$serverFunc->getCastStat() ?> >
						<div class="div_contents_table_text">
							<?=Audio_setup\Lang\STR_COMMON_IP_M_ADDR ?>
						</div>
						<div class="div_contents_table_box">
							<div class="textbox">
								<label for="input_server_ipAddr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
								<input type="text" id="input_server_ipAddr" value="<?=$serverFunc->getOperValue("ipAddr") ?>" <?=$serverFunc->getOperStat() ?> />
							</div>
						</div>
					</div>
					<div class="div_contents_table_text">
						<?=Audio_setup\Lang\STR_COMMON_PORT ?>
					</div>
					<div class="div_contents_table_box">
						<div class="textbox">
							<label for="input_server_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
							<input type="text" id="input_server_port" value="<?=$serverFunc->getOperValue("port") ?>" <?=$serverFunc->getOperStat() ?> />
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line"></div>

		<div class="div_button_wrap">
			<div id="div_button_server_cancel" class="div_log_button">
				<?=Audio_setup\Lang\STR_COMMON_CANCEL ?>
			</div>
			<div id="div_button_server_apply" class="div_log_button">
				<?=Audio_setup\Lang\STR_COMMON_APPLY ?>
			</div>
			<div id="div_button_server_apply_hidden" class="div_log_button" style="display:none;">
				<?=Audio_setup\Lang\STR_COMMON_APPLY ?>
			</div>
		</div>
	</div>
</div>



<!-- 동작 모드 화면 -->
<div class="div_contents_table" id="div_display_server_operation" <?=$serverFunc->getSetupStat("operation") ?> >
	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_SERVER_OP_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div id="div_server_operation_run" class="divActServerRunStat" style="display: none;"<?php // $serverFunc->getActStat("run") ?> >
			<span><?=Audio_setup\Lang\STR_SERVER_OP_RUN ?></span>
		</div>

		<div id="div_server_operation_stop" class="divActServerStopStat" <?php // $serverFunc->getActStat("stop") ?> >
			<span><?=Audio_setup\Lang\STR_SERVER_OP_STOP ?></span>
		</div>

		<div class="div_contents_cell_line" style="margin-top: 10px; border-top: 1px dashed #cccccc;"></div>
		<div class="div_button_wrap">
			<div id="div_button_server_setup" class="div_log_button">
				<?=Audio_setup\Lang\STR_COMMON_STOP ?>
				<?php // Audio_setup\Lang\STR_COMMON_SETUP ?>
			</div>
			<!--
			<div id="div_button_server_stop" class="div_log_button">
				<?=Audio_setup\Lang\STR_COMMON_STOP ?>
			</div>
			<div id="div_button_server_start" class="div_log_button">
				<?=Audio_setup\Lang\STR_COMMON_START ?>
			</div>
			-->
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
			<div class="div_contents_run_cell_contents">
				<div class="div_radio_double_wrap"
					style="background: #efefef; width: 210px; height: 25px; line-height: 25px; margin: 10px 0px 5px 0px; padding: 0px 0px 0px 0px; font-weight: bold;">
					|&nbsp;<?=Audio_setup\Lang\STR_SERVER_PROTOCOL ?>
				</div>
				<div class="div_radio_double_wrap">
					<input type="radio" id="radio_run_protocol" class="radio" checked />
					<label class="label_radio" id="radio_run_protocol_label" for="radio_run_protocol" style="cursor:default;"><?=$serverFunc->getProtocolValue() ?></label>
				</div>
			</div>
			<div class="div_contents_run_cell_contents">
				<div class="div_radio_double_wrap"
					style="background: #efefef; width: 210px; height: 25px; line-height: 25px; margin: 10px 0px 5px 0px; padding: 0px 0px 0px 0px; font-weight: bold;">
					|&nbsp;<?=Audio_setup\Lang\STR_SERVER_CAST_TYPE ?>
				</div>
				<div class="div_radio_double_wrap">
					<input type="radio" id="radio_run_castType" class="radio" checked />
					<label class="label_radio" id="radio_run_castType_label" for="radio_run_castType" style="cursor:default;"><?=$serverFunc->getCastTypeValue() ?></label>
				</div>
			</div>
			<div class="div_contents_run_cell_contents">
				<div class="div_radio_double_wrap"
					style="background: #efefef; width: 210px; height: 25px; line-height: 25px; margin: 10px 0px 5px 0px; padding: 0px 0px 0px 0px; font-weight: bold;">
					|&nbsp;<?=Audio_setup\Lang\STR_SERVER_ENCODE ?>
				</div>
				<div class="div_radio_double_wrap">
					<input type="radio" id="radio_run_encode" class="radio" checked />
					<label class="label_radio" id="radio_run_encode_label" for="radio_run_encode" style="cursor:default;"><?=$serverFunc->getEncodeValue() ?></label>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_run_cell_title">
				<?=Audio_setup\Lang\STR_SERVER_PLAY_INFO ?>
			</div>
	
			<div class="div_contents_cell_contents" style="padding: 10px 0px 10px 0px;">
				<div id="div_server_run_pcm" <?=$serverFunc->getPcmStat() ?>>
					<div class="div_contents_table_run_sub_title">
						<b><?=Audio_setup\Lang\STR_SERVER_PCM_INFO ?></b>
					</div>
	
					<div class="div_contents_run_table_box">
						<select id="select_run_sampleRate" disabled>
							<option value="44100" <?=$serverFunc->getSelectStat("pcm", "sampleRate", "44100") ?>> 44,100 Hz </option>
							<option value="48000" <?=$serverFunc->getSelectStat("pcm", "sampleRate", "48000") ?>> 48,000 Hz </option>
						</select>
					</div>
	
					<div class="div_contents_run_table_box" >
						<select id="select_run_channels" disabled>
							<option value="1" <?=$serverFunc->getSelectStat("pcm", "channels", "1") ?>> ( 1 ch ) Mono </option>
							<option value="2" <?=$serverFunc->getSelectStat("pcm", "channels", "2") ?>> ( 2 ch ) Stereo </option>
						</select>
					</div>
				</div>
				
				<div id="div_server_run_encode" <?=$serverFunc->getEncodeStat() ?>>
					<div class="div_contents_table_run_sub_title" style="margin-top: 10px;">
						<b><?=Audio_setup\Lang\STR_SERVER_MP3_INFO ?></b>
					</div>
					<div class="div_contents_run_table_box">
						<select id="select_run_mp3_sampleRate" disabled>
							<option value="44100" <?=$serverFunc->getSelectStat("mp3", "sampleRate", "44100") ?>> 44,100 Hz </option>
							<option value="48000" <?=$serverFunc->getSelectStat("mp3", "sampleRate", "48000") ?>> 48,000 Hz </option>
						</select>
					</div>
					<div class="div_contents_run_table_box">
						<select id="select_run_mp3_bitRate" disabled>
							<option value="32"  <?=$serverFunc->getSelectStat("mp3", "bitRate", "32") ?>> 32,000 bps </option>
							<option value="40"  <?=$serverFunc->getSelectStat("mp3", "bitRate", "40") ?>> 40,000 bps </option>
							<option value="48"  <?=$serverFunc->getSelectStat("mp3", "bitRate", "48") ?>> 48,000 bps </option>
							<option value="56"  <?=$serverFunc->getSelectStat("mp3", "bitRate", "56") ?>> 56,000 bps </option>
							<option value="64"  <?=$serverFunc->getSelectStat("mp3", "bitRate", "64") ?>> 64,000 bps </option>
							<option value="80"  <?=$serverFunc->getSelectStat("mp3", "bitRate", "80") ?>> 80,000 bps </option>
							<option value="96"  <?=$serverFunc->getSelectStat("mp3", "bitRate", "96") ?>> 96,000 bps </option>
							<option value="112" <?=$serverFunc->getSelectStat("mp3", "bitRate", "112") ?>> 112,000 bps </option>
							<option value="128" <?=$serverFunc->getSelectStat("mp3", "bitRate", "127") ?>> 128,000 bps </option>
							<option value="160" <?=$serverFunc->getSelectStat("mp3", "bitRate", "160") ?>> 160,000 bps </option>
							<option value="192" <?=$serverFunc->getSelectStat("mp3", "bitRate", "192") ?>> 192,000 bps </option>
							<option value="224" <?=$serverFunc->getSelectStat("mp3", "bitRate", "224") ?>> 224,000 bps </option>
							<option value="256" <?=$serverFunc->getSelectStat("mp3", "bitRate", "256") ?>> 256,000 bps </option>
							<option value="320" <?=$serverFunc->getSelectStat("mp3", "bitRate", "320") ?>> 320,000 bps </option>
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
					<div class="div_contents_table_run_sub_title" style="margin-top: 10px;">
						<b><?=Audio_setup\Lang\STR_SERVER_UNICAST ?>&nbsp;<?=Audio_setup\Lang\STR_COMMON_SERVER ?>&nbsp;<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?></b>
					</div>

					<div class="div_contents_run_table_box">
						
						<div class="textbox">
							<label for="input_server_run_ipAddr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
							<input type="text" id="input_server_run_ipAddr1" value="<?=$serverFunc->getOperValueMyIp("ipAddr") ?>" disabled style="color: #808080; background: #ffffff;" />
						</div>
					</div>

					<div class="div_contents_run_table_box">
						<div class="textbox">
							<label for="input_server_run_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
							<input type="text" id="input_server_run_port1" value="<?=$serverFunc->getOperValue("port") ?>" disabled style="color: #808080; background: #ffffff;" />
						</div>
					</div>
					<div class="div_contents_table_run_sub_title" style="margin-top: 10px;">
						<b><?=Audio_setup\Lang\STR_SERVER_MULTICAST ?>&nbsp;<?=Audio_setup\Lang\STR_COMMON_SERVER ?>&nbsp;<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?></b>
					</div>
					<div class="div_contents_run_table_box">
						<div class="textbox">
							<label for="input_server_run_ipAddr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
							<input type="text" id="input_server_run_ipAddr2" value="<?=$serverFunc->getOperValue("ipAddr") ?>" disabled style="color: #808080; background: #ffffff;" />
						</div>
					</div>

					<div class="div_contents_run_table_box">
						<div class="textbox">
							<label for="input_server_run_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
							<input type="text" id="input_server_run_port2" value="<?=$serverFunc->getOperValue("port") ?>" disabled style="color: #808080; background: #ffffff;" />
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
	<div class="div_contents_cell" style="margin-left: 10px;">
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
