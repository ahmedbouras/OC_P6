<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PasswordController extends AbstractController
{
    /**
     * @Route("/oublie-mot-de-passe", name="app_forgot_pass")
     */
    public function forgot()
    {
        return $this->render('password/index.html.twig', [
            'controller_name' => 'PasswordController',
        ]);
    }
}
