<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

$response = ['success' => false, 'errorMsg' => ''];

try {
    if (empty($_POST['physId']) || 0 > $_POST['physId']) {
        throw new Exception('Invalid physId param.', 1);
    }
    if (empty($_POST['ecString'])) {
        throw new Exception('Invalid ecString param.', 1);
    }

    $mysqli->query("CALL sp_set_physician_exam_components({$_POST['physId']}, '{$_POST['ecString']}');");
    
    $response['success'] = true;
}
catch(Exception $e) {
    $response['errorMsg'] = $e->getMessage();
}
header('Content-Type: application/json');
echo(json_encode($response));
exit();