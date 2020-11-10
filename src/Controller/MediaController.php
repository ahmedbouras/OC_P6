<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Video;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MediaController extends AbstractController
{
    public const PUBLIC_PATH = 'C:/wamp64/www/oc/OC_P6/public';

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

        // TODO: REFACTORISER EN METHODE POUR EVITER DOUBLON
        $url = $_POST['newUrl'];
        if ($url) {
            if (preg_match('#^(https://www.(youtube|dailymotion).com)#', $url)) {
                if (preg_match('#youtube#', $url)) {
                    $splitedUrl = preg_split('#&#', $url);
                    $cleanedUrl = preg_replace('#watch\?v=#', 'embed/', $splitedUrl[0]);
                } elseif (preg_match('#dailymotion#', $url)) {
                    $cleanedUrl = preg_replace('#video#', 'embed/video', $url);
                }
                $video->setName($cleanedUrl);
            } else {
                $this->addFlash('danger', 'Une erreur est survenue lors de la modification de la vidéo.');
                return $this->redirectToRoute('trick_update', ['id' => $trickId]);
            }
        }

        try {
            $em =$this->getDoctrine()->getManager();
            $em->persist($video);
            $em->flush();

            $this->addFlash('success', 'Vidéo modifié.');
            return $this->redirectToRoute('trick_update', ['id' => $trickId]);
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Une erreur est survenue lors de la modification de la vidéo.');
            return $this->redirectToRoute('trick_update', ['id' => $trickId]);
        }        
    }

    /**
     * @Route("/image/delete/{id}/trick/{trickId}", name="image_delete")
     */
    public function deleteImage(Image $image, $trickId)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        try {
            $imgPath = $image->getName();

            $em =$this->getDoctrine()->getManager();
            $em->remove($image);
            $em->flush();

            unlink(self::PUBLIC_PATH . $imgPath);

            $this->addFlash('success', 'Image supprimé.');
            return $this->redirectToRoute('trick_update', ['id' => $trickId]);
        } catch (\Exception $e) {
            $this->addFlash('danger', "Une erreur est survenue lors de la suppression de l'image.");
            return $this->redirectToRoute('trick_update', ['id' => $trickId]);
        }
    }
}
