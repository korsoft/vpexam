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
    <?php if($browser['browser']=='Chrome' && intval($browser['browser_version'])>=61 && isset($ispatient)) {   ?>
            <link rel="stylesheet" type="text/css" href="/js/fancybox/jquery.fancybox.css" media="screen" />
            <link rel="stylesheet" type="text/css" href="/style/video_chat.css?time=<?php echo time() ?>" />
            <input type="hidden" id="caller"  value='<?php echo $caller ?>' />
            <input type="hidden" id="calling" value='<?php echo isset($calling)?json_encode($calling):"" ?>' />
            <div id="videochatChrome" class="hide" >
                <div class="custom-videoChrome" id="custom-videoChrome">
                    <video id="localVideo" autoplay ></video>   
                    <div id="mycontrols">
                        <button id="playpause" class="icon-play"></button>
                        <div class="seeker">
                            <progress id="progressbar" class="progressbar" max="100" value="0"></progress>
                            <input type="range" id="seekbar" value="0" class="seekbar">
                        </div>
                        <button id="mute" class="icon-volume-2"></button>
                        <input type="range" id="volumebar" class="volumebar" value="1" min="0" max="1" step="0.1">
                        <button id="fullscreen" class="icon-fullscreen-alt"></button>
                    </div>
                    <div class="buttons">
                        <div id="answerButton" class="disabled" title="Accept"></div>
                        <div id="declineButton" class="disabled" title="End Call"></div>
                    </div>
                </div>
            </div>      
    <?php } else {?>
            <link rel="stylesheet" type="text/css" href="/js/fancybox/jquery.fancybox.css" media="screen" />
            <link rel="stylesheet" type="text/css" href="/style/video_chat.css?time=<?php echo time() ?>" />
            <input type="hidden" id="caller"  value='<?php echo $caller ?>' />
            <input type="hidden" id="calling" value='<?php echo isset($calling)?json_encode($calling):"" ?>' />
            
            <a href="#videochat" id="chat" class="hide"><img src="/img/video-camera.png" alt="Video chat" /></a>
            <div id="videochat" class="hide">
                <div class="custom-video" id="custom-video">
                    <video id="remoteVideo" autoplay  class="hide"></video>
                    <video id="localVideo" autoplay></video>
                    <div id="mycontrols">
                        <button id="playpause" class="icon-play"></button>
                        <div class="seeker">
                            <progress id="progressbar" class="progressbar" max="100" value="0"></progress>
                            <input type="range" id="seekbar" value="0" class="seekbar">
                        </div>
                        <button id="mute" class="icon-volume-2"></button>
                        <input type="range" id="volumebar" class="volumebar" value="1" min="0" max="1" step="0.1">
                        <button id="fullscreen" class="icon-fullscreen-alt"></button>
                    </div>
                    <div class="buttons">
                        <div id="answerButton" class="disabled" title="Accept"></div>
                        <div id="declineButton" class="disabled" title="End Call"></div>
                    </div>
                </div>
            </div>
    <?php } ?>
    <?php if($browser['browser']=='Chrome' && intval($browser['browser_version'])>=61 && isset($ispatient)) {  ?>
        <script type="text/javascript" src="https://webrtc.github.io/adapter/adapter-latest.js"></script>
        <script type="text/javascript" src="/js/fancybox/jquery.fancybox.pack.js"></script>
        <script type="text/javascript" src="/js/video_chat_chrome.js?time=<?php echo time(); ?>"></script>    
        <script type="text/javascript" src="/js/customvideo.js"></script>        
    <?php } else {?>
        <script type="text/javascript" src="https://webrtc.github.io/adapter/adapter-latest.js"></script>
        <script type="text/javascript" src="/js/fancybox/jquery.fancybox.pack.js"></script>
        <script type="text/javascript" src="/js/video_chat.js?time=<?php echo time(); ?>"></script>    
        <script type="text/javascript" src="/js/customvideo.js"></script>
    <?php } ?>
<script type="text/javascript">
    var is_patient = <?php echo isset($ispatient)?'true':'false'; ?>;
</script>