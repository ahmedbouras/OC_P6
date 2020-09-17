<?php

namespace App\Controller;

use App\Repository\TrickRepository;
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
     * @Route("/trick/{title}", name="trick_show")
     */
    public function trick(TrickRepository $trickRepository, $title)
    {
        $trick = $trickRepository->findOneBy(['title' => $title]);
        return $this->render('blog/trick.html.twig', [
            'trick' => $trick
        ]);
    }
}
