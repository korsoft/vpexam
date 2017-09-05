<?php

require_once 'AES256Implementation.php';

class MCryptAES256Implementation implements AES256Implementation {
    const BLOCK_SIZE = 16;  // 128 bits
    const KEY_SIZE = 32;    // 256 bits
    const MY_MCRYPT_CIPHER = MCRYPT_RIJNDAEL_128;   // AES
    const MY_MCRYPT_MODE = MCRYPT_MODE_CBC;         // AES

    public function checkDependencies() {
        $functionList = array(
            "mcrypt_create_iv",
            "mcrypt_encrypt",
            "mcrypt_decrypt"
        );

        foreach ($functionList as $func) {
            if (!function_exists($func))
                throw new Exception("Missing function dependency: " . $func);
        }
    }

    public function createIV() {
        return mcrypt_create_iv(self::BLOCK_SIZE, MCRYPT_RAND);
    }

    public function createRandomKey() {
        return mcrypt_create_iv(self::KEY_SIZE, MCRYPT_RAND);
    }

    public function encryptData($theData, $iv, $encKey) {
        return mcrypt_encrypt(self::MY_MCRYPT_CIPHER, $encKey, $theData, self::MY_MCRYPT_MODE, $iv);
    }

    public function decryptData($theData, $iv, $encKey) {
        return mcrypt_decrypt(self::MY_MCRYPT_CIPHER, $encKey, $theData, self::MY_MCRYPT_MODE, $iv);
    }
}