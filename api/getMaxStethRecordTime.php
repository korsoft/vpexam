<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

$success  = false;
$errorMsg = '';
$time     = -1;
try {
    if (!isset($_GET['physId'])) {
        throw 'One or more required parameters was not set.';
    }
    $response = getMaxStethRecordTime($mysqli, $_GET['physId']);
    $success  = true;
    if($response['success'] && $response['data']['max_steth_record_time']) {
        $time = $response['data']['max_steth_record_time'];
    }
}
catch(Exception $e) {
    $errorMsg = $e->getMessage();
}
echo(json_encode(['success' => $success, 'errorMsg' => $errorMsg, 'time' => $time]));
exit();