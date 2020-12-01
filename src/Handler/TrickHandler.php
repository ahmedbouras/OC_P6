<?php

namespace App\Handler;

use App\Service\TrickService;
use App\Handler\MediaHandler;
use Exception;

class TrickHandler
{
    private $trickService;
    private $mediaHandler;
    private $em;

    public function __construct($entityManager)
    {
        $this->trickService = new TrickService();
        $this->mediaHandler = new MediaHandler();
        $this->em = $entityManager;
    }

    public function handleTrick($trickEntity, $form, $user)
    {
        $title = $this->trickService->cleanUpTitle($form->get('title')->getData());

        if ($trickEntity->getCreatedAt() === null) {
            try {
                $trick = $this->trickService->setNewTrick($trickEntity, $title, $user);
                $this->em->persist($trick);
    
                if ($videoLink = $form->get('video')->getData()) {
                    $video = $this->mediaHandler->handleVideo($videoLink, $trick);
                    $this->em->persist($video);
                }
                if ($uploadedImage = $form->get('image')->getData()) {
                    $image = $this->mediaHandler->handleImage($uploadedImage, $trick);
                    $this->em->persist($image);
                }
    
                $this->em->flush();
            } catch (Exception $e) {
               throw new Exception("Impossible d'enregistrer le trick dans la base de donnée.");
            }
        }
    }
}