<?php

function varErrorLog($var = null) {
    ob_start();                     // Start buffer capture
    var_dump($var);                 // Dump the values
    $contents = ob_get_contents();  // Put the buffer into a variable
    ob_end_clean();                 // End capture
    error_log($contents);           // Log contents of the result of var_dump($var)
}