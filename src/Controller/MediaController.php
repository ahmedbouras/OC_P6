<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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

    /**
     * @Route("/image/update/{id}/trick/{trickId}", name="image_update")
     */
    public function updateImage(Image $image, $trickId)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        // TODO:  REFACTORISER 
        $allowedExtensions = ['jpeg', 'jpg', 'png'];
        $imgBefore = $image->getName();

        if (!empty($_FILES['newImg']['name'])) {
            $newImgExtension = pathinfo(strtolower($_FILES['newImg']['name']), PATHINFO_EXTENSION);
            $imgSize = getimagesize($_FILES['newImg']['tmp_name']);

            if (!in_array($newImgExtension, $allowedExtensions)) {
                $this->addFlash('danger', "L'image n'est pas au format jpg, jpeg ou png.");
                return $this->redirectToRoute('trick_update', ['id' => $trickId]);
            } elseif ($imgSize[0] < 900 && $imgSize[1] < 600) {
                $this->addFlash('danger', "L'image doit faire 900x600px minimum.");
                return $this->redirectToRoute('trick_update', ['id' => $trickId]);
            } elseif ($_FILES['newImg']['size'] > 1024000) {
                $this->addFlash('danger', "L'image ne doit pas dépasser les 1024ko.");
                return $this->redirectToRoute('trick_update', ['id' => $trickId]);
            } else {
                $imageTrick = uniqid("/uploads/", true) . '.' .$newImgExtension;

                try {
                    move_uploaded_file(
                        $_FILES['newImg']['tmp_name'], 
                        self::PUBLIC_PATH . $imageTrick);
                } catch (FileException $e) {
                    $this->addFlash('danger', "Une erreur s'est produite lors de l'enregistrement de l'image.");
                    return $this->redirectToRoute('trick_update', ['id' => $trickId]);
                }

                $image->setName($imageTrick);

                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($image);
                    $em->flush();
        
                    unlink(self::PUBLIC_PATH . $imgBefore);
        
                    $this->addFlash('success', 'Votre image a bien été enregistré !');
                    return $this->redirectToRoute('trick_update', ['id' => $trickId]);
                } catch (\Exception $e) {
                    $this->addFlash('danger', 'Une erreur s\'est produite durant l\'enregistrement en base de donnée.');
                    return $this->redirectToRoute('trick_update', ['id' => $trickId]);
                }
            }
        } else {
            $this->addFlash('danger', "Aucune image n'a été chargé.");
            return $this->redirectToRoute('trick_update', ['id' => $trickId]);
        }
    }

    /**
     * @Route("/mainImage/update/{id}")
     */
    public function updateMainImage(Trick $trick)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // TODO:  REFACTORISER POUR EVITER DOUBLON
        $allowedExtensions = ['jpeg', 'jpg', 'png'];
        $imgBefore = $trick->getMainImage() ? $trick->getMainImage() : false;

        if (!empty($_FILES['mainImage']['name'])) {
            $mainImageExtension = pathinfo(strtolower($_FILES['mainImage']['name']), PATHINFO_EXTENSION);
            $imgSize = getimagesize($_FILES['mainImage']['tmp_name']);

            if (!in_array($mainImageExtension, $allowedExtensions)) {
                $this->addFlash('danger', "L'image n'est pas au format jpg, jpeg ou png.");
                return $this->redirectToRoute('trick_update', ['id' => $trick->getId()]);
            } elseif ($imgSize[0] < 900 && $imgSize[1] < 600) {
                $this->addFlash('danger', "L'image doit faire 900x600px minimum.");
                return $this->redirectToRoute('trick_update', ['id' => $trick->getId()]);
            } elseif ($_FILES['mainImage']['size'] > 1024000) {
                $this->addFlash('danger', "L'image ne doit pas dépasser les 1024ko.");
                return $this->redirectToRoute('trick_update', ['id' => $trick->getId()]);
            } else {
                $mainImage = uniqid("/uploads/", true) . '.' .$mainImageExtension;

                try {
                    move_uploaded_file(
                        $_FILES['mainImage']['tmp_name'], 
                        self::PUBLIC_PATH . $mainImage);
                } catch (FileException $e) {
                    $this->addFlash('danger', "Une erreur s'est produite lors de l'enregistrement de l'image.");
                    return $this->redirectToRoute('trick_update', ['id' => $trick->getId()]);
                }

                $trick->setMainImage($mainImage);

                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($trick);
                    $em->flush();
        
                    $imgBefore !== null ? unlink(self::PUBLIC_PATH . $imgBefore) : false;
        
                    $this->addFlash('success', 'Votre image a bien été enregistré !');
                    return $this->redirectToRoute('trick_update', ['id' => $trick->getId()]);
                } catch (\Exception $e) {
                    $this->addFlash('danger', 'Une erreur s\'est produite durant l\'enregistrement en base de donnée.');
                    return $this->redirectToRoute('trick_update', ['id' => $trick->getId()]);
                }
            }
        } else {
            $this->addFlash('danger', "Aucune image n'a été chargé.");
            return $this->redirectToRoute('trick_update', ['id' => $trick->getId()]);
        }
    }
}
