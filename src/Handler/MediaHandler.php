<?php

namespace App\Handler;

use Exception;
use App\Service\ImageService;
use App\Service\VideoService;

class MediaHandler // extends ?
{
    private $videoService;
    private $imageService;

    public function __construct()
    {
        $this->videoService = new VideoService();
        $this->imageService = new ImageService();
    }

    public function handleVideo($videoLink, $trick)
    {
        if (!$this->videoService->isSourceAllowed($videoLink)) {
            throw new Exception("Veuillez insérer l'url d'une vidéo Youtube ou Dailymotion");
        }
        $embeddedLink = $this->videoService->makeLinkToEmbed($videoLink);
        $video = $this->videoService->setNewVideo($embeddedLink, $trick);
        //$this->em->persist($video);
        return $video;
    }

    public function handleImage($uploadedImage, $trick)
    {
        $renamedUploadedImage = $this->imageService->renameFile($uploadedImage->getClientOriginalName());
        $this->imageService->moveFile($uploadedImage, $renamedUploadedImage);
        $image = $this->imageService->setNewImage($renamedUploadedImage, $trick);
        //$this->em->persist($image);
        return $image;
    }
}