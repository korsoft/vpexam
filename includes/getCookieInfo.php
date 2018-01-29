<?php

$arr_data   = "";
$index      = 0; // array index
if (!empty($_COOKIE['vpexam'])) {
    foreach ($_COOKIE['vpexam'] as $name => $value) {
        $arr_data[$index]['name'] = htmlspecialchars($name);
        $arr_data[$index++]['value'] = htmlspecialchars($value);
    }
} 
$success = 0<$index ? true : false;

echo(json_encode(array("success" => $success,  "data" => $arr_data)));