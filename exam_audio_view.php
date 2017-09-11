<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

sec_session_start();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta content="text/html" charset="UTF-8" http-equiv="content-type">
		<link rel="stylesheet" type="text/css" href="style/exam_audio_view.css">
		<link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
		<link rel="stylesheet" type="text/css" href="js/jquery-simple-slider/css/simple-slider.css">
		<link rel="stylesheet" type="text/css" href="js/jquery-simple-slider/css/simple-slider-volume.css">
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
		<link rel="manifest" href="/manifest.json">
		<meta name="msapplication-TileColor" content="#2d89ef">
		<meta name="msapplication-TileImage" content="/mstile-144x144.png">
		<meta name="theme-color" content="#ffffff">
		<script src="https://code.jquery.com/jquery-latest.js"></script>
		<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
		<script src="https://tinymce.cachefly.net/4.1/tinymce.min.js"></script>
		<script src="js/wavesurfer.js/wavesurfer.min.js"></script>
		<script src="js/slideout.min.js"></script>
		<script src="js/jquery-simple-slider/js/simple-slider.js"></script>
		<script src="js/exam_audio_view.js"></script>
		<script src="js/jquery.fullscreen.js"></script>
        <script src="js/jquery.cookie.js"></script>
		<title>Physical Exam Audio</title>
		<style>
			.ui-resizable-s {
				background: #E9E9E9;
				display: table-cell;
				text-align: center;
				vertical-align: middle;
			}
			.ui-dialog {
				font-family: encode-sans, 'Trebuchet MS', Verdana, Arial, Helvetica, sans-serif;
				z-index: 10000000000 !important;
			}
			.ui-widget-header {
				background: #0082d2;
				color: white;
				font-weight: bold;
			}
			.ui-widget-content {
				color: #555;
			}
		</style>
	</head>
	<body>
	<?php if ((login_check($mysqli) == true) && ($_SESSION['is_patient'] == false)) :
		parse_str($_SERVER['QUERY_STRING']);
		$examPartsVideo = $_SESSION["examPartsVideo"];
		$_SESSION["currentExamPart"] = $idx;
		$examComponent = getExamComponentByAbbrev($abbrev, $mysqli);
		$title = $examComponent->title;
		$examInfo = getSingleExam($patientId, $examId, $mysqli);
		$examParts = getExamParts($patientId, $examId, $mysqli);
		$patientInfo = getExtendedPatientInfo($patientId, $mysqli);
		$fullName = ($patientInfo->firstName . ($patientInfo->middleName === "" ? " " : (" " . $patientInfo->middleName . " ")) . $patientInfo->lastName);
		$dob = $patientInfo->dob->format('m/d/Y');

		// Generate token that will be used to get access to video url
		$generateTokenURL = 'https://vpexam.com/includes/generateToken.php?id='. $examInfo->physicianId;
		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL, $generateTokenURL);
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
		$content = curl_exec($curlHandle);
		curl_close($curlHandle);

		$json = json_decode($content);
		if (!is_null($json)) {
			if ($json->success) {
				$token = $json->token;

				// We've successfully requested and gotten a token to play the video
				$audioURL = 'includes/getMedia.php?token=' . $token . '&physicianId=' . $examInfo->physicianId . '&patientId=' . $patientInfo->patientId . '&examId=' . $examInfo->examId . '&abbrev=' . $abbrev . '&type=a';
			}
		}
	?>
	<script type="text/javascript">
		<?php
            echo('setExamParts("' . urlencode(json_encode($examParts)) . '");');
            echo('setExamPartsVideo("' . urlencode(json_encode(array_values($examPartsVideo))) . '");');
            echo('setPhysician(' . $examInfo->physicianId . ');');
            echo('setPatientId(' . $patientId . ');');
            echo('setExamId(' . $examId . ');');
            echo('setAudioURL("' . urlencode($audioURL) . '");');

            // Set the jump table
            $jumpTableHTML = "";
            for ($i = 0; $i < count($examParts); $i++) {
            	$ec = getExamComponentByAbbrev($examParts[$i], $mysqli);
                $localName = $ec->title;
                $localAbbrev = $ec->abbrev;
                $jumpTableHTML .= "<tr class=\"jumpTableTr\" id=\"$localAbbrev\"><td>$localName</td></tr>";
            }
        ?>
	</script>
	<nav id="menu">
		<header>
			<div class="headerDiv">
				<img class="menuHeaderImg" src="img/logo_img_no_text.png" height="50">
				<span class="menuHeaderText">VPExam</span>
			</div>
            <div class="btn" id="btnMenuPatientOverview">Patient Overview</div>
            <div class="btn" id="btnMenuPatientDem">Patient Demographics</div>
            <div class="btn" id="btnMenuHistory">History</div>
            <div class="btn" id="btnMenuExam">Physical Exam</div>
            <div class="btn" id="btnMenuClipboard">Clipboard/Notes</div>
            <div class="btn" id="btnMenuMyPatients">My Patients</div>
            <div class="btn" id="btnMenuSearch">Search</div>
            <div class="btn" id="btnMenuSettings">My Account Settings</div>
            <div class="btn" id="btnMenuLogout">Logout</div>
		</header>
	</nav>

	<main id="panel">
        <header>
            <img class="toggle-button" src="img/menu_white.png" width="40px">
            <div class="patientInfoTopDiv">
                <img class="patientProfilePicSmall" src="includes/getProfileImage.php?id=<?php echo($patientInfo->patientId); ?>&type=1">
                <span class="patientNameTop"><?php echo($fullName); ?></span>
                <span class="topDivider">|</span>
                <span class="patientBirthdayTop"><?php echo($dob); ?></span>
            </div>
            <span class="headerText"><?php echo($examComponent->title); ?></span>
            <div class="welcomeDiv">Welcome, <?php echo($_SESSION['first_name']); ?>!</div>
        </header>
		<div class="mainContent">
			<div class="container">
				<div class="main">
					<div class="waveformContainer">
						<div id="waveform">
							<div class="progress progress-striped active" id="progress-bar">
								<div class="progress-bar progress-bar-info"></div>
							</div>

							<!-- Here be the waveform -->
						</div>
					</div>
					<div class="audioControlsDiv">
						<div class="audioControlsOuterContainer">
							<div class="button-dark-image-no-text" id="btnRw">
								<img src="img/rw.png">
							</div>
							<div class="button-dark-image-no-text" id="btnPlayPause">
								<img src="img/play.png">
							</div>
							<div class="button-dark-image-no-text" id="btnFf">
								<img src="img/ff.png">
							</div>
							<div class="button-dark-image-no-text" id="btnZoomIn">
								<img src="img/zoom_in.png">
							</div>
							<div class="button-dark-image-no-text" id="btnZoomOut">
								<img src="img/zoom_out.png">
							</div>
							<div class="button-dark-image-no-text" id="btnZoomFit">
								<img src="img/zoom_fit.png">
							</div>
						</div>
						<div class="volumeDiv">
							<input id="volumeSlider" type="text" data-slider="true" data-slider-highlight="true" data-slider-theme="volume">
						</div>
						<div class="volumeImgDiv"></div>
						<div class="audioTimeDiv">
							<span id="audioTime">0:00 / 0:00</span>
						</div>
					</div>
					<div class="controlsDiv">
						<div class="title" style="display: block; margin: 0 0 10px 0;">Controls</div>
						<div class="button-dark-image" id="btnPrevComponentMain">
							<img src="img/skip_prev.png" height="25" style="margin: 0 5px 0 0;">
							<span class="imgButtonSpan">Previous</span>
						</div>
						<div class="button-dark-image" id="btnJumpMain">
							<img src="img/jump.png" height="25" style="margin: 0 5px 0 0;">
							<span class="imgButtonSpan">Jump</span>
						</div>
						<div id="jumpDialogMain" title="Jump">
							<table class="jumpTable">
								<?php echo($jumpTableHTML); ?>
							</table>
						</div>
						<div class="button-dark-image" id="btnNextComponentMain">
							<img src="img/skip_next.png" height="25" style="margin: 0 5px 0 0;">
							<span class="imgButtonSpan">Next</span>
						</div>
					</div>
					<div class="clipboardDiv">
						<div style="display: block; height: 40px;">
							<div style="display: inline-block; float: left;">
								<div class="title" style="display: block;">Clipboard</div>
								<div class="subTitle" id="lastSavedMain">Last Saved: Not Saved</div>
							</div>
							<div style="float: right;">
								<div class="button-dark" id="btnInsertNormalMain">Insert Normal</div>
								<div class="button-dark" id="btnSaveClipboardMain">Save</div>
							</div>

						</div>
						<textarea id="taClipboardMain"></textarea>
					</div>
					<div id="cantMoveDialog"></div>
				</div>
			</div>
		</div>
	</main>
	</body>
	<?php else : ?>
		<p>
			<span class="error">You are not authorized to access this page.</span> Please <a href="main.php">login</a>.
		</p>
	<?php endif; ?>
</html>