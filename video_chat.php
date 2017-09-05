<?php
    $caller = isset($_SESSION['user_id'])?"{\"id\":{$_SESSION['user_id']},\"name\":\"{$_SESSION['first_name']} {$_SESSION['last_name']}\"}":'';
?>
<link rel="stylesheet" type="text/css" href="/js/fancybox/jquery.fancybox.css" media="screen" />
<link rel="stylesheet" type="text/css" href="/style/video_chat.css?time=<?php echo time() ?>" />
<input type="hidden" id="caller"  value='<?php echo $caller ?>' />
<input type="hidden" id="calling" value='<?php echo isset($calling)?json_encode($calling):"" ?>' />
<a href="#videochat" id="chat" class="hide"><img src="/img/video-camera.png" alt="Video chat" /></a>
<div id="videochat" class="hide">
    <video id="remoteVideo" autoplay class="hide"></video>
    <video id="localVideo" autoplay muted></video>
    <div class="buttons">
        <div id="answerButton" class="disabled" title="Pick up the phone"></div>
        <div id="declineButton" class="disabled" title="Hangout the call"></div>
    </div>
</div>
<script type="text/javascript" src="https://webrtc.github.io/adapter/adapter-latest.js"></script>
<script type="text/javascript" src="/js/fancybox/jquery.fancybox.pack.js"></script>
<script type="text/javascript" src="/js/video_chat.js?time=<?php echo time(); ?>"></script>
<script type="text/javascript">
    var is_patient = <?php echo isset($ispatient)?'true':'false'; ?>;
</script>

