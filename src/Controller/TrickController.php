<?php

namespace App\Controller;

use Exception;
use App\Entity\Trick;
use App\Entity\Comment;
use App\Form\TrickType;
use App\Form\CommentType;
use App\Handler\TrickHandler;
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
            $entityManager = $this->getDoctrine()->getManager();
            try {
                $trickHandler = new TrickHandler($entityManager);
                $trickHandler->writeTrick($trick, $form, $this->getUser());

                $this->addFlash('success', 'Votre Trick a bien été enregistré !');
            } catch (Exception $e) {
                $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
            }
            return $this->redirectToRoute('home');
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
            $entityManager = $this->getDoctrine()->getManager();
            try {
                $trickHandler = new TrickHandler($entityManager);
                $trickHandler->writeTrick($trick, $form, $this->getUser());

                $this->addFlash('success', 'Votre Trick a bien été modifié !');
            } catch (Exception $e) {
                $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
            }
            return $this->redirectToRoute('home');
        }

        return $this->render('trick/update.html.twig', [
            'trick' => $trick,
            'trickEditForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/suppression/trick/{id}", name="trick_delete")
     */
    public function delete(Trick $trick, ImageRepository $imageRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        try {
            $entityManager = $this->getDoctrine()->getManager();
            $trickHandler = new TrickHandler($entityManager);
            $trickHandler->deleteTrick($trick, $imageRepository);

            $this->addFlash('success', 'Le trick a bien été supprimé !');
        } catch (Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }
        return $this->redirectToRoute('home');
    }
}
