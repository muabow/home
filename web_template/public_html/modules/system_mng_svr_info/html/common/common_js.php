<script type="text/javascript">
	$(document).ready(function() {
		var systemFunc = new SystemFunc();
		var systemLog  = new CommonLogFunc("system_mng_svr_info");
		var logMsg;

		$("[name=radio_svr_select]").click(function() {
			var index = $(this).attr("value");

			submitParams = "";
			submitParams += systemFunc.makeArgs("type",				"system");
			submitParams += systemFunc.makeArgs("act",  			"set_svr_list");
			submitParams += systemFunc.makeArgs("svr_id",  			$("#div_svr_id_" + index).html().trim());

			systemFunc.postArgs("<?=System_mng_svr_info\Def\PATH_SYSTEM_PROCESS ?>", submitParams);

			return ;
		})

		$("#checkbox_select_all").click(function() {
			var stat = false;
			if( this.checked ) {
				stat = true;
			}

			$(':checkbox').each(function() {
				this.checked = stat;
			});
		});

		$("[id^=checkbox_svr_]").click(function() {
			$("#checkbox_select_all").prop("checked", false);

			return ;
		});

		$("#div_buttonApply").click(function() {
			var arrList = new Array();
			var logList = new Array();
			var arr_ip_list = new Array();

			$(':checkbox').each(function() {
				if( this.checked ) {
					if($(this).attr("id") == "checkbox_select_all" ) return ;

					var arrId = $(this).attr("id").split("_");
					var index = arrId[arrId.length - 1];

					arrList.push("'" + $("#div_svr_id_" + index).html().trim() + "'");
					logList.push(" " + $("#div_svr_id_" + index).html().trim() + "(" + $("#div_svr_ip_" + index).html().trim() + ")");
					
					arr_ip_list.push($("#div_svr_ip_" + index).html().trim());
				}
			});

			if( arrList.length == 0 ) {
				alert("<?=System_mng_svr_info\Lang\STR_SYSTEM_NOT_SELECT ?>");

				return ;
			}

			if( !confirm("<?=System_mng_svr_info\Lang\STR_SYSTEM_CONFIRM_REMOVE ?>") ) {

				return ;
			}

			submitParams = "";
			submitParams += systemFunc.makeArgs("type",				"system");
			submitParams += systemFunc.makeArgs("act",  			"remove_svr_list");
			submitParams += systemFunc.makeArgs("svr_id",  			arrList.toString());
			
			systemFunc.postArgs("<?=System_mng_svr_info\Def\PATH_SYSTEM_PROCESS ?>", submitParams);


			for( idx = 0 ; idx < arr_ip_list.length ; idx++ ) {
				svr_ip_addr = arr_ip_list[idx];

				submitParams = "";
				submitParams += systemFunc.makeArgs("type",				"open_api");
				submitParams += systemFunc.makeArgs("act",  			"unset_master_key");
				submitParams += systemFunc.makeArgs("server_addr",  	svr_ip_addr);

				systemFunc.postArgs("<?=System_mng_svr_info\Def\PATH_AJAX_OPEN_API_PROCESS ?>", submitParams);
			}
			
			$("#checkbox_select_all").prop("checked", false);

			LoadSvrList();

			logMsg = logList.toString() + " <?=System_mng_svr_info\Lang\STR_SYSTEM_SERVER_DELETE ?>";
			systemLog.info(logMsg);

			return ;
		});

		function LoadSvrList() {
			var submitParams = "";

			submitParams += systemFunc.makeArgs("type",				"system");
			submitParams += systemFunc.makeArgs("act",  			"get_svr_list");

			var list = systemFunc.postArgs("<?=System_mng_svr_info\Def\PATH_SYSTEM_PROCESS ?>", submitParams);

			$("#divSvrList").empty();
			$("#divSvrList").append(list);


		}
	});

	class SystemFunc {
		constructor() {		}

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

	} // end of SystemFunc()

</script>

<?php
	include_once 'common_js_etc.php';
?>