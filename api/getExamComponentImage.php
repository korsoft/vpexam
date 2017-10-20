<?php
$filename    = '';
$contenttype = 'image/gif';

try {
    if (empty($_GET['id'])) {
        throw new Exception('Invalid id param.', 1);
    }
    if (empty($_GET['type'])) {
        throw new Exception('Invalid type param.', 2);
    }
    $id = $_GET['id'];
    $gender = $_GET['type'];
    define('BASE_PATH', "/var/www/.uploads/exam/components/{$id}/"); 
    //const BASE_PATH = '/var/www/.uploads/exam/components/';
    if (file_exists(BASE_PATH . $gender)) {
        $filename = BASE_PATH . $gender;
    }
    else if (file_exists(BASE_PATH . "{$gender}_video")) {
        $filename    = BASE_PATH . "{$gender}_video";
        $contenttype = 'video/mp4';
    }
}
catch(Exception $e) {
    $response['errorMsg'] = $e->getMessage();
}
if('' != $filename) {
    header("Content-Type: $contenttype");
    header('Content-Length: ' . filesize($filename));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    readfile($filename);
}
else {
    header('HTTP/1.0 404 Not Found');
}
exit();
