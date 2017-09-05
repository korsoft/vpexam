<?php
include_once 'db_connect.php';
include_once 'psl-config.php';
include_once '../util/VideoStream.php';

// Token expires 1 hour from issuance
const TOKEN_EXPIRY_TIME = 3600;
const MEDIA_ROOT_PATH = "/var/www/.uploads/";

if (!isset($_GET["token"], $_GET["physicianId"], $_GET["patientId"], $_GET["examId"], $_GET["abbrev"], $_GET["type"])) {
    echo("Missing parameters in query string.");
    exit();
}
if (!preg_match('/^[0-9A-F]{40}$/i', $_GET["token"])) {
    echo("Invalid token string.");
    exit();
}

$token = $_GET["token"];
$physicianId = $_GET["physicianId"];
$patientId = $_GET["patientId"];
$examId = $_GET["examId"];
$abbrev = $_GET["abbrev"];
$type = $_GET["type"];

$userId = -1;
$tstamp = -1;

$prepStmtGetToken = "SELECT user_id, tstamp FROM pending_users WHERE token = ?";
$stmtGetToken = $mysqli->prepare($prepStmtGetToken);
if ($stmtGetToken) {
    $stmtGetToken->bind_param("s", $token);
    $stmtGetToken->execute();
    $stmtGetToken->bind_result($userId, $tstamp);
    $stmtGetToken->fetch();
    $stmtGetToken->close();
} else {
    echo("Error preparing MySQL query.");
    exit();
}

if ($userId != $physicianId || $userId == -1 || $tstamp == -1) {
    echo("Invalid token.");
    exit();
}

if (($_SERVER["REQUEST_TIME"] - $tstamp) > TOKEN_EXPIRY_TIME) {
    // Token has expired
    $prepStmtDelToken = "DELETE FROM pending_users WHERE user_id = ? AND token = ?";
    $stmtDelToken = $mysqli->prepare($prepStmtDelToken);
    $stmtDelToken->bind_param("is", $userId, $token);
    $stmtDelToken->execute();
    $stmtDelToken->close();
    echo("Token has expired.");
    exit();
}

if ($type === "a" || $type === "A") {
    $ext = ".wav";
    $mime = "audio/wav";
    $type = "audio";
} else if ($type === "v" || $type === "V") {
    $ext = ".mp4";
    $mime = "video/mp4";
    $type = "video";
}

$path = MEDIA_ROOT_PATH . $physicianId . "/" . $patientId . "/" . $examId . "/" . $type . "/" . $abbrev . $ext;
/*$size = filesize($path);

header("Content-Type: " . $mime);
header("Content-Length: " . $size);

readfile($path);*/

$stream = new VideoStream($path);
$stream->start();

// Delete the token
$prepStmtDelToken = "DELETE FROM pending_users WHERE user_id = ? AND token = ?";
$stmtDelToken = $mysqli->prepare($prepStmtDelToken);
$stmtDelToken->bind_param("is", $userId, $token);
$stmtDelToken->execute();
$stmtDelToken->close();
