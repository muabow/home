<?php
	if( isset($_POST["type"]) && $_POST["type"] == "deny_login" ) {
		echo unlink("/var/lib/php5/sessions/sess_" . $_POST["session_id"]);

		return ;
	}

	header("Pragma: no-cache");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Content-Type: text/html; charset=UTF-8");

	if( preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $_SERVER['HTTP_USER_AGENT'])
		 || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($_SERVER['HTTP_USER_AGENT'], 0, 4)) ) {
		if( !isset($_SESSION['pc_view']) ) {
			include_once "login_m.php";
		 }

		return ;
	}

	include_once "common/common_define.php";
	include_once "common/common_script.php";

	const DFLT_LIMIT_COUNT  = 5;
	$path_limit_login = $_SERVER['DOCUMENT_ROOT'] . "/" . "../conf/config-limit-login.json";

	// Template Class 선언 (common_script.php)
	$commonFunc = new Common\Func\CommonFunc();

	if( isset($_POST['language']) ) 		{	$commonFunc->setEnvInfoLanguage($_POST['language']);	return ;	}
	if( isset($_POST['main_contents']) )	{	$commonFunc->setMainContents($_POST['main_contents']);	return ;	}

	// get session list
	$fd = fopen("/tmp/session_list", "r");
	$str_session_list = fread($fd, filesize("/tmp/session_list"));
	fclose($fd);

	$json_session_list = json_decode($str_session_list);

	// get limit login count
	if( file_exists($path_limit_login) ) {
		$load_envData = file_get_contents($path_limit_login);
		$envData      = json_decode($load_envData);

		$limit_login_count  = $envData->limit_login_count;

	} else {
		$limit_login_count  = DFLT_LIMIT_COUNT;
	}

	if( $json_session_list->count < $limit_login_count ) {
		header("Location: http://" . $_SERVER["HTTP_HOST"]);
		exit;
	}

	$arr_user_info = array();
	for( $idx = 0 ; $idx < $json_session_list->count ; $idx++ ) {
		$arr_data = explode(";", $json_session_list->list[$idx]->data);

		$arr_user_info[$idx]["session_id"] = $json_session_list->list[$idx]->session_id;
		for( $ex_idx = 0 ; $ex_idx < count($arr_data) - 1 ; $ex_idx++ ) {
			$arr_attr_info  = explode("|", $arr_data[$ex_idx]);
			$arr_info_value = explode(":", $arr_attr_info[1]);
			$value = trim($arr_info_value[count($arr_info_value) - 1]);

			$arr_user_info[$idx][$arr_attr_info[0]] = $value;
		}
	}
?>

<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="content-type"  content="text/html; charset=UTF-8" />
		<meta http-equiv="cache-control" content="no-cache" />
		<meta http-equiv="expires" content="0" />
		<meta http-equiv="pragma"  content="no-cache" />

		<link rel="stylesheet"    href="<?=Common\Def\PATH_WEB_CSS_STYLE ?>" type="text/css" />
		<link rel="shortcut icon" href="<?=Common\Def\PATH_WEB_IMG_FAVICON ?>" />

		<script type="text/javascript" src="<?=Common\Def\PATH_WEB_JS_JQUERY    ?>"></script>
		<script type="text/javascript" src="<?=Common\Def\PATH_WEB_JS_JQUERY_UI ?>"></script>
		<script type="text/javascript" src="<?=Common\Def\PATH_WEB_JS_AJAX      ?>"></script>

	   	<meta name="author"     content="<?=$commonFunc->getEnvCompanyName() ?>" />
	   	<meta name="keywords"   content="<?=$commonFunc->getEnvDeviceName() ?>"  />

		<title>[<?=$commonFunc->getEnvDeviceName() ?>] <?=Common\Lang\STR_TITLE_SETUP_PAGE ?></title>
	</head>

	<body onselectstart="return false" ondragstart="return false">
		<!-- Header -->
		<div id="div_header">
			<div id="div_header_top">
				<img id="img_header_ci" src="<?=$commonFunc->getEnvCompanyLogo() ?>" />
				<div id="div_header_name">
					<span id="span_header_name" name="main_contents.php"><?=$commonFunc->getEnvDeviceName() ?></span>
				</div>
			</div>
		</div>

		<!-- Banner -->
		<div id="div_banner">
			<div id="div_banner_contents">
				<span id="span_banner_hostname"> <?=$commonFunc->getHeaderInfoHostName() ?> </span>&nbsp;|
				<span id="span_banner_location"> <?=$commonFunc->getHeaderInfoLocation() ?> </span>&nbsp;|
				<span id="span_banner_ipAddr"> <?=$_SERVER['SERVER_NAME']?>  </span>&nbsp;|
				<?=Common\Lang\STR_FIRMWARE_VERSION ?>:&nbsp;<span="span_banner_version"> <?=$commonFunc->getEnvDeviceVersion() ?> </span>
			</div>
		</div>

		<!-- Main -->
		<div id="div_main">
			<div style="border-color: #e7e7e7; border-style: solid; border-width: 2px 1px 1px 1px; width: 1197px; float: left; background-image: url(/img/background_login.png); background-size: cover;">
				<div style="width: 800px; border: 1px solid #d2d2d2; border-radius: 5px; margin: 40px auto; text-align: center; padding-bottom : 30px; min-height: 400px;">
					<br />
					<span id="span_login_form_title"><?=Common\Lang\STR_LIMIT_LOGIN_TITLE ?></span>
					<br />
					<hr style="width: 95%; margin: auto; margin-top: 5px; margin-bottom: 5px;"/>

					<div style="width : 90%; margin: 0 auto; display:flex; flex-direction: column;">
						<div style="display: flex; background: #cccccc; font-weight: bold; font-size: 12pt;">
							<div style="flex: 1; border : 1px solid #a3a3a3; border-right : 0; line-height: 30px;"><?=Common\Lang\STR_LIMIT_LOGIN_USER ?></div>
							<div style="flex: 1; border : 1px solid #a3a3a3; border-right : 0; line-height: 30px;"><?=Common\Lang\STR_LIMIT_LOGIN_IP_ADDR ?></div>
							<div style="flex: 1; border : 1px solid #a3a3a3; border-right : 0;  line-height: 30px; min-width: 240px;"><?=Common\Lang\STR_LIMIT_LOGIN_TIME ?></div>
							<div style="flex: 1; border : 1px solid #a3a3a3; line-height: 30px;"> - </div>
						</div>

						<div style="display: flex; flex-direction: column;">
						<?php
						for( $idx = 0 ; $idx < $json_session_list->count ; $idx++ ) {
							echo '	<div style="display: flex;">
										<div style="flex: 1; border : 1px solid #a3a3a3; border-top : 0; border-right : 0; line-height: 50px; background: white;">' . $arr_user_info[$idx]["username"] . '</div>
										<div style="flex: 1; border : 1px solid #a3a3a3; border-top : 0; border-right : 0; line-height: 50px; background: white;">' . $arr_user_info[$idx]["ip_addr"] . '</div>
										<div style="flex: 1; border : 1px solid #a3a3a3; border-top : 0; border-right : 0; line-height: 50px; background: white; min-width: 240px;">' . date("Y/m/d H:i:s", $arr_user_info[$idx]["timeout"]) . '</div>
										<div style="flex: 1; border : 1px solid #a3a3a3; border-top : 0; background: white; line-height: 50px;"><div id="div_login_form_submit" name="div_user_info_' . $arr_user_info[$idx]["session_id"] . '" style="width: 100px; ">' . Common\Lang\STR_LIMIT_LOGIN_DISCONNECT . '</div></div>
									</div>
							';
						}
						?>
						</div>

					</div>

				</div>
			</div>
		</div>

		<!-- Footer -->
		<div id="div_footer">
			<div id="div_footer_shell">
				<span id="span_footer_language">
					<img class="img_icon_banner" src="<?=Common\Def\PATH_IMG_ICON_LANGUAGE ?>" />
					<?=$commonFunc->getEnvInfoLanguageName() ?>
					<br />
				</span>
				<div id="div_footer_language_list">
					<?=$commonFunc->getEnvInfoLanguageList() ?>
				</div>

				Copyright (c) <?=$commonFunc->getEnvInfoMadeYear() ?> <a class="copyright_link" href=<?=$commonFunc->getEnvInfoHomepageURL() ?> target="_new"> <?=$commonFunc->getEnvCompanyName()  ?> </a> All rights reserved.
			</div>
		</div>
	</body>
</html>

<script type="text/javascript">
	function makeArgs(_key, _value) {
		var args = "&" + _key + "=" + _value;

		return args;
	}

	function postArgs(_target, _args) {
		var result;

		$.ajax({
			type	: "POST",
			url		: _target,
			data	: _args,
			async	: false,
			success	: function(data) {
				if( data != null ) {
					result = data;
				}
			}
		});

		return result;
	}
	function notiServerState(_state) {
		switch( _state ) {
			case <?=Common\Def\STATUS_FORBIDDEN ?> :
				alert('[' + <?=Common\Def\STATUS_FORBIDDEN ?> + '] ' + '<?=Common\Lang\STR_STAT_FORBIDDEN_MSG ?>');
				break;

			case <?=Common\Def\STATUS_NOT_FOUND ?> :
				alert('[' + <?=Common\Def\STATUS_NOT_FOUND ?> + '] ' + '<?=Common\Lang\STR_STAT_NOT_FOUND_MSG ?>');
				break;

			case <?=Common\Def\STATUS_INT_ERROR ?> :
				alert('[' + <?=Common\Def\STATUS_INT_ERROR ?> + '] ' + '<?=Common\Lang\STR_STAT_INT_ERROR_MSG ?>');
				break;

			default :
				// alert('[' + state + '] ' + '<?=Common\Lang\STR_STAT_UNKNOWN_ERR_MSG ?>');
				break;
		}
	}


	function submitedRefresh(_req) {
		if( _req.readyState == <?=Common\Def\READY_STAT_SUCCESS ?> ) {
			if( _req.status == <?=Common\Def\STATUS_SUCCESS ?> ) {
				location.reload();

			} else {
				notiServerState(_req.status);
			}
		}
	}

	$("[name^=div_user_info_]").click(function() {
		var arr_name = $(this).attr("name").split("_");
		var session_id = arr_name[arr_name.length - 1];

		if( !confirm("<?=Common\Lang\STR_LIMIT_LOGIN_CONFIRM_DISCN ?>") ) {
			return ;
		}

		var submitParams = "";
		submitParams += makeArgs("type", 		"deny_login");
		submitParams += makeArgs("session_id",  session_id);

		var rc = postArgs("/deny_login.php", submitParams);

		if( rc ) {
			window.location.href = "http://<?php echo $_SERVER["HTTP_HOST"] ?>";

		} else {
			alert("<?=Common\Lang\STR_LIMIT_LOGIN_ALERT_REFRESH ?>");
		}
		return ;
	});

	$("a[id^=a_language_]").click(function() {
		var currentLangSet = $("#span_footer_language").text().trim();

		if( $(this).html() != currentLangSet ) {
			var thisId = $(this).attr("id");
			var langType = thisId.split("_")[2];

			var submitArgs = makeArgs("language", langType);
			postArgs("/deny_login.php", submitArgs);

		} else {
			$("#div_footer_language_list").hide();
		}
	});
</script>
<?php include "common/common_js.php"; ?>
