<script type="text/javascript">
$(document).ready(function() {
	$("#select_tools").change(function() {
		var exec_id = "div_exec_" + $(this).val();

		$("[id^=div_exec_]").hide();
		$("[id=" + exec_id + "]").show();

		set_select_id($(this).val());

		return ;
	});

	function get_select_id() {
		var cookies = document.cookie.split(";");

		for( var i in cookies ) {
        	if( cookies[i].search("dev_exec_id") != -1 ) {
        		var select_id = $.trim(cookies[i].replace("dev_exec_id" + "=", ""));
        	}
		}

		var select_id = ((select_id == undefined || select_id == "null") ? "module_info" : select_id);
		$("#select_tools").val(select_id);

		return ;
	}

	function set_select_id(_exec_id) {
		var date = new Date();
		date.setDate(date.getDate() + 7);

		var tab_cookie = "";
		tab_cookie += "dev_exec_id=" + _exec_id + ";";
		tab_cookie += "Expires=" + date.toUTCString();
		document.cookie = tab_cookie;

		get_select_id();
	}

	get_select_id();
	$("#select_tools").trigger("change");
});

</script>
