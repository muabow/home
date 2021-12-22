<?php
	include_once "common_define.php";
	include_once "common_script.php";

	$sysFunc = new Common\Func\CommonSystemFunc();

	$code = 0;
	$fileElementName = 'file';

	if (empty($_FILES[$fileElementName]['tmp_name']) || $_FILES[$fileElementName]['tmp_name'] == 'none') {
		$code = -1;
		$msg = "No file was uploaded";

	} else {
		$filePath = "/tmp/" . $_FILES[$fileElementName]['name'];
		move_uploaded_file($_FILES[$fileElementName]['tmp_name'], $filePath);

		$sysFunc->execute('"/opt/interm/bin/upgrade.sh ' . $filePath . '"');

		$envData = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/env.json"));
		if (file_exists($envData -> info -> auth_key)) {
			$code = 0;
			$msg = "authentication success";

		} else {
			$code = -10;
			$msg = "authentication failed";
		}
	}

	echo '{"code":' . $code . ', "msg":"' . $msg . '"}';

	return;
?>