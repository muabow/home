<?php
	if( $commonFunc->procModuleStatus(basename(__FILE__)) ) return ;

    $env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

    include_once $env_pathModule . "common/common_define.php";
    include_once $env_pathModule . "common/common_script.php";

	$setup_handler  = new Audio_setup\Func\AudioSetupHandler();
	$server_handler = new Audio_setup\Func\AudioServerHandler();

	$is_enable_server = $setup_handler->is_enable_tab("audio_server");
	$is_enable_client = $setup_handler->is_enable_tab("audio_client");
?>
<link rel="stylesheet" href="<?=Audio_setup\Def\PATH_WEB_CSS_STYLE ?>" type="text/css">

<div id="div_loader">
	<img src="/img/loader.gif">
</div>

<div id="div_contents">
	<div id="div_contents_title"> <?=Audio_setup\Lang\STR_MENU_NAME ?> </div>

	<div id="div_tabs">
		<div id="div_tabs_list_wrap">
			<?php
				if( $is_enable_server ) {
					echo '<div id="tabs_list_1" class="tabs_list">' . Audio_setup\Lang\STR_SETUP_AUDIO_SERVER . '</div>';
				}

				if( $is_enable_client ) {
					echo '<div id="tabs_list_2" class="tabs_list">' . Audio_setup\Lang\STR_SETUP_AUDIO_CLIENT . '</div>';
				}
			?>

		</div>

		<div id="tabs_contents">
			<?php
				if( $is_enable_server ) {
					echo '<div id="tabs-1">';
			 		include "server.php";
					echo '</div>';
				}
			?>
			<?php
				if( $is_enable_client ) {
					echo '<div id="tabs-2">';
			 		include "client.php";
					echo '</div>';
				}
			?>
		</div>

	</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>
<?php include $env_pathModule . "common/audio_equlizer.php"; ?>
