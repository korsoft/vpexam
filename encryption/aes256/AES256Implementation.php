<?php

interface AES256Implementation {
    public function checkDependencies();
    public function createIV();
    public function createRandomKey();
    public function encryptData($theData, $iv, $encKey);
    public function decryptData($theData, $iv, $encKey);
}