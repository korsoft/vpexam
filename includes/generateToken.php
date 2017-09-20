<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

parse_str($_SERVER['QUERY_STRING']);

header('Content-Type: application/json');
echo(json_encode(generateToken($mysqli, $id)));
exit();