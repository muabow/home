<?php
/************************************
 * Javascript 구간
 * 언어팩, 상수, 경로 등 메크로를 사용하기 위해서 PHP 내에 script로 작성합니다.
 ************************************/
?>

<script type="text/javascript">
	$(document).ready(function() {
		// Log form resize
		$("#div_log_form").resizable({
			minWidth: 938,
			maxWidth: 938,
			minHeight: 400

		}).resize(function() {
			var reSize = (parseInt($("#div_log_form").css("height")) - 54);
			$("#div_log_frame").css("height", reSize + "px");

			return ;
		});

		// Log form prev
		$("#div_log_prev").click(function() {
			$("#select_log_scrollMode").val("manual");
			$("#select_log_scrollMode").trigger("change");

			logFunc.ScrollToTop();

			return ;
		});

		// Log form more
		$("#div_log_more").click(function() {
			logFunc.ScrollToBottom();

			return;
		});

		// Log & Line Select
		$("#select_log_displayType, #select_log_displayLine").change(function() {
			var logType = $("#select_log_displayType option:selected").val();
			var logLine = $("#select_log_displayLine option:selected").val();
			
			var log = logFunc.getLogContents(logType, -1, logLine, "log");
			log = log.replace(/<![^>]*>/g, "");
			log = log.replace(/<html>(.*?)<\/html>/s, "-");
			log = log.replace(/[\n\r]+/g, "");

			var logContents = JSON.parse(log);

			logFunc.lastestModule = logType;
			logFunc.lastestIndex  = logContents.index;

			$("#div_log_frame").html(logContents.log);

			logFunc.ScrollSetBottom();

			return ;
		});

		// Update Mode select
		$("#select_log_updateMode").change(function() {
			var type = $("#select_log_updateMode option:selected").val();

			if( type == "enabled" ) {
				logFunc.updateTimer = setInterval(function() {
					var logType = $("#select_log_displayType option:selected").val();

					if( logFunc.lastestModule == logType ) {
						var log = logFunc.getLogContents(logType, logFunc.lastestIndex, null, "update");
						log = log.replace(/<![^>]*>/g, "");
						log = log.replace(/<html>(.*?)<\/html>/s, "-");
						log = log.replace(/[\n\r]+/g, "");

						var logContents = JSON.parse(log);

						if( logContents.log == "" ) return ;

						logFunc.lastestIndex = logContents.index;

						$("#div_log_frame").append(logContents.log);

						if( logFunc.scrollMode == true ) {
							logFunc.ScrollToBottom();
						}

					} else {
						logFunc.lastestModule = logType;
					}
				}, logFunc.updateTime);

				$("#select_log_updateTime").attr("disabled", false);

			} else {
				clearInterval(logFunc.updateTimer);
				$("#select_log_updateTime").attr("disabled", true);

			}

			logFunc.setLogStat("update", type);

			return ;
		});

		// Update Time select
		$("#select_log_updateTime").change(function() {
			var time = $("#select_log_updateTime option:selected").val();

			logFunc.updateTime = time;

			clearInterval(logFunc.updateTimer);
			$("#select_log_updateMode").val("<?=$logFunc->getLogStatInfo("update"); ?>").trigger("change");

			logFunc.setLogStat("time", time);

			return ;
		});

		// Scroll Mode select
		$("#select_log_scrollMode").change(function() {
			var type = $("#select_log_scrollMode option:selected").val();

			if( type == "auto" ) {
				logFunc.scrollMode = true;

			} else {
				logFunc.scrollMode = false;
			}

			logFunc.setLogStat("scroll", type);

			return ;
		});

		// Log Download
		$("#div_log_download").click(function() {
			var logType = $("#select_log_displayType option:selected").val();
			var doc = document.body.appendChild(document.createElement("a"));

			doc.download = logType + ".html";
			doc.href = 'data:text/html;charset=UTF-8,' + encodeURIComponent($("#div_log_frame").html());
			doc.click();

			return ;
		});

		// log Remove
		$("#div_log_remove").click(function() {
			if( !confirm("<?=Log_viewer\Lang\STR_LOG_ALERT_REMOVE ?>") ) {
				return ;
			}

			var moduleName = $("#select_log_displayType").val().split("/")[1];
			var commonLogFunc = new CommonLogFunc(moduleName);

			commonLogFunc.clearLog();

			location.reload();
			return ;
		});

		var logFunc = new LogViewerFunc();

		// Document ready
		$("#select_log_displayType").val("<?=$logFunc->getLogStatInfo("module"); ?>").trigger("change");
		// $("#select_log_displayLine").val("<?=$logFunc->getLogStatInfo("line"); ?>").trigger("change");
		$("#select_log_scrollMode").val("<?=$logFunc->getLogStatInfo("scroll"); ?>").trigger("change");
		$("#select_log_updateMode").val("<?=$logFunc->getLogStatInfo("update"); ?>").trigger("change");
		$("#select_log_updateTime").val("<?=$logFunc->getLogStatInfo("time"); ?>").trigger("change");

		if( $("#select_log_displayType").val() == null ) {
			$("#select_log_displayType").val("common/system").trigger("change");
		}
		return ;
	});


	class LogViewerFunc {

		constructor() {
			this.logPath = "<?=Log_viewer\Def\PATH_AJAX_LOG_PROCESS ?>";
			this.lastestModule = "";
			this.lastestIndex  = 0;
			this.scrollMode    = true;
			this.updateTimer   = null;
			this.updateTime    = <?=$logFunc->getLogStatInfo("time"); ?>;
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

		getLogContents(_logType, _curIdx, _line, _type) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("type",   "log_view");
			submitArgs	+= this.makeArgs("act",    _type);
			submitArgs	+= this.makeArgs("module", _logType);
			submitArgs	+= this.makeArgs("index",  _curIdx);
			submitArgs	+= this.makeArgs("line",   _line);

			return this.postArgs(this.logPath, submitArgs);
		}

		setLogStat(_name, _value) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("type",   "log_view");
			submitArgs	+= this.makeArgs("act",    "stat");
			submitArgs	+= this.makeArgs(_name,   _value);

			return this.postArgs(this.logPath, submitArgs);
		}

		ScrollToTop() {
			$("#div_log_frame").animate({scrollTop: 0},'slow');
		}

		ScrollToBottom() {
			var frameHeight = parseInt($("#div_log_frame").prop("scrollHeight"));
			$("#div_log_frame").animate({scrollTop: frameHeight},'slow');
		}

		ScrollSetBottom() {
			var frameHeight = parseInt($("#div_log_frame").prop("scrollHeight"));
			$("#div_log_frame").scrollTop(frameHeight);
		}
	} // end of LogViewerFunc()

</script>

<?php
	include_once 'common_js_etc.php';
?>
