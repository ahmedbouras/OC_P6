<?php

namespace App\Handler;

use Exception;
use App\Service\ImageService;
use App\Service\VideoService;

class MediaHandler
{
    private $videoService;
    private $imageService;

    public function __construct()
    {
        $this->videoService = new VideoService();
        $this->imageService = new ImageService();
    }

    public function addVideo(string $videoLink, object $trick): object
    {
        $embeddedLink = $this->videoService->makeLinkToEmbed($videoLink);
        $video = $this->videoService->setNewVideo($embeddedLink, $trick);
        return $video;
    }

    public function editVideo(string $videoLink, object $video): object
    {
        if (!$this->videoService->isSourceAllowed($videoLink)) {
            throw new Exception("Veuillez insérer l'url d'une vidéo Youtube ou Dailymotion");
        }
        $embeddedLink = $this->videoService->makeLinkToEmbed($videoLink);
        $editedVideo = $this->videoService->setExistingVideo($embeddedLink, $video);
        return $editedVideo;
    }

    public function addImage($uploadedImage, object $trick): object
    {
        $renamedUploadedImage = $this->imageService->renameFile($uploadedImage->getClientOriginalName());
        $this->imageService->moveFile($uploadedImage, $renamedUploadedImage);
        $image = $this->imageService->setNewImage($renamedUploadedImage, $trick);
        return $image;
    }

    public function editImage(array $newImage, object $image): object
    {
        if (!$this->imageService->allowedProperties($newImage)) {
            throw new Exception("Veuillez respecter ces conditions : 
            Extensions autorisées : jpeg/jpg/png. Taille minimum : 900x600px. Poids maximum : 1024ko");
        }
        $renamedNewImage = $this->imageService->renameFile($newImage['name']);
        $this->imageService->moveFile($newImage['tmp_name'], $renamedNewImage);
        $editedImage = $this->imageService->setExistingImage($renamedNewImage, $image);
        return $editedImage;

    }

    public function editMainImage(array $newMainImage, object $trick): object
    {
        if (!$this->imageService->allowedProperties($newMainImage)) {
            throw new Exception("Veuillez respecter ces conditions : 
            Extensions autorisées : jpeg/jpg/png. Taille minimum : 900x600px. Poids maximum : 1024ko");
        }
        $renamedNewMainImage = $this->imageService->renameFile($newMainImage['name']);
        $this->imageService->moveFile($newMainImage['tmp_name'], $renamedNewMainImage);
        $editedImage = $trick->setMainImage($renamedNewMainImage);
        return $editedImage;
    }
}