<?php
/**
 * Please see https://www.aescrypt.com/aes_file_format.html
 * for the file format used.
 *
 */
class AESCryptFileLib {
    const ENCRYPTED_FILE_EXTENSION = "aes";

    //http://www.leaseweblabs.com/2014/02/aes-php-mcrypt-key-padding/
    //Only "Rijndael-128" in "Cipher-block chaining" (CBC) mode is defined as the Advanced Encryption Standard (AES).
    //The file format specifies IV length of 128 bits (the block length) and key length of 256 bits
    //These are assumed to be implemented properly in all AES256 interfaces
    private $aesImpl;

    private $useDynamicFilenaming;

    private $debugging = false;

    public function __construct(AES256Implementation $aesImpl, $useDynamicFilenaming = true) {
        $this->aesImpl = $aesImpl;
        $this->useDynamicFilenaming = $useDynamicFilenaming;

        try {
            $this->aesImpl->checkDependencies();
        } catch (Exception $e) {
            throw new AESCryptMissingDependencyException($e->getMessage());
        }
    }

    public function enableDebugging() {
        $this->debugging = true;
    }

    public function encryptFile($sourceFile, $passphrase, $destFile = NULL, $extData = NULL) {
        // Check we can read the source file
        $this->checkSourceExistsAndReadable($sourceFile);

        // Check any extData is formatted correctly
        $this->checkExtensionData($extData);

        // Check that the password is a string (it cannot be NULL)
        $this->checkPassphraseIsValid($passphrase);

        // Actually do the encryption here
        $destFH = $this->doEncryptFile($sourceFile, $passphrase, $destFile, $extData);

        // Return encrypted file location
        $metaData = stream_get_meta_data($destFH);
        fclose($destFH);
        $filename = realpath($metaData["uri"]);
        return $filename;
    }

    public function readExtensionBlocks($sourceFile) {
        // Check we can read the source file
        $this->checkSourceExistsAndReadable($sourceFile);

        // Attempt to parse and return the extension blocks only
        // Open the file
        $sourceFH = fopen($sourceFile, "rb");
        if ($sourceFH === false)
            throw new AESCryptFileAccessException("Cannot open file for reading: " . $sourceFile);

        $this->readChunk($sourceFH, 3, "file header", NULL, "AES");
        $versionChunk = $this->readChunk($sourceFH, 1, "version byte", "C");
        $extensionBlocks = array();
        if (bin2hex($versionChunk) === dechex(ord("0"))) {
            // This file uses version 0 of the standard
            // Extension blocks dont exist in this versions spec
            $extensionBlocks = NULL;
        } else if (bin2hex($versionChunk) === dechex(ord("1"))) {
            // This file uses version 1 of the standard
            // Extension blocks dont exist in this versions spec
            $extensionBlocks = NULL;
        } else if (bin2hex($versionChunk) === dechex(ord("2"))) {
            // This file uses version 2 of the standard (The latest standard at the time of writing)
            $this->readChunk($sourceFH, 1, "reserved byte", "C", 0);
            $ebIndex = 0;
            while (true) {
                // Read ext length
                $extLength = $this->readChunk($sourceFH, 2, "extension length", "n");
                if ($extLength == 0) {
                    break;
                } else {
                    $extContent = $this->readChunk($sourceFH, $extLength, "extension content");

                    // Find the first NULL splitter character
                    $nullIndex = self::bin_strpos($extContent, "\x00");
                    if ($nullIndex === false)
                        throw new AESCryptCorruptedFileException("Extension block data at index {$ebIndex} has no null splitter byte: " . $sourceFile);

                    $identifier = self::bin_substr($extContent, 0, $nullIndex);
                    $contents = self::bin_substr($extContent, $nullIndex + 1);

                    if ($identifier != "") {
                        $extensionBlocks[$ebIndex] = array(
                            "identifier" => $identifier,
                            "contents" => $contents
                        );
                        $ebIndex++;
                    }
                }
            }
        } else {
            throw new AESCryptCorruptedFileException("Unknown version: " . bin2hex($versionChunk));
        }
        return $extensionBlocks;
    }

    public function decryptFile($sourceFile, $passphrase, $destFile = NULL) {
        // Check we can read the source file
        $this->checkSourceExistsAndReadable($sourceFile);

        // Check whether the passphrase is correct before decrypting the keys and validating with HMAC1
        // If it is, attempt to decrypt the file using these keys and write to destination file
        $destFH = $this->doDecryptFile($sourceFile, $passphrase, $destFile);

        // Return decrypted file location
        $metaData = stream_get_meta_data($destFH);
        fclose($destFH);
        $filename = realpath($metaData["uri"]);
        return $filename;
    }

    private function checkSourceExistsAndReadable($sourceFile) {
        // Source file must exist
        if (!file_exists($sourceFile))
            throw new AESCryptFileMissingException($sourceFile);

        // Source file must be readable
        if (!is_readable($sourceFile))
            throw new AESCryptFileAccessException("Cannot read: " . $sourceFile);
    }

    private function openDestinationFile($sourceFile, $destFile, $encrypting = true) {
        $sourceInfo = pathinfo($sourceFile);

        if ($destFile === NULL) {
            if (!$encrypting) {
                // We are decrypting without a known destination file
                // We should check for a double extension in the file name e.g. (filename.docx.aes)
                // Actually, we just check it ends with .aes and strip off the rest
                if (preg_match("/^(.+)\." . self::ENCRYPTED_FILE_EXTENSION . "$/i", $sourceInfo['basename'], $matches)) {
                    // Yes, source is an .aes file
                    // We remove the .aes part and use a destination file in the same source directory
                    $destFile = $sourceInfo['dirname'] . DIRECTORY_SEPARATOR . $matches[1];
                } else {
                    throw new AESCryptCannotInferDestinationException($sourceFile);
                }
            } else {
                // We are encrypting, use .aes as destination file extension
                $destFile = $sourceFile . "." . self::ENCRYPTED_FILE_EXTENSION;
            }
        }

        if ($this->useDynamicFilenaming) {
            // Try others until it doesn't exist
            $destInfo = pathinfo($destFile);

            $duplicateId = 1;
            while (file_exists($destFile)) {
                // Check the destination file doesn't exist (We never overwrite)
                $destFile = $destInfo['dirname'] . DIRECTORY_SEPARATOR . $destInfo['filename'] . "({$duplicateId})." . $destInfo['extension'];
                $duplicateId++;
            }
        } else {
            if (file_exists($destFile))
                throw new AESCryptFileExistsException($destFile);
        }

        // Now that we found a non existing file, attempt to open it for writing
        $destFH = fopen($destFile, "xb");
        if ($destFH === false)
            throw new AESCryptFileAccessException("Cannot create for writing: " . $destFile);

        return $destFH;
    }

    private function readChunk($sourceFH, $numBytes, $chunkName, $unpackFormat = NULL, $expectedValue = NULL) {
        $readData = fread($sourceFH, $numBytes);
        if ($readData === false)
            throw new AESCryptFileAccessException("Could not read chunk " . $chunkName . " of " . $numBytes . " bytes");

        if (self::bin_strlen($readData) != $numBytes)
            throw new AESCryptCorruptedFileException("Could not read chunk " . $chunkName . " of " . $numBytes . " bytes, only found " . self::bin_strlen($readData) . " bytes");

        if ($unpackFormat !== NULL) {
            $readData = unpack($unpackFormat, $readData);
            if (is_array($readData))
                $readData = $readData[1];
        }

        if ($expectedValue !== NULL) {
            if ($readData !== $expectedValue)
                throw new AESCryptCorruptedFileException("The chunk " . $chunkName . " was expected to be " . bin2hex($expectedValue) . " but found " . bin2hex($readData));
        }
        return $readData;
    }

    private function checkExtensionData($extData) {
        if ($extData === NULL)
            return;
        if (!is_array($extData))
            throw new AESCryptInvalidExtensionException("Must be NULL or an array (containing 'extension block' arrays)");

        // Ignore associative arrays
        $extData = array_values($extData);

        $uniqueIdentifiers = array();
        foreach ($extData as $index => $eb) {
            if (!is_array($eb))
                throw new AESCryptInvalidExtensionException("Extension block at index {$index} must be an array");
            //Each block must contain the array keys 'identifier' and 'contents'
            if (!array_key_exists("identifier", $eb))
                throw new AESCryptInvalidExtensionException("Extension block at index {$index} must contain the key 'identifier'");
            if (!array_key_exists("contents", $eb))
                throw new AESCryptInvalidExtensionException("Extension block at index {$index} must contain the key 'contents'");

            $identifier = $eb['identifier'];
            $contents = $eb['contents'];
            if (!is_string($identifier))
                throw new AESCryptInvalidExtensionException("Extension block at index {$index} has a bad 'identifier' value.  It must be a string.");
            if (!is_string($contents))
                throw new AESCryptInvalidExtensionException("Extension block at index {$index} has a bad 'contents' value.  It must be a string.");

            if (in_array($identifier, $uniqueIdentifiers))
                throw new AESCryptInvalidExtensionException("Extension block at index {$index} contains an 'identifier' which has already been used.  Make sure they are unique.");
            else
                $uniqueIdentifiers[] = $identifier;
        }
    }

    private function checkPassphraseIsValid($passphrase) {
        if ($passphrase === NULL)
            throw new AESCryptInvalidPassphraseException("NULL passphrase not allowed");
    }

    private function doEncryptFile($sourceFile, $passphrase, $destFile, $extData) {
        $this->debug("ENCRYPTION", "Started");

        $header = "AES";
        $header .= pack("H*", "02");    // Version
        $header .= pack("H*", "00");    // Reserved

        // Generate the extension data
        $extdatBinary = $this->getBinaryExtensionData($extData);

        // Create a random IV using the aes implementation
        // IV is based on the block size which is 128 bits (16 bytes) for AES
        $iv1 = $this->aesImpl->createIV();
        if (self::bin_strlen($iv1) != 16)
            throw new AESCryptImplementationException("Returned an IV which is not 16 bytes long: " . bin2hex($iv1));
        $this->debug("IV1", bin2hex($iv1));

        // Use this IV and password to generate the first encryption key
        // We dont need to use AES for this as its just lots of sha hashing
        $passphrase = iconv(mb_internal_encoding(), "UTF-16LE", $passphrase);
        $this->debug("PASSPHRASE", $passphrase);
        $encKey1 = $this->createKeyUsingIVAndPassphrase($iv1, $passphrase);
        if (self::bin_strlen($encKey1) != 32)
            throw new Exception("Returned a passphrase which is not 32 bytes long: " . bin2hex($encKey1));
        $this->debug("KEY1", bin2hex($encKey1));

        // Create another set of keys to do the actual file encryption
        $iv2 = $this->aesImpl->createIV();
        if (self::bin_strlen($iv2) != 16)
            throw new AESCryptImplementationException("Returned an IV which is not 16 bytes long: " . bin2hex($iv2));
        $this->debug("IV2", bin2hex($iv2));

        // The file format uses AES 256 (which is the key length)
        $encKey2 = $this->aesImpl->createRandomKey();
        if (self::bin_strlen($encKey2) != 32)
            throw new AESCryptImplementationException("Returned a random key which is not 32 bytes long: " . bin2hex($encKey2));
        $this->debug("KEY2", bin2hex($encKey2));

        // Encrypt the second set of keys using the first keys
        $fileEncryptionKeys = $iv2 . $encKey2;

        $encryptedKeys = $this->aesImpl->encryptData($fileEncryptionKeys, $iv1, $encKey1);
        if (self::bin_strlen($encryptedKeys) != 48)
            throw new Exception("Assertion 1 failed");
        $this->debug("ENCRYPTED KEYS", bin2hex($encryptedKeys));

        // Calculate HMAC1 using the first enc key
        $hmac1 = hash_hmac("sha256", $encryptedKeys, $encKey1, true);
        if (self::bin_strlen($hmac1) != 32)
            throw new Exception("Assertion 2 failed");
        $this->debug("HMAC 1", bin2hex($hmac1));

        // Now do file encryption
        $sourceContents = file_get_contents($sourceFile);
        $encryptedFileData = $this->aesImpl->encryptData($sourceContents, $iv2, $encKey2);

        $fileSizeModulo = pack("C", self::bin_strlen($sourceContents) % 16);

        $this->debug("FS MODULO", bin2hex($fileSizeModulo));

        // HMAC the encrypted data too
        $this->debug("MD5 of ENC DATA", md5($encryptedFileData));
        $hmac2 = hash_hmac("sha256", $encryptedFileData, $encKey2, true);

        $this->debug("HMAC2", bin2hex($hmac2));

        // Actually write it to the dest fh
        $encData = $header . $extdatBinary . $iv1 . $encryptedKeys . $hmac1 . $encryptedFileData . $fileSizeModulo . $hmac2;

        // Open destination file for writing
        $destFH = $this->openDestinationFile($sourceFile, $destFile, true);
        $written = fwrite($destFH, $encData);
        if ($written === false)
            throw new AESCryptFileAccessException("Could not write encrypted data to file. Tried to write " . self::bin_strlen($encData) . " bytes");
        $this->debug("ENCRYPTION", "Complete");

        return $destFH;
    }

    private function doDecryptFile($sourceFile, $passphrase, $destFile) {
        $this->debug("DECRYPTION", "Started");

        // Check we can read the source file
        $this->checkSourceExistsAndReadable($sourceFile);

        // Attempt to parse and return the extension blocks only
        // Open the file
        $sourceFH = fopen($sourceFile, "rb");
        if ($sourceFH === false)
            throw new AESCryptFileAccessException("Cannot open file for reading: " . $sourceFile);

        $this->readChunk($sourceFH, 3, "file header", NULL, "AES");
        $versionChunk = $this->readChunk($sourceFH, 1, "version byte", "C");
        $extensionBlocks = array();
        if ($versionChunk === 0) {
            // This file uses version 0 of the standard
            $fileSizeModulos = $this->readChunk($sourceFH, 1, "file size modulo", "C", 0);
            if ($fileSizeModulos === 0)
                throw new Exception("Could not decode file size modulos");
            if ($fileSizeModulos < 0 || $fileSizeModulos >= 16)
                throw new Exception("Invalid file size modulos: " . $fileSizeModulos);

            $iv = $this->readChunk($sourceFH, 16, "IV");

            $restOfData = "";
            while (!feof($sourceFH))
                $restOfData .= fread($sourceFH, 8192);  // Read in 8K chunks
            $encryptedData = self::bin_substr($restOfData, 0, -32);
            $hmac = self::bin_substr($restOfData, -32, 32);

            // Convert the passphrase to UTF-16LE
            $passphrase = iconv(mb_internal_encoding(), 'UTF-16LE', $passphrase);
            $this->debug("PASSPHRASE", bin2hex($passphrase));
            $encKey = $this->createKeyUsingIVAndPassphrase($iv, $passphrase);
            $this->debug("ENCKEYFROMPASSWORD", bin2hex($encKey));

            // We simply use this enc key to decode the payload
            // We do not know if it is correct yet until we finish decrypting the data
            $decryptedData = $this->aesImpl->decryptData($encryptedData, $iv, $encKey);
            if ($fileSizeModulos > 0)
                $decryptedData = self::bin_substr($decryptedData, 0, ((16 - $fileSizeModulos) * -1));

            // Here the HMAC is (probably) used to verify the decrypted data
            // TODO: Test this using known encrypted files using version 0
            $this->validateHMAC($encKey, $decryptedData, $hmac, "HMAC");

            // Open destination file for writing
            $destFH = $this->openDestinationFile($sourceFile, $destFile, false);

            $result = fwrite($destFH, $decryptedData);
            if ($result === false)
                throw new Exception("Could not write back file");
            if ($result != self::bin_strlen($decryptedData))
                throw new Exception("Could not write back file");
            $this->debug("DECRYPTION", "Completed");
            return $destFH;
        } else if ($versionChunk === 1 || $versionChunk === 2) {
            if ($versionChunk === 1) {
                // This file uses version 1 of the standard
                $this->readChunk($sourceFH, 1, "reserved byte", "C", 0);
            } else if ($versionChunk === 2) {
                // This file uses version 2 of the standard (The latest standard at the time of writing)
                $this->readChunk($sourceFH, 1, "reserved byte", "C", 0);
                while (true) {
                    // Read ext length
                    $extLength = $this->readChunk($sourceFH, 2, "extension length", "n");
                    if ($extLength == 0)
                        break;
                    else
                        $this->readChunk($sourceFH, $extLength, "extension content");
                }
            }

            $iv1 = $this->readChunk($sourceFH, 16, "IV 1");
            $this->debug("IV1", bin2hex($iv1));
            $encKeys = $this->readChunk($sourceFH, 48, "Encrypted Keys");
            $this->debug("ENCRYPTED KEYS", bin2hex($encKeys));
            $hmac1 = $this->readChunk($sourceFH, 32, "HMAC 1");
            $this->debug("HMAC1", bin2hex($hmac1));

            // Convert the passphrase to UTF-16LE
            $passphrase = iconv(mb_internal_encoding(), 'UTF-16LE', $passphrase);
            $this->debug("PASSPHRASE", bin2hex($passphrase));
            $encKey1 = $this->createKeyUsingIVAndPassphrase($iv1, $passphrase);
            $this->debug("ENCKEY1FROMPASSWORD", bin2hex($encKey1));

            $this->validateHMAC($encKey1, $encKeys, $hmac1, "HMAC 1");

            $restOfData = "";
            while (!feof($sourceFH))
                $restOfData .= fread($sourceFH, 8192);  // Read in 8K chunks
            $encryptedData = self::bin_substr($restOfData, 0, -33);
            $fileSizeModulos = unpack("C", self::bin_substr($restOfData, -33, 1));
            $fileSizeModulos = $fileSizeModulos[1];
            if ($fileSizeModulos === false)
                throw new Exception("Could not decode file size modulos");
            if ($fileSizeModulos < 0 || $fileSizeModulos >= 16)
                throw new Exception("Invalid file size modulos: " . $fileSizeModulos);

            $hmac2 = self::bin_substr($restOfData, -32);
            $this->debug("HMAC2", bin2hex($hmac2));

            $decryptedKeys = $this->aesImpl->decryptData($encKeys, $iv1, $encKey1);
            $this->debug("DECRYPTED_KEYS", bin2hex($decryptedKeys));

            $iv2 = self::bin_substr($decryptedKeys, 0, 16);
            $encKey2 = self::bin_substr($decryptedKeys, 16);

            $this->debug("MD5 of ENC DATA", md5($encryptedData));

            $this->validateHMAC($encKey2, $encryptedData, $hmac2, "HMAC 2");
            // All keys were correct, we can be sure that the decrypted data will be correct
            $decryptedData = $this->aes_impl->decryptData($encryptedData, $iv2, $encKey2);
            // Modulos tells us how many bytes to trim from the end
            if ($fileSizeModulos > 0)
                $decryptedData = self::bin_substr($decryptedData, 0, ((16 - $fileSizeModulos) * -1));

            // Open destination file for writing
            $destFH = $this->openDestinationFile($sourceFile, $destFile, false);

            $result = fwrite($destFH, $decryptedData);
            if ($result === false)
                throw new Exception("Could not write back file");
            if ($result != self::bin_strlen($decryptedData))
                throw new Exception("Could not write back file");
            $this->debug("DECRYPTION", "Completed");
            return $destFH;
        } else {
            throw new Exception("Invalid version chunk: " . $versionChunk);
        }
        throw new Exception("Not implemented");
    }

    // Converts the given extension data in to binary data
    private function getBinaryExtensionData($extData) {
        $this->checkExtensionData($extData);

        if ($extData === NULL)
            $extData = array();

        $output = "";
        foreach ($extData as $ext) {
            $ident = $ext['identifier'];
            $contents = $ext['contents'];
            $data = $ident . pack("C", 0) . $contents;
            $output .= pack("n", self::bin_strlen($data));
            $output .= $data;
        }

        // Also insert a 128 byte container
        $data = str_repeat(pack("C", 0), 128);
        $output .= pack("n", self::bin_strlen($data));
        $output .= $data;

        // 2 finishing NULL bytes to signify end of extensions
        $output .= pack("C", 0);
        $output .= pack("C", 0);
        return $output;
    }

    // This is sha256 by standard and should always returns 256bits (32 bytes) of hash data
    // Looking at the java implementation, it seems we should iterate the hasing 8192 times
    private function createKeyUsingIVAndPassphrase($iv, $passphrase) {
        // Start with th IV padded to 32 bytes
        $aesKey = str_pad($iv, 32, hex2bin("00"));
        $iterations = 8192;
        for ($i = 0; $i < $iterations; $i++) {
            $hash = hash_init("sha256");
            hash_update($hash, $aesKey);
            hash_update($hash, $passphrase);
            $aesKey = hash_final($hash, true);
        }
        return $aesKey;
    }

    private function validateHMAC($key, $data, $hash, $name) {
        $calculated = hash_hmac("sha256", $data, $key, true);
        $actual = $hash;
        if ($calculated != $actual) {
            $this->debug("CALCULATED", bin2hex($calculated));
            $this->debug("ACTUAL", bin2hex($actual));
            if ($name == "HMAC 1")
                throw new AESCryptInvalidPassphraseException("{$name} failed to validate integrity of encryption keys.  Incorrect password or file corrupted.");
            else
                throw new AESCryptCorruptedFileException("{$name} failed to validate integrity of encrypted data.  The file is corrupted and should not be trusted.");
        }
    }

    private function debug($name, $msg) {
        if ($this->debugging) {
            echo "<br/>";
            echo $name . " - " . $msg;
            echo "<br/>";
        }
    }

    //http://php.net/manual/en/mbstring.overload.php
    //String functions which may be overloaded are: mail, strlen, strpos, strrpos, substr,
    //strtolower, strtoupper, stripos, strripos, strstr, stristr, strrchr,
    //substr_count, ereg, eregi, ereg_replace, eregi_replace, split
    //
    //Since we use some of these str_ php functions to manipulate binary data,
    //to prevent accidental multibyte string functions thinking binary data is a
    //multibyte string and breaking the engine, we use the 8bit mode
    //with the mb_ equivalents if they exist.

    //Functions we use and so must wrap: strlen, strpos, substr
    public static function bin_strlen($string) {
        if (function_exists('mb_strlen'))
            return mb_strlen($string, '8bit');
        else
            return strlen($string);
    }

    public static function bin_strpos($haystack, $needle, $offset = 0) {
        if (function_exists('mb_strpos'))
            return mb_strpos($haystack, $needle, $offset, '8bit');
        else
            return strpos($haystack, $needle, $offset);
    }

    public static function bin_substr($string, $start, $length = NULL) {
        if (function_exists('mb_substr'))
            return mb_substr($string, $start, $length, '8bit');
        else
            return substr($string, $start, $length);
    }
}


class AESCryptMissingDependencyException extends Exception {} //E.g. missing mcrypt
class AESCryptCorruptedFileException extends Exception {} //E.g. when file looks corrupted or wont parse
class AESCryptFileMissingException extends Exception {} //E.g. cant read file to encrypt
class AESCryptFileAccessException extends Exception {} //E.g. read/write error on files
class AESCryptFileExistsException extends Exception {} //E.g. when a destination file exists (we never overwrite)
class AESCryptInvalidExtensionException extends Exception {} //E.g. when an extension array is invalid
class AESCryptInvalidPassphraseException extends Exception {} //E.g. when the password is wrong
class AESCryptCannotInferDestinationException extends Exception {} //E.g. when we try to decrypt a 3rd party written file which doesnt have the standard file name convention
class AESCryptImplementationException extends Exception {} //For generic exceptions by the aes implementation used