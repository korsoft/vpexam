<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

$success = false;
$errorMsg = "";

if (empty($_POST['physId']) || empty($_POST['UrlUsername'])) {
    $errorMsg = "One or more required parameters was not set.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
elseif(!preg_match("/^[a-zA-Z0-9\-\_]+$/",$_POST['UrlUsername']))
{
    $errorMsg = "Please input alphanumeric characters only.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
elseif(!preg_match("/^[a-zA-Z]/",$_POST['UrlUsername']))
{
    $errorMsg = "Url must start with letters.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
elseif(100<strlen($_POST['UrlUsername']))
{
    $errorMsg = "Url must be equal or less than 100 characters.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
elseif(!preg_match("/^(?!.*?__).*$/",$_POST['UrlUsername']))
{
    $errorMsg = "Double special characters not allowed.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
elseif(!preg_match("/^(?!.*?--).*$/",$_POST['UrlUsername']))
{
    $errorMsg = "Double special characters not allowed.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
elseif(!preg_match("/^(?!.*?-_).*$/",$_POST['UrlUsername']))
{
    $errorMsg = "Double special characters not allowed.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
elseif(!preg_match("/^(?!.*?_-).*$/",$_POST['UrlUsername']))
{
    $errorMsg = "Double special characters not allowed.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
else {
    $physId      = trim($_POST['physId']);
    $UrlUsername = trim($_POST['UrlUsername']);

    $sql = "SELECT username FROM physicians WHERE username='$UrlUsername' AND  physician_id <> $physId";
    if ($result = $mysqli->query($sql)) {
        if(!empty($result->num_rows) && 0<>$result->num_rows)
        {
            $errorMsg = 'URL is already in use.';
            echo(json_encode(array("errorMsg" => $errorMsg, "success" => $success)));
            exit();            
        }
        else
        {
            $sqlUPD = "UPDATE physicians SET username='$UrlUsername' WHERE  physician_id = $physId";
            if ($resultUPD = $mysqli->query($sqlUPD)) {
                    $success = true;
                    echo(json_encode(array("errorMsg" => $errorMsg, "success" => $success)));
                     exit();
            }
            else {
                $errorMsg = "Error preparing mysql statement.";
                echo(json_encode(array("errorMsg" => $errorMsg, "success" => $success)));
                exit();
            }
        }
    }
    else {
        $errorMsg = "Error preparing mysql statement.";
        echo(json_encode(array("errorMsg" => $errorMsg, "success" => $success)));
        exit();
    }
}
?>