<?php
    const BLOOM_API_URL = "https://www.bloomapi.com/api/sources/usgov.hhs.npi/";

    $success = false;
    $errorNum = -1;
    $errorMsg = "";
    $line = -1;
    $data = [];
    $fname = "";
    $lname = "";
    $addr = "";
    $city = "";
    $state = "";
    $zip = "";

    if (isset($_GET['npi'])) {
        $url = BLOOM_API_URL . $_GET['npi'];
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false );
        if (!$content = curl_exec($curlHandle)) {
            curl_close($curlHandle);
            $errorNum = 3001;
            $line = __LINE__;
            echo(json_encode(array("success" => $success, "errorNum" => $errorNum, "errorMsg" => $errorMsg, "line" => $line, "data" => array("fname" => $fname, "lname" => $lname, "addr" => $addr, "city" => $city, "state" => $state, "zip" => $zip))));
            exit();
        }
        curl_close($curlHandle);

        $json = json_decode($content);
        if (is_null($json)) {
            $errorNum = 3002;
            $line = __LINE__;
            echo(json_encode(array("success" => $success, "errorNum" => $errorNum, "errorMsg" => $errorMsg, "line" => $line, "data" => array("fname" => $fname, "lname" => $lname, "addr" => $addr, "city" => $city, "state" => $state, "zip" => $zip))));
            exit();
        }
        if (gettype($json) === "string") {
            if ($json === "item not found") {
                $errorNum = 3003;
                $line = __LINE__;
                echo(json_encode(array("success" => $success, "errorNum" => $errorNum, "errorMsg" => $errorMsg, "line" => $line, "data" => array("fname" => $fname, "lname" => $lname, "addr" => $addr, "city" => $city, "state" => $state, "zip" => $zip))));
                exit();
            } else {
                $errorNum = 3004;
                $line = __LINE__;
                echo(json_encode(array("success" => $success, "errorNum" => $errorNum, "errorMsg" => $errorMsg, "line" => $line, "data" => array("fname" => $fname, "lname" => $lname, "addr" => $addr, "city" => $city, "state" => $state, "zip" => $zip))));
                exit();
            }
        } else if (gettype($json) === "object") {
            $fname = $json->result->first_name;
            $lname = $json->result->last_name;
            $addr = $json->result->practice_address->address_line;
            $city = $json->result->practice_address->city;
            $state = $json->result->practice_address->state;
            $zip = $json->result->practice_address->zip;
            /*$html =
            '
            <html>
                <body>
                    <form name="confirm" action="../confirm_npi.php" method="POST">
                        <input type="hidden" name="fname" value="'.$fname.'">
                        <input type="hidden" name="lname" value="'.$lname.'">
                        <input type="hidden" name="addr" value="'.$addr.'">
                        <input type="hidden" name="city" value="'.$city.'">
                        <input type="hidden" name="state" value="'.$state.'">
                        <input type="hidden" name="zip" value="'.$zip.'">
                    </form>
                </body>
                <script type="text/javascript">
                    document.confirm.submit();
                </script>
            </html>
            ';*/

            //echo($html);
            $success = true;
            echo(json_encode(array("success" => $success, "errorNum" => $errorNum, "errorMsg" => $errorMsg, "line" => $line, "data" => array("fname" => $fname, "lname" => $lname, "addr" => $addr, "city" => $city, "state" => $state, "zip" => $zip))));
            exit();
        }
    } else {
        $errorNum = 3000;
        $line = __LINE__;
        echo(json_encode(array("success" => $success, "errorNum" => $errorNum, "errorMsg" => $errorMsg, "line" => $line, "data" => array("fname" => $fname, "lname" => $lname, "addr" => $addr, "city" => $city, "state" => $state, "zip" => $zip))));
        exit();
    }
?>