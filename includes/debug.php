<?php
    include_once 'Timer.php';

    $DEBUG_ENABLED = true;
    const debugLogFile = "/var/log/vpe_debug.log";

    function logInfo($message) {
        $now = new DateTime('America/New_York');
        $dateStr = $now->format('Y-m-d H:i:s');
        error_log("[" . $dateStr . "] " . "[LOG_INFO]: " . $message . "\r\n", 3, debugLogFile);
    }

    function logWarn($message) {
        $now = new DateTime('America/New_York');
        $dateStr = $now->format('Y-m-d H:i:s');
        error_log("[" . $dateStr . "] " . "[LOG_WARN]: " . $message . "\r\n", 3, debugLogFile);
    }

    function logError($message) {
        $now = new DateTime('America/New_York');
        $dateStr = $now->format('Y-m-d H:i:s');
        error_log("[" . $dateStr . "] " . "[LOG_ERROR]: " . $message . "\r\n", 3, debugLogFile);
    }
?>