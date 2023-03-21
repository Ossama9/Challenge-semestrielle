<?php

namespace App\Controller\Back;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\CommentRepository;
use App\Repository\HotelRepository;
use App\Repository\RequestCustomerRepository;
use App\Repository\ReservationRepository;
use App\Repository\PaymentRepository;
use App\Repository\AnnounceRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/user', name: 'user_')]
class UserController extends AbstractController
{
    #[Route('/{role?}', name: 'index', requirements: ['role' => '(admin|user|customer)'], methods: ['GET'])]
    public function index(
        Request $request,
        UserRepository $userRepository,
        PaginatorInterface $paginator,
        mixed $role,
    ): Response
    {
        $goodRole = match ($role) {
            'admin' => 'ROLE_ADMIN',
            'user' => 'ROLE_USER',
            'customer' => 'ROLE_CUSTOMER',
            default => '',
        };

        $users = $goodRole
            ? $userRepository->findByRole($goodRole)
            : $userRepository->findAll();

        $paginatedUsers = $paginator->paginate(
            $users,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('back/user/index.html.twig', [
            'users' => $paginatedUsers,
            'role' => $role,
            'page' => 'user',
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Request $request, User $user): Response
    {
        return $this->render('back/user/show.html.twig', [
            'user' => $user,
            'page' => 'user',
        ]);
    }

    #[Route('/{id}/remove/{token}', name: 'remove', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function remove(
        Request $request,
        User $user,
        UserRepository $userRepository,
        CommentRepository $commentRepository,
        HotelRepository $hotelRepository,
        RequestCustomerRepository $requestCustomerRepository,
        ReservationRepository $reservationRepository,
        PaymentRepository $paymentRepository,
        AnnounceRepository $announceRepository,
        ManagerRegistry $managerRegistry,
        string $token
    ): Response
    {
        if (!$this->isCsrfTokenValid('remove' . $user->getId(), $token)) {
            throw $this->createAccessDeniedException();
        }

        $userRepository->remove($user, true);
        $this->addFlash('success',"L'utilisateur a bien été supprimé");

        return $this->redirectToRoute('admin_user_index');

    }

    #[Route('/{id}/ban/{token}', name: 'ban', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function ban(
        Request $request,
        User $user,
        UserRepository $userRepository,
        ManagerRegistry $managerRegistry,
        string $token
    ): Response
    {
        if (!$this->isCsrfTokenValid('ban' . $user->getId(), $token)) {
            throw $this->createAccessDeniedException();
        }

        $user->setIsBanned(true);
        $userRepository->save($user, true);

        return $this->redirectToRoute('admin_user_index');
    }

    #[Route('/{id}/unban/{token}', name: 'unban', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function unban(
        Request $request,
        User $user,
        UserRepository $userRepository,
        ManagerRegistry $managerRegistry,
        string $token
    ): Response
    {
        if (!$this->isCsrfTokenValid('unban' . $user->getId(), $token)) {
            throw $this->createAccessDeniedException();
        }

        $user->setIsBanned(false);
        $userRepository->save($user, true);

        return $this->redirectToRoute('admin_user_index');
    }
}
