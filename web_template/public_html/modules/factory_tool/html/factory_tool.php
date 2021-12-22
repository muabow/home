<?php
	if( $commonFunc->procModuleStatus(basename(__FILE__)) ) return ;

	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));
	
	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";

	
	$target_dir    = "{$env_pathModule}/script";
	$arr_file_list = read_file_info($target_dir);
	
	$arr_name_list = array();
?>

<link rel="stylesheet"	href="<?=Factory_tool\Def\PATH_WEB_CSS_STYLE ?>" type="text/css" />
<link rel="stylesheet"	href="/css/jquery-ui.css" type="text/css" />

<div id="div_module_contents">
	<div id="div_module_title"> <?=Factory_tool\Lang\STR_MENU_NAME ?> </div>

	<hr />

	<div id="div_module_banner">
		<select id="select_tools">
		    <option disabled selected style="display: none;"> </option>
		</select>
	</div>
	
	<div id="div_module_form" class="ui-widget-content">
		
	<?php
		foreach( $arr_file_list as $idx => $file_info ) {
			$file_name_ext    = basename($file_info);
			$file_name_nonext = pathinfo($file_info)["filename"];
			
			echo "<div class='div_module_frame' id='div_{$file_name_nonext}'>";
			include_once "{$env_pathModule}script/{$file_name_ext}";
			echo "</div>";
			
			$arr_name_list[] = str_replace("exec_", "", $file_name_nonext);
		}
	?>
	</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>
