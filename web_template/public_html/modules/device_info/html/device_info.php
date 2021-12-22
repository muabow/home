<?php
    $env_device_module = str_replace(basename(__FILE__), "", realpath(__FILE__));
    include_once $env_device_module . "common/common_define.php";
    include_once $env_device_module . "common/common_script.php";
?>
<link rel="stylesheet" href="<?=Device_info\Def\PATH_WEB_CSS_STYLE ?>" type="text/css">

<div class="div_contents">
	<!-- 제품 전/후면 이미지 -->
	<img src="<?=Device_info\Def\PATH_WEB_IMG_DEV_EXTERIOR ?>" style="width:100%; margin: auto; " />


	<!-- 네트워크 정보, Ethernet 정보 가변 대응 -->
	<div class="div_title"># <?=Device_info\Lang\STR_INIT_NETWORK_INFO ?></div>
	<div class="div_table">
		<div class="div_table_row">
			<div class="div_table_cell div_table_background"></div>
			<?=make_table_row("network_primary",	"title") ?>
			<?=make_table_row("network_secondary",	"title") ?>
			<?=make_table_row("network_bonding",	"title") ?>
		</div>
		<div class="div_table_row">
			<div class="div_table_cell div_table_background"><?=Device_info\Lang\STR_INIT_NETWORK_ENABLE ?></div>
			<?=make_table_row("network_primary",	"enable") ?>
			<?=make_table_row("network_secondary",	"enable") ?>
			<?=make_table_row("network_bonding",	"enable") ?>
		</div>
		<div class="div_table_row">
			<div class="div_table_cell div_table_background"><?=Device_info\Lang\STR_INIT_NETWORK_DHCP ?></div>
			<?=make_table_row("network_primary",	"dhcp") ?>
			<?=make_table_row("network_secondary",	"dhcp") ?>
			<?=make_table_row("network_bonding",	"dhcp") ?>
		</div>
		<div class="div_table_row">
			<div class="div_table_cell div_table_background"><?=Device_info\Lang\STR_INIT_NETWORK_MAC_ADDR ?></div>
			<?=make_table_row("network_primary",	"mac_address") ?>
			<?=make_table_row("network_secondary",	"mac_address") ?>
			<?=make_table_row("network_bonding",	"mac_address") ?>
		</div>
		<div class="div_table_row">
			<div class="div_table_cell div_table_background"><?=Device_info\Lang\STR_INIT_NETWORK_IP_ADDR ?></div>
			<?=make_table_row("network_primary",	"ip_address") ?>
			<?=make_table_row("network_secondary",	"ip_address") ?>
			<?=make_table_row("network_bonding",	"ip_address") ?>
		</div>
		<div class="div_table_row">
			<div class="div_table_cell div_table_background"><?=Device_info\Lang\STR_INIT_NETWORK_GATEWAY ?></div>
			<?=make_table_row("network_primary",	"gateway") ?>
			<?=make_table_row("network_secondary",	"gateway") ?>
			<?=make_table_row("network_bonding",	"gateway") ?>
		</div>
		<div class="div_table_row">
			<div class="div_table_cell div_table_background"><?=Device_info\Lang\STR_INIT_NETWORK_SUBNET ?></div>
			<?=make_table_row("network_primary",	"subnetmask") ?>
			<?=make_table_row("network_secondary",	"subnetmask") ?>
			<?=make_table_row("network_bonding",	"subnetmask") ?>
		</div>
		<div class="div_table_row">
			<div class="div_table_cell div_table_background">DNS 1</div>
			<?=make_table_row("network_primary",	"dns_server_1") ?>
			<?=make_table_row("network_secondary",	"dns_server_1") ?>
			<?=make_table_row("network_bonding",	"dns_server_1") ?>
		</div>
		<div class="div_table_row">
			<div class="div_table_cell div_table_background">DNS 2</div>
			<?=make_table_row("network_primary",	"dns_server_2") ?>
			<?=make_table_row("network_secondary",	"dns_server_2") ?>
			<?=make_table_row("network_bonding",	"dns_server_2") ?>
		</div>
		<div class="div_table_row">
			<div class="div_table_cell div_table_background"><?=Device_info\Lang\STR_INIT_NETWORK_LINK_STAT ?></div>
			<?=make_table_row("network_primary",	"link") ?>
			<?=make_table_row("network_secondary",	"link") ?>
			<?=make_table_row("network_bonding",	"link") ?>
		</div>
	</div>


	<!-- 규격 및 성능, 해당 장치 정보 수동 입력 -->
	<div class="div_title"># <?=Device_info\Lang\STR_INIT_SPEC_INFO ?></div>
			
	<div class="div_table">

		<?=make_deviceInfo_table_row("Audio") ?>

		<?=make_deviceInfo_table_row("CP") ?>

		<?=make_deviceInfo_table_row("RM") ?>
		
		<div class="div_table_row">
			<div class="div_table_column div_table_background">
				<div class="div_table_cell"><?=Device_info\Lang\STR_INIT_SPEC_COMM ?></div>
			</div>
			<div class="div_table_column div_table_background">
				<div class="div_table_cell"><?=Device_info\Lang\STR_INIT_SPEC_NETWORK ?></div>
			</div>
			<div class="div_table_column div_table_cell_double">
				<div class="div_table_cell">100/1000 BASE-T (RJ-45)</div>
			</div>
		</div>

		<div class="div_table_row">
			<div class="div_table_cell div_table_background" style="padding: 0 1px;"><?=Device_info\Lang\STR_INIT_SPEC_POWER ?></div>
			<div class="div_table_cell">DC 24V, 2A</div>
		</div>
		
	</div>
</div>

