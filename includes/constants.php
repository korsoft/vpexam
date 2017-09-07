<?php
/**
 * Script que tiene constantes que se utlizan en el header y footer de paginas de physician
 *
 * PHP version 5
 *
 * @category  Constants
 * @package   Includes
 * @author    Francisco Eliseo Navarro Lara <francisco.navarro@korsoftcorp.com>
 * @copyright 2017 Korsoft Corp All Rights Reserved
 * @link      .
 */
include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/db_connect.php';
include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/functions.php';
$_strPageTitle       = '';
$_strHeaderTitle     = '';
$_blnShowPatientMenu = false;
$_blnShowMenu        = true;
$_blnShowWaitingRoom = true;
$_arrStyles          = array(
                        '/style/physician_header_link.css',
                        '/style/sweetalert.css',
                       );
$_arrScripts         = array(
                        'https://code.jquery.com/jquery-latest.js',
                        '/js/slideout.min.js',
                        '/js/sweetalert.min.js',
                       );

if ( !( (login_check($mysqli) == true) && ($_SESSION['is_patient'] == false) )) { 
    $_arrStyles[]        = '/style/physician_main.css';
    $_arrScripts[]       = '/js/physician_main.js';
    $_blnShowWaitingRoom = false;
    $_blnShowMenu        = false;
    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/header_physician.php';
    ?>
    <p><span class="error">You are not authorized to access this page.</span> Please <a href="/main.php" target="_self">login</a>.</p>
    <?php
    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/footer_physician.php';
    exit();
}
