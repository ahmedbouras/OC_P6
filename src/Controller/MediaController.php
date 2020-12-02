<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Handler\MediaHandler;
use App\Repository\VideoRepository;
use App\Service\ImageHandler;
use App\Service\VideoHandler;
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

        if (!empty($_FILES['newImg']['name'])) {
            $imageHandler = new ImageHandler();

            if($imageHandler->allowedProperties($_FILES['newImg'])) {
                try {
                    $renamedUploadedImage = $imageHandler->renameFile($_FILES['newImg']['name']);
                    $imageHandler->moveFile($_FILES['newImg']['tmp_name'], $renamedUploadedImage);
                    $image->setName($renamedUploadedImage);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($image);
                    $em->flush();
        
                    unlink(self::PUBLIC_PATH . $oldImage);

                    $this->addFlash('success', 'Votre image a bien été enregistré !');
                    return $this->redirectToRoute('trick_update', ['id' => $trickId]);

                } catch (FileException $e) {
                    $this->addFlash('danger', "Une erreur s'est produite lors de l'enregistrement de l'image.");
                    return $this->redirectToRoute('trick_update', ['id' => $trickId]);
                }
            } else {
                $this->addFlash('warning', "Veuillez respecter ces conditions : 
                Extensions autorisées : jpeg/jpg/png. Taille minimum : 900x600px. Poids maximum : 1024ko");
                return $this->redirectToRoute('trick_update', ['id' => $trickId]);
            }
        } else {
            $this->addFlash('warning', "Aucune image n'a été chargé.");
            return $this->redirectToRoute('trick_update', ['id' => $trickId]);
        }
    }

    /**
     * @Route("/mainImage/update/{id}", name="main_image_update")
     */
    public function updateMainImage(Trick $trick)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $oldImage = $trick->getMainImage() !== null ? $trick->getMainImage() : null;
        if (!empty($_FILES['mainImage']['name'])) {
            $imageHandler = new ImageHandler();

            if($imageHandler->allowedProperties($_FILES['mainImage'])) {
                try {
                    $renamedUploadedImage = $imageHandler->renameFile($_FILES['mainImage']['name']);
                    $imageHandler->moveFile($_FILES['mainImage']['tmp_name'], $renamedUploadedImage);
                    $trick->setMainImage($renamedUploadedImage);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($trick);
                    $em->flush();
        
                    $oldImage ? unlink(self::PUBLIC_PATH . $oldImage): null;

                    $this->addFlash('success', 'Votre image a bien été enregistré !');
                    return $this->redirectToRoute('trick_update', ['id' => $trick->getId()]);

                } catch (FileException $e) {
                    $this->addFlash('danger', "Une erreur s'est produite lors de l'enregistrement de l'image.");
                    return $this->redirectToRoute('trick_update', ['id' => $trick->getId()]);
                }
            } else {
                $this->addFlash('warning', "Veuillez respecter ces conditions : 
                Extensions autorisées : jpeg/jpg/png. Taille minimum : 900x600px. Poids maximum : 1024ko");
                return $this->redirectToRoute('trick_update', ['id' => $trick->getId()]);
            }
        } else {
            $this->addFlash('warning', "Aucune image n'a été chargé.");
            return $this->redirectToRoute('trick_update', ['id' => $trick->getId()]);
        }
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
