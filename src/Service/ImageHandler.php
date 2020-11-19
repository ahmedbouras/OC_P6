<?php

namespace App\Service;

use App\Entity\Image;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ImageHandler
{
    public const PUBLIC_PATH = 'C:/wamp64/www/oc/OC_P6/public';

    public function renameFile($file)
    {
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
        $fileExtension = strtolower($fileExtension);
        $renamedFile = uniqid("/uploads/", true) . '.' .$fileExtension;
        return $renamedFile;
    }

    public function moveFile($file, $renamedFile)
    {
        try {
            move_uploaded_file(
                $file, 
                self::PUBLIC_PATH . $renamedFile);
        } catch (FileException $e) {
            throw ("Une erreur s'est prroduite lors de l'enregistrement du fichier.");
        }
    }

    public function setEntity($trick, $renamedImage)
    {
        $image = new Image();
        $image->setTrick($trick)->setName($renamedImage);
        return $image;
    }
}