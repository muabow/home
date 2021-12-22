<?php
	if( $commonFunc->procModuleStatus(basename(__FILE__)) ) return ;

	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";
?>

<link rel="stylesheet"	href="<?=Dev_tool\Def\PATH_WEB_CSS_STYLE ?>" type="text/css" />
<link rel="stylesheet"	href="/css/jquery-ui.css" type="text/css" />

<div id="div_module_contents">
	<div id="div_module_title"> <?=Dev_tool\Lang\STR_MENU_NAME ?> </div>

	<hr />

	<div id="div_module_banner">
		<select id="select_tools">
		    <option disabled selected style="display: none;"> </option>
			<option value="module_info"> 공용 모듈 정보 (Common module information) </option>
			<option value="create_user"> 비밀번호 변경 (Change password) </option>
			<option value="web_shell"  > 웹 쉘 (Web shell) </option>
			<option value="module_perm"> 권한 및 소유자 변경 (Change permission & owner) </option>
			<option value="system"> 시스템 환경 설정 (System preferences) </option>
			<option value="system_info"> 시스템 정보 (System information) </option>
		</select>
	</div>

	<div id="div_module_form" class="ui-widget-content">
		<div class="div_module_frame" id="div_exec_module_info">
			<?php include $env_pathModule . "script/exec_module_info.php" ?>
		</div>

		<div class="div_module_frame" id="div_exec_create_user">
			<?php include $env_pathModule . "script/exec_create_user.php" ?>
		</div>

		<div class="div_module_frame" id="div_exec_web_shell">
			<?php include $env_pathModule . "script/exec_web_shell.php" ?>
		</div>

		<div class="div_module_frame" id="div_exec_module_perm">
			<?php include $env_pathModule . "script/exec_module_perm.php" ?>
		</div>

		<div class="div_module_frame" id="div_exec_system">
			<?php include $env_pathModule . "script/exec_system.php" ?>
		</div>

		<div class="div_module_frame" id="div_exec_system_info">
			<?php include $env_pathModule . "script/exec_system_info.php" ?>
		</div>
	</div>
</div>

<?php include $env_pathModule . "common/common_js.php"; ?>