<?php
$queryStr = urldecode($_SERVER['QUERY_STRING']);
parse_str($queryStr);
//$fileDir = "/var/uploads/".$physId."/".$patientId."/";
$completeFilename = $path;

if (file_exists($completeFilename)) {
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.basename($completeFilename));
	header('Expires: 0');
	header('Cache-Control: must-revaliadte');
	header('Pragma: public');
	header('Content-Length: '.filesize($completeFilename));
	readfile($completeFilename);
	exit;
}
?>
