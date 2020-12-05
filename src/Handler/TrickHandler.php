<?php

namespace App\Handler;

use App\Service\TrickService;
use App\Handler\MediaHandler;
use App\Repository\ImageRepository;
use App\Service\ImageService;
use Exception;

class TrickHandler
{
    private $trickService;
    private $mediaHandler;
    private $imageService;
    private $em;

    public function __construct($entityManager)
    {
        $this->trickService = new TrickService();
        $this->imageService = new ImageService();
        $this->mediaHandler = new MediaHandler();
        $this->em = $entityManager;
    }

    public function writeTrick($trickEntity, $form, $user)
    {
        $title = $this->trickService->cleanUpTitle($form->get('title')->getData());

        try {
            $trick = $this->trickService->setTrick($trickEntity, $title, $user);
            $this->em->persist($trick);

            if ($videoLink = $form->get('video')->getData()) {
                $video = $this->mediaHandler->addVideo($videoLink, $trick);
                $this->em->persist($video);
            }
            if ($uploadedImage = $form->get('image')->getData()) {
                $image = $this->mediaHandler->addImage($uploadedImage, $trick);
                $this->em->persist($image);
            }

            $this->em->flush();
        } catch (Exception $e) {
            throw new Exception("Impossible d'enregistrer le trick dans la base de donnée.");
        }
    }

    public function deleteTrick($trick, $imageRepository)
    {
        $images = $this->imageService->getImagesNamesList($imageRepository->findBy(['trick' => $trick->getId()]));

        try {
            $this->em->remove($trick);
            $this->em->flush();   
        } catch (Exception $e) {
            throw new Exception("Impossible de supprimer le Trick");
            return;
        }
        $this->imageService->deleteLocalImages($images);
    }
}