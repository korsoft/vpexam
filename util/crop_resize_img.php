<?php
function createThumbnail($sourceFileName,$sourceFolder,$fileType) {
    
    $intDestWidth = 320;
    $intDestHeight = 480;
    
    try {      
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
    catch(Exception $e) {
            $msg = $e->getMessage();
            $response = ['success' => false, 'error' => ['msg' =>$msg, 'id' => $e->getCode()]];
            error_log("UPLOAD THUMBNAIL :: ERROR  : $msg");
    }
}
?>