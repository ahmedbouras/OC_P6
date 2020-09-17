<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
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

    /**
     * @Route("/inscription", name="app_register")
     */
    public function register()
    {
        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user);

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
