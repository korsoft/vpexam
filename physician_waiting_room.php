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
            <div id="divSidenavPhys" class="dvsidenavPhys" >
                <div id="divToolPhys">
                    <img id="imgToolPhys"  src="images/tool.png"/>
                </div>  
              <a href="javascript: void(0);"  id="lnBandwidth"><img id="imgbandwidth" src="images/bw_black.png"/>&nbsp;&nbsp;Bandwidth</a>
              <a href="#" onclick="return false;"><img id="imgCamera" src="images/camera_black.png"/>&nbsp;&nbsp;Camera</a>
              <a href="#" onclick="return false;"><img id="imgMic"   src="images/mic_black.png"/>&nbsp;&nbsp;&nbsp;Microphone</a>
              <a href="javascript: void(0);" id="lnSound"><img id="imgSound" src="images/audio_icon.png"/>&nbsp;&nbsp;Sound</a>
            </div>
            <div id="modalSoundTest"  class="hide" ></div>
            <div id="messageSoundTest" class="hide">
                <a href="javascript:void(0);" onClick="SoundTest.hide();return false;">X</a>
                <br />
                <br />
                <br />
                <div id="dlgTestSoundDialog" title="Sound Test">
                    <p style="margin: 0 0 1px 0;">Can you hear the sound?</p>
                    <br/>
                    <audio id="audSoundTest" controls="controls" controlsList="nodownload">
                        <source src="sounds/bird_tweet.mp3" />
                    </audio>
                    <br/>
                    <br/>
                    <div class="button-dark" id="btnYes" onclick="fncChangeImg(true);" >Yes</div>&nbsp;
                    <div class="button-dark" id="btnNo"  onclick="fncChangeImg(false);" >No</div>
                    <br/>
                </div>
                <br/>
            </div>
            <div class="PatientsWaitingLoading "></div>
            <div class="PatientsWaitingEmpty Hide">There are no people in the waiting room at this moment. </div>
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