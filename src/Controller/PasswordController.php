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
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
                $this->addFlash('danger', "Cet utilisateur n'existe pas.");
                return $this->redirectToRoute('app_forgot_pass');
            }

            try {
                $user->setResetToken(md5(random_bytes(5)));

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Impossible de générer un nouveau token de réinitialisation.');
                return $this->redirectToRoute('app_forgot_pass');
            }

            $email = (new TemplatedEmail())
                ->from('no-reply@snowtricks.com')
                ->to($user->getEmail())
                ->subject('Réinitialisation mot de passe')
                ->htmlTemplate('email/reset.html.twig')
                ->context([
                    'token' => $user->getResetToken(),
                ]);
            $mailer->send($email);

            $this->addFlash('success', 'Cliquez sur le lien envoyé par mail pour réinitialiser votre mot de passe.');
            return $this->redirectToRoute('app_forgot_pass');
        }

        return $this->render('password/forgot.html.twig', [
            'forgotForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/modification-mot-de-passe/{token}", name="app_reset_pass")
     */
    public function reset(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder, $token)
    {
        $user = $userRepository->findOneBy(['resetToken' => $token]);

        if (!$user) {
            $this->addFlash('danger', 'Token inexistant.');
            return $this->redirectToRoute('app_forgot_pass');
        }

        $form = $this->createForm(ResetPassType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('email')->getData() != $user->getEmail()) {
                $this->addFlash('danger', 'Veuillez saisir la bonne adresse email.');
                return $this->redirectToRoute('app_reset_pass', ['token' => $token]);
            }

            $newPassword = $form->get('password')->getData();

            $user->setResetToken(null)
                 ->setPassword($passwordEncoder->encodePassword($user, $newPassword));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('password/reinitialization.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
}
