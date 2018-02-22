<?php 
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

$response = ['success' => false, 'errorMsg' => ''];

try {
    if (!isset($_GET['query'])) {
        throw new Exception('One or more required parameters was not set.', 1);
    }
    
    $response['success'] = true;
    $response['results'] = searchPatients($_GET['query'], $mysqli);
}
catch(Exception $e) {
   echo  $response['errorMsg'] = $e->getMessage();
}

header('Content-Type: application/json');
echo(json_encode($response,JSON_NUMERIC_CHECK));
exit();