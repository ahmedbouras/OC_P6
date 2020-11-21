<?php

namespace App\Service;

class ImageHandler
{
    public const PUBLIC_PATH = 'C:/wamp64/www/oc/OC_P6/public';
    public const ALLOWED_EXTENSION = ['jpeg', 'jpg', 'png'];
    public const MIN_PIXEL_HEIGHT = 900;
    public const MIN_PIXEL_WIDTH = 600;
    public const MAX_WEIGHT_IN_BYTES = 1024000;

    public function renameFile($fileToRename)
    {
        $fileExtension = strtolower(pathinfo($fileToRename, PATHINFO_EXTENSION));
        return uniqid("/uploads/", true) . '.' .$fileExtension;
    }

    public function moveFile($file, $renamedFile)
    {
        move_uploaded_file($file, self::PUBLIC_PATH . $renamedFile);
    }

    public function allowedProperties($file)
    {
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileSize = getimagesize($file['tmp_name']);
        $fileWeight = $file['size'];

        if (in_array($fileExtension, self::ALLOWED_EXTENSION)) {
            if ($fileSize[0] >= self::MIN_PIXEL_HEIGHT && $fileSize[1] >= self::MIN_PIXEL_WIDTH) {
                if ($fileWeight < self::MAX_WEIGHT_IN_BYTES) {
                    return true;
                }
            }
        }
        return false;
    }
}