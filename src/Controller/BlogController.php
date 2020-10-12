<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use App\Entity\Video;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Form\VideoType;
use App\Repository\TrickRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(TrickRepository $trickRepository)
    {
        $tricks = $trickRepository->findAll();
        return $this->render('blog/home.html.twig', [
            'tricks' => $tricks
        ]);
    }

    /**
     * @Route("/trick/creation", name="trick_create")
     */
    public function create(Request $request)
    {
        $trick = new Trick();
        $video = new Video();

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $title = $form->get('title')->getData();
            $url = $form->get('video')->getData();
            $trick->setTitle(strtolower($title))
                  ->setCreatedAt(new \DateTime())
                  ->setUpdatedAt(new \DateTime())
                  ->setDefaultImage('images/default-image.jpg')
                  ->setUser($this->getUser());

            if ($url) {
                if (preg_match('#youtube#', $url)) {
                    $splitedUrl = preg_split('#&#', $url);
                    $cleanedUrl = preg_replace('#watch\?v=#', 'embed/', $splitedUrl[0]);
                } elseif (preg_match('#dailymotion#', $url)) {
                    $cleanedUrl = preg_replace('#video#', 'embed/video', $url);
                }
                $video->setTrick($trick)
                      ->setName($cleanedUrl);
            }

            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($trick);
                $em->persist($video);
                $em->flush();

                $this->addFlash('success', 'Votre Trick a bien été enregistré !');
                return $this->redirectToRoute('home');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Une erreur s\'est produite durant l\'enregistrement en base de donnée.');
                return $this->redirectToRoute('home');
            }
        }

        return $this->render('blog/trick_create.html.twig', [
            'trickCreationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/trick/modification/{id}", name="trick_update")
     */
    public function update(Request $request, Trick $trick)
    {
        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $title = $form->get('title')->getData();
            $trick->setTitle(strtolower($title))
                  ->setUpdatedAt(new \DateTime());

            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($trick);
                $em->flush();

                $this->addFlash('success', 'Votre Trick a bien été modifié !');
                return $this->redirectToRoute('home');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Une erreur s\'est produite durant l\'enregistrement en base de donnée.');
                return $this->redirectToRoute('home');
            }
        }

        return $this->render('blog/trick_modify.html.twig', [
            'trick' => $trick,
            'trickEditForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/trick/suppression/{id}", name="trick_delete")
     */
    public function delete(Trick $trick)
    {
        try {
            $em =$this->getDoctrine()->getManager();
            $em->remove($trick);
            $em->flush();

            $this->addFlash('success', 'Le trick a bien été supprimé !');
            return $this->redirectToRoute('home');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Une erreur est survenue lors de la suppression du trick.');
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/trick/{title}", name="trick_show")
     */
    public function trick(Request $request, TrickRepository $trickRepository, $title)
    {
        $trick = $trickRepository->findOneBy(['title' => $title]);

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

        return $this->render('blog/trick.html.twig', [
            'trick' => $trick,
            'commentForm' => $form->createView(),
        ]);
    }
}
