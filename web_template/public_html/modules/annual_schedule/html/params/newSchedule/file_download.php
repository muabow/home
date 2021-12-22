<?php
$filepath = '/opt/interm/public_html/modules/annual_schedule/html/params/newSchedule/info_schedule.xml';
$filesize = filesize($filepath);
$path_parts = pathinfo($filepath);
$filename = $path_parts['basename'];
$extension = $path_parts['extension'];

header("Pragma: public");
header("Expires: 0");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $filesize");

ob_clean();
flush();
readfile($filepath);
?>
