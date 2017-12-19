<?php
/**
 * Script que se usa para obtener un json con los pacientes en la sala de espera
 *
 * PHP version 5
 *
 * @category  GetPatientsFromWaitingRoom
 * @package   Api
 * @author    Francisco Eliseo Navarro Lara <francisco.navarro@korsoftcorp.com>
 * @copyright 2017 Korsoft Corp All Rights Reserved
 * @link      .
 */
include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/db_connect.php';
include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/functions.php';
/**
 * Script que se usa para obtener un json con los pacientes en la sala de espera
 *
 * PHP version 5
 *
 * @category  GetPatientsFromWaitingRoom
 * @package   Api
 * @author    Francisco Eliseo Navarro Lara <francisco.navarro@korsoftcorp.com>
 * @copyright 2017 Korsoft Corp All Rights Reserved
 * @link      .
 */
$numPhysicianId = intval( empty($_REQUEST['physician_id'])?0:$_REQUEST['physician_id']);
header('Content-Type: application/json');
$arrPatiencts = array(
                 'patients' => getPatientsFromWaitingRoom($mysqli, $numPhysicianId)
                );
error_log('********** getPatientsFromWaitingRoom php:: '.json_encode($arrPatiencts));
echo json_encode($arrPatiencts);
