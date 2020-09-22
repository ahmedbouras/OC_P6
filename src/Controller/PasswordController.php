<?php

namespace App\Controller;

use App\Form\ResetPassType;
use App\Form\ForgotPassType;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PasswordController extends AbstractController
{
    /**
     * @Route("/oublie-mot-de-passe", name="app_forgot_pass")
     */
    public function forgot(Request $request, UserRepository $userRepository, MailerInterface $mailer)
    {
        $form = $this->createForm(ForgotPassType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $username = $form->get('username')->getData();
            $user = $userRepository->findOneBy(['username' => $username]);

            if (!$user) {
                $this->addFlash('danger', 'Cet utilisateur n\'existe pas.');

                return $this->redirectToRoute('app_forgot_pass');
            }

            try {
                $user->setResetToken(md5(random_bytes(5)));

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', 'Erreur : ' . $e->getMessage());
                return $this->redirectToRoute('app_forgot_pass');
            }

            $email = (new TemplatedEmail())
                ->from('no-reply@snowtricks.com')
                ->to($user->getEmail())
                ->subject('Réinitialisation mot de passe')
                ->htmlTemplate('password/reset.html.twig')
                ->context([
                    'token' => $user->getResetToken(),
                ]);

            $mailer->send($email);

            $this->addFlash('success', 'Cliquez sur le lien envoyé par mail pour réinitialiser votre mot de passe.');
            return $this->redirectToRoute('app_forgot_pass');
        }

        return $this->render('password/forgotPass.html.twig', [
            'forgotForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/modification-mot-de-passe/{token}", name="app_reset_pass")
     */
    public function reset(Request $request, UserRepository $userRepository)
    {
        $form = $this->createForm(ResetPassType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

        }

        return $this->render('password/resetPass.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
}
