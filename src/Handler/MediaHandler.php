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

    public function addImage($uploadedImage, $trick): object
    {
        $renamedUploadedImage = $this->imageService->renameFile($uploadedImage->getClientOriginalName());
        $this->imageService->moveFile($uploadedImage, $renamedUploadedImage);
        $image = $this->imageService->setNewImage($renamedUploadedImage, $trick);
        return $image;
    }
}