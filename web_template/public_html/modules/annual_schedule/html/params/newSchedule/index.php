<?php
	$current_year = date('Y');



?>

<div id="year_view" style="display:none;"><iframe id="yearIframe" src="modules/annual_schedule/html/params/newSchedule/bootstrap/index.php" frameborder="0" scrolling="no" marginwidth="0" marginheight="0" frameborder="0" style="width:100%;height:100%;"></iframe></div>

<div id="copymonthly_dialog" title="<?php echo Annual_schedule\Lang\SIBO_COPY_STR?>" style="display:none;">
	<table cellpadding="0" cellspacing="0" border="0" height="50">
	  <tr>
	    <td><h4><?php echo Annual_schedule\Lang\SIBO_CHOOSE_FROM?></h4></td>
	  </tr>
	</table>
	<table cellpadding="0" cellspacing="0" border="0" height="50">
	  <tr>
	    <td>
		  <select id="cm1_year" name="cm1_year">
			<option value="<?php echo $current_year; ?>" selected="selected"><?php echo $current_year; ?></option>
			<?php
			for($i = $current_year+1;$i < $current_year+5;$i++) {
			?>
			<option value="<?=$i?>"><?=$i?></option>
			<?php
			}
			?>
		  </select><?php echo Annual_schedule\Lang\SIBO_DISPLAY_YEAR?>
		</td>
	    <td>
		  <select id="cm1_month" name="cm1_month">
			<?php
			for($j = 1;$j <= 12;$j++) {
			?>
			<option value="<?=$j?>"><?=$j?></option>
			<?php
			}
			?>
		  </select><?php echo Annual_schedule\Lang\SIBO_DISPLAY_MONTH?>
		</td>
	  </tr>
	</table>
	<table cellpadding="0" cellspacing="0" border="0" height="30">
	  <tr>
	    <td valign="top">--------------------------------</td>
	  </tr>
	</table>
	<table cellpadding="0" cellspacing="0" border="0" height="50">
	  <tr>
	    <td><h4><?php echo Annual_schedule\Lang\SIBO_CHOOSE_TO?></h4></td>
	  </tr>
	</table>
	<table cellpadding="0" cellspacing="0" border="0" height="50">
	  <tr>
	    <td>
		  <select id="cm2_year" name="cm2_year">
			<option value="<?php echo $current_year; ?>" selected="selected"><?php echo $current_year; ?></option>
			<?php
			for($i = $current_year+1;$i < $current_year+5;$i++) {
			?>
			<option value="<?=$i?>"><?=$i?></option>
			<?php
			}
			?>
		  </select><?php echo Annual_schedule\Lang\SIBO_DISPLAY_YEAR?>
		</td>
	    <td>
		  <select id="cm2_month" name="cm2_month">
			<?php
			for($j = 1;$j <= 12;$j++) {
			?>
			<option value="<?=$j?>"><?=$j?></option>
			<?php
			}
			?>
		  </select><?php echo Annual_schedule\Lang\SIBO_DISPLAY_MONTH?>
		</td>
	  </tr>
	</table>
	<table cellpadding="0" cellspacing="0" border="0" height="50">
	  <tr>
	    <td><button onclick="copy_monthly();return false;"><?php echo Annual_schedule\Lang\SIBO_EXECUTE?></button></td>
	    <td width="40">&nbsp;</td>
	    <td><button onclick="copy_close();return false;"><?php echo Annual_schedule\Lang\SIBO_CLOSE?></button></td>
	  </tr>
	</table>
</div>

<div style="width:<?php echo $width?>; margin:0 auto;">
<?php
include_once("/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/fw_calendar.php");

?>
</div>

<div id="delete_schedule_dialog" title="<?php echo Annual_schedule\Lang\SIBO_DELETE_STR?>" style="display:none;">
	<table cellpadding="0" cellspacing="0" border="0" height="50">
	  <tr>
	    <td><h4><?php echo Annual_schedule\Lang\DELETE_RECUR_SCHEDULE_STR?></h4></td> 
	  </tr>
	</table>
	<table cellpadding="0" cellspacing="0" border="0" height="50">
	  <tr>
	    <td>
				<input type="radio" name="radio-1" id="this_schedule" checked>
		</td>
		<td>
				<label for="this_schedule"><?php echo Annual_schedule\Lang\DELETE_THIS_SCHEDULE_STR?></label>
		</td>
		</tr>
		<tr>
			<td>
				<input type="radio" name="radio-1" id="all_schedule">
		</td>
		<td>
				<label for="all_schedule"><?php echo Annual_schedule\Lang\DELETE_ALL_SCHEDULE_STR?></label>
		</td>
	  </tr>
	</table>
	<table cellpadding="0" cellspacing="0" border="0" height="50" style="width:100%">
	  <tr>
			<td width="100%" align="right"><button id="delete_schedule_cancel_button" ><?php echo Annual_schedule\Lang\CANCEL_STR?></button>
			&nbsp;<button id="delete_schedule_button" ><?php echo Annual_schedule\Lang\OK_STR?></button></td>
	  </tr>
	</table>
</div>

<form name="upload_form" id="upload_form" enctype="multipart/form-data" action="modules/annual_schedule/html/params/newSchedule/fw_events.php?action=XMLUpload" method="POST">
<input type="hidden" name="MAX_FILE_SIZE" value="30000" />
<input id="userfile" name="userfile" type="file" style="display:none;" />
</form>

<script>
$("#userfile").bind('change', function() {
	var file = $("#userfile").val();

	if (file != "") {
		var fileExt = file.substring(file.lastIndexOf(".") + 1);
		var reg = /xml/i; // 업로드 가능 확장자

		if (reg.test(fileExt) == false) {
			alert("Upload XML files only.");
			return;
		}
	}

	$("#upload_form").submit();
});

function copy_monthly() {
	var cm1_year = $("#cm1_year").val();
	var cm1_month = $("#cm1_month").val();
	var cm2_year = $("#cm2_year").val();
	var cm2_month = $("#cm2_month").val();

	location.href=('modules/annual_schedule/html/params/newSchedule/fw_events.php?action=copySchedule&cm1_year='+cm1_year+'&cm1_month='+cm1_month+'&cm2_year='+cm2_year+'&cm2_month='+cm2_month);

}

function copy_close() {
	$("#copymonthly_dialog").dialog("close");
}

</script>
