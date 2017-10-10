<?php 
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

$response = ['success' => false, 'errorMsg' => ''];

   try {
        if (!isset($_GET['physId'])) {
            throw new Exception('One or more required parameters was not set.', 1);
        }
        $physId   = $_GET['physId'];

        $allComponents=getPhysicianSelectedExamComponents($physId, $mysqli);
        
        $response['success']        = true;
        $response['examComponents'] = $allComponents;
    }
    catch(Exception $e) {
       echo  $response['errorMsg'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo(json_encode($response,JSON_NUMERIC_CHECK));
    exit();