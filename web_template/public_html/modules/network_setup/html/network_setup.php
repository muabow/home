<?php
	if( $commonFunc->procModuleStatus(basename(__FILE__)) ) return ;

	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";

	$networkFunc = new Network_setup\Func\NetworkFunc();

	function getUseStat($_type) {
		if( $_SESSION['username'] == "dev" ) {
			$networkFunc = new Network_setup\Func\NetworkFunc();

			if( $networkFunc->getEnableTabStat("network_" . $_type) ) {
				$checked = "checked";

			} else {
				$checked = "";
			}

			$checkBox = ' <input type="checkbox" id="check_useNetwork_' . $_type . '" ' . $checked . ' />';

		} else {
			$checkBox = "";
		}

		return $checkBox;
	}
?>

<link rel="stylesheet" href="<?=Network_setup\Def\PATH_WEB_CSS_STYLE ?>" type="text/css">

<div id="div_contents">
	<div id="div_network_title"> <?=Network_setup\Lang\STR_MENU_NAME ?> </div>
	<hr>

	<div id="div_contents_table">
		<div class="div_contents_title">
			<?=Network_setup\Lang\STR_MENU_COMMON ?> <?=Network_setup\Lang\STR_NETWORK_SETUP ?>
		</div>
		<div class="div_contents_cell">
			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title" name="input_deviceHostname">
					<?=Network_setup\Lang\STR_NETWORK_SETUP_HOST ?>
				</div>
				<div class="div_contents_cell_contents">
					<div class="textbox">
						<label for="input_deviceHostname"><?=Network_setup\Lang\STR_NETWORK_SETUP_HOST ?></label>
						<input type="text" id="input_deviceHostname" value="<?=$networkFunc->getNetworkInfo("common", "hostname") ?>">
					</div>
				</div>
			</div>

			<div class="div_contents_cell_line">
				<div class="div_contents_cell_title" name="input_deviceLocation">
					<?=Network_setup\Lang\STR_NETWORK_SETUP_DEVICE ?>
				</div>
				<div class="div_contents_cell_contents">
					<div class="textbox">
						<label for="input_deviceLocation"><?=Network_setup\Lang\STR_NETWORK_SETUP_DEVICE ?></label>
						<input type="text" id="input_deviceLocation" value="<?=$networkFunc->getNetworkInfo("common", "location") ?>">
					</div>
				</div>
			</div>
			<div class="div_contents_cell_line">
			</div>
		</div>
	</div>

	<div id="div_tabs">
		<div id="div_tabs_list_wrap">
					<div class="tabs_list <?=$networkFunc->getTabStat("primary")   ?>" <?=$networkFunc->getTabViewStat("primary")   ?> id="tab_primary"> Primary </div>
					<div class="tabs_list <?=$networkFunc->getTabStat("secondary") ?>" <?=$networkFunc->getTabViewStat("secondary") ?> id="tab_secondary"> Secondary </div>
					<div class="tabs_list <?=$networkFunc->getTabStat("bonding")   ?>" <?=$networkFunc->getTabViewStat("bonding")   ?> id="tab_bonding"> Bonding </div>

		</div>

		<div id="tabs_contents">
			<div id="tabs-1" <?=$networkFunc->getTabContentStat("primary") ?>>
				<?php include "network_primary.php";	?>
			</div>
			<div id="tabs-2" <?=$networkFunc->getTabContentStat("secondary") ?>>
				<?php include "network_secondary.php";	?>
			</div>
			<div id="tabs-3" <?=$networkFunc->getTabContentStat("bonding") ?>>
				<?php include "network_bonding.php";	?>
			</div>
		</div>
	</div>

			<div class="div_button_wrap">
				<div class="div_network_button" id="button_networkCancel"> <?=Network_setup\Lang\STR_NETWORK_BUTTON_CANCLE ?> </div>
				<div class="div_network_button" id="button_networkApply"> <?=Network_setup\Lang\STR_NETWORK_BUTTON_SET ?> </div>
			</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>