<?php
	$load_default  		= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/default.json");
	$defaultData		= json_decode($load_default);

	$status_ssh  = (int)exec_read_shell("systemctl status ssh | grep Active | grep running | wc -l");
	$status_port = (int)exec_read_shell("cat /opt/interm/conf/haproxy.cfg | grep bind | awk '{print $2}' | sed 's/*://g'");

	function getLanguageList() {
		$selected = "";

		$load_envData  		= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/env.json");
		$envData   			= json_decode($load_envData);
		$env_langSet		= $envData->info->language_set;
		$env_langPack		= $envData->language_pack;

		global	$defaultData;

		foreach( $env_langPack as $type => $lang ) {
			if( $type == $defaultData->language->type ) {
				$selected = "selected";
			}

			echo "<option value=\"" . $type . "\" " . $selected .">" . $lang->name . "</option>";
			$selected = "";
		}

		return ;
	}

	function getLanguageListToggle() {
		global	$defaultData;

		if( $defaultData->language->use == "enabled" ) {
			return "system-div_switch_back_on_off";

		} else {
			return "";
		}
	}

	function getLanguageListOperation() {
		global	$defaultData;

		if( $defaultData->language->use == "enabled" ) {
			return "system-div_switch_background";

		} else {
			return "";
		}
	}

	function getServiceSSHToggle() {
		global $status_ssh;

		if( $status_ssh == 1 ) {
			return "system-div_switch_back_on_off";

		} else {
			return "";
		}

	}

	function getServiceSSHOperation() {
		global $status_ssh;

		if( $status_ssh == 1 ) {
			return "system-div_switch_background";

		} else {
			return "";
		}
	}

	function getSystemPort($_type) {
		global	$status_port;

		return $status_port;
	}



	function exec_read_shell($_cmd) {
		$fp = popen($_cmd, "r");

		$readMsg = "";
		while( !feof($fp) ) {
			$buffer = fgets($fp, 4096);
			$readMsg .= $buffer . "<br />";
		}
		pclose($fp);

		return $readMsg;
	}

	function setCountryCode() {
		$code = array(
			"kor" => "한국어",
			"eng" => "English",
			"fr" => "Français",
			"ru" => "русский",
			"nl" => "Nederlands",
			"dk" => "dansk",
			"de" => "Deutsch",
			"es" => "Español",
			"jp" => "日本",
			"tr" => "Türk"
		);
		return $code;
	}

	function getLanguageInfo() {
		$load_envData  		= file_get_contents("/opt/interm/conf/env.json");
		$envData   			= json_decode($load_envData);
		$env_set			= "";

		foreach ($envData->language_pack as $key => $value) {
			$list[] = $key;
		}

		$directory = "/opt/interm/public_html/language_packs/";

		$files = glob($directory . "*");

		foreach($files as $key => $value) {
				$file_name = basename($value);
				$file_name = explode("_", $file_name)[1];
				$file_name = explode(".", $file_name)[0];
			if ( !in_array($file_name, $list) ) {
				$list[] = $file_name;
			}
		}

		foreach( $list as $file_name ) {
			if( !is_dir($file_name) ) {
				if ( isset($envData->language_pack->$file_name) ) {
					$lang_info = $envData->language_pack->$file_name;

					if ( $file_name == "kor" || $file_name == "eng" ) {
						$env_set .=
						'<div class="system-div_table_row">
							<div class="system-div_enable_check">
								<div class="system-div_button_blur"></div>
								<div class="system-div_system_toggle system-div_switch_background" id="toggle_langList">
									<span class="system-span_system_check_on"> ON </span>
									<span class="system-span_system_check_off"> OFF </span>
									<div class="system-div_system_check_toggle system-div_switch_back_on_off" id="check_langList"></div>
								</div>
							</div>
							<div class="system-div_file_name">
								' . $file_name . '
							</div>
							<div class="system-div_language_name">
								<input class="system-table_input_text" type="text" value="' . $lang_info->name . '" disabled>
							</div>
							<div class="system-div_homepage">
								<input class="system-table_input_text" type="text" value="' . $lang_info->homepage . '" disabled>
							</div>
						</div>';
					} else {
						$env_set .=
						'<div class="system-div_table_row">
							<div class="system-div_enable_check">
								<div class="system-div_system_toggle system-div_switch_background" id="toggle_langList">
									<span class="system-span_system_check_on"> ON </span>
									<span class="system-span_system_check_off"> OFF </span>
									<div class="system-div_system_check_toggle system-div_switch_back_on_off" id="check_langList"></div>
								</div>
							</div>
							<div class="system-div_file_name">
								' . $file_name . '
							</div>
							<div class="system-div_language_name">
								<input class="system-table_input_text" type="text" value="' . $lang_info->name . '" disabled>
							</div>
							<div class="system-div_homepage">
								<input class="system-table_input_text" type="text" value="' . $lang_info->homepage . '" disabled>
							</div>
						</div>';
					}
				} else {
					$country = setCountryCode();

					$env_set .=
					'<div class="system-div_table_row">
						<div class="system-div_enable_check">
							<div class="system-div_system_toggle" id="toggle_langList">
								<span class="system-span_system_check_on"> ON </span>
								<span class="system-span_system_check_off"> OFF </span>
								<div class="system-div_system_check_toggle" id="check_langList"></div>
							</div>
						</div>
						<div class="system-div_file_name">
							' . $file_name . '
						</div>
						<div class="system-div_language_name">
							<input class="system-table_input_text" type="text" value="' . $country[$file_name] . '">
						</div>
						<div class="system-div_homepage">
							<input class="system-table_input_text" type="text" value="http://www.inter-m.net">
						</div>
					</div>';
				}
			}
		}

		return $env_set;
	}

	if( isset($_POST["type"]) && $_POST["type"] == "exec" ) {
		$cmd = $_POST["cmd"];

		echo exec_read_shell($cmd);

		exit ;
	}

	if( isset($_POST["type"]) && $_POST["type"] == "get_portList" ) {
		$port = $_POST["port"];

		// ftp, ssh, audio, serial232/422, contact,
		$arrUsedPort = [20, 21, 22, 5454, 5455, 5456, 5457];
		if( in_array($port, $arrUsedPort) ) {
			echo 1;
			exit;
		}

		// running process
		$cmd = "netstat -antp | grep LISTEN | awk '{print $4}'";
		$rc  = exec_read_shell($cmd);

		$arrRc = explode("\n", $rc);

		for( $idx = 0 ; $arrRc[$idx] ; $idx++ ) {
			$arrRc[$idx] = explode(":", $arrRc[$idx])[1];
		}

		$arrRc = array_filter($arrRc);
		if( in_array($port, $arrRc) ) {
			echo 1;
			exit;
		}

		echo 0;
		exit ;
	}

	if( isset($_POST["type"]) && $_POST["type"] == "change_port" ) {
		$port = $_POST["port"];

		exec_read_shell("sudo sed -i 's/\<bind.*/bind *:" . $port . "/g' /opt/interm/conf/haproxy.cfg");
  		exec_read_shell("ps -ef | grep haproxy | grep -v monitor | grep -v grep | awk {'print $2'} | xargs sudo kill");

		echo 0;
		exit;
	}


	if( isset($_POST["type"]) && $_POST["type"] == "change_service" ) {
		$name = $_POST["name"];
		$stat = $_POST["stat"];


		switch( $name ) {
			case "ssh" :
				if( $stat == "disabled" ) {
					exec_read_shell("sudo systemctl stop ssh");
		  			exec_read_shell("sudo systemctl disable ssh");

				} else {
		  			exec_read_shell("sudo systemctl enable ssh");
					exec_read_shell("sudo systemctl start ssh");
				}

				break;

			case "language" :
				$load_envData  		= file_get_contents("/opt/interm/conf/default.json");
				$envData   			= json_decode($load_envData);
				$envData->language->use = $stat;

				file_put_contents("/opt/interm/conf/default.json", json_encode($envData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
				break;
		}

		echo 0;
		exit;
	}

	if( isset($_POST["type"]) && $_POST["type"] == "change_language" ) {
		$stat = $_POST["stat"];

		$load_envData  		= file_get_contents("/opt/interm/conf/default.json");
		$envData   			= json_decode($load_envData);

		$envData->language->type = $stat;

		file_put_contents("/opt/interm/conf/default.json", json_encode($envData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

		echo 0;
		exit;
	}

	if( isset($_POST["type"]) && $_POST["type"] == "change_language_list" ) {
		$info = json_decode($_POST["info"]);

		$defaultEnv = json_decode(file_get_contents("/opt/interm/conf/default.json"));
		$envData  	= json_decode(file_get_contents("/opt/interm/conf/env.json"));

		foreach($info as $val) {
			$language_pack[$val->code] = array(
				"name" => $val->name,
				"path" => "language_packs/langpack_" . $val->code . ".php",
				"homepage" => $val->homepage
			);
			$returnVal[$val->code] = array("name" => $val->name, "isDefault" => "false");
		}

		$flag = false;
		$type = $defaultEnv->language->type;
		if ( isset($returnVal[$type]) ) {
			$returnVal[$type]["isDefault"] = "true";
		} else {
			$flag = true;
		}

		if ( $flag ) {
			$defaultEnv->language->type = "eng";
			$returnVal["eng"]["isDefault"] = "true";
		}

		$language_set = $envData->info->language_set;
		if ( !isset($language_pack[$language_set]) ) {
			$envData->info->language_set = "eng";
		}

		$envData->language_pack = $language_pack;
		file_put_contents("/opt/interm/conf/env.json", json_encode($envData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

		foreach($language_pack as $key => $val) {
			$defaultLanguageList[] = array($key => $val);
		}

		$defaultEnv->language_pack = $defaultLanguageList;
		file_put_contents("/opt/interm/conf/default.json", json_encode($defaultEnv, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

		echo json_encode($returnVal);
		exit;
	}
?>

<script type="text/javascript">
	class SystemFunc {

		constructor() {
			this.path = "http://<?php echo $_SERVER["HTTP_HOST"]; ?>/modules/factory_tool/html/script/exec_lang_pack.php";
		}

		makeArgs(_key, _value) {
			var args = "&" + _key + "=" + _value;

			return args;
		}

		postArgs(_target, _args) {
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

		exec(_cmd) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("type",   "exec");
			submitArgs	+= this.makeArgs("cmd",    _cmd);

			return this.postArgs(this.path, submitArgs);
		}

		checkPort(_port) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("type",   "get_portList");
			submitArgs	+= this.makeArgs("port",   _port);

			return this.postArgs(this.path, submitArgs);
		}

		changePort(_port) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("type",   "change_port");
			submitArgs	+= this.makeArgs("port",   _port);

			return this.postArgs(this.path, submitArgs);
		}

		changeService(_name, _stat) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("type",   "change_service");
			submitArgs	+= this.makeArgs("name",   _name);
			submitArgs	+= this.makeArgs("stat",   _stat);

			return this.postArgs(this.path, submitArgs);
		}

		changeLanguage(_stat) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("type",   "change_language");
			submitArgs	+= this.makeArgs("stat",   _stat);

			return this.postArgs(this.path, submitArgs);
		}

		changeLanguageList(_info) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("type",   "change_language_list");
			submitArgs	+= this.makeArgs("info",   _info);

			return this.postArgs(this.path, submitArgs);
		}
	}

	$(document).ready(function() {
		var systemFunc = new SystemFunc();

		// 0. textbox script
		var placeholderTarget = $('.system-textbox input[type="text"]');

		placeholderTarget.on('focus', function() {
			$(this).siblings('label').fadeOut('fast');
		});

		placeholderTarget.on('focusout', function() {
			if( $(this).val() == '' ) {
				$(this).siblings('label').fadeIn('fast');
			}
		});

		placeholderTarget.on('textchange', function() {
			$(this).siblings('label').fadeOut('fast');
		});


		$("#div_buttonApplyPort").click(function() {
			var port = $("#text_system_port_web").val();

			if( !$.isNumeric(port) || port <= 0 || port >= 65536 ) {
				alert("0~65536 사이 숫자로된 포트만 사용 가능합니다.");

				return ;
			}

			if( systemFunc.checkPort(port) == 1 ) {
				alert("이미 사용중인 포트입니다. 다른 포트를 사용하세요.");

				return ;
			}

			if( !confirm("(경고) 포트 변경은 시스템 동작에 큰 영향을 끼칩니다.\n그래도 변경 하시겠습니까?") ) {
				return ;
			}

			systemFunc.changePort(port);
			alert("포트가 변경 되었습니다.\n변경된 포트로 다시 접속하세요.");

			return ;
		});

		$("[id^=toggle_").click(function() {
			var stat = "disabled";
			var arrName = $(this).attr("id").split("_");
			var id = arrName[arrName.length - 1];

			if( id == "langList" ) {
				var table_row 	  = $(this).closest(".system-div_table_row");
				var language_name = table_row.find(".system-div_language_name input").val();
				var homepage	  = table_row.find(".system-div_homepage input").val();

				if ( language_name == "" ) {
					alert("언어명을 입력해주세요.");
					return;
				}

				if ( homepage == "" ) {
					alert("홈페이지를 입력해주세요.");
					return;
				}

			}

			if( !confirm("(경고) 서비스 설정 변경은 시스템 동작에 큰 영향을 끼칩니다.\n그래도 변경 하시겠습니까?") ) {
				return ;
			}

			$(this).find(".system-div_system_check_toggle").toggleClass("system-div_switch_back_on_off", 0, "easeOutSine");
			$(this).toggleClass("system-div_switch_background", 0, "easeOutSine");

			if( $(this).hasClass("system-div_switch_background") == true ) {
				stat = "enabled";
			}

			if ( id == "langList") {
				var lang_info = [];

				$(".system-div_table").find(".system-div_table_row").each(function (i, e) {
					if( $(e).find(".system-div_system_toggle").hasClass("system-div_switch_background") == true ) {
						var code 	 = $.trim($(e).find(".system-div_file_name").html());
						var name 	 = $.trim($(e).find(".system-div_language_name input").val());
						var homepage = $.trim($(e).find(".system-div_homepage input").val());

						lang_info.push({
							"code" : code,
							"name" : name,
							"homepage" : homepage
						});
					}
				});
				lang_info = JSON.stringify(lang_info);
				var result = systemFunc.changeLanguageList(lang_info);
				result = JSON.parse(result);

				$("#select_system_language").empty();
				var defaultLanguage = "eng";
				$.each(result, function(code, e) {
					$("#select_system_language").append('<option value="' + code + '">' + e.name + '</option>');
					if (e.isDefault == "true") {
						defaultLanguage = code;
					}
				});
				$("#select_system_language").val(defaultLanguage).prop("selected", true);
				$("#select_system_language").trigger("change");

				if ( stat == "enabled" ) {
					$(this).closest(".system-div_table_row").find("input").attr("disabled", true);
				} else {
					$(this).closest(".system-div_table_row").find("input").attr("disabled", false);
				}

				location.reload(true);
				return;
			} else {
				systemFunc.changeService(id, stat);
			}

			if( id == "language" ) {
				alert("언어 초기값 설정은 시스템 초기화 시 적용됩니다.");
			}

			return ;
		});

		$("#select_system_language").change(function() {
			systemFunc.changeLanguage($(this).val());

			return ;
		});

		$( "#sortable" ).sortable({
			items: ".system-div_table_row:not(.system-div_table_title)",
			update : function () {
				var lang_info = [];

				$(".system-div_table").find(".system-div_table_row").each(function (i, e) {
					if( $(e).find(".system-div_system_toggle").hasClass("system-div_switch_background") == true ) {
						var code 	 = $.trim($(e).find(".system-div_file_name").html());
						var name 	 = $.trim($(e).find(".system-div_language_name input").val());
						var homepage = $.trim($(e).find(".system-div_homepage input").val());

						lang_info.push({
							"code" : code,
							"name" : name,
							"homepage" : homepage
						});
					}
				});
				lang_info = JSON.stringify(lang_info);
				var result = systemFunc.changeLanguageList(lang_info);
			}
		});
	});
</script>


<div id="system-div_page_title_name" name="lang_pack"> 언어팩 설정 (Language pack setting) </div>

<hr class="title-hr" style="width: 890px"/>

<div id="system-div_contents_table">
	<div class="system-div_contents_cell">
		<div class="system-div_contents_cell_line">
			<div class="system-div_contents_cell_title">
				언어팩 목록
			</div>

			<div class="system-div_contents_cell_contents">
				<div class="system-div_table">
					<div class="system-div_table_title system-div_table_row">
						<div class="system-div_enable_check">활성</div>
						<div class="system-div_file_name">파일명</div>
						<div class="system-div_language_name">언어명</div>
						<div class="system-div_homepage">홈페이지</div>
					</div>
					<div class="div_table_content" id="sortable">
						<?=getLanguageInfo() ?>
					</div>
				</div>
			</div>
		</div>

		<div class="system-div_contents_cell_line">
			<div class="system-div_contents_cell_title">
				초기값 설정
			</div>

			<div class="system-div_contents_cell_contents">
				<div class="system-div_system_toggle <?=getLanguageListOperation() ?>" id="toggle_language">
					<span class="system-span_system_check_on"> ON </span>
					<span class="system-span_system_check_off"> OFF </span>
					<div class="system-div_system_check_toggle <?=getLanguageListToggle() ?>" id="check_language"></div>
				</div>
				<select class="system-select_system_list" id="select_system_language">
					<?=getLanguageList() ?>
				</select>
			</div>
		</div>

		<div class="system-div_contents_cell_line"></div>
		<span style="padding-left: 10px; font-weight: bold; font-size: 13px;"> * [환경설정 - 시스템 관리] 메뉴의 [시스템 초기화]를 진행해야 변경된 초기값이 반영 됩니다.</span>
	</div>
</div>
