<script type="text/javascript">
	$(document).ready(function() {
		var modeFunc  = new ModeFunc();
		var systemLog = new CommonLogFunc("mode_management");

		$("#div_buttonApply").click(function() {
			var idx	     = $("[name=radio_mode_select]:checked").val();
			var modeName = $("#div_mode_name_" + idx).html().trim();

			if( !confirm("<?=Mode_management\Lang\STR_MODE_CONFIRM_MSG ?>") ) {
				return ;
			}

			submitParams = "";
			submitParams += modeFunc.makeArgs("type",			"mode_management");
			submitParams += modeFunc.makeArgs("act",  			"set_mode_list");
			submitParams += modeFunc.makeArgs("mode_name", 		modeName);

			var rc = modeFunc.postArgs("<?=Mode_management\Def\PATH_MODE_PROCESS ?>", submitParams);

			var noti;
			switch( rc ) {
				case "-1": noti = "<?=Mode_management\Lang\STR_MODE_CONFIRM_WRONG   ?>"; 	break;
				case  "0": noti = "<?=Mode_management\Lang\STR_MODE_CONFIRM_ALREADY ?>";	break;
				case  "1": noti = "<?=Mode_management\Lang\STR_MODE_CONFIRM_SUCCESS ?>";	break;
				default  : noti = "<?=Mode_management\Lang\STR_MODE_CONFIRM_WRONG   ?>"; 	break;
			}

			alert(noti);
			location.reload();

			return ;
		});
	});

	class ModeFunc {
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

	} // end of modeFunc()

</script>

<?php
	include_once 'common_js_etc.php';
?>