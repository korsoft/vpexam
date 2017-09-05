<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

require_once '../util/JSLikeHTMLElement.php';

// Include the autoloader
require_once '../dompdf/autoload.inc.php';

// Reference and use the dompdf class
use Dompdf\Dompdf;

sec_session_start();

function generateBAA() {
    $document = new DOMDocument();
    $document->registerNodeClass('DOMElement', 'JSLikeHTMLElement');
    $document->loadHTMLFile('../baa_template.html');

    $document->getElementById('dateTop')->innerHTML = $_POST['dateTop'];
    $document->getElementById('coveredEntity')->innerHTML = $_POST['coveredEntity'];
    $document->getElementById('state')->innerHTML = $_POST['state'];
    $document->getElementById('businessType')->innerHTML = $_POST['businessType'];
    $document->getElementById('address')->innerHTML = $_POST['addr'];
    $document->getElementById('coveredEntityNameBottom')->innerHTML = $_POST['coveredEntity'];
    $document->getElementById('coveredEntityPersonBottom')->innerHTML = $_POST['name'];
    $document->getElementById('coveredEntityTitleBottom')->innerHTML = $_POST['posTitle'];
    $document->getElementById('coveredEntityDateBottom')->innerHTML = $_POST['dateBottom'];
    $document->getElementById('associateDateBottom')->innerHTML = $_POST['dateBottom'];

    $baaHTML = $document->saveHTML();

    // Instantiate and use the dompdf class
    $dompdf = new Dompdf();
    $dompdf->loadHtml($baaHTML);

    // (Optional) Setup the paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    // Render the HTML as PDF
    $dompdf->render();

    // Output the generated PDF to file
    $filename = '/var/www/.uploads/' . $_POST['physId'] . '/baa.pdf';
    $pdf = $dompdf->output();
    file_put_contents($filename, $pdf);

    $success = true;
    $errorMsg = "";
    echo(json_encode(array(
        "success" => $success,
        "errorMsg" => $errorMsg
    )));
}

if (isset($_POST['name'], $_POST['npi'], $_POST['coveredEntity'], $_POST['state'],
    $_POST['businessType'], $_POST['orgType'], $_POST['addr'], $_POST['posTitle'],
    $_POST['dateTop'], $_POST['dateBottom'], $_POST['physId'])) {
    generateBAA();
} else {
    $success = false;
    $errorMsg = "One or more of the required parameters was not set.";
    $array = array(
        "success" => $success,
        "errorMsg" => $errorMsg
    );
    echo(json_encode($array));
}