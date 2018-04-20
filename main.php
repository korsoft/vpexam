<?php
    include_once 'includes/db_connect.php';
    include_once 'includes/functions.php';

    sec_session_start();

    $userloggedid = isset($_SESSION['user_id'])?$_SESSION['user_id']:0;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta content="text/html; charset=windows-1252" http-equiv="content-type">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="msapplication-TileColor" content="#2d89ef">
        <meta name="msapplication-TileImage" content="/mstile-144x144.png">
        <meta name="theme-color" content="#ffffff">
        <meta name="description" content="The Virtual Physical Exam platform provides comprehensive, personalized Telemedicine for clinicians and patients.">
        <meta name="keywords" content="physician, doctor, hospital, exam, physical">
        <title>VPExam - Main</title>
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
        <link rel="stylesheet" type="text/css" href="<?php autoVerCss('/style/style.css');?>">
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.0.7/css/swiper.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link rel="manifest" href="/manifest.json">
    </head>
    <body>
        <div id="main">
            <div id="header">
                <div id="logo">
                    <div id="logo_text">
                        <img src="img/logo_img.png" />
                    </div>
                    <div class="topmenu">
                        <!--<span class="topmenuItem split">|</span>-->
                        <div class="topmenuItem hoverable">
                             <a href="/"><span>Home</span></a>
                        </div>
                        <div class="topmenuItem hoverable">
                            <?php if(0 < $userloggedid) { ?>
                                <a href="<?php echo true == $_SESSION['is_patient']?'patient_main':'physician_main'; ?>.php"><span>My Patients</span></a>
                            <?php } else { ?>
                                <span id="btnTopLogin">Login</span>
                            <?php } ?>
                        </div>
                        <div id="sidebarLoginPhysician">
                            <div class="arrow-up"></div>
                            <div>
                                <div class="sidebar_item">
                                    <h3>Login</h3>
                                    <form id="loginForm" class="loginForm" method="post" action="includes/process_login.php">
                                        <label for="user" class="loginW">Username:</label><br>
                                        <input class="login" name="username" id="user" type="text">
                                        <br>
                                        <label for="user" class="loginW">Password:</label><br>
                                        <input class="login" name="password" id="password" type="password">
                                        <br>
                                        <input name="remember" type="checkbox"><label for="remember" class="loginW">Remember
                                        Me</label>
                                        <br>
                                        <div id="btnLogin" name="btnLogin" class="button-dark" value="Login" type="button">
                                            Login
                                        </div>
                                    </form>
                                    <br>
                                </div>
                            </div>
                        </div>
                        <div class="topmenuItem hoverable" id="btnTopContact">
                            <a href="contact.php"><span>Contact Us</span></a>
                        </div>
                    </div>
                    <?php if(isset($_SESSION['first_name'])) { ?>
                        <div class="welcomeDiv">
                            Welcome, <?php echo "{$_SESSION['first_name']} {$_SESSION['last_name']}" ?>!
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div id="content_header"></div>
                <div id="site_content">
                    <div id="centerContent">
                       <div id="myCarousel" class="carousel slide" data-ride="carousel">
                            <!-- Indicators -->
                            <ol class="carousel-indicators">
                              <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
                              <li data-target="#myCarousel" data-slide-to="1"></li>
                              <li data-target="#myCarousel" data-slide-to="2"></li>
                              <li data-target="#myCarousel" data-slide-to="3"></li>
                              <li data-target="#myCarousel" data-slide-to="4"></li>
                              <li data-target="#myCarousel" data-slide-to="5"></li>
                              <li data-target="#myCarousel" data-slide-to="6"></li>
                            </ol>
                            <!-- Wrapper for slides -->
                            <div class="carousel-inner">
                              <div class="item active">
                                <img src="img/slider/VPExam1.jpg" alt="Los Angeles" style="width:100%;">
                                <div class="carousel-caption">
                                  <h3>Your solution to comprehensive personalized telemedicine.</h3>
                                </div>
                              </div>
                              <div class="item">
                                <img src="img/slider/VPExam2.jpg" alt="Chicago" style="width:100%;">
                                <div class="carousel-caption">
                                  <h3>Your solution to comprehensive personalized telemedicine.</h3>
                                </div>
                              </div>
                              <div class="item">
                                <img src="img/slider/VPExam3.jpg" alt="New York" style="width:100%;">
                                <div class="carousel-caption">
                                  <h3>Your solution to comprehensive personalized telemedicine.</h3>
                                </div>
                              </div>
                              <div class="item">
                                <img src="img/slider/VPExam4.jpg" alt="New York" style="width:100%;">
                                <div class="carousel-caption">
                                  <h3>Your solution to comprehensive personalized telemedicine.</h3>
                                </div>
                              </div>
                              <div class="item">
                                <img src="img/slider/VPExam5.jpg" alt="New York" style="width:100%;">
                                <div class="carousel-caption">
                                  <h3>Your solution to comprehensive personalized telemedicine.</h3>
                                </div>
                              </div>
                              <div class="item">
                                <img src="img/slider/VPExam6.jpg" alt="New York" style="width:100%;">
                                <div class="carousel-caption">
                                  <h3>Your solution to comprehensive personalized telemedicine.</h3>
                                </div>
                              </div>
                            </div>

                            <!-- Left and right controls -->
                            <a class="left carousel-control" href="#myCarousel" data-slide="prev">
                              <span class="glyphicon glyphicon-chevron-left"></span>
                              <span class="sr-only">Previous</span>
                            </a>
                            <a class="right carousel-control" href="#myCarousel" data-slide="next">
                              <span class="glyphicon glyphicon-chevron-right"></span>
                              <span class="sr-only">Next</span>
                            </a>
                          </div>
                        </div>
                    <!-- Login Div goes here -->
                    <div class="aboutDiv">
                        <div id="contentAbout">
                            <div class="contenedorAbout">
                                <img src="img/slider/acercaapp.jpg">
                                <div class="info">
                                    <h6>We've made comprehensive telemedicine easy <br >to integrate into your practice.</h6>
                                    <p>Create your personal account, waiting room, and add it to your practice today.​​​​</p>
                                </div>
                            </div>
                            <div class="infoR">
                                <img src="img/slider/laptop-exam-overview.jpg"><br>
                                <a class="watchVideo">Watch Our Video </a>
                                <div id="superplayer"><div id="cerrarYB"><img src="/img/cross.png"></div>
                                    <div id="player">
                                        
                                        <div class="videoContainer"> </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br /><br />

                        <div class="blockDiv">
                            <h1>Are you a physician?</h1>
                            <p>Start your trial today!</p>
                            <br />
                            <div class="createNewAccount" <?php echo (0 == $userloggedid?'id="btnRequestTrial"':'') ?>>Create New Account</div>
                        </div>
                        <div class="descrDiv">
                            <div class="frameYoutube">
                                <!--<iframe class="ytb-embed" src="
                                https://www.youtube.com/embed/PNkMrz056X0?autoplay=1&controls=0&loop=1&showinfo=0&rel=0&playlist=PNkMrz056X0&origin=https://dev.vpexam.com/" width="730" height="390" frameborder="0" allow="autoplay; encrypted-media" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                                -->
                                <div id="player2"></div>
                            </div>
                            <div class="infoY">
                                <h1>VPExam.com is your Telemedicine Portal for Patients and Physicians​​</h1>
                                <ul>
                                    <li>+ Create your personal account, waiting room, and add it to your practice today.</li>
                                    <li>+ Easy accessibility for patients and clinicians.</li>
                                    <li>+ HIPAA Compliant.</li>
                                    <li>+ Access your care from everywhere.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <div id="content_footer"></div>
            <div id="footer">
                <p>Copyright 2015 &#169; TeleHealth Care Solutions</p>
            </div>
        </div>
        <script type="text/javascript" src="https://code.jquery.com/jquery-latest.js"></script>
        <script type="text/javascript" src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.0.7/js/swiper.jquery.min.js"></script>
        <script type="text/javascript" src="js/sha512.js?time=<?php echo time(); ?>"></script>
        <script type="text/javascript" src="<?php autoVerCss('/js/forms.js');?>"></script>
        <script src="https://www.youtube.com/iframe_api"></script>
        <script type="text/javascript" src="js/main.js?time=<?php echo time(); ?>"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    </body>
</html>
