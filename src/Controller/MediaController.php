<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Handler\MediaHandler;
use App\Service\ImageHandler;
use Exception;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class MediaController extends AbstractController
{
    public const PUBLIC_PATH = 'C:/wamp64/www/oc/OC_P6/public';
    public const REGEX_VIDEO = '#^(https://www.(youtube|dailymotion).com)#';

    /**
     * @Route("/video/delete/{id}/trick/{trickId}", name="video_delete")
     */
    public function deleteVideo(Video $video, $trickId)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        try {
            $em =$this->getDoctrine()->getManager();
            $em->remove($video);
            $em->flush();

            $this->addFlash('success', 'Vidéo supprimé.');
            return $this->redirectToRoute('trick_update', ['id' => $trickId]);
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Une erreur est survenue lors de la suppression de la vidéo.');
            return $this->redirectToRoute('trick_update', ['id' => $trickId]);
        }
    }

    /**
     * @Route("/video/update/{id}/trick/{trickId}", name="video_update")
     */
    public function updateVideo(Video $video, $trickId)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $videoLink = isset($_POST['newUrl']) ? $_POST['newUrl'] : null;

        if ($videoLink) {
            try {
                $entityManager = $this->getDoctrine()->getManager();
                $mediaHandler = new MediaHandler();
                $video = $mediaHandler->editVideo($videoLink, $video);

                $entityManager->persist($video);
                $entityManager->flush();
                $this->addFlash('success', 'Vidéo modifié.');
            } catch (Exception $e) {
                $this->addFlash('danger', 'Impossible de modifier la vidéo.');
            }
        } else {
            $this->addFlash('warning', "Aucune vidéo n'a été chargé.");
        }
        return $this->redirectToRoute('trick_update', ['id' => $trickId]);       
    }

    /**
     * @Route("/image/delete/{id}/trick/{trickId}", name="image_delete")
     */
    public function deleteImage(Image $image, $trickId)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        try {
            $imagePath = $image->getName();

            $em =$this->getDoctrine()->getManager();
            $em->remove($image);
            $em->flush();

            unlink(self::PUBLIC_PATH . $imagePath);

            $this->addFlash('success', 'Image supprimé.');
            return $this->redirectToRoute('trick_update', ['id' => $trickId]);
        } catch (\Exception $e) {
            $this->addFlash('danger', "Une erreur est survenue lors de la suppression de l'image.");
            return $this->redirectToRoute('trick_update', ['id' => $trickId]);
        }
    }

    /**
     * @Route("/image/update/{id}/trick/{trickId}", name="image_update")
     */
    public function updateImage(Image $image, $trickId)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $oldImage = $image->getName();

        if (isset($_FILES['newImg']['name']) && !empty($_FILES['newImg']['name'])) {
            try {
                $newImage = $_FILES['newImg'];
                $mediaHandler = new MediaHandler();
                $updatedimage = $mediaHandler->editImage($newImage, $image);

                $em = $this->getDoctrine()->getManager();
                $em->persist($updatedimage);
                $em->flush();

                unlink(self::PUBLIC_PATH . $oldImage);
                $this->addFlash('success', 'Votre image a bien été enregistré !');
            } catch (Exception $e) {
                $this->addFlash('danger', "Une erreur s'est produite lors de l'enregistrement de l'image.");
            } 
        } else {
            $this->addFlash('warning', "Aucune image n'a été chargé.");
        }
        return $this->redirectToRoute('trick_update', ['id' => $trickId]);
    }

    /**
     * @Route("/mainImage/update/{id}", name="main_image_update")
     */
    public function updateMainImage(Trick $trick)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $oldImage = $trick->getMainImage() !== null ? $trick->getMainImage() : null;
        
        if (isset($_FILES['mainImage']['name']) && !empty($_FILES['mainImage']['name'])) {
            try {
                $newImage = $_FILES['mainImage'];
                $mediaHandler = new MediaHandler();
                $image = $mediaHandler->editMainImage($newImage, $trick);

                $em = $this->getDoctrine()->getManager();
                $em->persist($image);
                $em->flush();

                $oldImage ? unlink(self::PUBLIC_PATH . $oldImage): null;
                $this->addFlash('success', 'Votre image a bien été enregistré !');
            } catch (Exception $e) {
                $this->addFlash('danger', "Une erreur s'est produite lors de l'enregistrement de l'image principale.");
            } 
        } else {
            $this->addFlash('warning', "Aucune image n'a été chargé.");
        }
        return $this->redirectToRoute('trick_update', ['id' => $trick->getId()]);
    }

    /**
     * @Route("/mainImage/delete/{id}", name="main_image_delete")
     */
    public function deleteMainImage(Trick $trick)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $oldImage = $trick->getMainImage() !== null ? $trick->getMainImage() : null;
        if ($oldImage) {
            $trick->setMainImage(null);

            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($trick);
                $em->flush();
    
                unlink(self::PUBLIC_PATH . $oldImage);
    
                $this->addFlash('success', "L'image principale a bien été supprimé !");
            } catch (\Exception $e) {
                $this->addFlash('danger', "Une erreur s'est produite lors de la suppression de l'image principale.");
            }
        } else {
            $this->addFlash('warning', "Aucune image principale à supprimer.");
            
        }
        return $this->redirectToRoute('trick_update', ['id' => $trick->getId()]);
    }
}
