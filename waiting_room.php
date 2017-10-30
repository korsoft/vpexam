<?php
    include_once 'includes/db_connect.php';
    include_once 'includes/functions.php';

    try {
        //Obtenemos el parametro de physician con el username del doctor
        $physicianusername = !empty($_GET['physician'])?$_GET['physician']:''; 
        //Si no trae valor, redireccionamos al main
        if('' == $physicianusername) {
            throw new Exception('WAITING ROOM :: Parameter physician is not set.', 1);
        }
        //Obtenemos la info del physician segun el parametro recibido
        $result    = getPhysicianInfoByUsername($mysqli, $physicianusername);
        $physician = true == $result['success']?$result['data']:false;
        
        if(!is_array($physician)) {
            throw new Exception("Physician not found { $physicianusername }. ", 2);
        }
        //Variable para el video_chat.php
        $calling = $physician;
        //Obtenemos la session
        sec_session_start();
        //Generamos el arreglo con los datos del paciente
        $patient = ['id' => 0, 'name' => ''];
        if(!empty($_SESSION['user_id']) && !empty($_SESSION['is_patient'])) {
            $patient['id']   = $_SESSION['user_id'];
            $patient['name'] = $_SESSION['first_name'];
        }
        //Definimos que es paciente
        $ispatient = true;
        
        //Detectamos el el navegador, version y SO.
        $navigator = get_browser(null, true);
        $browser   = [
            'browser'    => $navigator['browser'],
            'browser_version' => $navigator['majorver'],
            'platform' => $navigator['platform_description']
        ];        
        
        
    }
    catch(Exception $e) {
        header('Location: /main.php');
        exit();
    }
?>
<html>
    <head>
        <meta name="Content-Type" content="application/xhtml+xml; charset=utf-8" />
        <meta name="msapplication-TileColor" content="#2d89ef">
        <meta name="msapplication-TileImage" content="/mstile-144x144.png">

        <meta name="description" content="Waiting room">
        <meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1, maximum-scale=1">
        <meta itemprop="description" content="Waiting room">
        <meta itemprop="image" content="../../../images/webrtc-icon-192x192.png">
        <meta itemprop="name" content="VPExam - Waiting room of <?php echo $physician['name']; ?>">
        <meta name="mobile-web-app-capable" content="yes">
        <meta id="theme-color" name="theme-color" content="#ffffff">

        <base target="_blank">

        <title>VPExam - Waiting room of <?php echo $physician['name']; ?></title>

        <link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="/apple-touch-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="/apple-touch-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-180x180.png">
        <link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32">
        <link rel="icon" type="image/png" href="/android-chrome-192x192.png" sizes="192x192">
        <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96">
        <link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">
        <link rel="stylesheet" type="text/css" href="/style/style.css" />
        <link rel="stylesheet" type="text/css" href="/style/waiting_room.css" />
        <link rel="stylesheet" type="text/css" href="/style/sweetalert.css" />
        <link rel="manifest" href="/manifest.json" />
    </head>
    <body>
        <div id="header">
            <div id="logo">
                <div id="logo_text"><img src="img/logo_img.png" /></div>
                <div class="welcomeDiv">
                    Waiting room of <?php echo $physician['name']; ?>
                </div>
            </div>
        </div>
        <?php if($browser['browser']=='Chrome' && intval($browser['browser_version'])>=61) {   ?>
        <div class="mainChrome" >
            <div id="msg">
                <img src="includes/getProfileImage.php?id=<?php echo $physician['id']; ?>&type=3&time=<?php $var = new DateTime(); echo $var->getTimestamp(); ?>" />
                <h1>Wait until <?php echo $physician['name']; ?> calls you.</h1>
                <h3>Thank you</h3>
            </div>
            <video id="remoteVideoChrome" autoplay class="hide" ></video>
        </div>
        <?php } else {?>
        <div class="main" >
            <div id="msg">
                <img src="includes/getProfileImage.php?id=<?php echo $physician['id']; ?>&type=3&time=<?php $var = new DateTime(); echo $var->getTimestamp(); ?>" />
                <h1>Wait until <?php echo $physician['name']; ?> calls you.</h1>
                <h3>Thank you</h3>
            </div>
        </div>
        <?php } ?>
        <script type="text/javascript" src="https://code.jquery.com/jquery-latest.js"></script>
        <script type="text/javascript" src="/js/easytimer.min.js"></script>
        <script type="text/javascript" src="/js/sweetalert.min.js"></script>
        <script type="text/javascript" src="/js/waiting_room.js"></script>
        <script type="text/javascript">
            WaitingRoom.init(<?php echo json_encode($physician); ?>, <?php echo json_encode($patient); ?>);
        </script>
        <?php include 'video_chat.php';?>
    </body>
</html>

