<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
 
sec_session_start();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta content="text/html" charset="UTF-8" http-equiv="content-type">
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
        <link rel="stylesheet" type="text/css" href="style/search_results.css">
		<script src="https://code.jquery.com/jquery-latest.js"></script>
		<script src="js/jquery.tablesorter.js"></script>
        <script src="js/search_results.js"></script>
		<title>Physician Main</title>
	</head>
	<body>
		<?php if ((login_check($mysqli) == true) && ($_SESSION['is_patient'] == false)) : ?>
		<div class="wrapper">
			<div class="mainContent" id="searchBoxDiv">
				<div class="headingDiv">
					<span class="heading">Search</span>
				</div>
				<div class="overflowContainer">
					<table>
						<tbody>
							<tr>
								<td>Gender:</td>
								<td><input class="genderCheckbox" type="checkbox" name="gender" value="m">Male</input></td>
								<td><input class="genderCheckbox" type="checkbox" name="gender" value="f">Female</input></td>
							</tr>
							<tr>
								<td>Last name:</td>
								<td colspan="2"><input type="text" name="lname" id="lname"/></td>
							</tr>
							<tr>
								<td>First name:</td>
								<td colspan="2"><input type="text" name="fname" id="fname"/></td>
							</tr>
							<tr>
								<td colspan="2"></td>
								<td align="right"><button id="btnSearch" onclick="search(<?php echo($_SESSION["user_id"]) ?>)">Search</button></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="mainContent" id="searchResultsDiv">
				<div class="headingDiv">
					<div style="display:inline; /*float:left; width:50%;*/">
						<table>
							<tr>
								<span class="heading">Search Results</span>
							</tr>
						</table>
					</div>
				</div>
				<div class="overflowContainer">
					<table id="tblSearchResults" style="width: 100%">

					</table>
				</div>
			</div>
		</div>
		<?php else : ?>
		<p>
			<span class="error">You are not authorized to access this page.</span> Please <a href="main.php">login</a>.
		</p>
		<?php endif; ?>
	</body>
</html>