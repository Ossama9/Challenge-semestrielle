<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\EditProfileType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\Email;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\String\Slugger\SluggerInterface;

class SecurityController extends AbstractController
{

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            $this->addFlash('info', 'Vous etes deja connecté');
            return $this->redirectToRoute('app_profile');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function create(UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer): Response
    {
        $user = new User();
        $mail = new Email($mailer);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'];
            $passwordConfirm = $_POST['passwordConfirm'];
            $result = count($userRepository->findBy(['email' => $_POST['email']]));
            if ($result != 0) {
                $this->addFlash("info", "ce compte existe déja");
            }
            elseif ($password == $passwordConfirm && strlen($password) > 8) {
                $lastname = $_POST['inputLastname'];
                $firstname = $_POST['firstname'];
                $email = $_POST['email'];
                $user->setLastname($lastname);
                $user->setFirstname($firstname);
                $user->setEmail($email);
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $password
                );
                $user->setCreatedAt(new DateTime());
                $user->setPassword($hashedPassword);
                $user->setToken($this->generateTokenForUser());
                $user->setAvatar('default.png');
                $mail->sendEmailForRegister($user->getEmail(), $user->getToken());
                $userRepository->save($user, true);
                $this->addFlash("success", "Votre compte a bien été créé, vous allez recevoir un email");
                return $this->redirectToRoute('app_login');
            } elseif (strlen($password) < 8) {
                $this->addFlash("error", "Votre mot de passe doit faire minimun 8 caractères minimum");
            } else {
                $this->addFlash("error", "Vérifiez vos champs d'inputs");
            }
        }

        return $this->render('security/register.html.twig', [
            'firstname' => $_POST['firstname'] ?? '',
            'lastname' => $_POST['inputLastname'] ?? '',
            'email' => $_POST['email'] ?? '',
        ]);
    }

    #[Route('/validate/{token}', name: 'validate_user', requirements: ['token' => '[a-zA-Z0-9]+'], methods: 'GET')]
    public function validate(Request $request, UserRepository $userRepository, string $token): Response
    {
        $user = $userRepository->findOneBy(['token' => $token]);
        $user->setIsActivated(true);
        $userRepository->save($user, true);
        $this->addFlash('success', 'Votre compte a bien été activé');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/profile', name: 'app_profile', methods: ['GET', 'POST'])]
    public function profile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $this->getUser());
        $form->handleRequest($request);
        $user = $this->getUser();
        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);
            $entityManager->flush();
            return $this->redirectToRoute('front_default_index');
        }

        $view = match ($user->getRoles()[0]) {
            'ROLE_ADMIN' => 'security/profile_back.html.twig',
            'ROLE_CUSTOMER' => 'security/profile_customer.html.twig',
            'ROLE_USER' => 'security/profile.html.twig',
            default => 'security/profile.html.twig',
        };

        return $this->render($view, [
            'form' => $form->createView(),
            'page' => 'profile',
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function editProfile(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(EditProfileType::class, $this->getUser());
        $form->handleRequest($request);
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $avatar = $form->get('avatar')->getData();
            if ($avatar) {
                $originalFilename = pathinfo($avatar->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $avatar->guessExtension();
                $pathFile = '../../../uploads/avatar/' . $newFilename;
                try {
                    $avatar->move(
                        $this->getParameter('avatar'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    dd($e->getMessage());
                }

                $user->setAvatar($pathFile);
            }
            $this->addFlash('success', 'Votre compte a bien été mis a jour');

            $entityManager->flush();
            return $this->redirectToRoute('app_profile');
        }

        $view = match ($user->getRoles()[0]) {
            'ROLE_ADMIN' => 'security/profile_back_edit.html.twig',
            'ROLE_USER' => 'security/profile_edit.html.twig',
            'ROLE_CUSTOMER' => 'security/profile_customer_edit.html.twig',
            default => 'security/profile.html.twig',
        };

        return $this->render($view, [
            'form' => $form->createView(),
            'page' => 'profile'
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    private function generateTokenForUser(): string
    {
        return $token = bin2hex(random_bytes(64));
    }


}
