<?php
/**
 * Script que tiene el header de las paginas de physician
 *
 * PHP version 5
 *
 * @category  Header_physician
 * @package   Includes
 * @author    Francisco Eliseo Navarro Lara <francisco.navarro@korsoftcorp.com>
 * @copyright 2017 Korsoft Corp All Rights Reserved
 * @link      .
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <meta name="Content-Type" content="application/xhtml+xml; charset=utf-8" />
        <meta name="msapplication-TileColor" content="#2d89ef">
        <meta name="msapplication-TileImage" content="/mstile-144x144.png">
        <meta name="description" content="VPExam - Physician">
        <meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1, maximum-scale=1">
        <meta itemprop="description" content="VPExam - Physician">
        <meta itemprop="image" content="/images/webrtc-icon-192x192.png">
        <meta itemprop="name" content="VPExam - Physician">
        <meta name="mobile-web-app-capable" content="yes">
        <meta id="theme-color" name="theme-color" content="#ffffff">
        <base target="_blank">
        <title><?php echo $_strPageTitle; ?></title>
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
        <?php 
            foreach( $_arrStyles as $strStyle ){
                echo '<link rel="stylesheet" type="text/css" href="' . $strStyle . '" />';
            }
        ?>
        <link rel="manifest" href="/manifest.json" />
    </head>
    <body>
        <nav id="menu">
            <header>
                <div class="headerDiv">
                    <img class="menuHeaderImg" src="img/logo_img_no_text.png" height="50">
                    <span class="menuHeaderText">VPExam</span>
                </div>
                <?php if($_blnShowPatientMenu){ ?>
                    <div class="btn" id="btnMenuPatientOverview">Patient Overview</div>
                    <div class="btn" id="btnMenuPatientDem">Patient Demographics</div>
                <?php } ?>
                <?php if($_blnShowMenu){ ?>
                    <a href="/physician_main.php" target="_self"><div class="btn" id="btnMenuMyPatients">My Patients</div></a>
                    <div class="btn" id="btnMenuSearch"><a href='/search.php' target="_self">Search</a></div>
                    <div class="btn" id="btnMenuSettings"><a href='/physician_settings.php' target="_self">My Account Settings</a></div>
                    <div class="btn" id="btnMenuLogout"><a href='/logout.php' target="_self">Logout</a></div>
                <?php } ?>
            </header>
        </nav>
        <main id="panel">
            <header>
                <img class="toggle-button" src="img/menu_white.png" width="40">
                <div class="welcomeDiv">
                    Welcome, <?php echo($_SESSION['first_name']); ?>!
                </div>
                <div class="button_content">
                    <?php if($_blnShowWaitingRoom){ ?>
                    <a class='button_waiting_room' href="/physician_waiting_room.php" target='_self'>Waiting Room</a>
                    <?php }  ?>
                </div>
                <div class="submenu">
                    <span class="headerText"><?php echo $_strHeaderTitle; ?>
                    </span>
                </div>
            </header>
