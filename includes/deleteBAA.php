<?php
if (isset($_GET['physId'])) {
    $errorMsg = "";
    $baaPath = "/var/www/.uploads/" . $_GET['physId'] . "/baa.pdf";

    $success = unlink($baaPath);

    if (!$success)
        $errorMsg = "An error occurred while deleting BAA file.";

    echo(json_encode(array(
        "errorMsg" => $errorMsg,
        "success" => $success
    )));
} else {
    echo(json_encode(array(
        "errorMsg" => "Required physisian ID parameter not set.",
        "success" => false
    )));
}
?>
