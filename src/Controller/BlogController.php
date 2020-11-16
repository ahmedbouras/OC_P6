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
        $totalTricks = count($trickRepository->findAll());
        
        $limit = 5;
        $tricks = $trickRepository->findByRangeOf(0, $limit);
        
        return $this->render('blog/home.html.twig', [
            'tricks' => $tricks,
            'totalTricks' => $totalTricks,
        ]);
    }

    /**
     * @Route("/more-trick", name="more_trick")
     */
    public function moreTrick(TrickRepository $trickRepository)
    {
        $offset = $_POST['offset'];
        $limit = 5;

        $tricks = $trickRepository->findByRangeOf($offset, $limit);
        
        return $this->render('blog/trick.html.twig', [
            'tricks' => $tricks,
        ]);
    }
}
