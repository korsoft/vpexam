<?php
include_once 'db_connect.php';
include_once 'functions.php';

sec_session_start();

$response = [
    'success'  => true, 
    'errorMsg' => ''
];

try {
    if(empty($_POST['id'])) {
       throw new Exception('Invalid physician id. ', 1);
    }
    $navigator = get_browser(null, true);
	$browser   = [
		'os' => [
			'name'    => $navigator['platform'],
	 		'version' => $navigator['platform_version'],
 		],
 		'browser' => $navigator['parent'],
	];
    $mysqli->query("CALL dbcode.sp_store_stream_error({$_POST['id']}, '{$_POST['msg']}', '" . json_encode($browser) . "', '{$_POST['devices']}');");
}
catch(Exception $e) {
    $response['success']  = false;
    $response['errorMsg'] = $e->getMessage(); 
}
echo(json_encode($response));
exit();
