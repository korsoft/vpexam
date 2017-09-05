<?php
const ERROR_CODES = [
    1000 => "Registration Error: Required POST variables not set.",
    1001 => "Registration Error: Hashed password is not expected length.",
    1002 => "Registration Error: User with specified email already exists.",
    1003 => "Registration Error: Physician does not exist in database.",
    1004 => "Registration Error: Physician upload directory does not exist.",
    1005 => "Registration Error: Error creating patient upload directory.",
    1006 => "Registration Error: A physician with this NPI is already registered.",
    2000 => "MySQL statement preparation failed.",
    2001 => "MySQL statement execution failed.",
    3000 => "NPI Verification Error: NPI not set as POST parameter.",
    3001 => "NPI Verification Error: Error contacting BloomAPI service.",
    3002 => "NPI Verification Error: Error parsing returned JSON.",
    3003 => "NPI Verification Error: The NPI number you entered is not associated with a valid physician.",
    3004 => "NPI Verification Error: Unknown error.",
    4000 => "Login: Invalid email or password.",
    4001 => "Login: Required POST variables were not set.",
    4002 => "Login: Invalid email address.",
    4003 => "Login: Incorrect password.",
    4004 => "Login: Unknown error."
]
?>