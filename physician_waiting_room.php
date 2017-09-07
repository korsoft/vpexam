<?php
/**
 * Script que se usa para mostrar la pagina de waiting room
 *
 * PHP version 5
 *
 * @category  Physician_waiting_room
 * @package   WEB
 * @author    Francisco Eliseo Navarro Lara <francisco.navarro@korsoftcorp.com>
 * @copyright 2017 Korsoft Corp All Rights Reserved
 * @link      .
 */
    include_once 'includes/db_connect.php';
    include_once 'includes/functions.php';
    sec_session_start();
    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/constants.php';
/**
 * Script que se usa para mostrar la pagina de waiting room
 *
 * PHP version 5
 *
 * @category  Physician_waiting_room
 * @package   WEB
 * @author    Francisco Eliseo Navarro Lara <francisco.navarro@korsoftcorp.com>
 * @copyright 2017 Korsoft Corp All Rights Reserved
 * @link      .
 */

    $_strPageTitle       = 'VPExam - Physician waiting room';
    $_strHeaderTitle     = 'WAITING ROOM';
    $_arrStyles[]        = '/style/physician_main.css';
    $_arrStyles[]        = '/style/single_waiting_room.css';
                         
    $_arrScripts[]       = '/js/physician_main.js';
    $_arrScripts[]       = '/js/waiting_room.js';
    $_arrScripts[]       = '/js/single_waiting_room.js';
    $_blnShowWaitingRoom = false;

    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/header_physician.php';
?>
            <div class="tableHeaderDiv">
                <table class="tableHeader">
                    <thead>
                        <tr>
                            <th class="shortColumn" align='left'>FULL NAME</th>
                            <th class="shortColumn">DOB</th>
                            <th class="longColumn">CONTACT INFO</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="PatientsWaitingLoading "></div>
            <div class="mainContent PatientsWaiting Hide">
                <table class="tableContent">
                    <tbody>
                    </tbody>
                </table>
                <ul>
                </ul>
            </div>
            <script>
                var _numPhysicianId= <?php echo $_SESSION['user_id']; ?>;
            </script>
<?php
    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/footer_physician.php';
    include_once $_SERVER['DOCUMENT_ROOT'] .'/video_chat.php';
