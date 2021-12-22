<?php
	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";

	$networkFunc = new Network_setup\Func\NetworkFunc();
?>

<link rel="stylesheet" href="<?=Network_setup\Def\PATH_WEB_CSS_STYLE_MOBILE ?>" type="text/css">

<div id="div_contents">

	<div id="div_log_title"> <?=Network_setup\Lang\STR_MENU_NAME ?> </div>
	<hr>

	<div id="div_contents_table">

		<div class="div_contents_cell">
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_category">
					<?=Network_setup\Lang\STR_NETWORK_NETWORK_INFO ?>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Network_setup\Lang\STR_NETWORK_SETUP_HOST ?>
				</div>
				<div class="div_contents_cell_contents">
					<div class="textbox">
						<?=$networkFunc->getNetworkInfo("common", "hostname") ?>
					</div>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Network_setup\Lang\STR_NETWORK_SETUP_DEVICE ?>
				</div>
				<div class="div_contents_cell_contents">
					<div class="textbox">
						<?=$networkFunc->getNetworkInfo("common", "location") ?>
					</div>
				</div>
			</div>

<?php
	$envData = $networkFunc->getEnvData();

	$arrDeviceList = array();
	foreach( $envData as $networkType => $value ) {
		if( strpos($networkType, "network_") !== 0 ) continue;
		if( $value->view == "disabled" ) continue;

		$networkName = substr($networkType, 8);
?>
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_category">
					<?php echo ucfirst($networkName); ?>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Network_setup\Lang\STR_NETWORK_SETUP_MAC ?>
				</div>
				<div class="div_contents_cell_contents">
					<div class="textbox">
						<?=$networkFunc->getNetworkInfo($networkType, "mac_address") ?>
					</div>
				</div>
			</div>

<?php
		if( $networkType != "network_bonding" ) {
?>
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Network_setup\Lang\STR_NETWORK_SETUP_DHCP ?>
				</div>
				<div class="div_contents_cell_contents">
					<div class="textbox">
						<span style="color: <?=$networkFunc->getNetworkStatColor($networkType) ?>;"><?php echo strtoupper($networkFunc->getNetworkDhcp($networkType)); ?></span>
					</div>
				</div>
			</div>
<?php
		}
?>
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Network_setup\Lang\STR_NETWORK_SETUP_IP ?>
				</div>
				<div class="div_contents_cell_contents">
					<div class="textbox">
						<?=$networkFunc->getNetworkInfo($networkType, "ip_address") ?>
					</div>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Network_setup\Lang\STR_NETWORK_SETUP_SUBNET ?>
				</div>
				<div class="div_contents_cell_contents">
					<div class="textbox">
						<?=$networkFunc->getNetworkInfo($networkType, "subnetmask") ?>
					</div>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Network_setup\Lang\STR_NETWORK_SETUP_GATEWAY ?>
				</div>
				<div class="div_contents_cell_contents">
					<div class="textbox">
						<?=$networkFunc->getNetworkInfo($networkType, "gateway") ?>
					</div>
				</div>
			</div>

<?php
		if( $networkType != "network_bonding" ) {
?>
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Network_setup\Lang\STR_NETWORK_SETUP_DNS1 ?>
				</div>
				<div class="div_contents_cell_contents">
					<div class="textbox">
						<?=$networkFunc->getNetworkInfo($networkType, "dns_server_1") ?>
					</div>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title">
					<?=Network_setup\Lang\STR_NETWORK_SETUP_DNS2 ?>
				</div>
				<div class="div_contents_cell_contents">
					<div class="textbox">
						<?=$networkFunc->getNetworkInfo($networkType, "dns_server_2") ?>
					</div>
				</div>
			</div>
<?php
		}
?>
			<div class="div_contents_cell_line">
			</div>
<?php
}
?>
		</div>
	</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>