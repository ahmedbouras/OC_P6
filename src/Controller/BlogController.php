<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    private const OFFSET = 0;
    private const LIMIT = 10;
    
    /**
     * @Route("/", name="home")
     */
    public function home(TrickRepository $trickRepository)
    {
        $totalTricks = count($trickRepository->findAll());
        $tricks = $trickRepository->findByRangeOf(self::OFFSET, self::LIMIT);
        
        return $this->render('blog/home.html.twig', [
            'tricks' => $tricks,
            'totalTricks' => $totalTricks,
        ]);
    }
}
