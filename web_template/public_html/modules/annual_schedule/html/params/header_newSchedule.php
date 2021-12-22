<?php
	$current_year = date('Y');

	function getDateFormat() {
		$DATE_FORMAT_FILE = '/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/date_format.xml';
		if (file_exists($DATE_FORMAT_FILE) == false) {
			file_put_contents($DATE_FORMAT_FILE,'<?xml version="1.0"?><option><dateformat>YYYY-MM-DD</dateformat></option>');
		} else {
		}
		$xmlDoc = simplexml_load_file($DATE_FORMAT_FILE, 'SimpleXMLElement', LIBXML_NOCDATA);
		$dateformat = "";
		if (isset($xmlDoc->dateformat))
			$dateformat = $xmlDoc->dateformat;

		if (strlen($dateformat) == 10)
			return $dateformat;
		else
			return strlen($dateformat);
	}
?>
<head>

	<script language="javascript" src="modules/annual_schedule/html/js/jquery_last.js"></script>
	<script language="javascript" src="modules/annual_schedule/html/js/jquery.filestyle.js"></script>
	<script language="javascript" src="modules/annual_schedule/html/js/ajaxfileupload.js"></script>
	<script language="javascript" src="modules/annual_schedule/html/js/common_request.js"></script>

<style>
.ui-dialog {
  position: fixed !important;
}
iframe {
	overflow: hidden;
}
</style>
	<script src="modules/annual_schedule/html/params/newSchedule/js/jquery-1.8.3.min.js"></script>
	<script src="modules/annual_schedule/html/params/newSchedule/js/jquery.menu.js?ver=161020"></script>
	<script src="modules/annual_schedule/html/params/newSchedule/js/common.js?ver=161020"></script>
	<script src="modules/annual_schedule/html/params/newSchedule/js/wrest.js?ver=161020"></script>

<script>

	var DATE_FORMAT = "<?=getDateFormat()?>";
	var DATE_FICKER_FORMAT = "yy-mm-dd";

	switch (DATE_FORMAT) {
		case "YYYY-MM-DD" :
			DATE_FICKER_FORMAT = "yy-mm-dd";
			break;
		case "MM-DD-YYYY" :
			DATE_FICKER_FORMAT = "mm-dd-yy";
			break;
		case "DD-MM-YYYY" :
			DATE_FICKER_FORMAT = "dd-mm-yy";
			break;
	};


$( function() {

	$( "#copymonthly_dialog" ).dialog({

		autoOpen: false,
		height: 500,

	});

	$( "#delete_schedule_dialog" ).dialog({

		modal: true,
		autoOpen: false,
		height: 240,

	});

	$("#dialog-message").dialog({
		modal: true,
		autoOpen: false,
		height: 600,
		width: 420,
	});

	$("#year_view").dialog({  //create dialog, but keep it closed
	    autoOpen: false,
	    height: 750,
	    width: 860,
	    modal: true,
	    open: function (event, ui) {
			$('#year_view').css('overflow', 'hidden'); //this line does the actual hiding
		}
	});
});

</script>
</head>
