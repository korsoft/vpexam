<?php
//require 'vendor/autoload.php';

//$logger = new Katzgrau\KLogger\Logger(__DIR__);

/**
 * These are the database login details
 */
define("HOST", "127.0.0.1");	// The host you want to connect to.
define("USER", "root");     // The database username.
define("PASSWORD", 'omeraz1nxom1'); // The database password.
define("DATABASE", "virtual_physical_secure");  // The database name.

//define("CAN_REGISTER", "any");
//define("DEFAULT_ROLE", "member");

define("SECURE", FALSE);	// For development only!!!!

// Other constants
define("UPLOADS_LOCATION", "/var/www/.uploads/");
?>
