<?php

namespace App\Service;

use Exception;
use App\Entity\Image;

class ImageService
{
    public const PUBLIC_PATH = 'C:/wamp64/www/oc/OC_P6/public';
    public const ALLOWED_EXTENSION = ['jpeg', 'jpg', 'png'];
    public const MIN_PIXEL_HEIGHT = 900;
    public const MIN_PIXEL_WIDTH = 600;
    public const MAX_WEIGHT_IN_BYTES = 1024000;

    public function setNewImage(string $newImage, object $trick): object
    {
        $image = new Image();
        $image->setTrick($trick)->setName($newImage);
        return $image;
    }

    public function setExistingImage(string $newImage, object $image): object
    {
        $image->setName($newImage);
        return $image;
    }

    public function renameFile(string $fileToRename): string
    {
        $fileExtension = strtolower(pathinfo($fileToRename, PATHINFO_EXTENSION));
        return uniqid("/uploads/", true) . '.' .$fileExtension;
    }

    public function moveFile(string $file, string $renamedFile): void
    {
        if (!move_uploaded_file($file, self::PUBLIC_PATH . $renamedFile)) {
            throw new Exception("Impossible d'enregistrer l'image");
        }
    }

    public function allowedProperties(array $file): bool
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

    public function getImagesNamesList(array $images): array
    {
        $imagesNamesList = [];
        foreach($images as $image) {
            $imagesNamesList[] = $image->getName();
        }
        return $imagesNamesList;
    }

    public function deleteLocalImages(array $images): void
    {
        foreach($images as $image) {
            unlink(self::PUBLIC_PATH . $image);
        }
    }
}