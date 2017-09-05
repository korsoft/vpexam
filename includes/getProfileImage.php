<?php
include_once 'functions.php';

sec_session_start();

const USER_TYPE_PATIENT   = 1;
const USER_TYPE_PHYSICIAN = 2;

const BASE_PATH           = '/var/www/.uploads/profile/';
const BASE_PATH_PATIENTS  = BASE_PATH . 'patients/img/';
const BASE_PATH_PHYS      = BASE_PATH . 'physicians/img/';
const DEFAULT_PROFILE_PIC = BASE_PATH . 'no_profile.jpg';

$filename   = DEFAULT_PROFILE_PIC;
$_GET['id'] = isset($_GET['id'])?$_GET['id']:$_SESSION['user_id'];
if (isset($_GET['id']) && isset($_GET['type'])) {
    $userType = $_GET['type'];
    if (intval($userType) === USER_TYPE_PATIENT) {
        if (file_exists(BASE_PATH_PATIENTS . "{$_GET['id']}.png")) {
            $filename = BASE_PATH_PATIENTS . "{$_GET['id']}.png";
        }
    }
    else if (intval($userType) === USER_TYPE_PHYSICIAN) {
        if (file_exists(BASE_PATH_PHYS . "{$_GET['id']}.png")) {
            $filename = BASE_PATH_PHYS . "{$_GET['id']}.png";
        }
    }
}
header('Content-Type: image/png');
header('Content-Length: ' . filesize($filename));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
readfile($filename);
