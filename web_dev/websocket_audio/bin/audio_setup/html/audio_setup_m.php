<?php
    $env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

    include_once $env_pathModule . "common/common_define.php";
    include_once $env_pathModule . "common/common_script.php";

	$serverFunc = new Audio_setup\Func\AudioServerFunc();
	$clientFunc = new Audio_setup\Func\AudioClientFunc();
?>
<link rel="stylesheet" href="<?=Audio_setup\Def\PATH_WEB_CSS_STYLE_MOBILE ?>" type="text/css">

<div id="div_contents">

	<div id="div_log_title"> <?=Audio_setup\Lang\STR_MENU_NAME ?> </div>
	<hr>

	<div id="div_tabs">
		<div id="div_tabs_list_wrap">
			<?php
				if( $serverFunc->getEnableTabStat("audio_server") ) {
					echo '<div class="tabs_list ' . $serverFunc->getTabStat("server") . '">' . Audio_setup\Lang\STR_SETUP_AUDIO_SERVER . '</div>';

				} else {
					$serverFunc->setTabStat("client");
				}

				if( $clientFunc->getEnableTabStat("audio_client") ) {
					echo '<div class="tabs_list ' . $serverFunc->getTabStat("client") . '">' . Audio_setup\Lang\STR_SETUP_AUDIO_CLIENT . '</div>';

				} else {
					 $serverFunc->setTabStat("server");
				}
			?>

		</div>

		<div id="tabs_contents">
			<div class="tab_item" id="tabs-1" <?=$serverFunc->getTabContentStat("server") ?>>
				<?php include "server_m.php";	?>
			</div>
			<div class="tab_item" id="tabs-2" <?=$serverFunc->getTabContentStat("client") ?>>
				<?php include "client_m.php";	?>
			</div>
		</div>

	</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>
<?php include $env_pathModule . "common/audio_equlizer.php"; ?>
