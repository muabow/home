<div class="div_contents_table" id="div_display_server_setup" <?=$serverFunc->getSetupStat("setup") ?> />
	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_SERVER_OP_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div id="div_server_operation_run_setup" class="divActServerStopStat" <?php // $serverFunc->getActStat("stop") ?> >
			<span><?=Audio_setup\Lang\STR_SERVER_OP_STOP ?></span>
		</div>

		<div class="div_button_wrap">			
			<div id="div_button_server_apply" class="div_log_button">
				<?=Audio_setup\Lang\STR_COMMON_START ?>
			</div>
			
		</div>
		<div class="div_contents_cell_line" style="margin-top: 10px;"></div>
	</div>

	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_SERVER_OP_SETUP_INFO ?>
	</div>
	<!--div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_cell_category">
				<?=Audio_setup\Lang\STR_SERVER_PROTOCOL_INFO ?>
			</div>
			<div class="div_contents_run_cell_contents">
				<div class="div_radio_double_wrap">
					<?=Audio_setup\Lang\STR_SERVER_PROTOCOL ?>
				</div>
				<div class="div_radio_double_wrap">
					<?=$serverFunc->getProtocolValue() ?>
				</div>
			</div>
			<div class="div_contents_run_cell_contents">
				<div class="div_radio_double_wrap">
					<?=Audio_setup\Lang\STR_SERVER_CAST_TYPE ?>
				</div>
				<div class="div_radio_double_wrap">
					<?=$serverFunc->getCastTypeValue() ?>
				</div>
			</div>
			<div class="div_contents_run_cell_contents">
				<div class="div_radio_double_wrap">
					<?=Audio_setup\Lang\STR_SERVER_ENCODE ?>
				</div>
				<div class="div_radio_double_wrap">
					<?=$serverFunc->getEncodeValue() ?>
				</div>
			</div>
		</div-->
	<div class="div_contents_cell_contents" style="display: none;">
				<div class="div_radio_wrap">
					<input type="radio" name="radio_protocolType" id="radio_protocol_tcp" class="radio" <?=$serverFunc->getEnableStat("protocol", "tcp") ?> />
					<label class="label_radio" for="radio_protocol_tcp">TCP/IP</label>
				</div>

				<div class="div_radio_wrap" style="display: none;">
					<input type="radio" name="radio_protocolType" id="radio_protocol_rtsp" class="radio" <?=$serverFunc->getEnableStat("protocol", "rtsp") ?> />
					<label class="label_radio" for="radio_protocol_rtsp">RTSP</label>
				</div>
	</div>
	<div class="div_contents_cell_contents" style="display: none;">
				<div class="div_radio_wrap">
					<input type="radio" name="radio_encodeType" id="radio_encode_pcm" class="radio" <?=$serverFunc->getEnableStat("encode", "pcm") ?> />
					<label class="label_radio" for="radio_encode_pcm">PCM</label>
				</div>

				<div class="div_radio_wrap">
					<input type="radio" name="radio_encodeType" id="radio_encode_mp3" class="radio" <?=$serverFunc->getEnableStat("encode", "mp3") ?> />
					<label class="label_radio" for="radio_encode_mp3">MP3</label>
				</div>
	</div>
	
		<div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_cell_category">
				<?=Audio_setup\Lang\STR_SERVER_PROTOCOL_INFO ?>
			</div>	
			<div class="div_contents_run_cell_contents">	
				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						<?=$serverFunc->getProtocolValue() ?> 
					</div>
						<div class="div_contents_run_table_box">
						<?=$serverFunc->getCastTypeValue() ?> 
						</div>
					<div class="div_contents_run_table_box">
						<?=$serverFunc->getEncodeValue() ?>
					</div>		
				</div>	
			</div>	
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_category" style="margin-top:10px;">
				<?=Audio_setup\Lang\STR_SERVER_PLAY_INFO ?>
			</div>
	
		<div class="div_contents_cell_contents">
				<div id="div_server_run_pcm" <?=$serverFunc->getPcmStat() ?>>
					<div class="div_radio_double_wrap">
						<b><?=Audio_setup\Lang\STR_SERVER_PCM_INFO ?></b>
					</div>
					<div class="table_box_wrap">
						<div class="div_contents_run_table_box">
						<select id="select_sampleRate" style="display: none;">
							<option value="44100" <?=$serverFunc->getSelectStat("pcm", "sampleRate", "44100") ?>> 44,100 Hz </option>
							<option value="48000" <?=$serverFunc->getSelectStat("pcm", "sampleRate", "48000") ?>> 48,000 Hz </option>
						</select>
							<?php
								$sampleRate = ($serverFunc->getSelectStat("pcm", "sampleRate", "44100") == "selected") ? "44100 Hz" : "48000 Hz";
								echo $sampleRate;
							?>
						</div>
						
						<div class="div_contents_run_table_box" >
						<select id="select_channels" style="display: none;">
							<option value="1" <?=$serverFunc->getSelectStat("pcm", "channels", "1") ?>> ( 1 ch ) Mono </option>
							<option value="2" <?=$serverFunc->getSelectStat("pcm", "channels", "2") ?>> ( 2 ch ) Stereo </option>
						</select>
							<?php
								$audioChannel = ($serverFunc->getSelectStat("pcm", "channels", "1") == "selected") ? "( 1 ch ) Mono" : "( 2 ch ) Stereo";
								echo $audioChannel;
							?>
						</div>
					</div>
				</div>
			</div>
			<div class="div_contents_cell_contents">
				<div id="div_server_run_encode" <?=$serverFunc->getEncodeStat() ?>>
					
					<div class="div_radio_double_wrap" >
						<b><?=Audio_setup\Lang\STR_SERVER_MP3_INFO ?></b>
					</div>
					
					<div class="table_box_wrap">
						<div class="div_contents_run_table_box" >
						<select id="select_mp3_sampleRate" style="display: none;">
							<option value="44100" <?=$serverFunc->getSelectStat("mp3", "sampleRate", "44100") ?>> 44,100 Hz </option>
							<option value="48000" <?=$serverFunc->getSelectStat("mp3", "sampleRate", "48000") ?>> 48,000 Hz </option>
						</select>
							<?php
								$sampleRate = ($serverFunc->getSelectStat("mp3", "sampleRate", "44100") == "selected") ? "44100 Hz" : "48000 Hz";
								echo $sampleRate;
							?>
						</div>
						<div class="div_contents_run_table_box">
						<select id="select_mp3_bitRate" style="display: none;">
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
							<?php
								if($serverFunc->getSelectStat("mp3", "bitRate", "32") == "selected") {
									echo "32,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "40") == "selected") {
									echo "40,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "48") == "selected") {
									echo "48,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "56") == "selected") {
									echo "56,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "64") == "selected") {
									echo "64,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "80") == "selected") {
									echo "80,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "96") == "selected") {
									echo "96,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "112") == "selected") {
									echo "112,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "127") == "selected") {
									echo "127,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "160") == "selected") {
									echo "160,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "192") == "selected") {
									echo "192,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "224") == "selected") {
									echo "224,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "256") == "selected") {
									echo "256,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "320") == "selected") {
									echo "320,000 bps";
								}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_category" style="margin-top:10px;">
				<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?>
			</div>
			<div class="div_contents_cell_contents">
				<div class="div_contents_table_run_sub_title">
					<b><?=Audio_setup\Lang\STR_SERVER_UNICAST ?>&nbsp;<?=Audio_setup\Lang\STR_COMMON_SERVER ?>&nbsp;<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?></b>
				</div>
				
				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						
						<div class="textbox">
							<label for="input_server_run_ipAddr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
							<?=$serverFunc->getOperValueMyIp("ipAddr") ?>
						</div>
					</div>
	
					<div class="div_contents_run_table_box">
						<div class="textbox">
							<label for="input_server_run_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
							<?=$serverFunc->getOperValue("port") ?>
						</div>
					</div>
				</div>
				
				<div class="div_contents_table_run_sub_title">
					<b><?=Audio_setup\Lang\STR_SERVER_MULTICAST ?>&nbsp;<?=Audio_setup\Lang\STR_COMMON_SERVER ?>&nbsp;<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?></b>
				</div>
				
				<div class="div_radio_wrap" style="display: none;">
					<input type="radio" name="radio_operType" id="radio_oper_default" class="radio" <?=$serverFunc->getEnableStat("operType", "default") ?> />
					<label class="label_radio" for="radio_oper_default"><?=Audio_setup\Lang\STR_SERVER_OPER_DEFAULT ?></label>
				</div>

				<div class="div_radio_wrap" style="display: none;">
					<input type="radio" name="radio_operType" id="radio_oper_change" class="radio" <?=$serverFunc->getEnableStat("operType", "change") ?> />
					<label class="label_radio" for="radio_oper_change"><?=Audio_setup\Lang\STR_SERVER_OPER_CHANGE ?></label>
				</div>
				
				
				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						<div class="textbox" style="display: none;">
								<label for="input_server_ipAddr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
								<input type="text" id="input_server_ipAddr" value="<?=$serverFunc->getOperValue("ipAddr") ?>" <?=$serverFunc->getOperStat() ?> />
						</div>
						<div class="textbox">
							<label for="input_server_run_ipAddr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
							<?=$serverFunc->getOperValue("ipAddr") ?>
						</div>
					</div>
	
					<div class="div_contents_run_table_box">
						<div class="textbox" style="display: none;">
							<label for="input_server_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
							<input type="text" id="input_server_port" value="<?=$serverFunc->getOperValue("port") ?>" <?=$serverFunc->getOperStat() ?> />
						</div>
						<div class="textbox">
							<label for="input_server_run_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
							<?=$serverFunc->getOperValue("port") ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line"></div>
	</div>
</div>



<!-- 동작 모드 화면 -->
<div class="div_contents_table" id="div_display_server_operation" <?=$serverFunc->getSetupStat("operation") ?>>
	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_SERVER_OP_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div id="div_server_operation_run"  class="divActServerRunStat" style="display: none;"<?php // $serverFunc->getActStat("run") ?> >
			<span><?=Audio_setup\Lang\STR_SERVER_OP_RUN ?></span>
		</div>

		<div id="div_server_operation_stop" class="divActServerStopStat" <?php // $serverFunc->getActStat("stop") ?> >
			<span><?=Audio_setup\Lang\STR_SERVER_OP_STOP ?></span>
		</div>

		<div class="div_button_wrap">
			<div id="div_button_server_setup" class="div_log_button">
				<?=Audio_setup\Lang\STR_COMMON_STOP ?>
			</div>
			
		</div>
		<div class="div_contents_cell_line" style="margin-top: 10px;"></div>
	</div>

	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_SERVER_OP_SETUP_INFO ?>
	</div>
	<!--div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_cell_category">
				<?=Audio_setup\Lang\STR_SERVER_PROTOCOL_INFO ?>
			</div>
			<div class="div_contents_run_cell_contents">
				<div class="div_radio_double_wrap">
					<?=Audio_setup\Lang\STR_SERVER_PROTOCOL ?>
				</div>
				<div class="div_radio_double_wrap">
					<?=$serverFunc->getProtocolValue() ?>
				</div>
			</div>
			<div class="div_contents_run_cell_contents">
				<div class="div_radio_double_wrap">
					<?=Audio_setup\Lang\STR_SERVER_CAST_TYPE ?>
				</div>
				<div class="div_radio_double_wrap">
					<?=$serverFunc->getCastTypeValue() ?>
				</div>
			</div>
			<div class="div_contents_run_cell_contents">
				<div class="div_radio_double_wrap">
					<?=Audio_setup\Lang\STR_SERVER_ENCODE ?>
				</div>
				<div class="div_radio_double_wrap">
					<?=$serverFunc->getEncodeValue() ?>
				</div>
			</div>
		</div-->
		<div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_cell_category">
				<?=Audio_setup\Lang\STR_SERVER_PROTOCOL_INFO ?>
			</div>	
			<div class="div_contents_run_cell_contents">	
				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						<?=$serverFunc->getProtocolValue() ?> 
					</div>
						<div class="div_contents_run_table_box">
						<?=$serverFunc->getCastTypeValue() ?> 
						</div>
					<div class="div_contents_run_table_box">
						<?=$serverFunc->getEncodeValue() ?>
					</div>		
				</div>	
			</div>	
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_category" style="margin-top:10px;">
				<?=Audio_setup\Lang\STR_SERVER_PLAY_INFO ?>
			</div>
	
		<div class="div_contents_cell_contents">
				<div id="div_server_run_pcm" <?=$serverFunc->getPcmStat() ?>>
					<div class="div_radio_double_wrap">
						<b><?=Audio_setup\Lang\STR_SERVER_PCM_INFO ?></b>
					</div>
					<div class="table_box_wrap">
						<div class="div_contents_run_table_box">
							<?php
								$sampleRate = ($serverFunc->getSelectStat("pcm", "sampleRate", "44100") == "selected") ? "44100 Hz" : "48000 Hz";
								echo $sampleRate;
							?>
						</div>
						
						<div class="div_contents_run_table_box" >
							<?php
								$audioChannel = ($serverFunc->getSelectStat("pcm", "channels", "1") == "selected") ? "( 1 ch ) Mono" : "( 2 ch ) Stereo";
								echo $audioChannel;
							?>
						</div>
					</div>
				</div>
			</div>
			<div class="div_contents_cell_contents">
				<div id="div_server_run_encode" <?=$serverFunc->getEncodeStat() ?>>
					
					<div class="div_radio_double_wrap" >
						<b><?=Audio_setup\Lang\STR_SERVER_MP3_INFO ?></b>
					</div>
					
					<div class="table_box_wrap">
						<div class="div_contents_run_table_box">
							<?php
								$sampleRate = ($serverFunc->getSelectStat("mp3", "sampleRate", "44100") == "selected") ? "44100 Hz" : "48000 Hz";
								echo $sampleRate;
							?>
						</div>
						<div class="div_contents_run_table_box">
							<?php
								if($serverFunc->getSelectStat("mp3", "bitRate", "32") == "selected") {
									echo "32,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "40") == "selected") {
									echo "40,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "48") == "selected") {
									echo "48,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "56") == "selected") {
									echo "56,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "64") == "selected") {
									echo "64,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "80") == "selected") {
									echo "80,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "96") == "selected") {
									echo "96,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "112") == "selected") {
									echo "112,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "127") == "selected") {
									echo "127,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "160") == "selected") {
									echo "160,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "192") == "selected") {
									echo "192,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "224") == "selected") {
									echo "224,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "256") == "selected") {
									echo "256,000 bps";
								} else if($serverFunc->getSelectStat("mp3", "bitRate", "320") == "selected") {
									echo "320,000 bps";
								}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_category" style="margin-top:10px;">
				<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?>
			</div>
			<div class="div_contents_cell_contents">
				<div class="div_contents_table_run_sub_title">
					<b><?=Audio_setup\Lang\STR_SERVER_UNICAST ?>&nbsp;<?=Audio_setup\Lang\STR_COMMON_SERVER ?>&nbsp;<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?></b>
				</div>
				
				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						
						<div class="textbox">
							<label for="input_server_run_ipAddr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
							<?=$serverFunc->getOperValueMyIp("ipAddr") ?>
						</div>
					</div>
	
					<div class="div_contents_run_table_box">
						<div class="textbox">
							<label for="input_server_run_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
							<?=$serverFunc->getOperValue("port") ?>
						</div>
					</div>
				</div>
				
				<div class="div_contents_table_run_sub_title">
					<b><?=Audio_setup\Lang\STR_SERVER_MULTICAST ?>&nbsp;<?=Audio_setup\Lang\STR_COMMON_SERVER ?>&nbsp;<?=Audio_setup\Lang\STR_SERVER_OPER_INFO ?></b>
				</div>
				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						<div class="textbox">
							<label for="input_server_run_ipAddr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
							<?=$serverFunc->getOperValue("ipAddr") ?>
						</div>
					</div>
	
					<div class="div_contents_run_table_box">
						<div class="textbox">
							<label for="input_server_run_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
							<?=$serverFunc->getOperValue("port") ?>
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
