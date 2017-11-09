<?php 
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

$response = ['success' => false, 'errorMsg' => ''];

   try {
        if (!isset($_GET['physId']) || !preg_match("/^[0-9]+$/",$_GET['physId'])) {
            throw new Exception('One or more required parameters was not set.', 1);
        }
        else
        {
            $physId   = $_GET['physId'];

            $allComponents=getPhysicianSelectedExamComponents($physId, $mysqli);

            foreach ($allComponents as $value) {
                $strFolder="/var/www/.uploads/exam/components/$value->id/"; 
                $value->male=(file_exists($strFolder . 'male'))?'image/gif':((file_exists($strFolder . "male_video"))?'video/mp4':'');
                $value->female=(file_exists($strFolder . 'female'))?'image/gif':((file_exists($strFolder . "female_video"))?'video/mp4':'');  
            }
            $response['success']        = true;
            $response['examComponents'] = $allComponents;
        }
    }
    catch(Exception $e) {
       echo  $response['errorMsg'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo(json_encode($response,JSON_NUMERIC_CHECK));
    exit();