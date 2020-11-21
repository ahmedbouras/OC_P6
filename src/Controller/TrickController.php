<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Entity\Comment;
use App\Form\TrickType;
use App\Form\CommentType;
use App\Service\ImageHandler;
use App\Service\VideoHandler;
use App\Repository\TrickRepository;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class TrickController extends AbstractController
{
    /**
     * @Route("/trick/{title}", name="trick_show")
     */
    public function trick(Request $request, TrickRepository $trickRepository, $title, CommentRepository $commentRepository)
    {
        $trick = $trickRepository->findOneBy(['title' => $title]);
        $totalComments = count($commentRepository->findBy(['trick' => $trick->getId()]));
        
        $limit = 4;
        $comments = $commentRepository->findByRangeOf(0, $limit, $trick->getId());

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setComment($form->get('comment')->getData())
                    ->setCreatedAt(new \DateTime)
                    ->setTrick($trick)
                    ->setUser($this->getUser());

                $em = $this->getDoctrine()->getManager();
                $em->persist($comment);
                $em->flush();

            return $this->redirectToRoute('trick_show', ['title' => $trick->getTitle()]);
        }

        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
            'commentForm' => $form->createView(),
            'comments' => $comments,
            'totalComments' => $totalComments,
        ]);
    }

    /**
     * @Route("/creation/trick", name="trick_create")
     */
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $trick = new Trick();

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $title = $form->get('title')->getData();

                $trick->setTitle(strtolower($title))
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime())
                    ->setDefaultImage('images/default-image.jpg')
                    ->setUser($this->getUser());

                $em = $this->getDoctrine()->getManager();

                if ($videoLink = $form->get('video')->getData()) {
                    $videoHandler = new VideoHandler();
                    $embeddedLink = $videoHandler->makeLinkToEmbed($videoLink);
                    
                    $video = new Video();
                    $video->setTrick($trick)->setName($embeddedLink);
                    $em->persist($video);
                }

                if ($uploadedImage = $form->get('image')->getData()) {
                    $imageHandler = new ImageHandler();
                    $renamedUploadedImage = $imageHandler->renameFile($uploadedImage);
                    $imageHandler->moveFile($uploadedImage, $renamedUploadedImage);
                    
                    $image = new Image();
                    $image->setTrick($trick)->setName($renamedUploadedImage);
                    $em->persist($image);
                }

                $em->persist($trick);
                $em->flush();

                $this->addFlash('success', 'Votre Trick a bien été enregistré !');
                return $this->redirectToRoute('home');

            } catch (\Exception $e) {
                $this->addFlash('danger', "Une erreur est survenue lors de l'enregistrement en base de donnée. $e");
                return $this->redirectToRoute('home');
            }
        }

        return $this->render('trick/create.html.twig', [
            'trickCreationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/modification/trick/{id}", name="trick_update")
     */
    public function update(Request $request, Trick $trick)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $title = $form->get('title')->getData();

            $trick->setTitle(strtolower($title))
                  ->setUpdatedAt(new \DateTime());

            $em = $this->getDoctrine()->getManager();

            if ($videoLink = $form->get('video')->getData()) {
                $videoHandler = new VideoHandler();
                $embeddedLink = $videoHandler->makeLinkToEmbed($videoLink);
                
                $video = new Video();
                $video->setTrick($trick)->setName($embeddedLink);
                $em->persist($video);
            }

            if ($uploadedImage = $form->get('image')->getData()) {
                $imageHandler = new ImageHandler();
                $renamedUploadedImage = $imageHandler->renameFile($uploadedImage);
                $imageHandler->moveFile($uploadedImage, $renamedUploadedImage);
                
                $image = new Image();
                $image->setTrick($trick)->setName($renamedUploadedImage);
                $em->persist($image);
            }

            try {
                $em->flush();

                $this->addFlash('success', 'Votre Trick a bien été modifié !');
                return $this->redirectToRoute('home');
            } catch (\Exception $e) {
                $this->addFlash('danger', "Une erreur s'est produite durant l'enregistrement en base de donnée.");
                return $this->redirectToRoute('home');
            }
        }

        return $this->render('trick/update.html.twig', [
            'trick' => $trick,
            'trickEditForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/suppression/trick/{id}", name="trick_delete")
     */
    public function delete(Trick $trick)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        try {
            $em =$this->getDoctrine()->getManager();
            $em->remove($trick);
            $em->flush();
            // TODO: supprimer les images en local
            $this->addFlash('success', 'Le trick a bien été supprimé !');
            return $this->redirectToRoute('home');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Une erreur est survenue lors de la suppression du trick.');
            return $this->redirectToRoute('home');
        }
    }
}
