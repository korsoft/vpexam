<?php
    include_once '../includes/db_connect.php';
    include_once '../includes/functions.php';

    sec_session_start();

    const CRASH_REPORT_PATH = "/var/www/crashReports/";

    /**
     * This page expects the PHP_AUTH_PW to be hashed
     */
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
        header("WWW-Authenticate: Basic realm=\"Administrator\"");
        header("HTTP/1.0 401 Unauthorized");
        // Only reached if authentication fails
        exit;
    } else {
        // Now check the sysadmin password
        $prepStmtCheckPwd = "SELECT password, salt FROM admins WHERE username = ?";
        $stmtCheckPwd = $mysqli->prepare($prepStmtCheckPwd);
        if ($stmtCheckPwd) {
            $username = $_SERVER['PHP_AUTH_USER'];
            $salt = "";
            $saltedPwd = "";

            $stmtCheckPwd->bind_param('s', $username);
            $stmtCheckPwd->execute();
            $stmtCheckPwd->store_result();
            $stmtCheckPwd->bind_result($saltedPwd, $salt);
            $stmtCheckPwd->fetch();
            $stmtCheckPwd->close();

            $givenPwdSalted = hash('sha512', $_SERVER['PHP_AUTH_PW'] . $salt);

            if ($givenPwdSalted == $saltedPwd) {
                // Outputs all POST parameters to a text file. The file name is the date_time of the report reception
                $filename = date('Y-m-d_H-i-s') . '.txt';
                $file = fopen(CRASH_REPORT_PATH . $filename, 'w');
                foreach ($_POST as $key => $value) {
                    $reportLine = $key . " = " . $value . "\n";
                    fwrite($file, $reportLine);
                }
                fclose($file);
            } else {
                header("WWW-Authenticate: Basic realm=\"Administrator\"");
                header("HTTP/1.0 401 Unauthorized");
                // Only reached if authentication fails
                exit;
            }
        } else {
            header("WWW-Authenticate: Basic realm=\"Administrator\"");
            header("HTTP/1.0 401 Unauthorized");
            // Only reached if authentication fails
            exit;
        }
    }
?>
