<?php
    $env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

    include_once $env_pathModule . "common/common_define.php";
    include_once $env_pathModule . "common/common_script.php";

	$setup_handler  = new Source_file_setup\Func\SetupHandler();
	$server_handler = new Source_file_setup\Func\ServerHandler();

	$is_enable_server = $setup_handler->is_enable_tab("server");
	$is_enable_client = $setup_handler->is_enable_tab("client");
?>
<link rel="stylesheet" href="<?=Source_file_setup\Def\PATH_WEB_CSS_STYLE_MOBILE ?>" type="text/css">
<script src="<?=Source_file_setup\Def\PATH_WEB_JS_MARQUEE ?>"></script>
<script src="<?=Source_file_setup\Def\PATH_WEB_JS_MARQUEE_MIN ?>"></script>

<div id="div_loader">
	<img src="/img/loader.gif">
</div>

<div id="div_contents">
	<div id="div_log_title"> <?=Source_file_setup\Lang\STR_MENU_NAME ?> </div>
	<hr>

	<div id="div_tabs">
		<div id="div_tabs_list_wrap">
			<?php
				if( $is_enable_server ) {
					echo '<div id="tabs_list_1" class="tabs_list">' . Source_file_setup\Lang\STR_SETUP_SERVER . '</div>';
				}

				if( $is_enable_client ) {
					echo '<div id="tabs_list_2" class="tabs_list">' . Source_file_setup\Lang\STR_SETUP_CLIENT . '</div>';
				}
			?>

		</div>

		<div id="tabs_contents">
			<?php
				if( $is_enable_server ) {
					echo '<div class="tab_item" id="tabs-1">';
					include "server_m.php";
					echo '</div>';
				}
			?>
			<?php
				if( $is_enable_client ) {
					echo '<div class="tab_item" id="tabs-2">';
					include "client_m.php";
					echo '</div>';
				}
			?>
		</div>

	</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>
<?php include $env_pathModule . "common/audio_equlizer.php"; ?>
