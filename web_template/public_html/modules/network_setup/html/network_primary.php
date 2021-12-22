<?php
	$fileName = basename(__FILE__, ".php");
	$type	  = str_replace("network_", "", $fileName);
?>

<div id="div_contents_table">

		<div class="div_contents_title">
			[<?=$type ?>] <?=Network_setup\Lang\STR_NETWORK_SETUP ?>
		</div>
		<div class="div_contents_cell">
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Network_setup\Lang\STR_NETWORK_USE_INFO ?>
				</div>

				<div class="div_contents_cell_contents">
					<div class="div_radio_wrap">
						<input type="radio" name="radio_use_<?=$type ?>" id="radio_networkSetupEnable_<?=$type ?>" class="radio" <?=$networkFunc->getNetworkEnableStat($fileName, "enabled") ?> />
						<label class="label_radio" for="radio_networkSetupEnable_<?=$type ?>"><?=Network_setup\Lang\STR_NETWORK_USE_ENABLE ?></label>
					</div>

					<div class="div_radio_wrap">
						<input type="radio" name="radio_use_<?=$type ?>" id="radio_networkSetupDisable_<?=$type ?>" class="radio" <?=$networkFunc->getNetworkEnableStat($fileName, "disabled") ?> />
						<label class="label_radio" for="radio_networkSetupDisable_<?=$type ?>"><?=Network_setup\Lang\STR_NETWORK_USE_DISABLE ?></label>
					</div>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Network_setup\Lang\STR_NETWORK_DHCP_INFO ?>
				</div>

				<div class="div_contents_cell_contents">
					<div class="div_radio_wrap">
						<input type="radio" name="radio_dhcp_<?=$type ?>" id="radio_networkSetupStatic_<?=$type ?>" class="radio" <?=$networkFunc->getNetworkDhcpStat($fileName, "off") ?> />
						<label class="label_radio" for="radio_networkSetupStatic_<?=$type ?>"><?=Network_setup\Lang\STR_NETWORK_SETUP_USER ?></label>
					</div>

					<div class="div_radio_wrap">
						<input type="radio" name="radio_dhcp_<?=$type ?>" id="radio_networkSetupDhcp_<?=$type ?>" class="radio" <?=$networkFunc->getNetworkDhcpStat($fileName, "on") ?> />
						<label class="label_radio" for="radio_networkSetupDhcp_<?=$type ?>"><?=Network_setup\Lang\STR_NETWORK_SETUP_DHCP ?></label>
					</div>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Network_setup\Lang\STR_NETWORK_NETWORK_INFO ?>
				</div>

				<div class="div_contents_cell_contents">
					<div class="div_table_visible">
						<div class="div_table_title_wrap">
							<div class="div_contents_table_text">
								<?=Network_setup\Lang\STR_NETWORK_SETUP_MAC ?>
							</div>
							<div class="div_contents_table_text" name="input_networkIpAddr">
								<?=Network_setup\Lang\STR_NETWORK_SETUP_IP ?>
							</div>
							<div class="div_contents_table_text" name="input_networkSubnetmask">
								<?=Network_setup\Lang\STR_NETWORK_SETUP_SUBNET ?>
							</div>
							<div class="div_contents_table_text" name="input_networkGateway">
								<?=Network_setup\Lang\STR_NETWORK_SETUP_GATEWAY ?>
							</div>
							<div class="div_contents_table_text" name="input_networkDns1">
								<?=Network_setup\Lang\STR_NETWORK_SETUP_DNS1 ?>
							</div>
							<div class="div_contents_table_text" name="input_networkDns2">
								<?=Network_setup\Lang\STR_NETWORK_SETUP_DNS2 ?>
							</div>
						</div>
						<div class="div_table_contents_wrap">
							<div class="div_contents_table_box" style="height: 38px; line-height: 38px;">
									<?=$networkFunc->getNetworkInfo($fileName, "mac_address") ?>
							</div>

							<div class="div_contents_table_box">
								<div class="textbox">
									<label for="text_ipAddress"><?=Network_setup\Lang\STR_NETWORK_SETUP_IP ?></label>
									<input type="text" id="input_networkIpAddr_<?=$type ?>" class="input_textbox"
											value="<?=$networkFunc->getNetworkInfo($fileName, "ip_address") ?>"
											<?=$networkFunc->getNetworkInputDisabled($fileName) ?>
									>
								</div>
							</div>
							<div class="div_contents_table_box">
								<div class="textbox">
									<label for="text_subnetmask"><?=Network_setup\Lang\STR_NETWORK_SETUP_SUBNET ?></label>
									<input type="text" id="input_networkSubnetmask_<?=$type ?>" class="input_textbox"
											value="<?=$networkFunc->getNetworkInfo($fileName, "subnetmask") ?>"
											<?=$networkFunc->getNetworkInputDisabled($fileName) ?>
									>
								</div>
							</div>
							<div class="div_contents_table_box">
								<div class="textbox">
									<label for="text_gateway"><?=Network_setup\Lang\STR_NETWORK_SETUP_GATEWAY ?></label>
									<input type="text" id="input_networkGateway_<?=$type ?>" class="input_textbox"
											value="<?=$networkFunc->getNetworkInfo($fileName, "gateway") ?>"
											<?=$networkFunc->getNetworkInputDisabled($fileName) ?>
									>
								</div>
							</div>
							<div class="div_contents_table_box">
								<div class="textbox">
									<label for="text_dns1"><?=Network_setup\Lang\STR_NETWORK_SETUP_DNS1 ?></label>
									<input type="text" id="input_networkDns1_<?=$type ?>" class="input_textbox"
											value="<?=$networkFunc->getNetworkInfo($fileName, "dns_server_1") ?>"
											<?=$networkFunc->getNetworkInputDisabled($fileName) ?>
									>
								</div>
							</div>
							<div class="div_contents_table_box">
								<div class="textbox">
									<label for="text_dns2"><?=Network_setup\Lang\STR_NETWORK_SETUP_DNS2 ?></label>
									<input type="text" id="input_networkDns2_<?=$type ?>" class="input_textbox"
											value="<?=$networkFunc->getNetworkInfo($fileName, "dns_server_2") ?>"
											<?=$networkFunc->getNetworkInputDisabled($fileName) ?>
									>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="div_contents_cell_line">
			</div>
		</div>
	</div>