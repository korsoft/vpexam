<?php
    $caller = isset($_SESSION['user_id'])?"{\"id\":{$_SESSION['user_id']},\"name\":\"{$_SESSION['first_name']} {$_SESSION['last_name']}\"}":'';
?>
<?php $navigator = get_browser(null, true);
    $browser   = [
        'browser'    => $navigator['browser'],
        'browser_version' => $navigator['majorver'],
        'platform' => $navigator['platform_description']
    ];
   
    ?>
    <?php if(isset($ispatient)) {   ?>
            <link rel="stylesheet" type="text/css" href="/js/fancybox/jquery.fancybox.css" media="screen" />
            <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

            <link rel="stylesheet" type="text/css" href="/style/video_chat.css?time=<?php echo time() ?>" />
            <input type="hidden" id="physician_id"  value='<?php echo $physician['id']; ?>' />
            <input type="hidden" id="patient_access"  value='<?php echo $patient_access ?>' />
            <input type="hidden" id="caller"  value='<?php echo $caller ?>' />
            <input type="hidden" id="calling" value='<?php echo isset($calling)?json_encode($calling):"" ?>' />
            <div id="divPhysicianStatus">
                <span id="spPhysicianStatusText">Your physician is&nbsp;</span>
                <span id="spPhysicianStatus">OFFLINE</span>
            </div>
            <div id="videochatPatient" class="hide" >
                <div class="custom-videoPatient" id="custom-videoPatient">
                    <video id="remoteVideoPatient" <?PHP if(!empty($navigator['device_name']) && $navigator['device_name']=='iPhone'){ ?>controls="false" webkit-playsinline="true" playsinline="true"  <?PHP } ?> autoplay class="hide" ></video>
                    <video id="localVideo" <?PHP if(!empty($navigator['device_name']) && $navigator['device_name']=='iPhone'){ ?>controls="false" webkit-playsinline="true" playsinline="true"  <?PHP } ?> autoplay muted></video>   
                    <div id="mycontrols" class="hide">
                        <button id="playpause" class="icon-play"></button>
                        <div class="seeker">
                            <progress id="progressbar" class="progressbar" max="100" value="0"></progress>
                            <input type="range" id="seekbar" value="0" class="seekbar">
                        </div>
                        <button id="mute" class="icon-volume-2"></button>
                        <input type="range" id="volumebar" class="volumebar" value="1" min="0" max="1" step="0.1">
                        <button id="fullscreen" class="icon-fullscreen-alt" ></button>
                    </div>
                    <div class="buttons">
                        <div id="answerButton" class="disabled" title="Accept"></div>
                        <div id="declineButton" class="disabled" title="End Call"></div>
                    </div>
                </div>
            </div>   
            <div id="divSidenav" class="dvsidenav" >
                <div id="divTool">
                    <img id="imgTool"  src="images/tool.png"/>
                </div>  
                <a href="javascript: void(0);"  id="lnBandwidth"><img id="imgbandwidth" src="images/wifi.png" class="normal"/>&nbsp;&nbsp;Bandwidth</a>
              <a href="#" onclick="return false;"><img id="imgCamera" src="images/webcam.png"/>&nbsp;&nbsp;Camera</a>
              <a href="#" onclick="return false;"><img id="imgMic"   src="images/micro.png"/>&nbsp;&nbsp;Microphone</a>
              <a href="javascript: void(0);"  id="lnSound"><img id="imgSound"  src="images/sound.png"/>&nbsp;&nbsp;Sound</a>
            </div>            
            <div id="modalSoundTest"  style="display: none" ></div>
            <div id="messageSoundTest" style="display: none">
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
            <div id="divReconnect" class="divReconnect" >Reconnecting, please wait!</div>
    <?php } else {?>
            <link rel="stylesheet" type="text/css" href="/js/fancybox/jquery.fancybox.css" media="screen" />
            <link rel="stylesheet" type="text/css" href="/style/video_chat.css?time=<?php echo time() ?>" />
            <input type="hidden" id="caller"  value='<?php echo $caller ?>' />
            <input type="hidden" id="calling" value='<?php echo isset($calling)?json_encode($calling):"" ?>' />
            
            <a href="#videochat" id="chat" class="hide"><img src="/img/video-camera.png" alt="Video chat" /></a>
            <div id="videochat" class="hide">
                <div class="custom-video" id="custom-video">
                    <video id="remoteVideo" <?PHP if(!empty($navigator['device_name']) && $navigator['device_name']=='iPhone'){ ?>controls="false" webkit-playsinline="true" playsinline="true"  <?PHP } ?> autoplay class="hide"></video>
                    <video id="localVideo"  <?PHP if(!empty($navigator['device_name']) && $navigator['device_name']=='iPhone'){ ?>controls="false" webkit-playsinline="true" playsinline="true"  <?PHP } ?> autoplay muted></video>
                    <div id="mycontrols">
                        <button id="playpause" class="icon-play"></button>
                        <div class="seeker">
                            <progress id="progressbar" class="progressbar" max="100" value="0"></progress>
                            <input type="range" id="seekbar" value="0" class="seekbar">
                        </div>
                        <button id="mute" class="icon-volume-2" <?PHP if(!empty($navigator['device_name']) && $navigator['device_name']=='iPhone'){ ?>style="display: none" <?PHP } ?>></button>
                        <input type="range" id="volumebar" class="volumebar" value="1" min="0" max="1" step="0.1">
                        <button id="fullscreen" class="icon-fullscreen-alt" <?PHP if(!empty($navigator['device_name']) && $navigator['device_name']=='iPhone'){ ?>style="display: none" <?PHP } ?>></button>
                    </div>
                    <div class="buttons">
                        <div id="answerButton" class="disabled" title="Accept"></div>
                        <div id="declineButton" class="disabled" title="End Call"></div>
                    </div>
                    <div id="divReconnect" class="divReconnect" >Reconnecting, please wait!</div>                    
                </div>
            </div>
            <div id="modalPhys" style="display: none"></div>
            <div id="divBW" style="display: none"  >
                <img id="imgLoading" src="img/loading.gif" /><br/>Testing your Internet connection.<br/>Please wait!
            </div>            
    <?php } ?>
        <script type="text/javascript" src="/js/bandwidth.js?time=<?php echo time(); ?>"></script> 
        <script type="text/javascript" src="https://webrtc.github.io/adapter/adapter-latest.js"></script>    
        <script type="text/javascript" src="/js/fancybox/jquery.fancybox.pack.js"></script>
        <script type="text/javascript" src="<?php autoVerCss('/js/customvideo.js');?>"></script> 
    <?php if(isset($ispatient)) {  ?>
        <script type="text/javascript" src="/js/video_chat_patient.js?time=<?php echo time(); ?>"></script>    
    <?php } else {?>
        <script type="text/javascript" src="/js/video_chat.js?time=<?php echo time(); ?>"></script>    
    <?php } ?>
<script type="text/javascript">
    var is_patient = <?php echo isset($ispatient)?'true':'false'; ?>;
</script>