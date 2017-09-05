<?php
if (isset($_GET['physId'])) {
    $pdfPath = "/var/www/.uploads/{$_GET['physId']}/baa.pdf";

    if (file_exists($pdfPath)) {
        header('Content-Type: application/pdf');
        header('Content-Length: ' . filesize($pdfPath));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($pdfPath);
    }
}
?>
