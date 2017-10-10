<?php
function createThumbnail($sourceFileName,$sourceFolder,$fileType) {
    
    $intDestWidth = 320;
    $intDestHeight = 480;
    
    switch ($fileType) {
            case "image/gif":
                $imgTmp= imagecreatefromgif($sourceFileName);
                break;
            case "image/jpg":
               $imgTmp= imagecreatefromjpeg($sourceFileName);
                break;            
            case "image/jpeg":
                $imgTmp= imagecreatefromjpeg($sourceFileName);
                break;
            case "image/png":
                $imgTmp= imagecreatefrompng($sourceFileName);
                break;
        }    
   
    $ox = imagesx($imgTmp);
    $oy = imagesy($imgTmp);
     
    $nx = $intDestWidth;
    $ny = $intDestHeight;
     
    $nm = imagecreatetruecolor($nx, $ny);
     
    imagecopyresized($nm, $imgTmp, 0,0,0,0,$nx,$ny,$ox,$oy);
    imagejpeg($nm, $sourceFolder . "thumbnail");
}


























 /*   $error = "";
    $success = false;
    $returnFilename = "";

    const DEST_IMAGE_W = 320;
    const DEST_IMAGE_H = 480;

    function crop_resize_img($src, $dst, $data,  $imgType) {
        switch ($imgType) {
            case "image/gif":
                $srcImg = imagecreatefromgif($src);
                break;
            case "image/jpg":
                $srcImg = imagecreatefromjpeg($src);
                break;            case "image/jpeg":
                $srcImg = imagecreatefromjpeg($src);
                break;
            case "image/png":
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

        $tmpImgW = $sizeW;
        $tmpImgH = $sizeH;
        $dstImgW = DEST_IMAGE_W;
        $dstImgH = DEST_IMAGE_H;

        $srcX = $size[0];
        $srcY = $size[1];

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
      //  imagefill($dstImg, 0, 0, imagecolorallocatealpha($dstImg, 0, 0, 0, 127));
     //   imagesavealpha($dstImg, true);

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

        $ext = image_type_to_extension(IMG_JPG);
        $error = "";
        $success = true;
        $returnFilename = $ext;
        return array("error" => $error, "success" => $success, "filename" => $returnFilename);
    }   */
?>