<?php
    include_once 'includes/db_connect.php';
    include_once 'includes/functions.php';

    sec_session_start();

    $waitingroom = getPatientsFromWaitingRoom($mysqli, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta content="text/html" charset="UTF-8" http-equiv="content-type">
        <meta name="msapplication-TileColor" content="#2d89ef">
        <meta name="msapplication-TileImage" content="/mstile-144x144.png">
        <meta name="theme-color" content="#ffffff">
        <meta http-equiv="cleartype" content="on">
        <meta name="MobileOptimized" content="320">
        <meta name="HandheldFriendly" content="True">
        <meta name="HandheldFriendly" content="True">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <title>Patient Search</title>
        <link rel="stylesheet" type="text/css" href="style/search.css">
        <link rel="stylesheet" type="text/css" href="style/sortable-theme-minimal.css">
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
        <link rel="stylesheet" type="text/css" href="/style/sweetalert.css" />
        <link rel="manifest" href="/manifest.json">
    </head>
    <body>
        <?php if ((login_check($mysqli) == true) && ($_SESSION['is_patient'] == false)) { ?>
            <nav id="menu">
                <header>
                    <div class="headerDiv">
                        <img class="menuHeaderImg" src="img/logo_img_no_text.png" height="50">
                        <span class="menuHeaderText">VPExam</span>
                    </div>
                    <?php if(0 < count($waitingroom)) { ?>
                        <div class="btn waitingRoom" data-id="<?php echo $_SESSION['user_id']; ?>">
                            Waiting Room
                            <ul>
                                <?php
                                    foreach ($waitingroom as $patient) {
                                        echo "<li id=\"menu-li-patient{$patient['id']}\" data-id=\"{$patient['id']}\"><img src=\"/img/remove.png\" alt=\"X\" class=\"removeFromWR\" /><label>{$patient['name']}</label></li>";
                                    }
                                ?>
                            </ul>
                        </div>
                    <?php } ?>
                    <div class="btn" id="btnMenuMyPatients">My Patients</div>
                    <div class="btn" id="btnMenuSearch">Search</div>
                    <div class="btn" id="btnMenuSettings">My Account Settings</div>
                    <div class="btn" id="btnMenuLogout">Logout</div>
                </header>
            </nav>
            <main id="panel">
                <header>
                    <img class="toggle-button" src="img/menu_white.png" width="40">
                    <span class="headerText">SEARCH</span>
                    <div class="welcomeDiv">
                        Welcome, <?php echo($_SESSION['first_name']); ?>!
                    </div>
                </header>
                <div class="mainContent">
                    <div class="searchInstr">
                        <span>Search by name, date of birth, or MRN combinations.</span>
                    </div>
                    <div class="searchBoxDiv">
                        <input class="holo-block" id="searchInput" type="text">
                        <div class="searchResultsDiv">

                        </div>
                    </div>
                </div>
            </main>
        <?php } else { ?>
            <p><span class="error">You are not authorized to access this page.</span> Please <a href="/main.php">login</a>.</p>
        <?php } ?>
        <script type="text/javascript" src="https://code.jquery.com/jquery-latest.js"></script>
        <script type="text/javascript" src="https://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        <script type="text/javascript" src="/js/search.js"></script>
        <script type="text/javascript" src="/js/slideout.min.js"></script>
        <script type="text/javascript" src="/js/sortable.js"></script>
        <script type="text/javascript" src="/js/jquery.paging.js"></script>
        <script type="text/javascript" src="/js/sweetalert.min.js"></script>
        <script type="text/javascript" src="/js/waiting_room.js"></script>
        <script type="text/javascript" >
            setPhysicianId(<?php echo($_SESSION['user_id']); ?>);
        </script>
        <?php include 'video_chat.php';?>
    </body>
</html>
