<div class="div_contents_table" id="div_display_client_setup" <?=$clientFunc->getSetupStat("setup") ?>>
	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_CLIENT_OP_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div id="div_client_operation_stop_mobile" class="divActClientStopStat" <?php // $clientFunc->getActStat("stop") ?> >
			<span id="span_client_operation_status"><?=Audio_setup\Lang\STR_CLIENT_OP_STOP ?></span>
		</div>
		<div class="div_contents_cell_line"></div>
		<div class="div_button_wrap">
			<div id="div_button_client_apply" class="div_log_button">
				<?=Audio_setup\Lang\STR_COMMON_START ?>
			</div>
		</div>
		<div class="div_contents_cell_line" style="margin-top: 10px;"></div>
	</div>
	
	<div class="div_contents_cell_contents" style="display: none;" >
		<div class="div_radio_wrap">
			<input type="radio" name="radio_client_protocolType" id="radio_client_protocol_tcp" class="radio" <?=$clientFunc->getEnableStat("protocol", "tcp") ?> />
			<label class="label_radio" for="radio_client_protocol_tcp">TCP/IP</label>
		</div>
		<div class="div_radio_wrap" >
			<input type="radio" name="radio_client_protocolType" id="radio_client_protocol_rtsp" class="radio" <?=$clientFunc->getEnableStat("protocol", "rtsp") ?> />
			<label class="label_radio" for="radio_client_protocol_rtsp">RTSP</label>
		</div>
	</div>
	
	<div class="div_contents_cell_contents" style="display: none;">
		<div class="div_radio_wrap">
			<input type="radio" name="radio_client_castType" id="radio_client_cast_unicast" class="radio" <?=$clientFunc->getEnableStat("castType", "unicast") ?> />
			<label class="label_radio" for="radio_client_cast_unicast">Unicast</label>
		</div>
		<div class="div_radio_wrap">
			<input type="radio" name="radio_client_castType" id="radio_client_cast_multicast" class="radio" <?=$clientFunc->getEnableStat("castType", "multicast") ?> />
			<label class="label_radio" for="radio_client_cast_multicast">Multicast</label>
		</div>
	</div>

	<div class="div_contents_title">
		<?=Audio_setup\Lang\STR_CLIENT_INFO_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_cell_category">
				<?=Audio_setup\Lang\STR_SERVER_PROTOCOL_INFO ?>
			</div>	
			<div class="div_contents_run_cell_contents">	
				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						<?=$clientFunc->getProtocolValue() ?> 
					</div>
					<div class="div_contents_run_table_box">
						<?=$clientFunc->getCastTypeValue() ?> 
					</div>
					<div class="div_contents_run_table_box">
						<span id="radio_stop_client_encode_label">-</span>
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
				<div id="div_client_stop_pcm">
					<div class="div_radio_double_wrap">
						<b><?=Audio_setup\Lang\STR_SERVER_PCM_INFO ?></b>
					</div>					
					<div class="table_box_wrap">
						<div class="div_contents_run_table_box">
							<span id="select_mobile_client_sampleRate_stop">-</span>
						</div>
						<div class="div_contents_run_table_box">
							<span id="select_mobile_client_channels_stop">-</span>
						</div>
					</div>
				</div>
			</div>
			<div class="div_contents_cell_contents" >
				<div id="div_client_stop_encode" style="display: none;">
					<div class="div_radio_double_wrap">
						<b><?=Audio_setup\Lang\STR_SERVER_MP3_INFO ?></b>
					</div>
					<div class="table_box_wrap">
						<div class="div_contents_run_table_box">
							<span id="select_mobile_client_mp3_sampleRate_stop">-</span>
						</div>
						<div class="div_contents_run_table_box">
							<span id="select_mobile_client_mp3_bitRate_stop">-</span>
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
					<span id="select_stop_client_buffer_sec"><?=$clientFunc->getBufferStatMobile("sec") ?> <?=Audio_setup\Lang\STR_COMMON_SEC ?></span>
					<select id="select_client_buffer_sec" style="display: none;" >
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
					<span id="select_stop_client_buffer_msec"><?=$clientFunc->getBufferStatMobile("msec") ?> <?=Audio_setup\Lang\STR_COMMON_MSEC ?></span>
					<select id="select_client_buffer_msec" style="display: none;" >
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
	<div class="div_contents_cell_contents" style="display: none;">
		<div class="div_radio_wrap">
			<input type="radio" name="radio_client_operType" id="radio_client_oper_default" class="radio" <?=$clientFunc->getEnableStat("operType", "default") ?> />
			<label class="label_radio" for="radio_client_oper_default"><?=Audio_setup\Lang\STR_SERVER_OPER_DEFAULT ?></label>
		</div>
		<div class="div_radio_wrap">
			<input type="radio" name="radio_client_operType" id="radio_client_oper_change" class="radio" <?=$clientFunc->getEnableStat("operType", "change") ?> />
			<label class="label_radio" for="radio_client_oper_change"><?=Audio_setup\Lang\STR_SERVER_OPER_CHANGE ?></label>
		</div>
		<div class="div_radio_wrap">
			<input type="radio" name="radio_client_redundancy" id="radio_client_redundancy_master" class="radio" <?=$clientFunc->getEnableStat("redundancy", "master") ?> />
			<label class="label_radio" for="radio_client_redundancy_master"><?=Audio_setup\Lang\STR_CLIENT_REDUNDANCY_MASTER ?></label>
		</div>
		<div class="div_radio_wrap">
			<input type="radio" name="radio_client_redundancy" id="radio_client_redundancy_slave" class="radio" <?=$clientFunc->getEnableStat("redundancy", "slave") ?> />
			<label class="label_radio" for="radio_client_redundancy_slave"><?=Audio_setup\Lang\STR_CLIENT_REDUNDANCY_SLAVE ?></label>
		</div>
		<div class="div_contents_table_box">
			<div class="textbox">
				<label for="input_client_ipAddr_master"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
				<input type="text" id="input_client_ipAddr_master" value="<?=$clientFunc->getOperValue("master", "ipAddr") ?>" <?=$clientFunc->getOperStat() ?> />
			</div>
		</div>
		<div class="div_contents_table_box">
			<div class="textbox">
				<label for="input_client_port_master"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
				<input type="text" id="input_client_port_master" value="<?=$clientFunc->getOperValue("master", "port") ?>" <?=$clientFunc->getOperStat() ?> />
			</div>
		</div>
		<div class="div_contents_table_box">
			<div class="textbox">
				<label for="input_client_ipAddr_slave"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
				<input type="text" id="input_client_ipAddr_slave" value="<?=$clientFunc->getOperValue("slave", "ipAddr") ?>" <?=$clientFunc->getOperStat() ?> />
			</div>
		</div>
		<div class="div_contents_table_box">
			<div class="textbox">
				<label for="input_client_port_slave"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
				<input type="text" id="input_client_port_slave" value="<?=$clientFunc->getOperValue("slave", "port") ?>" <?=$clientFunc->getOperStat() ?> />
			</div>
		</div>
	</div>

	<div class="div_contents_cell">
		<div class="div_contents_cell_line">
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
							<label for="input_client_stop_master_ipAddr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
							<input type="text" class="divServerInfo_deact" id="input_client_stop_master_ipAddr" value="<?=$clientFunc->getOperValue("master", "ipAddr") ?>" readonly />
						</div>
					</div>
					<div class="div_contents_run_table_box" >
						<div class="textbox">
							<label for="input_client_stop_master_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
							<input type="text" class="divServerInfo_deact" id="input_client_stop_master_port" value="<?=$clientFunc->getOperValue("master", "port") ?>" readonly  />
						</div>
					</div>
				</div>
				<div class="div_contents_cell_line"></div>
			</div>
		</div>
		<div class="div_contents_cell_line" id="div_redundancy_view" <?=$clientFunc->getRedundancyStat() ?>>
			<div class="div_contents_cell_contents" >
				<div class="div_radio_double_wrap">
					<?=Audio_setup\Lang\STR_CLIENT_INFO_SERVER_SLAVE ?>
				</div>
				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						<div class="textbox">
							<label for="input_client_stop_slave_ipAddr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
							<input type="text" class="divServerInfo_deact" id="input_client_stop_slave_ipAddr" value="<?=$clientFunc->getOperValue("slave", "ipAddr") ?>" readonly />
						</div>
					</div>
					<div class="div_contents_run_table_box">
						<div class="textbox">
							<label for="input_client_stop_slave_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
							<input type="text" class="divServerInfo_deact" id="input_client_stop_slave_port" value="<?=$clientFunc->getOperValue("slave", "port") ?>" readonly />
						</div>
					</div>
				</div>
			<div class="div_contents_cell_line"></div>
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

		<div class="div_button_wrap">
			<div id="div_button_client_setup_mobile" class="div_log_button">
				<?=Audio_setup\Lang\STR_COMMON_STOP ?>
			</div>
			<div id="div_button_client_apply_mobile" class="div_log_button" style="display: none;">
				<?=Audio_setup\Lang\STR_COMMON_START?>
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
						<?=$clientFunc->getProtocolValue() ?> 
					</div>
					<div class="div_contents_run_table_box">
						<?=$clientFunc->getCastTypeValue() ?> 
					</div>
					<div class="div_contents_run_table_box">
						<span id="radio_run_client_encode_label">-</span>
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
							<span id="select_mobile_client_sampleRate">-</span>
						</div>
		
						<div class="div_contents_run_table_box">
							<span id="select_mobile_client_channels">-</span>
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
							<span id="select_mobile_client_mp3_sampleRate">-</span>
						</div>
						<div class="div_contents_run_table_box">
							<span id="select_mobile_client_mp3_bitRate">-</span>
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
					<span id="select_run_client_buffer_sec"><?=$clientFunc->getBufferStatMobile("sec") ?> <?=Audio_setup\Lang\STR_COMMON_SEC ?></span>
				</div>
				<div class="div_contents_run_table_box">
					<span id="select_run_client_buffer_msec"><?=$clientFunc->getBufferStatMobile("msec") ?> <?=Audio_setup\Lang\STR_COMMON_MSEC ?></span>
				</div>
			</div>
		</div>
		<div class="div_contents_cell_line"></div>
	</div>
<!--서버 정보-->
	<div class="div_contents_cell">
		<div class="div_contents_cell_line">
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
							<input type="text" class="divServerInfo_deact" id="input_client_run_master_ipAddr" value="<?=$clientFunc->getOperValue("master", "ipAddr") ?>" readonly />
						</div>
					</div>
					<div class="div_contents_run_table_box" >
						<div class="textbox">
							<label for="input_client_run_master_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
							<input type="text" class="divServerInfo_deact" id="input_client_run_master_port" value="<?=$clientFunc->getOperValue("master", "port") ?>" readonly  />
						</div>
					</div>
				</div>
				<div class="div_contents_cell_line"></div>
			</div>
		</div>
		<div class="div_contents_cell_line" id="div_redundancy_view" <?=$clientFunc->getRedundancyStat() ?>>
			<div class="div_contents_cell_contents" >
				<div class="div_radio_double_wrap">
					<?=Audio_setup\Lang\STR_CLIENT_INFO_SERVER_SLAVE ?>
				</div>
				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						<div class="textbox">
							<label for="input_client_run_slave_ipAddr"><?=Audio_setup\Lang\STR_COMMON_IP_ADDR ?></label>
							<input type="text" class="divServerInfo_deact" id="input_client_run_slave_ipAddr" value="<?=$clientFunc->getOperValue("slave", "ipAddr") ?>" readonly />
						</div>
					</div>
					<div class="div_contents_run_table_box">
						<div class="textbox">
							<label for="input_client_run_slave_port"><?=Audio_setup\Lang\STR_COMMON_PORT ?></label>
							<input type="text" class="divServerInfo_deact" id="input_client_run_slave_port" value="<?=$clientFunc->getOperValue("slave", "port") ?>" readonly />
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
	
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_contents" style="display : flex;">
					<div class="div_radio_double_wrap">
						<?=Audio_setup\Lang\STR_CLIENT_INFO_VOLUME ?>
					</div>
					<div class="div_contents_run_table_box" >
						<div class="slidershell" id="slidershell1">
							<div class="sliderfill" id="sliderfill1"></div>
							<div class="slidertrack" id="slidertrack1"></div>
							<div class="sliderthumb" id="sliderthumb1"></div>
							<div class="slidervalue" id="slidervalue1">0</div>
							<input class="slider" id="slider1" type="range" min="0" max="100" value="0"
							    oninput="showValue(value, 1, false);" onchange="showValue(value, 1, false);"/>
						</div>
						<div class="level_outputVolume" style="display:none;">0</div>
						<input type="range" id="range_clientVolume" min="0" max="100" value="<?=$clientFunc->getOperVolume() ?>"/>
						<input type="text1" id="text_clientVolume" class="text_volume_value" value="<?=$clientFunc->getOperVolume() ?>"/>
						<div id="div_button_apply_volume_mobile" class="div_log_button_volume">
							<?=Audio_setup\Lang\STR_COMMON_APPLY ?>
						</div>
					</div>
				</div>
			</div>
			<div class="div_contents_cell_line"></div>
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
