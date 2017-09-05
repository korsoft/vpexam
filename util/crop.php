<?php
    $error = "";
    $success = false;
    $returnFilename = "";

    const DEST_IMAGE_W = 400;
    const DEST_IMAGE_H = 400;

    function crop($src, $dst, $data, $physId, $imgType) {
        switch ($imgType) {
            case IMAGETYPE_GIF:
                $srcImg = imagecreatefromgif($src);
                break;
            case IMAGETYPE_JPEG:
                $srcImg = imagecreatefromjpeg($src);
                break;
            case IMAGETYPE_PNG:
                $srcImg = imagecreatefrompng($src);
                break;
        }

        if (!$srcImg) {
            $error = "Unable to create image.";
            $success = false;
            $returnFilename = "";
            unlink($src);
            return array("error" => $error, "success" => $success, "filename" => $returnFilename);
        }

        $size = getimagesize($src);
        $sizeW = $size[0];  // Natural width
        $sizeH = $size[1];  // Natural height

        $srcImgW = $sizeW;
        $srcImgH = $sizeH;

        $degrees = $data->rotate;

        // Rotate the source image
        if (is_numeric($degrees) && $degrees != 0) {
            // PHP's degrees is opposite of CSS's degrees
            $newImg = imagerotate($srcImg, -$degrees, imagecolorallocatealpha($srcImg, 0, 0, 0, 127));

            imagedestroy($srcImg);
            $srcImg = $newImg;

            $deg = abs($degrees) % 180;
            $arc = ($deg > 90 ? (180 - $deg) : $deg) * M_PI / 180;

            $srcImgW = $sizeW * cos($arc) + $sizeH * sin($arc);
            $srcImgH = $sizeW * sin($arc) + $sizeH * cos($arc);

            // Fix rotated image miss 1px issue when degrees < 0
            $srcImgW -= 1;
            $srcImgH -= 1;
        }

        $tmpImgW = $data->width;
        $tmpImgH = $data->height;
        $dstImgW = DEST_IMAGE_W;
        $dstImgH = DEST_IMAGE_H;

        $srcX = $data->x;
        $srcY = $data->y;

        if ($srcX <= -$tmpImgW || $srcX > $srcImgW) {
            $srcX = $srcW = $dstX = $dstW = 0;
        } else if ($srcX <= 0) {
            $dstX = -$srcX;
            $srcX = 0;
            $srcW = $dstW = min($srcImgW, $tmpImgW + $srcX);
        } else if ($srcX <= $srcImgW) {
            $dstX = 0;
            $srcW = $dstW = min($tmpImgW, $srcImgW - $srcX);
        }

        if ($srcW <= 0 || $srcY <= -$tmpImgH || $srcY > $srcImgH) {
            $srcY = $srcH = $dstY = $dstH = 0;
        } else if ($srcY <= 0) {
            $dstY = -$srcY;
            $srcY = 0;
            $srcH = $dstH = min($srcImgH, $tmpImgH + $srcY);
        } else if ($srcY <= $srcImgH) {
            $dstY = 0;
            $srcH = $dstH = min($tmpImgH, $srcImgH - $srcY);
        }

        // Scale to destination position and size
        $ratio = $tmpImgW / $dstImgW;
        $dstX /= $ratio;
        $dstY /= $ratio;
        $dstW /= $ratio;
        $dstH /= $ratio;

        $dstImg = imagecreatetruecolor($dstImgW, $dstImgH);

        // Add transparent background to destination image
        imagefill($dstImg, 0, 0, imagecolorallocatealpha($dstImg, 0, 0, 0, 127));
        imagesavealpha($dstImg, true);

        $result = imagecopyresampled($dstImg, $srcImg, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);

        if ($result) {
            if (!imagepng($dstImg, $dst)) {
                $error = "Unable to save cropped image as PNG file.";
                $success = false;
                $returnFilename = "";
                unlink($src);
                return array("error" => $error, "success" => $success, "filename" => $returnFilename);
            }
        } else {
            $error = "Failed to crop the image file.";
            $success = false;
            $returnFilename = "";
            unlink($src);
            return array("error" => $error, "success" => $success, "filename" => $returnFilename);
        }

        unlink($src);

        $ext = image_type_to_extension($imgType);
        $error = "";
        $success = true;
        $returnFilename = $physId . $ext;
        return array("error" => $error, "success" => $success, "filename" => $returnFilename);
    }

    function codeToMessage($code) {
        $errors = array(
            UPLOAD_ERR_INI_SIZE =>'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE =>'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL =>'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE =>'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR =>'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE =>'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION =>'File upload stopped by extension',
        );

        if (array_key_exists($code, $errors))
            return $errors[$code];

        return "Unknown error";
    }

    const PROFILE_PICTURE_PATH = '/var/www/.uploads/profile/physicians/img/';
    const VPEXAM_TEMP_DIR = '/var/tmp/';

    if (!isset($_FILES['originalImage']) || !isset($_POST['cropData']) || !isset($_POST['physicianId'])) {
        $error = "One or more of the required parameters are not set.";
        $success = false;
        $returnFilename = "";
        echo(json_encode(array("error" => $error, "success" => $success, "filename" => $returnFilename)));
        exit();
    }

    // Check to see if the temp directory exists. If not, create it
    clearstatcache();
    if (!file_exists(VPEXAM_TEMP_DIR)) {
        $retVal = mkdir(VPEXAM_TEMP_DIR);
        if (!$retVal) {
            $error = "Temp directory does not exist, and it could not be created.";
            $success = false;
            $returnFilename = "";
            echo(json_encode(array("error" => $error, "success" => $success, "filename" => $returnFilename)));
            exit();
        }
    }

    $tmpImageFilename = $_FILES['originalImage']['tmp_name'];
    $imgDestFilename = "";
    $cropData = json_decode($_POST['cropData']);
    $physicianId = $_POST['physicianId'];

    // Check to make sure that the FILE upload was successful by
    // checking the error code
    $fileErrorCode = $_FILES['originalImage']['error'];
    if ($fileErrorCode == UPLOAD_ERR_OK) {
        $originalImgType = exif_imagetype($tmpImageFilename);
        if ($originalImgType) {
            $originalImgExt = image_type_to_extension($originalImgType);

            if ($originalImgType == IMAGETYPE_GIF || $originalImgType == IMAGETYPE_JPEG || $originalImgType == IMAGETYPE_PNG) {
                $filename = $physicianId . '.png';
                $uploadDest = VPEXAM_TEMP_DIR . $filename;
                $retVal = move_uploaded_file($tmpImageFilename, $uploadDest);
                $croppedImgDest = PROFILE_PICTURE_PATH . $filename;
                if (!$retVal) {
                    $error = "Error moving uploaded file.";
                    $success = false;
                    $returnFilename = "";
                    echo(json_encode(array("error" => $error, "success" => $success, "filename" => $returnFilename)));
                    exit();
                }
                $retVal = crop($uploadDest, $croppedImgDest, $cropData, $physicianId, $originalImgType);
                echo json_encode($retVal);
            } else {
                $error = "Unsupported image type. Image must be one of the following formats: GIF, JPEG, PNG.";
                $success = false;
                $returnFilename = "";
                echo(json_encode(array("error" => $error, "success" => $success, "filename" => $returnFilename)));
                exit();
            }
        }
    } else {
        $error = codeToMessage($fileErrorCode);
        $success = false;
        $returnFilename = "";
        echo(json_encode(array("error" => $error, "success" => $success, "filename" => $returnFilename)));
        exit();
    }
?>