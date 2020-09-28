<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class RegistrationController extends AbstractController
{
    /**
     * @Route("/inscription", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, MailerInterface $mailer)
    {
        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedAvatar = $form->get('avatar')->getData();
            $user = $form->getData();
            $user->setPassword($passwordEncoder->encodePassword(
                $user,
                $user->getPassword()
            ));

            if ($uploadedAvatar) {
                $fileNameAvatar = uniqid("uploads/", true) . '.' .$uploadedAvatar->guessExtension();

                try {
                    $uploadedAvatar->move(
                        $this->getParameter('images_directory'),
                        $fileNameAvatar
                    );
                }
                catch (FileException $e) {
                    $this->addFlash('danger', 'Une erreur s\'est prroduite lors du chargment du fichier : ' . $e);
                    return $this->redirectToRoute('app_register');
                }
            } else {
                $fileNameAvatar = 'uploads/avatar-default.png';
            }

            $user->setAvatar($fileNameAvatar);
            $user->setActivationToken(md5(random_bytes(5)));

            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            } catch(\Exception $e) {
                $this->addFlash('danger', 'Une erreur s\'est produite durant l\'enregistrement en base de donnée.');
                return $this->redirectToRoute('app_register');
            }

            $email = (new TemplatedEmail())
                ->from('no-reply@snowtricks.com')
                ->to($user->getEmail())
                ->subject('Confirmation Email')
                ->htmlTemplate('registration/activation.html.twig')
                ->context([
                    'token' => $user->getActivationToken(),
                ]);

            $mailer->send($email);

            $this->addFlash('success', 'Confirmez votre compte en cliquant sur le lien envoyé par mail.');
            return $this->redirectToRoute('home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/activation/{token}", name="app_activation")
     */
    public function activation(UserRepository $userRepository, $token)
    {
        $user = $userRepository->findOneBy(['activationToken' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur inconnu');
        }

        $user->setActivationToken(null);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Votre compte est maintenant activé !');
        
        return $this->redirectToRoute('app_login');
    }
}
